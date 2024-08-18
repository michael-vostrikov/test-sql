<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $order_num
 * @property integer $customer_id
 * @property string $order_date
 */
class OrderHeader extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_header}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_num', 'customer_id', 'order_date'], 'safe'],
        ];
    }
}
