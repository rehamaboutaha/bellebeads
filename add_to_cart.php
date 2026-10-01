<?php
session_start();
 if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
include 'conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'registereduser') {
    echo "<script>alert('You must be logged in to add items to the cart.'); window.history.back();</script>";
    exit;
}

if (isset($_GET['id'])) {
    $productId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($productId === false) {
        echo "<script>alert('Invalid product ID.'); window.history.back();</script>";
        exit;
    }
    $stmt = $conn->prepare("SELECT * FROM product WHERE ProductID = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $product = $result->fetch_assoc()) {
        $today = date('Y-m-d');
        $isOnSale = $product['IsOnSale'] == 1 && $product['SaleStart'] <= $today && $product['SaleEnd'] >= $today;
        $finalPrice = $isOnSale ? $product['SalePrice'] : $product['Price'];
$stock = intval($product['Stock']);
 $currentQtyInCart = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId]['Quantity'] : 0;

        if ($currentQtyInCart + 1 > $stock) {
            echo "<script>alert('You cannot add more than $stock of this item to the cart.'); window.history.back();</script>";
            exit;
        }
        $item = [
            'ProductID' => $product['ProductID'],
            'Name' => htmlspecialchars($product['Name']),
            'Price' => floatval($finalPrice),
            'ImageURL' => htmlspecialchars($product['ImageURL']),
            'Quantity' => 1
        ];
    }
    
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['Quantity']++; 
        } else {
            $_SESSION['cart'][$productId] = $item;
        }
    
    

    }
    $_SESSION['cart_message'] = "Item added to cart successfully!";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'shop.php') . "?added=1");
exit();

?>
