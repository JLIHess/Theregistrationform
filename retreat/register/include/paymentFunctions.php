<?php

require_once 'anet_php_sdk/AuthorizeNet.php';

function processPayment($nameOnCard="", $card_num, $expirationMonth, $expirationYear, $amount, $address="", $city="", $state="", $zip="", $country="", $phone="", $customer_id, $orderId="", $description="", $test = false) {
	
	//Setting Merchant Credentials
	if ($test == true) {
		$loginID = "8x4nW5hFMd";
		$transactionKey = "4j7qz26P49P7SbKB";
	} else {
		//$loginID = "2RyQw6s89Xtk";
		//$transactionKey = "67GqHX6J4xu93vvk";
		$loginID = "7zGL8Gev3H";
		$transactionKey = "74m8ZSwHuZ8334xb";
	}
	
	$authAIMObj = new AuthorizeNetAIM($loginID, $transactionKey);
	$authAIMObj->setSandbox($test);
	
	//prepare fields
	$exp_date = $expirationMonth.'/'.$expirationYear;
	
	$authAIMObj->setFields(
				array(
					'amount' => $amount,
					'card_num' => $card_num,
					'exp_date' => $exp_date ,
					'cust_id' => $customer_id
				)
			);
	
	//process payment
	$authCapResponse = $authAIMObj->authorizeAndCapture();
						
	if ($authCapResponse->approved) { //success process payment
		$transactionID = $authCapResponse->transaction_id;
		return array('approved'=>true, 'transactionID'=>$transactionID);
	} else { //failed to process payment
		$errorMessage = $authCapResponse->error_message;
		return array('approved'=>false, 'errorMessage'=>$errorMessage);
	}

}

function createProfileFromTrans ($transactionID, $test) {
	
	//Setting Merchant Credentials
	if ($test == true) {
		//$loginID = "8x4nW5hFMd";
		//$transactionKey = "4j7qz26P49P7SbKB";
		$loginID = "8Yb7gUMq37QH";
		$transactionKey = "2S3G762rdkdH6Dk7";
	} else {
		//$loginID = "2RyQw6s89Xtk";
		//$transactionKey = "67GqHX6J4xu93vvk";
		$loginID = "7zGL8Gev3H";
		$transactionKey = "74m8ZSwHuZ8334xb";
	}
		
	$authCIMObj = new AuthorizeNetCIM($loginID, $transactionKey);
	$authCIMObj->setSandbox($test);
//	echo $transactionID;
	$profileFromTransResponse = $authCIMObj->createCustomerProfileFromTransaction($transactionID);
	
	if($profileFromTransResponse->getResultCode()=="Ok") { //success to create profile
	
		$profilID = $profileFromTransResponse->getCustomerProfileId();
		$paymentProfilID = $profileFromTransResponse->getCustomerPaymentProfileIds();
		return array('approved'=>true, 'profilID'=>$profilID, 'paymentProfilID'=>$paymentProfilID);
	
	} else { // failed to create profile
		$errorMessage = $profileFromTransResponse->getErrorReason();
		return array('approved'=>false, 'errorMessage'=>$errorMessage);	
	}
}

?>