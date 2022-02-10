<?php

require('../include/config.php');
require('../include/functions.php');


$results = Array(
'success' => false,
'messages' => [],
);

$data = Array(
    'phone'    => '',
    'address'  => '',
    'address2' =>'' ,
    'city'     =>'' ,
    'state'    =>'' ,
    'zip'      =>'' ,
    'country'  =>'' ,
    'name'     =>'' ,
    'comments' =>'' ,
    'type'     =>'' ,

);

if ( count($_POST) > 0 ){

    $results = [];

    foreach($_POST["contact"] as $key => $value){

        if( $_POST["type"] == "phone" && $value["name"] == "" && $value["phone"]== "" ) {continue;}
        if( $_POST["type"] == "mail"  && $value["name"] == "" && $value["address"]== "" ) {continue;}

        $results[$key]['success'] = false;
        if ( !isset($value["name"]) ){
            $results[$key]['messages'] =  "Please enter a valid name";
            continue;
            
        }
        if (  !isset($_POST["referral_id"]) || !isset($_POST["type"]) ){

            $results[$key]['messages'] =  "Request is not valid ";
            continue;
        }

        if ( isset($_POST["type"])  && ($_POST["type"] == "phone" && !isset($value["phone"])) ){

            $results[$key]['messages'] =  "Please enter a a valid phone number";
            continue;
        }
        
        if ( isset($_POST["type"])  && ($_POST["type"] == "mail" && !isset($value["address"])) ){
        
            $results[$key]['messages'] =  "Please enter a a valid address";
            continue;
        }
        if ( isset($_POST["type"])  && ($_POST["type"] == "mail" && !isset($value["city"])) ){
        
            $results[$key]['messages'] =  "Please enter a a valid city";
            continue;
        }
        if ( isset($_POST["type"])  && ($_POST["type"] == "mail" && !isset($value["zip"])) ){
        
            $results[$key]['messages'] =  "Please enter a a valid zip/postal code";
            continue;
        }
        if ( isset($_POST["type"])  && ($_POST["type"] == "mail" && !isset($value["state"])) ){
        
            $results[$key]['messages'] =  "Please enter a a valid state";
            continue;
        }

        $referralId         =     isset($_POST["referral_id"]) ? $_POST["referral_id"] : '';
        $data['type']       =     isset($_POST["type"]) ? $_POST["type"] : '';
        $data['phone']      =     isset($value["phone"]) ? $value["phone"] : '';
        $data['address']    =     isset($value["address"]) ? $value["address"] : '';
        $data['address2']   =     isset($value["address2"]) ? $value["address2"] : '';
        $data['city']       =     isset($value["city"]) ? $value["city"] : '';
        $data['state']      =     isset($value["state"]) ? $value["state"] : '';
        $data['zip']        =     isset($value["zip"]) ? $value["zip"] : '';
        $data['country']    =     isset($value["country"]) ? $value["country"] : '';
        $data['comments']   =     isset($value["comments"]) ? $value["comments"] : '';
        $data['name']       =     isset($value["name"]) ? $value["name"] : '';


        $contactResults = createReferralContact($referralId, $data);

        if($contactResults['success']){
            $results[$key]['success'] = true;
            $results[$key]['messages'] =  "record has been saved";
        }else{
            $results[$key]['messages'] = $contactResults['error'];
        }  
        //echo json_encode($data );
        


            }
echo json_encode($results );
exit;

}




if ( !isset($_REQUEST["name"]) ){

    $results['messages'] =  "Please enter a valid name 11";
    echo json_encode($results );
    exit;
}
if (  !isset($_REQUEST["referralId"]) || !isset($_REQUEST["type"]) ){

    $results['messages'] =  "Request is not valid";
    echo json_encode($results );
    exit;
}

if ( isset($_REQUEST["type"])  && ($_REQUEST["type"] == "phone" && !isset($_REQUEST["phone"])) ){

    $results['messages'] =  "Please enter a a valid phone number";
    echo json_encode($results );
    exit;
}

if ( isset($_REQUEST["type"])  && ($_REQUEST["type"] == "mail" && !isset($_REQUEST["address"])) ){

    $results['messages'] =  "Please enter a a valid address";
    echo json_encode($results );
    exit;
}
if ( isset($_REQUEST["type"])  && ($_REQUEST["type"] == "mail" && !isset($_REQUEST["city"])) ){

    $results['messages'] =  "Please enter a a valid city";
    echo json_encode($results );
    exit;
}
if ( isset($_REQUEST["type"])  && ($_REQUEST["type"] == "mail" && !isset($_REQUEST["zip"])) ){

    $results['messages'] =  "Please enter a a valid zip/postal code";
    echo json_encode($results );
    exit;
}
if ( isset($_REQUEST["type"])  && ($_REQUEST["type"] == "mail" && !isset($_REQUEST["state"])) ){

    $results['messages'] =  "Please enter a a valid state";
    echo json_encode($results );
    exit;
}

$referralId         =     isset($_REQUEST["referralId"]) ? $_REQUEST["referralId"] : '';
$data['type']       =     isset($_REQUEST["type"]) ? $_REQUEST["type"] : '';
$data['phone']      =     isset($_REQUEST["phone"]) ? $_REQUEST["phone"] : '';
$data['address']    =     isset($_REQUEST["address"]) ? $_REQUEST["address"] : '';
$data['address2']   =     isset($_REQUEST["address2"]) ? $_REQUEST["address2"] : '';
$data['city']       =     isset($_REQUEST["city"]) ? $_REQUEST["city"] : '';
$data['state']      =     isset($_REQUEST["state"]) ? $_REQUEST["state"] : '';
$data['zip']        =     isset($_REQUEST["zip"]) ? $_REQUEST["zip"] : '';
$data['country']    =     isset($_REQUEST["country"]) ? $_REQUEST["country"] : '';
$data['comments']   =     isset($_REQUEST["comments"]) ? $_REQUEST["comments"] : '';
$data['name']       =     isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';


$contactResults = createReferralContact($referralId, $data);

if($contactResults['success']){
    $results['success'] = true;
    $results['messages'] =  "record has been saved";
}else{
    $results['messages'] = $contactResults['error'];
}  
//echo json_encode($data );
echo json_encode($results );






?>