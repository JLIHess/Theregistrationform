<?php
/**
 * Class Model_RetreatConfigBedTypes
 */
class Model_RetreatAdditionalOption extends Core_Model
{
    public function tableName()
    {
        return 'retreat_additional_option';
    }

    public function getList()
    {
        $query = "SELECT id, title FROM {$this->tableName()}";
        $list = $this->getResults($query);

        $return = array();
        if (is_array($list)) {
            foreach ($list as $row) {
                $return[$row['id']] = $row['title'];
            }
        }

        return $return;
    }
}