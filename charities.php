<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Include the database connection from auth.php
require_once 'auth.php';

// Handle form submission to add a new charity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_charity'])) {
    // Sanitize inputs
    $title = htmlspecialchars(trim($_POST['title']));
    $short_description = htmlspecialchars(trim($_POST['short_description']));
   // $logo = htmlspecialchars(trim($_POST['logo']));
    $total_donations = isset($_POST['total_donations']) ? (float) $_POST['total_donations'] : 0.00;
    $logo = $_FILES['logo'];
    $logo_name = uniqid() . '_' . basename($logo['name']);
    $target_dir = 'uploads/';
    $target_file = $target_dir . $logo_name;

    if (move_uploaded_file($logo['tmp_name'], $target_file)&& $title && $short_description && $total_donations >= 0) {
        // Insert charity data into database
        $stmt = $pdo->prepare("INSERT INTO charities (title, logo, short_description, total_donations, created_at, updated_at) 
                               VALUES (?, ?, ?, ?, NOW(), NOW())");
        $result = $stmt->execute([$title, $logo_name, $short_description, $total_donations]);

        if ($result) {
            header("Location: charities.php");
            exit;
        } else {
            $error_message = "Failed to add charity.";
        }
    } else {
        $error_message = "Please fill in all fields and provide a valid total donations amount.";
    }
}

// Handle edit charity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_charity'])) {
    // Sanitize inputs
    $id = (int)$_POST['id'];
    $title = htmlspecialchars(trim($_POST['title']));
    $short_description = htmlspecialchars(trim($_POST['short_description']));
    //$logo = htmlspecialchars(trim($_POST['logo']));
    $total_donations = isset($_POST['total_donations']) ? (float) $_POST['total_donations'] : 0.00;
    $logo = $_FILES['logo'];
    $logo_name = uniqid() . '_' . basename($logo['name']);
    $target_dir = 'uploads/';
    $target_file = $target_dir . $logo_name;

    if (move_uploaded_file($logo['tmp_name'], $target_file) && $title && $short_description && $total_donations >= 0) {
        // Update charity data in the database
        $stmt = $pdo->prepare("UPDATE charities SET title = ?, logo = ?, short_description = ?, total_donations = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$title, $logo, $short_description, $total_donations, $id]);

        if ($result) {
            header("Location: charities.php");
            exit;
        } else {
            $error_message = "Failed to update charity.";
        }
    } else {
        $error_message = "Please fill in all fields and provide a valid total donations amount.";
    }
}

// Handle delete charity
if (isset($_GET['delete_charity'])) {
    $id = (int)$_GET['delete_charity'];
    $stmt = $pdo->prepare("DELETE FROM charities WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        header("Location: charities.php");
        exit;
    } else {
        $error_message = "Failed to delete charity.";
    }
}

// Fetch all charities from the database
$stmt = $pdo->query("SELECT * FROM charities");
$charities = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charities</title>
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

        <!-- Display Error Message -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Button to trigger Add Charity modal -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCharityModal">
            <i class="bi bi-plus-circle me-2"></i> Add New Charity
        </button>

        <!-- Charities Table -->
        <div class="dashboard-card mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Existing Charities</h4>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Logo</th>
                        <th>Description</th>
                        <th>Donation Goal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($charities as $charity): ?>
                        <tr>
                            <td><?php echo $charity['id']; ?></td>
                            <td><?php echo $charity['title']; ?></td>
                            <td><img src="uploads/<?php echo $charity['logo']; ?>" alt="Logo" width="50"></td>
                            <td><?php echo $charity['short_description']; ?></td>
                            <td><?php echo number_format($charity['total_donations'], 2); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCharityModal" data-id="<?php echo $charity['id']; ?>" data-title="<?php echo htmlspecialchars($charity['title']); ?>" data-logo="<?php echo htmlspecialchars($charity['logo']); ?>" data-description="<?php echo htmlspecialchars($charity['short_description']); ?>" data-donations="<?php echo $charity['total_donations']; ?>">Edit</button>
                                <a href="charities.php?delete_charity=<?php echo $charity['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this charity?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Charity Modal -->
    <div class="modal fade" id="addCharityModal" tabindex="-1" aria-labelledby="addCharityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCharityModalLabel">Add New Charity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="charities.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Charity Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo URL</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description</label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="total_donations" class="form-label">Donation Goal</label>
                            <input type="number" class="form-control" id="total_donations" name="total_donations" step="0.01" min="0" value="0.00" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="submit_charity">Save Charity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Charity Modal -->
    <div class="modal fade" id="editCharityModal" tabindex="-1" aria-labelledby="editCharityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCharityModalLabel">Edit Charity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="charities.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editCharityId">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Charity Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLogo" class="form-label">Logo URL</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="editShortDescription" class="form-label">Short Description</label>
                            <textarea class="form-control" id="editShortDescription" name="short_description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editTotalDonations" class="form-label">Donation Goal</label>
                            <input type="number" class="form-control" id="editTotalDonations" name="total_donations" step="0.01" min="0" value="0.00" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="edit_charity">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Populate Edit Modal with current charity data
        const editModal = document.getElementById('editCharityModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const title = button.getAttribute('data-title');
            const logo = button.getAttribute('data-logo');
            const description = button.getAttribute('data-description');
            const donations = button.getAttribute('data-donations');

            document.getElementById('editCharityId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editLogo').value = logo;
            document.getElementById('editShortDescription').value = description;
            document.getElementById('editTotalDonations').value = donations;
        });
    </script>
</body>
</html>
