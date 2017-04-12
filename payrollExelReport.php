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
	$baseSalary_Basic=0;
	$baseSalary_RetroAdjustment=0;
	$baseSalary_ExcessUnpaidLeaves=0;
	$overtime_Current=0;
	$overtime_RetroAdjustment=0;
	$nightDiff_Current=0;
	$nightDiff_RetroAdjustment=0;
	$LanguagePremium=0;
	$cashAllowances_Taxable=0;
	$riceSubsidy_NonTaxable=0;
	$thirteenthMonthPay_Taxable=0;
	$thirteenthMonthPay_NonTaxable=0;
	$performanceBonus_Taxable=0;
	$performanceBonus_NonTaxable=0;
	$vacationLeave_Taxable=0;
	$vacationLeave_NonTaxable=0;
	$SickLeave=0;
	$EmployeeReferralFee=0;
	$Incentive =0;
	$totalIncome_Taxable=0;
	$totalIncome_NonTaxable=0;
	$sssContribution_Employee=0;
	$sssContribution_Employer=0;
	$sssContribution_Total=0;
	$hdmpContribution_Employee=0;
	$hdmpContribution_Employer=0;
	$hdmpContribution_Total=0;
	$phicContribution_Employee=0;
	$phicContribution_Employer=0;
	$phicContribution_Total=0;
	$totalContribution_Employee=0;
	$totalContribution_Employer=0;
	$withholdingTax=0;
	$taxRefund=0;
	$SSSsalaryLoan=0;
	$HDMFMultipurposeLoan=0;
	$OtherDeductions=0;
	$TotalDeductions=0;
	$netPay=0;
	$finalNetPay=0;
	


	$payrollDate = $_GET["payrollDate"];


	// add the  other features found at the notpad in your desktop
	$sql = "
SELECT e.user_id, e.emp_no,e.last_name,e.first_name,e.middle_name,e.nickname, e.gender_id ,e.civil_status,e.birth_date, e.orig_hire_date, e.hire_date,e.regularization_date, d.division, e.team_id, e.account_id, e.tin, e.sss_no, e.hdmf_no, e.phic_no, e.monthly_rate,
r.role_title ,
r.level ,
t.team,
s.base_salary,
s.retro_adjustment,
s.excess_or_unpaid_leave,
s.overtime,
s.night_differential,
s.language_premium,
s.cash_allowances,
s.rice_subsidy,
s.thirteenth_month_pay_taxable,
s.thirteenth_month_pay,
s.performance_bonus_taxable,
s.performance_bonus,
s.vacation_leave,
s.vacation_leave_taxable,
s.sick_leave_taxable,
s.employee_referral,
s.incentive,
s.sss,
s.phic,
s.hdmf,
s.sss_employer,
s.phic_employer,
s.hdmf_employer,
(s.sss+s.sss_employer) as totalSSSContribution,
(s.phic+s.phic_employer) as totalPHICContribution,
(s.hdmf+s.hdmf_employer) as totalHDMFContribution,
(s.sss+s.phic+s.hdmf) as totalEmployeeContribution,
(s.sss_employer+s.phic_employer+s.hdmf_employer) as totalEmployerContribution,
s.tax_refund,
s.sss_salary_loan,
s.hdmf_loan,
s.income_tax,
e.last_promotion_date,


CONCAT (e.last_name,', ',e.first_name,' ',e.middle_name,'.') as name,ed.tax,(ed.benifitDeduction+ed.otherDeduction) as payrollDeduction, MAX(CASE WHEN lc.leave_id = 2 THEN lc.leave_count END) AS sickLeave, MAX(CASE WHEN lc.leave_id = 1 THEN lc.leave_count END) AS vacationLeave, (ed.earnings-ed.tax-ed.benifitDeduction-otherDeduction) AS netPay, (ed.tax+ed.benifitDeduction+otherDeduction) as totalDeduction, ed.earnings, s.*, lc.leave_earned ,ed.benifitDeduction

FROM salarylog_tbl s
INNER JOIN tbl_user e
INNER JOIN tbl_leave_count lc
INNER JOIN earning_deduction_log_tbl ed
INNER JOIN tbl_role r
INNER JOIN tbl_division d
INNER JOIN tbl_team t



WHERE s.payrollDate='$payrollDate' AND ed.payrollDate='$payrollDate' AND e.user_id = s.employeeID AND e.user_id = ed.employeeID AND e.user_status=1 and lc.leave_user_id=e.user_id AND e.role_id = r.role_id AND d.division_id = e.division_id AND t.team_id = e.team_id

GROUP BY e.user_id, lc.leave_user_id";
	/* query before the development
$sql = "
SELECT e.user_id, e.emp_no, CONCAT (e.last_name,', ',e.first_name,' ',e.middle_name,'.') as name,ed.tax,(ed.benifitDeduction+ed.otherDeduction) as payrollDeduction, MAX(CASE WHEN lc.leave_id = 2 THEN lc.leave_count END) AS sickLeave, MAX(CASE WHEN lc.leave_id = 1 THEN lc.leave_count END) AS vacationLeave, (ed.earnings-ed.tax-ed.benifitDeduction-otherDeduction) AS netPay, (ed.tax+ed.benifitDeduction+otherDeduction) as totalDeduction, ed.earnings, s.*, lc.leave_earned ,ed.benifitDeduction FROM salarylog_tbl s INNER JOIN tbl_user e INNER JOIN tbl_leave_count lc INNER JOIN earning_deduction_log_tbl ed WHERE s.payrollDate='$payrollDate' AND ed.payrollDate='$payrollDate' AND e.user_id = s.employeeID AND e.user_id = ed.employeeID AND e.user_status=1 and lc.leave_user_id=e.user_id GROUP BY e.user_id, lc.leave_user_id";
*/
//	echo "$sql";


	$count = 0;
	$htmlCode = "";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows > 0) : // loop for updating
	while($row = $result->fetch_assoc()) :
	//			print_r($row);
	$htmlCode .= "<tr>";
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


	$hiredDate = $row['hire_date'];
	$secondMonth = validateDate($hiredDate) ? date('Y-m-d', strtotime($hiredDate. ' + 60 days')) : "non";
	$thirdMonth = validateDate($hiredDate) ? date('Y-m-d', strtotime($hiredDate. ' + 90 days')) : "non";
	$fifthMonth = validateDate($hiredDate) ? date('Y-m-d', strtotime($hiredDate. ' + 150 days')) : "non";


	$sql2 = "SELECT sum(ed.earnings) AS ytdEarnings,sum(ed.tax) as ytdTax,(sum(ed.benifitDeduction)) AS ytdAllDeductions FROM earning_deduction_log_tbl ed WHERE ed.employeeID=".$row['user_id']." AND YEAR('$payrollDate') = $year AND payrollDate <= '$payrollDate' ORDER BY ed.employeeID";
	$result2 = $conn->query($sql2);//execute query
	if ($result2->num_rows > 0) {// loop for updating
		while($row2 = $result2->fetch_assoc()) {
			$ytdEarnings = $row2['ytdEarnings'];
			$ytdDeductions = $row2['ytdAllDeductions'];
			$ytdTax = $row2['ytdTax'];




		}
	}
	
	$totalSSSContribution = round((double)$row['totalSSSContribution'], 2);
	$totalEmployeeContribution = round((double)$row['totalEmployeeContribution'], 2);
	$totalEmployerContribution = round((double)$row['totalEmployerContribution'], 2);
	
	$count++;

	$gender = ($row['gender_id']==1) ? "M" : "F";
	$htmlCode .= "
		<td>$count</td>
		<td>Local</td>
		<td>".$row['emp_no']."</td>
		<td>".$row['last_name']."</td>
		<td>".$row['first_name']."</td>
		<td>".$row['middle_name']."</td>
		<td>".$row['nickname']."</td>
		<td>$gender</td>
		<td>$civilStatus</td>
		<td>$civilStatus$dependent</td>
		<td>".$row['birth_date']."</td>
		<td>".$row['orig_hire_date']."</td>
		<td>".$row['hire_date']."</td>
		<td>$secondMonth</td>
		<td>$thirdMonth</td>
		<td>$fifthMonth</td>
		<td>".$row['regularization_date']."</td>
		<td>".$row['last_promotion_date']."</td>
		<td>".$row['role_title']."</td>
		<td>".$row['level']."</td>
		<td>".$row['division']."</td>
		<td>".$row['team']."</td>
		<td>".$row['account_id']."</td>
		<td>Number Here</td>
		<td>".$row['tin']."</td>
		<td>".$row['sss_no']."</td>
		<td>".$row['hdmf_no']."</td>
		<td>".$row['phic_no']."</td>
		<td>".$row['monthly_rate']."</td>
		<td>".$row['base_salary']."</td>
		<td>".$row['retro_adjustment']."</td>
		<td>".$row['excess_or_unpaid_leave']."</td>
		<td>".$row['overtime']."</td>
		<td>clarify</td>
		<td>".$row['night_differential']."</td>
		<td>clarify</td>
		<td>".$row['language_premium']."</td>
		<td>".$row['cash_allowances']."</td>
		<td>".$row['rice_subsidy']."</td>
		<td>".$row['thirteenth_month_pay_taxable']."</td>
		<td>".$row['thirteenth_month_pay']."</td>
		<td>".$row['performance_bonus_taxable']."</td>
		<td>".$row['performance_bonus']."</td>
		<td>".$row['vacation_leave']."</td>
		<td>".$row['vacation_leave_taxable']."</td>
		<td>".$row['sick_leave_taxable']."</td>
		<td>".$row['employee_referral']."</td>
		<td>".$row['incentive']."</td>
		<td>".$row['total_taxable_income']."</td>
		<td>".$row['total_non_taxable_income']."</td>
		<td>".$row['sss']."</td>
		<td>".$row['sss_employer']."</td>
		<td>$totalSSSContribution</td>
		<td>".$row['hdmf']."</td>
		<td>".$row['hdmf_employer']."</td>
		<td>".$row['hdmf_contribution_additional']."</td>
		<td>".$row['totalHDMFContribution']."</td>
		<td>".$row['phic']."</td>
		<td>".$row['phic_employer']."</td>
		<td>".$row['totalPHICContribution']."</td>
		<td>$totalEmployeeContribution</td>
		<td>$totalEmployerContribution</td>
		<td>".$row['basis_of_tax']."</td>
		<td>".$row['income_tax']."</td>
		<td>".$row['tax_refund']."</td>
		<td>".$row['sss_salary_loan']."</td>
		<td>".$row['hdmf_loan']."</td>
		<td></td>
		<td>".$row['totalDeduction']."</td>
		<td></td>
		<td></td>
		<td></td>
		"
		//		<td>".$row['']."</td>
		;
	//	break;
	$htmlCode .= "</tr>";
	endwhile;
	endif;

	echo "<table class='payrollDataToPrint'>
<tr>
	<th>Count</th>
	<th>Local</th>
	<th>emp_no</th>
	<th>last_name</th>
	<th>first_name</th>
	<th>middle_name</th>
	<th>nickname</th>
	<th>gender_id</th>
	<th>civil_status</th>
	<th>taxStatus</th>
	<th>birth_date</th>
	<th>orig_hire_date</th>
	<th>hire_date</th>
	<th>2nd month</th>
	<th>3rd month</th>
	<th>5th month</th>
	<th>regularization_date</th>
	<th>Promotion Date</th>
	<th>Role title</th>
	<th>Role level</th>
	<th>division_id</th>
	<th>team</th>
	<th>account_id</th>
	<th>BPI account no</th>
	<th>tin</th>
	<th>sss_no</th>
	<th>hdmf_no</th>
	<th>phic_no</th>
	<th>monthly_rate</th>
	<th>Basic</th>
	<th>Retro Adjustment</th>
	<th>Excess/Unpaid Leaves</th>
	<th>OT Current</th>
	<th>OT Retro Adjustment</th>
	<th>NDF Current</th>
	<th>NDF Retro Adjustment</th>
	<th>Language Premium</th>
	<th>Cash allo Taxable</th>
	<th>Rice Sub Non-Taxable</th>
	<th>13th month Taxable</th>
	<th>13th month Non-Taxable</th>
	<th>performanceBonus_Taxable</th>
	<th>performanceBonus_Non-Taxable</th>
	<th>vacationLeave_Taxable</th>
	<th>vacationLeave_Non-Taxable</th>
	<th>Sick Leave</th>
	<th>Employee Referral Fee</th>
	<th>Incentive </th>
	<th>Total Income Taxable</th>
	<th>Total Income Non-Taxable</th>
	<th>sss_Employee</th>
	<th>sss_Employer</th>
	<th>sss_Total</th>
	<th>hdmf_Employee</th>
	<th>hdmf_Employer</th>
	<th>hdmf_HDMFContributionEmployeeAdditional</th>
	<th>hdmf_Total</th>
	<th>phic_Employee</th>
	<th>phic_Employer</th>
	<th>phic_Total</th>
	<th>totalEmployeeContribution</th>
	<th>totalEmployerContribution</th>
	<th>Basis of Tax</th>
	<th>Witholding Tax</th>
	<th>Tax Refund</th>
	<th>SSS salary loan</th>
	<th>HDMF Multipurpose Loan</th>
	<th>Other Deductions</th>
	<th>Total deductions</th>
	<th>NET PAY</th>
	<th>FINAL NET PAY</th>
</tr>


$htmlCode</table>";
	
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
	
	
	function validateDate($date)
	{
		$d = DateTime::createFromFormat('Y-m-d', $date);
		return $d && $d->format('Y-m-d') === $date;
	}
	
	?>
</body>
</html>