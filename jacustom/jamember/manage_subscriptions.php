<?php
$page_name = "Manage Subscriptions' Card";
$page_title = 'John Amico - ' . $page_name;

// Header already exists issue. This will keep all the output in Buffer but will not release it.
ob_start();
require_once("../common_files/include/global.inc");
require_once("session_check.inc");


$main_member_id = $_SESSION['member']['ses_member_id'];
$main_member_amico_id = $_SESSION['member']['session_user'];


$is_ajax = false;
$post = $_POST;
if( !empty($post['is_ajax']) && !empty($post['sid']) && !empty($post['cid']) ) {
    $is_ajax = !empty($post['is_ajax']) ? true : false;
}

if($is_ajax) {
    $errorMessages = array();

    if( ! is_update_subscription_card_feature_enabled() || ! Mage_class_loaded() )
    {
        if(!empty($errorMessages)) {
            echo json_encode(array('success' => 0, 'message' => "Feature not available right now!"));
            die();
        }
    }

    $cardProfile = is_numeric($post['cid']) ? filter_var($post['cid'], FILTER_SANITIZE_NUMBER_INT) : false;
    $cardProfileExpiry = !empty($post['ce']) && (strpos($post['ce'], '-') !== false) ? filter_var($post['ce'], FILTER_SANITIZE_STRING) : false;
    $subscriptionId = is_numeric($post['sid']) ? filter_var($post['sid'], FILTER_SANITIZE_NUMBER_INT) : false;

    if( empty($cardProfile) ) {
        $errorMessages['cid'] = "Invalid Card ID";
    }
    if( empty($cardProfileExpiry) ) {
        $errorMessages['ce'] = "Invalid Card Expiry Date";
    } else {
        list($cardExpYear, $cardExpMon) = explode('-', $cardProfileExpiry);
    }
    if( empty($subscriptionId) ) {
        $errorMessages['ct'] = "Invalid Subscription ID";
    }
    if(!empty($cardExpYear) && !empty($cardExpMon) ) {
        if( !validateCardExpDate($cardExpYear, $cardExpMon) ) {
            $errorMessages['ce'] = "Your card is expired already.";
        }
        else {
            $expTime = strtotime($cardProfileExpiry);
            if( intval($expTime) ) {
                $cardExpEndDate = date("Y-m-t", $expTime);
                if(!empty($cardExpEndDate)) {
                    $cardHasValidity = Mage::getModel('mwauthorizenetcim/subscriptioncard')->validateCardExpiryForSubscription($subscriptionId, $cardExpEndDate);
                }
            }

            if(empty($cardHasValidity)) {
                $errorMessages['ce'] = "Your card will expire before the Subscription's next payment. Please choose another.";
            }
        }
    }

    if(empty($errorMessages)) {
        try {
            $customerProfileId = Mage::getModel('mwauthorizenetcim/membercard')->loadByAmicoId($main_member_amico_id, true);
            if(empty($customerProfileId)) {
                $errorMessages['auth'] = "Something went wrong! Could not retrieve your profile information.";
            }
            else {
                $updated = Mage::getModel('mwauthorizenetcim/subscriptioncard')->addSubscriptionCard($customerProfileId, $cardProfile, $subscriptionId, $main_member_amico_id);
                if ($updated) {
                    $successMessages['update'] = "Configuration Saved successfully!";
                }
                else {
                    $errorMessages['update'] = "Something went wrong! Could not assign card to the subscription.";
                }
            }
        }
        catch (Exception $e)
        {
            $errorMessages['auth'] = "Could not save your card. Error: {$e->getMessage()}";
        }
    }

    if(!empty($errorMessages)) {
        $message = (is_array($errorMessages) ? '<ul><li>' . implode("</li><li>", $errorMessages) . '</li></ul>' : $errorMessages);
        echo json_encode(array('success' => 0, 'message' => $message));
        die();
    }
    if(!empty($successMessages)) {
        $_SESSION['member_manage_subscriptions']['success_message'] = $successMessages;

        $message = (is_array($successMessages) ? '<ul><li>' . implode("</li><li>", $successMessages) . '</li></ul>' : $successMessages);
        echo json_encode(array('success' => 1, 'message' => $message));
        die();
    }
}


require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Subscription';
$member_type_name_plural = "{$member_type_name}s";
$self_page = basename(__FILE__);
$page_url = base_member_url() . "/{$self_page}?1=1";
$action_page = $self_page;
$action_page_url = base_member_url() . "/{$self_page}?1=1";
$export_url = base_member_url() . "/{$self_page}";


$cardAddFunctionalityEnabled = true;
$cardEditFunctionalityEnabled = false;

//echo '<pre>'; var_dump($_SESSION['member']); die();



function Mage_class_loaded()
{
    if( !class_exists('Mage') ) {
        require_once( base_shop_path() . '/app/Mage.php' );
        Mage::app();
    }

    if(class_exists('Mage')) {
        return true;
    }
    return false;
}

function validateCardExpDate($expYear, $expMonth)
{
    $expMonth = str_replace('0', '', $expMonth);
    $months = array(1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Aug', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dec');

    if(empty($months[$expMonth])) {
        return false;
    }
    if( $expYear < date('Y') ) {
        return false;
    }

    $lastDayOfMonth = date('t', strtotime("01-{$months[$expMonth]}-{$expYear}"));
    $cardLastDayOfExpire = strtotime("{$lastDayOfMonth}-{$months[$expMonth]}-{$expYear}");

    if($cardLastDayOfExpire <= time()) {
        return false;
    }

    return true;
}

function is_update_subscription_card_feature_enabled() {
    if(Mage_class_loaded()) {
        try {
            return Mage::helper('mwauthorizenetcim')->isUpdateSubscriptionCardFeatureEnabled();
        }
        catch (Exception $e) {
            return false;
        }
    }
    return false;
}

function formatCimCC( $str ) {
    return substr_replace( $str, '-', 4, 0 );
}

function getMemberCards($memberAmicoId) {
    if( !is_update_subscription_card_feature_enabled() ) {
        return array();
    }

    global $main_member_amico_id;

    if(!empty($memberAmicoId)) {

        if(Mage_class_loaded()) {
            $memberProfileId = Mage::getModel('mwauthorizenetcim/membercard')->loadByAmicoId($memberAmicoId, true);
            $subscriptionCard = $cardInUse = false;
            //echo '<pre> Test'; var_dump($memberProfileId); die();

            if(!empty($memberProfileId)) {
                $cards	= Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')->getPaymentProfiles($memberProfileId, true, true);
                $temp	= array();

                if( $cards !== false && count($cards) > 0 ) {
                    $model = Mage::getModel('mwauthorizenetcim/subscriptioncard');

                    foreach( $cards as $card ) {
                        $card->inUse = 0;
                        $card = $model->cardInSubscriptionUse($card, $main_member_amico_id);

                        if( !empty($card->inUse) ) {
                            $subscriptionCard = $card->customerPaymentProfileId;
                        }

                        $temp[] = $card;
                    }

                    if(!empty($temp)) {
                        foreach ($temp as $retrivedCard) {
                            $paymentProfileId = (string)$retrivedCard->customerPaymentProfileId;
                            $cardNumber = (string)$retrivedCard->payment->creditCard->cardNumber;
                            $expirationDate = (string)$retrivedCard->payment->creditCard->expirationDate;

                            list($expirationYear, $expirationMonth) = explode('-', $expirationDate);

                            //echo '<pre>'; var_dump($retrivedCard, $paymentProfileId, $expirationYear, $expirationMonth ); die();

                            if (!empty($paymentProfileId) && (!empty($expirationYear) && !empty($expirationMonth))) {

                                if (!validateCardExpDate($expirationYear, $expirationMonth)) {
                                    continue;
                                }

                                $cardsRetrived[$paymentProfileId] = array(
                                    'paymentProfile' => $paymentProfileId,
                                    'cardNumber' => $cardNumber,
                                    'cardExpire' => $expirationDate,
                                );

                                if(!empty($subscriptionCard) && $subscriptionCard==$paymentProfileId) {
                                    $cardsRetrived[$paymentProfileId]['isCurrentlyUsed'] = true;

                                    $cardInUse = $cardsRetrived[$paymentProfileId];
                                } else {
                                    $cardsRetrived[$paymentProfileId]['isCurrentlyUsed'] = false;
                                }

                                //$cardNums[$paymentProfileId] = str_replace(array('X', '-'), '', $cardNumber);

                                /*if(!empty( $cardExpireArr[(string) $expirationDate] ))
                                {
                                } else {
                                    $key = (string) $expirationDate;
                                }*/
                                $key = (string)$expirationDate . $paymentProfileId;
                                $cardExpireArr[$key] = $cardsRetrived[$paymentProfileId];
                            }
                        }

                        if (!empty($cardExpireArr)) {
                            ksort($cardExpireArr);
                        }
                    }
                }

                //echo '<pre>'; var_dump($memberProfileId, $cardExpireArr, $temp); die();

                return array('cards' => $cardExpireArr, 'currently_using' => $cardInUse);
            }

            //return $memberProfileId;
        }
    }
    return false;
}

function getMemberSubscriptions($memberAmicoId)
{
    global $conn;

    if( !is_update_subscription_card_feature_enabled() ) {
        return false;
    }
    $subscriptions = array();

    if(!empty($memberAmicoId))
    {
        if(Mage_class_loaded())
        {
            $sql = " 
                SELECT fs.subscription_id, oa.order_id, s.status
                FROM stws_recurringandrentalpayments_flat_subscription fs
                INNER JOIN stws_recurringandrentalpayments_subscription s ON fs.subscription_id = s.id 
                INNER JOIN stws_sales_flat_order o ON fs.parent_order_id=o.increment_id
                INNER JOIN stws_amasty_amorderattr_order_attribute oa ON o.entity_id=oa.order_id
                
                WHERE oa.jareferrer_amicoid = '{$memberAmicoId}' AND oa.jareferrer_self=1 AND s.status IN (1,0) 
            ";
            $query = mysqli_query($conn, $sql);
            while($subResult = mysqli_fetch_assoc($query)) {
                if(!empty($subResult['subscription_id'])) {
                    $subLoad = Mage::getModel('recurringandrentalpayments/subscription')->load($subResult['subscription_id']);

                    if( !empty($subLoad->getId()) ) {
                        $order = $subLoad->getOrder();

                        if(!empty($order->getId()))
                        {
                            $term = Mage::getModel('recurringandrentalpayments/terms')->load($subLoad->getTermType());

                            if(!empty($term->getId())) {
                                $plan_id = $term->getPlanId();
                                if(!empty($plan_id)) {
                                    $plan = Mage::getModel('recurringandrentalpayments/plans')->load($plan_id);
                                    if(!empty($plan->getId()))
                                    {
                                        $subscriptions[$subResult['subscription_id']]['subscription'] = $subLoad;
                                        $subscriptions[$subResult['subscription_id']]['term'] = $term;
                                        $subscriptions[$subResult['subscription_id']]['plan'] = $plan;
                                        $subscriptions[$subResult['subscription_id']]['order'] = $order;
                                        $subscriptions[$subResult['subscription_id']]['subscription_status'] = Mage::getModel('recurringandrentalpayments/source_subscription_status')->getLabel($subLoad->getStatus());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    //echo '<pre>'; var_dump($subscriptions[149]['order']->getPayment()->getCcLast4()); die();

    return $subscriptions;
}


if( !$is_edit ) {
    $memberCards = getMemberCards($main_member_amico_id);
    $memberSubscriptions = getMemberSubscriptions($main_member_amico_id);
}

?>

    <div role="main" class="content-body manage-subscriptions " id="manage-subscriptions">
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
            <?php if( !empty($_SESSION['member_manage_subscriptions']['error_message']) || !empty($_SESSION['member_manage_subscriptions']['success_message']) ): ?>

                <div class="row">
                    <?php if(!empty($_SESSION['member_manage_subscriptions']['error_message'])): ?>
                        <div class="col-sm-10 centering">
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php
                                echo $message = (is_array($_SESSION['member_manage_subscriptions']['error_message']) ? '<ul><li>' . implode("</li><li>", $_SESSION['member_manage_subscriptions']['error_message']) . '</li></ul>' : $_SESSION['member_manage_subscriptions']['error_message']);
                                unset($_SESSION['member_manage_subscriptions']['error_message']);
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($_SESSION['member_manage_subscriptions']['success_message'])): ?>
                        <div class="col-sm-10 centering">
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php
                                echo $message = (is_array($_SESSION['member_manage_subscriptions']['success_message']) ? '<ul><li>' . implode("</li><li>", $_SESSION['member_manage_subscriptions']['success_message']) . '</li></ul>' : $_SESSION['member_manage_subscriptions']['success_message']);
                                unset($_SESSION['member_manage_subscriptions']['success_message']);
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
            <?php if( !$is_edit && is_update_subscription_card_feature_enabled() ) : ?>

                <section class="panel">
                    <div class="col-xs-12">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row subscription-list">
                                <?php if(!empty($memberSubscriptions)) : ?>
                                    <?php $i=1; foreach($memberSubscriptions as $subDetails): ?>
                                    <div class="col-xs-12 col-sm-6 subscription_item">
                                        <div class="control-group static-information details">
                                            <div class="form-group">
                                                <label class="col-md-6">Subscribed Plan : </label>
                                                <div class="col-md-6"><p class="form-control-static"><?php echo $subDetails['plan']->getPlanName(); ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-6">Subscription Start Date : </label>
                                                <div class="col-md-6"><p class="form-control-static"><?php echo Mage::helper('recurringandrentalpayments')->__(Mage::helper('core')->formatDate($subDetails['subscription']->getDateStart(),'medium',false)); ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-6">Subscription Expiry Date : </label>
                                                <div class="col-md-6"><p class="form-control-static"><?php
                                                        if ($subDetails['subscription']->isInfinite() == 1):
                                                            echo '-';
                                                        else:
                                                            echo date("M d, Y" ,strtotime($subDetails['subscription']->getFlatDateExpire()));
                                                        endif;
                                                ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-6">Upcoming Payment Date  : </label>
                                                <div class="col-md-6"><p class="form-control-static"><?php echo date("M d, Y" ,strtotime($subDetails['subscription']->getFlatNextPaymentDate())); ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-6">Subscribed Term : </label>
                                                <div class="col-md-6"><p class="form-control-static"><?php echo $subDetails['term']->getLabel(); ?></p></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-6">Status : </label>
                                                <div class="col-md-6"><p class="form-control-static"><?php echo $subDetails['subscription_status']; ?></p></div>
                                            </div>
                                            <?php if($subDetails['order']->getPayment()->getMethodInstance()->getCode() === 'authnetcim' ) : ?>
                                            <div class="form-group">
                                                <label class="col-md-6">Card Using : </label>
                                                <div class="col-md-6">
                                                    <?php if( !empty($memberCards['currently_using']['cardNumber']) || !empty($subDetails['order']->getPayment()->getCcLast4()) ) : ?>
                                                    <p class="form-control-static"><?php echo ( !empty($memberCards['currently_using']['cardNumber']) ? formatCimCC($memberCards['currently_using']['cardNumber'])  : "XXXX-" . $subDetails['order']->getPayment()->getCcLast4() ); ?></p>
                                                    <?php endif; ?>
                                                    <p class="form-control-static">
                                                        <?php if(!empty($memberCards['cards'])) : ?>
                                                        <a href="#modal_cardform" type="button" data-target="#modal_cardform" data-sub-id="<?php echo $subDetails['subscription']->getId(); ?>" class="change-card-loader modal-with-form btn btn-success modal_loader" data-sub-title="<?php echo $subDetails['plan']->getPlanName() . "  :  " . $subDetails['term']->getLabel(); ?>" >Change Card</a>
                                                        <?php else: ?>
                                                        <strong>You do not have any Card stored in your account. Please <a href="<?php echo  base_member_url(); ?>/manage_cards.php">click here</a> to add one.</strong>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php $i++; endforeach; ?>
                                <?php else: ?>
                                    <div class="col-xs-12 text-center">
                                        <p>You have no subscriptions right now!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </section>
            <?php endif; ?>

            <?php if(!empty($memberCards['cards'])) : ?>
            <div class="modal_cardform modal-block modal-block-primary mfp-hide" id="modal_cardform">
                <form onsubmit="return false;" method="post" class="form form-validate card_editor_form">
                    <section class="panel">
                        <header class="panel-heading">
                            <div class="panel-actions">
                                <a href="#" class="panel-action panel-action-dismiss modal-dismiss modal_clear_everything"></a>
                            </div>
                            <h2 class="panel-title text-center"><span>Change Card For Subscription</span></h2>
                        </header>
                        <div class="panel-body">
                            <div class="system_message">
                                <div class="alert alert-success hide"></div>
                                <div class="alert alert-danger hide"></div>
                            </div>
                            <div class="control-group">
                                <div class="form-group static-information">
                                    <label class="col-md-4">Subscribed Plan : </label>
                                    <div class="col-md-8"><p class="form-control-static modal_sub_plan_name"><strong></strong></p></div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4" for="subscription_card">Choose Card: </label>
                                    <div class="col-md-8">
                                        <select name="subscription_card" id="subscription_card" class="form-control" required style="height: 34px;">
                                            <option value="">--- Please Select ---</option>
                                            <?php foreach($memberCards['cards'] as $card) : ?>
                                                <option value="<?php echo $card['paymentProfile']; ?>"<?php echo (!empty($card['isCurrentlyUsed']) ? " selected data-cardinuse='1' " : ""); ?><?php echo (!empty($card['cardExpire']) ? " data-cardexp='{$card['cardExpire']}' " : ""); ?> >Card:  <?php echo formatCimCC( $card['cardNumber'] ) . '  Exp: ' . $card['cardExpire']  . (!empty($card['isCurrentlyUsed']) ? "  (Currently Using)" : "") ?></option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <input type="hidden" name="sid" class="sub_id" value="" />
                                    <button type="Submit" name="submit" class="btn btn-primary btn-success pl-lg pr-lg submit_button">Submit</button>
                                    <button class="btn btn-default btn-warning modal-dismiss modal_clear_everything">Cancel</button>
                                </div>
                            </div>
                        </footer>
                    </section>
                </form>
            </div>
            <?php endif; ?>

            <style>
                .subscription_item .details {
                    border: 1px solid #ececec;
                    border-radius: 5px;
                    padding-top: 15px;
                }
                .static-information {
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                .static-information .form-group {
                    margin-bottom: 0px;
                }
                .static-information .form-control-static {
                    padding-top: 0px;
                }
                .static-information .form-group label {
                    font-weight: bold;
                }
            </style>
            <?php if(!empty($memberCards['cards'])) : ?>
            <script>
                jQuery('.change-card-loader').click(function() {
                    var sid = jQuery(this).attr('data-sub-id');
                    var sub_title = jQuery(this).attr('data-sub-title');

                    jQuery('#modal_cardform').modal('show');

                    if( typeof sid != 'undefind' && sid.length < 0) {
                        jQuery('#modal_cardform .system_message .alert').addClass('hide');
                        jQuery('#modal_cardform .system_message .alert .alert-danger').removeClass('hide').html('We couldn\'t find the card profile.');
                        jQuery('#modal_cardform .control-group').remove();
                        jQuery('#modal_cardform .panel-footer').remove();
                    } else {
                        jQuery('#modal_cardform .system_message .alert').addClass('hide');
                        jQuery('#modal_cardform .sub_id').val(sid);
                    }
                    if( typeof sub_title != 'undefind' && sub_title.length > 0) {
                        jQuery('#modal_cardform .modal_sub_plan_name strong').html(sub_title);
                    } else {
                        jQuery('#modal_cardform .modal_sub_plan_name').parents('.form-group').hide();
                    }

                    //console.log(cid.length < 0, card_number.length<0, cid, card_number);

                    return false;
                });
                jQuery('.modal_cardform .modal-dismiss').click(function() {
                    jQuery('#modal_cardform').modal('hide');
                    //jQuery('.mfp-ready').hide();
                });

                jQuery('.submit_button').click(function() {
                    var container = jQuery(this).parents('.modal_cardform');
                    var cardProfile = jQuery('#subscription_card', container).val();
                    var cardProfileExp = jQuery('#subscription_card option:selected', container).attr('data-cardexp');
                    var sub_id = jQuery('.sub_id', container).val();
                    var button = jQuery('.submit_button', container);

                    var errors = [], errorHtml='';

                    if( !jQuery.isNumeric(cardProfile) || (cardProfile.length < 0) ) {
                        errors.push("Please choose your desired card");
                    }
                    if( !jQuery.isNumeric(sub_id) || (sub_id.length < 0) ) {
                        errors.push("Subscription ID is not valid");
                    }

                    if(errors.length > 0 && typeof(errors) === 'object') {
                        errorHtml = "<ul>";
                        for(var e in errors) {
                            errorHtml += "<li>" + errors[e] + "</li>";
                        }
                        errorHtml += "</ul>";
                    }
                    //console.log(typeof(errors), errors, errors.length, errorHtml, cardType, cardNum, cardExpMon, cardExpYear, cardCvv);
                    if(errorHtml.length > 0) {
                        jQuery('.alert', container).addClass('hide');
                        jQuery('.alert.alert-danger', container).removeClass('hide').html(errorHtml);
                    } else {
                        jQuery('.alert', container).addClass('hide');
                    }

                    if(errors.length <= 0) {
                        // Change the Button text
                        jQuery(button).text("Processing...").attr('disabled', 'disabled');

                        jQuery.ajax({
                            method: "POST",
                            url: "<?php echo $action_page_url; ?>",
                            cache: false,
                            timeout: 20000, // 20s
                            data: {cid: cardProfile, sid:sub_id, ce:cardProfileExp, is_ajax: 1},
                            dataType: "json",

                            error: function (jqXHR, textStatus, errorThrown) {
                                jQuery(button).text("Submit").removeAttr('disabled');
                                jQuery('.alert', container).addClass('hide');
                                jQuery('.alert.alert-danger', container).removeClass('hide').html("Something went wrong! Please reload the page.");
                            }
                        })
                        .done(function (data) {
                            //var parsedData = jQuery.parseJSON(data);
                            var parsedData = data;

                            if (parsedData.success === 1) {
                                var message = parsedData.message || "Card Updated!";
                                jQuery('.alert', container).addClass('hide');
                                jQuery('.alert.alert-success', container).removeClass('hide').html(message);

                                window.location.reload(true);
                            }
                            else {
                                message = parsedData.message || "Something went wrong!";
                                jQuery('.alert', container).addClass('hide');
                                jQuery('.alert.alert-danger', container).removeClass('hide').html(message);
                            }
                            jQuery(button).html("Submit").removeAttr('disabled');
                        })
                        .fail(function (data) {
                            jQuery(button).text("Yes").removeAttr('disabled');
                            jQuery('.alert', container).addClass('hide');
                            jQuery('.alert.alert-danger', container).removeClass('hide').html("Something went wrong!");
                        });
                    }

                    return false;

                });
            </script>
            <?php endif; ?>
        </div>
    </div>


<?php
require_once("templates/footer.php");