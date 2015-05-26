<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\environment\models;

use Yii;

/**
 * This is the model class for table "matacms_itemenvironment".
 *
 * @property string $DocumentId
 * @property integer $Revision
 * @property string $Status
 */

class ItemEnvironment extends \mata\db\ActiveRecord
{

    const REQ_PARAM_ITEM_ENVIRONMENT = "item_environment";
    const REQ_PARAM_REVISION = "revision";
    
    public static function tableName() {
        return 'matacms_itemenvironment';
    }

    public function rules() {
        return [
            [['DocumentId', 'Revision', 'Status'], 'required'],
            [['Revision'], 'integer'],
            [['Status'], 'string', 'max' => 32]
        ];
    }

    public function attributeLabels() {
        return [
            'DocumentId' => 'Document ID',
            'Revision' => 'Revision',
            'Status' => 'Status',
        ];
    }

}
