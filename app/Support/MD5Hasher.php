<?php

namespace App\Support;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Hashing\AbstractHasher;

class MD5Hasher extends AbstractHasher implements HasherContract
{

    /*
     * 验证用户输入密码
     */
    public function check($value, $result = [], array $options = [])
    {
        $user_password = $result[0];
        $user_salt     = $result[1];

        $input_password = md5($user_salt .md5($value));

        return $input_password === $user_password;
    }

    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }

    public function make($value, array $options = [])
    {
        return md5($value);
    }
}
