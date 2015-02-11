// formHandler Javascript Class

// Options:
// formFileName =  filename/path of form html
// formPostURL = URL that will be posted against.

var $ = jQuery.noConflict();

var formHandler = function (Options)
{
    var Form
    var formHash;
    var formValidator;
    
    this.getForm = function ()
    {
        return Form;
    }

    //Get from data as JSON
    $.fn.getFormJSON = function() {
        //make sure this is of type form
        if (!this.is('form'))
        { return false; }
        var data = {};
        this.serializeArray().map(function(x){data[x.name] = x.value;});
        return data;
    };
    
    this.getFormJSON = function ()
    {
        return Form.getFormJSON();
    }
    
    this.createForm = function(Selector)
    {
        if (Options.formFileName == "" || Options.formFileName == undefined)
        { throw "ERROR:formFileName option not given"; }
        // load journal form from html and add events.
        Selector.load(Options.formFileName, function() {
            Form = Selector.find('form');
            formValidator = Form.validate();
            
            Form.change(function() {
                if (typeof formHash == 'undefined'){formHash = '';}
            });
            // Prevent enter causing a submit. And handle it ourselves.
            Form.on('submit',function(e) {
                e.preventDefault();
            })
            // Set up events for the buttons
            Form.find('button').button();
            Form.find('button[function]').click(function() {
                Form.submit();
                // Make sure the form is valid
                if (formValidator.valid()) {
                    var data = Form.getFormJSON();
                    postForm(Form,$(this).attr("function"),data);
                }
            });
            Form.find('button[type="reset"]').click(function() {
                formReset();
            }).attr("type","button");
        });
    }
    
    function showMessageDialog(json)
    {
        $('<div>'+json.message+'</div>').dialog({
            height: 'auto',
            width: 600,
            title: json.title,
            resizable: false,
            modal: true,
            buttons: {
                OK: function() {
                    $(this).dialog( "close" );
                }
            }
        });
    }
    
    function postForm(Form,Function,Data)
    {
        var URL;
        if (Options.formPostURL == "" || Options.formPostURL == undefined)
        { url = null; } else { url = Options.formPostURL; }
        // Send a request to the server
        $.ajax({
            type: "POST",
            url: Options.formPostURL,
            data: {"function":Function,"data":Data},
            datatype: 'json',
            success: function(json) {
                if (json.success == true) {
                    // Eventually reset the form, if everything went right.
                    Form.trigger("reset");
                }
        
                showMessageDialog(json);
            },
            error: function(json) {
                showMessageDialog({"title":"Return Code:"+json.status,"message":json.statusText});
            }
        }); 
    }
    
    this.fillForm = function(Data)
    {
        return fillForm(Form,Data);
    }
    
    function fillForm(Form,Data)
    {
        $.when(formChanged()).done(function() {
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
        });
    }
    
    function formReset()
    {
        $.when(formChanged()).done(function() {
            Form.trigger("reset");
            formHash = undefined;
            //Form.filter(':input').prop('disabled',false);
            formValidator.resetForm();
        });
    }
    
    function formChanged()
    {
        return $.Deferred(function() {
            var self = this;
            if (formHash != Form.serialize() && typeof formHash !='undefined') {
                $('<div><img src="/images/icons/warning.png"><p>You will lose all changes. Do you wish to continue?</p></div>').dialog({
                    title: "Warning!",
                    resizable: false,
                    modal: true,
                    buttons: {
                        "Continue": function() {
                            $(this).dialog( "close" );
                            formHash = Form.serialize();
                            formValidator.resetForm();
                            self.resolve();  
                        },
                        Cancel: function() {
                            $(this).dialog( "close" );
                            self.reject();  
                        }
                    }
                }); 
            } else {
                self.resolve(); 
            }
        });
    }
}