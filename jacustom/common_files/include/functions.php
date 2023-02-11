<?php
/**
 * User: omar
 */

date_default_timezone_set('America/New_York');

include_once "query_results_limit_functions.php";

class MailQueue {

    const MAIL_QUEUE_TABLE = 'mail_log';
    const MAIL_QUEUE_CHUNK_TABLE = 'mail_log_chunk';
    const MAIL_QUEUE_COMPLETION_MAIL_TABLE = 'mail_log_queue_complete_email';

    /**
     * number of rows to save to database at once
     */
    var $CHUNK_SIZE = 50;
    var $MAIL_SENDING_CHUNK_SIZE = 25;

    var $queue = array();
    var $queueCodeColumn_set = false;
    var $fromColumn_set = false;
    var $queueChunkTable_set = false;
    var $queueCompletionMailTable_set = false;
    var $queueMailIdColumn_set = false;

    public $mailer = null;
    public $usePHPMailer = false;
    public $useSwiftMailer = false;
    public $_useSendgridSMTP = false;


    public $sendgridHost = 'smtp.sendgrid.net';
    public $sendgridUser = 'apikey';
    public $sendgridPass = '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';
    public $sendgridPort = 587;

    public $localSMTPHost = '192.168.0.10';


    function __construct(){
        global $conn;

        $this->mysqli_connection = $conn;

        if( $this->is_live_site() ) {
            $this->_useSendgridSMTP = false;
        }

        if( !$this->is_live_site() ) {
            $this->sendgridUser = 'mvi_johnamico_magento';  // SMTP username
            $this->sendgridPass = 'MVI123mvi';   // SMTP password
        }

        $this->prepare_QueueMailId_tableColumn_in_chunkTable();
        $this->prepare_QueueCode_tableColumn();
        $this->prepare_createdAt_tableColumn();
        $this->prepare_from_tableColumn();
        $this->prepare_sentOn_tableColumn();

        $this->prepare_queueChunk_table();
        $this->prepare_queueCompletionMail_table();

        $this->requireLibraries();

        $this->initializeMailer();
    }

    function prepare_QueueMailId_tableColumn_in_chunkTable() {
        $columnCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getQueueChunkTableName()}' AND COLUMN_NAME = 'queue_mail_id'";
        $query = mysqli_query($this->mysqli_connection, $columnCheckSql);

        if( mysqli_num_rows($query) < 1 ) {
            $alterSql = " ALTER TABLE `{$this->getQueueChunkTableName()}` ADD `queue_mail_id` int(255) NOT NULL COMMENT 'Mail Complete ID for the Queue' AFTER `chunk_id` ; ";
            $query = mysqli_query($this->mysqli_connection, $alterSql);
            $alterSql = " ALTER TABLE `{$this->getQueueChunkTableName()}` ADD INDEX `queue_mail_id` (`queue_mail_id`); ";
            $query = mysqli_query($this->mysqli_connection, $alterSql);

            if( $query ) {
                $updateSql = "
                    UPDATE mail_log_chunk mlc
                    INNER JOIN mail_log_queue_complete_email mlce ON mlc.queue_code = mlce.queue_code
                    SET mlc.queue_mail_id=mlce.queue_mail_id
                ";
                $query = mysqli_query($this->mysqli_connection, $updateSql);
                $this->queueMailIdColumn_set = true;
            }
        } else {
            $this->queueMailIdColumn_set = true;
        }
    }

    function prepare_QueueCode_tableColumn() {
        $columnCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getTableName()}' AND COLUMN_NAME = 'queue_code'";
        $query = mysqli_query($this->mysqli_connection, $columnCheckSql);

        if( mysqli_num_rows($query) < 1 ) {
            $alterSql = " ALTER TABLE `{$this->getTableName()}` ADD `queue_code` varchar(255) COLLATE 'latin1_swedish_ci' NULL COMMENT 'Identifier Code for the Queue'; ";
            $query = mysqli_query($this->mysqli_connection, $alterSql);

            if( $query ) {
                $this->queueCodeColumn_set = true;
            }
        } else {
            $this->queueCodeColumn_set = true;
        }
    }

    function prepare_createdAt_tableColumn() {
        $columnCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getTableName()}' AND COLUMN_NAME = 'created_at'";
        $query = mysqli_query($this->mysqli_connection, $columnCheckSql);

        if( mysqli_num_rows($query) < 1 ) {
            $alterSql = " ALTER TABLE `{$this->getTableName()}` ADD `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `queue_code`; ";
            $query = mysqli_query($this->mysqli_connection, $alterSql);
        }
    }

    function prepare_from_tableColumn() {
        $columnCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getTableName()}' AND COLUMN_NAME = 'from'";
        $query = mysqli_query($this->mysqli_connection, $columnCheckSql);

        if( mysqli_num_rows($query) < 1 ) {
            $alterSql = " ALTER TABLE `{$this->getTableName()}` ADD `from` varchar(255) COLLATE 'latin1_swedish_ci' NULL COMMENT 'sender email id' AFTER `to`;";
            $query = mysqli_query($this->mysqli_connection, $alterSql);

            if( $query ) {
                $this->fromColumn_set = true;
            }
        } else {
            $this->fromColumn_set = true;
        }
    }

    function prepare_sentOn_tableColumn() {
        $columnCheckSql = "SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getTableName()}' AND COLUMN_NAME = 'sent_on'";
        $query = mysqli_query($this->mysqli_connection, $columnCheckSql);

        if( mysqli_num_rows($query) > 0 ) {
            $dataType = mysqli_fetch_assoc($query);

            if( $dataType['DATA_TYPE'] != 'varchar' ) {
                $alterSql = " ALTER TABLE `{$this->getTableName()}` CHANGE `sent_on` `sent_on` varchar(20) NULL COMMENT 'mail() sent unix timestamp. Updated after mail is sent. Null when in queue.' AFTER `header`;";
                $query = mysqli_query($this->mysqli_connection, $alterSql);

                if( $query ) {
                    $this->fromColumn_set = true;
                }
            } else {
                $this->fromColumn_set = true;
            }
        } else {
            $this->fromColumn_set = true;
        }
    }

    function prepare_queueChunk_table() {
        $tableCheckSql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getQueueChunkTableName()}' ";
        $query = mysqli_query($this->mysqli_connection, $tableCheckSql);

        if( mysqli_num_rows($query) < 1 ) {
            $sql = "
            
            CREATE TABLE IF NOT EXISTS `{$this->getQueueChunkTableName()}` ( 
                `chunk_id` int(255) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `queue_code` varchar(50) NOT NULL,
                `mail_log_id` int(255) NOT NULL COMMENT 'Mail Log ID',
                `status` int(1) NOT NULL DEFAULT '0' COMMENT '0=Pending; 1=Running; 2=Completed; 4=CouldNotSend',
                `created` datetime NOT NULL,
                `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP

            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ";

            $query = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

            if( $query ) {
                $this->queueChunkTable_set = true;
            }
        } else {
            $this->queueChunkTable_set = true;
        }
    }

    function prepare_queueCompletionMail_table() {
        $tableCheckSql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getQueueCompletionMailTableName()}' ";
        $query = mysqli_query($this->mysqli_connection, $tableCheckSql);

        //echo $tableCheckSql; die();

        if( mysqli_num_rows($query) < 1 ) {
            $sql = "
            
            CREATE TABLE IF NOT EXISTS `{$this->getQueueCompletionMailTableName()}` ( 
                `queue_mail_id` int(255) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `queue_code` varchar(50) NOT NULL,
                `sender_id` varchar(1000) NOT NULL,
                `sender_email` varchar(100000) NOT NULL,
                `subject` varchar(100000) NOT NULL,
                `status` int(1) NOT NULL DEFAULT '0' COMMENT '0=Pending; 1=Running; 2=Completed; 3=ConfirmationEmailSent',
                `created` datetime NOT NULL,
                `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP

            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ";

            //echo $sql; die();

            $query = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

            if( $query ) {
                $this->queueCompletionMailTable_set = true;
            }
        } else {
            $this->queueCompletionMailTable_set = true;
        }
    }

    function requireLibraries()
    {
        if($this->usePHPMailer) {
            include_once dirname(__FILE__) . "/../library/phpmailer/PHPMailerAutoload.php";
        }
        if($this->useSwiftMailer) {
            include_once dirname(__FILE__) . "/../library/swiftmailer/lib/swift_required.php";
        }
    }

    function initializeMailer() {
        $mailer = false;

        if( $this->usePHPMailer ) {
            if( class_exists('PHPMailer') )
            {
                $mailer = new PHPMailer();
                $mailer->isSMTP(); // Set mailer to use SMTP

                if( $this->_useSendgridSMTP )
                {
                    $mailer->Host = $this->sendgridHost;
                    $mailer->SMTPAuth = true;      // Enable SMTP authentication
                    $mailer->Port = $this->sendgridPort;  // TCP port to connect to

                    $mailer->Username = $this->sendgridUser;  // SMTP username
                    $mailer->Password = $this->sendgridPass;   // SMTP password

                }
                else {
                    if( $this->is_live_site() ) {
                        $mailer->Host = $this->localSMTPHost;  // This will work only from MVI Server
                        $mailer->SMTPAuth = false;  // Do not need authentication
                    } else {
                        //$unsetMailer = true;
                        $mailer->isMail();
                    }
                }
                $mailer->XMailer = 'John Amico';  // Do not need authentication
            }

            if(!empty($unsetMailer)) {
                $mailer = null;
            }
        }
        elseif( $this->useSwiftMailer ) {
            if( class_exists('Swift') )
            {
                if( $this->_useSendgridSMTP ) {
                    $transport = Swift_SmtpTransport::newInstance($this->sendgridHost, $this->sendgridPort)
                        ->setUsername($this->sendgridUser)
                        ->setPassword($this->sendgridPass)
                    ;
                }
                else {
                    if( $this->is_live_site() ) {
                        $transport = Swift_SmtpTransport::newInstance($this->localSMTPHost, 25);
                    } else {
                        $transport = Swift_SmtpTransport::newInstance();
                    }
                }

                $mailer = Swift_Mailer::newInstance($transport);
            } else {
                $mailer = null;
            }
        }

        $this->mailer = $mailer;

        return $mailer;
    }

    function getTableName() {
        return self::MAIL_QUEUE_TABLE;
    }

    function getQueueChunkTableName() {
        return self::MAIL_QUEUE_CHUNK_TABLE;
    }

    function getQueueCompletionMailTableName() {
        return self::MAIL_QUEUE_COMPLETION_MAIL_TABLE;
    }

    function getDatabaseName() {
        if ($result = mysqli_query($this->mysqli_connection, "SELECT DATABASE()")) {
            $row = mysqli_fetch_row($result);
            $db = $row[0];
            mysqli_free_result($result);

            return $db;
        }

        return false;
    }

    function add_to_queue($to, $subject, $message, $header, $sent_by, $member_id, $amico_id, $fromEmail=null) {

        if( !$this->is_live_site() ) { $to = $this->to_builder($to); }

        $item = array(
            'to' => $to,
            //'to' => 'ownrr@mailinator.com',
            'subject' => $subject,
            'message' => $message,
            'header' => $header,
            'sent_by' => $sent_by,
            'member_id' => $member_id,
            'amico_id' => $amico_id,
            'from' => $fromEmail
        );

        $this->queue[] = $item;
    }

    function to_builder($to) {
        if( !empty($to) ) {
            $to = str_replace(array(' ', '.', ',', '-', '_', '<', '>', '/', '(', ')'), '', strtolower($to));
            $to = str_replace(array('@'), '_', $to);

            $to = "omar.at.mvi.dev+{$to}@gmail.com";

            return $to;
        }

        return '';
    }

    function to_built($to) {
        if( !empty($to) ) {
            if( strpos($to, "omar.at.mvi.dev") !== false ) {
                return true;
            }
        }

        return false;
    }

    function is_valid_email($email) {
        return preg_match('/@.+\./', $email);
    }

    function escape($str) {
        //return filter_var( stripslashes($str), FILTER_SANITIZE_STRING);
        return mysqli_real_escape_string($this->mysqli_connection, stripslashes($str));
    }

    function unescape($str) {
        return stripslashes($str);
    }

    function prepare_row_insert_sql_data($item) {

        $string = "('{$this->escape($item['to'])}','{$this->escape($item['subject'])}','{$this->escape($item['message'])}','{$this->escape($item['header'])}','{$item['sent_by']}','{$item['member_id']}','{$item['amico_id']}' ";

        if( !empty($item['queue_code']) && $this->queueCodeColumn_set ) {
            $string .= ", '{$item['queue_code']}' ";
        }
        if( !empty($item['from']) && $this->fromColumn_set ) {
            $string .= ", '{$item['from']}' ";
        }

        $string .= " )";

        return $string;
    }

    function save_queue_chunk($chunk, $queueCode=null) {
        $row_data = array();
        foreach($chunk as $item) {
            if($this->is_valid_email($item['to'])) {

                if( !empty($queueCode) ) {
                    $item['queue_code'] = $queueCode;
                }
                $row_data[] = $this->prepare_row_insert_sql_data($item);
            }
        }

        $sql = "INSERT INTO `{$this->getTableName()}` (`to`, `subject`, `message`, `header`, `sent_by`, `member_id`, `amico_id` ";
        if( !empty($queueCode) && $this->queueCodeColumn_set ) {
            $sql .= ", `queue_code` ";
        }
        if( $this->fromColumn_set ) {
            $sql .= ", `from` ";
        }
        $sql .= ") VALUES ".implode(',',$row_data);

        mysqli_query($this->mysqli_connection,$sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);
        //mysqli_query($this->mysqli_connection,$sql) or die(mysqli_error($this->mysqli_connection));

        unset($row_data);
        unset($sql);
    }

    function save_queue() {
        $queueCode = $this->randomString();
        $queue_chunks = array_chunk($this->queue, $this->CHUNK_SIZE, true);

        foreach($queue_chunks as $chunk) {
            $this->save_queue_chunk($chunk, $queueCode);
        }

        //$this->generate_mail_queue_chunks($queueCode);

        unset($this->queue);
    }

    function generate_mail_queue_chunks($queueCode='') {

        if(!empty($queueCode)) {
            $this->generate_mail_queue_chunk($queueCode);
        }
        else {
            // CHeck if there is any Unsent email left in the queue
            $sql = "SELECT queue_code FROM {$this->getTableName()} WHERE sent_on IS NULL GROUP BY `queue_code` ORDER BY id ASC ";
            $checkQuery = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

            if( mysqli_num_rows($checkQuery) > 0 ) {
                while($row = mysqli_fetch_assoc($checkQuery)) {
                    if( !empty($row['queue_code']) ) {
                        $this->generate_mail_queue_chunk($row['queue_code']);
                    }
                }
            }
        }

    }

    function generate_mail_queue_chunk($queueCode) {
        if( empty($queueCode) ) {
            return false;
        }

        // Check if there is any Unsent email left in the queue or we didn't added them already in the Queue
        $checkSql = " 
            SELECT 0 AS queue_mail_id, '{$queueCode}' AS queue_code, ml.id AS mail_log_id, 0 AS status, NOW() AS created, NOW() AS updated 
            FROM {$this->getTableName()} as ml
            WHERE ml.sent_on IS NULL AND ml.queue_code='{$queueCode}' 
            AND ml.id NOT IN (SELECT mail_log_id FROM {$this->getQueueChunkTableName()} WHERE queue_code='{$queueCode}' )
        ";

        //echo $checkSql; die();

        $checkQuery = mysqli_query($this->mysqli_connection, $checkSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $checkSql, __FUNCTION__);

        // If there is no generated Queue, create queues
        if( mysqli_num_rows($checkQuery) > 0 ) {
            $insertSql = "INSERT INTO {$this->getQueueChunkTableName()} (queue_mail_id, queue_code, mail_log_id, status, created, updated) ";
            $insertSql .= " {$checkSql} ";

            $insertQuery = mysqli_query($this->mysqli_connection, $insertSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $insertSql, __FUNCTION__);

            if($insertQuery) {
                $completionId = $this->insert_to_completion_log($queueCode);
                if(!empty($completionId))
                {
                    $updateSql = "UPDATE {$this->getQueueChunkTableName()} SET `queue_mail_id`='{$completionId}' WHERE `queue_code`='{$queueCode}' AND `queue_mail_id`=0 ";
                    $updateQuery = mysqli_query($this->mysqli_connection, $updateSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $insertSql, __FUNCTION__);
                }
            }
        }


        return true;
    }

    function get_queue_last_chunk_id($queueCode) {

        if( empty($queueCode) ) {
            return 0;
        }
        // CHeck if there is any Unsent email left in the queue
        $sql = "SELECT queue_code_chunk_id FROM {$this->getQueueChunkTableName()} WHERE queue_code='{$queueCode}' ORDER BY chunk_id DESC LIMIT 1 ";
        $checkQuery = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

        if( mysqli_num_rows($checkQuery) > 0 ) {
            $row = mysqli_fetch_assoc($checkQuery);
            $chunkId = $row['queue_code_chunk_id'];

            return $chunkId;
        }

        return 0;
    }

    function get_queue_startable_chunk_id($queueCode) {

        if( empty($queueCode) ) {
            return 0;
        }
        // CHeck if there is any Unsent email left in the queue
        $sql = "SELECT queue_code_chunk_id FROM {$this->getQueueChunkTableName()} WHERE queue_code='{$queueCode}' ORDER BY chunk_id DESC LIMIT 1 ";
        $checkQuery = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

        if( mysqli_num_rows($checkQuery) > 0 ) {
            $row = mysqli_fetch_assoc($checkQuery);
            $chunkId = $row['queue_code_chunk_id'];

            return $chunkId;
        }

        return 0;
    }

    function get_is_queue_completed($queueCode) {
        if( empty($queueCode) ) {
            return false;
        }

        $sql = "SELECT COUNT(chunk_id) AS count_id FROM {$this->getQueueChunkTableName()} WHERE queue_code='{$queueCode}' AND status IN (0,1) ";
        $checkQuery = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

        if( mysqli_num_rows($checkQuery) > 0 ) {
            $row = mysqli_fetch_assoc($checkQuery);
            $chunkCount = $row['count_id'];

            if( $chunkCount > 0 ) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    function get_queue_count($queueCode) {
        if( empty($queueCode) ) {
            return false;
        }

        $checkSql = "SELECT COUNT(chunk_id) AS count_id FROM {$this->getQueueChunkTableName()} WHERE queue_code='{$queueCode}' ";
        $checkQuery = mysqli_query($this->mysqli_connection, $checkSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $checkSql, __FUNCTION__);

        if( mysqli_num_rows($checkQuery) > 0 ) {
            $row = mysqli_fetch_assoc($checkQuery);
            $chunkCount = $row['count_id'];

            return $chunkCount;
        }

        return false;
    }

    function insert_to_completion_log($queueCode) {
        if(!empty($queueCode)) {

            $checkSql = "SELECT ml.from, ml.subject, ml.sent_by FROM {$this->getTableName()} as ml WHERE ml.queue_code='{$queueCode}' LIMIT 1";
            $checkQuery = mysqli_query($this->mysqli_connection, $checkSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $checkSql, __FUNCTION__);

            if (mysqli_num_rows($checkQuery) > 0) {
                $checkResult = mysqli_fetch_assoc($checkQuery);
                if(!empty($checkResult[0])) {
                    $checkResult = $checkResult[0];
                }
                $fromEmail = $checkResult['from'];
                $subject = $checkResult['subject'];
                $sentBy = $checkResult['sent_by'];
            }

            $confirmMailCheckSql = "SELECT queue_mail_id FROM {$this->getQueueCompletionMailTableName()} WHERE queue_code='{$queueCode}' ORDER BY queue_mail_id DESC LIMIT 1";
            $confirmMailCheckQuery = mysqli_query($this->mysqli_connection, $confirmMailCheckSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $confirmMailCheckSql, __FUNCTION__);

            if (mysqli_num_rows($confirmMailCheckQuery) < 1) {
                $confirmMailCheckSql = "INSERT INTO {$this->getQueueCompletionMailTableName()} (`queue_code`, `sender_id`, `sender_email`,`subject`, `created`) VALUES ('{$queueCode}', '{$sentBy}','{$fromEmail}','{$subject}','" . date('Y-m-d H:i:s') . "') ";
                $confirmMailCheckQuery = mysqli_query($this->mysqli_connection, $confirmMailCheckSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $confirmMailCheckSql, __FUNCTION__);

                return $mailQueueCompleteId = mysqli_insert_id($this->mysqli_connection);
            }
        }

        return false;
    }

    function update_completion_log_status($queueCode, $queueMailId, $status=2) {
        if(!empty($queueCode) && !empty($queueMailId)) {

            $sql = "UPDATE {$this->getQueueCompletionMailTableName()} SET status='{$status}' WHERE queue_code='{$queueCode}' AND queue_mail_id='{$queueMailId}'  ";
            $status_updated = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

            return $status_updated;
        }

        return false;
    }

    function send_mail($item) {

        /*if( !empty($item->queue_code) ) {
            $query = mysqli_query($this->mysqli_connection, "SELECT count(chunk_id) as count_id FROM {$this->getQueueChunkTableName()} WHERE queue_code='{$item->queue_code}' AND mail_log_id='{$item->id}' AND status=2 ") or die(mysqli_error($this->mysqli_connection));

            if( mysqli_num_rows($query) > 0 ) {
                $this->update_queue($item, true);

                return true;
            }
        }*/

        $this->mailer = $this->initializeMailer();

        $to = $this->unescape($item->to);

        if( !$this->is_live_site()) {
            if(!$this->to_built($to)) {
                $to = $this->to_builder($to);
            }
        }
        $subject = $this->unescape($item->subject);
        $message = $this->unescape($item->message);
        $header = $this->unescape($item->header);

        if( !empty($this->mailer) ) {
            if( $this->usePHPMailer )
            {
                $mailSent = $this->sendUsingPHPMailer($item, $to, $subject, $message, $header);
                $this->mailer = null;
            }
            elseif ( $this->useSwiftMailer )
            {
                $mailSent = $this->sendUsingSwiftMailer($item, $to, $subject, $message, $header);
                $this->mailer = null;
            }
            else {
                $mailSent = mail($to, $subject, $message, $header);
            }
        }
        else {
            $mailSent = mail($to, $subject, $message, $header);
        }


        if( !empty($mailSent) ) {
            $this->update_queue($item, true);

            return true;
        } else {
            $this->update_queue($item, -1);  // Could not send
        }

        return false;
    }

    function sendUsingPHPMailer($item, $to, $subject, $message, $header) {
        if( !$this->mailer->validateAddress($to) ) {
            $this->update_queue($item, -1);  // Could not send

            return false;
        }

        $customHeaders = explode(PHP_EOL, $header);
        if(!empty($customHeaders)) {
            foreach($customHeaders as $customHeader) {
                if( !empty($customHeader) ) {
                    list($headKey, $headVal) = explode(':', $customHeader);
                    $headVal = trim($headVal);

                    if( !empty($headKey) ) {
                        if( strtolower($headKey) == 'from' ) {
                            $this->mailer->setFrom($headVal);
                        } else {
                            $this->mailer->addCustomHeader($headKey, $headVal);
                        }
                    }
                }
            }
        }

        //echo '<pre>'; var_dump( array($header, $customHeaders, $this->mailer->getCustomHeaders()) ); die();

        $this->mailer->addAddress($to);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $message;

        try {
            $mailSent = $this->mailer->send();
        }
        catch(Exception $e) {
            echo $e->getMessage() . PHP_EOL;

            $mailSent = mail($to, $subject, $message, $header);
        }

        // Clearing all the things from Object
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearReplyTos();
        $this->mailer->clearCustomHeaders();

        return $mailSent;
    }

    function sendUsingSwiftMailer($item, $to, $subject, $message, $header) {
        if( !$this->mailer->validateAddress($to) ) {
            $this->update_queue($item, -1);  // Could not send

            return false;
        }

        $customHeaders = explode(PHP_EOL, $header);
        if(!empty($customHeaders)) {
            foreach($customHeaders as $customHeader) {
                if( !empty($customHeader) ) {
                    list($headKey, $headVal) = explode(':', $customHeader);
                    $headVal = trim($headVal);

                    if( !empty($headKey) ) {
                        if( strtolower($headKey) == 'from' ) {
                            $this->mailer->setFrom($headVal);
                        } else {
                            $this->mailer->addCustomHeader($headKey, $headVal);
                        }
                    }
                }
            }
        }

        //echo '<pre>'; var_dump( array($header, $customHeaders, $this->mailer->getCustomHeaders()) ); die();

        $this->mailer->addAddress($to);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $message;

        try {
            $mailSent = $this->mailer->send();
        }
        catch(Exception $e) {
            echo $e->getMessage() . PHP_EOL;

            $mailSent = mail($to, $subject, $message, $header);
        }

        // Clearing all the things from Object
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearReplyTos();
        $this->mailer->clearCustomHeaders();

        return $mailSent;
    }

    function update_queue($item, $sent) {


        if($sent === true) {
            $sent_on = time();
        } elseif ($sent === false) {
            $sent_on = "-1";
        } else {
            $sent_on = $sent;
        }


        $sql = " UPDATE {$this->getQueueChunkTableName()} SET status='2', updated=NOW() WHERE mail_log_id='{$item->id}'; ";
        mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

        $sql = " UPDATE {$this->getTableName()} SET sent_on='{$sent_on}' WHERE id='{$item->id}'; ";
        mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);


        // Wait for 1 Second and let the email send
        //sleep(1);

        return true;
    }

    function process_queue() {

        //$updateTheChunk = false;

        $sqlPart = "
            FROM {$this->getTableName()} AS ml 
            INNER JOIN {$this->getQueueCompletionMailTableName()} AS mlqce ON ml.queue_code=mlqce.queue_code 
            INNER JOIN {$this->getQueueChunkTableName()} AS mlc ON mlqce.queue_mail_id=mlc.queue_mail_id 
            WHERE ml.id=mlc.mail_log_id AND mlc.status IN (0,1) AND mlqce.status IN (0,1) AND ml.sent_on IS NULL
            ORDER BY mlqce.queue_mail_id, mlc.chunk_id ASC 
        ";
        if(!empty($this->MAIL_SENDING_CHUNK_SIZE)) {
            $sqlPart .= " LIMIT {$this->MAIL_SENDING_CHUNK_SIZE} ";
        }

        $chunkedSql = " SELECT mlc.chunk_id, mlc.queue_code, mlqce.queue_mail_id {$sqlPart} ";
        $chunkedQuery = mysqli_query($this->mysqli_connection, $chunkedSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $chunkedSql, __FUNCTION__);

        if(mysqli_num_rows($chunkedQuery) > 0) {
            while($chunkRow = mysqli_fetch_assoc($chunkedQuery)) {
                $chunkIds[] = $chunkRow['chunk_id'];
                $queueCodesSelected[$chunkRow['queue_mail_id']] = $chunkRow['queue_code'];
            }

            if(empty($chunkIds)) {
                return false;
            }

            if(!empty($queueCodesSelected)) {
                foreach($queueCodesSelected as $queueMailId => $queueCodeSelected) {
                    $this->update_completion_log_status($queueCodeSelected, $queueMailId, 1);
                }
            }

            $updateSql = "UPDATE mail_log_chunk SET status=1, updated=NOW() WHERE chunk_id IN (".implode(',', $chunkIds).") ORDER BY chunk_id ASC ";
            $updateSql .= !empty($this->MAIL_SENDING_CHUNK_SIZE) ? " LIMIT {$this->MAIL_SENDING_CHUNK_SIZE} " : "";

            //$updateSql = "UPDATE {$this->getQueueChunkTableName()} SET status=1, updated=NOW() WHERE chunk_id IN ($chunkedSql) ";
            //echo '<pre>'; var_dump($updateSql); echo '</pre>'; die();
            $updateQuery = mysqli_query($this->mysqli_connection, $updateSql) or $this->watchdog(mysqli_error($this->mysqli_connection), $updateSql, __FUNCTION__);

            if($updateQuery) {
                $sql = " SELECT * ";
                $sql .= str_replace('mlc.status IN (0,1)', "mlc.status=1", $sqlPart);

                //echo $sql; die();

                $result = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

                if( mysqli_num_rows($result) > 0 ) {
                    while ($row = mysqli_fetch_object($result))
                    {
                        if (user_is_affiliate($row->sent_by) && get_level($row->sent_by, $row->amico_id) > 6) {
                            $sent = $this->update_queue($row, false);
                        } else {
                            $sent = $this->send_mail($row);
                        }

                        if($sent) {
                            $sql = "UPDATE {$this->getQueueChunkTableName()} SET status='2', updated=NOW() WHERE mail_log_id='{$row->id}'  ";
                            mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);
                        } else {
                            $sql = "UPDATE {$this->getQueueChunkTableName()} SET status='4', updated=NOW() WHERE mail_log_id='{$row->id}'  ";
                            mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

                            $this->watchdog("Could not send email. \n\r \n\r" . print_r((array)$row, true), '', __FUNCTION__);
                        }
                    }
                }
            }
        }

        $this->send_confirmation_email();

    }

    function send_confirmation_email() {

        $send_email = false;

        $confirmMailCheckSql = "
            SELECT qcm.* 
            FROM {$this->getQueueCompletionMailTableName()} qcm 
            WHERE qcm.status IN (0,1) 
            ORDER BY qcm.created ASC 
        ";
        $confirmMailCheckQuery = mysqli_query($this->mysqli_connection, $confirmMailCheckSql);

        if( mysqli_num_rows($confirmMailCheckQuery) > 0 )
        {
            while( $row = mysqli_fetch_assoc($confirmMailCheckQuery) )
            {
                if( !empty($row['queue_code']) ) {
                    if( $this->isQueueChunkFinished($row['queue_code']) ) {
                        $send_email = true;

                        /*$sql = "UPDATE {$this->getQueueCompletionMailTableName()} SET status='1' WHERE queue_code='{$row['queue_code']}' AND queue_mail_id='{$row['queue_mail_id']}'  ";
                        $status_updated = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);*/

                        if( !empty($send_email) )
                        {

                            $initiatorEmail = $row['sender_email'];

                            $notificationSubject = 'Processed Contact Organizer email requests';
                            $notificationBody = "
                            Hi There,
                            
                                <ul>
                                    <li>Total processed emails at this run: <strong>{$this->queueChunkFinishedCount($row['queue_code'])}</strong></li>
                                    <li>Initiator/Sender ID: <strong>{$row['sender_id']}</strong></li>
                                    <li>\"FROM\" Email: <strong>$initiatorEmail</strong></li>
                                    <li>Email Subject: <strong>{$row['subject']}</strong></li>
                                    <li>Encrypted Instance Code: <strong>{$row['queue_code']}</strong></li>
                                </ul>
                            <br/>
                            <br/>
                            Thanks,
                            John Amico
                            ";

                            $headers = "From: info@JohnAmico.com\r\n";
                            $headers .= "MIME-Version: 1.0\r\n";
                            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                            if( $this->is_live_site() ) {
                                $headers .= "Bcc: jeff.douglas@mvisolutions.com\r\n";
                                $headers .= "Bcc: john.amicojr@gmail.com\r\n";
                            }
                            $headers .= "Bcc: omar.at.mvi.dev@gmail.com\r\n";

                            if( !$this->is_live_site() ) {
                                $initiatorEmail = 'omar.at.mvi.dev@gmail.com';
                            }

                            $notificationSent = mail($initiatorEmail, $notificationSubject, $notificationBody, $headers);

                            if( $notificationSent ) {
                                $this->update_completion_log_status($row['queue_code'], $row['queue_mail_id'], 2);
                            }
                        }
                    }
                }
            }
        }
    }


    function isQueueChunkFinished($queue_code) {

        if( $this->queueChunkTable_set && $this->queueCompletionMailTable_set ) {
            return $this->get_is_queue_completed($queue_code);
        } else {
            $sql = "SELECT COUNT(id) as counting FROM mail_log WHERE sent_on IS NULL AND queue_code='$queue_code' ";
            $result = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

            $resultCount = mysqli_fetch_assoc($result);

            //echo '<pre>'; var_dump($resultCount); die();

            return ( $resultCount['counting'] > 0 ) ? false : true;
        }
    }

    function queueChunkFinishedCount($queue_code) {

        if( $this->queueChunkTable_set && $this->queueCompletionMailTable_set ) {
            return $this->get_queue_count($queue_code);
        }
        else {
            $sql = "SELECT COUNT(id) as counting FROM mail_log WHERE sent_on IS NOT NULL AND queue_code='$queue_code' ";
            $result = mysqli_query($this->mysqli_connection, $sql) or $this->watchdog(mysqli_error($this->mysqli_connection), $sql, __FUNCTION__);

            $resultCount = mysqli_fetch_assoc($result);

            //echo '<pre>'; var_dump($resultCount); die();

            return $resultCount['counting'];
        }

    }

    function watchdog($msg, $sql='', $function='') {
        $message = '';
        $message .= $msg;
        $message .= PHP_EOL . PHP_EOL;
        if( !empty($sql) ) {
            $message .= "SQL Query Was: " . PHP_EOL . $sql . PHP_EOL . PHP_EOL;
        }
        if( !empty($function) ) {
            $message .= "Function Name: " . PHP_EOL . $function . PHP_EOL . PHP_EOL;
        }

        mail('omar.at.mvi.dev@gmail.com', 'Debug message from JohnAmico.com (ProcessMailQueue.php Cron)', $message , 'From:info@JohnAmico.com');
    }

    function randomString($length=10) {
        $str = "";
        $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    function is_live_site() {
        /*$liveUrls = array(
            'www.johnamico.com',
            '/johnamico.com',
            'johnamico.com',
        );
        foreach($liveUrls as $liveUrl) {
            if( strpos( base_url(), $liveUrl ) !== false ) {
                return true;
            }
        }*/

        try {
            return IS_LIVE_SITE;
        } catch (Exception $e) {
            $this->watchdog("PHP ERROR: \n\r \n\r" . $e->getMessage(), '', __FUNCTION__);
        }
    }
}
