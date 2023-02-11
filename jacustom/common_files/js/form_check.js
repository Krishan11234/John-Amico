/*  Program name		: form_check.js
 	Program function	: general form checking client side js functions 	
	Author				: Colbridge
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
	
sample usage of some of the functions declared below are as follows:
<SCRIPT language=javascript src="./js/form_check.js"></SCRIPT>
<script language="javascript">
<!---  
function Validate(theform)
{
	if (isEmpty(theform.City.value)){    //this is text box validation example
  		alert("Please enter your City.\nNo spaces or blanks allowed.");
		theform.City.focus();
		return false; 
	}
		
	if(theform.int_country_id.selectedIndex == 0){  //this is dropdown validation example
	  alert("Please Select your Counrty");
	  theform.int_country_id.focus();
	  return false;	
	}	
	
	var retval = usernameCheck(theform.dealer_username.value) //sample username validation example
	if (retval == false){
		theform.dealer_username.focus();
  		return false;
	}	
	
	var retval = emailCheck(theform.email.value)  // email validation example
	if (retval == false){
		theform.email.focus();
  		return false;
	}	 
}	
--->
</script>	
--------------------------------------------------------------------*/
// function to open a new window
function openWin(windowURL, windowName, windowFeatures){ 
	return window.open(windowURL, windowName, windowFeatures); 
}

// function to confirm record deletion
function confirmCleanUp(Link) {
   if (confirm("Are you sure you want to delete ?")) {
      location.href=Link;
   }
}

// to simulate the clicking of the browser back button
function fn_back(){
	window.history.back();
}	

// function to check indiger field
function IsInteger(snum)
{
	var reInteger = /^\d+$/
    return reInteger.test(snum)
}

// function to check SSN US
function IsSSN(snum)
{
	var reSSN = /^(\d{3})(-)(\d{2})(-)(\d{4})$/	
    return reSSN.test(snum)
}

// function to check US phone - fomat 999-999-9999
function IsPhone(snum)
{
	var rePhone = /^(\d{3})(-)(\d{3})(-)(\d{4})$/	
    return rePhone.test(snum)
}

// Check whether string s is empty.
function isEmpty(s)
{  
	return ((s == null) || (s.length == 0) || (s.substr(0,1) == " "))
}

// function to check float/decimal fileds
function isFloat(s)
{  
	var reFloat = /^((\d+(\.\d*)?)|((\d*\.)?\d+))$/	
	return reFloat.test(s)	   	
}

// function to check username fields
function usernameCheck (s){
  // this is done to avoid any special characters
  var reUsername = /^[a-zA-Z0-9][a-zA-Z0-9_]*$/
  
  if (isEmpty(s)){
  	 alert("Please enter a desired Username. Minimum 8 characters.\nNo spaces, blanks or special characters.");
	 return false; 
  } 
  else{
   	 var matchArray = s.match(reUsername); // is the format ok? 
	 if (matchArray == null) { 
		alert("Username should contain only \nalphabets, digits or underscore (_).");
		return false; 
	 }	 	
	 else{ 
	  	 if(s.length<4){		
			alert("Username should be minimum of 4 characters");	
			return false; 
		 }	
	 }			
  }      	 
  return true;
}

// function to check username fields
function passwordCheck (s){
  // this is done to avoid any special characters
  var rePassword = /^[a-zA-Z0-9][a-zA-Z0-9~!@#$%^&*()]*$/
  
  if (isEmpty(s)){
  	 alert("Please enter a desired Password. Minimum 4 characters.\nNo spaces and blanks allowed.");
	 return false; 
  } 
  else{
   	 var matchArray = s.match(rePassword); // is the format ok? 
	 if (matchArray == null) { 
		alert("Password should contain only alphabets, digits\nor only these special characters ~!@#$%^&*()$");
		return false; 
	 }	 	
	 else{ 
	  	 if(s.length<4){		
			alert("Password should be minimum of 4 characters");	
			return false; 
		 }	
	 }			
  }      	 
  return true;
}
	
// function to validate date field
function isValidDate(dateStr) { 
// Checks for the following valid date formats: 
// MM/DD/YY MM/DD/YYYY MM-DD-YY MM-DD-YYYY 
// Also separates date into month, day, and year variables 

var datePat = /^(\d{1,2})(\/|-)(\d{1,2})\2(\d{2}|\d{4})$/; 

// To require a 4 digit year entry, use this line instead: 
// var datePat = /^(\d{1,2})(\/|-)(\d{1,2})\2(\d{4})$/; 

var matchArray = dateStr.match(datePat); // is the format ok? 
if (matchArray == null) { 
	alert("Date is not in a valid format.") 
	return false; 
} 
month = matchArray[1]; // parse date into variables 
day = matchArray[3]; 
year = matchArray[4]; 
if (month < 1 || month > 12) { // check month range 
alert("Month must be between 1 and 12."); 
return false; 
} 
if (day < 1 || day > 31) {
alert("Day must be between 1 and 31."); 
return false; 
} 
if ((month==4 || month==6 || month==9 || month==11) && day==31) {
alert("Month "+month+" doesn't have 31 days!") 
return false 
} 
if (month == 2) { // check for february 29th 
var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0)); 
if (day>29 || (day==29 && !isleap)) { 
alert("February " + year + " doesn't have " + day + " days!"); 
return false; 
} 
} 
return true; // date is valid 
} 
// End date validation -------------------------------------> 

function emailCheck(emailStr) {
	/* The following pattern is used to check if the entered e-mail address
	   fits the user@domain format.  It also is used to separate the username
	   from the domain. */
	var emailPat=/^(.+)@(.+)$/
	/* The following string represents the pattern for matching all special
	   characters.  We don't want to allow special characters in the address. 
	   These characters include ( ) < > @ , ; : \ " . [ ]    */
	var specialChars="\\(\\)<>@,;:\\\\\\\"\\.\\[\\]"
	/* The following string represents the range of characters allowed in a 
	   username or domainname.  It really states which chars aren't allowed. */
	var validChars="\[^\\s" + specialChars + "\]"
	/* The following pattern applies if the "user" is a quoted string (in
	   which case, there are no rules about which characters are allowed
	   and which aren't; anything goes).  E.g. "jiminy cricket"@disney.com
	   is a legal e-mail address. */
	var quotedUser="(\"[^\"]*\")"
	/* The following pattern applies for domains that are IP addresses,
	   rather than symbolic names.  E.g. joe@[123.124.233.4] is a legal
	   e-mail address. NOTE: The square brackets are required. */
	var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/
	/* The following string represents an atom (basically a series of
	   non-special characters.) */
	var atom=validChars + '+'
	/* The following string represents one word in the typical username.
	   For example, in john.doe@somewhere.com, john and doe are words.
	   Basically, a word is either an atom or quoted string. */
	var word="(" + atom + "|" + quotedUser + ")"
	// The following pattern describes the structure of the user
	var userPat=new RegExp("^" + word + "(\\." + word + ")*$")
	/* The following pattern describes the structure of a normal symbolic
	   domain, as opposed to ipDomainPat, shown above. */
	var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$")
	
	
	/* Finally, let's start trying to figure out if the supplied address is
	   valid. */
	
	/* Begin with the coarse pattern to simply break up user@domain into
	   different pieces that are easy to analyze. */
	var matchArray=emailStr.match(emailPat)
	if (matchArray==null) {
	  /* Too many/few @'s or something; basically, this address doesn't
	     even fit the general mould of a valid e-mail address. */
		alert("Email address seems incorrect (check @ and .'s)")
		return false
	}
	var user=matchArray[1]
	var domain=matchArray[2]
	
	// See if "user" is valid 
	if (user.match(userPat)==null) {
	    // user is not valid
	    alert("The username doesn't seem to be valid.")
	    return false
	}
	
	/* if the e-mail address is at an IP address (as opposed to a symbolic
	   host name) make sure the IP address is valid. */
	var IPArray=domain.match(ipDomainPat)
	if (IPArray!=null) {
	    // this is an IP address
		  for (var i=1;i<=4;i++) {
		    if (IPArray[i]>255) {
		        alert("Destination IP address is invalid!")
			return false
		    }
	    }
	    return true
	}
	
	// Domain is symbolic name
	var domainArray=domain.match(domainPat)
	if (domainArray==null) {
		alert("The domain name doesn't seem to be valid.")
	    return false
	}
	
	/* domain name seems valid, but now make sure that it ends in a
	   three-letter word (like com, edu, gov) or a two-letter word,
	   representing country (uk, nl), and that there's a hostname preceding 
	   the domain or country. */
	
	/* Now we need to break up the domain to get a count of how many atoms
	   it consists of. */
	var atomPat=new RegExp(atom,"g")
	var domArr=domain.match(atomPat)
	var len=domArr.length
	if (domArr[domArr.length-1].length<2 || 
	    domArr[domArr.length-1].length>3) {
	   // the address must end in a two letter or three letter word.
	   alert("The address must end in a three-letter domain, or two letter country.")
	   return false
	}
	
	// Make sure there's a host name preceding the domain.
	if (len<2) {
	   var errStr="This E-mail address is missing a hostname!"
	   alert(errStr)
	   return false
	   }
	   // If we've gotten this far, everything's valid!
	return true	   
}
// end o
