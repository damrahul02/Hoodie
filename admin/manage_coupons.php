<?php
include '../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $couponId = $_POST['couponId'];

    if ($action == 'delete') {
        $sql = "DELETE FROM coupons WHERE id = $couponId";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Coupon deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete coupon.']);
        }
    }
    elseif ($action == 'update') {
        $newLimit = $_POST['newLimit'];
        $sql = "UPDATE coupons SET total_coupons = $newLimit WHERE id = $couponId";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Coupon updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update coupon.']);
        }
    }
}
?>
