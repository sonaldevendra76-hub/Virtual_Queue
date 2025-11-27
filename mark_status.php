<?php
session_start();
include 'db_connection.php';
header('Content-Type: application/json');

$qid = $_POST['qid'] ?? 0;
$status = $_POST['status'] ?? '';

if(!$qid || !in_array($status,['inside','completed'])) exit(json_encode(['status'=>'error','message'=>'Invalid data']));

$res = mysqli_query($con,"UPDATE queue SET status='$status' WHERE qid='$qid'");
if($res) echo json_encode(['status'=>'success']);
else echo json_encode(['status'=>'error','message'=>mysqli_error($con)]);
?>
