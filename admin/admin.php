<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hoodie_orders";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['search_email'])) {
    $searchEmail = $_GET['search_email'];
    $query = "SELECT * FROM orders WHERE student_id LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$searchEmail%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $query = "SELECT * FROM orders";
    $result = $conn->query($query);
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

$totalCouponsQuery = "SELECT COUNT(*) AS total FROM coupons";
$totalCouponsResult = $conn->query($totalCouponsQuery);
$totalCoupons = $totalCouponsResult->fetch_assoc()['total'];


$claimableCouponsQuery = "SELECT COUNT(*) AS claimable FROM coupons WHERE status = 'claimable'";
$claimableCouponsResult = $conn->query($claimableCouponsQuery);
$claimableCoupons = $claimableCouponsResult->fetch_assoc()['claimable'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
    <style>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}


table {
    width: 100%;
    border-collapse: collapse;
    overflow-x: auto;
    display: block; 
}

th, td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
}

th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

@media (max-width: 768px) {
    th, td {
        font-size: 12px;
        padding: 5px;
    }
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
    overflow-y: auto;
    border: 2px solid #007bff; 
}

.modal-content {
    display: flex;
    flex-direction: column;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.info {
    margin-top: 10px;
    font-size: 14px;
    color: #555;
}

button {
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button.add-coupon {
    background-color: #28a745;
    color: white;
}

button.cancel {
    background-color: #dc3545;
    color: white;
}

button.search-btn {
    background-color: #007bff;
    color: white;
    padding: 8px 12px;
    font-size: 14px;
    border-radius: 5px;
}

button.clear-btn {
    background-color: #ffc107; 
    color: black;
    padding: 8px 12px;
    font-size: 14px;
    border-radius: 5px;
}

button.confirm {
    background-color: #28a745; 
    color: white;
}

button.delete {
    background-color: #dc3545; 
    color: white;
}


.search-bar {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 20px;
}

.search-bar input {
    flex: 1;
    min-width: 200px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

@media (max-width: 600px) {
    .search-bar input {
        font-size: 14px;
    }
    .search-bar button {
        font-size: 12px;
        padding: 8px;
    }
}


.page-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .page-grid {
        grid-template-columns: 1fr;
    }
}

.page-grid > div {
    background-color: #f8f9fa;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}


@media (max-width: 600px) {
    .modal {
        font-size: 14px;
        padding: 15px;
    }

    .form-group input {
        font-size: 14px;
    }
}


@media (max-width: 1024px) {
    .table-container {
        overflow-x: auto;
    }
}

@media (max-width: 600px) {
    th, td {
        font-size: 12px;
    }

    button {
        font-size: 12px;
        padding: 8px;
    }
}

.modal .success-message {
    color: #28a745;
    font-size: 14px;
    margin-top: 10px;
}

.modal .error-message {
    color: #dc3545;
    font-size: 14px;
    margin-top: 10px;
}



    </style>
</head>
<body>

    <div class="container">
        <h1>Manage Orders</h1>

        <div class="search-bar">
            <input type="text" id="search-email" placeholder="Search by student id" onkeyup="liveSearch()">
            <button type="button" onclick="window.location.href='admin.php'">Clear</button>
        </div>
        <button onclick="openModal()" style="margin-bottom:20px; background-color:#28a745; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Add Coupon Code</button>

        <table id="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Semester</th>
                    <th>ID</th>
                    <th>Phone</th>
                    <th>Size</th>
                    <th>Rope</th>
                    <th>Transaction ID</th>
                    <th>Price</th>
                    <th>status</th>

                    <th>Submitted At</th>
                    <th> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo htmlspecialchars($user['semester']); ?></td>
                        <td><?php echo htmlspecialchars($user['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['size']); ?></td>
                        <td><?php echo htmlspecialchars($user['rope_color']); ?></td>
                        <td><?php echo htmlspecialchars($user['transaction_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['price']); ?></td>

                        <td>
                            <span class="<?php echo $user['status'] === 'ban' ? 'status-banned' : 'status-active'; ?>">
                                <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                        <form method="POST" action="confirm_order.php" style="display:inline;">
                              <input type="hidden" name="order_id" value="<?php echo $user['id']; ?>">
                              <button type="submit" name="confirm_order" class="reactivate-button">Confirm</button>
                        </form>
                       <form method="POST" action="delete_order.php" style="display:inline;">
                              <input type="hidden" name="order_id" value="<?php echo $user['id']; ?>">
                            
                     
                             <button type="submit" name="delete_user" class="ban-button">Delete</button>
</form>

                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>

<div id="couponModal" class="modal">
    <div class="modal-content">
        <h3>Add New Coupon Code</h3>
        <form id="couponForm">
            <div class="form-group">
                <label for="coupon_code">Coupon Code:</label>
                <input type="text" id="coupon_code" name="coupon_code" placeholder="Enter coupon code" required>
            </div>
            <div class="info">
                <p>Total Coupons: <strong id="totalCoupons"><?php echo $totalCoupons; ?></strong></p>
                <p>Claimable Coupons: <strong id="claimableCoupons"><?php echo $claimableCoupons; ?></strong></p>
            </div>
            <div id="message"></div>
            <button type="submit" class="btn-primary">Add Coupon</button>
            <button type="button" onclick="closeModal()" class="btn-danger">Cancel</button>
        </form>
    </div>
</div>
<div id="overlay" class="overlay" onclick="closeModal()"></div>

    <script>
        function liveSearch() {
            const searchValue = document.getElementById('search-email').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'admin.php?search_email=' + searchValue, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(xhr.responseText, 'text/html');
                    const newTbody = doc.getElementById('users-table').querySelector('tbody');
                    document.getElementById('users-table').querySelector('tbody').innerHTML = newTbody.innerHTML;
                }
            };
            xhr.send();
        }
      
    
 
    function openModal() {
        document.getElementById('couponModal').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('couponModal').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('message').textContent = ''; 
    }


    document.getElementById('couponForm').addEventListener('submit', function (e) {
        e.preventDefault(); 

        const couponCode = document.getElementById('coupon_code').value;
        const message = document.getElementById('message');

        fetch('add_coupon.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `coupon_code=${encodeURIComponent(couponCode)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('success')) {
                message.textContent = 'Coupon added successfully!';
                message.className = 'success';
                document.getElementById('coupon_code').value = ''; 
            } else {
                message.textContent = 'Error adding coupon: ' + data;
                message.className = 'error';
            }
        })
        .catch(error => {
            message.textContent = 'An error occurred: ' + error.message;
            message.className = 'error';
        });
    });


window.addEventListener('resize', function() {
    const modal = document.getElementById('couponModal');
    if (modal.style.display === 'block') {
        const height = window.innerHeight * 0.8; // Use 80% of screen height
        modal.style.maxHeight = height + 'px';
    }
});


    </script>

</body>
</html>
