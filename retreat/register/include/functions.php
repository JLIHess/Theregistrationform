<?php
include(__DIR__ . '/emailFunctions.php');

function getOrder($orderId)
{

    global $conn;

    // Get the order information from the database.
    $statement = $conn->prepare("
                SELECT `retreat_orders`.*, `retreat_config_events`.`name` AS `event`, `retreat_config_order_statuses`.`status` AS `status_message`, `retreat_config_sponsorship_types`.`type` AS `sponsorship_type`
                FROM `retreat_orders`
                LEFT JOIN `retreat_config_events`
                    ON `retreat_orders`.`event_id` = `retreat_config_events`.`id`
                LEFT JOIN `retreat_config_order_statuses`
                    ON `retreat_orders`.`status` = `retreat_config_order_statuses`.`id`
                LEFT JOIN `retreat_config_sponsorship_types`
                    ON `retreat_orders`.`sponsorship_type_id` = `retreat_config_sponsorship_types`.`id`
                WHERE `retreat_orders`.`id` = :orderId
            ");
    $statement->execute(array(':orderId' => $orderId));
    if ($statement->rowCount() == 0) return null;
    $order = $statement->fetch(PDO::FETCH_ASSOC);

    // Get the customer information.
    $order['customer'] = getUser($order['customer_id']);

    // Get the room information.
    $order['rooms'] = getRooms($orderId);

    // Get the guest information.
    $order['guests'] = getGuests($orderId);

    // Get the price.
    $order['price'] = $order['tax'] = $order['cme_price'] = 0;
    foreach ($order['rooms'] as $room) {
        $order['price'] += $room['price'];
        $order['tax'] += $room['tax'];
    }
    foreach ($order['guests'] as $guest) {
        $order['cme_price'] += $guest['price'];
    }
    $order['total'] = $order['price'] + $order['tax'] + $order['cme_price'] + $order['sponsorship_amount'];

    // Get the payments.
    $order['payments'] = getPayments($orderId);

    // Return the order.
    return $order;


}

function getUserGuests($userId)
{
    global $conn;

    $sql = 'SELECT DISTINCT g.*, LOWER(g.gender) AS gender, t.age AS user_type, og.additional_notes, r.relation_name
        FROM retreat_users g
        LEFT JOIN retreat_orders_rooms_users og ON og.user_id = g.id
        LEFT JOIN retreat_orders_rooms_users o ON o.order_id = og.order_id
        LEFT JOIN retreat_users p ON p.id = o.user_id
        LEFT JOIN retreat_config_user_types t ON t.id = g.user_type_id
        LEFT JOIN retreat_user_relationships r ON r.user_id = p.id AND r.related_user_id = g.id
        WHERE p.id = :id AND p.id != g.id AND g.status = 1
        ORDER BY og.order_id DESC';

    $statement = $conn->prepare($sql);
    $statement->execute([':id' => $userId]);

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    $guests = [];
    foreach ($results as $guest) {

        if (!empty($guest['merged_user_id'])) {
            $user = getUser($guest['merged_user_id']);

            $name = $user['first_name'];
            $name .= ($name? ' ' : '') . $user['middle_name'];
            $name .= ($name? ' ' : '') . $user['last_name'];

            $guests[$guest['merged_user_id']] = $user;
            $guests[$guest['merged_user_id']]['name'] = $name;
            $guests[$guest['merged_user_id']]['relation_name'] = ucfirst($guest['relation_name']);

        } else {
            $guests[$guest['id']] = $guest;

            $name = $guest['first_name'];
            $name .= ($name? ' ' : '') . $guest['middle_name'];
            $name .= ($name? ' ' : '') . $guest['last_name'];
            $guests[$guest['id']]['name'] = $name;
            $guests[$guest['id']]['relation_name'] = ucfirst($guest['relation_name']);

            $guests[$guest['id']]['address'] = getAddress($guest['address_id']);
            $guests[$guest['id']]['billing_address'] = getAddress($guest['billing_address_id']);
        }
    }
    return $guests;
}

function getUser($userId)
{

    // Access the database connection.
    global $conn;

    // Get the user information from the database.
    $statement = $conn->prepare("SELECT u.*, t.age AS user_type FROM `retreat_users` u LEFT JOIN retreat_config_user_types t ON t.id = u.user_type_id WHERE `u`.`id` = :userId");
    $statement->execute(array(':userId' => $userId));
    if ($statement->rowCount() == 0) return null;
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    $name = $user['first_name'];
    $name .= ($name? ' ' : '') . $user['middle_name'];
    $name .= ($name? ' ' : '') . $user['last_name'];

    $user['name'] = $name;

    $user['address'] = getAddress($user['address_id']);
    $user['billing_address'] = getAddress($user['billing_address_id']);

    // Return the user.
    return $user;

}

function getAddress($addressId)
{

    // Access the database connection.
    global $conn;

    // Get the address information from the database.
    $statement = $conn->prepare("
                SELECT `retreat_addresses`.*, `retreat_location_countries`.`countries_description` AS `country`
                FROM `retreat_addresses`
                LEFT JOIN `retreat_location_countries`
                    ON `retreat_addresses`.`country_id` = `retreat_location_countries`.`countries_id`
                WHERE `retreat_addresses`.`id` = :addressId
            ");
    $statement->execute(array(':addressId' => $addressId));
    if ($statement->rowCount() == 0) return null;
    $address = $statement->fetch(PDO::FETCH_ASSOC);

    // Return the address.
    return $address;

}

function getRooms($orderId)
{

    // Access the database connection.
    global $conn;

    // Get the rooms information from the database.
    $statement = $conn->prepare("
                SELECT `retreat_orders_rooms`.`id`, `retreat_orders_rooms`.`room_type_id`, `retreat_config_room_types`.`type` AS `room_type`, `retreat_orders_rooms`.`bed_type_id`, `retreat_config_bed_types`.`type` AS `bed_type`, `retreat_orders_rooms`.`occupancy_id`, `retreat_config_occupancies`.`name` AS `occupancy`, `retreat_orders_rooms`.`program_start_date`, `retreat_orders_rooms`.`program_end_date`, `retreat_orders_rooms`.`hotel_start_date`, `retreat_orders_rooms`.`hotel_end_date`, `retreat_orders_rooms`.`price`  , `retreat_orders_rooms`.`tax`
                FROM `retreat_orders_rooms`
                LEFT JOIN `retreat_config_bed_types`
                    ON `retreat_orders_rooms`.`bed_type_id` = `retreat_config_bed_types`.`id`
                LEFT JOIN `retreat_config_room_types`
                    ON `retreat_orders_rooms`.`room_type_id` = `retreat_config_room_types`.`id`
                LEFT JOIN `retreat_config_occupancies`
                    ON `retreat_orders_rooms`.`occupancy_id` = `retreat_config_occupancies`.`id`
                WHERE `retreat_orders_rooms`.`order_id` = :orderId
                ORDER BY `retreat_orders_rooms`.`id` ASC
        ");
    $statement->execute(array(':orderId' => $orderId));
    if ($statement->rowCount() == 0) return array();
    $rooms = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Loop through the rooms
    foreach ($rooms as $key => $room) {

        // Create a variable to hold the guest ids
        $guests = array();

        // Get the guests in this room.
        $statement = $conn->prepare("SELECT `id` FROM `retreat_orders_rooms_users` WHERE `retreat_orders_rooms_users`.`orders_room_id` = :roomId");
        $statement->execute(array(':roomId' => $room['id']));
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Go through the results.
        foreach ($results as $guest) $guests[] = $guest['id'];

        $rooms[$key]['guests'] = $guests;

    }


    // Return the rooms.
    return $rooms;

}

function getGuests($orderId)
{

    // Access the database connection.
    global $conn;

    // Create a variable to store the guests.
    $guests = array();

    // Get the rooms information from the database.
    $statement = $conn->prepare("
                SELECT `retreat_orders_rooms_users`.`id`, `retreat_orders_rooms_users`.`user_id`,
                `retreat_orders_rooms_users`.`user_type_id`, `retreat_config_user_types`.`type` AS `user_type`,
                `retreat_orders_rooms_users`.`primary`, `retreat_orders_rooms_users`.`cme_credits`,
                `retreat_orders_rooms_users`.`price`, `retreat_orders_rooms_users`.`orders_room_id`,
                `retreat_orders_rooms_users`.`notes`, `retreat_orders_rooms_users`.`additional_notes`,
                `retreat_orders_rooms_users`.`internal_notes`,
                `retreat_orders_rooms_users`.`relation_to_primary`
                FROM `retreat_orders_rooms_users`
                LEFT JOIN `retreat_config_user_types`
                    ON `retreat_orders_rooms_users`.`user_type_id` = `retreat_config_user_types`.`id`
                WHERE `retreat_orders_rooms_users`.`order_id` = :orderId
                ORDER BY `retreat_orders_rooms_users`.`id` ASC
        ");
    $statement->execute(array(':orderId' => $orderId));
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Go through the results.
    foreach ($results as $guest) {

        $guest['user'] = getUser($guest['user_id']);
        $guests[$guest['id']] = $guest;

    }

    // Return the guests.
    return $guests;

}

function getGuest($guestId)
{

    // Access the database connection.
    global $conn;

    // Get the rooms information from the database.
    $statement = $conn->prepare("
                SELECT `retreat_orders_rooms_users`.`id`, `retreat_orders_rooms_users`.`user_id`,
                `retreat_orders_rooms_users`.`user_type_id`, `retreat_config_user_types`.`type` AS `user_type`,
                `retreat_orders_rooms_users`.`primary`, `retreat_orders_rooms_users`.`order_id`,
                `retreat_orders_rooms_users`.`orders_room_id`, `retreat_orders_rooms_users`.`notes`,
                `retreat_orders_rooms_users`.`additional_notes`, `retreat_orders_rooms_users`.`internal_notes`
                FROM `retreat_orders_rooms_users`
                LEFT JOIN `retreat_config_user_types`
                    ON `retreat_orders_rooms_users`.`user_type_id` = `retreat_config_user_types`.`id`
                WHERE `retreat_orders_rooms_users`.`id` = :guestid
                ORDER BY `retreat_orders_rooms_users`.`id` ASC
        ");
    $statement->execute(array(':guestid' => $guestId));
    if ($statement->rowCount() == 0) return null;
    $guest = $statement->fetch(PDO::FETCH_ASSOC);

    // Get user inforamtion.
    $guest['user'] = getUser($guest['user_id']);

    // Return the guests.
    return $guest;

}

function getPayments($orderId)
{

    // Access the database connection.
    global $conn;

    // Get the rooms information from the database.
    $statement = $conn->prepare("
                SELECT `retreat_orders_payments`.*
                FROM `retreat_orders_payments`
                WHERE `retreat_orders_payments`.`order_id` = :orderId
                ORDER BY `retreat_orders_payments`.`id` ASC
        ");
    $statement->execute(array(':orderId' => $orderId));
    if ($statement->rowCount() == 0) return array();
    $payments = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Return the payments.
    return $payments;

}

function getCountries()
{

    // Access the database connection.
    global $conn;

    // Create a variable to store the countries.
    $countries = array();

    // Get the countries from the database.
    $statement = $conn->prepare("
                SELECT `retreat_location_countries`.`countries_id`, `retreat_location_countries`.`countries_description`
                FROM `retreat_location_countries`
                ORDER BY `retreat_location_countries`.`countries_description` ASC
        ");
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Go through the results.
    foreach ($results as $country) {

        $countries[$country['countries_id']] = $country['countries_description'];

    }

    // Return the countries.
    return $countries;

}

function getCampuses($allFields = false)
{

    // Access the database connection.
    global $conn;

    // Create a variable to store the campuses.
    $campuses = array();

    // Get the campuses from the database.
    $statement = $conn->prepare("
                SELECT *
                FROM `retreat_campuses`
                ORDER BY `name` ASC
        ");
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Go through the results.
    foreach ($results as $campus) {
        $campuses[$campus['campus_id']] = ($allFields ? $campus : $campus['name']);
    }

    // Return the campuses.
    return $campuses;

}

function getStudentYears()
{

    // Access the database connection.
    global $conn;

    // Create a variable to store the years.
    $years = array();

    // Get the years from the database.
    $statement = $conn->prepare("
                SELECT *
                FROM `retreat_config_events`
                WHERE `students` = TRUE and `current` = 0
                ORDER BY `name` ASC
        ");
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Go through the results.
    foreach ($results as $year) {

        $years[$year['id']] = $year['name'].(empty($year['location']) ? '' : ' - '.$year['location']);

    }

    // Return the years.
    return $years;

}

function getSponsorships()
{

    // Access the database connection.
    global $conn;

    // Get the sponsorships from the database.
    $statement = $conn->query("
                SELECT * FROM `retreat_config_sponsorship_types` ORDER BY `sort` ASC
        ");
    $sponsorships = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Return the sponsorships.
    return $sponsorships;

}

function getStates($country = null)
{

    // Access the database connection.
    global $conn;

    // Create a variable to store the states.
    $states = array();

    // Get the country.
    if ($country != null) {
        $whereClause = 'WHERE `retreat_location_states`.`countries_id` = :country';
        $variables = array(':country' => $country);
    } else {
        $whereClause = '';
        $variables = array();
    }

    // Get the countries from the database.
    $statement = $conn->prepare("
                SELECT `retreat_location_states`.`states_id`, `retreat_location_states`.`states_name`
                FROM `retreat_location_states`
                " . $whereClause . "
                ORDER BY `retreat_location_states`.`states_name` ASC
        ");
    $statement->execute($variables);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Go through the results.
    foreach ($results as $state) {

        $states[$state['states_id']] = $state['states_name'];

    }

    // Return the states.
    return $states;

}

// This function validates the guest information form.
function validateGuestInformation($guest, $form)
{

    // Validate name.
    if (!isset($form['name']) or !preg_match("/^([a-z\,\.\'\-]+\s+)([a-z\,\.\'\-]+\s*)+$/i", trim($form['name']))) {
        return array(false, "You must enter your first and last name.");
    }
    if ((!$guest['primary']) and ((!isset($_POST['relationToPrimary'])) or (trim($_POST['relationToPrimary']) == ''))) {
        return array(false, "You must enter your relationship.");
    }
    // Validte tagName.
    if ((!isset($form['tagName'])) or (trim($form['tagName']) == '')) {
        return array(false, "You must entere a tag name.");
    }
    // Validate gender
    if ((!isset($form['gender'])) or (!in_array(trim($form['gender']), array('', 'm', 'f')))) {
        return array(false, "Please select gender.");
    }
    if ($guest['user_type_id'] <= 2) {
        // Validate email.
        if ((!empty($form['guest_email']) && !filter_var($form['guest_email'], FILTER_VALIDATE_EMAIL))
             || (isset($form['email']) && !isset($form['guest_email']) && !filter_var($form['email'], FILTER_VALIDATE_EMAIL))
        ) {
            return array(false, "You must enter a valid email address.");
        }
        // Validate day phone
        if (!isset($form['dayPhone']) or !preg_match("/^[0-9\(\)\-\s\.]{7,}$/i", trim($form['dayPhone']))) {
            return array(false, "You must enter a valid phone number.");
        }
    }
    // If the user is a teen / child then they must enter a DOB, otherwise it is optional. However if it is entered, it must be valid.
    if (($guest['user_type_id'] > 2) or (isset($form['dob']) and (trim($form['dob']) != ''))) {
        // Validate date of birth.
        if (!isset($form['dob']) or !preg_match("/^\d{4}\-\d{1,2}\-\d{1,2}$/i", trim($form['dob']))) {
            return array(false, "You must enter a valid date of birth.");
        }
        $dobParts = explode('-', trim($form['dob']));
        if (!checkdate($dobParts[1], $dobParts[2], $dobParts[0])) {
            return array(false, "You must enter a valid date of birth.");
        }
    }
    if ($guest['user_type_id'] <= 3) {

        // Check if they're using the same emergency contact information as the main user.
        if (($guest['primary']) or (!isset($_POST['useSameEmergencyInfo'])) or (!$_POST['useSameEmergencyInfo'])) {

            // Validate emergency contact.
            if ((!isset($form['emergencyContact'])) or (trim($form['emergencyContact']) == '')) {
                return array(false, "You must enter an emergency contact.");
            }
            // Validate emergency relation.
            if ((!isset($form['emergencyRelation'])) or (trim($form['emergencyRelation']) == '')) {
                return array(false, "You must enter the emergency contact's relation.");
            }
            // Validate emergency phone.
            if (!isset($form['emergencyPhone']) or !preg_match("/^[0-9\(\)\-\s\.]{7,}$/i", trim($form['emergencyPhone']))) {
                return array(false, "You must enter a valid emergency phone number.");
            }

        }

    }
    if ($guest['user_type_id'] <= 4) {
        // Check if they're using the same addresst information as the main user.
        if (($guest['primary']) or (!isset($_POST['useSameAddress'])) or (!$_POST['useSameAddress'])) {
            // Validate address.
            if ((!isset($form['addressLine1'])) or (trim($form['addressLine1']) == '')) {
                return array(false, "You must enter an address.");
            }
            // Validate city.
            if ((!isset($form['city'])) or (trim($form['city']) == '')) {
                return array(false, "You must enter a city.");
            }
            // Validate state.
            if ((!isset($form['state'])) or (trim($form['state']) == '')) {
                return array(false, "You must select a state.");
            }
            // Validate zip.
            if ((!isset($form['zip'])) or (trim($form['zip']) == '')) {
                return array(false, "You must enter a zip.");
            }
            // Validate country.
            $countries = getCountries();
            if ((!isset($form['countryId'])) or (!isset($countries[intval($form['countryId'])]))) {
                return array(false, "You must select a country.");
            }
        }
    }

    return array(true);

}

// This function saves the guest information.
function saveGuestInformation($guest, $form)
{

    // Access the database connection.
    global $conn;

    // Get the order.
    $order = getOrder($guest['order_id']);
    $primaryUser = $order['guests'][$order['rooms'][0]['guests'][0]]['user'];

    if (!empty($form['user_id'])) {
        $statement = $conn->prepare("SELECT id FROM retreat_orders_rooms_users WHERE user_id = :user_id AND order_id = :order_id");
        $statement->execute(array(':user_id' => $form['user_id'], ':order_id' => $guest['order_id']));

        if ($statement->rowCount() != 0) {
            $form['user_id'] = null;
        }
    }

    /* ADDRESS */
    if ($guest['user_type_id'] <= 4) {

        // Remove old address.
        if (($guest['user'] != null) and ($guest['user']['address'] != null)) {
            $statement = $conn->prepare("DELETE FROM `retreat_addresses` WHERE `id` = :addressId");
            $statement->execute(array(':addressId' => $guest['user']['address_id']));
        }

        // Get the address fields.
        if (isset($form['useSameAddress']) and ($form['useSameAddress'])) {
            $line1 = ($primaryUser['address']['line1'])? $primaryUser['address']['line1'] : '';
            $line2 = ($primaryUser['address']['line2'])? $primaryUser['address']['line2'] : '';
            $city = ($primaryUser['address']['city'])? $primaryUser['address']['city'] : '';
            $state = ($primaryUser['address']['state'])? $primaryUser['address']['state'] : '';
            $zip = ($primaryUser['address']['zip'])? $primaryUser['address']['zip'] : '';
            $countryId = ($primaryUser['address']['country_id'])? $primaryUser['address']['country_id'] : '';
        } else {
            $line1 = ($form['addressLine1'])? trim($form['addressLine1']) : '';
            $line2 = ($form['addressLine2'])? trim($form['addressLine2']) : '';
            $city = ($form['city'])? trim($form['city']) : '';
            $state = ($form['state'])? trim($form['state']) : '';
            $zip = ($form['zip'])? trim($form['zip']) : '';
            $countryId = ($form['countryId'])? intval($form['countryId']) : '';
        }

        // Add to the database.
        $statement = $conn->prepare("INSERT INTO `retreat_addresses`(`line1`, `line2`, `city`, `state`, `zip`, `country_id`) VALUES(:line1, :line2, :city, :state, :zip, :countryId)");
        $statement->execute(array(':line1' => $line1, ':line2' => $line2, ':city' => $city, ':state' => $state, ':zip' => $zip, ':countryId' => $countryId));

        // Get the address id.
        $addressId = $conn->lastInsertId();

    } else $addressId = null;

    /* USER */

    // Get the old user information.
    $oldUserId = $guest['user_id'];

    // Get the user fields.
    $names = preg_split('/\s+/', trim($form['name']));
    $lastName = array_pop($names);
    $middleName = (count($names) > 1) ? array_pop($names) : '';
    $firstName = implode(' ', $names);
    $tagName = trim($form['tagName']);
    $gender = trim($form['gender']);
    $cmeCredits = (isset($form['cmeCredits']) and $form['cmeCredits']);
    $prefix = isset($form['prefix']) ? trim($form['prefix']) : '';
    $email = isset($form['email']) ? trim($form['email']) : '';
    $dayPhone = isset($form['dayPhone']) ? preg_replace("/[^0-9]/", "", $form['dayPhone']) : '';
    $homePhone = isset($form['homePhone']) ? preg_replace("/[^0-9]/", "", $form['homePhone']) : '';
    $cellPhone = isset($form['cellPhone']) ? preg_replace("/[^0-9]/", "", $form['cellPhone']) : '';
    // Get the emergency contact information.
    if (isset($form['useSameEmergencyInfo']) and ($form['useSameEmergencyInfo'])) {
        $emergencyContact = ($primaryUser['emergency_contact'])? $primaryUser['emergency_contact'] : '';
        $emergencyRelation = ($primaryUser['emergency_relation'])? $primaryUser['emergency_relation'] : '';
        $emergencyPhone = ($primaryUser['emergency_phone'])? $primaryUser['emergency_phone'] : '';
    } else {
        $emergencyContact = isset($form['emergencyContact']) ? trim($form['emergencyContact']) : '';
        $emergencyRelation = isset($form['emergencyRelation']) ? trim($form['emergencyRelation']) : '';
        $emergencyPhone = isset($form['emergencyPhone']) ? preg_replace("/[^\\d]/i", "", $form['emergencyPhone']) : '';
    }
    $notes = isset($form['notes']) ? trim($form['notes']) : '';
    $additionalNotes = isset($form['additional_notes']) ? trim($form['additional_notes']) : '';
    $relationToPrimary = (isset($form['relationToPrimary']) and (strtolower(trim($form['relationToPrimary'])) != 'other')) ? trim($form['relationToPrimary']) : '';
    $referredBy = isset($form['referredBy']) ? trim($form['referredBy']) : '';
    $shliach = isset($form['shliach']) ? trim($form['shliach']) : '';
    $jliStudent = isset($form['jliStudent']) ? trim($form['jliStudent']) : '';
    $dob = (isset($form['dob']) and (trim($form['dob']) != '')) ? date('Y-m-d', strtotime(trim($form['dob']))) : null;

    if (empty($gender) && !empty($prefix)) {
        $gender = (in_array($prefix, ['Mr.', 'Rabbi']))? 'm' : (in_array($prefix, ['Mrs.', 'Ms.'])? 'f' : '');
    }

    if (empty($form['user_id'])) {
        // Add user to the database.
        $statement = $conn->prepare(
            "INSERT INTO `retreat_users` (`prefix`, `first_name`, `middle_name`, `last_name`, `tag_name`, `date_of_birth`,
                `user_type_id`, `gender`, `email`, `home_phone`, `day_phone`, `cell_phone`, `address_id`,
                `emergency_contact`, `emergency_relation`, `emergency_phone`, `referred_by`, `jli_student`, `shliach`)
            VALUES(:prefix, :firstName, :middleName, :lastName, :tagName, :dob, 0, :gender, :email, :homePhone,
                :dayPhone, :cellPhone, :addressId, :emergencyContact, :emergencyRelation, :emergencyPhone, :referredBy,
                :jliStudent, :shliach)"
        );
        $statement->execute(array(
            ':prefix' => $prefix, ':firstName' => $firstName, ':middleName' => $middleName, ':lastName' => $lastName,
            ':tagName' => $tagName, ':dob' => $dob, ':gender' => $gender, ':email' => $email, ':homePhone' => $homePhone,
            ':dayPhone' => $dayPhone, ':cellPhone' => $cellPhone, ':addressId' => $addressId,
            ':emergencyContact' => $emergencyContact, ':emergencyRelation' => $emergencyRelation,
            ':emergencyPhone' => $emergencyPhone, ':referredBy' => $referredBy, ':jliStudent' => $jliStudent,
            ':shliach' => $shliach
        ));

        // Get the user id.
        $userId = $conn->lastInsertId();
    } else {
        $userId = $form['user_id'];
    }

    // Remove old user.
    if ($oldUserId != null) {
        $statement = $conn->prepare("DELETE FROM `retreat_users` WHERE `id` = :userId");
        $statement->execute(array(':userId' => $oldUserId));
    }

    // Update guest
    $statement = $conn->prepare(
        "UPDATE `retreat_orders_rooms_users` SET `user_id` = :userId, `cme_credits` = :cmeCredits,
        `price` = :price, `notes` = :notes, `additional_notes` = :additional_notes, `relation_to_primary` = :relationToPrimary WHERE `id` = :guestId");
    $statement->execute(array(
        ':userId' => $userId, ':cmeCredits' => $cmeCredits, ':price' => (($cmeCredits) ? 20000 : 0),
        ':notes' => $notes, ':additional_notes' => $additionalNotes, ':relationToPrimary' => $relationToPrimary,
        ':guestId' => $guest['id']));

    // Get the order.
    $order = getOrder($guest['order_id']);

    // Update order
    $statement = $conn->prepare("UPDATE `retreat_orders` SET `customer_id` = :userId, `status` = GREATEST(`status`, IF((SELECT COUNT(*) FROM `retreat_orders_rooms_users` WHERE `order_id` = :orderId AND `user_id` IS NULL) = 0, 3, 2)) WHERE `id` = :orderId");
    $statement->execute(array(':orderId' => $guest['order_id'], ':userId' => ($guest['primary'] ? $userId : $order['customer_id'])));

    // Update relationships
    $primary = $order['customer'];
    $primaryGender = $primary['gender'];

    if (empty($primaryGender) && !empty($primary['prefix'])) {
        $primaryGender = (in_array($primary['prefix'], ['Mr.', 'Rabbi']))?
            'm' : (in_array($primary['prefix'], ['Mrs.', 'Ms.'])? 'f' : '');
    }

    if (!empty($gender) || !empty($primaryGender) || strtolower($relationToPrimary) == 'spouse') {

        $primaryId = $primary['id'];
        $guestId = $userId;
        $primaryRelationName = '';
        $guestRelationName = '';

        if (strtolower($relationToPrimary) == 'spouse') {
            if (!empty($primaryGender)) {
                if (strtolower($primaryGender) == 'm') {
                    $primaryRelationName = 'husband';
                    $guestRelationName = 'wife';
                } else {
                    $primaryRelationName = 'wife';
                    $guestRelationName = 'husband';
                }
            } else if (!empty($gender)) {
                if (strtolower($gender) == 'm') {
                    $primaryRelationName = 'wife';
                    $guestRelationName = 'husband';
                } else {
                    $primaryRelationName = 'husband';
                    $guestRelationName = 'wife';
                }
            } else {
                $primaryRelationName = 'spouse';
                $guestRelationName = 'spouse';
            }
        } else if (strtolower($relationToPrimary) == 'child') {
            if (!empty($gender)) {
                if (strtolower($gender) == 'm') {
                    $guestRelationName = 'son';
                } else {
                    $guestRelationName = 'daughter';
                }
            }
            if (!empty($primaryGender)) {
                if (strtolower($primaryGender) == 'm') {
                    $primaryRelationName = 'father';
                } else {
                    $primaryRelationName = 'mother';
                }
            }
        } else if (strtolower($relationToPrimary) == 'parent') {
            if (!empty($gender)) {
                if (strtolower($gender) == 'm') {
                    $guestRelationName = 'father';
                } else {
                    $guestRelationName = 'mother';

                }
            }
            if (!empty($primaryGender)) {
                if (strtolower($primaryGender) == 'm') {
                    $primaryRelationName = 'son';
                } else {
                    $primaryRelationName = 'daughter';
                }
            }
        } else if (strtolower($relationToPrimary) == 'friend') {
            $guestRelationName = 'friend';
            $primaryRelationName = 'friend';
        }

        // insert primary relation
        if (!empty($guestRelationName) && $primaryId && $guestId) {
            $statement = $conn->prepare(
                "INSERT INTO `retreat_user_relationships` (user_id, related_user_id, relation_name) VALUES (:uid, :ruid, :r)"
            );
            $statement->execute([':uid' => $primaryId, ':ruid' => $guestId, ':r' => $guestRelationName]);
        }

        // insert guest relation
        if (!empty($primaryRelationName) && $guestId && $primaryId) {
            $statement = $conn->prepare(
                "INSERT INTO `retreat_user_relationships` (user_id, related_user_id, relation_name) VALUES (:uid, :ruid, :r)"
            );
            $statement->execute([':uid' => $guestId, ':ruid' => $primaryId, ':r' => $primaryRelationName]);
        }
    }
}

// This function validates the student information form.
function validateStudentInformation($form)
{

    global $validImageExtensions;

    // Create a variable to store the errors.
    $errors = [];

    // Validate the university.
    $campuses = getCampuses();
    if ((!isset($form['campusId'])) or (!isset($campuses[trim($form['campusId'])]))) {
        $errors[] = "Please select your University.";
    }
    // Validate the student status.
    if (empty($form['studentStatus']) or ($form['studentStatus'] == 0)) {
        $errors[] = "Please select your Current Status.";
    }
    // Validate name.
    if (!isset($form['name']) or !preg_match("/^([a-z\,\.\'\-]+\s+)([a-z\,\.\'\-]+\s*)+$/i", trim($form['name']))) {
        $errors[] = "You must enter your first and last name.";
    }
    // Validate gender
    if ((!isset($form['gender'])) or (!in_array(trim($form['gender']), array('m', 'f')))) {
        $errors[] = "Please select gender.";
    }
    // Validate email.
    if ((!isset($form['email'])) or (!filter_var($form['email'], FILTER_VALIDATE_EMAIL))) {
        $errors[] = "You must entere a valid email address.";
    }
    // Validate cell phone
    if (!isset($form['cellPhone']) or !preg_match("/^[0-9\(\)\-\s\.]{7,}$/i", trim($form['cellPhone']))) {
        $errors[] = "You must enter a valid phone number.";
    }
    // Validate date of birth.
    if (!isset($form['dob']) or !preg_match("/^\d{4}\-\d{1,2}\-\d{1,2}$/i", trim($form['dob']))) {
        $errors[] = "You must enter a valid date of birth.";
    } else {
        $dobParts = explode('-', trim($form['dob']));
        if (!checkdate($dobParts[1], $dobParts[2], $dobParts[0])) {
            $errors[] = "You must enter a valid date of birth.";
        }
    }
    // Validate address.
    if ((!isset($form['address'])) or (trim($form['address']) == '')) {
        $errors[] = "You must enter an address.";
    }
    // Validate city.
    if ((!isset($form['city'])) or (trim($form['city']) == '')) {
        $errors[] = "You must enter a city.";
    }
    // Validate zip.
    if ((!isset($form['zip'])) or (trim($form['zip']) == '')) {
        $errors[] = "You must enter a zip.";
    }
    // Validate country.
    $countries = getCountries();
    if ((!isset($form['countryId'])) or (!isset($countries[intval($form['countryId'])]))) {
        $errors[] = "You must select a country.";
    }
    // Validate state.
    if($form['countryId'] == UNITED_STATES_COUNTRY_ID) {
        if ((!isset($form['stateSelect'])) or (trim($form['stateSelect']) == '')) {
            $errors[] = "You must select a state.";
        }
        $states = getStates(UNITED_STATES_COUNTRY_ID);
        if(!in_array($form['stateSelect'], $states)) $errors[] = "Invalid state";

    } else {
        if ((!isset($form['stateTxt'])) or (trim($form['stateTxt']) == '')) {
            $errors[] = "You must enter a state.";
        }
    }
    // Validate emergency contact.
    if ((!isset($form['emergencyContact'])) or (trim($form['emergencyContact']) == '')) {
        $errors[] = "You must enter an emergency contact.";
    }
    // Validate emergency relation.
    if ((!isset($form['emergencyRelation'])) or (trim($form['emergencyRelation']) == '')) {
        $errors[] = "You must enter the emergency contact's relation.";
    }
    // Validate emergency phone.
    if (!isset($form['emergencyPhone']) or !preg_match("/^[0-9\(\)\-\s\.]{7,}$/i", trim($form['emergencyPhone']))) {
        $errors[] = "You must enter a valid emergency phone number.";
    }

    // Check if an image was uploaded.
    if((isset($_FILES['profilePhoto'])) and (trim($_FILES['profilePhoto']['name']) != "")) {

        // Make sure there was no error in the upload.
        if($_FILES['profilePhoto']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'There was an error uploading your profile image';

        } elseif(!in_array(strtolower(pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION)), $validImageExtensions)) {

            $errors[] = 'Invalid image file type: '.strtolower(pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION));

        } else {

            // Get the file.
            $_SESSION['retreat']['student']['profilePhoto'] = array('name' => preg_replace("/[^A-Za-z0-9\_\.]/", '', $_FILES['profilePhoto']['name']), 'file' => file_get_contents($_FILES['profilePhoto']['tmp_name']));

        }
    } else $errors[] = 'You must upload a profile photo.';
    return $errors;

}

// This function saves the Student information.
function saveStudentInformation($form)
{

    // Access the database connection.
    global $conn, $imageUploadDirectory;


    // Get the address fields.
    $line1 = trim($form['address']);
    $city = trim($form['city']);
    $zip = trim($form['zip']);
    $countryId = intval($form['countryId']);
    // Validate state.
    if($form['countryId'] == UNITED_STATES_COUNTRY_ID) {
        $state = trim($form['stateSelect']);
    } else {
        $state = trim($form['stateTxt']);
    }

    // Add to the database.
    $statement = $conn->prepare("INSERT INTO `retreat_addresses`(`line1`, `city`, `state`, `zip`, `country_id`) VALUES(:line1, :city, :state, :zip, :countryId)");
    $statement->execute(array(':line1' => $line1, ':city' => $city, ':state' => $state, ':zip' => $zip, ':countryId' => $countryId));

    // Get the address id.
    $addressId = $conn->lastInsertId();

    // Get the user fields.
    $names = preg_split('/\s+/', trim($form['name']));
    $lastName = array_pop($names);
    $middleName = (count($names) > 1) ? array_pop($names) : '';
    $firstName = implode(' ', $names);
    $gender = trim($form['gender']);
    $email = isset($form['email']) ? trim($form['email']) : '';
    $cellPhone = isset($form['cellPhone']) ? preg_replace("/[^\\d]/i", "", $form['cellPhone']) : '';
    $emergencyContact = isset($form['emergencyContact']) ? trim($form['emergencyContact']) : '';
    $emergencyRelation = isset($form['emergencyRelation']) ? trim($form['emergencyRelation']) : '';
    $emergencyPhone = isset($form['emergencyPhone']) ? preg_replace("/[^\\d]/i", "", $form['emergencyPhone']) : '';
    $notes = isset($form['notes']) ? trim($form['notes']) : '';
    $additionalNotes = isset($form['additional_notes']) ? trim($form['additional_notes']) : '';
    $dob = (isset($form['dob']) and (trim($form['dob']) != '')) ? date('Y-m-d', strtotime(trim($form['dob']))) : null;
    $campusId = isset($form['campusId']) ? trim($form['campusId']) : null;
    $studentStatus = isset($form['studentStatus']) ? trim($form['studentStatus']) : null;
    $additionalFields = serialize([
        'previousYears' => (isset($form['previousYears']) ? $form['previousYears'] : []),
        'classes' => (isset($form['classes']) ? trim($form['classes']) : ''),
        'impact' => (isset($form['impact']) ? trim($form['impact']) : ''),
        'hopeToGain' => (isset($form['hopeToGain']) ? trim($form['hopeToGain']) : ''),
        'previousExperience' => (isset($form['previousExperience']) ? trim($form['previousExperience']) : ''),
        'questions' => (isset($form['questions']) ? trim($form['questions']) : ''),
        'grow' => (isset($form['grow']) ? trim($form['grow']) : ''),
        'aleph' => (isset($form['aleph']) ? trim($form['aleph']) : ''),
        'thoughts' => (isset($form['thoughts']) ?
                        [
                            'marriage' => (isset($form['thoughts']['marriage']) ? trim($form['thoughts']['marriage']) : ''),
                            'shabbat' => (isset($form['thoughts']['shabbat']) ? trim($form['thoughts']['shabbat']) : ''),
                            'torahstudy' => (isset($form['thoughts']['torahstudy']) ? trim($form['thoughts']['torahstudy']) : ''),
                            'jewishcommunity' => (isset($form['thoughts']['jewishcommunity']) ? trim($form['thoughts']['jewishcommunity']) : ''),
                            'jewishholidays' => (isset($form['thoughts']['jewishholidays']) ? trim($form['thoughts']['jewishholidays']) : ''),
                            'charity' => (isset($form['thoughts']['charity']) ? trim($form['thoughts']['charity']) : ''),
                            'god' => (isset($form['thoughts']['god']) ? trim($form['thoughts']['god']) : ''),
                            'jewishpractices' => (isset($form['thoughts']['jewishpractices']) ? trim($form['thoughts']['jewishpractices']) : ''),
                            'israel' => (isset($form['thoughts']['israel']) ? trim($form['thoughts']['israel']) : ''),

                        ]

                        : []),
    ]);

    // Add user to the database.
    $statement = $conn->prepare(
        "INSERT INTO `retreat_users` (`first_name`, `middle_name`, `last_name`, `date_of_birth`,
            `user_type_id`, `gender`, `email`, `cell_phone`, `address_id`,
            `emergency_contact`, `emergency_relation`, `emergency_phone`, `campus_id`, `student_status`, `additional_fields`)
        VALUES(:firstName, :middleName, :lastName, :dob, (SELECT `id` FROM `retreat_config_user_types` WHERE `type` = 'Sinai Scholars'), :gender, :email, :cellPhone, :addressId, :emergencyContact, :emergencyRelation, :emergencyPhone, :campusId, :studentStatus, :additionalFields)"
    );
    $statement->execute(array(
        ':firstName' => $firstName, ':middleName' => $middleName, ':lastName' => $lastName,
        ':dob' => $dob, ':gender' => $gender, ':email' => $email, ':cellPhone' => $cellPhone, ':addressId' => $addressId,
        ':emergencyContact' => $emergencyContact, ':emergencyRelation' => $emergencyRelation,
        ':emergencyPhone' => $emergencyPhone, ':campusId' => $campusId, ':studentStatus' => $studentStatus, ':additionalFields' => $additionalFields
    ));

    // Get the user id.
    $userId = $conn->lastInsertId();

    // Save the image.
    if(!empty($_SESSION['retreat']['student']['profilePhoto']['name'])) {

        file_put_contents($imageUploadDirectory.str_pad($userId, 7, '0', STR_PAD_LEFT).'-'.$_SESSION['retreat']['student']['profilePhoto']['name'], $_SESSION['retreat']['student']['profilePhoto']['file']);

        // Save to database.
        $statement = $conn->prepare("UPDATE `retreat_users` SET `profile_photo` = :profilePhoto WHERE `id` = :userId");
        $statement->execute(array(':profilePhoto' => str_pad($userId, 7, '0', STR_PAD_LEFT).'-'.$_SESSION['retreat']['student']['profilePhoto']['name'], ':userId' => $userId));

        // Delete the image from the session.
        $_SESSION['retreat']['student']['profilePhoto'] = null;
        unset($_SESSION['retreat']['student']['profilePhoto']);

    }

    // Create the order.
    $statement = $conn->prepare("INSERT INTO `retreat_orders`(`customer_id`, `event_id`, `status`, `time_created`)
        VALUES(:userId, :eventId, 6, CURRENT_TIMESTAMP())");
    $statement->execute(array(
        "userId" => $userId,
        "eventId" => 18,
    ));
    $orderId = $conn->lastInsertId();

    // Create room.
    $statement = $conn->prepare(
        "INSERT INTO `retreat_orders_rooms`(`order_id`, `room_type_id`, `occupancy_id`, `bed_type_id`,
        `program_start_date`, `program_end_date`, `hotel_start_date`, `hotel_end_date`, `price`, `tax`)
        VALUES(:orderId, 5, 0, null, :programStartDate, :programEndDate, 0, 0, 0, 0)");
    $statement->execute(array(
        "orderId" => $orderId,
        "programStartDate" => '2018-08-01 14:30:00',
        "programEndDate" => '2018-08-05 14:30:00'
    ));
    $roomId = $conn->lastInsertId();

    $statement = $conn->prepare("INSERT INTO `retreat_orders_rooms_users`(`order_id`, `user_id`, `user_type_id`, `primary`, `orders_room_id`, `notes`,
        `additional_notes`) VALUES(:orderId, :userId, (SELECT `id` FROM `retreat_config_user_types` WHERE `type` = 'Student'), TRUE, :roomId, :notes, :additional_notes)");
    $statement->execute(array(
        "orderId" => intval($orderId),
        "userId" => intval($userId),
        "roomId" => intval($roomId),
        "notes" => $notes,
        "additional_notes" => $additionalNotes
    ));
    $roomUserId = $conn->lastInsertId();

    return $orderId;

}

function sendStudentRegistrationEmails($orderId) {

    // Get the database.
    global $conn, $userIsAdmin, $studentFromEmail, $studentBccEmails;

    // Include the email functions.


    // Get the order.
    $order = getOrder($orderId);

    // Send email to student.
    $emailAddress = $order['customer']['email'];
    $subject = "The Sinai Scholars National Jewish Retreat: Your Application was Received";

    // Generate the email message.
    ob_start();
    $templateVariables = array('order' => $order);
    include(__DIR__ . '/../templates/studentEmail.php');
    $emailMessage = ob_get_clean();

    // Send email.
    $emailResult = sendEmailWithSendGrid($emailAddress, $studentFromEmail, $subject, $emailMessage, array('fromName' => 'Sinai Scholars Retreat'));

    //
    $statement = $conn->prepare('INSERT INTO `retreat_orders_confirmation_emails`
            (`order_id`, `from_email`, `to_email`, `subject`, `body`, `sent`)
            VALUES
            (:orderId, :fromEmail, :toEmail, :subject, :body, '.($emailResult['sent'] ? '1' : '0').')');
    $statement->execute([
        ':orderId' => $orderId,
        ':fromEmail' => $studentFromEmail,
        ':toEmail' => $emailAddress,
        ':subject' => $subject,
        ':body' => $emailMessage
    ]);

    // Send email to campus Shliach.
    $campuses = getCampuses(true);
    $emailMessage2 ="Dear Shliach,<br><br>
    Please be advised that on ".date('Y-m-d')." <b>".$order['customer']['first_name'].' '.$order['customer']['last_name']."</b> from <b>".$campuses[$order['customer']['campus_id']]['name']."</b> registered for the Sinai Scholars Retreat.<br><br>
    Kol Tuv,<br>
    <em>The SinaiScholars Team</em><br><br>";
    sendEmailWithSendGrid($campuses[$order['customer']['campus_id']]['email'], $studentFromEmail, "New Registration for the Sinai Scholars Retreat", $emailMessage2, array('bcc' => $studentBccEmails, 'fromName' => 'Sinai Scholars Retreat'));

}

// This function sends out an order confirmation email.
function sendStudentAcceptanceEmail($orderId)
{

    // Get the database.
    global $conn, $userIsAdmin, $studentFromEmail, $studentBccEmails;

    // Include the email functions.


    // Get the order.
    $order = getOrder($orderId);

    $emailAddress = $order['customer']['email'];
    $subject = 'Congratulations from the Sinai Scholars Retreat!';

    // Generate the email message.
    ob_start();
    $templateVariables = array('order' => $order);
    include(__DIR__ . '/../templates/studentAcceptanceEmail.php');
    $emailMessage = ob_get_clean();

    $statement = $conn->prepare('INSERT INTO `retreat_orders_confirmation_emails`
            (`order_id`, `from_email`, `to_email`, `subject`, `body`, `sent`)
            VALUES
            (:orderId, :fromEmail, :toEmail, :subject, :body, 0)');

    $statement->execute([
        ':orderId' => $orderId,
        ':fromEmail' => $studentFromEmail,
        ':toEmail' => $emailAddress,
        ':subject' => $subject,
        ':body' => $emailMessage,
    ]);

    // Get the user id.
    $confirmationEmailId = $conn->lastInsertId();

    // Send email.
    $emailResult = sendEmailWithSendGrid($emailAddress, $studentFromEmail, $subject, $emailMessage, array('fromName' => 'Sinai Scholars Retreat'));

    // Update order
    if ($emailResult['sent']) {

        if ($order['status'] == 6) {
            $statement = $conn->prepare("UPDATE `retreat_orders` SET `status` = 9 WHERE `id` = :orderId");
            $statement->execute(array(':orderId' => $orderId));
        }

        $statement = $conn->prepare('UPDATE `retreat_orders_confirmation_emails` SET `sent` = 1 WHERE `id` = :confirmationEmailId');
        $statement->execute(array(':confirmationEmailId' => $confirmationEmailId));

    } else {

        $statement = $conn->prepare("UPDATE `retreat_orders` SET `errors` = TRIM(BOTH ' | ' FROM CONCAT(errors, ' | ', :error)) WHERE `id` = :orderId");
        $statement->execute(array(':error' => 'Email not sent: ' . $emailResult['error'], ':orderId' => $orderId));

    }

    // Return.
    return $emailResult['sent'];

}

function generateRandomString($length = 20)
{
    $randomString = "";
    $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $alphabet[rand(0, strlen($alphabet) - 1)];
    }
    return $randomString;
}

function processCurlPayment($firstNameOnCard, $lastNameOnCard, $cardNumber, $expirationMonth, $expirationYear, $amount, $address, $city, $state, $zip, $country, $phone, $customerId, $orderId, $description = '', $testMode = false)
{

    // global $useTestAccount
    global $authorizeNetLoginID, $authorizeNetTransactionKey, $authorizeDotNetUrl;

    // Payment processing url
    $params = "x_login=" . $authorizeNetLoginID;
    $params .= "&x_tran_key=" . $authorizeNetTransactionKey;

    $params .= "&x_version=3.1";
    $params .= "&x_test_request=" . ($testMode ? "TRUE" : "FALSE"); //test mode
    $params .= "&x_card_num=" . $cardNumber;
    $params .= "&x_exp_date=" . $expirationMonth . '/' . $expirationYear;
    $params .= "&x_first_name=" . $firstNameOnCard;
    $params .= "&x_last_name=" . $lastNameOnCard;
    $params .= "&x_address=" . $address;
    $params .= "&x_city=" . $city;
    $params .= "&x_state=" . $state;
    $params .= "&x_zip=" . $zip;
    $params .= "&x_country=" . $country;
    $params .= "&x_phone=" . $phone;
    $params .= "&x_cust_id=" . $customerId;
    $params .= "&x_email_customer=FALSE";
    $params .= "&x_invoice_num=" . $orderId;
    $params .= "&x_amount=" . $amount;
    $params .= "&x_currency_code=USD";
    $params .= "&x_method=CC";
    $params .= "&x_type=AUTH_CAPTURE";
    $params .= "&x_description=" . $description . ' #' . $orderId;

    // Connect to authorize.net.
    $ch = curl_init();
    //curl_setopt($ch, CURLOPT_CAINFO, __DIR__."\cacert.pem");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_URL, $authorizeDotNetUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $result = curl_exec($ch);
    curl_close($ch);

    // Get the response.
    $authNet = explode('|', $result);
    $mainCode = $authNet[0];
    # 1 approved
    # 2 declined
    # 3 display bouncer
    $subCode = $authNet[1];
    $reason = $authNet[2];
    $bouncer = $authNet[3];

    // Create the return array.
    $response = array($mainCode == 1);
    if ($mainCode == 1) $response[1] = $authNet[6];
    else $response[1] = $authNet[3];
    $response[] = $authNet;

    return $response;

}

// This function sends out an order confirmation email.
function sendOrderConfirmationEmail($orderId)
{

    // Get the database.
    global $conn, $userIsAdmin, $fromEmail, $bccEmails, $customNotificationEmail;

    // Include the email functions.


    // Get the order.
    $order = getOrder($orderId);
    $referral = null;
    $refferalSettings = getReferralSettings($order['event_id']);
    if(strtotime($refferalSettings['expiry']) > time() && $refferalSettings['expiry'] != '0000-00-00 00:00:00'){
        $referral = getReferralByorder($orderId);
    }


    // Make sure the order is complete.
    //if($order['status'] < 4) return array('successful' => false, 'error' => 'Payment not completed.');

    // Make sure the email was not sent out yet.
    //if($order['status'] > 4) return array('successful' => false, 'error' => 'Email already sent.');

    // Email variables.
    if ($userIsAdmin) {
        $emailAddress = $fromEmail;
        // Prevent the same address from being included twice.
        if (($key = array_search($emailAddress, $bccEmails)) !== false) {
            unset($bccEmails[$key]);
        }
    } else {
        $emailAddress = $order['customer']['email'];
    }
    $subject = 'National Jewish Retreat - Order confirmation';

    // Generate the email message.
    ob_start();
    $templateVariables = array('order' => $order, 'referral' => $referral);
    include(__DIR__ . '/../templates/email.php');
    $emailMessage = ob_get_clean();

    $statement = $conn->prepare('INSERT INTO `retreat_orders_confirmation_emails`
            (`order_id`, `from_email`, `to_email`, `subject`, `body`, `sent`)
            VALUES
            (:orderId, :fromEmail, :toEmail, :subject, :body, 0)');

    $statement->execute([
        ':orderId' => $orderId,
        ':fromEmail' => $fromEmail,
        ':toEmail' => $emailAddress,
        ':subject' => $subject,
        ':body' => $emailMessage,
    ]);

    // Get the user id.
    $confirmationEmailId = $conn->lastInsertId();

    // Send email.
    $emailResult = sendEmailWithSendGrid($emailAddress, $fromEmail, $subject, $emailMessage, array('bcc' => $bccEmails));

    // Send custom notification.
    ob_start();
    $templateVariables = array('order' => $order);
    include(__DIR__ . '/../templates/customNotificationEmail.php');
    $customNotificationMessage = ob_get_clean();
    $customNotificationResult = sendEmailWithSendGrid($customNotificationEmail, $fromEmail, 'Retreat order: ' . $orderId, $customNotificationMessage);

    // Update order
    if ($emailResult['sent']) {
        if ($order['status'] == 4) {
            $statement = $conn->prepare("UPDATE `retreat_orders` SET `status` = 5 WHERE `id` = :orderId");
            $statement->execute(array(':orderId' => $orderId));
        }

        $statement = $conn->prepare('UPDATE `retreat_orders_confirmation_emails` SET `sent` = 1 WHERE `id` = :confirmationEmailId');
        $statement->execute([
            ':confirmationEmailId' => $confirmationEmailId,
        ]);
    } else {
        $statement = $conn->prepare("UPDATE `retreat_orders` SET `errors` = TRIM(BOTH ' | ' FROM CONCAT(errors, ' | ', :error)) WHERE `id` = :orderId");
        $statement->execute(array(':error' => 'Email not sent: ' . $emailResult['error'], ':orderId' => $orderId));
    }

    // Return.
    return $emailResult['sent'];

}

// This function removes duplicate users in the order.
function removeDuplicateUsers($orderId)
{

    // Get the database.
    global $conn;

    // Get the order.
    $order = getOrder($orderId);

    // Loop through the guests.
    foreach ($order['guests'] as $guest) {

//echo '<pre>'.print_r($guest, true).'</pre>';

        // Check if this user has previously registered (using the same name and email address).
        $statement = $conn->prepare("
                    SELECT *
                    FROM `retreat_users`
                    WHERE `first_name`=:first_name
                        AND `middle_name`=:middle_name
                        AND `last_name`=:last_name
                        AND `email`=:email
                        AND `id` NOT IN (
                            SELECT `user_id`
                            FROM `retreat_orders_rooms_users`
                            WHERE `order_id` = :orderId
                        )");
        $statement->execute(array(':first_name' => $guest['user']['first_name'], ':middle_name' => $guest['user']['middle_name'], ':last_name' => $guest['user']['last_name'], ':email' => $guest['user']['email'], ':orderId' => $orderId));
        $results = $statement->fetchAll();

        // If there were no results, move to next user.
        if (count($results) == 0) continue;

        // Get the id from the previous user.
        $previousUserId = $results[0]['id'];

        // Save the user to the 'Old Users' table.
        $conn->query("
                INSERT INTO `retreat_old_users` (`old_id`, `first_name`, `last_name`, `middle_name`, `tag_name`, `date_of_birth`, `user_type_id`, `gender`, `email`, `password`, `salt`, `code`, `home_phone`, `day_phone`, `address_id`, `billing_address_id`, `emergency_contact`, `emergency_relation`, `emergency_phone`, `referred_by`, `jli_student`, `shliach`, `shliach_id`)
                SELECT `id`, `first_name`, `last_name`, `middle_name`, `tag_name`, `date_of_birth`, `user_type_id`, `gender`, `email`, `password`, `salt`, `code`, `home_phone`, `day_phone`, `address_id`, `billing_address_id`, `emergency_contact`, `emergency_relation`, `emergency_phone`, `referred_by`, `jli_student`, `shliach`, `shliach_id`
                FROM `retreat_users`
                WHERE `retreat_users`.`id` = '" . intval($previousUserId) . "'");
        // Delete old address.
        /*
        $conn->query("
            INSERT INTO `retreat_users_old` (`old_id`, `prefix_person`, `first_name`, `middle_name`, `last_name`, `gender`, `user_email`, `user_phone`, `shliach`, `shliach_id`, `user_address1`, `user_address2`, `user_city`, `states_id`, `user_zip`, `country_id`)
            SELECT `retreat_users`.`id`, `retreat_users`.`prefix`, `retreat_users`.`first_name`, `retreat_users`.`middle_name`, `retreat_users`.`last_name`, `retreat_users`.`gender`, `retreat_users`.`email`, `retreat_users`.`home_phone`, `retreat_users`.`shliach`, `retreat_users`.`shliach_id`, `retreat_addresses`.`line1`, `retreat_addresses`.`line2`, `retreat_addresses`.`city`, `retreat_addresses`.`state`, `retreat_addresses`.`zip`, `retreat_addresses`.`country_id`
            FROM `retreat_users`
            LEFT JOIN `retreat_addresses`
                ON `retreat_users`.`address_id` = `retreat_addresses`.`id`
            WHERE id = '".intval($previousUserId)."'");
        // Delete old address.
        $statement = $conn->prepare("DELETE FROM `retreat_addresses` WHERE `id` = :addressId");
        $statement->execute(array(':userId' => $result[0]['address_id']));
        */

        // Delete the old user.
        $statement = $conn->prepare("DELETE FROM `retreat_users` WHERE `id` = :userId");
        $statement->execute(array(':userId' => $previousUserId));

        // Update new user's id.
        $statement = $conn->prepare("UPDATE `retreat_users` SET `id` = :previousUserId WHERE `id` = :userId");
        $statement->execute(array(':previousUserId' => $previousUserId, ':userId' => $guest['user_id']));

        // Update this order with the user id.
        $statement = $conn->prepare("UPDATE `retreat_orders` SET `customer_id` = :previousUserId WHERE `customer_id` = :userId");
        $statement->execute(array(':previousUserId' => $previousUserId, ':userId' => $guest['user_id']));
        $statement = $conn->prepare("UPDATE `retreat_orders_rooms_users` SET `user_id` = :previousUserId WHERE `user_id` = :userId");
        $statement->execute(array(':previousUserId' => $previousUserId, ':userId' => $guest['user_id']));

    }

}

function formatUsPhone($phone)
{
    if (strlen($phone) == 10) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
    } else {
        return $phone;
    }
}

function saveRelationships($orderId)
{
    // Access the database connection.
    global $conn;

    $sql = 'SELECT u.id AS related_user_id, TRIM(LOWER(ru.relation_to_primary)) AS relation_name,
            pu.id AS user_id, TRIM(LOWER(u.gender)) AS guest_gender, TRIM(LOWER(pu.gender)) AS user_gender
            FROM retreat_orders o
            INNER JOIN retreat_users pu ON pu.id = o.customer_id
            INNER JOIN retreat_orders_rooms_users ru ON ru.order_id = o.id
            INNER JOIN retreat_users u ON u.id = ru.user_id
            WHERE ru.primary = 0 AND ru.relation_to_primary != "" AND o.id = :id
            GROUP BY ru.user_id';

    $sth = $conn->prepare($sql);
    if ($sth->execute([':id' => $orderId])) {
        $results = $sth->fetchAll();

        foreach ($results as $row) {
            $relationName = null;
            $relationPrimaryName = null;
            if ($row['relation_name'] == 'spouse') {
                if ($row['guest_gender'] == 'm') {
                    $relationName = 'husband';
                } else if ($row['guest_gender'] == 'f') {
                    $relationName = 'wife';
                } else {
                    $relationName = 'spouse';
                }
                if ($row['user_gender'] == 'm') {
                    $relationPrimaryName = 'husband';
                } else if ($row['user_gender'] == 'f') {
                    $relationPrimaryName = 'wife';
                } else {
                    $relationPrimaryName = 'spouse';
                }
            } else if ($row['relation_name'] == 'sibling') {
                $relationName = 'sibling';
                $relationPrimaryName = 'sibling';
            } else if ($row['relation_name'] == 'child') {
                if ($row['guest_gender'] == 'm') {
                    $relationName = 'son';
                } else if ($row['guest_gender'] == 'f') {
                    $relationName = 'daughter';
                } else {
                    $relationName = 'child';
                }
                if ($row['user_gender'] == 'm') {
                    $relationPrimaryName = 'father';
                } else if ($row['user_gender'] == 'f') {
                    $relationPrimaryName = 'mother';
                } else {
                    $relationPrimaryName = 'parent';
                }
            } else if ($row['relation_name'] == 'parent') {
                if ($row['guest_gender'] == 'm') {
                    $relationName = 'father';
                } else if ($row['guest_gender'] == 'f') {
                    $relationName = 'mother';
                } else {
                    $relationName = 'parent';
                }
                if ($row['user_gender'] == 'm') {
                    $relationPrimaryName = 'son';
                } else if ($row['user_gender'] == 'f') {
                    $relationPrimaryName = 'daughter';
                } else {
                    $relationPrimaryName = 'child';
                }
            } else if ($row['relation_name'] == 'friend') {
                $relationName = 'friend';
                $relationPrimaryName = 'friend';
            }

            if (!empty($relationName)) {
                $sql = 'INSERT INTO retreat_user_relationships (user_id, related_user_id, relation_name)
                        VALUES (:uid, :ruid, :n)';
                $sth = $conn->prepare($sql);
                $sth->execute([
                    ':uid' => $row['user_id'],
                    ':ruid' => $row['related_user_id'],
                    ':n' => $relationName
                ]);
            }
            if (!empty($relationPrimaryName)) {
                $sql = 'INSERT INTO retreat_user_relationships (user_id, related_user_id, relation_name)
                        VALUES (:uid, :ruid, :n)';
                $sth = $conn->prepare($sql);
                $sth->execute([
                    ':uid' => $row['related_user_id'],
                    ':ruid' => $row['user_id'],
                    ':n' => $relationPrimaryName
                ]);
            }
        }
    }
}


function validateDate($date, $format = 'Y-m-d H:i:s')
 {
     $d = DateTime::createFromFormat($format, $date);
     return $d && $d->format($format) == $date;
 }



function getReferralSettings($event_id){

     // Access the database connection.
    global $conn;

    $referralSettings = array(
        'amount' => 0,
        'min_days' => 0,
        'use_limit' => 0,
        'expiry' => '0000-00-00 00:00:00',
    );

    $statement = $conn->prepare("SELECT * FROM `retreat_config_promotions` WHERE `retreat_config_promotions`.`event_id` = :eventId");
    $statement->execute(array(':eventId' => $event_id));
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        if($row['type'] == 'referral'){
            switch($row['name']){
                case 'amount':
                    $referralSettings['amount'] = intval($row['value'])?intval($row['value']):0;
                    break;
                case 'min_days':
                    $referralSettings['min_days'] = intval($row['value'])?intval($row['value']):0;
                    break;
                case 'use_limit':
                    $referralSettings['use_limit'] = intval($row['value'])?intval($row['value']):0;
                    break;
                case 'expiry':
                    $referralSettings['expiry'] = validateDate($row['value']) ? date_create($row['value'])->format('Y-m-d H:i:s') : '0000-00-00 00:00:00' ;
                    break;
            }
        }
    }

    return $referralSettings;
}


function saveReferralSettings($event_id, $referralSettings){

    // Access the database connection.
    global $conn;

    //validate settings
    $referralSettingsCheck = array(
        'amount' => 50,
        'min_days' => 3,
        'use_limit' => 0,
        'expiry' => '0000-00-00 00:00:00',
    );
    if (count(array_intersect(array_keys($referralSettingsCheck), array_keys($referralSettings))) != count(array_keys($referralSettings))) return Array(false, $referralSettings);
    $referralSettings['expiry'] = validateDate($referralSettings['expiry']) ? date_create($referralSettings['expiry'])->format('Y-m-d H:i:s') : '0000-00-00 00:00:00' ;
    foreach($referralSettings as $key => $value){
        $statement = $conn->prepare("SELECT * FROM `retreat_config_promotions`
                                    WHERE `retreat_config_promotions`.`event_id` = :eventId
                                    AND `retreat_config_promotions`.`name` = :name");
        $statement->execute(array(':eventId' => $event_id, ':name' =>$key ));
        $action = 'update';
        if ($statement->rowCount() == 0) $action = 'insert';
        $results = $statement->fetch(PDO::FETCH_ASSOC);

        $sql = ($action == 'update' ? 'UPDATE' : 'INSERT INTO'). '`retreat_config_promotions` SET `event_id` = :eventId, `name` = :key, `value` = :v, `type`= "referral"';
        $sql .= ($action == 'update' ? ' WHERE `retreat_config_promotions`.`event_id` = :eventId': '');

        $sql .= ($action == 'update' ? ' AND `retreat_config_promotions`.`name` = "'.$key.'"' : '');
        $pstatement = $conn->prepare($sql);
        $pstatement->execute(array(':eventId' => $event_id, ':key' => $key,  ':v' => $value ));
    }

    $referralSettings = getReferralSettings($event_id);

    $statement = $conn->prepare(
        "UPDATE `retreat_promotions` SET `expiry` = :expiry, `limit` = :limit,
        `amount` = :amount, `day_limit` = :day_limit  WHERE `event_id` = :event_id AND `type` = 'referral' AND `order_id` IS NOT NULL");
    $statement->execute(array(
        ':expiry' => $referralSettings['expiry'],
        ':limit' => $referralSettings['use_limit'],
        ':amount' => $referralSettings['amount'],
        ':day_limit' => $referralSettings['min_days'],
        ':event_id' => $event_id,
        ));


    return Array(true,$referralSettings);
}

function getrefferalByCode($code, $eventId){
    if (!isset($code) ) return null;

    $statement = $conn->prepare('SELECT *, (SELECT COUNT(*) FROM `retreat_orders` WHERE `status` in (4,5) AND `retreat_orders`.`promotion_id` = `retreat_promotions`.`id`) AS count,
                                    (SELECT COUNT(*) FROM `retreat_orders`
                                        LEFT  JOIN `retreat_orders_rooms_users`
                                            ON `retreat_orders_rooms_users`.`order_id` = `retreat_orders`.`id`
                                        LEFT JOIN `retreat_config_user_types`
                                            ON `retreat_orders_rooms_users`.`user_type_id` = `retreat_config_user_types`.`id`
                                        WHERE `retreat_orders`.`status` in (4,5) AND `retreat_orders`.`promotion_id` = `retreat_promotions`.`id`) AS guest_count
                                FROM `retreat_promotions`
                                WHERE `type` = "referral" AND `retreat_promotions`.`event_id` =  :eventId AND `retreat_promotions`.`code` =  :code LIMIT 1');
    $statement->execute(array(':code' => $code,':eventId' => $eventId));
    $results = $statement->fetch(PDO::FETCH_ASSOC);

    return $results;

}

function getReferralById($id, $promo = false){
    global $conn;
    if (!isset($id) ) return null;

    $statement = $conn->prepare('SELECT *, (SELECT COUNT(*) FROM `retreat_orders` WHERE `status` in (4,5) AND `retreat_orders`.`promotion_id` = `retreat_promotions`.`id`) AS count,
                                    (SELECT COUNT(*) FROM `retreat_orders`
                                        LEFT  JOIN `retreat_orders_rooms_users`
                                            ON `retreat_orders_rooms_users`.`order_id` = `retreat_orders`.`id`
                                        LEFT JOIN `retreat_config_user_types`
                                            ON `retreat_orders_rooms_users`.`user_type_id` = `retreat_config_user_types`.`id`
                                        WHERE `retreat_orders`.`status` in (4,5) AND `retreat_orders`.`promotion_id` = `retreat_promotions`.`id`) AS guest_count
                                FROM `retreat_promotions`
                                WHERE '. ($promo ? '':'`type` = "referral" AND ').' `retreat_promotions`.`id` =  :id LIMIT 1');
    $statement->execute(array(':id' => $id));
    $results = $statement->fetch(PDO::FETCH_ASSOC);

    return $results;

}


function getReferralByorder($orderId){
    global $conn;
    if (!isset($orderId) ) return null;

    $statement = $conn->prepare('SELECT *, (SELECT COUNT(*) FROM `retreat_orders` WHERE `status` in (4,5) AND `retreat_orders`.`promotion_id` = `retreat_promotions`.`id`) AS count,
                                    (SELECT COUNT(*) FROM `retreat_orders`
                                        LEFT  JOIN `retreat_orders_rooms_users`
                                            ON `retreat_orders_rooms_users`.`order_id` = `retreat_orders`.`id`
                                        LEFT JOIN `retreat_config_user_types`
                                            ON `retreat_orders_rooms_users`.`user_type_id` = `retreat_config_user_types`.`id`
                                        WHERE `retreat_orders`.`status` in (4,5) AND `retreat_orders`.`promotion_id` = `retreat_promotions`.`id`) AS guest_count
                                FROM `retreat_promotions`
                                WHERE `type` = "referral" AND  `retreat_promotions`.`order_id` =  :order_id LIMIT 1');
    $statement->execute(array(':order_id' => $orderId));
    $results = $statement->fetch(PDO::FETCH_ASSOC);

    return $results;

}


function generateReferral($orderId){
    global $conn;

    $order = getOrder($orderId);


    $primaryUser = $order['guests'][$order['rooms'][0]['guests'][0]]['user'];
    $referralSettings = getReferralSettings($order['event_id']);
    $referralCode = $primaryUser['last_name'].mb_substr($primaryUser['first_name'], 0, 1).$orderId;



    $statement = $conn->prepare("SELECT * FROM `retreat_promotions`
                                    WHERE `retreat_promotions`.`event_id` = :eventId
                                    AND `retreat_promotions`.`order_id` = :order_id
                                    AND `retreat_promotions`.`type` = 'referral'
                                    LIMIT 1 ");
    $statement->execute(array(':eventId' => $order['event_id'], ':order_id'=> $orderId ));

    if ($statement->rowCount() == 0) {
        //id, `code`, `expiry`, `limit`, `amount`, `event_id`, `order_id`, `type`, `day_limit`
        $statement = $conn->prepare("INSERT INTO `retreat_promotions`(`code`, `expiry`, `limit`, `amount`, `event_id`, `order_id`, `type`,`day_limit`)
        VALUES(:code,  :expiry,   :limit, :amount,  :event_id,  :order_id, 'referral', :day_limit )");
        $statement->execute(array(
            ':code' => $referralCode,
            ':expiry' => $referralSettings['expiry'],
            ':limit' => $referralSettings['use_limit'],
            ':amount' => $referralSettings['amount'],
            ':event_id' => $order['event_id'],
            ':day_limit' => $referralSettings['min_days'],
            ':order_id'=>$orderId
        ));
        $statement = $conn->prepare("SELECT * FROM `retreat_promotions`
                                    WHERE `retreat_promotions`.`event_id` = :eventId
                                    AND `retreat_promotions`.`order_id` = :order_id
                                    AND `retreat_promotions`.`type` = 'referral'
                                    LIMIT 1 ");
        $statement->execute(array(':eventId' => $order['event_id'], ':order_id'=>$orderId ));
        if ($statement->rowCount() == 0) return null;
        $results = $statement->fetch(PDO::FETCH_ASSOC);
        return $results;

    }
    $results = $statement->fetch(PDO::FETCH_ASSOC);
    return $results;
}


function sendReferralEmail($orderId)
{

    // Get the database.
    global $conn, $userIsAdmin, $fromEmail, $bccEmails, $customNotificationEmail;



    // Include the email functions.


    // Get the orders.

    $order = getOrder($orderId);
    $referral = getReferralById($order['promotion_id']);
    if (!$referral || count($referral) == 0 || $referral['order_id'] =='') return null;
    $refOrder = getOrder($referral['order_id']);

    $referral_setting = getReferralSettings($refOrder['event_id']);
    if (date_create($referral_setting['expiry']) < date_create('now')) return null;
    if (!$refOrder['customer']['email']) return null;


    // Make sure the order is complete.
    //if($order['status'] < 4) return array('successful' => false, 'error' => 'Payment not completed.');

    // Make sure the email was not sent out yet.
    //if($order['status'] > 4) return array('successful' => false, 'error' => 'Email already sent.');

    // Email variables.
    if ($userIsAdmin) {
        $emailAddress = $fromEmail;
        // Prevent the same address from being included twice.
        if (($key = array_search($emailAddress, $bccEmails)) !== false) {
            unset($bccEmails[$key]);
        }
    } else {
        $emailAddress = $refOrder['customer']['email'];
    }

    $subject = $order['customer']['first_name'].' '.$order['customer']['last_name']. ' is joining you at the JLI Retreat';

    // Generate the email message.
    ob_start();
    $templateVariables = array('order' => $order,'reforder' => $refOrder, 'referral'=> $referral);
    include(__DIR__ . '/../templates/referral_email.php');
    $emailMessage = ob_get_clean();

    $statement = $conn->prepare('INSERT INTO `retreat_referral_emails`
            (`order_id`, `from_email`, `to_email`, `subject`, `body`, `sent`)
            VALUES
            (:orderId, :fromEmail, :toEmail, :subject, :body, 0)');

    $statement->execute([
        ':orderId' => $refOrder['id'],
        ':fromEmail' => $fromEmail,
        ':toEmail' => $emailAddress,
        ':subject' => $subject,
        ':body' => $emailMessage,
    ]);

    // Get the user id.
    $confirmationEmailId = $conn->lastInsertId();

    // Send email.
    $emailResult = sendEmailWithSendGrid($emailAddress, $fromEmail, $subject, $emailMessage);



    // Update referral
    if ($emailResult['sent']) {

        $statement = $conn->prepare('UPDATE `retreat_referral_emails` SET `sent` = 1 WHERE `id` = :confirmationEmailId');
        $statement->execute([
            ':confirmationEmailId' => $confirmationEmailId,
        ]);
    }

    // Return.
    return $emailResult['sent'];

}

function sendReferralLinkEmail($referralId,  $toName, $toEmail, $subject = 'Will you join me? '){
    global $conn;
    $yahooAOL = array('yahoo.com', 'aol.com', 'yahoo.co.uk','yahoo.ca', 'yahoo.fr','yahoo.com ','rocketmail.com ','ymail.com ','y7mail.com ','yahoo.at ','yahoo.be ','yahoo.bg ','yahoo.cl ','yahoo.co.hu ','yahoo.co.id ','yahoo.co.il ','yahoo.co.kr ','yahoo.co.th ','yahoo.co.za ','yahoo.com.co ','yahoo.com.hr ','yahoo.com.my ','yahoo.com.pe ','yahoo.com.ph ','yahoo.com.sg ','yahoo.com.tr ','yahoo.com.tw ','yahoo.com.ua ','yahoo.com.ve ','yahoo.com.vn ','yahoo.cz ','yahoo.dk ','yahoo.ee ','yahoo.fi ','yahoo.hr ','yahoo.hu ','yahoo.ie ','yahoo.lt ','yahoo.lv ','yahoo.nl ','yahoo.no ','yahoo.pl ','yahoo.pt ','yahoo.rs ','yahoo.se ','yahoo.si ','yahoo.sk ','yahoogroups.co.kr ','yahoogroups.com.cn ','yahoogroups.com.sg ','yahoogroups.com.tw ','yahoogrupper.dk ','yahoogruppi.it ','yahooxtra.co.nz ','yahoo.ca ','yahoo.co.in ','yahoo.co.nz ','yahoo.co.uk ','yahoo.com.ar ','yahoo.com.au ','yahoo.com.br ','yahoo.com.hk ','yahoo.com.mx ','yahoo.de ','yahoo.es ','yahoo.fr ','yahoo.gr ','yahoo.in ','yahoo.it ','yahoo.ro');


    // Include the email functions.


    $referral = getReferralById($referralId);
    if (!$referral || count($referral) == 0) return Array ('sent'=>false, 'error' => 'Referral not valid');
    $refOrder = getOrder($referral['order_id']);
    $fromEmail = $refOrder['customer']['email'];
    $replyToEmail = $fromEmail;
    $fromName = $refOrder['customer']['first_name'].' '.$refOrder['customer']['last_name'];

    $explodedEmail = explode('@', strtolower($fromEmail));
        if ( in_array($explodedEmail[1], $yahooAOL))
    {
        $fromEmail = 'no_reply@jretreat.com';
    }


    // Generate the email message.
    ob_start();
    $templateVariables = array('reforder' => $refOrder, 'referral'=> $referral, 'name'=>$toName, 'email'=>$toEmail);
    include(__DIR__ . '/../templates/referral_link_email.php');
    $emailMessage = ob_get_clean();

    $statement = $conn->prepare('INSERT INTO `retreat_referral_emails`
            (`order_id`, `from_email`, `to_email`, `subject`, `body`, `sent`)
            VALUES
            (:orderId, :fromEmail, :toEmail, :subject, :body, 0)');

    $statement->execute([
        ':orderId' => $refOrder['id'],
        ':fromEmail' => $fromEmail,
        ':toEmail' => $toEmail,
        ':subject' => $subject,
        ':body' => $emailMessage,
    ]);

    // Get the user id.
    $confirmationEmailId = $conn->lastInsertId();

    // Send email.
    $emailResult = sendEmailWithSendGrid($toEmail, $fromEmail, $subject, $emailMessage, array('replyToEmail' => $fromEmail, 'replyToName'=> $fromName, 'fromName'=> $fromName));

    // Update order
    if ($emailResult['sent']) {

        $statement = $conn->prepare('UPDATE `retreat_referral_emails` SET `sent` = 1 WHERE `id` = :confirmationEmailId');
        $statement->execute([
            ':confirmationEmailId' => $confirmationEmailId,
        ]);
    }

    // Return.
    return $emailResult;
}

function createReferralContact($referralId, $data = []){
    global $conn;

    $referral = getReferralById($referralId);
    if (!$referral || count($referral) == 0) return Array ('success'=>false, 'error' => 'Referral not valid');
    $refOrder = getOrder($referral['order_id']);

    if (count($data) == 0) return Array ('success'=>false, 'error' => 'No data sent');

    if ($data['name'] == "") return Array ('success'=>false, 'error' => 'Please enter a valid name');
    if ($data['type'] == "phone" && $data['phone'] == "") return Array ('success'=>false, 'error' => 'Please enter a valid phone number');
    if ($data['type'] == "mail" && $data['address'] == "") return Array ('success'=>false, 'error' => 'Please enter a valid address');
    if ($data['type'] == "mail" && $data['zip'] == "") return Array ('success'=>false, 'error' => 'Please enter a valid zip/postal code');
    if ($data['type'] == "mail" && $data['city'] == "") return Array ('success'=>false, 'error' => 'Please enter a valid city');
    if ($data['type'] == "mail" && $data['state'] == "") return Array ('success'=>false, 'error' => 'Please enter a valid state');

    //order_id, type, status, name, phone, address, address2, city, state, zip, country, comments,  promo_id

    $statement = $conn->prepare('INSERT INTO `retreat_referral_contact`
        (`order_id`, `type`, `status`, `name`, `phone`, `address`, `address2`, `city`, `state`, `zip`, `country`, `comments`, `promo_id`)
        VALUES
        (:order_id, :type, :status, :name, :phone, :address, :address2, :city, :state, :zip, :country, :comments, :promo_id)');

    $statement->execute([
        ':order_id' => $refOrder['id'],
        ':promo_id' => $referral['id'],
        ':phone' => $data['phone'],
        ':address' => $data['address'],
        ':address2' => $data['address2'],
        ':city' => $data['city'],
        ':state' => $data['state'],
        ':zip' => $data['zip'],
        ':country' => $data['country'],
        ':name' => $data['name'],
        ':comments' => $data['comments'],
        ':status' => 'New',
        ':type' => $data['type'],
    ]);

    return Array ('success'=>true, 'error' => '');


}

function getOrderByReferralId ($id){
    $referral = getReferralById($id);
    $order = getOrder($referral['order_id']);


    return Array('order'=> $order,'referral'=> $referral);



}
