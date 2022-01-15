<?php
namespace app\api;

class Error
{
    public function __call($name, $arguments)
    {
        dump(2);
        return show(10000);
    }
}
