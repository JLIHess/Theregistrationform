<?php
/**
 * Class Model_RetreatBabysitting for table retreat_babysitting
 */
class Model_RetreatBabysitting extends Core_Model
{
    public function tableName()
    {
        return 'retreat_babysitting';
    }

    /**
     * Get array data by retreat_order_rooms_users.id
     *
     * @param int $orderRoomUserId retreat_order_rooms_users.id
     * @return bool|array
     */
    public function getBabysitterByOrderRoomUserId($orderRoomUserId)
    {
        $query = "SELECT * FROM {$this->tableName()} WHERE room_id = :id";
        $params = array(':id' => $orderRoomUserId);

        $row = $this->getRow($query, $params);

        return $row;
    }
}