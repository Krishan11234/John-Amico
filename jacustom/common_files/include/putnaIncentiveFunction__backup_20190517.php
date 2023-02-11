<?php

class IncentiveReportGenerator {

    const REPORT_TABLE = 'puntacana_incentives';

    public $mysqli_connection;
    public $putnaTableReady = false;
    public $putnaTableAltered_IncentiveTypeAdded = false;
    public $putnaTableAltered_UniqueKeyUpdated = false;

    public $startTime = '2017-07-01 00:00:00';
    public $endTime = '2017-12-31 11:59:59';
    public $incentive_type = '';
    public $incentive_percentage = '';


    function __construct($conn=null, $incentiveType='cancun', $percentage=10) {
        if(!empty($conn)) {
            global $conn;
        }
        $this->mysqli_connection = $conn;

        $this->prepare_incentiveReport_table();
        $this->prepare_incentiveReport_table_addIncentiveTypeColumn();
        $this->prepare_incentiveReport_table_updateUniqueKey();

        $this->setIncentiveType($incentiveType);
        $this->setIncentivePercentage($percentage);
    }

    public function dbConnection() {
        return $this->mysqli_connection;
    }

    public function getIncentiveReportTableName() {
        return self::REPORT_TABLE;
    }

    public function getDatabaseName() {
        if ($result = mysqli_query($this->dbConnection(), "SELECT DATABASE()")) {
            $row = mysqli_fetch_row($result);
            $db = $row[0];
            mysqli_free_result($result);

            return $db;
        }

        return false;
    }

    public function prepare_incentiveReport_table() {
        $tableCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->getDatabaseName()}' AND TABLE_NAME = '{$this->getIncentiveReportTableName()}' ";
        $query = mysqli_query($this->dbConnection(), $tableCheckSql);

        if( mysqli_num_rows($query) < 1 ) {
            $sql = "
            
            CREATE TABLE IF NOT EXISTS `{$this->getIncentiveReportTableName()}` ( 
                `report_id` int(255) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `member_id` int(255) NOT NULL COMMENT 'tbl_member ID',
                `total_sale` DOUBLE(15,2) NOT NULL COMMENT 'Total Referring Sale',
                `incentive_percentage` int(3) NOT NULL DEFAULT '10' COMMENT 'Incentive percentage in %',
                `created` datetime NOT NULL,
                `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ";

            $query = mysqli_query($this->dbConnection(), $sql) or $this->watchdog(mysqli_error($this->dbConnection()), $sql, __FUNCTION__);


            $sql = "ALTER TABLE `{$this->getIncentiveReportTableName()}` ADD UNIQUE `member_id` (`member_id`);";
            $query = mysqli_query($this->dbConnection(), $sql) or $this->watchdog(mysqli_error($this->dbConnection()), $sql, __FUNCTION__);

            $this->putnaTableReady = true;
        } else {
            $this->putnaTableReady = true;
        }
    }

    public function prepare_incentiveReport_table_addIncentiveTypeColumn()
    {
        if($this->getPutnaTableReady())
        {
            $columnCheckSql = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '{$this->getDatabaseName()}' AND `TABLE_NAME` = '{$this->getIncentiveReportTableName()}' AND `COLUMN_NAME` = 'incentive_type'";
            $query = mysqli_query($this->dbConnection(), $columnCheckSql);

            if( mysqli_num_rows($query) < 1 ) {
                $sql = "
                ALTER TABLE `{$this->getIncentiveReportTableName()}`
                ADD `incentive_type` varchar(100) NOT NULL DEFAULT 'cancun' AFTER `total_sale`;
                ";

                $query = mysqli_query($this->dbConnection(), $sql) or $this->watchdog(mysqli_error($this->dbConnection()), $sql, __FUNCTION__);

                $this->putnaTableAltered_IncentiveTypeAdded = true;
            } else {
                $this->putnaTableAltered_IncentiveTypeAdded = true;
            }
        }
    }

    public function prepare_incentiveReport_table_updateUniqueKey()
    {
        if($this->getPutnaTableReady())
        {
            $columnCheckSql = "SELECT `CONSTRAINT_NAME` FROM `information_schema`.`TABLE_CONSTRAINTS` WHERE `CONSTRAINT_SCHEMA` = '{$this->getDatabaseName()}' AND `TABLE_NAME` = '{$this->getIncentiveReportTableName()}' AND `CONSTRAINT_TYPE` = 'UNIQUE' AND `CONSTRAINT_NAME` = 'member_id_incentive_type' ";
            $query = mysqli_query($this->dbConnection(), $columnCheckSql);

            if( mysqli_num_rows($query) < 1 ) {
                $sql = "
                ALTER TABLE `{$this->getIncentiveReportTableName()}`
                ADD UNIQUE `member_id_incentive_type` (`member_id`, `incentive_type`),
                DROP INDEX `member_id`;
                ";

                $query = mysqli_query($this->dbConnection(), $sql) or $this->watchdog(mysqli_error($this->dbConnection()), $sql, __FUNCTION__);

                $this->putnaTableAltered_UniqueKeyUpdated = true;
            } else {
                $this->putnaTableAltered_UniqueKeyUpdated = true;
            }
        }
    }

    public function getPutnaTableReady() {
        return $this->putnaTableReady;
    }

    public function getIncentiveTypeColumnReady() {
        return $this->putnaTableAltered_IncentiveTypeAdded;
    }

    public function setStartTime($time='') {
        if(!empty($time)) {
            $this->startTime = $time;
        }
        return $this;
    }
    public function setEndTime($time='') {
        if(!empty($time)) {
            $this->endTime = $time;
        }
        return $this;
    }
    public function setIncentiveType($incentiveType) {
        if(!empty($incentiveType)) {
            $this->incentive_type = filter_var($incentiveType, FILTER_SANITIZE_STRING);
        }
        return $this;
    }
    public function setIncentivePercentage($percentage) {
        if(is_numeric($percentage)) {
            $this->incentive_percentage = $percentage;
        }
        return $this;
    }

    public function getStartTime() {
        return $this->startTime;
    }
    public function getEndTime() {
        return $this->endTime;
    }
    public function getIncentiveType() {
        return $this->incentive_type;
    }
    public function getIncentivePercentage() {
        return $this->incentive_percentage;
    }


    public function getStartTimeInWords($format="M jS, Y") {
        if(!empty($this->getStartTime())) {
            return date($format, strtotime($this->getStartTime()));
        }
        return '';
    }
    public function getEndTimeInWords($format="M jS, Y") {
        if(!empty($this->getEndTime())) {
            return date($format, strtotime($this->getEndTime()));
        }
        return '';
    }



    public function generateSalesReports() {

        if( $this->getPutnaTableReady() ) {
            $insertableRows = array();
            $row_data = $this->iterateThroughMembers();
            //$row_data = $this->generateTotalOrderSalesSummations();

            if(!empty($row_data)) {
                $keys = array('member_id', 'total_sale', 'incentive_percentage');
                if($this->getIncentiveTypeColumnReady()) {
                    $keys[] = 'incentive_type';
                }
                foreach($row_data as $data) {
                    if(is_array($data) && !empty($data['member_id']) ) {

                        $data['incentive_percentage'] = ( ( !empty($this->getIncentivePercentage()) && is_numeric($this->getIncentivePercentage()) ) ? $this->getIncentivePercentage() : 10 );

                        if($this->getIncentiveTypeColumnReady()) {
                            $data['incentive_type'] = !empty($this->getIncentiveType()) ? $this->getIncentiveType() : '';
                        }

                        $rows[$data['member_id']] = array();
                        foreach($keys as $key) {
                            if(empty($data[$key])) {
                                $data[$key] = '';
                            }
                            $rows[$data['member_id']][$key] = $data[$key];
                        }
                        if(!empty($rows[$data['member_id']])) {
                            $insertableRows[$data['member_id']] = "INSERT INTO `{$this->getIncentiveReportTableName()}` (`" . implode('`,`', $keys) . "`, `created`, `updated`) ";
                            $insertableRows[$data['member_id']] .= " VALUES ( '" . implode("','", $rows[$data['member_id']]) . "', NOW(), NOW() )";
                            $insertableRows[$data['member_id']] .= " ON DUPLICATE KEY UPDATE  total_sale='{$rows[$data['member_id']]['total_sale']}', incentive_percentage='{$rows[$data['member_id']]['incentive_percentage']}', updated=NOW() ";


                            mysqli_query($this->dbConnection(), $insertableRows[$data['member_id']]) or $this->watchdog(mysqli_error($this->dbConnection()), $sql, __FUNCTION__);

                        }
                    }
                }

                //echo '<pre>'; var_dump($insertableRows); echo '</pre>'; //die();

                if(!empty($insertableRows)) {
                    //$sql = implode("; ", $insertableRows) . ";";
                    //echo '<pre>'; print_r($sql); echo '</pre>'; //die();
                    //mysqli_query($this->dbConnection(), $sql) or $this->watchdog(mysqli_error($this->dbConnection()), $sql, __FUNCTION__);
                }
            }
        } else {
            $this->watchdog("Table `{$this->getIncentiveReportTableName()}` not ready", '', __FUNCTION__);
        }
    }

    public function iterateThroughMembers() {
        $data = array();

        $sql = " SELECT m.int_member_id, m.amico_id, SUM( (sfo.base_subtotal+sfo.discount_amount) ) AS total_sales ";
        $sql .= " FROM tbl_member m ";
        $sql .= " INNER JOIN customers c ON m.int_customer_id = c.customers_id";
        $sql .= " INNER JOIN ".MAGENTO_TABLE_PREFIX."amasty_amorderattr_order_attribute aoa ON m.amico_id = aoa.jareferrer_amicoid";
        $sql .= " INNER JOIN ".MAGENTO_TABLE_PREFIX."sales_flat_order sfo ON aoa.order_id = sfo.entity_id";

        $sql .= " WHERE m.bit_active=1 AND aoa.jareferrer_self=0 ";
        $sql .= " AND aoa.later_applied_member_id IN (0, NULL, '') ";
        $sql .= " AND m.amico_id NOT IN ('W00888888') ";
        $sql .= " AND sfo.created_at >= '{$this->getStartTime()}' AND sfo.created_at <= '{$this->getEndTime()}' ";
        $sql .= " GROUP BY  m.amico_id ";
        $sql .= " ORDER BY  m.int_member_id ";

        //echo $sql; die();

        $query = mysqli_query($this->dbConnection(), $sql);

        while($row = mysqli_fetch_assoc($query)) {
            $data[$row['int_member_id']] = array(
                'member_id' => $row['int_member_id'],
                'amico_id' => $row['amico_id'],
                'total_sale' => $row['total_sales'],
            );
        }

        return $data;
    }

    public function getIncentiveReport($memberId) {
        $data = array();

        if( $this->getPutnaTableReady() ) {

            if (!empty($memberId)) {
                $sql = " SELECT m.int_member_id, m.amico_id, ir.total_sale, ir.incentive_percentage ";
                $sql .= " FROM `{$this->getIncentiveReportTableName()}` ir ";
                $sql .= " INNER JOIN tbl_member m ON ir.member_id = m.int_member_id";
                $sql .= " INNER JOIN customers c ON m.int_customer_id = c.customers_id";

                $sql .= " WHERE m.int_member_id='{$memberId}' ";
                $sql .= " AND m.amico_id NOT IN ('W00888888') ";
                if($this->getIncentiveTypeColumnReady() && !empty($this->getIncentiveType()) ) {
                    $sql .= "AND ir.incentive_type='{$this->getIncentiveType()}'";
                }
                $sql .= " LIMIT 1 ";

                //echo $sql; die();

                $query = mysqli_query($this->dbConnection(), $sql);

                while ($row = mysqli_fetch_assoc($query)) {
                    $data = array(
                        'member_id' => $row['int_member_id'],
                        'amico_id' => $row['amico_id'],
                        'total_sale' => $row['total_sale'],
                        'incentive' => ($row['total_sale'] * ($row['incentive_percentage']/100) ),
                    );
                }
            }
        } else {
            $this->watchdog("Table `{$this->getIncentiveReportTableName()}` not ready", '', __FUNCTION__);
        }

        return $data;
    }

    public function getTopIncentiveReports($limit=3) {
        $data = array();

        if( $this->getPutnaTableReady() ) {

            $sql = " SELECT m.int_member_id, m.amico_id, ir.total_sale, ir.incentive_percentage, c.customers_firstname, c.customers_lastname";
            $sql .= " FROM `{$this->getIncentiveReportTableName()}` ir ";
            $sql .= " INNER JOIN tbl_member m ON ir.member_id = m.int_member_id";
            $sql .= " INNER JOIN customers c ON m.int_customer_id = c.customers_id";

            $sql .= " WHERE m.amico_id NOT IN ('W00888888') ";
            if($this->getIncentiveTypeColumnReady() && !empty($this->getIncentiveType()) ) {
                $sql .= "AND ir.incentive_type='{$this->getIncentiveType()}'";
            }
            $sql .= " ORDER BY ir.total_sale DESC ";
            if(!empty($limit)) {
                $sql .= " LIMIT $limit ";
            }

            //echo $sql; die();

            $query = mysqli_query($this->dbConnection(), $sql);

            while ($row = mysqli_fetch_assoc($query)) {
                $data[$row['int_member_id']] = array(
                    'member_id' => $row['int_member_id'],
                    'amico_id' => $row['amico_id'],
                    'firstname' => $row['customers_firstname'],
                    'lastname' => $row['customers_lastname'],
                    'total_sale' => $row['total_sale'],
                    'incentive_percentage' => $row['incentive_percentage'],
                    'incentive' => ($row['total_sale'] * ($row['incentive_percentage']/100) ),
                );
            }
        } else {
            $this->watchdog("Table `{$this->getIncentiveReportTableName()}` not ready", '', __FUNCTION__);
        }

        return $data;
    }


    public function generateTotalOrderSalesSummations()
    {

        $oldStartTime = $this->getStartTime();
        $oldEndTime = $this->getEndTime();

        $splits = $this->splitDateRange();
        //echo '<pre>'; var_dump($splits); die();

        $totalSales = array();

        if(!empty($splits)) {
            foreach($splits as $newTimeRange) {
                if(!empty($newTimeRange['start']) && !empty($newTimeRange['end']) )
                {
                    $this->setStartTime($newTimeRange['start']);
                    $this->setEndTime($newTimeRange['end']);

                    $newSales = $this->iterateThroughMembers();

                    if(!empty($newSales)) {
                        foreach($newSales as $newSale) {
                            if(!empty($newSale['total_sale'])) {
                                if(!empty( $totalSales[$newSale['member_id']] ))
                                {
                                    $totalSales[$newSale['member_id']]['total_sale'] += $newSale['total_sale'];
                                }
                                else {
                                    $totalSales[$newSale['member_id']] = $newSale;
                                }
                            }
                        }
                    }
                }
            }
        }

        //echo '<pre>'; var_dump($totalSales); die();

        return $totalSales;

    }

    public function splitDateRange()
    {

        $timeStart = strtotime($this->getStartTime());
        $timeEnd   = strtotime($this->getEndTime());
        $out       = [];
        $milestones[] = $timeStart;
        $timeEndMonth = strtotime('first day of next month midnight', $timeStart);
        while ($timeEndMonth < $timeEnd) {
            $milestones[] = $timeEndMonth;
            $timeEndMonth = strtotime('+1 month', $timeEndMonth);
        }
        $milestones[] = $timeEnd;
        $count = count($milestones);
        for ($i = 1; $i < $count; $i++) {
            $out[] = [
                'start' => date('Y-m-d H:i:s', $milestones[$i - 1]),
                'end'   => date('Y-m-d H:i:s', $milestones[$i] - 1)
            ];
        }
        return $out;
    }


    public function watchdog($msg, $sql='', $function='') {
        $message = '';
        $message .= $msg;
        $message .= PHP_EOL . PHP_EOL;
        if( !empty($sql) ) {
            $message .= "SQL Query Was: " . PHP_EOL . $sql . PHP_EOL . PHP_EOL;
        }
        if( !empty($function) ) {
            $message .= "Function Name: " . PHP_EOL . $function . PHP_EOL . PHP_EOL;
        }

        mail('omar.at.mvi.dev@gmail.com', 'Debug message from JohnAmico.com ('.__FILE__.' Cron)', $message , 'From:info@JohnAmico.com');

        echo $msg;
    }

    public function is_live_site() {
        try {
            return IS_LIVE_SITE;
        } catch (Exception $e) {
            $this->watchdog("PHP ERROR: \n\r \n\r" . $e->getMessage(), '', __FUNCTION__);
        }
    }

}