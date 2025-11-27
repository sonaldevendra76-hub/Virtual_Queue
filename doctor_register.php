<?php
$con = mysqli_connect('localhost','root','','virtual_queue');
if (isset($_POST['sub'])) {
    $name          = $_POST['name'];
    $specialization= $_POST['specialization'];
    $address       = $_POST['address'];
    $phone         = $_POST['phone'];
    $email         = $_POST['email'];
    $password      = $_POST['password'];
    $from_hour   = $_POST['available_from_hour'];
    $from_minute = $_POST['available_from_minute'];
    $from_ampm   = $_POST['available_from_ampm'];

    $to_hour     = $_POST['available_to_hour'];
    $to_minute   = $_POST['available_to_minute'];
    $to_ampm     = $_POST['available_to_ampm'];

    $available_from = sprintf("%02d:%02d %s", $from_hour, $from_minute, $from_ampm);
    $available_to   = sprintf("%02d:%02d %s", $to_hour, $to_minute, $to_ampm);

    $query = "INSERT INTO doctor
        (name, specialization, address, mobile, email, password, available_from, available_to) 
        VALUES 
        ('$name','$specialization','$address','$phone','$email','$password','$available_from','$available_to')";

    $execute = mysqli_query($con, $query);

    if ($execute) {
       header("Location: doctor_login.php?msg=registered");
        exit();
    } else {
        echo "❌ Error: " . mysqli_error($con);
    }

}

?>
