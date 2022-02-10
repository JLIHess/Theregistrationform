<?php

/**
 * Class Model_RetreatUsers for table retreat_users
 */
class Model_RetreatUsers extends Core_Model
{
    public static function tableName()
    {
        return 'retreat_users';
    }

    public function findById($userId)
    {
        $query = "SELECT * FROM retreat_users WHERE id LIKE :id";
        $params = [':id' => $userId];

        return $this->getRow($query, $params);
    }
    /**
     * @param string $email
     * @return bool|array
     */
    public function findByEmail($email)
    {
        $query = "SELECT t.id, t.first_name, t.last_name, t.prefix, t.email, t.password, u.user_password AS `old_password`
        FROM retreat_users t
        LEFT JOIN Users u ON u.user_id = t.user_id
        WHERE t.email LIKE '" . $email . "' AND t.`status` = 1 AND t.merged_user_id IS NULL
        ORDER BY t.id DESC";

        $results = [];
        foreach ($this->getResults($query) as $row) {

            if (!empty($row['password'])) {
                $results = [[
                    'id' => $row['id'],
                    'prefix' => $row['prefix'],
                    'email' => $row['email'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'password' => $row['password'],
                    'old_password' => $row['old_password'],
                ]];
                break;
            }

            $results[$row['id']] = [
                'id' => $row['id'],
                'prefix' => $row['prefix'],
                'email' => $row['email'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'password' => $row['password'],
                'old_password' => $row['old_password'],
            ];
        }

        return $results;
    }

    public function findByPhone($phone)
    {
        $query = "SELECT u.first_name, u.last_name, u.prefix, u.id, u.email, u.password
            FROM retreat_users u
            WHERE (u.cell_phone LIKE '%" . $phone . "' OR u.day_phone LIKE '%" . $phone . "' OR u.home_phone LIKE '%" . $phone . "') AND u.`status` = 1 AND u.merged_user_id IS NULL
            ORDER BY id DESC";

        $results = [];
        foreach ($this->getResults($query) as $row) {

            if (!empty($row['password'])) {
                $results = [[
                    'id' => $row['id'],
                    'prefix' => $row['prefix'],
                    'email' => $row['email'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                ]];
                break;
            }

            $results[$row['id']] = [
                'id' => $row['id'],
                'email' => $row['email'],
                'prefix' => $row['prefix'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
            ];
        }

        return $results;
    }

    public function findByOrderId($orderId)
    {
        $query = "SELECT t.*, u.user_password AS `old_password`
            FROM " . Model_RetreatOrdersRoomsUsers::tableName() . " oru
            LEFT JOIN retreat_users t ON t.id = oru.user_id
            LEFT JOIN Users u ON u.user_id = t.user_id
            WHERE oru.order_id = :id AND oru.primary = 1 AND merged_user_id IS NULL AND status = 1";
        $params = [':id' => $orderId];

        return $this->getRow($query, $params);
    }

    public function findByCode($code)
    {
        $query = "SELECT * FROM retreat_users WHERE code = :code";
        $params = [':code' => $code];

        return $this->getRow($query, $params);
    }

    public function findByHash($code, $secret)
    {
        $query = "SELECT * FROM retreat_users WHERE SHA2(CONCAT('" . $secret . "+', id), 512) = '" . $code . '"';

        return $this->getRow($query);
    }

    public function updatePassword($password, $userId)
    {
        $query = 'UPDATE retreat_users SET password = "' .  password_hash($password, PASSWORD_BCRYPT) . '" WHERE id = ' . abs($userId);

        return $this->query($query);
    }
}
