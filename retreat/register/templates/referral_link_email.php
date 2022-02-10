<?php //$templateVariables = array('reforder' => $refOrder, 'referral'=> $referral, 'name'=>$toName, 'email'=>$toEmail); ?>

<html>
    <body id="body">
    <p> Hi <?php echo $templateVariables['name']; ?>,

<p>I'm excited to be attending the JLI Retreat this July 31-August 5 in Providence, Rhode Island.</p>

<p>Will you join me?</p>

<p>Use this <a href="<?php echo SELECT_ROOMS_URL ?>?promo=<?php echo $templateVariables['referral']['code']; ?>"> link</a> 
(or coupon code <span style="text-transform:uppercase"><?php echo $templateVariables['referral']['code']; ?></span>) to save $50 on your registration.</p>

<p>I'm looking forward to having an awesome time together!</p>

<p>Best,</p>

    <p><?php echo $templateVariables['reforder']['customer']['first_name'].' '.$templateVariables['reforder']['customer']['last_name']; ?></p>

    
    </body>

</html>