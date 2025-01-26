<?php
session_start();

// Database configuration (replace with your actual database credentials)
$host = '127.0.0.1';
$db   = 'laravel';
$user = 'user';
$pass = 'user';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

function registerUser($fullname, $email, $password) {
    global $pdo;
    
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $pdo->prepare('INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)');
    $result = $stmt->execute([$fullname, $email, $hashedPassword]);
    
    return ['success' => $result, 'message' => $result ? 'Registration successful' : 'Registration failed'];
}

function loginUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    return ['success' => false, 'message' => 'Invalid credentials'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'register') {
        $result = registerUser($_POST['fullname'], $_POST['email'], $_POST['password']);
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            // In a real app, you'd pass error message via session
            echo $result['message'];
        }
    }
    
    if ($action === 'login') {
        $result = loginUser($_POST['email'], $_POST['password']);
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            echo $result['message'];
        }
    }
}
?>