<?php
require('computations.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Untitled Document</title>
		<meta charset="UTF-8">
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.css">
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
	
	<!-- MODAL -->
		<!-- Earning -->
		<div class="modal fade" id="earning" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content form-horizontal earnings-form">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Earnings</h4>
						
					</div>
					<div class="modal-body">
						<?php getSalaryAdditionFields($conn); ?>
					</div>
					<div class="modal-footer">
						<p class="dataRecordingConfirmation pull-left"></p>
						<button type="submit" class="btn btn-default">Add Earnings</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>

			</div>
		</div>
		<!-- Deduction -->
		<div class="modal fade" id="deduction" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content form-horizontal deduction-form">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Deduction</h4>
					</div>
					<div class="modal-body">
						<?php getSalaryDeductionFields($conn); ?>
					</div>
					<div class="modal-footer">
						<p class="dataRecordingConfirmation pull-left"></p>
						<button type="submit" class="btn btn-default">Add Deduction</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>

			</div>
		</div>
	<!-- HEAD -->
	<header>
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-6">
					<h1>Payroll monitoring</h1>
				</div>
				<div class="col-lg-6">
					<button class="saveRecords pull-right mt-20">Save</button>
					<button class="viewRecords pull-right mt-20 mr-5">View Records</button>
				</div>
			</div>
		</div>
	</header>
	<main>
		<section id="parRollTable">
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<p>Payroll date: <input type="text" id="payrollDate"></p>
						<?php createPayroll($conn,1); ?>
					</div>
				</div>
			</div>
		</section>
	</main>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		<?php
			$sal = 20000;
			$totalDeductions = getSSSContribution($sal)+getPhilHealthContribution($sal)+getPagIbigContribution();
			$basePay = 10000;
			$status = 1;
			$tax = round(getTax($basePay,$totalDeductions,$status),2);
		?>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="js/main.js"></script>
	</body>
</html>