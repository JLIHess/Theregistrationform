<?php
	session_start();
    if(isset($_GET['admin']) and ($_GET['admin'] == 'af4eiuqr')) $_SESSION['admin'] = true;
	
	$order = array(
		'id' => null,
		'eventId' => 14,
		'promotionId' => null,
		'earlyBird' => false,
		'isAdmin' => false,
		'internalNote' => 'Test note',
		'rooms' => array(
			0 => array(
				'roomTypeId' => 3,
				'bedTypeId' => 2,
				'occupancy' => 1,
				'adult' => 1,
				'teen' => 0,
				'child' => 0,
				'infant' => 0,
				'programStartDate' => '2015-01-02',
				'programEndDate' => '2015-02-02',
				'hotelStartDate' => '2015-01-02',
				'hotelEndDate' => '2015-02-02',
				'babysitter' => array('day1' => 80, 'day3' => 40),
				'price' => 10725,
				'tax' => 725
			)
		)
	);
	
	if(isset($_POST['order'])) {
		
		try {
		
			$_SESSION['roomingInfoSentFromPage1'] = json_decode($_POST['order'], true);
		
		} catch(Exception $e) {
		
			echo 'there was an error processing input.';
			$error = true;
			unset($_SESSION['retreat']['orderCreated']);
			
		}
		
		if(!$error) {
			
			header('Location: guestRegistrationForm.php');
			exit(0);
			
		}
	}
	
	if(isset($_SESSION['roomingInfoSentFromPage1'])) {
	
		echo 'Order:<br /><br />';
		echo nl2br(print_r($_SESSION['roomingInfoSentFromPage1'], true))."<br /><br />";
		
	}

?>
<html>
	<body onload="load();">
		<script>
			var order = <?php echo json_encode($order); ?>;
			function load() {
				document.getElementById('txtOrder').innerHTML = JSON.stringify(order, null, 4);
			};
		</script>
		<form method="post">
			<textarea name="order" id="txtOrder" style="width: 900px; height:500px;"><?php echo json_encode($order); ?></textarea><br />
			<input type="submit" value="Go" />
		</form>
	</body>
</html>