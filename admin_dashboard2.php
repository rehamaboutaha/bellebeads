<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'conn.php';
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

</head>
<body>
    <header class="main-header-area" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 1000;">
  <div class="main-header header-sticky">
    <div class="custom-area">
      <div class="d-flex align-items-center justify-content-between w-100">

        <div class="header-logo d-flex align-items-center">
          <a href="admin_dashboard2.php">
            <div class="welcome">
              <strong>Welcome, Admin</strong>
            </div>
          </a>
        </div>
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

<div class="dashboard-container">
    <h2 class="dashboard-title">Dashboard Overview</h2>

    <div class="dashboard-grid">

    <div class="dashboard-card">
    <h5>Admins</h5>
    <p>
        <?php
        $result = $conn->query("SELECT COUNT(*) AS total FROM admin");
        $row = $result->fetch_assoc();
        echo $row['total'];
        ?>
    </p>
</div>
    <div class="dashboard-card">
    <h5>Registered Users</h5>
    <p>
        <?php
        $result = $conn->query("SELECT COUNT(*) AS total FROM registereduser");
        $row = $result->fetch_assoc();
        echo $row['total'];
        ?>
    </p>
</div>
<div class="dashboard-card">
            <h5>Delivery Person</h5>
            <p>
                <?php
                $result = $conn->query("SELECT COUNT(*) AS total FROM deliveryperson");
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>
        <div class="dashboard-card">
            <h5>Pending Orders</h5>
            <p>
                <?php
                $result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE Status = 'pending'");
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>

        <div class="dashboard-card">
            <h5>Total Sales</h5>
            <p>
                <?php
                $result = $conn->query("SELECT SUM(TotalAmount) AS total_sales FROM orders WHERE Status = 'Completed'");
                $row = $result->fetch_assoc();
                echo '$' . number_format($row['total_sales'], 2);
                ?>
            </p>
        </div>

        
        <div class="dashboard-card">
    <h5>Newsletter Subscribers</h5>
    <p>
        <?php
        $result = $conn->query("SELECT COUNT(*) AS total FROM newslettersubscriber");
        $row = $result->fetch_assoc();
        echo $row['total'];
        ?>
    </p>
</div>

        <div class="dashboard-card">
            <h5>Categories</h5>
            <p>
                <?php
                $result = $conn->query("SELECT COUNT(*) AS total FROM category");
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>
        <div class="dashboard-card">
    <h5>Products</h5>
    <p>
        <?php
        $result = $conn->query("SELECT COUNT(*) AS total FROM product");
        $row = $result->fetch_assoc();
        echo $row['total'];
        ?>
    </p>
</div>
<div class="dashboard-card">
    <h5>Messages</h5>
    <p>
<?php
        $result = $conn->query("SELECT COUNT(*) AS total FROM message");
        $row = $result->fetch_assoc();
        echo $row['total'];
        ?>
    </p>
</div>
<div class="dashboard-card">
    <h5>Discount Codes</h5>
    <p>
        <?php
        $result = $conn->query("SELECT COUNT(*) AS total FROM discountcode");
        $row = $result->fetch_assoc();
        echo $row['total'];
        ?>
    </p>
</div>
<div class="dashboard-card">
    <h5>Delivery Report</h5>
    <p><a href="managedeliveryreports.php">Click here</a></p>
</div>



    <style>
        .welcome{font-size:20px;}
        
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
.h2{text-align:center;}

.dashboard-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

.dashboard-title {
    text-align: center;
    margin-bottom: 30px;
    font-size: 28px;
}

.dashboard-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.dashboard-card {
    background-color: beige;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    width: 220px;
    height: 140px;
    padding: 20px;
    text-align: center;
    transition: transform 0.2s ease;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}

.dashboard-card h5 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #333;
}

.dashboard-card p {
    font-size: 24px;
    font-weight: bold;
    color:rgb(167, 125, 70);
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
