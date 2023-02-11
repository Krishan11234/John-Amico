<?php
//debug(false, false, $_POST);
require_once( base_admin_path() . '/act_members.php' );
?>

<script>
    var shfields = {}, checkfields = {};

    function pop(url,w,h) {
        var p = window.open(url,
            "pop","width="+w+",height="+h+",alwaysLowered=0,alwaysRaised=1,channelmode=0,directories=0,fullscreen=0,hotkeys=1,location=0,menubar=0,resizable=1,scrollbars=1,status=1,titlebar=1,toolbar=0,z-lock=0");
        if( p.opener == null ) p.opener = window;
    }

    function copyBillingToShipping(checkbox) {

        jQuery('form.member_addedit .form-control').each(function(){
            var nameProp = $(this).prop('name');
            if (nameProp.toLowerCase().indexOf("sh_") >= 0) {
                if($(checkbox).is(':checked') ) {

                    jQuery('[name="shiped"]').val(1);

                    var mainFieldName = nameProp.split('sh_')[1];
                    var mainFieldValue = jQuery('[name="'+mainFieldName+'"]').val();

                    shfields[nameProp] = $(this).val();
                    $(this).val( mainFieldValue );

                } else {
                    jQuery('[name="shiped"]').val(0);

                    if( shfields.hasOwnProperty(nameProp) ) {
                        jQuery('[name="'+nameProp+'"]').val(shfields[nameProp]);
                    }
                }
            }
        });
    }

    function copyBillingToCheckAddress(checkbox){

        jQuery('form.member_addedit .form-control').each(function(){
            var nameProp = $(this).prop('name');
            if (nameProp.toLowerCase().indexOf("check_") >= 0) {
                if($(checkbox).is(':checked') ) {
                    var mainFieldName = nameProp.split('check_')[1];
                    var mainFieldValue = jQuery('[name="' + mainFieldName + '"]').val();

                    checkfields[nameProp] = $(this).val();
                    $(this).val(mainFieldValue);

                } else {
                    if( checkfields.hasOwnProperty(nameProp) ) {
                        jQuery('[name="'+nameProp+'"]').val(checkfields[nameProp]);
                    }
                }
            }
        });
    }

    jQuery(document).ready(function($){
        $('.switch .ios-switch').click(function(){
            alert(1);
            if( jQuery(this).hasClass('on') ) {
                jQuery(this).parents('.switch').children('input[type="checkbox"]').attr('checked', 'checked');
            } else {
                jQuery(this).parents('.switch').children('input[type="checkbox"]').removeAttr('checked');
            }
        });
    });

    <?php
    $sql = "SELECT c.*, tm.int_member_id, tm.int_customer_id, tm.amico_id
            FROM tbl_member tm
            INNER JOIN customers c ON tm.int_customer_id=c.customers_id
    ";
    if(in_array($mtype, array('m', 'a'))) {
        $sql .= "WHERE  tm.mtype IN ('m', 'a') and tm.amico_id!='' ORDER BY c.customers_lastname ";
    }

    $result = mysqli_query($conn,$sql);
    $count = 0;

    $amico_ids = $member_ids = $member_names = "";
    while($row = mysqli_fetch_array($result)) {
        $amico_ids .= "\"".strtolower($row['amico_id'])."\",";
        $member_ids .= "\"".$row['int_member_id']."\",";
        $member_names .= "\"".addslashes($row['customers_firstname'])." ".addslashes($row['customers_lastname'])."\",";
    }
    $amico_ids = substr($amico_ids, 0, strlen($amico_ids)-1);
    $member_ids = substr($member_ids, 0, strlen($member_ids)-1);
    $member_names = substr($member_names, 0, strlen($member_names)-1);
    ?>

    function find_member(t, o, i) {
        var amico_ids = new Array(<?php echo $amico_ids; ?>);
        var member_ids = new Array(<?php echo $member_ids; ?>);
        var member_names = new Array(<?php echo $member_names?>);
        var str = t.value.toLowerCase();
        var found = false;

        for(count=0;count<amico_ids.length;count++) {
            if(str == amico_ids[count]) {
                found = true;
                o.innerHTML = member_names[count];
                i.value = member_ids[count];
                ///document.getElementById('subbut').disabled = false;
                break;
            }
        }

        if(!found) {
            o.innerHTML = "Please Enter a Valid Member ID";
            i.value = '1';
            //document.getElementById('subbut').disabled = true;
        }

        return;
    }

    <?php
    $sql = "SELECT c.*, tm.int_member_id, tm.int_customer_id, tm.amico_id FROM tbl_member tm INNER JOIN customers c ON tm.int_customer_id=c.customers_id WHERE  tm.mtype='e' and tm.amico_id!='' ORDER BY c.customers_lastname";
    $result = mysqli_query($conn,$sql);
    $count = 0;

    $amico_ids = $member_ids = $member_names = "";
    while($row = mysqli_fetch_array($result)) {
        $amico_ids .= "\"".strtolower($row['amico_id'])."\",";
        $member_ids .= "\"".$row['amico_id']."\",";
        $member_names .= "\"".addslashes($row['customers_firstname'])." ".addslashes($row['customers_lastname'])."\",";
    }
    $amico_ids = substr($amico_ids, 0, strlen($amico_ids)-1);
    $member_ids = substr($member_ids, 0, strlen($member_ids)-1);
    $member_names = substr($member_names, 0, strlen($member_names)-1);
    ?>

    function find_ec_id(t, o, i) {
        var amico_ids2 = new Array(<?=$amico_ids?>);
        var member_ids2 = new Array(<?=$member_ids?>);
        var member_names2 = new Array(<?=$member_names?>);
        var str = t.value.toLowerCase();
        var found = false;

        for(count=0;count<amico_ids2.length;count++) {
            if(str == amico_ids2[count]) {
                found = true;
                o.innerHTML = member_names2[count];
                i.value = member_ids2[count];
                ///document.getElementById('subbut').disabled = false;
                break;
            }
        }

        if(!found) {
            o.innerHTML = "Please Enter a Valid EC ID";
            i.value = '1';
            //document.getElementById('subbut').disabled = true;
        }

        return;
    }

</script>

<div class="add_edit_member_type_wrapper">

    <?php
    if(!empty($_POST) ) {
    //if(!empty($_POST) && empty($success_message) ) {
        extract($_POST);
        if( !empty($_POST['stuff']) ) { $referrer_amico_id = $_POST['stuff']; }
        if( !empty($_POST['stuff2']) ) { $ec_id = $_POST['stuff2']; }
        if( !empty($_POST['stuff2_new']) ) { $new_ec_id = $_POST['stuff2_new']; }
        if( !empty($_POST['expire_custom_comission']) ) { $expire_custom_comission = $_POST['expire_custom_comission']; }
    }

    //echo '<pre>'; var_dump( $expire_custom_comission, $_POST['expire_custom_comission'], !empty($expire_custom_comission) && strpos($expire_custom_comission, '-') ); die();
    if( !empty($expire_custom_comission) && strpos($expire_custom_comission, '-') ) {
        $strtoTime_expireCommission = strtotime( $expire_custom_comission );
        $expire_custom_comission = date('m/d/Y', $strtoTime_expireCommission);
    }

    ?>

    <?php if($members_page) : ?>
    <section class="panel">
        <div class="panel-body text-center">
            <form action="<?php echo base_admin_url() . "/member_errors.php"; ?>" method="post">
                <input type="hidden" name="memberid" value="<?php echo ( !empty($memberid) ? $memberid : '' ); ?>">
                <input type="hidden" name="redirectto" value="<?php echo base_admin_url() . "/members.php" ?>" />
                <input type="submit" name="error_comment" value="Errors / Comments" class="command">
            </form>
        </div>
    </section>
    <?php endif;?>

    <?php
    //echo '<pre>'; print_r( array($_POST, $_SESSION['member_form_errors']) ); echo '</pre>'; //die();
    ?>

    <?php if( !empty($_SESSION['member_form_errors']) ) : ?>
        <div class="message">
            <div class="alert alert-danger"><ul><li><?php echo implode('</li><li>', $_SESSION['member_form_errors']); ?></li></ul></div>
        </div>
        <?php unset( $_SESSION['member_form_errors'] ); ?>
    <?php endif ?>
    <?php if( !empty($success_message) ) : ?>
        <div class="message">
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        </div>
    <?php endif ?>

    <!--<form name="theform" class="form-bordered member_addedit" action="<?php /*echo $action_page_url; */?>" method="post">-->
    <form name="theform" class="form-bordered member_addedit" action="" method="post">
        <input type="hidden" name="memberid" value="<?php echo ( !empty($memberid) ? $memberid : '' ); ?>">
        <input type="hidden" name="cc_id" value="<?php echo ( !empty($cc_id) ? $cc_id : '' ); ?>">
        <input type="hidden" name="customerid" value="<?php echo ( !empty($customerid) ? $customerid : '' ); ?>">
        <input type="hidden" name="mtype" value="<?php echo $mtype?>">

        <input type="hidden" name="alpabet" value="<?php $alpabet; ?>">
        <input type="hidden" name="designations" value="<?php $designations; ?>">
        <input type="hidden" name="sort" value="<?php $sort; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">

        <header class="panel-heading">
            <h2 class="panel-title text-center"><?php echo ( !empty($is_edit) ? 'Edit' : 'Add New' ); ?> <?php echo $member_type_name; ?></h2>
        </header>
        <div class="panel-body">
            <?php if( $is_edit ) : ?>
                <div class="form-group no-border-bottom">
                    <label class="col-md-4 control-label">Amico ID</label>
                    <div class="col-lg-8 ">
                        <?php if( (in_array($mtype, array('m', 'a'))) && !empty($amigo_id) ) : ?>
                            <p class="form-control-static remove_fcs_padding"><strong><?php echo $amigo_id; ?></strong></p>
                        <?php elseif( in_array($mtype, array('e', 'c')) && !empty($amico_id_xyz) ) : ?>
                            <p class="form-control-static remove_fcs_padding"><strong><?php echo $amico_id_xyz; ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else : ?>
                <?php if( in_array($mtype, array('m', 'a')) ) : ?>
                    <div class="form-group no-border-bottom">
                        <label class="col-md-4 control-label">Next Amico ID</label>
                        <div class="col-lg-8 ">
                            <?php $nextAmicoId = get_next_amico_id($mtype); ?>
                            <input type="hidden" name="amigo_id" value="<?php echo $nextAmicoId; ?>" />
                            <p class="form-control-static remove_fcs_padding"><strong><?php echo $nextAmicoId; ?></strong></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="contact_billing_fields_section">
                <div class="form-group heading text-center no-border-bottom"><p>Contact Billing Information</p></div>
                <div class="forms-group contact_billing_fields">
                    <?php if( in_array($mtype, array('m', 'a')) ) : ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Chapter ID</label>
                            <div class="col-lg-8 ">
                                <p class="form-control-static"><strong><?php echo found_chapter($postcode); ?></strong></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="stuff">Referring Member's ID:</label>
                            <div class="col-md-8 form-inline">
                                <input type="text" class="form-control" name="stuff" id="stuff" maxlength="20" required value="<?php echo ( !empty($referrer_amico_id) ? $referrer_amico_id : '' ); ?>" onKeyUp="find_member(this, document.getElementById('member_name'), this.form.refer_member_id);" >

                                <input type="hidden" name="refer_member_id" value="<?php echo ( !empty($refer_member_id) ? $refer_member_id : '' ); ?>">
                                <p class="form-control-static">
                                    <?php if( !empty($refer_member_id) ) { ?>
                                        <strong id="member_name"><?php if(!empty($referrer_first_name)) echo $referrer_first_name; ?> <?php if(!empty($referrer_last_name)) echo $referrer_last_name; ?></strong>
                                    <?php } else { ?>
                                        <strong id="member_name">Please Enter a Valid Member ID</strong>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 col-xs-12 control-label">Join Date</label>
                            <div class="col-lg-8 col-xs-12">
                                <!--<p class="form-control-static remove_fcs_padding"><strong><?php /*echo ( (!empty($ec_join_date)) ? $ec_join_date : '' ); */?></strong></p>-->
                                <p class="form-control-static remove_fcs_padding"><strong><?php echo ( (!empty($mem_join_data)) ? $mem_join_data : 'N/A' ); ?></strong></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="stuff2">EC's ID</label>
                            <div class="col-md-8 form-inline">
                                <input type="text" class="form-control" name="stuff2" id="stuff2" maxlength="20" value="<?php echo ( empty($ec_id) ? '' : $ec_id ); ?>" onKeyUp="find_ec_id(this, document.getElementById('ec_name'), this.form.ec_member_id);" >
                                <input type="hidden" name="ec_member_id" value="<?php echo ( !empty($ec_member_id) ? $ec_member_id : '' ); ?>">
                                <p class="form-control-static">
                                    <?php if( !empty($is_edit) ) { ?>
                                        <?php if( isset($ec_id) ) { ?>
                                            <?php if($ec_id>0) { ?>
                                                <strong id="ec_name"><?php if(!empty($ec_first_name)) echo $ec_first_name; ?> <?php if(!empty($ec_last_name)) echo $ec_last_name; ?></strong>
                                            <?php } elseif($ec_id == 0) { ?>
                                                <strong id="ec_name">dead accounts</strong>
                                            <?php } ?>
                                        <?php  } ?>
                                    <?php } else { ?>
                                        <strong id="ec_name">Please Enter a Valid EC ID</strong>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="stuff2_new">NEW 100 days EC's ID</label>
                            <div class="col-md-8 form-inline">
                                <input type="text" class="form-control" name="stuff2_new" id="stuff2_new" maxlength="20" value="<?php echo ( empty($new_ec_id) ? '' : $new_ec_id ); ?>" onKeyUp="find_ec_id(this, document.getElementById('new_ec_name'), this.form.new_ec_member_id);" >
                                <input type="hidden" name="new_ec_member_id" value="<?php echo ( !empty($new_ec_member_id) ? $new_ec_member_id : '' ); ?>">
                                <p class="form-control-static">
                                    <?php if( !empty($is_edit) ) { ?>
                                        <?php if( isset($new_ec_id) ) { ?>
                                            <?php if($new_ec_id>0) { ?>
                                                <strong id="new_ec_name"><?php if(!empty($new_ec_first_name)) echo $new_ec_first_name; ?> <?php if(!empty($new_ec_last_name)) echo $new_ec_last_name; ?></strong>
                                            <?php } elseif($new_ec_id == 0) { ?>
                                                <strong id="new_ec_name">dead accounts</strong>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <strong id="new_ec_name">Please Enter a Valid EC ID</strong>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                        <?php //if(!is_in_live()): ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="bit_ja_mobileapp_active">JA Mobile App</label>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="col-md-4 col-xs-4 control-label" for="bit_ja_mobileapp_active">Enabled</label>
                                    <div class="col-md-8 col-xs-8">
                                        <input type="checkbox" name="bit_ja_mobileapp_active" class="" value="1" id="bit_ja_mobileapp_active" <?php echo ( !empty($bit_ja_mobileapp_active) ? 'checked="checked"' : '' ); ?> >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php //endif; ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="bit_no_purchase_required">No purchase required</label>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="col-md-4 col-xs-4 control-label" for="bit_no_purchase_required">Enabled</label>
                                    <div class="col-md-8 col-xs-8">
                                        <input type="checkbox" name="bit_no_purchase_required" class="" value="1" id="bit_no_purchase_required" <?php echo ( !empty($bit_no_purchase_required) ? 'checked="checked"' : '' ); ?> >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="bit_custom_comission">Custom commission 20%</label>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="col-md-4 col-xs-4 control-label" for="bit_custom_comission">Enabled</label>
                                    <div class="col-md-8 col-xs-8">
                                        <input type="checkbox" name="bit_custom_comission" class="" value="1" id="bit_custom_comission" <?php echo ( !empty($bit_custom_comission) ? 'checked="checked"' : '' ); ?> >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 col-xs-4 control-label" for="expire_custom_comission">Expire On</label>
                                    <div class="col-md-8 col-xs-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" data-plugin-datepicker="" value="<?php echo ( !empty($expire_custom_comission) ? $expire_custom_comission : '' ); ?>" class="form-control" name="expire_custom_comission" id="expire_custom_comission" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if( $is_add && in_array($mtype, array('e', 'c')) ) { ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="amico_id_xyz">Amico ID</label>
                            <div class="col-lg-8 ">
                                <input type="text" class="form-control" name="amico_id_xyz" id="amico_id_xyz" maxlength="20" required value="<?php echo ( !empty($amico_id_xyz) ? $amico_id_xyz : '' ); ?>" >
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="title">Name</label>
                        <div class="col-md-8">
                            <div class="row form-group">
                                <div class="col-lg-4">
                                    <select name="title" id="title" class="form-control" required>
                                        <option>Title</option>
                                        <option value="Mr." <?php echo ( (!empty($title) && ($title == 'Mr.') ) ? 'selected' : '' ); ?> >Mr</option>
                                        <option value="Miss." <?php echo ( (!empty($title) && ($title == 'Miss.') ) ? 'selected' : '' ); ?> >Miss</option>
                                        <option value="Mrs." <?php echo ( (!empty($title) && ($title == 'Mrs.') ) ? 'selected' : '' ); ?> >Mrs</option>
                                    </select>
                                </div>
                                <div class="mb-md hidden-lg hidden-xl"></div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="firstname" name="firstname" maxlength="20" required placeholder="First Name" value="<?php echo ( !empty($firstname) ? $firstname : '' ); ?>">
                                </div>
                                <div class="mb-md hidden-lg hidden-xl"></div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="lastname" name="lastname" maxlength="20" required placeholder="Last Name" value="<?php echo ( !empty($lastname) ? $lastname : '' ); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if( in_array($mtype, array('e', 'c')) ) { ?>
                        <?php if( in_array($mtype, array('e')) ) { ?>
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="growth">Growth</label>
                                <div class="col-lg-8 form-inline">
                                    <input type="text" class="form-control" name="growth" id="growth" maxlength="5" size="5" value="<?php echo ( !empty($growth) ? $growth : '' ); ?>" >
                                </div>
                            </div>
                        <?php } ?>
                        <?php if( in_array($mtype, array('c')) ) { ?>
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="growth">Population</label>
                                <div class="col-lg-8 form-inline">
                                    <input type="text" class="form-control" name="growth" id="growth" maxlength="5" size="5" value="<?php echo ( !empty($growth) ? $growth : '' ); ?>" >
                                    <?php if ( $is_edit && (empty($growth) || ($growth!='xxx')) ) {?>
                                        <p class="form-control-static remove_fcs_padding"><strong>
                                            <a href="#" onClick="javasript:pop('_upload.php?amico_id=<?php echo ( !empty($amico_id_xyz) ? $amico_id_xyz : ''); ?>', 400, 200);">[upload salons]</a>
                                        </strong></p>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="contest">Contest</label>
                            <div class="col-lg-8 form-inline">
                                <input type="text" class="form-control" name="contest" id="contest" maxlength="5" size="5" value="<?php echo ( !empty($contest) ? $contest : '' ); ?>" >
                            </div>
                        </div>

                    <?php } ?>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="email">Email Address</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="email" id="email" maxlength="100" required value="<?php echo ( !empty($email) ? $email : '' ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="streetadd">Address</label>
                        <div class="col-md-8">
                            <div class="row form-group">
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="streetadd" name="streetadd" maxlength="30" required placeholder="Street Address 1" value="<?php echo ( !empty($streetadd) ? $streetadd : '' ); ?>">
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="streetadd_two" name="streetadd_two" maxlength="40" placeholder="Street Address 2" value="<?php echo ( !empty($streetadd_two) ? $streetadd_two : '' ); ?>">
                                </div>
                                <div class="pb-xs hidden-md hidden-xs hidden-sm">&nbsp;</div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="city" name="city" maxlength="20" required placeholder="City" value="<?php echo ( !empty($city) ? $city : '' ); ?>">
                                </div>
                                <div class="col-lg-4">
                                    <select name="zone" class="form-control" required>
                                        <option>Select State</option>
                                        <?php
                                        $rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
                                        while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)){
                                            if( !empty($zone) && ($i_zoneid==$zone) )
                                                echo '<option value ='.$i_zoneid.' selected>'.$s_zone.'</option>';
                                            else
                                                echo '<option value ='.$i_zoneid.' >'.$s_zone.'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="postcode" name="postcode" maxlength="20" required placeholder="Post Code" value="<?php echo ( !empty($postcode) ? $postcode : '' ); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="phone">Phone Number</label>
                        <div class="col-md-8 form-inline">
                            <input type="text" class="form-control" name="phone" id="phone" maxlength="40" required value="<?php echo ( !empty($phone) ? $phone : '' ); ?>">
                        </div>
                    </div>
                    <?php if( in_array($mtype, array('e')) ) { ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="phone1">Phone Number #2</label>
                            <div class="col-md-8 form-inline">
                                <input type="text" class="form-control" name="phone1" id="phone1" maxlength="40" value="<?php echo ( !empty($phone1) ? $phone1 : '' ); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="phone2">Phone Number #3</label>
                            <div class="col-md-8 form-inline">
                                <input type="text" class="form-control" name="phone2" id="phone2" maxlength="40" value="<?php echo ( !empty($phone2) ? $phone2 : '' ); ?>">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="mobile_phone">Mobile Number</label>
                        <div class="col-md-8 form-inline">
                            <input type="text" class="form-control" name="mobile_phone" id="mobile_phone" maxlength="20" value="<?php echo ( !empty($mobile_phone) ? $mobile_phone : '' ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="operator">Carrier</label>
                        <div class="col-md-8 form-inline">
                            <select name="operator" class="form-control">
                                <option value = 0 >Select Operator</option>
                                <?php
                                $rs_operator = mysqli_query($conn,"select id, operator from mobile_operators order by operator");
                                while(list($op_id, $operat)= mysqli_fetch_row($rs_operator)){

                                    if( !empty($operator_id) && ($op_id==$operator_id) )
                                        echo '<option value ='.$op_id.' selected>'.$operat.'</option>';
                                    else
                                        echo '<option value ='.$op_id.' >'.$operat.'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="fax">FAX Number</label>
                        <div class="col-md-8 form-inline">
                            <input type="text" class="form-control" name="fax" id="fax" maxlength="40" value="<?php echo ( !empty($fax) ? $fax : '' ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="ssn">Social Security Number</label>
                        <div class="col-md-8 form-inline">
                            <input type="text" class="form-control" name="ssn" id="ssn" maxlength="11" value="<?php echo ( !empty($ssn) ? $ssn : '' ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="license_number">License Number</label>
                        <div class="col-md-8 form-inline">
                            <input type="text" class="form-control" name="license_number" id="license_number" maxlength="20" value="<?php echo ( !empty($license_number) ? $license_number : '' ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="type">Type</label>
                        <div class="col-md-8 form-inline">
                            <?php $type = ( (!empty($type) ) ? $type : '' );?>
                            <select name="type" id="type" class="form-control" required>
                                <option value="0">Please Select</option>
                                <option value="Booth Rental"<?if($type == "Booth Rental"){?> SELECTED<?}?>>Booth Rental</option>
                                <option value="Consultant"<?if($type == "Consultant"){?> SELECTED<?}?>>Consultant</option>
                                <option value="Salon Owner"<?if($type == "Salon Owner"){?> SELECTED<?}?>>Salon Owner</option>
                                <option value="School Owner"<?if($type == "School Owner"){?> SELECTED<?}?>>School Owner</option>
                                <option value="Stylist"<?if($type == "Stylist"){?> SELECTED<?}?>>Stylist</option>
                            </select>
                        </div>
                    </div>
                    <?php if( in_array($mtype, array('m', 'a')) ) { ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="nickname">Nickname</label>
                            <div class="col-md-8 form-inline">
                                <input type="text" class="form-control" name="nickname" id="nickname" maxlength="20" value="<?php echo ( !empty($nickname) ? $nickname : '' ); ?>">
                            </div>
                        </div>
                    <?php } ?>
                    <?php if( in_array($mtype, array('e')) ) { ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="change_ec">Allowed to Change EC?</label>
                            <div class="col-md-8 form-inline">
                                <!--<input type="checkbox" name="change_ec" value="<?php /*echo $change_ec; */?>" <?php /*echo ( ($change_ec == 'Y') ? 'checked' : '' ); */?> style="">-->

                                <div class="switch switch-sm switch-success">
                                    <input type="checkbox" name="change_ec" data-plugin-ios-switch="" value="<?php echo $change_ec; ?>" <?php echo ( ($change_ec == 'Y') ? 'checked' : '' ); ?> style="">
                                    <!--<input type="checkbox" name="change_ec" data-plugin-ios-switch="" value="Y" <?php /*echo ( !empty($change_ec) ? 'checked' : '' ); */?> style="">-->
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if( in_array($mtype, array('c')) ) { ?>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="miles">Assign members within miles</label>
                            <div class="col-md-8 form-inline">
                                <div class="switch switch-sm switch-success">
                                    <input type="text" class="form-control" name="miles" id="miles" maxlength="20" value="<?php echo ( !empty($miles) ? $miles : '' ); ?>">
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="shipping_info_fields_section">
                <div class="form-group heading text-center no-border-bottom">
                    <p>Shipping Information</p>
                    <p>
                        (<input type="Checkbox" id="copy_address_sh" name="copy_address_sh" <?php echo (!empty($copy_address_sh) ? 'checked' : ''); ?> onclick="copyBillingToShipping(this);"><label for="copy_address_sh">same as contact Billing Info</label>)
                        <input type="hidden" name="shiped">
                    </p>
                </div>
                <div class="forms-group shipping_info_fields">
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="sh_title">Name</label>
                        <div class="col-md-8">
                            <div class="row form-group">
                                <!--<div class="col-lg-4">
                                    <select name="sh_title" id="sh_title" class="form-control">
                                        <option>Title</option>
                                        <option value="Mr." <?php /*echo ( (!empty($sh_title) && ($sh_title == 'Mr.') ) ? 'selected' : '' ); */?> >Mr</option>
                                        <option value="Miss." <?php /*echo ( (!empty($sh_title) && ($sh_title == 'Miss.') ) ? 'selected' : '' ); */?> >Miss</option>
                                        <option value="Mrs." <?php /*echo ( (!empty($sh_title) && ($sh_title == 'Mrs.') ) ? 'selected' : '' ); */?> >Mrs</option>
                                    </select>
                                </div>
                                <div class="mb-md hidden-lg hidden-xl"></div>-->
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="sh_firstname" name="sh_firstname" maxlength="20" placeholder="First Name" value="<?php echo ( !empty($sh_firstname) ? $sh_firstname : '' ); ?>">
                                </div>
                                <div class="mb-md hidden-lg hidden-xl"></div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="sh_lastname" name="sh_lastname" maxlength="20" placeholder="Last Name" value="<?php echo ( !empty($sh_lastname) ? $sh_lastname : '' ); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="sh_streetadd">Address</label>
                        <div class="col-md-8">
                            <div class="row form-group">
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="sh_streetadd" name="sh_streetadd" maxlength="30" placeholder="Street Address 1" value="<?php echo ( !empty($sh_streetadd) ? $sh_streetadd : '' ); ?>">
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="sh_streetadd_two" name="sh_streetadd_two" maxlength="40" placeholder="Street Address 2" value="<?php echo ( !empty($sh_streetadd_two) ? $sh_streetadd_two : '' ); ?>">
                                </div>
                                <div class="pb-xs hidden-md hidden-xs hidden-sm">&nbsp;</div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="sh_city" name="sh_city" maxlength="20" placeholder="City" value="<?php echo ( !empty($sh_city) ? $sh_city : '' ); ?>">
                                </div>
                                <div class="col-lg-4">
                                    <select name="sh_zone" class="form-control" required>
                                        <option>Select State</option>
                                        <?php
                                        $rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
                                        while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)){
                                            if( !empty($sh_zone) && ($i_zoneid==$sh_zone) )
                                                echo '<option value ='.$i_zoneid.' selected>'.$s_zone.'</option>';
                                            else
                                                echo '<option value ='.$i_zoneid.' >'.$s_zone.'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control" id="sh_postcode" name="sh_postcode" maxlength="20" placeholder="Post Code" value="<?php echo ( !empty($sh_postcode) ? $sh_postcode : '' ); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if( in_array($mtype, array('m', 'e', 'a')) ) { ?>
                <div class="check_address_fields_section">
                    <div class="form-group heading text-center no-border-bottom">
                        <p>Check Address</p>
                        <p>(<input type="Checkbox" id="copy_address_check" name="copy_address_check" <?php echo (!empty($copy_address_check) ? 'checked' : ''); ?> onclick="copyBillingToCheckAddress(this);"><label for="copy_address_check">same as contact Billing Info</label>)</p>
                    </div>
                    <div class="forms-group shipping_info_fields">
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="check_title">Name</label>
                            <div class="col-md-8">
                                <div class="row form-group">
                                    <!--<div class="col-lg-4">
                                        <select name="check_title" id="check_title" class="form-control">
                                            <option>Title</option>
                                            <option value="Mr." <?php /*echo ( (!empty($check_title) && ($check_title == 'Mr.') ) ? 'selected' : '' ); */?> >Mr</option>
                                            <option value="Miss." <?php /*echo ( (!empty($check_title) && ($check_title == 'Miss.') ) ? 'selected' : '' ); */?> >Miss</option>
                                            <option value="Mrs." <?php /*echo ( (!empty($check_title) && ($check_title == 'Mrs.') ) ? 'selected' : '' ); */?> >Mrs</option>
                                        </select>
                                    </div>
                                    <div class="mb-md hidden-lg hidden-xl"></div>-->
                                    <div class="col-lg-4">
                                        <input type="text" class="form-control" id="check_firstname" name="check_firstname" maxlength="20" placeholder="First Name" value="<?php echo ( !empty($check_firstname) ? $check_firstname : '' ); ?>">
                                    </div>
                                    <div class="mb-md hidden-lg hidden-xl"></div>
                                    <div class="col-lg-4">
                                        <input type="text" class="form-control" id="check_lastname" name="check_lastname" maxlength="20" placeholder="Last Name" value="<?php echo ( !empty($check_lastname) ? $check_lastname : '' ); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="check_streetadd">Address</label>
                            <div class="col-md-8">
                                <div class="row form-group">
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="check_streetadd" name="check_streetadd" maxlength="30" placeholder="Street Address 1" value="<?php echo ( !empty($check_streetadd) ? $check_streetadd : '' ); ?>">
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" id="check_streetadd_two" name="check_streetadd_two" maxlength="40" placeholder="Street Address 2" value="<?php echo ( !empty($check_streetadd_two) ? $check_streetadd_two : '' ); ?>">
                                    </div>
                                    <div class="pb-xs hidden-md hidden-xs hidden-sm">&nbsp;</div>
                                    <div class="col-lg-4">
                                        <input type="text" class="form-control" id="check_city" name="check_city" maxlength="20" placeholder="City" value="<?php echo ( !empty($check_city) ? $check_city : '' ); ?>">
                                    </div>
                                    <div class="col-lg-4">
                                        <select name="check_zone" class="form-control" required>
                                            <option>Select State</option>
                                            <?php
                                            $rs_zone = mysqli_query($conn,"select z.zone_id, z.zone_name from zones z, countries c  where z.zone_country_id=c.countries_id and c.countries_iso_code_3 ='USA' order by zone_name");
                                            while(list($i_zoneid, $s_zone)= mysqli_fetch_row($rs_zone)) {
                                                if( !empty($check_zone) && ($i_zoneid==$check_zone) )
                                                    echo '<option value ='.$i_zoneid.' selected>'.$s_zone.'</option>';
                                                else
                                                    echo '<option value ='.$i_zoneid.' >'.$s_zone.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" class="form-control" id="check_postcode" name="check_postcode" maxlength="20" placeholder="Post Code" value="<?php echo ( !empty($check_postcode) ? $check_postcode : '' ); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="check_address_fields_section">
                <div class="form-group heading text-center no-border-bottom"><p>Password Information</p></div>
                <div class="forms-group shipping_info_fields">
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="pass">Password</label>
                        <div class="col-md-8 <?php echo ( !empty($password) ? " form-inline" : '' ); ?> ">
                            <input type="password" class="form-control" name="pass" id="pass" maxlength="20" <?php echo (($is_add) ? 'required' : '');?> value="<?php echo ( !empty($password) ? $password : '' ); ?>">
                            <?php echo ( !empty($password) ? "<p class='form-control-static'>$password</p>" : '' ); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="confirmpass">Confirm Password</label>
                        <?php $confirmpass = empty($confirmpass) ? $password : $confirmpass; ?>
                        <div class="col-md-8 <?php echo ( !empty($confirmpass) ? " form-inline" : '' ); ?>">
                            <input type="password" class="form-control" name="confirmpass" id="confirmpass" maxlength="20" <?php echo (($is_add) ? 'required' : '');?> value="<?php echo ( !empty($confirmpass) ? $confirmpass : '' ); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-sm-9 centering text-center">
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <?php //echo ( (!empty($rule_id)) ? '<input type="hidden" name="ruleid" value="'.$rule_id.'">' : ''); ?>
                    <?php echo ( !empty($is_add) ? '<input type="hidden" name="goto" value="add">' : '<input type ="hidden" name="goto" value="update">' ); ?>
                    <button type="Submit" name="<?php echo ( !empty($is_add) ? 'addUser' : 'updateUser'); ?>" value="<?php echo ( !empty($is_add) ? 'Add' : 'Update'); ?>" class="command  btn btn-default btn-success"><?php echo ( !empty($is_add) ? 'Add' : 'Update'); ?></button>
                    <button id="cancel" type="button" name="cancel" value="Cancel" class="command btn btn-default btn-warning"  onClick="location.href='<?php echo $page_url.'&page='.$page; ?>';">Cancel</button>
                </div>
            </div>
        </footer>
    </form>
</div>
