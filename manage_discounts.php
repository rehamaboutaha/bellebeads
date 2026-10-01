<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['code']);
    $value = floatval($_POST['value']);
    $expiry = $conn->real_escape_string($_POST['expiry']);
    $conn->query("INSERT INTO discountcode (Code, DiscountValue, ExpiryDate) VALUES ('$code', $value, '$expiry')");
}

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM discountcode WHERE DiscountCodeID = $id");
}

$result = $conn->query("SELECT * FROM discountcode");
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belle Beads</title>
    <link rel="shortcut icon" type="image/x-icon" href="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg">

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <!-- Header Area Start Here -->
    <header class="main-header-area" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 1000;">
  <div class="main-header header-sticky">
    <div class="custom-area">
      <div class="d-flex align-items-center justify-content-between w-100">

        <!-- Left (Logo/Welcome) -->
        <div class="header-logo d-flex align-items-center">
          <a href="admin_dashboard2.php">
            <div class="welcome">
              <strong>Welcome, Admin</strong>
            </div>
          </a>
        </div>
        <!-- Center (Navigation) -->
        <nav class="main-nav flex-grow-1 mx-3">
          <ul class="nav d-flex justify-content-center flex-wrap gap-2">
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

        <!-- Right (User Icon) -->
        <div class="user-area position-relative d-flex align-items-center">
          <i id="user-btn" class="fas fa-user fa-lg" style="cursor:pointer; font-size: 28px;"></i>
          <div id="account-box" class="account-box">
            <strong><p>Hi <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></p></strong>
            <p>Email: <span><?php echo isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Not logged in'; ?></span></p>
            <a href="logout.php" class="btn bellebeads-button secondary-btn rounded-0">Logout</a>
          </div>
        </div>

      </div>
    </div>
  </div>
</header>
        <!-- Main Header Area End -->
        <!-- off-canvas menu start -->
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
                <a href="logout.php" class="btn bellebeads-button secondary-btn rounded-0">Logout</a>
            </div>
        </div>
    </div>
</aside>
    <div class="breadcrumbs-area position-relative">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="breadcrumb-content position-relative section-content">
                        <h3 class="title-3">Manage Discount Codes </h3>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="container mt-4">
    <h2>Discount Codes</h2>
    <form method="post" class="mb-3">
        <input type="text" name="code" class="form-control" placeholder="Discount Code" required>
        <input type="number" name="value" step="0.01" class="form-control mt-2" placeholder="Value (%)" required>
        <input type="date" name="expiry" class="form-control mt-2" required>
        <br>
        <button class="btn bellebeads-button secondary-btn rounded-0">Add Discount</button>
    </form>
    <table class="table table-bordered">
    <thead class="table-dark">
<tr><th>ID</th><th>Code</th><th>Value</th><th>Expiry</th><th>Action</th></tr></thead>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['DiscountCodeID'] ?></td>
                <td><?= $row['Code'] ?></td>
                <td><?= $row['DiscountValue'] ?>%</td>
                <td><?= $row['ExpiryDate'] ?></td>
                <td><a href="?delete_id=<?= $row['DiscountCodeID'] ?>" class="btn bellebeads-button secondary-btn rounded-0">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

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
