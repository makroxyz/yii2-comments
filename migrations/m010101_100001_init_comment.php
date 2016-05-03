<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m010101_100001_init_comment
 */
class m010101_100001_init_comment extends Migration
{
    /**
     * Create table `Comment`
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%comment}}', [
            'id' => $this->primaryKey(),
            'entity' => $this->string(10)->notNull(),
            'entity_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'parent_id' => $this->integer()->defaultExpression('NULL'),
            'level' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'related_to' => $this->string(500)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('entity_index', '{{%comment}}', 'entity');
        $this->createIndex('status_index', '{{%comment}}', 'status');
    }

    /**
     * Drop table `Comment`
     */
    public function down()
    {
        $this->dropTable('{{%comment}}');
    }

}