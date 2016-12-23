$(document).ready(function(){
	$( "#payrollDate" ).datepicker({ dateFormat: 'yy-mm-dd' });
	/* variables */
	//OPERATORS
	var jsonDataValue = "";
	var empID = "";


	var item = "";

	/* Functions */

	function saveData(elemSelector,process){
		var jsonCode = "";
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
			console.log(jsonCode);
			jsonCode = $.parseJSON(jsonCode);
			console.log(jsonCode);
	//		send the json code to the database.
			$.ajax({
				url: "functions.php",
				type: 'POST',
				data: { todo: "insertPayrollData", payrollData: jsonCode},
				success: function(result){
					alert(result);
					console.log(result);
				},
				error: function () {
					alert();
				}
			});
		}
	}




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

	$('.deduction button').click(function(){
		empID = $(this).parent().parent().attr("data-id");
		$('#deduction input').val("");
		$('.dataRecordingConfirmation').text("");
	});

	$('.saveRecords').click(function(){
		saveDatas();
	});

});