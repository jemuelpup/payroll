<?php
require('connect.php');
require('computations.php');

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

//
//print_r(testgetDeductionFields($conn));
//

/********************** Imporant code ****************************/
// this code select all the deduction fields found int salary log table

//
//$sql = "SELECT * FROM `salarylog_tbl` WHERE 1";
//$result = $conn->query($sql);//execute query
//if ($result->num_rows > 0) {
//	while($row = $result->fetch_assoc()) {
//		
//		// very important line of code...
//		foreach(getDeductionFields($conn) as $dedField){
//			$val = $row[str_replace(' ','_',$dedField)];
//			echo "<tr>
//				<td>$dedField</td>
//				<td>$val</td>
//			</tr>";
//		}
//		echo "<br>";
//	}
//}
//
//function getDeductionFields($conn){
//	$deductionFieldArray = [];
//	$sql = "SELECT name FROM `deduction_tbl` WHERE 1";
//	$result = $conn->query($sql);//execute query
//	if ($result->num_rows > 0) { // loop for updating
//		while($row = $result->fetch_assoc()) {
//			array_push($deductionFieldArray,$row['name']);
//		}
//	}
//	return $deductionFieldArray;
//}

?>


<!DOCTYPE html>
<html>
	<head>
		<title>Untitled Document</title>
		<meta charset="UTF-8">
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="css/reset.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="queryGenerated">
			<p></p>
		</div>
	<main>
		
		<section class="sectionArea01 incomeMgmtArea">
			<div class="cotainer">
				<div class="row">
					<div class="col-md-4">
						<h2>Income management</h2>
						<div class="sectionBlock01A">
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
					</div>
					<div class="col-md-8">
						<div class="sectionBlock01B">
							<?php getIncomeFields($conn); ?>
						</div>
					</div>
				<div class="row">
			</div>
		</section>
		
		<section class="deductionMgmt sectionArea02">
			<div class="cotainer">
				<div class="row">
					<div class="col-md-4">
						<div class="sectionBlock02A">
							<div class="addNewSalaryDeduction">
								<h2>addNewSalaryDeduction</h2>
								<p>Deduction Name<input type="text" class="name" name="name" placeholder="name"></p>
								<p>PlaceHolder<input type="text" class="placeholder" name="placeHolder" placeholder="name"></p>
								<input type="checkbox" name="inModal" class="inModal" value="1" checked>inModal<br>
								<input type="checkbox" name="active" class="active" value="1" checked>active<br>
								<button class="addNewSalaryDeductionBtn">addNewSalaryDeduction</button>
							</div>
						</div>
					</div>
					<div class="col-md-8">
						<div class="sectionBlock02B">
							<?php getDeductionFields($conn); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section class="table salaryMgmtArea">
		<!--create a search here that shows the employees
			Click employee information and redirect to a form that shows all his information
			Edit the salary of that employee in that form then click update.
			Record the update at salary_increase_log_tbl...-->
			<table class="table table-striped employeeList">
				<div class="cotainer">
					<div class="row">
						<div class="col-md-4">
							<form class="searchEmployee">
								<input class="search" type="text">
								<button type="submit">Search</button>
							</form>
						</div>
						<div class="col-md-8">
							<tr>
								<th>emp_no</th>
								<th>first_name</th>
								<th>middle_name</th>
								<th>last_name</th>
								<th>birth_date</th>
								<th>age</th>
								<th>user_status</th>
								<th>employmentStatus</th>
							</tr>
						</div>
					</div>
				</div>
			</table>
		</section>
		
		<section class="roleMgmtArea">
			<div class="cotainer">
				<div class="row">
					<div class="col-md-12">
						<form class="roleMgmt">
							<div class="col-md-2">
								<input class='form-control' type='text' class='' name='role_title' maxlength='50' placeholder="Role title" required/>
							</div>
							<div class="col-md-2">
								<select class='form-control' name='role_active'>
									<option value='1' selected>Active</option>
									<option value='0'>Inactive</option>
								</select>
							</div>
							<button type="submit">Insert</button>
						</form>
					</div>
				</div>
			</div>
		</section>
		<p>
			compute or review the following for exel report file:

			1) tax status (done)
			2) Original Hire Date (for phase one)
			3) 2nd Month (done)
			4) 3nd Month (done)
			5) 5nd Month (done)
			6) Promotion Date (done)
			7) Role Title(based on your role) (done)
			8) Role Level(get this from database) (done)
			9) Division(get the name) (done)
			10) Team(get the name) (done)
			11) Other incomes...

			Sick Leave

			Taxable
			Non-Taxable

			Total Income
			-Taxable
			-Non-Taxable

			total_taxable_income
			total_non_taxable_income

			35000-1118.8
			
		</p>
	</main>
		
		
		
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
	/* Adding employee roles */
	$('.roleMgmt').submit(function(event){
		event.preventDefault();
		var allInputs = [];
		$(this).find('input').map(function (i,e){
			allInputs.push({
				name: $(e).attr("name"),
				type: $(e).attr("type"),
				val: $(e).val()
			});
		});
		$(this).find('select').map(function(i,e){
			allInputs.push({
				name: $(e).attr("name"),
				type: "number",
				val: $(e).val()
			});
		});
		
		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "insertRole", data: allInputs},
			success: function(result){
//				console.log(result)
			},
			error: function () {
				alert("Something wrong.");
			}
		});

	});
	
	$(".addIncomeFieldBtn").click(function(){
		var incomeName = $(".incomeMgmtArea .incomeName").val();
		var taxable = $(".incomeMgmtArea .taxable").val();
		var placeholder = $(".incomeMgmtArea .placeholder").val();
		
		var inModal = $(".incomeMgmtArea .inModal").is(':checked') ? 1:0;
		var active = $(".incomeMgmtArea .active").is(':checked') ? 1:0;
		
//		console.log("inModal"+inModal+"\nactive"+active);
	
//		console.log("incomeName "+ incomeName+ "; taxable "+ taxable+ "; placeholder"+placeholder+"; inModal"+inModal+"; active"+active);
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
//		location.reload();
	});
	
	function printQuery(query){
		$(".queryGenerated p").text(query);
	}

	$(".addNewSalaryDeductionBtn").click(function(){
		var name = $(".addNewSalaryDeduction .name").val();
		var placeholder = $(".deductionMgmt .placeholder").val();
		alert(placeholder);
		var inModal = $(".deductionMgmt .inModal").is(':checked') ? 1:0;
		var active = $(".deductionMgmt .active").is(':checked') ? 1:0;
		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "addNewSalaryDeduction", name: name, placeholder:placeholder, inModal:inModal, active:active},
			success: function(result){
//				printQuery(result);
				alert(result+" added in deduction.");
			},
			error: function () {
				//your error code
				alert();
			}
		});
//		location.reload();
	});


	$('.earning-data-table .change').click(function(){
		//		alert("sadf");
		var rowClass = $(this).parent().parent();
		var oldName = rowClass.find(".fieldName").attr('name');
		var incomeName = rowClass.find('.fieldName').val();
		var taxable = rowClass.find('.fieldTaxable').is(":checked") ? 1:0;
		var placeholder = rowClass.find('.fieldPlaceHolder').val();
		var inModal = rowClass.find('.fieldInModal').is(":checked") ? 1:0;
		var active = rowClass.find('.fieldActive').is(":checked") ? 1:0;
		var active = rowClass.find('.fieldActive').is(":checked") ? 1:0;
		var dataID = rowClass.attr('class').split("-")[1];

		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "editIncomeField", incomeName : incomeName,oldName : oldName, taxable : taxable, placeholder:placeholder,inModal:inModal,active:active,dataID:dataID},
			success: function(result){
				alert(result);
			},
			error: function() {//your error code
				alert("There's something wrong in the system. Call the support");
			}
		});
	});


	$('.deduction-data-table .change').click(function(){
		//		alert("sadf");
		var rowClass = $(this).parent().parent();
		var oldName = rowClass.find(".fieldName").attr('name');
		var incomeName = rowClass.find('.fieldName').val();
		var taxDeductable = rowClass.find('.fieldTaxDeductable').is(":checked") ? 1:0;
		var placeholder = rowClass.find('.fieldPlaceHolder').val();
		var inModal = rowClass.find('.fieldInModal').is(":checked") ? 1:0;
		var active = rowClass.find('.fieldActive').is(":checked") ? 1:0;
		var active = rowClass.find('.fieldActive').is(":checked") ? 1:0;
		var dataID = rowClass.attr('class').split("-")[1];

		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "editDeductionField", incomeName : incomeName,oldName : oldName, taxDeductable : taxDeductable, placeholder:placeholder,inModal:inModal,active:active,dataID:dataID},
			success: function(result){
				alert(result);
			},
			error: function() {//your error code
				alert("There's something wrong in the system. Call the support");
			}
		});
	});
// under development do click event here ad add the names of the employees found at the search box.
	/* get the list of all the employees */
	$(".searchEmployee").submit(function(event){
		event.preventDefault();
		$keyword = "";
		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "selectEmployeeRecords", data: $keyword},
			success: function(result){
				// create loop here
				
				
				alert(result);
			},
			error: function() {//your error code
				alert("There's something wrong in the system. Call the support");
			}
		});
	});
	$('.employeeList').append(`<tr>
							  <th>emp_no</th>
							  <th>first_name</th>
							  <th>middle_name</th>
							  <th>last_name</th>
							  <th>birth_date</th>
							  <th>age</th>
							  <th>user_status</th>
							  <th>employmentStatus</th>
	</tr>`);
				
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