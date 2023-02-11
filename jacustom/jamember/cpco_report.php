<?php
set_time_limit(60);

$page_name = 'CPCO Report';
$page_title = "John Amico - $page_name";

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

if(!empty($member_id)) {
    $a_amico = mysqli_fetch_array(mysqli_query($conn,"select amico_id from tbl_member where int_member_id='$member_id'"));
    $user_amico_id = $a_amico['amico_id'];
}
?>


<script language="JavaScript">
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
                                <th class="text-center">Month</th>
                                <th class="text-center">ID#</th>
                                <th class="text-center">Population</th>
                                <th class="text-center">Chapter membership</th>
                                <th class="text-center">Active Membership</th>
                                <th class="text-center">Penetration percentage</th>
                                <th class="text-center">Active Chapter percentage</th>
                                <th class="text-center">Market Share Percentage</th>
                            </tr>

                            <?php
                            if(!empty($user_amico_id)) {
                                $i = 0;
                                while ($i < 100) {
                                    if (date("Y-m", (time() - $i * 86400 * 31)) > "20010-12") {

                                        $query = " SELECT amico_id, growth  FROM tbl_member WHERE tbl_member.mtype = 'c' AND amico_id = '$user_amico_id'";
                                        $query = mysqli_query($conn, $query);

                                        while ($f = mysqli_fetch_array($query)) {
                                            ?>

                                            <tr>
                                                <td class=""><?php echo date("F, Y", (time() - $i * 86400 * 31)); ?></td>
                                                <td class=""><?php echo $f['amico_id']; ?></td>
                                                <td class=""><?php echo $population = $f['growth']; ?></td>
                                                <td class="">
                                                    <?php
                                                    $res = mysqli_query($conn, "select amico_id from tbl_member where chapter_id='{$f['amico_id']}'");
                                                    echo $cm = mysqli_num_rows($res);
                                                    ?>
                                                </td>
                                                <td class="">
                                                    <?php
                                                    $res = " SELECT bw_invoice_line_items.FKEntity FROM bw_invoices, bw_invoice_line_items, tbl_member
                                                    WHERE tbl_member.chapter_id = '{$f['amico_id']}' AND bw_invoices.ID=tbl_member.amico_id AND bw_invoices.SKOEInvoice=bw_invoice_line_items.FKEntity AND bw_invoices.OrderDate>='" . date("Y-m-d H:i:s", time() - ($i * 86400 * 31) - 7776000) . "' GROUP BY tbl_member.amico_id";

                                                    $res = mysqli_query($conn, $res);
                                                    echo $am = mysqli_num_rows($res);
                                                    ?>
                                                </td>
                                                <td class="">
                                                    <?php if (intval($population) == 0) {
                                                        $pp = 0;
                                                    }
                                                    else {
                                                        $pp = ($cm / $population) * 100;
                                                    } ?>
                                                    <?php echo number_format($pp, 2); ?>
                                                    %
                                                </td>
                                                <td class="">
                                                    <?php if ($cm == 0) {
                                                        $acp = 0;
                                                    }
                                                    else {
                                                        $acp = ($am / $cm) * 100;
                                                    } ?>
                                                    <?php echo number_format($acp, 2); ?>
                                                    %
                                                </td>
                                                <td class="">
                                                    <?php if (intval($population) == 0) {
                                                        $msp = 0;
                                                    }
                                                    else {
                                                        $msp = ($am / $population) * 100;
                                                    } ?>
                                                    <?php echo number_format($msp, 2); ?>
                                                    %
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    $i++;
                                }
                            } else {
                                echo '<tr><td class="text-center" colspan="100%">No Data Found!</td>';
                            }

                            ?>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php
require_once("templates/footer.php");

