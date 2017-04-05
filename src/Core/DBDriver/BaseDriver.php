<?php

namespace WechatOpen\Core\DBDriver;

/**
 * 缓存基类.
 *
 */
abstract class BaseDriver implements DBInter
{
    protected $dir; // 缓存路径

    public function __construct(array $options, $dir = '')
    {
        $this->dir = $dir;
    }
}
