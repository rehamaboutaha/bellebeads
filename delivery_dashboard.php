<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'deliveryperson') {
    header("Location: login.php");
    exit();
}

include 'conn.php';
$deliverypersonId = $_SESSION['delivery_id'];
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
          <a href="delivery_dashboard.php">
            <div class="welcome">
              <strong>Welcome, Delivery Person!</strong>
            </div>
          </a>
        </div>
        
        <nav class="main-nav flex-grow-1 mx-3 container">
          <ul class="nav d-flex justify-content-center flex-wrap gap-2">
            <li><a href="delivery_dashboard.php"><span class="menu-text">Dashboard</span></a></li>
            <li><a href="manage_orders.php"><span class="menu-text">Orders</span></a></li>
            <li><a href="manage_transactions.php"><span class="menu-text">Payments</span></a></li>
            <li><a href="managedeliveryreports.php"><span class="menu-text">Delivery Reports</span></a></li>
          </ul>
        </nav>

        <div class="user-area position-relative d-flex align-items-center">
          <i id="user-btn" class="fas fa-user fa-lg" style="cursor:pointer; font-size: 28px;"></i>
          <div id="account-box" class="account-box">
            <strong><p>Hi <span><?php echo htmlspecialchars($_SESSION['user_username']); ?></span></p></strong>
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
            <li><a href="delivery_dashboard.php"><span class="menu-text">Dashboard</span></a></li>
            <li><a href="manage_orders.php"><span class="menu-text">Orders</span></a></li>
            <li><a href="manage_transactions.php"><span class="menu-text">Payments</span></a></li>
            <li><a href="managedeliveryreports.php"><span class="menu-text">Delivery Reports</span></a></li>
            </ul>
        </nav>

        <!-- User Icon -->
        <div class="user-area position-relative d-flex align-items-center ms-3">
            <i id="user-btn-mobile" class="fas fa-user" style="cursor:pointer; font-size: 24px;"></i>
            <div id="account-box-mobile" class="account-box" style="display:none; position:absolute; right:0; top:100%;">
            <strong><p>Hi <span><?php echo htmlspecialchars($_SESSION['user_username']); ?></span></p></strong>
            <a href="logout.php" class="dbtn bellebeads-button secondary-btn rounded-0">Logout</a>
          
            </div>
        </div>
    </div>
</aside>

<div class="dashboard-container">
    <h2 class="dashboard-title">Dashboard Overview</h2>

    <div class="dashboard-grid">

        <div class="dashboard-card">
            <h5>Total Orders</h5>
            <p>
                <?php
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE DeliveryID= ? AND Status IN ('Out for Delivery','Completed')");
        $stmt->bind_param("i", $deliverypersonId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo $row['total'];
                ?>
            </p>
        </div>
        
        <div class="dashboard-card">
            <h5>Pending Orders</h5>
            <p>
         <?php
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE DeliveryID = ? AND Status IN ('Out for Delivery')");
        $stmt->bind_param("i", $deliverypersonId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo $row['total']; 
                ?>
            </p>
        </div>
        
        <div class="dashboard-card">
            <h5>Completed Orders</h5>
            <p>
                <?php
                $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM orders WHERE DeliveryID = ? AND Status = 'Completed'");
                $stmt->bind_param("i", $deliverypersonId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>
        
        <div class="dashboard-card">
    <h5>Total Payments</h5>
    <p>
        <?php
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT t.TransactionID) AS total 
                                FROM transaction t
                                JOIN orders o ON t.OrderID = o.OrderID
                                WHERE o.DeliveryID = ? AND o.Status IN ('Out for Delivery', 'Completed')");
        $stmt->bind_param("i", $deliverypersonId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo $row['total'];
        ?>
    </p>
</div>

         <div class="dashboard-card">
            <h5>Pending Payments</h5>
            <p>
                <?php
                $stmt = $conn->prepare("SELECT COUNT(DISTINCT t.TransactionID) AS total 
                                      FROM transaction t
                                      JOIN orders o ON t.OrderID = o.OrderID
                                      WHERE o.DeliveryID = ? AND t.PaymentStatus='Pending' AND o.Status IN ('Out for Delivery', 'Completed')");
                $stmt->bind_param("i", $deliverypersonId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>
 <div class="dashboard-card">
            <h5>Paid Payments</h5>
            <p>
                <?php
                $stmt = $conn->prepare("SELECT COUNT(DISTINCT t.TransactionID) AS total 
                                      FROM transaction t
                                      JOIN orders o ON t.OrderID = o.OrderID
                                      WHERE o.DeliveryID = ? AND t.PaymentStatus='Paid' AND o.Status IN ('Out for delivery', 'Completed')");
                $stmt->bind_param("i", $deliverypersonId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </p>
        </div>
    </div>
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
    max-width: 900px;
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
       width: 300px;
    height:130px;
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