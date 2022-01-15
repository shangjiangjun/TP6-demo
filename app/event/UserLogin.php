<?php
declare (strict_types = 1);

namespace app\event;

use app\common\model\User;

class UserLogin
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
