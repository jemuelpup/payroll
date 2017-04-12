<?php
require('connect.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payslip</title>
<link rel="stylesheet" href="css/reset.css">
<link rel="stylesheet" href="css/style_payslip.css">
</head>
<body>
<?php
	

	function getDeductionFields($conn){
		$deductionFieldArray = [];
		$sql = "SELECT name FROM `deduction_tbl` WHERE active = 1";
		$result = $conn->query($sql);//execute query
		if ($result->num_rows > 0) { // loop for updating
			while($row = $result->fetch_assoc()) {
				array_push($deductionFieldArray,$row['name']);
			}
		}
		return $deductionFieldArray;
	}

	function getEarningFields($conn){
		$earningFieldArray = [];
		$sql = "SELECT name FROM `earning_tbl` WHERE active = 1";
		$result = $conn->query($sql);//execute query
		if ($result->num_rows > 0) { // loop for updating
			while($row = $result->fetch_assoc()) {
				array_push($earningFieldArray,$row['name']);
			}
		}
		return $earningFieldArray;
	}
	
	/*
	
	SELECT leave_user_id,
MAX(CASE WHEN leave_id = 2 THEN leave_count END) AS sickLeave,
MAX(CASE WHEN leave_id = 1 THEN leave_count END) AS vacationLeave
FROM tbl_leave_count
GROUP BY leave_user_id
	*/
	
	$payrollDate = $_GET["payrollDate"];
	
	
	// add the  other features found at the notpad in your desktop
	
	$sql = "
	SELECT e.user_id, e.emp_no, CONCAT (e.last_name,', ',e.first_name,' ',e.middle_name,'.') as name,ed.tax,(ed.benifitDeduction+ed.otherDeduction) as payrollDeduction, MAX(CASE WHEN lc.leave_id = 2 THEN lc.leave_count END) AS sickLeave, MAX(CASE WHEN lc.leave_id = 1 THEN lc.leave_count END) AS vacationLeave, (ed.earnings-ed.tax-ed.benifitDeduction-otherDeduction) AS netPay, (ed.tax+ed.benifitDeduction+otherDeduction) as totalDeduction, ed.earnings, s.*, lc.leave_earned ,ed.benifitDeduction FROM salarylog_tbl s INNER JOIN tbl_user e INNER JOIN tbl_leave_count lc INNER JOIN earning_deduction_log_tbl ed WHERE s.payrollDate='$payrollDate' AND ed.payrollDate='$payrollDate' AND e.user_id = s.employeeID AND e.user_id = ed.employeeID AND e.user_status=1 and lc.leave_user_id=e.user_id GROUP BY e.user_id, lc.leave_user_id";
	
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) : // loop for updating
		while($row = $result->fetch_assoc()) :
	//			print_r($row);
			$civilStatus = ($row["civil_status"]==1) ? "S" : "M";
			$dependent = ($row["dependents"]==0) ? "" : $row["dependents"];
			$ytdEarnings = 0;
			$ytdDeductions = 0;
			$ytdTax = 0;
			$benifitDeductions = 0;
			$year = explode("-",$payrollDate)[0];
			$sickLeave = $row["sickLeave"];
			$vacationLeave = $row["vacationLeave"];
			$leave_earned = $row['leave_earned'];
			$benifitDeductions = $row['benifitDeduction'];


			$sql2 = "SELECT sum(ed.earnings) AS ytdEarnings,sum(ed.tax) as ytdTax,(sum(ed.benifitDeduction)) AS ytdAllDeductions FROM earning_deduction_log_tbl ed WHERE ed.employeeID=".$row['user_id']." AND YEAR('$payrollDate') = $year AND payrollDate <= '$payrollDate' ORDER BY ed.employeeID";
			$result2 = $conn->query($sql2);//execute query
			if ($result2->num_rows > 0) {// loop for updating
				while($row2 = $result2->fetch_assoc()) {
					$ytdEarnings = $row2['ytdEarnings'];
					$ytdDeductions = $row2['ytdAllDeductions'];
					$ytdTax = $row2['ytdTax'];
				}
			}
			
?>
	<div class="innerWrap">
		<div class="infoArea">
			<p class="p_confidential">PRIVATE and CONFIDENTIAL</p>
			<table class="employee_info">
				<tr>
					<th class="company_name">Company Name:</th>
					<td class="txt_bold">Transcosmos Asia Philippines, INC.</td>
					<th class="pay_period">Pay Period:</th>
					<td class="txt_bold"><?php echo date('d F Y', strtotime($payrollDate));; ?></td>
				</tr>
				<tr>
					<th class="employee_name">Employee Name</th>
					<td class="txt_bold"><?php echo $row["name"]; ?></td>
					<th class="team">Team:</th>
					<td class="txt_bold">Services</td>
				</tr>
				<tr>
					<th>Employee Number:</th>
					<td class="txt_bold"><?php echo $row["emp_no"]; ?></td>
					<th>Tax Status:</th>
					<td class="txt_bold"><?php echo $civilStatus.$dependent; ?></td>
				</tr>
			</table>
		</div>

		<div class="computationArea ovh">
			<div class="earnings fl">
				<h2 class="txt_bold earnings">EARNINGS:</h2>
				<table>
					<?php
					// very important line of code...
					foreach(getEarningFields($conn) as $earnField){
						$val = $row[str_replace(' ','_',$earnField)];
						echo "<tr>
							<th class='w20'>$earnField</th>
							<td>$val</td>
							</tr>";
					}

					?>
					<tr class="totalEarnings">
						<th class="txt_bold">TOTAL EARNINGS</th>
						<td class="txt_bold red"><?php echo $row["earnings"]; ?></td>
					</tr>
				</table>

				<h2 class="txt_bold YTD">YTD</h2>

				<table class="leaves">
					<tr>
						<th rowspan="2" class="txt_bold w50">Leave type</th>
						<td colspan="3" class="w50 txt_bold">YTD as of this pay-period</td>
					</tr>
					<tr>
						<td class="w50">earned</td>
						<td class="w50">availed</td>
						<td class="txt_bold w50">YTD balance</td>
					</tr>
					<tr>
						<th class="w50">vacation leave</th>
						<td class="w50"><?php echo $leave_earned; ?></td>
						<td class="w50"><?php echo $vacationLeave; ?></td>
						<td class="w50 txt_bold"><?php echo ($leave_earned-$vacationLeave); ?></td>
					</tr>
					<tr>
						<th class="w50">sick leave </th>
						<td class="w50"><?php echo $leave_earned; ?></td>
						<td class="w50"><?php echo $sickLeave; ?></td>
						<td class="w50 txt_bold"><?php echo ($leave_earned-$sickLeave); ?></td>
					</tr>
				</table>
			</div>


			<div class="earnings fl">
				<h2 class="txt_bold earnings">DEDUCTIONS:</h2>
				<table>
					<?php
					// very important line of code...
					foreach(getDeductionFields($conn) as $dedField){
						$val = $row[str_replace(' ','_',$dedField)];
						echo "<tr>
							<th class='w20'>$dedField</th>
							<td>$val</td>
							</tr>";
					}

					?>
					<tr class="totalDeductions">
						<th class="txt_bold">TOTAL DEDUCTIONS</th>
						<td class="txt_bold red"><?php echo $row["totalDeduction"]; ?></td>
					</tr>
					<tr class="netPay01">
						<th class="w20 txt_bold">NET PAY</th>
						<td class="txt_bold red"><?php echo $row["netPay"]; ?></td>
					</tr>
				</table>

				<div class="netpay">
					<table class="leaves">
						<tr>

							<td colspan="2" class="w30 txt_bold">this pay-period</td>
							<td class="txt_bold w50">YTD balance</td>
						</tr>
						<tr>
							<th class="txt_bold w50">Earnings</th>
							<td class="w50"><?php echo $row["earnings"]; ?></td>
							<td class="w50 txt_bold"><?php echo $ytdEarnings; ?></td>

						</tr>
						<tr>
							<th class="w50">Withholding Tax</th>
							<td class="w50"><?php echo $row["tax"]; ?></td>
							<td class="w50 txt_bold"><?php echo $ytdTax; ?></td>
						</tr>
						<tr>
							<th class="w50">SSS, PHIC, HDMF</th>
							<td class="w50"><?php echo $benifitDeductions; ?></td>
							<td class="w50 txt_bold"><?php echo $ytdDeductions; ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<p class="infotxt">* This certifies that all earnings, deductions and YTD leave balances are true and correct and should not be disclosed to other individuals.</p>
		<p class="infotxt">* The transcosmos Asia Philippines Inc. payslip form stands as a legitimate support document for any legal purposes it September serve.</p>

		<div class="tableSign">
			<table class="sign">
				<tr>
					<th class="txt_bold underline">Marideth C. Castalone</th>
				</tr>
				<tr>
					<th>Human Resources Team Supervisor</th>
				</tr>
				<tr>
					<th>Payroll and Benefits</th>
				</tr>
			</table>
		</div>
	</div>
<?php
//	break;
	endwhile;
endif;
?>
</body>
</html>