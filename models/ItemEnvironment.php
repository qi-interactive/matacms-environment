<?php

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
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'matacms_itemenvironment';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['DocumentId', 'Revision', 'Status'], 'required'],
            [['Revision'], 'integer'],
            [['DocumentId'], 'string', 'max' => 64],
            [['Status'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'DocumentId' => 'Document ID',
            'Revision' => 'Revision',
            'Status' => 'Status',
        ];
    }
}