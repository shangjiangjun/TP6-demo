<?php
declare (strict_types = 1);

namespace app\api\controller\v1;

use app\BaseController;
use think\Request;

class User extends BaseController
{

    public function index()
    {
        return show(0, '版本1');
    }
}
