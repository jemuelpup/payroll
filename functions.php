<?php
/*
* Important notes:
*
* 1) add active to the table user. 1 means active, 0 means inactive
*/

require('connect.php');
require('computations.php');



//*
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
	case "insertovertimeRecord":{
		dbInsert_insertovertimeRecord($conn,$_POST["data"]);
	}break;
	case "insertRole":{
		dbInsert_insertRole($conn,$_POST["data"]);
	}break;
	case "selectEmployeeRecords":{
		dbSelect_selectEmployeeRecords($conn,$_POST["data"]);
	}break;
}
/***************************************************************************************
* This function gets the employee records
***************************************************************************************/
// under development
function dbSelect_selectEmployeeRecords($conn,$data){
	
}


/***************************************************************************************
* This function inserts the roles in the company
***************************************************************************************/
function dbInsert_insertRole($conn,$data){
	$tableName = "tbl_role";
	$fields = "";
	$values = "";
	foreach($data as $fieldData){
		$fields .= $fieldData["name"].",";
		if ($fieldData["type"] == "text") {	$values .= "'".$fieldData["val"]."',";	}
		elseif($fieldData["type"] == "number") {	$values .= $fieldData["val"].",";	};
	}
	$fields = "(".substr($fields, 0, -1).")";
	$values = "(".substr($values, 0, -1).")";
	$sql = "INSERT INTO $tableName $fields VALUES $values ";
	$conn->query($sql);
}
/***************************************************************************************
* This function inserts the overtime record of the employee
***************************************************************************************/
function dbInsert_insertovertimeRecord($conn,$data){
	$tableName = "tbl_overtime_request";
	$fields = "";
	$values = "";
	
	foreach($data as $fieldData){
		if($fieldData["name"]=="daterange"){
			$dateRangeInput = $fieldData["val"];
			if(validatedDateInput($dateRangeInput)){
				$testingStringRange = explode("-",$dateRangeInput);
				
//				echo "First<start time: $testingStringRange[0]><end time: $testingStringRange[1]>";
				
				$startTime = date(' Y-m-d G:i', strtotime(formatDate(trim($testingStringRange[0]))));
				$endTime = date(' Y-m-d G:i', strtotime(formatDate(trim($testingStringRange[1]))));
//				echo "Second<start time: $startTime><end time: $endTime>";
				
				$fields .= "ot_time_in,ot_time_out,";
				$values .= "'$startTime','$endTime',";
			}
		}
		else{
			$fields .= $fieldData["name"].",";
			if ($fieldData["type"] == "text") {	$values .= "'".$fieldData["val"]."',";	}
			if ($fieldData["type"] == "date") {	$values .= "'".date(' Y-m-d', strtotime($fieldData["val"]))."',";	echo "Nandito ako.";}
			elseif($fieldData["type"] == "number") {	$values .= $fieldData["val"].",";	}
		}
	}
	$fields = "(".substr($fields, 0, -1).")";
	$values = "(".substr($values, 0, -1).")";
	$sql = "INSERT INTO $tableName $fields VALUES $values ";
//	echo $sql;
	$conn->query($sql);//execute query
	
/************************************************************************************
* This function converts the MM/DD/YYYY format to YYYY-MM-DD format
************************************************************************************/
//	function formatDate($strDate){
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
		$result = $conn->query($sql);//execute query
		
		if($result){// means success

			$newColName = preg_replace('/\s\s+/', ' ', $newColName);
			$newColName = str_replace(" -","-",$newColName);
			$newColName = str_replace("- ","-",$newColName);
			$newColName = str_replace(" ","_",$newColName);//replace space by underscore

			$sql = "SHOW COLUMNS FROM `salarylog_tbl` LIKE '$newColName'";
			
			$result = $conn->query($sql);//execute query
			$exists = (mysqli_num_rows($result))?TRUE:FALSE;
			if(!$exists){// if column not existing, add the new colum in the salarylog_tbl
				$sql = "ALTER TABLE salarylog_tbl ADD $newColName float DEFAULT 0";
				$conn->query($sql);//execute query
				echo "$newColName field is now available";
			}
			echo "Insertion of data successful";
		}
		else{// query not inserted
			echo "Insertion of data unsuccessful";
		}
		
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
/******************************************************************
* $payrollDataJSON contains json file and the data to make a query
* example {base_salary => 20000,sss => 1}
******************************************************************/
function insertParollData($conn,$payrollDataJSON){
	//underDevelopment
	//	print_r($payrollDataJSON);
	$sql = "";
	$insertCodeFields = "";
	$insertCodeValues = "";
	$updateCode = "";
	$salary = 0;
	// field schecking for continous payments //  for the loop
	$fieldName = "";
	$fieldValue = 0;
	$employeeNumber = $payrollDataJSON["employeeID"];
	$typeOfContinousPayment = ""; // 1-means once a month; 2 means semi monthly; 3 means to stop
	//set the leaves of the employees
	// This area process the received json file
	// under development...
	foreach($payrollDataJSON as $key=>$data){
		
		// check if the json name has dash. If it has, process it. at the setContinousEraningsAndDeduction function
		// this if block is only responsible on adding earnings and deductions.
		if(strpos($key, '-') !== false){// means that the dash exist; get the employee number here.
			$keyData = (explode("-",$key));
			echo "<br>".str_replace('-', '', $keyData[1])."-".str_replace('_', '', $fieldName)."<br>";
			if(str_replace('-', '', $keyData[1])==str_replace('_', '', $fieldName)){// means that the field is set to continous payment
				echo "-$fieldName-";// this is the field for database...
				setContinousEraningsAndDeduction($conn,$keyData[0],$employeeNumber,$fieldName,$fieldValue,$data);
			}
			//underDevelopment
			$fieldName = "";
			$fieldValue = "";
		}
		else{
//			echo "$key";
			$fieldName = $key;
			$fieldValue = $data;
			
			if($key=="base_salary"){
				$salary = floatval($data)*2;
			}
			elseif($key=="sss" and $data==1){// means the sss is checked
				$data = getSSSContribution($salary);
				$employeeContribution = getEmployerSSSContribution($salary);
				$insertCodeFields .= "sss_employer,";
				$insertCodeValues .= $employeeContribution.",";
				$updateCode .= "sss_employer=".$employeeContribution.",";
			}
			elseif($key=="phic" and $data==1){// means the sss is checked
				$data = getPhilHealthContribution($salary);
				$insertCodeFields .= "phic_employer,";
				$insertCodeValues .= $data.",";
				$updateCode .= "phic_employer=".$data.",";
			}
			elseif($key=="hdmf" and $data==1){// means the sss is checked
				$data = getPagIbigContribution();
				$insertCodeFields .= "hdmf_employer,";
				$insertCodeValues .= $data.",";
				$updateCode .= "hdmf_employer=".$data.",";
			}

			$insertCodeFields .= $key.",";
			$insertCodeValues .= ($key=="payrollDate") ? "'".$data."'," : $data.",";
			$updateCode .= ($key=="payrollDate") ? $key."='".$data."',": $key."=".$data.",";
		}
		
	}
	$insertCodeFields="(".rtrim($insertCodeFields, ",").")";
	$insertCodeValues="(".rtrim($insertCodeValues, ",").")";
	$updateCode=rtrim($updateCode, ",");
	$sql = "INSERT INTO salarylog_tbl $insertCodeFields VALUES $insertCodeValues ON DUPLICATE KEY UPDATE $updateCode";
//	echo $sql.";";
	$conn->query($sql);//execute query
//	echo $sql;
}
// underDevelopment - fix the dates... it may cause error...


/************************************************************************************
* checks the format of the date function.
* format must be YYYY-MM-DD
*************************************************************************************/
function dateFormatCorrect($date){
	$dateCorrectFormat = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/i";
	if(preg_match($dateCorrectFormat,$date)){
		return true;
	}
	return false;
}
/************************************************************************************
* This function converts the MM/DD/YYYY format to YYYY-MM-DD format
* 
************************************************************************************/
function formatDate($strDate){
	// if the date is formated. return it
	if(strlen($strDate)==10 and dateFormatCorrect($strDate)){
		return $strDate;
	}
//	format the date
	$dateFormatParts = explode(' ',$strDate);
	$timePart = "";
//	print_r($dateFormatParts);
	if(count($dateFormatParts)==3){
		$timePart = " ".$dateFormatParts[1]." ".$dateFormatParts[2];
		$strDate = $dateFormatParts[0];
//		echo "Dumaan dito";
	}
	
//	echo "$strDate";
	$dateParts = explode('/',$strDate); // month day year
	$newDateFromat = $dateParts[2]."-".$dateParts[0]."-".$dateParts[1];
//	echo "new format: ".$newDateFromat.$timePart;
//	echo "--->>>".$newDateFromat.$timePart."<<<---";
	return $newDateFromat.$timePart;
}

/*************************************************************************************
* Parameters: 
*
* This function updates the salary log table overtime in the given payroll period
*************************************************************************************/
function updateOvertimePayrollRecord($conn,$payrollDate){

	
	$day = date('d',strtotime($payrollDate));
	$betweenDates = "";
	$fromDateCondition = "";
	$toDateCondition = "";
	if($day>0 and $day<16){
		$fromDateCondition = date(' Y-m-d', strtotime(formatDate(substr($payrollDate, 0, -2) . '01')));
		$toDateCondition = date(' Y-m-d', strtotime(formatDate(substr($payrollDate, 0, -2) . '15')));
	}
	elseif($day>15 and $day<32){
		echo ">$payrollDate<";
		$fromDateCondition = date(' Y-m-d', strtotime(formatDate(substr($payrollDate, 0, -2) . '16')));
		$toDateCondition = date(' Y-m-t', strtotime(formatDate($payrollDate)));
	}
	// remove the value of the overtime on this day period
	
	
	// change the query
	$sqlQuery = "SELECT ot.ot_request_id, ot.ot_timelog_id, ot.ot_user_id, u.monthly_rate, ot.ot_filing_date, ot.ot_date, ot.ot_approval_date, ot.ot_reason, ot.ot_status_id, ot.ot_approveby_id, ot.final_approved, ot.ot_token, ot.ot_time_in, ot.ot_time_out FROM tbl_overtime_request ot, tbl_user u  WHERE (ot.ot_time_in BETWEEN '$fromDateCondition' AND '$toDateCondition') AND ot.ot_user_id = u.user_id";
	
	// set first the overtime record to 0 of the employees who fille overtime. This method removes the error of duplication of overtime because in the next loop, it will just increment it.
	$result = $conn->query($sqlQuery);//execute query
	if ($result->num_rows > 0) { // loop for updating
		while($row = $result->fetch_assoc()) {
			$empID = $row["ot_user_id"];
			$sql = " UPDATE `salarylog_tbl` SET overtime = 0 WHERE (payrollDate BETWEEN '$fromDateCondition' AND '$toDateCondition') AND employeeID = $empID"; // create the query
			$conn->query($sql);//execute query
		}
	}
	$result = $conn->query($sqlQuery);//execute query
	
	if ($result->num_rows > 0) { // loop for updating
		while($row = $result->fetch_assoc()) {
			$empID = $row["ot_user_id"];
			$shiftBeginning = date("H:i", strtotime($row["ot_time_in"]));
			$shiftEnd = date("H:i", strtotime($row["ot_time_out"]));
			$shiftDayType = "regular";
			$workType = "overTime";
			$date = $row["ot_time_in"];
			$ratePerHour = ((float)$row["monthly_rate"])/174;
			$overtime = getWorkHoursAndPay($conn,$shiftBeginning,$shiftEnd,$shiftDayType,$workType,$date,$ratePerHour);
			$overtimePay = $overtime[1]["OverTimePay"];
			$sql = " UPDATE `salarylog_tbl` SET overtime = overtime + $overtimePay WHERE (payrollDate BETWEEN '$fromDateCondition' AND '$toDateCondition') AND employeeID = $empID"; // create the query
			echo "$sql";
			$conn->query($sql);//execute query
		}
	}
}

function validatedDateInput($dateInput){
	// check if the string has '-' if it has, explode
	// checkt the format of the date and time. dd/mm/yyyy HH:SS AM
//	echo "$dateInput<br>";
	$dateFormatRegX = "/[0-9]{2}\/[0-9]{2}\/[0-9]{4} ([0-9]{1}|[0-9]{2}):([0-9]{2}) (am|pm)/i";
	$dateInput = explode('-',$dateInput);
	$dateTime1 = strlen($dateInput[0]);
	$dateTime2 = strlen($dateInput[1]);
//	echo strlen($dateInput[0])."-".strlen($dateInput[1])."<br>";
	if((count($dateInput)==2) and ($dateTime1==20 or $dateTime1==19) and ($dateTime2==20 or $dateTime2==19)){
		$dateInput[0] = trim($dateInput[0], " ");
		$dateInput[1] = trim($dateInput[1], " ");
//		echo $dateInput[0]." ".$dateInput[1]."<br>";
		if(preg_match($dateFormatRegX,$dateInput[0]) and preg_match($dateFormatRegX,$dateInput[1])){
			return true;
		}
	}
	return false;
}
/***********************************************************************************
* This function records the continous deduction and earning
* The key here contains the continous earnings and the code also the value of the
* $data is the value of the continous earnings or deduction
* 1 - means monthly
* 2 - means semimonthly
* 3 - means to stop
* If the continous deduction is stopped, the record will be deleted on database
***********************************************************************************/
function setContinousEraningsAndDeduction($conn,$continousDataType,$employeeID,$fieldName,$fieldValue,$data){
	$sql = "";
	if($continousDataType=="continuousEarnings"){
		if($data==3){ // This is the delete query
			$sql = "DELETE FROM contious_earning_tbl WHERE `employeeID` = $employeeID AND `fieldName` = '$fieldName'";
		}
		else{ // this is the insert update query
			$sql = "INSERT INTO `contious_earning_tbl`(`employeeID`, `fieldName`, `fieldValue`, earningStatus) VALUES ($employeeID,'$fieldName',$fieldValue,$data) ON DUPLICATE KEY UPDATE fieldValue = $fieldValue, earningStatus = $data";
		}
	}
	elseif($continousDataType=="continuousDeduction"){
		if($data==3){ // This is the delete query
			$sql = "DELETE FROM contious_earning_tbl WHERE `employeeID` = $employeeID AND `fieldName` = '$fieldName'";
		}
		else{ // this is the insert update query
			$sql = "INSERT INTO `contious_earning_tbl`(`employeeID`, `fieldName`, `fieldValue`, deductionStatus) VALUES ($employeeID,'$fieldName',$fieldValue,$data) ON DUPLICATE KEY UPDATE fieldValue = $fieldValue, deductionStatus = $data";
		}
	}
	
	$conn->query($sql);//execute query
}

/****************************************************************
* This function gets the JSON code and iterates to it.
* JSON code contains all the queries needed to be processed and
* save to the database.
****************************************************************/
function finalPayrollData($conn,$payrollDataJSON){
	// under development. Testing phase...
	
	// get the date record inside the $payrollDataJSON variable
	$date = $payrollDataJSON[0]['payrollDate'];
	// loop through the JSON
	foreach($payrollDataJSON as $payrollData){
		insertParollData($conn,$payrollData);// process the query
	}
	
	// update the overtime record
	echo "nandito 1";
	updateOvertimePayrollRecord($conn,$date);//MM/DD/YYYY
	// update the continous deduction here...  with the given date
	updateSalaryLogForContinousPayment($conn,$date);
	
}


/****************************************************************
* This function update the salary log table by adding the continous
* earnings and deductions in the salarylog table.
****************************************************************/
function updateSalaryLogForContinousPayment($conn, $payrollDate){ // the date given
	// this is for the deductions of the employee
	$sql = "SELECT cdt.employeeID, cdt.fieldName, cdt.fieldValue, cdt.deductionStatus FROM tbl_user tu, contious_deduction_tbl cdt WHERE tu.user_id = cdt.employeeID and tu.division_id = 1 and tu.active = 1";// change the division here
	$result = $conn->query($sql);//execute query
	executeContinousPaymentUpdate($conn,$result,'deductionStatus',$payrollDate,"contious_deduction_tbl");
	
	// this is for the earnings of the employee
	$sql = "SELECT cet.employeeID, cet.fieldName, cet.fieldValue, cet.earningStatus FROM tbl_user tu, contious_earning_tbl cet WHERE tu.user_id = cet.employeeID and tu.division_id = 1 and tu.active = 1";// change the division here
	$result = $conn->query($sql);//execute query
	executeContinousPaymentUpdate($conn,$result,'earningStatus',$payrollDate,"contious_earning_tbl");
}
/**************************************************************************************
* This function executes the queries from updateSalaryLogForContinousPayment function
**************************************************************************************/
function executeContinousPaymentUpdate($conn,$result,$paymentUpdateStatus,$payrollDate,$tableUsed){
	$payrollDateDay = intval(substr($payrollDate, strrpos($payrollDate, '-') + 1));echo "$payrollDateDay<br>";
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$sql = "";
			$employeeID = $row['employeeID'];
			$fieldName = $row['fieldName'];
			$fieldVal = $row['fieldValue'];
			$paymentStatus = $row[$paymentUpdateStatus];
			if($paymentStatus == 1){ // means the deduction or earnings is monthly
				// check fi the given day is on the first 2 weeks of the month
				if($payrollDateDay>0 and $payrollDateDay<16){ // if it is, do the program
					$sql = "UPDATE `salarylog_tbl` SET $fieldName=$fieldVal WHERE employeeID=$employeeID AND payrollDate='$payrollDate'";
					$conn->query($sql);//execute query
				}
			}
			elseif($paymentStatus == 2){// means the deductioin or earnings is semi monthly(pe cutoff)
				$sql = "UPDATE `salarylog_tbl` SET $fieldName=$fieldVal WHERE employeeID=$employeeID AND payrollDate='$payrollDate'";
				$conn->query($sql);//execute query
			}
		}
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
		$sql = "SELECT name FROM `earning_tbl` WHERE taxable = 0 AND active = 1";
		$nonTaxableEarningFields = getFields($conn,$sql);
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
				echo "EmpID: ".$empID;
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
				
				$nonTaxableEarning = getEarnings($conn,$nonTaxableEarningFields,$empID,$payrollDate);// returns non taxable earnings.
				$totalTaxableIncome = $earningsForTax - $deductionForTax;
				// underdevelopment. not tested-current coding
				
				$sql = "UPDATE `salarylog_tbl` SET `income_tax`=$tax,total_taxable_income=$earningsForTax, total_non_taxable_income=$nonTaxableEarning, basis_of_tax=$totalTaxableIncome WHERE employeeID = $empID AND payrollDate='$payrollDate'";
				$conn->query($sql);
				$sql = "SELECT name FROM `earning_tbl` WHERE active = 1";
				$earnings = getFields($conn,$sql);
				$allEarnings = getEarnings($conn,$earnings,$empID,$payrollDate);
				
				
				$sql = "INSERT INTO earning_deduction_log_tbl(employeeID, earnings, tax, benifitDeduction, payrollDate,otherDeduction) VALUES ($empID,$allEarnings,$tax,$deductionForTax,'$payrollDate',$otherDeductions) ON DUPLICATE KEY UPDATE earnings = $allEarnings, tax = $tax, benifitDeduction=$deductionForTax, otherDeduction=$otherDeductions";
				
				
				
				$conn->query($sql);
				
//				echo $sql.";";
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

//under development
// Inserting the salary of the employee
/*
Do this on Friday... set it to the code of johnRey and Doms

Updating the salary of the employee
UPDATE `tbl_user` SET `monthly_rate`= $newMonthlyRate WHERE `user_id`=$uDI
Inserting the salary history of the employees
INSERT INTO `salary_increase_log_tbl`(`employee_id`, `last_salary_date_before_increase`, `base_salary`, `role_id`) VALUES ($id, '$currentDate', $newMonthlyRate, 'newRole')

*/

/*********************************************
In resetting the value in the database, if you make changes regarding salary,
always truncate salarylog_tbl, earning_deduction_log_tbl, tbl_leaverequest, tbl_leave_count

*********************************************/

?>