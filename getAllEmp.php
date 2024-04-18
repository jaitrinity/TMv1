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

$filterSql = "";
if($roleId != 1){
	$empIdArr = array();
	// getNlevelRm($empId);
	$sql = "SELECT e.EmpId FROM Employees e where e.RMId='$empId'";
	$query = mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$loopEmpId = $row["EmpId"];
		array_push($empIdArr, $loopEmpId);
	}

	if(count($empIdArr) !=0){
		$empIdImp = implode("','", $empIdArr);
		$filterSql .= "and e.`EmpId` in ('$empIdImp') ";
	}
	else{
		$filterSql .= "and e.`EmpId` in ('$empId') ";
	}
}

// $sql = "SELECT `EmpId` as `empId`, `Name` as `name` FROM `Employees` where `IsActive`=1 $filterSql order by `Name`";
$sql = "SELECT e.`EmpId` as `empId`, e.`Name` as `name`, e.`Email` as `email`, r.Role as `roleName`, e.RMId as `rmId`, e1.Name as rmName FROM Employees e left join Employees e1 on e.RMId=e1.EmpId join RoleMaster r on e.RoleId=r.RoleId where e.`IsActive`=1 $filterSql order by e.`Name`";
$query = mysqli_query($conn,$sql);
$empList = array();
while($row = mysqli_fetch_assoc($query)){
	array_push($empList, $row);
}
echo json_encode($empList);

?>

<?php
function getNlevelRm($empId){
	global $conn;
	global $empIdArr;
	$sql = "SELECT e.EmpId FROM Employees e left join Employees e1 on e.RMId=e1.EmpId where e.RMId='$empId'";
	$query = mysqli_query($conn,$sql);
	while($row = mysqli_fetch_assoc($query)){
		$loopEmpId = $row["EmpId"];
		array_push($empIdArr, $loopEmpId);
		getNlevelRm($loopEmpId);
	}
}
?>