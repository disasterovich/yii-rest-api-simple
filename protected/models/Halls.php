<?php
class Halls extends CActiveRecord
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
        return 'halls';
        }
        
    public function relations()
        {
        return array( 
                    'sessions' => array(self::HAS_MANY, 'Sessions', 'hall_id' ),
                    'cinemas' => array(self::BELONGS_TO, 'Cinemas', 'cinema_id' ),
                    );
        }
    }