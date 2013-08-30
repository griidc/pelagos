//
// URL Validation and php Ajax script for form.
//

var $ = jQuery.noConflict();
    
function checkURL(url)
{
    if (url.length==0)
    { 
        return url;
    }
    if (!isUrl(formatHTTPURL(url,true)))
    {
        return url;
    }
    else
    {
        validateURL(formatHTTPURL(url,false));
        return formatHTTPURL(url,false);
    }
}

function validateURL(url)
{
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function validateURL()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            document.getElementById("urlValidate").value=xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET","?url="+url,true);
    xmlhttp.send();
}

function formatHTTPURL(url,validate)
{
    if ((url.indexOf("http://")== -1) && (url.indexOf("https://") == -1) && (url.indexOf("ftp://") == -1))
    {
        if (((url.indexOf("http")== -1) && (url.indexOf("https") == -1) && (url.indexOf("ftp") == -1)) || validate)
        {
            return "http://" + url;
        }
        else
        {
            return url;// + "://";
        }
    }
    else 
    {
        return url;
    }
}

function isUrl(url) 
{
    //var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    //return regexp.test(url);
    var strRegex = "^((https|http|ftp|rtsp|mms)?://)"
    + "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?"
    + "(([0-9]{1,3}\.){3}[0-9]{1,3}"
    + "|"
    + "([0-9a-z_!~*'()-]+\.)*" 
    + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." 
    + "[a-z]{2,6})"
    + "(:[0-9]{1,4})?" 
    + "((/?)|" 
    + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$";
    var re=new RegExp(strRegex);
    return re.test(url.toLowerCase());
}

function setHelpText(hText)
{
    document.getElementById("helptext").innerHTML = hText;
    alert('huh');
}