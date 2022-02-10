
<?php
require('../include/config.php');
require('../include/functions.php');




$results = Array(
'success' => false,
'messages' => [],
);

if ( count($_POST) > 0 ){

    $results = [];

    foreach($_POST["contact"] as $key => $value){
        if(!$value["email"] && !$value["name"]) continue;
        $referralId = $_POST["referral_id"];
        $toEmail = $value["email"];
        $toName = $value["name"] ? $value["name"] : chr(8);

        $email = filter_var($toEmail, FILTER_SANITIZE_EMAIL);
        // Validate e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $results[$key]['messages'] =  "email $key is an invalid email address";
            $results[$key]['success'] = false;
            
        } else{

            //sendReferralLinkEmail($referralId,  $toName, $toEmail, $subject = 'Will you join me? ')
            $emailResults = sendReferralLinkEmail($referralId,  $toName, $toEmail, $subject = 'Will you join me? ');
                if($emailResults['sent']){
                    $results[$key]['success'] = true;
                    $results[$key]['messages'] =  "email sent to $email";
                }else{
                    $results[$key]['messages'] = $emailResults['error'];
                }    

        }
    }


    echo json_encode($results );
    exit;

}

if ( !isset($_REQUEST["name"])  || !isset($_REQUEST["referralId"]) || !isset($_REQUEST["email"]) ){

    $results['messages'] =  "Please supply an email, name and referral Id";
    echo json_encode($results );
    exit;
}

$toEmail = $_REQUEST["email"];
$toName = $_REQUEST["name"];
$referralId = $_REQUEST["referralId"];

// Remove all illegal characters from email
$email = filter_var($toEmail, FILTER_SANITIZE_EMAIL);
// Validate e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $results['messages'] =  "$email is an invalid email address";
	
} else{

    //sendReferralLinkEmail($referralId,  $toName, $toEmail, $subject = 'Will you join me? ')
    $emailResults = sendReferralLinkEmail($referralId,  $toName, $toEmail, $subject = 'Will you join me? ');
        if($emailResults['sent']){
            $results['success'] = true;
            $results['messages'] =  "email sent to $email";
        }else{
            $results['messages'] = $emailResults['error'];
        }    

}

echo json_encode($results );


?>