<?php
$page_name = 'Send Newsletters';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Newsletter';
$member_type_name_plural = 'Newsletters';
$self_page = 'send_newsletter.php';
$page_url = base_admin_url() . "/$self_page?1=1";
$action_page = 'send_newsletter.php';
$action_page_url = base_admin_url() . "/$action_page?1=1";
$parent_page = 'newsletter_new.php';
$parent_page_url = base_admin_url() . "/$parent_page?1=1";


$newsletters_selected = $newsletter_mail_content = $newsletter_preview_content = $newsletters_selected_position = $error_messages = $messages = array();
$archive_selected = '';

if( !empty($_POST['goto']) ) {
    switch($_POST['goto']) {
        case 'preview' :
            if( !empty($_POST['newsletters']) && is_array($_POST['newsletters']) ) {
                foreach($_POST['newsletters'] as $key => $newsletter) {
                    if(is_numeric($newsletter)) {
                        $newsletters_selected[ ($key+1) ] = $newsletter;
                        $newsletters_selected_position[$newsletter] = ($key+1);
                    }
                }

                if( !empty($newsletters_selected) ) {
                    $_SESSION['newsletter_selected'] = $newsletters_selected;
                    $_SESSION['newsletter_selected_position'] = $newsletters_selected_position;
                }

                //debug(false, true, $_SESSION);

                if(!empty($_POST['archive']) && is_numeric($_POST['archive'])) {
                    $archive_selected = $_POST['archive'];
                }
            }

        break;
    }
}

//debug(false, true, $_POST);
if( !empty($_POST['submit_type']) ) {
    switch($_POST['submit_type']) {
        case 'reorder' :
            debug(false, true, $_POST);
            list($newsletters_selected,$newsletters_selected_position) = reposition_handler();

        break;
        case 'send_test' :
        case 'send_mails_members' :

            $newsletters_selected = $_SESSION['newsletter_selected'];
            $newsletters_selected_position = $_SESSION['newsletter_selected_position'];

            if( !empty($_POST['test_email']) ) {
                $test_email = trim($_POST['test_email']);
                if( !filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL) ) {
                    $error_messages['send_email_modal']['test_email'] = 'Please enter a valid test email address';
                }
                $sender_email = trim($_POST['sender_email']);
                if( !filter_var($_POST['sender_email'], FILTER_VALIDATE_EMAIL) ) {
                    $error_messages['send_email_modal']['sender_email'] = 'Please enter a valid sender email address';
                }

                $sender_name = filter_var($_POST['sender_name'], FILTER_SANITIZE_STRING);
                if( empty($sender_name) ) {
                    $error_messages['send_email_modal']['sender_name'] = 'Please enter email sender name';
                }
                $email_subject = filter_var($_POST['email_subject'], FILTER_SANITIZE_STRING);
                if( empty($email_subject) ) {
                    $error_messages['send_email_modal']['email_subject'] = 'Please enter email subject';
                }

                if(empty($error_messages)) {

                    $email_header = mysqli_real_escape_string($conn, $_POST['email_header']);
                    $email_footer = mysqli_real_escape_string($conn, $_POST['email_footer']);


                    $email_body = newsletter_mail_body($newsletters_selected, $email_header, $email_footer);

                    if( $_POST['submit_type'] == 'send_test' ) {
                        $success = send_email($test_email, $email_subject, $email_body, array(), $sender_name, $sender_email);

                        if($success) {
                            $messages['send_email_modal'][] = 'Test Email successfully sent to: <strong>'.$test_email. '</strong>';
                            $open_modal = true;
                        }
                    }
                    elseif( $_POST['submit_type'] == 'send_mails_members' ) {

                        // Insert Article Items to the archive table for later use;
                        mysqli_query($conn,"insert into newsletters_mails set date_created=now()");
                        $newsletter_archive_table_id = mysqli_insert_id($conn);

                        foreach ($newsletters_selected as $key => $value) {
                            mysqli_query($conn,"insert into newsletters_articles set newsletter_id='$newsletter_archive_table_id', article_id='$value', `position`='{$newsletters_selected_position[$value]}' ");
                        }

                        $_SESSION['sess_email_subj']   = $email_subject;
                        $_SESSION['sess_sender_name']  = $sender_name;
                        $_SESSION['sess_sender_email'] = $sender_email;
                        $_SESSION['sess_email_header'] = $email_header;
                        $_SESSION['sess_email_footer'] = $email_footer;
                        $_SESSION['sess_email_body'] = $email_body;

                        $newsletter_loop_url = base_admin_url()."/send_newsletter_loop.php?newsletter_id=$newsletter_archive_table_id";

                        echo "<script>window.location = '$newsletter_loop_url';</script>";
                        exit();
                    }

                }

            } else {
                $error_messages['send_email_modal']['test_email'] = 'Please enter a valid test email address';
            }
        break;

    }
}

function reposition_handler() {
    $newsletters_selected = $newsletters_selected_position =  array();

    if( ( !empty($_POST['reorders']) && is_array($_POST['reorders']) ) ) {
        foreach($_POST['reorders'] as $newsletter_id => $position) {
            if(is_numeric($position) && is_numeric($newsletter_id) ) {
                if( !empty($newsletters_selected[$position]) ) {
                    $newsletters_selected[ ($position.'_'.$newsletter_id)] = $newsletter_id;
                } else {
                    $newsletters_selected[$position] = $newsletter_id;
                }

                $newsletters_selected_position[$newsletter_id] = $position;
            }
        }

        if(!empty($newsletters_selected)) {
            ksort($newsletters_selected);
            $_SESSION['newsletter_selected'] = $newsletters_selected;
            $_SESSION['newsletter_selected_position'] = $newsletters_selected_position;
        }
    }

    return array($newsletters_selected,$newsletters_selected_position) ;
}
function newsletter_mail_content_load($article_id){
    global $_SERVER, $newsletter_id, $conn;

    $sn = base_url();

    $row = mysqli_fetch_array(mysqli_query($conn,"select * from tbl_newsletter_new where id='$article_id'"));
    $content = '
<table class="table">
  <tr>
	<td valign="top">
	<a href="'.$sn.'/newsletter_ja_details.php?newsletter_id='.$newsletter_id.'&article_id='.$article_id.'&page=details" target="_blank"><img src="'.$sn.'/images/'.$row['img'].'" alt="'.$row['title'].'" border="0" width="120"></a>';

    if (trim($row['img1'])!='') {$content.='<br/><br/><a href="'.$sn.'/newsletter_ja_details.php?newsletter_id='.$newsletter_id.'&article_id='.$article_id.'&page=details" target="_blank"><img src="'.$sn.'/images/'.$row['img1'].'" alt="'.$row['title'].'" border="0" width="120"></a>';};
    if (trim($row['img2'])!='') {$content.='<br/><br/><a href="'.$sn.'/newsletter_ja_details.php?newsletter_id='.$newsletter_id.'&article_id='.$article_id.'&page=details" target="_blank"><img src="'.$sn.'/images/'.$row['img2'].'" alt="'.$row['title'].'" border="0" width="120"></a>';};
    if (trim($row['img3'])!='') {$content.='<br/><br/><a href="'.$sn.'/newsletter_ja_details.php?newsletter_id='.$newsletter_id.'&article_id='.$article_id.'&page=details" target="_blank"><img src="'.$sn.'/images/'.$row['img3'].'" alt="'.$row['title'].'" border="0" width="120"></a>';};
    if (trim($row['img4'])!='') {$content.='<br/><br/><a href="'.$sn.'/newsletter_ja_details.php?newsletter_id='.$newsletter_id.'&article_id='.$article_id.'&page=details" target="_blank"><img src="'.$sn.'/images/'.$row['img4'].'" alt="'.$row['title'].'" border="0" width="120"></a>';};

    $content.='</td>
	<td valign="top">
		<a href="'.$sn.'/newsletter_ja_details.php?newsletter_id='.$newsletter_id.'&article_id='.$article_id.'&page=details" target="_blank"><b>'.$row['title'].'</b></a><br>
		'.$row['short_descr'].'
	</td>
  </tr>
</table>
    ';

    return $content;
}
function newsletter_mail_body($newsletters_selected, $email_header='', $email_footer='') {
    $sn = base_url();

    $content = '';

    if(!empty($newsletters_selected)) {
        $content .= '
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="5575AC">
            <tr>
                <td bgcolor="5575AC"><a href="' . $sn . '" target="_blank"><img src="' . $sn . '/images/newsletter_logo.jpg" width="422" height="132" border="0"></a></td>
                <td bgcolor="5575AC" width="10%" valign="top">&nbsp;</td>
                <td bgcolor="5575AC" width="49%" valign="middle"><p><img src="' . $sn . '/images/weekly_tag.jpg"></p>
            </td>
            </tr>
            <tr><td colspan=3 align=right><b>' . date("m/d/Y") . '</b>&nbsp;&nbsp;</td></tr>
        </table>

        <font face=Arial size=2>' . nl2br($email_header) . '</font><br>
        <table border="0" cellpadding="2" cellspacing="0">
    ';
        $i = 0;
        foreach ($newsletters_selected as $nl_id) {
            $content .= '<tr ';
            if ($i % 2) {
                $content .= 'bgcolor="#CECECE"';
            }
            $content .= '>';
            $content .= '
                        <td >
                           ' . newsletter_mail_content_load($nl_id) . '
                        </td>
                    </tr>
                ';
            $content .= '
                    <tr>
                        <td height=25 >
                           <hr width=100%>
                        </td>
                    </tr>
                ';
            $i++;
        }

        $content .= '
        </table>
        <font face=Arial size=2>' . nl2br($email_footer) . '</font><br>
        ';
    }

    return $content;
}
function send_email($to, $subject, $body, $headers=array(), $sender_name='', $sender_email='') {
    if(!empty($to)) {
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=iso-8859-1";

        // Additional headers
        //$headers .= 'To: '. $email . "\r\n";
        $headers .= "From: $sender_name <$sender_email>";

        // Mail it
        $content=stripslashes(str_replace("/UserFiles/Image/", base_url()."/UserFiles/Image/", $body));
        $header_string = implode('\n', $headers);

        mail($to, $subject, $content, $header_string);

        return true;
    }

    return false;
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

    <?php //debug(false, true, $newsletters_selected, $_POST); ?>

    <div class="row ">
        <?php if(!empty($messages['other'])) : ?>
            <section class="panel">
                <div class="col-md-8 col-xs-12 centering">
                    <div class="row">
                        <div class="message_wrapper">
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <ul>
                                    <li><?php echo implode('</li><li>', $messages['other']); ?></li>
                                </ul>
                                <?php //unset($error_messages['send_email_modal']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        <?php if(!empty($newsletters_selected)) : ?>
            <section class="panel">
                <script>var collapse_left_sidebar=true;</script>
                <div class="row">
                    <div class="col-md-12 col-xs-12 centering newsletter_send_preview_wrapper">
                        <form action="" method="post">
                            <header class="panel-heading">
                                <h2 class="panel-title text-center">Preview Newsletter</h2>
                            </header>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <?php foreach($newsletters_selected as $nl_id) : ?>
                                            <tr>
                                                <td>
                                                    <div class="form-group form-inline" style="min-width: 50px;">
                                                        <div class="col-xs-12 p-n">
                                                            <input type="number" size="2" name="reorders[<?php echo $nl_id; ?>]" class="form-control" value="<?php echo $newsletters_selected_position[ $nl_id ]; ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo newsletter_mail_content_load($nl_id); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            </div>
                            <footer class="panel-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="text-left">
                                            <button type="Submit" name="submit_type" value="reorder" class="command  btn btn-default btn-primary">Re-Position</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="#modal_newslemailform" type="button" data-target="#modal_newslemailform" class="modal-with-form btn btn-primary btn-success modal_newslemailform_load modal_loader">Enter Email Details & Send</a>
                                        <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $parent_page_url.'&page='.$page.'&status_filter='.$status_filter; ?>';">Cancel</button>
                                    </div>
                                </div>
                            </footer>
                        </form>
                    </div>
                </div>
                <div class="modal_newslemailform modal-block modal-block-primary mfp-hide" id="modal_newslemailform">
                    <form name="send_email" action="<?php echo $self_page; ?>" method="post" class="form form-validate">
                        <section class="panel">
                            <header class="panel-heading">
                                <div class="panel-actions">
                                    <a href="#" class="panel-action panel-action-dismiss modal-dismiss modal_clear_everything"></a>
                                </div>
                                <h2 class="panel-title text-center">Send Newsletter</h2>
                            </header>
                            <div class="panel-body">
                                <?php if(!empty($error_messages['send_email_modal'])) : ?>
                                    <div class="row">
                                        <div class="message_wrapper">
                                            <div class="alert alert-danger">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                <ul>
                                                    <li><?php echo implode('</li><li>', $error_messages['send_email_modal']); ?></li>
                                                </ul>
                                                <?php //unset($error_messages['send_email_modal']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($messages['send_email_modal'])) : ?>
                                    <div class="row">
                                        <div class="message_wrapper">
                                            <div class="alert alert-success">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                <ul>
                                                    <li><?php echo implode('</li><li>', $messages['send_email_modal']); ?></li>
                                                </ul>
                                                <?php //unset($error_messages['send_email_modal']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="form-group <?php echo ( !empty($error_messages['send_email_modal']['email_subject']) ? 'has-error' : '' ); ?> ">
                                    <label class="col-md-4 control-label" for="email_subject">Email Subject</label>
                                    <div class="col-md-8">
                                        <input type="text" name="email_subject" id="email_subject" class="form-control" value="<?php echo ( !empty($email_subject) ? $email_subject: '' ); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group <?php echo ( !empty($error_messages['send_email_modal']['sender_name']) ? 'has-error' : '' ); ?> ">
                                    <label class="col-md-4" for="sender_name">Sender Name</label>
                                    <div class="col-md-8">
                                        <input type="text" name="sender_name" id="sender_name" class="form-control" value="<?php echo ( !empty($sender_name) ? $sender_name: '' ); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group <?php echo ( !empty($error_messages['send_email_modal']['sender_email']) ? 'has-error' : '' ); ?> ">
                                    <label class="col-md-4" for="sender_email">Sender Email</label>
                                    <div class="col-md-8">
                                        <input type="text" name="sender_email" id="sender_email" class="form-control" value="<?php echo ( !empty($sender_email) ? $sender_email: '' ); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group <?php echo ( !empty($error_messages['send_email_modal']['test_email']) ? 'has-error' : '' ); ?> ">
                                    <label class="col-md-4" for="test_email">Test Email</label>
                                    <div class="col-md-8">
                                        <input type="text" name="test_email" id="test_email" class="form-control" value="<?php echo ( !empty($test_email) ? $test_email: '' ); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group <?php echo ( !empty($error_messages['send_email_modal']['email_header']) ? 'has-error' : '' ); ?> ">
                                    <label class="col-md-4" for="email_header">Email Header</label>
                                    <div class="col-md-8">
                                        <textarea name="email_header" id="email_header" class="form-control" cols="33" rows="5"><?php echo ( !empty($email_header) ? $email_header: '' ); ?> </textarea>
                                    </div>
                                </div>
                                <div class="form-group <?php echo ( !empty($error_messages['send_email_modal']['email_footer']) ? 'has-error' : '' ); ?> ">
                                    <label class="col-md-4" for="email_footer">Email Footer</label>
                                    <div class="col-md-8">
                                        <textarea name="email_footer" id="email_footer" class="form-control" cols="33" rows="5"><?php echo ( !empty($email_footer) ? $email_footer: '' ); ?> </textarea>
                                    </div>
                                </div>
                            </div>
                            <footer class="panel-footer">
                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <button type="Submit" name="submit_type" value="send_test" class="command  btn btn-default btn-success">Send Test Newsletter</button>
                                        <button type="Submit" name="submit_type" value="send_mails_members" class="command  btn btn-default btn-warning" onclick="if(confirm('Are you sure you want to send newsletter to all the members?')){return true;}else{return false;}" >Send Newsletter to Members</button>
                                        <button class="btn btn-default btn-warning modal-dismiss modal_clear_everything">Cancel</button>
                                    </div>
                                </div>
                            </footer>
                        </section>
                    </form>
                </div>
            </section>
            <section class="p-lg m-lg"></section>
        <?php endif; ?>

        <section class="panel">
            <div class="row">
                <div class="col-md-8 col-xs-12 centering newsletter_send_form_wrapper">
                    <form name="theform" class="form-bordered newsletter_send form-validate" action="<?php echo $self_page; ?>" method="post" >
                        <header class="panel-heading">
                            <h2 class="panel-title text-center">Send Newsletter</h2>
                        </header>
                        <div class="panel-body">
                            <div class="form-group">
                                <input type="hidden" name="newsletterid" value="<?php echo ( ( !empty($newsletterid) ) ? $newsletterid : '' );?>">
                                <label class="col-md-4 control-label" for="archive">Select Archive</label>
                                <div class="col-md-8">
                                    <select class="form-control archive_select" name="archive" id="archive">
                                        <?php
                                        $sql = "select *  from newsletters_articles n inner join tbl_newsletter_new t on n.article_id=t.id order by n.position ";
                                        $query = mysqli_query($conn,$sql);
                                        $i=0;
                                        while ($row = mysqli_fetch_array($query)) {
                                            $arch_news_arr_id[ $row['newsletter_id'] ][] = $row['article_id'];
                                        }

                                        $query = "select *, date_format(date_created, '%m/%d/%Y') as dtn from newsletters_mails where archived='1' order by id desc ";
                                        $q = mysqli_query($conn,$query);

                                        echo '<option>Select Archive</option>';

                                        while ($a = mysqli_fetch_array($q)) {
                                            $selected = ( (!empty($archive_selected) && ($archive_selected == $a['id']) ) ? 'selected' : '' );
                                            echo '<option value="'.$a['id'].'" '. ( !empty($arch_news_arr_id[ $a['id'] ]) ? 'data-articles="'.implode(',', $arch_news_arr_id[ $a['id'] ]).'"' : '') .'  '.$selected.' >'.$a['dtn'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 or_wrapper text-center">
                                    <p class="form-control-static">-- OR --</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="newsletters[]">Select Newsletter</label>
                                <div class="col-md-8">
                                    <select class="form-control newsletters_select" name="newsletters[]" id="newsletters[]" multiple size="15" required>
                                        <?php
                                        $query  = "select id, title, date_format(dt, '%m/%d/%Y') as dtn from tbl_newsletter_new where `status`='1' order by dtn desc, title ";
                                        $q 		= mysqli_query($conn,$query);

                                        while ($news = mysqli_fetch_array($q)) {
                                            $selected = ( (!empty($newsletters_selected) && is_array($newsletters_selected) && in_array($news['id'], $newsletters_selected)) ? 'selected' : '' );
                                            echo '<option value="'.$news['id'].'" '.$selected.'>'.$news['title'] . ' - ' .$news['dtn'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group"></div>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-sm-9 centering text-center">
                                    <input type="hidden" name="goto" value="preview">
                                    <button type="Submit" name="preview" value="" class="command  btn btn-default btn-success">Preview Newsletter</button>
                                    <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $parent_page_url.'&page='.$page.'&status_filter='.$status_filter; ?>';">Cancel</button>
                                </div>
                            </div>
                        </footer>

                        <script>
                            jQuery(document).ready(function($){
                                $('.archive_select').change(function(){
                                    var value = $(this).val();
                                    var nIds = $('option[value="'+value+'"]', this).attr('data-articles');

                                    if(nIds) {
                                        var nIds_exploded = nIds.split(',');

                                        if( nIds_exploded.length > 0 ) {
                                            $('.newsletters_select').val([]);
                                            for (var i in nIds_exploded) {
                                                $('.newsletters_select option[value="' + nIds_exploded[i] + '"]').attr("selected", "selected");
                                            }
                                        }
                                    }
                                });
                            });
                        </script>

                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
<script>
    function test_email(){
        var email_address = prompt('Please enter the email address for the test newsletter:', 'brian@mvisolutions.com');
        if ( (email_address==' ') || (email_address==null) ) {
        } else {
            document.send_email.test_email.value =	email_address;
            document.send_email.submit();
        }
    }

    jQuery(document).ready(function($){
        var showmodal = <?php echo ( (!empty($error_messages['send_email_modal']) || $open_modal ) ? 'true;' : 'false' ); ?>;

        if( (window.location.hash == '#showmodal') || showmodal ) {
            jQuery('.modal_newslemailform_load').click();
        }
    });
</script>


<?php
require_once("templates/footer.php");