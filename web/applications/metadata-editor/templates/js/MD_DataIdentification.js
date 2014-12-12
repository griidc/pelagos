
function addFields{{instanceName}}(where)
{
	pipe = "|";

	fieldA = document.getElementById("MDD5a_{{instanceName}}").value;
	fieldB = document.getElementById("MDD5b_{{instanceName}}").value;
	fieldC = document.getElementById("MDD5c_{{instanceName}}").value;
	fieldD = document.getElementById("MDD5d_{{instanceName}}").value;
	fieldE = document.getElementById("MDD5e_{{instanceName}}").value;
	fieldF = document.getElementById("MDD5f_{{instanceName}}").value;
	
	newval = fieldA + pipe + fieldB + pipe + fieldC + pipe + fieldD + pipe + fieldE + pipe + fieldF;
	document.getElementById(where).value = newval;
}