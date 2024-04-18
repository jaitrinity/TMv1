<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	$output = array('code' => 405, 'message' => 'Invalid method Type');
	echo json_encode($output);
	return;
}
$insertType = $_REQUEST["insertType"];

$json = file_get_contents('php://input');
$jsonData=json_decode($json);

if($insertType == "employee"){
	$name = $jsonData->name;
	$emailId = $jsonData->emailId;
	$roleId = $jsonData->roleId;
	$rmId = $jsonData->rmId;

	$sql = "SELECT * from `Employees` where `Email` = ? and `IsActive` = 1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $emailId);
	$stmt->execute();
	$query = $stmt->get_result();
	$rowCount = mysqli_num_rows($query);
	if($rowCount != 0){
		$output = array(
			'code' => 204, 
			'message' => "Employee already exist on $emailId"
		);
		echo json_encode($output);
		return;
	}

	$configSql = "SELECT (`EmpCount`+1) as `EmpCount` FROM `Configuration`";
	$configQuery = mysqli_query($conn,$configSql);
	$configRow = mysqli_fetch_assoc($configQuery);
	$empCount = $configRow["EmpCount"];
	$employeeId = 'tr'.$empCount;
	$passTxt = rand();
	$password = base64_encode($passTxt);

	$sql = "INSERT INTO `Employees`(`EmpId`, `Name`, `Email`, `Password`, `RoleId`, `RMId`) VALUES (?,?,?,?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssssis", $employeeId, $name, $emailId, $password, $roleId, $rmId);
	if($stmt->execute()){

		$output = array(
			'code' => 200, 
			'message' => "Employee successfully inserted"
		);
		echo json_encode($output);

		$updateConfig = "UPDATE `Configuration` set `EmpCount` = $empCount";
		mysqli_query($conn,$updateConfig);

		$subject = "New Employee";
		$msg = "Dear $name,<br><br>";
		$msg .= "Your portal login password is $passTxt, you can change this password from portal at anytime.<br>";
		$msg .= "<a href='https://trinityapplab.in/ThreatModeler_v1/#/login'>Click Here</a> to going to portal.<br><br>";
		$msg .= "Regards<br>";
		$msg .= "Trinity automation team.";

		require 'SendMailClass.php';
		$sendMailObj = new SendMailClass();
		$sendMailStatus = $sendMailObj->sendMail($emailId, $subject, $msg, null);

		if($rmId !=""){
			$empSql = "SELECT `Name`, `Email` FROM `Employees` where `EmpId`='$rmId' and `IsActive`=1";
			$empQuery = mysqli_query($conn,$empSql);
			$empRow = mysqli_fetch_assoc($empQuery);
			$rmName = $empRow["Name"];
			$rmEmail = $empRow["Email"];

			$subject1 = "New employee in your hierarchy";
			$msg1 = "Dear $rmName,<br><br>";
			$msg1 .= "New employee is added in your hierarchy, plz do as needful. <br><br>";
			$msg1 .= "Regards<br>";
			$msg1 .= "Trinity automation team.";
			$sendMailStatus = $sendMailObj->sendMail($rmEmail, $subject1, $msg1, null);
		}
	}
}
?>