function addUCList(patch, callback) {

        var params;
        var xmlDoc;
        var xmlhttp = buildXMLHTTP();
	var lid;
        if (xmlhttp === false) {
                return false;
        }

        document.getElementById("msg_uclist").innerHTML = "Adding..";
	var sel = document.getElementById("selectAddUCList");
	lid = sel.options[sel.selectedIndex].value;

        xmlhttp.onreadystatechange=function()
        {
                if(xmlhttp.readyState==4) {
                  if (xmlhttp.status == 200 || xmlhttp.status == 304) {
                        callback(xmlhttp.responseText);
                  }
                }
        }
        params = "/addto_uclist/p/" + patch;
        params = params + "/i/" + lid;
        xmlhttp.open("GET",params,true);
        xmlhttp.send(null);
        return true;
}

function delUCList(patch, lid, callback) {

        var params;
        var xmlDoc;
        var xmlhttp = buildXMLHTTP();
        if (xmlhttp === false) {
                return false;
        }

        xmlhttp.onreadystatechange=function()
        {
                if(xmlhttp.readyState==4) {
                  if (xmlhttp.status == 200 || xmlhttp.status == 304) {
                        callback(patch, xmlhttp.responseText);
                  }
                }
        }
        params = "/delfrom_uclist/p/" + patch;
        params = params + "/i/" + lid;
        xmlhttp.open("GET",params,true);
        xmlhttp.send(null);
        return true;
}

function showDelMsg(patch, xmlDoc)
{
  var divid = "p_"+patch;
  document.getElementById(divid).innerHTML = xmlDoc;
}


function showMessage(xmlDoc)
{
  document.getElementById("msg_uclist").innerHTML = xmlDoc;
}

