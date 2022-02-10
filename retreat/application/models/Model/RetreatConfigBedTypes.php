<?php
/**
 * Class Model_RetreatConfigBedTypes
 */
class Model_RetreatConfigBedTypes extends Core_Model
{
    public function tableName()
    {
        return 'retreat_config_bed_types';
    }

    public function getBedTypeIdByName($name)
    {
        $query = "SELECT id FROM {$this->tableName()} WHERE type = :name";
        $params = array(':name' => $name);
        $name = $this->getVar($query, $params);

        return $name;
    }

    public function getListOfBedTypes()
    {
        $query = "SELECT id, type FROM {$this->tableName()}";
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