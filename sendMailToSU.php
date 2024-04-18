<?php 
include("dbConfiguration.php");
require 'SendMailClass.php';

$fontFamily = 'font-family: "Times New Roman", Times, serif';
$sendMailObj = new SendMailClass();

$sql = "SELECT DISTINCT e.EmpId, e.Name, e.Email FROM MyTask mt join Employees e on mt.SU_EmpId = e.EmpId where `RM_Rating` is not null";
// $sql = "SELECT DISTINCT e.EmpId, e.Name, e.Email FROM MyTask mt join Employees e on mt.SU_EmpId = e.EmpId where `RM_Rating` is not null and mt.SU_EmpId = 'tr57'";

$query = mysqli_query($conn,$sql);
while($row = mysqli_fetch_assoc($query)){
	$suEmpId = $row["EmpId"];
	$suName = $row["Name"];
	$suEmailId = $row["Email"];
	// $suEmailId = "ayush.agarwal@trinityapplab.co.in,jai.prakash@trinityapplab.co.in";
	// $suEmailId = "jai.prakash@trinityapplab.co.in";

	$mtSql = "SELECT e.Name as FillBy, mt.Week, (case when mt.RM_Rating is null then 'Pending' else mt.RM_Rating end) as RmRating, e1.Name as RatingGivenBy FROM MyTask mt join Employees e on mt.EmpId=e.EmpId join Employees e1 on mt.RM_EmpId=e1.EmpId where mt.SU_EmpId='$suEmpId' order by mt.TaskId desc";
	$mtQuery = mysqli_query($conn,$mtSql);

	$subject = "Task rating";
	$msg = "Dear $suName,<br>";
	$msg .= "Please find task rating: <br><br>";
	$msg .= "<table border=1 cellpadding=5 cellspacing=0 style='$fontFamily;font-size:14px'>
		<thead>
			<tr style='background-color:#2E57A7;color:white'>
				<th>Fill By</th>
				<th>Week</th>
				<th>RM Rating</th>
				<th>Rating Given By</th>
			</tr>
		</thead>
		<tbody>";
	while($mtRow = mysqli_fetch_assoc($mtQuery)){
		$fillBy = $mtRow["FillBy"];
		$week = $mtRow["Week"];
		$rmRating = $mtRow["RmRating"];
		$ratingGivenBy = $mtRow["RatingGivenBy"];
		$msg .= "<tr>
					<td>$fillBy</td>
					<td>$week</td>
					<td>$rmRating</td>
					<td>$ratingGivenBy</td>
				</tr>";
	}
	$msg .= "</tbody>
			</table>";

	$sendMailStatus = $sendMailObj->sendMail($suEmailId, $subject, $msg, null);
}

?>