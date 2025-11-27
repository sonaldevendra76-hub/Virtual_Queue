<?php
session_start();
include 'db_connection.php';
header('Content-Type: application/json');
error_reporting(0);

define('MAKE_WEBHOOK_URL', 'https://hook.eu2.make.com/xbbcrqmiehbqfpy0fgytlk8pg2mlx24r');
define('MAKE_API_KEY', 'mySecret123');

$pid = $_SESSION['patient_id'] ?? 0;
$doctor_id = $_SESSION['doctor_id'] ?? 0;
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function triggerMakeWebhook($data) {
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nx-make-apikey: " . MAKE_API_KEY . "\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context = stream_context_create($options);
    return @file_get_contents(MAKE_WEBHOOK_URL, false, $context);
}

// ---------------- PATIENT ACTIONS ----------------

if ($action == 'join' && $pid) {
    $did = intval($_POST['doctor_id'] ?? 0);
    if (!$did) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid doctor']); exit;
    }
    $check = mysqli_query($con, "SELECT * FROM queue WHERE patient_id='$pid' AND status!='completed'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Already in queue']); exit;
    }
    mysqli_query($con, "INSERT INTO queue (patient_id, doctor_id, status, created_at) VALUES ('$pid','$did','waiting',NOW())");
    echo json_encode(['status' => 'success']); exit;
}


if ($action == 'leave' && $pid) {
    mysqli_query($con, "DELETE FROM queue WHERE patient_id='$pid' AND status!='completed'");
    echo json_encode(['status' => 'success']); exit;
}

if ($action == 'check_doctor_selection' && $pid) {
    $doctors = [];
    $res = mysqli_query($con, "SELECT * FROM doctor");
    while ($row = mysqli_fetch_assoc($res)) $doctors[] = $row;

    $joined = false;
    $q = mysqli_query($con, "SELECT * FROM queue WHERE patient_id='$pid' AND status!='completed'");
    if (mysqli_num_rows($q)) $joined = true;

    echo json_encode(['doctors' => $doctors, 'joined' => $joined]);
    exit;
}


if ($action == 'fetch' && $pid) {
    $res = mysqli_query($con, "SELECT doctor_id FROM queue WHERE patient_id='$pid' ORDER BY created_at DESC LIMIT 1");
    $row = mysqli_fetch_assoc($res);
    $doctor_id_row = $row['doctor_id'] ?? null;

    if (!$doctor_id_row) {
        echo json_encode(['doctor' => null, 'queue' => []]); exit;
    }

    $q = mysqli_query($con, "SELECT q.qid,q.patient_id,q.status,q.created_at,p.pname,p.age,d.name AS doctor_name,d.specialization,d.address
        FROM queue q
        JOIN patient p ON q.patient_id=p.pid
        JOIN doctor d ON q.doctor_id=d.did
        WHERE q.doctor_id='$doctor_id_row'
        ORDER BY q.created_at ASC");

    $queue = [];
    $doctor = null;
    while ($row = mysqli_fetch_assoc($q)) {
        $doctor = ['name' => $row['doctor_name'], 'specialization' => $row['specialization'], 'address' => $row['address']];
        $queue[] = $row;
    }
    echo json_encode(['doctor' => $doctor, 'queue' => $queue]);
    exit;
}

// ---------------- DOCTOR ACTIONS ----------------

if ($action == 'fetch_doctor_queue') {
    $doctor_id = $_GET['doctor_id'] ?? ($_SESSION['doctor_id'] ?? 0);
    if (!$doctor_id) {
        echo json_encode(['queue' => []]); exit;
    }

    $q = mysqli_query($con, "SELECT q.qid,q.patient_id,q.status,q.created_at,p.pname,p.age
        FROM queue q
        JOIN patient p ON q.patient_id=p.pid
        WHERE q.doctor_id='$doctor_id'
        ORDER BY q.created_at ASC");

    $queue = [];
    while ($row = mysqli_fetch_assoc($q)) $queue[] = $row;
    echo json_encode(['queue' => $queue]);
    exit;
}

if ($action == 'mark_status') {
    $doctor_id = $_POST['doctor_id'] ?? ($_SESSION['doctor_id'] ?? 0);
    $qid = intval($_POST['qid'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!$qid || !$doctor_id || !in_array($status, ['inside', 'completed'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']); exit;
    }

    $res = mysqli_query($con, "SELECT * FROM queue WHERE qid='$qid' AND doctor_id='$doctor_id'");
    if (mysqli_num_rows($res) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Queue not found']); exit;
    }

    if ($status == 'completed') {
        
        mysqli_query($con, "UPDATE queue SET status='completed' WHERE qid='$qid' AND doctor_id='$doctor_id'");

        
        ignore_user_abort(true);
        register_shutdown_function(function() use ($con, $qid, $doctor_id) {
            sleep(1);
            mysqli_query($con, "DELETE FROM queue WHERE qid='$qid' AND doctor_id='$doctor_id'");
        });
    } else {
        mysqli_query($con, "UPDATE queue SET status='$status' WHERE qid='$qid'");
    }

    
    $next_res = mysqli_query($con, "SELECT q.qid, p.pname, p.pmobile 
        FROM queue q
        JOIN patient p ON q.patient_id=p.pid
        WHERE q.doctor_id='$doctor_id' AND q.status='waiting' AND (q.sms_sent=0 OR q.sms_sent IS NULL)
        ORDER BY q.qid ASC LIMIT 1");

    if ($next = $next_res->fetch_assoc()) {
        $payload = [
            'patient_name' => $next['pname'],
            'patient_mobile' => $next['pmobile'],
            'doctor_id' => $doctor_id,
            'queue_id' => $next['qid']
        ];
        $result = triggerMakeWebhook($payload);
        if ($result !== false) {
            mysqli_query($con, "UPDATE queue SET sms_sent=1 WHERE qid={$next['qid']}");
        }
    }

    echo json_encode(['status' => 'success']);
    exit;
}
?>
