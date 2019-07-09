<?php
// +----------------------------------------------------------------------
// | Queue.队列封装
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.yuemeet.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: vijay <1937832819@qq.com> 2018-01-11
// +----------------------------------------------------------------------

namespace Vijay\Queue;

class Queue
{
    /**
     * @var null
     */
    protected $handler = null;

    /**
     * @var string
     */
    protected $error = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * Queue constructor.
     * @param null $driver
     */
    public function __construct($driver = null)
    {
        $this->setDriver($driver);
    }

    /**
     * Instructions:获取列队
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:29
     * @param string $name 队列名称
     * @return Queue
     */
    public static function init($name)
    {
        static $handler;
        if ($name && isset($handler[$name])) {
            $object = $handler[$name];
        } else {
            $object = $handler[$name] = new self();
            $object->name($name);
        }
        return $object;
    }

    /**
     * Instructions:获取列队名称
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:30
     * @param null $name
     * @return $this|string
     */
    public function name($name = null)
    {
        if (is_null($name)) {
            return $this->name;
        } else {
            $this->name = $name;
        }
        return $this;
    }

    /**
     * Instructions:获取驱动类型(建议配置redis使用)
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:30
     * @param $driver
     * @return $this|bool
     */
    public function setDriver($driver = null)
    {
        if (!is_object($driver)) {
            $this->error = '缓存驱动错误';
            return false;
        } else {
            $this->handler = $driver;
        }
        return $this;
    }

    /**
     * Instructions:获取队列载体对象
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:33
     * @return mixed
     */
    public function handler()
    {
        return $this->handler->handler();
    }

    /**
     * Instructions:把数据压入当前队列尾部
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:35
     * @param string|array|object $data
     * @return bool
     */
    public function push($data)
    {
        try {
            foreach (func_get_args() as $value) {
                $this->handler()->rPush($this->name(), serialize($value));
            }
            return true;
        } catch (\Exception $e) {
            $this->error = '压入队列失败';
        }
        return false;
    }

    /**
     * Instructions:把数据压入当前队列开头
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:36
     * @param $data
     * @return bool
     */
    public function lPush($data)
    {
        try {
            foreach (func_get_args() as $value) {
                $this->handler()->lPush($this->name(), serialize($value));
            }
            return true;
        } catch (\Exception $e) {
            $this->error = '压入队列失败';
        }
        return false;
    }

    /**
     * Instructions:获取队列中的数据
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:36
     * @param int $limit
     * @return array|bool
     */
    public function getData($limit = 100)
    {
        $dataList = [];
        try {
            $empty = 0;
            for ($i = 0; $i < $limit; $i++) {
                if ($empty > 10) {
                    break;
                }
                $data = $this->handler()->lPop($this->name());
                if (empty($data)) {
                    $empty++;
                    continue;
                }
                $dataList[] = unserialize($data);
            }
        } catch (\Exception $e) {
            $this->error = '从队列获取失败';
            return false;
        }
        return $dataList;
    }

    /**
     * Instructions:删除
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:37
     * @return mixed
     */
    public function rm()
    {
        return $this->handler()->delete($this->name());
    }

    /**
     * Instructions:获取队列长度
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:37
     * @return mixed
     */
    public function length()
    {
        return $this->handler()->LLEN($this->name());
    }

    /**
     * Instructions:获取错误信息
     * User: Vijay  <1937832819@qq.com>
     * Time: 2018/7/28 11:37
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}