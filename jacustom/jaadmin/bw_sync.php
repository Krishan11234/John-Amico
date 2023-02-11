<?php
$page_name = 'BW Gold Sync';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

function get_children($id, $list) {
    global $conn;

    $sql = "SELECT int_member_id FROM tbl_member WHERE int_parent_id = '$id'";
// echo $sql."<br>";
    $result = mysqli_query($conn, $sql);
// echo mysqli_num_rows($result)."<br>";
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_row($result)) {
            if (!in_array($row[0], $list)) {
                $list[] = $row[0];
                $list = get_children($row[0], $list);
            }
        }
    }

    return $list;
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

        <div class="row admin-control">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 centering">
                <div class="panel panel-primary">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center">Synchronize w/BWGold</h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 text-center pb-lg pt-lg">Click on "Synchronize" to start the synchronization process</div>
                            <div class="col-xs-12 text-center pb-lg pt-lg">
                                <button class="btn btn-primary btn-success" id="sync_but" onclick="this.disabled=true;location.href='<?php echo base_admin_url(); ?>/bw_sync.php?action=sync';">Synchronize</button>
                            </div>
                            <div class="col-xs-12">
                                <div class="table-responsive">
                                    <?php
                                    if ($action && $action == "sync") {
                                        ?>

                                        <table class="table table-bordered">
                                            <tr id="one">
                                                <td align="left">Checking for Updates and New Members</td>
                                                <td align="right" width="150">Percent Complete:</td>
                                                <td width="500">
                                                    <table class="table ">
                                                        <tr>
                                                            <td>
                                                                <table class="table table-bordered" bgcolor="green">
                                                                    <tr>
                                                                        <td id="cd_meter" width="0" height="12"></td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td><font id='cd_percent'>0%</font></td>
                                            </tr>
                                        </table>

                                        <?php
                                        echo "<script language=\"javascript\">document.getElementById('one').bgColor = '#EEEEEE';</script>\n";
                                        flush();

                                        $cd_percent = 0;
                                        $cl_percent = 0;
                                        $cml_percent = 0;
                                        $cd_percent_cnt = 0;
                                        $cl_percent_cnt = 0;
                                        $cml_percent_cnt = 0;

                                        $link = odbc_connect("AMICODB", "MANAGER", "KING");
                                        $query = "SELECT count(ID) FROM ARCustomer WHERE Name <> 'Cash Sales' AND ID LIKE 'W0%'";
                                        $result = odbc_exec($link, $query);
                                        odbc_fetch_row($result);
                                        $cd_count = odbc_result($result, 1);
                                        odbc_free_result($result);

                                        $query = "SELECT TOP 1 ID FROM ARCustomer WHERE Name <> 'Cash Sales' AND ID LIKE 'W0%' ORDER BY ID ASC";
                                        $result = odbc_exec($link, $query);
                                        odbc_fetch_row($result);
                                        $cd_start = preg_replace("/W0*/", "", odbc_result($result, 1));
                                        $cl_start = $cd_start;
                                        odbc_free_result($result);

                                        $query = "SELECT TOP 1 ID FROM ARCustomer WHERE Name <> 'Cash Sales' AND ID LIKE 'W0%' ORDER BY ID DESC";
                                        $result = odbc_exec($link, $query);
                                        odbc_fetch_row($result);
                                        $cd_end = preg_replace("/W0*/", "", odbc_result($result, 1));
                                        $cl_end = $cd_end;
                                        odbc_free_result($result);

                                        $parent_ids = array();

                                        while ($cd_start < $cd_end) {
                                            $start_id_str = "W";
                                            while (strlen($cd_start) + strlen($start_id_str) < 9) {
                                                $start_id_str .= "0";
                                            }
                                            $start_id_str .= $cd_start;

                                            $cd_start += 10000;

                                            $end_id_str = "W";
                                            while (strlen($cd_start) + strlen($end_id_str) < 9) {
                                                $end_id_str .= "0";
                                            }
                                            $end_id_str .= $cd_start;

                                            $query = "SELECT ID, FinanceContact, Name, FinanceEmail, Address1, Address2, City, State, ZIPCode, FinancePhoneNo, FinanceFaxNo, UserDef1, UserDef2, CustCatNo FROM ARCustomer WHERE Name <> 'Cash Sales' AND ID >= '$start_id_str' AND ID < '$end_id_str'";
                                            $result = odbc_exec($link, $query);

                                            while (odbc_fetch_row($result)) {
                                                $cd_percent_cnt++;
                                                $cd_percent_new = ceil(($cd_percent_cnt / $cd_count) * 100);
                                                if ($cd_percent_new != $cd_percent) {
                                                    $cd_percent = $cd_percent_new;
                                                    echo "<script language=\"javascript\">document.getElementById('cd_percent').innerHTML = '" . $cd_percent . "%';document.getElementById('cd_meter').width=$cd_percent;</script>\n";
                                                    flush();
                                                }

                                                $row = array();
                                                $row['ID'] = rtrim(str_replace("'", "\'", odbc_result($result, 1)));
                                                $row['Custom5'] = rtrim(str_replace("'", "\'", odbc_result($result, 2)));
                                                $row['Name'] = rtrim(str_replace("'", "\'", odbc_result($result, 3)));
                                                $row['FinanceEmail'] = rtrim(str_replace("'", "\'", odbc_result($result, 4)));
                                                $row['Address1'] = rtrim(str_replace("'", "\'", odbc_result($result, 5)));
                                                $row['Address2'] = rtrim(str_replace("'", "\'", odbc_result($result, 6)));
                                                $row['City'] = rtrim(str_replace("'", "\'", odbc_result($result, 7)));
                                                $row['State'] = rtrim(str_replace("'", "\'", odbc_result($result, 8)));
                                                $row['ZIPCode'] = rtrim(str_replace("'", "\'", odbc_result($result, 9)));
                                                $row['FinancePhoneNo'] = rtrim(str_replace("'", "\'", odbc_result($result, 10)));
                                                $row['FinanceFaxNo'] = rtrim(str_replace("'", "\'", odbc_result($result, 11)));
                                                $row['Custom4'] = rtrim(str_replace("'", "\'", odbc_result($result, 12)));
                                                $row['Custom3'] = rtrim(str_replace("'", "\'", odbc_result($result, 13)));
                                                $row['CustCatNo'] = rtrim(str_replace("'", "\'", odbc_result($result, 14)));

                                                $parent_ids[$row['ID']] = $row['Custom5'];

                                                $sql = "SELECT int_member_id, int_customer_id FROM tbl_member WHERE amico_id = '{$row['ID']}'";
                                                $result2 = mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");

                                                list($firstname, $lastname) = explode(" ", $row['Name']);

                                                $types = array();
                                                $types[0] = "";
                                                $types[1] = "Salon Owner";
                                                $types[2] = "Booth Rentals";
                                                $types[3] = "School Owner";
                                                $types[4] = "Stylist";
                                                $types[5] = "Student";
                                                $types[6] = "Consultant";
                                                $types[7] = "Consumer";

                                                if (mysqli_num_rows($result2) > 0) {
                                                    list($member_id, $customer_id) = mysqli_fetch_row($result2);

                                                    $sql = "UPDATE customers SET customers_firstname='$firstname', customers_lastname='$lastname', customers_email_address='{$row['FinanceEmail']}', customers_telephone='{$row['FinancePhoneNo']}', customers_fax='{$row['FinanceFaxNo']}', type='" . $types[$row['CustCatNo']] . "', customers_password='{$row['Custom4']}', ssn='{$row['Custom3']}' WHERE customers_id = '$customer_id'";
//   echo $sql."<bR>";
                                                    mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");

                                                    $sql = "SELECT zone_id FROM zones WHERE zone_code = '{$row['State']}'";
//   echo $sql."<br>";
                                                    $result3 = mysqli_query($conn, $sql);
                                                    if (mysqli_num_rows($result3) > 0) {
                                                        $zone_id = mysqli_result($result3, 0);
                                                    }
                                                    else {
                                                        $zone_id = 43;
                                                    }

                                                    $sql = "UPDATE address_book SET entry_firstname='$firstname', entry_lastname='$lastname', entry_street_address='{$row['Address1']}', entry_street_address2='{$row['Address2']}', entry_postcode='{$row['ZIPCode']}', entry_city='{$row['City']}', entry_zone_id='$zone_id' WHERE customers_id = '$customer_id'";
//   echo $sql."<bR>";
                                                    mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");
                                                }
                                                else {
                                                    $sql = "INSERT INTO customers (customers_firstname, customers_lastname, customers_email_address, customers_telephone, customers_fax, type, customers_password, ssn) VALUES ('$firstname', '$lastname', '{$row['FinanceEmail']}', '{$row['FinancePhoneNo']}', '{$row['FinanceFaxNo']}', '" . $types[$row['CustCatNo']] . "', '{$row['Custom4']}', '{$row['Custom3']}')";
//   echo $sql."<bR>";
                                                    mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");

                                                    $customers_id = mysqli_insert_id($conn);

                                                    $sql = "SELECT zone_id FROM zones WHERE zone_code = '{$row['State']}'";
//   echo $sql."<br>";
                                                    $result3 = mysqli_query($conn, $sql);
                                                    if (mysqli_num_rows($result3) > 0) {
                                                        $zone_id = mysqli_result($result3, 0);
                                                    }
                                                    else {
                                                        $zone_id = 43;
                                                    }

                                                    $sql = "INSERT INTO address_book (customers_id, address_book_id, entry_firstname, entry_lastname, entry_street_address, entry_street_address2, entry_postcode, entry_city, entry_country_id, entry_zone_id) VALUES ('$customers_id', '1', '$firstname', '$lastname', '{$row['Address1']}', '{$row['Address2']}', '{$row['ZIPCode']}', '{$row['City']}', '223', '$zone_id')";
//   echo $sql."<bR>";
                                                    mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");

                                                    $sql = "INSERT INTO address_book (customers_id, address_book_id, entry_firstname, entry_lastname, entry_street_address, entry_street_address2, entry_postcode, entry_city, entry_country_id, entry_zone_id) VALUES ('$customers_id', '2', '$firstname', '$lastname', '{$row['Address1']}', '{$row['Address2']}', '{$row['ZIPCode']}', '{$row['City']}', '223', '$zone_id')";
//   echo $sql."<bR>";
                                                    mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");

                                                    $sql = "INSERT INTO tbl_member (amico_id, int_customer_id, bit_active) VALUES ('{$row['ID']}', '$customers_id', '1')";
//   echo $sql."<bR>";
                                                    mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");

                                                    $members_id = mysqli_insert_id($conn);

                                                    $sql = "INSERT INTO tbl_member_contact_list (int_member_id, str_member_contact_list, bit_active) VALUES ('$members_id', '', '1')";
//   echo $sql."<bR>";
                                                    mysqli_query($conn, $sql) or die(mysqli_error($conn) . " - SQL: " . $sql . "<br>");
                                                }
//  echo "<br>";
                                            }
                                            odbc_free_result($result);
                                        }
                                        echo "<script language=\"javascript\">document.getElementById('one').bgColor = '#FFFFFF';</script>\n";

                                        echo "<script language=\"javascript\">document.getElementById('sync_but').disabled=false;</script>\n";
                                        ?>
                                        <br>
                                        <table class="table table-bordered" align="center">
                                            <tr>
                                                <td align="right">Synchronization Complete</td>
                                            </tr>
                                        </table>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
require_once("templates/footer.php");
