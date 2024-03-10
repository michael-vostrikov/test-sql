<?php

use common\models\User;

class m240303_193900_add_demo_user extends yii\db\Migration
{
    public function up()
    {
        $user = new User();
        $user->id = 1;
        $user->username = 'user';
        $user->email = 'user@example.com';
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword('123456');
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->save();
    }

    public function down()
    {
        User::deleteAll();
    }
}
