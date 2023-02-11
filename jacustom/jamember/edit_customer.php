<?php

$page_name = 'Customer';

require_once("../common_files/include/global.inc");
require_once("session_check.inc");

$display_header = false;
require_once("templates/header.php");
//require_once("templates/sidebar.php");

$member_id = $_SESSION['member']['ses_member_id'];

$which = !empty($_GET['which']) ? filter_var($_GET['which'], FILTER_SANITIZE_NUMBER_INT) : 0;


if (!empty($Action)) {
	if ($Action == "Update Customer") {

        $DOB = strtotime($DOB);
        if(!empty($DOB)) {
            $DOB = date('Y-m-d', $DOB);
        } else {
            $DOB = '';
        }

        $sql = "UPDATE  member_customers SET first_name='$First', last_name='$Last',street_address='$Address', city='$City', state='$State', zip='$Zip', email='$Email', phone='$Phone', dob='$DOB' WHERE members_customer_id='$CustomerID' AND member_id='$member_id'";

        //debug(false, true, $_POST, $sql);

        /*$DOBa = explode('/',$DOB);
		$DOB = $DOBa[2]."-".$DOBa[0]."-".$DOBa[1];*/
		mysqli_query($conn, $sql);
		?>
		<script>
			alert("<?=$First." ".$Last?>'s information has been updated.");
            window.opener.location.reload();
			window.close();
		</script>
		die();
		<?
	}
	elseif ($Action == "Add Customer") {
		//First, check for existence
		$ExistenceTest = mysqli_query($conn,"SELECT * FROM member_customers WHERE email='$Email' AND first_name='$First' AND last_name='$Last'");
		if ($ExistenceTest && mysqli_num_rows($ExistenceTest) > 0) {
			$CustomerInfo = mysqli_fetch_object($ExistenceTest);
			//Entry already exists.
			$Message = $Email." is already attributed to ".$CustomerInfo->first_name." ".$CustomerInfo->last_name.".";
			$CustomerToEdit->first_name=$First;
			$CustomerToEdit->last_name=$Last;
			$CustomerToEdit->street_address=$Address;
			$CustomerToEdit->city=$City;
			$CustomerToEdit->state=$State;
			$CustomerToEdit->zip=$Zip;
			$CustomerToEdit->phone=$Phone;

            $DOB = strtotime($DOB);
            if(!empty($DOB)) {
                $DOB = date('Y-m-d', $DOB);
            } else {
                $DOB = '';
            }

			/*$DOBa = explode('/',$DOB);
			$DOB = $DOBa[2]."-".$DOBa[0]."-".$DOBa[1];*/

			$CustomerToEdit->dob=$DOB;
			?>
			<script>
				alert("<?=$Message?>");
			</script>
			<?
		}
		else {
            $DOB = strtotime($DOB);
            if(!empty($DOB)) {
                $DOB = date('Y-m-d', $DOB);
            } else {
                $DOB = '';
            }

			/*$DOBa = explode('/',$DOB);
			$DOB = $DOBa[2]."-".$DOBa[0]."-".$DOBa[1];*/

			mysqli_query($conn,"INSERT INTO member_customers SET member_id='$member_id',first_name='$First', last_name='$Last',street_address='$Address', city='$City', state='$State', zip='$Zip', email='$Email', phone='$Phone', dob='$DOB'");
			$Message = $First." has been added to your customer list.";
			?>
			<script>
				alert("<?=$Message?>");
				window.opener.location.reload();
				window.close();
			</script>
			die();
			<?
		}
	}
}
if (!empty($which)) {
	$CustomerToEdit = mysqli_fetch_object(mysqli_query($conn,"SELECT * FROM member_customers WHERE members_customer_id=".$which));
	//debug(true, true, $CustomerToEdit);

    if(!empty($CustomerToEdit->dob) && ($CustomerToEdit->dob != '0000-00-00') ) {
        $DOB = date('Y/m/d', strtotime($CustomerToEdit->dob));
    } else {
        $DOB = '';
    }

    //$DOB = $CustomerToEdit->dob;

    /*$DOBa = explode('-',$CustomerToEdit->dob);
	$DOB = $DOBa[1]."/".$DOBa[2]."/".$DOBa[0];*/

    $First = $CustomerToEdit->first_name;
    $Last = $CustomerToEdit->last_name;
    $Address = $CustomerToEdit->street_address;
    $Email = $CustomerToEdit->email;
    $City = $CustomerToEdit->city;
    $State = $CustomerToEdit->state;
    $Zip = $CustomerToEdit->zip;
    $Phone = $CustomerToEdit->phone;

	$ActionKeyword = "Update";
}
else {
	$ActionKeyword = "Add";
}

?>

<div role="main" class="content-body no-margin-left">
    <div class="row ">
        <div class="col-xs-12 centering">
            <form name="send_email" action="" method="post" class="form form-validate">
                <section class="panel">
                    <header class="panel-heading">
                        <h2 class="panel-title text-center"><?php echo (!empty($which) ? 'Edit ' : 'Add ') . $page_name; ?></h2>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <input type="hidden" name="Action" value="<?=$ActionKeyword?> Customer">
                                <input type="hidden" name="CustomerID" value="<?=$which?>">

                                <?php
                                $fieldList = array(
                                    'First' => 'First Name',
                                    'Last' => 'Last Name',
                                    'Address' => 'Street Address',
                                    'City' => 'City',
                                    'State' => 'State',
                                    'Zip' => 'Zip',
                                    'Email' => 'Email',
                                    'Phone' => 'Phone',
                                    'DOB' => 'Birth Date',
                                );

                                foreach($fieldList as $field => $name) {
                                    $required = $datePicker = '';
                                    if( in_array($field, array('Email', 'First', 'Last')) ) {
                                        $required = 'required';
                                    }
                                    if( in_array($field, array('DOB')) ) {
                                        $datePicker = 'data-plugin-datepicker';
                                    }
                                    ?>
                                    <div class="form-group <?php echo ( !empty($error_messages[$field]) ? 'has-error' : '' ); ?> ">
                                        <label class="col-sm-4 control-label" for="<?php echo $field ?>"><?php echo $name ?></label>
                                        <div class="col-sm-8">
                                            <input type="text" name="<?php echo $field ?>" id="<?php echo $field ?>" class="form-control" value="<?php echo ( !empty($$field) ? $$field: '' ); ?>" <?php echo $required;?> <?php echo $datePicker;?>>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="Submit" name="submit" value="" class="command  btn btn-default btn-success mr-lg">Submit</button>
                                <button type="button" onclick="window.close();" class="btn btn-default btn-warning ml-lg">Close</button>
                            </div>
                        </div>
                    </footer>
                </section>
            </form>
        </div>
    </div>
</div>



<?php
require_once("templates/footer.php");