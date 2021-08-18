var req;
function checkemptyfield(){
var fname=document.getElementById("fuser").value;	
var username=document.getElementById("name").value;
 var email=document.getElementById("mail").value;
 var pass = document.getElementById("password").value;
 var confirmp=document.getElementById("confirm").value;	
                  if(fname=="" ||username=="" || email=="" || pass =="" || confirmp==""){
	                document.getElementById('general').style.color="red";
					document.getElementById('general').innerHTML="All fields are required";	
					return false;
					}
				 else if(!pass.match(confirmp)){
							document.getElementById('general').style.color="red";
							document.getElementById('general').innerHTML="Password mismatch";	
							return false;
					}
				  else  if(pass.length<8 && !/[^a-zA-Z0-9_-]/.test(pass)){
							document.getElementById('general').style.color="red";
							document.getElementById('general').innerHTML="Password must be atleast 8 characters";	
							return false;
					}
}
