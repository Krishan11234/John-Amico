<?php
$page_name = 'Next Level Information';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


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
            <div class="col-md-6 col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">JOHN AMICO <?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body ">
                        <div class="row">
                            <div class="next_level text-center">
                                <h4>
                                <?php

                                $memberid=$_SESSION['ses_member_id'];
                                $query="select int_designation_id from tbl_member where int_member_id='".$memberid."'";
                                $rs=mysqli_query($conn,$query);
                                list($designation)= mysqli_fetch_row($rs);
                                if ($designation<=5){
                                    $desig=$designation;
                                    if($desig==1){
                                        echo'<img src="../images/ipr.gif">';
                                        $query="select int_member_id from tbl_member where int_parent_id=".$memberid." and int_designation_id>='".$designation."'";
                                        $rs=mysqli_query($conn,$query);
                                        $no_rows = mysqli_num_rows($rs);
                                        $child_needed=5-$no_rows;
                                        echo'<br> needs '.$child_needed.' active I.P.R.s to become Team Leader';
                                    }
                                    elseif($desig==2){
                                        echo'<img src="../images/teamleader.gif">';
                                        $query="select int_member_id from tbl_member where int_parent_id=".$memberid." and int_designation_id>='".$designation."'";
                                        $rs=mysqli_query($conn,$query);
                                        $no_rows = mysqli_num_rows($rs);
                                        $child_needed=5-$no_rows;
                                        echo'<br> needs '.$child_needed.' I.P.R.s ';
                                    }
                                    elseif($desig==3){
                                        echo'<img src="../images/seniorleader.gif">';
                                        $rs=mysqli_query($conn,"select int_member_id from tbl_member where int_parent_id=".$memberid." and int_designation_id>=".$designation);
                                        $no_rows = mysqli_num_rows($rs);
                                        $child_needed=3-$no_rows;
                                        echo'<br><br> You Need <b><font color=red>'.$child_needed.'</font></b> Team Leaders To Get To the Next Level';
                                    }
                                    elseif($desig==4){
                                        echo'<img src="../images/masterleader.gif">';
                                        $rs=mysqli_query($conn,"select int_member_id from tbl_member where int_parent_id=".$memberid." and int_designation_id>=".$designation);
                                        $no_rows = mysqli_num_rows($rs);
                                        $child_needed=3-$no_rows;
                                        echo'<br> needs '.$child_needed.' Senior Leaders ';

                                    }
                                    elseif($desig==5){
                                        echo'<img src="../images/director.gif">';
                                        $rs=mysqli_query($conn,"select int_member_id from tbl_member where int_parent_id=".$memberid." and int_designation_id>=".$designation);
                                        $no_rows = mysqli_num_rows($rs);
                                        $child_needed=3-$no_rows;
                                        echo'<br> needs '.$child_needed.' Master Leaders ';

                                    }
                                    else{
                                        echo'<br> You are in the 5th Level - Director.';
                                    }
                                }

                                ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");

