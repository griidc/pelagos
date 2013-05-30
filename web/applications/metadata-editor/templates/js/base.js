
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

(function ($) {
    $(function() {
		$( "#dtabs" ).tabs({
            heightStyleType: "fill"
        });
		{{jqUIs}}
    });
	
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
			rules:
			{
				example: {}
				{{validateRules}}
			},
			focusInvalid: true,
			focusCleanup: false,
			invalidHandler: function(event, validator) {
				
				$('#dtabs-6 input').each(function() {
					if ($(this).hasClass('error')) {
						// hilight tab 6
						//alert('error in tab 6');
						$( "#dtabs" ).tabs({
							active: 6
						});
						//break;
					}
				});
				$('#dtabs-5 input').each(function() {
					if ($(this).hasClass('error')) {
						// hilight tab 5
						//alert('error in tab 5');
						$( "#dtabs" ).tabs({
							active: 5
						});
						//break;
					}
				});
				$('#dtabs-4 input').each(function() {
					if ($(this).hasClass('error')) {
						// hilight tab 4
						//alert('error in tab 4');
						$( "#dtabs" ).tabs({
							active: 4
						});
						//break;
					}
				});
				$('#dtabs-3 input').each(function() {
					if ($(this).hasClass('error')) {
						// hilight tab 3
						//alert('error in tab 3');
						$( "#dtabs" ).tabs({
							active: 3
						});
						//break;
					}
				});
				$('#dtabs-2 input').each(function() {
					if ($(this).hasClass('error')) {
						// hilight tab 2
						//alert('error in tab 2');
						$( "#dtabs" ).tabs({
							active: 2
						});
						//break;
					}
				});
				$('#dtabs-1 input').each(function() {
					if ($(this).hasClass('error')) {
						// hilight tab 1
						//alert('error in tab 1');
						$( "#dtabs" ).tabs({
							active: 1
						});
						//break;
					}
				});
				$('#dtabs-0 input').each(function() {
					if ($(this).hasClass('error')) {
						// hilight tab 0
						//alert('error in tab 0');
						$( "#dtabs" ).tabs({
							active: 0
						});
						//break;
					}
				});
			}
		});
    });
})(jQuery);

