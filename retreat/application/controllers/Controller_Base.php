<?php

/**
 * Class Controller_Base
 */
class Controller_Base extends Core_Controller
{
    public $layout = 'main';
    
    /**
     * @var Model_Suite $suiteModel
     */
    private $_suiteModel = null;

    public function getSuiteModel()
    {
        if ($this->_suiteModel == null) {
            $this->_suiteModel = new Model_Suite();
        }

        if (isset($_GET['early-bird'])) {
            $this->_suiteModel->enableEarlyBird = (bool)$_GET['early-bird'];
        }
        if (isset($_GET['early-bird-date'])) {
            $this->_suiteModel->earlyBirdLastDate = $_GET['early-bird-date'];
        }
        $this->_suiteModel->setPriceName();

        return $this->_suiteModel;
    }

    /**
     * Index page
     * Request /
     */
    public function action_Index()
    {
        if (isset($_GET['auth'])) {

            $auth = $this->user;

            if ($auth->loginByHash($_GET['auth'])) {
                $this->request->redirect(BASE_URL);
                exit;
            }
        }
        $suite = $this->getSuiteModel();

        $formData = (isset($_SESSION['form'])) ? $_SESSION['form'] : [];
        $isAdmin = (isset($_SESSION['admin'])) ? 1 : 0;

        /*if (!(isset($_GET['preview']) && $_GET['preview'] == 'yes')) {
            if (!$isAdmin) {
                $this->view->render('dummy_page');
            }
        }*/

        $array = [];
        $titles = [];

        foreach ($formData as $formId => $data) {
            $array[$formId]['form_id'] = $formId;

            if (empty($array[$formId]['room_type'])
                || (empty($data['hotel_start_date']) && empty($data['hotel_end_date']))
            ) {
                $array[$formId]['room_type'] = $suite->defaultRoomType;
                $array[$formId]['occupancy'] = $suite->defaultOccupancy;
            }

            $array[$formId]['program_start_time'] =
                (!empty($data['program_start_time'])) ? $data['program_start_time'] : null;
            $array[$formId]['program_end_time'] =
                (!empty($data['program_end_time'])) ? $data['program_end_time'] : null;

            $startDate = null;
            $endDate = null;
            if (!empty($data['program_start_date']) && !empty($data['program_end_date'])
                && !empty($data['hotel_start_date']) && !empty($data['hotel_end_date'])
            ) {
                $day = 24 * 3600;
                $programDates = [];
                for (
                    $d = strtotime($data['program_start_date']);
                    $d <= strtotime($data['program_end_date']);
                    $d += $day
                ) {
                    $programDates[date($suite->dateFormat, $d)] = date($suite->dateFormat, $d);
                }
                asort($programDates);

                $hotelDates = [];
                for (
                    $d = strtotime($data['hotel_start_date']);
                    $d <= strtotime($data['hotel_end_date']);
                    $d += $day
                ) {
                    $hotelDates[date($suite->dateFormat, $d)] = date($suite->dateFormat, $d);
                }
                asort($hotelDates);

                $dateRange = [];
                foreach ($hotelDates as $key => $date) {
                    if (in_array($date, $programDates)) {
                        $dateRange[$key] = $date;
                    }
                }
                asort($dateRange);

                if (!empty($dateRange)) {
                    $startDate = reset($dateRange);
                    $endDate = end($dateRange);
                }
            }

            foreach ($data as $name => $value) {

                if (count($value) == 1 && is_array($value)) {
                    //$value = reset($value);
                }

                if ($name == 'room_type' && !is_array($value)) {
                    if (array_key_exists($value, $titles)) {
                        $titles[$value] += 1;
                    } else {
                        $titles[$value] = 0;
                    }
                    $array[$formId]['title'] = $value . ($titles[$value] > 0 ? ' ' . $titles[$value] : '');
                }
                $array[$formId][$name] = $value;
            }

            if (isset($array[$formId]['guest_type'])) {

                $totalPersons = 0;
                foreach ($array[$formId]['guest_type'] as $type => $count) {
                    if (in_array($type, $suite->getListOfCalculatedGuests())) {
                        $totalPersons += (!empty($count)) ? $count : 0;
                    }
                }
                if (empty($array[$formId]['room_type'])) {
                    $array[$formId]['room_type'] = 'No Rooming';
                }

                if ($array[$formId]['room_type'] != 'No Rooming') {
                } else {
                    $array[$formId]['occupancy'] = 'None';
                }
                $array[$formId]['description'] = $suite->getRoomDescription($array[$formId]['room_type']);

                $occupancyName = $suite->getOccupancyName(
                    $array[$formId]['guest_type'],
                    $array[$formId]['room_type'],
                    $array[$formId]['occupancy']
                );

                if (isset($array[$formId]['room_type'], $array[$formId]['occupancy'])) {

                    if (in_array($occupancyName, $suite->disabledOccupancies)) {
                        $beddingOptions = $suite->getBeddingByPersons(
                            $array[$formId]['room_type'], $occupancyName, $totalPersons
                        );
                    } else {
                        $beddingOptions = $suite->getBeddingByPersons(
                            $array[$formId]['room_type'], $array[$formId]['occupancy'], $totalPersons
                        );
                    }

                    if ($beddingOptions) {
                        $array[$formId]['beddingOptions'] = $beddingOptions;

                        $additionalBeddingOptions = [];
                        if ($totalPersons > 2) {
                            $additionalBeddingOptions['rollaway'] = 'Rollaway ($25)';
                        }
                        if (!empty($array[$formId]['guest_type']['Infant'])) {
                            $additionalBeddingOptions['crib'] = 'Crib ($25)';
                        }
                        $array[$formId]['additionalBeddingOptions'] = $additionalBeddingOptions;
                    }

                    $array[$formId]['occupancies'] = $suite->getOccupancyListByRoomType($array[$formId]['room_type']);
                }

                $requestData['form_id'] = $formId;
                $requestData['start_date'][$formId] = (isset($array[$formId]['start_date'])) ?
                    $array[$formId]['start_date'] : null;
                $requestData['end_date'][$formId] = (isset($array[$formId]['end_date'])) ?
                    $array[$formId]['end_date'] : null;
                $requestData['program_start_date'][$formId] = (isset($array[$formId]['program_start_date'])) ?
                    $array[$formId]['program_start_date'] : null;
                $requestData['program_end_date'][$formId] = (isset($array[$formId]['program_end_date'])) ?
                    $array[$formId]['program_end_date'] : null;
                $requestData['program_start_time'][$formId] = $array[$formId]['program_start_time'];
                $requestData['program_end_time'][$formId] = $array[$formId]['program_end_time'];
                $requestData['hotel_start_date'][$formId] = (isset($array[$formId]['hotel_start_date'])) ?
                    $array[$formId]['hotel_start_date'] : null;
                $requestData['hotel_end_date'][$formId] = (isset($array[$formId]['hotel_end_date'])) ?
                    $array[$formId]['hotel_end_date'] : null;
                $requestData['room_type'][$formId] = (isset($array[$formId]['room_type'])) ?
                    $array[$formId]['room_type'] : null;
                $requestData['occupancy'][$formId] = (isset($array[$formId]['occupancy'])) ?
                    $array[$formId]['occupancy'] : null;
                $requestData['staff'][$formId] = (isset($array[$formId]['staff'])) ?
                    $array[$formId]['staff'] : null;
                $requestData['guest_type'][$formId] = (isset($array[$formId]['guest_type'])) ?
                    $array[$formId]['guest_type'] : null;
                $requestData['promo'][$formId] = (isset($array[$formId]['promo'])) ?
                    $array[$formId]['promo'] : null;

                if (!empty($array[$formId]['staff'])) {
                    $staffData = $suite->getStaffData(
                        $array[$formId]['room_type'],
                        $array[$formId]['occupancy'],
                        $array[$formId]['guest_type'],
                        [$startDate, $endDate],
                        $array[$formId]['program_start_time'],
                        $array[$formId]['program_end_time']
                    );

                    if (array_key_exists('babysitter', $array[$formId]['staff'])
                        && array_key_exists('babysitter', $staffData)
                    ) {
                        foreach ($staffData['babysitter']['dates'] as $dayData) {
                            if (array_key_exists($dayData['name'], $array[$formId]['staff']['babysitter'])) {
                                $requestData['staff'][$formId]['babysitter'][$dayData['name']] = $dayData['price'];
                            }
                        }
                    }

                    $array[$formId]['staffData'] = $staffData;
                }

                $prices = $this->action_GetTotal(true, $requestData);
                $array[$formId]['staffPrices'] = $prices['staff_prices'];
                $array[$formId]['guestPrices'] = $prices['guest_prices'];
                $array[$formId]['tax'] = $prices['tax'];
                $array[$formId]['surcharge'] = $prices['surcharge'];

                if (!empty($data['total']) && $isAdmin) {
                    $array[$formId]['total'] = $data['total'];
                } else {
                    $array[$formId]['total'] = $prices['total'];
                }
            }

            if (!isset($array[$formId]['title'])) {
                $array[$formId]['title'] = 'No Rooming';
            }
        }
        $formData = $array;

        $this->view->render('index', [
            'formId'       => 0,
            'isAdmin'      => $isAdmin,
            'formData'     => $formData,
            'suite'        => $suite,
            'packageDates' => $suite->getProgramDates(),
            'basicDates'   => $suite->getBasicDates(),
        ]);
    }

    /**
     * Add New Room
     *
     * @return string
     */
    public function action_AddNewRoom()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');
            $return = ['result' => false, 'html' => ''];

            if (isset($_GET['form_id'])) {
                $formId = (int)$_GET['form_id'] + 1;
                $isAdmin = (isset($_SESSION['admin'])) ? 1 : 0;

                $this->saveToSession($_GET);

                $suite = $this->getSuiteModel();

                $return = [
                    'result' => true,
                    'formId' => $formId,
                    'html'   => preg_replace('/\s+/', ' ',
                        $this->view->renderPartial('_form', compact('formId', 'suite', 'isAdmin'), false)),
                ];
            }
            echo json_encode($return);
            die();
        }

        return '';
    }

    public function action_RemoveRoom()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');
            $return = ['result' => false];

            if (isset($_GET['form_id'])) {
                $isRemoved = false;
                $formId = (int)$_GET['form_id'];
                $return['form_id'] = $formId;

                if ($formId != 0) {
                    if (isset($_SESSION['form'], $_SESSION['form'][$formId])) {
                        unset($_SESSION['form'][$formId]);
                    }
                    if (isset(
                        $_SESSION['roomingInfoSentFromPage1'],
                        $_SESSION['roomingInfoSentFromPage1']['rooms']
                    )) {
                        $sessionData = $_SESSION['roomingInfoSentFromPage1']['rooms'];
                        $return['form_id'] = key($sessionData);
                    }
                }
                $return['result'] = $isRemoved;
            }
            echo json_encode($return);
            die();
        }

        return '';
    }

    public function action_UpdateRoom()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');
            $return = ['result' => false];

            if (isset($_GET['form_id'])) {
                $result = $this->saveToSession($_GET);
                $return = ['result' => !empty($result)];
            }
            echo json_encode($return);
            die();
        }

        return '';
    }

    /**
     * Get partial html block Room Types and Rates
     *
     * @return string
     */
    public function action_GetRoomTypeArea()
    {
        if ($this->request->isAjax()) {

            header('Content-type: application/json');
            $return = ['result' => false, 'html' => ''];

            if (isset($_GET['form_id'])) {
                $formId = (int)abs($_GET['form_id']);

                $suite = $this->getSuiteModel();

                $return = [
                    'result'  => true,
                    'form_id' => $formId,
                    'html'    => preg_replace('/\s+/', ' ',
                        $this->view->renderPartial('_room_type', compact('formId', 'suite'), false)
                    ),
                ];
            }
            echo json_encode($return);
            die();
        }

        return '';
    }

    /**
     * Get available occupancies by selected room type
     *
     * @return string
     */
    public function action_GetOccupancies()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');
            $return = [
                'occupancy'    => '',
                'description'  => '',
                'range'        => [],
                'guest_prices' => [],
                'total'        => 0,
                'tax'          => 0,
                'surcharge'    => 0,
            ];

            if (isset($_GET['form_id'], $_GET['room_type'], $_GET['room_type'][$_GET['form_id']])) {

                $isAdmin = (isset($_SESSION['admin'])) ? true : false;

                $formId = (int)abs($_GET['form_id']);
                $roomTypeName = $_GET['room_type'][$formId];
                $guestType = isset($_GET['guest_type'], $_GET['guest_type'][$formId]) ?
                    $_GET['guest_type'][$formId] : [];

                $suite = $this->getSuiteModel();
                $occupancies = $suite->getOccupancyListByRoomType($roomTypeName);
                $description = $suite->getRoomDescription($roomTypeName);

                $totalPersons = 0;
                foreach ($guestType as $type => $count) {
                    if (in_array($type, [Model_Suite::GUEST_TYPE_TEEN, Model_Suite::GUEST_TYPE_ADULT])) {
                        $totalPersons += $count;
                    }
                }
                $range = [];
                if (!$occupancies && $roomTypeName == 'No Rooming') {
                    $range = $suite->getGuestRange($roomTypeName, $guestType, 'None');

                    if ($isAdmin) {
                        foreach (array_keys($suite->getGuestTypeList()) as $type) {
                            $min = 0;
                            $max = 100;

                            $range[$type] = [$min, $max];
                        }
                    }
                }

                $setDefaultState = true;
                $guestTypeDefault = [];
                foreach ($range as $type => $typeData) {
                    $guestTypeDefault[$type] = reset($typeData);

                    if (isset($guestType[$type]) && $guestType[$type] != 0) {
                        if ($guestType[$type] != $guestTypeDefault[$type]) {
                            $setDefaultState = false;
                            break;
                        }
                    }
                }

                $requestData = $_GET;
                if ($setDefaultState) {
                    $requestData['guest_type'][$formId] = $guestTypeDefault;
                }

                $return = [
                    'form_id'     => $formId,
                    'occupancy'   => preg_replace('/\s+/', ' ',
                        $this->view->renderPartial('_occupancies', compact('formId', 'occupancies'), false)
                    ),
                    'description' => preg_replace('/\s+/', ' ',
                        $this->view->renderPartial(
                            '_room_description', compact('formId', 'description'), false
                        )),
                    'range'       => $range,
                ];

                $prices = $this->action_GetTotal(true, $requestData);
                if (!empty($prices)) {
                    $return['total'] = $prices['total'];
                    $return['guest_prices'] = $prices['guest_prices'];
                    $return['tax'] = $prices['tax'];
                    $return['surcharge'] = $prices['surcharge'];
                }
            }

            echo json_encode($return);
            die();
        }

        return '';
    }

    public function action_getGuestRange()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');
        }

        $return = '';

        if (isset($_GET['form_id'])) {

            $return = [
                'total'                      => 0,
                'range'                      => [],
                'html'                       => '',
                'guest_prices'               => [],
                'tax'                        => 0,
                'surcharge'                  => 0,
                'early_bird_discount'        => 0,
                'occupancy_html'             => '',
                'occupancy_description_html' => '',
                'room_type'                  => '',
            ];

            $isAdmin = (isset($_SESSION['admin'])) ? true : false;
            $suite = $this->getSuiteModel();
            $formId = (int)abs($_GET['form_id']);

            $data = [];
            foreach ($_GET as $name => $nameData) {
                if (!empty($nameData[$formId])) {
                    $data[$name] = $nameData[$formId];
                }
            }

            if (!isset($data['program_start_time'])) {
                $data['program_start_time'] = null;
            }
            if (!isset($data['program_end_time'])) {
                $data['program_end_time'] = null;
            }

            $occupancy = (isset($_GET['occupancy'], $_GET['occupancy'][$formId])) ?
                $_GET['occupancy'][$formId] : $suite->defaultOccupancy;
            $roomTypeName = (isset($_GET['room_type'], $_GET['room_type'][$formId])) ?
                $_GET['room_type'][$formId] : $suite->defaultRoomType;
            $guestType = isset($_GET['guest_type'], $_GET['guest_type'][$formId]) ?
                $_GET['guest_type'][$formId] : [];
            $startDate = null;
            $endDate = null;
            $programStartTime = (isset($_GET['program_start_time'], $_GET['program_start_time'][$formId])) ?
                $_GET['program_start_time'][$formId] : null;
            $programStartDate = (isset($_GET['program_start_date'], $_GET['program_start_date'][$formId])) ?
                $_GET['program_start_date'][$formId] : null;
            $programEndDate = (isset($_GET['program_end_date'], $_GET['program_end_date'][$formId])) ?
                $_GET['program_end_date'][$formId] : null;
            $programEndTime = (isset($_GET['program_end_time'], $_GET['program_end_time'][$formId])) ?
                $_GET['program_end_time'][$formId] : null;

            $hotelAvailability = (isset($_GET['hotel_availability'], $_GET['hotel_availability'][$formId])) ?
                $_GET['hotel_availability'][$formId] : 0;


            $hotelStartDate = null;
            $hotelEndDate = null;
            if ($hotelAvailability != 0) {
                $hotelStartDate = (isset($_GET['hotel_start_date'], $_GET['hotel_start_date'][$formId])) ?
                    $_GET['hotel_start_date'][$formId] : null;
                $hotelEndDate = (isset($_GET['hotel_end_date'], $_GET['hotel_end_date'][$formId])) ?
                    $_GET['hotel_end_date'][$formId] : null;
            }

            $data['staff'] = (isset($_GET['staff'], $_GET['staff'][$formId])) ? $_GET['staff'][$formId] : [];

            $babysitter = $data['staff']['babysitter'];

            if (isset($babysitter['total'])) {
                unset($babysitter['total']);
            }

            if (empty($data['staff']['group_babysitting'])) {
                $data['staff']['babysitter'] = [];
            }

            if (!empty($programStartDate) && !empty($programEndDate)
                && !empty($hotelStartDate) && !empty($hotelEndDate)
            ) {
                $roomTypes = $suite->getRoomTypeList();

                if ($roomTypeName == $suite->defaultRoomType && count($roomTypes) == 1) {
                    $roomTypeName = key($roomTypes);

                    $occupancies = $suite->getOccupancyListByRoomType($roomTypeName);
                    $description = $suite->getRoomDescription($roomTypeName);

                    $return['occupancy_html'] = preg_replace('/\s+/', ' ',
                        $this->view->renderPartial('_occupancies', compact('formId', 'occupancies'), false)
                    );
                    $return['occupancy_description_html'] = preg_replace('/\s+/', ' ',
                        $this->view->renderPartial('_room_description', compact('formId', 'description'), false)
                    );
                    $return['room_type'] = $roomTypeName;
                }
            }

            if (($hotelAvailability && !empty($hotelStartDate) && !empty($hotelEndDate))
                || (!$hotelAvailability && empty($hotelStartDate) && empty($hotelEndDate))
            ) {
                $return['range'] = $suite->getGuestRange($roomTypeName, $guestType, $occupancy);
                if ($isAdmin) {
                    foreach (array_keys($suite->getGuestTypeList()) as $type) {
                        $min = 0;
                        $max = 100;

                        $return['range'][$type] = [$min, $max];
                    }
                }

                $setDefaultState = true;
                $guestTypeDefault = [];
                foreach ($return['range'] as $type => $typeData) {
                    $guestTypeDefault[$type] = reset($typeData);

                    if (isset($guestType[$type]) && $guestType[$type] != 0) {
                        if ($guestType[$type] != $guestTypeDefault[$type]) {
                            $setDefaultState = false;
                            break;
                        }
                    }
                }

                $requestData = $_GET;
                if ($setDefaultState) {
                    $guestType = $guestTypeDefault;
                    $requestData['guest_type'][$formId] = $guestTypeDefault;
                }

                $staffData = $suite->getStaffData(
                    $roomTypeName,
                    $occupancy,
                    $guestType,
                    [$programStartDate, $programEndDate],
                    $programStartTime,
                    $programEndTime
                );

                if (empty($data['staff'])) {
                    foreach ($staffData as $staffName => $staffItem) {
                        if (!empty($staffItem['dates'])) {
                            foreach ($staffItem['dates'] as $date => $dayData) {
                                $data['staff'][$staffName][$dayData['name']] = 0;
                            }
                        }
                    }
                }

                $requestData['staff'][$formId] = $data['staff'];

                $prices = $this->action_GetTotal(true, $requestData);
                $return['total'] = $prices['total'];
                $return['promo_price'] = $prices['promo_price'];
                if ($prices['promo_price']) {
                    $return['promo_message'] = 'discount applied';
                }
                $return['promo_error'] = $prices['promo_error'];
                $return['guest_prices'] = $prices['guest_prices'];
                $return['tax'] = $prices['tax'];
                $return['surcharge'] = $prices['surcharge'];
                $staffPrices = $prices['staff_prices'];
                $return['early_bird_discount'] = $prices['early_bird_discount'];

                $return['staff_html'] = '';
                if (!empty($staffData) && !empty($staffPrices)) {
                    $return['staff_html'] = preg_replace('/\s+/', ' ',
                        $this->view->renderPartial('_staff', compact('formId', 'staffPrices', 'data', 'staffData'), false)
                    );
                }

                if (!$isAdmin) {
                    $message = $suite->getGuestValidationMessage($roomTypeName, $occupancy, $guestType);
                    if (!empty($message)) {
                        $type = key($message);
                        $message = reset($message);
                        if ($message != '') {
                            $return['guest_' . $type . '_validation_message'] = preg_replace('/\s+/', ' ',
                                $this->view->renderPartial('_message_info', compact('message'), false)
                            );
                        }
                    }
                }

                $return['html'] = $this->action_GetBeddingItems(true, $requestData);
            }

            // program offset
            $data['program_start_offset'] = $suite->getProgramOffset(
                'start', $programStartDate, $programEndDate, $data['program_start_time'], $data['program_end_time'],
                $data['room_type'], $data['occupancy']
            );
            $data['program_end_offset'] = $suite->getProgramOffset(
                'end', $programStartDate, $programEndDate, $data['program_start_time'], $data['program_end_time'],
                $data['room_type'], $data['occupancy']
            );
            $data['program_total_offset'] = $suite->getProgramOffset(
                'total', $programStartDate, $programEndDate, $data['program_start_time'], $data['program_end_time'],
                $data['room_type'], $data['occupancy']
            );

            /*if ($isAdmin && $programStartDate == '08/18/2016' && $programEndDate == '08/18/2016') {
                $suite->programStartTime = '08:30pm';
                $suite->programEndTime = '10:00pm';
                $suite->programStartTimes = [
                    '08:30pm',
                    '09:00am',
                    '03:00pm'
                ];
                $suite->programEndTimes = [
                    '03:00pm',
                    '10:00pm',
                    '11:00pm'
                ];
                $data['program_start_offset'] = 0;
                $data['program_end_offset'] = 0;
                $data['program_total_offset'] = 0;
            }*/

            $return['program_times_html'] = '';
            if ($programStartDate !== null && $programEndDate !== null) {
                $return['program_times_html'] = preg_replace('/\s+/', ' ',
                    $this->view->renderPartial('_program_times', compact('formId', 'data', 'suite', 'isAdmin'), false)
                );
            }

            $return['form_id'] = $formId;
            $return['occupancy'] = $occupancy;
        }
        if ($this->request->isAjax()) {
            echo json_encode($return);
            die();
        }

        return $return;
    }

    public function action_GetBeddingItems($return = false, $requestData = [])
    {
        if ($return !== true && $this->request->isAjax()) {
            header('Content-type: application/json');
        }

        if (empty($requestData)) {
            $requestData = $_GET;
        }

        if (isset($requestData['form_id'], $requestData['room_type'],
            $requestData['room_type'][$requestData['form_id']],
            $requestData['guest_type'], $requestData['guest_type'][$requestData['form_id']])
        ) {

            $formId = (int)abs($requestData['form_id']);
            $occupancyName = isset($requestData['occupancy'], $requestData['occupancy'][$formId]) ?
                $requestData['occupancy'][$formId] : null;
            $roomTypeName = $requestData['room_type'][$formId];
            $guestType = $requestData['guest_type'][$formId];
            $additionalBeddingOptions = isset($requestData['additional_bedding'],
                $requestData['additional_bedding'][$formId]) ? $requestData['additional_bedding'][$formId] : [];
            $suite = $this->getSuiteModel();

            $totalPersons = 0;
            foreach ($guestType as $type => $count) {
                if (in_array($type, $suite->getListOfCalculatedGuests())) {
                    $totalPersons += $count;
                }
            }
            $occupancy = $suite->getOccupancyName($guestType, $roomTypeName, $occupancyName);
            if (in_array($occupancy, $suite->disabledOccupancies)) {
                $beddingOptions = $suite->getBeddingByPersons($roomTypeName, $occupancy, $totalPersons);
            } else {
                $beddingOptions = $suite->getBeddingByPersons($roomTypeName, $occupancyName, $totalPersons);
            }

            $beddingHtml = '';
            if (!empty($beddingOptions) && empty($additionalBeddingOptions)) {

                $additionalBeddingOptions = [];
                if ($totalPersons > 2) {
                    $additionalBeddingOptions['rollaway'] = 'Rollaway ($25)';
                }
                if (!empty($guestType['Infant']) || !empty($guestType['Toddler'])) {
                    $additionalBeddingOptions['crib'] = 'Crib ($25)';
                }

                $beddingHtml = preg_replace('/\s+/', ' ',
                    $this->view->renderPartial('_bedding', compact('beddingOptions', 'formId', 'additionalBeddingOptions'), false));
            }

            if ($return !== true && $this->request->isAjax()) {
                $return = ['form_id' => null, 'html' => ''];

                if ($totalPersons != 0) {
                    $return = [
                        'form_id' => $formId,
                        'html'    => $beddingHtml,
                    ];
                }

                echo json_encode($return);
                die();
            } else {
                return $beddingHtml;
            }
        }

        return '';
    }

    /**
     * Get total price by selected options
     *
     * @param bool $return
     * @param array $requestData
     *
     * @return array()|string
     */
    public function action_ValidateForm($return = false, $requestData = [])
    {
        if ($return !== true && $this->request->isAjax()) {
            header('Content-type: application/json');
        }

        $isAdmin = (isset($_SESSION['admin'])) ? true : false;
        $result = ['errors' => []];
        $suite = $this->getSuiteModel();


        if (empty($requestData)) {
            $requestData = $_GET;
        }

        $formId = $requestData['form_id'];

        if (!empty($requestData['room_type'])
            && in_array($requestData['room_type'][$formId], $suite->disabledTypes)
        ) {
            $requestData['occupancy'][$formId] = 'None';
        }

        if (!$isAdmin) {
            // rules for room type
            /*if (empty($requestData['room_type'][$formId])) {
                $result['errors']['room_type'] = 'Room Type should be selected';
            }*/
            // rules for occupancy
            if (!empty($requestData['room_type'][$formId])
                && !in_array($requestData['room_type'][$formId], $suite->disabledTypes)
                && empty($requestData['occupancy'][$formId])
            ) {
                $result['errors']['occupancy'] = 'Occupancy should be selected';
            }

            // rules for selected guest
            if (!empty($requestData['room_type'][$formId])
                && !empty($requestData['guest_type'][$formId])
            ) {
                $min = $suite->getMinPersonCount(
                    $requestData['room_type'][$formId], $requestData['occupancy'][$formId]
                );

                $total = array_sum($requestData['guest_type'][$formId]);

                if ($min > $total) {
                    $result['errors']['guest_type'] = $requestData['occupancy'][$formId] . ' should include ' . $min
                                                      . ' guest(s) minimum';
                }
            }

            if (!empty($requestData['start_date'][$formId]) && !empty($requestData['end_date'][$formId])) {

                $dateFormat = 'Y-m-d';
                $programDates = $suite->getProgramDates();
                $programStartDate = new DateTime(reset($programDates));
                $programStartDate = $programStartDate->format($suite->dateFormat);
                $programEndDate = new DateTime(end($programDates));
                $programEndDate = $programEndDate->format($suite->dateFormat);

                foreach ($programDates as $k => $date) {
                    $programDates[$k] = new DateTime($date);
                    $programDates[$k] = $programDates[$k]->format($dateFormat);
                }

                $startDate = new DateTime($requestData['start_date'][$formId]);
                $startDate = $startDate->format($dateFormat);

                $endDate = new DateTime($requestData['end_date'][$formId]);
                $endDate = $endDate->format($dateFormat);

                if (!in_array($startDate, $programDates) && !in_array($endDate, $programDates)) {
                    $result['errors']['start_date'] =
                        'The selection should include a dates from ' . $programStartDate . ' to ' . $programEndDate;
                }
            }

            if (!empty($requestData['guest_type'][$formId])
                && !empty($requestData['guest_type'][$formId][Model_Suite::GUEST_TYPE_INFANT])
            ) {
                $babysitter = !empty($requestData['staff'][$formId]) ?
                    $requestData['staff'][$formId] : [];

                if (isset($babysitter['babysitter']['total'])) {
                    unset($babysitter['babysitter']['total']);
                }

                if (!isset($babysitter['group_babysitting'])) {
                    $result['errors']['staff'] = 'This field is required';
                } else if ($babysitter['group_babysitting'] == 1 && empty($babysitter['babysitter'])) {
                    $result['errors']['staff'] = 'Please, select one of more options';
                }

                if (!isset($babysitter['private_babysitting'])) {
                    $result['errors']['staff'] = 'This field is required';
                }
            }

            if (!empty($requestData['hotel_availability'][$formId]) && empty($requestData['bedding'][$formId])) {
                $result['errors']['bedding'] = 'This field is required';
            }
        }

        if (empty($result['errors'])) {
            $this->saveToSession();
        }

        if ($return !== true && $this->request->isAjax()) {
            echo json_encode($result);
            die();
        } else {
            return $result;
        }
    }

    /**
     * Get total price by selected options
     *
     * @param bool $return
     * @param array $requestData
     *
     * @return string|int
     */
    public function action_GetTotal($return = false, $requestData = [])
    {
        if ($return !== true && $this->request->isAjax()) {
            header('Content-type: application/json');
        }

        $suite = $this->getSuiteModel();
        $total = 0;
        $hotelTaxRate = 0;
        $programTaxRate = 0;
        $guestPrices = [];
        $guestProgramTax = [];
        $staffPrices = [];
        $guestData = [];
        $tax = 0;
        $guestTotal = 0;
        $programOffsetPrice = 0;
        $earlyBirdDiscount = 0;

        $isAdmin = (isset($_SESSION['admin'])) ? true : false;

        $result = [
            'total'        => $total,
            'promo_price'  => 0,
            'guest_prices' => $guestPrices,
            'staff_prices' => $staffPrices,
            'tax'          => $tax,
        ];

        if (empty($requestData)) {
            $requestData = $_GET;
        }

        if (isset($requestData['form_id'], $requestData['guest_type'], $requestData['guest_type'][$requestData['form_id']])
            && (isset($requestData['start_date'], $requestData['start_date'][$requestData['form_id']],
                    $requestData['end_date'], $requestData['end_date'][$requestData['form_id']])
                || isset($requestData['program_start_date'], $requestData['program_start_date'][$requestData['form_id']],
                    $requestData['program_end_date'], $requestData['program_end_date'][$requestData['form_id']]))
        ) {
            $formId = (int)abs($requestData['form_id']);
            $data = [
                'start_date'           => (isset($requestData['start_date'], $requestData['start_date'][$formId])) ?
                    $requestData['start_date'][$formId] : null,
                'end_date'             => (isset($requestData['end_date'], $requestData['end_date'][$formId])) ?
                    $requestData['end_date'][$formId] : null,
                'program_start_date'   => (isset($requestData['program_start_date'], $requestData['program_start_date'][$formId])) ?
                    $requestData['program_start_date'][$formId] : null,
                'program_end_date'     => (isset($requestData['program_end_date'], $requestData['program_end_date'][$formId])) ?
                    $requestData['program_end_date'][$formId] : null,
                'program_start_time'   => (isset($requestData['program_start_time'], $requestData['program_start_time'][$formId])) ?
                    $requestData['program_start_time'][$formId] : null,
                'program_end_time'     => (isset($requestData['program_end_time'], $requestData['program_end_time'][$formId])) ?
                    $requestData['program_end_time'][$formId] : null,
                'program_start_offset' => (isset($requestData['program_start_offset'], $requestData['program_start_offset'][$formId])) ?
                    $requestData['program_start_offset'][$formId] : null,
                'program_end_offset'   => (isset($requestData['program_end_offset'], $requestData['program_end_offset'][$formId])) ?
                    $requestData['program_end_offset'][$formId] : null,
                'program_total_offset' => (isset($requestData['program_total_offset'], $requestData['program_total_offset'][$formId])) ?
                    $requestData['program_total_offset'][$formId] : null,
                'hotel_start_date'     => (isset($requestData['hotel_start_date'], $requestData['hotel_start_date'][$formId])) ?
                    $requestData['hotel_start_date'][$formId] : null,
                'hotel_end_date'       => (isset($requestData['hotel_end_date'], $requestData['hotel_end_date'][$formId])) ?
                    $requestData['hotel_end_date'][$formId] : null,
                'room_type'            => (isset($requestData['room_type'], $requestData['room_type'][$formId])) ?
                    $requestData['room_type'][$formId] : $suite->defaultRoomType,
                'occupancy'            => (isset($requestData['occupancy'], $requestData['occupancy'][$formId])) ?
                    $requestData['occupancy'][$formId] : $suite->defaultOccupancy,
                'staff'                => (isset($requestData['staff'], $requestData['staff'][$formId])) ?
                    $requestData['staff'][$formId] : [],
                'guest_type'           => $requestData['guest_type'][$formId],
                'promo'                => (isset($requestData['promo'], $requestData['promo'][$formId])) ?
                    $requestData['promo'][$formId] : '',
                'additional_bedding'   => (isset($requestData['additional_bedding'], $requestData['additional_bedding'][$formId])) ?
                    $requestData['additional_bedding'][$formId] : [],
                'hotel_availability'   => (isset($requestData['hotel_availability'], $requestData['hotel_availability'][$formId])) ?
                    $requestData['hotel_availability'][$formId] : 0,
            ];
            $guestTypes = $data['guest_type'];

            $programDates = $suite->getProgramDates();
            $startProgramDate = reset($programDates);
            $endProgramDate = end($programDates);

            $startProgramDate = date('m/d/Y', strtotime($startProgramDate));
            $endProgramDate = date('m/d/Y', strtotime($endProgramDate));

            if (empty($data['program_start_date']) && empty($data['program_end_date'])
                && !empty($data['hotel_start_date']) && !empty($data['hotel_end_date'])
            ) {
                if (strtotime($startProgramDate) < strtotime($data['hotel_start_date'])) {
                    $data['program_start_date'] = $data['hotel_start_date'];
                } else {
                    $data['program_start_date'] = $startProgramDate;
                }
                if (strtotime($endProgramDate) > strtotime($data['hotel_end_date'])) {
                    $data['program_end_date'] = $data['hotel_end_date'];
                } else {
                    $data['program_end_date'] = $endProgramDate;
                }
            }

            // promotion discount
            $promotion = new Model_RetreatPromotions();
            $promo = $promotion->getPromoByCode($data['promo']);

            $retreatEvent = new Model_RetreatEvents();
            $event = $retreatEvent->getCurrentEvent();


            $promoPrice = 0;
            $promoMessage = '';
            $promoError = '';
            if ($promo.length == 0 && $data['promo'] !="" ){$promoError = 'discount code not found';}
            foreach($promo as $key=>$p){

                $date2 = new DateTime($data['program_end_date'].' '.$data['program_end_time']);
                $date1 = new DateTime($data['program_start_date'].' '.$data['program_start_time']);

                $dayCount =  $date1->diff($date2);

                          
                if ( new DateTime($p['expiry']) >= new DateTime('NOW') 
                && ($p['count'] < $p['limit'] || $p['limit'] == 0) && $p['event_id'] == $event
                && ($p['day_limit'] <= $dayCount->d+1 || $p['day_limit'] == 0 )
                ){
                    $promoPrice = $p['amount'];
                    $promoMessage = 'discount applied';
                    $promoError = '';
                    break;

                }else{
                    if(new DateTime($p['expiry']) < new DateTime('NOW')){
                        $promoError = 'Discount expired.';
                    }
                    if($p['count'] < $p['limit'] && $p['limit'] != 0){
                        $promoError = 'Discount limit reached.';
                    }
                    if($p['event_id'] != $event){
                        $promoError = 'Invalid discount.';
                    }
                    if($p['day_limit'] > $dayCount->d+1 && $p['day_limit'] != 0){
                        $promoError = 'Discount is only available to registrations of '.$p['day_limit'].' days and more.';
                    }
                }
                
            }

            foreach ($guestTypes as $type => $count) {
                if (in_array($type, $suite->getListOfCalculatedGuests())) {
                    $guestTotal += $count;
                }
                if ($type == 'Adult') {
                    $promoPrice = $promoPrice * $count;
                }
            }

            if ($guestTotal) {
                // program offset
                foreach ($guestTypes as $type => $count) {
                    $guestOffsetPrice = $suite->getProgramOffset(
                        'total', $data['program_start_date'], $data['program_end_date'], $data['program_start_time'],
                        $data['program_end_time'], $data['room_type'], $data['occupancy'], [$type => $count]
                    );
                    if (!empty($guestPrices)) {
                        $programOffsetPrice = array_sum($guestPrices);
                    }
                    if ($programOffsetPrice > $suite->programTotalOffset) {
                        $programOffsetPrice = $suite->programTotalOffset;
                    }
                    $guestPrices[$type] = $guestOffsetPrice;
                }
            }

            $definedOccupancyName = $suite->getOccupancyName($guestTypes, $data['room_type'], $data['occupancy']);

            // find selected occupancy array data
            if (empty($data['occupancy'])) {
                $data['occupancy'] = $definedOccupancyName;
            }

            if (!empty($guestTypes)) {

                $programStartDate = $data['start_date'];
                $programEndDate = $data['end_date'];
                $hotelStartDate = null;
                $hotelEndDate = null;

                if ($data['start_date'] != null && $data['end_date'] != null) {
                    $hotelStartDate = $programStartDate;
                    $hotelEndDate = $programEndDate;
                }
                if ($data['program_start_date'] != null && $data['program_end_date'] != null) {
                    $programStartDate = $data['program_start_date'];
                    $programEndDate = $data['program_end_date'];
                }
                if ($data['hotel_start_date'] != null && $data['hotel_end_date'] != null) {
                    $hotelStartDate = $data['hotel_start_date'];
                    $hotelEndDate = $data['hotel_end_date'];
                }

                // tax rate
                $hotelTaxRate = $suite->getHotelTax($data['room_type'], $data['occupancy']);

                /*if ($isAdmin) {
                    $minGuestCount = $suite->getMinPersonCount($data['room_type'], $data['occupancy']);
                    if ($minGuestCount == 2 && $guestTotal == 1) {
                        $hotelTaxRate = $hotelTaxRate / 2;
                    }
                }*/

                $programTaxRate = $suite->getProgramTax();

                // additional program tax for an additional half day
                $extraProgramTax = 0;
                if (!empty($data['program_start_time']) && !empty($data['program_end_time'])
                    && $programStartDate != $programEndDate
                ) {
                    $pStartTime = date_create($data['program_start_time']);
                    $pEndTime = date_create($data['program_end_time']);
                    $interval = date_diff($pStartTime, $pEndTime);
                    $interval = $interval->format('%h');

                    if ($interval >= 6) {
                        $extraProgramTax = $suite->getProgramTax();
                    }
                }

                $hotelTaxableGuestCount = 0;
                $programTaxableGuestCount = 0;
                foreach ($guestTypes as $type => $count) {
                    if (in_array($type, $suite->getListOfCalculatedGuests())
                        && !in_array($type, $suite->nonTaxableGuestTypes)
                    ) {
                        $hotelTaxableGuestCount += $count;
                        $programTaxableGuestCount += $count;

                        $guestProgramTax[$type] = $programTaxRate;
                    }
                }

                if (((!empty($guestTypes[Model_Suite::GUEST_TYPE_CHILD])
                      && $guestTypes[Model_Suite::GUEST_TYPE_CHILD] >= 1)
                     || (!empty($guestTypes[Model_Suite::GUEST_TYPE_TODDLER])
                         && $guestTypes[Model_Suite::GUEST_TYPE_TODDLER] >= 1))
                    && $guestTypes[Model_Suite::GUEST_TYPE_ADULT] == 1
                    && $guestTypes[Model_Suite::GUEST_TYPE_TEEN] == 0
                ) {
                    $hotelTaxableGuestCount = $hotelTaxableGuestCount + 1;
                }

                $hotelTax = 0;
                if (!in_array($data['room_type'], $suite->disabledTypes)) {
                    $hStartDate = strtotime($hotelStartDate);
                    $hEndDate = strtotime($hotelEndDate);
                    $countHotelDays = 0;
                    $day = 24 * 3600;
                    for ($d = $hStartDate; $d < $hEndDate; $d += $day) {
                        $countHotelDays++;
                    }

                    if ($hotelTaxableGuestCount > 2) {
                        $hotelTax = $countHotelDays * 2 * $hotelTaxRate;
                    } else {
                        $hotelTax = $countHotelDays * $hotelTaxableGuestCount * $hotelTaxRate;
                    }
                }
                $pStartDate = strtotime($programStartDate);
                $pEndDate = strtotime($programEndDate);
                $countProgramDays = 0;
                $day = 24 * 3600;

                for ($d = $pStartDate; $d < $pEndDate; $d += $day) {
                    $countProgramDays++;
                }
                if ($countProgramDays == 0) {
                    $countProgramDays = 1;
                }

                $programTax = ($countProgramDays * $programTaxRate + $extraProgramTax) * $programTaxableGuestCount;
                $tax = $hotelTax + $programTax;
                // early bird discount
                $earlyBirdDiscount = $suite->getEarlyBirdDiscount($data['guest_type'], $programStartDate, $programEndDate);
            }

            // defining occupancy by guest count and dividing guests by occupancies
            if ($definedOccupancyName != $data['occupancy']) {

                if (in_array($definedOccupancyName, $suite->disabledOccupancies)) {
                    $maxSelectedPersonCount = $suite->getMaxPersonCount(
                        $data['room_type'], $data['occupancy']
                    );
                    $selectedPersonCount = 0;
                    $counted = 0;
                    foreach ($guestTypes as $type => $count) {
                        if (in_array($type, $suite->getListOfCalculatedGuests())) {

                            $selectedPersonCount += $count;

                            if ($count > 0 && $selectedPersonCount > $maxSelectedPersonCount) {
                                $count = $selectedPersonCount - $maxSelectedPersonCount - $counted;
                                $guestData[$type][$definedOccupancyName] = $count;
                                $counted += $count;
                            }
                        }
                    }
                } else {
                    $data['occupancy'] = $definedOccupancyName;
                }
            }

            if ($data['program_start_date'] != null && $data['program_end_date'] != null) {
                $dStart = $data['program_start_date'];
                $dEnd = $data['program_end_date'];
            } else {
                $programDates = $suite->getProgramDates();

                foreach ($programDates as $key => $date) {
                    if (!($date >= $data['start_date'] && $date < $data['end_date'])) {
                        unset($programDates[$key]);
                    }
                }
                $dStart = reset($programDates) . ' ' . $data['program_start_time'];
                $dEnd = end($programDates) . ' ' . $data['program_end_time'];
            }

            $additionalHotelCharge = null;

            $detailedInfo = [];
            $detailedInfo2 = [];
            // get total price and get price for each guest separately
            foreach (array_keys($suite->getGuestTypeList()) as $type) {

                $guestPrice = 0;
                $staffPrice = 0;
                $price = 0;

                if (array_key_exists($type, $guestTypes) && $guestTypes[$type] > 0) {
                    $data['guest_types'] = $guestTypes;
                    $data['guest_type'] = $type;

                    if (array_key_exists($type, $guestData)) {

                        foreach ($guestData[$type] as $occupancyName => $count) {
                            $gData = $data;
                            $gData['occupancy'] = $occupancyName;

                            $guestPrice += $suite->getTotal($gData) * $count;

                            if (!empty($data['staff']) && is_array($data['staff'])) {
                                foreach ($data['staff'] as $staffName => $staffData) {

                                    if (is_array($staffData)) {
                                        foreach ($staffData as $name => $staffNameData) {
                                            if ($staffNameData) {
                                                $price += $suite->getStaffPrice(
                                                    $data['room_type'],
                                                    $data['occupancy'],
                                                    $staffName,
                                                    [$type => $count],
                                                    [$dStart, $dEnd],
                                                    $name,
                                                    $data['program_start_time'],
                                                    $data['program_end_time']
                                                );
                                            }
                                        }
                                    } else if ($staffData) {
                                        $price += $suite->getStaffPrice(
                                            $data['room_type'],
                                            $data['occupancy'],
                                            $staffName,
                                            [$type => $count],
                                            [$dStart, $dEnd],
                                            null,
                                            $data['program_start_time'],
                                            $data['program_end_time']
                                        );
                                    }

                                    if (array_key_exists($staffName, $staffPrices)) {
                                        $staffPrices[$staffName] += $price;
                                    } else {
                                        $staffPrices[$staffName] = $price;
                                    }
                                    $staffPrice += $price;
                                }
                            }
                            $guestTypes[$type] = $guestTypes[$type] - $count;

                            if (isset($requestData['detailed'])) {
                                $detailedInfo2[$type][] =
                                    array_merge(['occupancy' => $gData['occupancy']], $suite->getTotal($gData, false));
                            }
                        }
                    }

                    $count = (int)$guestTypes[$type];
                    $guestPrice += $suite->getTotal($data) * $count;

                    if (!empty($data['staff']) && is_array($data['staff'])) {
                        foreach ($data['staff'] as $staffName => $staffData) {
                            $price = 0;
                            if (is_array($staffData)) {
                                foreach ($staffData as $name => $staffNameData) {
                                    if ($staffNameData) {
                                        $price += $suite->getStaffPrice(
                                            $data['room_type'],
                                            $data['occupancy'],
                                            $staffName,
                                            [$type => $count],
                                            [$dStart, $dEnd],
                                            $name,
                                            $data['program_start_time'],
                                            $data['program_end_time']
                                        );
                                    }
                                }
                            } else if ($staffData) {
                                $price += $suite->getStaffPrice(
                                    $data['room_type'],
                                    $data['occupancy'],
                                    $staffName,
                                    [$type => $count],
                                    [$dStart, $dEnd],
                                    null,
                                    $data['program_start_time'],
                                    $data['program_end_time']
                                );
                            }

                            if (array_key_exists($staffName, $staffPrices)) {
                                $staffPrices[$staffName] += $price;
                            } else {
                                $staffPrices[$staffName] = $price;
                            }
                            $staffPrice += $price;
                        }
                    }

                    if (isset($requestData['detailed']) && $count > 0) {
                        $detailedInfo[$type] = $suite->getTotal($data, false);
                    }

                    if (isset($requestData['detailed']) && $count > 0) {
                        $detailedInfo2[$type][] =
                            array_merge(['occupancy' => $data['occupancy']], $suite->getTotal($data, false));
                    }

                    if (isset($guestPrices[$type])) {
                        if ($guestPrice) {
                            $guestPrices[$type] = $guestPrice + $guestPrices[$type];
                        }
                    } else {
                        $guestPrices[$type] = $guestPrice;
                    }

                    if ($guestPrice == 0) {

                        $nPrice = 0;
                        $gPrice = $guestPrices[$type];
                        if ($data['program_start_date'] == $data['program_end_date']) {
                            $nData = $data;
                            $nData['program_end_date'] =
                                date($suite->dateFormat, strtotime('+1 day', strtotime($data['program_end_date'])));
                            $nPrice = $suite->getTotal($nData) * $count;
                        }

                        if ($nPrice != 0 && $gPrice > $nPrice) {
                            $guestPrices[$type] = $nPrice;
                        } else {
                            $guestPrices[$type] = $gPrice;
                        }
                    }

                    // additional hotel charge from one Child/Toddler with one Adult/Teen
                    if ($additionalHotelCharge === null &&
                        in_array($type, [Model_Suite::GUEST_TYPE_CHILD, Model_Suite::GUEST_TYPE_TODDLER])
                        && ($guestTypes[Model_Suite::GUEST_TYPE_ADULT] + $guestTypes[Model_Suite::GUEST_TYPE_TEEN] == 1)
                    ) {

                        $addData = $data;
                        $addData['guest_type'] = Model_Suite::GUEST_TYPE_ADULT;
                        $pricing = $suite->getTotal($addData, false);
                        $additionalHotelCharge = $pricing['hotel_day_price'] * $pricing['hotel_days_count'];

                        $guestPrices[$type] += $additionalHotelCharge;
                        $additionalHotelCharge = 0;
                    }

                    $total += $guestPrices[$type] + $staffPrice;
                }
            }
            $total = $total - $promoPrice;

            if (!empty($data['additional_bedding']['crib'])) {
                $total += $suite->getCribPrice();
            }
            if (!empty($data['additional_bedding']['rollaway'])) {
                $total += $suite->getRollawayPrice();
            }

            $surcharge = 0;
            $surchargePercent = 0;
            /* if (strtotime('2018-07-12 12:00 AM') < time()) {
            $surchargePercent = 10;
            $surcharge = $total/$surchargePercent;
            } */
            $total = $total + $surcharge;
            $total += $tax;

            /*if ($isAdmin
                && $data['program_start_date'] == '08/18/2016' && $data['program_end_date'] == '08/18/2016'
                && $data['program_start_time'] == '08:30pm' && $data['program_end_time'] == '10:00pm'
                && $data['hotel_availability'] == 0
            ) {
                $price = 40;
                $promoPrice = 0;
                $surcharge = 0;
                $surchargePercent = 0;
                $programTaxRate = 0;
                $tax = 0;
                $total = 0;

                foreach ($guestTypes as $type => $count) {
                    if ($type != Model_Suite::GUEST_TYPE_INFANT) {
                        $guestPrices[$type] = $price * intval($count);
                        $total += $price * intval($count);
                    }
                }

                $newGuestProgramTax = [];
                foreach ($guestProgramTax as $type => $value) {
                    $newGuestProgramTax[$type] = 0;
                }
                $guestProgramTax = $newGuestProgramTax;

                foreach ($staffPrices as $type => $value) {
                    $total += $value;
                }
            }*/

            $total = number_format($total, 2);
            $surcharge = number_format($surcharge, 2);
            $tax = number_format($tax, 2);

            $result = [
                'form_id'             => $formId,
                'total'               => $total,
                'promo_price'         => $promoPrice,
                'promo_error'         => $promoError,
                'promo_message'       => $promoMessage,
                'guest_prices'        => $guestPrices,
                'staff_prices'        => $staffPrices,
                'surcharge'           => $surcharge,
                'surcharge_percent'   => $surchargePercent,
                'tax'                 => $tax,
                'hotel_tax_rate'      => $hotelTaxRate,
                'program_tax_rate'    => $programTaxRate,
                'guest_program_tax'   => $guestProgramTax,
                'early_bird_discount' => $earlyBirdDiscount,
                'detailed_info'       => $detailedInfo,
            ];
        }
        if ($return !== true && $this->request->isAjax()) {
            echo json_encode($result);
            die();
        } else {
            return $result;
        }
    }

    /**
     * Get price by promo code
     *
     * @return string
     */
    public function action_ApplyPromo()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');
            $return = ['total' => 0, 'promo' => 0, 'message' => '','error' => ''];

            if (isset($_GET['form_id'])) {
                $prices = $this->action_GetTotal(true);
                $return['promo'] = $prices['promo_price'];
                $return['total'] = $prices['total'];

                if ($prices['promo_price']) {
                    $return['message'] = 'discount applied';
                }
                $return['error'] = $prices['promo_error'];
            }
            echo json_encode($return);
            die();
        }

        return '';
    }

   

    public function saveToSession($roomData = [])
    {
        if (!session_id()) {
            session_start();
        }

        if (empty($roomData) && !empty($_GET)) {
            $roomData = $_GET;
        }

        $isAdmin = (array_key_exists('is_admin', $roomData) && $roomData['is_admin'] == 1) ? 1 : 0;

        $roomTypeModel = new Model_RetreatConfigRoomTypes();
        $roomTypeList = $roomTypeModel->getListOfRoomTypes();
        $bedTypeModel = new Model_RetreatConfigBedTypes();
        $bedTypeList = $bedTypeModel->getListOfBedTypes();
        $occupancyModel = new Model_RetreatConfigOccupancies();
        $occupancyList = $occupancyModel->getListOfOccupancies();
        $promoModel = new Model_RetreatPromotions();
        $promoList = $promoModel->getPromoList();

        if (!empty($roomData)) {
            // session fields for room
            $roomAttributes = [
                'bedding'            => 'bedTypeId',
                'room_type'          => 'roomTypeId',
                'occupancy'          => 'occupancy',
                'guest_type'         => [
                    Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                    Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                    Model_Suite::GUEST_TYPE_CHILD   => 'child',
                    Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                    Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                ],
                'guests'             => [
                    'program_price'       => [
                        Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                        Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                        Model_Suite::GUEST_TYPE_CHILD   => 'child',
                        Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                        Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                    ],
                    'hotel_price'         => [
                        Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                        Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                        Model_Suite::GUEST_TYPE_CHILD   => 'child',
                        Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                        Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                    ],
                    'early_bird_discount' => [
                        Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                        Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                        Model_Suite::GUEST_TYPE_CHILD   => 'child',
                        Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                        Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                    ],
                    'daily_discount'      => [
                        Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                        Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                        Model_Suite::GUEST_TYPE_CHILD   => 'child',
                        Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                        Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                    ],
                    'program_offset'      => [
                        Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                        Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                        Model_Suite::GUEST_TYPE_CHILD   => 'child',
                        Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                        Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                    ],
                    'program_tax'         => [
                        Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                        Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                        Model_Suite::GUEST_TYPE_CHILD   => 'child',
                        Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                        Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                    ],
                    'hotel_tax'           => [
                        Model_Suite::GUEST_TYPE_ADULT   => 'adult',
                        Model_Suite::GUEST_TYPE_TEEN    => 'teen',
                        Model_Suite::GUEST_TYPE_CHILD   => 'child',
                        Model_Suite::GUEST_TYPE_TODDLER => 'toddler',
                        Model_Suite::GUEST_TYPE_INFANT  => 'infant',
                    ],
                ],
                'staff'              => [
                    'babysitter'          => [
                        'day1' => 'day1',
                        'day2' => 'day2',
                        'day3' => 'day3',
                        'day4' => 'day4',
                        'day5' => 'day5',
                        'day6' => 'day6',
                        'day7' => 'day7',
                    ],
                    'group_babysitting'   => 'group_babysitting',
                    'private_babysitting' => 'private_babysitting',
                ],
                'additional_bedding' => 'additionalBedding',
                'start_date'         => 'endDate',
                'end_date'           => 'endDate',
                'program_start_date' => 'programStartDate',
                'program_end_date'   => 'programEndDate',
                'program_start_time' => 'programStartTime',
                'program_end_time'   => 'programEndTime',
                'hotel_start_date'   => 'hotelStartDate',
                'hotel_end_date'     => 'hotelEndDate',
                'total'              => 'price',
                'surcharge'          => 'surcharge',
                'hotel_tax_rate'     => 'hotelTaxRate',
                'program_tax_rate'   => 'programTaxRate',
                'tax'                => 'tax',
            ];

            $suite = $this->getSuiteModel();
            // session common fields
            $sessionData = [
                'id'           => null,
                'eventId'      => 18,
                'promotionId'  => null,
                'earlyBird'    => $suite->isEarlyBird(),
                'internalNote' => '',
            ];

            if (isset($_SESSION['admin'])) {
                $sessionData['admin'] = true;
            }

            $formData = [];
            if (isset($_SESSION['form'])) {
                $formData = $_SESSION['form'];
            }

            $programDates = $suite->getProgramDates();
            $startProgramDate = reset($programDates);
            $endProgramDate = end($programDates);

            $startProgramDate = date('m/d/Y', strtotime($startProgramDate));
            $endProgramDate = date('m/d/Y', strtotime($endProgramDate));

            $guestData = [];
            foreach ($roomData as $name => $data) {
                if (is_array($data)) {
                    foreach ($data as $roomId => $value) {
                        $guestData[$roomId][$name] = $value;
                    }
                }
            }

            foreach ($roomData as $name => $data) {
                if (is_array($data)) {
                    foreach ($data as $roomId => $value) {

                        $formData[$roomId][$name] = $value;

                        if (empty($roomData['room_type'][$roomId])
                            || (empty($roomData['hotel_start_date'][$roomId])
                                && empty($roomData['hotel_end_date'][$roomId]))
                        ) {
                            $roomData['room_type'][$roomId] = $suite->defaultRoomType;
                            $roomData['occupancy'][$roomId] = $suite->defaultOccupancy;
                            $roomData['bedding'][$roomId] = '';
                            $formData[$roomId]['room_type'] = $suite->defaultRoomType;
                            $formData[$roomId]['occupancy'] = $suite->defaultOccupancy;
                            $formData[$roomId]['bedding'] = '';
                        }

                        if (!empty($roomData['additional_bedding'][$roomId])) {
                            if (!empty($roomData['additional_bedding'][$roomId]['crib'])) {
                                $roomData['additional_bedding'][$roomId]['crib'] = $suite->getCribPrice();
                            }
                            if (!empty($roomData['additional_bedding'][$roomId]['rollaway'])) {
                                $roomData['additional_bedding'][$roomId]['rollaway'] = $suite->getRollawayPrice();
                            }
                        }

                        if (empty($roomData['program_start_date'][$roomId])
                            && empty($roomData['program_end_date'][$roomId])
                            && !empty($roomData['hotel_start_date'][$roomId])
                            && !empty($roomData['hotel_end_date'][$roomId])
                        ) {
                            if (strtotime($startProgramDate) < strtotime($roomData['hotel_start_date'][$roomId])) {
                                $roomData['program_start_date'][$roomId] = $roomData['hotel_start_date'][$roomId];
                            } else {
                                $roomData['program_start_date'][$roomId] = $startProgramDate;
                            }
                            if (strtotime($endProgramDate) > strtotime($roomData['hotel_end_date'][$roomId])) {
                                $roomData['program_end_date'][$roomId] = $roomData['hotel_end_date'][$roomId];
                            } else {
                                $roomData['program_end_date'][$roomId] = $endProgramDate;
                            }
                        }

                        $startDate = null;
                        $endDate = null;

                        if (!empty($roomData['program_start_date'][$roomId])
                            && !empty($roomData['program_end_date'][$roomId])
                            && !empty($roomData['hotel_start_date'][$roomId])
                            && !empty($roomData['hotel_end_date'][$roomId])
                        ) {
                            $day = 24 * 3600;
                            $programDates = [];
                            for (
                                $d = strtotime($roomData['program_start_date'][$roomId]);
                                $d <= strtotime($roomData['program_end_date'][$roomId]);
                                $d += $day
                            ) {
                                $programDates[date($suite->dateFormat, $d)] = date($suite->dateFormat, $d);
                            }
                            asort($programDates);

                            $hotelDates = [];
                            for (
                                $d = strtotime($roomData['hotel_start_date'][$roomId]);
                                $d <= strtotime($roomData['hotel_end_date'][$roomId]);
                                $d += $day
                            ) {
                                $hotelDates[date($suite->dateFormat, $d)] = date($suite->dateFormat, $d);
                            }
                            asort($hotelDates);

                            $dateRange = [];
                            foreach ($hotelDates as $key => $date) {
                                if (in_array($date, $programDates)) {
                                    $dateRange[$key] = $date;
                                }
                            }
                            asort($dateRange);

                            if (!empty($dateRange)) {
                                $startDate = reset($dateRange);
                                $endDate = end($dateRange);
                            }
                        }

                        foreach (array_keys($suite->getGuestTypeList()) as $type) {

                            $programPrice = 0;
                            $hotelPrice = 0;

                            if (array_key_exists($type, $roomData['guest_type'][$roomId]) && $roomData['guest_type'][$roomId][$type] > 0) {

                                $guestData[$roomId]['guest_types'] = $roomData['guest_type'][$roomId];
                                $guestData[$roomId]['guest_type'] = $type;

                                $totalData = $suite->getTotal($guestData[$roomId], false);

                                $roomData['guests'][$roomId]['daily_discount'][$type] = $totalData['lock_in_discount'];
                                $roomData['guests'][$roomId]['early_bird_discount'][$type] = $totalData['early_bird_discount'];

                                $programPrice = $totalData['program_price'] + $totalData['lock_in_discount'];
                                $hotelPrice = $totalData['hotel_price'];
                            }
                            $roomData['guests'][$roomId]['program_price'][$type] = (float)$programPrice;
                            $roomData['guests'][$roomId]['hotel_price'][$type] = (float)$hotelPrice;
                        }

                        if (!empty($roomData['program_start_date'][$roomId])
                            && !empty($roomData['program_end_date'][$roomId])
                            && !empty($roomData['program_start_time'][$roomId])
                            && !empty($roomData['program_end_time'][$roomId])
                        ) {
                            $programOffsetPrice = 0;
                            foreach ($roomData['guest_type'][$roomId] as $type => $count) {
                                $guestOffsetPrice = $suite->getProgramOffset(
                                    'total',
                                    $roomData['program_start_date'][$roomId],
                                    $roomData['program_end_date'][$roomId],
                                    $roomData['program_start_time'][$roomId],
                                    $roomData['program_end_time'][$roomId],
                                    $roomData['room_type'][$roomId],
                                    $roomData['occupancy'][$roomId],
                                    [$type => 1]
                                );
                                if (!empty($guestPrices)) {
                                    $programOffsetPrice = array_sum($guestPrices);
                                }
                                if ($programOffsetPrice > $suite->programTotalOffset) {
                                    $programOffsetPrice = $suite->programTotalOffset;
                                }
                                $roomData['guests'][$roomId]['program_offset'][$type] = (float)$guestOffsetPrice;
                            }
                        }
                        $sessionData['promo'.$roomId] = $roomData['promo'][$roomId];
                        if (!empty($roomData['promo'][$roomId])
                            //&& $promoId = array_search($roomData['promo'][$roomId], $promoList)
                        ) {

                            $promo = $promoModel->getPromoByCode($roomData['promo'][$roomId]);
                            $sessionData['promo'] = $promo;
                            $retreatEvent = new Model_RetreatEvents();
                            $event = $retreatEvent->getCurrentEvent();

                            $date2 = new DateTime($roomData['program_end_date'][$roomId].' '.$roomData['program_end_time'][$roomId]);
                            $date1 = new DateTime($roomData['program_start_date'][$roomId].' '.$roomData['program_start_time'][$roomId]);

                            $dayCount =  $date1->diff($date2);

                            foreach ($promo as $key=>$p){       
                            if ( new DateTime($p['expiry']) >= new DateTime('NOW') 
                            && ($p['count'] < $p['limit'] || $p['limit'] == 0) && $p['event_id'] == $event
                            && ($dayCount->d+1 >= $p['day_limit']   || $p['day_limit'] == 0 )
                            ){
                                $sessionData['promotionId'] = $p['id'];

                            }
                            }


                            
                        } else {
                            $sessionData['promotionId'] = null;
                            $roomData['promo'][$roomId] = '';
                            $formData[$roomId]['promo'] = '';
                        }

                        if (!empty($roomData['internal_note'][$roomId])) {
                            $sessionData['internalNote'] = $roomData['internal_note'][$roomId];
                        }

                        if (!empty($roomData['total'][$roomId])) {
                            $roomData['formatted_total'] = $roomData['total'][$roomId];
                            $roomData['total'][$roomId] =
                                (int)str_replace([',', '.'], '', $roomData['total'][$roomId]);
                        }
                        if (!empty($roomData['surcharge'][$roomId])) {
                            $roomData['formatted_surcharge'] = $roomData['surcharge'][$roomId];
                            $roomData['surcharge'][$roomId] =
                                (int)str_replace([',', '.'], '', $roomData['surcharge'][$roomId]);
                        }

                        if (!empty($roomData['tax'][$roomId])) {
                            if (!empty($roomData['total'][$roomId])) {
                                $roomData['formatted_tax'] = $roomData['tax'][$roomId];;
                                $roomData['tax'][$roomId] = (int)str_replace([',', '.'], '', $roomData['tax'][$roomId]);
                            } else {
                                $roomData['program_tax'][$roomId] = 0;
                                $roomData['hotel_tax'][$roomId] = 0;
                                $roomData['formatted_tax'] = 0;
                                $roomData['tax'][$roomId] = 0;
                            }
                        }

                        if (!isset($roomData['guests'], $roomData['guests'][$roomId], $roomData['guests'][$roomId]['program_tax'])) {
                            // tax rate
                            $programTaxRate = $suite->getProgramTax();
                            $roomData['program_tax_rate'][$roomId] = $programTaxRate;

                            // additional program tax for an additional half day
                            $extraProgramTax = 0;
                            if (!empty($roomData['program_start_time'][$roomId]) && !empty($roomData['program_end_time'][$roomId])
                                && $roomData['program_start_date'][$roomId] != $roomData['program_end_date'][$roomId]
                            ) {
                                $start = date_create($roomData['program_start_time'][$roomId]);
                                $end = date_create($roomData['program_end_time'][$roomId]);
                                $interval = date_diff($start, $end);
                                $interval = $interval->format('%h');

                                if ($interval >= 6) {
                                    $extraProgramTax = $suite->getProgramTax();
                                }
                            }

                            $guestTypes = $roomData['guest_type'][$roomId];
                            $start = date_create($roomData['program_start_date'][$roomId]);
                            $end = date_create($roomData['program_end_date'][$roomId]);
                            $interval = date_diff($start, $end);
                            $countProgramDays = $interval->format('%d');

                            if ($countProgramDays == 0) {
                                $countProgramDays = 1;
                            }

                            $totalCount = 0;
                            foreach ($guestTypes as $type => $count) {
                                if (in_array($type, $suite->getListOfCalculatedGuests())) {

                                    $roomData['guests'][$roomId]['program_tax'][$type] = [];

                                    if (in_array($type, [Model_Suite::GUEST_TYPE_CHILD, Model_Suite::GUEST_TYPE_TODDLER])
                                        && $count >= 1
                                        && $guestTypes[Model_Suite::GUEST_TYPE_ADULT] == 1
                                        && $guestTypes[Model_Suite::GUEST_TYPE_TEEN] == 0
                                    ) {
                                        if ($count == 1) {
                                            $roomData['guests'][$roomId]['program_tax'][$type][0] = $programTaxRate * $countProgramDays + $extraProgramTax;
                                        } else {
                                            $roomData['guests'][$roomId]['program_tax'][$type][0] = $programTaxRate * $countProgramDays + $extraProgramTax;
                                            for ($i = 1; $i < $count; $i++) {
                                                $roomData['guests'][$roomId]['program_tax'][$type][$i] = 0;
                                            }
                                        }
                                    } else if (!in_array($type, $suite->nonTaxableGuestTypes)) {
                                        for ($i = 0; $i < $count; $i++) {
                                            $roomData['guests'][$roomId]['program_tax'][$type][$i] = $programTaxRate * $countProgramDays + $extraProgramTax;
                                        }
                                    }

                                    $roomData['guests'][$roomId]['hotel_tax'][$type] = [];

                                    if (isset($roomData['room_type'][$roomId], $roomData['occupancy'][$roomId])
                                        && isset($roomData['hotel_start_date'][$roomId], $roomData['hotel_end_date'][$roomId])
                                    ) {
                                        $hotelTaxRate = $suite->getHotelTax($roomData['room_type'][$roomId], $roomData['occupancy'][$roomId]);
                                        $roomData['hotel_tax_rate'][$roomId] = $hotelTaxRate;

                                        if (!in_array($data['room_type'], $suite->disabledTypes)) {
                                            $start = date_create($roomData['hotel_start_date'][$roomId]);
                                            $end = date_create($roomData['hotel_end_date'][$roomId]);
                                            $interval = date_diff($start, $end);
                                            $countHotelDays = $interval->format('%d');

                                            if (in_array($type, [Model_Suite::GUEST_TYPE_CHILD, Model_Suite::GUEST_TYPE_TODDLER])
                                                && $count >= 1
                                                && $guestTypes[Model_Suite::GUEST_TYPE_ADULT] == 1
                                                && $guestTypes[Model_Suite::GUEST_TYPE_TEEN] == 0
                                            ) {
                                                if ($count == 1) {
                                                    $roomData['guests'][$roomId]['hotel_tax'][$type][0] = (float)$countHotelDays * $hotelTaxRate;
                                                } else {
                                                    $roomData['guests'][$roomId]['hotel_tax'][$type][0] = (float)$countHotelDays * $hotelTaxRate;
                                                    for ($i = 1; $i < $count; $i++) {
                                                        $roomData['guests'][$roomId]['hotel_tax'][$type][$i] = 0;
                                                    }
                                                }
                                            } else if (!in_array($type, $suite->nonTaxableGuestTypes)) {
                                                $totalCount += $count;

                                                $c = $count;
                                                if ($totalCount > 2) {
                                                    $c -= ($totalCount - 2);
                                                    $c = ($c < 0)? 0 : $c;
                                                }

                                                for ($i = 0; $i < $c; $i++) {
                                                    $roomData['guests'][$roomId]['hotel_tax'][$type][$i] = (float)$countHotelDays * $hotelTaxRate;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (isset($roomData['staff'], $roomData['staff'][$roomId])) {
                            if (is_array($roomData['staff'][$roomId])) {
                                foreach ($roomData['staff'][$roomId] as $staffName => $staffData) {
                                    if (is_array($staffData)) {
                                        foreach ($staffData as $name => $value) {
                                            if ($roomData['staff'][$roomId][$staffName][$name] == 1) {
                                                $roomData['staff'][$roomId][$staffName][$name] = 0;
                                            }

                                            $price = $suite->getStaffPrice(
                                                $roomData['room_type'][$roomId],
                                                $roomData['occupancy'][$roomId],
                                                $staffName,
                                                $roomData['guest_type'][$roomId],
                                                [$startDate, $endDate],
                                                $name,
                                                $roomData['program_start_time'][$roomId],
                                                $roomData['program_end_time'][$roomId]
                                            );

                                            $roomData['staff'][$roomId][$staffName][$name] = $price;
                                        }
                                    }
                                }
                            }
                        }

                        $roomTypeId = null;
                        foreach ($roomTypeList as $id => $type) {
                            $pattern = '/' . implode('.*', explode(' ', strtolower($type))) . '/';
                            if (preg_match($pattern, strtolower($roomData['room_type'][$roomId]))) {
                                $roomTypeId = $id;
                                break;
                            }
                        }
                        if ($roomTypeId !== null) {
                            $roomData['room_type'][$roomId] = $roomTypeId;
                        }

                        $bedTypeId = null;
                        if (isset($roomData['bedding'], $roomData['bedding'][$roomId])) {
                            foreach ($bedTypeList as $id => $type) {
                                $pattern = '/' . implode('.*', explode(' ', strtolower($type))) . '/';
                                if (preg_match($pattern, strtolower($roomData['bedding'][$roomId]))) {
                                    $bedTypeId = $id;
                                    break;
                                }
                            }
                        }

                        if ($bedTypeId !== null) {
                            $roomData['bedding'][$roomId] = $bedTypeId;
                        }

                        $occupancyId = null;
                        if (isset($roomData['occupancy'], $roomData['occupancy'][$roomId])) {
                            foreach ($occupancyList as $id => $type) {
                                $pattern = '/' . implode('.*', explode(' ', strtolower($type))) . '/';
                                if (preg_match($pattern, strtolower($roomData['occupancy'][$roomId]))) {
                                    $occupancyId = $id;
                                    break;
                                }
                            }
                        }

                        if ($occupancyId !== null) {
                            $roomData['occupancy'][$roomId] = $occupancyId;
                        }
                    }
                }
            }

            $rooms = [];
            foreach ($roomData as $name => $data) {
                if (isset($roomAttributes[$name]) && is_array($data)) {
                    foreach ($data as $roomId => $value) {

                        if (is_array($roomAttributes[$name]) && is_array($value)) {
                            foreach ($roomAttributes[$name] as $key => $val) {
                                $emptyValue = null;
                                if (!isset($value[$key])) {
                                    $emptyValue = 0;
                                }

                                if (isset($value[$key])) {
                                    if (is_array($val)) {
                                        foreach ($val as $valKey => $valData) {
                                            if (isset($value[$key][$valKey])) {
                                                if (!isset($rooms[$roomId], $rooms[$roomId][$key],
                                                        $rooms[$roomId][$key][$valKey])
                                                    || empty($rooms[$roomId][$key][$valKey])
                                                ) {
                                                    $rooms[$roomId][$key][$valKey] = $value[$key][$valKey];
                                                } else {
                                                    $rooms[$roomId][$key][$valKey] = $emptyValue;
                                                }
                                            }
                                        }
                                    } else {
                                        if (!isset($rooms[$roomId], $rooms[$roomId][$val])
                                            || empty($rooms[$roomId][$val])
                                        ) {
                                            $rooms[$roomId][$val] = $value[$key];
                                        } else {
                                            $rooms[$roomId][$val] = $emptyValue;
                                        }
                                    }
                                }
                            }
                        } else if (!isset($rooms[$roomId], $rooms[$roomId][$roomAttributes[$name]])
                                   || empty($rooms[$roomId][$roomAttributes[$name]])
                        ) {
                            $rooms[$roomId][$roomAttributes[$name]] = $value;
                        } else {
                            $rooms[$roomId][$roomAttributes[$name]] = null;
                        }
                    }
                }
            }

            $sessionData['rooms'] = $rooms;

            if (isset($_SESSION['rooming'])) {
                unset($_SESSION['rooming']);
            }
            if (isset($_SESSION['roomingInfoSentFromPage1'])) {
                foreach ($rooms as $roomId => $roomData) {
                    $_SESSION['roomingInfoSentFromPage1']['rooms'][$roomId] = $roomData;
                }
            } else {
                $_SESSION['roomingInfoSentFromPage1'] = $sessionData;
            }
            // Indicate that reservation is dome by admin or not
            $_SESSION['roomingInfoSentFromPage1']['isAdmin'] = $isAdmin;

            $_SESSION['form'] = $formData;
        }
    }
}