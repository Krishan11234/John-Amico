<?php
$page_name = 'Notes';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");



$member_type_name = 'Note';
$member_type_name_plural = 'Notes';
$self_page = 'notes.php';
$page_url = base_member_url() . "/$self_page?";
$action_page = 'notes.php';
$action_page_url = base_member_url() . "/$self_page?1=1";
//$export_url = base_admin_url() . '/members_export.php';


$is_edit = $is_add = false;
$success_message = $error_message = $member_search_result = $conditions = array();

//debug(false, true, $_POST);

$member_id = $_SESSION['member']['ses_member_id'];

$mesg = ( !empty($_GET['msg']) ? $_GET['msg'] : '' );

if( !empty($member_id) ) {

    if( !empty($_POST['goto']) && ($_POST['goto'] == 'add_entry') && !empty($_POST['note_memberid']) ) {
        $comment = filter_var($_POST['clientcomments'], FILTER_SANITIZE_STRING);
        $note = filter_var($_POST['notes'], FILTER_SANITIZE_STRING);

        $contact_member_id = filter_var($_POST['note_memberid'], FILTER_SANITIZE_NUMBER_INT);

        $contact_member_id_sql = "SELECT int_annotate_note_id FROM tbl_annotate_note WHERE int_member_list='$contact_member_id' AND int_member_id='$member_id' LIMIT 1";
        $contact_member_id_query = mysqli_query($conn, $contact_member_id_sql);

        if( mysqli_num_rows($contact_member_id_query) > 0 ) {

            while($res = mysqli_fetch_object($contact_member_id_query)) {
                $noteid = $res->int_annotate_note_id;
            }

            if( update_note($noteid, $comment, $note) ) {
                $mesg = "$member_type_name entry added successfully.";
                $is_edit = $is_add = false;
            }

        } else {
            $table = "tbl_annotate_note";
            $in_fieldlist="int_member_id,int_member_list,bit_credit_card,int_status_id,str_legal_issue1,str_legal_issue2,dtt_legal,str_client_comments,str_notes,bit_active";
            $in_values="'{$member_id}','{$contact_member_id}',{$_POST['creditcard']},{$_POST['status']},'{$_POST['legalissue1']}','{$_POST['legalissue2']}',now(),'{$comment}','{$note}',1";
            $result=insert_fields($conn, $table, $in_fieldlist, $in_values); // function call inserting values

            $mesg = "$member_type_name entry added successfully.";

            $is_edit = $is_add = false;
        }

    }
    elseif( !empty($_POST['goto']) && ($_POST['goto'] == 'add') ) {
        $is_add = true;
    }
    elseif( !empty($_POST['goto']) && ($_POST['goto'] == 'search') && !empty($_POST['search_by']) ) {
        $is_add = true;

        $sql_contact="select int_member_contact_list_id,str_member_contact_list from tbl_member_contact_list where int_member_id='$member_id'";
        $rscontact=mysqli_query($conn,$sql_contact);
        list($listid,$listcontact)=mysqli_fetch_row($rscontact);
        $listcontact = substr($listcontact,0,(strlen($listcontact)-1));


        $sql = "select m.int_member_id,c.customers_firstname, c.customers_lastname  from tbl_member m
                left outer join customers c on m.int_customer_id=c.customers_id
        ";

        //$conditions[] = " m.int_parent_id = '$member_id' ";
        $conditions[] = " m.int_member_id IN($listcontact) ";
        $conditions[] = " m.bit_active = 1 ";

        switch($_POST['search_by']) {
            case 'name':
                if( !empty($_POST['searchname']) && !empty($_POST['searchorder']) ) {
                    $searchname = filter_var($_POST['searchname'], FILTER_SANITIZE_STRING);
                    $searchorder = $_POST['searchorder'];

                    if( $searchorder == 1 ) {
                        $conditions[] = " c.customers_firstname LIKE '$searchname%' ";
                    }
                    elseif( $searchorder == 2 ) {
                        $conditions[] = " c.customers_lastname LIKE '$searchname%' ";
                    }
                }
                break;

            case 'id':
                if( !empty($_POST['searchid']) ) {
                    $searchid = filter_var($_POST['searchid'], FILTER_SANITIZE_STRING); // Amico ID
                    $conditions[] = " m.amico_id LIKE '%$searchid%' ";
                }
                break;
        }

        if(!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        //debug(false, true, $sql);

        $rsmember = mysqli_query($conn,$sql);
    }
    elseif( !empty($_POST['goto']) && ($_POST['goto'] == 'update_entry') && !empty($_POST['noteid']) && is_numeric($_POST['noteid'])  ) {
        $noteid = filter_var($_POST['noteid'], FILTER_SANITIZE_NUMBER_INT);
        $comment = filter_var($_POST['clientcomments'], FILTER_SANITIZE_STRING);
        $note = filter_var($_POST['notes'], FILTER_SANITIZE_STRING);

        if( update_note($noteid, $comment, $note) ) {
            $mesg = "$member_type_name entry updated successfully.";
            $is_edit = $is_add = false;
        }
    }
    elseif( !empty($_GET['noteid']) && is_numeric($_GET['noteid']) ) {
        $noteid = filter_var($_GET['noteid'], FILTER_SANITIZE_NUMBER_INT);

        if(!empty($noteid)) {
            $contact_member_id_query = mysqli_query($conn, "SELECT int_member_list,str_client_comments,str_notes FROM tbl_annotate_note WHERE int_annotate_note_id='$noteid'");
            list($contact_member_id, $clientcomment, $notes) = mysqli_fetch_row($contact_member_id_query);

            $rsmember = mysqli_query($conn, "select m.amico_id, c.customers_firstname,c.customers_lastname,c.customers_telephone,c.customers_email_address,m.tme_best_time,a.entry_street_address,entry_city,entry_postcode,entry_zone_id,entry_country_id from tbl_member m left outer join customers c on m.int_customer_id=c.customers_id left outer join address_book a  on c.customers_id=a.customers_id where m.int_member_id='{$contact_member_id}' and a.address_book_id=1");
            list($contact_amico_id, $firstname, $lastname, $phone, $email, $besttime, $streetaddress, $city, $postcode, $zoneid, $countryid) = mysqli_fetch_row($rsmember);

            $rsstate = mysqli_query($conn, "select zone_name from zones where zone_id=$zoneid");
            list($state) = mysqli_fetch_row($rsstate);


            $is_edit = true;
        }
    }
}

if( !empty($_GET['add']) && ($_GET['add'] == 1) && !empty($member_id) ) {
    $is_add = true;
}

if($is_add && !empty($_GET['memberid']) && is_numeric($_GET['memberid']) && !empty($member_id) ) {
    $contact_member_id = filter_var($_GET['memberid'], FILTER_SANITIZE_NUMBER_INT);

    $contact_member_id_sql = "SELECT str_client_comments,str_notes FROM tbl_annotate_note WHERE int_member_list='$contact_member_id' AND int_member_id='$member_id'";
    $contact_member_id_query = mysqli_query($conn, $contact_member_id_sql);
    list($clientcomment, $notes) = mysqli_fetch_row($contact_member_id_query);

    $rsmember = mysqli_query($conn, "select m.amico_id, c.customers_firstname,c.customers_lastname,c.customers_telephone,c.customers_email_address,m.tme_best_time,a.entry_street_address,entry_city,entry_postcode,entry_zone_id,entry_country_id from tbl_member m left outer join customers c on m.int_customer_id=c.customers_id left outer join address_book a  on c.customers_id=a.customers_id where m.int_member_id='{$contact_member_id}' and a.address_book_id=1");
    list($contact_amico_id, $firstname, $lastname, $phone, $email, $besttime, $streetaddress, $city, $postcode, $zoneid, $countryid) = mysqli_fetch_row($rsmember);

    $rsstate = mysqli_query($conn, "select zone_name from zones where zone_id=$zoneid");
    list($state) = mysqli_fetch_row($rsstate);


    $is_edit = true;
}

if(!$is_edit && !$is_add && !empty($member_id)) {

    $limit = 50;
    $page = ((!empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1);

    $limit_start = ($page * $limit) - $limit;
    $limit_end = ($page * $limit);


    $conditions = $sortby = array();

    //debug(true, false, $status_filter, (!in_array((string)$_REQUEST['params']['status_filter'], array('1','0'), true)), $_POST);

    $sql = "SELECT an.int_annotate_note_id, an.dtt_legal, an.int_member_list AS contact_member_id, m.int_customer_id AS member_list_customer_id, c.customers_firstname,c.customers_lastname, CONCAT(c.customers_firstname,' ',c.customers_lastname) as fullname_contact
            FROM tbl_annotate_note an
            INNER JOIN tbl_member m ON m.int_member_id = an.int_member_list
            INNER JOIN customers c ON m.int_customer_id = c.customers_id
    ";


    $sortby = '';
    $sortby = "order by an.dtt_legal desc";

    //debug(true, true, $designations, ( in_array($designations, array('', NULL, null, false)) ) );

    $conditions[] = "an.int_member_id='$member_id'";
    $conditions[] = "an.bit_active='1'";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " $sortby ";


    $field_details = array(
        'dtt_legal' => 'Date',
        'fullname_contact' => array(
            'name' => 'Contact Person',
            'link' => "$page_url&noteid=ID_FIELD_VALUE",
            'id_field' => 'int_annotate_note_id',
            'text_to_display' => 'TEXT_FIELD_VALUE',
            'text_field' => 'fullname_contact',
        ),
    );

    $id_field = 'int_annotate_note_id';

    $action_page__id_handler = 'noteid';


    //$query_pag_data = " $condition LIMIT $start, $per_page";
    $data_num_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

    mysqli_store_result($conn);
    $numrows = mysqli_num_rows($data_num_query);

    //echo $sql;

    $sql .= " LIMIT $limit OFFSET $limit_start ";
    //echo $sql;
    $data_query = mysqli_query($conn, $sql) or die('MySql Error' . mysqli_error($conn));

}



function update_note($noteid, $comment, $note) {
    global $conn, $member_id, $member_type_name;

    $return = false;

    if (!empty($noteid)) {
        $rsnotes = mysqli_query($conn, "select int_member_id from tbl_annotate_note where int_annotate_note_id={$noteid} ");

        if (mysqli_num_rows($rsnotes) > 0) {

            $table = "tbl_annotate_note";
            $fieldlist = "str_client_comments='{$comment}',str_notes='{$note}'";
            $condition = " where int_annotate_note_id={$noteid} ";
            $result = update_rows($conn, $table, $fieldlist, $condition);

            $return = true;
        }
    }

    return $return;
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
            <section class="panel">
                <div class="col-md-8 col-xs-12 centering">
                    <?php if(!$is_edit && !$is_add): ?>
                        <!-- Buttons -->
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 header_buttons_wrapper text-center centering">
                                    <div class="col-xs- add_button_wrapper centering">
                                        <form action="<?php echo $self_page; ?>" method="post">
                                            <input type="hidden" name="page" value="<?php echo $page; ?>">
                                            <input type="hidden" name="goto" value="add">
                                            <input type="submit" name="add" value="Add New <?php echo $member_type_name; ?>" class="command">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Buttons -->
                    <?php else: ?>
                        <!-- Add Form -->
                        <?php if($is_add && !$is_edit): ?>
                            <div class="col-md-10 col-xs-12 centering">
                                <section class="panel">
                                    <header class="panel-heading">
                                        <h2 class="panel-title text-center">Search Member</h2>
                                    </header>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <form name="theform" class="form-bordered note_add" action="<?php echo $self_page; ?>" method="post">
                                                <div class="row">
                                                    <label class="col-md-4 control-label" for="searchname">Search Member by Name</label>
                                                    <div class="col-md-6">
                                                        <div class="row">
                                                            <div class="col-xs-12">
                                                                <input type="text" required name="searchname" class="form-control" id="searchname" value="<?php echo ( !empty($searchname) ? $searchname : '' ); ?>" />
                                                            </div>
                                                            <div class="col-md-12 form-inline">
                                                                <div class="row">
                                                                    <div class="radio col-xs-6">
                                                                        <input type="radio" id="searchorder_first" name="searchorder" value="1" <?php echo ( !empty($searchorder) && ($searchorder == 1) ? 'checked' : '' ); ?><?php echo ( empty($searchorder)? 'checked' : '' ); ?> required>
                                                                        <label for="searchorder_first">First Name</label>
                                                                    </div>
                                                                    <div class="radio col-xs-6">
                                                                        <input type="radio" id="searchorder_last" name="searchorder" value="2" <?php echo ( !empty($searchorder) && ($searchorder == 2) ? 'checked' : '' ); ?> required>
                                                                        <label for="searchorder_last">Last Name</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                        <input type="hidden" name="goto" value="search">
                                                        <button type="Submit" name="search_by" value="name" class="command  btn btn-default btn-success">Search</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <p class="form-control-static text-center font-size__20">OR</p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <form name="theform" class="form-bordered note_add" action="<?php echo $self_page; ?>" method="post">
                                                <div class="row">
                                                    <label class="col-md-4 control-label" for="searchid">Search Member by ID</label>
                                                    <div class="col-md-6">
                                                        <input type="text" required class="form-control" name="searchid" id="searchid" value="<?php echo ( !empty($searchid) ? $searchid : '' ); ?>" >
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                                                        <input type="hidden" name="goto" value="search">
                                                        <button type="Submit" name="search_by" value="id" class="command  btn btn-default btn-success">Search</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <footer class="panel-footer">
                                        <div class="row">
                                            <div class="col-sm-9 centering text-center">
                                                <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $page_url.'&page='.$page; ?>';">Cancel</button>
                                            </div>
                                        </div>
                                    </footer>
                                </section>
                                <?php if( !empty($rsmember) ) : ?>
                                    <section class="panel">
                                        <header class="panel-heading">
                                            <h2 class="panel-title text-center">Matching Records</h2>
                                        </header>
                                        <div class="panel-body">
                                            <div class="col-xs-12 centering form-bordered found_members">
                                                <?php
                                                $rows = mysqli_num_rows($rsmember);

                                                if( $rows > 0 ) :
                                                    $i = $item = 0;

                                                    while(list($found_memberid,$firstname,$lastname)=mysqli_fetch_row($rsmember)) :

                                                        if($i == 0) { echo "<div class=\"row form-group\">"; }
                                                    ?>

                                                        <div class="col-md-4 col-sm-6 col-xs-12 found_member text-capitalize"><a href="<?php echo "$self_page?add=1&memberid=$found_memberid"; ?>"><?php echo "$firstname $lastname"; ?></a></div>

                                                        <?php
                                                        $i++; $item++;

                                                        if($i == 3) {
                                                            echo "</div>";
                                                            $i = 0;
                                                        }
                                                        ?>

                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <div class="row form-group">
                                                        <div class="col-md-12 found_member alert-danger text-capitalize">No Member Found</div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </section>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <!-- /Add Form -->

                        <!-- Edit Form -->
                        <?php if($is_edit): ?>
                            <form name="theform" class="form-bordered note_edit" action="<?php echo $self_page; ?>" method="post">
                                <header class="panel-heading">
                                    <h2 class="panel-title text-center"><?php echo ( !empty($is_add) ? 'Add New' : 'Edit' ); ?> <?php echo $member_type_name; ?></h2>
                                </header>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">First Name</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($firstname) ? $firstname : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Last Name</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($lastname) ? $lastname : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Member ID</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($contact_amico_id) ? $contact_amico_id : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Day Phone</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($phone) ? $phone : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Email</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($email) ? $email : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Street Address</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($streetaddress) ? $streetaddress : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">City</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($city) ? $city : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">State</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($state) ? $state : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Zip Code</label>
                                        <div class="col-md-8"><p class="form-control-static"><?php echo ( !empty($postcode) ? $postcode : '' ); ?></p></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label" for="clientcomments">Client Comments</label>
                                        <div class="col-md-8">
                                            <textarea name="clientcomments" id="clientcomments" class="form-control" rows="5"><?php echo ( !empty($clientcomment) ? $clientcomment : '' ); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label" for="notes">Notes: (You have to hit the "save"button!)</label>
                                        <div class="col-md-8">
                                            <textarea name="notes" id="notes" class="form-control" rows="5"><?php echo ( !empty($notes) ? $notes : '' ); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <footer class="panel-footer">
                                    <div class="row">
                                        <div class="col-sm-9 centering text-center">
                                            <input type="hidden" name="creditcard" value="1">
                                            <input type="hidden" name="status" value="1">
                                            <?php if( $is_add && !empty($contact_member_id)): ?><input type="hidden" name="note_memberid" value="<?php echo $contact_member_id; ?>" /><?php endif; ?>
                                            <input type="hidden" name="noteid" value="<?php echo ( ( !empty($is_edit) && !empty($noteid) ) ? $noteid : '' );?>">
                                            <input type="hidden" name="params[page]" value="<?php echo $page; ?>">
                                            <input type="hidden" name="page" value="<?php echo $page; ?>">
                                            <?php echo ( !empty($is_add) ? '<input type="hidden" name="goto" value="add_entry">' : '<input type ="hidden" name="goto" value="update_entry">' ); ?>
                                            <button type="Submit" name="<?php echo ( !empty($is_add) ? 'add' : 'update'); ?>" value="" class="command  btn btn-default btn-success">Save</button>
                                            <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $page_url.'&page='.$page; ?>';">Cancel</button>
                                        </div>
                                    </div>
                                </footer>
                            </form>
                        <?php endif; ?>
                        <!-- /Edit Form -->

                    <?php endif; ?>
                </div>
                <div class="clearfix"></div>
            </section>
            <?php if(!$is_edit && !$is_add): ?>
                <section class="panel">
                    <div class="col-sm-8 col-xs-12 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">List of <?php echo $member_type_name_plural; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12 filter_wrapper ">
                                    <?php if(!empty($mesg)): ?>
                                        <div class="message  pb-lg pt-lg mb-lg mt-lg">
                                            <div class="alert alert-success">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                <?php echo $mesg; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php require_once("../$admin_path/display_members_data.php"); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </section>
            <?php endif; ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
