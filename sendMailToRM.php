<?php 
// crontab run on sunday night
include("dbConfiguration.php");
require 'SendMailClass.php';

$fontFamily = 'font-family: "Times New Roman", Times, serif';
$sendMailObj = new SendMailClass();

$currentDate = date('Y-m-d');
// $currentDate = "2024-02-12";
$date = new DateTime($currentDate);
$year = $date->format("Y");
$weekNo = $date->format("W");

$week_array = getStartAndEndDate($weekNo,$year);
$startWeekDate = $week_array["week_start"];
$endWeekDate = $week_array["week_end"];

$startDate = date("d-M-Y", strtotime($startWeekDate));
$endDate = date("d-M-Y", strtotime($endWeekDate));

$sql = "SELECT DISTINCT e1.EmpId, e1.Name, e1.Email FROM Employees e join Employees e1 on e.RMId = e1.EmpId where e.RMId is not null";
// $sql = "SELECT DISTINCT e1.EmpId, e1.Name, e1.Email FROM Employees e join Employees e1 on e.RMId = e1.EmpId where e.RMId='tr57'";
$query = mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$rmEmpId = $row["EmpId"];
	$rmName = $row["Name"];
	$rmEmailId = $row["Email"];
	// $rmEmailId = "ayush.agarwal@trinityapplab.co.in,jai.prakash@trinityapplab.co.in";
	// $rmEmailId = "jai.prakash@trinityapplab.co.in";

	$mtSql = "SELECT e.EmpId, e.Name, (case when count(mt.EmpId) != 0 then 'Yes' else 'No' end) as TaskSubmit FROM Employees e left join MyTask mt on e.EmpId = mt.EmpId and date(mt.CreateDate)>='$startWeekDate' and date(mt.CreateDate)<='$endWeekDate' where e.RMId = '$rmEmpId'  GROUP by e.EmpId";
	$mtQuery = mysqli_query($conn,$mtSql);

	$subject = "Task submit summary of week $weekNo";
	$msg = "Dear $rmName,<br>";
	$msg .= "Please find task submit summary of your employee of week $weekNo ($startDate - $endDate): <br><br>";
	$msg .= "<table border=1 cellpadding=5 cellspacing=0 style='$fontFamily;font-size:14px'>
		<thead>
			<tr style='background-color:#2E57A7;color:white'>
				<th>Employee Name</th>
				<th>Task Submit</th>
			</tr>
		</thead>
		<tbody>";
	while($mtRow = mysqli_fetch_assoc($mtQuery)){
		$empId = $mtRow["EmpId"];
		$empName = $mtRow["Name"];
		$taskSubmit = $mtRow["TaskSubmit"];
		$msg .= "<tr>
					<td>$empName</td>
					<td>$taskSubmit</td>
				</tr>";
	}
	$msg .= "</tbody>
			</table>";

	$sendMailStatus = $sendMailObj->sendMail($rmEmailId, $subject, $msg, null);


}

?>

<?php
function getStartAndEndDate($week, $year) {
 	$dto = new DateTime();
	$dto->setISODate($year, $week);
	$ret['week_start'] = $dto->format('Y-m-d');
	$dto->modify('+6 days');
	$ret['week_end'] = $dto->format('Y-m-d');
	return $ret;
}
?>