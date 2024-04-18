<?php
include("dbConfiguration.php");

$sql = "SELECT `RoleId` as `roleId`, `Role` as `roleName` FROM `RoleMaster`";
$query = mysqli_query($conn,$sql);
$empList = array();
while($row = mysqli_fetch_assoc($query)){
	array_push($empList, $row);
}
echo json_encode($empList);
?>