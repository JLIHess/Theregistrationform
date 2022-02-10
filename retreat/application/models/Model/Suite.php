<?php

/**
 * Class Model_Suite
 */
class Model_Suite extends Core_Model
{

    /**
     * @var array|null
     */
    public $data = null;

    /**
     * @var array
     */
    public $packageDates = [];

    /**
     * @var array
     */
    public $basicDates = [];

    /**
     * @var string
     */
    public $dateFormat = 'm/d/Y';

    /**
     * @var string
     */
    public $datePickerFormat = 'mm/dd/yy';

    /**
     * @var int
     */
    public $countGuestsByDefault = 10;

    /**
     * @var string in format: mm/dd/YYYY
     */
    public $earlyBirdLastDate = '04/28/2018';
    public $earlyBirdMinDiscount = 50;
    public $earlyBirdMaxDiscount = 50;
    public $earlyBirdProgramCount = 3;

    public $enableEarlyBird = false;//true;

    /**
     * @var array $dailyDiscount count of days => discount
     */
    public $dailyDiscount = [
        5 => 200,
        4 => 120,
        3 => 60,
    ];

    /**
     * @var string
     */
    public $priceName = '';

    public $defaultRoomType = 'No Rooming';
    public $defaultOccupancy = 'None';

    /**
     * Hidden room types
     *
     * @var array
     */
    public $disabledTypes = [
        "No Rooming",
    ];

    public $lastVisibleOccupancy = 'Double Occupancy';

    /**
     * Hidden occupancies
     *
     * @var array
     */
    public $disabledOccupancies = [
        "3rd or 4th Room",
    ];

    /**
     * @var int
     */
    public $lowestRateTax = 0;

    /**
     * @var int
     */
    public $highestRateTax = 0;

    public $taxPercentage = 0;

    public $nonTaxableGuestTypes = [
        'Child',
        'Toddler',
        'Infant',
        'Adult',
    ];

    public $programStartTime = '03:00pm';
    public $programEndTime = '03:00pm';

    public $programStartTimes = [
        '09:00am',
        '03:00pm',
    ];

    public $programEndTimes = [
        '03:00pm',
        '11:00pm',
    ];

    public $programStartOffset = 180;
    public $programEndOffset = 180;
    public $programTotalOffset = 300;

    public $programKidsStartOffset = 107;
    public $programKidsEndOffset = 107;

    public $guestCalcOffset = [
        'Adult',
        'Teen',
        'Child',
        'Toddler',
    ];

    public $basic_program_tax = '0';
    public $basic_hotel_tax = '0';

    /**
     * Guest types
     */
    const GUEST_TYPE_ADULT = 'Adult';
    const GUEST_TYPE_CHILD = 'Child';
    const GUEST_TYPE_TODDLER = 'Toddler';
    const GUEST_TYPE_INFANT = 'Infant';
    const GUEST_TYPE_TEEN = 'Teen';

    /**
     * Price types
     */
    const OPTION_NAME_PRICE = 'price';
    const OPTION_NAME_EARLY_BIRD = 'early_bird_price';

    /**
     * Date types
     */
    const DATE_TYPE_PACKAGE = 'package';
    const DATE_TYPE_PROGRAM = 'program';
    const DATE_TYPE_HOTEL = 'hotel';

    /**
     * Get file patch of suites data
     *
     * @return string
     */
    public function getFilePatch()
    {
        return BASE_PATCH . DS . 'data' . DS . 'suites.json';
    }

    /**
     * @return float
     */
    public function getProgramTax()
    {
        $data = $this->getData();

        return isset($data['program_tax']) ? $data['program_tax'] : $this->basic_program_tax;
    }

    /**
     * @param $roomType
     * @param null $occupancyName
     *
     * @return float
     */
    public function getHotelTax($roomType, $occupancyName = null)
    {

        $roomData = $this->getRoomData($roomType);

        if (!empty($occupancyName)) {
            $occupancy = $this->getOccupancyData($roomType, $occupancyName);

            if (!empty($occupancy['tax'])) {
                return $occupancy['tax'];
            }
        }

        return isset($roomData['tax']) ? $roomData['tax'] : $this->basic_hotel_tax;
    }

    public function getOffset($guestType)
    {
        $data = $this->getData();

        return isset($data['half_day_offset'], $data['half_day_offset'][$guestType]) ?
            $data['half_day_offset'][$guestType] : 0;
    }

    public function getCribPrice()
    {
        $data = $this->getData();

        return isset($data['crib_price']) ? $data['crib_price'] : 0;
    }

    public function getRollawayPrice()
    {
        $data = $this->getData();

        return isset($data['rollaway_price']) ? $data['rollaway_price'] : 0;
    }

    /**
     * Get suites data
     *
     * @return array|null
     */
    public function getData()
    {
        if ($this->data === null) {
            $filePatch = $this->getFilePatch();

            if (file_exists($filePatch)) {
                $this->data = json_decode(file_get_contents($filePatch), true);
            }
        }

        return $this->data;
    }

    public function getRoomData($roomTypeName)
    {
        $roomData = [];
        $data = $this->getData();

        if (isset($data['room_types'], $data['room_types'][$roomTypeName])) {
            $roomData = $data['room_types'][$roomTypeName];
        }

        return $roomData;
    }

    public function getOccupancyData($roomTypeName, $occupancyName)
    {
        $occupancyData = [];
        $data = $this->getData();

        if (isset($data['room_types'], $data['room_types'][$roomTypeName],
            $data['room_types'][$roomTypeName]['occupancies'],
            $data['room_types'][$roomTypeName]['occupancies'][$occupancyName])
        ) {
            $occupancyData = $data['room_types'][$roomTypeName]['occupancies'][$occupancyName];
        }

        return $occupancyData;
    }

    public function setPriceName()
    {
        $this->priceName = self::OPTION_NAME_PRICE;
    }

    public function isEarlyBird()
    {
        if ($this->enableEarlyBird === true && (strtotime('now') <= strtotime($this->earlyBirdLastDate))) {
            return true;
        }

        return false;
    }

    /**
     * Get list of guests which can be counted
     *
     * @return array
     */
    public function getListOfCalculatedGuests()
    {
        return [
            self::GUEST_TYPE_ADULT,
            self::GUEST_TYPE_TEEN,
            self::GUEST_TYPE_CHILD,
            self::GUEST_TYPE_TODDLER,
        ];
    }

    /**
     * Get list of program dates
     *
     * @return array
     */
    public function getProgramDates()
    {
        foreach ($this->walkByDates() as $data) {
            if ($data['type'] == self::DATE_TYPE_PROGRAM) {
                for (
                    $date = strtotime($data['start_date']); $date <= strtotime($data['end_date']);
                    $date += 24 * 3600
                ) {
                    $this->packageDates[date($this->dateFormat, $date)] =
                        date($this->dateFormat, $date);
                }
            }
        }
        ksort($this->packageDates);

        return $this->packageDates;
    }

    /**
     * Get hotel dates
     *
     * @return mixed
     */
    public function getBasicDates()
    {
        if (empty($this->basicDates)) {
            $packageDates = $this->getProgramDates();
            foreach ($this->walkByDates() as $data) {
                if ($data['type'] == self::DATE_TYPE_HOTEL) {
                    for (
                        $date = strtotime($data['start_date']); $date <= strtotime($data['end_date']);
                        $date += 24 * 3600
                    ) {
                        if (!in_array(date($this->dateFormat, $date), $packageDates)) {
                            $this->basicDates[date($this->dateFormat, $date)] =
                                date($this->dateFormat, $date);
                        }
                    }
                }
            }
            ksort($this->basicDates);
        }

        return $this->basicDates;
    }

    /**
     * Get list of room types
     *
     * @return array
     */
    public function getRoomTypeList()
    {
        $return = [];
        $data = $this->getData();
        foreach ($data['room_types'] as $roomType => $suiteData) {
            if (isset($suiteData['occupancies']) && !in_array($roomType, $this->disabledTypes) && $suiteData['available'] ) {
                $return[$roomType] = $roomType;
            }
        }

        return $return;
    }

    public function getOccupancyListByRoomType($roomTypeName)
    {
        $return = [];
        $suites = $this->getData();

        if (isset($suites['room_types'], $suites['room_types'][$roomTypeName],
                $suites['room_types'][$roomTypeName]['occupancies']) && !in_array($roomTypeName, $this->disabledTypes)
        ) {
            $room = $suites['room_types'][$roomTypeName];

            foreach ($room['occupancies'] as $occupancy => $occupancyData) {
                if (!in_array($occupancy, $this->disabledOccupancies)) {
                    $return[$occupancy] = $occupancy;
                }
            }
        }

        return $return;
    }

    public function getRoomDescription($roomTypeName, $occupancyName = null)
    {
        $suites = $this->getData();

        $data = [];
        if (isset($suites['room_types'], $suites['room_types'][$roomTypeName],
            $suites['room_types'][$roomTypeName]['occupancies'])
        ) {
            $description = "";
            $image = "";

            $room = $suites['room_types'][$roomTypeName];
            if (!empty($room['image'])) {
                $image = $room['image'];
            }
            if (!empty($room['description'])) {
                $description = $room['description'];
            }

            if ($occupancyName !== null && !empty($room['occupancies'][$occupancyName])) {
                $room = $room['occupancies'][$occupancyName];

                if (!empty($room['image'])) {
                    $image = $room['image'];
                }
                if (!empty($room['description'])) {
                    $description = $room['description'];
                }
            }

            $filePath = BASE_PATCH . DS . 'img' . DS . 'rooms' . DS . $image;
            $fileUrl = BASE_URL . 'img/rooms/' . $image;

            $data['image'] = (!empty($image) && file_exists($filePath)) ?
                $fileUrl : "";
            $data['description'] = $description;
        }

        return $data;
    }

    public function getGuestRange($roomTypeName = null, $guestType = [], $occupancyName = null)
    {
        $personRange = [];

        if ($roomTypeName == null && empty($guestType) && $occupancyName == null) {
            foreach (array_keys($this->getGuestTypeList()) as $type) {
                $min = 0;
                $max = 0;

                $personRange[$type] = [$min, $max];
            }

            return $personRange;
        }

        $totalPersons = 0;

        foreach ($guestType as $type => $count) {
            $totalPersons += $count;
        }

        // pre defined room type if undefined
        if (empty($roomTypeName)) {
            $roomTypeName = 'No Rooming';
        }

        if (in_array($occupancyName, $this->disabledOccupancies) || $this->lastVisibleOccupancy == $occupancyName) {
            $occupancyName = null;
        }

        // generate range
        $count = 0;
        foreach (array_keys($this->getGuestTypeList()) as $type) {
            $min = 0;
            $max = 100;

            // TODO: fix for dynamically changing
            if ($type == self::GUEST_TYPE_ADULT) {
                $personMinCount = 1;
            } else {
                $personMinCount = $this->getMinPersonCount($roomTypeName, $occupancyName, $type);
            }
            $personMaxCount = $this->getMaxPersonCount($roomTypeName, $occupancyName, $type, $guestType);

            if ($personMinCount !== null) {
                $min = $personMinCount;
            }
            if ($personMaxCount !== null) {
                $max = $personMaxCount;
            }

            $personRange[$type] = [$min, $max];
        }

        return $personRange;
    }

    public function getBeddingByPersons($roomTypeName = null, $occupancyName = null, $persons)
    {
        $suites = $this->getData();

        // pre defined room type if undefined
        if (empty($roomTypeName)) {
            $roomTypeName = 'No Rooming';
        }

        // pre defined first occupancy if undefined
        if (empty($occupancyName)) {
            if (isset($suites['room_types'], $suites['room_types'][$roomTypeName],
                $suites['room_types'][$roomTypeName]['occupancies'])
            ) {
                $occupancyNames = array_keys($suites['room_types'][$roomTypeName]['occupancies']);
                $occupancyName = reset($occupancyNames);
            }
        }

        $data = [];
        if (isset($suites['room_types'], $suites['room_types'][$roomTypeName],
            $suites['room_types'][$roomTypeName]['occupancies'],
            $suites['room_types'][$roomTypeName]['occupancies'][$occupancyName])
        ) {
            $occupancyData = $suites['room_types'][$roomTypeName]['occupancies'][$occupancyName];

            if (isset($occupancyData['bedding'], $occupancyData['bedding']['persons'])
                && !empty($occupancyData['bedding']) && !empty($occupancyData['bedding']['persons'])
            ) {
                if (isset($occupancyData['bedding']['persons'][$persons])) {
                    $data = $occupancyData['bedding']['persons'][$persons];
                } else {
                    $beddingOptions = $occupancyData['bedding']['persons'];
                    ksort($beddingOptions);
                    foreach ($beddingOptions as $count => $bedOptions) {
                        if ($count < $persons) {
                            $data = $occupancyData['bedding']['persons'][$count];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get list of guest types
     *
     * @return array
     */
    public function getGuestTypeList()
    {
        return [
            self::GUEST_TYPE_ADULT   => 'Adult',
            self::GUEST_TYPE_TEEN    => 'Teen',
            self::GUEST_TYPE_CHILD   => 'Child',
            self::GUEST_TYPE_TODDLER => 'Toddler',
            self::GUEST_TYPE_INFANT  => 'Infant',
        ];
    }

    public function getGuestTypeLabel($type)
    {

        $label = $type;

        switch ($type) {
            case self::GUEST_TYPE_ADULT:
                $label = 'Adult';
                break;
            case self::GUEST_TYPE_TEEN:
                $label = 'Teen (Ages 14-17)';
                break;
            case self::GUEST_TYPE_CHILD:
                $label = 'Child (Ages 4-13)';
                break;
            case self::GUEST_TYPE_TODDLER:
                $label = 'Toddler (Ages 2-3)';
                break;
            case self::GUEST_TYPE_INFANT:
                $label = 'Infant (Ages 0-1)';
                break;
        }

        return $label;
    }

    /**
     * Check is package or not
     *
     * @param $startDate
     * @param $endDate
     *
     * @return bool
     */
    public function isPackageDates($startDate, $endDate)
    {
        $isPackage = false;
        foreach ($this->walkByDates() as $data) {
            if ($data['type'] == self::DATE_TYPE_PACKAGE) {
                if (strtotime($startDate) == strtotime($data['start_date'])
                    && strtotime($endDate) == strtotime($data['end_date'])
                ) {
                    $isPackage = true;
                    break;
                }
            }
        }

        return $isPackage;
    }

    /*
     * Get array of all dates
     *
     * @return array
     */
    protected function walkByDates()
    {
        $arrayData = [];

        $data = $this->getData();
        foreach ($data['room_types'] as $roomType => $roomTypeData) {
            foreach ($roomTypeData['occupancies'] as $occupancy => $occupancyData) {
                foreach ($occupancyData['dates'] as $date) {
                    $arrayData[] = $date;
                }
            }
        }

        return $arrayData;
    }

    /**
     * Find occupancy name by guest count
     *
     * @param array $guestTypes
     * @param null $roomTypeName
     * @param null $occupancyName
     *
     * @return int|null|string
     */
    public function getOccupancyName($guestTypes = [], $roomTypeName = null, $occupancyName = null)
    {
        $suites = $this->getData();

        $guestTotal = 0;
        foreach ($guestTypes as $type => $count) {
            if (in_array($type, $this->getListOfCalculatedGuests())) {
                $guestTotal += $count;
            }
        }
        // 1 Adult by default
        if ($guestTotal == 1 && !empty($guestTypes[self::GUEST_TYPE_ADULT])) {
            return $occupancyName;
        }

        if (empty($roomTypeName)) {
            $roomTypeName = $this->defaultRoomType;
        }
        if (isset($suites['room_types'], $suites['room_types'][$roomTypeName],
            $suites['room_types'][$roomTypeName]['occupancies'])) {

            $roomTypeData = $suites['room_types'][$roomTypeName]['occupancies'];

            if (empty($occupancyName)) {
                $occupancyNames = array_keys($roomTypeData);
                $occupancyName = reset($occupancyNames);
            }

            if (isset($roomTypeData[$occupancyName])) {

                $min = $this->getMinPersonCount($roomTypeName, $occupancyName);
                $max = $this->getMaxPersonCount($roomTypeName, $occupancyName);

                // validate selected guests in selected occupancy
                $validCount = [];
                $calcCount = 0;
                foreach ($guestTypes as $type => $count) {
                    $calcCount += $count;

                    $minCount = $this->getMinPersonCount($roomTypeName, $occupancyName, $type);
                    $maxCount = $this->getMaxPersonCount($roomTypeName, $occupancyName, $type);

                    if ($count >= $minCount && $count <= $maxCount && $calcCount <= $max) {
                        $validCount[$type] = true;
                    } else {
                        $validCount[$type] = false;
                    }
                }

                // find new valid occupancy
                if (in_array(false, $validCount) && ($guestTotal > $max || $guestTotal < $min)) {

                    foreach ($roomTypeData as $name => $occupancy) {
                        $min = $this->getMinPersonCount($roomTypeName, $name);
                        //$max = $this->getMaxPersonCount($roomTypeName, $name);

                        if ($name != $occupancyName) {
                            if ($guestTotal >= $min) {
                                $occupancyName = $name;
                            }
                        }
                    }
                }
            }
        }

        return $occupancyName;
    }

    /**
     * @param string $roomTypeName
     * @param string|null $occupancyName
     * @param string|null $guestType
     *
     * @return int|null
     */
    public function getMinPersonCount($roomTypeName, $occupancyName = null, $guestType = null)
    {
        $suites = $this->getData();
        $minCount = null;
        $min = null;

        if (isset($suites['room_types'], $suites['room_types'][$roomTypeName],
            $suites['room_types'][$roomTypeName]['occupancies'])) {

            $roomTypeData = $suites['room_types'][$roomTypeName]['occupancies'];

            if (!array_key_exists($occupancyName, $roomTypeData)) {
                $occupancyDataArray = $roomTypeData;
            } else {
                $occupancyDataArray[$occupancyName] = $roomTypeData[$occupancyName];
            }

            $minCount = [];
            foreach ($occupancyDataArray as $occupancy => $occupancyData) {
                $minCount[$occupancy] = null;
                if (!empty($occupancyData['persons'])) {
                    $min = !empty($occupancyData['persons']['min']) ? $occupancyData['persons']['min'] : [];
                    foreach ($min as $minData) {
                        foreach ($minData as $count => $type) {
                            if (!empty($guestType)) {

                                if (is_array($type)) {
                                    if (in_array($guestType, $type)) {
                                        $minCount[$occupancy] += $count;
                                    }
                                } else if ($type == $guestType) {
                                    $minCount[$occupancy] += $count;
                                }
                            } else {
                                $minCount[$occupancy] += $count;
                            }
                            break;
                        }
                    }
                }
            }

            if (!empty($minCount[$occupancyName])) {
                $min = $minCount[$occupancyName];
            } else {
                $min = 0;
                foreach ($minCount as $count) {
                    if ($count !== null && ($min === null || $count < $min)) {
                        $min = $count;
                    }
                }
            }
        }

        return $min;
    }

    /**
     * @param string $roomTypeName
     * @param string|null $occupancyName
     * @param string|null $guestType
     * @param array $selectedGuests
     *
     * @return int|null
     */
    public function getMaxPersonCount($roomTypeName, $occupancyName = null, $guestType = null, $selectedGuests = [])
    {
        $suites = $this->getData();
        $maxCount = null;
        $max = null;

        $occupancy = $occupancyName;

        if (isset($suites['room_types'], $suites['room_types'][$roomTypeName],
            $suites['room_types'][$roomTypeName]['occupancies'])) {

            $roomTypeData = $suites['room_types'][$roomTypeName]['occupancies'];

            if (!array_key_exists($occupancyName, $roomTypeData)) {
                $occupancyDataArray = $roomTypeData;
            } else {
                $occupancyDataArray[$occupancyName] = $roomTypeData[$occupancyName];
            }

            $maxCount = [];
            foreach ($occupancyDataArray as $occupancyName => $occupancyData) {
                $maxCount[$occupancyName] = null;
                if (!empty($occupancyData['persons'])) {
                    $max = !empty($occupancyData['persons']['max']) ? $occupancyData['persons']['max'] : [];
                    $maxPersons = !empty($occupancyData['persons']['max_persons']) ? $occupancyData['persons']['max_persons'] : null;

                    foreach ($max as $maxData) { 
                        foreach ($maxData as $count => $typeData) {

                            if (!empty($guestType)) {
                                if (is_array($typeData)) {
                                    if (in_array($guestType, $typeData)) {

                                        if (!empty($selectedGuests)) {

                                            $selectedCalcCount = 0;
                                            foreach ($selectedGuests as $selectedGuestType => $selectedCount) {
                                                if (!is_null($maxPersons)) {
                                                    if ($guestType != $selectedGuestType) {
                                                        $selectedCalcCount += $selectedCount;
                                                    }
                                                } else {
                                                    if (in_array($selectedGuestType, $typeData)
                                                        && $guestType != $selectedGuestType
                                                    ) {
                                                        $selectedCalcCount += $selectedCount;
                                                    }
                                                }
                                            }
                                            if (!is_null($maxPersons) && $selectedCalcCount < $maxPersons) {
                                                $count = $maxPersons - $selectedCalcCount;
                                            } else if ($selectedCalcCount < $count) {
                                                $count = $count - $selectedCalcCount;
                                            } else {
                                                $count = 0;
                                            }
                                        }
                                        $maxCount[$occupancyName] += $count;
                                    }
                                } else if ($typeData == $guestType) {
                                    $maxCount[$occupancyName] += $count;
                                }
                            } else {
                                $maxCount[$occupancyName] += $count;
                            }
                        }
                    }
                }
            }

            $max = null;
            if ($occupancy !== null) {
                $max = max($maxCount);
            } else {
                foreach ($maxCount as $count) {
                    if ($count !== null) {

                        if ($max === null || $count > $max) {
                            $max = $count;
                        }
                    }
                }
            }
        }

        return $max;
    }

    public function getStaffData($roomTypeName, $occupancyName, $guestTypes = [], $days = [], $startTime = '', $endTime = '')
    {
        $list = [];
        $data = $this->getOccupancyData($roomTypeName, $occupancyName);

        if (!empty($data['staff'])) {
            $list = $data['staff'];
        } else {
            $data = $this->getData();
            if (!empty($data['staff'])) {
                $list = $data['staff'];
            }
        }

        if (!empty($list)) {
            if (!empty($guestTypes) && is_array($guestTypes)) {
                foreach ($list as $name => $itemData) {
                    if (isset($itemData['related_guest'])) {
                        if (array_key_exists($itemData['related_guest'], $guestTypes)
                            && (int)$guestTypes[$itemData['related_guest']]
                        ) {
                            continue;
                        }
                        unset($list[$name]);
                    }
                }
            }

            $dates = [];
            foreach ($list as $name => $itemData) {
                if (!empty($itemData['dates']) && is_array($itemData['dates'])) {
                    foreach ($itemData['dates'] as $day => $dayData) {
                        $dates[] = $day;
                    }
                }
            }

            if (!empty($days)) {
                if (is_array($days)) {
                    if (count($days) == 2) {
                        sort($days);
                        $startDate = reset($days);
                        $endDate = end($days);

                        if ($startDate && $endDate) {
                            $startDate = DateTime::createFromFormat($this->dateFormat, $startDate);
                            $endDate = DateTime::createFromFormat($this->dateFormat, $endDate);

                            if (is_a($endDate, 'DateTime') && is_a($startDate, 'DateTime')) {
                                $startDate = $startDate->getTimestamp();
                                $endDate = $endDate->getTimestamp();

                                $day = 24 * 3600;

                                $dateRange = [];
                                for ($d = $startDate; $d <= $endDate; $d += $day) {
                                    $dateRange[] = date($this->dateFormat, $d);
                                }
                                $days = $dateRange;
                            }
                        }
                    }
                } else {
                    $days = [$days];
                }

                $no = 0;
                foreach ($days as $key => $date) {
                    if ($date) {
                        if (!empty($startTime) && $no == 0) {
                            $date = $date . ' ' . $startTime;
                            $fromFormat = $this->dateFormat . ' h:ia';
                            $toFormat = 'Y-m-d h:ia';
                        } else if (!empty($endTime) && $no == count($days) - 1) {
                            $date = $date . ' ' . $endTime;
                            $fromFormat = $this->dateFormat . ' h:ia';
                            $toFormat = 'Y-m-d h:ia';
                        } else {
                            $fromFormat = $this->dateFormat;
                            $toFormat = 'Y-m-d';
                        }

                        $date = DateTime::createFromFormat($fromFormat, $date);

                        if (is_a($date, 'DateTime')) {

                            $formattedDate = $date->format($toFormat);
                            if (!in_array($formattedDate, $dates)) {
                                $formattedDate = $date->format('Y-m-d');
                            }
                            $days[$key] = $formattedDate;
                            $no++;
                        } else {
                            unset($days[$key]);
                            $no = 0;
                        }
                    }
                }

                foreach ($list as $name => $itemData) {
                    if (!empty($itemData['dates']) && is_array($itemData['dates'])) {
                        foreach ($itemData['dates'] as $day => $dayData) {

                            if (!in_array($day, $days)) {
                                unset($list[$name]['dates'][$day]);
                            }
                        }
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Get total price by selected options
     *
     * @param array $data
     * @param bool $totalOnly indicate if need to return total value only or return all details
     *
     * @return false|int
     */
    public function getTotal($data, $totalOnly = true)
    {
        $total = false;
        if (!$totalOnly) {
            $priceResponse = [
                'program_price'       => 0,
                'program_day_price'   => 0,
                'program_days_count'  => 0,
                'hotel_day_price'     => 0,
                'hotel_days_count'    => 0,
                'early_bird_discount' => 0,
                'lock_in_discount'    => 0,
            ];
        }
        if (!empty($data['guest_type'])
            && (isset($data['start_date'], $data['end_date'])
                || isset($data['program_start_date'], $data['program_end_date']))
        ) {
            $startDate = (!empty($data['start_date'])) ? strtotime($data['start_date']) : null;
            $endDate = (!empty($data['end_date'])) ? strtotime($data['end_date']) : null;
            $programStartDate = (!empty($data['program_start_date'])) ? strtotime($data['program_start_date']) : null;
            $programEndDate = (!empty($data['program_end_date'])) ? strtotime($data['program_end_date']) : null;
            $hotelStartDate = (!empty($data['hotel_start_date'])) ? strtotime($data['hotel_start_date']) : null;
            $hotelEndDate = (!empty($data['hotel_end_date'])) ? strtotime($data['hotel_end_date']) : null;
            $roomType = (string)(!empty($data['room_type'])) ? $data['room_type'] : null;
            $occupancy = (string)(!empty($data['occupancy'])) ? $data['occupancy'] : null;
            $guestType = (string)$data['guest_type'];
            $guestTypes = (array)$data['guest_types'];

            $day = 24 * 3600;
            $data = $this->getData();

            // pre defined room type if undefined
            if (empty($roomType)) {
                $roomType = 'No Rooming';
            }

            // pre defined first occupancy if undefined
            if (isset($data['room_types'], $data['room_types'][$roomType],
                $data['room_types'][$roomType]['occupancies'])
            ) {
                $occupancyNames = array_keys($data['room_types'][$roomType]['occupancies']);

                if (!in_array($occupancy, $occupancyNames) || empty($occupancy)) {
                    $occupancy = reset($occupancyNames);
                }
            }

            if (isset($data['room_types'], $data['room_types'][$roomType],
                $data['room_types'][$roomType]['occupancies'],
                $data['room_types'][$roomType]['occupancies'][$occupancy],
                $data['room_types'][$roomType]['occupancies'][$occupancy]['dates'])
            ) {
                $dates = $data['room_types'][$roomType]['occupancies'][$occupancy]['dates'];

                $packageNumDays = 0;
                $packageStartDate = null;
                $packageEndDate = null;
                $programDates = [];
                $hotelDates = [];
                $total = 0;
                $isAvailable = false;

                if ($programStartDate === null && $programEndDate === null) {
                    for ($d = $startDate; $d < $endDate; $d += $day) {
                        $hotelDates[date($this->dateFormat, $d)] = date($this->dateFormat, $d);
                    }
                    asort($hotelDates);

                    if (empty($hotelDates) && $startDate == $endDate) {
                        $hotelDates[date($this->dateFormat, $startDate)] = date($this->dateFormat, $startDate);
                    }

                    $programDates = $hotelDates;

                } else if ($hotelStartDate === null && $hotelEndDate === null) {
                    for ($d = $programStartDate; $d < $programEndDate; $d += $day) {
                        $programDates[date($this->dateFormat, $d)] = date($this->dateFormat, $d);
                    }
                    asort($programDates);

                    if (empty($programDates) && $programStartDate == $programEndDate) {
                        $programDates[date($this->dateFormat, $programStartDate)] =
                            date($this->dateFormat, $programStartDate);
                    }

                    $startDate = $programStartDate;
                    $endDate = $programEndDate;
                } else {
                    for ($d = $programStartDate; $d < $programEndDate; $d += $day) {
                        $programDates[date($this->dateFormat, $d)] = date($this->dateFormat, $d);
                    }
                    asort($programDates);

                    if (empty($programDates) && $programStartDate == $programEndDate) {
                        $programDates[date($this->dateFormat, $programStartDate)] =
                            date($this->dateFormat, $programStartDate);
                    }

                    if ($hotelStartDate != null && $hotelEndDate != null) {
                        for ($d = $hotelStartDate; $d < $hotelEndDate; $d += $day) {
                            $hotelDates[date($this->dateFormat, $d)] = date($this->dateFormat, $d);
                        }
                        asort($hotelDates);

                        if (empty($hotelDates) && $hotelStartDate == $hotelEndDate) {
                            $hotelDates[date($this->dateFormat, $hotelStartDate)] =
                                date($this->dateFormat, $hotelStartDate);
                        }

                        $dateRange = [];
                        foreach ($hotelDates as $key => $date) {
                            if (in_array($date, $programDates)) {
                                $dateRange[$key] = $date;
                            }
                        }

                        asort($dateRange);

                        if (!empty($dateRange)) {
                            $startDate = strtotime(reset($dateRange));
                            $endDate = strtotime(end($dateRange)) + $day;
                        }
                    } else {
                        $startDate = $programStartDate;
                        $endDate = $programEndDate;
                    }
                }

                // find package price which includes program and hotel dates
                if ($startDate && $endDate) {

                    foreach ($dates as $data) {
                        if (isset($data['guest_types'], $data['guest_types'][$guestType],
                            $data['guest_types'][$guestType][$this->priceName])
                        ) {
                            if ($data['type'] == self::DATE_TYPE_PACKAGE) {

                                $curStartDate = strtotime($data['start_date']);
                                $curEndDate = strtotime($data['end_date']);
                                $price = $data['guest_types'][$guestType][$this->priceName];

                                if ($startDate <= $curStartDate && $endDate >= $curEndDate
                                    && $packageNumDays <= $data['count']
                                ) {
                                    $packageStartDate = $curStartDate;
                                    $packageEndDate = $curEndDate;
                                    $packageNumDays = $data['count'];
                                    $total = $price;
                                    $isAvailable = true;
                                }
                            }
                        }
                    }
                }

                // get additional hotel and package dates
                if (!empty($packageStartDate) && !empty($packageEndDate)) {

                    if (!empty($hotelDates)) {
                        foreach ($hotelDates as $key => $hotelDate) {
                            $hotelDate = strtotime($hotelDate);

                            if ($hotelDate >= $packageStartDate && $hotelDate < $packageEndDate) {
                                unset($hotelDates[$key]);
                            }
                        }
                    }

                    if (!empty($programDates)) {
                        foreach ($programDates as $key => $programDate) {
                            $programDate = strtotime($programDate);

                            if ($programDate >= $packageStartDate && $programDate < $packageEndDate) {
                                unset($programDates[$key]);
                            }
                        }
                    }
                }

                // summarize additional hotel dates
                if (!empty($hotelDates)) {

                    $hotelTotal = 0;

                    foreach ($dates as $data) {
                        if (isset($data['guest_types'], $data['guest_types'][$guestType],
                            $data['guest_types'][$guestType][$this->priceName])
                        ) {
                            if ($data['type'] == self::DATE_TYPE_HOTEL) {

                                if (!$totalOnly && $data['count'] == 1) {
                                    $priceResponse['hotel_day_price'] =
                                        $data['guest_types'][$guestType]['price'];
                                }

                                $curStartDate = strtotime($data['start_date']);
                                $curEndDate = strtotime($data['end_date']);
                                $price = $data['guest_types'][$guestType][$this->priceName];

                                if ($data['count'] > 1 && $curStartDate == $hotelStartDate
                                    && $curEndDate == $hotelEndDate
                                ) {
                                    if ($guestType == self::GUEST_TYPE_INFANT) {
                                        $hotelTotal = $price;
                                    } else {
                                        $hotelTotal += $price;
                                    }
                                    $isAvailable = true;

                                    if (!$totalOnly) {
                                        $priceResponse['hotel_days_count'] = $data['count'];
                                    }
                                }
                            }
                        }
                    }

                    if ($hotelTotal == 0) {
                        foreach ($hotelDates as $hotelDate) {
                            $hotelDate = strtotime($hotelDate);

                            foreach ($dates as $data) {
                                if (isset($data['guest_types'], $data['guest_types'][$guestType],
                                    $data['guest_types'][$guestType][$this->priceName])
                                ) {
                                    if ($data['type'] == self::DATE_TYPE_HOTEL) {

                                        $curStartDate = strtotime($data['start_date']);
                                        $curEndDate = strtotime($data['end_date']);
                                        $price = $data['guest_types'][$guestType][$this->priceName];

                                        if (($hotelDate >= $curStartDate) && ($hotelDate < $curEndDate)
                                            && $data['count'] == 1
                                        ) {

                                            if ($guestType == self::GUEST_TYPE_INFANT) {
                                                $hotelTotal = $price;
                                            } else {
                                                $hotelTotal += $price;
                                            }
                                            $isAvailable = true;

                                            if (!$totalOnly) {
                                                $priceResponse['hotel_days_count'] += $data['count'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (!$totalOnly) {
                        $priceResponse['hotel_price'] = $hotelTotal;
                    }

                    $total += $hotelTotal;
                }

                // summarize additional program dates
                if (!empty($programDates)) {

                    $programTotal = 0;

                    foreach ($dates as $data) {
                        if (isset($data['guest_types'], $data['guest_types'][$guestType],
                            $data['guest_types'][$guestType][$this->priceName])
                        ) {
                            if ($data['type'] == self::DATE_TYPE_PROGRAM) {

                                if (!$totalOnly && $data['count'] == 1) {
                                    $priceResponse['program_day_price'] =
                                        $data['guest_types'][$guestType]['price'];
                                }

                                $curStartDate = strtotime($data['start_date']);
                                $curEndDate = strtotime($data['end_date']);
                                $price = $data['guest_types'][$guestType][$this->priceName];

                                if ($data['count'] > 1 && $curStartDate == $programStartDate
                                    && $curEndDate == $programEndDate
                                ) {
                                    if ($guestType == self::GUEST_TYPE_INFANT) {
                                        $programTotal = $price;
                                    } else {
                                        $programTotal += $price;
                                    }

                                    $isAvailable = true;

                                    if (!$totalOnly) {
                                        $priceResponse['program_days_count'] = $data['count'];
                                    }
                                }
                            }
                        }
                    }

                    if ($programTotal == 0) {
                        foreach ($programDates as $programDate) {
                            $programDate = strtotime($programDate);

                            foreach ($dates as $data) {
                                if (isset($data['guest_types'], $data['guest_types'][$guestType],
                                    $data['guest_types'][$guestType][$this->priceName])
                                ) {
                                    if ($data['type'] == self::DATE_TYPE_PROGRAM) {

                                        $curStartDate = strtotime($data['start_date']);
                                        $curEndDate = strtotime($data['end_date']);
                                        $price = $data['guest_types'][$guestType][$this->priceName];

                                        if ((($startDate != $endDate && empty($hotelDates))
                                             || (!empty($hotelDates) && $programStartDate != $programEndDate))
                                            && $programDate >= $curStartDate && $programDate < $curEndDate
                                            && $data['count'] == 1
                                        ) {
                                            if ($guestType == self::GUEST_TYPE_INFANT) {
                                                $programTotal = $price;
                                            } else {
                                                $programTotal += $price;
                                            }

                                            $isAvailable = true;

                                            if (!$totalOnly) {
                                                $priceResponse['program_days_count'] += $data['count'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!$totalOnly) {
                        $priceResponse['program_price'] = $programTotal;

                        if (in_array($guestType, $this->getListOfCalculatedGuests())) {
                            $priceResponse['lock_in_discount'] =
                                ($priceResponse['program_day_price'] * $priceResponse['program_days_count']) - $priceResponse['program_price'];

                        }
                    }

                    $total += $programTotal;
                }

                if (!$isAvailable) {
                    $total = false;
                }
            }
        }

        if ($total) {
            // $total += $total*10/100;
            $earlyBirdDiscount = $this->getEarlyBirdDiscount($guestType, date('Y-m-d', $programStartDate), date('Y-m-d', $programEndDate));
            $total -= $earlyBirdDiscount;
            $priceResponse['early_bird_discount'] = $earlyBirdDiscount;
        }

        return ($totalOnly) ? $total : $priceResponse;
    }

    public function getEarlyBirdDiscount($guestTypes, $programStartDate, $programEndDate)
    {
        $price = 0;
        if ($this->isEarlyBird()) {

            if (!is_array($guestTypes)) {
                $guestTypes = [$guestTypes => 1];
            }

            $start = date_create($programStartDate);
            $end = date_create($programEndDate);
            $interval = date_diff($start, $end);
            $countProgramDays = $interval->format('%d');

            foreach ($guestTypes as $guestType => $guestCount) {
                if (in_array($guestType, [Model_Suite::GUEST_TYPE_ADULT, Model_Suite::GUEST_TYPE_TEEN])) {
                    if ($countProgramDays >= $this->earlyBirdProgramCount) {
                        $price += $this->earlyBirdMaxDiscount * $guestCount;
                    } /* else {
                        $price += $this->earlyBirdMinDiscount * $guestCount;
                    } */
                }
            }
        }

        return $price;
    }

    public function getDailyDiscount($guestTypes, $hotelStartDate, $hotelEndDate)
    {
        $price = 0;

        if (!is_array($guestTypes)) {
            $guestTypes = [$guestTypes => 1];
        }

        $startDate = date_create($hotelStartDate);
        $endDate = date_create($hotelEndDate);
        $interval = date_diff($startDate, $endDate);
        $interval = $interval->format('%d');

        if (!empty($this->dailyDiscount[$interval])) {

            foreach ($guestTypes as $guestType => $guestCount) {
                if (in_array($guestType, [Model_Suite::GUEST_TYPE_ADULT, Model_Suite::GUEST_TYPE_TEEN])) {
                    $price += $this->dailyDiscount[$interval] * $guestCount;
                }
            }
        }

        return $price;
    }

    /**
     * Price for additional staff
     *
     * @param $roomTypeName
     * @param $occupancyName
     * @param $staffName
     * @param $guestType
     * @param $days
     * @param $name
     * @param $startTime
     * @param $endTime
     *
     * @return int
     */
    public function getStaffPrice($roomTypeName, $occupancyName, $staffName, $guestType, $days, $name = null, $startTime = '', $endTime = '')
    {
        $staffPrice = 0;

        $data = $this->getStaffData($roomTypeName, $occupancyName, $guestType, $days, $startTime, $endTime);


        if (!empty($data[$staffName])) {
            $staffData = $data[$staffName];

            if (isset($staffData['dates'])) {
                foreach ($staffData['dates'] as $day => $dayData) {
                    if ($dayData['name'] == $name) {
                        $staffPrice += $dayData['price'];
                    }
                }
            } else if (isset($staffData['price'])) {
                $staffPrice += $staffData['price'];
            }

            if (!empty($guestType[$staffData['related_guest']])) {
                $staffPrice = $staffPrice * (int)$guestType[$staffData['related_guest']];
            }
        }

        return $staffPrice;
    }

    public function getTaxeableAmount($programStartDate, $programEndDate, $hotelStartDate = null, $hotelEndDate = null, $tax = null)
    {
        if ($hotelStartDate === null && $hotelEndDate === null) {
            return 0;
        }

        $day = 24 * 3600;
        $programStartDate = strtotime($programStartDate);
        $programEndDate = strtotime($programEndDate);
        $countProgramDays = 0;

        $hotelStartDate = strtotime($hotelStartDate);
        $hotelEndDate = strtotime($hotelEndDate);
        $countHotelDays = 0;

        $packageDays = $this->getProgramDates();
        $packageStartDate = strtotime(reset($packageDays));
        $packageEndDate = strtotime(end($packageDays));

        /*if ($programEndDate > $packageEndDate) {
            $programEndDate = $packageEndDate;
        }

        $programDates = array();
        if ($hotelEndDate > $packageStartDate) {
            for ($d = $programStartDate; $d < $programEndDate; $d += $day) {
                if (in_array(date($this->dateFormat, $d), $packageDays)) {
                    $countProgramDays++;
                    $programDates[date($this->dateFormat, $d)] = date($this->dateFormat, $d);
                }
            }
        }

        if ($hotelStartDate == $hotelEndDate) {
            $countHotelDays += 1;
        } else {
            for ($d = $hotelStartDate; $d < $hotelEndDate; $d += $day) {
                if ($countProgramDays == 0
                    || ($countProgramDays > 0
                        && (!in_array(date($this->dateFormat, $d), $packageDays)
                            || !in_array(date($this->dateFormat, $d), $programDates)))
                ) {
                    $countHotelDays++;
                }
            }
        }

        if ($tax !== null) {
            $programRate = $tax;
        } else if ($countProgramDays >= 4) {
            $programRate = $this->lowestRateTax;
        } else {
            $programRate = $this->highestRateTax;
        }

        $price = $programRate * $countProgramDays + $countHotelDays * $this->highestRateTax;
        */


        if ($hotelStartDate == $hotelEndDate) {
            if (in_array(date($this->dateFormat, $hotelStartDate), $packageDays)) {
                $countProgramDays++;
            } else {
                $countHotelDays++;
            }
        } else {
            for ($d = $hotelStartDate; $d < $hotelEndDate; $d += $day) {
                if (in_array(date($this->dateFormat, $d), $packageDays)) {
                    $countProgramDays++;
                } else {
                    $countHotelDays++;
                }
            }
        }

        $programTaxRate = $this->highestRateTax;
        if ($countProgramDays >= 4) {
            $programTaxRate = $this->lowestRateTax;
        }

        if (!empty($tax)) {
            $price = $tax * $programTaxRate + $countHotelDays * $tax;
        } else {
            $price = $countProgramDays * $programTaxRate + $countHotelDays * $this->highestRateTax;
        }

        return $price;
    }

    public function getActualTax($programStartDate, $programEndDate, $hotelStartDate = null, $hotelEndDate = null, $roomTypeName = null)
    {
        $tax = null;
        if ($roomTypeName !== null) {
            $data = $this->getData();

            if (isset($data['room_types'], $data['room_types'][$roomTypeName],
                $data['room_types'][$roomTypeName]['tax_rate'])
            ) {
                $tax = $data['room_types'][$roomTypeName]['tax_rate'];
            }
        }

        return ($this->getTaxeableAmount($programStartDate, $programEndDate, $hotelStartDate, $hotelEndDate, $tax) / 100)
               * $this->taxPercentage;
    }

    public function getGuestValidationMessage($roomTypeName, $occupancyName, $guestTypes)
    {
        $message = '';
        $data = $this->getData();

        $guestTotal = 0;
        foreach ($guestTypes as $type => $count) {
            if (in_array($type, $this->getListOfCalculatedGuests())) {
                $guestTotal += $count;
            }
        }

        $type = 'max';
        $minGuests = $this->getMinPersonCount($roomTypeName, $occupancyName);

        if ($guestTotal < $minGuests) {
            $type = 'min';
        }

        if (isset($data['room_types'], $data['room_types'][$roomTypeName],
            $data['room_types'][$roomTypeName]['occupancies'],
            $data['room_types'][$roomTypeName]['occupancies'][$occupancyName])
        ) {
            if (!empty($data['room_types'][$roomTypeName]['occupancies'][$occupancyName][$type . '_guest_message'])) {
                $message = $data['room_types'][$roomTypeName]['occupancies'][$occupancyName][$type . '_guest_message'];
            }
        }

        return [$type => $message];
    }

    public function getProgramTimeList($programStartDate = null, $programEndDate = null, $programStartTime = null)
    {
        $timeList = [];

        $programDates = $this->getProgramDates();
        $startDate = strtotime(reset($programDates));
        $endDate = strtotime(end($programDates));

        if (!empty($programEndDate)) {
            $programStartDate = strtotime($programStartDate);
            $programEndDate = strtotime($programEndDate);
            $timeList = array_combine($this->programEndTimes, $this->programEndTimes);
            $endTime = strtotime($this->programEndTime);

            if ($programEndDate == $endDate) {
                $timeList = [];
                foreach ($this->programEndTimes as $time) {
                    if (strtotime($time) <= $endTime) {
                        $timeList[$time] = $time;
                    }
                }
            }

            if (($programStartDate == $programEndDate)
                && (strtotime($programStartTime) == strtotime($this->programStartTime))
                && array_key_exists($this->programStartTime, $timeList)
            ) {
                unset($timeList[$this->programStartTime]);
            }
        } else if (!empty($programStartDate)) {
            $programStartDate = strtotime($programStartDate);
            $timeList = array_combine($this->programStartTimes, $this->programStartTimes);
            $startTime = strtotime($this->programStartTime);

            if ($programStartDate == $startDate) {
                $timeList = [];
                foreach ($this->programStartTimes as $time) {
                    if (strtotime($time) >= $startTime) {
                        $timeList[$time] = $time;
                    }
                }
            }
        }

        return $timeList;
    }

    public function getProgramOffset(
        $type, $programStartDate, $programEndDate, $programStartTime, $programEndTime,
        $roomType = "No Rooming", $occupancyName = "None", $guestType = []
    )
    {
        $programOffset = 0;
        $programStartOffset = 0;
        $programEndOffset = 0;
        $count = 0;
        $gType = null;

        if (!empty($guestType)) {
            $count = (int)reset($guestType);
            $gType = key($guestType);
        }

        if (!empty($roomType) && !empty($occupancyName)) {
            $startTimeList = $this->getProgramTimeList($programStartDate, null, $programStartTime);
            if (!empty($programStartTime) && array_key_exists($programStartTime, $startTimeList)) {
                if (strtotime($programStartTime) < strtotime($this->programStartTime)) {
                    $programStartOffset = $this->getOffset($gType);
                } else {
                    $programStartOffset = 0;
                }
            } else {
                $programStartTime = $this->programStartTime;
                $programStartOffset = 0;
            }
            $programOffset += $programStartOffset;

            $endTimeList = $this->getProgramTimeList($programStartDate, $programEndDate, $programStartTime);
            if (!empty($programEndTime) && array_key_exists($programEndTime, $endTimeList)) {
                if (strtotime($programEndTime) > strtotime($this->programEndTime)) {
                    $programEndOffset = $this->getOffset($gType);
                } else {
                    $programEndOffset = 0;
                }
            } else {
                $programEndTime = reset($endTimeList);
                if (!empty($endTimeList) && strtotime($programEndTime) != strtotime($this->programEndTime)) {
                    $programEndOffset = $this->getOffset($gType);
                } else {
                    $programEndOffset = 0;
                }
            }
            $programOffset += $programEndOffset;

            $occupancy = $this->getOccupancyData($roomType, $occupancyName);

            $programTotalOffset = $this->programTotalOffset;
            if (isset($occupancy['dates'])) {
                foreach ($occupancy['dates'] as $date) {
                    if ($date['type'] == 'program' && $date['count'] == 1) {
                        if (isset($date['guest_types'][$gType]) && !empty($date['guest_types'][$gType]['price'])) {
                            $programTotalOffset = $date['guest_types'][$gType]['price'];
                            break;
                        }
                    }
                }
            }

            if ($programOffset > $programTotalOffset) {
                $programOffset = $programTotalOffset;
            }

            if (!empty($guestType)) {
                if (!in_array($gType, $this->guestCalcOffset)) {
                    $programOffset = 0;
                }
                $programOffset *= $count;
            }
        }

        $result = 0;
        switch ($type) {
            case 'start':
                $result = $programStartOffset;
                break;
            case 'end':
                $result = $programEndOffset;
                break;
            case 'total':
                $result = $programOffset;
                break;
        }

        return $result;
    }
}