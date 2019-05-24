/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function ($, Drupal) {

  'use strict';
  /* CODE GOES HERE */
// Code that uses jQuery's $ can follow here.
	
	 $('.cnp-credit-cvv-help').hover(function() {
		$(".cnp_cvv_help_image").show();
	  }, function() {
		$(".cnp_cvv_help_image").show().hide();
	  });
	
	//$(".cnp-credit-cvv-help").hover(function(){
		//$(".cnp_cvv_help_image").show();
		
	//});
	
	$('#cnp-main-settings').submit(function() {
		var ctoi=$("#edit-cnp-recurr-type-option-installment").is(":checked");
		var cros=$("#edit-cnp-recurr-type-option-subscription").is(":checked");
		var recur=$("#edit-cnp-recurr-recur-1").is(":checked");
		
		
		if(!($("#edit-cnp-recurr-oto-oto").is(":checked") || $("#edit-cnp-recurr-recur-1").is(":checked")))
		{
			alert("Please select payment options");
			$("#edit-cnp-recurr-oto-oto").focus();
			return false;
		}
		
		if($("#edit-cnp-accid").val()=="")
		{
			alert("Please select account Id");
			$("#edit-cnp-accid").focus();
			return false;
		}
		
		if(recur==true)
		{
			if(ctoi==false && cros==false)
			{
				alert("Please select at least one recurring type");
				$("#edit-cnp-recurr-type-option-installment").focus();
				return false;
			}
		}
		var selected = 0;
		if(recur==true)
		{	
			if($("#edit-cnp-recurring-periodicity-options-week").prop('checked')) selected++;
			if($("#edit-cnp-recurring-periodicity-options-2-weeks").prop('checked')) selected++;
			if($("#edit-cnp-recurring-periodicity-options-month").prop('checked')) selected++;
			if($("#edit-cnp-recurring-periodicity-options-2-months").prop('checked')) selected++;
			if($("#edit-cnp-recurring-periodicity-options-quarter").prop('checked')) selected++;
			if($("#edit-cnp-recurring-periodicity-options-6-months").prop('checked')) selected++;
			if($("#edit-cnp-recurring-periodicity-options-year").prop('checked')) selected++;
			if(selected == 0) {
				alert('Please select at least one period');
				$("#edit-cnp-recurring-periodicity-options-week").focus();
				return false;
			}
		}
		
		if(recur==true)
		{	
			var nop = 0;
			
			if($("#edit-cnp-recurr-type-option-installment").is(":checked") && (!$("#edit-cnp-recurr-type-option-subscription").is(":checked")))
			{
				if($("#edit-cnp-recurring-no-of-payments-options-openfield").prop('checked')) nop++;
				if($("#edit-cnp-recurring-no-of-payments-options-fixednumber").prop('checked')) nop++;
				if(nop == 0) {
					alert('Please select number of payment options');
					$("#edit-cnp-recurring-no-of-payments-options-openfield").focus();
					return false;
				}
			}
			
			if($("#edit-cnp-recurr-type-option-installment").is(":checked") && $("#edit-cnp-recurr-type-option-subscription").is(":checked"))
			{
				if($("#edit-cnp-recurring-no-of-payments-options-openfield").prop('checked')) nop++;
				if($("#edit-cnp-recurring-no-of-payments-options-fixednumber").prop('checked')) nop++;
				if($("#edit-cnp-recurring-no-of-payments-options-indefinite-openfield").prop('checked')) nop++;
				if($("#edit-cnp-recurring-no-of-payments-options-1").prop('checked')) nop++;
				if(nop == 0) {
					alert('Please select number of payment options');
					$("#edit-cnp-recurring-no-of-payments-options-openfield").focus();
					return false;
				}
			}
			
		}
		
		//edit-cnp-recurr-type-option-installment
		//edit-cnp-recurr-type-option-subscription
		//edit-cnp-default-recurring-type
		
		//validate number of payments on submit
		if(recur==true)
		{
			if($("#edit-cnp-recurring-no-of-payments-options-indefinite-openfield").is(":checked"))
			{
				if($("#edit-cnp-recurring-default-no-payments-open-filed").val()!="")
				{
					
					if($("#edit-cnp-recurring-default-no-payments-open-filed").val()<=1)
					{
						alert("Please enter default number of payments value greater than 1");
						$("#edit-cnp-recurring-default-no-payments-open-filed").focus();
						return false;
					}
				}
				
				
			}
			if($("#edit-cnp-recurring-no-of-payments-options-openfield").is(":checked"))
			{
				
				var installOpt=$("#edit-cnp-recurr-type-option-installment").is(":checked");
				var subscrOpt=$("#edit-cnp-recurr-type-option-subscription").is(":checked");
				
				var one=parseInt($("#edit-cnp-recurring-default-no-payments").val());
				var two=parseInt($("#edit-cnp-recurring-max-no-payment").val());
				
				if(installOpt && subscrOpt)
				{
					var recurrOpt=$("#edit-cnp-default-recurring-type").val();
					if(recurrOpt=="Subscription")
					{
						//logic for Subscription
						if(one != "")
						{
							//console.log(one);
							if(isNaN(one))
							{
								/*alert("Please enter a valid number for subscription3")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;*/
							}
							if(one<=1)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
							if(one>=1000)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
						}
						if(two != "")
						{
							if(isNaN(two))
							{
								/*alert("Please enter a valid number for subscription4")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;*/
							}
							if(two<=1)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two >= 1000)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two == 0)
							{
								alert("Please enter maximum number of installments allowed value greater than 1");
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
						}
					}
					else
					{
						//logic for installments
						if(one != "")
						{
							if(isNaN(one))
							{
								alert("Please enter a valid number for installment")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
							if(one<=1)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
							if(one>=999)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
						}
						if(two != "")
						{
							if(isNaN(two))
							{
								alert("Please enter a valid number for installment")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two<=1)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two >= 999)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two == 0)
							{
								alert("Please enter maximum number of installments allowed value greater than 1");
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
						}
					}
					if(two==0)
					{
						alert("Please enter maximum number of installments allowed value greater than 1");
						$("#edit-cnp-recurring-max-no-payment").focus();
						return false;
					}
					if(one<=0)
					{
						alert("Please enter maximum number of installments allowed value greater than 1");
						$("#edit-cnp-recurring-default-no-payments").focus();
						return false;
					}
				}
				else
				{
					//if installments only selected
					if(installOpt==true)
					{
						if(one != "")
						{
							if(isNaN(one))
							{
								/*alert("Please enter a valid number for installment3")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;*/
							}
							if(one<=1)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
							if(one>=999)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
						}
						if(two != "")
						{
							if(isNaN(two))
							{
								/*alert("Please enter a valid number for installment4")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;*/
							}
							if(two<=1)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two >= 999)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
						}
						if(two == 0)
						{
							alert("Please enter maximum number of installments allowed value greater than 1");
							$("#edit-cnp-recurring-max-no-payment").focus();
							return false;
						}
					}
					//if subscription only selected
					if(subscrOpt==true)
					{
						if(one != "")
						{
							if(isNaN(one))
							{
								alert("Please enter a valid number for subscription1")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
							if(one<=1)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
							if(one>=1000)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-default-no-payments").focus();
								return false;
							}
						}
						if(two != "")
						{
							
							if(isNaN(one))
							{
								alert("Please enter a valid number for subscription2")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two<=1)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
							if(two >= 1000)
							{
								alert("Please enter value between 2 to 999 for subscription")
								$("#edit-cnp-recurring-max-no-payment").focus();
								return false;
							}
						}
						if(two == 0)
						{
							alert("Please enter maximum number of subscription allowed value greater than 1");
							$("#edit-cnp-recurring-max-no-payment").focus();
							return false;
						}
					}
				}

				if(one != "" && two != "")
				{
					//console.log(one > two);
					if(one >= two)
					{
						alert("Enter maximum number of installments value should be more than default number of payments");
						$("#edit-cnp-recurring-max-no-payment").focus();
						return false;
					}
				}
				
			}//openfield validation completed---END
			
			if($("#edit-cnp-recurring-no-of-payments-options-fixednumber").is(":checked"))
			{
				var fnnc=$("#edit-cnp-recurring-default-no-payments-fnnc").val();
				if(fnnc=="")
				{
					alert("Enter default number of payments");
					$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
					return false;
				}
				
				var installOpt=$("#edit-cnp-recurr-type-option-installment").is(":checked");
				var subscrOpt=$("#edit-cnp-recurr-type-option-subscription").is(":checked");
				if(installOpt && subscrOpt)
				{
					var recurrOpt=$("#edit-cnp-default-recurring-type").val();
					if(recurrOpt=="Subscription")
					{
						//if subscription selected
						if(fnnc != "")
						{
							if(fnnc<=1)
							{
								alert("Please enter default number of payments value greater than 1")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
							if(fnnc >= 1000)
							{
								alert("Please enter value between 2 to 999 for Subscription")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
						}
						
					}
					else
					{
						//if installments selected
						if(fnnc != "")
						{
							if(fnnc<=1)
							{
								alert("Please enter default number of payments value greater than 1")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
							if(fnnc >= 999)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
						}
					}
				}
				else
				{
					//if installments only checked
					if(installOpt==true)
					{
						if(fnnc != "")
						{
							if(fnnc<=1)
							{
								alert("Please enter default number of payments value greater than 1")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
							if(fnnc >= 999)
							{
								alert("Please enter value between 2 to 998 for installment")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
						}
					}
					//if subscriptions only checked
					if(subscrOpt==true)
					{
						if(fnnc != "")
						{
							if(fnnc<=1)
							{
								alert("Please enter default number of payments value greater than 1")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
							if(fnnc >= 1000)
							{
								alert("Please enter value between 2 to 999 for Subscription")
								$("#edit-cnp-recurring-default-no-payments-fnnc").focus();
								return false;
							}
						}
					}
				}
				
			}
			
			
		}
		
		
		
		
		
	});
	
	$("#enable-disable-cnp-payment-gateway").click(function(){
		var edcnp=$("#enable-disable-cnp-payment-gateway").is(':checked');
		if(edcnp==true)
		{
			//$(".recurr_option").show();
			$("#cnp_hs_div").show();
		}
		else
		{
			//$(".recurr_option").hide();
			$("#cnp_hs_div").hide();
		}
	});
	
	//onload diable or enable gateway
	var edcnp=$("#enable-disable-cnp-payment-gateway").is(':checked');
	if(edcnp==true)
	{
		//$(".recurr_option").show();
		$("#cnp_hs_div").show();
	}
	else
	{
		$("#cnp_hs_div").hide();
		//$(".recurr_option").hide();
	}
	
	//hide and show recurring drop down
	$("#edit-cnp-recurr-type-option-installment").click(function(){
		if($(this).is(":checked") && $("#edit-cnp-recurr-type-option-subscription").is(":checked"))
		{
			$("#default_recurring_type_wrapper").show();
		}
		else
		{
			$("#default_recurring_type_wrapper").hide();
		}
	});
	$("#edit-cnp-recurr-type-option-subscription").click(function(){
		if($(this).is(":checked") && $("#edit-cnp-recurr-type-option-installment").is(":checked"))
		{
			$("#default_recurring_type_wrapper").show();
		}
		else
		{
			$("#default_recurring_type_wrapper").hide();
		}
		
		//display and hide no.of payments
		if($("#edit-cnp-recurr-type-option-subscription").is(":checked"))
		{
			$("#edit-cnp-recurring-no-of-payments-options-indefinite-openfield").parent().show();
			$("#edit-cnp-recurring-no-of-payments-options-1").parent().show();
		}
		else
		{
			$("#edit-cnp-recurring-no-of-payments-options-indefinite-openfield").parent().hide();
			$("#edit-cnp-recurring-no-of-payments-options-1").parent().hide();
		}
	});
	
	$("#edit-cnp-recurring-no-of-payments-options-1").click(function(){
		
		$(".default_no_of_payments_wrapper_start").hide();
		$(".open_filed_wrapper_start").hide();
		$(".fixed_number_no_chnage_wrapper_start").hide();
		clearValues();
	});
	$("#edit-cnp-recurring-no-of-payments-options-openfield").click(function(){
		clearValues();
		$(".default_no_of_payments_wrapper_start").show();
		$(".open_filed_wrapper_start").hide();
		$(".fixed_number_no_chnage_wrapper_start").hide();
	});
	
	$("#edit-cnp-recurring-no-of-payments-options-indefinite-openfield").click(function(){
		clearValues();
		$(".default_no_of_payments_wrapper_start").hide();
		$(".open_filed_wrapper_start").show();
		$(".fixed_number_no_chnage_wrapper_start").hide();
	});
	//edit-cnp-recurring-no-of-payments-options-fixednumber	
	$("#edit-cnp-recurring-no-of-payments-options-fixednumber").click(function(){
		if($(this).is(":checked"))
		{
			clearValues();
			$(".default_no_of_payments_wrapper_start").hide();
			$(".open_filed_wrapper_start").hide();
			$(".fixed_number_no_chnage_wrapper_start").show();
		}
	});
	
	function clearValues()
	{
		$("#edit-cnp-recurring-default-no-payments-open-filed").val("");
		$("#edit-cnp-recurring-default-no-payments").val("");
		$("#edit-cnp-recurring-max-no-payment").val("");
		$("#edit-cnp-recurring-default-no-payments-fnnc").val("");
	}
	
	function onLoadCnPActions()
	{
		
		if(!$("#edit-cnp-recurr-recur-1").is(":checked"))
		{
			$(".default_no_of_payments_wrapper_start").hide();
			$(".open_filed_wrapper_start").hide();
			$(".fixed_number_no_chnage_wrapper_start").hide();
		}
		
		
		if($("#edit-cnp-recurr-oto-oto").is(":checked") && $("#edit-cnp-recurr-recur-1").is(":checked"))
		{
			$("#default_payment_options_wrapper").show();
		}
		else
		{
			$("#default_payment_options_wrapper").hide();
		}
		
		var Loadctoi=$("#edit-cnp-recurr-type-option-installment").is(":checked");
		var Loadcros=$("#edit-cnp-recurr-type-option-subscription").is(":checked");
		if(Loadctoi==true && Loadcros==true)
		{
			$("#default_recurring_type_wrapper").show();
		}
		else
		{
			$("#default_recurring_type_wrapper").hide();
		}
		
		//hide and show subscription options on load
		if(!$("#edit-cnp-recurr-type-option-subscription").is(":checked"))
		{
			$("#edit-cnp-recurring-no-of-payments-options-indefinite-openfield").parent().hide();
			//$("#edit-cnp-recurring-no-of-payments-options-fixednumber").parent().hide();
			$("#edit-cnp-recurring-no-of-payments-options-1").parent().hide();
		}
		//no of payments options
		if($("#edit-cnp-recurring-no-of-payments-options-1").is(":checked"))
		{
			$(".default_no_of_payments_wrapper_start").hide();
			
		}
		else
		{
			$(".default_no_of_payments_wrapper_start").show();
		}
		//woocommerce_clickandpledge_indefinite
		if($("#edit-cnp-recurring-no-of-payments-options-openfield").is(":checked"))
		{
			$(".default_no_of_payments_wrapper_start").show();
			$(".open_filed_wrapper_start").hide();
			$(".fixed_number_no_chnage_wrapper_start").hide();
		}
		
		if($("#edit-cnp-recurring-no-of-payments-options-indefinite-openfield").is(":checked"))
		{
			$(".default_no_of_payments_wrapper_start").hide();
			$(".open_filed_wrapper_start").show();
			$(".fixed_number_no_chnage_wrapper_start").hide();
		}
		if($("#edit-cnp-recurring-no-of-payments-options-fixednumber").is(":checked"))
		{
			$(".default_no_of_payments_wrapper_start").hide();
			$(".open_filed_wrapper_start").hide();
			$(".fixed_number_no_chnage_wrapper_start").show();
			
		}
		
		if($("#edit-cnp-recurring-no-of-payments-options-1").is(":checked"))
		{
			$(".default_no_of_payments_wrapper_start").hide();
			$(".open_filed_wrapper_start").hide();
			$(".fixed_number_no_chnage_wrapper_start").hide();
			
		}
	
	}
	
	
	/*=====================on load actions=======================*/
	onLoadCnPActions();
	/*===========================================================*/
	$("#edit-cnp-recurr-oto-oto").click(function(){
		if($("#edit-cnp-recurr-oto-oto").is(":checked"))
		{
			$("#default_payment_options_wrapper").show();
		}
		else
		{
			$("#default_payment_options_wrapper").hide();
		}
	});
	
	$("#edit-cnp-recurring-default-no-payments-fnnc").keypress(function(e) {
		var a = [];
		var k = e.which;

		for (i = 48; i < 58; i++)
			a.push(i);

		if (!(a.indexOf(k)>=0))
			e.preventDefault();
	});
	
	$("#edit-cnp-recurring-default-no-payments-open-filed").keypress(function(e) {
		var a = [];
		var k = e.which;

		for (i = 48; i < 58; i++)
			a.push(i);

		if (!(a.indexOf(k)>=0))
			e.preventDefault();
	});
	
	$('#edit-cnp-recurring-default-no-payments').keypress(function(e) {
		var a = [];
		var k = e.which;

		for (i = 48; i < 58; i++)
			a.push(i);

		if (!(a.indexOf(k)>=0))
			e.preventDefault();
	});
	$('#edit-cnp-recurring-max-no-payment').keypress(function(e) {
		var a = [];
		var k = e.which;

		for (i = 48; i < 58; i++)
			a.push(i);

		if (!(a.indexOf(k)>=0))
			e.preventDefault();
	});
	
	//on form load
		function checkCustomPayment()
		{
			//var acc=$("#cnp_accid").val();
			if($("#edit-cnp-accid").length !=0)
			{
				var acc=$("#edit-cnp-accid").val();
				if(acc!="")
				{
					var basePath=$("#base_url_cnp").val()
					var url = location.protocol +"//"+ location.host + basePath + "admin/cnp_module/ajax/"+acc;
					console.log(url);
					$.ajax({
						type : "GET",
						url: url,
						success : function(res){
							var loadarr = Object.keys(res.GetAccountDetailResult).map((key) => [key, res.GetAccountDetailResult[key]]);
							
							
							displayPaymentOptions(loadarr);
							
						 },  
					});	
				}
			}
		}
		checkCustomPayment();
	
	
	$("#custom-payments").click(function(){
		if(!$(this).is(":checked"))
		{
			$(".payment-titles-area").hide();
			$(".ref-number").hide();
		}
		else
		{
			$(".payment-titles-area").show();
			$(".ref-number").show();
		}
	});
	if($("#edit-cnp-recurr-recur-1").is(":checked")){
		$(".recurr_option").show();
	}
	//toggeling recurring options
	$("#edit-cnp-recurr-recur-1").click(function(){
		if($(this).is(":checked"))
		{
			$(".recurr_option").show();
		}
		else
		{
			$(".recurr_option").hide();
		}
		if($("#edit-cnp-recurr-oto-oto").is(":checked"))
		{
			$("#default_payment_options_wrapper").show();
		}
	});
		
	//textarea counter
	$("#cnp_receipt_head_msg").keyup(function(){
		var el = $(this);
		if(el.val().length >= 1501){
			el.val( el.val().substr(0, 1500) );
		} else {
			$("#cnpheadcount").text(1500-el.val().length);
		}
	});
	$("#cnp_terms_con_msg").keyup(function(){
		var el = $(this);
		if(el.val().length >= 1501){
			el.val( el.val().substr(0, 1500) );
		} else {
			$("#cnptnccount").text(1500-el.val().length);
		}
	});
	//textarea counter on load page
	function displayTextareaCounter()
	{
		
			var el = $("#cnp_receipt_head_msg");
			if(el.val().length >= 1500){
				//alert("Yes:"+el.val().length)
				el.val( el.val().substr(0, 1500) );
				$("#cnpheadcount").text("0");
			} else {
				//alert(el.val().length)
				$("#cnpheadcount").text(1500-el.val().length);
			}
		
		
			var eltnc = $("#cnp_terms_con_msg");
			if(eltnc.val().length >= 1500){
				eltnc.val( eltnc.val().substr(0, 1500) );
				$("#cnptnccount").text("0");
			} else {
				$("#cnptnccount").text(1500-eltnc.val().length);
			}
		
	}
	if($("#cnp_receipt_head_msg").length != 0) {
		displayTextareaCounter();
	}

if($("#edit-cnp-vemail").val()!="")
{
	//$("#verifycode").attr("type","text");
	$("#cnpauth").val("Log in");
}
//account id change get payment information
//$("#cnp_accid").change(function(){
$("#edit-cnp-accid").change(function(){
	
	//checkCustomPayment();
	
			var acc=$("#edit-cnp-accid").val();
			if(acc!="")
			{
				var basePath=$("#base_url_cnp").val()
				var url = location.protocol +"//"+ location.host + basePath + "admin/cnp_module/ajax/"+acc;
				console.log(url);
				$.ajax({
					type : "GET",
					url: url,
					success : function(res){
						var loadarr = Object.keys(res.GetAccountDetailResult).map((key) => [key, res.GetAccountDetailResult[key]]);
						displayPaymentOptions(loadarr);
						
					 },  
				});	
			}
	
});

//display payment options based on account selection
function displayPaymentOptions(arr)
{
	
	var cards=["Amex","Discover","Jcb","Master","Visa"];
	var acceptedCards=[];
	var options="";
	var textOptions="";
	textOptions += '<b>Accepted Credit Cards</b><br/>';
	for(var po=0;po<arr.length;po++)
	{
		for(var c=0;c<cards.length;c++)
		{
			if(arr[po][0]==cards[c])
			{
				if(arr[po][1]==true)
				{
					//console.log(arr[po][0]);
					options+= arr[po][0]+"#";
					textOptions+= '<div class="js-form-item form-item js-form-type-checkbox form-type-checkbox js-form-item-cnp-payment-credit-card-options-'+cards[c].toLowerCase()+' form-item-cnp-payment-credit-card-options-'+cards[c].toLowerCase()+' form-disabled">';
					textOptions += '<input checked="checked" disabled="disabled" data-drupal-selector="edit-cnp-payment-credit-card-options-'+cards[c].toLowerCase()+'" type="checkbox" id="edit-cnp-payment-credit-card-options-'+cards[c].toLowerCase()+'" name="cnp_payment_credit_card_options['+cards[c]+']" value="'+cards[c]+'" class="form-checkbox">';
					textOptions+=' <label for="edit-cnp-payment-credit-card-options-'+cards[c].toLowerCase+'" class="option">'+cards[c]+'</label></div>';
					
					$("#payment_options_wrapper").html(textOptions);
					
				}else{
					//$("#edit-cnp-payment-credit-card-options-"+arr[po][0].toLowerCase()).parent().hide();
					//$("#edit-cnp-payment-credit-card-options-"+arr[po][0].toLowerCase()).prop('checked', false);
				}
			}
		}
	}
	$("#card_options_hidden").val(options);
	//$("#payment_options_wrapper").html(options);
	//payment_options_wrapper
}
//attachCampaignURLS

function attachCampaignURLS(data,acid)
{
	var options="";
	if(data.length>0)
	{
		options+="<option value=''>Select campaign name</option>";
		for(var cam=0;cam<data.length;cam++)
		{
			if(data[cam].orgid==acid)
			{
				var attrB="selected";
			}
			else
			{
				var attrB="";
			}
			options+= "<option "+attrB+" value='"+data[cam].orgid+"'>"+data[cam].orgid+" ["+data[cam].orgname+"]</option>";
		}
	}
	else
	{
		options+="<option value='no'>No campaigns found</option>";
	}
	return options;
}

//hide or show custom payment option based on service 
function hideShowCustomPayment(arr)
{
	for(var cp=0;cp<arr.length;cp++)
	{
		if(arr[cp][0]=="CustomPaymentType")
		{
			if(arr[cp][1]==true)
			{
				
				$(".cnp_payment_wrapper").show();
				$('#edit-cnp-payment-methdos1-custompayment').prop('checked', true);									
				$(".payment-titles").show();
				$(".ref-number").show();
				
			}
			else
			{
				$(".cnp_payment_wrapper").hide();
				$('#edit-cnp-payment-methdos1-custompayment').prop('checked', false);									
				$(".payment-titles").hide();
				$(".ref-number").hide();
			}
		}
	}
}


//add payment titles from custom titles to defualt payment method
//admdefaultpayment();	
jQuery('#edit-cnp-payment-methdos1-custompayment').on('change', function() {
	if($('#edit-cnp-payment-methdos1-custompayment').is(':checked')) {
		$('.payment-titles-area').show();
		$('.ref-number').show();
	}
	else
	{
		$('.payment-titles-area').hide();
		$('.ref-number').hide();
	}
});	


function admdefaultpayment() { 
	var paymethods = [];
	var paymethods_titles = [];
	var str = '';
	var defaultval = $('#edit-cnp-default-payment').val();
	if($('#edit-cnp-payment-methdos-credit-cards').val()!=""){
		paymethods.push('CreditCard');
		paymethods_titles.push('Credit Card');
	}
	if($('#edit-cnp-payment-methdos-echeck').val()!="") {
		paymethods.push('eCheck');
		paymethods_titles.push('eCheck');
	}
	
	if($('#custom-payments').is(':checked')) {
		$('#payment-titles').show();
		$('#edit-cnp-referencenumber-label').show();
		
		//var titles = $('#payment-titles-area').val();
		var titles = $('#edit-cnp-custompayment-titles').val();
		var titlesarr = titles.split(";");
		for(var j=0;j < titlesarr.length; j++)
		{
			if(titlesarr[j] !=""){
				paymethods.push(titlesarr[j]);
				paymethods_titles.push(titlesarr[j]);
			}
		}
	} else {
		$('#payment-titles').hide();
		$('#edit-cnp-referencenumber-label').hide();
	}
	
	if(paymethods.length > 0) {
		for(var i = 0; i < paymethods.length; i++) {
			if(paymethods[i] == defaultval) {
			str += '<option value="'+paymethods[i]+'" selected>'+paymethods_titles[i]+'</option>';
			} else {
			str += '<option value="'+paymethods[i]+'">'+paymethods_titles[i]+'</option>';
			}
		}
	} else {
	 str = '<option selected="selected" value="">Please select</option>';
	}
	$('#edit-cnp-default-payment').html(str);
}



//refresh accounts
$('#rfrshtokens').on('click', function(e){ 
		e.preventDefault();
		var basePath=$("#base_url_cnp").val();
		var acid=$("#cnp_accid_hidden").val();
		checkCustomPayment();
				var url = location.protocol +"//"+ location.host + basePath + "admin/cnp_module/refreshaccounts/"+acid;
				//console.log(url);
				$.ajax({
					type : "GET",
					url: url,
					 beforeSend: function() {
						options="<option value=''>Loading...</option>";
						$("#edit-cnp-accid").html(options);
					},
					success : function(res){
						var list=attachCampaignURLS(res,acid);
						//console.log(list);
						$("#edit-cnp-accid").html(list);
					}
				});
			
		
		
});
//onload the form hide payment options


if($("#cnppayoption").val()!="")
{
	//fef_recurr_options_division
	if($("#cnppayoption").val()=="Recurring")
	{
		$("#fef_recurr_options_division").show();
	}
	else
	{
		$("#fef_recurr_options_division").hide();
	}
}


})(jQuery, Drupal);

