<?php
class OrdersPlaces extends CActiveRecord
    {
    /**
     * Returns the static model of the specified AR class.
     * @return CActiveRecord the static model class
     */
    public static function model($className=__CLASS__)
        {
        return parent::model($className);
        }

    /**
     * @return string the associated database table name
     */
    public function tableName()
        {
        return 'orders_places';
        }
        
    public function relations()
        {
        return array( 
                    'orders' => array(self::BELONGS_TO, 'Orders', 'order_id' ),
                    );
        }
    }