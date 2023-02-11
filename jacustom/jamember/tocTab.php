<?php

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

function maketree($parentno,$memberid){
    global $conn;

    $rsmember=mysqli_query($conn,"select m.int_member_id, m.int_designation_id, m.int_customer_id, REPLACE(c.customers_firstname, ',', '[comma]') as customers_firstname, REPLACE(c.customers_lastname, ',', '[comma]') as customers_lastname from tbl_member m left outer join customers c on m.int_customer_id=c.customers_id where m.int_member_id=$memberid");
    if(mysqli_num_rows($rsmember)>0){
        $treearray = !empty($treearray) ? $treearray : '';

        list($memberid,$designation,$customerid,$firstname,$lastname)=mysqli_fetch_row($rsmember);
        $place=explode(".",$parentno);
        $parentno="";
        $parentno=$place[0];
        for($i=1;$i<(count($place)-1);$i++){
            $parentno=$parentno.'.'.$place[$i];
        }
        if(count($place)==1) {
            $parentno = $parentno . '.1';
        } else {
            $parentno = $parentno . '.' . ($place[count($place) - 1] + 1);
        }
        $treearray .= $parentno.','.$firstname.' '.$lastname.' ,'.$memberid.',';
        $rschild=mysqli_query($conn,"select m.int_member_id, m.int_designation_id, m.int_customer_id, REPLACE(c.customers_firstname, ',', '[comma]') as customers_firstname, REPLACE(c.customers_lastname, ',', '[comma]') as customers_lastname from tbl_member m left outer join customers c on m.int_customer_id=c.customers_id where m.int_parent_id=$memberid");
        $place=$parentno.".0";
        while(list($memberid,$designation,$customerid,$firstname,$lastname)=mysqli_fetch_row($rschild)){
            $treearray=$treearray.maketree($place,$memberid,$treearray);
            $parentno=$place;
            $place=explode(".",$parentno);
            $parentno="";
            $parentno=$place[0];
            for($i=1;$i<(count($place)-1);$i++){
                $parentno=$parentno.'.'.$place[$i];
            }
            if(count($place)==1)
                $parentno=$parentno.'.1';
            else
                $parentno=$parentno.'.'.($place[count($place)-1] + 1);
            $place=$parentno;
        }
    }
    return $treearray;
}

$rsmember=mysqli_query($conn,"select m.int_member_id, m.int_designation_id, m.int_customer_id, REPLACE(c.customers_firstname, ',', '[comma]') as customers_firstname, REPLACE(c.customers_lastname, ',', '[comma]') as customers_lastname from tbl_member m left outer join customers c on m.int_customer_id=c.customers_id where int_member_id=$_SESSION[ses_member_id]");
list($memberid1,$designation1,$customerid1,$firstname1,$lastname1)=mysqli_fetch_row($rsmember);
$treearray='0,'.$firstname1.' '.$lastname1.','.$memberid1.',';
//$treearray='0,shehran,"httlp",';
$rschildmembers=mysqli_query($conn,"select m.int_member_id, m.int_designation_id, m.int_customer_id, REPLACE(c.customers_firstname, ',', '[comma]') as customers_firstname, REPLACE(c.customers_lastname, ',', '[comma]') as customers_lastname from tbl_member m left outer join customers c on m.int_customer_id=c.customers_id where int_parent_id=$_SESSION[ses_member_id]");
$mainparentno=1;
while(list($memberid,$designation,$customerid,$firstname,$lastname)=mysqli_fetch_row($rschildmembers)){
    $treearray=$treearray.$mainparentno.','.$firstname.' '.$lastname.' ,'.$memberid.',';
    $rschild=mysqli_query($conn,"select m.int_member_id, m.int_designation_id, m.int_customer_id, REPLACE(c.customers_firstname, ',', '[comma]') as customers_firstname, REPLACE(c.customers_lastname, ',', '[comma]') as customers_lastname from tbl_member m left outer join customers c on m.int_customer_id=c.customers_id where int_parent_id=$memberid");
    $parentno=$mainparentno;
    $place=$parentno.".0";
    while(list($memberid,$designation,$customerid,$firstname,$lastname)=mysqli_fetch_row($rschild)){
        $treearray=$treearray.maketree($parentno,$memberid,$treearray);
        $place1=explode(".",$parentno);
        $parentno="";
        $parentno=$place1[0];
        for($i=1;$i<(count($place1)-1);$i++){
            $parentno=$parentno.'.'.$place1[$i];
        }
        if(count($place1)==1)
            $parentno=$parentno.'.1';
        else
            $parentno=$parentno.'.'.($place1[count($place1)-1] + 1);
    }
    $mainparentno=($mainparentno+1);
}
//echo $treearray;
$treearray=substr($treearray,0,(strlen($treearray)-1));
$arr=explode(",",$treearray);
for($i=0,$k=0;$i<count($arr);$i=$i+3,$k++){
    $strar[$k]=array($arr[$i],str_replace("[comma]", ",", $arr[$i+1]),$arr[$i+2]);

}
$tocTab = $strar;
$nCols = 20;