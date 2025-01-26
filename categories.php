<?php
session_start();
require_once 'auth.php'; // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name']);
    
    // Validate input
    if (empty($categoryName)) {
        $errorMessage = "Category name is required!";
    } else {
        // Prepare and execute the insert query
        $stmt = $pdo->prepare("INSERT INTO categories (name, created_at, updated_at) VALUES (?, NOW(), NOW())");
        $stmt->execute([$categoryName]);
        
        $successMessage = "Category added successfully!";
    }
}

// Handle category deletion
if (isset($_GET['delete_category'])) {
    $categoryId = (int) $_GET['delete_category'];
    
    // Delete the category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    
    header("Location: categories.php"); // Refresh the page after deletion
    exit;
}

// Handle category editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $categoryId = (int) $_POST['category_id'];
    $categoryName = trim($_POST['category_name']);
    
    // Update category name
    if (!empty($categoryName)) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$categoryName, $categoryId]);
        
        $successMessage = "Category updated successfully!";
    } else {
        $errorMessage = "Category name cannot be empty!";
    }
}

// Fetch all categories
$stmt = $pdo->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pocket Donation Tracker-Categories</title>
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

        <!-- Display Success or Error Message -->
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success mt-3">
                <?php echo $successMessage; ?>
            </div>
        <?php elseif (isset($errorMessage)): ?>
            <div class="alert alert-danger mt-3">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Categories Table -->
        <div class="dashboard-card mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Existing Categories</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle me-2"></i> Add Category
                </button>
            </div>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo $category['created_at']; ?></td>
                            <td><?php echo $category['updated_at']; ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="categories.php?delete_category=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Adding Category -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary">Save Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Editing Category -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="category_id" id="category_id">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                        </div>
                        <button type="submit" name="edit_category" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit category modal
        const editCategoryModal = document.getElementById('editCategoryModal');
        editCategoryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const categoryId = button.getAttribute('data-id');
            const categoryName = button.getAttribute('data-name');
            
            document.getElementById('category_id').value = categoryId;
            document.getElementById('edit_category_name').value = categoryName;
        });
    </script>
</body>
</html>
