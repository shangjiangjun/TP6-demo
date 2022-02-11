<?php
declare (strict_types = 1);

namespace app\api\controller\v1;

use app\BaseController;
use app\common\model\Notification as NotificationModel;

class Notification extends BaseController
{
    /**
     * 读取所有的邮件信息
     *
     *
     */
    public function readAllMail()
    {
        $lists = NotificationModel::getUserNotify(1);
        foreach ($lists as &$item) {
            $item->is_get = 1;
            if ($item->read_at == null) {
                $item->read_at = date('Y-m-d H:i:s');
            }
            $item->save();
        }
        dump($lists->toArray());

        die();
    }
}