<?php
require('connect.php');

//dynamicInput($conn);

/***********************************************************
* Call this function after you record the data of the employees
************************************************************/
//dynamicInput($conn,'2017-01-31');
/***********************************************
Editting function Done
***********************************************/
function dynamicInput($conn,$cutOffDate){
	$sql = "SELECT user_id as `id`, `hire_date`, `regularization_date`, `employmentStatus` FROM `tbl_user` WHERE 1";
	$result = $conn->query($sql);
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$hire_date = $row["hire_date"];
			$regularization_date = $row["regularization_date"];
			$employmentStatus = $row["employmentStatus"];
			$leaveEarned = 0;
			
			$leaveEarned = insertLeaveData($id,$hire_date,$regularization_date,$employmentStatus,$cutOffDate);
			
			$sql = "INSERT INTO `tbl_leave_count`(`leave_user_id`, `leave_id`, `leave_earned`) VALUES ($id,1,$leaveEarned) ON DUPLICATE KEY UPDATE `leave_user_id`=$id,`leave_id`=1,`leave_earned`=$leaveEarned"; // for vacation leave
			$conn->query($sql);
			$sql = "INSERT INTO `tbl_leave_count`(`leave_user_id`, `leave_id`, `leave_earned`) VALUES ($id,2,$leaveEarned) ON DUPLICATE KEY UPDATE `leave_user_id`=$id,`leave_id`=2,`leave_earned`=$leaveEarned"; // for sickLeave
			$conn->query($sql);
		}
	}
}
/********************************************************
* This function records the leave earned by the employee
********************************************************/
function insertLeaveData($empID,$empHiredDate,$empRegularizationDate,$employmentStatus,$cutOffDate){
	$regularEmployee = 1;
	$leaveAccumulated = 0;
	$availedLeavePerCutoff = 1.25; // depends on whatever you want
	$availedLeavePerCutoff = 0.625; // depends on whatever you want
	$cutoffLeaveEarned = 0;
	
	
//	$currentDate = time();
	$currentDate = strtotime($cutOffDate);
	$currentMonth = date('m', $currentDate);
	$currentYear = date('Y', $currentDate);
	$currentDay = date('d', $currentDate);
	
	$hiredDate = strtotime($empHiredDate);// changable - must be dynamic
	$hiredYear = date('Y', $hiredDate);
	
	$regularizationDate = strtotime($empRegularizationDate);// changable - must be dynamic
	$regularizationYear = date('Y', $regularizationDate);
	
	$dateDiffHiredToCurrent = $currentDate - $hiredDate;
	$dateDiffRegularizationToCurrent = $currentDate - $regularizationDate;
	
	$daysHired = ($dateDiffHiredToCurrent / 86400);
	$monthsHired = ($daysHired/30.417);
	$yearsHired = floor($monthsHired/12);
	
	$daysRegularized = ($dateDiffRegularizationToCurrent / 86400);
	$monthsRegularized = ($daysRegularized/30.417);
	$yearsRegularized = floor($monthsRegularized/12);
	
	
//	echo "months $monthsRegularized -";
	
//	echo $yearsHired;
	$employeeIsRegular = true;
	
	$addedLeave = 0;//number of months from hired date to december of the past year from current
	$deductedLeave = 0;
	$sql = "";
	// compute the sl/vl to be payed to the employee
	// delete date accumulated. Replace by the value below
	$cutoffLeaveEarned = $currentMonth*2;
	if($currentDay>=1 and $currentDay<=15){
		$cutoffLeaveEarned -= 1;
	}
	
	$cutoffLeaveEarned *= $availedLeavePerCutoff;
	
	if($employmentStatus == $regularEmployee){ // if employee is regular //  FIX THIS. It can cause error...
		if($currentYear==$regularizationYear){// ex 2016 regularization; 2016 current year
			if($hiredYear<$regularizationYear){// ex: 2015-hired date; 2016-regularization date; 2016 current year;
				$lastYearDecember = strtotime(($regularizationYear-1).'-12-31');
				$dateDiffHiredToDecLastYear = round(($lastYearDecember - $hiredDate)/ 86400);// returns day
				$addedLeave = round($dateDiffHiredToDecLastYear/15)*$availedLeavePerCutoff; // leave earned to be added
				return $cutoffLeaveEarned + $addedLeave;
			}
			else{// ex: 2016-hired date; 2016-regularization date; 2016 current year;
				$thisYearJanuary = strtotime(($regularizationYear).'-01-01');
				$dateDiffHiredToDecLastYear = round(($hiredDate - $thisYearJanuary)/ 86400);// returns day
				$deductedLeave = round($dateDiffHiredToDecLastYear/15)*$availedLeavePerCutoff; // leave earned to be deducted
				return $cutoffLeaveEarned - $deductedLeave;
			}
		}
		else{// ex 2015 regularization; 2016 current year // follow forrmula: leaveEarned = monthCount*1.25
//			return $cutoffLeaveEarned;// accumulating leave
			return 15;// leave earned is automatically 15
		}
	}
	else{// if employee not regular, get the leave accumulated
		$dateDiffHiredDateToCurrDate = round(($currentDate - $hiredDate)/ 86400);// returns day
		$leaveAccumulated = round($dateDiffHiredDateToCurrDate/15)*$availedLeavePerCutoff;// add this to leave accumulated
		return $leaveAccumulated;
	}
	return 0;
}

function getIncomeFields($conn){
	$sql = "SELECT `id`, `name`, `taxable`, `active`, `inModal`, `placeholder` FROM `earning_tbl` where deleted = 0 AND `name` <> 'base salary';";
	$result = $conn->query($sql);//execute query
	echo "
	<table class='table table-striped earning-data-table'>
		<tr>
			<th>Name</th>
			<th>Placeholder Num</th>
			<th>Taxable</th>
			<th>Active</th>
			<th>InModal</th>
			<th>Change</th>
			<th>Delete</th>
		</tr>
		";
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$name = $row["name"];
			$placeholder = $row["placeholder"];
			$taxable = isChecked($row["taxable"]);
			$active = isChecked($row["active"]);
			$inModal = isChecked($row["inModal"]);
			$fieldName = str_replace(' ', '_', $name);
			
			echo "
			<tr class='field-$id';>
				<td><input type='text' class='fieldName' name='$fieldName' value='$name'></td>
				<td><input type='text' class='fieldPlaceHolder' value='$placeholder'></td>
				<td><input type='checkbox' class='fieldTaxable' $taxable></td>
				<td><input type='checkbox' class='fieldActive' $active></td>
				<td><input type='checkbox' class='fieldInModal' $inModal></td>
				<td><button class='change'>Change</button></td>
				<td><button class='delete'>Delete</button></td>
			</tr>
			
			";
				
		}
	}
	echo "</table>";
}
function getDeductionFields($conn){
	$sql = "SELECT `id`, `name`, `active`, `placeholder`, `inModal`, `taxDeductable` FROM `deduction_tbl` where deleted = 0 and name<>'sss' and name<>'phic' and name<>'hdmf'  and name<>'income tax'";
	$result = $conn->query($sql);//execute query
	echo "
	<table class='table table-striped deduction-data-table'>
		<tr>
			<th>Name</th>
			<th>Placeholder Num</th>
			<th>Tax deductable</th>
			<th>Active</th>
			<th>InModal</th>
			<th>Change</th>
			<th>Delete</th>
		</tr>
		";
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$name = $row["name"];
			$placeholder = $row["placeholder"];
			$taxDeductable = isChecked($row["taxDeductable"]);
			$active = isChecked($row["active"]);
			$inModal = isChecked($row["inModal"]);
			$fieldName = str_replace(' ', '_', $name);

			echo "
			<tr class='field-$id';>
				<td><input type='text' class='fieldName' name='$fieldName' value='$name'></td>
				<td><input type='text' class='fieldPlaceHolder' value='$placeholder'></td>
				<td><input type='checkbox' class='fieldTaxDeductable' $taxDeductable></td>
				<td><input type='checkbox' class='fieldActive' $active></td>
				<td><input type='checkbox' class='fieldInModal' $inModal></td>
				<td><button class='change'>Change</button></td>
				<td><button class='delete'>Delete</button></td>

			</tr>

			";

		}
	}
	echo "</table>";
}
function isChecked($CheckBoxVal){
	if($CheckBoxVal==1){
		return 'checked';
	}
	return '';
}
/***********************************************
Editting function Done
***********************************************/
function createPayroll($conn,$division){

	$employeeEarningJSON = "";
	$employeeDeductionJSON = "";

	$sql = "SELECT `user_id`, CONCAT (last_name,', ',first_name,' ',middle_name,'.') as `name`, emp_no as `employeeNum`, division_id as `division`,monthly_rate as `salary`, civil_status as `civilStatus`, `dependents` FROM `tbl_user` WHERE division_id = $division and user_status=1";
	$result = $conn->query($sql);//execute query
	echo "
	<table class='table table-striped payroll-data-table'>
		<tr>
			<th>Name</th>
			<th>Employee Num</th>
			<th>Division</th>
			<th>BasicSalary</th>
			<th>Civil Status</th>
			<th>Dependent/s</th>
			<th>Earnings</th>
			<th>Deductions</th>
			<th>Leaves</th>
			<th class='benifits sss closed'>SSS</th>
			<th class='benifits phic closed'>PHIC</th>
			<th class='benifits hdmf closed'>HDMF</th>
			<th class='benifits riceSubsidy closed'>
				<input type='checkbox' class='riceSubsidyValCB'>Rice Subsidy<br>
				<input class='riceSubsidyVal closed' type=number value=0>
			</th>
			<th>Over time</th>
			<th>
				<input type='checkbox' class='cashAllowanceValCB'>Cash allowance<br>
				<input class='cashAllowanceVal closed' type=number value=0>
			</th>
		</tr>
		";
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {

			$id = $row["user_id"];
			$name = $row["name"];
			$employeeNum = $row["employeeNum"];
			$division = $row["division"];
//			$client = $row["client"];
			$civilStatus = $row['civilStatus'];
			$dependents = $row['dependents'];
			$salary = $row["salary"];
			$basicPay = $salary/2;
			$taxStatus = "";
			$employeeEarningJSON .= "{\"id\":$id,{\"salary\":$salary}},";
			$employeeDeductionJSON .= "{\"id\":$id,{\"salary\":$salary}},";
			//tax status single/merried = 1/2

			switch($civilStatus){
				case 1:{ $taxStatus = "Single"; }break;
				case 2:{ $taxStatus = "Married"; }break;
				case 3:{ $taxStatus = "Widow"; }break;
			}

			echo "<tr data-id = '$id'>
					<td class='name'>$name</td>
					<td class='empNum'>$employeeNum</td>
					<td class='division'>$division</td>
					<td class='basicSalary'>$basicPay</td>
					<td class='civil'>$civilStatus</td>
					<td class='dependents'>$dependents</td>
					<td class='earning'><button data-toggle='modal' data-target='#earning'>Add Earnings</button></td>
					<td class='deduction'><button data-toggle='modal' data-target='#deduction'>Add Deduction</button></td>
					<td class='leave'><button data-toggle='modal' data-target='#leave'>Add Leave</button></td>
					<td class='benifits sss ease closed'><input type='checkbox' value=1></td>
					<td class='benifits phic closed'><input type='checkbox' value=1></td>
					<td class='benifits hdmf closed'><input type='checkbox' value=1></td>
					<td class='benifits riceSubsidy closed'><input type='number' value=0></td>
					<td class='overTime'><input type='number'></td>
					<td class='cashAllowance'><input type='number'></td>
				</tr>";

		}
	}
	echo "</table>";

	// insert the salary of the employee...
	// create a button that writes the philhealth, sss, and pagibig of every employee
	// create 

	// generate payroll... then save....

	//	$cutoff = 
}
/***********************************************
Editting function Done
***********************************************/
function getEmployeeList($conn,$division){
	$sql = "SELECT *, CONCAT (last_name,', ',first_name,' ',middle_name,'.') as name FROM `tbl_user` WHERE division_id = $division AND user_status=1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["user_id"];
			$teamName = $row["name"];
			echo "<input type='radio' name='team' value='$id'> $teamName<br>";
			break;
		}
	}
}

function getSalaryDeductionFields($conn){
	$sql = "SELECT id,name,placeholder FROM `deduction_tbl` WHERE active = 1 AND inModal = 1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row['id'];
			$name = $row['name'];
			$placeholder = $row['placeholder'];
			$nameID = str_replace(' ', '',$name);
			$tagName = str_replace(" ","_",$name);//replace space by underscore - for deductionlog_tbl
			echo "
				<div class='form-group'>
					<label for='$nameID' class='col-sm-6 control-label text-left'>$name:</label>
					<div class='col-sm-6'>
						<input type='number' class='form-control' name='$tagName' id='$nameID' placeholder='$placeholder'>
					</div>
				</div>
			";
		}
	}
}
function getSalaryAdditionFields($conn){
	$sql = "SELECT * FROM `earning_tbl` WHERE active = 1 AND inModal = 1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row['id'];
			$name = $row['name'];
			$placeholder = $row['placeholder'];
			$nameID = str_replace(' ', '',$name);
			$tagName = str_replace(" ","_",$name);//replace space by underscore - for deductionlog_tbl
			echo "
				<div class='form-group'>
					<label for='$nameID' class='col-sm-6 control-label text-left'>$name:</label>
					<div class='col-sm-6'>
						<input type='number' class='form-control' name='$tagName'  id='$nameID' placeholder='$placeholder'>
					</div>
				</div>
			";
		}
	}
}
function getSSSContribution($salaryMonthly){
	$additional = 0;
	$salaryMax = 15750;
	$salaryBracket = 0;
	$sssContribution = 1000;
	if($salaryMonthly<$salaryMax){
		for($j=1249.99;$j<=$salaryMonthly;$j+=500){
			$sssContribution += 500;
		}
		$salaryBracket = floor($salaryMonthly/500)-1;
		for($j = 0; $j < $salaryBracket; $j++){
			if($j == 0){ $additional = 0; }
			else if($j%3==0){ $additional -= 0.05; }
			else{ $additional += 0.05; }
		}
	}
	else{
		$sssContribution = 16000;
		$additional = 0.5;
	}
	$sssContribution *= 0.0363;
	$sssContribution += $additional;
	return $sssContribution;
}
function getPhilHealthContribution($salaryMonthly){
	$philHealthContribution = 437.50;
	if($salaryMonthly>=35000){
		return $philHealthContribution;
	}
	$bracket = 35000;
	while($salaryMonthly<$bracket){
		$philHealthContribution -= 12.5;
		$bracket -= 1000;
	}
	return $philHealthContribution;
}
function getPagIbigContribution(){
	return 100;
}
function getTax($totalEarnings, $totalDeductions, $taxStatus){
	// tax bracket
	$taxSM = [0,2083,2500,3333,5000,7917,12500,22917];
	$taxSMD1 = [0,3125,3542,4375,6042,8958,13542,23958];
	$taxSMD2 = [0,4167,4583,5417,7083,10000,14583,25000];
	$taxSMD3 = [0,5208,5625,6458,8125,11042,15625,26042];
	$taxSMD4 = [0,6250,6667,7500,9167,12083,16667,27083];
	$taxBracketToUse = [];
	// deduction percentage
	$deductionPercentageSet = [0,0.05,0.1,0.15,0.2,0.25,0.3,0.32];
	// fixed tax
	$witholdingTax = [0,0,20.83,104.17,354.17,937.50,2083.33,5208.33];
	// operators
	$j = 0;
	$tax = 0;
	$excessTax = 0;
	$salary = 0;
	$salary = $totalEarnings-$totalDeductions;
	switch($taxStatus){
		case 0:{ $taxBracketToUse = $taxSM; }break;
		case 1:{ $taxBracketToUse = $taxSMD1; }break;
		case 2:{ $taxBracketToUse = $taxSMD2; }break;
		case 3:{ $taxBracketToUse = $taxSMD3; }break;
		case 4:{ $taxBracketToUse = $taxSMD4; }break;
		default:{ $taxBracketToUse = $taxSMD4; }break;
	}
	for($i=count($taxBracketToUse)-1;$i>0;$i--){
		if($totalEarnings>$taxBracketToUse[$i]){
			$j = $i;
			break;
		}
	}
	
	$excessTax = ($salary-$taxBracketToUse[$j])*$deductionPercentageSet[$j];
	$tax = $witholdingTax[$j]+$excessTax;
	return $tax;
}
function updateDeduction($empNum,$payrollDate,$totalEarnings,$incomeTax,$monthlySalary,$sssNeeded,$phliHealthNeeded,$paigibigNeeded){
	$baseSalary = $monthlySalary/2;
	$sss = 0;
	$philHealth = 0;
	$pagibig = 0;
	if($sssNeeded){
		$sss = getSSSContribution($salaryMonthly);
	}
	if($phliHealthNeeded){
		$philHealth = getPhilHealthContribution($salaryMonthly);
	}
	if($paigibigNeeded){
		$pagibig = getPagIbigContribution();
	}
	
	
	
}

?>