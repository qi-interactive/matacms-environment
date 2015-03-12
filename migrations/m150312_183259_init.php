<?php

/*
 * This file is part of the mata project.
 *
 * (c) mata project <http://github.com/mata/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use yii\db\Schema;
use mata\user\migrations\Migration;

/**
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
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