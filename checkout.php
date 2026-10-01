<?php
session_start();
include 'conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registereduser') {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}


$userId = $_SESSION['user_id'];
$userData = $conn->query("SELECT * FROM registereduser WHERE UserId = $userId")->fetch_assoc();
$userEmail = $_SESSION['user_email'];
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

$defaultAddress = null;
$addressQuery = $conn->query("SELECT * FROM shippingaddress WHERE UserID = $userId AND IsDefault = 1 AND IsDeleted = 0 LIMIT 1");
if ($addressQuery->num_rows > 0) {
    $defaultAddress = $addressQuery->fetch_assoc();
    
    $nameParts = explode(' ', $defaultAddress['RecipientName'], 2);
    $defaultFirstName = $nameParts[0] ?? '';
    $defaultLastName = $nameParts[1] ?? '';
}

$notes = isset($_POST['notes']) ? $conn->real_escape_string(trim($_POST['notes'])) : null;

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['Price'] * $item['Quantity'];
}

if ($subtotal >= 30) {
    $shippingFee = 0;
    $shippingMessage = "✅ Free shipping";
} elseif ($subtotal >= 20) {
    $shippingFee = 3.5;
    $shippingMessage = "🚚 3.5 USD shipping fee";
} else {
    $shippingFee = 4;
    $shippingMessage = "📦 4 USD shipping fee";
}

$discount = 0;
$discountError = '';
$discountRequiresPoints = false;
$pointsRequired = 0;
$discountCodeId = null; 
$discountCodeUsed = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['discount_code'])) {
        $discountCode = strtoupper(trim($_POST['discount_code']));
        
        $codeCheck = $conn->query("SELECT * FROM discountCode WHERE Code = '$discountCode' AND IsActive = 1 AND (ExpiryDate IS NULL OR ExpiryDate >= NOW())");
        
        if ($codeCheck->num_rows > 0) {
            $codeData = $codeCheck->fetch_assoc();
            $discountCodeId = $codeData['DiscountCodeID']; 
            $discountCodeUsed = $codeData['Code'];
            
            if ($codeData['Code'] === 'REDEEM10') {
                if ($userData['points'] >= 1000) {
                    $discount = $subtotal * ($codeData['DiscountValue'] / 100);
                    $_SESSION['discount_applied'] = [
                        'code' => $codeData['Code'],
                        'points_deducted' => 1000,
                        'code_id' => $discountCodeId // Fixed variable name
                    ];
                } else {
                    $discountError = "You need at least 1000 points to redeem this discount";
                }
            } else {
                $discount = $subtotal * ($codeData['DiscountValue'] / 100);
                $_SESSION['discount_applied'] = [
                    'code' => $codeData['Code'],
                    'points_deducted' => 0,
                    'code_id' => $discountCodeId // Fixed variable name
                ];
            }
        } else {
            $discountError = "Invalid or expired discount code";
        }
    } elseif (isset($_POST['remove_discount'])) {
        $discount = 0;
        unset($_SESSION['discount_applied']);
        $discountCodeId = null; 
        $discountCodeUsed = '';
    }
}

$taxRate = 0.11; 
$taxableAmount = $subtotal - $discount;
$taxAmount = $taxableAmount * $taxRate;
$total1 = $taxableAmount + $taxAmount + $shippingFee;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $conn->begin_transaction();
    
    try {
        $recipientName = $conn->real_escape_string($_POST['first_name'] . ' ' . $_POST['last_name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        $city = $conn->real_escape_string($_POST['city']);
        $zipcode = $conn->real_escape_string($_POST['zipcode']);
        
        $existingAddressQuery = $conn->query("SELECT AddressID FROM shippingaddress WHERE UserID = $userId AND IsDefault = 1");
        
        if ($existingAddressQuery->num_rows > 0) {
            $addressId = $existingAddressQuery->fetch_assoc()['AddressID'];
            $addressSql = "UPDATE shippingaddress SET 
                          RecipientName = '$recipientName', 
                          Phone = '$phone', 
                          AddressLine = '$address', 
                          City = '$city', 
                          ZipCode = '$zipcode'
                          WHERE AddressID = $addressId";
            $conn->query($addressSql);
        } else {
            $addressSql = "INSERT INTO shippingaddress 
                          (UserID, RecipientName, Phone, AddressLine, City, ZipCode, Country, IsDefault)
                          VALUES ($userId, '$recipientName', '$phone', '$address', '$city', '$zipcode', 'Lebanon', 0)";
            $conn->query($addressSql);
            $addressId = $conn->insert_id;
        }

        $trackingCode = 'BB' . strtoupper(uniqid());

        $orderSql = "INSERT INTO `orders` 
                    (UserID, ShippingAddressID, DiscountCodeID, OrderDate, TotalAmount, TaxAmount, PaymentMethod, ShippingFee, notes, Status, TrackingCode)
                    VALUES ($userId, $addressId, " . ($discountCodeId ? $discountCodeId : 'NULL') . ", NOW(), $total1, $taxAmount, 'COD', $shippingFee," . ($notes ? "'$notes'" : "NULL") . " , 'Pending', '$trackingCode')";
        $conn->query($orderSql);
        $orderId = $conn->insert_id;

        $transactionSql = "INSERT INTO transaction 
                         (OrderID, PaymentStatus, PaymentDate, PaymentReference)
                         VALUES ($orderId, 'Pending', NOW(), 'COD-$orderId')";
        $conn->query($transactionSql);

       foreach ($_SESSION['cart'] as $productId => $item) {
    $quantity = (int)$item['Quantity'];
    $price = (float)$item['Price'];
    
    $conn->query("INSERT INTO orderitem (OrderID, ProductID, Quantity, UnitPrice) 
                 VALUES ($orderId, $productId, $quantity, $price)");
    
    $conn->query("UPDATE product SET Stock = Stock - $quantity WHERE ProductID = $productId");
}
        $pointsEarned = round($subtotal * 10); 
        
        if (isset($_SESSION['discount_applied'])) {
            if ($_SESSION['discount_applied']['points_deducted'] > 0) {
                $pointsDeducted = $_SESSION['discount_applied']['points_deducted'];
                $conn->query("UPDATE registereduser SET points = points - $pointsDeducted WHERE UserId = $userId");
                $conn->query("INSERT INTO pointstransaction 
                             (UserID, ChangeType, PointsChange, OrderID, Description)
                             VALUES ($userId, 'Redeemed', -$pointsDeducted, $orderId, 'Discount redemption for code {$_SESSION['discount_applied']['code']}')");
            }
            unset($_SESSION['discount_applied']);
        }
        
        $conn->query("UPDATE registereduser SET points = points + $pointsEarned WHERE UserId = $userId");
        $conn->query("INSERT INTO pointstransaction 
                     (UserID, ChangeType, PointsChange, OrderID, Description)
                     VALUES ($userId, 'Earned', $pointsEarned, $orderId, 'points from orders')");

        $conn->commit();

$confirmationHTML = '
<div style="
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
">
    <div class="order-confirmation" style="
        width: 800px;
        padding: 40px;
        background: #f8f9fa;
        border: 2px solid #a39157;
        border-radius: 10px;
        text-align: center;
        font-size: 1.2em;
    ">
        <h2 style="color:#a39157; font-size: 2em;">✅ Order Placed Successfully!</h2>
        <div style="margin: 30px 0;">
            <p><strong>Order ID:</strong> BB' . $orderId . '</p>
            <p><strong>Tracking Code:</strong> ' . $trackingCode . '</p>
            <p><strong>Total Paid:</strong> $' . number_format($total1, 2) . '</p>
        </div>
        <p>A detailed receipt has been sent to <strong>' . htmlspecialchars($userEmail) . '</strong></p>
        <a href="shop.php" class="btn" style="
            display: inline-block;
            padding: 12px 30px;
            background: #a39157;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 1em;
        ">Continue Shopping</a>
    </div>
</div>';

$cartItemsForEmail = $_SESSION['cart'];

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'rehamaboutaha2002@gmail.com'; 
    $mail->Password = 'xkejivtfwevzfbmx'; 
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('rehamaboutaha2002@gmail.com', 'Belle Beads');
    $mail->addAddress($userEmail);
    $mail->Subject = 'Your Belle Beads Order #BB'.$orderId;

    $mail->isHTML(true);
    $emailContent = '
    <h2 style="color:#a39157;">Thank You for Your Order!</h2>
    <p>Order #BB'.$orderId.' • '.date('F j, Y').'</p>
    <p>Tracking Code: <strong>'.$trackingCode.'</strong></p>
    
    <h3>Order Summary</h3>
    <table style="width:100%; border-collapse:collapse;">
        <tr style="background-color:#f5f5f5;">
            <th style="padding:10px;text-align:left;">Item</th>
            <th style="padding:10px;text-align:right;">Price</th>
            <th style="padding:10px;text-align:center;">Qty</th>
            <th style="padding:10px;text-align:right;">Subtotal</th>
        </tr>';
    
    foreach ($_SESSION['cart'] as $item) {
        $emailContent .= '
        <tr style="border-bottom:1px solid #eee;">
            <td style="padding:10px;">'.htmlspecialchars($item['Name']).'</td>
            <td style="padding:10px;text-align:right;">$'.number_format($item['Price'],2).'</td>
            <td style="padding:10px;text-align:center;">'.$item['Quantity'].'</td>
            <td style="padding:10px;text-align:right;">$'.number_format($item['Price']*$item['Quantity'],2).'</td>
        </tr>';
    }
    
    $emailContent .= '
        <tr>
            <td colspan="3" style="padding:10px;text-align:right;"><strong>Subtotal:</strong></td>
            <td style="padding:10px;text-align:right;">$'.number_format($subtotal,2).'</td>
        </tr>';
    
    if ($discount > 0) {
        $emailContent .= '
        <tr>
            <td colspan="3" style="padding:10px;text-align:right;"><strong>Discount:</strong></td>
            <td style="padding:10px;text-align:right;">-$'.number_format($discount,2).'</td>
        </tr>';
    }
    
    $emailContent .= '
        <tr>
            <td colspan="3" style="padding:10px;text-align:right;"><strong>Shipping:</strong></td>
            <td style="padding:10px;text-align:right;">$'.number_format($shippingFee,2).'</td>
        </tr>
        <tr>
            <td colspan="3" style="padding:10px;text-align:right;"><strong>Tax:</strong></td>
            <td style="padding:10px;text-align:right;">$'.number_format($taxAmount,2).'</td>
        </tr>
        <tr style="font-weight:bold;">
            <td colspan="3" style="padding:10px;text-align:right;">Total:</td>
            <td style="padding:10px;text-align:right;">$'.number_format($total1,2).'</td>
        </tr>
    </table>
    
    <h3>Shipping To</h3>
    <p>'.$recipientName.'<br>
    '.$address.'<br>
    '.$city.', '.$zipcode.'<br>
    Phone: '.$phone.'</p>
    
    <p>We will notify you when your order ships.</p>';
    
    $mail->Body = $emailContent;
    $mail->send();
    
} catch (Exception $e) {
    error_log("Email error: ".$e->getMessage());
    $confirmationHTML .= '<p style="color:#dc3545;">(Email receipt could not be sent)</p>';
}

// Clear cart and show confirmation
unset($_SESSION['cart']);
die($confirmationHTML);

} catch (Exception $e) {
    $conn->rollback();
    $error = "Error processing your order. Please try again. Error: " . $e->getMessage();
}}
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belle Beads - Checkout</title>
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
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <li><a href="userdashboard.php">My Account</a></li>
                                <?php else: ?>
                                    <li><a href="login.php">Login/Register</a></li>
                                <?php endif; ?>
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
                                <li><a href="shop.php">Shop</a></li>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <li><a href="userdashboard.php">My Account</a></li>
                                <?php else: ?>
                                    <li><a href="login.php">Login/Register</a></li>
                                <?php endif; ?>
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
                        <p class="desc-content">Welcome to Rama Abou Taha's website, where every product tells a unique story. Each handmade creation is crafted with love and passion, making every piece truly special <br>
                        <div class="top-info-wrap text-left text-black">
                            <ul class="address-info">
                                <li><i class="fa fa-phone"></i>
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
                    <!-- offcanvas widget area end -->
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
                        <h3 class="title-3">Checkout</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Area End Here -->
    
    <div class="checkout-area mt-no-text">
        <div class="container custom-container">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-12 col-custom">
                    <div class="coupon-accordion">
                        <h3>Have a coupon? <span id="showcoupon">Click here to enter your code</span></h3>
                        <div id="checkout_coupon" class="coupon-checkout-content">
                            <div class="coupon-info">
                                <form method="POST">
                                    <?php if(isset($_SESSION['discount_applied'])): ?>
                                        <p class="checkout-coupon">
                                            <input type="text" value="<?= $_SESSION['discount_applied']['code'] ?>" readonly>
                                            <button type="submit" name="remove_discount" class="coupon-inner_btn" style="background-color: #ff6b6b;">Remove</button>
                                        </p>
                                        <p class="text-success">Discount applied! -$<?= number_format($discount, 2) ?></p>
                                    <?php else: ?>
                                        <p class="checkout-coupon">
                                            <input placeholder="Enter REDEEM10" type="text" name="discount_code">
                                            <button type="submit" class="coupon-inner_btn">Apply</button>
                                        </p>
                                    <?php endif; ?>
                                    <?php if($discountError): ?>
                                        <p class="text-danger"><?= $discountError ?></p>
                                    <?php endif; ?>
                                    <p>You have <?= $userData['points'] ?> loyalty points (1000 points = 10% discount)</p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <form action="checkout.php" method="POST">
                <div class="row">
                    <div class="col-lg-6 col-12 col-custom">
                        <div class="checkbox-form">
                            <h3>Billing Details</h3>
                            <div class="row">
                                <div class="col-md-12 col-custom">
                                    <div class="country-select clearfix">
                                        <label>Country <span class="required">*</span></label>
                                        <select class="myniceselect nice-select wide rounded-0" name="country">
                                            <option data-display="Lebanon">Lebanon</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-custom">
                                    <div class="checkout-form-list">
                                        <label>First Name <span class="required">*</span></label>
                                        <input name="first_name" placeholder="" type="text" 
                                               value="<?= isset($defaultFirstName) ? htmlspecialchars($defaultFirstName) : '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-custom">
                                    <div class="checkout-form-list">
                                        <label>Last Name <span class="required">*</span></label>
                                        <input name="last_name" placeholder="" type="text" 
                                               value="<?= isset($defaultLastName) ? htmlspecialchars($defaultLastName) : '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-12 col-custom">
                                    <div class="checkout-form-list">
                                        <label>Phone Number<span class="required">*</span></label>
                                        <input name="phone" type="tel" placeholder="+961 70 123 456"
                                            pattern="^\+961\s?(3|70|71|76|78|79|81)\s?\d{3}\s?\d{3}$"
                                            title="Enter a valid Lebanese mobile number like +961 70 123 456" 
                                            value="<?= isset($defaultAddress['Phone']) ? htmlspecialchars($defaultAddress['Phone']) : '' ?>" required>
                                        <div id="phone-feedback" class="feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-custom">
                                    <div class="checkout-form-list">
                                        <label>Address <span class="required">*</span></label>
                                        <input name="address" placeholder="Street address" type="text" 
                                               value="<?= isset($defaultAddress['AddressLine']) ? htmlspecialchars($defaultAddress['AddressLine']) : '' ?>" required>
                                    </div>
                                </div>
                              
                                <div class="col-md-12 col-custom">
                                    <div class="checkout-form-list">
                                        <label>City <span class="required">*</span></label>
                                        <input name="city" type="text" 
                                               value="<?= isset($defaultAddress['City']) ? htmlspecialchars($defaultAddress['City']) : '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-custom">
                                    <div class="checkout-form-list">
                                        <label>ZipCode <span class="required">*</span></label>
                                        <input name="zipcode" placeholder="" type="text" 
                                               value="<?= isset($defaultAddress['ZipCode']) ? htmlspecialchars($defaultAddress['ZipCode']) : '' ?>" required>
                                    </div>
                                </div>
                              <div class="col-md-6 col-custom">
    <div class="checkout-form-list">
        <label>Email Address <span class="required">*</span></label>
        <input name="email" type="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
    </div>
</div>
                                <div class="col-md-12 col-custom">
                                    <div class="checkout-form-list">
                                        <label>
                                            <input type="radio" name="shipping" value="<?= $shippingFee ?>" checked>
                                            <?= $shippingMessage ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-custom">
                                    <div class="checkout-form-list">
                                        <label>
                                            <input type="radio" name="payment_method" value="COD" checked>
                                            Cash On Delivery (COD)
                                        </label>
                                    </div>
                                </div>
                                <div class="order-notes mt-3">
                                    <div class="checkout-form-list checkout-form-list-2">
                                        <label>Order Notes</label>
                                        <textarea name="notes" id="checkout-mess" cols="30" rows="10" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12 col-custom">
                        <div class="your-order">
                            <h3>Your order</h3>
                            <div class="your-order-table table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="cart-product-name">Product</th>
                                            <th class="cart-product-total">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                        <tr class="cart_item">
                                            <td class="cart-product-name">
                                                <?= htmlspecialchars($item['Name']) ?> <strong class="product-quantity">× <?= $item['Quantity'] ?></strong>
                                            </td>
                                            <td class="cart-product-total text-center">
                                                <span class="amount">$<?= number_format($item['Price'] * $item['Quantity'], 2) ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="cart-subtotal">
                                            <th>Subtotal</th>
                                            <td class="text-center">$<?= number_format($subtotal, 2) ?></td>
                                        </tr>
                                        <?php if($discount > 0): ?>
                                        <tr class="cart-discount">
                                            <th>Discount (<?= isset($_SESSION['discount_applied']) ? $_SESSION['discount_applied']['code'] : '' ?>)</th>
                                            <td class="text-center">-$<?= number_format($discount, 2) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="cart-shipping">
                                            <th>Shipping</th>
                                            <td class="text-center">$<?= number_format($shippingFee, 2) ?></td>
                                        </tr>
                                        <tr class="cart-tax">
                                            <th>Tax (<?= number_format($taxRate * 100, 2) ?>%)</th>
                                            <td class="text-center">$<?= number_format($taxAmount, 2) ?></td>
                                        </tr>
                                        <tr class="order-total">
                                            <th>Total</th>
                                            <td class="text-center"><strong>$<?= number_format($total1, 2) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="payment-method">
                                <div class="payment-accordion">
                                    <div id="accordion">
                                        <div class="card">
                                            <div class="card-header" id="#payment-1">
                                                <h5 class="panel-title mb-3">
                                                    <a href="#" class="" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                        Cash On Delivery
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseOne" class="collapse show" aria-labelledby="#payment-1" data-bs-parent="#accordion">
                                                <div class="card-body">
                                                    <p>Pay with cash upon delivery.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-button-payment">
                                    <button type="submit" name="place_order" class="btn bellebeads-button secondary-btn black-color rounded-0 w-100">Place Order</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
   <!--Footer Area Start-->
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
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li><a href="userdashboard.php">My Account</a></li>
                            <?php else: ?>
                                <li><a href="login.php">Login/Register</a></li>
                            <?php endif; ?>
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

<!-- Shipping Policy Modal -->
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

<!-- Refund Policy Modal -->
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

<style>.popup-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(3px);
    align-items: center;
    justify-content: center;
  }
  
  .popup-content {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
    animation: fadeIn 0.3s ease;
    text-align: left;
  }
  
  .popup-content h3 {
    margin-top: 0;
    color: #a39157;
  }
  
  .popup-content ul {
    padding-left: 20px;
    margin: 10px 0;
  }
  
  .popup-close {
    position: absolute;
    top: 8px; right: 12px;
    font-size: 22px;
    color: #8a7a4f;
    cursor: pointer;
  }
  
  .popup-close:hover {
    color: #000;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
  }
    .sidemenu-wrap .dropdown-sidemenu {
        width: 250px; 
    }
    
    .sidemenu-wrap .dropdown-sidemenu li {
        position: static; 
    }
    
    #live-search-results {
        position: absolute;
        width: 100%;
        z-index: 1001;
        display: none;
    }
    
    .search-results-container {
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        max-height: 300px;
        overflow-y: auto;
    }
    
    .search-result-item {
        padding: 8px 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        font-size: 13px;
    }
    
    .search-result-item:hover {
        background-color: #f9f9f9;
    }
    
    .search-result-price {
        color: #a39157;
        font-weight: bold;
    }
    
    .see-more-results {
        display: block;
        padding: 8px;
        text-align: center;
        background: #f5f5f5;
        color: #333;
        font-weight: bold;
    }
</style>
 

<script>
    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
    }
  
    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }
  
    // Close popup when clicking outside
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

    // Hide results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown-search').length) {
            $('#live-search-results').hide();
        }
    });

    // Keep results visible when hovering over them
    $('.dropdown-search').hover(function() {
        $('#live-search-results').show();
    }, function() {
        if (!$('#search-input').val()) {
            $('#live-search-results').hide();
        }
    });
});
// Phone number validation
document.querySelector('input[name="phone"]').addEventListener('input', function() {
    const phone = this.value.trim();
    const pattern = /^\+961\s?(3|70|71|76|78|79|81)\s?\d{3}\s?\d{3}$/;
    const feedback = document.getElementById('phone-feedback');

    if (!pattern.test(phone)) {
        feedback.textContent = 'Please enter a valid Lebanese number (e.g. +961 70 123 456)';
    } else {
        feedback.textContent = '';
    }
});
</script>
</body>
</html>