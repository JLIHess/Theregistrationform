<?php

	// Set template variables.
	$templateVariables['page'] = 'complete';
	$templateVariables['title'] = 'Payment Information Verified';

	// Display header.
	include('header.php');
	
?>
<div class="pageContent">
    <h1 class="pageHeader"><?php echo $templateVariables['title']; ?></h1>
    <div style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; font-weight: bold; text-align: center; padding: 50px 100px;">
        Thank you for submitting your payment information.<br />
        If you have any questions, please email &nbsp;<a href=
        "mailto:<?php echo htmlentities($templateVariables['contactEmail']); ?>" target="_blank"><?php echo htmlentities($templateVariables['contactEmail']); ?></a>.
	</div>
	<div class="clear"></div>
</div>
<link rel="stylesheet" type="text/css" href='<?php echo BASE_URL; ?>/css/complete.css'></script>
<?php

	// Display header.
	include('footer.php');
	
?>