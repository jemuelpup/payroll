<?php

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
		// Get the emp number of the employees selected at employee_tbl
		$sql = "SELECT id,dependent FROM `employee_tbl` WHERE division = $department AND active=1";
		$result = $conn->query($sql);//execute query
		if ($result->num_rows > 0) { // loop for updating
			while($row = $result->fetch_assoc()) {
				$empID = $row["id"];
				$earningsForTax = 0;
				$deductionForTax = 0;
				$dependent = 0;
				$tax = 0;
				$dependent = (int)$row["dependent"];
				$earningsForTax = getEarnings($conn,$earningFields,$empID,$payrollDate);
				$deductionForTax = getDeductions($conn,$deductionForTaxFields,$empID,$payrollDate);
				$tax = round(getTax($earningsForTax, $deductionForTax, $dependent), 2);
				//				echo "$tax-";
				$sql = "UPDATE `salarylog_tbl` SET `income_tax`=$tax WHERE employeeID = $empID AND payrollDate='$payrollDate'";
				$conn->query($sql);

				$sql = "INSERT INTO `earning_deduction_log_tbl`(`employeeID`, `earnings`, `tax`, `benifitDeduction`, `payrollDate`) VALUES ($empID,$earningsForTax,$tax,$deductionForTax,'$payrollDate') ON DUPLICATE KEY UPDATE `earnings` = $earningsForTax, `tax` = $tax, `benifitDeduction`=$deductionForTax";
				$conn->query($sql);
			}
		}
		echo "Payroll tax assignment done.";
	}
	else{
		echo "No record found.";
	}
}

?>