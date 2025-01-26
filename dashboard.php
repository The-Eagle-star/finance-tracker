<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// User data for display
$userFullName = $_SESSION['fullname'] ?? 'User';

// Include the database connection from auth.php
require_once 'auth.php';

// Fetch total donations
$stmt_total_donations = $pdo->query("SELECT SUM(amount) AS total_donations FROM donations");
$total_donations = $stmt_total_donations->fetch()['total_donations'];

// Fetch total donations per category
$stmt_category_donations = $pdo->query("SELECT categories.name AS category_name, SUM(donations.amount) AS total_amount
                                         FROM donations
                                         LEFT JOIN categories ON donations.category_id = categories.id
                                         GROUP BY donations.category_id");
$category_donations = $stmt_category_donations->fetchAll();

// Fetch donations made vs remaining balance for each charity
$stmt_charity_donations = $pdo->query("SELECT charities.title AS charity_title, charities.total_donations, SUM(donations.amount) AS total_donated
                                        FROM donations
                                        LEFT JOIN charities ON donations.charity_id = charities.id
                                        GROUP BY donations.charity_id");
$charity_donations = $stmt_charity_donations->fetchAll();

// Fetch total donations made last month
$last_month = date('Y-m-01', strtotime('first day of last month'));
$next_month = date('Y-m-01', strtotime('first day of this month'));

$stmt_last_month_donations = $pdo->prepare("SELECT SUM(amount) AS total_last_month
                                             FROM donations
                                             WHERE date >= ? AND date < ?");
$stmt_last_month_donations->execute([$last_month, $next_month]);
$total_last_month = $stmt_last_month_donations->fetch()['total_last_month'];

// Fetch donations made this month
$current_month = date('Y-m-01');
$stmt_this_month_donations = $pdo->prepare("SELECT SUM(amount) AS total_this_month
                                             FROM donations
                                             WHERE date >= ?");
$stmt_this_month_donations->execute([$current_month]);
$total_this_month = $stmt_this_month_donations->fetch()['total_this_month'];
$monthly_donations = $pdo->query("
    SELECT MONTH(date) AS month, SUM(amount) AS total 
    FROM donations 
    GROUP BY MONTH(date)
")->fetchAll(PDO::FETCH_KEY_PAIR);

$monthly_totals = [];
for ($i = 1; $i <= 12; $i++) {
    $monthly_totals[] = $monthly_donations[$i] ?? 0; // Fill missing months with 0
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pocket Donation Tracker-Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-header {
            margin-bottom: 30px;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
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
            <h2>Welcome, <?php echo htmlspecialchars($userFullName); ?>!</h2>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>

        <!-- Dashboard Cards -->
        <div class="row mb-4">
            <!-- Total Donations -->
            <div class="col-md-6 my-2">
                <div class="dashboard-card text-center">
                    <i class="bi bi-currency-dollar text-primary icon-large"></i>
                    <h4 class="mt-3">Total Donations</h4>
                    <p><?php echo number_format($total_donations, 2); ?> Euro</p>
                </div>
            </div>

            <!-- Donations This Month -->
            <div class="col-md-6 my-2">
                <div class="dashboard-card text-center">
                    <i class="bi bi-calendar text-success icon-large"></i>
                    <h4 class="mt-3">Donations This Month</h4>
                    <p><?php echo number_format($total_this_month, 2); ?> Euro</p>
                </div>
            </div>

            

           
            
        </div>

        <!-- Donations per Category -->
        <div class="row">
    <?php foreach ($category_donations as $category): ?>
        <div class="col-md-4 mb-4">
            <div class="dashboard-card">
                <div class="d-flex align-items-center">
                    <!-- Category Icon -->
                    <i class="bi bi-tag text-primary me-3 icon-large"></i>
                    <h4 class="text-center"><?php echo htmlspecialchars($category['category_name']); ?></h4>
                </div>
                <p class="text-center">
                    <i class="bi bi-currency-dollar text-success"></i> Total Donations: Euro <?php echo number_format($category['total_amount'], 2); ?>
                </p>
                <ul class="list-group">
                    <?php
                    // Fetch donations for this category
                    $stmt_donations_per_category = $pdo->prepare("SELECT * FROM donations WHERE category_id = ?");
                    $stmt_donations_per_category->execute([$category['category_id']]);
                    $donations_per_category = $stmt_donations_per_category->fetchAll();

                    foreach ($donations_per_category as $donation): ?>
                        <li class="list-group-item">
                            <!-- Donation Amount Icon -->
                            <i class="bi bi-currency-dollar text-primary"></i> <strong>Amount:</strong> Euro <?php echo number_format($donation['amount'], 2); ?>
                            <br><i class="bi bi-calendar text-secondary"></i> <strong>Date:</strong> <?php echo $donation['date']; ?>
                            <br><i class="bi bi-pen text-info"></i> <strong>Notes:</strong> <?php echo htmlspecialchars($donation['notes']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<div class="row">
    <?php foreach ($charity_donations as $charity): ?>
        <div class="col-md-4 mb-4">
            <div class="dashboard-card">
                <div class="d-flex align-items-center">
                    <!-- Charity Icon -->
                    <i class="bi bi-heart text-danger me-3 icon-large"></i>
                    <h4 class="text-center"><?php echo htmlspecialchars($charity['charity_title']); ?></h4>
                </div>
                <p class="text-center">
                    <i class="bi bi-currency-dollar text-primary"></i> Donated: Euro <?php echo number_format($charity['total_donated'], 2); ?>
                    <br>
                    <i class="bi bi-gift text-success"></i> Goal: Euro <?php echo number_format($charity['total_donations'], 2); ?>
                    <br>
                    <i class="bi bi-currency-dollar text-warning"></i> Remaining: Euro <?php echo number_format($charity['total_donations'] - $charity['total_donated'], 2); ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="container mt-4">
    <h3 class="text-center mb-4">Donation Insights</h3>
    <div class="row">
        <div class="col-md-6">
            <div class="dashboard-card">
                <h5 class="text-center">Total Donations Over Time</h5>
                <canvas id="totalDonationsChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card">
                <h5 class="text-center">Donations by Category</h5>
                <canvas id="categoryDonationsChart"></canvas>
            </div>
        </div>
    </div>
</div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Total Donations Over Time Data (Example - customize as per your DB structure)
    const totalDonationsData = {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    datasets: [{
        label: "Donations (Euro)",
        data: <?php echo json_encode($monthly_totals); ?>,
        borderColor: "rgba(75, 192, 192, 1)",
        backgroundColor: "rgba(75, 192, 192, 0.2)",
        tension: 0.4
    }]
};


    // Donations by Category Data
    const categoryDonationsData = {
    labels: <?php echo json_encode(array_column($category_donations, 'category_name')); ?>,
    datasets: [{
        label: "Donations (Euro)",
        data: <?php echo json_encode(array_column($category_donations, 'total_amount')); ?>,
        backgroundColor: [
            "rgba(255, 99, 132, 0.2)",
            "rgba(54, 162, 235, 0.2)",
            "rgba(255, 206, 86, 0.2)",
            "rgba(75, 192, 192, 0.2)",
            "rgba(153, 102, 255, 0.2)"
        ],
        borderColor: [
            "rgba(255, 99, 132, 1)",
            "rgba(54, 162, 235, 1)",
            "rgba(255, 206, 86, 1)",
            "rgba(75, 192, 192, 1)",
            "rgba(153, 102, 255, 1)"
        ],
        borderWidth: 1
    }]
};


    // Total Donations Over Time Chart
    new Chart(document.getElementById("totalDonationsChart"), {
        type: "line",
        data: totalDonationsData,
        options: {
            responsive: true,
            plugins: {
                legend: { position: "top" },
                title: { display: true, text: "Total Donations Over Time" }
            }
        }
    });

    // Donations by Category Chart
    new Chart(document.getElementById("categoryDonationsChart"), {
        type: "bar",
        data: categoryDonationsData,
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: "Donations by Category" }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>


</body>
</html>
