<?php 
include("dbConfiguration.php");

$currentDate = date('Y-m-d');
// $currentDate = "2024-02-19";
$date = new DateTime($currentDate);
$year = $date->format("Y");
$week = $date->format("W");

$week_array = getStartAndEndDate($week,$year);
$startWeekDate = $week_array["week_start"];
$endWeekDate = $week_array["week_end"];

$weekDates = $startWeekDate." to ".$endWeekDate;
$weeks = "Week ".$week;

$weekJson = array(
	'week'=> $weeks,
	'weekDate' => $weekDates
);

echo json_encode($weekJson);
?>

<?php
function getStartAndEndDate($week, $year) {
 	$dto = new DateTime();
	$dto->setISODate($year, $week);
	$ret['week_start'] = $dto->format('d-M-Y');
	$dto->modify('+6 days');
	$ret['week_end'] = $dto->format('d-M-Y');
	return $ret;
}
?>