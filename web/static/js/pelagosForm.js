(function( $ ) {
    $.fn.pelagosForm = function() {
        //make sure this is of type form
        if (!this.is('form'))
        { return false; }
        
        this.find('input').each(function() {
            $(this)
            .attr('readonly',true)
            .addClass('specialfield')
            .wrap( "<span class='editField'></span>" )
            .click( function () {
                $(this)
                .attr("readonly",false)
                .parent().removeClass("editField");
            })
            .blur( function () {
                $(this)
                .attr("readonly",true)
                .parent().addClass("editField");
            });
        });
        
        return this.each(function() {
            //stuff
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
    
    function isForm(Selector)
    {
        return Selector.is('form');
    }
}( jQuery ));