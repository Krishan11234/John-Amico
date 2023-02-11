<?php
/*  Project Name		: Amico
   	Program name		: print_family_tree.php
 	Program function	: creating of the tree 
	Author				: Shehran
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
 	Created Date  		: 5 Aug, 2003
 	Last Modified		: 5 Aug, 2003
-------------------------------------------------------------------- 	*/
require_once("tocParas.php");
require_once("tocTab.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../css/login.css" rel="stylesheet" type="text/css">
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tr>
    <td>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr> 
          <td >
		  	<table width="100%" border="0">
		  		<tr>
					<table  width="100%" border="0">
						<tr>
							<td width="50%">
								 <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>JOHN 
						            AMICO Family Tree</strong></font>
							</td>	
							<td width="50%">
									<a href="#" onclick="javascript:window.print();">Print</a>
							</td>
						</tr>
					</table>
				</tr>
		  		<tr>
					<td colspan="2"><hr></td>	
				</tr>
			</table>
		 </td>
        </tr>
        <tr> 
          <td  valign="top"> 
              <table border="0" cellspacing="0" cellpadding="0">
              	<tr>
					<td>
					<table border="0" cellspacing="0" cellpadding="0">
              			<tr>
							
						<?php
						$mdi=$textSizes[1];
						$sml=$textSizes[2];
						$currentNumArray = explode(".",$_GET['currentNumber']);
						$currentLevel = sizeof($currentNumArray)-1;
						$theHref = "";
						for ($i=0; $i<sizeof($tocTab); $i++) {
							$thisNumber = $tocTab[$i][0];
							$isCurrentNumber = ($thisNumber == $_GET['currentNumber']);
							if ($isCurrentNumber) $theHref=$tocTab[$i][2];
								$thisNumArray = explode(".",$thisNumber);
							
							$thisLevel = sizeof($thisNumArray)-1;
							$toDisplay = TRUE;
							$thisIsExpanded = ($toDisplay && ($thisNumArray[$thisLevel] == $currentNumArray[$thisLevel])) ? 1 : 0;
							if ($_GET['currentIsExpanded']=="1") {
								$toDisplay = $toDisplay && ($thisLevel<=$currentLevel);
								if ($isCurrentNumber) $thisIsExpanded = 0;
							}
							if ($toDisplay) {
								if ($i==0) {
									echo ("\n<td  align='left' colspan=" . ($nCols+1) . "><font size='2' face='Verdana, Arial, Helvetica, sans-serif'><strong>" . $tocTab[$i][1] . "</strong></font> <font style=\"font-family: " . $fontLines . "; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; text-decoration:none; font-style: italic;\">(#: indicates downline level)</font></td></tr>");
									for ($k=0; $k<$nCols; $k++) {
										echo "<td align='left' valign='middle' >&nbsp;</td>";
									}
									echo "<td width=240 align='left' valign='middle'>&nbsp;</td></tr>\n";
								}
								else {
									// &#183; reprsents middle dot -->
									$isLeaf = ($i==sizeof($tocTab)-1) || ($thisLevel >= sizeof(explode(".",$tocTab[$i+1][0]))-1);
									$img = ($isLeaf) ? "<b>&nbsp;&#183;&nbsp;</b>" : (($thisIsExpanded)?"<b>&nbsp;-&nbsp;</b>":"<b>&nbsp;-&nbsp;</b>");
									echo "<tr>";
									for ($k=1; $k<=$thisLevel; $k++) {
										echo "<td align='left' valign='middle'>&nbsp;</td>";
									}
									$lnum = count(explode(".",$thisNumber)); // create levels here:
									echo ("<td align='left' valign='middle'><b>$img</b></td> <td align='left' valign='middle' colspan=" . ($nCols-$thisLevel) . "><font style=\"font-family: " . $fontLines . "; font-weight:bold; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; text-decoration:none\">".$lnum.":</font> <font size='1' face='Verdana, Arial, Helvetica, sans-serif'><strong>" . $tocTab[$i][1] . "</strong></font></a></td></tr>\n");
									
								}
							}
						}
					?>
			  		</td>
				</tr>
			  </table>
            </td>
        </tr>
      </table> </td>
  </tr>
</table>
</body>
</html>
