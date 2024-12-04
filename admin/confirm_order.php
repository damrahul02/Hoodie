<?php
$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "hoodie_orders"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderId = $_POST['order_id']; // Use the correct POST key

    $orderId = $conn->real_escape_string($orderId);

    $sql = "UPDATE orders SET status = 'confirmed' WHERE id = ?"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);

    if ($stmt->execute()) {
        header("Location: admin.php?success=Order confirmed");
        exit;
    } else {
        echo "Error confirming order: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
