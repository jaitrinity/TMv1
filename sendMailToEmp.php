<?php 
// crontab run on Saturday night.
include("dbConfiguration.php");
require 'SendMailClass.php';

$sendMailObj = new SendMailClass();

$currentDate = date('Y-m-d');
// $currentDate = "2024-02-10";
$toDate = date('Y-m-d',strtotime("$currentDate -5 days"));

$subject="No any task found for this week";

$sql="SELECT e.EmpId, e.Email, count(mt.EmpId) as Done FROM Employees e left join MyTask mt on e.EmpId=mt.EmpId and date(mt.CreateDate)>='$currentDate' and date(mt.CreateDate)<='$toDate' GROUP by e.EmpId";
// $sql="SELECT e.EmpId, e.Name, e.Email, count(mt.EmpId) as TaskCount FROM Employees e left join MyTask mt on e.EmpId=mt.EmpId and date(mt.CreateDate)>='$currentDate' and date(mt.CreateDate)<='$toDate' where e.EmpId in ('tr01','tr03') GROUP by e.EmpId";

$query = mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$name = $row["Name"];
	$toEmailId = $row["Email"];
	$taskCount = $row["TaskCount"];
	if($taskCount == 0){
		$msg="Dear $name,<br><br>
		You have not submit any task at this week, please do your task on priority.";
		$sendMailStatus = $sendMailObj->sendMail($toEmailId, $subject, $msg, null);
	}
}

?>
