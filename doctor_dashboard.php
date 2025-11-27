<?php
session_start();
include 'db_connection.php';

mysqli_query($con, "DELETE FROM queue WHERE patient_id NOT IN (SELECT pid FROM patient)");

$doctor_id = $_SESSION['doctor_id'] ?? 0;
$doctor_name = $_SESSION['doctor_name'] ?? '';

if(!$doctor_id) header("Location: doctor_login.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Dashboard</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
    margin: 0;
    padding: 0;
}
.container {
    width: 900px;
    margin: 50px auto;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.logout {
    float: right;
    background: red;
    color: #fff;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
}
h2 {
    margin-top: 0;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    padding: 8px;
    border: 1px solid #ddd;
    text-align: center;
}
th {
    background: #007bff;
    color: #fff;
}
button {
    padding: 5px 8px;
    border: none;
    border-radius: 6px;
    margin: 2px;
    cursor: pointer;
}
.inside-btn { background: #ffc107; color: #000; }
.completed-btn { background: #28a745; color: #fff; }
.waiting { background: #e2f0ff; }
.inside { background: #ffeeba; }
.completed { background: #d4edda; }
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="Back"><a href="index.html">← Go Back</a></div>
<div class="container">
<a href="logout.php" class="logout">Logout</a>
<h2>Welcome, Dr. <?= htmlspecialchars($doctor_name) ?></h2>

<div id="queue_container"></div>
</div>

<script>
let doctor_id = <?= $doctor_id ?>;

function markStatus(qid, status) {
    $.post('queue_action.php', 
        { action: 'mark_status', qid: qid, status: status, doctor_id: doctor_id }, 
        function(data) {
            if (data.status === 'success') loadQueue();
            else alert(data.message || 'Failed to update');
        }, 
        'json'
    );
}

function loadQueue() {
    $.get('queue_action.php', 
        { action: 'fetch_doctor_queue', doctor_id: doctor_id, t: Date.now() }, 
        function(data) {
            if (!data.queue) return;
            let html = '<table><tr><th>Token</th><th>Patient Name</th><th>Age</th><th>Joined At</th><th>Status</th><th>Action</th></tr>';
            
            data.queue.forEach(function(p, i) {
                html += '<tr class="' + p.status + '">';
                html += '<td>' + (i + 1) + '</td><td>' + p.pname + '</td><td>' + p.age + '</td><td>' + p.created_at + '</td>';
                html += '<td>' + p.status.charAt(0).toUpperCase() + p.status.slice(1) + '</td><td>';
                
                if (p.status === 'waiting') {
                    html += '<button class="inside-btn" onclick="markStatus(' + p.qid + ',\'inside\')">Mark Inside</button>';
                    html += '<button class="completed-btn" onclick="markStatus(' + p.qid + ',\'completed\')">Mark Completed</button>';
                } else if (p.status === 'inside') {
                    html += '<button class="completed-btn" onclick="markStatus(' + p.qid + ',\'completed\')">Mark Completed</button>';
                } else {
                    html += '-';
                }
                html += '</td></tr>';
            });
            html += '</table>';
            $('#queue_container').html(html);
        }, 'json'
    );
}

setInterval(loadQueue, 2000);
loadQueue();
</script>
</body>
</html>
