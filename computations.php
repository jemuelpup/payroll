<?php
require('connect.php');

function createPayroll($conn,$division){

	$employeeEarningJSON = "";
	$employeeDeductionJSON = "";

	$sql = "SELECT `id`, `name`, `employeeNum`, `division`, `client`, `taxId`, `salary`, `civilStatus`, `dependent` FROM `employee_tbl` WHERE division = $division and active=1";
	$result = $conn->query($sql);//execute query
	echo "
	<table class='table table-striped'>
		<tr>
			<th>Name</th>
			<th>Employee Num</th>
			<th>Division</th>
			<th>BasicSalary</th>
			<th>Tax Id</th>
			<th>Civil Status</th>
			<th>Dependents</th>
			<th>Earnings</th>
			<th>Deductions</th>
			<th>Over time</th>
			<th>Cash allowance</th>
		</tr>
		";
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {

			$id = $row["id"];
			$name = $row["name"];
			$employeeNum = $row["employeeNum"];
			$division = $row["division"];
			$client = $row["client"];
			$taxId = $row["taxId"];
			$civilStatus = $row['civilStatus'];
			$dependent = $row['dependent'];
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
					<td class='taxStatus'>$civilStatus</td>
					<td class='civil'>$civilStatus</td>
					<td class='dependent'>$dependent</td>

					<td class='earning'><button data-toggle='modal' data-target='#earning'>Add Earnings</button></td>
					<td class='deduction'><button data-toggle='modal' data-target='#deduction'>Add Deduction</button></td>

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

function getEmployeeList($conn,$division){
	$sql = "SELECT * FROM `employee_tbl` WHERE division = $division";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$teamName = $row["name"];
			echo "<input type='radio' name='team' value='$id'> $teamName<br>";
			break;
		}
	}
}

function getSalaryDeductionFields($conn){
	$sql = "SELECT id,name FROM `deduction_tbl` WHERE active = 1 AND inModal = 1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row['id'];
			$name = $row['name'];
			$nameID = str_replace(' ', '',$name);
			$tagName = str_replace(" ","_",$name);//replace space by underscore - for deductionlog_tbl
			echo "
				<div class='form-group'>
					<label for='$nameID' class='col-sm-6 control-label text-left'>$name:</label>
					<div class='col-sm-6'>
						<input type='number' class='form-control' name='$tagName' min='0' id='$nameID'>
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
		case 1:{ $taxBracketToUse = $taxSM; }break;
		case 2:{ $taxBracketToUse = $taxSMD1; }break;
		case 3:{ $taxBracketToUse = $taxSMD2; }break;
		case 4:{ $taxBracketToUse = $taxSMD3; }break;
		case 5:{ $taxBracketToUse = $taxSMD4; }break;
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