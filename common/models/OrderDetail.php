<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $order_id
 * @property integer $line_num
 * @property integer $sku
 * @property string $price
 * @property integer $qty
 */
class OrderDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_details}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'line_num', 'sku', 'price', 'qty'], 'safe'],
        ];
    }
}
