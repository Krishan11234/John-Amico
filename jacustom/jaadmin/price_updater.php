<?php
$page_name = 'Update Product Prices';
$page_title = 'John Amico - ' . $page_name;

require_once("../common_files/include/global.inc");
require_once("session_check.inc");
require_once("templates/header.php");
require_once("templates/sidebar.php");

$logs = $notFoundedSKUs = $successSKUs = $foundSKUs = $priceUpdated = array();

if ($act=="1") {
    if($_FILES['filename']['name'] != "")
    {
        $name = time() . '__' . $_FILES['filename']['name'];
        $moved = move_uploaded_file($_FILES['filename']['tmp_name'], base_path()."/temp/".$name);
        $fp = fopen(base_path()."/temp/".$name, "r");

        $row = 0;
        $fileRow = 0;

        $customerGroupPricingEnabled = is_mageways_opabs_customerpricing_enabled();

        while (($data = fgetcsv($fp, 1000, ",")) !== FALSE)
        {

            if($fileRow == 0) {
                $data = array_map('trim',$data);

                $ambPro_Column = array_search('Ambassador Pro', $data);
                $amb_Column = array_search('Ambassador', $data);
                $priceA_Column = array_search('Price Level A', $data);
                $priceB_Column = array_search('Price Level B', $data);
                $sku_Column = array_search('SKU', $data);

                if($ambPro_Column === false) { $ambPro_Column = array_search('Member Price', $data); }
                if($amb_Column === false) { $amb_Column = array_search('Ambassador Price', $data); }
            }

            //echo '<pre>'; var_dump( $fileRow, $data, $ambPro_Column, $amb_Column, $priceA_Column, $priceB_Column, $sku_Column ); die();

            // Leave the first Row for Headers

            if( $fileRow != 0 ) {

                /*
                 * stws_catalog_product_entity
                 * stws_catalog_product_entity_group_price
                 * stws_catalog_product_entity_decimal (attribute_id=75)
                 *
                 * stws_catalog_product_option_type_value (search for SKU)
                 * stws_catalog_product_option_type_price
                 *
                 */
                $num = count($data);

                if ($num > 0 && ($sku_Column !== false) ) {
                    $sku = (!empty($data[$sku_Column])) ? trim($data[$sku_Column]) : '';
                    $price_Member = (isset($data[$ambPro_Column])) ? trim(str_replace(array('$',','), '', html_entity_decode($data[$ambPro_Column]))) : false;
                    $price_A = (isset($data[$priceA_Column])) ? trim(str_replace(array('$',','), '', html_entity_decode($data[$priceA_Column]))) : false;
                    $price_B = (isset($data[$priceB_Column])) ? trim(str_replace(array('$',','), '', html_entity_decode($data[$priceB_Column]))) : false;
                    $price_ambassador = (isset($data[$amb_Column])) ? trim(str_replace(array('$',','), '', html_entity_decode($data[$amb_Column]))) : false;

                    $price_for_customerGroups = array(
                        //'B' => array('price'=>$price_B, 'group_id' => 5),
                        'AMB' => array('price'=>$price_ambassador, 'group_id' => 7),
                        'Member' => array('price'=>$price_Member, 'group_id' => 4),
                    );

                    //echo '<pre>'; var_dump( $price_Member, $price_A, $price_B ); die();

                    if( !empty($sku) ) {
                        // Update/Insert Price on the Product

                        $prodId = search_main_product_by_sku($sku);

                        if (!empty($prodId)) {

                            if( is_numeric($price_A) ) {
                                // Updating Price Level A => Main Price
                                $sql = "
                                UPDATE " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_decimal pp SET pp.value='$price_A' WHERE pp.attribute_id='75' AND pp.entity_id='$prodId'
                                ";
                                mysqli_query($conn, $sql);

                                $priceUpdated['A'][$sku] = $sku;
                                $successSKUs[$sku] = $sku;
                            }

                            if( !empty($price_for_customerGroups) ) {
                                foreach($price_for_customerGroups as $groupCode => $groupDetails) {
                                    if( !empty($groupDetails['group_id']) && !empty($groupDetails['price']) )
                                    {
                                        $groupId = $groupDetails['group_id'];
                                        $groupPrice = $groupDetails['price'];

                                        $priceLevel_sql = "SELECT value_id FROM " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price pgp WHERE pgp.customer_group_id='{$groupId}' AND pgp.entity_id='{$prodId}' ";
                                        $priceLevel_query = mysqli_query($conn, $priceLevel_sql);

                                        if (mysqli_num_rows($priceLevel_query) > 0) {
                                            $sql = "
                                            UPDATE " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price pgp SET pgp.value='{$groupPrice}' WHERE pgp.customer_group_id='{$groupId}' AND pgp.entity_id='{$prodId}'
                                            ";
                                            mysqli_query($conn, $sql);
                                        } else {
                                            $sql_b = " INSERT INTO " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price (entity_id, all_groups, customer_group_id, `value`, website_id, is_percent) VALUES ('{$prodId}',0,{$groupId},'{$groupPrice}', 0, 0)
                                            ";
                                            mysqli_query($conn, $sql_b);
                                        }

                                        $priceUpdated[$groupCode][$sku] = $sku;
                                        $successSKUs[$sku] = $sku;
                                    }
                                }
                            }
                            /*// Updating Price Level B => Group Price (Customer 5)
                            if( is_numeric($price_B) ) {
                                $priceLevelB_sql = "SELECT value_id FROM " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price pgp WHERE pgp.customer_group_id='5' AND pgp.entity_id='$prodId' ";
                                $priceLevelB_query = mysqli_query($conn, $priceLevelB_sql);

                                if (mysqli_num_rows($priceLevelB_query) > 0) {
                                    $sql = "
                                    UPDATE " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price pgp SET pgp.value='$price_B' WHERE pgp.customer_group_id='5' AND pgp.entity_id='$prodId'
                                    ";

                                    //echo $sql."<br>";
                                    mysqli_query($conn, $sql);
                                }
                                else {
                                    $sql_b = " INSERT INTO " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price (entity_id, all_groups, customer_group_id, `value`, website_id, is_percent)
                                        VALUES ('$prodId',0,5,'$price_B', 0, 0)
                                    ";

                                    mysqli_query($conn, $sql_b);
                                }

                                $priceUpdated['B'][] = $sku;
                            }


                            // Updating Price Level Member => Group Price (Customer 4)
                            if( is_numeric($price_Member) ) {
                                $priceLevel_Member_sql = "SELECT value_id FROM " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price pgp WHERE pgp.customer_group_id='4' AND pgp.entity_id='$prodId' ";
                                $priceLevel_Member_query = mysqli_query($conn, $priceLevel_Member_sql);

                                if (mysqli_num_rows($priceLevel_Member_query) > 0) {

                                    $sql = "
                                    UPDATE " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price pgp SET pgp.value='$price_Member' WHERE pgp.customer_group_id='4' AND pgp.entity_id='$prodId'
                                    ";

                                    //echo $sql."<br>";
                                    mysqli_query($conn, $sql);
                                }
                                else {
                                    $sql_a = " INSERT INTO " . MAGENTO_TABLE_PREFIX . "catalog_product_entity_group_price (entity_id, all_groups, customer_group_id, `value`, website_id, is_percent)
                                        VALUES ('$prodId',0,4,'$price_Member', 0, 0)
                                    ";

                                    mysqli_query($conn, $sql_a);
                                }

                                $priceUpdated['Member'][] = $sku;
                            }*/

                            $row++;

                        } else {
                            $notFoundedSKUs[$sku] = $sku;
                        }



                        // Update/Insert Price on the Custom Options of Product

                        $optionArr = search_product_option_by_sku($sku);

                        if (!empty($optionArr)) {

                            $prodOptionId = $optionArr['option_id'];
                            $prodOptionTypeId = $optionArr['option_type_id'];

                            if( is_numeric($price_A) ) {
                                // Updating Price Level A => Main Price
                                if( $customerGroupPricingEnabled ) {
                                    $sql = " UPDATE " . MAGENTO_TABLE_PREFIX . "mageways_optionsabsolute_customer_group_price SET price='{$price_A}' WHERE option_id='{$prodOptionId}' AND option_value_id='{$prodOptionTypeId}' AND customer_group='0' ";
                                } else {
                                    $sql = " UPDATE " . MAGENTO_TABLE_PREFIX . "catalog_product_option_type_price potp SET potp.price='{$price_A}' WHERE potp.option_type_id='{$prodOptionTypeId}' ";
                                }
                                mysqli_query($conn, $sql);

                                $priceUpdated['A'][$sku] = $sku;
                                $successSKUs[$sku] = $sku;
                            }

                            if( !empty($price_for_customerGroups) ) {
                                foreach($price_for_customerGroups as $groupCode => $groupDetails) {
                                    if( !empty($groupDetails['group_id']) && !empty($groupDetails['price']) )
                                    {
                                        $groupId = $groupDetails['group_id'];
                                        $groupPrice = $groupDetails['price'];


                                        if( $customerGroupPricingEnabled ) {
                                            $priceLevel_sql = "SELECT mop_id FROM " . MAGENTO_TABLE_PREFIX . "mageways_optionsabsolute_customer_group_price WHERE customer_group='{$groupId}' AND option_value_id='{$prodOptionTypeId}' ";
                                            $priceLevel_query = mysqli_query($conn, $priceLevel_sql);

                                            if (mysqli_num_rows($priceLevel_query) > 0) {

                                                $mopId_assoc = mysqli_fetch_assoc($priceLevel_query);
                                                $mopID = $mopId_assoc['mop_id'];

                                                $sql = " UPDATE " . MAGENTO_TABLE_PREFIX . "mageways_optionsabsolute_customer_group_price SET price='{$groupPrice}' WHERE mop_id='{$mopID}' ";
                                                mysqli_query($conn, $sql);
                                            }
                                            else {
                                                $sql_b = " INSERT INTO " . MAGENTO_TABLE_PREFIX . "mageways_optionsabsolute_customer_group_price (store_id, option_id, option_value_id, `price`, price_type, customer_group) VALUES (0, {$prodOptionId},{$prodOptionTypeId},{$groupPrice}, 'abs', {$groupId} ) ";
                                                mysqli_query($conn, $sql_b);
                                            }

                                            $priceUpdated[$groupCode][$sku] = $sku;
                                            $successSKUs[$sku] = $sku;
                                        } else {

                                            if( in_array($groupCode, array('B', 'Member')) ) {
                                                $sql = " UPDATE " . MAGENTO_TABLE_PREFIX . "catalog_product_option_type_value SET ";

                                                if($groupCode == 'B')  $sql .= "price_levelb";
                                                if($groupCode == 'Member')  $sql .= "price_member";

                                                $sql .= "'{$groupPrice}' WHERE option_type_id='{$prodOptionTypeId}' ";

                                                mysqli_query($conn, $sql);

                                                $priceUpdated[$groupCode][$sku] = $sku;
                                                $successSKUs[$sku] = $sku;
                                            }
                                        }
                                    }
                                }
                            }

                        } else {
                            $notFoundedSKUs[$sku] = $sku;
                        }


                        $foundSKUs[] = $sku;
                    }
                }
            }

            $fileRow++;
        }

        $diffSKUs = array_diff($notFoundedSKUs, $successSKUs);

        //echo '<pre>'; print_r( array( count($successSKUs), count($notFoundedSKUs), count($diffSKUs), $successSKUs, $diffSKUs)); echo '</pre>';

        fclose($fp);
        if( $row > 0 && !empty($successSKUs) ) {
            $msg = "Contents loaded. Total <strong>".count($foundSKUs)."</strong> rows (SKUs) collected from file. <br/><br/>Total <strong>".count($successSKUs)."</strong> records updated successfully.";
            $msg .= "<br/>";
            $msg .= "<ul>";
            $msg .= "<li>Price Level A updated on <strong>".count($priceUpdated['A'])."</strong> products</li>";
            $msg .= "<li>Price Level B updated on <strong>".count($priceUpdated['B'])."</strong> products</li>";
            $msg .= "<li>Price Level Ambassador updated on <strong>".count($priceUpdated['AMB'])."</strong> products</li>";
            $msg .= "<li>Price Level Ambassador Pro updated on <strong>".count($priceUpdated['Member'])."</strong> products</li>";
            $msg .= "</ul>";
        } else {
            $errorMsg = "Contents couldn't be loaded.";
        }

        if( !empty($notFoundedSKUs) ) {
            $notFoundedSKUs = array_unique($notFoundedSKUs);

            $warningMsg = "Total <strong>".count($notFoundedSKUs)." SKUs</strong> couldn't be found. List: <br/><br/><div class='row'><div class=\"col-xs-3\">" . implode('</div><div class="col-xs-3">', $notFoundedSKUs) . "</div></div>";
        }
    }
    else
    {
        $errorMsg = "File not found";
    }
}


function search_main_product_by_sku($sku) {
    global $conn;

    if(empty($sku)) {
        return false;
    }

    $sku = trim($sku);
    $prodId_sql = "SELECT entity_id FROM " . MAGENTO_TABLE_PREFIX . "catalog_product_entity WHERE sku='$sku' ";
    $prodId_query = mysqli_query($conn, $prodId_sql);

    if (mysqli_num_rows($prodId_query) > 0) {

        $prodId_assoc = mysqli_fetch_assoc($prodId_query);
        $prodId = $prodId_assoc['entity_id'];

        return $prodId;
    } else {
        $sku_parts = explode('-', $sku);
        if(count($sku_parts) > 1) {
            if(is_numeric($sku_parts[count($sku_parts)-1])) {
                unset($sku_parts[count($sku_parts) - 1]);

                $sku = implode('-', $sku_parts);

                return search_main_product_by_sku($sku);
            }
        }
    }

    return false;
}

function search_product_option_by_sku($sku, $mainProductSku='') {
    global $conn;

    if(empty($sku)) {
        return array();
    }

    $sku = trim($sku);
    $prodOptionId_sql = "SELECT otv.option_id, otv.option_type_id FROM " . MAGENTO_TABLE_PREFIX . "catalog_product_option_type_value otv  ";
    if(!empty($mainProductSku)) {
        $mainProductSku = trim($mainProductSku);
        $mainProductId = search_main_product_by_sku($mainProductSku);
        if(!empty($mainProductId)) {
            $prodOptionId_sql .= " INNER JOIN " . MAGENTO_TABLE_PREFIX . "catalog_product_option po ON po.option_id=otv.option_id";
            $prodOptionId_sql .= " WHERE otv.sku='$sku' AND po.product_id='{$mainProductId}' ";
        }
    } else {
        $prodOptionId_sql .= " WHERE otv.sku='$sku' ";
    }

    $prodOptionId_query = mysqli_query($conn, $prodOptionId_sql);

    if (mysqli_num_rows($prodOptionId_query) > 0) {

        $prodId_assoc = mysqli_fetch_assoc($prodOptionId_query);
        $prodOptionId = $prodId_assoc['option_id'];
        $prodOptionTypeId = $prodId_assoc['option_type_id'];

        return array('option_id'=>$prodOptionId, 'option_type_id'=>$prodOptionTypeId);
    } else {
        $sku_parts = explode('-', $sku);
        if(count($sku_parts) > 1) {

            $optionSku = str_replace(array('*'), '', $sku_parts[count($sku_parts)-1]);
            $optionSku = trim($optionSku);

            if(is_numeric($optionSku)) {
                $sku = $sku_parts[count($sku_parts)-1];

                unset($sku_parts[count($sku_parts) - 1]);
                $mainProductSku = implode('-', $sku_parts);

                return search_product_option_by_sku($sku, $mainProductSku);
            }
        }
    }

    return array();
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
                <form name="file_upload" class="form-bordered" action="" method="post" enctype="multipart/form-data">
                    <div class="col-xs-12 col-lg-10 col-md-10 centering">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <?php if( !empty($msg) || !empty($warningMsg) || !empty($errorMsg) ): ?>
                            <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <?php if(!empty($msg)): ?>
                                <div class="alert alert-success">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $msg; ?>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($warningMsg)): ?>
                                <div class="alert alert-warning">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $warningMsg; ?>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($errorMsg)): ?>
                                <div class="alert alert-danger">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <?php echo $errorMsg; ?>
                                </div>
                            <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="panel-body pb-lg pt-lg mb-lg mt-lg">
                            <!--<div class="form-group">
                                <label class="col-md-3 control-label">Upload File</label>
                                <div class="col-md-6">
                                    <div class="fileupload fileupload-new" data-provides="fileupload">
                                        <div class="input-append">
                                            <div class="uneditable-input">
                                                <i class="fa fa-file fileupload-exists"></i>
                                                <span class="fileupload-preview"></span>
                                            </div>
                                        <span class="btn btn-default btn-file">
                                            <span class="fileupload-exists">Change</span>
                                            <span class="fileupload-new">Select file</span>
                                            <input type="file" name="filename">
                                        </span>
                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="price_update_csv">CSV File <span class="required">*</span></label>
                                <div class="col-md-6">
                                    <input id="price_update_csv" type="file" class="form-control" name="filename" required/>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer text-center">
                            <input type="hidden" name="act" value="1">
                            <input type="submit" value="Upload" name="submit" />
                        </footer>
                    </div>
                </form>
                <div class="clearfix"></div>
            </section>
            <?php if( !empty($logs) ) {
                echo implode('<br/>', $logs);
            } ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");
