<?php

session_start();

// Include necessary files.
require_once('include/config.php');
require_once('include/functions.php');

// Make sure an action was requested.
if(!isset($_GET['action'])) exit(0);

// Check what action was requested.
switch(strtolower(trim($_GET['action']))) {

	// Get order.
	case 'getorder':

		// Make sure an order Id was requested.
		if(!isset($_GET['orderId'])) {
			echo '2Missing parameter';
			exit(0);
		}

		// Get the order Id
		$orderId = intval(trim($_GET['orderId']));

		// Make sure they have authorization to access this order.
		if(!isset($_SESSION['retreat']['orderId']) or ($_SESSION['retreat']['orderId'] != $orderId)) {
			echo '0';
			exit(0);
		}

		// Get the order.
		echo '1'.json_encode(getOrder($orderId));

		break;

	case 'updateguestinformation':

		// Make sure the guest id was sent.
		if(!isset($_POST['id'])) {
			echo json_encode(array('success' => false, 'error' => 'No guest ID entered'));
			exit(0);
		}

		$guestId = intval($_POST['id']);

		// Get the guest information.
		$guest = getGuest($guestId);

		// Make sure the guest exists.
		if($guest === null) {
			echo json_encode(array('success' => false, 'error' => 'Guest not found'));
			exit(0);
		}

		// Make sure they have authorization to access this order.
		if(!isset($_SESSION['retreat']['orderId']) or ($_SESSION['retreat']['orderId'] != $guest['order_id'])) {
			echo json_encode(array('success' => false, 'error' => '0'));
			exit(0);
		}

		// Validate the information.
		$validation = validateGuestInformation($guest, $_POST);
		if(!$validation[0]) {
			echo json_encode(array('success' => false, 'error' => $validation[1]));
			break;
		}

		// Save the information.
		saveGuestInformation($guest, $_POST);

		// Respond OK.
		echo json_encode(array('success' => true));
		break;
	case 'validateGuestEmailAddress':
		global $conn;

		$result = ['valid' => true, 'message' => ''];

		if (!empty($_REQUEST['order_id']) && !empty($_REQUEST['email']) && filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {

			$sql = 'SELECT COUNT(u.*) AS exists
					FROM retreat_orders_rooms_users oru 
					INNER JOIN retreat_users u ON u.id = oru.user_id WHERE oru.order_id = :id 
					AND u.email LIKE :e';

			$statement = $conn->prepare($sql);
			$statement->execute([':id' => abs($_REQUEST['order_id']), ':e' => $_REQUEST['email']]);

			if ($statement->rowCount() > 0) {
				$data = $statement->fetch(PDO::FETCH_ASSOC);

				if ($data['exists']) {
					$result = ['valid' => false, 'message' => 'This email address already exists in this registration.'];
				}
			}
		}

		echo json_encode($result);
		break;
}

?>