<?php
class Sessions extends CActiveRecord
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
        return 'sessions';
        }
        
    public function relations()
        {
        return array( 
                    'films' => array(self::BELONGS_TO, 'Films', 'film_id' ),
                    'halls' => array(self::BELONGS_TO, 'Halls', 'hall_id' ),
                    );
        }
    }