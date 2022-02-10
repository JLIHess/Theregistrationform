<?php
	// Set template variables.
	$templateVariables['page'] = 'complete';
	$templateVariables['title'] = 'Registration Complete';

	// Display header.
	include('header.php');
?>

<div class="pageContent">
    <h1 class="pageHeader">Application successfully submitted</h1>
    <div style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; font-weight: bold; text-align: center; padding: 50px 100px;">
        Thank you for applying to the 12 Annual Sinai Scholars National Jewish Retreat. The Sinai Scholars office will contact you shortly for an interview. If you have an inquiry, please email &nbsp;<a href=
        "mailto:<?php echo htmlentities($templateVariables['contactEmail']); ?>" target="_blank"><?php echo htmlentities($templateVariables['contactEmail']); ?></a>.
	</div>
	<div class="clear"></div>
</div>

<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/css/complete.css"></script>
<?php
	// Display header.
	include('footer.php');
?>