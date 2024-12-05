<?php
include('../connection.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $couponCode = $_POST['couponCode'];
    $couponLimit = $_POST['couponLimit'];

    // Validate inputs
    if (!empty($couponCode) && !empty($couponLimit) && $couponLimit > 0) {
        // Insert the new coupon into the database
        $sql = "INSERT INTO coupons (code, total_coupons, status) VALUES ('$couponCode', $couponLimit, 'active')";
        
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Coupon added successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
}
?>
