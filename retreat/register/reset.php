<?php

    set_time_limit(200);
    
	// Database details.
	if(strpos($_SERVER["SERVER_NAME"], 'torahcafe') !== false) {
		$db = "testRetreat";
	} else {
		$db = "retreat";
	}
    $dbHost = "localhost";
      
    // Set up Authorization variable.
    $auth = false;

    //Check if the form was submitted and user is trying to reset the databasae.
    if(isset($_POST['submit']) and ($_POST['submit']=='reset')) {
	
		$dbLink = mysql_connect($dbHost, $_POST['username'], $_POST['password']) or die('Could not connect: ' . mysql_error());
		mysql_select_db($db) or die('Could not select database');

    }	

    // Check if they are authorized.
    if(isset($dbLink) and $dbLink) {
	
        // delete any tables that are here already.
        $result = mysql_query("SHOW TABLES FROM {$db}", $dbLink);
        if($result != false) {
            for($i = 0; $i < mysql_num_rows($result); $i++) {
                $table = mysql_fetch_array($result);
                $result2 = mysql_query("DROP TABLE ".$table[0], $dbLink);
                if(!$result2) echo 'Invalid query: '.mysql_error();
            }
        }

        // Create the Users table.
        $result = mysql_query("CREATE TABLE `retreat_users` (`id` INT NOT NULL AUTO_INCREMENT, `prefix` VARCHAR(10) NOT NULL DEFAULT '', `first_name` VARCHAR(1000) NOT NULL, `last_name` VARCHAR(1000) NOT NULL, `middle_name` VARCHAR(1000) NOT NULL DEFAULT '', `tag_name` VARCHAR(1000) NOT NULL, `date_of_birth` DATE DEFAULT NULL, `user_type_id` INT NOT NULL, `gender` CHAR(1) NOT NULL, `email` VARCHAR(100) NOT NULL DEFAULT '', `password` VARCHAR(255) NOT NULL DEFAULT '', `salt` VARCHAR(255) NOT NULL DEFAULT '', `code` VARCHAR(255) NOT NULL DEFAULT '', `home_phone` VARCHAR(100) NOT NULL DEFAULT '', `day_phone` VARCHAR(100) NOT NULL DEFAULT '', `address_id` INT DEFAULT NULL, `billing_address_id` INT DEFAULT NULL, `emergency_contact` VARCHAR(255) NOT NULL DEFAULT '', `emergency_relation` VARCHAR(255) NOT NULL DEFAULT '', `emergency_phone` VARCHAR(100), `referred_by` VARCHAR(255) NOT NULL DEFAULT '', `jli_student` VARCHAR(255) NOT NULL DEFAULT '', `shliach` VARCHAR(255) NOT NULL DEFAULT '', `shliach_id` INT DEFAULT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        
        // Create the Old Users table.
        $result = mysql_query("CREATE TABLE `retreat_old_users` (`id` INT NOT NULL AUTO_INCREMENT, `old_id` INT NOT NULL, `prefix` VARCHAR(10) NOT NULL DEFAULT '', `first_name` VARCHAR(1000) NOT NULL, `last_name` VARCHAR(1000) NOT NULL, `middle_name` VARCHAR(1000) NOT NULL DEFAULT '', `tag_name` VARCHAR(1000) NOT NULL, `date_of_birth` DATE DEFAULT NULL, `user_type_id` INT NOT NULL, `gender` CHAR(1) NOT NULL, `email` VARCHAR(100) NOT NULL DEFAULT '', `password` VARCHAR(255) NOT NULL DEFAULT '', `salt` VARCHAR(255) NOT NULL DEFAULT '', `code` VARCHAR(255) NOT NULL DEFAULT '', `home_phone` VARCHAR(100) NOT NULL DEFAULT '', `day_phone` VARCHAR(100) NOT NULL DEFAULT '', `address_id` INT DEFAULT NULL, `billing_address_id` INT DEFAULT NULL, `emergency_contact` VARCHAR(255) NOT NULL DEFAULT '', `emergency_relation` VARCHAR(255) NOT NULL DEFAULT '', `emergency_phone` VARCHAR(100), `referred_by` VARCHAR(255) NOT NULL DEFAULT '', `jli_student` VARCHAR(255) NOT NULL DEFAULT '', `shliach` VARCHAR(255) NOT NULL DEFAULT '', `shliach_id` INT DEFAULT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        
        // Create the Address table.
        $result = mysql_query("CREATE TABLE `retreat_addresses` (`id` INT NOT NULL AUTO_INCREMENT, `line1` VARCHAR(1000) NOT NULL, `line2` VARCHAR(1000) NOT NULL DEFAULT '', `city` VARCHAR(1000) NOT NULL DEFAULT '', `state` VARCHAR(255) NOT NULL DEFAULT '', `zip` VARCHAR(10) NOT NULL DEFAULT '', `country_id` INT DEFAULT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        
        // Create the Orders table.
        $result = mysql_query("CREATE TABLE `retreat_orders` (`id` INT NOT NULL AUTO_INCREMENT, `customer_id` INT DEFAULT NULL, `event_id` INT NOT NULL, `status` INT NOT NULL DEFAULT 0, `promotion_id` INT DEFAULT NULL, `early_bird` BOOLEAN NOT NULL DEFAULT FALSE, `is_admin` BOOLEAN NOT NULL DEFAULT FALSE, `sponsorship_type_id` INT DEFAULT NULL, `sponsorship_amount` INT NOT NULL DEFAULT 0, `sponsorship_notes` TEXT NOT NULL DEFAULT '', `total_amount` INT NOT NULL DEFAULT 0, `current_balance` INT NOT NULL DEFAULT 0, `time_created` TIMESTAMP NULL, `time_of_last_change` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `note` TEXT NOT NULL, `errors` TEXT NOT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        
        // Create the OrdersRooms table.
        $result = mysql_query("CREATE TABLE `retreat_orders_rooms` (`id` INT NOT NULL AUTO_INCREMENT, `order_id` INT NOT NULL, `room_type_id` INT NOT NULL, `occupancy_id` INT DEFAULT NULL, `bed_type_id` INT DEFAULT NULL, `program_start_date` DATETIME NOT NULL, `program_end_date` DATETIME NOT NULL, `hotel_start_date` DATETIME NOT NULL, `hotel_end_date` DATETIME NOT NULL, `price` INT NOT NULL DEFAULT 0, `tax` INT NOT NULL DEFAULT 0, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        
        // Create the OrdersRoomsUsers table.
        $result = mysql_query("CREATE TABLE `retreat_orders_rooms_users` (`id` INT NOT NULL AUTO_INCREMENT, `order_id` INT NOT NULL, `user_id` INT DEFAULT NULL, `user_type_id` INT NOT NULL, `primary` BOOLEAN NOT NULL DEFAULT FALSE, `orders_room_id` INT NOT NULL, `cme_credits` BOOLEAN DEFAULT FALSE, `price` INT NOT NULL DEFAULT 0, `notes` TEXT, `additional_notes` TEXT, `relation_to_primary` VARCHAR(255) NOT NULL DEFAULT '', `internal_notes` TEXT, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        
        // Create the table to hold a list of events.
        $result = mysql_query("CREATE TABLE `retreat_config_events` (`id` INT NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_events` (`name`) VALUES ('National Jewish Retreat 5775')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
        // Create the table to hold the payments.
        $result = mysql_query("CREATE TABLE `retreat_orders_payments` (`id` INT NOT NULL AUTO_INCREMENT, `order_id` INT NOT NULL, `amount` INT NOT NULL, `tax` INT NOT NULL DEFAULT 0, `payment_method` VARCHAR(255) NOT NULL DEFAULT '', `type` VARCHAR(255) NOT NULL DEFAULT 'credit', `notes` TEXT DEFAULT '', `status` INT NOT NULL DEFAULT 0, `transaction_id` VARCHAR(255) DEFAULT NULL, `customer_profile_id` VARCHAR(255) DEFAULT NULL, `payment_profile_id` VARCHAR(255) DEFAULT NULL, `last4` VARCHAR(10) DEFAULT NULL, `address_id` INT DEFAULT NULL, `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
        // Create the table to hold the babysitting.
        $result = mysql_query("CREATE TABLE `retreat_babysitting` (`id` INT NOT NULL AUTO_INCREMENT, `room_id` INT NOT NULL, `day1` INT DEFAULT NULL, `day2` INT DEFAULT NULL, `day3` INT DEFAULT NULL, `day4` INT DEFAULT NULL, `day5` INT DEFAULT NULL, `day6` INT DEFAULT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
        // Create the table to hold the room types.
        $result = mysql_query("CREATE TABLE `retreat_config_room_types` (`id` INT NOT NULL AUTO_INCREMENT, `type` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        $result = mysql_query("INSERT INTO `retreat_config_room_types` (`id`, `type`) VALUES (0, 'No Rooming')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("UPDATE `retreat_config_room_types` SET `id` = 0", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("ALTER TABLE `retreat_config_room_types` AUTO_INCREMENT = 1;", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_room_types` (`type`) VALUES ('Standard')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_room_types` (`type`) VALUES ('Classic Suite')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_room_types` (`type`) VALUES ('One Bedroom Suite')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_room_types` (`type`) VALUES ('Presidential Suite')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
        // Create the table to hold the age types.
        $result = mysql_query("CREATE TABLE `retreat_config_user_types` (`id` INT NOT NULL AUTO_INCREMENT, `type` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        $result = mysql_query("INSERT INTO `retreat_config_user_types` (`type`) VALUES ('Shliach')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_user_types` (`type`) VALUES ('Adult')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_user_types` (`type`) VALUES ('Teen')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_user_types` (`type`) VALUES ('Child')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_user_types` (`type`) VALUES ('Toddler')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_user_types` (`type`) VALUES ('Infant')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
        // Create the table to hold the bed types.
        $result = mysql_query("CREATE TABLE `retreat_config_bed_types` (`id` INT NOT NULL AUTO_INCREMENT, `type` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        $result = mysql_query("INSERT INTO `retreat_config_bed_types` (`type`) VALUES ('King')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_bed_types` (`type`) VALUES ('Queen')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_bed_types` (`type`) VALUES ('Queen + Sofa')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
        // Create the table to hold the occupancies.
        $result = mysql_query("CREATE TABLE `retreat_config_occupancies` (`id` INT NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        $result = mysql_query("INSERT INTO `retreat_config_occupancies` (`name`) VALUES ('Single Occupancy')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_occupancies` (`name`) VALUES ('Double Occupancy')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_occupancies` (`name`) VALUES ('3rd or 4th Room')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
        // Create the table to hold the sponsorship types.
        $result = mysql_query("CREATE TABLE `retreat_config_sponsorship_types` (`id` INT NOT NULL AUTO_INCREMENT, `type` VARCHAR(255) NOT NULL, `display` VARCHAR(1000) NOT NULL, `description` TEXT NOT NULL DEFAULT '', `amount` INT DEFAULT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        $result = mysql_query("INSERT INTO `retreat_config_sponsorship_types` (`type`, `display`, `amount`) VALUES ('patron', 'Retreat Patron', 100000)", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_sponsorship_types` (`type`, `display`, `amount`) VALUES ('seminar', 'Sponsor a Seminar', 36000)", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_sponsorship_types` (`type`, `display`, `amount`) VALUES ('workshop', 'Sponsor a Workshop', 18000)", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_sponsorship_types` (`type`, `display`, `amount`) VALUES ('other', 'Other sponsorship', NULL)", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
       // Create the table to hold the reservation statuses.
        $result = mysql_query("CREATE TABLE `retreat_config_order_statuses` (`id` INT NOT NULL, `status` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) COLLATE utf8_general_ci ENGINE=InnoDB", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_order_statuses` (`id`, `status`) VALUES (1, 'Selected Dates and Rooms')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_order_statuses` (`id`, `status`) VALUES (2, 'Began selecting guests')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_order_statuses` (`id`, `status`) VALUES (3, 'Finished selecting guests')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_order_statuses` (`id`, `status`) VALUES (4, 'Submitted payment')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_order_statuses` (`id`, `status`) VALUES (5, 'Confirmed')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("INSERT INTO `retreat_config_order_statuses` (`id`, `status`) VALUES (1000, 'Payment Error')", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
		
		// Countries table.
        $result = mysql_query("
				CREATE TABLE `retreat_location_countries` (
				  `countries_id` int(11) NOT NULL AUTO_INCREMENT,
				  `countries_description` varchar(128) NOT NULL DEFAULT '',
				  `regions_id` int(11) NOT NULL DEFAULT '0',
				  `countries_sortorder` int(11) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`countries_id`),
				  UNIQUE KEY `countries_id` (`countries_id`)
				) ENGINE=InnoDB AUTO_INCREMENT=895 DEFAULT CHARSET=utf8;
			", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("
			INSERT INTO `retreat_location_countries` VALUES (4,'Afghanistan',8,1),(8,'Albania',4,1),(10,'Antarctica',3,1),(12,'Algeria',5,1),(16,'American Samoa',7,1),(20,'Andorra',4,1),(24,'Angola',5,1),(28,'Antigua and Barbuda',2,1),(31,'Azerbaijan',4,1),(32,'Argentina',3,1),(36,'Australia',7,1),(40,'Austria',4,1),(44,'Bahamas',1,1),(48,'Bahrain',8,1),(50,'Bangladesh',6,1),(51,'Armenia',4,1),(52,'Barbados',2,1),(56,'Belgium',4,1),(60,'Bermuda',1,1),(64,'Bhutan',6,1),(68,'Bolivia',3,1),(70,'Bosnia and Herzegowina',4,1),(72,'Botswana',5,1),(74,'Bouvet Island',3,1),(76,'Brazil',3,1),(84,'Belize',2,1),(90,'Solomon Islands',7,1),(92,'British Virgin Islands',2,1),(96,'Brunei Darussalam',6,1),(100,'Bulgaria',4,1),(104,'Myanmar',6,1),(108,'Burundi',5,1),(112,'Belarus',4,1),(116,'Cambodia',6,1),(120,'Cameroon',5,1),(124,'Canada',1,1),(132,'Cape Verde',5,1),(136,'Cayman Islands',2,1),(140,'Central African Republic',5,1),(144,'Sri Lanka',6,1),(148,'Chad',5,1),(152,'Chile',3,1),(156,'China',6,1),(158,'Taiwan',6,1),(162,'Christmas Island',6,1),(166,'Cocos (Keeling) Islands',2,1),(170,'Colombia',3,1),(174,'Comoros',5,1),(175,'Mayotte',5,1),(178,'Congo',5,1),(180,'Congo',5,1),(184,'Cook Islands',7,1),(188,'Costa Rica',3,1),(191,'Hrvatska (Croatia)',4,1),(192,'Cuba',2,1),(196,'Cyprus',8,1),(203,'Czech Republic',4,1),(204,'Benin',5,1),(208,'Denmark',4,1),(212,'Dominica',2,1),(214,'Dominican Republic',2,1),(218,'Ecuador',3,1),(222,'El Salvador',2,1),(226,'Equatorial Guinea',5,1),(231,'Ethiopia',5,1),(232,'Eritrea',5,1),(233,'Estonia',4,1),(234,'Faeroe Islands',4,1),(242,'Fiji',7,1),(246,'Finland',4,1),(250,'France',4,1),(254,'French Guiana',3,1),(258,'French Polynesia',7,1),(262,'Djibouti',5,1),(266,'Gabon',5,1),(268,'Georgia',4,1),(270,'Gambia',5,1),(275,'Palestinian Territory',6,1),(276,'Germany',4,1),(288,'Ghana',5,1),(292,'Gibraltar',4,1),(296,'Kiribati',7,1),(300,'Greece',4,1),(304,'Greenland',4,1),(308,'Grenada',2,1),(312,'Guadaloupe',2,1),(316,'Guam',7,1),(320,'Guatemala',2,1),(324,'Guinea',5,1),(328,'Guyana',3,1),(332,'Haiti',2,1),(340,'Honduras',2,1),(344,'Hong Kong',6,1),(348,'Hungary',4,1),(352,'Iceland',4,1),(356,'India',6,1),(360,'Indonesia',6,1),(364,'Iran',8,1),(368,'Iraq',8,1),(372,'Ireland',4,1),(376,'Israel',8,1),(380,'Italy',4,1),(384,'Cote DIvoire',5,1),(388,'Jamaica',2,1),(392,'Japan',6,1),(398,'Kazakhstan',8,1),(400,'Jordan',8,1),(404,'Kenya',5,1),(408,'Korea',6,1),(410,'Korea',6,1),(414,'Kuwait',6,1),(417,'Kyrgyz Republic',8,1),(418,'Laos',6,1),(422,'Lebanon',8,1),(426,'Lesotho',5,1),(428,'Latvia',4,1),(430,'Liberia',5,1),(434,'Libyan Arab Jamahiriya',5,1),(438,'Liechtenstein',4,1),(440,'Lithuania',4,1),(442,'Luxembourg',4,1),(446,'Macau',6,1),(450,'Madagascar',5,1),(454,'Malawi',5,1),(458,'Malaysia',6,1),(462,'Maldives',6,1),(466,'Mali',5,1),(470,'Malta',4,1),(474,'Martinique',2,1),(478,'Mauritania',5,1),(480,'Mauritius',5,1),(484,'Mexico',1,1),(492,'Monaco',4,1),(496,'Mongolia',6,1),(498,'Moldova',4,1),(500,'Montserrat',2,1),(504,'Morocco',5,1),(508,'Mozambique',5,1),(512,'Oman',8,1),(516,'Namibia',5,1),(520,'Nauru',7,1),(524,'Nepal',6,1),(528,'Netherlands',4,1),(530,'Netherlands Antilles',3,1),(533,'Aruba',2,1),(540,'New Caledonia',7,1),(548,'Vanuatu',7,1),(554,'New Zealand',7,1),(558,'Nicaragua',2,1),(562,'Niger',5,1),(566,'Nigeria',5,1),(570,'Niue',7,1),(574,'Norfolk Island',7,1),(578,'Norway',4,1),(583,'Micronesia',7,1),(584,'Marshall Islands',7,1),(585,'Palau',7,1),(586,'Pakistan',8,1),(591,'Panama',2,1),(598,'Papua New Guinea',7,1),(600,'Paraguay',3,1),(604,'Peru',3,1),(608,'Philippines',6,1),(612,'Pitcairn Island',7,1),(616,'Poland',4,1),(620,'Portugal',4,1),(624,'Guinea-Bissau',5,1),(626,'East Timor',6,1),(630,'Puerto Rico',2,1),(634,'Qatar',8,1),(638,'Reunion',5,1),(642,'Romania',4,1),(643,'Russian Federation',6,1),(646,'Rwand',5,1),(654,'St. Helena',3,1),(659,'Saint Kitts and Nevis',2,1),(660,'Anguilla',2,1),(662,'Saint Lucia',2,1),(674,'San Marino',4,1),(682,'Saudi Arabi',8,1),(686,'Senegal',5,1),(690,'Seychelles',5,1),(694,'Sierra Leone',5,1),(702,'Singapore',6,1),(703,'Slovakia',4,1),(704,'Viet Nam',6,1),(705,'Slovenia',4,1),(706,'Somalia',5,1),(710,'South Africa',5,1),(716,'Zimbabwe',5,1),(724,'Spain',4,1),(732,'Western Sahara',5,1),(736,'Sudan',5,1),(740,'Suriname',3,1),(748,'Swaziland',5,1),(752,'Sweden',4,1),(756,'Switzerland',4,1),(760,'Syrian Arab Republic',8,1),(762,'Tajikistan',8,1),(764,'Thailand',6,1),(768,'Togo',5,1),(772,'Tokelau',7,1),(776,'Tonga',7,1),(780,'Trinidad and Tobago',2,1),(784,'United Arab Emirates',8,1),(788,'Tunisia',5,1),(792,'Turkey',8,1),(795,'Turkmenistan',8,1),(798,'Tuvalu',7,1),(800,'Uganda',5,1),(804,'Ukraine',4,1),(807,'Macedonia',4,1),(818,'Egypt',5,1),(826,'United Kingdom',4,1),(834,'Tanzania',5,1),(840,'United States',1,0),(850,'US Virgin Islands',1,1),(854,'Burkina Faso',5,1),(858,'Uruguay',3,1),(860,'Uzbekistan',8,1),(862,'Venezuela',3,1),(882,'Samoa',7,1),(887,'Yemen',8,1),(891,'Yugoslavia',4,1),(894,'Zambia',5,1);						
			", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';

		// States table.
        $result = mysql_query("CREATE TABLE `retreat_location_states` (
			  `states_id` int(11) NOT NULL AUTO_INCREMENT,
			  `states_code` char(3) NOT NULL DEFAULT '',
			  `states_name` varchar(50) NOT NULL DEFAULT '',
			  `countries_id` int(11) DEFAULT NULL,
			  PRIMARY KEY (`states_id`),
			  UNIQUE KEY `states_id` (`states_id`)
			) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8;", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';
        $result = mysql_query("
				INSERT INTO `retreat_location_states` VALUES (1,'BA','Buenos Aires',1),(2,'AK','Alaska',840),(3,'AL','Alabama',840),(4,'AR','Arkansas',840),(6,'AZ','Arizona',840),(7,'CA','California',840),(8,'CO','Colorado',840),(9,'CT','Connecticut',840),(10,'DC','District of Columbia',840),(11,'DE','Delaware',840),(12,'FL','Florida',840),(13,'GA','Georgia',840),(15,'HI','Hawaii',840),(16,'IA','Iowa',840),(17,'ID','Idaho',840),(18,'IL','Illinois',840),(19,'IN','Indiana',840),(20,'KS','Kansas',840),(21,'KY','Kentucky',840),(22,'LA','Louisiana',840),(23,'MA','Massachusetts',840),(24,'MD','Maryland',840),(25,'ME','Maine',840),(27,'MI','Michigan',840),(28,'MN','Minnesota',840),(29,'MO','Missouri',840),(30,'MS','Mississippi',840),(31,'MT','Montana',840),(32,'NC','North Carolina',840),(33,'ND','North Dakota',840),(34,'NE','Nebraska',840),(35,'NH','New Hampshire',840),(36,'NJ','New Jersey',840),(37,'NM','New Mexico',840),(38,'NV','Nevada',840),(39,'NY','New York',840),(40,'OH','Ohio',840),(41,'OK','Oklahoma',840),(42,'OR','Oregon',840),(43,'PA','Pennsylvania',840),(44,'PR','Puerto Rico',840),(46,'RI','Rhode Island',840),(47,'SC','South Carolina',840),(48,'SD','South Dakota',840),(49,'TN','Tennessee',840),(50,'TX','Texas',840),(51,'UT','Utah',840),(52,'VA','Virginia',840),(53,'VI','Virgin Islands',840),(54,'VT','Vermont',840),(55,'WA','Washington',840),(56,'WI','Wisconsin',840),(57,'WV','West Virginia',840),(58,'WY','Wyoming',840),(59,'BAI','Buenos Aires',32),(60,'CDB','Córdoba',32),(61,'SC','Santa Cruz',68),(62,'CAT','Catalunya',724),(63,'LYO','Lyon state',250),(64,'PAR','Paris (state)',250),(65,'ONT','Ontario',124),(67,'ALB','Alberta',124),(68,'BC','British Columbia',124),(69,'MAN','Manitoba',124),(70,'NS','Nova Scotia',124),(71,'QC','Quebec',124),(72,'VIC','Victoria',36),(73,'NSW','New South Wales',36),(74,'','Noord-Holland',528),(75,'','Zuid-Holland',528),(76,'','Antwerpen',56),(77,'MAN','Manchester',826),(78,'','Copenhagen',208),(79,'','Caracas',862),(80,'SJO','San Jose',188),(82,'','Helsinki',246),(83,'.','Istanbul',792),(85,'WC','Western Cape',710),(86,'','Stockholm',752),(87,'SP','São Paulo',76),(88,'CUN','Cundinamarca',170),(90,'','Israel',376),(91,'','Oslo',578),(92,'','Essex',826);
			", $dbLink);
        if(!$result) echo 'Line: '.__LINE__.'. Invalid query: ' . mysql_error().'<br />';


        // Close database connection.
        mysql_close($dbLink);
        echo "<h1>Reset Database.</h1>";
    }

?>
<!doctype html>
<html>
    <body>
        <form method="post">
            Name:
            <input name="username" type="text">
            <br>
            Password:
            <input name="password" type="password">
            <br>
            <input type="submit" name="submit" value="reset" />
        </form>
    </body>
</html>
