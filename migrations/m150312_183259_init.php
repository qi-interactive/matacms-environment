<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

use yii\db\Schema;
use mata\user\migrations\Migration;

class m150312_183259_init extends Migration {
    
    public function safeUp() {
        $this->createTable('{{%matacms_itemenvironment}}', [
            'DocumentId'   => Schema::TYPE_STRING . '(64) NOT NULL',
            'Revision'     => Schema::TYPE_INTEGER . ' NOT NULL',
            'Status' => Schema::TYPE_STRING . '(32) NOT NULL'
            ]);

        $this->addPrimaryKey("PK_DocumentRevision", "{{%matacms_itemenvironment}}", ["DocumentId", "Revision"]);
    }

    public function safeDown() {
        $this->dropTable('{{%matacms_itemenvironment}}');
    }

}
