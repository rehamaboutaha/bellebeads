<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registereduser') {
    header("Location: login.php");
    exit;
}

$orderInfo = null;
$currentStatus = null;
$currentIndex = 0;
$isCanceled = false;
$trackingCode = '';
$errorMessage = '';
$trackingHistory = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['tracking_code'])) {
    $trackingCode = trim($_POST['tracking_code']);

    $orderQuery = "SELECT * FROM `orders` WHERE TrackingCode = ? AND UserID = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("si", $trackingCode, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

 if ($result && $result->num_rows > 0) {
    $orderInfo = $result->fetch_assoc();
    $orderID = $orderInfo['OrderID'];
    $statuses = ['Pending', 'Confirmed', 'Out for Delivery', 'Completed'];
    $currentStatus = $orderInfo['Status'];
    $isCanceled = ($currentStatus === 'Canceled');
    $currentIndex = $isCanceled ? 0 : array_search($currentStatus, $statuses);
    if ($currentIndex === false && !$isCanceled) {
        $currentIndex = 0;
    }

    $checkTracking = $conn->prepare("SELECT Status FROM `ordertracking` WHERE OrderID = ? ORDER BY Timestamp DESC LIMIT 1");
    $checkTracking->bind_param("i", $orderID);
    $checkTracking->execute();
    $checkResult = $checkTracking->get_result();
    $shouldInsert = false;
    
    if ($checkResult->num_rows === 0) {
        $shouldInsert = true;
        $message = "Tracking started. Current status: $currentStatus.";
    } else {
        $lastTracking = $checkResult->fetch_assoc();
        $lastStatus = $lastTracking['Status'];
        
        if ($lastStatus !== $currentStatus) {
            $shouldInsert = true;
            $message = "Status updated to: $currentStatus.";
        }
    }
    
    if ($shouldInsert) {
        $insert = $conn->prepare("INSERT INTO `ordertracking` (OrderID, Status, Message) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $orderID, $currentStatus, $message);
        $insert->execute();
        $insert->close();
    }

    $historyQuery = $conn->prepare("SELECT * FROM `ordertracking` WHERE OrderID = ? ORDER BY Timestamp DESC");
    $historyQuery->bind_param("i", $orderID);
    $historyQuery->execute();
    $historyResult = $historyQuery->get_result();
    while ($row = $historyResult->fetch_assoc()) {
        $trackingHistory[] = $row;
    }


    } else {
        $errorMessage = "Tracking code not found or does not belong to your account.";
    }
}
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT first_name, last_name, points FROM registereduser WHERE UserID = $user_id");
$user = mysqli_fetch_assoc($user_query);
$user_points = $user['points'] ?? 0;
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
    
    <style>
        .account-container {
            display: flex;
            padding: 40px;
            min-height: 60vh;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .sidebar {
            width: 250px;
            background: beige;
            padding: 20px;
            border: 1px solid #ddd;
            margin-right: 40px;
            height: fit-content;
        }
        
        .dashboard-content {
            flex: 1;
            max-width: calc(100% - 290px);
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar ul li {
            margin-bottom: 10px;
            background: #fff;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .sidebar ul li:hover {
            background: #f0f0f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        
        .sidebar ul li.active {
            background: rgb(196, 155, 102);
            border-color: #999;
            color: white;
        }
        
        .sidebar ul li a {
            display: block;
            text-decoration: none;
            color: inherit;
        }
        
        .track-form {
            text-align: center;
            margin: 30px 0;
        }
        
        .track-form input[type="text"] {
            padding: 10px 15px;
            width: 300px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .track-form input[type="submit"] {
            padding: 10px 25px;
            font-size: 16px;
            margin-left: 10px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .track-form input[type="submit"]:hover {
            background-color: #333;
        }
        
        .status-bar {
            display: flex;
            align-items: center;
            margin: 40px auto;
            justify-content: center;
            max-width: 800px;
        }
        
        .stage {
            text-align: center;
            flex: 1;
            position: relative;
        }
        
        .circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ccc;
            margin: 0 auto;
            line-height: 32px;
            color: white;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }
        
        .label {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
        }
        
        .active .circle {
            background: #000;
        }
        
        .active .label {
            color: #000;
            font-weight: bold;
        }
        
        .bar {
            height: 4px;
            flex: 1;
            background: #ccc;
            position: relative;
            top: 16px;
        }
        
        .bar.filled {
            background: #000;
        }
        
        .order-info {
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            max-width: 800px;
            background: #f9f9f9;
        }
        
        .order-info strong {
            display: inline-block;
            width: 120px;
            color: #333;
        }
        
        .tracking-history {
            width: 100%;
            max-width: 800px;
            margin: 30px auto;
            border-collapse: collapse;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .tracking-history th, 
        .tracking-history td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .tracking-history th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .tracking-history tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .error-message {
            color: #d9534f;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .canceled {
            color: #d9534f;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .popup-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(3px);
            align-items: center;
            justify-content: center;
        }
        
        .popup-content {
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
            animation: fadeIn 0.3s ease;
        }
        
        .popup-content h3 {
            margin-top: 0;
            color: #a39157;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .popup-content ul {
            padding-left: 20px;
            margin: 15px 0;
        }
        
        .popup-content ul li {
            margin-bottom: 8px;
        }
        
        .popup-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            color: #8a7a4f;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .popup-close:hover {
            color: #000;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .rewards-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .rewards-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .close-rewards {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .points-display {
            margin: 20px 0;
            font-size: 2.5rem;
            color: #a39157;
            font-weight: bold;
        }
        
        .points-value {
            font-size: 3rem;
        }
        
        .points-label {
            display: block;
            font-size: 1.2rem;
            margin-top: 5px;
        }
        
        .points-note {
            font-style: italic;
            color: #666;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .points-info {
            color: #a39157;
            font-weight: 500;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .account-container {
                flex-direction: column;
                padding: 20px;
            }
            
            .sidebar {
                width: 100%;
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .dashboard-content {
                max-width: 100%;
            }
            
            .track-form {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            
            .track-form input[type="text"] {
                width: 100%;
                max-width: 300px;
                margin-bottom: 10px;
            }
            
            .track-form input[type="submit"] {
                margin-left: 0;
            }
            
            .status-bar {
                flex-wrap: wrap;
                margin: 20px 0;
            }
            
            .stage {
                flex: 0 0 25%;
                margin-bottom: 20px;
            }
            
            .bar {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Header Area Start Here -->
    <header class="main-header-area">
        <!-- Main Header Area Start -->
        <div class="main-header header-sticky">
            <div class="container custom-area">
                <div class="row align-items-center">
                    <div class="col-lg-2 col-xl-2 col-md-6 col-6 col-custom">
                        <div class="header-logo d-flex align-items-center">
                            <a href="index.php">
                                <img class="img-full" src="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg" alt="Header Logo">
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-8 d-none d-lg-flex justify-content-center col-custom">
                        <nav class="main-nav d-none d-lg-flex">
                            <ul class="nav">
                                <li>
                                    <a href="index.php">
                                        <span class="menu-text"> Home Page</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="active" href="userdashboard.php">
                                        <span class="menu-text"> My Account</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="shop.php">
                                        <span class="menu-text">Shop</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="aboutus.php">
                                        <span class="menu-text"> About Us</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="contactus.php">
                                        <span class="menu-text">Contact Us</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="col-lg-2 col-md-6 col-6 col-custom">
                        <div class="header-right-area main-nav">
                            <ul class="nav">
                                <li class="minicart-wrap">
                                    <a href="cart.php" class="minicart-btn toolbar-btn">
                                        <i class="fa fa-shopping-cart"></i>
                                        <span class="cart-item_count">
                                            <?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'Quantity')) : 0 ?>
                                        </span>
                                    </a>

                                    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                                        <div class="cart-item-wrapper dropdown-sidemenu dropdown-hover-2">
                                            <?php $total = 0; ?>
                                            <?php $count = 0; ?>
                                            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                                <?php $total += $item['Price'] * $item['Quantity']; ?>
                                            <?php endforeach; ?>
                                            
                                            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                                <?php if ($count >= 3) break; ?>
                                                <?php $count++; ?>
                                                
                                                <div class="single-cart-item">
                                                    <div class="cart-img">
                                                        <a href="cart.php"><img src="<?= htmlspecialchars($item['ImageURL']) ?>" alt=""></a>
                                                    </div>
                                                    <div class="cart-text">
                                                        <h5 class="title" style="margin: 0 0 6px 0; font-size: 14px;">
                                                            <a href="cart.php" style="text-decoration: none; color: #000;">
                                                                <?= htmlspecialchars($item['Name']) ?>
                                                            </a>
                                                        </h5>
                                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                                            <div>
                                                                <span><?= $item['Quantity'] ?>× $<?= number_format($item['Price'], 2) ?></span>
                                                            </div>
                                                            <form action="cart.php" method="post" style="margin: 0;">
                                                                <input type="hidden" name="remove_id" value="<?= $id ?>">
                                                                <button type="submit" title="Remove Item" style="background: none; border: none; font-size: 16px; cursor: pointer;">🗑️</button>
                                                            </form>
                                                        </div>
                                                        <form action="cart.php" method="post" style="display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                                                            <input type="hidden" name="id" value="<?= $id ?>">
                                                            <button type="submit" name="quantity" value="decrease" style="padding: 2px 6px; font-size: 13px;">-</button>
                                                            <span style="font-size: 13px;"><?= $item['Quantity'] ?></span>
                                                            <button type="submit" name="quantity" value="increase" style="padding: 2px 6px; font-size: 13px;">+</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>

                                            <div class="cart-price-total d-flex justify-content-between">
                                                <h5>Total :</h5>
                                                <h5>$<?= number_format($total, 2) ?></h5>
                                            </div>
                                            <div class="cart-links d-flex justify-content-between">
                                                <a class="btn product-cart button-icon bellebeads-button dark-btn" href="cart.php">View cart</a>
                                                <a class="btn bellebeads-button secondary-btn rounded-0" href="checkout.php">Checkout</a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </li>
                                <li class="sidemenu-wrap">
                                    <a href="#"><i class="fa fa-search"></i> </a>
                                    <ul class="dropdown-sidemenu dropdown-hover-2 dropdown-search">
                                        <li>
                                            <form action="shop.php" method="get" id="search-form">
                                                <input name="search" id="search-input" placeholder="Search" type="text" autocomplete="off">
                                                <button type="submit"><i class="fa fa-search"></i></button>
                                            </form>
                                            <div id="live-search-results" style="display: none;">
                                                <div class="search-results-container">
                                                    <div id="search-results-list"></div>
                                                    <a href="shop.php" class="see-more-results">See More Results</a>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="account-menu-wrap d-none d-lg-flex">
                                    <a href="#" class="off-canvas-menu-btn">
                                        <i class="fa fa-bars"></i>
                                    </a>
                                </li>
                                <li class="mobile-menu-btn d-lg-none">
                                    <a class="off-canvas-btn" href="#">
                                        <i class="fa fa-bars"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Header Area End -->
        <!-- off-canvas menu start -->
        <aside class="off-canvas-wrapper" id="mobileMenu">
            <div class="off-canvas-overlay"></div>
            <div class="off-canvas-inner-content">
                <div class="btn-close-off-canvas">
                    <i class="fa fa-times"></i>
                </div>
                <div class="off-canvas-inner">
                    <div class="search-box-offcanvas">
                        <form>
                            <input type="text" placeholder="Search product...">
                            <button class="search-btn"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                    <!-- mobile menu start -->
                    <div class="mobile-navigation">
                        <!-- mobile menu navigation start -->
                        <nav class="main-nav d-none d-lg-flex">
                            <ul class="nav">
                                <li>
                                    <a href="index.php">
                                        <span class="menu-text"> Home Page</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="active" href="userdashboard.php">
                                        <span class="menu-text"> My Account</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="shop.php">
                                        <span class="menu-text">Shop</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="aboutus.php">
                                        <span class="menu-text"> About Us</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="contactus.php">
                                        <span class="menu-text">Contact Us</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <!-- mobile menu navigation end -->
                    </div>
                    <!-- mobile menu end -->
                    <div class="offcanvas-widget-area">
                        <div class="top-info-wrap text-left text-black">
                            <ul class="address-info">
                                <li>
                                    <i class="fa fa-phone"></i>
                                    <a href="https://wa.me/96103563201">(+961) 3563201</a>
                                </li>
                                <li>
                                    <i class="fa fa-envelope"></i>
                                    Email: <a href="mailto:ramaaboutaha@gmail.com">ramaaboutaha@gmail.com</a>
                                </li>
                            </ul>
                            <div class="widget-social">
                                <a class="facebook-color-bg" title="Facebook-f" href="#"><i class="fa fa-facebook-f"></i></a>
                                <a class="whatsapp-color-bg" title="whatsapp" href="https://wa.me/96103563201"><i class="fa fa-whatsapp"></i></a>
                                <a class="instagram-color-bg" title="Instagram" href="https://www.instagram.com/belle_beads.lb?igsh=MWRhbWQ4cnJ1azFtYw=="><i class="fa fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
  
        <aside class="off-canvas-menu-wrapper" id="sideMenu">
            <div class="off-canvas-overlay"></div>
            <div class="off-canvas-inner-content">
                <div class="off-canvas-inner">
                    <div class="btn-close-off-canvas">
                        <i class="fa fa-times"></i>
                    </div>
                    <div class="offcanvas-widget-area">
                        <ul class="menu-top-menu">
                            <li><a href="aboutus.php">About Us</a></li>
                        </ul>
                        <p class="desc-content">Welcome to Rama Abou Taha's website, where every product tells a unique story. Each handmade creation is crafted with love and passion, making every piece truly special.</p>
                        <div class="top-info-wrap text-left text-black">
                            <ul class="address-info">
                                <li>
                                    <i class="fa fa-phone"></i>
                                    <a href="https://wa.me/96103563201">(+961) 3563201</a>
                                </li>
                                <li>
                                    <i class="fa fa-envelope"></i>
                                    Email: <a href="mailto:ramaaboutaha@gmail.com">ramaaboutaha@gmail.com</a>
                                </li>
                            </ul>
                            <div class="widget-social">
                                <a class="facebook-color-bg" title="Facebook-f" href="#"><i class="fa fa-facebook-f"></i></a>
                                <a class="whatsapp-color-bg" title="whatsapp" href="https://wa.me/96103563201"><i class="fa fa-whatsapp"></i></a>
                                <a class="instagram-color-bg" title="Instagram" href="https://www.instagram.com/belle_beads.lb?igsh=MWRhbWQ4cnJ1azFtYw=="><i class="fa fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        <!-- off-canvas menu end -->
    </header>
    <!-- Header Area End Here -->
    
    <!-- Breadcrumb Area Start Here -->
    <div class="breadcrumbs-area position-relative">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="breadcrumb-content position-relative section-content">
                        <h3 class="title-3">Tracking Orders</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="account-container">
        <aside class="sidebar">
            <ul>
                <li><a href="userdashboard.php">Dashboard</a></li>
                <li class="active"><a href="track.php">Track My Order</a></li>
                <li><a href="javascript:void(0)" onclick="showRewardsPopup()">Rewards</a></li>
                <li><a href="addresses.php">Addresses</a></li>
                <li><a href="favorite.php">Favorite</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>
        
        <div class="dashboard-content">
            <div class="track-form">
                <form method="post" action="">
                    <input type="text" name="tracking_code" placeholder="Enter your Tracking Code" value="<?= htmlspecialchars($trackingCode) ?>" required>
                    <input type="submit" value="Track">
                </form>
            </div>

            <?php if ($errorMessage): ?>
                <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
            <?php endif; ?>


               

            <?php if ($orderInfo): ?>
                 <div class="order-info">
                    <strong>Order Code:</strong> <?= htmlspecialchars($orderInfo['OrderID']) ?><br>
                    <strong>Status:</strong> <?= htmlspecialchars($orderInfo['Status']) ?><br>
                    <strong>Tracking Code:</strong> <?= htmlspecialchars($orderInfo['TrackingCode']) ?><br>
                    <strong>Shipping Via:</strong> Wakilni<br>
                    <strong>Order Date:</strong> <?= htmlspecialchars($orderInfo['OrderDate']) ?><br>
                    <strong>Total:</strong> $<?= number_format($orderInfo['TotalAmount'], 2) ?>
                </div>

                <div class="status-bar">
                    <?php 
                    $statuses = ['Pending', 'Confirmed', 'Out for Delivery', 'Completed'];
                    foreach ($statuses as $i => $status): ?>
                        <div class="<?= $i <= $currentIndex ? 'stage active' : 'stage' ?>">
                            <div class="circle"><?= $i + 1 ?></div>
                            <div class="label"><?= htmlspecialchars($status) ?></div>
                        </div>
                        <?php if ($i < count($statuses) - 1): ?>
                            <div class="bar <?= $i < $currentIndex ? 'filled' : '' ?>"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
        

                <?php if ($isCanceled): ?>
                    <div class="canceled">This order has been canceled.</div>
                <?php endif; ?>
                
                <?php if (!empty($trackingHistory)): ?>
                    <table class="tracking-history">
                  <tr>
                  <th colspan="3">
             <h3 style="text-align: center; margin: 20px 0 20px; height:15px">Tracking History</h3>
            </th>
                   </tr>
                        <tr>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Timestamp</th>
                        </tr>
                        <?php foreach ($trackingHistory as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['Status']) ?></td>
                                <td><?= htmlspecialchars($entry['Message']) ?></td>
                                <td><?= htmlspecialchars($entry['Timestamp']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer-area mt-no-text">
        <div class="footer-widget-area">
            <div class="container container-default custom-area">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-custom">
                        <div class="single-footer-widget m-0">
                            <div class="footer-logo">
                                <a href="index.php">
                                    <img src="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg" height="120" width="150" alt="Logo Image">
                                </a>
                            </div>
                            <div class="social-links">
                                <ul class="d-flex">
                                    <li>
                                        <a class="rounded-circle" href="#" title="Facebook">
                                            <i class="fa fa-facebook-f"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="rounded-circle" href="https://wa.me/96103563201" title="whatsapp">
                                            <i class="fa fa-whatsapp"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="rounded-circle" href="https://www.instagram.com/belle_beads.lb?igsh=MWRhbWQ4cnJ1azFtYw==" title="Instagram">
                                            <i class="fa fa-instagram"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-2 col-custom">
                        <div class="single-footer-widget">
                            <br><br>
                            <h2 class="widget-title">Quicklink</h2>
                            <ul class="widget-list">
                                <li><a href="userdashboard.php">My Account</a></li>
                                <li><a href="shop.php">Shop</a></li>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <li><a href="cart.php">Cart</a></li>
                                <?php else: ?>
                                    <li><a href="#" onclick="alert('Please login to view your cart.')">Cart</a></li>
                                <?php endif; ?>
                                <li><a href="contactus.php">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-2 col-custom">
                        <div class="single-footer-widget">
                            <br><br>
                            <h2 class="widget-title">Support</h2>
                            <ul class="widget-list">
                                <li><a href="contactus.php">Online Support</a></li>
                                <li><a href="javascript:void(0)" onclick="openModal('shipping-popup')">Shipping Policy</a></li>
                                <li><a href="javascript:void(0)" onclick="openModal('refund-popup')">Refund Policy</a></li>
                                <li><a href="contact-us.html">Terms of Service</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-3 col-custom">
                        <div class="single-footer-widget">
                            <br><br>
                            <h2 class="widget-title">See Information</h2>
                            <div class="widget-body">
                                <address>
                                    Al Manara, West Bekaa, Lebanon.<br>
                                    Phone: <a href="tel:+96103563201">(+961) 03 563 201</a><br>
                                    Email: <a href="mailto:ramaaboutaha@gmail.com">ramaaboutaha@gmail.com</a>
                                </address>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-copyright-area">
            <div class="container custom-area">
                <div class="row">
                    <div class="col-12 text-center col-custom">
                        <div class="copyright-content">
                            <p>Copyright © 2025 <strong>Belle Beads</strong> All Rights Reserved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

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


<div id="shipping-popup" class="popup-modal">
    <div class="popup-content">
      <span class="popup-close" onclick="closeModal('shipping-popup')">&times;</span>
      <h3>Shipping Policy</h3>
      <ul>
        <li>✅ Free shipping for orders over 30 USD</li>
        <li>🚚 3.5 USD shipping fee for orders between 20–29.99 USD</li>
        <li>📦 4 USD shipping fee for orders below 20 USD</li>
      </ul>
    </div>
  </div>
  
<!-- Refund Modal -->
<div id="refund-popup" class="popup-modal">
<div class="popup-content">
<span class="popup-close" onclick="closeModal('refund-popup')">&times;</span>
<h3>Refund & Exchange Policy</h3>
<ul>
<li><strong>Exchange:</strong> Must be claimed within 12 hours of reception.</li>
<li>4 USD delivery charge for exchanges.</li>
<li>If the item is wrong, exchange is <strong>FREE</strong>.</li>
</ul>
</div>
</div>

<script>
    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
    }   
  
    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }
  
    window.onclick = function(event) {
      const popup = document.getElementById('shipping-popup');
      if (event.target === popup) {
        popup.style.display = "none";
      }
    };
  </script>
  <script>
$(document).ready(function() {
    $('#search-input').on('input', function() {
        var query = $(this).val();
        if (query.length > 2) {
            $.ajax({
                url: 'search_live.php',
                method: 'GET',
                data: { search: query },
                success: function(response) {
                    if (response) {
                        $('#search-results-list').html(response);
                        $('#live-search-results').show();
                    } else {
                        $('#live-search-results').hide();
                    }
                }
            });
        } else {
            $('#live-search-results').hide();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown-search').length) {
            $('#live-search-results').hide();
        }
    });

    $('.dropdown-search').hover(function() {
        $('#live-search-results').show();
    }, function() {
        if (!$('#search-input').val()) {
            $('#live-search-results').hide();
        }
    });
});
</script>
<script>
function showRewardsPopup() {
    const points = <?php echo $user_points; ?>;
    const popup = document.createElement('div');
    popup.className = 'rewards-popup';
    popup.innerHTML = `
        <div class="rewards-content">
            <span class="close-rewards" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <h3>Your Rewards</h3>
            <div class="points-display">
                <span class="points-value">${points}</span>
                <span class="points-label">Points</span>
            </div>
            <p class="points-note">Every $1 spent = 10 points</p>
            <p class="points-info">Redeem your points at checkout!</p>
        </div>
    `;
    document.body.appendChild(popup);
}

document.addEventListener('DOMContentLoaded', function() {
    const rewardsLink = document.querySelector('a[href="rewards.php"]');
    if (rewardsLink) {
        rewardsLink.addEventListener('click', function(e) {
            e.preventDefault();
            showRewardsPopup();
        });
    }
});
</script>

</body>


</html>



















