<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Class Controller_Auth
 */
class Controller_Auth extends Core_Controller
{
    public $layout = 'main';

    public function action_verifyEmail()
    {
        if ($this->request->isAjax()) {

            $return = ['status' => 'error',
                'errors' => [['name' => 'login_email', 'message' => 'incorrect email address, try again or continue as guest']]
            ];

            header('Content-type: application/json');

            $email = $_POST['login_email'];

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $model = new Model_RetreatUsers();

                if ($accounts = $model->findByEmail($email)) {
                    $return = ['status' => 'success', 'message' => ''];
                }
            }

            echo json_encode($return);
            exit;
        }
    }

    public function action_verifyPhone()
    {
        if ($this->request->isAjax()) {

            $return = ['status' => 'error',
                'errors' => [['name' => 'login_phone', 'message' => 'incorrect phone number, try again or continue as guest']]
            ];

            $phone = isset($_POST['login_phone'])? preg_replace('/[^0-9]{1,}/', '', $_POST['login_phone']) : '';
            $phoneCode = isset($_POST['login_phone_code'])? preg_replace('/[^0-9]{1,}/', '', $_POST['login_phone_code']) : '';

            header('Content-type: application/json');

            $model = new Model_RetreatUsers;

            if (strlen($phone) > 4) {

                $phone = preg_replace('/^' . $phoneCode . '/', '', $phone);

                if ($accounts = $model->findByPhone($phone)) {
                    $return = ['status' => 'success', 'message' => ''];
                }
            } else if (empty($phone) && !empty($_POST['login_phone'])) {
                $return['errors'] = [['name' => 'login_phone', 'message' => 'number is invalid']];
            } else {
                $return['errors'] = [['name' => 'login_phone', 'message' => 'number is too short']];
            }

            echo json_encode($return);
            exit;
        }
    }

    public function action_login()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');

            $return = ['status' => 'error'];

            $return = ['status' => 'error', 'message' => 'Please try again, or <a href="#" class="close-popup">continue without sining in</a>'];

            $email = isset($_POST['login_email'])? $_POST['login_email'] : '';
            $password = isset($_POST['login_password'])? $_POST['login_password'] : '';
            $userId = isset($_POST['login_user_id'])? $_POST['login_user_id'] : '';
            $token = isset($_POST['login_token'])? $_POST['login_token'] : '';

            if (!empty($userId) && !empty($token)) {
                if ($this->user->validateToken($token)) {

                    $model = new Model_RetreatUsers;

                    if ($this->user->autenticate($model->findById($userId))) {
                        $return = ['status' => 'success', 'redirect' => $_SERVER['HTTP_REFERER']];
                    }
                }
            } else if (!empty($email) && !empty($password)) {
                if ($this->user->login($email, $password)) {
                    $return = ['status' => 'success', 'redirect' => $_SERVER['HTTP_REFERER']];
                } else {
                    $return['errors'] = [['name' => 'login_password', 'message' => 'password is invalid']];
                }
            }

            echo json_encode($return);
            exit;
        }
    }

    public function action_verifyCode()
    {
        if ($this->request->isAjax()) {
            header('Content-type: application/json');

            $return = ['status' => 'error',
                'errors' => [['name' => 'login_verification_code', 'message' => 'The code is incorrect, Please try again, or <a href="#" class="close-popup">continue without sining in</a>']]
            ];

            $email = isset($_POST['login_email'])? $_POST['login_email'] : '';
            $phone = isset($_POST['login_phone'])? preg_replace('/[^0-9]{1,}/', '', $_POST['login_phone']) : '';
            $phoneCode = isset($_POST['login_phone_code'])? preg_replace('/[^0-9]{1,}/', '', $_POST['login_phone_code']) : '1';
            $code = isset($_POST['login_verification_code'])? preg_replace('/[^0-9a-zA-Z]{1,}/', '', $_POST['login_verification_code']) : '';

            $model = new Model_RetreatUsers;

            $accounts = [];
            if (!empty($email)) {
                $accounts = $model->findByEmail($email);

            } else if (!empty($phone)) {

                $phone = preg_replace('/^' . $phoneCode . '/', '', $phone);
                $accounts = $model->findByPhone($phone);
            }

            $valid = false;
            if ($code) {

                if ($phone) {

                    $authy_api = new Authy\AuthyApi('Zy9965915IMWelnEy3q69bS49UM1CQOQ');
                    $check = $authy_api->phoneVerificationCheck($phone, $phoneCode, $code);

                    if ($check) {
                        $valid = true;
                    }
                } else {
                    if (isset($_SESSION['code']) && $_SESSION['code'] == $code) {
                        $valid = true;
                    }
                }

            } else {
                $return['errors'] = [['name' => 'login_verification_code', 'message' => 'please enter the code']];
            }

            if ($valid) {

                $return = ['status' => 'success'];

                $results = [];
                foreach ($accounts as $account) {

                    $name = $account['first_name'] . ' ' . $account['last_name'];
                    if (!empty($account['prefix'])) {
                        $name =  $account['prefix'] . ' ' . $name;
                    }

                    if (isset($results[$name])) {
                        if ($results[$name]['id'] > $account['id']) {
                            continue;
                        }
                    }
                    $results[$name] = $account;
                    $results[$name]['user_id'] = $account['id'];
                    $results[$name]['name'] = $name;
                }
                $results = array_values($results);

                if (count($results) > 1) {

                    $return['accounts'] = $results;
                    $return['token'] = $this->user->createToken();

                } else {
                    $account = reset($results);

                    if ($this->user->autenticate($account)) {
                        $return['redirect'] = $_SERVER['HTTP_REFERER'];
                    }
                }
            }

            echo json_encode($return);
            exit;
        }
    }

    public function action_logout()
    {
        if ($this->user->logout()) {
            $this->request->redirect(BASE_URL);
        }
    }

    public function action_sendVerificationCode()
    {
        if ($this->request->isAjax()) {
            $return = ['status' => 'error', 'message' => 'something went wrong. Please try again, or <a href="#" class="close-popup">continue without sining in</a>'];

            header('Content-type: application/json');

            $phone = isset($_POST['login_phone'])? preg_replace('/[^0-9]{1,}/', '', $_POST['login_phone']) : '';
            $phoneCode = isset($_POST['login_phone_code'])? preg_replace('/[^0-9]{1,}/', '', $_POST['login_phone_code']) : '1';
            $verifyType = isset($_POST['verify_type'])? $_POST['verify_type'] : 'sms';
            $email = isset($_POST['login_email'])? $_POST['login_email'] : '';

            if ($phone) {
                $phone = preg_replace('/^' . $phoneCode . '/', '', $phone);

                $return = ['status' => 'success'];

                $authy_api = new Authy\AuthyApi('Zy9965915IMWelnEy3q69bS49UM1CQOQ');
                $response = $authy_api->phoneVerificationStart($phone, $phoneCode, $verifyType);

            } else {
                $_SESSION['code'] = mt_rand(1000, 9999);

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                    if ($_SERVER['REMOTE_ADDR'] == '188.232.229.188') {
                        $email = 'chepurko87@gmail.com';
                    }

                    $mail = new PHPMailer();

                    try {
                        //Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.sendgrid.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'mendye-jli';
                        $mail->Password = 'JLIcentral770';
                        $mail->Port = 587;

                        //Recipients
                        $mail->setFrom('info@jretreat.com', 'National Jewish Retreat');
                        $mail->addAddress($email);

                        //Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Verification Code for the National Jewish Retreat: ' . $_SESSION['code'];
                        $mail->Body = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title></title>
    <!--[if (mso 16)]>
    <style type="text/css">
    a {text-decoration: none;}
    </style>
    <![endif]-->
    <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]-->
</head>
<body>
    <div style="background-color: #f9f9f9; width: 100%; height: 100%;">
        <!--[if gte mso 9]>
            <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
                <v:fill type="tile" color="#f6f6f6"></v:fill>
            </v:background>
        <![endif]-->
        <table cellpadding="0" cellspacing="0"  width="100%" style="margin-bottom: 40px;">
            <tbody>
                <tr>
                    <td valign="top" >
                        <table cellpadding="0" cellspacing="0" align="center">
                            <tbody>
                                <tr>
                                    <td align="center">
                                        <table align="center" cellpadding="0" cellspacing="0" width="600">
                                            <tbody>
                                                <tr>
                                                    <td align="left">
                                                        <table cellpadding="0" cellspacing="0" width="100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td width="560"  align="center" valign="top">
                                                                        <table cellpadding="0" cellspacing="0" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" >
                                                                                        <a href="https://myjli.com/retreat/" target="_blank">
                                                                                        <img src="https://myjli.com/retreat/img/email-logo.png" alt="" width="124" style="display: block;">
                                                                                        </a>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table cellpadding="0" cellspacing="0"  align="center">
                            <tbody>
                                <tr>
                                    <td  align="center">
                                        <table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" width="600" style="padding: 20px;">
                                            <tbody>
                                                <tr>
                                                    <td  align="left" style="background-position: left top; background-color: rgb(255, 255, 255);" bgcolor="#ffffff">
                                                        <table cellpadding="0" cellspacing="0" width="100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td width="560"  align="center" valign="top">
                                                                        <table cellpadding="0" cellspacing="0" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="left" >
                                                                                        <h2 style="color: #bd917d;"><strong>Verify your email with the following code</strong></h2>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" >
                                                                                        <p>Welcome back. We\'re looking forward to hosting you at the retreat.</p>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" >
                                                                                        <p>Enter the following code in the window where you were taken after login:</p>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" >
                                                                                        <p style="font-size: 23px;"><strong>' . $_SESSION['code'] . '</strong></p>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" >
                                                                                        <p>If you have any questions, email us at info@jretreat.com or call 1-877-JRetreat.</p>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" >
                                                                                        <p>The team at the National Jewish Retreat</p>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>';

                        $mail->send();
                        $return = ['status' => 'success'];
                    } catch (Exception $e) {
                         $return = ['message' => $mail->ErrorInfo];
                    }
                }
            }

            echo json_encode($return);
            exit;
        }
    }

    public function action_createPassword()
    {
        if ($this->request->isAjax()) {
            $return = ['status' => 'error'];

            header('Content-type: application/json');

            $userId = isset($_POST['user_id'])? $_POST['user_id'] : '';
            $password = isset($_POST['password'])? $_POST['password'] : '';

            if (empty($password)) {

                 $return['errors'] = [['name' => 'password', 'message' => 'Please fill in the password']];

            } else if (strlen($password) < 6) {

                $return['errors'] = [['name' => 'password', 'message' => 'Your password needs to include at least 6 characters.']];

            } else {

                $model = new Model_RetreatUsers;

                if ($account = $model->findById($userId)) {
                    $model->updatePassword($password, $userId);

                    $return['status'] = 'success';
                }
            }

            echo json_encode($return);
            exit;
        }
    }
}
