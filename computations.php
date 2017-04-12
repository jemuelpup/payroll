<?php
/*
Study the following

	SELECT


	(CASE WHEN lc.leave_earned>=5 AND lc.leave_id = 1 THEN ROUND((u.monthly_rate * 12/ 261*5),2) WHEN lc.leave_id = 1 THEN ROUND((u.monthly_rate * 12/ 261*lc.leave_earned),2) END) as vlpayment,

	(CASE WHEN lc.leave_earned>=5 AND lc.leave_id = 2 THEN ROUND((u.monthly_rate * 12/ 261*5),2) WHEN lc.leave_id = 2 THEN ROUND((u.monthly_rate * 12/ 261*lc.leave_earned),2) END) as slpayment,

	u.user_id,
	(u.monthly_rate * 12/ 261) as daily_rate,
	lc.leave_earned
		FROM
		tbl_user u, tbl_leave_count lc
			WHERE
			u.regularization_date < NOW() and u.regularization_date != 0000-00-00 and
			lc.leave_id = 1 and lc.leave_user_id = u.user_id


// this is is right sql code
	SELECT a.leave_user_id, sumsl, sumvl
	FROM
	(SELECT leave_user_id, sum(leave_earned) as sumvl FROM `tbl_leave_count` WHERE leave_id = 1 GROUP BY leave_user_id) as a  INNER JOIN
	(SELECT leave_user_id, sum(leave_earned) as sumsl FROM `tbl_leave_count` WHERE leave_id = 2 GROUP BY leave_user_id) as b ON a.leave_user_id = b.leave_user_id

	;


*/
/***********************************************
 * @description...Computation of tax,phic,pagibig,leaves, overtime rates, and overtime minutes
 * @author     ...Jemuel Elimanco
 * @version    ...Release: v00.05(stable)
***********************************************/

require('connect.php');

/*
check the problem in this query
SELECT a.employeeID, ROUND(DATEDIFF(CONCAT(YEAR(b.latest_payroll_date),'-12-31'), b.latest_payroll_date)/15) AS payrolls_left, a.base_salary, b.latest_payroll_date, b.ytd_salary, ((ROUND(DATEDIFF(CONCAT(YEAR(b.latest_payroll_date),'-12-31'), b.latest_payroll_date)/15)*a.base_salary)+b.ytd_salary)/12 as thirteenthMonthPay FROM salarylog_tbl a INNER JOIN( SELECT employeeID, MAX(payrollDate) as latest_payroll_date,payrollDate,SUM(base_salary) as ytd_salary, base_salary FROM salarylog_tbl GROUP BY employeeID ) b ON a.employeeID = b.employeeID AND a.payrollDate = b.latest_payroll_date GROUP BY a.employeeID,a.payrollDate asc
*/

// UNDER CONSTRUCTION
//setThirteenthMonthPay($conn);
function setThirteenthMonthPay($conn){
	$sql = "
	SELECT
	a.employeeID, ROUND(DATEDIFF(CONCAT(YEAR(b.latest_payroll_date),'-12-31'), b.latest_payroll_date)/15) AS payrolls_left, a.base_salary, b.latest_payroll_date, b.ytd_salary
	FROM salarylog_tbl a
	INNER JOIN(
	SELECT employeeID, MAX(payrollDate) as latest_payroll_date,payrollDate,SUM(base_salary) as ytd_salary, base_salary
	FROM
	salarylog_tbl
	GROUP BY employeeID
	) b
	ON a.employeeID = b.employeeID AND a.payrollDate = b.latest_payroll_date
	GROUP BY a.employeeID,a.payrollDate asc
	";
	$thirteenthMonthPay = 0;
	$result = $conn->query($sql);
	if($result->num_rows> 0){ // if date is holly day
		while($row = $result->fetch_assoc()) {
			$employeeID = $row['employeeID'];
			$payrolls_left = (float)$row['payrolls_left']; // mustiply this to the base salary
			$base_salary = (float)$row['base_salary'];
			$latest_payroll_date = $row['latest_payroll_date'];
			$ytd_salary = (float)$row['ytd_salary'];
			
			$thirteenthMonthPay = round((($base_salary*$payrolls_left)+$ytd_salary)/12,2);// 24 months payments including the salary before increase or decrease
			
			
			
			$sql = "UPDATE salarylog_tbl SET thirteenth_month_pay = $thirteenthMonthPay WHERE employeeID = $employeeID";
			echo "$sql<br>";
			$conn->query($sql);
		}
	}
}


//setLeavePayment($conn,"2017-04-15");
/*format of the day must be yyyy-mm-dd*/

/***************************************************************
* This function sets the leave payment of the regular employee
****************************************************************/
function setLeavePayment($conn,$payrollDate){
	$day = date('d',strtotime($payrollDate));
	$year = date('Y',strtotime($payrollDate));
	if($day<16){// means 1-15
		$fromDate = date(' Y-m-d', strtotime(substr($payrollDate, 0, -2) . '01'));
		$toDate = date(' Y-m-d', strtotime(substr($payrollDate, 0, -2) . '15'));
	}
	else{// means 16-31
		$fromDate = date(' Y-m-d', strtotime(substr($payrollDate, 0, -2) . '16'));
		$toDate = date(' Y-m-t', strtotime($payrollDate));
	}
	$sql = "
	SELECT a.leave_user_id, sumsl, sumvl, (CASE WHEN sumvl>=5 THEN ROUND((u.monthly_rate * 12/261*5),2) ELSE ROUND((u.monthly_rate * 12/261*sumvl),2) END) as vleave_payment, (CASE WHEN sumvl>=5 THEN ROUND((u.monthly_rate * 12/261*5),2) ELSE ROUND((u.monthly_rate * 12/261*sumsl),2) END) as sleave_payment FROM tbl_user u, (SELECT leave_user_id, (sum(leave_earned)-sum(leave_count)) as sumvl FROM `tbl_leave_count` WHERE leave_id = 1 GROUP BY leave_user_id) as a INNER JOIN (SELECT leave_user_id, (sum(leave_earned)-sum(leave_count)) as sumsl FROM `tbl_leave_count` WHERE leave_id = 2 GROUP BY leave_user_id) as b ON a.leave_user_id = b.leave_user_id WHERE u.regularization_date < NOW() and u.regularization_date != 0000-00-00 and a.leave_user_id = u.user_id
	";
	// set where date...;
	$result = $conn->query($sql);
	if($result->num_rows> 0){ // if date is holly day
		while($row = $result->fetch_assoc()) {
			$vlpayment = $row['vleave_payment'];
			$slpayment = $row['sleave_payment'];
			$userID = $row['leave_user_id'];
			$sql = "
			INSERT INTO tbl_leave_payments(user_id, vleave_payment, sleave_payment, year) VALUES ($userID,$vlpayment,$slpayment,'$payrollDate') ON DUPLICATE KEY
			UPDATE vleave_payment=$vlpayment,sleave_payment=$slpayment
			";
			$conn->query($sql);
		}
	}
}

/*********************c***************************
* check what kind of holly day is this
* returns the hollydayRate multiplyer
*************************************************/
function getHollyDayRateMultiplyer($conn,$date){
	$hollyDayMultiplyer = 1; // if 1 is retruned value, it means that this day is not holly day
	$sql = "SELECT holiday_type FROM `tbl_holiday` WHERE holiday = '$date';";
	$result = 0;
	$result = $conn->query($sql);
	if($result->num_rows> 0){ // if date is holly day
		while($row = $result->fetch_assoc()) {
			$holiday_type = $row["holiday_type"];

			if($holiday_type=='1'){//1 - means special
				$hollyDayMultiplyer = 1.3;
			}
			elseif($holiday_type=='2'){//2 - means it is regular hollyday
				$hollyDayMultiplyer = 2;
			}
			elseif($holiday_type=='3'){//3 - means double holliday
				$hollyDayMultiplyer = 3;
			}
		}
	}
	return $hollyDayMultiplyer;
}

/************************************************
* checks if the given date is hollyday
* returns true if hollyday false otherwise
************************************************/
function isHollyDay($conn,$date){
	$sql = "SELECT holiday_type FROM `tbl_holiday` WHERE holiday = '$date';";
	$result = $conn->query($sql);
	if($result->num_rows> 0){ // if date is holly day
		return true;
	}
	return false;
}

/************************************************
* checks if the function call has overtime
* returns true if $shiftDayType="rest" or $workType="overTime" or date is Holly Day false otherwise
************************************************/
function hasOvertime($conn,$shiftDayType,$workType,$date){
	if($shiftDayType=="rest" or $workType=="overTime" or isHollyDay($conn,$date)){
		return true;
	}
	return false;
}

/*******************************************************************************************************
* This function retruns the work hour key
* Parameters:
* 	$shiftDayType - "regular" or "rest"(String),
* 	$workType - "overTime" or "regularShift",
* 	$isNightDif - true | false (Boolean),
* 	$hollidayType - (double) (1.3-special)(2-regular)(3-double)
*******************************************************************************************************/
function getWorkHourKey($shiftDayType,$workType,$isNightDif,$hollidayType){
	$arrayVarKey = "";
	// this is for checking the dayshift type
	if($shiftDayType=="regular"){
		$arrayVarKey.="RG_";
	}
	elseif($shiftDayType=="rest"){
		$arrayVarKey.="RS_";
	}
	// this is for checking the worktype
	if($workType=="overTime"){
		$arrayVarKey.="OT_";
	}
	elseif($workType=="regularShift"){
		$arrayVarKey.="RSF_";
	}
	// this is for checking the nightdiff
	if($isNightDif){
		$arrayVarKey.="NDC_";
	}
	else{
		$arrayVarKey.="RDC_";
	}
	// this is for checking the holliday
	if($hollidayType==1.3){//(1.3-special)
		$arrayVarKey.="SH";
	}
	elseif($hollidayType==2){//(2-regular)
		$arrayVarKey.="RH";
	}
	elseif($hollidayType==3){//(3-double)
		$arrayVarKey.="DH";
	}
	if(substr($arrayVarKey, -1)=="_"){
		$arrayVarKey = rtrim($arrayVarKey, "_");//remove the _
	}
	return $arrayVarKey;
}

/*******************************************************************************************************
* This function returns the data needed to record on the database
* Returns array with two index.
* ->first index returns timespent on specific overtime range
* ->second index returns an associative array with values for:
*	1) regular shift - array("RegularHours"=>(val), "nightDiffHours"=>(val),"RegularPay"=>(val), "nightDiffPay"=>(val)) (if input is regular hours)
*	2) overtime shift - array("OverTimeHours"=>(val)), "nightDiffHours"=>(val)),"OverTimePay"=>(val)), "nightDiffPay"=>(val))); (if input is overtime hours)
* Parameters:
* 	$conn - Database connection,
* 	$shiftBeginning - beginning time(24Hour format)(HH:MM)(String),
* 	$shiftEnd - end time(24Hour format)(HH:MM)(String),
* 	$shiftDayType - "regular" or "rest"(String),
* 	$workType - "overTime" or "regularShift",
* 	$date - given date(YYYY-MM-DD)(String),
* 	$ratePerHour - ratePerMonth(Rate per hour is computed as  Base Pay X 12 months / 261 days / 8 hours)(double)
*******************************************************************************************************/
function getWorkHoursAndPay($conn,$shiftBeginning,$shiftEnd,$shiftDayType,$workType,$date,$ratePerHour){// the output must be minute...
	// under development
	// -----------------------------------------------------
	// for time computation
	$timeReportKey = "";
	/***********************************
	$timeReport index name meaning:
	RG - regular day
	RS - rest day
	RSF - regular shift
	OT - overtime shift
	NDC - night differential computation
	RDC - regular hours computation
	SH - special holliday
	RH - regular holliday
	DH - double holliday
	***********************************/
	$timeReport = array(
		"RS"=>0,
		"RG_OT"=>0,
		"RS_RSF"=>0,
		"RS_OT"=>0,
		"RG_OT_NDC"=>0,
		"RG_OT_RDC"=>0,
		"RS_RSF_NDC"=>0,
		"RS_RSF_RDC"=>0,
		"RS_OT_NDC"=>0,
		"RS_OT_RDC"=>0,
		"RG_RSF_NDC_SH"=>0,
		"RG_RSF_NDC_RH"=>0,
		"RG_RSF_NDC_DH"=>0,
		"RG_RSF_RDC_SH"=>0,
		"RG_RSF_RDC_RH"=>0,
		"RG_RSF_RDC_DH"=>0,
		"RG_OT_NDC_SH"=>0,
		"RG_OT_NDC_RH"=>0,
		"RG_OT_NDC_DH"=>0,
		"RG_OT_RDC_SH"=>0,
		"RG_OT_RDC_RH"=>0,
		"RG_OT_RDC_DH"=>0,
		"RS_RSF_NDC_SH"=>0,
		"RS_RSF_NDC_RH"=>0,
		"RS_RSF_NDC_DH"=>0,
		"RS_RSF_RDC_SH"=>0,
		"RS_RSF_RDC_RH"=>0,
		"RS_RSF_RDC_DH"=>0,
		"RS_OT_NDC_SH"=>0,
		"RS_OT_NDC_RH"=>0,
		"RS_OT_NDC_DH"=>0,
		"RS_OT_RDC_SH"=>0,
		"RS_OT_RDC_RH"=>0,
		"RS_OT_RDC_DH"=>0);
	$hollidayRateForTimeComputation = 0;// this is for computing the time in the types of OT
	$currDayNDiffTimeReportKey = "";
	$nextDayNDiffTimeReportKey = "";
	$currDayTimeReportKey = "";
	$nextDayTimeReportKey = "";

	$breakTimeDeductionKey = "";
	$breakTimeDeductionNightDiffKey = "";
	// -----------------------------------------------------
	// fixed
	// Initialization of variables
	$totalRegularMinutes=0;
	$overTimeHours=0;
	$totalNightDiffMinutes=0;
	$regularPay=0;
	$overTimePay=0;
	$nightDiffPay=0;

	$ratePerMinute = $ratePerHour/60;	// rate of employee per minute.
	$overTimeRateAdditional = 1;	// important values in computations
	$nightDifferentialRate = 0.1;	// important values in computations

	$nightDiffCountHour = 0;	// the total hours in nightdiff
	$nightDiffCountMinute = 0;	// the total minute in night diff

	// rates:
	$breakTimeDeductionRate = 0;
	$breakTimeDeduction = 0;
	$breakTimeDeductionNightDiff = 0;
	$currentDayRate = 0;
	$nextDayRate = 0;

	// payments
	$CDP = 0;// current payment
	$CDPN = 0;// current payment night shift
	$NDP = 0;// next day payment
	$NDPN = 0;// next day payment night shift;

	// procesed value variables
	$thisDateIsHollyDay = isHollyDay($conn,$date);
	$hollidayRateForTimeComputation = $thisDateIsHollyDay;// for types of OT
	$hollyDayDateMultiplyer = getHollyDayRateMultiplyer($conn, $date);
	$thisDateIsSpecialDay = false;
	$currentDayRate = $hollyDayDateMultiplyer;

	// the date variables
	$currentDate = strtotime($date);

	$shiftBeginningHour = explode(":", $shiftBeginning);	// the starting time
	$shiftBeginningMinute = (int)$shiftBeginningHour[1];	// returns starting minute
	$shiftBeginningHour = (int)$shiftBeginningHour[0];	// return starting hour

	$shiftEndHour = explode(":", $shiftEnd);	// the end time
	$shiftEndMinute = (int)$shiftEndHour[1];	// returns end minute
	$shiftEndHour = (int)$shiftEndHour[0];	// return end hour

	// Check if this day is your rest day
	if($shiftDayType=="rest" and $hollyDayDateMultiplyer==1.3){
		$hollyDayDateMultiplyer = 1.5;
	}
	elseif($shiftDayType=="rest"){
		$hollyDayDateMultiplyer = $hollyDayDateMultiplyer * 1.3;
	}

	// This block of code is responsible for computing the overtime rate additional
	// ----------------------------------------------------------------------------
	if($thisDateIsHollyDay and $workType=='overTime'){ // means this day is hollyday shift and overtime is on holly day shift
		$overTimeRateAdditional = 1.3;
	}
	elseif($workType=='overTime'){ // means morethan 8 hours of work. Overtime on Regular day shift
		$overTimeRateAdditional = 1.25;
		if($shiftDayType=='rest'){
			$overTimeRateAdditional = 1.3;
		}
	}
	else{ // means regular shift in regular day. not overtime
		$overTimeRateAdditional = 1;
	}
	// ----------------------------------------------------------------------------

	// on the current day, check if this is holly day and get rate for holliday
	$ratePerMinute = $ratePerMinute * $hollyDayDateMultiplyer;

	// for computing the night shift
	// start of computing the night shift --------------------------------------------------------------
	// example: in-20:10 <---*+++*---> out-5:45
	if($shiftBeginningHour>$shiftEndHour){ // You have nightdiff here in separate date
		// get the nightdiff and working hour on current day
		if($shiftBeginningHour>=22){// means you start after 22
			$nightDiffCountMinute += ((24-$shiftBeginningHour)*60)+(-1*$shiftBeginningMinute); // the minute spent on the current day
			$regularMinutes = 0; //0 because you start at the nightdiff. You have no regular working hour on the current day
		}
		else{// means you start before 22
			$nightDiffCountMinute = 120; // the minute spent on the current day. Note: 120 means 2 hours
			$regularMinutes = (22-$shiftBeginningHour)*60 - $shiftBeginningMinute;// the minutes spent before 22
		}
		$totalRegularMinutes += $regularMinutes;//the total regular minutes spent on the shift
		$regularPay += $regularMinutes * $ratePerMinute * $overTimeRateAdditional;// the regular pay before 22. from start to 22
		$CDP += $regularPay;
		$totalNightDiffMinutes += $nightDiffCountMinute; //  the minutes spent after 22
		// pay
		$nightDiffPay += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional * $nightDifferentialRate);// Note 1 in the middle means this is not overtime only 0.1;
		// Remember: computation of payments on currrent day might be different from next day
		$CDPN += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional);// current payment night shift. (payment + night diff)

		//---------------------------------------------------------------------------------------------
		// Compute the  number of hours on the current day

		// getWorkHourKey($shiftDayType,$workType,$isNightDif,$hollidayType);

		$timeReportKey = getWorkHourKey($shiftDayType,$workType,true,$hollidayRateForTimeComputation);// for night diff
		if (array_key_exists($timeReportKey, $timeReport)){
			$timeReport[$timeReportKey] += $nightDiffCountMinute;// change the value of the associative array
			$currDayNDiffTimeReportKey = $timeReportKey;
		}
		$timeReportKey = getWorkHourKey($shiftDayType,$workType,false,$hollidayRateForTimeComputation);// for regularhours
		if (array_key_exists($timeReportKey, $timeReport)){
			$timeReport[$timeReportKey] += $regularMinutes;// change the value of the associative array
			$currDayTimeReportKey = $timeReportKey;
		}
		// Note: reset $hollidayRateForTimeComputation;
		//----------------------------------------------------------------------------------------

		// This is the next day ----------------------------------------------------------------------------------------------------------------------
		// get the date of the next day
		$nightDiffCountMinute = 0;// since this is the next day, you will reset the night diff minute count
		$date1 = str_replace('-', '/', $date);
		$tomorrow = date('Y-m-d',strtotime($date1 . "+1 days"));
		$date = $tomorrow;

		// on the next day, reset the rate per minute and check if this is holly day and get rate for holliday
		$ratePerMinute = $ratePerHour/60;
		$hollyDayDateMultiplyer = getHollyDayRateMultiplyer($conn, $date);
		$nextDayRate = $hollyDayDateMultiplyer;
		$hollidayRateForTimeComputation = $hollyDayDateMultiplyer;// reset $hollidayRateForTimeComputation;

		// check if this shift is your rest day
		if($shiftDayType=="rest" and $hollyDayDateMultiplyer==1.3){// means this day is your rest day and special hollyday
			$hollyDayDateMultiplyer = 1.5;
		}
		elseif($shiftDayType=="rest"){
			$hollyDayDateMultiplyer = $hollyDayDateMultiplyer * 1.3;
		}
		$ratePerMinute = $ratePerMinute * $hollyDayDateMultiplyer;

		// check how many hours you spent on the next day in nightdiff
		if($shiftEndHour<6){// means you end before 6
			$nightDiffCountMinute += ($shiftEndHour*60)+$shiftEndMinute;// num of minute consumed ex 20
			$regularMinutes = 0; // you have no regular min in the next day since you finish after 6
		}
		else{// means you end after 6 // the computation here is correct
			$nightDiffCountMinute += 360; // means 6 hours. from 0 - 6am
			$regularMinutes = ($shiftEndHour-6)*60 + $shiftEndMinute; // the regular computation. ex 7:30 out. $regularMinutes = 90min
			$NDP = $regularMinutes * $overTimeRateAdditional * $ratePerMinute; // the payment computation
			$regularPay += $regularMinutes * $overTimeRateAdditional * $ratePerMinute; // the payment computation
			$totalRegularMinutes += $regularMinutes;//the total regular hours spent on the shift
		}

		// Get the multiplyer of the next day(Check its holly day type)
		$totalNightDiffMinutes += $nightDiffCountMinute;

		// overall nightdiff
		$nightDiffPay += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional * $nightDifferentialRate);// Note 1 in the middle means this is not overtime only 0.1;
		$NDPN += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional);


		//----------------------------------------------------------------------------------------------
		// hour report computation
		$timeReportKey = getWorkHourKey($shiftDayType,$workType,true,$hollidayRateForTimeComputation);// for night diff
		if (array_key_exists($timeReportKey, $timeReport)){
			$timeReport[$timeReportKey] += $nightDiffCountMinute;// change the value of the associative array
			$nextDayNDiffTimeReportKey = $timeReportKey;
		}
		$timeReportKey = getWorkHourKey($shiftDayType,$workType,false,$hollidayRateForTimeComputation);// for regularhours
		if (array_key_exists($timeReportKey, $timeReport)){
			$timeReport[$timeReportKey] += $regularMinutes;// change the value of the associative array
			$nextDayTimeReportKey = $timeReportKey;
		}
		//-------------------------------------------------------------------------------------------
	}
	// example: in-22:30 <---*+++*---> out-24:00
	elseif($shiftBeginningHour>=22 and $shiftEndHour>=22 and $shiftEndHour<=24){// means your shift or overtime in and out is on nightdiff
		$nightDiffCountMinute = (($shiftEndHour-$shiftBeginningHour)*60) + ($shiftEndMinute-$shiftBeginningMinute);
		$totalNightDiffMinutes = $nightDiffCountMinute;
		$nightDiffPay += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional * $nightDifferentialRate); // 0.1 only
		$CDPN += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional);

		//----------------------------------------------------------------------------------------------
		// hour report computation
		$timeReportKey = getWorkHourKey($shiftDayType,$workType,true,$hollidayRateForTimeComputation);// for night diff
		if (array_key_exists($timeReportKey, $timeReport)){
			$timeReport[$timeReportKey] = $nightDiffCountMinute;// change the value of the associative array
		}

		//-------------------------------------------------------------------------------------------

	}
	// means you start on or before 00:00 and finish on or before 24. The typical scenario...
	else{// cpmputation for night differential

		if(($shiftEndHour-22)>=0){ // means you have night diff on current day (22-24)// if this is negative, means, you have no night diff on this time range
			$nightDiffCountMinute += (($shiftEndHour-22)*60) + $shiftEndMinute;// example 23:10 = 10
		}
		if((6-$shiftBeginningHour)>=0){ // means you have night diff on current day (0-6)// if this is negative, means, you have no night diff on this time range
			$nightDiffCountMinute += ((6-$shiftBeginningHour)*60)-$shiftBeginningMinute;// example 5:20 = -20
		}

		$totalNightDiffMinutes = $nightDiffCountMinute;
		$nightDiffPay += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional * $nightDifferentialRate);// only 0.1
		$CDPN += ($nightDiffCountMinute * $ratePerMinute * $overTimeRateAdditional);

		$numberOfMinuteWorked = (($shiftEndHour-$shiftBeginningHour)*60)+($shiftEndMinute-$shiftBeginningMinute); // time spent from shift start to finish
		$regularMinutes = $numberOfMinuteWorked - $totalNightDiffMinutes;
		$totalRegularMinutes = $regularMinutes; // regularHours = totalwork - nightdiff
		$regularPay += $totalRegularMinutes * $ratePerMinute * $overTimeRateAdditional;// the regular pay before 22 and after 6
		$CDP += $regularPay;

		$timeReportKey = getWorkHourKey($shiftDayType,$workType,true,$hollidayRateForTimeComputation);// for night diff
		if (array_key_exists($timeReportKey, $timeReport)){
			$timeReport[$timeReportKey] += $nightDiffCountMinute;// change the value of the associative array
		}
		$timeReportKey = getWorkHourKey($shiftDayType,$workType,false,$hollidayRateForTimeComputation);// for regularhours
		if (array_key_exists($timeReportKey, $timeReport)){
			$timeReport[$timeReportKey] += $regularMinutes;// change the value of the associative array
		}

	}

	// end of computing the night shift ----------------------------------------------------
	// this if block is used only on regular shift. This block of code is not used in overtime computation.
	// TODO: decide where you will deduct the 1 hour break
	if($workType=="regularShift"){// rule: regular shift is 8 hours only
		// check where will you deduct the breaktime.
		if($currentDayRate > $nextDayRate){
			$breakTimeDeductionRate = $nextDayRate;
			$breakTimeDeductionNightDiffKey = $nextDayNDiffTimeReportKey;
			$breakTimeDeductionKey = $nextDayTimeReportKey;
		}
		else{
			$breakTimeDeductionRate = $currentDayRate;
			$breakTimeDeductionNightDiffKey = $currDayNDiffTimeReportKey;
			$breakTimeDeductionKey = $currDayTimeReportKey;
		}

		// this if is only for undertime...
		if($totalRegularMinutes<60){ // if regular hours is less than 60, deduct the other minutes breaktime to night shift spent. means you undertime
			$totalRegularMinutes -= 60;// the value of $totalRegularMinutes is from 0-59. So the value of this variable will become negative.
			// means when you subtract 60 to totalRegularMinutes, the value is negative.
			$totalNightDiffMinutes += $totalRegularMinutes;// add the negative value to night shift
			$breakTimeDeduction = $breakTimeDeductionRate*$totalRegularMinutes*($ratePerHour/60);// the value of this variable is negative. ex:-20min * hollydayDeductionPercentage * RPerMin.
			$breakTimeDeductionNightDiff = $breakTimeDeduction*0.1;
			// check where to deduct the breaktime(night diff hours)
			// set all the regular minnutes in regular time computation to 0
			$timeReport[$nextDayTimeReportKey]=0;
			$timeReport[$currDayTimeReportKey]=0;
			if($currentDayRate > $nextDayRate){
				$timeReport[$nextDayNDiffTimeReportKey] += $totalRegularMinutes;// ex: 480 +=(-15) => 465
			}
			else{
				$timeReport[$currDayNDiffTimeReportKey] += $totalRegularMinutes;// ex: 480 +=(-15) => 465
			}
			$totalRegularMinutes = 0;
		}
		else{// deduct the breaktime in regular hours spent
			$totalRegularMinutes -= 60; // this is for break time.
			$breakTimeDeduction = 60*$ratePerMinute*(-1);
			// check if current regular hours plus next day hours is = 60. If true, set them both to 0
			$totalRegularHoursForTimeRep = $timeReport[$currDayTimeReportKey]+$timeReport[$nextDayTimeReportKey];
			//-------------------------------------------------------------------------------------
			if($totalRegularHoursForTimeRep>=60){// means regular hours might be 60 68, 100, 120, 299 etc
				$timeReport[$currDayTimeReportKey] -= 60;// thie value of the timeReport[$currDayTimeReportKey] will become negative
				if($timeReport[$currDayTimeReportKey]<0){// if current time becomes negative, deduct it to the next day
					$timeReport[$nextDayTimeReportKey] += $timeReport[$currDayTimeReportKey];
					$timeReport[$currDayTimeReportKey] = 0;
				}
			}
			//-------------------------------------------------------------------------------------
		}

		if($totalRegularMinutes==0){// set NDP and CDP to 0 since you dont have regular hour minutes.
			$NDP = 0;//next day pay(regular hours)
			$CDP = 0;//current day pay(regular hours)
		}

		$regularPay = $CDP + $CDPN + $NDP + $NDPN+$breakTimeDeduction;//($ratePerHour/60*8) // this is your pay for that day// $breakTimeDeduction is negative

		if(!($shiftDayType=="rest" or $workType=="overTime")){
			$regularPay -= ($ratePerHour*8);//($ratePerHour/60*8) // this is your pay for that day
		}
		$nightDiffPay += $breakTimeDeductionNightDiff;// remember, the value of $breakTimeDeductionNightDiff is negative.
		$payrollData = array("RegularHours"=>$totalRegularMinutes, "nightDiffHours"=>$totalNightDiffMinutes,"RegularPay"=>$regularPay, "nightDiffPay"=>$nightDiffPay);

	}

	if($workType=="overTime"){
		if($shiftDayType=="rest"){// if overtime is done on restday, add tha night diff total amount to the regular pay
			$regularPay += $nightDiffPay*10;
		}
		$nightDiffPay += $breakTimeDeductionNightDiff;// remember, the value of $breakTimeDeductionNightDiff is negative.
		//		new updates - add the night diffovertime pay to the 
		$nightDiffPay *= 11; // move the decimal point by tenths and add the night diff and add the 10% of the overtime night diff. 10% + 1% = 11% multiplyer
		$regularPay += $nightDiffPay;
		$nightDiffPay = 0;
		$payrollData = array("OverTimeHours"=>$totalRegularMinutes, "nightDiffHours"=>$totalNightDiffMinutes,"OverTimePay"=>$regularPay, "nightDiffPay"=>$nightDiffPay);
	}

	return array($timeReport,$payrollData);
}

/**************************************************
* This function sets the leaves of the employees.
**************************************************/
//setEmployeeLeaves($conn,'2017-02-28');
setEmployeeLeaves($conn,date('Y/m/d', time()));

function setEmployeeLeaves($conn,$cutOffDate){
	$sql = "SELECT user_id as `id`, `hire_date`, `regularization_date`, `employmentStatus` FROM `tbl_user` WHERE 1";
	$result = $conn->query($sql);
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$hire_date = $row["hire_date"];
			$regularization_date = $row["regularization_date"];
			//			echo "<<$hire_date>>>$regularization_date>><br>";
			$employmentStatus = $row["employmentStatus"];
			$leaveEarned = 0;
			if($hire_date!="0000-00-00"){
				$leaveEarned = getLeaveEarned($id,$hire_date,$regularization_date,$employmentStatus,$cutOffDate);
				// highest possible leave is 22
				if($leaveEarned>22.5){
					$leaveEarned = 22.5;// make it leave is 22 if it reach it.
				}
			}
			$sql = "INSERT INTO `tbl_leave_count`(`leave_user_id`, `leave_id`, `leave_earned`) VALUES ($id,1,$leaveEarned) ON DUPLICATE KEY UPDATE `leave_user_id`=$id,`leave_id`=1,`leave_earned`=$leaveEarned"; // for vacation leave
			$conn->query($sql);
			$sql = "INSERT INTO `tbl_leave_count`(`leave_user_id`, `leave_id`, `leave_earned`) VALUES ($id,2,$leaveEarned) ON DUPLICATE KEY UPDATE `leave_user_id`=$id,`leave_id`=2,`leave_earned`=$leaveEarned"; // for sickLeave
			$conn->query($sql);
		}
	}
}
/********************************************************
* This function returns the leave accumulated by the employee
********************************************************/
function getLeaveEarned($empID,$empHiredDate,$empRegularizationDate,$employmentStatus,$cutOffDate){

	$regularEmployee = 1;
	$leaveAccumulated = 0;
	$availedLeavePerCutoff = 1.25; // depends on whatever you want
	$availedLeavePerCutoff = 0.625; // depends on whatever you want
	$cutoffLeaveEarned = 0;

	//	$currentDate = time();
	$currentDate = strtotime($cutOffDate);
	$currentMonth = date('m', $currentDate);
	$currentYear = date('Y', $currentDate);
	$currentDay = date('d', $currentDate);

	$hiredDate = strtotime($empHiredDate);// changable - must be dynamic
	$hiredYear = date('Y', $hiredDate);

	$regularizationDate = strtotime($empRegularizationDate);// changable - must be dynamic
	$regularizationYear = date('Y', $regularizationDate);

	$dateDiffHiredToCurrent = $currentDate - $hiredDate;
	$dateDiffRegularizationToCurrent = $currentDate - $regularizationDate;

	$daysHired = ($dateDiffHiredToCurrent / 86400);
	$monthsHired = ($daysHired/30.417);
	$yearsHired = floor($monthsHired/12);

	$daysRegularized = ($dateDiffRegularizationToCurrent / 86400);
	$monthsRegularized = ($daysRegularized/30.417);
	$yearsRegularized = floor($monthsRegularized/12);

	$employeeIsRegular = true;

	$addedLeave = 0;//number of months from hired date to december of the past year from current
	$deductedLeave = 0;
	$sql = "";
	// compute the sl/vl to be payed to the employee
	// delete date accumulated. Replace by the value below
	$cutoffLeaveEarned = $currentMonth*2; // remove the *2 if you will use 1.25 $cutoffLeaveEarned
	if($currentDay>=1 and $currentDay<=15){
		$cutoffLeaveEarned -= 1;
	}

	$cutoffLeaveEarned *= $availedLeavePerCutoff;

	if($employmentStatus == $regularEmployee){ // if employee is regular //  FIX THIS. It can cause error...
		if($currentYear==$regularizationYear){// ex 2016 regularization; 2016 current year
			if($hiredYear<$regularizationYear){// ex: 2015-hired date; 2016-regularization date; 2016 current year;
				$lastYearDecember = strtotime(($regularizationYear-1).'-12-31');
				$dateDiffHiredToDecLastYear = round(($lastYearDecember - $hiredDate)/ 86400);// returns day
				$addedLeave = round($dateDiffHiredToDecLastYear/15)*$availedLeavePerCutoff; // leave earned to be added
				return $cutoffLeaveEarned + $addedLeave;
			}
			else{// ex: 2016-hired date; 2016-regularization date; 2016 current year;
				$thisYearJanuary = strtotime(($regularizationYear).'-01-01');
				$dateDiffHiredToDecLastYear = round(($hiredDate - $thisYearJanuary)/ 86400);// returns day
				$deductedLeave = round($dateDiffHiredToDecLastYear/15)*$availedLeavePerCutoff; // leave earned to be deducted
				return $cutoffLeaveEarned - $deductedLeave;
			}
		}
		else{// ex 2015 regularization; 2016 current year // follow forrmula: leaveEarned = monthCount*1.25
			//			return $cutoffLeaveEarned;// accumulating leave
			return 15;// leave earned is automatically 15
		}
	}
	else{// if employee not regular, get the leave accumulated
		$dateDiffHiredDateToCurrDate = round(($currentDate - $hiredDate)/ 86400);// returns day
		$leaveAccumulated = round($dateDiffHiredDateToCurrDate/15)*$availedLeavePerCutoff;// add this to leave accumulated
		return $leaveAccumulated;
	}
	return 0;
}

/********************************************************
* This function returns income fields for modal
* Returns html code
* Note: This must be in views
********************************************************/
function getIncomeFields($conn){
	$sql = "SELECT `id`, `name`, `taxable`, `active`, `inModal`, `placeholder` FROM `earning_tbl` where deleted = 0 AND `name` <> 'base salary';";
	$result = $conn->query($sql);//execute query
	echo "
	<table class='table table-striped earning-data-table'>
		<tr>
			<th>Name</th>
			<th>Placeholder Num</th>
			<th>Taxable</th>
			<th>Active</th>
			<th>InModal</th>
			<th>Change</th>
			<th>Delete</th>
		</tr>
		";
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$name = $row["name"];
			$placeholder = $row["placeholder"];
			$taxable = isChecked($row["taxable"]);
			$active = isChecked($row["active"]);
			$inModal = isChecked($row["inModal"]);
			$fieldName = str_replace(' ', '_', $name);

			echo "
			<tr class='field-$id';>
				<td><input type='text' class='fieldName' name='$fieldName' value='$name'></td>
				<td><input type='text' class='fieldPlaceHolder' value='$placeholder'></td>
				<td><input type='checkbox' class='fieldTaxable' $taxable></td>
				<td><input type='checkbox' class='fieldActive' $active></td>
				<td><input type='checkbox' class='fieldInModal' $inModal></td>
				<td><button class='change'>Change</button></td>
				<td><button class='delete'>Delete</button></td>
			</tr>

			";

		}
	}
	echo "</table>";
}
/********************************************************
* This function returns deduction fields for modal
* Returns html code
* Note: This must be in views
********************************************************/
function getDeductionFields($conn){
	$sql = "SELECT `id`, `name`, `active`, `placeholder`, `inModal`, `taxDeductable` FROM `deduction_tbl` where deleted = 0 and name<>'sss' and name<>'phic' and name<>'hdmf'  and name<>'income tax'";
	$result = $conn->query($sql);//execute query
	echo "
	<table class='table table-striped deduction-data-table'>
		<tr>
			<th>Name</th>
			<th>Placeholder Num</th>
			<th>Tax deductable</th>
			<th>Active</th>
			<th>InModal</th>
			<th>Change</th>
			<th>Delete</th>
		</tr>
		";
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["id"];
			$name = $row["name"];
			$placeholder = $row["placeholder"];
			$taxDeductable = isChecked($row["taxDeductable"]);
			$active = isChecked($row["active"]);
			$inModal = isChecked($row["inModal"]);
			$fieldName = str_replace(' ', '_', $name);

			echo "
			<tr class='field-$id';>
				<td><input type='text' class='fieldName' name='$fieldName' value='$name'></td>
				<td><input type='text' class='fieldPlaceHolder' value='$placeholder'></td>
				<td><input type='checkbox' class='fieldTaxDeductable' $taxDeductable></td>
				<td><input type='checkbox' class='fieldActive' $active></td>
				<td><input type='checkbox' class='fieldInModal' $inModal></td>
				<td><button class='change'>Change</button></td>
				<td><button class='delete'>Delete</button></td>

			</tr>

			";

		}
	}
	echo "</table>";
}

/********************************************************************************************
* This function returns checked if fields data in database is checked. empty string otherwise
* for getDeductionFields and getIncomeFields data
* Note: This must be in views
********************************************************************************************/
function isChecked($CheckBoxVal){
	if($CheckBoxVal==1){
		return 'checked';
	}
	return '';
}


/********************************************************************************************
* This function shows the overtime form
********************************************************************************************/

function overtimeForm(){
	//	echo "
	//		<div class='form-group row'>
	//			<label for='ot_filing_date' class='col-xs-3 col-form-label'>Overtime filing_date</label>
	//			<div class='col-xs-9'><input type='text' class='form-control' name='ot_filing_date' maxlength='' required/></div>
	//		</div>
	//		<div class='form-group row'>
	//			<label for='ot_reason' class='col-xs-3 col-form-label'>Overtime reason</label>
	//			<div class='col-xs-9'><input type='text' class='form-control' name='ot_reason' maxlength='' required/></div>
	//		</div>
	//		<div class='form-group row'>
	//			<label for='ot_approval_date' class='col-xs-3 col-form-label'>Approval date</label>
	//			<div class='col-xs-9'>
	//				<select class='form-control';>
	//					<option value='1'>Manager 1</option>
	//					<option value='2'>Manager 2</option>
	//					<option value='3'>Manager 3</option>
	//					<option value='4'>Manager 4</option>
	//					<option value='5'>HR</option>
	//				</select>
	//			</div>
	//		</div>
	//		<div class='form-group row'>
	//			<label for='ot_approval_date' class='col-xs-3 col-form-label'>Approval date</label>
	//			<div class='col-xs-9'><input type='text' class='form-control' name='ot_approval_date' maxlength='11' required/></div>
	//		</div>
	//
	//		<label for='ot_approveby_id' class='col-xs-12 col-form-label'>Overtime range</label>
	//		<div class='input-group'>
	//			<div class='input-group-addon'>
	//				<i class='glyphicon glyphicon-calendar fa fa-calendar'></i>
	//			</div>
	//			<input type='text' class='form-control pull-right' name='daterange' value='Click Here To Input Date'>
	//		</div>
	//	";
	echo "
		<div class='form-group row'>
			<label for='ot_filing_date' class='col-xs-3 col-form-label'>Overtime filing_date</label>
			<div class='col-xs-9'><input type='text' class='form-control' name='ot_filing_date' maxlength='' required/></div>
		</div>
		<div class='form-group row'>
			<label for='ot_reason' class='col-xs-3 col-form-label'>Overtime reason</label>
			<div class='col-xs-9'><input type='text' class='form-control' name='ot_reason' maxlength='' required/></div>
		</div>
		<div class='form-group row'>
			<label for='ot_approval_date' class='col-xs-3 col-form-label'>Approval date</label>
			<div class='col-xs-9'>
				<select class='form-control';>
					<option value='1'>Manager 1</option>
					<option value='2'>Manager 2</option>
					<option value='3'>Manager 3</option>
					<option value='4'>Manager 4</option>
					<option value='5'>HR</option>
				</select>
			</div>
		</div>
		<div class='form-group row'>
			<label for='ot_approval_date' class='col-xs-3 col-form-label'>Approval date</label>
			<div class='col-xs-9'><input type='text' class='form-control' name='ot_approval_date' maxlength='11' required/></div>
		</div>

		<label for='ot_approveby_id' class='col-xs-12 col-form-label'>Overtime range</label>
		<div class='input-group'>
			<div class='input-group-addon'>
				<i class='glyphicon glyphicon-calendar fa fa-calendar'></i>
			</div>
			<input type='text' class='form-control pull-right' name='daterange'>
		</div>
	";
}

/***********************************************
* This function returns employee payment fields
* Returns html code
* Note: This must be in views
***********************************************/
function createPayroll($conn,$division){
	$employeeEarningJSON = "";
	$employeeDeductionJSON = "";

	$sql = "SELECT `user_id`, CONCAT (last_name,', ',first_name,' ',middle_name,'.') as `name`, emp_no as `employeeNum`, division_id as `division`,monthly_rate as `salary`, civil_status as `civilStatus`, `dependents`,regularization_date FROM `tbl_user` WHERE division_id = $division and user_status=1";
	$result = $conn->query($sql);//execute query
	echo "
	<table class='table table-striped payroll-data-table'>
		<tr>
			<th>Name</th>
			<th>Employee Num</th>
			<th>Division</th>
			<th>BasicSalary</th>
			<th>Civil Status</th>
			<th>Dependent/s</th>
			<th>Earnings</th>
			<th>Deductions</th>
			<th>Leaves</th>
			<th class='benifits sss closed'>SSS</th>
			<th class='benifits phic closed'>PHIC</th>
			<th class='benifits hdmf closed'>HDMF</th>
			<th>Over time</th>
			<th>
				<input type='checkbox' class='cashAllowanceValCB'>Cash allowance<br>
				<input class='cashAllowanceVal closed' type=number value=0>
			</th>
		</tr>
		";
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {

			$id = $row["user_id"];
			$name = $row["name"];
			$employeeNum = $row["employeeNum"];
			$division = $row["division"];
			//			$client = $row["client"];
			$civilStatus = $row['civilStatus'];
			$dependents = $row['dependents'];
			$salary = $row["salary"];
			$basicPay = $salary/2;
			$taxStatus = "";
			$employeeEarningJSON .= "{\"id\":$id,{\"salary\":$salary}},";
			$employeeDeductionJSON .= "{\"id\":$id,{\"salary\":$salary}},";

			$classRegular = "";
			$regularizationDate = $row["regularization_date"];
			// check if the employee is regular. regularizationDateIsEmpty
			if($regularizationDate!="0000-00-00"){// means if you have regularization date record do the code below
				if(strtotime($regularizationDate)<strtotime(date("Y-m-d"))){// compare the date to current date
					$classRegular = "regularEmp";
				}
			}

			//tax status single/merried = 1/2

			switch($civilStatus){
				case 1:{ $taxStatus = "Single"; }break;
				case 2:{ $taxStatus = "Married"; }break;
				case 3:{ $taxStatus = "Widow"; }break;
			}

			echo "<tr data-id = '$id' class='$classRegular'>
					<td class='name'>$name</td>
					<td class='empNum'>$employeeNum</td>
					<td class='division'>$division</td>
					<td class='basicSalary'>$basicPay</td>
					<td class='civil'>$civilStatus</td>
					<td class='dependents'>$dependents</td>
					<td class='earning'><button data-toggle='modal' data-target='#earning'>Add Earnings</button></td>
					<td class='deduction'><button data-toggle='modal' data-target='#deduction'>Add Deduction</button></td>
					<td class='leave'><button data-toggle='modal' data-target='#leave'>Add Leave</button></td>
					<td class='benifits sss ease closed'><input type='checkbox' value=1></td>
					<td class='benifits phic closed'><input type='checkbox' value=1></td>
					<td class='benifits hdmf closed'><input type='checkbox' value=1></td>
					<td class='overTime'><button data-toggle='modal' data-target='#overTime'>Overtime</button></td>
					<td class='cashAllowance'><input type='number'></td>
				</tr>";

		}
	}
	echo "</table>";

	// insert the salary of the employee...
	// create a button that writes the philhealth, sss, and pagibig of every employee
	// create 

	// generate payroll... then save....

	//	$cutoff = 
}

/***********************************************
* This function returns income fields for modal
* Returns html code
* Note: This must be in views
***********************************************/
function getEmployeeList($conn,$division){
	$sql = "SELECT *, CONCAT (last_name,', ',first_name,' ',middle_name,'.') as name FROM `tbl_user` WHERE division_id = $division AND user_status=1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row["user_id"];
			$teamName = $row["name"];
			echo "<input type='radio' name='team' value='$id'> $teamName<br>";
			break;
		}
	}
}

/**********************************************
* This function returns deduction fields for modal
* Returns html code
* Note: This must be in views
**********************************************/
function getSalaryDeductionFields($conn){
	$sql = "SELECT id,name,placeholder FROM `deduction_tbl` WHERE active = 1 AND inModal = 1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row['id'];
			$name = $row['name'];
			$placeholder = $row['placeholder'];
			$nameID = str_replace(' ', '',$name);
			$tagName = str_replace(" ","_",$name);//replace space by underscore - for deductionlog_tbl
			echo "
				<div class='form-group'>
					<label for='$nameID' class='col-sm-4 control-label text-left'>$name:</label>
					<div class='col-sm-3'>
						<input type='number' class='form-control' name='$tagName' id='$nameID' placeholder='$placeholder'>
					</div>
					<div class='col-sm-5 repeatedDeduction-$nameID'>
						<label>
							<input type='radio' name='continuousDeduction-$nameID' autocomplete='off' value='1'>monthly
						</label>
						<label>
							<input type='radio' name='continuousDeduction-$nameID' autocomplete='off' value='2'>semimonthly
						</label>
						<label>
							<input type='radio' name='continuousDeduction-$nameID' autocomplete='off' value='3'>stop
						</label>
					</div>
				</div>
			";
		}
	}
}

/**********************************************
* This function returns deduction fields for modal
* Returns html code
* Note: This must be in views
**********************************************/
function getSalaryAdditionFields($conn){
	$sql = "SELECT * FROM `earning_tbl` WHERE active = 1 AND inModal = 1";
	$result = $conn->query($sql);//execute query
	if ($result->num_rows> 0) {
		while($row = $result->fetch_assoc()) {
			$id = $row['id'];
			$name = $row['name'];
			$placeholder = $row['placeholder'];
			$nameID = str_replace(' ', '',$name);
			$tagName = str_replace(" ","_",$name);//replace space by underscore - for deductionlog_tbl
			echo "
				<div class='form-group'>
					<label for='$nameID' class='col-sm-4 control-label text-left'>$name:</label>
					<div class='col-sm-3'>
						<input type='number' class='form-control' name='$tagName'  id='$nameID' placeholder='$placeholder'>
					</div>
					<div class='col-sm-5 repeatedEarning-$nameID'>
						<label>
							<input type='radio' name='continuousEarnings-$nameID' autocomplete='off' value='1'>monthly
						</label>
						<label>
							<input type='radio' name='continuousEarnings-$nameID' autocomplete='off' value='2'>semimonthly
						</label>
						<label>
							<input type='radio' name='continuousEarnings-$nameID' autocomplete='off' value='3'>stop
						</label>
					</div>
				</div>
			";
		}
	}
}



/***********************************************************************************
* This function returns the SSS contribution of the employee based on their salary
***********************************************************************************/

function getSSSContribution($salaryMonthly){
	$additional = 0;
	$salaryMax = 15750;
	$salaryBracket = 0;
	$sssContribution = 1000;
	$employerContribution = 0;
	if($salaryMonthly<$salaryMax){
		for($j=1249.99;$j<=$salaryMonthly;$j+=500){
			$sssContribution += 500;
		}
		$salaryBracket = floor($salaryMonthly/500)-1;
		for($j = 0; $j < $salaryBracket; $j++){
			if($j == 0){ $additional = 0; }
			else if($j%3==0){ $additional -= 0.05; }
			else{ $additional += 0.05; }
		}
	}
	else{
		$sssContribution = 16000;
		$additional = 0.5;
	}
	$sssContribution = ($sssContribution*0.0363)+$additional;
	return $sssContribution;
}



/***********************************************************************************
* This function returns the SSS contribution of the employer based on their salary
***********************************************************************************/
function getEmployerSSSContribution($salaryMonthly){
	$additional = 0;
	$salaryMax = 15750;
	$salaryBracket = 0;
	$sssContribution = 1000;
	$employerContribution = 0;
	if($salaryMonthly<$salaryMax){
		for($j=1249.99;$j<=$salaryMonthly;$j+=500){
			$sssContribution += 500;
		}
		$salaryBracket = floor($salaryMonthly/500)-1;
		for($j = 0; $j < $salaryBracket; $j++){
			if($j == 0){ $additional = 0; }
			else if($j%3==0){ $additional -= 0.05; }
			else{ $additional += 0.05; }
		}
	}
	else{
		$sssContribution = 16000;
		$additional = 0.5;
	}
	$employerContribution = ($sssContribution * 0.0737)-$additional;
	return $employerContribution;
}

/******************************************************************************************
* This function returns the PHILHEALTH contribution of the employee based on their salary
******************************************************************************************/
function getPhilHealthContribution($salaryMonthly){
	$philHealthContribution = 437.50;
	if($salaryMonthly>=35000){
		return $philHealthContribution;
	}
	$bracket = 35000;
	while($salaryMonthly<$bracket){
		$philHealthContribution -= 12.5;
		$bracket -= 1000;
	}
	return $philHealthContribution;
}

/*************************************************************
* This function returns the PAGIBIG contribution EXACTLY 100
*************************************************************/
function getPagIbigContribution(){
	return 100;
}

/***********************************************************************************************************************
* This function returns the computed tax of the employee based on their total earnings, total deductions and tax status
************************************************************************************************************************/
function getTax($totalEarnings, $totalDeductions, $taxStatus){
	// tax bracket
	$taxSM = [0,2083,2500,3333,5000,7917,12500,22917];
	$taxSMD1 = [0,3125,3542,4375,6042,8958,13542,23958];
	$taxSMD2 = [0,4167,4583,5417,7083,10000,14583,25000];
	$taxSMD3 = [0,5208,5625,6458,8125,11042,15625,26042];
	$taxSMD4 = [0,6250,6667,7500,9167,12083,16667,27083];
	$taxBracketToUse = [];
	// deduction percentage
	$deductionPercentageSet = [0,0.05,0.1,0.15,0.2,0.25,0.3,0.32];
	// fixed tax
	$witholdingTax = [0,0,20.83,104.17,354.17,937.50,2083.33,5208.33];
	// operators
	$j = 0;
	$tax = 0;
	$excessTax = 0;
	$salary = 0;
	$salary = $totalEarnings-$totalDeductions;
	echo "<sal: $salary = $totalEarnings-$totalDeductions>";
	switch($taxStatus){
		case 0:{ $taxBracketToUse = $taxSM; }break;
		case 1:{ $taxBracketToUse = $taxSMD1; }break;
		case 2:{ $taxBracketToUse = $taxSMD2; }break;
		case 3:{ $taxBracketToUse = $taxSMD3; }break;
		case 4:{ $taxBracketToUse = $taxSMD4; }break;
		default:{ $taxBracketToUse = $taxSMD4; }break;
	}
	for($i=count($taxBracketToUse)-1;$i>0;$i--){
		if($salary>$taxBracketToUse[$i]){
			$j = $i;
			break;
		}
	}

	$excessTax = ($salary-$taxBracketToUse[$j])*$deductionPercentageSet[$j];
	echo "<$excessTax = ($salary-$taxBracketToUse[$j])*$deductionPercentageSet[$j];>";
	$tax = $witholdingTax[$j]+$excessTax;
	echo "<$tax = $witholdingTax[$j]+$excessTax;>";
	return $tax;
}

/**********************************************************
* Undefined function. Under construction or to be deleted
**********************************************************/
function updateDeduction($empNum,$payrollDate,$totalEarnings,$incomeTax,$monthlySalary,$sssNeeded,$phliHealthNeeded,$paigibigNeeded){
	$baseSalary = $monthlySalary/2;
	$sss = 0;
	$philHealth = 0;
	$pagibig = 0;
	if($sssNeeded){
		$sss = getSSSContribution($salaryMonthly);
	}
	if($phliHealthNeeded){
		$philHealth = getPhilHealthContribution($salaryMonthly);
	}
	if($paigibigNeeded){
		$pagibig = getPagIbigContribution();
	}
}



?>