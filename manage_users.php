<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();}
include 'conn.php';
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    $deliveryCheck = $conn->query("SELECT COUNT(*) as delivery_count FROM orders WHERE UserID = $id AND Status = 'Out for Delivery'");
    $deliveryResult = $deliveryCheck->fetch_assoc();
    
    if ($deliveryResult['delivery_count'] > 0) {
        echo "Cannot delete user: They have ".$deliveryResult['delivery_count']." active delivery(ies) in progress.";
        exit();
    }

    $conn->begin_transaction();
    
    try {
        $tablesToDelete = [
            'review' => 'UserID',
            'favorite' => 'UserID',
            'newslettersubscriber' => 'UserID',
            'message' => 'UserID',
            'pointstransaction' => 'UserID'
        ];
        
        foreach ($tablesToDelete as $table => $column) {
            if (!$conn->query("DELETE FROM $table WHERE $column = $id")) {
                throw new Exception("Failed to delete from $table: " . $conn->error);
            }
        }
                $orders = $conn->query("SELECT OrderID, Status FROM orders WHERE UserID = $id");
        
        while ($order = $orders->fetch_assoc()) {
            $orderId = $order['OrderID'];
            $status = $order['Status'];
            
            if (in_array($status, ['Pending', 'Confirmed','Canceled'])) {
                
                if (!$conn->query("DELETE FROM transaction WHERE OrderID = $orderId")) {
                    throw new Exception("Failed to delete transactions: " . $conn->error);
                }
                
                if (!$conn->query("DELETE FROM orderitem WHERE OrderID = $orderId")) {
                    throw new Exception("Failed to delete order items: " . $conn->error);
                }
                
                if (!$conn->query("DELETE FROM orders WHERE OrderID = $orderId")) {
                    throw new Exception("Failed to delete order: " . $conn->error);
                }
            } else {
                if (!$conn->query("UPDATE orders SET UserID = NULL WHERE OrderID = $orderId")) {
                    throw new Exception("Failed to anonymize order: " . $conn->error);
                }
            }
        }
        
        if (!$conn->query("UPDATE shippingaddress SET UserID = NULL WHERE UserID = $id")) {
            throw new Exception("Failed to unlink shipping addresses: " . $conn->error);
        }
        
        if (!$conn->query("DELETE sa FROM shippingaddress sa LEFT JOIN orders o ON sa.AddressID = o.ShippingAddressID WHERE sa.UserID IS NULL AND o.ShippingAddressID IS NULL")) {
            throw new Exception("Failed to clean up unused addresses: " . $conn->error);
        }
        
        if (!$conn->query("DELETE FROM registereduser WHERE UserID = $id")) {
            throw new Exception("Failed to delete user: " . $conn->error);
        }
        
        $conn->commit();
        echo "User and associated data deleted successfully.";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
        exit();
    }
}
$result = $conn->query("SELECT * FROM registereduser");
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belle Beads</title>
    <link rel="shortcut icon" type="image/x-icon" href="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
                        <h3 class="title-3">Manage Users </h3>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

<body>
<div class="container mt-4">
    <table class="table table-bordered ">
    <thead class="table-dark">

        <tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Username</th><th>Email</th><th>Phone Number</th><th>Action</th></tr></thead>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['UserID'] ?></td>
                <td><?= $row['first_name'] ?></td>
                <td><?= $row['last_name'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['phone'] ?></td>
                <td><a href="?delete_id=<?= $row['UserID'] ?>" class="btn bellebeads-button secondary-btn rounded-0">Delete</a></td>

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





