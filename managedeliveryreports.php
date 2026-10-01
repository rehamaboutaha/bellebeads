<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$allowed_roles = ['admin', 'deliveryperson'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

if (!file_exists('delivery_proofs')) {
    mkdir('delivery_proofs', 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_report'])) {
        $orderID = intval($_POST['order_id']);
        $description = trim($_POST['description']);
        $deliveryID = intval($_SESSION['delivery_id']);
        $proofImageURL = '';

        $orderCheck = $conn->prepare("SELECT OrderID FROM orders WHERE OrderID = ? AND DeliveryID = ?");
        $orderCheck->bind_param("ii", $orderID, $deliveryID);
        $orderCheck->execute();
        $orderResult = $orderCheck->get_result();
        
        if ($orderResult->num_rows === 0) {
            $_SESSION['error'] = 'You can only create reports for orders assigned to you.';
        } else {
            if (isset($_FILES['proof_image'])) {
                $file = $_FILES['proof_image'];
                
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($file['tmp_name']);
                    
                    if (in_array($fileType, $allowedTypes)) {
                        if ($file['size'] <= 5 * 1024 * 1024) {
                            $fileName = time() . '_' . preg_replace("/[^A-Za-z0-9\.\-_]/", '_', basename($file['name']));
                            $destPath = 'delivery_proofs/' . $fileName;

                            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                                $proofImageURL = $conn->real_escape_string($destPath);
                            } else {
                                $_SESSION['error'] = 'Error uploading file.';
                            }
                        } else {
                            $_SESSION['error'] = 'Image size must be less than 5MB.';
                        }
                    } else {
                        $_SESSION['error'] = 'Only JPG, PNG, and GIF images are allowed.';
                    }
                } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $_SESSION['error'] = 'File upload error: ' . $file['error'];
                }
            }

            if (!isset($_SESSION['error'])) {
                $stmt = $conn->prepare("INSERT INTO deliveryreport (OrderID, DeliveryID, Description, ProofImageURL, Status) VALUES (?, ?, ?, ?, 'Pending')");
                $stmt->bind_param("iiss", $orderID, $deliveryID, $description, $proofImageURL);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Report submitted successfully!';
                } else {
                    $_SESSION['error'] = 'Error submitting report: ' . $conn->error;
                }
            }
        }
    }
    elseif (isset($_POST['resolve_report'])) {
        $reportID = intval($_POST['report_id']);
        $deliveryID = intval($_SESSION['delivery_id']);
        
        $verifyStmt = $conn->prepare("SELECT ReportID FROM deliveryreport WHERE ReportID = ? AND DeliveryID = ? AND Status = 'Pending'");
        $verifyStmt->bind_param("ii", $reportID, $deliveryID);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        
        if ($verifyResult->num_rows === 0) {
            $_SESSION['error'] = 'You can only resolve your own pending reports.';
        } else {
            $paymentCheck = $conn->prepare("SELECT t.PaymentStatus FROM deliveryreport r JOIN orders o ON r.OrderID = o.OrderID JOIN transaction t ON o.OrderID = t.OrderID WHERE r.ReportID = ?");
            $paymentCheck->bind_param("i", $reportID);
            $paymentCheck->execute();
            $paymentResult = $paymentCheck->get_result();
            $paymentStatus = $paymentResult->fetch_assoc();
            
            if ($paymentStatus && $paymentStatus['PaymentStatus'] === 'Paid') {
                $updateReport = $conn->prepare("UPDATE deliveryreport SET Status = 'Resolved' WHERE ReportID = ?");
                $updateReport->bind_param("i", $reportID);
                $updateReport->execute();

                $getOrder = $conn->prepare("SELECT OrderID FROM deliveryreport WHERE ReportID = ?");
                $getOrder->bind_param("i", $reportID);
                $getOrder->execute();
                $orderResult = $getOrder->get_result();
                $orderRow = $orderResult->fetch_assoc();

                if ($orderRow) {
                    $orderID = $orderRow['OrderID'];
                    $updateOrder = $conn->prepare("UPDATE orders SET Status = 'Completed' WHERE OrderID = ?");
                    $updateOrder->bind_param("i", $orderID);
                    $updateOrder->execute();
                }
                $_SESSION['success'] = 'Report resolved successfully and order marked as completed!';
            } else {
                $_SESSION['error'] = 'Cannot resolve report - payment status is not completed.';
            }
        }
    }
    
    if (isset($_POST['submit_report']) || isset($_POST['resolve_report'])) {
        header("Location: managedeliveryreports.php");
        exit();
    }
}
if ($_SESSION['role'] === 'admin') {
    $sql = "SELECT r.*, o.OrderDate, o.Status AS OrderStatus, d.FullName AS DeliveryFullName
            FROM deliveryreport r
            JOIN orders o ON r.OrderID = o.OrderID
            JOIN deliveryperson d ON r.DeliveryID = d.DeliveryID
            ORDER BY r.ReportDate DESC";
} else {
    $deliveryID = intval($_SESSION['delivery_id']);
    $sql = "SELECT r.*, o.OrderDate, o.Status AS OrderStatus, d.FullName AS DeliveryFullName
            FROM deliveryreport r
            JOIN orders o ON r.OrderID = o.OrderID
            JOIN deliveryperson d ON r.DeliveryID = d.DeliveryID
            WHERE r.DeliveryID = $deliveryID
            ORDER BY r.ReportDate DESC";
}
$result = $conn->query($sql);

if ($_SESSION['role'] === 'deliveryperson') {
    $deliveryID = intval($_SESSION['delivery_id']);
    $assignedOrders = $conn->query("SELECT o.OrderID FROM orders o WHERE o.DeliveryID = $deliveryID AND o.Status = 'Out for Delivery'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belle Beads - Delivery Reports</title>
    <link rel="shortcut icon" type="image/x-icon" href="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Reham/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="Reham/css/vendor/font.awesome.min.css">
    <link rel="stylesheet" href="Reham/css/style.css">
    <style>
        .user-area { margin-left: auto !important; margin-right: 35px !important; padding-right: 0 !important; display: flex; align-items: center; }
        #user-btn { font-size: 30px; cursor: pointer; }
        .account-box { position: absolute; top: 130%; right: 0; background: #fff; border: 1px solid #ccc; padding: 10px 15px; width: 400px; height:170px; display: none; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 999; }
        .account-box.active { display: block; }
        .account-box p, .account-box a, .account-box div { font-size: 15px; margin: 6px 0; }
        .table-responsive { overflow-x: auto; }
        @media (max-width: 768px) { .table td, .table th { padding: 0.5rem; font-size: 0.875rem; } }
        body { color: #000; }
        .table th, .table td { vertical-align: middle; }
        .btn.bellebeads-button { background-color: rgb(0, 12, 24); color: white; border: 1px solid #000; border-radius: 0 !important; transition: all 0.3s; }
        .btn.bellebeads-button:hover { background-color: #000; color: #fff; }
        .badge.bg-warning { background-color: rgb(204, 172, 142) !important; color: #000; }
        .badge.bg-success { background-color: #28a745 !important; color: white !important; }
        .proof-image { max-width: 100px; max-height: 100px; }
        td { color: black; }
        .order-status { font-weight: bold; }
        .status-pending { color: #ffc107; }
        .status-confirmed { color: #17a2b8; }
        .status-out { color: #fd7e14; }
        .status-completed { color: #28a745; }
        .status-canceled { color: #dc3545; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .alert-danger { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
    </style>
</head>
<body>
    <!-- Header Area -->
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
     <div class="breadcrumbs-area position-relative">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="breadcrumb-content position-relative section-content">
                        <h3 class="title-3"> Delivery Reports</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    <div class="breadcrumbs-area position-relative">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="breadcrumb-content position-relative section-content">
                        <h3 class="title-3">Manage Delivery Reports</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    

    <div class="container mt-4 mb-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'deliveryperson'): ?>
        <div class="card mb-4">
            <div class="card-header" style="background-color: #000; color: #fff;">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Report</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Order Number</label>
                            <select name="order_id" class="form-select" required>
                                <option value="">Select Order</option>
                                <?php while ($order = $assignedOrders->fetch_assoc()): ?>
                                    <option value="<?= $order['OrderID'] ?>">Order #<?= $order['OrderID'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Proof Photo</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*">
                            <small class="text-muted">Max 5MB (JPG, PNG, GIF)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3" required placeholder="Describe the delivery issue..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="submit_report" class="btn bellebeads-button secondary-btn rounded-0 py-2">
                                <i class="fas fa-paper-plane me-2"></i>Submit Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Report ID</th>
                        <th>Order ID</th>
                        <th>Order Status</th>
                        <th>Delivery Person</th>
                        <th>Description</th>
                        <th>Proof</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ReportID'] ?></td>
                        <td>#<?= $row['OrderID'] ?></td>
                        <td>
                            <span class="order-status status-<?= strtolower(str_replace(' ', '-', $row['OrderStatus'])) ?>">
                                <?= $row['OrderStatus'] ?>
                            </span>
                        </td>
                        <td><?= $row['DeliveryFullName'] ?></td>
                        <td><?= nl2br(htmlspecialchars($row['Description'])) ?></td>
                        <td>
                            <?php if (!empty($row['ProofImageURL'])): ?>
                                <a href="<?= $row['ProofImageURL'] ?>" target="_blank" class="btn bellebeads-button btn-sm">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            <?php else: ?>
                                <span class="badge bg-secondary">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y h:i A', strtotime($row['ReportDate'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['Status'] === 'Pending' ? 'warning' : 'success' ?>">
                                <?= $row['Status'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($_SESSION['role'] === 'deliveryperson' && $row['Status'] === 'Pending'): ?>
                                <form method="POST" onsubmit="return confirm('Mark this report as resolved?')">
                                    <input type="hidden" name="report_id" value="<?= $row['ReportID'] ?>">
                                    <button type="submit" name="resolve_report" class="btn bellebeads-button btn-sm">
                                        <i class="fas fa-check-circle me-1"></i> Resolve
                                    </button>
                                </form>
                            <?php elseif ($row['Status'] === 'Resolved'): ?>
                                <span class="text-success fw-bold">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="Reham/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="Reham/js/vendor/bootstrap.bundle.min.js"></script>
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

    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    </script>
</body>
</html>