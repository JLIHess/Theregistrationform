<?php

session_start();

// Import necessary files.
require_once('include/config.php');
require_once('include/functions.php');

// Check if a order ID was requested.
if(isset($_GET['orderId']) && isset($_GET['name']) ) {

	$orderId = intval($_GET['orderId']);
	$name = $_GET['name'];

} else {

	// If there is no order in the URL then they came here in Error.
	// Redirect them to the homepage.
	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}


// Get the  order.
$order = getOrder($orderId);

// Make sure the order exists.
if($order === null || strtolower($order['customer']['last_name']) != strtolower($name) ) {

	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}

// Make sure all the guest information was entered.
if($order['status'] < 4) {

	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}

$referral = getReferralByorder($orderId);
if(!$referral) {
	generateReferral($orderId);
	$referral = getReferralByorder($orderId);

};


// Set the template variables.
$templateVariables = array('orderId' => $orderId, 'order' => $order, 'contactEmail' => $fromEmail, 'referral'=> $referral);

// Load the template.
include_once('templates/referral.php');


?>