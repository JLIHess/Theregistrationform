<?php

session_start();

// Import necessary files.
require_once('include/config.php');
require_once('include/functions.php');

// Check if a order ID was requested.
if(isset($_GET['orderId'])) {

	$orderId = intval($_GET['orderId']);

} else {

	// If there is no order in the URL then they came here in Error.
	// Redirect them to the homepage.
	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}

// Make sure they have authorization to edit this order.
if((!isset($_SESSION['retreat']['orderId']) or ($_SESSION['retreat']['orderId'] != $orderId)) and (!$userIsAdmin)) {

	// They don't have proper authorization. Redirect them to the homepage.
	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}

// Get the  order.
$order = getOrder($orderId);

// Make sure the order exists.
if($order === null) {

	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}

// Make sure all the guest information was entered.
if($order['status'] < 3) {

	header('Location: '.BASE_URL.'guestRegistrationForm.php?orderId='.$orderId);
	exit(0);

} elseif($order['status'] < 4) {

	header('Location: '.BASE_URL.'checkout.php?orderId='.$orderId);
	exit(0);

}

$referral = getReferralByorder($orderId);
if(!$referral) $referral = generateReferral($orderId);


// Set the template variables.
$templateVariables = array('orderId' => $orderId, 'order' => $order, 'contactEmail' => $fromEmail, 'referral'=> $referral);

// Load the template.
include_once('templates/complete.php');


?>