(function( $ ) {
    $.fn.editableForm = function() {
        //make sure this is of type form
        if (!this.is('form'))
        { return false; }
        
        self = this;
        
        self.hashValue = 'test';
        
        self.wrap('<div id="editableWrapper" class="editableForm formReadonly"></div>');
        
        $('#editableWrapper').append('<div class="innerForm"><div>');
        
        this.find('input').each(function() {
            $(this)
            .attr('readonly',true)
            .addClass('formfield')
        });
        
        $('#editableWrapper').one("click", function() {
            self.find('input').each(function() {
                $(this).attr('readonly',false)
                .addClass('active')
                $('.innerForm').remove();
            });
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