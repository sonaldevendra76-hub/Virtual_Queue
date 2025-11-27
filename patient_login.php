<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = mysqli_connect('localhost', 'root', '', 'virtual_queue');
if (!$con) die("Connection failed: " . mysqli_connect_error());

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $mobile = trim($_POST['pmobile']);
    $password = trim($_POST['password']);

    if (empty($mobile) || empty($password)) {
        $error = "❌ Please enter both mobile number and password!";
    } else {
        $mobile = mysqli_real_escape_string($con, $mobile);
        $sql = "SELECT * FROM patient WHERE pmobile='$mobile'";
        $result = mysqli_query($con, $sql);

        if (!$result) die("Query failed: " . mysqli_error($con));

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($password, $row['password'])) {
                $_SESSION['patient_id'] = $row['pid'];
                $_SESSION['patient_name'] = $row['pname'];
                $_SESSION['doctor_id'] = $row['doctor_id'];

                header("Location: patient_dashboard.php");
                exit;
            } else {
                $error = "❌ Incorrect password!";
            }
        } else {
            $error = "❌ No account found with this mobile!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Login</title>
<style>
body {
    background: linear-gradient(90deg,#45caf7ff,rgba(12,140,232,1));
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    font-family:Arial,sans-serif;
    position: relative;
    margin:0;
}
.login-container {
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    width:350px;
    text-align:center;
}
input {
    width:100%;
    padding:10px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
}
button {
    padding:10px 20px;
    border:none;
    border-radius:8px;
    background:#007bff;
    color:#fff;
    cursor:pointer;
}
button:hover { background:#0056b3; }
.error { color:red; }

/* Go Back button */
.Back a {
    position: absolute;
    top: 20px;
    left: 20px;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
    color: black;
}
.Back a:hover { color: #007bff; }
</style>
</head>
<body>
<div class="Back">
    <a href="index.html">← Go Back</a>
</div>

<div class="login-container">
    <h2>Patient Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form action="" method="POST">
        <input name="pmobile" placeholder="Enter Mobile Number" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>
