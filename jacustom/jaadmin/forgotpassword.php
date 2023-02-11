<?php

$page_title = 'John Amico - Admin Forgot Password';

require_once("../common_files/include/global.inc");
require_once("templates/header.php");

$entered_email = 0;

if(!empty($_POST['submit'])){
    if ( !empty($_POST['email']) ){
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

        if( !$email ) {
            $entered_email = 4;
        } else {
            $entered_email = 1;
            // retrieve password
            $sql = "select str_password, str_email from tbl_admin where str_email = '$email' and bit_active = 1";
            $rs_AdminEmail = mysqli_query($conn,$sql);

            if(mysqli_num_rows($rs_AdminEmail) > 0) {
                list($Password, $Email) = mysqli_fetch_row($rs_AdminEmail);
                if (!$Password == "") {
                    // send mail the submited mail id.
                    $rsSelAdminEmails = mysqli_query($conn, "select * from tbl_admin_email");  // select the pre-set admin email to fill in the from
                    list($AdminEmailID, $AdminEmail) = mysqli_fetch_row($rsSelAdminEmails);

                    $mail_to = $Email;
                    $mail_from = $AdminEmail;
                    $subject = "Admin - Lost Password - John Amico";
                    $sitelink = "<a href='" . base_url() . "'>johnamico.com</a>";

                    $mailbody .= "Your lost password is: " . $Password . "<br><br>Thank you.<br><br>" . $sitelink;

                    $headers .= "From: Admin <$mail_from>\n";
                    $headers .= "X-Mailer: PHP/" . phpversion() . "\n"; // mailer
                    $headers .= "Reply-To:" . $mail_from . "\n";  // Return path for errors
                    $headers .= "Content-Type: text/html; charset=iso-8859-1\n"; // Mime type
                    $mail_stat = mail($mail_to, $subject, $mailbody, $headers);
                }
                else {
                    $entered_email = 2;
                }
            } else {
                $entered_email = 2;
            }
        }
    } else{
        $entered_email = 4;
    }
}

?>

<script language="javascript">
<!--
function Validate(theform) {
	if(theform.Email.value.length == 0){
	  alert("Please enter an email");
	  theform.Email.focus();
	  return false;	
	}
	if(theform.Email.value.length != 0 ){
	var retval = emailCheck(theform.Email.value)
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
	  }
	}
	if (retval == false){
		theform.Email.focus();
  		return false;
	}
	return true;	
}	
//-->
</script>


<!-- start: page -->
<div class="row admin-control">
    <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-12">
        <div class="panel panel-primary">
            <div class="panel-heading">Forgot Password</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-10 col-xs-offset-1">
                        <form name="frmlogin" action="" method="post">

                            <div class="messages">
                                <?php
                                if ($entered_email == 1) {
                                    echo "<b>Your password has been mailed to you.</b>";
                                }
                                elseif ($entered_email == 2) {
                                    echo "<b>We could not find the Email ID you entered!</b>";
                                }
                                elseif ($entered_email == 3) {
                                    echo "<b>Unspecified Error! - Please retry.</b>";
                                }
                                elseif ($entered_email == 4) {
                                    echo "<b>Please enter a valid email address.</b>";
                                }
                                ?>
                            </div>

                            <div class="form-group">
                                <label for="email">Email ID</label>
                                <input type="email" name="email" class="form-control" id="email" placeholder="Email ID" value="<?php echo ( !empty($email) ? $email : '' ); ?>" />
                            </div>
                            <div class="form-group">
                                &nbsp;
                            </div>
                            <div class="form-group submit_wrapper">
                                <div class="pull-right">
                                    <input type="submit" name="submit" class="btn btn-default btn-primary" value="Submit"/>
                                </div>
                                <div class="pull-left">
                                    <input type="button" name="Cancel" value="Cancel" class="warning" onclick="window.location='<?php echo base_admin_url(); ?>/admin_login.php'" >
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php

require_once("templates/footer.php");


