<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = mysqli_connect("localhost", "root", "", "virtual_queue");
if (!$con) die("Connection failed: " . mysqli_connect_error());

$error = "";

if (isset($_POST['login'])) {
    $demail = trim($_POST['demail']);
    $dpass  = trim($_POST['dpass']);

    
    $query = "SELECT * FROM doctor WHERE email='$demail' AND password='$dpass'";
    $result = mysqli_query($con, $query);
    if (!$result) die("Query Failed: " . mysqli_error($con));

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['doctor_id'] = $row['did'];        
        $_SESSION['doctor_name'] = $row['name'];     
        $_SESSION['doctor_email'] = $demail;
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        $error = "❌ Invalid Email or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Login</title>
<style>
body { background: linear-gradient(90deg, #45caf7ff, rgba(12, 140, 232, 1)); display: flex; justify-content: center; align-items: center; height: 100vh; font-family: Arial, sans-serif; position: relative; margin:0; }
.login-container { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); width: 350px; text-align: center; }
input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc; }
button { padding: 10px 20px; border: none; border-radius: 8px; background: #007bff; color: #fff; cursor: pointer; }
button:hover { background: #0056b3; }
.error { color: red; }


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
    <h2>Doctor Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form action="" method="POST">
        <input type="email" name="demail" placeholder="Enter Email" required>
        <input type="password" name="dpass" placeholder="Enter Password" required>
        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>
