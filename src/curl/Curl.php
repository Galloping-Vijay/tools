<?php
// +----------------------------------------------------------------------
// | Curl.Curl的封装
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.yuemeet.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: vijay <1937832819@qq.com> 2018-01-11
// +----------------------------------------------------------------------

namespace vijay\curl;

use Exception;

class Curl
{
    /**
     * 执行请求
     * Author: vijay <1937832819@qq.com>
     * @param string $method 请求方式
     * @param string $url 请求地址
     * @param string|array $fields 附带参数，可以是数组，也可以是字符串
     * @param string $userAgent 浏览器UA
     * @param array $httpHeaders header头部，数组形式
     * @param string $username 用户名
     * @param string $password 密码
     * @return array|bool|mixed
     */
    public function execute($method, $url, $fields = '', $userAgent = '', $httpHeaders = [], $username = '', $password = '')
    {
        $ch = $this->create();
        if (false === $ch) {
            return false;
        }
        if (is_string($url) && strlen($url)) {
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            return false;
        }
        //是否显示头部信息
        curl_setopt($ch, CURLOPT_HEADER, false);
        //不直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //传递一个形如[username]:[password]风格的字符串
        if (!empty($username)) {
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        }
        //ssl
        if (false !== stripos($url, "https://")) {
            //对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $method = strtolower($method);
        if ('post' == $method) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($fields)) {
                $fields = http_build_query($fields);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        } else if ('put' == $method) {
            curl_setopt($ch, CURLOPT_PUT, true);
        }
        //curl_setopt($ch, CURLOPT_PROGRESS, true);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        //curl_setopt($ch, CURLOPT_MUTE, false);
        //设置curl超时秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //在HTTP请求中包含一个'user-agent'头的字符串
        if (strlen($userAgent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        }
        if (is_array($httpHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }
        try {
            $ret = curl_exec($ch);
            //返回最后一次的错误号
            if (curl_errno($ch)) {
                curl_close($ch);
                return [curl_error($ch), curl_errno($ch)];
            }
            curl_close($ch);
            if (!is_string($ret) || !strlen($ret)) {
                return false;
            }
            return $ret;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * POST请求
     * Author: vijay <1937832819@qq.com>
     * @param string $url 地址
     * @param array|string $fields 附带参数，可以是数组，也可以是字符串
     * @param string $userAgent 浏览器UA
     * @param string $httpHeaders header头部，数组形式
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool
     */
    public function post($url, $fields, $userAgent = '', $httpHeaders = '', $username = '', $password = '')
    {
        $ret = $this->execute('POST', $url, $fields, $userAgent, $httpHeaders, $username, $password);
        if (false === $ret) {
            return false;
        }
        if (is_array($ret)) {
            return false;
        }
        return $ret;
    }

    /**
     * GET请求
     * Author: vijay <1937832819@qq.com>
     * @param string $url 地址
     * @param string $userAgent 浏览器UA
     * @param array $httpHeaders header头部，数组形式
     * @param string $username 用户名
     * @param string $password 密码
     * @return array|bool|mixed
     */
    public function get($url, $userAgent = '', $httpHeaders = [], $username = '', $password = '')
    {
        $ret = $this->execute('GET', $url, "", $userAgent, $httpHeaders, $username, $password);
        if (false === $ret) {
            return false;
        }
        if (is_array($ret)) {
            return false;
        }
        return $ret;
    }

    /**
     *  curl支持 检测
     * Author: vijay <1937832819@qq.com>
     * @return bool|null|resource
     */
    public function create()
    {
        $ch = null;
        if (!function_exists('curl_init')) {
            return false;
        }
        $ch = curl_init();
        if (!is_resource($ch)) {
            return false;
        }
        return $ch;
    }
}