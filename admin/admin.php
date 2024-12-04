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
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
        }

        .search-bar input[type="text"] {
            padding: 12px 18px;
            font-size: 16px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 50px;
            margin-right: 10px;
            transition: width 0.4s ease;
        }

        .search-bar input[type="text"]:focus {
            width: 400px;
            border-color: #007bff;
        }

        .search-bar button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-bar button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
            color: #333;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .reactivate-button,
       .ban-button {
    padding: 8px 15px;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-right: 5px; 
    margin-top:5px;
}

        .ban-button {
    background-color: #dc3545;
    color: white;
}

.ban-button:hover {
    background-color: #c82333;
}

.reactivate-button {
    background-color: #28a745;
    color: white;
}

.reactivate-button:hover {
    background-color: #218838;
}

        .status-active {
            color: green;
            font-weight: bold;
        }

        .status-banned {
            color: red;
            font-weight: bold;
        }



.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    width: 400px;
    max-width: 90%;
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

.info p {
    margin: 5px 0;
    font-size: 14px;
}


button {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
    margin-right: 10px;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
    transition: background-color 0.3s ease;
}

.btn-danger:hover {
    background-color: #c82333;
}

.overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

#message {
    margin-top: 10px;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
}

#message.success {
    color: green;
}

#message.error {
    color: red;
}


    </style>
</head>
<body>

    <div class="container">
        <h1>Manage Orders</h1>

        <div class="search-bar">
            <input type="text" id="search-email" placeholder="Search by student id" onkeyup="liveSearch()">
            <button type="button" onclick="window.location.href='manage_users.php'">Clear</button>
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



    </script>

</body>
</html>
