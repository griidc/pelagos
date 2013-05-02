(function ($) {
    $(function() {
        $( "#tabs" ).tabs({
            heightStyleType: "fill",
            disabled: [2,3,4],
            selected: <?php echo $tabselect;?>
        });
        
        $( "#availdate" ).datepicker({
            showOn: "button",
            buttonImageOnly: false,
            dateFormat: "yy-mm-dd",
            autoSize:true
        });
    });
    
    $(document).ready(function(){
        $("#regForm").validate({
        rules: {
            title:
            {
                required: true,
                maxlength: 200
            },
            abstrct:
            {
                required: true,
                maxlength: 4000
            },
            sshdatapath: "required",
            auth: "required",
            sshauth: "required",
            pocname: "required",
            whendl: "required",
            pullds: "required",
            pocemail:
            {
                required: true,
                email: true
            },
            dataurl: 
            {
                required: true,
                url: true
            },
            metadataurl: 
            {
                required: false,
                url: true
            },
            uname:
            {
                required: "#auth:checked"
            },
            pword:
            {
                required: "#auth:checked"
            },
            availdate:
            {
                required: true,
                dateISO: true
            },
            regbutton: 
            {
				required: "#registry_id:minlength:15",
            },
            dataset_originator:
            {
                required: true,
                maxlength: 200
            }
        },
        messages: {
            txtMetaURL: "Please enter a valid URL.",
            radAuth: "Please select one.",
            dataurl: { 
                required: "Please enter a valid URLress", 
                remote: jQuery.format("Please check the URL, it may not exist!") 
            }, 
        }
        });
    
        $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "middle left",
                at: "middle right",
                viewport: $(window)
            },
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                event: "mouseleave blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "ui-tooltip-shadow ui-tooltip-tipped"
            }
        });
        
        $("#qtip_title").qtip({
            content: $("#title_tip")
        });
        
        
        
    });
})(jQuery);
