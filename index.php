<?php
session_start();
include 'conn.php';  

$userData = null;
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'registereduser') {
    $userId = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT * FROM registereduser WHERE UserId = ?");
    $stmt->bind_param("i", $userId); 
    $stmt->execute();
    $result = $stmt->get_result();  
    $userData = $result->fetch_assoc();  
}

$bestSellers = $conn->query("SELECT * FROM product WHERE IsBestSeller = 1 LIMIT 4");

$isSubscribed = false;
if (isset($_SESSION['user_id'], $_SESSION['user_email'])) {
    $stmt = $conn->prepare("SELECT IsActive FROM newslettersubscriber WHERE Email = ? AND UserID = ?");
    $stmt->bind_param("si", $_SESSION['user_email'], $_SESSION['user_id']); // 's' for string, 'i' for integer
    $stmt->execute();
    $result = $stmt->get_result();  
    $row = $result->fetch_assoc();  
    if ($row && $row['IsActive']) {
        $isSubscribed = true;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
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
  <header class="main-header-area">
    <!-- Main Header Area Start -->
    <div class="main-header header-transparent header-sticky">
        <div class="container-fluid">
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
                                <a class="active" href="index.php">
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
                <?php $count = 0;  ?>
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
                            <!-- Product Name -->
                            <h5 class="title" style="margin: 0 0 6px 0; font-size: 14px;">
                                <a href="cart.php" style="text-decoration: none; color: #000;">
                                    <?= htmlspecialchars($item['Name']) ?>
                                </a>
                            </h5>

                            <!-- Quantity × Price and Trash Icon -->
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                                <div>
                                    <span><?= $item['Quantity'] ?>× $<?= number_format($item['Price'], 2) ?></span>
                                </div>
                                <form action="cart.php" method="post" style="margin: 0;">
                                    <input type="hidden" name="remove_id" value="<?= $id ?>">
                                    <button type="submit" title="Remove Item" style="background: none; border: none; font-size: 16px; cursor: pointer;">🗑️</button>
                                </form>
                            </div>

                            <!-- Quantity Controls -->
                            <form action="cart.php" method="post" style="display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" name="quantity" value="decrease" style="padding: 2px 6px; font-size: 13px;">-</button>
                                <span style="font-size: 13px;"><?= $item['Quantity'] ?></span>
                                <button type="submit" name="quantity" value="increase" style="padding: 2px 6px; font-size: 13px;">+</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Total and Links -->
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
                    <nav class="main-nav d-none d-lg-flex">
                        <ul class="nav">
                            <li>
                                <a class="active" href="index.php">
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
                        <!-- mobile menu navigation end -->
                    </div>
                    <!-- mobile menu end -->
                    <div class="offcanvas-widget-area">
                      
                        <div class="top-info-wrap text-left text-black">
                            <ul class="address-info">
                                <li>
                                    <i class="fa fa-phone"></i>
                                    <a href=>(+961) 3563201</a>
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
                            <li><a href="about-us.html">About Us</a></li>
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
    <!-- Slider/Intro Section Start -->
    <div class="intro11-slider-wrap section">
        <div class="intro11-slider swiper-container">
            <div class="swiper-wrapper">
                <div class="intro11-section swiper-slide slide-1 slide-bg-1 bg-position">
                    <!-- Intro Content Start -->
                    <div class="intro11-content text-left">
                        <h3 class="title-slider text-uppercase">Top Trend</h3>
                        <h2 class="title">Belle Beads</h2>
                        <p class="desc-content">Welcome to Rama Abou Taha's website, where every product tells a unique story. Each handmade creation is crafted with love and passion, making every piece truly special </p>
                        <a href="shop.php" class="btn bellebeads-button secondary-btn theme-color  rounded-0">Shop Now</a>
                    </div>
                    <!-- Intro Content End -->
                </div>
                <div class="intro11-section swiper-slide slide-2 slide-bg-1 bg-position">
                    <!-- Intro Content Start -->
                    <div class="intro11-content text-left">
                        <h3 class="title-slider black-slider-title text-uppercase">Collection</h3>
                        <h2 class="title">Bracelets and Necklaces<br> Unique Pieces</h2>
                        <p class="desc-content">Welcome to Rama Abou Taha's website, where every product tells a unique story. Each handmade creation is crafted with love and passion, making every piece truly special </p>
                        <a href="product-details.html" class="btn bellebeads-button secondary-btn rounded-0">Shop Now</a>
                    </div>
                    <!-- Intro Content End -->
                </div>
            </div>
            <!-- Slider Navigation -->
            <div class="home1-slider-prev swiper-button-prev main-slider-nav"><i class="lnr lnr-arrow-left"></i></div>
            <div class="home1-slider-next swiper-button-next main-slider-nav"><i class="lnr lnr-arrow-right"></i></div>
            <!-- Slider pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
    <!-- Slider/Intro Section End -->
    <!--Categories Area Start-->
    <?php
include 'conn.php';
$categories = $conn->query("SELECT * FROM category ORDER BY CategoryID ASC");
?>
      <!-- Left large image -->
      <?php if ($row = $categories->fetch_assoc()): ?>
      <div class="categories-area pt-40">
        <div class="container-fluid">
            <div class="row align-items-stretch">
                <!-- Left side (Vertical image) -->
                <div class="cat-1 col-md-4 col-sm-12 col-custom">
                    <div class="categories-img mb-30 h-100">
                    <a href="<?= $row['Description'] ?>"><img src="<?= $row['ImageURL'] ?>" alt="" 
                    style="height: 100%; object-fit: cover;"></a>
                    <div class="categories-content">
                    <h3><?= htmlspecialchars($row['Name']) ?></h3>
                    </div>
                    </div>
                </div>
                <?php endif; ?>
      <!-- Right 4 grid items -->
      <div class="cat-2 col-md-8 col-sm-12 col-custom">
        <div class="row">
          <?php while ($row = $categories->fetch_assoc()): ?>
          <div class="col-md-6 col-sm-6 mb-30">
            <div class="categories-img">
              <a href="<?= $row['Description'] ?>"><img src="<?= $row['ImageURL'] ?>" alt=""></a>
              <div class="categories-content">
                <h3><?= htmlspecialchars($row['Name']) ?></h3>
              </div>
            </div>
          </div>
          <?php endwhile; ?>

        </div>
      </div>

    </div>
  </div>
</div>             
    <!--Categories Area End-->


    
    <!--Product Area Start-->
    <div class="product-area mt-text-2">
        <div class="container custom-area-2 overflow-hidden">
            <div class="row">
                <!--Section Title Start-->
                <div class="col-12 col-custom">
                    <div class="section-title text-center mb-30">
                        <span class="section-title-1">Order Yours</span>
                        <h3 class="section-title-3">Best Seller</h3>
                    </div>
                </div>
                <!--Section Title End-->
            </div>
            <div class="row product-row">
                <div class="col-12 col-custom">
                    <div class="product-slider swiper-container anime-element-multi">
                    <div class="swiper-wrapper">
    <?php while($product = $bestSellers->fetch_assoc()): ?>
    <div class="single-item swiper-slide">
        <!--Single Product Start-->
        <div class="single-product position-relative mb-30">
            <div class="product-image">
                <a class="d-block" href="product-details.php?id=<?= $product['ProductID'] ?>">
                    <img src="<?= htmlspecialchars($product['ImageURL']) ?>" alt="" class="product-image-1 w-100">
                    <?php if ($product['IsOnSale'] && $product['SalePrice'] < $product['Price']): ?>
    <span class="onsale">Sale!</span>
<?php endif; ?>

                </a>
                <div class="add-action d-flex flex-column position-absolute">
                   <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'registereduser'): ?>
                          <a href="favorite.php?id=<?= $product['ProductID'] ?>" title="Add To favorite">
                         <i class="lnr lnr-heart"></i>
                          </a>
                        <?php else: ?>
                        <a href="#" onclick="alert('Please login to add favorites.'); return false;" title="Add To favorite">
                       <i class="lnr lnr-heart"></i>
                     </a>
                     <?php endif; ?>
                       <a href="product-details.php?id=<?php echo $product['ProductID']; ?>">
                    
                            <i class="lnr lnr-eye" title="Quick View"></i>
                        </a>
                </div>
            </div>
            <div class="product-content">
                <div class="product-title">
                    <br><br><br>
                    <h4 class="title-2">
                        <a href="product-details.php?id=<?= $product['ProductID'] ?>">
                            <?= htmlspecialchars($product['Name']) ?>
                        </a>
                    </h4>
                </div>
                <div class="product-rating">
    <?php
    $productId = $product['ProductID'];
    $ratingQuery = $conn->query("
        SELECT AVG(Rating) as average_rating 
        FROM review 
        WHERE ProductID = $productId
    ");
    $avgRating = $ratingQuery->fetch_assoc()['average_rating'];
    
    for ($i = 1; $i <= 5; $i++) {
        if ($avgRating >= $i) {
            echo '<i class="fa fa-star"></i>'; 
        } elseif ($avgRating >= ($i - 0.5)) {
            echo '<i class="fa fa-star-half-alt"></i>'; 
        } else {
            echo '<i class="fa fa-star-o"></i>'; 
        }
    }
    if ($avgRating) {
        echo '<span class="rating-value">('.number_format($avgRating, 1).')</span>';
    }
    ?>
</div>

                <div class="price-box">
    <?php if ($product['IsOnSale'] && $product['SalePrice'] < $product['Price']): ?>
        <span class="regular-price">$<?= number_format($product['SalePrice'], 2) ?></span>
        <span class="old-price"><del>$<?= number_format($product['Price'], 2) ?></del></span>
    <?php else: ?>
        <span class="regular-price">$<?= number_format($product['Price'], 2) ?></span>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'registereduser'): ?>
    <a href="add_to_cart.php?id=<?= $product['ProductID'] ?>" class="btn product-cart">Add to Cart</a>
<?php else: ?>
    <button class="btn product-cart" onclick="alert('Please log in to add items to the cart.')">Add to Cart</button>
<?php endif; ?>

            </div>
        </div>
        <!--Single Product End-->
    </div>
    <?php endwhile; ?>
</div>
                            
    <!--Product Area End-->
    <!-- Product Countdown Area Start Here -->
    <div class="product-countdown-area mt-text-3">
        <div class="container custom-area">
            <div class="row">
                <!--Section Title Start-->
                <div class="col-12 col-custom">
                    <div class="section-title text-center mb-30">
                        <h3 class="section-title-3">Deal of The Day</h3>
                    </div>
                </div>
                <!--Section Title End-->
            </div>
            <div class="row">
                <!--Countdown Start-->
                      <!--Countdown Start-->
                      <div class="col-12 col-custom">
                    <div class="countdown-area">
                        <div class="countdown-wrapper d-flex justify-content-center" data-countdown="2026/11/5"></div>
                    </div>
                </div>
                <!--Countdown End-->
            </div>
            <div class="row product-row">
                <div class="col-12 col-custom">
                    <div class="item-carousel-2 swiper-container anime-element-multi product-area">
                        
                        <div class="swiper-wrapper">


<?php
$today = date('Y-m-d');
$sql = "SELECT * FROM product WHERE IsOnSale = 1 AND SaleStart <= '$today' AND SaleEnd >= '$today'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        ?>
        <div class="single-item swiper-slide">
            <div class="single-product position-relative mb-30">
                <div class="product-image">
                    <a class="d-block" href="product-details.php?id=<?php echo $row['ProductID']; ?>">
                        <img src="<?php echo $row['ImageURL']; ?>" alt="" class="product-image-1 w-100">
                    </a>
                    <span class="onsale">Sale!</span>
                    <div class="add-action d-flex flex-column position-absolute">
                       <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'registereduser'): ?>
                          <a href="favorite.php?id=<?= $row['ProductID'] ?>" title="Add To favorite">
                         <i class="lnr lnr-heart"></i>
                          </a>
                        <?php else: ?>
                        <a href="#" onclick="alert('Please login to add favorites.'); return false;" title="Add To favorite">
                       <i class="lnr lnr-heart"></i>
                     </a>
                     <?php endif; ?>
                        <a href="product-details.php?id=<?php echo $row['ProductID']; ?>">
                    
                            <i class="lnr lnr-eye" title="Quick View"></i>
                        </a>
                    </div>
                </div>
                <div class="product-content">
                    <div class="product-title">
                        <h4 class="title-2">
                            <a href="product-details.php?id=<?php echo $row['ProductID']; ?>">
                                <?php echo htmlspecialchars($row['Name']); ?>
                            </a>
                        </h4>
                    </div>
                     <div class="product-rating">
    <?php
    $productId = $row['ProductID'];
    $ratingQuery = $conn->query("
        SELECT AVG(Rating) as average_rating 
        FROM review 
        WHERE ProductID = $productId
    ");
    $avgRating = $ratingQuery->fetch_assoc()['average_rating'];
    
    for ($i = 1; $i <= 5; $i++) {
        if ($avgRating >= $i) {
            echo '<i class="fa fa-star"></i>'; 
        } elseif ($avgRating >= ($i - 0.5)) {
            echo '<i class="fa fa-star-half-alt"></i>'; 
        } else {
            echo '<i class="fa fa-star-o"></i>'; 
        }
    }
    if ($avgRating) {
        echo '<span class="rating-value">('.number_format($avgRating, 1).')</span>';
    }
    ?>
</div>
                    <div class="price-box">
                        <span class="regular-price ">$<?php echo $row['SalePrice']; ?></span>
                        <span class="old-price"><del>$<?php echo $row['Price']; ?></del></span>
                    </div>
<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'registereduser'): ?>
    <a href="add_to_cart.php?id=<?= $row['ProductID'] ?>" class="btn product-cart">Add to Cart</a>
<?php else: ?>
    <button class="btn product-cart" onclick="alert('Please log in to add items to the cart.')">Add to Cart</button>
<?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo "<p class='text-center'>No products on sale today.</p>";
}

$conn->close();
?>

</div>
                    <div class="swiper-pagination default-pagination"></div>
</div>
</div></div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- Product Countdown Area End Here -->
    <!-- History Area Start Here -->
    <div class="our-history-area pt-text-3">
        <div class="container">
            <div class="row">
                <!--Section Title Start-->
                <div class="col-12">
                    <div class="section-title text-center mb-30">
                        <span class="section-title-1">A little Story About Us</span>
                        <h2 class="section-title-large">Our History</h2>
                    </div>
                </div>
                <!--Section Title End-->
            </div>
            <div class="row">
                <div class="col-lg-8 ms-auto me-auto">
                    <div class="history-area-content pb-0 mb-0 border-0 text-center">
                        <p><strong>Belle Beads: Where elegance meets artistry</strong></p>
                        <p>Our journey began with a simple handmade prayer bead bracelet that unexpectedly opened doors to something much bigger. What started as a creative experiment turned into a thriving business when a fun social media video gained unexpected popularity, sparking interest in our products. This led to the creation of Belle Beads, initially focused on handmade accessories. As demand grew, so did our vision—we expanded our collection to include a diverse range of elegant and unique accessories, including all types of accessories and ready-made pieces, allowing us to reach a wider audience and grow our brand into what it is today. Today, Belle Beads is more than just a brand; it’s a reflection of passion and the beauty of handmade artistry.</p>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
    <!-- History Area End Here -->
    <!-- Newsletter Area Start Here -->
<!-- Newsletter Section -->

<div class="news-letter-area gray-bg pt-no-text pb-no-text mt-text-3">
    <div class="container custom-area">
        <div class="row align-items-center">
            <div class="col-md-6 col-custom">
                <div class="section-title text-left mb-35">
                    <h3 class="section-title-3">Send Newsletter</h3>
                    <p class="desc-content mb-0">Enter Your Email Address For Our Mailing List To Keep Your Self Update</p>
                </div>
            </div>
            <div class="col-md-6 col-custom">
                <div class="news-latter-box">
                    <div class="newsletter-form-wrap text-center">
                        <form id="mc-form" class="mc-form">
    <input type="email" id="mc-email" class="form-control email-box"
        placeholder="email@example.com"
        value="<?= isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '' ?>"
        readonly>

    <button id="mc-submit" class="btn rounded-0" type="submit">
        <?= (isset($_SESSION['user_id']) && $isSubscribed) ? 'Unsubscribe' : 'Subscribe' ?>
    </button>
</form>

<div class="mailchimp-alerts text-centre">
    <div class="mailchimp-success text-success" style="display:none;"></div>
    <div class="mailchimp-error text-danger" style="display:none;"></div>
</div>

<?php if (!isset($_SESSION['user_id'])): ?>
<script>
    document.getElementById('mc-form').addEventListener('submit', function(e) {
        e.preventDefault();
        alert("⚠️ Please log in to subscribe to the newsletter.");
    });
</script>
<?php endif; ?>

                        </form>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery and AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#mc-form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'subscribe.php',
            method: 'POST',
            data: { email: $('#mc-email').val() },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('.mailchimp-success').text(response.message).fadeIn().delay(1500).fadeOut();
                    $('#mc-submit').text(response.status === 'subscribed' ? 'Unsubscribe' : 'Subscribe');
                } else {
                    $('.mailchimp-error').text(response.message).fadeIn().delay(1500).fadeOut();
                }
            },
            error: function () {
                $('.mailchimp-error').text('Something went wrong.').fadeIn().delay(1500).fadeOut();
            }
        });
    });
});
</script>

    
    <footer class="footer-area">
        <div class="footer-widget-area">
            <div class="container container-default custom-area">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-3 col-custom">
                        <div class="single-footer-widget m-0">
                            <div class="footer-logo">
                                <a href="index.php">
                                    <img src="Reham/Images/bc2587bf928f10d45a8227b64cab9c90.jpg" height="120" WIDTH="150"  alt="Logo Image">
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
 <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="userdashboard.php">My Account</a></li>
    <?php else: ?>
        <li><a href="login.php">Login/Register</a></li>
    <?php endif; ?>                                <li><a href="shop.php">Shop</a></li>
<?php if (isset($_SESSION['user'])): ?>
    <li><a href="cart.php">Cart</a></li>
<?php else: ?>
    <li><a href="#" onclick="alert('Please login to view your cart.')">Cart</a></li>
<?php endif; ?>                                <li><a href="contactus.php">Contact</a></li>
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
                                  </address>                            </div>
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
                            <p>Copyright © 2025 <strong>Belle Beads</strong>  All Rights Reserved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </footer>
    <!--Footer Area End-->

   
                                           
    </div>

    <a class="scroll-to-top" href="#">
        <i class="lnr lnr-arrow-up"></i>
    </a>
   
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
        success: function (response) {
    if (response === 'redirect') {
        window.location.href = "login.php?msg=You must be logged in to subscribe.";
    } else {
        $('.mailchimp-success').text(response).show();
        $('.mailchimp-error').hide();
    }
}



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


</body>


</html>                            























    