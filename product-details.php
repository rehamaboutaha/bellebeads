<?php
session_start();
include 'conn.php';

// Initialize user data if logged in
$userData = null;
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'registereduser') {
    $userId = intval($_SESSION['user_id']);
    $userData = $conn->query("SELECT * FROM registereduser WHERE UserId = $userId")->fetch_assoc();
}

// Validate product ID - this is the key fix
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error'] = "Invalid product ID";
   
}


$productID = (int)$_GET['id'];
try {
    $stmt = $conn->prepare("SELECT * FROM product WHERE ProductID = ?");
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Product not found";
    
    }
    
    $product = $result->fetch_assoc();
} catch (Exception $e) {
    $_SESSION['error'] = "Database error";
    
}
?>
<?php if (isset($_SESSION['favorite_message'])): ?>
    <div class="favorite-notification">
        <?= $_SESSION['favorite_message'] ?>
    </div>
    <?php unset($_SESSION['favorite_message']); ?>
<?php endif; ?>

<style>
.favorite-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background:rgb(0, 0, 0);
    color: white;
    padding: 15px;
    border-radius: 5px;
    z-index: 10000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    animation: fadeInOut 3s ease-in-out forwards;
}

@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-20px); }
    10% { opacity: 1; transform: translateY(0); }
    90% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-20px); }
}
</style>

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
                                                <?php if ($count >= 3) break; ?>
                                                <?php $count++; ?>
                                                <?php $total += $item['Price'] * $item['Quantity']; ?>
                                                
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
                                <a class="whatsapp-color-bg" title="whatsapp" href="#"><i class="fa fa-whatsapp"></i></a>
                                <a class="instagram-color-bg" title="Instagram" href="#"><i class="fa fa-instagram"></i></a>
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
                                <a class="instagram-color-bg" title="Instagram" href="#"><i class="fa fa-instagram"></i></a>
   
                            </div>
                        </div>
                    </div>
                    <!-- offcanvas widget area end -->
                </div>
            </div>
        </aside>
        <!-- off-canvas menu end -->
    </header>
<div class="breadcrumbs-area position-relative">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="breadcrumb-content position-relative section-content">
                        <h3 class="title-3">Product Details</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="container mt-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-5 col-custom mb-4">
            
            <img src="<?= htmlspecialchars($product['ImageURL']) ?>" alt="<?= htmlspecialchars($product['Name']) ?>" class="img-fluid w-100">
            
        </div>

        <!-- Product Info -->
        <div class="col-lg-7 col-custom " style="margin-top:100px;">
            <div class="product-summery position-relative">
                <div class="product-head mb-3">
                    <h2 class="product-title"><?= htmlspecialchars($product['Name']) ?></h2>
                </div>

                <div class="price-box mb-2">
                    <?php if ($product['IsOnSale'] && $product['SalePrice'] < $product['Price']): ?>
                        <span class="regular-price">$<?= number_format($product['SalePrice'], 2) ?></span>
                        <span class="old-price"><del>$<?= number_format($product['Price'], 2) ?></del></span>
                    <?php else: ?>
                        <span class="regular-price">$<?= number_format($product['Price'], 2) ?></span>
                    <?php endif; ?>
                </div>

           <div class="product-rating mb-3">
    <?php
    $productId = $_GET['id'];
    $userId = $_SESSION['user_id'] ?? null;
    
    $userRating = 0;
    $hasRating = false;
   if (isset($userId) && $userId) {
    $stmt = $conn->prepare("SELECT Rating FROM review WHERE UserID = ? AND ProductID = ?");
    
    $stmt->bind_param("ii", $userId, $productId);
    
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userRating = $result->fetch_assoc()['Rating'];
        $hasRating = true;
    }
    
    $stmt->close();
}
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $userRating) {
            echo '<i class="fa fa-star active" data-rating="'.$i.'"></i>';
        } else {
            echo '<i class="fa fa-star-o" data-rating="'.$i.'"></i>';
        }
    }
    ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.product-rating .fa');
    const ratingInput = document.getElementById('selectedRating');
    const ratingBtn = document.getElementById('ratingBtn');
    const removeBtn = document.getElementById('removeBtn');
    
    <?php if ($hasRating): ?>
        ratingInput.value = <?= $userRating ?>;
        ratingBtn.style.display = 'none';
        removeBtn.style.display = 'inline-block';
    <?php endif; ?>
    
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = parseInt(this.dataset.rating);
            highlightStars(rating);
        });
        
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            ratingBtn.style.display = 'inline-block';
            removeBtn.style.display = 'none';
        });
    });
    
    document.querySelector('.product-rating').addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value) || 0;
        highlightStars(currentRating);
    });
    
    function highlightStars(rating) {
        stars.forEach(star => {
            star.classList.remove('fa-star', 'fa-star-o');
            const starRating = parseInt(star.dataset.rating);
            star.classList.add(starRating <= rating ? 'fa-star' : 'fa-star-o');
        });
    }
});
</script>

<?php if (isset($_SESSION['user_id'])): ?>
    <form id="ratingForm" action="save_rating.php" method="post">
        <input type="hidden" name="product_id" value="<?= $productId ?>">
        <input type="hidden" name="rating" id="selectedRating" value="<?= $hasRating ? $userRating : 0 ?>">
        
        <button type="submit" id="ratingBtn" class="btn btn-sm btn-outline-secondary" 
                style="<?= $hasRating ? 'display:none' : 'display:inline-block' ?>">
            Submit Rating
        </button>
        
        <?php if ($hasRating): ?>
            <button type="button" id="removeBtn" class="btn btn-sm btn-outline-danger" 
                    onclick="document.getElementById('selectedRating').value=0; document.getElementById('ratingForm').submit();">
                Remove Rating
            </button>
        <?php endif; ?>
    </form>
<?php else: ?>
    <p class="text-muted">Please <a href="login.php">login</a> to rate this product</p>
<?php endif; ?>
                <div class="sku mb-3">
                    <span>SKU: <?= $product['ProductID'] ?></span>
                </div>

                <div class="product-description mb-4">
                    <p><?= nl2br(htmlspecialchars($product['Description'])) ?></p>
                    <p><strong>In Stock:</strong> <?= $product['Stock'] ?></p>
                    <p><strong>Category ID:</strong> <?= $product['CategoryID'] ?></p>
                </div>

               <div class="quantity-with_btn mb-5">
    <div class="quantity">
        <div class="cart-plus-minus">
            <input class="cart-plus-minus-box" id="quantity-input" value="1" type="number" min="1" max="<?= $product['Stock'] ?>">
            <div class="dec qtybutton">-</div>
            <div class="inc qtybutton">+</div>
        </div>
    </div>
    <div class="add-to_cart">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'registereduser'): ?>
            <a href="#" id="add-to-cart-btn" data-product-id="<?= $product['ProductID'] ?>" class="btn bellebeads-button secondary-btn secondary-border rounded-0">Add to Cart</a>
        <?php else: ?>
            <button class="btn bellebeads-button secondary-btn secondary-border rounded-0" onclick="alert('Please log in to add items to the cart.')">Add to Cart</button>
        <?php endif; ?>
        <a class="btn bellebeads-button secondary-btn secondary-border rounded-0" href="favorite.php?id=<?= $product['ProductID'] ?>">Add to wishlist</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('.cart-plus-minus-box');
    const decButton = document.querySelector('.dec.qtybutton');
    const incButton = document.querySelector('.inc.qtybutton');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    
    decButton.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    incButton.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        let maxStock = <?= $product['Stock'] ?>;
        if (currentValue < maxStock) {
            quantityInput.value = currentValue + 1;
        }
    });
    
    quantityInput.addEventListener('change', function() {
        let value = parseInt(this.value);
        let maxStock = <?= $product['Stock'] ?>;
        
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > maxStock) {
            this.value = maxStock;
        }
    });
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const quantity = parseInt(quantityInput.value);
            
            const originalText = addToCartBtn.innerHTML;
            addToCartBtn.innerHTML = 'Adding...';
            addToCartBtn.disabled = true;
            
            let requests = [];
            for (let i = 0; i < quantity; i++) {
                requests.push(
                    fetch(`add_to_cart.php?id=${productId}`)
                        .then(response => response.ok)
                        .catch(() => false)
                );
            }
            
            Promise.all(requests).then(results => {
                const successCount = results.filter(Boolean).length;
                
                if (successCount === quantity) {
                    window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'added=1';
                } else if (successCount > 0) {
                    alert(`Added ${successCount} item(s) to cart. Could not add all requested quantity.`);
                    window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'added=1';
                } else {
                    alert('Failed to add items to cart. Please try again.');
                    window.location.reload();
                }
            }).finally(() => {
                addToCartBtn.innerHTML = originalText;
                addToCartBtn.disabled = false;
            });
        });
    }
});
</script>

                <div class="widget-social">
    <strong><span>Contact us via:  &nbsp; </span><strong>
   
    <a class="whatsapp-color-bg" title="WhatsApp"
   href="https://wa.me/9613563201?text=Hello%20I%20have%20a%20question%20about%20your%20accessories" target="_blank">
        <i class="fa fa-whatsapp"></i>
    </a>
           </div>
<br><br>
               
            </div>
        </div>
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
                                        <a class="rounded-circle" href="#" title="Instagram">
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
    <?php endif; ?>                                <li><a href="shop.php">Shop</a></li>
<?php if (isset($_SESSION['user_id'])): ?>
    <li><a href="cart.php">Cart</a></li>
<?php else: ?>
    <li><a href="#" onclick="alert('Please login to view your cart.')">Cart</a></li>
<?php endif; ?>                                <li><a href="contactus.php">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-2 col-custom">
                        <div class="single-footer-widget">
                            <br><br>
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

   
    </div>

    <!-- Scroll to Top Start -->
    <a class="scroll-to-top" href="#">
        <i class="lnr lnr-arrow-up"></i>
    </a>
    <!-- Scroll to Top End -->

    <!-- JS
============================================ -->


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

<style>


.popup-modal {
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
<?php if (isset($_SESSION['cart_message'])): ?>
    <div class="cart-notification">
        <?= $_SESSION['cart_message'] ?>
    </div>
    <?php unset($_SESSION['cart_message']); ?>
<?php endif; ?>
<style>
.cart-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #4CAF50; /* Green background */
    color: white;
    padding: 15px;
    border-radius: 5px;
    z-index: 10000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    animation: fadeInOut 3s ease-in-out forwards;
}

@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-20px); }
    10% { opacity: 1; transform: translateY(0); }
    90% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-20px); }
}
</style>



</body>


</html>