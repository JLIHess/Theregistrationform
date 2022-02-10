<?php

	date_default_timezone_set('America/New_York');

	// Create a variable to store the database login information.
	$database = array();
	
		define("BASE_URL","https://myjli.com/retreat_new/register/");
		define("SELECT_ROOMS_URL","https://myjli.com/retreat_new/");
		$database["host"]="localhost";
		$database["user"]="jli";
		$database["password"]="077ilj";
		$database["dbName"]="jli3-new";
		$useTestAccount = true;
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		$fromEmail = 'info@jretreat.com';
		$bccEmails = array('test@myjli.com');
		// The following line was created to send a custom 'order notification email' to Shmuly Karp.
		$customNotificationEmail = 'test@myjli.com';
		$studentFromEmail = 'retreat@sinaischolars.com';
		$studentBccEmails = array('test@myjli.com');
		$useSecureSite = true;

	define("UNITED_STATES_COUNTRY_ID", 840);
	$suggestedRelationships = array('Spouse', 'Sibling', 'Child', 'Parent', 'Friend');

	// Authorize.net login.
    $authorizeDotNetClass = __DIR__ . "/../../../../common/clases/authorizedotnetcim.php";
	if ($useTestAccount == true) {
		$authorizeNetLoginID = "8x4nW5hFMd";
		$authorizeNetTransactionKey = "4j7qz26P49P7SbKB";
		$authorizeDotNetUrl = 'https://test.authorize.net/gateway/transact.dll';	
	} else {
		//$authorizeNetLoginID = "2RyQw6s89Xtk";
		//$authorizeNetTransactionKey = "67GqHX6J4xu93vvk";
		$authorizeNetLoginID = "7zGL8Gev3H";
		$authorizeNetTransactionKey = "74m8ZSwHuZ8334xb";
		$authorizeDotNetUrl = 'https://secure.authorize.net/gateway/transact.dll';	
	}
	
	$userIsAdmin = (isset($_SESSION['retreatAdmin']) and $_SESSION['retreatAdmin']);
	
	// Connect to the database.
    $conn = new PDO("mysql:host={$database['host']};dbname={$database['dbName']}", $database['user'], $database['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Student registration.
    $validImageExtensions = ['png', 'jpeg', 'jpg', 'gif'];
    $imageUploadDirectory = '/home/jli/servidor/vivo/www/public/retreat_new/register/uploads/';
    


?>
