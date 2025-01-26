<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Include the database connection file
require_once 'auth.php'; // Ensure this file contains your DB connection logic

// Handle form submission to add a new donation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_donation'])) {
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0.00;
    $notes = htmlspecialchars(trim($_POST['notes']));
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : null;
    $charity_id = isset($_POST['charity_id']) ? (int) $_POST['charity_id'] : null;
    $date = date('Y-m-d'); // Today's date for the donation

    if ($amount > 0 && $category_id && $charity_id) {
        // Insert donation data into database
        $stmt = $pdo->prepare("INSERT INTO donations (amount, date, notes, category_id, charity_id, created_at, updated_at) 
                               VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $result = $stmt->execute([$amount, $date, $notes, $category_id, $charity_id]);

        if ($result) {
            header("Location: donations.php");
            exit;
        } else {
            $error_message = "Failed to make the donation.";
        }
    } else {
        $error_message = "Please fill in all fields and provide a valid donation amount.";
    }
}

// Fetch categories from the database
$stmt_categories = $pdo->query("SELECT * FROM categories");
$categories = $stmt_categories->fetchAll();

// Fetch charities from the database
$stmt_charities = $pdo->query("SELECT * FROM charities");
$charities = $stmt_charities->fetchAll();

// Fetch all donations
$stmt_donations = $pdo->query("SELECT donations.id, donations.amount, donations.date, donations.notes, categories.name AS category_name, charities.title AS charity_title 
                               FROM donations 
                               LEFT JOIN categories ON donations.category_id = categories.id
                               LEFT JOIN charities ON donations.charity_id = charities.id");
$donations = $stmt_donations->fetchAll();
// Handle donation deletion
if (isset($_GET['delete_donation'])) {
    $donation_id = $_GET['delete_donation'];

    $stmt_delete = $pdo->prepare("DELETE FROM donations WHERE id = ?");
    $stmt_delete->execute([$donation_id]);

    header("Location: donations.php");
    exit;
}





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pocket Donation Tracker-Donations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .dashboard-header {
            margin-bottom: 30px;
        }
        .icon-large {
            font-size: 3rem;
        }
    </style>
</head>
<body>
    <!-- Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-group">
            <li class="list-group-item">
    <a href="dashboard.php" class="text-decoration-none">
        <i class="bi bi-graph-up me-2"></i> Analytics
    </a>
</li>

                <li class="list-group-item">
                    <a href="charities.php" class="text-decoration-none">
                        <i class="bi bi-heart me-2"></i> Charities
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="donations.php" class="text-decoration-none">
                        <i class="bi bi-currency-dollar me-2"></i> Donations
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="categories.php" class="text-decoration-none">
                        <i class="bi bi-folder me-2"></i> Categories
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center dashboard-header">
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                <i class="bi bi-list"></i> Menu
            </button>
            <div class="text-center w-100">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h2>
            </div>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>

        <!-- Display error message -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Button to open donation modal -->
        <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#donationModal">
            Make a Donation
        </button>

        <!-- Donation Form Modal -->
        <div class="modal fade" id="donationModal" tabindex="-1" aria-labelledby="donationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="donationModalLabel">Donation Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="donations.php" method="POST">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Select Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Choose a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="charity_id" class="form-label">Select Charity</label>
                                <select class="form-select" id="charity_id" name="charity_id" required>
                                    <option value="">Choose a charity</option>
                                    <?php foreach ($charities as $charity): ?>
                                        <option value="<?php echo $charity['id']; ?>"><?php echo $charity['title']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Donation Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" name="submit_donation">Make Donation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        

        <!-- Donations Table -->
        <h3 class="mt-5">All Donations</h3>
        <div class="dashboard-card">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Charity</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Notes</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td><?php echo $donation['id']; ?></td>
                            <td><?php echo $donation['charity_title']; ?></td>
                            <td><?php echo $donation['category_name']; ?></td>
                            <td><?php echo number_format($donation['amount'], 2); ?></td>
                            <td><?php echo $donation['date']; ?></td>
                            <td><?php echo $donation['notes']; ?></td>
                            <td>

    <a href="donations.php?delete_donation=<?php echo $donation['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this donation?')">Delete</a>
</td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
   

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
