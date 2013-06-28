
window.onload=function()
{
	altRows('alternatecolor');
}

var $ = jQuery.noConflict();

function copyValue(what,to)
{
	document.getElementById(to).value = what.value;
}

function altRows(id){
	if(document.getElementsByTagName){  
		
		var table = document.getElementById(id);  
		var rows = table.getElementsByTagName("tr"); 
		 
		for(i = 0; i < rows.length; i++){          
			if(i % 2 == 0){
				rows[i].className = "evenrowcolor";
			}else{
				rows[i].className = "oddrowcolor";
			}      
		}
	}
}

function sortSelect(selectToSort) {
    var arrOptions = [];

    for (var i = 0; i < selectToSort.options.length; i++)  {
        arrOptions[i] = [];
        arrOptions[i][0] = selectToSort.options[i].value;
        arrOptions[i][1] = selectToSort.options[i].text;
        arrOptions[i][2] = selectToSort.options[i].selected;
    }

    arrOptions.sort();

    for (var i = 0; i < selectToSort.options.length; i++)  {
        selectToSort.options[i].value = arrOptions[i][0];
        selectToSort.options[i].text = arrOptions[i][1];
        selectToSort.options[i].selected = arrOptions[i][2];
    }
}

function validateTabs(shouldTabFocus)
	{
	tab0HasErrors = false;
	tab1HasErrors = false;
	tab2HasErrors = false;
	tab3HasErrors = false;
	tab4HasErrors = false;
	tab5HasErrors = false;
	tab6HasErrors = false;
	
	$('#dtabs-0 input,#dtabs-0 textarea,#dtabs-0 select').each(function() {
		if ($(this).hasClass('error')) {
			// hilight tab 0
			//alert('error in tab 0');
			if (shouldTabFocus != false)
			{ 
				$( "#dtabs" ).tabs({
					active: 6
				});
			}
			$('#chkimgtab0').attr("src","/dm/includes/images/x.png");
			tab0HasErrors = true;
			//break;
		}
	});
	
	$('#dtabs-5 input,#dtabs-5 textarea,#dtabs-5 select').each(function() {
		if ($(this).hasClass('error')) {
			// hilight tab 5
			//alert('error in tab 5');
			if (shouldTabFocus != false)
			{ 
				$( "#dtabs" ).tabs({
					active: 5
				});
			}
			$('#chkimgtab5').attr("src","/dm/includes/images/x.png");
			tab5HasErrors = true;
			//break;
		}
	});
	
	$('#dtabs-6 input,#dtabs-6 textarea,#dtabs-6 select').each(function() {
		if ($(this).hasClass('error')) {
			// hilight tab 6
			//alert('error in tab 6');
			if (shouldTabFocus != false)
			{ 
				$( "#dtabs" ).tabs({
				active: 4
				});
			}
			$('#chkimgtab6').attr("src","/dm/includes/images/x.png");
			tab6HasErrors = true;
			//break;
		}
	});
	
	$('#dtabs-4 input,#dtabs-4 textarea,#dtabs-4 select').each(function() {
		if ($(this).hasClass('error')) {
			// hilight tab 4
			//alert('error in tab 4');
			if (shouldTabFocus != false)
			{ 
				$( "#dtabs" ).tabs({
					active: 3
				});
			}
			$('#chkimgtab4').attr("src","/dm/includes/images/x.png");
			tab4HasErrors = true;
			//break;
		}
	});
	
	$('#dtabs-3 input,#dtabs-3 textarea,#dtabs-3 select').each(function() {
		if ($(this).hasClass('error')) {
			// hilight tab 3
			//alert('error in tab 3');
			if (shouldTabFocus != false)
			{ 
				$( "#dtabs" ).tabs({
					active: 2
				});
			}
			$('#chkimgtab3').attr("src","/dm/includes/images/x.png");
			tab3HasErrors = true;
			//break;
		}
	});
	
	$('#dtabs-1 input,#dtabs-1 textarea,#dtabs-1 select').each(function() {
		if ($(this).hasClass('error')) {
			// hilight tab 1
			//alert('error in tab 1');
			if (shouldTabFocus != false)
			{ 
				$( "#dtabs" ).tabs({
					active: 1
				});
			}
			$('#chkimgtab1').attr("src","/dm/includes/images/x.png");
			tab1HasErrors = true;
			//break;
		}
	});
	
	$('#dtabs-2 input,#dtabs-2 textarea,#dtabs-2 select').each(function() {
		if ($(this).hasClass('error')) {
			// hilight tab 2
			//alert('error in tab 2');
			if (shouldTabFocus != false)
			{ 
				$( "#dtabs" ).tabs({
					active: 0
				});
			}
			//$("#metadata").validate();
			$('#chkimgtab2').attr("src","/dm/includes/images/x.png");
			tab2HasErrors = true;
			//break;
		}
	});
	
	if (!tab0HasErrors){$('#chkimgtab0').attr("src","/dm/includes/images/check.png");};
	if (!tab1HasErrors){$('#chkimgtab1').attr("src","/dm/includes/images/check.png");};
	if (!tab2HasErrors){$('#chkimgtab2').attr("src","/dm/includes/images/check.png");};
	if (!tab3HasErrors){$('#chkimgtab3').attr("src","/dm/includes/images/check.png");};
	if (!tab4HasErrors){$('#chkimgtab4').attr("src","/dm/includes/images/check.png");};
	if (!tab5HasErrors){$('#chkimgtab5').attr("src","/dm/includes/images/check.png");};
	if (!tab6HasErrors){$('#chkimgtab6').attr("src","/dm/includes/images/check.png");};
}

isBad = false;

function uploadFile()
{
	$("#uploadfrm").submit();
}

(function ($) {
    $(function() {
		$(document).ready(function() 
		{
			{{onReady}}
		});
		
		$("#file").change(function() { 
			uploadFile();
		});
		
		$( "#dtabs" ).tabs({
            heightStyleType: "fill"
        });
		{{jqUIs}}
		
		$( "#metadialog" ).dialog({
			modal: true,
			autoOpen: false,
			resizable: false,
			buttons: {
				Ok: function() {
					//$("#metadata").validate().cancelSubmit = true;
					$("#metadata").submit();
					$( this ).dialog( "close" );
				}
			},
		});
		
		$( "#generate" )
			.button()
			.click(function( event ) {
				$('#metadata').valid();
				validateTabs();
				$("#metadata").submit();
			});
		$( "#upload" )
		.button()
		.click(function( event ) {
			if ($.browser.msie)
			{
				$("#loadfrm").css('display', 'inline');
			}
			else
			{
				$("#file").click();
			}
		});
		$( "#reset" ).button()
		.click(function( event ) {
			$(':input','#metadata')
			.not(':button, :submit, :reset, :hidden')
			.val('')
			.removeAttr('checked')
			.removeAttr('selected');
		});
		
		$( "#forcesave" ).button()
			.click(function( event ) {
			$("#metadata").validate().cancelSubmit = true;
			$("#metadata").submit();
		});
		
		$( "#startover" ).button()
			.click(function( event ) {
			location.href="/metadata";
		});
	 });
		
	$("#metadata :checkbox").change(function(){
		if(this.checked) {
			$(this).parent("div").removeClass("error");
		}
	})
	
    $(document).ready(function(){
		
		$.validator.addClassRules({
			phone: {
				digits: false,
				minlength: 2,
				maxlength: 15
			},
			latcoord: {
				range: [-90,90]
			},
			longcoord: {
				range: [-180,180]
			},
			zip: {
				digits: true,
				minlength: 5,
				maxlength: 5
			},
			starttime: {
				dateISO: true
			},
			endtime: {
				dateISO: true//,
				//greaterThan: "starttime"
			}
		});
		
		jQuery.validator.addMethod("greaterThan", 
		function(value, element, params) {
			
			if (!/Invalid|NaN/.test(new Date(value))) {
				return new Date(value) > new Date($(params).val());
			}
			
			return isNaN(value) && isNaN($(params).val()) 
			|| (Number(value) > Number($(params).val())); 
		},'Must be greater than {0}.');
				
		$("#metadata").validate({
			ignore: ".ignore",
			onfocusout: function(event, validator) {
				if (this.numberOfInvalids() > 0){validateTabs(false);}
				},
			rules:
			{
				example: {}
				{{validateRules}}
			},
			focusInvalid: true,
			focusCleanup: false,
			invalidHandler: function(event, validator) {

				isbad = true;
				
			},
			submitHandler: function(form) {
				if ($("#metadialog").dialog( "isOpen" )== false)
				{
					$("#metadialog").dialog( "open" );
				}
				else
				{
					form.submit();
				}
			}
		});
    });
})(jQuery);
