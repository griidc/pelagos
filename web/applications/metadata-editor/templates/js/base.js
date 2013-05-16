
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

(function ($) {
    $(function() {
		$( "#mitabs" ).tabs({
            heightStyleType: "fill"
        });
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
			coord: {
				range: [-90,90]
			},
			zip: {
				required: true,
				digits: true,
				minlength: 5,
				maxlength: 5
			}				
		});
		
		$("#metadata").validate();
       
    });
})(jQuery);

