<?php
require('connect.php');

function getTeams($conn){
	$sql = "SELECT `id`, `name` FROM `division_tbl` WHERE `active` = 1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$teamName = $row["name"];
			echo "<input type='radio' name='team' value='$id'> $teamName<br>";
		}
	}
}
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Untitled Document</title>
		<meta charset="UTF-8">
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="incomeMgmt">
			<h2>Income management</h2>
			<p>Income name <input type="text" class="incomeName" name="incomeName" placeholder="Income name"></p>
			<p>Placeholder <input type="text" class="placeholder" name="placeholder" placeholder="placeholder"></p>
			<input type="checkbox" name="inModal" class="inModal" value="1" checked>inModal<br>
			<input type="checkbox" name="active" class="active" value="1" checked>active<br>
			<select class="taxable">
				<option value=0>Non taxable</option>
				<option value=1>Taxable</option>
			</select>
			<button class="addIncomeFieldBtn">Add income field</button>
			<button class="editIncomeField">Edit income field</button>
			<button class="deleteIncomeField">Delete income field</button>
			
			<div class="incomeList dn">
				<select class="incomeList">
					<option value=0>Select income name</option>
				</select>
			</div>
		</div>
		
		<div class="deductionMgmt">
			<div class="addNewSalaryDeduction">
				<h2>addNewSalaryDeduction</h2>
				<p>name<input type="text" class="name" name="name" placeholder="name"></p>
				<input type="checkbox" name="inModal" class="inModal" value="1" checked>inModal<br>
				<input type="checkbox" name="active" class="active" value="1" checked>active<br>
				<button class="addNewSalaryDeductionBtn">addNewSalaryDeduction</button>
			</div>
		</div>
		
		
		<div class="payRollOfEmployees">
			<form action="payRoll.php" method="post">
				<p>Select department</p>
				<?php
				getTeams($conn);
				?>
				<button>Create payroll</button>
			</form>
		</div>
		
		
		
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
	$(".incomeMgmt .addIncomeFieldBtn").click(function(){
		var incomeName = $(".incomeMgmt .incomeName").val();
		var taxable = $(".incomeMgmt .taxable").val();
		var placeholder = $(".incomeMgmt .placeholder").val();
		
		var inModal = $(".incomeMgmt .inModal").is(':checked') ? 1:0;
		var active = $(".incomeMgmt .active").is(':checked') ? 1:0;
		
//		console.log("inModal"+inModal+"\nactive"+active);
	
		
		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "addNewIncome", incomeName : incomeName, taxable : taxable, placeholder:placeholder,inModal:inModal,active:active},
			success: function(result){
				alert(result);
			},
			error: function() {//your error code
				alert("There's something wrong in the system. Call the support");
			}
		});
	});

	$(".addNewSalaryDeductionBtn").click(function(){

		var name = $(".addNewSalaryDeduction .name").val();
		var placeholder = $(".deductionMgmt .placeholder").val();
		var inModal = $(".deductionMgmt .inModal").is(':checked') ? 1:0;
		var active = $(".deductionMgmt .active").is(':checked') ? 1:0;
		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "addNewSalaryDeduction", name: name},
			success: function(result){
				alert(result+" added in deduction.");
			},
			error: function () {
				//your error code
				alert();
			}
		});
	});
				
/************ SALARY JS IMPLEMENTATION ****************/

	var totalEarnings = 15000,
		totalDeduction = 0,
		taxStatus = 1,
		tax = 0,
		salary = 0;

	tax = getTax(totalEarnings,totalDeduction,taxStatus)
	console.log(tax);
	salary = totalEarnings - totalDeduction - tax;
	console.log(salary);

	function getTax(totalEarnings, totalDeductions, taxStatus){
		// tax bracket
		var taxSM = [0,2083,2500,3333,5000,7917,12500,22917];
		var taxSMD1 = [0,3125,3542,4375,6042,8958,13542,23958];
		var taxSMD2 = [0,4167,4583,5417,7083,10000,14583,25000];
		var taxSMD3 = [0,5208,5625,6458,8125,11042,15625,26042];
		var taxSMD4 = [0,6250,6667,7500,9167,12083,16667,27083];
		var taxBracketToUse = [];
		// deduction percentage
		var deductionPercentageSet = [0,0.05,0.1,0.15,0.2,0.25,0.3,0.32];
		// fixed tax
		var witholdingTax = [0,0,20.83,104.17,354.17,937.50,2083.33,5208.33];

		// operators
		var j = 0,
		tax = 0,
		excessTax = 0,
		percentage = 0,
		salary = 0,
		salary = totalEarnings-totalDeductions;
		switch(taxStatus){
			case 1:{ taxBracketToUse = taxSM; }break;
			case 2:{ taxBracketToUse = taxSMD1; }break;
			case 3:{ taxBracketToUse = taxSMD2; }break;
			case 4:{ taxBracketToUse = taxSMD3; }break;
			case 5:{ taxBracketToUse = taxSMD4; }break;
			default:{ taxBracketToUse = taxSMD4; }break;
		}
		for(var i=taxBracketToUse.length-1;i>0;i--){
			if(totalEarnings>taxBracketToUse[i]){
				console.log(totalEarnings+">"+taxBracketToUse[i]);
				j = i;
				break;
			}
		}
		excessTax = (salary-taxBracketToUse[j])*deductionPercentageSet[j];
		tax = witholdingTax[j]+excessTax;
		console.log(j);
		return tax;
	}
});
		</script>
	</body>
</html>