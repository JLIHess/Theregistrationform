<?php
/**
 * Class Model_RetreatPromotions for table retreat_promotions
 */
class Model_RetreatPromotions extends Core_Model
{
    public function tableName()
    {
        return 'retreat_promotions';
    }

    public function getPriceByCode($code)
    {
        $query = "SELECT amount FROM {$this->tableName()} WHERE LOWER(code) = LOWER(:code) AND expiry >= NOW()";
        $params = array(':code' => $code);

        $price = $this->getVar($query, $params);
        if (empty($price)) {
            $price = 0;
        }

        return $price;
    }
    public function getPromoByCode($code)
    {
        $query = "SELECT *, (SELECT COUNT(*) FROM `retreat_orders` WHERE `retreat_orders`.`status` IN (4,5)  AND `retreat_orders`.`promotion_id` = {$this->tableName()}.`id`) AS count FROM {$this->tableName()} 
        
        WHERE LOWER(code) = LOWER(:code)";
        $params = array(':code' => $code);

        

        $results = $this->getResults($query, $params);
        $list = array();
        foreach ($results as $data) {
            $list[$data['id']] = $data;
        }

        return $list;

        
    }

    public function getPromoList()
    {
        $query = "SELECT id, code FROM {$this->tableName()} WHERE expiry >= NOW()";

        $results = $this->getResults($query);
        $list = array();
        foreach ($results as $data) {
            $list[$data['id']] = $data['code'];
        }

        return $list;
    }
}