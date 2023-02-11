<?php 
set_time_limit(60);

$page_name = 'Salons in Trouble Report';
$page_title = "John Amico - $page_name";

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

if(!empty($member_id)) {
    $a_amico = mysqli_fetch_array(mysqli_query($conn, "select * from tbl_member where int_member_id='$member_id'"));
    $user_amico_id = $a_amico['amico_id'];
}
?>


    <script language=JavaScript>
        collapse_left_sidebar_func(true, true);

        function MM_findObj(n, d) { //v4.01
          var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
            d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
          if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
          for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
          if(!x && d.getElementById) x=d.getElementById(n); return x;
        }

        function MM_showHideLayers() { //v6.0
          var i,p,v,obj,args=MM_showHideLayers.arguments;
          for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
            if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
            obj.visibility=v; }
        }

        function HideStatus()	{
          if (document.readyState=="complete")  {
            MM_showHideLayers('layer_loader','','hide');
          }
        }

        document.onreadystatechange = HideStatus;
    </script>

    <div role="main" class="content-body <?php echo ( $is_popup ? 'no-margin-left' : '' ); ?> ">
        <?php if(!$is_popup): ?>
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
        <?php endif; ?>

        <div class="row ">
            <div class="col-xs-12 centering">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                    </header>
                    <div class="panel-body">

                        <div id="layer_loader" style="POSITION:absolute ; TOP: 200; LEFT: 50%;">
                            <table width="200" bgcolor="#FFFFFF" align="center" style="border:1px solid black">
                                <tr>
                                    <td align="center"><img src="../images/loading.gif"><br><h1>Loading Data...<br>Please wait.</h1></td>
                                </tr>
                            </table>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th class="text-center">ID#</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Total Last Year</th>
                                    <th class="text-center">Total This Year</th>
                                    <th class="text-center">Total Short</th>
                                </tr>

                                <?php if(!empty($user_amico_id)) { ?>
                                    <?php
                                    $query = " SELECT * FROM tbl_member, customers WHERE tbl_member.ec_id = '$user_amico_id' AND tbl_member.int_customer_id=customers.customers_id ORDER BY sit_short ASC LIMIT 100 ";

                                    $query=mysqli_query($conn,$query);

                                    if( mysqli_num_rows($query) > 0 ) {
                                        while ($f = mysqli_fetch_array($query)) {

                                            echo '<tr>';
                                            echo '<td nowrap>' . $f['amico_id'] . '</td>';
                                            echo '<td nowrap>' . stripslashes($f['customers_firstname']) . ' ' . stripslashes($f['customers_lastname']) . '</td>';
                                            echo '<td nowrap>' . $f['sit_tly'] . '</td>';
                                            echo '<td nowrap>' . $f['sit_tty'] . '</td>';
                                            echo '<td nowrap>' . $f['sit_short'] . '</td>';
                                            echo '</tr>';

                                        }
                                    } else {
                                        echo '<tr><td colspan="5">No Data Found!</td>';
                                    }
                                    ?>
                                <?php } else { ?>
                                    <tr><td colspan="5">No Data Found!</td>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

<?php
require_once("templates/footer.php");