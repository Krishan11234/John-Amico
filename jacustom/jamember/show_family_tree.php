<?php
$page_name = 'Your JOHN AMICO Organization - Genealogy';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

require_once("tocParas.php");
require_once("tocTab.php");

?>

    <div role="main" class="content-body">
        <header class="page-header">
            <h2><?php echo $page_name; ?></h2>

            <div class="right-wrapper pull-right">
                <ol class="breadcrumbs">
                    <li>
                        <a href="<?php echo base_admin_url(); ?>">
                            <i class="fa fa-home"></i>
                        </a>
                    </li>
                    <li><span><?php echo $page_name; ?></span></li>
                </ol>


                <a class="sidebar-right-toggle"></a>
            </div>
        </header>

        <div class="row ">
            <div class="col-md-12 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        <div class="text-right">
                            <a href="#" onclick='window.open("print_family_tree.php","Print","scrollbars=yes");'>Printer Friendly - Expand All</a>
                        </div>
                    </header>
                    <div class="panel-body ">
                        <div class="row">
                            <section class="panel">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <!--<div class="info" style="color:blue; margin-bottom: 15px;"><i>#: indicates downline level</i></div>-->
                                        <table class="table table-striped">
                                            <tr>
                                                <?php
                                                $mdi=$textSizes[1];
                                                $sml=$textSizes[2];
                                                $currentNumArray = ( !empty($_GET['currentNumber']) ? explode(".",$_GET['currentNumber']) : array() );
                                                $currentLevel = sizeof($currentNumArray)-1;
                                                $theHref = "";
                                                for ($i=0; $i<sizeof($tocTab); $i++) {
                                                    $thisNumber = $tocTab[$i][0];
                                                    if(!empty($_GET['currentNumber'])) {
                                                        $isCurrentNumber = ($thisNumber == $_GET['currentNumber']);
                                                        if ($isCurrentNumber) {
                                                            $theHref = $tocTab[$i][2];
                                                        }
                                                    }
                                                    $thisNumArray = explode(".",$thisNumber);
                                                    $thisLevel = sizeof($thisNumArray)-1;
                                                    $toDisplay = TRUE;
                                                    if ($thisLevel > 0) {
                                                        for ($j=0; $j<$thisLevel; $j++) {
                                                            $toDisplay = ($j>$currentLevel)?FALSE:$toDisplay && ($thisNumArray[$j] == $currentNumArray[$j]);
                                                        }
                                                    }
                                                    $thisIsExpanded = ($toDisplay && ($thisNumArray[$thisLevel] == $currentNumArray[$thisLevel])) ? 1 : 0;
                                                    if ( !empty($_GET['currentIsExpanded']) && $_GET['currentIsExpanded']=="1") {
                                                        $toDisplay = $toDisplay && ($thisLevel<=$currentLevel);
                                                        if ($isCurrentNumber) $thisIsExpanded = 0;
                                                    }

                                                    if ($toDisplay) {
                                                        if ($i==0) {
                                                            echo ("\n<td  align='left' colspan=" . ($nCols+1) . "><a href=\"show_family_tree.php?memberid=".$tocTab[$i][2]."&currentNumber=" . rawurlencode($thisNumber) . "&currentIsExpanded=" . $thisIsExpanded . "\" style=\"font-family: " . $fontTitle . "; font-weight:bold; font-size:" . $textSizes[0] . "em; color: " . $titleColor . "; text-decoration:none\">" . $tocTab[$i][1] . "</a> <font color=blue style=\"font-family: " . $fontLines . "; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; text-decoration:none; font-style: italic;\">(#: indicates downline level)</font></td></tr>");
                                                            for ($k=0; $k<$nCols; $k++) {
                                                                echo "<td align='left' >&nbsp;</td>";
                                                            }
                                                            echo "<td width=240 align='left'>&nbsp;</td></tr>\n";
                                                        }
                                                        else {
                                                            $isLeaf = ($i==sizeof($tocTab)-1) || ($thisLevel >= sizeof(explode(".",$tocTab[$i+1][0]))-1);
                                                            $img = ($isLeaf) ? "leaf" : (($thisIsExpanded)?"minus":"plus");
                                                            echo "<tr>";
                                                            for ($k=1; $k<=$thisLevel; $k++) {
                                                                echo "<td align='left' >&nbsp;</td>";
                                                            }
                                                            //if ($img=="leaf"){
                                                            //echo ("<td align='left' valign=top><img src=\"../images/" . $img . ".gif\" width=13 height=12 border=0></td> <td align='left' colspan=" . ($nCols-$thisLevel) . "><a style=\"font-family: " . $fontLines . ";" . (($thisLevel<=$mLevel)?"font-weight:bold":"") .  "; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; color: " . (($isCurrentNumber)?$currentColor:$normalColor) . "; text-decoration:none\">" . $tocTab[$i][1] . "</a></td></tr>\n");
                                                            //}
                                                            //else{

                                                            $lnum = count(explode(".",$thisNumber)); // create levels here:
                                                            if (trim($img)=='plus') {
                                                                echo ("<td align='left' valign=bottom><a href=\"show_family_tree.php?currentNumber=" . rawurlencode($thisNumber) . "&currentIsExpanded=" . $thisIsExpanded . "\"><img src=\"../images/" . $img . ".gif\" width=13 height=12 border=0 vspace='1'></a></td> <td align='left' valign=top colspan=" . ($nCols-$thisLevel) . "><font color=blue style=\"font-family: " . $fontLines . "; font-weight:bold; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; text-decoration:none\">".$lnum.":</font> <a href=\"show_family_tree.php?memberid=".$tocTab[$i][2]."&currentNumber=" . rawurlencode($thisNumber) . "&currentIsExpanded=" . $thisIsExpanded . "\" style=\"font-family: " . $fontLines . ";" . (($thisLevel<=$mLevel)?"font-weight:bold":"") .  "; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; color: " . (($isCurrentNumber)?$currentColor:$normalColor) . "; text-decoration:none\">" . $tocTab[$i][1]. "</a></td></tr>\n");
                                                            } else {
                                                                if (trim($tocTab[$i][2])!='') {
                                                                    echo ("<td align='left' valign=bottom><a href=\"show_family_tree.php?currentNumber=" . rawurlencode($thisNumber) . "&currentIsExpanded=" . $thisIsExpanded . "\"><img src=\"../images/" . $img . ".gif\" width=13 height=12 border=0 vspace='1'></a></td> <td align='left' valign=top colspan=" . ($nCols-$thisLevel) . "><font color=blue style=\"font-family: " . $fontLines . "; font-weight:bold; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; text-decoration:none\">".$lnum.":</font> <a href=\"show_family_tree.php?memberid=".$tocTab[$i][2]."&currentNumber=" . rawurlencode($thisNumber) . "&currentIsExpanded=" . $thisIsExpanded . "\" style=\"font-family: " . $fontLines . ";" . (($thisLevel<=$mLevel)?"font-weight:bold":"") .  "; font-size:" . (($thisLevel<=$mLevel)?$mdi:$sml) . "em; color: " . (($isCurrentNumber)?$currentColor:$normalColor) . "; text-decoration:none\">" . $tocTab[$i][1]. "</a></td></tr>\n");
                                                                };
                                                            };
                                                            //}

                                                        }
                                                    }
                                                }
                                                ?>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

