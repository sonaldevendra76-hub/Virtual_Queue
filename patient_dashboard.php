<?php
session_start();
include 'db_connection.php';
$pid = $_SESSION['patient_id'] ?? 0;
$pname = $_SESSION['patient_name'] ?? '';
if(!$pid) header("Location: patient_login.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Dashboard</title>
<style>
body{font-family:Arial;background:#f2f2f2;margin:0;padding:0;}
.container{width:900px;margin:50px auto;padding:20px;background:#fff;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.logout{float:right;background:red;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;}
h2{margin-top:0;}
.doctor-card{background:#f9f9f9;padding:15px;margin:15px 0;border-radius:8px;}
button{padding:5px 10px;border:none;border-radius:6px;cursor:pointer;margin:2px;}
.join-btn{background:#007bff;color:#fff;}
.leave-btn{background:red;color:#fff;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #ddd;padding:8px;text-align:center;}
th{background:#007bff;color:#fff;}
.waiting{background:#e2f0ff;}
.inside{background:#ffeeba;}
.completed{background:#d4edda;}
.Back a{position:absolute;top:20px;left:20px;text-decoration:none;font-size:18px;font-weight:bold;color:black;}
.Back a:hover{color:#007bff;}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="Back"><a href="index.html">← Go Back</a></div>
<div class="container">
<a href="logout.php" class="logout">Logout</a>
<h2>Welcome, <?= htmlspecialchars($pname) ?></h2>

<div id="doctor_selection"></div>
<div id="queue_container" style="display:none;"></div>
</div>

<script>
let pid = <?= $pid ?>;

function joinQueue(did){
    $.post('queue_action.php',{action:'join',doctor_id:did},function(data){
        if(data.status=='success'){ $('#doctor_selection').hide(); loadQueue(); }
        else alert(data.message||'Failed to join queue.');
    },'json');
}

function leaveQueue(){
    $.post('queue_action.php',{action:'leave'},function(data){
        $('#queue_container').hide();
        loadDashboard();
    },'json');
}

function loadDoctors(doctors){
    let html='';
    doctors.forEach(d=>{
        html+='<div class="doctor-card">';
        html+='<h3>'+d.name+'</h3>';
        html+='<p><strong>Specialization:</strong> '+d.specialization+'</p>';
        html+='<p><strong>Clinic Address:</strong> '+d.address+'</p>';
        html+='<button class="join-btn" onclick="joinQueue('+d.did+')">Join Queue</button>';
        html+='</div>';
    });
    $('#doctor_selection').html(html).show();
}

function loadDashboard(){
    $.get('queue_action.php',{action:'check_doctor_selection'},function(data){
        if(data.joined){ $('#doctor_selection').hide(); loadQueue(); }
        else loadDoctors(data.doctors);
    },'json');
}

function loadQueue(){
    $.get('queue_action.php',{action:'fetch'},function(data){
        if(!data.doctor) return;
        let html='<div class="doctor-card">';
        html+='<h3>'+data.doctor.name+'</h3>';
        html+='<p><strong>Specialization:</strong> '+data.doctor.specialization+'</p>';
        html+='<p><strong>Clinic Address:</strong> '+data.doctor.address+'</p>';
        html+='<p>Waiting Patients: '+data.queue.length+'</p>';

        let pos=0, in_queue=false;
        data.queue.forEach((p,i)=>{ if(p.patient_id==pid){ pos=i+1; if(p.status=='waiting'||p.status=='inside') in_queue=true; } });
        if(pos) html+='<p>Your Position: '+pos+'</p>';
        if(pos) html+='<p>Your Status: '+data.queue[pos-1].status.charAt(0).toUpperCase()+data.queue[pos-1].status.slice(1)+'</p>';
        if(in_queue) html+='<button class="leave-btn" onclick="leaveQueue()">Leave Queue</button>';

        html+='<h4>Current Queue</h4><table><tr><th>Token</th><th>Patient Name</th><th>Age</th><th>Joined At</th><th>Status</th></tr>';
        data.queue.forEach((p,i)=>{
            html+='<tr class="'+p.status+'">';
            html+='<td>'+(i+1)+'</td><td>'+p.pname+'</td><td>'+p.age+'</td><td>'+p.created_at+'</td><td>'+p.status.charAt(0).toUpperCase()+p.status.slice(1)+'</td>';
            html+='</tr>';
        });
        html+='</table></div>';
        $('#queue_container').html(html).show();
    },'json');
}

setInterval(function(){ if($('#queue_container').is(':visible')) loadQueue(); },2000);
loadDashboard();
</script>
</body>
</html>