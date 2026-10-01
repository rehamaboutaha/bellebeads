<?php
session_start();
if (!isset($_SESSION['role']) ){
    header("Location: login.php");
    exit();
}

include 'conn.php';
if ($_SESSION['role'] === 'admin') {
    $delivery_stmt = $conn->prepare("SELECT DeliveryID, FullName FROM deliveryperson");
    $delivery_stmt->execute();
    $delivery_persons = $delivery_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $delivery_stmt->close();
}

if (isset($_SESSION['order_updated'])) {
    echo "<script>alert('Order updated successfully!');</script>";
    unset($_SESSION['order_updated']);
}

if (isset($_SESSION['order_deleted'])) {
    echo "<script>alert('Order deleted successfully!');</script>";
    unset($_SESSION['order_deleted']);
}

if (isset($_POST['update_order'], $_POST['order_id']) && $_SESSION['role'] === 'admin') {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'] ?? null;
    $delivery_id = !empty($_POST['delivery_id']) ? intval($_POST['delivery_id']) : null;

    if ($status === 'Completed') {
        $payment_check = $conn->query("SELECT PaymentStatus FROM transaction WHERE OrderID = $order_id");
        if ($payment_check->num_rows > 0) {
            $payment = $payment_check->fetch_assoc();
            if ($payment['PaymentStatus'] != 'Paid') {
                die("<script>alert('Cannot complete order: Payment not received'); history.back();</script>");
            }
        } else {
            die("<script>alert('Cannot complete order: No payment record found'); history.back();</script>");
        }
    }

    if ($conn->query("UPDATE orders SET Status = '$status', DeliveryID = $delivery_id WHERE OrderID = $order_id")) {
        $_SESSION['order_updated'] = true;
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        die("<script>alert('Error updating order'); history.back();</script>");
    }
}
    
    if (isset($_POST['delete_order'], $_POST['order_id']) && $_SESSION['role'] === 'admin') {
        $order_id = intval($_POST['order_id']);

        $stmt = $conn->prepare("SELECT Status FROM orders WHERE OrderID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        if ($order && ($order['Status'] == 'Pending' || $order['Status'] == 'Confirmed')) {
            $stmt1 = $conn->prepare("DELETE FROM pointstransaction WHERE OrderID = ?");
            $stmt1->bind_param("i", $order_id);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $conn->prepare("DELETE FROM orderitem WHERE OrderID = ?");
            $stmt2->bind_param("i", $order_id);
            $stmt2->execute();
            $stmt2->close();

            $stmt4 = $conn->prepare("DELETE FROM transaction WHERE OrderID = ?");
            $stmt4->bind_param("i", $order_id);
            $stmt4->execute();
            $stmt4->close();

            $stmt3 = $conn->prepare("DELETE FROM orders WHERE OrderID = ?");
            $stmt3->bind_param("i", $order_id);
            if ($stmt3->execute()) {
                $_SESSION['order_deleted'] = true;
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }
            $stmt3->close();
        } else {
            echo "<script>alert('Only Pending or Confirmed orders can be deleted.');</script>";
        }
    }


if ($_SESSION['role'] === 'admin') {
    $stmt = $conn->prepare("SELECT o.*, d.FullName as DeliveryPersonName 
                            FROM orders o 
                            LEFT JOIN deliveryperson d ON o.DeliveryID = d.DeliveryID 
                            ORDER BY OrderID DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} 
elseif ($_SESSION['role'] === 'deliveryperson') {
    $delivery_id = intval($_SESSION['delivery_id']);
    $stmt = $conn->prepare("SELECT o.*, d.FullName as DeliveryPersonName 
                            FROM orders o 
                            LEFT JOIN deliveryperson d ON o.DeliveryID = d.DeliveryID 
                            WHERE o.DeliveryID = ?
                            ORDER BY OrderID DESC");
    $stmt->bind_param("i", $delivery_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
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
                            <a href="logout.php" class="btn bellebeads-button secondary-btn rounded-0">Logout</a>
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
                            <a href="logout.php" class="btn bellebeads-button secondary-btn rounded-0">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Mobile Menu -->
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
                        <h3 class="title-3">Manage Orders</h3>
                        <?php if ($_SESSION['role'] === 'deliveryperson'): ?>
                        <p>Showing only orders assigned to you</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <table class="table table-bordered" style="width: 1200px;">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Status</th>
                    <th>Shipping Address ID</th>
                    <th>Discount Code ID</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <th>Delivery Person</th>
                    <?php endif; ?>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Shipping Fee</th>
                    <th>Tracking Code</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['OrderID'] ?></td>
                        <td><?= $row['UserID'] ?></td>
                        <td>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="order_id" value="<?= $row['OrderID'] ?>">
                                <select name="status" class="form-control form-control-sm">
                                    <option <?= $row['Status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option <?= $row['Status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option <?= $row['Status'] == 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                    <option <?= $row['Status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option <?= $row['Status'] == 'Canceled' ? 'selected' : '' ?>>Canceled</option>
                                </select>
                            <?php else: ?>
                                <?= $row['Status'] ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['ShippingAddressID'] ?></td>
                        <td><?= $row['DiscountCodeID'] ?? '—' ?></td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <td>
                            <select name="delivery_id" class="form-control form-control-sm">
                                <option value="">— Select Delivery —</option>
                                <?php foreach ($delivery_persons as $delivery): ?>
                                    <option value="<?= $delivery['DeliveryID'] ?>" <?= $row['DeliveryID'] == $delivery['DeliveryID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($delivery['FullName'] . ' (' . $delivery['DeliveryID'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <?php endif; ?>
                        <td><?= $row['OrderDate'] ?></td>
                        <td>$<?= number_format($row['TotalAmount'], 2) ?></td>
                        <td><?= $row['PaymentMethod'] ?></td>
                        <td>$<?= number_format($row['ShippingFee'], 2) ?></td>
                        <td><?= $row['TrackingCode'] ?? '—' ?></td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="submit" name="update_order" class="btn bellebeads-button secondary-btn rounded-0">Update</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?= $row['OrderID'] ?>">
                                    <?php if ($row['Status'] == 'Pending' || $row['Status'] == 'Confirmed'): ?>
                                        <button type="submit" name="delete_order" class="btn bellebeads-button secondary-btn rounded-0" onclick="return confirm('Are you sure?')">Delete</button>
                                    <?php else: ?>
                                        <button type="button" class="btn bellebeads-button secondary-btn rounded-0" disabled title="Can only delete Pending/Confirmed orders">Delete</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
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