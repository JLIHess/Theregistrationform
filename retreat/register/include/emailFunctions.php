<?php


	function sendMailWithMandrill($to, $fromEmail, $subject, $htmlMessage, $otherOptions=null) {
		
		// Get optional options.
		if($otherOptions === null) $otherOptions = array();
		$textMessage = isset($otherOptions['textMessage']) ? $otherOptions['textMessage'] : strip_tags($htmlMessage);
		$fromName = isset($otherOptions['fromName']) ? $otherOptions['fromName'] : '';
		$replyToEmail = isset($otherOptions['replyToEmail']) ? $otherOptions['replyToEmail'] : $fromName;
		$replyToName = isset($otherOptions['replyToName']) ? $otherOptions['replyToName'] : $fromName;
		
		require_once("Mandrill.php");
		
		$apikey = "vqVg0oae9DMpn_RVAP0hgA";
		
		$mandrill=new Mandrill($apikey);
			
		$message = array(
			'html' => $htmlMessage,
			'text' => $textMessage,
			'subject' => $subject,
			'from_email' => $fromEmail,
			'from_name' => $fromName,
			'to' => array(),
			'headers' => array('Reply-To' => $replyToEmail),
			'important' => false,
			'track_opens' => true,
		);
		// Add addresses
		if(is_array($to)) {
			foreach($to as $emailAddress) $message['to'][] = array('email' => $emailAddress, 'name' => $emailAddress, 'type' => 'to');
		} else {
			$message['to'][] = array('email' => $to, 'name' => $to, 'type' => 'to');
		}
		if(isset($otherOptions['cc'])) {
			if(is_array($otherOptions['cc'])) {
				foreach($otherOptions['cc'] as $emailAddress) $message['to'][] = array('email' => $emailAddress, 'name' => $emailAddress, 'type' => 'cc');
			} else {
				$message['to'][] = array('email' => $otherOptions['cc'], 'name' => $otherOptions['cc'], 'type' => 'cc');
			}
		}
		if(isset($otherOptions['bcc'])) {
			if(is_array($otherOptions['bcc'])) {
				foreach($otherOptions['bcc'] as $emailAddress) $message['to'][] = array('email' => $emailAddress, 'name' => $emailAddress, 'type' => 'bcc');
			} else {
				$message['to'][] = array('email' => $otherOptions['bcc'], 'name' => $otherOptions['bcc'], 'type' => 'bcc');
			}
		}
//		$result = $mandrill->messages->send($message, $async, $ip_pool);

		// Try to send the email.
		try {
		
			$result = $mandrill->messages->send($message);
			
			// Check if it was sent.
			if($result[0]["status"]=="sent") {
				return array('sent' => true, 'error' => '');
			} else {
				return array('sent' => false, 'error' => $result[0]["reject_reason"]);
			}
		
		// Catch errors.
		} catch(Mandrill_Error $e) {
			return array('sent' => false, 'error' => get_class($e).' - '.$e->getMessage());
		}
		
	}
	
	/*
	// This function does not work because Sendgrid does not support CC and BCC using their web API (you need to use SMTP, as I use in the new function)
	function OLDsendEmailWithSendGrid($to, $fromEmail, $subject, $htmlMessage, $otherOptions=null) {
		
		$url = 'https://api.sendgrid.com/';
		$user = 'mendye-jli';
		$pass = 'JLIcentral770';

		// Get optional options.
		if($otherOptions === null) $otherOptions = array();
		$textMessage = isset($otherOptions['textMessage']) ? $otherOptions['textMessage'] : strip_tags($htmlMessage);
		if(isset($otherOptions['fromName'])) $fromEmail = $otherOptions['fromName'].'<'.$fromEmail.'>';
		$replyToEmail = isset($otherOptions['replyToEmail']) ? $otherOptions['replyToEmail'] : $fromEmail;
		if(isset($otherOptions['replyToName'])) $replyToEmail = $otherOptions['replyToName'].'<'.$replyToEmail.'>';
		if(!is_array($to)) $to = array($to);
		$cc = (isset($otherOptions['cc']) ? (is_array($otherOptions['cc']) ? $otherOptions['cc'] : array($otherOptions['cc'])) : array());
		$bcc = (isset($otherOptions['bcc']) ? (is_array($otherOptions['bcc']) ? $otherOptions['bcc'] : array($otherOptions['bcc'])) : array());

		$json_string = array(
		  'to' => $to,
		  'category' => 'Retreat'
		);
		if(count($cc) > 0) $json_string['cc'] = $cc;
		if(count($bcc) > 0) $json_string['bcc'] = $bcc;


		$params = array(
				'api_user'  => $user,
				'api_key'   => $pass,
				'x-smtpapi' => json_encode($json_string),
				'to'        => $to,
				'subject'   => $subject,
				'html'      => $htmlMessage,
				'text'      => $textMessage,
				'from'      => $fromEmail
			);
		if(count($cc) > 0) $params['cc'] = $cc;
		if(count($bcc) > 0) $params['bcc'] = $bcc;


		$request =  $url.'api/mail.send.json';

		// Generate curl request
		$session = curl_init($request);
		// Tell curl to use HTTP POST
		curl_setopt ($session, CURLOPT_POST, true);
		// Tell curl that this is the body of the POST
		curl_setopt ($session, CURLOPT_POSTFIELDS, http_build_query($params));
		// Tell curl not to return headers, but do return the response
		curl_setopt($session, CURLOPT_HEADER, false);
		// Tell PHP not to use SSLv3 (instead opting for TLS)
		//curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		// obtain response
		$response = curl_exec($session);
		curl_close($session);
echo 'done'.var_dump($response);

		// print everything out
		print_r($response);
		
	}
	*/
	
	function sendEmailWithSendGrid($to, $fromEmail, $subject, $htmlMessage, $otherOptions=null) {
		
		$user = 'mendye-jli';
		$pass = 'JLIcentral770';

		// Get optional options.
		if($otherOptions === null) $otherOptions = array();
		$textMessage = isset($otherOptions['textMessage']) ? $otherOptions['textMessage'] : strip_tags($htmlMessage);
		$fromName = isset($otherOptions['fromName'])? $otherOptions['fromName'] : '';
		$replyToEmail = isset($otherOptions['replyToEmail']) ? $otherOptions['replyToEmail'] : $fromEmail;
		$replyToName = isset($otherOptions['replyToName']) ? $otherOptions['replyToName'] : $fromName;
		if(!is_array($to)) $to = array_filter(explode(',', $to), 'strlen');
		$cc = (isset($otherOptions['cc']) ? (is_array($otherOptions['cc']) ? $otherOptions['cc'] : array($otherOptions['cc'])) : array());
		$bcc = (isset($otherOptions['bcc']) ? (is_array($otherOptions['bcc']) ? $otherOptions['bcc'] : array($otherOptions['bcc'])) : array());
	
		// Include phpmailer.
		require_once('phpMailer/PHPMailerAutoload.php');
		
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = "smtp.sendgrid.com";
		$mail->Port = 587;
		$mail->Username = $user;
		$mail->Password = $pass;

		$mail->SetFrom($fromEmail, $fromName);
		foreach($to as $email) $mail->AddAddress($email);
		foreach($cc as $email) $mail->AddCc($email);
		foreach($bcc as $email) $mail->AddBcc($email);
		$mail->AddReplyTo($replyToEmail, $replyToName);
		$mail->Subject = $subject;
		$mail->Body = $htmlMessage;
		$mail->AltBody  = $textMessage;
		$sent = $mail->Send();

		// Check if it was sent.
		if($sent) {
			return array('sent' => true, 'error' => '');
		} else {
			return array('sent' => false, 'error' => $mail->ErrorInfo);
		}
		
	}
?>