<?php

use yii\db\Schema;

class m140622_111540_create_image_table extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%image}}', [
            'id' => $this->primaryKey(),
            'filePath' => $this->string()->notNull(),
            'itemId' => $this->integer()->notNull(),
            'modelName' => $this->string(),
            'modelTableName' => $this->string()->notNull(),
            'isMain' => $this->boolean(),
            'name' => $this->string(64),
            'sortOrder' => $this->integer()->defaultValue(0),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%image}}');
    }
}
