<?php
include '../connection.php';


$sql = "SELECT id, code, total_coupons,count FROM coupons";
$result = mysqli_query($conn, $sql);

$coupons = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $coupons[] = $row;
    }
}

echo json_encode($coupons);
?>
