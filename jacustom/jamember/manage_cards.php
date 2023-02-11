<?php
$page_name = 'Manage Saved Cards';
$page_title = 'John Amico - ' . $page_name;

// Header already exists issue. This will keep all the output in Buffer but will not release it.
ob_start();
require_once("../common_files/include/global.inc");
require_once("session_check.inc");



$main_member_id = $_SESSION['member']['ses_member_id'];
$main_member_amico_id = $_SESSION['member']['session_user'];


$memberInfoSql = " SELECT m.int_member_id, m.int_customer_id, m.amico_id, c.customers_email_address AS email, a.entry_firstname AS firstname, a.entry_lastname AS lastname, a.entry_street_address AS street_address, a.entry_street_address2 As street_address2, a.entry_city As city, z.zone_name AS state, a.entry_postcode AS postcode, c.customers_telephone As phone 
FROM `tbl_member` m
INNER JOIN customers c ON c.customers_id=m.int_customer_id
INNER JOIN address_book a ON a.customers_id=m.int_customer_id
INNER JOIN zones z ON a.entry_state=z.zone_id
WHERE m.`amico_id` = '{$main_member_amico_id}' AND a.address_book_id=1  ";
$memberInfoQuery = mysqli_query($conn, $memberInfoSql);
$memberInfo = mysqli_fetch_assoc($memberInfoQuery);

$memberInfo['phone'] = str_replace(array('(', ')', '-', ' ', '/'), '', $memberInfo['phone']);

$hasRequiredInfoForCard = true;

$informationNotValidMessage = "Your Profile information are not valid or cannot be matched with the Card Information.";
$informationNotAvailableMessage = "Required information are not available.";
$informationNotFilledMessage = "Please go to your profile settings page and add your correct information. You must have correct First name, Last name, Email Address, Street Address, City, State, Zip Code and Phone Number";

//echo '<pre>'; var_dump($memberInfo); die();

$is_edit = $is_ajax = $is_add = $is_delete = false;
$post = $_POST;
$cardTypes = array(
    'AE' => "American Express",
    'VI' => "VISA",
    'MC' => "MasterCard",
    'DI' => "Discovery",
);

if( !empty($post['is_ajax']) && !empty($post['goto']) && ( in_array($post['goto'], array('add', 'update', 'delete')) ) ) {
    $is_edit = ($_REQUEST['goto'] == 'update') ? true : false;
    $is_add = ($_REQUEST['goto'] == 'add') ? true : false;
    $is_delete = ($_REQUEST['goto'] == 'delete') ? true : false;
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

    if( $is_add || $is_edit || $is_delete )
    {
        $cardProfile = is_numeric($post['cid']) ? filter_var($post['cid'], FILTER_SANITIZE_NUMBER_INT) : false;
        $cardNumber = is_numeric($post['c']) ? filter_var($post['c'], FILTER_SANITIZE_NUMBER_INT) : false;
        $cardType = in_array( strtoupper($post['ct']), array_keys($cardTypes)) ? strtoupper($post['ct']) : false;
        $cardExpMon = ($post['cem'] < 13 && $post['cem'] > 0) ? $post['cem'] : false;
        $cardExpYear = ( $post['cey'] >= date('Y') && $post['cey'] < (date('Y')+12) ) ? $post['cey'] : false;
        $cardCvv = ( is_numeric($post['cc']) && (strlen($post['cc']) >= 3 && strlen($post['cc']) < 5 ) ) ? $post['cc'] : false;
    }

    if( empty($hasRequiredInfoForCard) ) {
        $errorMessages['pro'] = "{$informationNotValidMessage} {$informationNotFilledMessage}";
    }

    if( empty($cardNumber) || (strlen($cardNumber) > 20 || strlen($cardNumber) < 12) ) {
        $errorMessages['c'] = "Invalid card number";
    }
    if( empty($cardType) ) {
        $errorMessages['ct'] = "Invalid card type";
    }
    if( empty($cardExpMon) ) {
        $errorMessages['cem'] = "Invalid card expiry month";
    }
    if( empty($cardExpYear) ) {
        $errorMessages['cey'] = "Invalid card expiry year";
    }
    if( empty($cardCvv) ) {
        $errorMessages['cc'] = "Invalid card cvv";
    }
    if( !validateCardExpDate($cardExpYear, $cardExpMon) ) {
        $errorMessages['ce'] = "Your card is expired already.";
    }

    $data = array(
        'firstname' => $memberInfo['firstname'],
        'lastname' => $memberInfo['lastname'],
        'street' => $memberInfo['street_address'],
        'city' => $memberInfo['city'],
        'region' => $memberInfo['state'],
        'postcode' => $memberInfo['postcode'],
        'country' => 'US',
        'telephone' => !empty($memberInfo['phone']) ? $memberInfo['phone'] : '',
        'cc_number'=> $cardNumber,
        'cid' => $cardCvv,
        'exp_year' => $cardExpYear,
        'exp_mon' => $cardExpMon,
        'cc_number_last_4' => substr($cardNumber, -1, 4),
    );

    if($is_add) {
        if(empty($errorMessages)) {
            try {
                $customerProfileId = Mage::getModel('mwauthorizenetcim/membercard')->loadByAmicoId($main_member_amico_id, true);
                if(empty($customerProfileId)) {
                    $customerProfileId = Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')
                        ->createCustomProfile(array('email'=>$memberInfo['email'], 'merchantCustomerId'=>$main_member_amico_id), true);
                }
                if(empty($customerProfileId)) {
                    $errorMessages['auth'] = "Something went wrong! Could not retrieve your profile information.";
                }
                else {
                    $customerPaymentProfileId = Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')
                        ->createCustomPaymentProfile( $customerProfileId, $data, true, true);

                    if(!empty($customerPaymentProfileId)) {
                        $successMessages['edit'] = "Successfully added your card";
                    } else {
                        $errorMessages['auth'] = "Something went wrong! Could not save your card.";
                    }
                }
            }
            catch (Exception $e)
            {
                $errorMessages['auth'] = "Could not save your card. Error: {$e->getMessage()}";
            }
        }
    }
    else if($is_edit) {
        if( empty($cardProfile) ) {
            $errorMessages['cid'] = "Invalid payment profile";
        }

        if(empty($errorMessages)) {
            try {
                $customerProfileId = Mage::getModel('mwauthorizenetcim/membercard')->loadByAmicoId($main_member_amico_id, true);
                if(empty($customerProfileId)) {
                    $customerProfileId = Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')
                        ->createCustomProfile(array('email'=>$memberInfo['email'], 'merchantCustomerId'=>$main_member_amico_id), true);
                }
                if(empty($customerProfileId)) {
                    $errorMessages['auth'] = "Something went wrong! Could not retrieve your profile information.";
                }
                else {
                    $data['address1'] = $data['street'];
                    $data['zip'] = $data['postcode'];
                    $data['cc_cid'] = $data['cid'];
                    $data['cc_exp_year'] = $data['exp_year'];
                    $data['cc_exp_month'] = $data['exp_mon'];

                    $customerPaymentProfileId = Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')
                        ->updateCustomerPaymentProfile( $cardProfile, $data, $customerProfileId);

                    if(!empty($customerPaymentProfileId)) {
                        $successMessages['edit'] = "Successfully updated your card";
                    } else {
                        $errorMessages['auth'] = "Something went wrong! Could not update your card.";
                    }
                }
            }
            catch (Exception $e)
            {
                $errorMessages['auth'] = "Could not update your card. Error: {$e->getMessage()}";
            }
        }

    }
    else if($is_delete) {
        unset($errorMessages['c'], $errorMessages['ct'], $errorMessages['cem'], $errorMessages['cey'], $errorMessages['cc'], $errorMessages['ce']);
        if( empty($cardProfile) ) {
            $errorMessages['cid'] = "Invalid payment profile";
        }

        try {
            $customerProfileId = Mage::getModel('mwauthorizenetcim/membercard')->loadByAmicoId($main_member_amico_id, true);
            if(empty($customerProfileId)) {
                $customerProfileId = Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')
                    ->createCustomProfile(array('email'=>$memberInfo['email'], 'merchantCustomerId'=>$main_member_amico_id), true);
            }
            if(empty($customerProfileId)) {
                $errorMessages['auth'] = "Something went wrong! Could not retrieve your profile information.";
            }
            else {
                $deleted = Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')
                    ->customDeletePaymentProfile($customerProfileId, $cardProfile);

                //echo '<pre>'; var_dump($deleted, $customerProfileId, $cardProfile); die();
                if($deleted) {
                    $successMessages['removed'] = "Successfully removed your card";
                } else {
                    $errorMessages['auth'] = "Something went wrong! Could not remove your card.";
                }
            }
        }
        catch (Exception $e)
        {
            $errorMessages['auth'] = "Could not remove your card. Error: {$e->getMessage()}";
        }
    }

    if(!empty($errorMessages)) {
        //$_SESSION['member_manage_credit_card']['error_message'] = $errorMessages;

        $message = (is_array($errorMessages) ? '<ul><li>' . implode("</li><li>", $errorMessages) . '</li></ul>' : $errorMessages);
        echo json_encode(array('success' => 0, 'message' => $message));
        die();
    }
    if(!empty($successMessages)) {
        $_SESSION['member_manage_credit_card']['success_message'] = $successMessages;

        $message = (is_array($successMessages) ? '<ul><li>' . implode("</li><li>", $successMessages) . '</li></ul>' : $successMessages);
        echo json_encode(array('success' => 1, 'message' => $message));
        die();
    }
}


require_once("templates/header.php");
require_once("templates/sidebar.php");


$member_type_name = 'Card';
$member_type_name_plural = 'Cards';
$self_page = 'manage_cards.php';
$page_url = base_member_url() . '/manage_cards.php?1=1';
$action_page = 'manage_cards.php';
$action_page_url = base_member_url() . '/manage_cards.php?1=1';
$export_url = base_member_url() . '/manage_cards.php';


$cardAddFunctionalityEnabled = true;
$cardEditFunctionalityEnabled = true;

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

    if(!empty($memberAmicoId)) {

        if(Mage_class_loaded()) {
            $memberProfileId = Mage::getModel('mwauthorizenetcim/membercard')->loadByAmicoId($memberAmicoId, true);
            //echo '<pre> Test'; var_dump($memberProfileId); die();

            if(!empty($memberProfileId)) {
                $cards  = Mage::getModel('mwauthorizenetcim/paradoxLabs_authorizeNetCim_payment')->getPaymentProfiles($memberProfileId);
                $temp   = array();

                /**
                 * Get customer's active orders and check for card conflicts.
                 */
                $orders = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect( '*' )
                    ->addAttributeToFilter( 'state', array('nin' => array( Mage_Sales_Model_Order::STATE_COMPLETE, Mage_Sales_Model_Order::STATE_CLOSED, Mage_Sales_Model_Order::STATE_CANCELED ) ) )
                    ->join(
                        array('oa' => 'amorderattr/order_attribute'), "oa.order_id=main_table.entity_id AND oa.jareferrer_self=1 AND oa.jareferrer_amicoid='{$memberAmicoId}' ",
                        ''
                    )
                    ->join(
                        array('op' => 'sales/order_payment'), "op.parent_id=main_table.entity_id AND main_table.ext_customer_id != '' ",
                        ''
                    )
                ;

                //echo '<pre>'; var_dump( (string) $orders->getSelect(), $orders->getItems()); die();

                if( $cards !== false && count($cards) > 0 ) {
                    $model = Mage::getModel('mwauthorizenetcim/subscriptioncard');

                    foreach( $cards as $card ) {
                        $card->inUse = 0;

                        if( count($orders) > 0 ) {
                            foreach( $orders as $order ) {
                                if( $order->getExtCustomerId() == $card->customerPaymentProfileId ) {
                                    // If we found an order with this card that is not complete, closed, or canceled,
                                    // it is still active and the payment ID is important. No editey.
                                    $card->inUse = 1;
                                    break;
                                }
                            }
                        }

                        $card = $model->cardInSubscriptionUse($card);

                        $temp[] = $card;
                    }
                }

                //echo '<pre>'; var_dump($memberProfileId, $temp); die();

                return $temp;
            }

            //return $memberProfileId;
        }
    }
    return false;
}


if( !$is_edit ) {
    $memberCards = getMemberCards($main_member_amico_id);
}

?>

    <div role="main" class="content-body manage-cards " id="manage-cards">
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
            <?php if(!empty($_SESSION['member_manage_credit_card']['error_message']) || !empty($_SESSION['member_manage_credit_card']['success_message'])): ?>

                <div class="row">
                    <?php if(!empty($_SESSION['member_manage_credit_card']['error_message'])): ?>
                        <div class="col-sm-10 centering">
                            <div class="alert alert-danger">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php
                                echo $message = (is_array($_SESSION['member_manage_credit_card']['error_message']) ? '<ul><li>' . implode("</li><li>", $_SESSION['member_manage_credit_card']['error_message']) . '</li></ul>' : $_SESSION['member_manage_credit_card']['error_message']);
                                unset($_SESSION['member_manage_credit_card']['error_message']);
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($_SESSION['member_manage_credit_card']['success_message'])): ?>
                        <div class="col-sm-10 centering">
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php
                                echo $message = (is_array($_SESSION['member_manage_credit_card']['success_message']) ? '<ul><li>' . implode("</li><li>", $_SESSION['member_manage_credit_card']['success_message']) . '</li></ul>' : $_SESSION['member_manage_credit_card']['success_message']);
                                unset($_SESSION['member_manage_credit_card']['success_message']);
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
            <?php if(!$is_edit && is_update_subscription_card_feature_enabled() ) : ?>
                <section class="panel">
                    <div class="col-xs-12">
                        <header class="panel-heading">
                            <h2 class="panel-title text-center"><?php echo $page_name; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="row cards-list">
                            <?php if($cardAddFunctionalityEnabled): ?>
                                <div class="col-xs-12 text-center mb20">
                                    <a href="#modal_cardform" type="button" data-target="#modal_cardform" class="manage-card-modal-loader modal-with-form btn btn-success modal_loader" data-manage-type="add"><i class="fa fa-plus"></i> Add New Card</a>
                                </div>
                            <?php endif; ?>
                            <?php if( !empty($memberCards) ): ?>
                                <?php foreach($memberCards as $card): ?>
                                <?php if(empty($card->customerPaymentProfileId)) { continue; } ?>
                                <div class="col-xs-12 col-md-4 card-item">
                                    <div class="card-item-details">
                                        <h3 class="box-title">Card: <?php echo formatCimCC($card->payment->creditCard->cardNumber); ?></h3>
                                        <address class="box-content">
                                            <?php echo $card->billTo->firstName.' '.$card->billTo->lastName ?><br />
                                            <?php echo $card->billTo->address ?><br />
                                            <?php echo $card->billTo->city ?>, <?php echo $card->billTo->state ?>, <?php echo $card->billTo->zip ?><br />
                                            <?php echo $card->billTo->country ?>
                                            <div class="pull-right">
                                                <?php if( $card->inUse == 1 ): ?>
                                                    <abbr title="This card cannot be modified while associated with open orders or subscriptions">Card In Use</abbr>
                                                <?php else: ?>
                                                    <?php if($cardEditFunctionalityEnabled): ?>
                                                    <a href="#modal_cardform" type="button" data-target="#modal_cardform" class="manage-card-modal-loader modal-edit-form modal-with-form btn btn-success modal_loader" data-card-number="<?php echo formatCimCC($card->payment->creditCard->cardNumber); ?>" data-cid="<?php echo $card->customerPaymentProfileId; ?>" data-manage-type="update" >Edit</a>
                                                    <!--<a href="<?php /*echo $action_page_url . "&goto=update&cid={$card->customerPaymentProfileId}"; */?>" class="btn btn-primary">Edit</a>-->
                                                    <?php endif; ?>
                                                    <!--<a href="<?php /*echo $action_page_url . "&goto=delete&cid={$card->customerPaymentProfileId}"; */?>" onclick="return confirm('Are you sure you want to delete this card? This action cannot be reversed.');" class="btn btn-danger">Delete</a>-->
                                                    <a href="#modal_card_delete_form" type="button" data-target="#modal_card_delete_form" class="manage-card-modal-loader modal-with-form btn btn-danger modal_loader" data-card-number="<?php echo formatCimCC($card->payment->creditCard->cardNumber); ?>" data-cid="<?php echo $card->customerPaymentProfileId; ?>" data-manage-type="delete">Delete</a>
                                                <?php endif; ?>
                                            </div>
                                        </address>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </section>
            <?php endif; ?>

            <div class="modal_card_delete_form modal-block mfp-hide" id="modal_card_delete_form" role="dialog">
                <section class="panel">
                    <header class="panel-heading">
                        <div class="panel-actions">
                            <a href="#" class="panel-action panel-action-dismiss modal-dismiss modal_clear_everything"></a>
                        </div>
                        <h2 class="panel-title text-center"><span>Delete</span> Card</h2>
                    </header>
                    <div class="panel-body">
                        <div class="system_message">
                            <div class="alert alert-success hide"></div>
                            <div class="alert alert-danger hide"></div>
                        </div>
                        <div class="control-group">
                            <div class="col-xs-12">
                                <p style="font-size: 16px; color: #d83a33;" class="text-center">
                                    <strong>
                                        Are you sure you want to remove your Card #<span class="card_number"></span>?
                                        <br/>This action cannot be undone.
                                    </strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <input type="hidden" name="cid" value="" class="card_id">
                                <input type="hidden" name="goto" value="delete" class="goto_type" />
                                <button type="Submit" name="submit" class="btn btn-primary btn-danger pl-lg pr-lg delete_button">Yes</button>
                                <button class="btn btn-default btn-success modal-dismiss modal_clear_everything">No</button>
                            </div>
                        </div>
                    </footer>
                </section>
            </div>
            <div class="modal_cardform modal-block modal-block-primary mfp-hide" id="modal_cardform">
                <form onsubmit="return false;" method="post" class="form form-validate card_editor_form">
                    <section class="panel">
                        <header class="panel-heading">
                            <div class="panel-actions">
                                <a href="#" class="panel-action panel-action-dismiss modal-dismiss modal_clear_everything"></a>
                            </div>
                            <h2 class="panel-title text-center"><span>Add</span> <?php echo $member_type_name; ?></h2>
                        </header>
                        <div class="panel-body">
                            <div class="system_message">
                                <div class="alert alert-success hide"></div>
                                <div class="alert alert-danger hide"></div>
                            </div>
                            <div class="control-group">
                                <fieldset>
                                    <legend>Billing Information</legend>
                                    <div class="control-group static-information">
                                        <div class="form-group">
                                            <label class="col-md-4" for="firstname">First Name</label>
                                            <div class="col-md-8"><p id="firstname" class="form-control-static"><?php echo $memberInfo['firstname']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="lastname">Last Name</label>
                                            <div class="col-md-8"><p id="lastname" class="form-control-static"><?php echo $memberInfo['lastname']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="lastname">Email</label>
                                            <div class="col-md-8"><p id="lastname" class="form-control-static"><?php echo $memberInfo['email']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="street_address">Street Address 1</label>
                                            <div class="col-md-8"><p id="street_address" class="form-control-static"><?php echo $memberInfo['street_address']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="street_address">Street Address 2</label>
                                            <div class="col-md-8"><p id="street_address" class="form-control-static"><?php echo $memberInfo['street_address2']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="city">City</label>
                                            <div class="col-md-8"><p id="city" class="form-control-static"><?php echo $memberInfo['city']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="state">State</label>
                                            <div class="col-md-8"><p id="state" class="form-control-static"><?php echo $memberInfo['state']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="state">Zip Code</label>
                                            <div class="col-md-8"><p id="state" class="form-control-static"><?php echo $memberInfo['postcode']; ?></p></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="state">Phone</label>
                                            <div class="col-md-8"><p id="state" class="form-control-static"><?php echo $memberInfo['phone']; ?></p></div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend>Credit Card Information</legend>
                                    <div class="control-group">
                                        <?php if(!$hasRequiredInfoForCard): ?>
                                            <div class="form-group">
                                                <div class="col-xs-12">
                                                    <div class="alert alert-danger"><?php echo $informationNotAvailableMessage . " " . $informationNotFilledMessage; ?></div>
                                                </div>
                                            </div>
                                        <?php else :?>
                                        <div class="form-group">
                                            <label class="col-md-4" for="card_type">Card Type</label>
                                            <div class="col-md-8">
                                                <select name="card_type" id="card_type" class="form-control" required style="height: 34px;">
                                                    <option value="">--Please Select--</option>
                                                    <option value="AE">American Express</option>
                                                    <option value="VI">Visa</option>
                                                    <option value="MC">MasterCard</option>
                                                    <option value="DI">Discover</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="card_number">Card Number</label>
                                            <div class="col-md-8">
                                                <input id="card_number" type="text" name="card_number" class="form-control pl-lg text_place card_number" required="" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="card_expiration_fields">Card Expiration</label>
                                            <div class="col-md-8">
                                                <div class="form-inline" id="card_expiration_fields">
                                                    <div class="form-group">
                                                        <label class="sr-only" for="card_expire_month">Exp Month</label>
                                                        <select name="card_expire_month" id="card_expire_month" class="form-control" required style="height: 34px;">
                                                            <option value="">Month</option>
                                                            <?php for($i=1; $i<13; $i++) { echo "<option value='{$i}'>{$i}</option>";  } ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="sr-only" for="card_expire_year">Exp Year</label>
                                                        <select name="card_expire_year" id="card_expire_year" class="form-control" required style="height: 34px;">
                                                            <option value="">Year</option>
                                                            <?php for($i=date('Y'); $i<2031; $i++) { echo "<option value='{$i}'>{$i}</option>";  } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4" for="card_cvv">Card CVV</label>
                                            <div class="col-md-8">
                                                <input id="card_cvv" type="text" name="card_cvv" class="form-control pl-lg text_place" style="width: auto" required="" size="4" maxlength="4" />
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <footer class="panel-footer">
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <?php if($hasRequiredInfoForCard): ?>
                                    <input type="hidden" name="cid" value="" class="card_id">
                                    <input type="hidden" name="goto" value="add" class="goto_type" />
                                    <button type="Submit" name="submit" value="Upload" class="btn btn-primary btn-success pl-lg pr-lg submit_button">Submit</button>
                                    <?php endif; ?>
                                    <button class="btn btn-default btn-warning modal-dismiss modal_clear_everything">Cancel</button>
                                </div>
                            </div>
                        </footer>
                    </section>
                </form>
            </div>

            <style>
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
            <script>
                jQuery('.manage-card-modal-loader').click(function() {
                    var cid = jQuery(this).attr('data-cid');
                    var card_number = jQuery(this).attr('data-card-number');
                    var manage_type = jQuery(this).attr('data-manage-type');

                    if(manage_type === 'delete') {
                        var modalElement = jQuery('#modal_card_delete_form');
                    }
                    else if( jQuery.inArray(manage_type, ['update', 'add']) !== -1 ) {
                        modalElement = jQuery('#modal_cardform');
                        jQuery('.goto_type', modalElement).val(manage_type);
                    }
                    if(modalElement) {
                        jQuery(modalElement).modal('show');

                        if( jQuery.inArray(manage_type, ['update', 'delete'])  !== -1 ) {
                            if ( typeof cid === 'undefined' || cid.length < 0) {
                                jQuery('.system_message .alert', modalElement).addClass('hide');
                                jQuery('.system_message .alert .alert-danger', modalElement).removeClass('hide').html('We couldn\'t find the card profile.');
                                jQuery('.control-group', modalElement).remove();
                                jQuery('.panel-footer', modalElement).remove();
                            } else {
                                jQuery('.card_id', modalElement).val(cid);
                            }
                            if ( typeof card_number === 'undefined' ||  card_number.length > 0) {
                                if (manage_type === 'delete') {
                                    jQuery('.panel-body .card_number', modalElement).text(card_number);
                                } else if (manage_type === 'update') {
                                    jQuery('.panel-body #card_number', modalElement).val(card_number);
                                }
                            }
                        }
                        if(manage_type === 'update') {
                            jQuery('.panel-title span', modalElement).text('Edit');
                        }
                        else if(manage_type === 'add') {
                            jQuery('.panel-title span', modalElement).text('Add');
                            jQuery('.card_id', modalElement).val('');
                            jQuery('.panel-body #card_number', modalElement).val('');
                        }
                    }

                    //console.log(cid.length < 0, card_number.length<0, cid, card_number);

                    return false;
                });
                jQuery('.modal-dismiss').click(function() {
                    jQuery('#modal_card_delete_form').modal('hide');
                    jQuery('#modal_cardform').modal('hide');
                    //jQuery('.mfp-ready').hide();
                });
                jQuery('#modal_card_delete_form .delete_button').click(function() {
                    var container = jQuery(this).parents('.modal_card_delete_form');
                    var cardProfile = jQuery('.card_id', container).val();
                    var goto = jQuery('.goto_type', container).val();
                    var button = jQuery('.delete_button', container);

                    var errors = [], errorHtml='';

                    if( !jQuery.isNumeric(cardProfile) || (cardProfile.length < 0) ) {
                        errors.push("Card ID is not valid");
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
                            data: {cid: cardProfile, goto:goto, is_ajax: 1},
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
                                var message = parsedData.message || "Card Removed!";
                                jQuery('.alert', container).addClass('hide');
                                jQuery('.alert.alert-success', container).removeClass('hide').html(message);

                                window.location.reload(true);
                            }
                            else {
                                message = parsedData.message || "Something went wrong!";
                                jQuery('.alert', container).addClass('hide');
                                jQuery('.alert.alert-danger', container).removeClass('hide').html(message);
                            }
                            jQuery(button).html("Yes").removeAttr('disabled');
                        })
                        .fail(function (data) {
                            jQuery(button).text("Yes").removeAttr('disabled');
                            jQuery('.alert', container).addClass('hide');
                            jQuery('.alert.alert-danger', container).removeClass('hide').html("Something went wrong!");
                        });
                    }

                    return false;



                });


                jQuery('.card_editor_form').submit(function() {
                    var container = jQuery(this);
                    var cardType = jQuery('#card_type', container).val();
                    var cardProfile = jQuery('.card_id', container).val();
                    var cardNum = jQuery('#card_number', container).val();
                    var cardExpMon = jQuery('#card_expire_month', container).val();
                    var cardExpYear = jQuery('#card_expire_year', container).val();
                    var goto = jQuery('.goto_type', container).val();
                    var cardCvv = jQuery('#card_cvv', container).val();
                    var button = jQuery('.submit_button', container);

                    var months = [], years = [], errors = [], errorHtml='';

                    for(var m=1; m<13; m++) { months.push(m) }
                    for(var y=(new Date()).getFullYear(); y<((new Date()).getFullYear() + 13); y++) { years.push(y) }

                    if( jQuery.inArray(cardType, ['VI', 'AE', 'MC', 'DI']) === -1 ) {
                        errors.push("Card Type is not valid");
                    }
                    if( jQuery.isNumeric(cardNum) && cardNum.length < 10) {
                        errors.push("Card Number is not valid");
                    }
                    if(!jQuery.inArray(cardExpMon, months)) {
                        errors.push("Card Expiry Month is not valid");
                    }
                    if(!jQuery.inArray(cardExpYear, years)) {
                        errors.push("Card Expiry Year is not valid");
                    }
                    if( !jQuery.isNumeric(cardCvv) || (cardCvv.length > 4 && cardCvv.length < 3) ) {
                        errors.push("Card CVV is not valid");
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
                            data: {cid: cardProfile, c: cardNum, ct: cardType, cem:cardExpMon, cey:cardExpYear, cc: cardCvv, goto:goto, is_ajax: 1},
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
                                    var message = parsedData.message || "Card Saved!";
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
                                jQuery(button).text("Submit").removeAttr('disabled');
                                jQuery('.alert', container).addClass('hide');
                                jQuery('.alert.alert-danger', container).removeClass('hide').html("Something went wrong!");
                            });
                    }

                    return false;

                });
            </script>

        </div>
    </div>


<?php
require_once("templates/footer.php");