<?php

class m240818_120000_create_tables extends yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('customer', [
            'id' => $this->primaryKey()->notNull(),
            'external_id' => $this->string()->notNull()->unique(),
            'last_order' => $this->string()->null(),
        ]);

        $this->createTable('order_header', [
            'id' => $this->primaryKey()->notNull(),
            'order_num' => $this->string()->notNull()->unique(),
            'customer_id' => $this->integer()->notNull(),
            'order_date' => $this->date()->notNull(),
        ]);

        $this->createTable('order_details', [
            'id' => $this->primaryKey()->notNull(),
            'order_id' => $this->integer()->notNull(),
            'line_num' => $this->integer()->notNull(),
            'sku' => $this->integer()->notNull(),
            'price' => $this->decimal(13,2)->notNull(),
            'qty' => $this->integer()->notNull(),
        ]);
        $this->createIndex('order_details_order_id_idx', 'order_details', 'order_id');

        $this->addForeignKey('order_header_customer', 'order_header', 'customer_id', 'customer', 'id', null, null);
        $this->addForeignKey('order_details_order_header', 'order_details', 'order_id', 'order_header', 'id', null, null);
    }

    public function safeDown()
    {
        $this->dropForeignKey('order_details_order_header', 'order_details');
        $this->dropForeignKey('order_header_customer', 'order_header');

        $this->dropIndex('order_details_order_id_idx', 'order_details');

        $this->dropTable('order_details');
        $this->dropTable('order_header');
        $this->dropTable('customer');
    }
}
