$(document).ready(function(){
	$( "#payrollDate" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "input[name='ot_filing_date']" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "input[name='ot_approval_date']" ).datepicker({ dateFormat: 'yy-mm-dd' });
	
	/* variables */
	//OPERATORS
	var jsonCode = "";
	var jsonDataValue = "";
	var empID = "";
	var item = "";

	/****************************************************************
	* Tcap rules
	****************************************************************/
	$( "#payrollDate" ).change(function(){
		var dateDay = Number($(this).val().split("-")[2]);
		if(dateDay<16){
			$(".benifits input").prop("checked", true);
		}
		else{
			$(".benifits input").prop("checked", false);
		}
		
	});
	/***************************************************************/
	
	
	/* Functions */
	/**************************************************************
	* Prints the query generated
	**************************************************************/
	function printQuery(query){
		$(".queryGenerated p").text(query);
	}
	
	
	/******************************************************************
	* Insert payroll data
	*******************************************************************/
	$( ".overtime-form" ).submit(function( event ) {
		console.log(empID);
		event.preventDefault();
		
		var allInputs = [];
		
		allInputs.push({
			name: "ot_user_id",
			type: "number",
			val: empID
		});
		
		$(this).find('input').map(function (i,e){
			allInputs.push({
				name: $(e).attr("name"),
				type: $(e).attr("type"),
				val: $(e).val()
			});
		});
		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: {  todo: "insertovertimeRecord", data: allInputs},
			success: function(result){
				alert("Dumaan dito.");
				console.log(result)
			},
			error: function () {
				alert("Something wrong.");
			}
		});
		
	});

	
	/******************************************************************
	* Save the data on the database
	* JSON string format is sent to PHP file that handles the process
	******************************************************************/
	function saveData(elemSelector,process){
		jsonCode = "";
		jsonDataValue = "";
		var payrollDate = $( "#payrollDate" ).val();
		if(payrollDate==""){
			alert("Please input the payroll date");
		}
		else{
			jsonDataValue += "\"employeeID\":"+empID+",\"payrollDate\":"+"\""+payrollDate+"\",";
			var data = $(elemSelector).serializeArray();
			console.log(data);
			for(var i = 0; i < data.length; i++) {
				if(data[i].value==""){ jsonDataValue += "\""+data[i].name+"\":0,"; }
				else{ jsonDataValue += "\""+data[i].name+"\":"+data[i].value+",";}
			}
			jsonCode = "{"+jsonDataValue.slice(0,-1)+"}";
			jsonCode = $.parseJSON(jsonCode);
//			send the json code to the database.
			console.log(jsonCode);
			$.ajax({
				url: "functions.php",
				type: 'POST',
				data: { todo: "insertPayrollData", payrollData: jsonCode},
				success: function(result){
					printQuery(result);
					alert("Records done. Compute the tax.");
				},
				error: function () {
					alert("Something wrong.");
				}
			});
		}
	}
	
	/******************************************************************
	* Check if the payroll in given date is done.
	* Returns true if payroll in given date is done. false otherwise.
	******************************************************************/
	function payrollDateIsExisting(payrollDate){
		var payrollDateExisting = true;
		$.ajax({
			url: "functions.php",
			type: 'POST',
			data: { todo: "checkPayroll", payrollDate: payrollDate},
			async: false,
			success: function(result){
				payrollDateExisting = (result == '1') ? true : false;
			}
		});
		return payrollDateExisting;
	}
	
	/* Event listeners */
	
	/*********************************************************************
	* For multiple selection of input
	*********************************************************************/
	$(".cashAllowanceVal").bind('keyup mouseup', function(){
		$(".cashAllowance input[type=number]").val($(this).val());
	});
	
	/*********************************************************************
	* Redirect to other page with given date data
	*********************************************************************/
	$(".viewPayslip").click(function(){
		var payrollDate = $( "#payrollDate" ).val();
		window.open('printPayroll.php?payrollDate='+payrollDate);
	});
	/*********************************************************************
	* Redirect to other page with given date data
	*********************************************************************/
	$(".tabledReport").click(function(){
		var payrollDate = $( "#payrollDate" ).val();
		window.open('payrollExelReport.php?payrollDate='+payrollDate);
	});
	
	/*********************************************************************
	* This function saves all the data in the field input and information
	* by storing them at JSON to be sent to the PHP function that handles
	* the operation
	*********************************************************************/
	$(".saveRecords").click(function(){
		jsonDataValue = "";
		var basicSalary = "";
		var civilStatus = "";
		var dependents = "";
		var cashAllowance = "";
		var payrollDate = $( "#payrollDate" ).val();
		var sss = 0;
		var phic = 0;
		var hdmf = 0;
		var allowRecordingOfData = true;
		
		var resultPayroll = payrollDateIsExisting(payrollDate);
//		alert(resultPayroll);
		
//		if(false)
		if(payrollDate==""){
			alert("Please input the payroll date");
		}
		else{
			if(payrollDateIsExisting(payrollDate)){
				allowRecordingOfData = confirm("Overwrite existing record?") ? true : false;
			}
			if(allowRecordingOfData){
				$(".payroll-data-table tr").each(function(i){
					if (i > 0){ //skip the first element
						empID = $(this).attr("data-id");
						basicSalary = $(this).children("td.basicSalary").text();
						civilStatus = $(this).children("td.civil").text();
						dependents = $(this).children("td.dependents").text();
						cashAllowance = $(this).find("td.cashAllowance input").val();
						sss = $(this).find(".sss input").is(":checked") ? 1 : 0;
						phic = $(this).find(".phic input").is(":checked") ? 1 : 0;
						hdmf = $(this).find(".hdmf input").is(":checked") ? 1 : 0;
						if(cashAllowance==""){ cashAllowance = 0; }
						//under development
						jsonDataValue += "{\"employeeID\":"+empID+",\"payrollDate\":"+"\""+payrollDate+"\",\"base_salary\":"+basicSalary+",\"civil_status\":"+civilStatus+",\"dependents\":"+dependents+",\"cash_allowances\":"+cashAllowance+",\"sss\":"+sss+",\"phic\":"+phic+",\"hdmf\":"+hdmf+"},";
					}
				});
				jsonCode = "["+jsonDataValue.slice(0,-1)+"]";
//				console.log(jsonCode);
				jsonCode = $.parseJSON(jsonCode);
//				send the json code to the database.
				$.ajax({
					url: "functions.php",
					type: 'POST',
					data: { todo: "finalPayrollData", payrollData: jsonCode},
					success: function(result){
						printQuery(result);
						alert("Records done. Compute the tax.");
					},
					error: function () {
						alert("Something wrong.");
					}
				});
			}
		}
		allowRecordingOfData = true;
	});
	
	/*********************************************************************
	* About to write the code...
	*********************************************************************/
	$(".viewRecords").click(function(){
		$(".payroll-data-table tr").each(function(i){
			if (i > 0){ //skip the first element. This is the table header
				empID = $(this).attr("data-id");
				sss = $(this).find(".sss input").is(":checked");
				console.log(empID+" "+sss);
			}
		});
	});
	
	
	/*********************************************************************
	* This function deletes a payroll data on a given date.
	**********************************************************************/
	$(".deleteRecord").click(function(){
		var payrollDate = $( "#payrollDate" ).val();
		if(payrollDate==""){
			alert("Please input the payroll date");
		}
		else{
			var allowRecordingOfData = confirm("Delete payroll data on this date "+payrollDate+"?") ? true : false;
			if(allowRecordingOfData){
				$.ajax({
					url: "functions.php",
					type: 'POST',
					data: { todo: "deleteRecord", payrollDate: payrollDate, department: 1 },
					success: function(result){
						printQuery(result);
					},
					error: function () {
						alert("Something wrong.");
					}
				});
			}
		}
	});
	
	/*********************************************************************
	* This event calls the compute tax function in the php to be recorded
	* at the database
	*********************************************************************/
	$(".computeTax").click(function(){
		var payrollDate = $( "#payrollDate" ).val();
		if(payrollDate==""){
			alert("Please input the payroll date");
		}
		else{
			$.ajax({
				url: "functions.php",
				type: 'POST',
				data: { todo: "setTax", payrollDate: payrollDate, department: 1 },
				success: function(result){
					printQuery(result);
				},
				error: function () {
					alert("Something wrong.");
				}
			});
		}
	});
	
	/**********************************************************************
	* This functions get the employee ID of the selected row.
	* The empID val is used in the next operations.
	**********************************************************************/
	$('.earning button').click(function(){
		empID = $(this).parent().parent().attr("data-id");
		$('#earning input[type="text"]').val("");
		$('.dataRecordingConfirmation').text("");
		$('#earning input[type="radio"]').prop('checked', false);
	});
	$('.deduction button').click(function(){
		empID = $(this).parent().parent().attr("data-id");
		$('#deduction input').val("");
		$('.dataRecordingConfirmation').text("");
		$('#deduction input[type="radio"]').prop('checked', false);
	});
	$('.leave button').click(function(){
		empID = $(this).parent().parent().attr("data-id");
		$('#SLDate').val('');
	});
	$('.overTime button').click(function(){
		empID = $(this).parent().parent().attr("data-id");
	});
	
	/**********************************************************************
	* This function add's Sick leave data to the database
	**********************************************************************/
	$('.addSL').click(function(e){
		e.preventDefault();
		var slDate = $(this).parent().children("input").val();
		var leavePoint = $('input[name="leavePoint"]:checked').val();
		console.log(slDate)
		if(slDate==''){
			alert("Please input Sick Leave date");
		}
		else{
			$.ajax({
				url: "functions.php",
				type: 'POST',
				data: { todo: "addSL", employeeID: empID, slDate: slDate,leavePoint:leavePoint},
				success: function(result){
					$(".queryGenerated p").text(result);

					//					alert(result);
				},
				error: function () {
					alert("Something wrong.");
				}
			});
		}
	});

	/**********************************************************************
	* This function add's Vacation leave data to the database
	**********************************************************************/
	$('.addVL').click(function(e){
		e.preventDefault();
		var vlDate = $(this).parent().children("input").val();
		var leavePoint = $('input[name="leavePoint"]:checked').val();
		console.log(vlDate);
		if(vlDate==''){
			alert("Please input Vacation Leave date");
		}
		else{
			$.ajax({
				url: "functions.php",
				type: 'POST',
				data: { todo: "addVL", employeeID: empID, vlDate: vlDate,leavePoint:leavePoint},
				success: function(result){
					$(".queryGenerated p").text(result);
					alert(result);
				},
				error: function () {
					alert("Something wrong.");
				}
			});
		}
	});
	
	/**********************************************************************
	* This function enable multiple selection of the given fields i.e. cash
	* allowance
	**********************************************************************/
	$('.cashAllowanceValCB').change(function(){
		$(".cashAllowanceVal").toggleClass("closed");
	});

	/****************************************************************
	* Show this fields in the row when the check box was checked
	****************************************************************/
	$("#sss").change(function(){
		if($(this).is(":checked")){ $(".sss input").prop('checked', true); }
		else{ $(".sss input").prop('checked', false); }
		$(".sss").fadeToggle("slow");
	});
	$("#philhealth").change(function(){
		if($(this).is(":checked")){ $(".phic input").prop('checked', true); }
		else{ $(".phic input").prop('checked', false); }
		$(".phic").fadeToggle("slow");
	});
	$("#pagibig").change(function(){
		if($(this).is(":checked")){ $(".hdmf input").prop('checked', true); }
		else{ $(".hdmf input").prop('checked', false); }
		$(".hdmf").fadeToggle("slow");
	});
	
	/**********************************************************************
	* This function saves tha data in the input field of the earnings form
	* ir deductions form
	**********************************************************************/
	$('.earnings-form').submit(function(e) {
		e.preventDefault();
//		console.log($(this));
		saveData($(this),"earning");
	});
	$('.deduction-form').submit(function(e){
		e.preventDefault();
		saveData($(this),"deduction");
	});
	
	//
	
	$('input[name="daterange"]').daterangepicker({
		autoUpdateInput: false,
		timePicker: true,
		timePickerIncrement: 1,
		locale: {
			label: 'Click Here To Input Date',
			format: 'MM/DD/YYYY h:mm A',
			"opens": "center"
		}
	});

	$('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
		$(this).val(picker.startDate.format('MM/DD/YYYY h:mm A') + ' - ' + picker.endDate.format('MM/DD/YYYY h:mm A'));
	});

	$('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
		$(this).val('Click Here To Input Date');
	})
	
	
	
});