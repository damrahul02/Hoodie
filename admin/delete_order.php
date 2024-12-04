<?php
include('../connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderId = $_POST['order_id']; // Use the correct POST key

    $orderId = $conn->real_escape_string($orderId);

    $sql = "DELETE FROM orders WHERE id = ?"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);

    if ($stmt->execute()) {
        header("Location: admin.php?success=Order deleted");
        exit;
    } else {
        echo "Error deleting order: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
