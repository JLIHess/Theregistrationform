<?php
/**
 * Base Class Core_Auth
 */
class Core_Auth
{
    /**
     * @var string $secret
     * generated as bin2hex(openssl_random_pseudo_bytes(16))
    */
    private $secret = 'da74668ee5460799a66bb98135623593';

    private $user = null;
    public $isLoggedIn = false;
    public $orderId = null;

    public function __construct()
    {
        if (isset($_SESSION['auth'], $_SESSION['auth']['id'])) {
            $this->isLoggedIn = true;

            $this->setUser($_SESSION['auth']);
        }
    }

    private function setUser($data)
    {
        $_SESSION['auth'] = $data;
        $this->user = $data;

        return $this->user;
    }

    private function getData($name)
    {
        return isset($this->user[$name])? $this->user[$name] : null;
    }

    public function __get($key)
    {
        return (method_exists($this, 'get' . ucfirst($key)))? $this->{'get' . $key} : null;
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function getEmail()
    {
        return $this->getData('email');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getFirstName()
    {
        return $this->getData('first_name');
    }

    public function getLastName()
    {
        return $this->getData('last_name');
    }

    public function autenticate($account)
    {
        if (isset($account['id'], $account['email'])) {
            $_SESSION['auth'] = [
                'id' => $account['id'],
                'email' => $account['email'],
                'name' => ($account['prefix']? $account['prefix'] . ' ' : '')
                    . $account['first_name'] . ' ' . $account['last_name'],
                'first_name' => $account['first_name'],
                'last_name' =>  $account['last_name'],
            ];
            $this->user = $_SESSION['auth'];
            $this->isLoggedIn = true;

            return true;
        }
        return false;
    }

    public function login($email, $password)
    {
        $model = new Model_RetreatUsers;

        $accounts = $model->findByEmail($email);

        foreach ($accounts as $account) {
            if (password_verify($password, $account['password'])
                || (!empty($account['old_password']) && $account['old_password'] = md5($password))
            ) {
                return $this->autenticate($account);
            }
        }
        return false;
    }

    public function loginByHash($hash)
    {
        $model = new Model_RetreatUsers;

        if ($account = $model->findByHash($hash, $this->secret)) {
            return $this->autenticate($account);
        }

        return false;
    }

    public function createToken()
    {
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16));

        return $_SESSION['token'];
    }

    public function validateToken($token)
    {
        $lastToken = $_SESSION['token'];
        unset($_SESSION['token']);

        return ($token == $lastToken);
    }

    public function logout()
    {
        if (!empty($_SESSION['auth'])) {
            unset($_SESSION['auth']);

            return true;
        }
        return false;
    }
}
