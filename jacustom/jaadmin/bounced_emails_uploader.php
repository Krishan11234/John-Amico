<?php
//ini_set('display_errors', 0);
//error_reporting(E_ALL ^ E_NOTICE);

/* $Id$
 *
 * This script will allow the admin
 * to upload their CSV file to the server.
 * it will then parse this file, initiating
 * any of the commission rules that match
 * the specific category.
 *
 */

$page_name = 'Bounced Emails\' CSV Upload';
$page_title = 'John Amico - ' . $page_name;

require_once("session_check.inc");
require_once("../common_files/include/global.inc");
require_once( base_library_path().'/phpmailer/class.phpmailer.php');

define('CSV_DIR', base_admin_path()."/csv/bounced_emails/");


//echo '<pre>'; var_dump($_POST, $_FILES); echo ''; die();

$error_messages = $success_messages = array();
$member_type_name = 'STW Report Upload';
//$member_type_name_plural = 'STW Reports';
$self_page = basename(__FILE__);
$self_page_url = base_admin_url(). "/{$self_page}";
$page_url = base_admin_url() . "/{$self_page}?1=1";


if ( !empty($_FILES['csv']) ) {
    $file_info = pathinfo( $_FILES['csv']['name'] );

    //echo '<pre>'; var_dump($file_info); echo '</pre>'; die();

    if(!empty($_POST['campaign_name'])) {
        $campaign_name = filter_var($_POST['campaign_name'], FILTER_SANITIZE_STRING);
        if(empty($campaign_name)) {
            bouncedEmailUploaderLog('error',"Invalid Campaign name");
        }
    } else {
        bouncedEmailUploaderLog('error',"Campaign name required");
    }

    if(!empty($campaign_name)) {
        if(_checkIfMailchimpCampaignExists($campaign_name)) {
            bouncedEmailUploaderLog('error',"Campaign already exists");
        }
    }

    if( empty($error_messages) && !empty($file_info) && in_array(strtolower($file_info['extension']), array('csv')) ) {
        if(!is_dir(CSV_DIR)) {
            mkdir(CSV_DIR);
        }
        if(is_dir(CSV_DIR)) {
            $fileOriginalName = "{$file_info['filename']}.{$file_info['extension']}";
            $fileName = $file_info['filename'] . "___" . date('d-M-Y__G-i') . "." . $file_info['extension'];
            $csv_file = CSV_DIR . $fileName;
            $csv_name = $_FILES['csv']['name'];

            if (move_uploaded_file($_FILES['csv']['tmp_name'], $csv_file))
            {
                bouncedEmailUploaderLog('success', "File was uploaded successfully!");
                $rows = parse_bounced_csv_file($fileName);

                if(!empty($rows)) {
                    $campaignId = import_bounced_email_rows(array('campaign_name'=>$campaign_name, 'email_rows' => $rows));
                    if(!empty($campaignId)) {
                        bounced_emails_uploaded_success_email($fileName, $fileOriginalName, $campaign_name, str_replace(array('uploader'), 'campaigns', $self_page_url)."?action=view&id={$campaignId}");
                    }
                } else {
                    bouncedEmailUploaderLog('error',"No valid email address found in CSV");
                }

            } else {
                bouncedEmailUploaderLog('error',"Could not save the file to the desired directory");
            }
        } else {
            bouncedEmailUploaderLog('error',"The directory: '<em>".CSV_DIR."</em>'is not found");
        }
    }

}


function parse_bounced_csv_file($filename) {
    $file_full_path = CSV_DIR . $filename;

    $rows = array();

    if(file_exists($file_full_path)) {
        $fp = fopen($file_full_path, "r");

        $row = 0;
        $fileRow = 0;

        while (($data = fgetcsv($fp, 1000, ",")) !== FALSE) {

            if($fileRow == 0) {
                $emailTitle = trim(html_entity_decode($data[0]));

                if($emailTitle != 'Email Address') {
                    bouncedEmailUploaderLog('error',"The first Column of the CSV, is not for 'Email Address' ");
                    break;
                }
            }

            // Leave the first Row for Headers
            if( $fileRow != 0 ) {
                $email = trim(html_entity_decode($data[0]));
                $fname = trim(html_entity_decode($data[1]));
                $lname = trim(html_entity_decode($data[2]));

                if(!empty($email)) {
                    $rows[$row]['email'] = $email;
                    $rows[$row]['firstname'] = $fname;
                    $rows[$row]['lastname'] = $lname;

                    $row++;
                }
            }

            $fileRow++;
        }
    }

    return $rows;
}
function import_bounced_email_rows(array $argValues) {
    global $conn;

    if(empty($argValues['campaign_name'])) {
        $error = true;
        bouncedEmailUploaderLog('error',"Campaign name not found while saving data");
    }
    if(empty($argValues['email_rows']) || !is_array($argValues['email_rows'])) {
        $error = true;
        bouncedEmailUploaderLog('error',"No emails found while saving data");
    }

    if(!_checkIfMailchimpBouncedTableExists() && empty($error)) {
        _createMailchimpBouncedTable();
    }

    if(_checkIfMailchimpBouncedTableExists() && empty($error)) {

        $sql = "INSERT INTO mailchimp_bounced_emails_campaigns (`name`, `created`) VALUES ('{$argValues['campaign_name']}', NOW() )";
        $query = mysqli_query($conn, $sql) or bouncedEmailUploaderLog('error', "Mysql Error happened on <em>".__FUNCTION__."</em>. Error was: <strong>".mysqli_error($conn)."</strong>");
        $campaignId = mysqli_insert_id($conn);


        $found = 0;
        foreach ($argValues['email_rows'] as $row) {
            if (!empty($row['email'])) {
                $values[] = "( {$campaignId}, '{$row['email']}', '{$row['firstname']}', '{$row['lastname']}' )";
                $found++;
            }
        }

        if (!empty($values)) {
            $insertSql = "INSERT INTO mailchimp_bounced_emails (`campaign_id`, `email`, `firstname`, `lastname`) VALUES";
            $insertSql .= implode(', ', $values);
            $insertSql .= ";";

            $query = mysqli_query($conn, $insertSql) or bouncedEmailUploaderLog('error', "Mysql Error happened on <em>".__FUNCTION__."</em>. Error was: <strong>".mysqli_error($conn)."</strong>");

            if(!empty($query)) {
                bouncedEmailUploaderLog('success', "Total <em><strong>{$found}</strong></em> emails found and inserted.");

                return $campaignId;
            }
        }
    } else {
        bouncedEmailUploaderLog('error',"DB Table not found.");
    }

    return false;
}
function bounced_emails_uploaded_success_email($filename, $originalNameOfFile, $campaignName, $emailsUrl) {
    $file_full_path = CSV_DIR . $filename;

    if(file_exists($file_full_path)) {

        // Subject
        $subject = "New Mailchimp bounced email list is uploaded to custom admin panel at johnamico.com";

        // Main Message
        $htmlMessage = "";
        $htmlMessage .= "<p>Hello,</p>";
        $htmlMessage .= "<p>This is to notify that a new \"Bounced Email List\" form Mailchimp campaign (<strong>{$campaignName}</strong>) is uploaded successfully to the custom admin panel.</p>";
        $htmlMessage .= "<p>You can see the list of the emails <a href='$emailsUrl'>here</a>.</p><br/>";
        $htmlMessage .= "<p>Thank You,<br/>John Amico Web Portal Team</p>";


        if(class_exists('PHPMailer')) {
            $mail = new PHPMailer();
            $mail->isHTML();

            $mail->setFrom("support@johnamico.com", "John Amico Support");
            $mail->Subject = $subject;
            $mail->MsgHTML($htmlMessage);
            $mail->AddAttachment($file_full_path, $originalNameOfFile);

            if(is_in_live()) {
                $mail->addAddress("john.amicojr@johnamico.com", "John Amico");
                $mail->addBCC("omar.at.mvi.dev@gmail.com", "Omar Sharif");
            } else {
                $mail->addAddress("omar.at.mvi.dev@gmail.com", "Omar Sharif");
            }
            $mail->addBCC("minkul@mvisolutions.com", "Minkul Alam");

            $mail->send();
        }

    }
}
function _createMailchimpBouncedTable() {
    global $conn;

    $sql = "
        CREATE TABLE IF NOT EXISTS `mailchimp_bounced_emails_campaigns` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        
    ";
    mysqli_query($conn, $sql) or bouncedEmailUploaderLog('error', "Mysql Error happened on <em>".__FUNCTION__."</em>. Error was: <strong>".mysqli_error($conn)."</strong>");

    $sql = "
        CREATE TABLE IF NOT EXISTS `mailchimp_bounced_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `campaign_id` int(11) NOT NULL,
            `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            `firstname` varchar(100) COLLATE utf8_unicode_ci NULL,
            `lastname` varchar(100) COLLATE utf8_unicode_ci NULL,
          PRIMARY KEY (`id`),
          KEY `campaign_id` (`campaign_id`),
          CONSTRAINT `mailchimp_bounced_emails_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `mailchimp_bounced_emails_campaigns` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        
    ";
    mysqli_query($conn, $sql) or bouncedEmailUploaderLog('error', "Mysql Error happened on <em>".__FUNCTION__."</em>. Error was: <strong>".mysqli_error($conn)."</strong>");
}
function _checkIfMailchimpBouncedTableExists() {
    global $conn;

    $query = mysqli_query($conn, "SHOW TABLES LIKE 'mailchimp_bounced_emails_campaigns';") or bouncedEmailUploaderLog('error', "Mysql Error happened on <em>".__FUNCTION__."</em>. Error was: <strong>".mysqli_error($conn)."</strong>");
    if(mysqli_num_rows($query) > 0) {
        return true;
    }

    return false;
}
function _checkIfMailchimpCampaignExists($campaignName) {
    global $conn;

    if( !_checkIfMailchimpBouncedTableExists() ) {
        _createMailchimpBouncedTable();
    }

    if(!empty($campaignName)) {
        $query = mysqli_query($conn, "SELECT * FROM `mailchimp_bounced_emails_campaigns` WHERE `name`='{$campaignName}' ") or bouncedEmailUploaderLog('error', "Mysql Error happened on <em>".__FUNCTION__."</em>. Error was: <strong>".mysqli_error($conn)."</strong>");

        if(mysqli_num_rows($query) > 0) {
            return true;
        }
    }

    return false;
}
function bouncedEmailUploaderLog($logType='success', $message) {
    global $success_messages, $error_messages;

    if(!empty($message)) {
        if($logType == 'success') {
            $success_messages[] = $message;
        }
        if($logType == 'error') {
            $error_messages[] = $message;
        }
    }
}



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

    <?php if( !empty($success_messages) || !empty($error_messages) ):?>
    <div class="row ">
        <section class="panel">
            <div class="col-xs-12 col-lg-10 col-md-10 centering">
                <?php if(!empty($error_messages)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <li><?php echo implode('</li><li>', $error_messages);?></li>
                        </ul>
                    </div>
                <?php endif;?>
                <?php if(!empty($success_messages)): ?>
                    <div class="alert alert-success">
                        <ul>
                            <li><?php echo implode('</li><li>', $success_messages);?></li>
                        </ul>
                    </div>
                <?php endif;?>
            </div>
        </section>
    </div>
    <?php endif; ?>
    <div class="row ">
        <section class="panel">
            <form name="file_upload" class="form-bordered" action="" method="post" enctype="multipart/form-data">
                <div class="col-xs-12 col-lg-10 col-md-10 centering">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">Upload CSV file</h2>
                    </header>
                    <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="campaign_name">Mailchimp Campaign Name <span class="required">*</span></label>
                            <div class="col-md-6">
                                <input id="campaign_name" type="text" class="form-control" name="campaign_name" value="<?php echo !empty($_POST['campaign_name']) ? $_POST['campaign_name']: ''?>" required/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="bounced_emails_csv">CSV File <span class="required">*</span></label>
                            <div class="col-md-6">
                                <input id="bounced_emails_csv" type="file" class="form-control" name="csv" required/>
                            </div>
                        </div>

                    </div>
                    <footer class="panel-footer text-center">
                        <input type="submit" value="Upload" name="submit" />
                    </footer>
                </div>
            </form>
            <div class="clearfix"></div>
        </section>
    </div>
</div>


<?php
require_once("templates/footer.php");
