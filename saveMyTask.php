<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$empId = $jsonData->empId;
$project = $jsonData->project;
$week = $jsonData->week;
if($week == "Yesterday"){
	$yesterday = date('d-M-Y', strtotime('-1 day'));
	// $week .= ' ('.$yesterday.')';
	$week = $yesterday;
}
else if($week == "Today"){
	$currentDate = date('d-M-Y');
	// $week .= ' ('.$currentDate.')';
	$week = $currentDate;
}
$noOfTask = $jsonData->noOfTask;
$kpi = $jsonData->kpi;
$taskList = $jsonData->taskList;
$taskId = 0;

$rmSuSql = "SELECT e.Name as EmpName,  e.RMId, e1.Name as RmName, e1.Email as RmEmail, e1.RMId as SUId FROM Employees e left join Employees e1 on e.RMId = e1.EmpId where e.EmpId = '$empId'";
$rmSuQuery = mysqli_query($conn,$rmSuSql);
$rowCount=mysqli_num_rows($rmSuQuery);
$empName="";
$rmName="";
$rmEmailId="";
$rmId="";
$suId="";
if($rowCount != 0){
	$rmSuRow = mysqli_fetch_assoc($rmSuQuery);
	$empName = $rmSuRow["EmpName"];
	$rmId = $rmSuRow["RMId"];
	$rmName = $rmSuRow["RmName"];
	$rmEmailId = $rmSuRow["RmEmail"];
	$suId = $rmSuRow["SUId"];
}
$sql = "INSERT INTO `MyTask`(`EmpId`, `ProjectName`, `KPI`, `Week`, `NoOfTask`, `RM_EmpId`, `SU_EmpId`) VALUES ('$empId', '$project', '$kpi', '$week', $noOfTask, '$rmId', '$suId')";
$code = 0;
$message = "";
$fontFamily = 'font-family: "Times New Roman", Times, serif';
$subTaskMsg="<table border=1 cellpadding=5 cellspacing=0 style='$fontFamily;font-size:14px'>
			<thead>
				<tr style='background-color:#2E57A7;color:white'>
					<th>SR</th>
					<th>Task Name</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>";
if(mysqli_query($conn,$sql)){
	$taskId = $conn->insert_id;

	$status = false;
	$table="INSERT INTO `SubTask`(`TaskId`, `SrNo`, `TaskName`, `Subject`, `TaskDescription`) ";
	for($i=0;$i<count($taskList);$i++){
		$taskObj = $taskList[$i];
		$srNo = $taskObj->srNo;
		$taskName = $taskObj->taskName;
		$subject = '';
		$taskDesc = $taskObj->task;
		$data = "($taskId, $srNo, '$taskName', '$subject', ?)";
		$insertSubTask = $table." VALUES ".$data;

		$stmt = $conn->prepare($insertSubTask);
		$stmt->bind_param("s", $taskDesc);
		if($stmt->execute()){
			$status = true;
			$subTaskMsg .= "<tr>
								<td>$srNo</td>
								<td>$taskName</td>
								<td>$taskDesc</td>
							</tr>";
		}
		else{
			$status = false;
			$message = "Something wrong while insert data of task $srNo in `SubTask` table";
			break;
		}
	}
	$subTaskMsg .= "</tbody>
			</table>";

	// echo $insertSubTask;
	if($status){
		$code = 200;
		$message = "Success";

		// $taskRep = "UPDATE `TaskReport` SET `$week`=1 WHERE `EmpId`='$empId'";
		// $taskRepStmt = $conn->prepare($taskRep);
		// $taskRepStmt->execute();

		require 'LoggerClass.php';
		$classObj = new LoggerClass();
		$classObj->insertLog($empId, "Task Submit");
	}
	else{
		$code = 500;
		$message = $message;

		$delSubTask = "DELETE FROM `SubTask` WHERE `TaskId`='$taskId'";
		mysqli_query($conn,$delSubTask);

		$delTask = "DELETE FROM `MyTask` WHERE `TaskId`='$taskId'";
		mysqli_query($conn,$delTask);

		$taskId = 0;

	}
}
else{
	$code = 500;
	$message = "Something wrong while insert data in `MyTask` table";
}
$output = array(
	'code' => $code,
	'message' => $message,
	'taskId' => $taskId
);
echo json_encode($output);

if($code == 200){
	$subject="New Task";
	$msg = "Dear $rmName, <br><br>
	Below task is submitted by <b>$empName</b>:<br><br>
	$subTaskMsg<br><br>
	Regards<br>
	Trinity automation team.";

	require 'SendMailClass.php';
	$sendMailObj = new SendMailClass();
	$sendMailStatus = $sendMailObj->sendMail($rmEmailId, $subject, $msg, null);
}
?>