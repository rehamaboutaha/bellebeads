<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ProductID'])) {
        $ProductID = intval($_POST['ProductID']);
        $Name = $_POST['Name'];
        $Description = $_POST['Description'];
        $Price = $_POST['Price'];
        $Stock = $_POST['Stock'];
        $IsBestSeller = isset($_POST['IsBestSeller']) ? intval($_POST['IsBestSeller']) : 0;
        $CategoryID = $_POST['CategoryID'];
        $IsOnSale = $_POST['IsOnSale'];
        $SalePrice = !empty($_POST['SalePrice']) ? $_POST['SalePrice'] : null;
        $SaleStart = !empty($_POST['SaleStart']) ? $_POST['SaleStart'] : null;
        $SaleEnd = !empty($_POST['SaleEnd']) ? $_POST['SaleEnd'] : null;

        $ImageURL = '';
        if (isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['Image']['tmp_name'];
            $fileName = basename($_FILES['Image']['name']);
            $fileName = time() . '_' . preg_replace("/[^A-Za-z0-9\.\-_]/", '_', $fileName);
            $destPath = 'Images/' . $fileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $ImageURL = $conn->real_escape_string($destPath);
            }
        }

        if (!empty($ImageURL)) {
            $stmt = $conn->prepare("UPDATE product SET 
                Name = ?, 
                Description = ?, 
                Price = ?, 
                Stock = ?, 
                ImageURL = ?, 
                IsBestSeller = ?, 
                CategoryID = ?, 
                IsOnSale = ?, 
                SalePrice = ?, 
                SaleStart = ?, 
                SaleEnd = ?
                WHERE ProductID = ?");
            $stmt->bind_param(
                "ssdisiiidssi",
                $Name,
                $Description,
                $Price,
                $Stock,
                $ImageURL,
                $IsBestSeller,
                $CategoryID,
                $IsOnSale,
                $SalePrice,
                $SaleStart,
                $SaleEnd,
                $ProductID
            );
        } else {
            $stmt = $conn->prepare("UPDATE product SET 
                Name = ?, 
                Description = ?, 
                Price = ?, 
                Stock = ?, 
                IsBestSeller = ?, 
                CategoryID = ?, 
                IsOnSale = ?, 
                SalePrice = ?, 
                SaleStart = ?, 
                SaleEnd = ?
                WHERE ProductID = ?");
            $stmt->bind_param(
                "ssdiiiidssi",
                $Name,
                $Description,
                $Price,
                $Stock,
                $IsBestSeller,
                $CategoryID,
                $IsOnSale,
                $SalePrice,
                $SaleStart,
                $SaleEnd,
                $ProductID
            );
        }

        if ($stmt->execute()) {
            echo "<script>alert('Product updated successfully.');</script>";
        } else {
            echo "<script>alert('Error updating product: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        // Handle add
        $Name = $_POST['Name'];
        $Description = $_POST['Description'];
        $Price = $_POST['Price'];
        $Stock = $_POST['Stock'];
        $IsBestSeller = isset($_POST['IsBestSeller']) ? intval($_POST['IsBestSeller']) : 0;
        $CategoryID = $_POST['CategoryID'];
        $IsOnSale = $_POST['IsOnSale'];
        $SalePrice = !empty($_POST['SalePrice']) ? $_POST['SalePrice'] : null;
        $SaleStart = !empty($_POST['SaleStart']) ? $_POST['SaleStart'] : null;
        $SaleEnd = !empty($_POST['SaleEnd']) ? $_POST['SaleEnd'] : null;

        $ImageURL = '';
        if (isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['Image']['tmp_name'];
            $fileName = basename($_FILES['Image']['name']);
            $fileName = time() . '_' . preg_replace("/[^A-Za-z0-9\.\-_]/", '_', $fileName);
            $destPath = 'Images/' . $fileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $ImageURL = $conn->real_escape_string($destPath);
            }
        }

        $stmt = $conn->prepare("INSERT INTO product 
        (Name, Description, Price, Stock, ImageURL, IsBestSeller, CategoryID, IsOnSale, SalePrice, SaleStart, SaleEnd) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "ssdisiiidss",
            $Name,
            $Description,
            $Price,
            $Stock,
            $ImageURL,
            $IsBestSeller,
            $CategoryID,
            $IsOnSale,
            $SalePrice,
            $SaleStart,
            $SaleEnd
        );

        if ($stmt->execute()) {
            echo "<script>alert('Product added successfully.'); </script>";
        } else {
            echo "<script>alert('Error adding product: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }
}

if (isset($_GET['delete_id'])) {
    $ProductID = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM product WHERE ProductID = ?");
    $stmt->bind_param("i", $ProductID);
    $stmt->execute();
}

$categories = $conn->query("SELECT * FROM category");
$products = $conn->query("SELECT product.*, category.Name AS CategoryName 
                          FROM product
                          JOIN category ON product.CategoryID = category.CategoryID");
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
            <a href="logout.php" class="btn bellebeads-button secondary-btn rounded-0">Logout</a>
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
                        <h3 class="title-3">Manage Products </h3>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="container mt-4">
    <form method="post" enctype="multipart/form-data" class="row g-2 border p-3 rounded bg-light">
        <div>
            <input type="text" name="Name" class="form-control" placeholder="Product Name" required>
        </div>
        <div>
            <input type="text" name="Description" class="form-control" placeholder="Description">
        </div>
        <div >
            <input type="number" step="0.01" name="Price" class="form-control" placeholder="Price" required>
        </div>
        <div >
            <input type="number" name="Stock" class="form-control" placeholder="Stock" required>
        </div>
        <div >
            <input type="file" name="Image" class="form-control" accept="image/*" required>
        </div>
        
        <div >
            <select name="CategoryID" class="form-control" required>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['CategoryID'] ?>"><?= htmlspecialchars($cat['Name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <br>
        <div>
            <label for="IsBestSeller" class="form-label">Best Seller</label>
            <select name="IsBestSeller" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div>
            <label for="IsOnSale" class="form-label" >On Sale</label>
            <select name="IsOnSale" id="IsOnSale" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div id="sale-fields" style="display: none;">
            <div >
                <input type="number" step="0.01" name="SalePrice" class="form-control" placeholder="Sale Price">
            </div>
            <br>
            <div>
                <input type="date" name="SaleStart" class="form-control" placeholder="Sale Start">
            </div>
            <br>
            <div>
                <input type="date" name="SaleEnd" class="form-control" placeholder="Sale End">
            </div>
        </div>
        <div>
            <button class="btn bellebeads-button secondary-btn rounded-0">Add Product</button>
        </div>
    </form>

    <table class="table table-bordered mt-4">
        <thead class="table-dark" >
        <tr>
            <th>ProductID</th><th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Image</th>
            <th>Category</th><th>Best Seller</th><th>On Sale</th><th>Sale Price</th><th>Sale Start</th>
            <th>Sale End</th><th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $categories = $conn->query("SELECT * FROM category"); 
        while ($row = $products->fetch_assoc()): 
            $isEditing = isset($_GET['edit_id']) && $_GET['edit_id'] == $row['ProductID'];
        ?>
            <tr>
                <?php if ($isEditing): ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ProductID" value="<?= $row['ProductID'] ?>">
                        <td><?= $row['ProductID'] ?></td>
                        <td><input type="text" name="Name" value="<?= htmlspecialchars($row['Name']) ?>" class="form-control form-control-sm"></td>
                        <td><input type="text" name="Description" value="<?= htmlspecialchars($row['Description']) ?>" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.01" name="Price" value="<?= $row['Price'] ?>" class="form-control form-control-sm"></td>
                        <td><input type="number" name="Stock" value="<?= $row['Stock'] ?>" class="form-control form-control-sm"></td>
                        <td>
                            <input type="file" name="Image" class="form-control form-control-sm">
                            <?php if ($row['ImageURL']): ?>
                                <small>Current: <?= basename($row['ImageURL']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select name="CategoryID" class="form-control form-control-sm">
                                <?php 
                                $categories->data_seek(0); 
                                while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= $cat['CategoryID'] ?>" <?= $cat['CategoryID'] == $row['CategoryID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['Name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td>
                            <select name="IsBestSeller" class="form-control form-control-sm">
                                <option value="0" <?= !$row['IsBestSeller'] ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= $row['IsBestSeller'] ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </td>
                        <td>
                            <select name="IsOnSale" class="form-control form-control-sm">
                                <option value="0" <?= !$row['IsOnSale'] ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= $row['IsOnSale'] ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" name="SalePrice" value="<?= $row['SalePrice'] ?>" class="form-control form-control-sm"></td>
                        <td><input type="date" name="SaleStart" value="<?= $row['SaleStart'] ?>" class="form-control form-control-sm"></td>
                        <td><input type="date" name="SaleEnd" value="<?= $row['SaleEnd'] ?>" class="form-control form-control-sm"></td>
                        <td class="d-flex gap-1">
                            <button type="submit" class="btn bellebeads-button secondary-btn rounded-0">Save</button>
                            <a href="manage_products.php" class="btn bellebeads-button secondary-btn rounded-0">Cancel</a>
                        </td>
                    </form>
                <?php else: ?>
                    <td><?= $row['ProductID'] ?></td>
                    <td><?= htmlspecialchars($row['Name']) ?></td>
                    <td><?= htmlspecialchars($row['Description']) ?></td>
                    <td>$<?= number_format($row['Price'], 2) ?></td>
                    <td><?= $row['Stock'] ?></td>
                    <td>
                        <?php if ($row['ImageURL']): ?>
                            <img src="<?= htmlspecialchars($row['ImageURL']) ?>" alt="Image" style="width:50px;">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['CategoryName']) ?></td>
                    <td><?= $row['IsBestSeller'] ? '✅' : '❌' ?></td>
                    <td><?= $row['IsOnSale'] ? '✅' : '❌' ?></td>
                    <td><?= $row['IsOnSale'] ? '$' . number_format($row['SalePrice'], 2) : '-' ?></td>
                    <td><?= $row['SaleStart'] ?? '-' ?></td>
                    <td><?= $row['SaleEnd'] ?? '-' ?></td>
                    <td class="d-flex gap-1">
                        <a href="?edit_id=<?= $row['ProductID'] ?>" class="btn bellebeads-button secondary-btn rounded-0">Edit</a>
                        <a href="?delete_id=<?= $row['ProductID'] ?>" class="btn bellebeads-button secondary-btn rounded-0" onclick="return confirm('Are you sure?')">Delete</a>
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
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        
    }

    .table-bordered {
        border: 1px solid #dee2e6 !important;
    }
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6 !important;
    }
    .table-bordered thead th,
    .table-bordered thead td {
        border-bottom-width: 2px !important;
    }
    .table-bordered input.form-control-sm,
    .table-bordered select.form-control-sm {
        border: 1px solid #dee2e6 !important;
        width: 100%;
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

document.addEventListener('DOMContentLoaded', function () {
    const onSaleSelect = document.getElementById('IsOnSale');
    const saleFields = document.getElementById('sale-fields');

    function toggleSaleFields() {
        saleFields.style.display = onSaleSelect.value === '1' ? 'block' : 'none';
    }

    onSaleSelect.addEventListener('change', toggleSaleFields);

    toggleSaleFields();
});

document.getElementById('IsOnSale').addEventListener('change', function () {
    document.getElementById('sale-fields').style.display = this.value == '1' ? 'block' : 'none';
});
</script>

</body>
</html>