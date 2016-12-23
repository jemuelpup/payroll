<?php
require('connect.php');

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
		
		
		
//		$sql = "ALTER TABLE earninglog_tbl ADD $newColName float DEFAULT 0";
		$sql = "ALTER TABLE salarylog_tbl ADD $newColName float DEFAULT 0";
		
		
		$conn->query($sql);//execute query

		echo "$newColName field is now available";
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
		$sql = "INSERT INTO deduction_tbl(name)VALUES(\"$name\");";
		$conn->query($sql);//execute query
		
		$newColName = preg_replace('/\s\s+/', ' ', $newColName);
		$newColName = str_replace(" -","-",$newColName);
		$newColName = str_replace("- ","-",$newColName);
		$newColName = str_replace(" ","_",$newColName);//replace space by underscore
//		$sql = "ALTER TABLE deductionlog_tbl ADD $newColName float DEFAULT 0";
		$sql = "ALTER TABLE salarylog_tbl ADD $newColName float DEFAULT 0";
		$conn->query($sql);//execute query
		echo "$newColName";
	}
}

function insertParollData($conn,$payrollDataJSON){
	//	print_r($payrollDataJSON);
	$insertCodeFields = "";
	$insertCodeValues = "";
	$updateCode = "";
	foreach($payrollDataJSON as $key=>$data){
		$insertCodeFields .= $key.",";
		$insertCodeValues .= ($key=="payrollDate") ? "'".$data."'," : $data.",";
		$updateCode .= ($key=="payrollDate") ? $key."='".$data."',": $key."=".$data.",";
	}
	$insertCodeFields="(".rtrim($insertCodeFields, ",").")";
	$insertCodeValues="(".rtrim($insertCodeValues, ",").")";
	$updateCode=rtrim($updateCode, ",");
	$sql = "INSERT INTO salarylog_tbl $insertCodeFields VALUES $insertCodeValues ON DUPLICATE KEY UPDATE $updateCode";
	echo $sql;
}


?>