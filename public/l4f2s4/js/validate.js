var req;
function checkemptyfield()
{
var fname=document.getElementById("fname").value;
var username=document.getElementById("uname").value;
var email=document.getElementById("mail").value;
var pass = document.getElementById("pass").value;
var confirmp=document.getElementById("cpass").value;
if(fname=="" ||username=="" || email=="" || pass =="" || confirmp=="") {
	document.getElementById('general').style.color = "red";
	document.getElementById('general').innerHTML = "All fields are required";
	return false;
}
if(!pass.match(confirmp))
{
	document.getElementById('just').style.color="red";
	document.getElementById('just').style.textAlign="center";
	document.getElementById('just').innerHTML="Password mismatch";
	document.getElementById('cpass').style.borderColor="red";
	document.getElementById('pass').style.borderColor="red";
	return false;
}
if(pass.length<8 && !/[^a-zA-Z0-9_-]/.test(pass))
{
	document.getElementById('just').style.color="red";
	document.getElementById('just').style.textAlign="center";
	document.getElementById('pass').style.borderColor="red";
	document.getElementById('cpass').style.borderColor="red";
	document.getElementById('just').innerHTML="Password must be atleast 8 characters";

   return false;
}
}
