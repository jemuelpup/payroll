<?php

$fieldSet = "";// column names in database. This must be separated by dash
$valueSet = "";// value of column name. This must be separated by dash
$processName = "";// this is the name of the process you want to do

//$valueSet = "Vi Nachman-Yolonda Zeitz-Avery Kincannon-Gilberte Berti-Bette Monnier-Lorita Wilding-Pamila Frese-Lissette Egger-Leona Estell-Kasha Rafael-Fausto Talamantez-Ileen Pebley-Latesha Lafon-Raymon Thornton-Laci Ridgley-Carter Toups-Beula Miah-Monty Vogler-Cythia Richey-Jarrod Tilly";
//echo "INSERT INTO employee_tbl(name, employeeNum, division, client, taxId, salary) VALUES (\"".$field."\",\"21501".$addedString."\",1,1,1,10000);\n";
$tableName = "income_tbl";
$fieldSet = "id-name-taxable";

$tableName = "deduction_tbl";
$fieldSet = "name";//dont forget to remove the id
$processName = "addNewSalaryDeduction";


$tableName = "deductionlog_tbl";
$fieldSet = "employeeID-date";



// THIS IS THE PART WHERE YOU WRITE WHAT YOU WANT TO DO
echo '<textarea rows="80" cols="250">';
echo createTable($tableName,getFieldsCreateTable($fieldSet))."\n";
echo insertQuery($tableName,getFieldsInsertQuery($fieldSet),getValues($fieldSet))."\n";
echo getUserInterfaceCode($processName,$fieldSet)."\n";
echo getSwitchCaseCode($processName,$fieldSet)."\n";
echo getAjaxCode($processName,$fieldSet)."\n";
echo getFunctionCode($processName,$fieldSet)."\n";
echo '</textarea>';















/************ FOR DATABASE *************/

//FOR CREATING TABLES
function getFieldsCreateTable($fieldSet){
	$tableData = "";
	$fields = explode("-",$fieldSet);
	foreach($fields as $field){ $tableData .= ($field === end($fields)) ? $field." varchar(255)\n" : $field." varchar(255),\n";}
	return $tableData;
}
function createTable($tableName,$queryData){
	return "CREATE TABLE ".$tableName."(\n".$queryData.");"."\n";
}

// FOR INSERTING DATA
function getFieldsInsertQuery($fields){
	$theCode = "";
	$fields = explode("-",$fields);
	foreach($fields as $field){ $theCode .= $field.','; }
	return rtrim($theCode, ",");
}
function getValues($values){
	$theCode = "";
	$fields = explode("-",$values);
	foreach($fields as $field){ $theCode .= '\\"$'.$field.'\\",'; }
	return rtrim($theCode, ",");
}
function insertQuery($tableName,$fields,$values){
	return "INSERT INTO ".$tableName."(".$fields.")VALUES(".$values.");\n";
}


















/************* FOR USER INTERFACE ******************/
function getUserInterfaceCode($processName,$fieldSet){
	$theCode = "";
	$theCode = "<div class=\"$processName\">"."\n";
	$theCode .= "<h2>$processName</h2>"."\n";
	$fields = explode("-",$fieldSet);
	foreach($fields as $field){ $theCode .= '<p>'.$field.'<input type="text" class="'.$field.'" name="'.$field.'" placeholder="'.$field.'"></p>'."\n"; }
	$theCode .= '<button class="'.$processName.'Btn">'.$processName.'</button>'."\n";
	$theCode .= '</div>'."\n";
	return $theCode;
}




















/************* FOR PROGRAM PROCESSES ******************/

// For switch cases
function getSwitchCaseCode($processName,$fieldSet){
	$theCode = "";
	$fieldCodes = "";
	$fields = explode("-",$fieldSet);
	foreach($fields as $field){
		$fieldCodes .= '$_POST["'. $field .'"],';
	}
	$fieldCodes = rtrim($fieldCodes, ",");
	$theCode .= "case \"$processName\":{\n";
	$theCode .=  $processName.'($conn,'.$fieldCodes.');'."\n";
	$theCode .=  "}break;\n";
	return $theCode;
}

// For click and save to database process
function getAjaxCode($processName,$fieldSet){
	$theCode = "";
	$theCode .= '$(".'.$processName.'Btn").click(function(){'."\n";
	$theCode .= "$.ajax({
url: \"functions.php\",
type: 'POST',\n";
	$theCode .= "data: { todo: \"$processName\",},\n";
	
	
	$theCode .= "success: function(result){
alert(result);
},
error: function () {
//your error code
alert();
}
});
});";
	return $theCode;
}

function getFunctionCode($processName,$fieldSet){
	$fieldCodes = "";
	$fields = explode("-",$fieldSet);
	foreach($fields as $field){
		$fieldCodes .= ',$'. $field ;
	}
	
echo '
function '.$processName.'($conn'.$fieldCodes.'){

}
';
}




// usefull functions in the future
//if (preg_match("/[^A-Za-z0-9\ -]/", $incomeName)){
//	echo "No special characters allowed in adding fields except: '-'";
//}





?>