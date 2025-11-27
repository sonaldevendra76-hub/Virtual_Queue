<?php
session_start();
include __DIR__.'/db_connection.php';

if(!isset($_SESSION['patient_id']) || !isset($_SESSION['doctor_id'])) exit;

$doctor_id = intval($_SESSION['doctor_id']);

$sql = "SELECT q.patient_id, p.pname AS patient_name, p.age, q.created_at, q.status 
        FROM queue q
        JOIN patient p ON q.patient_id = p.pid
        WHERE q.doctor_id=$doctor_id AND q.status!='completed'
        ORDER BY q.created_at ASC";

$result = mysqli_query($con, $sql);
if(!$result){
    die("Query failed: ".mysqli_error($con));
}
?>

<table class="table table-bordered table-striped">
<thead class="table-primary">
<tr>
<th>Token</th>
<th>Patient Name</th>
<th>Age</th>
<th>Joined At</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php
$counter=1;
while($row=mysqli_fetch_assoc($result)){
    echo "<tr>";
    echo "<td>".$counter."</td>";
    echo "<td>".htmlspecialchars($row['patient_name'])."</td>";
    echo "<td>".htmlspecialchars($row['age'])."</td>";
    echo "<td>".htmlspecialchars($row['created_at'])."</td>";
    echo "<td>".htmlspecialchars($row['status'])."</td>";
    echo "</tr>";
    $counter++;
}
?>
</tbody>
</table>
