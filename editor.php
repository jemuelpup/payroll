employeeID-base$Salary-retroAd$justment-excessUnpaidLeave-overtime-overtimeAd$justment-languagePremium-nightDifferential-nightDifferentialAd$j-cashAllowances-riceSubsidy-employeeReferral-incentive-month13thPayNonTaxable-month13thPayTaxable-vacationLeaveNonTaxable-vacationLeaveTaxable-sickLeaveTaxable-performanceBonusNonTaxable-performanceBonusTaxable




$classess = "Company Name:,Pay Period:,Employee Name:,Team:,Employee Number:,Tax Status:,Base $Salary,SSS
Retro Ad$justment,PHIC,Excess / Unpaid Leave,HDMF,Overtime,HDMF Contribution - Additional,Sick Leave - Taxable,Overtime Ad$justment,Income Tax,Rice Subsidy - non taxable,Language Premium,Tax Refund,All other earnings - taxable,Night Differential,SSS $Salary Loan,Night Differential Ad$j,HDMF Loan,Cash Allowances,Other Deductions,Rice Subsidy,Employee Referral,Incentive,13th Month Pay - Non Taxable,13th Month Pay - Taxable,Vacation Leave - Non Taxable,Vacation Leave - Taxable,Sick Leave - Taxable,Performance Bonus - Non Taxable,Performance Bonus - Taxable";

$classes = explode(",",$classess);

foreach($classes as $class){
$class = str_replace(' ', '', $class);
$class = str_replace(':', '', $class);
$class = str_replace('/', '', $class);
$class = lcfirst($class);
echo "'$class',";
}


