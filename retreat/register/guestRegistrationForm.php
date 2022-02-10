<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
// Start the session.
if (isset($_GET['sessionId']) and ($_GET['sessionId'] != '')) {
    session_start($_GET['sessionId']);
    session_regenerate_id(true);
//      echo "SESSION:<br /><pre>".print_r($_SESSION, true)."</pre><br /><br />";
    header("Location: https://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
} else {
    session_start();
}

// Import necessary files.
require('include/config.php');
require('include/functions.php');

// Redirect to HTTPS page.
if (getenv('APPLICATION_ENV') != 'development'
    && $useSecureSite and ((!isset($_SERVER['HTTPS'])) or ($_SERVER['HTTPS'] == ""))
) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?') . "?sessionId=" . session_id());
    exit();
}

// Check if they have an order in the SESSION variable.
if (isset($_SESSION['roomingInfoSentFromPage1'])) {

    // Save the order to the database.
    $order = $_SESSION['roomingInfoSentFromPage1'];

    // Make sure it has at least 1 room with 1 adult.
    if ((count($order['rooms']) < 1) or ($order['rooms'][0]['adult'] < 1)) {
        echo '<pre>';
        //print_r($_SESSION);
        echo '</pre>';
        echo '<br /><br />Error: Order must have at least 1 room with 1 Adult.';
        exit(0);
    }

    // Create order.
    if (isset($order['id']) and $order['id']) {
        exit(0);
    } else {
        $statement = $conn->prepare("INSERT INTO `retreat_orders`(`customer_id`, `event_id`, `status`, `promotion_id`, `early_bird`, `is_admin`, `time_created`)
                VALUES(NULL, :eventId, 1, :promotionId, :earlyBird, :isAdmin, CURRENT_TIMESTAMP())");
    }
    $statement->execute([
        "eventId"     => (isset($order['eventId']) ? intval($order['eventId']) : 18),
        "promotionId" => intval($order['promotionId']),
        "earlyBird"   => ($order['earlyBird'] ? true : false),
        "isAdmin"     => ($order['isAdmin'] ? 1 : 0),
    ]);
    $orderId = $conn->lastInsertId();

    // Default time.
    $defaultTime = " 16:00:00";
    $defaultHotelEndTime = " 11:00:00";

    // Loop through the rooms.
    foreach ($order['rooms'] as $roomKey => $room) {

        $programStartDate = (!empty($room['programStartTime'])) ?
            date('Y-m-d', strtotime($room['programStartDate'])) . ' '
            . date('H:i', strtotime($room['programStartTime'])) . ':00'
            : date('Y-m-d' . $defaultTime, strtotime($room['programStartDate']));
        $programEndDate = (!empty($room['programEndTime'])) ?
            date('Y-m-d', strtotime($room['programEndDate'])) . ' '
            . date('H:i', strtotime($room['programEndTime'])) . ':00'
            : date('Y-m-d' . $defaultTime, strtotime($room['programEndDate']));
        $hotelStartDate = (!empty($room['hotelStartDate'])) ?
            date('Y-m-d' . $defaultTime, strtotime($room['hotelStartDate'])) : '0000-00-00 00:00:00';
        $hotelEndDate = (!empty($room['hotelEndDate'])) ?
            date('Y-m-d' . $defaultHotelEndTime, strtotime($room['hotelEndDate'])) : '0000-00-00 00:00:00';

        // Create room.
        $statement = $conn->prepare("INSERT INTO `retreat_orders_rooms`(`order_id`, `room_type_id`, `occupancy_id`, `bed_type_id`, `program_start_date`,
                `program_end_date`, `hotel_start_date`, `hotel_end_date`, `price`, `surcharge`, `tax`, `babysitter`, `private_babysitter`,
                `crib`, `crib_price`, `rollaway`, `rollaway_price`, `program_tax_rate`, `hotel_tax_rate`)
                VALUES(:orderId, :roomTypeId, :occupancyId, :bedTypeId, :programStartDate, :programEndDate, :hotelStartDate, :hotelEndDate,
                    :price, :surcharge, :tax, :babysitter, :privateBabysitter, :crib, :cribPrice, :rollaway, :rollawayPrice, :program_tax_rate, :hotel_tax_rate)");

        $data = [
            "orderId"            => intval($orderId),
            "roomTypeId"         => intval($room['roomTypeId']),
            "occupancyId"        => ((isset($room['occupancy']) and $room['occupancy']) ? intval($room['occupancy']) : null),
            "bedTypeId"          => ((isset($room['bedTypeId']) and $room['bedTypeId']) ? intval($room['bedTypeId']) : null),
            //"startDate" => date('Y-m-d', strtotime($room['startDate'])),
            //"endDate" => date('Y-m-d', strtotime($room['endDate'])),
            "programStartDate"   => $programStartDate,
            "programEndDate"     => $programEndDate,
            "hotelStartDate"     => $hotelStartDate,
            "hotelEndDate"       => $hotelEndDate,
            "tax"                => intval($room['tax']),
            // For some reason Page 1 includes tax in the price so we need to deduct it.
            "babysitter"         => !empty($room['group_babysitting']) ? 1 : 0,
            "privateBabysitter" => !empty($room['private_babysitting']) ? 1 : 0,
            "price"              => intval($room['price']) - intval($room['tax']) - intval($room['surcharge']),
            "surcharge"          => intval($room['surcharge']),
            "crib"               => 0,
            "cribPrice"          => 0,
            "rollaway"           => 0,
            "rollawayPrice"      => 0,
            "program_tax_rate"   => !empty($room['programTaxRate']) ? $room['programTaxRate'] : 0,
            "hotel_tax_rate"     => !empty($room['hotelTaxRate']) ? $room['hotelTaxRate'] : 0,
        ];

        if (!empty($room['additionalBedding'])) {
            if (isset($room['additionalBedding']['crib'])) {
                $data['crib'] = 1;
                $data['cribPrice'] = $room['additionalBedding']['crib'];
            }
            if (isset($room['additionalBedding']['rollaway'])) {
                $data['rollaway'] = 1;
                $data['rollawayPrice'] = $room['additionalBedding']['rollaway'];
            }
        }

        $statement->execute($data);
        $roomId = $conn->lastInsertId();

        $guestCount = 0;

        // Create people.
        for ($i = 0; $i < $room['adult']; $i++) {
            $guestCount++;

            $statement = $conn->prepare("INSERT INTO `retreat_orders_rooms_users`
                (`order_id`, `user_id`, `user_type_id`, `primary`, `orders_room_id`, `internal_notes`, `program_price`,
                `hotel_price`, `early_bird_discount`, `daily_discount`, `program_offset`, `program_tax`, `hotel_tax`)
                VALUES (:orderId, NULL, (SELECT `id` FROM `retreat_config_user_types` WHERE `type` = 'Adult'),
                " . ((($roomKey == 0) and ($i == 0)) ? 'TRUE' : 'FALSE') . ",
                    :roomId, :internalNote, :programPrice, :hotelPrice, :earlyBirdDiscount, :dailyDiscount, :programOffset,
                    :programTax, :hotelTax)");

            $adultHotelPrice = isset($room['hotel_price'], $room['hotel_price']['Adult']) ?
                $room['hotel_price']['Adult'] : 0;

            if ($guestCount > 2) {
                $adultHotelPrice = 0;
            }

            $data = [
                "orderId"           => intval($orderId),
                "roomId"            => intval($roomId),
                "internalNote"      => ((($roomKey == 0) and ($i == 0) and isset($order['internalNote'])) ?
                    trim($order['internalNote']) : ''),
                "programPrice"      => isset($room['program_price'], $room['program_price']['Adult']) ?
                    $room['program_price']['Adult'] : 0,
                "hotelPrice"        => $adultHotelPrice,
                "earlyBirdDiscount" => isset($room['early_bird_discount'], $room['early_bird_discount']['Adult']) ?
                    $room['early_bird_discount']['Adult'] : 0,
                "dailyDiscount"     => isset($room['daily_discount'], $room['daily_discount']['Adult']) ?
                    $room['daily_discount']['Adult'] : 0,
                "programOffset"     => isset($room['program_offset'], $room['program_offset']['Adult']) ?
                    $room['program_offset']['Adult'] : 0,
                "programTax"        => isset($room['program_tax'], $room['program_tax']['Adult'], $room['program_tax']['Adult'][$i]) ?
                    $room['program_tax']['Adult'][$i] : 0,
                "hotelTax"          => isset($room['hotel_tax'], $room['hotel_tax']['Adult'], $room['hotel_tax']['Adult'][$i]) ?
                    $room['hotel_tax']['Adult'][$i] : 0,
            ];

            $statement->execute($data);
        }
        for ($i = 0; $i < $room['teen']; $i++) {
            $guestCount++;

            $statement = $conn->prepare("INSERT INTO `retreat_orders_rooms_users`
                (`order_id`, `user_id`, `user_type_id`, `orders_room_id`, `program_price`,
                `hotel_price`, `early_bird_discount`, `daily_discount`, `program_offset`, `program_tax`, `hotel_tax`)
                VALUES(:orderId, NULL, (SELECT `id` FROM `retreat_config_user_types` WHERE `type` = 'Teen'), :roomId,
                :programPrice, :hotelPrice, :earlyBirdDiscount, :dailyDiscount, :program_offset, :programTax, :hotelTax)");

            $teenHotelPrice = isset($room['hotel_price'], $room['hotel_price']['Teen']) ?
                $room['hotel_price']['Teen'] : 0;

            if ($guestCount > 2) {
                $teenHotelPrice = 0;
            }

            $data = [
                "orderId"           => intval($orderId),
                "roomId"            => intval($roomId),
                "programPrice"      => isset($room['program_price'], $room['program_price']['Teen']) ?
                    $room['program_price']['Teen'] : 0,
                "hotelPrice"        => $teenHotelPrice,
                "earlyBirdDiscount" => isset($room['early_bird_discount'], $room['early_bird_discount']['Teen']) ?
                    $room['early_bird_discount']['Teen'] : 0,
                "dailyDiscount"     => isset($room['daily_discount'], $room['daily_discount']['Teen']) ?
                    $room['daily_discount']['Teen'] : 0,
                "program_offset"    => isset($room['program_offset'], $room['program_offset']['Teen']) ?
                    $room['program_offset']['Teen'] : 0,
                "programTax"        => isset($room['program_tax'], $room['program_tax']['Teen'], $room['program_tax']['Teen'][$i]) ?
                    $room['program_tax']['Teen'][$i] : 0,
                "hotelTax"          => isset($room['hotel_tax'], $room['hotel_tax']['Teen'], $room['hotel_tax']['Teen'][$i]) ?
                    $room['hotel_tax']['Teen'][$i] : 0,
            ];

            $statement->execute($data);

        }
        for ($i = 0; $i < $room['child']; $i++) {
            $statement = $conn->prepare("INSERT INTO `retreat_orders_rooms_users`
                (`order_id`, `user_id`, `user_type_id`, `orders_room_id`, `program_price`,
                `hotel_price`, `early_bird_discount`, `daily_discount`, `program_offset`, `program_tax`, `hotel_tax`)
                VALUES(:orderId, NULL, (SELECT `id` FROM `retreat_config_user_types` WHERE `type` = 'Child'), :roomId,
                :programPrice, :hotelPrice, :earlyBirdDiscount, :dailyDiscount, :program_offset, :programTax, :hotelTax)");

            $data = [
                "orderId"           => intval($orderId),
                "roomId"            => intval($roomId),
                "programPrice"      => isset($room['program_price'], $room['program_price']['Child']) ?
                    $room['program_price']['Child'] : 0,
                "hotelPrice"        => isset($room['hotel_price'], $room['hotel_price']['Child']) ?
                    $room['hotel_price']['Child'] : 0,
                "earlyBirdDiscount" => isset($room['early_bird_discount'], $room['early_bird_discount']['Child']) ?
                    $room['early_bird_discount']['Child'] : 0,
                "dailyDiscount"     => isset($room['daily_discount'], $room['daily_discount']['Child']) ?
                    $room['daily_discount']['Child'] : 0,
                "program_offset"    => isset($room['program_offset'], $room['program_offset']['Child']) ?
                    $room['program_offset']['Child'] : 0,
                "programTax"        => isset($room['program_tax'], $room['program_tax']['Child'], $room['program_tax']['Child'][$i]) ?
                    $room['program_tax']['Child'][$i] : 0,
                "hotelTax"          => isset($room['hotel_tax'], $room['hotel_tax']['Child'], $room['hotel_tax']['Child'][$i]) ?
                    $room['hotel_tax']['Child'][$i] : 0,
            ];

            $statement->execute($data);
        }
        for ($i = 0; $i < $room['toddler']; $i++) {
            $statement = $conn->prepare("INSERT INTO `retreat_orders_rooms_users`
                (`order_id`, `user_id`, `user_type_id`, `orders_room_id`, `program_price`,
                `hotel_price`, `early_bird_discount`, `daily_discount`, `program_offset`, `program_tax`, `hotel_tax`)
                VALUES(:orderId, NULL, (SELECT `id` FROM `retreat_config_user_types` WHERE `type` = 'Toddler'), :roomId,
                :programPrice, :hotelPrice, :earlyBirdDiscount, :dailyDiscount, :program_offset, :programTax, :hotelTax)");
            $data = [
                "orderId"           => intval($orderId),
                "roomId"            => intval($roomId),
                "programPrice"      => isset($room['program_price'], $room['program_price']['Toddler']) ?
                    $room['program_price']['Toddler'] : 0,
                "hotelPrice"        => isset($room['hotel_price'], $room['hotel_price']['Toddler']) ?
                    $room['hotel_price']['Toddler'] : 0,
                "earlyBirdDiscount" => isset($room['early_bird_discount'], $room['early_bird_discount']['Toddler']) ?
                    $room['early_bird_discount']['Toddler'] : 0,
                "dailyDiscount"     => isset($room['daily_discount'], $room['daily_discount']['Toddler']) ?
                    $room['daily_discount']['Toddler'] : 0,
                "program_offset"    => isset($room['program_offset'], $room['program_offset']['Toddler']) ?
                    $room['program_offset']['Toddler'] : 0,
                "programTax"        => isset($room['program_tax'], $room['program_tax']['Toddler'], $room['program_tax']['Toddler'][$i]) ?
                    $room['program_tax']['Toddler'][$i] : 0,
                "hotelTax"          => isset($room['hotel_tax'], $room['hotel_tax']['Toddler'], $room['hotel_tax']['Toddler'][$i]) ?
                    $room['hotel_tax']['Toddler'][$i] : 0,
            ];

            $statement->execute($data);
        }
        for ($i = 0; $i < $room['infant']; $i++) {
            $statement = $conn->prepare("INSERT INTO `retreat_orders_rooms_users`
                (`order_id`, `user_id`, `user_type_id`, `orders_room_id`, `program_price`,
                `hotel_price`, `early_bird_discount`, `daily_discount`, `program_offset`, `program_tax`, `hotel_tax`)
                VALUES(:orderId, NULL, (SELECT `id` FROM `retreat_config_user_types` WHERE `type` = 'Infant'), :roomId,
                :programPrice, :hotelPrice, :earlyBirdDiscount, :dailyDiscount, :program_offset, :programTax, :hotelTax)");
            $data = [
                "orderId"           => intval($orderId),
                "roomId"            => intval($roomId),
                "programPrice"      => isset($room['program_price'], $room['program_price']['Infant']) ?
                    $room['program_price']['Infant'] : 0,
                "hotelPrice"        => isset($room['hotel_price'], $room['hotel_price']['Infant']) ?
                    $room['hotel_price']['Infant'] : 0,
                "earlyBirdDiscount" => isset($room['early_bird_discount'], $room['early_bird_discount']['Infant']) ?
                    $room['early_bird_discount']['Infant'] : 0,
                "dailyDiscount"     => isset($room['daily_discount'], $room['daily_discount']['Infant']) ?
                    $room['daily_discount']['Infant'] : 0,
                "program_offset"    => isset($room['program_offset'], $room['program_offset']['Infant']) ?
                    $room['program_offset']['Infant'] : 0,
                "programTax"        => isset($room['program_tax'], $room['program_tax']['Infant'], $room['program_tax']['Infant'][$i]) ?
                    $room['program_tax']['Infant'][$i] : 0,
                "hotelTax"          => isset($room['hotel_tax'], $room['hotel_tax']['Infant'], $room['hotel_tax']['Infant'][$i]) ?
                    $room['hotel_tax']['Infant'][$i] : 0,
            ];

            $statement->execute($data);
        }

        // Babysitter.
        if (!empty($room['group_babysitting']) && $room['infant'] > 0) {
            $numberOfDays = 7;
            $sqlFields = "`room_id`";
            $sqlValues = ":roomId";
            $variables = ["roomId" => intval($roomId)];
            for ($i = 1; $i <= $numberOfDays; $i++) {
                $sqlFields .= ", `day" . $i . "`";
                $sqlValues .= ", :day" . $i;
                $variables['day' . $i] = (!empty($room['babysitter']['day' . $i]) ? intval($room['babysitter']['day' . $i]) : null);
            }
            $statement = $conn->prepare("INSERT INTO `retreat_babysitting` (" . $sqlFields . ") VALUES(" . $sqlValues . ")");
            $statement->execute($variables);
        }
    }

    // Unset the order in the session.
    $_SESSION['rooming'] = $_SESSION['roomingInfoSentFromPage1'];
    unset($_SESSION['roomingInfoSentFromPage1']);

    // Mark the order ID in the Session (as they are not logged in yet).
    $_SESSION['retreat']['orderId'] = $orderId;

    // Redirect.
    header('Location: ' . BASE_URL . 'guestRegistrationForm.php?orderId=' . $orderId);
    exit(0);

}

// Check if a order ID was requested.
if (isset($_GET['orderId'])) {

    $orderId = intval($_GET['orderId']);

} else {

    // If there is no order in the URL or in the SESSION then they came here in Error.
    // Redirect them to the homepage.
    //echo '<pre>'.print_r($_SESSION, true).'</pre>';
    header('Location: ' . SELECT_ROOMS_URL);
    exit(0);

}

// Make sure they have authorization to edit this order.
if (!isset($_SESSION['retreat']['orderId']) or ($_SESSION['retreat']['orderId'] != $orderId)) {

    // They don't have proper authorization. Redirect them to the homepage.
    header('Location: ' . SELECT_ROOMS_URL);
    exit(0);

}

// Get the  order.
$order = getOrder($orderId);

// Make sure the order exists.
if ($order === null) {

    header('Location: ' . SELECT_ROOMS_URL);
    exit(0);

}

// Make sure the payment is not complete yet.
if ($order['status'] > 3) {

    // Load the template.
    include('templates/complete.php');
    exit(0);

}

// Check if the form was submitted.
if (isset($_POST['submit']) and ($_POST['submit'] == 'Next') and ($order['status'] >= 3)) {

    header('Location: ' . BASE_URL . 'checkout.php?orderId=' . $orderId);
    exit(0);

}

// Get the countries / states.
$countries = getCountries();
$states = getStates(840);

// check if logged in
$currentUser = [];
$guests = [];
if (isset($_SESSION['auth'])) {
    $currentUser = $_SESSION['auth'];

    $guests = getUserGuests($currentUser['id']);
    $currentUser = getUser($currentUser['id']);
}


// Set the template variables.
$templateVariables = ['orderId' => $orderId, 'order' => $order, 'countries' => $countries, 'states' => $states, 'currentUser' => $currentUser, 'guests' => $guests];

// Load the template.
include('templates/guestRegistrationForm.php');
