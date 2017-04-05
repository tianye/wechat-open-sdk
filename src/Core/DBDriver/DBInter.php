<?php

namespace WechatOpen\Core\DBDriver;

interface  DBInter
{
    public function __construct(array $options, $dir = '');

    public function _get($name);

    public function _set($name, $value, $expires);
}
