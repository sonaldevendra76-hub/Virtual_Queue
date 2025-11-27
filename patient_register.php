<?php

$host = "localhost";
$user = "root";        // default XAMPP user
$pass = "";            // default XAMPP password is empty
$db   = "virtual_queue";

$conn = new mysqli($host, $user, $pass, $db);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $pname    = trim($_POST['pname']);
    $paddress = trim($_POST['paddress']);
    $pmobile  = trim($_POST['pmobile']);
    $age      = intval($_POST['age']);
    $password = $_POST['password'];

    if (empty($pname) || empty($paddress) || empty($pmobile) || empty($age) || empty($password)) {
        die("❌ All fields are required!");
    }

    
    $check = $conn->prepare("SELECT * FROM patient WHERE pmobile = ?");
    $check->bind_param("s", $pmobile);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        die("❌ This mobile number is already registered!");
    }
    $check->close();

    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    
    $stmt = $conn->prepare("INSERT INTO patient (pname, paddress, pmobile, age, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $pname, $paddress, $pmobile, $age, $hashed_password);

    if ($stmt->execute()) {
        
        header("Location: patient_login.php?registered=success");
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
