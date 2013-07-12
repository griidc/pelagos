
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
            heightStyleType: "fill",
			activate: function(event, ui) {
				var validator = $("#metadata").validate();
				
				if (validator.numberOfInvalids() > 0)
				{
					//$("#metadata").validate();
					$("#metadata").valid();
					validateTabs(false);
				}
			}
        });
		{{jqUIs}}
		
		$( "#metadialog" ).dialog({
			title: "Metadata Editor:",
			modal: true,
			width: 500,
			autoOpen: false,
			resizable: false,
			buttons: {
				Ok: function() {
					$("#metadata").validate().cancelSubmit = true;
					$("#metadata").submit();
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
		});
		
		$( "#udidialog" ).dialog({
			title: "Metadata Editor:",
			modal: true,
			width: 500,
			autoOpen: false,
			resizable: false,
			buttons: {
				Ok: function() {
					$( this ).dialog( "close" );
					var urls = location.href.split("?");
					var baseurls = location.href.split("/");
					var udiurl = urls[0] + "?dataUrl=http://" + baseurls[2] + "/metadata-generator/" + $('#udifld').val();
					location.href = udiurl;
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
		});
		
		$( "#errordialog" ).dialog({
			title: "Warning:",
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"OK": function() {
					$( this ).dialog( "close" );
				}
			},
		});    

		$( "#helpdialog" ).dialog({
			title: "Metadata Generator Help:",
			autoOpen: false,
			modal: true,
			resizable: true,
			width: 750,
            open: function() {
                if ($(this).parent().height() > $(window).height()) {
                    $(this).height($(window).height()*0.8);
                    $(this).parent().css({top:'40px'});
                }
            },
			buttons: {
				"OK": function() {
					$( this ).dialog( "close" );
				}
			},
		});    
		
		$( "#savedialog" ).dialog({
			title: "Metadata Editor:",
			autoOpen: false,
			modal: true,
			resizable: true,
			width: 500,
            open: function() {
                if ($(this).parent().height() > $(window).height()) {
                    $(this).height($(window).height()*0.8);
                    $(this).parent().css({top:'40px'});
                }
            },
			buttons: {
				Ok: function() {
					var filenamefld = $("#filename").val();
					$("#MI1").val(filenamefld);
					$("#metadata").validate().cancelSubmit = true;
					$("#metadata").submit();
					$( this ).dialog( "close" );
					
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
		});    
		
		
		$( "#generate" )
			.button({
					icons: {
						primary: "ui-icon-check",
						secondary: "ui-icon-disk"
					}
				})
			.click(function(event) {
				$('#metadata').valid();
				var validator = $("#metadata").validate();
				var numOfInvalids = validator.numberOfInvalids();
				var errText =  "The Metadata form has " + numOfInvalids + " incomplete field(s).<br>Please review fields prior to download.<p/>Please click OK to continue.";
				
				
				var filenamefld = $("#MI1").val();
				$("#filename").val(filenamefld);
				
				if (validator.numberOfInvalids() > 0)
				{
					$('#errordialog').html(errText); 
					$("#errordialog").dialog( "open" );
				}
				
				//$('#metadialog').html("All required fields are complete.<br>Please enter a filename:.");
				$('#metadata').valid();
				validateTabs();
				$("#metadata").submit();
			});
		$( "#upload" )
		.button({
			icons: {
				primary: "ui-icon-folder-open"
				}
			})
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
		$( "#fromudi" ).button({
			icons: {
				primary: "ui-icon-link"
			}
		})
		.click(function( event ) {
			$("#udidialog").dialog("open");
		});
		
		$( "#forcesave" ).button({
				icons: {
					primary: "ui-icon-disk"
				}
			})
			.click(function( event ) {
			var spntxt = "Your metadata file is ready for download.<br/>Your file has NOT been validated.<br/>";
			$("#dialogtxt").html = spntxt;
			var filenamefld = $("#MI1").val();
			$("#filename").val(filenamefld);
			
			$("#metadata").validate().cancelSubmit = true;
			$("#metadata").submit();
		});
		
		$( "#startover" ).button({
			icons: {
				primary: "ui-icon-refresh"
			}
		})
			.click(function( event ) {
			var urls = location.href.split("?");
			location.href=urls[0];
		});
		
		$( "#helpscreen" ).button({
		icons: {
			primary: "ui-icon-help"
		}
		})
		
		.click(function( event ) {
			$("#helpdialog").dialog("open");
//			location.href='/metadata';
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
				if ($("#errordialog").dialog( "isOpen" )== false)
				{
					
				}
				isbad = true;
				
			},
			submitHandler: function(form) {
				if ($("#savedialog").dialog( "isOpen" )== false)
				{
					$("#savedialog").dialog( "open" );
				}
				else
				{
					form.submit();
				}
			}
		});
    });
})(jQuery);
