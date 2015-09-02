(function( $ ) {
    $.fn.editableForm = function(command) {
        
        return this.each(function() {
            //plug-in
            
            //debugger;
            
            // // make sure this is of type form
            // if (!this.is('form'))
            // { return false; }
            
            self = $(this);
            
            //debugger;
            
            if (command && typeof command != "object") {
                switch (command) {
                    case 'cancel':
                        // $('.revertField', this).each(function() {
                            // $('[name="'+$(this).attr('original')+'"]').val($(this).val());
                            // });
                    case 'reset':
                        
                        $('input,textarea', this).each(function() {
                            $(this)
                                .attr('readonly',true)
                                .removeClass('active');
                                //.rules("remove");
                        });
                        $('.editableForm')
                        .append('<div class="innerForm"><div>')
                        .removeClass('active');
                        
                        $('.editableFormButton,.showOnEdit', this).css({opacity: 1.0, visibility: "visible" }).animate({opacity: 0.0});
                        //$('.revertField', this).remove();
                        window.onbeforeunload = null;
                        break;
                    default:
                        break;
                }
                return;
            }
            
            var options = command;
            
            $.validator.methods._required = $.validator.methods.required;
            $.validator.methods.required = function( value, element, param )
            {
                if (typeof this.settings.rules[ $(element).attr('name') ] != 'undefined' 
                && typeof this.settings.rules[ $(element).attr('name') ].remote != 'undefined') {
                    return true;
                }
                return  $.validator.methods._required.call( this, value, element, param );
            }
            
            self.hashValue = self.serialize();
            
            self.wrap('<div class="editableForm formReadonly"></div>');
            
            self.append('<div style="position:relative;"><div id="notycontainer" style="position:absolute;top:0px;bottom:0px;width:600px;"></div><br><button class="editableFormButton" type="submit">Save</button>&nbsp;<button id="cancelButton" class="editableFormButton" type="button">Cancel</button></div>');
            $('.editableFormButton').css('visibility','hidden').button();
            
            $('.editableForm').append('<div class="innerForm"><div>')
            
            //debugger;
            
            $('input,textarea', this).each(function() {
                $(this)
                .attr('readonly',true)
                .addClass('formfield');
            });
            
            $('.editableForm').on("click", function() {
                if (!$(this).hasClass('active')) {
                        window.onbeforeunload = function() {
                        return "You still have unsaved changed!\nAre you sure you want to navigate away?";
                    }
                    $(this).addClass('active');
                    
                    var url = options.validationURL;
                    $('input,textarea', this).each(function() {
                        //self.append('<input class="revertField" type="hidden" original="'+
                        //  $(this).attr('name') + '">');
                        //$('.revertField[original="'+$(this).attr('name')+'"]').val($(this).val());
                        $(this).attr('readonly',false)
                            .addClass('active')
                            .rules( "add", {
                                remote: {
                                    url: url,
                                }
                        })
                        $('.innerForm').remove();
                    });
                    $('.editableFormButton,.showOnEdit',this).css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0});
                }   
            });
            
            $('#cancelButton', this).click(function(event) {
                event.stopPropagation();
                //$(this).editableForm('cancel');
            });
       
        });
    };
    
    $.fn.getFormJSON = function() {
        //make sure this is of type form
        if (!this.is('form'))
        { return false; }
        var data = {};
        this.serializeArray().map(function(x){data[x.name] = x.value;});
        return data;
    };
    
    $.fn.fillForm = function(Data) {
        //make sure this is of type form
        if (!this.is('form'))
        { return false; }
        var Form = $(this);

        if (typeof Data != "undefined" && Object.keys(Data).length > 0)
        {
            Form.trigger("reset");
            $.each(Data, function(name,value) {
                var selector = Form.find('[name="'+name+'"]');
                var elementType = selector.prop("type");
                switch (elementType)
                {
                    case "radio":
                        selector.filter('[value="'+value+'"]').prop("checked",true);
                        break;
                    case "checkbox":
                        selector.prop("checked",value);
                        break;
                    case "select":
                        $.each(value, function(index,value) {
                            selector.filter('[value="'+value+'"]').prop("checked",true);
                        });
                        break;
                    case "file":
                        /* Do Nothing */
                        break;
                    default:
                        selector.val(value);
                        selector.filter(':hidden').change();
                        break;
                }
            });
            return true;
        } else {
            return false;
        }
    };
    
    function isForm(Selector)
    {
        return Selector.is('form');
    }
}( jQuery ));