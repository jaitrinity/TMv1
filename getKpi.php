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
$roleId = $jsonData->roleId;

$sql = "SELECT * FROM `KpiMaster` where find_in_set($roleId,`RoleId`)";
$query = mysqli_query($conn,$sql);
$kpiList = array();
while($row = mysqli_fetch_assoc($query)){
	$kpi = $row["KPI"];
	$task = $row["Task"];
	$taskList = explode(",", $task);
	$kpiJson = array('kpi' => $kpi, 'taskList' => $taskList);
	array_push($kpiList, $kpiJson);
}
echo json_encode($kpiList);
?>