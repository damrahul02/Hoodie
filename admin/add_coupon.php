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
    $couponCode = $_POST['coupon_code'];


    $checkQuery = "SELECT COUNT(*) AS count FROM coupons WHERE code = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $couponCode);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['count'] > 0) {
        echo "error: Coupon code already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO coupons (code, status) VALUES (?, 'claimable')");
        $stmt->bind_param("s", $couponCode);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: Unable to add coupon.";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
