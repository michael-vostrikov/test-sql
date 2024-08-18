<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $external_id
 * @property string $last_order
 */
class Customer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['external_id', 'last_order'], 'safe'],
        ];
    }
}
