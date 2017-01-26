<?php
require('connect.php');
require('computations.php');

switch($_POST["todo"]){
	case "addNewIncome":{
		addNewIncome($conn,$_POST["incomeName"],$_POST["taxable"],$_POST["placeholder"],$_POST["inModal"],$_POST["active"]);
	}break;
		
	case "addNewSalaryDeduction":{
		addNewSalaryDeduction($conn,$_POST["name"],$_POST["placeholder"],$_POST["inModal"],$_POST["active"]);
	}break;

	case "insertPayrollData":{
		insertParollData($conn,$_POST["payrollData"]);
	}break;

	case "finalPayrollData":{
		finalPayrollData($conn,$_POST["payrollData"]);
	}break;

	case "setTax":{
		setTaxes($conn,$_POST["payrollDate"],$_POST["department"]);
	}break;

	case "deleteRecord":{
		deleteRecord($conn,$_POST["payrollDate"],$_POST["department"]);
	}break;

	case "checkPayroll":{
		payrollIsExisting($conn,$_POST["payrollDate"]);
	}break;
	case "editIncomeField":{
		editIncomeField($conn,$_POST["incomeName"],$_POST["oldName"],$_POST["taxable"],$_POST["placeholder"],$_POST["inModal"],$_POST["active"],$_POST["dataID"]);
	}break;
	case "editDeductionField":{
		editDeductionField($conn,$_POST["incomeName"],$_POST["oldName"],$_POST["taxDeductable"],$_POST["placeholder"],$_POST["inModal"],$_POST["active"],$_POST["dataID"]);
	}break;
	case "addSL":{
		addSL($conn,$_POST["employeeID"],$_POST["slDate"],$_POST["leavePoint"]);
	}break;
	case "addVL":{
		addVL($conn,$_POST["employeeID"],$_POST["vlDate"],$_POST["leavePoint"]);
	}break;
}

function addSL($conn,$employeeID,$slDate,$leavePoint){
	$sql = "INSERT INTO `tbl_leaverequest`(`leave_id`, `leave_user_id`, `leave_filing_date`, `leave_date`, `leave_approval_date`, `leave_status_id`, `leave_approveby_id`, `leave_point_deduction_val`) VALUES (2,$employeeID,'$slDate','$slDate','$slDate',2,1,$leavePoint)";// leave_id = 2 means sl
	$result = $conn->query($sql);//execute query
	if($result){
		$sql = "INSERT INTO tbl_leave_count (leave_count, leave_user_id, leave_id) SELECT DISTINCT leaveCount, leave_user_id, leave_id FROM ( SELECT (SELECT SUM(leave_point_deduction_val) FROM tbl_leaverequest WHERE leave_user_id = $employeeID AND leave_id = 2) AS leaveCount, leave_user_id, leave_id FROM tbl_leaverequest WHERE leave_user_id = $employeeID AND leave_id = 2) t ON DUPLICATE KEY UPDATE leave_count = t.leaveCount";//leave_id = 2 means vl
		$conn->query($sql);//execute query
	}
	else{
		echo "Sick leave in this date $slDate is already filed";
	}
}
function addVL($conn,$employeeID,$slDate,$leavePoint){
	$sql = "INSERT INTO `tbl_leaverequest`(`leave_id`, `leave_user_id`, `leave_filing_date`, `leave_date`, `leave_approval_date`, `leave_status_id`, `leave_approveby_id`, `leave_point_deduction_val`) VALUES (1,$employeeID,'$slDate','$slDate','$slDate',2,1,$leavePoint)";
	$result = $conn->query($sql);//execute query
	if($result){
		$sql = "INSERT INTO tbl_leave_count (leave_count, leave_user_id, leave_id) SELECT DISTINCT leaveCount, leave_user_id, leave_id FROM ( SELECT (SELECT SUM(leave_point_deduction_val) FROM tbl_leaverequest WHERE leave_user_id = $employeeID AND leave_id = 1) AS leaveCount, leave_user_id, leave_id FROM tbl_leaverequest WHERE leave_user_id = $employeeID AND leave_id = 1 ) t ON DUPLICATE KEY UPDATE leave_count = t.leaveCount";//leave_id = 1 means vl
		$conn->query($sql);//execute query
	}
	else{
		echo "Vacation leave in this date $slDate is already filed";
	}
}

function editDeductionField($conn,$incomeName,$oldName,$taxDeductable,$placeholder,$inModal,$active,$dataID){
	if(is_numeric(substr($incomeName, 0, 1))){
		echo "Name must start with letters";
	}
	else if (preg_match("/[^a-zA-Z0-9\s]+/", $incomeName)){
		echo "No special characters allowed in adding fields";
	}
	else{
		$incomeName = strtolower($incomeName);
		$newColName = $incomeName;

		$sql = "UPDATE deduction_tbl SET name =\"$incomeName\",taxDeductable = $taxDeductable, placeholder =\"$placeholder\", inModal = $inModal, active = $active WHERE id=$dataID";
		$conn->query($sql);//execute query

		$newColName = preg_replace('/\s\s+/', ' ', $newColName);
		$newColName = str_replace(" -","-",$newColName);
		$newColName = str_replace("- ","-",$newColName);
		$newColName = str_replace(" ","_",$newColName);//replace space by underscore
		if(strcmp($newColName,$oldName)!=0){
			$sql = "ALTER TABLE `table_name` CHANGE `$oldName` `$newColName` float DEFAULT 0";
			$conn->query($sql);//execute query
		}
		echo "$oldName field is now updated";
	}
}

function addNewIncome($conn,$incomeName,$taxable,$placeholder,$inModal,$active){
//	echo "nandito";
	if(is_numeric(substr($incomeName, 0, 1))){
		echo "Name must start with letters";
	}
	else if (preg_match("/[^a-zA-Z0-9\s]+/", $incomeName)){
		echo "No special characters allowed in adding fields";
	}
	else{
		$incomeName = strtolower($incomeName);
		$newColName = $incomeName;

		$sql = "INSERT INTO earning_tbl(name,taxable,placeholder,inModal,active)VALUES(\"$incomeName\",$taxable,\"$placeholder\",$inModal,$active);";
		$conn->query($sql);//execute query
		
		$newColName = preg_replace('/\s\s+/', ' ', $newColName);
		$newColName = str_replace(" -","-",$newColName);
		$newColName = str_replace("- ","-",$newColName);
		$newColName = str_replace(" ","_",$newColName);//replace space by underscore
		$sql = "ALTER TABLE salarylog_tbl ADD $newColName float DEFAULT 0";
		$conn->query($sql);//execute query
		echo "$newColName field is now available";
	}
}
function editIncomeField($conn,$incomeName,$oldName,$taxable,$placeholder,$inModal,$active,$dataID){
	if(is_numeric(substr($incomeName, 0, 1))){
		echo "Name must start with letters";
	}
	else if (preg_match("/[^a-zA-Z0-9\s]+/", $incomeName)){
		echo "No special characters allowed in adding fields";
	}
	else{
		$incomeName = strtolower($incomeName);
		$newColName = $incomeName;
		
		$sql = "UPDATE earning_tbl SET name =\"$incomeName\",taxable = $taxable, placeholder =\"$placeholder\", inModal = $inModal, active = $active WHERE id=$dataID";
		$conn->query($sql);//execute query

		$newColName = preg_replace('/\s\s+/', ' ', $newColName);
		$newColName = str_replace(" -","-",$newColName);
		$newColName = str_replace("- ","-",$newColName);
		$newColName = str_replace(" ","_",$newColName);//replace space by underscore
		if(strcmp($oldName,$newColName)!=0){
			$sql = "ALTER TABLE `table_name` CHANGE `$oldName` `$newColName` float DEFAULT 0";
			$conn->query($sql);//execute query
		}
		echo "$oldName field is now updated";
	}
}
function addNewSalaryDeduction($conn,$name,$placeholder,$inModal,$active){
	if(is_numeric(substr($name, 0, 1))){
		echo "Name must start with letters";
	}
	else if (preg_match("/[^a-zA-Z0-9\s]+/", $name)){
		echo "No special characters allowed in adding fields";
	}
	else{
		$name = strtolower($name);

		$newColName = $name;
		$sql = "INSERT INTO deduction_tbl(`name`, `active`, `placeholder`, `inModal`)VALUES(\"$name\",$active,\"$placeholder\",$inModal);";
		$conn->query($sql);//execute query
		echo $sql."";
		$newColName = preg_replace('/\s\s+/', ' ', $newColName);
		$newColName = str_replace(" -","-",$newColName);
		$newColName = str_replace("- ","-",$newColName);
		$newColName = str_replace(" ","_",$newColName);//replace space by underscore
//		$sql = "ALTER TABLE deductionlog_tbl ADD $newColName float DEFAULT 0";
		$sql = "ALTER TABLE salarylog_tbl ADD $newColName float DEFAULT 0";
		echo $sql."";
		$conn->query($sql);//execute query
		echo "$newColName";
	}
}
function insertParollData($conn,$payrollDataJSON){
	//	print_r($payrollDataJSON);
	$sql = "";
	$insertCodeFields = "";
	$insertCodeValues = "";
	$updateCode = "";
	$salary = 0;
	foreach($payrollDataJSON as $key=>$data){
		
		if($key=="base_salary"){
			$salary = floatval($data)*2;
		}
		elseif($key=="sss" and $data==1){
			$data = getSSSContribution($salary);
		}
		elseif($key=="phic" and $data==1){
			$data = getPhilHealthContribution($salary);
		}
		elseif($key=="hdmf" and $data==1){
			$data = getPagIbigContribution();
		}
		
		$insertCodeFields .= $key.",";
		$insertCodeValues .= ($key=="payrollDate") ? "'".$data."'," : $data.",";
		$updateCode .= ($key=="payrollDate") ? $key."='".$data."',": $key."=".$data.",";
		
	}
	$insertCodeFields="(".rtrim($insertCodeFields, ",").")";
	$insertCodeValues="(".rtrim($insertCodeValues, ",").")";
	$updateCode=rtrim($updateCode, ",");
	$sql = "INSERT INTO salarylog_tbl $insertCodeFields VALUES $insertCodeValues ON DUPLICATE KEY UPDATE $updateCode";
	$conn->query($sql);//execute query
	echo $sql;
}
function finalPayrollData($conn,$payrollDataJSON){
//	print_r($payrollDataJSON);
	foreach($payrollDataJSON as $payrollData){
		insertParollData($conn,$payrollData);
	}
}
function getFields($conn,$sql){
	$fields = "";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$fields .= str_replace(" ","_",$row["name"]).",";//replace space by underscore - for deductionlog_tbl
		}
	}
	return rtrim($fields, ",");
}
function getEarnings($conn,$earningFields,$empID,$payrollDate){
	$totalEarnings = 0;
	$sumOfColumns = str_replace(",","+",$earningFields);
	$sql = "SELECT ($sumOfColumns) as totalEarings FROM `salarylog_tbl` WHERE employeeID=$empID AND payrollDate='$payrollDate'";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) { // loop for updating
		while($row = $result->fetch_assoc()) {
			$totalEarnings = $row["totalEarings"];
		}
	}
//	echo "$totalEarnings-";
	return round($totalEarnings, 2);
}
function getDeductionsForTax($conn,$deductionFields,$empID,$payrollDate){
	$totalDeductions = 0;
	$sumOfColumns = str_replace(",","+",$deductionFields);
	$sql = "SELECT ($sumOfColumns) as totalDeductions  FROM `salarylog_tbl` WHERE employeeID=$empID AND payrollDate='$payrollDate'";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) { // loop for updating
		while($row = $result->fetch_assoc()) {
			$totalDeductions = $row["totalDeductions"];
		}
	}
	return round($totalDeductions, 2);
}

function getAllDeductions($conn,$deductionFields,$empID,$payrollDate){
	$totalDeductions = 0;
	$sumOfColumns = str_replace(",","+",$deductionFields);
	$sql = "SELECT ($sumOfColumns) as totalDeductions  FROM `salarylog_tbl` WHERE employeeID=$empID AND payrollDate='$payrollDate'";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) { // loop for updating
		while($row = $result->fetch_assoc()) {
			$totalDeductions = $row["totalDeductions"];
		}
	}
	return round($totalDeductions, 2);
}
/***********************************************
Editting function Done
***********************************************/
function deleteRecord($conn,$payrollDate,$department){
	$sql = "DELETE s.* FROM salarylog_tbl s INNER JOIN tbl_user e ON e.user_id = s.employeeID WHERE (e.user_id = s.employeeID AND s.payrollDate='$payrollDate' AND e.division_id = $department and e.active = 1)";
	$conn->query($sql);
	$sql = "DELETE ed.* FROM `earning_deduction_log_tbl` ed INNER JOIN tbl_user e ON e.user_id = ed.employeeID WHERE (e.user_id = ed.employeeID AND ed.payrollDate='$payrollDate' AND e.division_id = $department and e.active = 1);";
	$conn->query($sql);
}
/***********************************************
Editting function Done
***********************************************/
function setTaxes($conn,$payrollDate,$department){
	// Check if the payroll was created
	$sql = "SELECT payrollDate  FROM `salarylog_tbl` WHERE payrollDate='$payrollDate'";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) { // do you have records in database in this date?
		// Get the fields
		$sql = "SELECT name FROM `earning_tbl` WHERE taxable = 1 AND active = 1";
		$earningFields = getFields($conn,$sql);
		$sql = "SELECT name FROM `deduction_tbl` WHERE active = 1 AND taxDeductable = 1";
		$deductionForTaxFields = getFields($conn,$sql);
		$sql = "SELECT name FROM `deduction_tbl` WHERE active = 1 AND taxDeductable = 0 and name<>'income tax'";
		$otherDeductionFields = getFields($conn,$sql);
		// Get the emp number of the employees selected at tbl_user
		$sql = "SELECT user_id,dependents FROM `tbl_user` WHERE division_id = $department AND user_status=1";
		$result = $conn->query($sql);//execute query
		if ($result->num_rows > 0) { // loop for updating
			while($row = $result->fetch_assoc()) {
				$empID = $row["user_id"];
				$earningsForTax = 0;
				$deductionForTax = 0;
				$dependents = 0;
				$tax = 0;
				$dependents = (int)$row["dependents"];
				$earningsForTax = getEarnings($conn,$earningFields,$empID,$payrollDate);
				$deductionForTax = getDeductionsForTax($conn,$deductionForTaxFields,$empID,$payrollDate);
				$tax = round(getTax($earningsForTax, $deductionForTax, $dependents), 2);
				$otherDeductions = getAllDeductions($conn,$otherDeductionFields,$empID,$payrollDate);
				echo $otherDeductions;
				//				echo "$tax-";
				$sql = "UPDATE `salarylog_tbl` SET `income_tax`=$tax WHERE employeeID = $empID AND payrollDate='$payrollDate'";
				$conn->query($sql);
				$sql = "SELECT name FROM `earning_tbl` WHERE active = 1";
				$earnings = getFields($conn,$sql);
				$allEarnings = getEarnings($conn,$earnings,$empID,$payrollDate);
				$sql = "INSERT INTO `earning_deduction_log_tbl`(`employeeID`, `earnings`, `tax`, `benifitDeduction`, `payrollDate`,otherDeduction) VALUES ($empID,$allEarnings,$tax,$deductionForTax,'$payrollDate',$otherDeductions) ON DUPLICATE KEY UPDATE `earnings` = $allEarnings, `tax` = $tax, `benifitDeduction`=$deductionForTax, otherDeduction=$otherDeductions";
				$conn->query($sql);
			}
		}
		echo "Payroll tax assignment done.";
	}
	else{
		echo "No record found.";
	}
}
function payrollIsExisting($conn,$payrollDate){
	$sql = "SELECT employeeID FROM `salarylog_tbl` WHERE payrollDate = '$payrollDate'";
//	echo $sql."";
	$result = $conn->query($sql);//execute query
//	echo "".$result->num_rows;
	if ($result->num_rows > 0) {
		echo "1";
	}
	else{
		echo "0";
	}
}

/*********************************************
In resetting the value in the database, if you make changes regarding salary,
always truncate salarylog_tbl, earning_deduction_log_tbl, tbl_leaverequest, tbl_leave_count

*********************************************/

?>