(function( $ ) {
    $.fn.editableForm = function(command) {
        //make sure this is of type form
        if (!this.is('form'))
        { return false; }
        
        self = this;
        
        if (command) {
            switch (command) {
                case 'reset': 
                    this.find('input').each(function() {
                        $(this)
                        .attr('readonly',true)
                        .removeClass('active');
                    });
                    $('#editableWrapper')
                    .append('<div class="innerForm"><div>')
                    .removeClass('active');
                    $('.editableFormButton').css({opacity: 1.0, visibility: "visible"}).animate({opacity: 0.0});
                    break;
                default:
                    break;
            }
            return;
        }
        
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
        
        self.wrap('<div id="editableWrapper" class="editableForm formReadonly"></div>');
        
        self.append('<button class="editableFormButton" type="submit">Save</input>');
        self.append('<button class="editableFormButton" type="reset">Reset</input>');
        $('.editableFormButton').css('visibility','hidden');
        
        $('#editableWrapper').append('<div class="innerForm"><div>')
        
        
        this.find('input').each(function() {
            $(this)
            .attr('readonly',true)
            .addClass('formfield');
        });
        
        $('#editableWrapper').on("click", function() {
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                var url = base_path + "/services/person/validateProperty";
                self.find('input').each(function() {
                    $(this).attr('readonly',false)
                    .addClass('active')
                    .rules( "add", {
                        remote: {
                            url: url,
                        }
                    })
                    $('.innerForm').remove();
                });
                $('.editableFormButton').css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0});
            }   
        });
        
        return this.each(function() {
            //plug-in placeholder
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