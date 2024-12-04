<?php
// Database connection
$servername = "localhost";
$username = "root";   // Default username for XAMPP
$password = "";       // Default password for XAMPP
$dbname = "hoodie_orders"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $name = $_POST['name'];
    $semester = $_POST['semester'];
    $student_id = $_POST['id'];
    $phone = $_POST['phone'];
    $size = $_POST['size'];
    $rope_color = $_POST['rope'];
    $transaction_id = $_POST['transaction_id'];

    // Set price based on the selected rope color
    $price = ($rope_color === 'white') ? 560 : 550;  // 10 BDT extra for white rope color

    // Get the current date and time for `created_at`
    $created_at = date('Y-m-d H:i:s');

    // Sanitize input to prevent SQL injection
    $name = $conn->real_escape_string($name);
    $semester = $conn->real_escape_string($semester);
    $student_id = $conn->real_escape_string($student_id);
    $phone = $conn->real_escape_string($phone);
    $size = $conn->real_escape_string($size);
    $rope_color = $conn->real_escape_string($rope_color);
    $transaction_id = $conn->real_escape_string($transaction_id);

    // SQL query with placeholders
    $sql = "INSERT INTO orders (name, semester, student_id, size, phone_number, rope_color, transaction_id, price, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("sssssssds", $name, $semester, $student_id, $size, $phone, $rope_color, $transaction_id, $price, $created_at);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to a confirmation page or show success message
        echo "<script>alert('Order placed successfully!'); window.location.href='index.html';</script>";
    } else {
        // Display error if query fails
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();

    // Close the connection
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
