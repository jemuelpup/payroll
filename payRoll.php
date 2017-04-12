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
		<link rel="stylesheet" href="css/style_payroll.css">
<!--	Other plugins -->
		<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
		<style>
			.off.available{
				padding: 1px!important;
			}
			.table-condensed>thead>tr>th,
			.table-condensed>tbody>tr>td{
				padding: 0!important;
			}
			.daterangepicker .calendar th, .daterangepicker .calendar td {
				min-width: 0;
			}
			.daterangepicker {
				width: 445px!important;
			}
			.overTime input{
				font-size: 10px;
			}
			.overTime .input-group{
				width: 278px;
			}
		</style>
	</head>
	<body>
	<!-- MODAL -->
	
		<!-- Leaves -->
		<div class="modal fade" id="leave" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content form-horizontal earnings-form">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Leave</h4>

					</div>
					<div class="modal-body">
						<div class='form-group'>
							<label for='$nameID' class='col-sm-6 control-label text-left'>Vacation leave date:</label>
							<div class='col-sm-6'>
								<input type="date" id="VLDate" class="hasDatepicker">
								<button type="submit" class="btn btn-default addVL">Add VL</button>
							</div>
						</div>
						<div class='form-group'>
							<label for='$nameID' class='col-sm-6 control-label text-left'>Sick leave date:</label>
							<div class='col-sm-6'>
								<input type="date" id="SLDate" class="hasDatepicker">
								<button type="submit" class="btn btn-default addSL">Add SL</button>
							</div>
						</div>
						<div class='form-group'>
							<label for='$nameID' class='col-sm-6 control-label text-left'>Leave point</label>
							<div class='col-sm-6'>
								<input type="radio" name="leavePoint" value="1" checked="checked"> 1<br>
								<input type="radio" name="leavePoint" value="0.5"> 0.5<br>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<p class="dataRecordingConfirmation pull-left"></p>
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>

			</div>
		</div>
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
		<!-- Overtime -->
		<div class="modal fade" id="overTime" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content overtime-form">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Overtime</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-lg-12">
								<?php overtimeForm(); ?>
							</div>
						</div>
						
					</div>
					<div class="modal-footer">
						<p class="dataRecordingConfirmation pull-left"></p>
						<button type="submit" class="btn btn-default">Insert Overtime.</button>
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
					<button class="computeTax pull-right mt-20 mr-5">Compute Tax</button>
					<button class="viewPayslip pull-right mt-20 mr-5">View Payslip</button>
					<button class="viewRecords pull-right mt-20 mr-5">View Records</button>
					<button class="deleteRecord pull-right mt-20 mr-5">Delete Record</button>
					<button class="tabledReport pull-right mt-20 mr-5">View Tabled Report</button>
				</div>
			</div>
		</div>
	</header>
	<main>
		<section id="parRollTable">
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-12">
						<div class="queryGenerated">
							<p></p>
						</div>
						<p>Payroll date: <input type="text" id="payrollDate"></p>
						<div class="benifits">
							<input type="checkbox" id="sss">SSS<br>
							<input type="checkbox" id="philhealth">PHILHEALTH<br>
							<input type="checkbox" id="pagibig">PAGIBIG<br>
							
							<input type="checkbox" id="thirteenthMonthPay">13th month pay<br>
							<input type="checkbox" id="pagibig">PAGIBIG<br>
							
							
						</div>
						<?php createPayroll($conn,1); ?>
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
			7) Role Title(based on your role)
			8) Role Level(get this from database)
			9) Division(get the name)
			10) Team(get the name)
			11) Other incomes...
			
		</p>
	</main>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!--		other plugin -->
		<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
		<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
		<script src="js/main.js"></script>
	</body>
</html>