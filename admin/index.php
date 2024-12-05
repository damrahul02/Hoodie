<?php
include('../connection.php');
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
    <link rel="stylesheet" href="style.css">
   
</head>
<body>

    <div class="container">
        <h1>Manage Orders</h1>

        <div class="search-bar">
            <input type="text" id="search-email" placeholder="Search by student id" onkeyup="liveSearch()">
            <button type="button" onclick="window.location.href='index.php'">Clear</button>
        </div>
        <button onclick="openModal()" style="margin-bottom:20px; background-color:#28a745; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Add Coupon Code</button>
        <button onclick="openCouponsModal()" style="margin-bottom:20px; background-color:#28a745; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">view all the coupons</button>

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

<!-- Add Coupon Modal -->
<div id="addCouponModal" class="modal">
    <div class="modal-content">
        <h3>Add New Coupon Code</h3>
        <form id="addCouponForm" method="POST" action="add_coupon.php">
            <!-- Coupon Code Input -->
            <div class="form-group">
                <label for="couponCode">Coupon Code:</label>
                <input type="text" id="couponCode" name="couponCode" placeholder="Enter coupon code" required>
            </div>
            
            <!-- Limit Input -->
            <div class="form-group">
                <label for="couponLimit">Limit:</label>
                <input type="number" id="couponLimit" name="couponLimit" placeholder="Enter claim limit" min="1" required>
            </div>
            
            <!-- Submit and Cancel Buttons -->
            <div class="form-buttons">
                <button type="submit" class="add-coupon">Add Coupon</button>
                <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
            </div>

            <!-- Success and Error Messages -->
            <div id="couponMessages">
                <p class="success-message" style="display: none;">Coupon added successfully!</p>
                <p class="error-message" style="display: none;">Failed to add the coupon. Please try again.</p>
            </div>
        </form>
    </div>
</div>

<div id="couponsModal" class="modal">
    <div class="modal-content">
        <h3>All Coupons</h3>
        <table class="coupons-table">
            <thead>
                <tr>
                    <th>Coupon Code</th>
                    <th>Limit</th>
                    <th>Claimed</th>
                 
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="couponsTableBody">
            
            </tbody>
        </table>
        <div class="form-buttons">
            <button class="cancel" onclick="closeCouponsModal()">Close</button>
        </div>
    </div>
</div>


    <script>
        function liveSearch() {
            const searchValue = document.getElementById('search-email').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'index.php?search_email=' + searchValue, true);
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
    document.getElementById("addCouponModal").style.display = "block";
}

// Close the modal
function closeModal() {
    document.getElementById("addCouponModal").style.display = "none";
}

// AJAX Submission for Adding Coupons
document.getElementById("addCouponForm").addEventListener("submit", function (event) {
    event.preventDefault();
    
    const formData = new FormData(this);
    fetch("add_coupon.php", {
        method: "POST",
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            const successMessage = document.querySelector(".success-message");
            const errorMessage = document.querySelector(".error-message");

            if (data.success) {
                successMessage.style.display = "block";
                errorMessage.style.display = "none";
                this.reset(); // Clear the form
            } else {
                successMessage.style.display = "none";
                errorMessage.style.display = "block";
                errorMessage.textContent = data.message;
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
});

window.addEventListener('resize', function() {
    const modal = document.getElementById('couponModal');
    if (modal.style.display === 'block') {
        const height = window.innerHeight * 0.8; // Use 80% of screen height
        modal.style.maxHeight = height + 'px';
    }
});

function openCouponsModal() {
    const modal = document.getElementById("couponsModal");
    const tableBody = document.getElementById("couponsTableBody");

    fetch("coupons.php")
        .then(response => response.json())
        .then(coupons => {
            tableBody.innerHTML = ""; 
            coupons.forEach(coupon => {
                const row = document.createElement("tr");

                row.innerHTML = `
                    <td>${coupon.code}</td>
                    <td><input type="number" value="${coupon.total_coupons}" min="1" onchange="updateCouponLimit(${coupon.id}, this.value)"></td>
                    <td>${coupon.count}</td>
               
                    <td>
                        <button class="delete-btn" onclick="deleteCoupon(${coupon.id})">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error("Error fetching coupons:", error));

    modal.style.display = "block";
}


function closeCouponsModal() {
    document.getElementById("couponsModal").style.display = "none";
}

function deleteCoupon(couponId) {
    if (confirm("Are you sure you want to delete this coupon?")) {
        fetch("manage_coupons.php", {
            method: "POST",
            body: new URLSearchParams({ action: "delete", couponId }),
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) openCouponsModal(); 
            })
            .catch(error => console.error("Error deleting coupon:", error));
    }
}


function updateCouponLimit(couponId, newLimit) {
    fetch("manage_coupons.php", {
        method: "POST",
        body: new URLSearchParams({ action: "update", couponId, newLimit }),
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        })
        .catch(error => console.error("Error updating coupon:", error));
}

    </script>

</body>
</html>
