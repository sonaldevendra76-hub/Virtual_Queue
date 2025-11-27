<?php
session_start();
include 'db_connection.php';

// If a patient logs out, remove their active queue record
if (isset($_SESSION['patient_id'])) {
    $pid = $_SESSION['patient_id'];
    mysqli_query($con, "DELETE FROM queue WHERE patient_id='$pid' AND status!='completed'");
}

session_destroy();
header("Location: index.html");
exit;
?>
