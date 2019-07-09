<?php
// +----------------------------------------------------------------------
// | Visitor.获取用户浏览信息
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.yuemeet.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: vijay <1937832819@qq.com> 2018-01-11
// +----------------------------------------------------------------------

namespace Vijay\Visitor;

class Visitor
{
    /**
     * 获取浏览器名称
     * Author: vijay <1937832819@qq.com>
     * @return string
     */
    public static function getBrowser()
    {
        $br = '';
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i', $br)) {
                $br = 'MSIE';
            } elseif (preg_match('/Firefox/i', $br)) {
                $br = 'Firefox';
            } elseif (preg_match('/Chrome/i', $br)) {
                $br = 'Chrome';
            } elseif (preg_match('/Safari/i', $br)) {
                $br = 'Safari';
            } elseif (preg_match('/Opera/i', $br)) {
                $br = 'Opera';
            } else {
                $br = 'Other';
            }
        }
        return $br;
    }

    /**
     * 获取客户端IP地址
     * Author: vijay <1937832819@qq.com>
     * @param int $type 返回类型 0 返回IP地址 1 返回IPV4地址数
     * @param bool $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public static function getIp($type = 0, $adv = true)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if ($ip !== null) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }

    /**
     * 获取阿里云通过SLB负载均衡取得客户端IP
     * Author: vijay <1937832819@qq.com>
     * @return mixed|string
     */
    public static function getForwardedForIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip[0]);
            return $ip ? $ip : '127.0.0.1';
        }
        return self::getIp();
    }

    /**
     * 获取字符类型
     * Author: vijay <1937832819@qq.com>
     * @return bool|string
     */
    public static function getLang()
    {
        $lang = '未知';
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang, 0, 5);
            if (preg_match('/zh-cn/i', $lang)) {
                $lang = '简体中文';
            } elseif (preg_match("/zh/i", $lang)) {
                $lang = '繁体中文';
            } else {
                $lang = 'English';
            }
        }
        return $lang;
    }

    /**
     * 获取操作系统名称
     * Author: vijay <1937832819@qq.com>
     * @return string
     */
    public static function getOs()
    {
        $OS = '未知';
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $OS = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i', $OS)) {
                $OS = 'Windows';
            } elseif (preg_match('/mac/i', $OS)) {
                $OS = 'MAC';
            } elseif (preg_match('/linux/i', $OS)) {
                $OS = 'Linux';
            } elseif (preg_match('/unix/i', $OS)) {
                $OS = 'Unix';
            } elseif (preg_match('/bsd/i', $OS)) {
                $OS = 'BSD';
            } else {
                $OS = '未知';
            }
        }
        return $OS;
    }

    /**
     * 获取访客物理地址
     * Author: vijay <1937832819@qq.com>
     * @return string
     */
    public static function getAddr()
    {
        $ip = self::getIp();
        if ($ip == '127.0.0.1') {
            $add = '未知地址';
        } else {
            //根据新浪api接口获取
            if ($ipadd = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip)) {
                $ipadd = json_decode($ipadd);
                if (!is_object($ipadd) or $ipadd->ret == '-1') {
                    $add = '未知地址';
                } elseif (is_object($ipadd) and $ipadd->ret <> '-1') {
                    $add = $ipadd->province . $ipadd->isp;
                } else {
                    $add = '未知地址';
                }
            } else {
                $add = '未知地址';
            }
        }
        return $add;
    }

    /**
     * 获取访客来源地址
     * Author: vijay <1937832819@qq.com>
     * @return string
     */
    public static function getReferer()
    {
        $Urs = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
        return $Urs;
    }

    /**
     * 获取当前访问地址
     * Author: vijay <1937832819@qq.com>
     * @return string
     */
    public static function getUrl()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }

    /**
     * 获取访客GET查询
     * Author: vijay <1937832819@qq.com>
     * @return string
     */
    public static function getQuery()
    {
        if (isset($_SERVER["QUERY_STRING"])) {
            $uquery = addslashes($_SERVER["QUERY_STRING"]);
        } else {
            $uquery = '';
        }
        return $uquery;
    }

    /**
     * 获取访客终端综合信息
     * Author: vijay <1937832819@qq.com>
     * @return string
     */
    public static function getAgent()
    {
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $uagent = addslashes($_SERVER["HTTP_USER_AGENT"]);
        } else {
            $uagent = "未知终端";
        }
        return $uagent;
    }

    /**
     * 是否手机访问
     * Author: vijay <1937832819@qq.com>
     * @return bool
     */
    public static function isMobileRequest()
    {
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
        $_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        //如果是命令行模式下，UA尝试使用request的
        if (IS_CLI) {
            $_SERVER['HTTP_USER_AGENT'] = request()->server('HTTP_USER_AGENT');
        }
        $mobile_browser = 0;
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }
        if ((isset($_SERVER['HTTP_ACCEPT'])) && (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false)) {
            $mobile_browser++;
        }
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            $mobile_browser++;
        }
        if (isset($_SERVER['HTTP_PROFILE'])) {
            $mobile_browser++;
        }
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = [
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda', 'xda-'
        ];
        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }
        if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) {
            $mobile_browser++;
        }
        // Pre-final check to reset everything if the user is on Windows
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false) {
            $mobile_browser = 0;
        }
        // But WP7 is also Windows, with a slightly different characteristic
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false) {
            $mobile_browser++;
        }
        if ($mobile_browser > 0) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否蜘蛛访问
     * Author: vijay <1937832819@qq.com>
     * @return bool
     */
    public static function isSpider()
    {
        $kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla|360Spider';
        $kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla|360User-agent';
        $spider = false;
        if (!strpos($_SERVER['HTTP_USER_AGENT'], 'http://') && preg_match("/($kw_browsers)/i", $_SERVER['HTTP_USER_AGENT'])) {
            $spider = false;
        } elseif (preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT'])) {
            $spider = true;
        }
        return $spider;
    }

    /**
     * 判断是否微信内置浏览器访问
     * Author: vijay <1937832819@qq.com>
     * @return bool
     */
    public static function isWeixinRequest()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($userAgent, 'MicroMessenger') || strpos(strtolower($userAgent), 'micromessenger')) {
            return true;
        }
        return false;
    }

    /**
     * 根据淘宝接口获取手机归属地
     * Author: vijay <1937832819@qq.com>
     * @param $phone
     * @return array|bool
     */
    public static function getMobileLocalByTaobao($phone)
    {
        $res = [];
        $url = "https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=$phone" . "&t=" . time();
        $contnet = iconv('GB2312', 'UTF-8', file_get_contents($url));
        preg_match_all("/(\w+):'(\W+|\w+)'/", $contnet, $matches);
        if (count($matches) == 3) {
            foreach ($matches[1] as $k => $v) {
                $res[$v] = $matches[2][$k];
            }
            if (isset($res['province'])) {
                $data = [
                    'phone' => $phone,
                    'area' => $res['province'],
                    'operator' => $res['catName'],
                    'area_operator' => $res['carrier'],
                ];
                return $data;
            }
        }
        return false;
    }

    /**
     * Author: vijay <1937832819@qq.com>
     * @param $phone
     * @return array|bool
     */
    public static function getMobileLocalByBaifubao($phone)
    {
        $url = "https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=$phone" . "&t=" . time();
        $contnet = file_get_contents($url);//file_get_contents乱码,改用curl
        $is = preg_match('/(\{\S+\})/i', $contnet, $matches);
        if ($is) {
            $res = json_decode($matches[0], true);
            if (isset($res['data']['area'])) {
                $data = [
                    'phone' => $phone,
                    'area' => $res['data']['area'],
                    'operator' => $res['data']['operator'],
                    'area_operator' => $res['data']['area_operator'],
                ];
                return $data;
            }
        }
        return false;
    }
}