<?php
/**
 * Class Model_RetreatConfigRoomTypes for table retreat_config_occupancies
 */
class Model_RetreatConfigOccupancies extends Core_Model
{
    public function tableName()
    {
        return 'retreat_config_occupancies';
    }

    public function getListOfOccupancies()
    {
        $query = "SELECT id, name FROM {$this->tableName()}";
        $list = $this->getResults($query);

        $return = array();
        if (is_array($list)) {
            foreach ($list as $row) {
                $return[$row['id']] = $row['name'];
            }
        }

        return $return;
    }
}