<?php
$page_name = 'Your JOHN AMICO Organization - Genealogy';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


function drawlevels($memberid,$lnum) {
    global $conn;

    $query = "SELECT int_customer_id,
			 int_designation_id
		  FROM tbl_member
		  WHERE int_member_id='".$memberid."'";

    $rsmember = mysqli_query($conn,$query);

    list($customerid, $designation) = mysqli_fetch_row($rsmember);

    if(!empty($customerid)) {
        $rscustomer = mysqli_query($conn, "SELECT c.customers_firstname,
                          c.customers_lastname,
                          m.amico_id
                       FROM customers c,
                        tbl_member m
                       WHERE c.customers_id=$customerid
                       AND c.customers_id=m.int_customer_id");
        //echo "SELECT c.customers_firstname, c.customers_lastname, m.amico_id  FROM customers c, tbl_member m WHERE c.customers_id=$customerid AND c.customers_id=m.int_customer_id";

        list($firstname, $lastname, $amico_id) = mysqli_fetch_row($rscustomer);


        if ($customerid != $_SESSION['ses_member_id']):
            echo '<tr> ';
            for ($i = 1; $i <= 5; $i++) {
                if ($i == $designation && $customerid != $_SESSION['ses_member_id']) {
                    echo '<td><font color="blue">' . $lnum . ':</font> ' . $firstname . ' ' . $lastname . ' - ' . $amico_id . '</td>';
                }
                else {
                    echo '<td>&nbsp;</td>';
                }
            }
            echo '</tr>';
        endif;
    }

    $query2 = "SELECT int_member_id FROM tbl_member WHERE int_parent_id='".$memberid."'";
    $rschilds = mysqli_query($conn,$query2);
    $l = $lnum+1; //using this to print out level # in downline
    while (list($childmemberid) = mysqli_fetch_row($rschilds)) {
        drawlevels($childmemberid,$l);
    }
}

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
                    </header>
                    <div class="panel-body ">
                        <div class="row">
                            <section class="panel">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <div class="info" style="color:blue; margin-bottom: 15px;"><i>#: indicates downline level</i></div>
                                        <table class="table table-bordered table-striped">
                                            <tr class="">
                                                <th>I.P.R.</th>
                                                <th>Team Leader</th>
                                                <th>Senior Leader</th>
                                                <th>Master Leader</th>
                                                <th>Director</th>
                                            </tr>
                                            <?php drawlevels($_SESSION['member']['ses_member_id'],0); ?>
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

