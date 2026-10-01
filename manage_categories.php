<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['description'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $ImageURL = '';

    if (isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['Image']['tmp_name'];
        $fileName = time() . '_' . preg_replace("/[^A-Za-z0-9\.\-_]/", '_', basename($_FILES['Image']['name']));
        $destPath = 'Images/' . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $ImageURL = $conn->real_escape_string($destPath);
        }
    }

    if (!empty($_POST['edit_id'])) {
        $id = intval($_POST['edit_id']);
        $sql = "UPDATE category SET Name='$name', Description='$description'";
        if (!empty($ImageURL)) {
            $sql .= ", ImageURL='$ImageURL'";
        }
        $sql .= " WHERE CategoryID=$id";
        $conn->query($sql);
    } else {
        
        $conn->query("INSERT INTO category (Name, Description, ImageURL) VALUES ('$name', '$description', '$ImageURL')");
    }
    header("Location: manage_categories.php"); 
    exit();
}


if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM product WHERE CategoryID = $id");

    $conn->query("DELETE FROM category WHERE CategoryID = $id");
    header("Location: manage_categories.php");
    exit();
}

$editCategory = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $editResult = $conn->query("SELECT * FROM category WHERE CategoryID = $edit_id");
    if ($editResult->num_rows > 0) {
        $editCategory = $editResult->fetch_assoc();
    }
}


$result = $conn->query("SELECT * FROM category ORDER BY CategoryID DESC");
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
                    <h3 class="title-3">Manage Categories </h3>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-4">
    <h2 class="mb-4"><?= $editCategory ? 'Edit Category' : 'Add Category' ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="edit_id" value="<?= $editCategory['CategoryID'] ?? '' ?>">
        <div class="mb-3">
            <label>Name:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editCategory['Name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label>Description:</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($editCategory['Description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label>Image:</label>
            <input type="file" name="Image" class="form-control">
            <?php if (!empty($editCategory['ImageURL'])): ?>
                <div class="mt-2">
                    <img src="<?= $editCategory['ImageURL'] ?>" width="120" alt="Current Image">
                </div>
            <?php endif; ?>
        </div>
        <br>
        <button type="submit" class="btn bellebeads-button secondary-btn rounded-0-<?= $editCategory ? 'warning' : 'primary' ?>">
            <?= $editCategory ? 'Update Category' : 'Add Category' ?>
        </button>
        <?php if ($editCategory): ?>
            <a href="manage_categories.php" class="btn bellebeads-button secondary-btn rounded-0">Cancel</a>
        <?php endif; ?>
    </form>
<br>
    <table class="table table-bordered ">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['CategoryID'] ?></td>
                <td><?= htmlspecialchars($row['Name']) ?></td>
                <td><?= htmlspecialchars($row['Description']) ?></td>
                <td>
                    <?php if (!empty($row['ImageURL']) && file_exists($row['ImageURL'])): ?>
                        <img src="<?= $row['ImageURL'] ?>" width="80">
                    <?php else: ?>
                        No image
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?edit_id=<?= $row['CategoryID'] ?>" class="btn bellebeads-button secondary-btn rounded-0">Edit</a>
                    <a href="?delete_id=<?= $row['CategoryID'] ?>" class="btn bellebeads-button secondary-btn rounded-0" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
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