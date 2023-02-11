<?php
$page_name = 'Princess Cruise Incentive Contest';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("../common_files/include/putnaIncentiveFunction.php");
require_once("session_check.inc");


$reportGenerator = new IncentiveReportGenerator($conn, 'princess_cruise', 5);
$reportGenerator->setStartTime('2018-01-01 00:00:00')->setEndTime('2018-08-31 11:59:59');
$thisTableReady = $reportGenerator->getPutnaTableReady();


if($thisTableReady) {

    $now = gmdate("D, d M Y H:i:s");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header('Content-Type: text/x-csv');

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename=cancun_contest_report.csv");
    header("Content-Transfer-Encoding: binary");
    header("Connection: close");


    $rows = $reportGenerator->getTopIncentiveReports(0);
    $fp = fopen('php://output', 'w');

    fputcsv($fp, array('Amico ID', 'Firstname', 'Lastname', 'Total Sales', 'Incentive Percentage', 'Total Incentive'));

    if(!empty($rows)) {
        foreach($rows as $row) {
            if(!empty($row['amico_id'])) {
                $data = array(
                    'amico_id' => $row['amico_id'],
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'total_sale' => "$" . number_format($row['total_sale'], 2),
                    'incentive_percentage' => $row['incentive_percentage'] . "%",
                    'incentive' => "$" . number_format($row['incentive'], 2),
                );

                fputcsv($fp, $data);
            }
        }
    }

    fclose($fp);
}