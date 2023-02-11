<?php // $Id$
/**
 * Create a new contact
 */
	function const_contact_create($email, $first_name, $last_name, $company) {
	require_once 'class.cc.php';
	
	// Set your Constant Contact account username and password below
	$cc = new cc('johnamico', 'haircare');





	$contact_list = 6;
	$extra_fields = array(
		'FirstName' => $first_name,
		'LastName' => $last_name,
		'Company' => $company
	);
	
	// check if the contact exists
	$contact = $cc->query_contacts($email);
	
	// uncomment this line if the user makes the action themselves
	//$cc->set_action_type('contact');  
	

	if($contact) {
		// update the contact
		//$cc->update_contact($contact['id'], $email, $contact_list, $extra_fields);
}
	
	else {
		// create the contact
		 //$cc->create_contact($email, $contact_list, $extra_fields); 
}
		
	}	
	
?>


