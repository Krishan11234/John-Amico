<?php
/*  Project Name		: Amico
   	Program name		: tocParas.php
 	Program function	: parameter for the tree
	Author				: Shehran
	Developed by	    : Colbridge Web Logics - www.colbridge.com 
 	Created Date  		: 12 Jul, 2003
 	Last Modified		: 12 Jul, 2003
-------------------------------------------------------------------- 	*/

/* These are the parameters to define the appearance of the ToC. */
$showNumbers = true; 		// display the ordering strings: yes=true | no=false
$backColor = "#999999";		// background color of the ToC 
$normalColor = "#5F5F5F";	// text color of the ToC headlines
$currentColor = "#5F5F5F";	// text color of the actual line just clicked on
$titleColor = "#5F5F5F";		// text color of the title "Table of Contents"
$mLevel = 1.2;					// number of levels minus 1 the headlines of which are presentet with large and bold fonts
$textSizes = array(1.2, 0.85, 0.75, 0.9, 0.85);			// font-size factors for: [0] the title "Table of Contents", [1] larger and bold fonts [2] smaller fonts if MS Internet Explorer [3] larger and bold fonts [4] smaller fonts if Netscape Navigator.
$fontTitle = "Verdana, Arial, Helvetica, sans-serif"; // font-family of the title "Table of Contents"
$fontLines = "Verdana, Arial, Helvetica, sans-serif"; // font-family of the headlines