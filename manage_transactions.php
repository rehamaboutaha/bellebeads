<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'deliveryperson'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'], $_POST['payment_status'])) {
    $transactionId = intval($_POST['transaction_id']);
    $paymentStatus = $conn->real_escape_string($_POST['payment_status']);

    $conn->query("UPDATE transaction SET PaymentStatus = '$paymentStatus' WHERE TransactionID = $transactionId");
}

if ($_SESSION['role'] === 'admin') {
    $sql = "SELECT t.*, o.TotalAmount, o.OrderDate 
            FROM transaction t
            JOIN `orders` o ON t.OrderID = o.OrderID
            ORDER BY t.PaymentDate DESC";
} else { 
    $delivery_id = intval($_SESSION['delivery_id']);
    $sql = "SELECT t.*, o.TotalAmount, o.OrderDate 
            FROM transaction t
            JOIN `orders` o ON t.OrderID = o.OrderID
            WHERE o.DeliveryID = $delivery_id
            ORDER BY t.PaymentDate DESC";
}

$result = $conn->query($sql);
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
    <?php if ($_SESSION['role'] === 'admin'): ?>
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
    <?php elseif ($_SESSION['role'] === 'deliveryperson'): ?>
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
    <!-- off-canvas menu start -->
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
            <div class="user-area position-relative d-flex align-items-center ms-3">
                <i id="user-btn-mobile" class="fas fa-user" style="cursor:pointer; font-size: 24px;"></i>
                <div id="account-box-mobile" class="account-box" style="display:none; position:absolute; right:0; top:100%;">
                    <strong><p>Hi <span><?php echo htmlspecialchars($_SESSION['user_username']); ?></span></p></strong>
                    <a href="logout.php" class="btn bellebeads-button secondary-btn rounded-0">Logout</a>
                </div>
            </div>
        </div>
    </aside>
    <?php endif; ?>

    <div class="breadcrumbs-area position-relative">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="breadcrumb-content position-relative section-content">
                        <h3 class="title-3">Manage Payments</h3>
                        <?php if ($_SESSION['role'] === 'deliveryperson'): ?>
                        <p>Showing only payments for orders assigned to you</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br><br>
    <div class="container">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Transaction ID</th>
                    <th>Order ID</th>
                    <th>Total Amount</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['TransactionID'] ?></td>
                    <td><?= $row['OrderID'] ?></td>
                    <td>$<?= number_format($row['TotalAmount'], 2) ?></td>
                    <td><?= $row['PaymentDate'] ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="transaction_id" value="<?= $row['TransactionID'] ?>">
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="Pending"<?= $row['PaymentStatus'] == 'Pending' ? ' selected' : '' ?>>Pending</option>
                                <option value="Paid"<?= $row['PaymentStatus'] == 'Paid' ? ' selected' : '' ?>>Paid</option>
                                <option value="Failed"<?= $row['PaymentStatus'] == 'Failed' ? ' selected' : '' ?>>Failed</option>
                                <option value="Refunded"<?= $row['PaymentStatus'] == 'Refunded' ? ' selected' : '' ?>>Refunded</option>
                            </select>
                    </td>
                    <td><?= $row['PaymentReference'] ?></td>
                    <td>
                            <button class="btn bellebeads-button secondary-btn rounded-0">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <style>
        .user-area {
            margin-left: auto !important;
            margin-right: 35px !important;
            padding-right: 0 !important;
            display: flex;
            align-items: center;
        }
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