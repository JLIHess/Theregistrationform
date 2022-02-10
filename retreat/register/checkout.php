<?php

session_start();

// Import necessary files.
require_once('include/config.php');
require_once('include/functions.php');
require_once('include/paymentFunctions.php');

// Sponsorship prices.
$sponsorships = getSponsorships();

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
if(!isset($_SESSION['retreat']['orderId']) or ($_SESSION['retreat']['orderId'] != $orderId)) {

	// They don't have proper authorization. Redirect them to the homepage.
	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}

// Get the  order.
$order = getOrder($orderId);

// Get the countries / states.
$countries = getCountries();
$states = getStates(UNITED_STATES_COUNTRY_ID);

// Make sure the order exists.
if($order === null) {

	header('Location: '.SELECT_ROOMS_URL);
	exit(0);

}

// Make sure all the guest information was entered.
if($order['status'] < 3) {

	header('Location: '.BASE_URL.'guestRegistrationForm.php?orderId='.$orderId);
	exit(0);

}

// Make sure the payment is not complete yet.
if($order['status'] > 3) {

	header('Location: '.BASE_URL.'complete.php?orderId='.$orderId);
	exit(0);

}

// Get the total price.
$order['total'] = $order['price'] + $order['tax'] + $order['cme_price'];

// Create a variable to hold any error we encounter.
$errors = array();

// Check if the form was submitted.
if(isset($_POST['submit']) and ($_POST['submit'] == 'Checkout')) {

	// Get the variables.

	// Get the sponsorship.
	$sponsorshipTypeId = '';
	$sponsorshipAmount = 0;
	$sponsorshipNotes = '';
	$selectedSponsorship = null;
	if(isset($_POST['sponsorships']) and (count($_POST['sponsorships']) > 0)) {

		// Find the selected sponsorship.
		foreach($sponsorships as $sponsorship) {
			if($sponsorship['id'] == intval($_POST['sponsorships'][0])) $selectedSponsorship = $sponsorship;
		}

		// Check if this sponsorship type exists.
		if($selectedSponsorship !== null) {

			// Get the sponsorship type id.
			$sponsorshipTypeId = $selectedSponsorship['id'];

			// Check if this sponsorship type has a set amount.
			if($selectedSponsorship['amount'] != null) {

				// Get the sponsorship amount.
				$sponsorshipAmount = $selectedSponsorship['amount'];

			} else {

				// Get the amount entered by the user.
				if(isset($_POST['sponsorshipAmount'.$sponsorshipTypeId])) {
					$sponsorshipAmount = floor(floatval($_POST['sponsorshipAmount'.$sponsorshipTypeId]) * 100);
				}

				// Make sure a valid amount was entered.
				if($sponsorshipAmount <= 0) {
					$errors[] = "Please enter a valid sponsorship amount.";
				}

			}

			// Get the notes.
			$sponsorshipNotes = (isset($_POST['sponsorshipNotes']) ? trim($_POST['sponsorshipNotes']) : '');

		}

	}

	// Check if this is an administrator who is paying by check.
	if(isset($_SESSION['retreatAdmin']) and $_SESSION['retreatAdmin'] and isset($_POST['payByCheck']) and ($_POST['payByCheck'] == '1') and (count($errors) == 0)) {

		// Update order
		$statement = $conn->prepare("UPDATE `retreat_orders` SET `status` = 4, `sponsorship_type_id` = :sponsorshipTypeId, `sponsorship_amount` = :sponsorshipAmount, `sponsorship_notes` = :sponsorshipNotes, `total_amount` = :orderTotal, `current_balance` = :orderTotal WHERE `id` = :orderId");
		$statement->execute(array(':orderId' => $orderId, ':sponsorshipTypeId' => $sponsorshipTypeId, ':sponsorshipAmount' => $sponsorshipAmount, ':sponsorshipNotes' => $sponsorshipNotes, ':orderTotal' => $order['total'] + $sponsorshipAmount));

		// Add the guests to the CRM.
		foreach($order['guests'] as $guest) {
			$statement = $conn->prepare("UPDATE `retreat_users` SET `user_type_id` = :user_type_id WHERE `id` = :userId");
			$statement->execute(array(':user_type_id' => $guest['user_type_id'], ':userId' => $guest['user_id']));
		}


		$referral = generateReferral($orderId);
		// Send confirmation email.
		sendOrderConfirmationEmail($orderId);
		sendReferralEmail($orderId);

		// Reload the page.
		header('Location: '.BASE_URL.'complete.php?orderId='.$orderId);
		exit(0);


	} else {

		// Name on card.
		if((!isset($_POST['firstNameOnCard'])) or (trim($_POST['firstNameOnCard']) == '')) {
			$errors[] = "Please enter the first name on your credit card.";
		} else {
			$firstNameOnCard = trim($_POST['firstNameOnCard']);
		}
		if((!isset($_POST['lastNameOnCard'])) or (trim($_POST['lastNameOnCard']) == '')) {
			$errors[] = "Please enter the last name on your credit card.";
		} else {
			$lastNameOnCard = trim($_POST['lastNameOnCard']);
		}

		// Card number.
		if((!isset($_POST['cardNumber'])) or (trim($_POST['cardNumber']) == '') or (!preg_match("/^[0-9\s\-]{13,}$/i", trim($_POST['cardNumber'])))) {
			$errors[] = "Please enter a valid credit card number.";
		} else {
			$cardNumber = preg_replace("/[^0-9]/", "", $_POST['cardNumber']);
			$last4 = substr($cardNumber, -4);
		}

		// Get date.
		if(!isset($_POST['expirationMonth']) or !preg_match("/^\d{1,2}$/i", trim($_POST['expirationMonth']))) {
			$errors[] = "Please select the expiration month.";
		} else {
			$expirationMonth = intval($_POST['expirationMonth']);
		}
		if(!isset($_POST['expirationYear']) or !preg_match("/^\d{2,4}$/i", trim($_POST['expirationYear']))) {
			$errors[] = "Please select the expiration year.";
		} else {
			$expirationYear = intval($_POST['expirationYear']);
		}
		if(!checkdate($expirationMonth, 4, $expirationYear)) {
			$errors[] = "You must enter a valid expiration date.";
		}
		if(mktime(0, 0, 0, $expirationMonth + 1, 1, $expirationYear) < time()) {
			$errors[] = "Your card has already expired.";
		}

		// Get the security number
		if((!isset($_POST['securityNumber'])) or (trim($_POST['securityNumber']) == '') or (!preg_match("/^[0-9]{3,4}$/", trim($_POST['securityNumber'])))) {
			$errors[] = "Please enter your card's security number.";
		} else {
			$securityNumber = trim($_POST['securityNumber']);
		}

		// Get the Address.
		if((!isset($_POST['addressLine1'])) or (trim($_POST['addressLine1']) == '')) {
			$errors[] = "Please enter your billing address.";
		} else {
			$addressLine1 = trim($_POST['addressLine1']);
		}

		// Get the city.
		if((!isset($_POST['city'])) or (trim($_POST['city']) == '')) {
			$errors[] = "Please enter your billing city.";
		} else {
			$city = trim($_POST['city']);
		}

		// Get the country.
		if((!isset($_POST['countryId'])) or (!isset($countries[intval($_POST['countryId'])]))) {
			$errors[] = "Please select your billing country.";
		} else {
			$countryId = intval($_POST['countryId']);
		}

		// Get the state.
		if($countryId == UNITED_STATES_COUNTRY_ID) {
			if((!isset($_POST['stateSelect'])) or (trim($_POST['stateSelect']) == '')) {
				$errors[] = "Please select your billing state.";
			} else {
				$state = trim($_POST['stateSelect']);
			}
		} else {
			if((!isset($_POST['stateTxt'])) or (trim($_POST['stateTxt']) == '')) {
				$errors[] = "Please enter your billing state.";
			} else {
				$state = trim($_POST['stateTxt']);
			}
		}

		// Get the zip.
		if((!isset($_POST['zip'])) or (trim($_POST['zip']) == '')) {
			$errors[] = "Please enter your billing zip / postal code.";
		} else {
			$zip = trim($_POST['zip']);
		}

		if(count($errors) == 0) {

			// Save the address.
			if(($order['customer']['address']['line1'] == $addressLine1) and ($order['customer']['address']['city'] == $city) and ($order['customer']['address']['state'] == $state) and ($order['customer']['address']['zip'] == $zip) and ($order['customer']['address']['country_id'] == $countryId)) {
				$addressId = $order['customer']['address_id'];
			} else {
				// Add to the database.
				$statement = $conn->prepare("INSERT INTO `retreat_addresses`(`line1`, `line2`, `city`, `state`, `zip`, `country_id`) VALUES(:line1, :line2, :city, :state, :zip, :countryId)");
				$statement->execute(array(':line1' => $addressLine1, ':line2' => '', ':city' => $city, ':state' => $state, ':zip' => $zip, ':countryId' => $countryId));

				// Get the address id.
				$addressId = $conn->lastInsertId();
			}

			// Update the user with the new billing address.
			$statement = $conn->prepare("UPDATE `retreat_users` SET `billing_address_id` = :addressId WHERE `id` = :userId");
			$statement->execute(array(':addressId' => $addressId, ':userId' => $order['customer_id']));

			$order['customer']['billing_address_id'] = $addressId;
			$order['customer']['billing_address'] = getAddress($addressId);

			// Get the billing variables.
			$address = $order['customer']['billing_address']['line1'];
			$city = $order['customer']['billing_address']['city'];
			$state = $order['customer']['billing_address']['state'];
			$zip = $order['customer']['billing_address']['zip'];
			$country = $order['customer']['billing_address']['country'];
			$phone = $order['customer']['day_phone'];
			$customerId = $order['customer_id'];
			$orderId = $order['id'];
			$amountToCharge = ($order['total'] + $sponsorshipAmount) / 100;
			$description = $order['event'].' reservation';

			// Process the payment.
			$result = processCurlPayment($firstNameOnCard, $lastNameOnCard, $cardNumber, $expirationMonth, $expirationYear, $amountToCharge, $address, $city, $state, $zip, $country, $phone, $customerId, 'NJR - '.$orderId, $description, (isset($_POST['test']) and ($_POST['test'])));

			if($result[0]) {

				// Transaction ID.
				$transactionId = $result[1];

				// Save the payment profile.
				define('AUTHORIZENET_LOG_FILE', __DIR__.'/include/authorizeNetLog.txt');
				$profileResult = createProfileFromTrans($transactionId, $useTestAccount);

				if($profileResult['approved']) {
					$customerProfileId = $profileResult['profilID'];
					$paymentProfileId = $profileResult['paymentProfilID'];
				} else {
					$customerProfileId = 'Error: '.$profileResult['errorMessage'];
					$paymentProfileId = null;
				}

				//try{

				// Add payment to database.
				$statement = $conn->prepare("INSERT INTO `retreat_orders_payments`(`order_id`, `amount`, `tax`, `payment_method`, `status`, `transaction_id`, `customer_profile_id`, `payment_profile_id`, `last4`, `address_id`) 
							VALUES(:orderId, :amount, :tax, 'credit card', 1, :transactionId, :customerProfileId, :paymentProfileId, :last4, :addressId)");
				$statement->execute(array(':orderId' => $orderId, ':amount' => ($order['price'] + $order['cme_price'] + $sponsorshipAmount), ':tax' => $order['tax'], ':transactionId' => $transactionId, ':customerProfileId' => $customerProfileId, ':paymentProfileId' => $paymentProfileId, ':last4' => $last4, ':addressId' => $order['customer']['billing_address_id']));

				// Update order
				$statement = $conn->prepare("UPDATE `retreat_orders` SET `status` = 4, `sponsorship_type_id` = :sponsorshipTypeId, `sponsorship_amount` = :sponsorshipAmount, `sponsorship_notes` = :sponsorshipNotes, `total_amount` = :orderTotal, `current_balance` = 0 WHERE `id` = :orderId");
				$statement->execute(array(':orderId' => $orderId, ':sponsorshipTypeId' => $sponsorshipTypeId, ':sponsorshipAmount' => $sponsorshipAmount, ':sponsorshipNotes' => $sponsorshipNotes, ':orderTotal' => $order['total'] + $sponsorshipAmount));

				// Add the guests to the CRM.
				foreach($order['guests'] as $guest) {
					$statement = $conn->prepare("UPDATE `retreat_users` SET `user_type_id` = :user_type_id WHERE `id` = :userId");
					$statement->execute(array(':user_type_id' => $guest['user_type_id'], ':userId' => $guest['user_id']));
				}

				$referral = generateReferral($orderId);

				// Send confirmation email.
				sendOrderConfirmationEmail($orderId);
				sendReferralEmail($orderId);

				// Remove duplicate users.
				removeDuplicateUsers($orderId);
				$order = getOrder($orderId);

				//}catch(Exception $e){echo $e->getMessage();}
				// Reload the page.
				header('Location: '.BASE_URL.'checkout.php?orderId='.$orderId);
				exit(0);

			} else {

				echo "<!--".print_r($result, true)."-->";
				$errors[] = 'Cannot process payment - '.$result[1];

			}

		}

	}

}

// Set the template variables.
$templateVariables = array('orderId' => $orderId, 'order' => $order, 'sponsorships' => $sponsorships, 'errors' => $errors, 'countries' => $countries, 'states' => $states);

// Load the template.
include_once('templates/checkout.php');


?>