<?php
require_once("../common_files/include/global.inc");
require_once("session_check.inc");

//settings of printing
//all values are in inches

$top_margin_of_page=0.39;      
$left_margin_of_page=0.129;
$label_height=0.94;
$label_width=2.59;


$top_margin_of_page=$top_margin_of_page*77;
$left_margin_of_page=$left_margin_of_page*77;
$label_height=$label_height*77;
$label_width=$label_width*77;

$member_id = $_SESSION['member']['ses_member_id'];
$session_user = $_SESSION['member']['session_user'];

if(!empty($member_id)) {
    $a_amico = mysqli_fetch_array(mysqli_query($conn, "select amico_id from tbl_member where int_member_id='$member_id'"));
    $user_amico_id = $a_amico['amico_id'];
}

$mode = filter_var($_GET['mode'], FILTER_SANITIZE_STRING);

$output = '';

if(!empty($user_amico_id)) {
    if ($mode == 'salons') {
        $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE is_salon='yes' AND chapter_id='$user_amico_id'");
    } else {
        $query = mysqli_query($conn, "SELECT * FROM tbl_member WHERE chapter_id='$user_amico_id'");
    }


    $output .= '<html>';
    $output .= '<head>';
    $output .= '<title>Mail Labels</title>';
    $output .= '<body leftmargin="' . $left_margin_of_page . '" rightmargin="0" topmargin="' . $top_margin_of_page . '" marginwidth="0" marginheight="0">';
    $output .= '<table wdith="100%" border="0" align="left" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">';
    $output .= '<tr>';

    $i = 1;
    $cntr = 1;
    while ($f = mysqli_fetch_array($query)) {
        $customer_id = $f['int_customer_id'];
        $amico_id = $f['amico_id'];

        $output .= '<td width="' . $label_width . '" height="' . $label_height . '" align="left" style="font-size:11px; padding-left:15px;">';


        $customer_id = $f['int_customer_id'];

        $res = mysqli_query($conn, "select * from customers where customers_id='$customer_id'");
        $f = mysqli_fetch_array($res);

        $res2 = mysqli_query($conn, "select * from address_book where customers_id='$customer_id' AND address_book_id='1'");
        $f2 = mysqli_fetch_array($res2);
        if ($f2['entry_company'] != '') {
            $output .= stripslashes($f2['entry_company']);
            $output .= '<br/>';
        };

        if ($f['customers_firstname'] != '' || $f['customers_lastname'] != '') {
            $output .= stripslashes($f['customers_firstname']) . ' ' . stripslashes($f['customers_lastname']);
            $output .= '<br/>';
        };

        $res = mysqli_query($conn, "select * from address_book where customers_id='$customer_id' AND address_book_id='1'");
        $f = mysqli_fetch_array($res);
        $output .= stripslashes($f['entry_street_address']) . ' ' . stripslashes($f['entry_street_address2']);
        $postcode = $f['entry_postcode'];
        $output .= '<br/>';
        $output .= stripslashes($f['entry_city']) . ', ';

        $res = mysqli_query($conn, "select * from zones where zone_id='" . $f['entry_zone_id'] . "'");
        $f = mysqli_fetch_array($res);
        $output .= stripslashes($f['zone_name']) . ', ';
        $output .= stripslashes($postcode);
        $output .= '<br><br>';
        $output .= '</td>';

        $z = fmod($i, 3);
        if ($z == 0) {
            $output .= '</tr><tr>';
            $cntr = 1;
        };

        $z2 = fmod($i, 30);
        if ($z2 == 0) {
            $output .= '<tr><td height="40">&nbsp;</td></tr></table>

<table wdith="100%" border="0" align="left" leftmargin="0" rightmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<tr><td height="31">&nbsp;</td></tr>

<tr>

';
        };


        $cntr++;
        $i++;
    };

    $output .= '</body>';
    $output .= '</html>';


    //include_once('class.html2pdf.php');
    include_once( base_library_path() . '/mpdf/mpdf.php');

    $mpdf = new mPDF();
    //$mpdf->showStats = true;
    $mpdf->SetDisplayMode('fullpage');
    $mpdf->WriteHTML($output);

    $mpdf->Output('result.pdf', 'D');


    /*$h2p = new html2pdf( base_library_url(), base_library_path() );

    $h2p->set_screen_width(615);

    $h2p->render_html($output, "http://" . $_SERVER['SERVER_NAME']);

    //debug(true, true, $output, $h2p );

    $h2p->output_pdf("result.pdf");*/

}
