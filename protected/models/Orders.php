<?php
class Orders extends CActiveRecord
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
        return 'orders';
        }      
        
        
    protected function beforeSave()
        {
        if(parent::beforeSave())
            {
            if($this->isNewRecord)
                {
                $this->code = TextHelper::generateCode();
                }
                
            return true;
            }
        else
            return false;
        }

    }