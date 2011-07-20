function buildXMLHTTP()
{
        if (window.XMLHttpRequest)
        {
                // code for IE7+, Firefox, Chrome, Opera, Safari
                return new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
                // code for IE6, IE5
                return new ActiveXObject("Microsoft.XMLHTTP");
        }
        else
        {
                alert("Your browser does not support XMLHTTP!");
        }
        return false;
}

