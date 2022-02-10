<?php
/**
 * Class Model_RetreatConfigRoomTypes
 */
class Model_RetreatConfigRoomTypes extends Core_Model
{

    public function tableName()
    {
        return 'retreat_config_room_types';
    }

    public function getRoomTypeIdByName($name)
    {
        $query = "SELECT id FROM {$this->tableName()} WHERE type = :name";
        $params = array(':name' => $name);

        $name = $this->getVar($query, $params);

        return $name;
    }

    public function getListOfRoomTypes()
    {
        $query = "SELECT id, type, status FROM {$this->tableName()} where status = 1";
        $list = $this->getResults($query);

        $return = array();
        if (is_array($list)) {
            foreach ($list as $row) {
                $return[$row['id']] = $row['type'];
            }
        }

        return $return;
    }
}