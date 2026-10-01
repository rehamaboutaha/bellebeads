<?php
include 'conn.php';

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("
        SELECT * FROM product 
        WHERE Name LIKE CONCAT('%', ?, '%') 
           OR Description LIKE CONCAT('%', ?, '%') 
        ORDER BY 
            CASE 
                WHEN Name LIKE ? THEN 1
                WHEN Name LIKE CONCAT('%', ?, '%') THEN 2
                ELSE 3
            END
        LIMIT 5
    ");
    $stmt->bind_param("ssss", $search, $search, $search, $search);
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        while($product = $results->fetch_assoc()) {
            echo '<div class="search-result-item">';
            echo '<a href="product-details.php?id='.$product['ProductID'].'">';
            echo htmlspecialchars($product['Name']);
            echo '</a>';
            echo '<span class="search-result-price">$'.number_format($product['Price'], 2).'</span>';
            echo '</div>';
        }
    } else {
        echo '<div class="search-result-item">No products found</div>';
    }
}
?>