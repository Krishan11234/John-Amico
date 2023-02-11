<?php 

require_once("../common_files/include/global.inc");

/**
 * We don't need to check if admin is logged in or not.
 * We will run this script using CRON.
 */
//require_once("session_check.inc");




$calculate_commission_token_file_path = base_admin_temp_path() . "/calculate_commission";
if(!file_exists( $calculate_commission_token_file_path )) {
    exit;
}

$executor_email = file_get_contents($calculate_commission_token_file_path);
if( !filter_var( $executor_email, FILTER_VALIDATE_EMAIL ) ) {
    exit;
} else {
    unlink( $calculate_commission_token_file_path );
}


/*
$email_content_path = base_admin_path() . "/templates/commission_calculate.email.html";
$email_content = file_get_contents($email_content_path);


$executor_email = "omar@mvisolutions.com";

$headers = implode("\r\n", array(
    "MIME-Version: 1.0",
    "Content-type:text/html;charset=UTF-8",
    "From: <info@johnamico.com>",
    "Reply-To: <info@johnamico.com>",
));

mail($executor_email, "Commissions are Calculated on John Amico", $email_content, $headers );


exit();*/



$rsmember=mysqli_query($conn,"select int_member_id,int_customer_id,int_designation_id from tbl_member");
$year=0;
$maxmonth = 6;//maximum number of previous months for which commision has to be calculated
$prevmonth = (date("m")-1);

//to find the latest date to be updated
$rsdate=mysqli_query($conn,"select dtt_calculate from tbl_commision_sales_history group by(dtt_calculate) ORDER BY dtt_calculate DESC LIMIT 1");
list($dates_calculate)=mysqli_fetch_row($rsdate);
$latestdate = $dates_calculate;

//end of finding latest date
//$prevmonth=(date("m")-1);

list($latestyear,$latestmonth,$latestday) = explode('-',$latestdate);

if($prevmonth != ($latestmonth-1)){
    $startmonth = ($latestmonth-1);
}
elseif(!isset($latestdate)){//when no records in the table
    $startmonth = $prevmonth-6;
}

if(isset($startmonth)){
    if($startmonth < 0){//if month is before Jan
        $year =-1;//go to previous year
    }
    $startmonth = $startmonth+1;
    $beginmonth = $startmonth;

    while(list($memberid, $customerid,$designation) = mysqli_fetch_row($rsmember)){
        $startmonth = $beginmonth;
        for($i=$startmonth; $i<=$prevmonth; $i++){
            if($startmonth <= 0){//if month inthe previous year;
                $sql="select sum(ot.value),o.date_purchased from orders o left outer join orders_total ot on o.orders_id=ot.orders_id where ot.title='total:' and o.customers_id=$customerid and month(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))=($i+12) and year(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))=(year(now())+$year) group by(month(DATE_FORMAT(o.date_purchased, '%y-%m-%d')))";
                $rssale = mysqli_query($conn,$sql);
                $years = (date("Y")-1);
                $months = $i+12;
                $startmonth++;
            }
            else{
                $sql="select sum(ot.value),o.date_purchased from orders o left outer join orders_total ot on o.orders_id=ot.orders_id where ot.title='total:' and o.customers_id=$customerid and month(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))=$i and year(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))=year(now()) group by(month(DATE_FORMAT(o.date_purchased, '%y-%m-%d')))";
                $rssale = mysqli_query($conn,$sql);
                $years = (date("Y"));
                $months = $i;
            }

            list($salevalue,$date)=mysqli_fetch_row($rssale);
            $rschild=mysqli_query($conn,"select int_member_id from tbl_member where int_parent_id=$memberid");
            while(list($childid)=mysqli_fetch_row($rschild)){
                $salevaluecollected = getfullsales($childid,$months,$years);//recursive function which gets sales done by child members
                $salevalue += $salevaluecollected;
            }

            $comm_value += getfullcommision($memberid,$months,$years,$designation);
            $rs = mysqli_query($conn,"select int_customer_id from tbl_member where int_member_id=$memberid");

            //checking whethter he has made sales of $50
            list($customerid)=mysqli_fetch_row($rs);
            $rsorder=mysqli_query($conn,"select sum(ot.value) from orders o left outer join orders_total ot on o.orders_id=ot.orders_id where customers_id=$customerid and month(DATE_FORMAT(date_purchased, '%y-%m-%d'))=$months and year(DATE_FORMAT(date_purchased, '%y-%m-%d'))=$years and ot.title='total:' group by month(DATE_FORMAT(date_purchased, '%y-%m-%d'))");
            list($svalue)=mysqli_fetch_row($rsorder);
            if($svalue < 50){
                $comm_value = 0;
            }

            if(!isset($comm_value)){$comm_value=0;}
            if(!isset($salevalue)){$salevalue=0;}

            $today=date("Y-m-d");
            $table = "tbl_commision_sales_history";
            $in_fieldlist="int_member_id,dtt_calculate,int_commision,int_sales,int_month,int_year";
            $in_values="$memberid,'$today',$comm_value,$salevalue,$months,$years";
            $result=insert_fields($conn, $table, $in_fieldlist, $in_values);
            $salevalue=0;
            $comm_value=0;
        }
    }


    $email_content_path = base_admin_path() . "/templates/commission_calculate.email.html";
    $email_content = file_get_contents($email_content);

    $headers = implode("\r\n", array(
        "MIME-Version: 1.0",
        "Content-type:text/html;charset=UTF-8",
        "From: <info@johnamico.com>",
        "Reply-To: <info@johnamico.com>",
    ));

    mail($executor_email, "Commissions are Calculated on John Amico", $email_content, $headers );


}


//$_SESSION['admin']['calculated_members_commission'] = true;
//header("Location: ".base_admin_url()."/members.php");




//function to calculate full commision
function getfullcommision($memberid,$months,$years,$designation) {
    global $conn;

    $totalcommision=0;

    $rs = mysqli_query($conn,"select int_customer_id from tbl_member where int_member_id=$memberid");
    list($customerid)=mysqli_fetch_row($rs);
    $rsorder=mysqli_query($conn,"select orders_id from orders where int_member_id=$memberid and month(DATE_FORMAT(date_purchased, '%y-%m-%d'))=$months and  year(DATE_FORMAT(date_purchased, '%y-%m-%d'))=$years");
    for($i=0;$i<mysqli_num_rows($rsorder);$i++){
        $rsorderproducts=mysqli_query($conn,"select * from orders_products where orders_id=".mysqli_result($rsorder,$i,'orders_id'));
        for($j=0;$j<mysqli_num_rows($rsorderproducts);$j++){
            $rsproduct=mysqli_query($conn,"select p.*,c.* from products p left outer join tbl_commision_rule c on p.int_commision_rule=c.int_commision_rule_id where p.products_id=".mysqli_result($rsorderproducts,$j,'products_id'));
            if(mysqli_result($rsproduct,0,'bit_commisionable')==0){
                $totalcommision=0;
            }
            elseif(mysqli_result($rsproduct,0,'bit_commisionable')==1){
                if(mysqli_result($rsproduct,0,'bit_percentage')==1){
                    $totalcommision += ((mysqli_result($rsorderproducts,$j,'final_price')*mysqli_result($rsorderproducts,$j,'products_quantity'))*mysqli_result($rsproduct,0,'int_value')/100);
                }
                else{
                    $totalcommision += (mysqli_result($rsorderproducts,$j,'products_quantity')*mysqli_result($rsproduct,0,'int_value'));
                }
            }
        }
    }

    $totalcommision += get_child_fullcommision($memberid,$months,$years,$designation);

    //echo "Commission::  $memberid :  $totalcommision<br/>";

    /*$rschild = mysqli_query($conn,"select int_member_id from tbl_member where int_parent_id=$memberid");
    if(mysqli_num_rows($rschild)>0){
        while (list($childmemberid)=mysqli_fetch_row($rschild)){
            $totalcommision += getfullcommision($childmemberid,$months,$years,$designation);//for totaling the sales of all the children
        }
    }*/
    return $totalcommision;
}

function get_child_fullcommision($memberid,$months,$years,$designation) {
    global $conn;

    $totalcommision = 0;

    $rschild = mysqli_query($conn,"select int_member_id from tbl_member where int_parent_id=$memberid");
    if(mysqli_num_rows($rschild)>0){
        while (list($childmemberid)=mysqli_fetch_row($rschild)){
            $totalcommision += get_parentchild_fullcommision($childmemberid,$months,$years,$designation);//for totaling the sales of all the children
        }
    }

    return $totalcommision;
}

function get_parentchild_fullcommision($memberid,$months,$years,$designation) {
    global $conn;

    $totalcommision=0;

    $rs = mysqli_query($conn,"select int_customer_id from tbl_member where int_member_id=$memberid");
    list($customerid)=mysqli_fetch_row($rs);
    $rsorder=mysqli_query($conn,"select orders_id from orders where int_member_id=$memberid and month(DATE_FORMAT(date_purchased, '%y-%m-%d'))=$months and  year(DATE_FORMAT(date_purchased, '%y-%m-%d'))=$years");
    for($i=0;$i<mysqli_num_rows($rsorder);$i++){
        $rsorderproducts=mysqli_query($conn,"select * from orders_products where orders_id=".mysqli_result($rsorder,$i,'orders_id'));
        for($j=0;$j<mysqli_num_rows($rsorderproducts);$j++){
            $rsproduct=mysqli_query($conn,"select p.*,c.* from products p left outer join tbl_commision_rule c on p.int_commision_rule=c.int_commision_rule_id where p.products_id=".mysqli_result($rsorderproducts,$j,'products_id'));
            if(mysqli_result($rsproduct,0,'bit_commisionable')==0){
                $totalcommision=0;
            }
            elseif(mysqli_result($rsproduct,0,'bit_commisionable')==1){
                if(mysqli_result($rsproduct,0,'bit_percentage')==1){
                    $totalcommision += ((mysqli_result($rsorderproducts,$j,'final_price')*mysqli_result($rsorderproducts,$j,'products_quantity'))*mysqli_result($rsproduct,0,'int_value')/100);
                }
                else{
                    $totalcommision += (mysqli_result($rsorderproducts,$j,'products_quantity')*mysqli_result($rsproduct,0,'int_value'));
                }
            }
        }
    }

    return $totalcommision;
}


//function to calculate full sales
function getfullsales($memberid,$months,$years) {
    global $conn;

    $totalsale = 0;

    $rs = mysqli_query($conn,"select int_customer_id,int_designation_id from tbl_member where int_member_id=$memberid");
    if(mysqli_num_rows($rs)>0){
        list($customerid,$designation)=mysqli_fetch_row($rs);
        $rssale = mysqli_query($conn,"select sum(ot.value) from orders o left join orders_total ot  on o.orders_id=ot.orders_id where o.int_member_id=$memberid and ot.title='total:' and month(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))='$months' and  year(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))='$years' group by(month(DATE_FORMAT(o.date_purchased, '%y-%m-%d')))");
        if(mysqli_num_rows($rssale)>0){
            list($salevalue)=mysqli_fetch_row($rssale);
            $totalsale += $salevalue;
        }

        $totalsale += get_child_fullsales($memberid,$months,$years);

    }

    //echo "Sales::  $memberid :  $totalsale<br/>";

    return $totalsale;
}

function get_child_fullsales($memberid,$months,$years) {
    global $conn;

    $totalsale = 0;

    $rschild = mysqli_query($conn,"select int_member_id from tbl_member where int_parent_id=$memberid");
    if(mysqli_num_rows($rschild)>0){
        while (list($childmemberid)=mysqli_fetch_row($rschild)){
            $totalsale += get_parentchild_fullsales($childmemberid,$months,$years)	;//for totaling the sales of all the children
        }
    }

    return $totalsale;
}

function get_parentchild_fullsales($memberid,$months,$years) {
    global $conn;

    $totalsale = 0;

    $rs = mysqli_query($conn,"select int_customer_id,int_designation_id from tbl_member where int_member_id=$memberid");
    if(mysqli_num_rows($rs)>0){
        list($customerid,$designation)=mysqli_fetch_row($rs);
        $rssale = mysqli_query($conn,"select sum(ot.value) from orders o left join orders_total ot  on o.orders_id=ot.orders_id where o.int_member_id=$memberid and ot.title='total:' and month(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))='$months' and  year(DATE_FORMAT(o.date_purchased, '%y-%m-%d'))='$years' group by(month(DATE_FORMAT(o.date_purchased, '%y-%m-%d')))");
        if(mysqli_num_rows($rssale)>0){
            list($salevalue)=mysqli_fetch_row($rssale);
            $totalsale += $salevalue;
        }
    }

    return $totalsale;
}