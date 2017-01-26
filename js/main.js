$(document).ready(function(){
	$( "#payrollDate" ).datepicker({ dateFormat: 'yy-mm-dd' });
	/* variables */
	//OPERATORS
	var jsonCode = "";
	var jsonDataValue = "";
	var empID = "";
	var item = "";

	/* Functions */
	
	function printQuery(query){
		$(".queryGenerated p").text(query);
	}

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
			for(var i = 0; i < data.length; i++) {
				if(data[i].value==""){ jsonDataValue += "\""+data[i].name+"\":0,"; }
				else{ jsonDataValue += "\""+data[i].name+"\":"+data[i].value+",";}
			}

			jsonCode = "{"+jsonDataValue.slice(0,-1)+"}";
			jsonCode = $.parseJSON(jsonCode);
//			send the json code to the database.
			$.ajax({
				url: "functions.php",
				type: 'POST',
				data: { todo: "insertPayrollData", payrollData: jsonCode},
				success: function(result){
//					printQuery(result);
					alert("Records done. Compute the tax.");
				},
				error: function () {
					alert("Something wrong.");
				}
			});
		}
	}
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
	
	$(".riceSubsidyVal").bind('keyup mouseup', function () {
		$(".riceSubsidy input[type=number]").val($(this).val());
	});
	$(".cashAllowanceVal").bind('keyup mouseup', function(){
		$(".cashAllowance input[type=number]").val($(this).val());
	});
	$(".viewPayslip").click(function(){
		var payrollDate = $( "#payrollDate" ).val();
		window.open('printPayroll.php?payrollDate='+payrollDate);
	});
	$(".saveRecords").click(function(){
		jsonDataValue = "";
		var basicSalary = "";
		var civilStatus = "";
		var dependents = "";
		var overTime = "";
		var cashAllowance = "";
		var payrollDate = $( "#payrollDate" ).val();
		var sss = 0;
		var phic = 0;
		var hdmf = 0;
		var riceSubsidy = 0;
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
						overTime = $(this).find("td.overTime input").val();
						cashAllowance = $(this).find("td.cashAllowance input").val();
						sss = $(this).find(".sss input").is(":checked") ? 1 : 0;
						phic = $(this).find(".phic input").is(":checked") ? 1 : 0;
						hdmf = $(this).find(".hdmf input").is(":checked") ? 1 : 0;
						riceSubsidy = $(this).find(".riceSubsidy input").val();
						if(overTime==""){ overTime = 0; }
						if(cashAllowance==""){ cashAllowance = 0; }
						jsonDataValue += "{\"employeeID\":"+empID+",\"payrollDate\":"+"\""+payrollDate+"\",\"base_salary\":"+basicSalary+",\"civil_status\":"+civilStatus+",\"dependents\":"+dependents+",\"overtime\":"+overTime+",\"cash_allowances\":"+cashAllowance+",\"sss\":"+sss+",\"phic\":"+phic+",\"hdmf\":"+hdmf+",\"rice_subsidy\":"+riceSubsidy+"},";
					}
				});
				jsonCode = "["+jsonDataValue.slice(0,-1)+"]";
//				printQuery(jsonCode);
				jsonCode = $.parseJSON(jsonCode);
//				printQuery(jsonCode);
//				send the json code to the database.
				$.ajax({
					url: "functions.php",
					type: 'POST',
					data: { todo: "finalPayrollData", payrollData: jsonCode},
					success: function(result){
	//					printQuery(result);
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
	$(".viewRecords").click(function(){
		$(".payroll-data-table tr").each(function(i){
			if (i > 0){ //skip the first element
				empID = $(this).attr("data-id");
				sss = $(this).find(".sss input").is(":checked");
				console.log(empID+" "+sss);
			}
		});
		
	});
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
	//				alert("Records done. Compute the tax.");
				},
				error: function () {
					alert("Something wrong.");
				}
			});
		}
	});
	$('.cashAllowanceValCB').change(function(){
		$(".cashAllowanceVal").toggleClass("closed");
	});
	$('.riceSubsidyValCB').change(function(){
		$(".riceSubsidyVal").toggleClass("closed");
	});
	$("#sss").change(function(){
		if($(this).is(":checked")){ $(".sss input").prop('checked', true); }
		else{ $(".sss input").prop('checked', false); }
		$(".sss").fadeToggle("slow");
	});
	$("#riceSubsidy").change(function(){
		if($(this).is(":checked")){ $(this).prop('checked', true); }
		else{
			$('.riceSubsidy input[type=number],.riceSubsidyVal').val(0);
			$(this).prop('checked', false); }
		$(".riceSubsidy").fadeToggle("slow");
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
	$('.earnings-form').submit(function(e) {
		e.preventDefault();
		saveData($(this),"earning");
	});
	$('.deduction-form').submit(function(e){
		e.preventDefault();
		saveData($(this),"deduction");
	});
	$('.earning button').click(function(){
		empID = $(this).parent().parent().attr("data-id");
		$('#earning input').val("");
		$('.dataRecordingConfirmation').text("");
	});
	
	
	$('.leave button').click(function(){
//		alert("dfsgz");
		empID = $(this).parent().parent().attr("data-id");
		console.log(empID);
//		$('#earning input').val("");
//		$('.dataRecordingConfirmation').text("");
	});
	$('.deduction button').click(function(){
		empID = $(this).parent().parent().attr("data-id");
		$('#deduction input').val("");
		$('.dataRecordingConfirmation').text("");
	});
	$('.leave button').click(function(){
		$('#SLDate').val('');
	});
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
});