<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Monthly sales
$monthlySales = [];
$result = $conn->query("SELECT 
    DATE_FORMAT(OrderDate, '%Y-%m') AS month, 
    SUM(TotalAmount) AS total 
    FROM `orders` 
    WHERE Status = 'Completed'
    GROUP BY DATE_FORMAT(OrderDate, '%Y-%m') 
    ORDER BY month");

while($row = $result->fetch_assoc()) {
    $monthlySales[] = $row;
}

// Daily sales (last 30 days)
$dailySales = [];
$result = $conn->query("SELECT 
    DATE(OrderDate) AS day, 
    SUM(TotalAmount) AS total 
    FROM `orders` 
    WHERE Status = 'Completed' AND OrderDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(OrderDate) 
    ORDER BY day");

while($row = $result->fetch_assoc()) {
    $dailySales[] = $row;
}

// Category sales (total items sold per category)
$categorySales = [];
$result = $conn->query("
    SELECT c.Name AS category, SUM(oi.Quantity) AS total_quantity
    FROM orderitem oi
    JOIN product p ON oi.ProductID = p.ProductID
    JOIN category c ON p.CategoryID = c.CategoryID
    JOIN `orders` o ON oi.OrderID = o.OrderID
    WHERE o.Status = 'Completed'
    GROUP BY c.CategoryID, c.Name
    ORDER BY total_quantity DESC
");

while ($row = $result->fetch_assoc()) {
    $categorySales[] = $row;
}
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belle Beads</title>
    <link rel="shortcut icon" type="image/x-icon" href="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg">
<!-- font awesome cdn link  -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- custom admin css file link  -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="Reham/css/vendor/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="Reham/css/vendor/font.awesome.min.css">
    <!-- Linear Icons CSS -->
    <link rel="stylesheet" href="Reham/css/vendor/linearicons.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="Reham/css/plugins/swiper-bundle.min.css">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="Reham/css/plugins/animate.min.css">
    <!-- Jquery ui CSS -->
    <link rel="stylesheet" href="Reham/css/plugins/jquery-ui.min.css">
    <!-- Nice Select CSS -->
    <link rel="stylesheet" href="Reham/css/plugins/nice-select.min.css">
    <!-- Magnific Popup -->
    <link rel="stylesheet" href="Reham/css/plugins/magnific-popup.css">
    <!-- Main Style CSS -->
    <link rel="stylesheet" href="Reham/css/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body>
    <header class="main-header-area" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 1000;">
  <div class="main-header header-sticky">
    <div class="custom-area">
      <div class="d-flex align-items-center justify-content-between w-100">

        <div class="header-logo d-flex align-items-center">
<a href="admin_dashboard2.php" style="color: inherit; text-decoration: none;">
            <div class="welcome">
              <strong>Welcome, Admin</strong>
            </div>
          </a>
        </div>
        <nav class="main-nav flex-grow-1 mx-3">
          <ul class="nav d-flex justify-content-center flex-wrap gap-2">
            <li><a href="admin_dashboard2.php " ><span class="menu-text">Dashboard</span></a></li>
            <li><a href="manage_users.php"><span class="menu-text">Users</span></a></li>
            <li><a href="view_messages.php"><span class="menu-text">Messages</span></a></li>
            <li><a href="manage-subscribers.php"><span class="menu-text">Subscribers</span></a></li>
            <li><a href="manage_orders.php"><span class="menu-text">Orders</span></a></li>
            <li><a href="manage_transactions.php"><span class="menu-text">Payments</span></a></li>
            <li><a href="manage_delivery.php"><span class="menu-text">Delivery</span></a></li>
            <li><a href="manage_categories.php"><span class="menu-text">Categories</span></a></li>
            <li><a href="manage_products.php"><span class="menu-text">Products</span></a></li>
            <li><a href="sales_report.php"><span class="menu-text">Sales</span></a></li>
            <li><a href="manage_discounts.php"><span class="menu-text">Discounts</span></a></li>
          </ul>
        </nav>

        <!-- Right (User Icon) -->
        <div class="user-area position-relative d-flex align-items-center">
          <i id="user-btn" class="fas fa-user fa-lg" style="cursor:pointer; font-size: 28px;"></i>
          <div id="account-box" class="account-box">
            <strong><p>Hi <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></p></strong>
            <p>Email: <span><?php echo isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Not logged in'; ?></span></p>
            <a href="logout.php" class="dbtn bellebeads-button secondary-btn rounded-0">Logout</a>
          </div>
        </div>

      </div>
    </div>
  </div>
</header>

        <aside class="off-canvas-wrapper d-block d-lg-none" id="mobileMenu" style="padding: 10px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <nav class="mobile-navigation flex-grow-1">
            <ul class="nav d-flex flex-nowrap overflow-auto" style="gap: 10px; white-space: nowrap; padding: 0; margin: 0;">
                <li><a href="admin_dashboard2.php"><span class="menu-text">Dashboard</span></a></li>
                <li><a href="manage_users.php"><span class="menu-text">Users</span></a></li>
                <li><a href="view_messages.php"><span class="menu-text">Messages</span></a></li>
                <li><a href="manage-subscribers.php"><span class="menu-text">Subscribers</span></a></li>
                <li><a href="manage_orders.php"><span class="menu-text">Orders</span></a></li>
                <li><a href="manage_transactions.php"><span class="menu-text">Payments</span></a></li>
                <li><a href="manage_delivery.php"><span class="menu-text">Delivery</span></a></li>
                <li><a href="manage_categories.php"><span class="menu-text">Categories</span></a></li>
                <li><a href="manage_products.php"><span class="menu-text">Products</span></a></li>
                <li><a href="sales_report.php"><span class="menu-text">Sales</span></a></li>
                <li><a href="manage_discounts.php"><span class="menu-text">Discounts</span></a></li>
            </ul>
        </nav>

        <!-- User Icon -->
        <div class="user-area position-relative d-flex align-items-center ms-3">
            <i id="user-btn-mobile" class="fas fa-user" style="cursor:pointer; font-size: 24px;"></i>
            <div id="account-box-mobile" class="account-box" style="display:none; position:absolute; right:0; top:100%;">
                <strong><p>Hi <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></p></strong>
                <p>Email: <span><?php echo isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Not logged in'; ?></span></p>
                <a href="logout.php" class="dbtn bellebeads-button secondary-btn rounded-0">Logout</a>
            </div>
        </div>
    </div>
</aside>
   <div class="breadcrumbs-area position-relative">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="breadcrumb-content position-relative section-content">
                        <h3 class="title-3">Sales Report </h3>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-4">
                <div class="dashboard-card card mb-4" style="  margin-left: 230px;">
                    <div class="card-body" >
                        <h5 class="card-title">Total Sales</h5>
                        <p class="card-text fs-4">
                            <?php
                            $result = $conn->query("SELECT SUM(TotalAmount) AS total_sales FROM `orders` WHERE Status = 'Completed'");
                            $row = $result->fetch_assoc();
                            echo '$' . number_format($row['total_sales'], 2);
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="graph-container">
            <h5 class="graph-title">Monthly Sales</h5>
            <div class="row">
                <?php 
                $maxMonthly = max(array_column($monthlySales, 'total')) ?: 1;
                foreach ($monthlySales as $sale): 
                    $percentage = ($sale['total'] / $maxMonthly) * 100;
                ?>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= date('M Y', strtotime($sale['month'])) ?></span>
                            <span>$<?= number_format($sale['total'], 2) ?></span>
                        </div>
                        <div class="progress progress-thin">
<div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%; background-color:rgb(175, 138, 113);" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="graph-container">
            <h5 class="graph-title">Daily Sales (Last 30 Days)</h5>
            <div class="row">
                <?php 
                $maxDaily = max(array_column($dailySales, 'total')) ?: 1;
                foreach ($dailySales as $sale): 
                    $percentage = ($sale['total'] / $maxDaily) * 100;
                ?>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= date('M j', strtotime($sale['day'])) ?></span>
                            <span>$<?= number_format($sale['total'], 2) ?></span>
                        </div>
                        <div class="progress progress-thin">
<div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%; background-color:rgb(219, 191, 157);" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Category Sales Graph -->
        <div class="graph-container">
            <h5 class="graph-title">Items Sold by Category</h5>
            <div class="row">
                <?php 
                $maxCategory = max(array_column($categorySales, 'total_quantity')) ?: 1;
                foreach ($categorySales as $category): 
                    $percent = ($category['total_quantity'] / $maxCategory) * 100;
                ?>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= htmlspecialchars($category['category']) ?></span>
                            <span><?= $category['total_quantity'] ?> pcs</span>
                        </div>
                        <div class="progress progress-thin">
<div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%; background-color:rgb(223, 197, 180);" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
    <style>
        .progress-thin {
            height: 10px;
        }
        .graph-container {
            background: white;
            width:1200px;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            margin-left: 230px;

            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .graph-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #333;
            
        }
    </style>
<style>
   .user-area {
        margin-left: auto !important;
    margin-right: 35px !important;
    padding-right: 0 !important;
    display: flex;
    align-items: center;}
    #user-btn {
    font-size: 30px;
    cursor: pointer;
}

.account-box {
    position: absolute;
    top: 130%;
    right: 0;
    background: #fff;
    border: 1px solid #ccc;
    padding: 10px 15px;
    width: 400px;
    height:170px;
    display: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    z-index: 999;
}

.account-box.active {
    display: block;
}

.account-box p,
.account-box a,
.account-box div {
    font-size: 15px;
    margin: 6px 0;
}

    </style>
  

<!-- jQuery JS -->
<script src="Reham/js/vendor/jquery-3.6.0.min.js"></script>
<!-- jQuery Migrate JS -->
<script src="Reham/js/vendor/jquery-migrate-3.3.2.min.js"></script>
<!-- Modernizer JS -->
<script src="Reham/js/vendor/modernizr-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="Reham/js/vendor/bootstrap.bundle.min.js"></script>

<!-- Swiper Slider JS -->
<script src="Reham/js/plugins/swiper-bundle.min.js"></script>
<!-- nice select JS -->
<script src="Reham/js/plugins/nice-select.min.js"></script>
<!-- Ajaxchimpt js -->
<script src="Reham/js/plugins/jquery.ajaxchimp.min.js"></script>
<!-- Jquery Ui js -->
<script src="Reham/js/plugins/jquery-ui.min.js"></script>
<!-- Jquery Countdown js -->
<script src="Reham/js/plugins/jquery.countdown.min.js"></script>
<!-- jquery magnific popup js -->
<script src="Reham/js/plugins/jquery.magnific-popup.min.js"></script>

<!-- Main JS -->
<script src="Reham/js/main.js"></script>


<script>
document.getElementById('user-btn').addEventListener('click', function () {
    const box = document.getElementById('account-box');
    box.classList.toggle('active');
});

document.addEventListener('click', function (e) {
    const box = document.getElementById('account-box');
    const icon = document.getElementById('user-btn');
    if (!box.contains(e.target) && !icon.contains(e.target)) {
        box.classList.remove('active');
    }
});
  </script>
  
</body>


</html>

