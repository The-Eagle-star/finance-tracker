<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/error.log'); // Replace with an appropriate path

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Use existing database connection from auth file
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize inputs
        $charity_id = filter_input(INPUT_POST, 'charity_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $user_id = $_SESSION['user_id'];

        if (!$charity_id || !$amount || $amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            exit;
        }

        // Start transaction
        $pdo->beginTransaction();

        // Ensure donations table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS donations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id BIGINT UNSIGNED NOT NULL,
            charity_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert donation record
        $stmt = $pdo->prepare("INSERT INTO donations (category_id, charity_id, amount,date) VALUES (?, ?, ?,NOW()");
        $stmt->execute([1, $charity_id, $amount]);

        // Update charity total donations
        $stmt = $pdo->prepare("UPDATE charities SET total_donations = total_donations + ? WHERE id = ?");
        $stmt->execute([$amount, $charity_id]);

        // Commit transaction
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Donation successful!']);
    } catch (Exception $e) {
        // Roll back only if a transaction is active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>
