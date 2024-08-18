<?php

use common\models\Customer;
use common\models\OrderDetail;
use common\models\OrderHeader;

class m240818_160000_add_demo_data extends yii\db\Migration
{
    public function up()
    {
        $customer = new Customer();
        $customer->external_id = 'testCustomer';
        $customer->last_order = 'testCustomer2024/000001';
        $customer->save();
    }

    public function down()
    {
        OrderDetail::deleteAll();
        OrderHeader::deleteAll();
        Customer::deleteAll();
    }
}
