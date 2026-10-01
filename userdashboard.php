<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'registereduser' || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$user_query = mysqli_query($conn, "SELECT first_name, last_name FROM registereduser WHERE UserID = $user_id");
$user = mysqli_fetch_assoc($user_query);

$order_sql = "
SELECT 
    o.OrderID, o.OrderDate, o.TotalAmount, o.Status AS FulfillmentStatus, o.PaymentMethod AS PaymentStatus,o.TrackingCode,
    a.RecipientName, a.Phone, a.AddressLine, a.City, a.ZipCode, a.Country
FROM `orders` o
JOIN shippingaddress a ON o.ShippingAddressID = a.AddressID
WHERE o.UserID = $user_id
ORDER BY o.OrderDate DESC
";

$address_sql = "SELECT * FROM shippingaddress WHERE UserID = $user_id AND IsDefault = 1 LIMIT 1";
$address_result = mysqli_query($conn, $address_sql);
$default_address = mysqli_fetch_assoc($address_result); 

$order_result = mysqli_query($conn, $order_sql);
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
                                <img class="img-full" src="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg"
                                    alt="Header Logo">
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-8 d-none d-lg-flex justify-content-center col-custom">
                        <nav class="main-nav d-none d-lg-flex">
                            <ul class="nav">
                                <li>
                                    <a  href="index.php">
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
                                    <a class href="contactus.php">
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
                                    <a  href="index.php">
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
                                        <a  class="active" href="contactus.php">
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
                        <h3 class="title-3">My Account</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

 <div class="account-container">
    <aside class="sidebar">
     <ul>
                <li class="active"><a href="userdashboard.php">Dashboard</a></li>
                <li><a href="track.php">Track My Order</a></li>
                <li><a href="javascript:void(0)" onclick="showRewardsPopup()">Rewards</a></li>
                <li><a href="addresses.php">Addresses</a></li>
                <li><a href="favorite.php">Favorite</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
</aside>


    <section class="dashboard-content">
       <div class="hello"> <p>Hello <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> (not you? <a href="logout.php">Log out</a>)</p>
</div>
        <h3>Order History:</h3>
        <?php if (mysqli_num_rows($order_result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Payment Status</th>
                    <th>Fulfillment Status</th>
                    <th>Total</th>
                    <th>Tracking Code</th>

                </tr>
            </thead>
            <tbody>
                <?php
                mysqli_data_seek($order_result, 0); 
                while ($order = mysqli_fetch_assoc($order_result)) { ?>
                    <tr>
                        <td><a href="order-details.php?order_id=<?php echo $order['OrderID']; ?>">#<?php echo $order['OrderID']; ?></a></td>
                        <td><?php echo date("d/m/Y", strtotime($order['OrderDate'])); ?></td>
                        <td><?php echo ucfirst($order['PaymentStatus']); ?></td>
                        <td><?php echo ucfirst($order['FulfillmentStatus']); ?></td>
                        <td>$<?php echo number_format($order['TotalAmount'], 2); ?> USD</td>
                        <td><?php echo ucfirst($order['TrackingCode']); ?></td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>You have no orders yet.</p>
        <?php endif; ?>

        <?php if ($default_address): ?>
        <h3>Account Details:</h3>
        <div class="account-details">
            <p><strong>Recipient:</strong> <?php echo htmlspecialchars($default_address['RecipientName']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($default_address['Phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($default_address['AddressLine']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($default_address['City']); ?></p>
            <p><strong>Zip:</strong> <?php echo htmlspecialchars($default_address['ZipCode']); ?></p>
            <p><strong>Country:</strong> <?php echo htmlspecialchars($default_address['Country']); ?></p>
        </div>
        <?php endif; ?>
    </section>
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
                                <img src="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg" height="120"
                                    WIDTH="150" alt="Logo Image">
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
                        <BR><BR>
                        <h2 class="widget-title">Quicklink</h2>
                        <ul class="widget-list">
                            <li><a href="userdashboard.php">My Acoount</a></li>
                            <li><a href="shop.php">Shop</a></li>
<?php if (isset($_SESSION['user_id'])): ?>
    <li><a href="cart.php">Cart</a></li>
<?php else: ?>
    <li><a href="#" onclick="alert('Please login to view your cart.')">Cart</a></li>
<?php endif; ?>                            <li><a href="contactus.php">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-2 col-custom">
                    <div class="single-footer-widget">
                        <BR><BR>
                        <h2 class="widget-title">Support</h2>
                        <ul class="widget-list">
                            <li><a href="contactus.php">Online Support</a></li>
                            <a href="javascript:void(0)" onclick="openModal('shipping-popup')">Shipping Policy</a>
                            <a href="javascript:void(0)" onclick="openModal('refund-popup')">Refund Policy</a>
                            <li><a href="contact-us.html">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3 col-custom">
                    <div class="single-footer-widget">
                        <BR><BR>

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
</style>
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
    top: 20px; 
    align-self: flex-start;
    height: fit-content;
}

.dashboard-content {
    flex: 1;
    padding: 0 20px 20px 40px; 
    max-width: calc(100% - 290px);
}
       
        .sidebar ul {
            list-style: none;
            padding: 0   }
       .sidebar ul li {
    margin-bottom: 10px;
    background: #fff;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-weight: 500;
    cursor: pointer;
    transition:  box-shadow 0.3s;
}

.sidebar ul li:hover {
    background: #f0f0f0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.sidebar ul li.active {
    background:rgb(196, 155, 102);
    border-color: #999;
}
.sidebar ul li a {
    display: block;
    text-decoration: none;
    color: inherit; 
    width: 100%;
    height: 100%;
}
       
        table {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }
        th, td {
            border: 2px solid #ddd;
            padding: 12px;
        }
        th {
            background: #f0f0f0;
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
       
  /* Rewards Popup Styles */
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
}

.points-info {
    color: #a39157;
    font-weight: 500;
}
        
        </style>

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

// Update the rewards link to trigger the popup
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