<?php
/**
 * Class Model_RetreatEvents for table retreat_config_events
 */
class Model_RetreatEvents extends Core_Model
{
    public function tableName()
    {
        return 'retreat_config_events';
    }

    public function getCurrentEvent()
    {
        $query = "SELECT * FROM {$this->tableName()} WHERE current=1";
        $event = $this->getVar($query);
        return $event;
    }
    
}