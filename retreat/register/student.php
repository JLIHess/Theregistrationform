<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the Session.
session_start();

// Import necessary files.
include_once('include/config.php');
include_once('include/functions.php');

// Redirect to HTTPS page.
if((getenv('APPLICATION_ENV') != 'development' && $useSecureSite) and  ((!isset($_SERVER['HTTPS'])) or ($_SERVER['HTTPS'] == ""))) {
	header("Location: https://".$_SERVER['HTTP_HOST'].strtok($_SERVER["REQUEST_URI"],'?'));
	exit();
}

$errors = [];

// Get the countries / states.
$countries = getCountries();
$states = getStates(UNITED_STATES_COUNTRY_ID);
$campuses = getCampuses();
$years = getStudentYears();

// Check if the order is complete.
if(isset($_GET['complete']) and $_GET['complete']) {

	// Make sure an order was made.
	if(!isset($_SESSION['retreat']['student']['orderId'])) {
		header('Location: student.php');
		exit(0);
	}

	// Set the template variables.
	$templateVariables = array('contactEmail' => $studentFromEmail);

	// Load the template.
	include_once('templates/student'.((isset($_GET['cc']) and $_GET['cc']) ? 'CC' : '').'Complete.php');
	exit(0);

}

// Check if we are supposed to get the student's CC info.
if(isset($_GET['cc']) and $_GET['cc']) {

	// Make sure the URL contains an order id.
	if(!isset($_GET['orderId'])) {

		// If there is no order in the URL then they came here in Error.
		// Redirect them to student registration page.
		header('Location: '.BASE_URL.'student.php');
		exit(0);

	}

	// Get the  order.
	$orderId = intval($_GET['orderId']);
	$order = getOrder($orderId);

	// Make sure the order exists, and that we are waiting for the CC information.
	if(($order === null) or ($order['status'] != 9)) {

		header('Location: '.BASE_URL.'student.php');
		exit(0);

	}

	// Check if the form was submitted.
	if(isset($_POST['submit']) and ($_POST['submit'] == 'Purchase')) {

		// Get the variables.

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

			// Load authorize.net class.
			require_once($authorizeDotNetClass);
			$aNetCim = new AuthorizeDotNetCim(array('testMode' => $useTestAccount));

			// Get the customer information.
			$customerInformation = array('customerId' => 'RetreatOrder'.$orderId, 'email' => $order['customer']['email']);

			// Create customer profile.
			$response = $aNetCim->createCustomerProfile($customerInformation);

			// Make sure the customer profile was created.
			if(!$response['success']) {

				$errors[] = "Could not create customer profile.\\n".implode("\\n", $response['errors']);

			} else {

				// Save the profile ID
				$customerProfileId = $response['customerProfileId'];

				// Create payment profile.
				$paymentInformation = array(
					'firstName' => $firstNameOnCard,
					'lastName' => $lastNameOnCard,
					'address' => $address,
					'city' => $city,
					'state' => $state,
					'zip' => $zip,
					'country' => $country,
					'phone' => $phone,
					'cc' => $cardNumber,
					'exMonth' => str_pad($expirationMonth, 2, "0", STR_PAD_LEFT),
					'exYear' => $expirationYear
				);
				$response = $aNetCim->createPaymentProfile($paymentInformation);

				// Make sure the payment profile was created.
				if(!$response['success']) {

					$errors[] = "Could not validate the credit card.\n".implode("\n", $response['errors']);

				} else {

					$paymentProfileId = $response['paymentProfileId'];

					$statement = $conn->prepare("INSERT INTO `retreat_orders_payments`(`order_id`, `amount`, `tax`, `payment_method`, `status`, `customer_profile_id`, `payment_profile_id`, `last4`, `address_id`) 
                                VALUES(:orderId, 0, 0, 'credit card validation', 1, :customerProfileId, :paymentProfileId, :last4, :addressId)");
					$statement->execute(array(':orderId' => $orderId, ':customerProfileId' => $customerProfileId, ':paymentProfileId' => $paymentProfileId, ':last4' => $last4, ':addressId' => $order['customer']['billing_address_id']));

					// Update order
					$statement = $conn->prepare("UPDATE `retreat_orders` SET `status` = 5 WHERE `id` = :orderId");
					$statement->execute(array(':orderId' => $orderId));

					// Save the order id in the session.
					$_SESSION['retreat']['student'] = ['orderId' => $orderId];

					// Send email.
					include_once(__DIR__ . '/include/emailFunctions.php');
					$emailResult = sendEmailWithSendGrid($studentBccEmails, $fromEmail, "Jretreat registration CC form.", "User ".$order['customer']['first_name'].' '.$order['customer']['last_name']." filled out their CC information.", ['fromName' => 'Sinai Scholars Retreat']);

					// Reload the page.
					header('Location: '.BASE_URL.'student.php?cc=1&complete=1');
					exit(0);

				}


			}

		}

	}
	// Set the template variables.
	$templateVariables = array('order' => $order, 'orderId' => $orderId, 'countries' => $countries, 'states' => $states, 'errors' => $errors);

	// Load the template.
	include_once('templates/studentCreditCard.php');
	exit(0);

}

// Check if this is a request for the profile image.
if(isset($_GET['action']) and $_GET['action'] == 'getImage') {
	if(isset($_SESSION['retreat']['student']['profilePhoto']['name'])) {
		header('Content-type: image/'.strtolower(pathinfo($_SESSION['retreat']['student']['profilePhoto']['name'], PATHINFO_EXTENSION)));
		header('Content-Transfer-Encoding: binary');
		ob_clean();
		flush();
		echo $_SESSION['retreat']['student']['profilePhoto']['file'];
	}
	exit(0);
}

// Check if the form was submitted.
if(count($_POST) > 0 ) {

	// Validate the form.
	$errors = validateStudentInformation($_POST);

	if(count($errors) == 0) {

		// Save the information.
		$orderId = saveStudentInformation($_POST);

		// Send out the emails.
		sendStudentRegistrationEmails($orderId);

		// Save the order id in the session.
		$_SESSION['retreat']['student'] = ['orderId' => $orderId];

		// Redirect.
		header('Location: student.php?complete=1');
		exit();

	}

}

// Set the template variables.
$templateVariables = array('countries' => $countries, 'states' => $states, 'campuses' => $campuses, 'years' => $years, 'errors' => $errors, 'validImageExtensions' => $validImageExtensions);

// Load the template.
include_once('templates/student.php');

?>