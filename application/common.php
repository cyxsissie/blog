<?php

use think\Db;
use app\common\model\AuthRule as AuthRuleModel;
use app\common\Hook;
use think\Hook AS thinkHook;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use app\common\model\CrmResource;
use app\common\model\CrmSpreadResourceReceiveLog;
use think\Request;
use Aliyun\DySDKLite\sendSms as sms;
use think\Cache;
use think\Loader;
use think\Url;
use aliyunSms\api_demo\SmsDemo;
use org\Auth;

//输出文本log

/* *
 * MD5
 * 详细：MD5加密
 * 版本：3.3
 * 日期：2012-07-19
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
include_once 'view_funciton.php';

//获取手机号码归属地
function get_mobile_area($phone)
{
    $data = array();
    $area = Db::name('mobile_info')->where('mobile_no', substr($phone, 0, 7))->find();
    if ($area) {
        $data['prov'] = $area['province']; //省份
        $data['city'] = $area['city']; //城市
        $data['type'] = $area['operator_name']; //归属地
    } else {
        $data['prov'] = ''; //省份
        $data['city'] = ''; //城市
        $data['type'] = ''; //归属地
    }

    return $data;

}

function get_map_name($name)
{
    if (strpos($name, '省') !== false || strpos($name, '市') !== false) {
        $res = mb_substr($name, 0, (mb_strlen($name, 'utf-8') - 1), 'utf-8');
    } else {
        if (mb_strpos($name, '内蒙古') !== false) {
            $res = mb_substr($name, 0, 3, 'utf-8');
        } else {
            $res = mb_substr($name, 0, 2, 'utf-8');
        }
    }
    return $res;
}

//1提现处理SMS_133005796 txcash
// 2直播临近 SMS_133005791 livetitle
//3提现提交 SMS_132990837 txcash
//4直播间创建失败 SMS_133005750 livetitle
// 5直播间创建成功 SMS_133005749 livetitle
//6 身份开通成功 SMS_133000684
// 7 绑定成功 SMS_132995699
// 8短信验证 SMS_132995697 code
// 9 预约临近 SMS_133005690 livetitle
function sendSms($type, $phone, $val = null)
{
    $s = new  sms();
    $res = $s->sendType($type, $phone, $val);

}

function check_code($phone, $code)
{
    Db::name('code')->where($phone, $code)->find();
}

//获取ip地址
function get_client_ip($proxy_override = false)
{
    if ($proxy_override) {
        /* 优先从代理那获取地址或者 HTTP_CLIENT_IP 没有值 */
        $ip = empty($_SERVER["HTTP_X_FORWARDED_FOR"]) ? (empty($_SERVER["HTTP_CLIENT_IP"]) ? NULL : $_SERVER["HTTP_CLIENT_IP"]) : $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
        /* 取 HTTP_CLIENT_IP, 虽然这个值可以被伪造, 但被伪造之后 NS 会把客户端真实的 IP 附加在后面 */
        $ip = empty($_SERVER["HTTP_CLIENT_IP"]) ? NULL : $_SERVER["HTTP_CLIENT_IP"];
    }

    if (empty($ip)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    /* 真实的IP在以逗号分隔的最后一个, 当然如果没用代理, 没伪造IP, 就没有逗号分离的IP */
    if ($p = strrpos($ip, ",")) {
        $ip = substr($ip, $p + 1);
    }

    return trim($ip);

}

function getUpNode($arr, $id)
{
    static $top;
    //如果为0表示，没有父级了
    if ($id == 0) return $top;

    foreach ($arr as $k => $v) {
        if ($v['id'] == $id) {
            if ($v['pid'] != 0) {
                $top = $v['pid'];
                getUpNode($arr, $v['pid']);
            } else {
                return $v['id'];
            }

        }
    }
    $c = $top;
    unset($top);
    return $c;
}

function getAllNode($arr, $pid)
{
    static $all = [];
    foreach ($arr as $k => $v) {
        if ($pid == $v['id']) {
            $all[] = $v;
        }
        if ($v['pid'] == $pid) {
            getAllNode($arr, $v['id']);
        }
    }
    $c = $all;
    unset($all);
    return $c;
}

function data_echo($data, $code = 1, $msg = '操作成功')
{
    echo json_encode(array('code' => $code, 'data' => $data, 'msg' => $msg), JSON_UNESCAPED_UNICODE);
    exit;
}

function c_dump($c)
{
    echo "<pre>";
    print_r($c);
    echo "<pre>";
}

function xmlToArray($xml)
{

    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);

    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

    $val = json_decode(json_encode($xmlstring), true);

    return $val;
}

/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * @param $key 私钥
 * return 签名结果
 */
function put_log_file($data, $name = '')
{
    if (empty($name)) {
        $date = date('Ysdhis', time());
    } else {
        $date = date('Ysdhis', time()) . '_' . $name;
    }

    $file = fopen(ROOT_PATH . 'public/log/' . $date . ".txt", 'w');
    fwrite($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    fclose($file);
}

function md5Sign($prestr, $key)
{
    $prestr = $prestr . $key;
    return md5($prestr);
}

/**
 * 验证签名
 * @param $prestr 需要签名的字符串
 * @param $sign 签名结果
 * @param $key 私钥
 * return 签名结果
 */
function md5Verify($prestr, $sign, $key)
{
    $prestr = $prestr . $key;
    $mysgin = md5($prestr);

    if ($mysgin == $sign) {
        return true;
    } else {
        return false;
    }
}

/* *
 * 支付宝接口公用函数
* 详细：该类是请求、通知返回两个文件所调用的公用函数核心处理文件
* 版本：3.3
* 日期：2012-07-19
* 说明：
* 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
* 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstring($para)
{
    $arg = "";
    while (list ($key, $val) = each($para)) {
        $arg .= $key . "=" . $val . "&";
    }
    //去掉最后一个&字符
    $arg = substr($arg, 0, count($arg) - 2);

    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $arg = stripslashes($arg);
    }

    return $arg;
}

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstringUrlencode($para)
{
    $arg = "";
    while (list ($key, $val) = each($para)) {
        $arg .= $key . "=" . urlencode($val) . "&";
    }
    //去掉最后一个&字符
    $arg = substr($arg, 0, count($arg) - 2);

    //如果存在转义字符，那么去掉转义
    if (get_magic_quotes_gpc()) {
        $arg = stripslashes($arg);
    }

    return $arg;
}

/**
 * 除去数组中的空值和签名参数
 * @param $para 签名参数组
 * return 去掉空值与签名参数后的新签名参数组
 */
function paraFilter($para)
{
    $para_filter = array();
    while (list ($key, $val) = each($para)) {
        if ($key == "sign" || $key == "sign_type" || $val == "") continue;
        else    $para_filter[$key] = $para[$key];
    }
    return $para_filter;
}

/**
 * 对数组排序
 * @param $para 排序前的数组
 * return 排序后的数组
 */
function argSort($para)
{
    ksort($para);
    reset($para);
    return $para;
}

/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * 注意：服务器需要开通fopen配置
 * @param $word 要写入日志里的文本内容 默认值：空值
 */
function logResult($word = '')
{
    $fp = fopen("log.txt", "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * 远程获取数据，POST模式
 * 注意：
 * @param $url 指定URL完整路径地址
 * @param $cacert_url 指定当前工作目录绝对路径
 * return 远程输出的数据
 */
function request_post($url = '', $param = '')
{
    if (empty($url) || empty($param)) {
        return false;
    }
    $postUrl = $url;
    $curlPost = $param;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);

    return $data;
}

/**
 * 远程获取数据，GET模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * @param $cacert_url 指定当前工作目录绝对路径
 * return 远程输出的数据
 */
function getHttpResponseGET($url, $cacert_url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);//证书地址
    $responseText = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $responseText;
}

/**
 * 实现多种字符编码方式
 * @param $input 需要编码的字符串
 * @param $_output_charset 输出的编码格式
 * @param $_input_charset 输入的编码格式
 * return 编码后的字符串
 */
function charsetEncode($input, $_output_charset, $_input_charset)
{
    $output = "";
    if (!isset($_output_charset)) $_output_charset = $_input_charset;
    if ($_input_charset == $_output_charset || $input == null) {
        $output = $input;
    } elseif (function_exists("mb_convert_encoding")) {
        $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
    } elseif (function_exists("iconv")) {
        $output = iconv($_input_charset, $_output_charset, $input);
    } else die("sorry, you have no libs support for charset change.");
    return $output;
}

/**
 * 实现多种字符解码方式
 * @param $input 需要解码的字符串
 * @param $_output_charset 输出的解码格式
 * @param $_input_charset 输入的解码格式
 * return 解码后的字符串
 */
function charsetDecode($input, $_input_charset, $_output_charset)
{
    $output = "";
    if (!isset($_input_charset)) $_input_charset = $_input_charset;
    if ($_input_charset == $_output_charset || $input == null) {
        $output = $input;
    } elseif (function_exists("mb_convert_encoding")) {
        $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
    } elseif (function_exists("iconv")) {
        $output = iconv($_input_charset, $_output_charset, $input);
    } else die("sorry, you have no libs support for charset changes.");
    return $output;
}

function do_post($url, $data)
{
    $header = array(
        "Content-Type" => "application/json;charset=UTF-8"
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);

    $ret = curl_exec($ch);
    var_dump(curl_error($ch));
    curl_close($ch);
    return $ret;
}

function get_url_contents($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function getgradenamebyid($id)
{
    $name = Db::name('usergrade')->where('id', $id)->value('name');
    return $name;
}

function url($url = '', $vars = '', $suffix = true, $domain = false)
{


    if (strtolower(request()->module()) == 'index' && !config('url_route_on')) {
        Url::root(getbaseurl() . 'api.php');
    } else if (strtolower(request()->module()) == 'index' && config('url_route_on')) {
        //Url::root(getbaseurl().'/');
    }

    return 'http://' . $_SERVER['HTTP_HOST'] . Url::build($url, $vars, $suffix, $domain);
}

function routerurl($url, $arr = array())
{
    if (empty($arr)) {
        $str = url($url);
    } else {
        $str = url($url, $arr);
    }


    $str = str_replace('admin.php', 'api.php', $str);

    return $str;
}

function remove_xss($html)
{
    $html = htmlspecialchars_decode($html);
    preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

    $searchs[] = '<';
    $replaces[] = '&lt;';
    $searchs[] = '>';
    $replaces[] = '&gt;';

    if ($ms[1]) {
        $allowtags = 'attach|img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|strike|pre|code|embed';
        $ms[1] = array_unique($ms[1]);
        foreach ($ms[1] as $value) {
            $searchs[] = "&lt;" . $value . "&gt;";

            $value = str_replace('&amp;', '_uch_tmp_str_', $value);
            $value = string_htmlspecialchars($value);
            $value = str_replace('_uch_tmp_str_', '&amp;', $value);

            $value = str_replace(array('\\', '/*'), array('.', '/.'), $value);
            $skipkeys = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate',
                'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange',
                'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick',
                'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate',
                'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete',
                'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel',
                'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart',
                'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop',
                'onsubmit', 'onunload', 'javascript', 'script', 'eval', 'behaviour', 'expression');
            $skipstr = implode('|', $skipkeys);
            $value = preg_replace(array("/($skipstr)/i"), '.', $value);
            if (!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
                $value = '';
            }
            $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
        }
    }
    $html = str_replace($searchs, $replaces, $html);
    $html = htmlspecialchars($html);
    return $html;
}

function string_htmlspecialchars($string, $flags = null)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = string_htmlspecialchars($val, $flags);
        }
    } else {
        if ($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
            if (strpos($string, '&amp;#') !== false) {
                $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (!defined('CHARSET') || (strtolower(CHARSET) == 'utf-8')) {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }

    return $string;
}

function string_remove_xss($val)
{
    $val = htmlspecialchars_decode($val);
    $val = strip_tags($val, '<img><attach><u><p><b><i><a><strike><pre><code><font><blockquote><span><ul><li><table><tbody><tr><td><ol><iframe><embed>');

    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

    return $val;

    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';


    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val);
        $val = preg_replace('/(�{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val);
    }

    $ra1 = array('embed', 'iframe', 'frame', 'ilayer', 'layer', 'javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'object', 'frameset', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(�{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    $val = htmlspecialchars($val);
    return $val;
}

function point_note($score, $uid, $controller, $pointid = 0)
{


    if ($score != 0) {

        if ($controller == 'login') {

            $time = time();
            $maptime['add_time'] = array('gt', $time - 24 * 60 * 60);
            $maptime['uid'] = $uid;
            $maptime['controller'] = 'login';

            $count = Db::name('point_note')->where($maptime)->count();
            if ($count > 0) {

            } else {
                Db::name('user')->where('id', $uid)->setInc('point', $score);
                $data['uid'] = $uid;
                $data['add_time'] = time();
                $data['controller'] = $controller;
                $data['score'] = $score;
                $data['pointid'] = $pointid;
                Db::name('point_note')->insert($data);
                //根据用户积分提升或降低用户等级

                $data = Db::name('user')->where('id', $uid)->find();


                $map['score'] = array('elt', $data['point']);

                $res = Db::name('usergrade')->where($map)->order('score desc')->limit(1)->value('id');

                if (!empty($res) && $res != $data['grades']) {
                    Db::name('user')->where('id', $uid)->setField('grades', $res);
                }
            }
        } else {

            Db::name('user')->where('id', $uid)->setInc('point', $score);
            $data['uid'] = $uid;
            $data['add_time'] = time();
            $data['controller'] = $controller;
            $data['score'] = $score;
            $data['pointid'] = $pointid;


            Db::name('point_note')->insert($data);
            //根据用户积分提升或降低用户等级

            $data = Db::name('user')->where('id', $uid)->find();


            $map['score'] = array('elt', $data['point']);

            $res = Db::name('usergrade')->where($map)->order('score desc')->limit(1)->value('id');

            if (!empty($res) && $res != $data['grades']) {
                Db::name('user')->where('id', $uid)->setField('grades', $res);
            }


        }


    }


    return;


}

function getpoint($uid, $controller, $pointid)
{
    $map['uid'] = $uid;
    $map['pointid'] = $pointid;
    $map['controller'] = $controller;


    $res = Db::name('Point_note')->where($map)->value('score');
    return $res;
}


/**
 * 加载模型
 */
function load_model($name = '', $module = '')
{

    // 回溯跟踪
    $backtrace_array = debug_backtrace(false, 1);

    // 调用者目录名称
    $current_directory_name = basename(dirname($backtrace_array[0]['file']));

    // 设置模块
    !empty($module) && $name = $module . '/' . $name;

    // 返回的对象
    $return_object = null;

    // 加载模型规则
    switch ($current_directory_name) {

        case LAYER_CONTROLLER_NAME :
            $return_object = model($name, LAYER_LOGIC_NAME);
            break;
        case LAYER_LOGIC_NAME      :
            $return_object = model($name, LAYER_MODEL_NAME);
            break;
        case LAYER_SERVICE_NAME    :
            $return_object = model($name, LAYER_MODEL_NAME);
            break;
        case LAYER_MODEL_NAME      :
            $return_object = model($name, LAYER_MODEL_NAME);
            break;
        default                    :
            $return_object = model($name, LAYER_LOGIC_NAME);
            break;
    }

    return $return_object;
}

/**
 * 获取插件类的类名
 * @param $name 插件名
 * @param string $type 返回命名空间类型
 * @param string $class 当前类名
 * @return string
 */
function get_addon_class($name = '', $class = null)
{

    $name = \think\Loader::parseName($name);

    $class = \think\Loader::parseName(is_null($class) ? $name : $class, 0);

    return $namespace = "addons\\" . $name . "\\" . $class;
}


//function D($name='',$layer='') {
//
//  if(empty($name)) return new Think\Model;
//  static $_model  =   array();
//  $layer          =   $layer? :'model';
//
//
//
//
//  if(isset($_model[$name]))
//      return $_model[$name];
//  $class          =   parse_res_name($name,$layer);
//
//  if(class_exists($class)) {
//      $model      =   new $class(basename($name));
//  }elseif(false === strpos($name,'/')){
//      // 自动加载公共模块下面的模型
//
//          $class      =   '\\common\\'.$layer.'\\'.$name;
//
//      $model      =   class_exists($class)? new $class($name) : new Think\Model($name);
//  }else {
//      Think\Log::record('D方法实例化没找到模型类'.$class,Think\Log::NOTICE);
//      $model      =   new Think\Model(basename($name));
//  }
//  $_model[$name.$layer]  =  $model;
//
//  return $model;
//}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type = 0)
{
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 解析资源地址并导入类库文件
 * 例如 module/controller addon://module/behavior
 * @param string $name 资源地址 格式：[扩展://][模块/]资源名
 * @param string $layer 分层名称
 * @return string
 */
function parse_res_name($name, $layer, $level = 1)
{
    if (strpos($name, '://')) {// 指定扩展资源
        list($extend, $name) = explode('://', $name);
    } else {
        $extend = '';
    }
    if (strpos($name, '/') && substr_count($name, '/') >= $level) { // 指定模块
        list($module, $name) = explode('/', $name, 2);
    } else {
        $module = Request::instance()->module();
    }
    $array = explode('/', $name);
    $class = $module . '\\' . $layer;

    foreach ($array as $name) {
        $class .= '\\' . parse_name($name, 1);
    }


    // 导入资源类库
    if ($extend) { // 扩展资源
        $class = $extend . '\\' . $class;
    }

    return $class;//.$layer;
}

function AURL($name, $layer = '', $level = 0)
{
    static $_action = array();
    $layer = $layer ?: 'Controller';

    $class = parse_res_name($name, $layer);

    return $class;
}

function A($name, $layer = '', $level = 0)
{
    static $_action = array();
    $layer = $layer ?: 'controller';


    if (isset($_action[$name . $layer]))
        return $_action[$name . $layer];

    $class = parse_res_name($name, $layer);

    if (class_exists($class)) {
        $action = new $class();
        $_action[$name . $layer] = $action;


        return $action;
    } else {
        return false;
    }
}

function get_cover($cover_id, $field = null)
{
    if (empty($cover_id)) {
        return false;
    }
    $picture = Db::name('file')->find($cover_id);


    return WEB_URL . $picture[$field];
}


function gethook($controller, $name)
{

    Hook::call($controller, $name);

}

function int_to_string(&$data, $map = array('status' => array(1 => '正常', -1 => '删除', 0 => '禁用', 2 => '未审核', 3 => '草稿')))
{
    if ($data === false || $data === null) {
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str 要分割的字符串
 * @param  string $glue 分割符
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function str2arr($str, $glue = ',')
{
    return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array $arr 要连接的数组
 * @param  string $glue 分割符
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function arr2str($arr, $glue = ',')
{
    return implode($glue, $arr);
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc')
{

    if (is_array($list)) {
        $refer = array();
        $resultSet = array();

        foreach ($list as $i => $data) {


            $refer[$i] = $data[$field];

        }


        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = array(), $n = false, $field = '')
{

    if ($n) {
        $m = \Think\Hook::listen($hook, $params);
        if (!empty($m)) {
            return $m[0];
        } else {
            return $params[$field];
        }

    } else {
        \Think\Hook::listen($hook, $params);
    }


}


/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name)
{
    $class = get_addon_class($name);
    if (class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    } else {
        return array();
    }
}

function addonurl($name, $action)
{


    return get_addon_class($name) . '\\' . $action;


}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array(), $json = false)
{
    $url = parse_url($url);
    $addons = $url['scheme'];
    $controller = $url['host'];
    $action = $url['path'];

    /* 基础参数 */
    $params_array = array(
        'addon_name' => $addons,
        'controller_name' => $controller,
        'action_name' => substr($action, 1),
        'json' => $json
    );

    $params = array_merge($params_array, $param); //添加额外参数

    return url('addons/execute', $params);


}


function userhead($userhead)
{
    if ($userhead == '') {
        return '/public/images/default.png';
    } else {
        return $userhead;
    }
}

function getweburl($controller, $action, $name = '', $value = '')
{
    if (Cache::has('site_config')) {
        $site_config = Cache::get('site_config');
    } else {
        $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
        Cache::set('site_config', $site_config);
    }

    if ($site_config['site_wjt'] == 1) {
        if ($name != '') {
            $arr = array($name => $value);
            $url = url($controller . "/" . $action, $arr);
        } else {
            $url = url($controller . "/" . $action);
        }

    } else {
        if ($name != '') {
            $arr = array($name => $value);
            $url = url($controller . "/" . $action, $arr);
        } else {
            $url = url($controller . "/" . $action);
        }
    }

    return $url;

}

function sendmail($switch)
{

    ignore_user_abort();//关闭浏览器后，继续执行php代码
    set_time_limit(0);//程序执行时间无限制
    $sleep_time = 5;//多长时间执行一次

    while ($switch) {

        $msg = date("Y-m-d H:i:s") . $switch;
        file_put_contents("log.log", $msg, FILE_APPEND);//记录日志
        sleep($sleep_time);//等待时间，进行下一次操作。
    }
    exit();

}

function getbaseurl()
{
    $baseUrl = str_replace('\\', '', dirname($_SERVER['SCRIPT_NAME']));
    $baseUrl = empty($baseUrl) ? '/' : '/' . trim($baseUrl, '/') . '/';
    return $baseUrl;
}

function showyourdomain()
{
    $domain = $_SERVER['HTTP_HOST'];
    $par = time();

    $url = 'http://www.eadmin.top/api.php/Index/savebanquan/' . '?url=' . $domain;
    $fp = @fsockopen("www.eadmin.top", 80, $errno, $errstr, 3);
    $out = "POST " . $url . " HTTP/1.1\r\n";
    $out .= "Host: typecho.org\r\n";
    //$out.="User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13"."\r\n";
    $out .= "Content-type: application/x-www-form-urlencoded\r\n";
    //$out.="PHPSESSID=".$sessionid."0\r\n";
    $out .= "Content-Length: " . strlen($par) . "\r\n";
    $out .= "Connection: close\r\n\r\n";
    $out .= $par . "\r\n\r\n";
    if ($fp) {
        fputs($fp, $out);
        fclose($fp);
    }


}

function asyn_sendmail($data)
{
    $query = http_build_query($data);
    $request = Request::instance();
    //$baseUrl = str_replace('\\','',dirname($_SERVER['SCRIPT_NAME']));
    //$baseUrl = empty($baseUrl) ? '/' : '/'.trim($baseUrl,'/').'/';
    $domain = $_SERVER['HTTP_HOST'];

    $url = 'http://' . $domain . getbaseurl() . 'api.php/Index/send_mail/' . '?' . $query;


    $par = time();
    //$sessionid=session_id();


    $fp = stream_socket_client("tcp://" . $domain . ":80", $errno, $errstr, 3);
    $out = "POST " . $url . " HTTP/1.1\r\n";
    $out .= "Host: typecho.org\r\n";
    //$out.="User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13"."\r\n";
    $out .= "Content-type: application/x-www-form-urlencoded\r\n";
    //$out.="PHPSESSID=".$sessionid."0\r\n";
    $out .= "Content-Length: " . strlen($par) . "\r\n";
    $out .= "Connection: close\r\n\r\n";
    $out .= $par . "\r\n\r\n";
    fputs($fp, $out);


    /*

    while (!feof($fp))
    {
        echo fgets($fp, 1280);
    }   */
    fclose($fp);


}

/*  $domain=$_SERVER['HTTP_HOST'];

    $url=url('Index/send_mail',array('email'=>$email));

    $par=time();

    $header="GET $url HTTP/1.0\r\n";

    $header.="Content-Type: application/x-www-form-urlencoded\r\n";

    $header.="Content-Length: ".strlen($par)."\r\n\r\n";

    $fp=stream_socket_client($domain.':80',$errno,$errstr,30);

    fputs($fp,$header.$par);

    echo fputs($fp,$header.$par);
    fclose($fp); */


/**
 * 用常规方式发送邮件。
 */
function send_mail_local($to = '', $subject = '', $body = '', $from_name = '', $attachment = null, $reply_email = '', $reply_name = '')
{

    $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
    $site_config = unserialize($site_config['value']);


    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码


    // 服务器设置
    $mail->SMTPDebug = 2;                                    // 开启Debug
    $mail->isSMTP();                                        // 使用SMTP

    $mail->Host = $site_config['smtp_server']; // SMTP 服务器
    $mail->Port = $site_config['smtp_port']; // SMTP服务器的端口号
    $mail->Username = $site_config['smtp_user']; // SMTP服务器用户名
    $mail->Password = $site_config['smtp_pass']; // SMTP服务器密码


    $mail->SMTPAuth = true;                                    // 开启SMTP验证


    $mail->SMTPSecure = 'tls';                                // 开启TLS 可选

    // 收件人
//  $mail->setFrom('17309981908@163.com', 'SandBoxCn');            // 来自
//  $mail->addAddress('176314141@qq.com', 'George.Haung');        // 添加一个收件人
    //$mail->addAddress('176314141@qq.com');                        // 可以只传邮箱地址

    if ($to == '') {
        $to = $site_config['smtp_cs'];//邮件地址为空时，默认使用后台默认邮件测试地址
    }
    if ($from_name == '') {
        $from_name = $site_config['site_title'];
        //发送者名称为空时，默认使用网站名称
    }
    if ($subject == '') {
        $subject = $site_config['seo_title']; //邮件主题为空时，默认使用网站标题
    }
    if ($body == '') {
        $body = $site_config['seo_description'];//邮件内容为空时，默认使用网站描述
    }

    $from_email = $site_config['smtp_user'];

    $reply_email = '';
    $reply_name = '';

    $mail->SetFrom($from_email, $from_name);
    $replyEmail = $reply_email ? $reply_email : $from_email;
    $replyName = $reply_name ? $reply_name : $from_name;
    //$mail->addReplyTo('173099819081@163.com', 'SandBoxCn');        // 回复地址,回复时显示在地址栏中
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body); //解析
    $mail->AddAddress($to, $from_name);
    /*  if (is_array($attachment)) { // 添加附件
     foreach ($attachment as $file) {
     is_file($file) && $mail->AddAttachment($file);
     }
     } */
    // 附件
    //$mail->addAttachment('/var/tmp/file.tar.gz');                // 添加附件
//  $mail->addAttachment('/tmp/image.jpg', 'new.jpg');            // 可以设定名字


    return $mail->Send() ? true : $mail->ErrorInfo; //返回错误信息


}

function iconurl($icon, $type)
{

    if ($icon != 0 && $icon != '') {

        if ($type == 2) {

            return "<i class='iconfont icon-" . $icon . "'></i>";
        } else {

            return "<img src='" . $icon . "' />";
        }
    } else {

        return "空";

    }
}

function getcommentbyid($id)
{

    $children = Db::name('comment')->where(['id' => $id])->find();
    //此时查询都是前台会员

    $content = getusernamebyid($children['uid']) . ':' . htmlspecialchars_decode($children['content']);

    return $content;


}

function getuserinfobyid($uid)
{
    if ($uid == 0) {
        return '所有人';
    } else {
        $children = Db::name('user')->where(['id' => $uid])->find();
        //此时查询都是前台会员


        return $children;

    }


}

function getusernamebyid($uid)
{
    if ($uid == 0) {
        return '所有人';
    } else {
        $children = Db::name('user')->where(['id' => $uid])->find();
        if (empty($children)) {

            $children = Db::name('admin_user')->where(['id' => $uid])->find();
            return $children['username'];
        } else {
            return $children['username'];
        }

    }


}

function getforumbyid($id)
{
    if ($id == 0) {
        return '无此帖';
    } else {
        $children = Db::name('forum')->where(['id' => $id])->find();
        if (empty($children)) {


            return '无此帖';
        } else {
            return $children['title'];
        }

    }


}

function friendlyDate($sTime, $type = 'normal', $alt = 'false')
{
    if (!$sTime)
        return '';
    //sTime=源时间，cTime=当前时间，dTime=时间差
    $cTime = time();
    $dTime = $cTime - $sTime;
    $dDay = intval(date("z", $cTime)) - intval(date("z", $sTime));
    //$dDay     =   intval($dTime/3600/24);
    $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
    //normal：n秒前，n分钟前，n小时前，日期
    if ($type == 'normal') {
        if ($dTime < 60) {
            if ($dTime < 10) {
                return '刚刚';    //by yangjs
            } else {
                return intval(floor($dTime / 10) * 10) . "秒前";
            }
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . "分钟前";
            //今天的数据.年份相同.日期相同.
        } elseif ($dYear == 0 && $dDay == 0) {
            //return intval($dTime/3600)."小时前";
            return '今天' . date('H:i', $sTime);
        } elseif ($dYear == 0) {
            return date("m月d日 H:i", $sTime);
        } else {
            return date("Y-m-d", $sTime);
        }
    } elseif ($type == 'mohu') {
        if ($dTime < 60) {
            return $dTime . "秒前";
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . "分钟前";
        } elseif ($dTime >= 3600 && $dDay == 0) {
            return intval($dTime / 3600) . "小时前";
        } elseif ($dDay > 0 && $dDay <= 7) {
            return intval($dDay) . "天前";
        } elseif ($dDay > 7 && $dDay <= 30) {
            return intval($dDay / 7) . '周前';
        } elseif ($dDay > 30) {
            return intval($dDay / 30) . '个月前';
        }
        //full: Y-m-d , H:i:s
    } elseif ($type == 'full') {
        return date("Y-m-d , H:i:s", $sTime);
    } elseif ($type == 'ymd') {
        return date("Y-m-d", $sTime);
    } else {
        if ($dTime < 60) {
            return $dTime . "秒前";
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . "分钟前";
        } elseif ($dTime >= 3600 && $dDay == 0) {
            return intval($dTime / 3600) . "小时前";
        } elseif ($dYear == 0) {
            return date("Y-m-d H:i:s", $sTime);
        } else {
            return date("Y-m-d H:i:s", $sTime);
        }
    }
}

/*
 * 来判断导航链接内部外部从而生成新链接
 *
 *
 * */
function getnavlink($link, $sid)
{
    if ($sid == 1) {

        $arr = explode(',', $link);

        $url = $arr[0];

        array_shift($arr);
        if (empty($arr)) {

            $link = routerurl($url);

        } else {
            $m = 1;
            $queue = array();
            foreach ($arr as $k => $v) {

                if ($m == 1) {
                    $n = $v;
                    $m = 2;

                } else {
                    $b = $v;
                    $queue[$n] = $b;
                    $m = 1;
                }
            }
            if (empty($queue)) {
                $link = routerurl($url);
            } else {
                $link = routerurl($url, $queue);
            }


        }


    }

    return $link;
}


function dir_create($path, $mode = 0777)
{
    if (is_dir($path)) {
        return TRUE;
    }
    $ftp_enable = 0;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir)) {
            continue;
        }
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

function format_bytes($size, $delimiter = '')
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    for ($i = 0; $size >= 1024 && $i < 6; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

//用于生成用户密码的随机字符
function generate_password($length = 8)
{
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * 获取分类所有子分类
 * @param int $cid 分类ID
 * @return array|bool
 */
function get_category_children($cid)
{
    if (empty($cid)) {
        return false;
    }

    $children = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->select();

    return array2tree($children);
}

/**
 * 根据分类ID获取文章列表（包括子分类）
 * @param int $cid 分类ID
 * @param int $limit 显示条数
 * @param array $where 查询条件
 * @param array $order 排序
 * @param array $filed 查询字段
 * @return bool|false|PDOStatement|string|\think\Collection
 */
function get_articles_by_cid($cid, $limit = 10, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->limit($limit)->select();

    return $article_list;
}

/**
 * 根据分类ID获取文章列表，带分页（包括子分类）
 * @param int $cid 分类ID
 * @param int $page_size 每页显示条数
 * @param array $where 查询条件
 * @param array $order 排序
 * @param array $filed 查询字段
 * @return bool|\think\paginator\Collection
 */
function get_articles_by_cid_paged($cid, $page_size = 15, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->paginate($page_size);

    return $article_list;
}

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int $pid
 * @param int $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    if (empty($array)) {
        return [];
    }
    static $list = [];

    foreach ($array as $v) {


        if ($v['pid'] == $pid) {

            $v['level'] = $level;
            $v['level'] = $level;
            $list[] = $v;

            array2level($array, $v['id'], $level + 1);
        }
    }

    return $list;
}

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int $pid
 * @param int $level
 * @return array
 */
function array3level($array, $pid = 0, $level = 1)
{
    if (empty($array)) {
        return [];
    }
    static $list1 = [];

    foreach ($array as $v) {


        if ($v['pid'] == $pid) {

            $v['level'] = $level;
            $list1[] = $v;

            array3level($array, $v['id'], $level + 1);
        }
    }

    return $list1;
}

/**
 * 构建层级（树状）数组
 * @param array $array 要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name 父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }

    return $tree;
}

/**
 * 子元素计数器
 * @param array $array
 * @param int $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }

    return $counter;
}

/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name]))
                $item[$child_key_name] = [];
            $item[$child_key_name][] = $child;
        }
    }

    return $parent;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name)
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function is_mobile()
{
    static $is_mobile;

    if (isset($is_mobile)) {
        return $is_mobile;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
    ) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

/**
 * 手机号格式检查
 * @param string $mobile
 * @return bool
 */
function check_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    $reg = '#^13[\d]{9}$|^14[\d]{9}$|^15[^4]{1}\d{8}$|^16[\d]{9}|^17[\d]{9}|^18[\d]{9}|^19[\d]{9}$#';

    return preg_match($reg, $mobile) ? true : false;
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{


    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    //截取内容时去掉图片，仅保留文字


    return $suffix ? $slice . '...' : $slice;
}

function clearcontent($content)
{

    $content = htmlspecialchars_decode($content);


    $content = preg_replace("/&lt;/i", "<", $content);


    $content = preg_replace("/&gt;/i", ">", $content);

    $content = preg_replace("/&amp;/i", "&", $content);


    $content = strip_tags($content);
    return $content;
}


function clearHtml($content)
{
    $content = preg_replace("/<a[^>]*>/i", "", $content);
    $content = preg_replace("/<\/a>/i", "", $content);
    $content = preg_replace("/<p>/i", "", $content);
    $content = preg_replace("/<\/p>/i", "", $content);
    $content = preg_replace("/<div[^>]*>/i", "", $content);
    $content = preg_replace("/<\/div>/i", "", $content);
    $content = preg_replace("/<!--[^>]*-->/i", "", $content);//注释内容
    $content = preg_replace("/style=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/class=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/id=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/lang=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/width=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/height=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/border=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/face=.+?['|\"]/i", '', $content);//去除样式
    $content = preg_replace("/face=.+?['|\"]/", '', $content);//去除样式 只允许小写 正则匹配没有带 i 参数
    return $content;
}

function cutstr_html($string, $length = 0, $ellipsis = '…')
{

    $string = strip_tags($string);
    $string = preg_replace("/\n/is", '', $string);
    $string = preg_replace("/\r\n/is", '', $string);

    $string = preg_replace('/ |　/is', '', $string);
    $string = preg_replace('/&nbsp;/is', '', $string);
    $string = preg_replace('/&emsp;/is', '', $string);

    if (mb_strlen($string, 'utf-8') <= $length) {
        $ellipsis = '';
    }
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $string);
    if (is_array($string) && !empty($string[0])) {
        if (is_numeric($length) && $length) {


            $string = join('', array_slice($string[0], 0, $length)) . $ellipsis;
        } else {
            $string = implode('', $string[0]);
        }
    } else {
        $string = '';
    }
    return $string;
}


/**
 * Export Excel
 * @param $expTitle    文件名
 * @param $expCellName  array  每一列的标题
 * @param $expTableData array  导出的表中数据
 */
function exportExcel($expTitle, $expCellName, $expTableData)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle); //文件名称
    $fileName = $xlsTitle; //or $xlsTitle 文件名称可根据自己情况设定
    $cellNum = count($expCellName);
    $dataNum = count($expTableData);
    Vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new PHPExcel();
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

    // $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1'); //合并单元格
    // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));

    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '1', $expCellName[$i][1]);
    }
    // Miscellaneous glyphs, UTF-8
    for ($i = 0; $i < $dataNum; $i++) {
        for ($j = 0; $j < $cellNum; $j++) {
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
        }
    }

    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save('php://output');
    exit;
}

//导出成csv文件
function exportData($filename, $columns, $data)
{
    set_time_limit(0);
    ini_set('memory_limit', '1024M');
    // $columns = [            '列名1', '列名2', '列名3'      //需要几列，定义好列名
    // ];
    //设置好告诉浏览器要下载excel文件的headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    $fp = fopen('php://output', 'a');//打开output流
    mb_convert_variables('GBK', 'UTF-8', $columns);
    fputcsv($fp, $columns);//将数据格式化为CSV格式并写入到output流中

    // 计数器
    $cnt = 0;
    $limit = 10000;  // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小

    // 逐行取出数据，不浪费内存
    $count = count($data);

    for ($i = 0; $i < $count; $i++) {
        $cnt++;
        if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
            ob_flush();
            flush();
            $cnt = 0;
        }
        $row = $data[$i];
        foreach ($row as $j => $v) {
            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $v) > 0) {
                $row[$j] = iconv('utf-8', 'gbk', $v);
            }
        }
        fputcsv($fp, $row);
    }

    fclose($fp);
    exit();
}

// 输出CSV
function ouputCsv($filename, $data)
{
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
    header('Cache-Control: max-age=0');

    $fp = fopen('php://output', 'a');

    // 计数器
    $cnt = 0;
    $limit = 10000;  // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小

    // 逐行取出数据，不浪费内存
    $count = count($data);

    for ($i = 0; $i < $count; $i++) {
        $cnt++;
        if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
            ob_flush();
            flush();
            $cnt = 0;
        }
        $row = $data[$i];
        foreach ($row as $j => $v) {
            // $row[$j] = iconv('utf-8', 'gbk', $v);
            $row[$j] = charsetEncode($v, 'gbk', 'utf-8'); //转码
        }
        fputcsv($fp, $row);
    }

    exit;
}

/**
 *  数据导入
 * @param string $file excel文件
 * @param string $sheet
 * @return string   返回解析数据
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Reader_Exception
 */
function importExcel($file = '', $sheet = 0)
{
    $file = iconv("utf-8", "gb2312", $file);   //转码
    if (empty($file) OR !file_exists($file)) {
        die('file not exists!');
    }
    Vendor('PHPExcel.PHPExcel');  //引入PHP EXCEL类
    $objRead = new PHPExcel_Reader_Excel2007();   //建立reader对象
    if (!$objRead->canRead($file)) {
        $objRead = new PHPExcel_Reader_Excel5();
        if (!$objRead->canRead($file)) {
            die('No Excel!');
        }
    }

    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

    $obj = $objRead->load($file);  //建立excel对象
    $currSheet = $obj->getSheet($sheet);   //获取指定的sheet表
    $columnH = $currSheet->getHighestColumn();   //取得最大的列号
    $columnCnt = array_search($columnH, $cellName);
    $rowCnt = $currSheet->getHighestRow();   //获取总行数

    $data = array();
    for ($_row = 1; $_row <= $rowCnt; $_row++) {  //读取内容
        for ($_column = 0; $_column <= $columnCnt; $_column++) {
            $cellId = $cellName[$_column] . $_row;
            $cellValue = $currSheet->getCell($cellId)->getValue();
            //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
            if ($cellValue instanceof PHPExcel_RichText) {   //富文本转换字符串
                $cellValue = $cellValue->__toString();
            }

            $data[$_row][] = $cellValue;
        }
    }

    return $data;
}

// 输入CSV
function input_csv($handle)
{
    setlocale(LC_ALL, array('zh_CN.gbk', 'zh_CN.gb2312', 'zh_CN.gb18030')); //预防LINUX FGETCSV读取GBK数据乱码
    $out = array();
    $n = 0;

    while ($data = fgetcsv($handle, 10000)) {
        $num = count($data);
        for ($i = 0; $i < $num; $i++) {

            // $out[$n][$i] = $data[$i];
            $out[$n][$i] = iconv('gbk', 'utf-8', $data[$i]);

        }
        $n++;
    }

    return $out;
}

/**
 * 解析获取php.ini 的upload_max_filesize（单位：byte）
 * @param $dec int 小数位数
 * @return float （单位：byte）
 * */
function get_upload_max_filesize_byte($dec = 2)
{
    $max_size = ini_get('upload_max_filesize');
    preg_match('/(^[0-9\.]+)(\w+)/', $max_size, $info);
    $size = $info[1];
    $suffix = strtoupper($info[2]);
    $a = array_flip(array("B", "KB", "MB", "GB", "TB", "PB"));
    $b = array_flip(array("B", "K", "M", "G", "T", "P"));
    $pos = isset($a[$suffix]) && $a[$suffix] !== 0 ? $a[$suffix] : $b[$suffix];
    return round($size * pow(1024, $pos), $dec);
}


//数据处理
function deal_mobile($number)
{
    //去除空格
    $number = str_replace(' ', '', $number);
    //去除-字符
    if (strpos($number, '-') !== false) {
        $number = explode('-', $number);

        $number = implode('', $number);

    }

    return $number;
}

/**
 * 判断身份证号
 * @param
 * @return boolean
 */
function is_idCard($name)
{
    if (preg_match('/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $name)) {
        return true;
    }
    return false;
}

/**
 * 判断是否详细地址
 * @param
 * @return boolean
 */
function is_address($name)
{
    if (preg_match('/^[\x{4e00}-\x{9fa5}|\.]{5,100}.{5,100}$/u', $name)) {
        return true;
    }
    return false;
}

/**
 * 判断中文名称效格式
 * @param
 * @return boolean
 */
function is_name($name)
{

    //新疆等少数民族可能有·
    $str = $name;
    if (strpos($str, '·')) { //将·去掉，看看剩下的是不是都是中文
        $str = str_replace("·", '', $str);
        if (preg_match('/^[\x7f-\xff]{6,30}+$/', $str)) {
            return true;//全是中文
        } else {
            return false;//不全是中文
        }
    } else {
        if (preg_match('/^[\x7f-\xff]{6,30}+$/', $str)) {
            return true;//全是中文
        } else {
            return false;//不全是中文
        }
    }
}

/**
 * 获取客户端操作系统
 * @return Ambigous <boolean, string>
 */
function get_device()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = false;
    if (preg_match('/iPhone/i', $agent)) {
        $os = 'iPhone OS';
    } else if (preg_match('/Android/i', $agent)) {
        $os = 'Android';
    } else if (preg_match('/SymbianOS/i', $agent)) {
        $os = 'SymbianOS';
    } else if (preg_match('/iPad/i', $agent)) {
        $os = 'iPad';
    } else if (preg_match('/Windows/i', $agent) && strpos($agent, 'Phone')) {
        $os = 'Windows Phone OS';
    } else if (preg_match('/XBLWP7/i', $agent)) {
        $os = 'XBLWP7';
    } else if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
        $os = 'Windows 95';
    } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
        $os = 'Windows ME';
    } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
        $os = 'Windows 98';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
        $os = 'Windows XP';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
        $os = 'Windows 2000';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
        $os = 'Windows 7';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.3/i', $agent)) {
        $os = 'Windows 8';
        if (preg_match('/WOW64/i', $agent))
            $os .= ' x64';
        else
            $os .= ' x86';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
        $os = 'Windows NT';
    } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
        $os = 'Windows 32';
    } else if (preg_match('/linux/i', $agent)) {
        $os = 'Linux';
    } else if (preg_match('/unix/i', $agent)) {
        $os = 'Unix';
    } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'SunOS';
    } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'IBM OS/2';
    } else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)) {
        $os = 'Macintosh';
    } else if (preg_match('/PowerPC/i', $agent)) {
        $os = 'PowerPC';
    } else if (preg_match('/AIX/i', $agent)) {
        $os = 'AIX';
    } else if (preg_match('/HPUX/i', $agent)) {
        $os = 'HPUX';
    } else if (preg_match('/NetBSD/i', $agent)) {
        $os = 'NetBSD';
    } else if (preg_match('/BSD/i', $agent)) {
        $os = 'BSD';
    } else if (preg_match('/OSF1/i', $agent)) {
        $os = 'OSF1';
    } else if (preg_match('/IRIX/i', $agent)) {
        $os = 'IRIX';
    } else if (preg_match('/FreeBSD/i', $agent)) {
        $os = 'FreeBSD';
    } else if (preg_match('/teleport/i', $agent)) {
        $os = 'teleport';
    } else if (preg_match('/flashget/i', $agent)) {
        $os = 'flashget';
    } else if (preg_match('/webzip/i', $agent)) {
        $os = 'webzip';
    } else if (preg_match('/offline/i', $agent)) {
        $os = 'offline';
    } else {
        $os = 'Unknown';
    }
    return $os;
}

function findKey($data, $val)
{
    $start = strpos($data, '(');
    $end = strpos($data, ')');
    $str = substr($data, $start, $end - $start);

    $agent = explode(";", $str);
    if ($val == null) {
        $new = explode('/', $agent[count($agent) > 0 ? count($agent) - 1 : 0]);
        return $new[0];
    }
    foreach ($agent as $key => $value) {
        if (preg_match('/' . $val . '/i', $value))
            return $value;
    }
    return 'unknown';
}

/**
 * 获取手机型号
 * @param 操作系统 $type
 * @return Ambigous <string, unknown>
 */
function getPhoneModel($type)
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if ($type == 'iPhone OS') {
        $mod = findKey($agent, $type);
    } else if ($type == 'Android') {
        $mod = findKey($agent, null);
    } else {
        $mod = $type;
    }
    return $mod;
}


/**
 * 获取用户真实IP
 */
function get_user_ip()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"),
            "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']
        && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return ($ip);
}

/**
 * 根据HTML代码获取word文档内容
 * 创建一个本质为mht的文档，该函数会分析文件内容并从远程下载页面中的图片资源
 * 该函数依赖于类WordMake
 * 该函数会分析img标签，提取src的属性值。但是，src的属性值必须被引号包围，否则不能提取
 * @param string $content HTML内容
 * @param string $absolutePath 网页的绝对路径。如果HTML内容里的图片路径为相对路径，那么就需要填写这个参数，来让该函数自动填补成绝对路径。这个参数最后需要以/结束
 * @param bool $isEraseLink 是否去掉HTML内容中的链接
 */
function WordMake($content, $absolutePath = "", $isEraseLink = true)
{
    require_once APP_PATH . '../extend/wordmaker/wordMaker.php';
    $mht = new \Wordmaker();
    if ($isEraseLink) {
        $content = preg_replace('/<a\s*.*?\s*>(\s*.*?\s*)<\/a>/i', '$1', $content);   //去掉链接
    }
    $images = array();
    $files = array();
    $matches = array();
//这个算法要求src后的属性值必须使用引号括起来   /<img[.\n]*?src\s*?=\s*?[\"\'](.*?)[\"\'](.*?)\/>/i

    if (preg_match_all('|<img\s*.*src\s*=\s*["/](.*?)["](.*?)>|i', $content, $matches)) {
        $arrPath = $matches[1];
        for ($i = 0; $i < count($arrPath); $i++) {
            $path = $arrPath[$i];
            $imgPath = trim($path);
            if ($imgPath != "") {
                $files[] = $imgPath;
                if (substr($imgPath, 0, 7) == 'http://') {
//绝对链接，不加前缀
                } else {
                    $imgPath = $absolutePath . $imgPath;
                }
                $images[] = $imgPath;
            }
        }
    }
    $mht->AddContents("tmp.html", $mht->GetMimeType("tmp.html"), $content);
    for ($i = 0; $i < count($images); $i++) {
        $image = $images[$i];
        if (@fopen($image, 'r')) {
            $imgcontent = @file_get_contents($image);
            if ($content)
                $mht->AddContents($files[$i], $mht->GetMimeType($image), $imgcontent);
        } else {
            echo "file:" . $image . " not exist!<br />";
        }
    }
    return $mht->GetFile();
}

//添加到压缩文件
function addFileToZip($path, $zip)
{
    $handler = opendir($path); //打开当前文件夹由$path指定。
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != "..") {//文件夹文件名字为'.'和‘..'，不要对他们进行操作
            if (is_dir($path . "/" . $filename)) {// 如果读取的某个对象是文件夹，则递归
                addFileToZip($path . "/" . $filename, $zip);
            } else { //将文件加入zip对象
                $zip->addFile($path . "/" . $filename, $filename);
            }
        }
    }
    @closedir($handler);
}

//根据员工号获取员工名称
function get_name_by_job_number($job_number)
{
    return Db::name('user')->where('job_number', $job_number)->value('username');
}

/**
 * 发送短信
 * @return stdClass
 * $sign = "灵狐广告"
 * $content = array('code'=>'123456','product'=>'asasd')
 */
function sendOneSms($phone, $sign, $tpl, $content)
{
    $sms = new SmsDemo();
    $result = $sms->sendSms($phone, $sign, $tpl, $content);
    $result = (array)$result;

    return $result;
}

/**
 * 批量发送短信(一次最多100)
 * @return stdClass
 * $phone = array("1500000000","1500000001",)
 * $sign = array( "云通信","云通信")
 * $content = array(
 *    array(
 *         "name" => "Tom",
 *        "code" => "123",
 *    ),
 *    array(
 *       "name" => "Jack",
 *       "code" => "456",
 *    ),
 */
function sendBatchSms($phone, $sign, $tpl, $content)
{
    $sms = new SmsDemo();
    $result = $sms->sendBatchSms($phone, $sign, $tpl, $content);
    $result = (array)$result;
    return $result;
}

//短信模板内容
function getSmsContent($tpl, $first, $second)
{
    switch ($tpl) {
        case 'SMS_155570432':
            $content = $first . ' 客户 您好，您安装的 ' . $second . ' 将要到期，请及时续费';
            break;
        case 'SMS_120156266':
            $content = '您的验证码' . $first . '，该验证码5分钟内有效，请勿泄漏于他人！';
            break;
        default:
            break;
    }
    return $content;
}


//通过用户id获取部门名称（包含父级）
function get_department($user_id)
{
    $d_id = Db::name('user')->where('id', $user_id)->value('d_id');
    $name = get_d_pname($d_id);
    return $name;
}

//通过部门id获取部门全称
function get_d_pname($d_id, $name = '')
{
    $d_info = Db::name('department')->where('id', $d_id)->field('id,name,pid,zone')->find();
    $name = $d_info['zone'] . $name;
    if ($d_info['pid'] > 0) {
        $name = get_d_pname($d_info['pid'], $name);
    }

    return $name;
}

//获取部门id获取当前及下属一系列部门id
function get_child_pids($d_id, $ids = array(), $where = false)
{
    if (!is_array($d_id)) $d_id = array(intval($d_id));

    if ($where) {
        $next_ids = Db::name('department')->where($where)->where(array('pid' => array('in', $d_id)))->column('id');
    } else {
        $next_ids = Db::name('department')->where(array('pid' => array('in', $d_id)))->column('id');
    }
    if ($next_ids) {
        if ($where) {
            $children = Db::name('department')->where($where)->where(array('pid' => array('in', $next_ids)))->column('id');
        } else {
            $children = Db::name('department')->where(array('pid' => array('in', $next_ids)))->column('id');
        }
        if ($children) {
            $now_ids = array_merge($d_id, $ids, $next_ids);
            $all_ids = get_child_pids($children, $now_ids, $where);
        } else {
            $all_ids = array_merge($d_id, $ids, $next_ids);
        }
    } else {
        $all_ids = array_merge($d_id, $ids);
    }

    return $all_ids;
}


//获取角色id获取当前及下属一系列角色id
function get_child_group($d_id, $ids = array())
{
    if (!is_array($d_id)) $d_id = array(intval($d_id));

    $next_ids = Db::name('auth_group')->where(array('pid' => array('in', $d_id)))->column('id');
    if ($next_ids) {

        $children = Db::name('auth_group')->where(array('pid' => array('in', $next_ids)))->column('id');
        if ($children) {
            $now_ids = array_merge($d_id, $ids, $next_ids);
            $all_ids = get_child_group($children, $now_ids);
        } else {
            $all_ids = array_merge($d_id, $ids, $next_ids);
        }
    } else {
        $all_ids = array_merge($d_id, $ids);
    }

    return $all_ids;
}

//获取角色id获取当前及上属一系列角色id
function get_parent_group($d_id, $ids = array(), $merge = false)
{
    if (!is_array($d_id)) $d_id = array(intval($d_id));

    $next_ids = Db::name('auth_group')->where(array('id' => array('in', $d_id)))->column('pid');
    if ($next_ids) {
        $children = Db::name('auth_group')->where(array('id' => array('in', $next_ids)))->column('pid');
        if ($children) {
            $now_ids = array_merge($d_id, $ids, $next_ids);
            $all_ids = get_parent_group($children, $now_ids);
        } else {
            $all_ids = array_merge($d_id, $ids, $next_ids);
        }
    } else {
        if (!$merge) {
            $all_ids = array_merge($d_id, $ids);
        } else {
            $all_ids = $ids;
        }
    }

    return $all_ids;
}

//递归获取父级部门id（通过当前部门id获取当前及向上一系列部门id）
function get_parent_pids($d_id, $ids = array(), $merge = false)
{
    $pid = Db::name('department')->where('id', $d_id)->value('pid');
    if ($pid > 0) {
        array_push($ids, $d_id, $pid);
        $pre_pid = Db::name('department')->where('id', $pid)->value('pid');
        if ($pre_pid > 0) {
            $ids = get_parent_pids($pre_pid, $ids);
        }
    } else {
        if (!$merge) {
            array_push($ids, $d_id);
        }
    }

    return $ids;
}


//加密函数
function lock_url($txt, $key = '')
{
    if ($txt == '') return '';
    $key = empty($key) ? config('lock_phone_key') : 'jushuoniu.com';
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+@#$%&*";
    $nh = intval(substr($txt, -1));
    $ch = $chars[$nh];
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = base64_encode($txt);
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = ($nh + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 64;
        $tmp .= $chars[$j];
    }
    return urlencode($ch . $tmp);
}

//解密函数
function unlock_url($txt, $key = '')
{
    if ($txt == '') return '';
    $key = empty($key) ? config('lock_phone_key') : 'jushuoniu.com';
    $txt = urldecode($txt);
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+@#$%&*";
    $ch = $txt[0];
    $nh = strpos($chars, $ch);
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = substr($txt, 1);
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
        while ($j < 0) $j += 64;
        $tmp .= $chars[$j];
    }
    return base64_decode($tmp);
}

function hiddenDecodeMobile($mobileEncoded)
{
    $mobile = is_numeric($mobileEncoded) ? $mobileEncoded : unlock_url($mobileEncoded);
    return strlen($mobile) == 11 ? substr_replace($mobile, '****', 3, 4) : $mobile;
}

//手机展示处理
function get_show_mobile($mobile)
{
    $mobile_hide_status = session('mobile_hide_status');
    $is_admin = session('is_admin');
    $admin_id = session('admin_id');
    if ($is_admin == 1) { //是管理员
        if ($admin_id == 1 || $admin_id == 2569) { //admin管理员
            $number = is_numeric($mobile) ? lock_url($mobile) : $mobile; //加密
        } else {
            $number = is_numeric($mobile) ? $mobile : unlock_url($mobile); //解密
        }
    } else {
        switch ($mobile_hide_status) {
            case '1': //解密展示
                $number = is_numeric($mobile) ? $mobile : unlock_url($mobile);
                break;
            case '2': //完全隐藏
                $number = '';
                break;
            case '3': //隐藏中间4位
                $mobile = is_numeric($mobile) ? $mobile : unlock_url($mobile);
                $number = strlen($mobile) == 11 ? substr_replace($mobile, '****', 3, 4) : $mobile;
                break;
            default: //加密
                $number = $mobile;
                break;
        }
    }

    return $number;
}

// 是手机号?
function isMobile($str)
{
    return !!preg_match('#^1\d{10}$#iUs', $str); // 返回0, 模型自动验证失败, 故写成!!
}

//查看菜单是否有权限
function check_auth($url)
{
    // 排除权限
    $auth = new Auth();
    $admin_id = Session('admin_id');
    $is_admin = Session('is_admin');
    if (!$auth->check($url, $admin_id) && $is_admin != 1) {
        return false;
    }
    return true;
}

//mysqli 操作数据库
function mysqli_opera($sql)
{
    /* Connect to a MySQL server  连接数据库服务器 */
    $link = mysqli_connect(
        'localhost',  /* The host to connect to 连接MySQL地址 */
        'root',      /* The user to connect as 连接MySQL用户名 */
        'root',  /* The password to use 连接MySQL密码 */
        'bry');    /* The default database to query 连接数据库名称*/

    if (!$link) {
        printf("Can't connect to MySQL Server. Errorcode: %s ", mysqli_connect_error());
        exit;
    }
    /* Send a query to the server 向服务器发送查询请求*/
    if ($result = mysqli_query($link, $sql)) {

        $arr = mysqli_fetch_all($result, MYSQLI_ASSOC);
        /* Destroy the result set and free the memory used for it 结束查询释放内存 */
        mysqli_free_result($result);
    }
    /* Close the connection 关闭连接*/
    mysqli_close($link);

    return $arr;
}

//判断是否是正整数
function check_plus_int($keyword)
{
    $res = preg_match("/^[1-9][0-9]*$/", $keyword);
    return $res;
}

//计算下个月最后一天
function getNextMonthEndDate1($date)
{
    $firstday = date('Y-m-01', strtotime($date));
    $lastday = date('Y-m-d', strtotime("$firstday +2 month -1 day"));
    return $lastday;
}

//计算下个月的同天
//$date格式：'Y-m-d'
function next_month_today1($today)
{
    $date = date('Y-m-d', $today);
    $time = date('H:i:s', $today);
    // dump($date);
    //获取今天是一个月中的第多少天
    $current_month_t = date("t", strtotime($date));
    $current_month_d = date("d", strtotime($date));
    $current_month_m = date("m", strtotime($date));

    //获取下个月最后一天及下个月的总天数
    $next_month_end = getNextMonthEndDate1($date);
    $next_month_t = date("t", strtotime($next_month_end));

    $returnDate = '';
    if ($current_month_d == $current_month_t) {//月末
        //获取下个月的月末
        $returnDate = $next_month_end;
    } else {//非月末
        //获取下个月的今天
        if ($current_month_d > $next_month_t) { //如 01-30，二月没有今天,直接返回2月最后一天
            $returnDate = $next_month_end;
        } else {
            $returnDate = date("Y-m", strtotime($next_month_end)) . "-" . $current_month_d;
        }
    }
    return strtotime($returnDate . ' ' . $time);
}

//生成导入批次号
function create_batch()
{
    //最新一条记录
    $new_record = Db::name('crm_import_records')->order('id desc')->limit(1)->select();

    if (empty($new_record)) {
        $batch = 'duxin00000001';
    } else {
        $last_batch = $new_record[0]['batch'];
        $number = substr($last_batch, 5);
        $new_number = sprintf("%08d", intval($number) + 1);
        $batch = 'duxin' . $new_number;
    }

    return $batch;
}

function secondToMinute($second)
{
    if (empty($second))
        return null;
    return date('i:s', intval($second));
}

function _curl($url, $data = null, $timeout = 0, $isProxy = false)
{


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    curl_setopt($ch, CURLOPT_TIMEOUT, 1);

    curl_exec($ch);
    curl_close($ch);

}

function wordToHtml($wordname)
{
    $filename = explode('.', $wordname);
    $htmlname = $filename[0] . 'html';
    if (file_exists($htmlname)) {
        return $htmlname;
    }
    $ewt = pathinfo($wordname, PATHINFO_EXTENSION);
    if ($ewt == 'doc') {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($wordname, 'MsDoc');
    } elseif ($ewt == 'docx') {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($wordname);
    } else {
        return '';
    }

    $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, "HTML");
    $xmlWriter->save($htmlname);
    return $htmlname;
}

//获取信易赢最后登录时间
function get_xinyiying_last_login_time($mobile)
{
    //$t1 = microtime(true);
    try {
        $url = 'http://139.196.248.255:8081/client/getClientLoginData';
        $data = [
            'mphone' => unlock_url($mobile),
            'key' => 'Q1JNY3Jt',
            'isFirst' => 1
        ];
        $result = json_decode(request_post($url, $data), true);
        //$t2 = microtime(true);
        if (isset($result['code']) && $result['code'] == 201) {

            return '<a data-url="' . url("crm/kefu/xinyiying_login_log", ['mobile' => $mobile]) . '" data-title="信易赢登录日志" class="popup_no_end">' . $result['data'][0]['last_login_date'] . '</a>';
        } else {
            return '';
        }
    } catch (\Exception $e) {
        return '';
    }
}

/**
 * [自动分配]
 * @param  [type] $tg_id [分配人id]
 * @param  [type] $type  [类型]
 * @return [type]        [description]
 */
function auto_allot_11_27($tg_id,$type=3)
{   
    //当前账号是否开启了自动分配，没有直接返回
    $auto_allot = Db::name('crm_spread_resource_auto_allot')->where('uid', $tg_id)->find();
    if (!$auto_allot || $auto_allot['status'] != 1 || !$auto_allot['rate']) {
        return false;
    }
    
    $rate = unserialize($auto_allot['rate']);
    if (!$rate) {
        return false;
    }

    $resource_ids = Db::name('crm_resource')->where('catid', 1)->where('tg_id', $tg_id)->where('status', -1)->column('id');
    if (empty($resource_ids)) {
        return false;
    }
    $key = array_keys($rate); //键名：用户id
    $enable_user = Db::name('user')->where('id','in',$key)->where('status',1)->column('id'); //启用的用户id
    
    //根据分配比例权重构造users随机数组
    $rate_values = array_values($rate); //键值：权重
    $split = implode(',',array_unique($rate_values));
    
    $users = [];
    $has_user = []; //分配比例大于0的用户
    if(strpos($split, '.')!==false){ //判断是否存在小数，存在1位小数
        foreach ($rate as $key => $val) {
            if($val>0 && in_array($key,$enable_user)){
                $has_user[] = $key;
                for($i=0;$i<$val*10;$i++){
                    $users[]=$key;//放大数组
                }
            }
        }
    }else{ //不存在小数
        foreach ($rate as $key => $val) {
            if($val>0 && in_array($key,$enable_user)){
                $has_user[] = $key;
                for($i=0;$i<$val;$i++){
                    $users[]=$key;//放大数组
                }
            }
        }
    }
    shuffle($users); //打乱排序

    $child_user = Db::name('user')->where('id','in',$has_user)->where('status',1)->field('id,job_number,true_name')->select();
    if(empty($child_user)){
        return 2;
    }
    $child_info = array_column($child_user,null,'id');

    $update_data = [];
    $allot_user = [];
    foreach ($resource_ids as $key => $val) {
        //分配人
        $use_key = array_rand($users);
        $use_id = $users[$use_key];
        $allot_user[] = $use_id;

        $updateData['id'] = $val;
        $updateData['status'] = -1;
        $updateData['resource_allot_id'] = $tg_id;
        $updateData['resource_allot_time'] = time();
        $updateData['tg_id'] = $use_id;
        $updateData['tg_job_number'] = isset($child_info[$use_id])?$child_info[$use_id]['job_number']:'';
        $updateData['tg_name'] = isset($child_info[$use_id])?$child_info[$use_id]['true_name']:'';

        $update_data[] = $updateData;
    }
    $allot_user_count = array_count_values($allot_user); //本次分配数量详情
    //存入通知
    foreach ($allot_user_count as $key =>$value){
        Db::name("crm_allot_notice")->insert(array("uid"=>$key,"num"=>$value));
        get_url_contents(config("notice_address")."/?uid=".$key);
    }
    //批量更新
    $resource = new CrmResource();
    $affect = $resource->saveAll($update_data);
    
    if($affect!=false){
        $batch = time().rand(1000,9999); //批次号：时间戳+4位随机数
        //记录分配日志
        $record = [
            'type'      => $type,
            'batch'     => $batch,
            'rate'      => $auto_allot['rate'],
            'allot_num' => serialize($allot_user_count),
            'allot_id'  => $auto_allot['uid'],
            'add_time'  => time(),
        ];
        Db::name('crm_spread_record')->insert($record);

        //下属员工销售以上的角色开启了自动分配则继续分配
        $staff_allot = Db::name('crm_spread_resource_auto_allot')->where('uid','in',$has_user)->select();
        if(!empty($staff_allot)){
            foreach ($staff_allot as $key => $val) {
                auto_allot($val['uid'],3);//已分配资源再次自动下发；
            }  
        }
         
    }
    return true;
}

/**
 * [自动分配]
 * @param  [type] $tg_id [分配人id]
 * @param  [type] $type  [类型]
 * @return [type]        [description]
 */
function auto_allot($tg_id,$type=3)
{   
    //当前账号是否开启了自动分配，没有直接返回
    $auto_allot = Db::name('crm_spread_resource_auto_allot')->where('uid', $tg_id)->find();
    if (!$auto_allot || $auto_allot['status'] != 1) {
        return false;
    }

    $resource_ids = Db::name('crm_resource')->where('catid', 1)->where('tg_id', $tg_id)->where('status', -1)->column('id');
    if (empty($resource_ids)) { //资源存在判断
        return false;
    }
    
    $resource_count = count($resource_ids); //待分配资源总数
    
    switch ($auto_allot['allot_type']) {
        case '1': //按上限自动分配
            $allot_sort = unserialize($auto_allot['allot_sort']); //分配排序
            $rate = array_filter(unserialize($auto_allot['rate'])); //分配上限,过滤分配上限设置未空或0
            if(empty($rate)){ //都没有设置上限则不需要分配
                return false;
            }
            //查询被分配人今日历史分配数据
            $today = strtotime(date('Y-m-d',time()));
            $user_receive = Db::name('crm_spread_resource_receive_log')->where('uid','in',$allot_sort)->where('date',$today)->select();
            $receive = array_column($user_receive,null,'uid');

            //检测资源数量是否达到每日上限并过滤分配上限设置未空或0的排序信息
            $rate_key = array_keys($rate);
            foreach ($allot_sort as $key => $val) {
                if(!in_array($val, $rate_key)){ //过滤上限为0
                    unset($allot_sort[$key]);
                    continue;
                }
                $allot_limit = $rate[$val]; //单人设置的分配上限
                $today_limit = isset($receive[$val])?$receive[$val]['num']:0; //今日已经分配数量
                $today_spare = $allot_limit-$today_limit; //今日剩余数量上限
                if($today_spare<=0){
                    unset($allot_sort[$key]);
                    unset($rate[$val]);
                }else{
                    $rate[$val]  = $today_spare;
                }
            }
            $rate_num = array_sum($rate); //本次应分配数量之和
            if($rate_num < $resource_count){ 
                $resource_ids = array_slice($resource_ids,0,$rate_num);
            }
            
            $sort_key = array_keys($allot_sort); //排序数组
            $child_user = Db::name('user')->where('id','in',$allot_sort)->field('id,job_number,true_name')->select();

            if(empty($child_user)){
                return false;
            }
            $child_info = array_column($child_user,null,'id'); //下属用户信息

            $update_data = [];
            $allot_user = [];
            $has_allot_num = [];//此次已分配数量（总的）
            $i = 0; //分配起始人
            //上次分配记录存在下次分配人时
            $spread_record = Db::name('crm_spread_record')->where('type',$type)->where('allot_id',$tg_id)->order('add_time desc')->field('allot_type,allot_sort,next_assigned_id')->find();
            if($spread_record['allot_type']==1 && $auto_allot['allot_sort']==$spread_record['allot_sort'] && $spread_record['next_assigned_id']>0){ //仅按上限自动分配且排序未发生变化时才紧跟上次分配记录处理
                $key = array_search($spread_record['next_assigned_id'], $allot_sort);
                $i = array_search($key, $sort_key); //上次记录中下次分配人
            }

            $pre_allot_sort = $allot_sort; //此次实际分配之前分配排序
            $pre_sort_key = $sort_key; //此次实际分配之前分配排序键名数组
            $last_i = $i;//最后一条资源对应$i；
            
            foreach ($resource_ids as $key => $val) {
                $user_num = count($allot_sort); //可分配人员数量

                //根据排序
                $y = $i%$user_num; //余数
                $user_id = $allot_sort[$sort_key[$y]]; //对应用户id
                $allot_limit = $rate[$user_id]; //对应设置上限
                $has_allot_num[$user_id] = isset($has_allot_num[$user_id])?$has_allot_num[$user_id]:0; //对应人员已分配数量
                
                if($has_allot_num[$user_id]>=$allot_limit){ //已分配达到上限
                    
                    //循环分给团队人员
                    $next = 0;
                    for ($j=$y+1; $j<$user_num; $j++) { //在后续排序查找可分配人员
                        $user_id = $allot_sort[$sort_key[$j]]; //对应用户id
                        $allot_limit = $rate[$user_id]; //对应设置上限
                        
                        if($has_allot_num[$user_id]<$allot_limit){ //未分配达到上限
                        
                            $next = 1; //找到后面排序可分配人员
                            $allot_user[] = $user_id; //被分配人
                            $has_allot_num[$user_id] += 1;
                            break;
                        }
                    }
                    if($next==0){ //在后续排序未找到可分配人员
                        for ($j=0; $j <= $y; $j++) { //在前置排序查找可分配人员
                            // $y = $j%$user_num; //余数
                            $user_id = $allot_sort[$sort_key[$j]]; //对应用户id
                            $allot_limit = $rate[$user_id]; //对应设置上限

                            if($has_allot_num[$user_id]<$allot_limit){ //未分配达到上限
                            
                                $next = 1; //找到前置排序可分配人员
                                $allot_user[] = $user_id; //被分配人
                                $has_allot_num[$user_id] += 1;
                                break;
                            }
                        }
                    }
                    
                    if($next==0){ //全部达到今日分配上限
                        break; //退出循环
                    }

                }else{ //可分配
                    $allot_user[] = $user_id; //被分配人
                    $has_allot_num[$user_id] += 1;
                }
                $last_i = $i;
                $i++;

                $updateData['id'] = $val;
                $updateData['status'] = -1;
                $updateData['resource_allot_id'] = $tg_id; //分配人
                $updateData['resource_allot_time'] = time();
                $updateData['tg_id'] = $user_id;
                $updateData['tg_job_number'] = isset($child_info[$user_id])?$child_info[$user_id]['job_number']:'';
                $updateData['tg_name'] = isset($child_info[$user_id])?$child_info[$user_id]['true_name']:'';

                $update_data[] = $updateData;
            }

            $next_y = ($last_i+1)%$user_num; //余数
            $next_assigned_id = $pre_allot_sort[$pre_sort_key[$next_y]];//此次分配结束后下次该分配的人

            $allot_user_count = $has_allot_num; //本次分配数量详情
            break;
        case '2': //按概率自动平均分配
            //对应分配的下属
            $user = Db::name('user')->alias('a')
                ->join('auth_group_access c', 'c.uid = a.id')
                ->join('auth_group d', 'd.pid = c.group_id')
                ->where('a.id', $tg_id)
                ->field('a.id,d_id,c.group_id,d.id as group_cid')->find();
            $children = Db::name('user')->alias('a')
                ->join('auth_group_access b', 'b.uid = a.id', 'left')
                ->join('department c', 'c.id = a.d_id', 'left')
                ->where('a.status', 1)
                ->where('c.pid', $user['d_id'])
                ->where('group_id', $user['group_cid'])
                ->where('business_type', 1)
                ->group('a.d_id')
                ->column('a.id,a.job_number,a.true_name');
            if (!$children) {
                $children = Db::name('user')->alias('a')
                    ->join('auth_group_access b', 'b.uid = a.id', 'left')
                    ->join('department c', 'c.id = a.d_id', 'left')
                    ->where('a.status', 1)
                    ->where('d_id', $user['d_id'])
                    ->where('group_id', $user['group_cid'])
                    ->where('business_type', 1)
                    ->column('a.id,a.job_number,a.true_name');
            }

            if(empty($children))return false;
            $child_info = $children; //下属用户信息
            $users = array_column($children,'id');

            //查询被分配人今日历史分配数据
            $today = strtotime(date('Y-m-d',time()));
            $user_receive = Db::name('crm_spread_resource_receive_log')->where('uid','in',$users)->where('date',$today)->select();
            $receive = array_column($user_receive,null,'uid');
            
            shuffle($users); //打乱排序
            $update_data = [];
            $allot_user = [];
            foreach ($resource_ids as $key => $val) {
                //分配人
                $use_key = array_rand($users);
                $user_id = $users[$use_key];
                $allot_user[] = $user_id;

                $updateData['id'] = $val;
                $updateData['status'] = -1;
                $updateData['resource_allot_id'] = $tg_id; //系统分配
                $updateData['resource_allot_time'] = time();
                $updateData['tg_id'] = $user_id;
                $updateData['tg_job_number'] = isset($child_info[$user_id])?$child_info[$user_id]['job_number']:'';
                $updateData['tg_name'] = isset($child_info[$user_id])?$child_info[$user_id]['true_name']:'';

                $update_data[] = $updateData;
            }

            $allot_user_count = array_count_values($allot_user); //本次分配数量详情
            $next_assigned_id = 0;
            break;
        default:
            break;
    }
    
    //批量更新
    $resource = new CrmResource();
    $affect = $resource->saveAll($update_data);
    if($affect!=false){
        //存入通知
        foreach ($allot_user_count as $key =>$value){
            Db::name("crm_allot_notice")->insert(array("uid"=>$key,"num"=>$value));
            get_url_contents(config("notice_address")."/?uid=".$key);
        }

        $batch = time().rand(1000,9999); //批次号：时间戳+4位随机数
        //记录分配日志
        $record = [
            'type'      => $type, //待分配资源池自动分配
            'batch'     => $batch,
            'rate'      => $auto_allot['rate'],
            'allot_num' => serialize($allot_user_count),
            'allot_id'  => $tg_id, //待分配资源自动分配分配人设置为admin
            'add_time'  => time(),
            'allot_sort'  => $auto_allot['allot_sort'],
            'allot_type'  => $auto_allot['allot_type'],
            'next_assigned_id'  => $next_assigned_id,
        ];
        Db::name('crm_spread_record')->insert($record);

        //更新本日已接收资源数量记录表
        $resourceRreceiveLog = new CrmSpreadResourceReceiveLog();
        $receive_log = [];
        $receive_ids = array_keys($receive);
        foreach ($allot_user_count as $key => $val) {
            $log = [
                'uid' => $key,
                'num' => $val,
                'date' => $today,
            ];
            if(in_array($key, $receive_ids)){ //已存在本日记录
                $log['id'] = $receive[$key]['id'];
                $log['num'] += $receive[$key]['num'];
            }
            $receive_log[] = $log;
        }
        $resourceRreceiveLog->saveAll($receive_log);

        //下属员工销售以上的角色开启了自动分配则继续分配
        $staff_allot = Db::name('crm_spread_resource_auto_allot')->where('uid','in',array_keys($allot_user_count))->select();
        if(!empty($staff_allot)){
            foreach ($staff_allot as $key => $val) {
                auto_allot($val['uid'],3);//已分配资源再次自动下发；
            }  
        }
    }
    return true;
}

/**
 * [待分配资源池自动下发]
 * @param  string $type [2.待分配资源池自动分配，1.推广资源插入接口分配]
 * @return [bool]       [description]
 */
function wait_resource_auto_allot($type='')
{   
    $type = $type!=''?$type:2;
    //当前账号是否开启了自动分配，没有直接返回
    $auto_allot = Db::name('crm_spread_resource_auto_allot')->where('uid', 1)->find();
    if (!$auto_allot || $auto_allot['status'] != 1) {
        return false;
    }
    $resource_ids = Db::name('crm_resource')
        ->where('status', 1)
        ->where('add_type', 3)
        ->where('invalid_status', 0)
        ->column('id');
    if (empty($resource_ids)) { //资源存在判断
        return false;
    }

    $resource_count = count($resource_ids); //待分配资源总数
    
    switch ($auto_allot['allot_type']) {
        case '1': //按上限自动分配
            $allot_sort = unserialize($auto_allot['allot_sort']); //分配排序
            $rate = array_filter(unserialize($auto_allot['rate'])); //分配上限,过滤分配上限设置未空或0
            if(empty($rate)){ //都没有设置上限则不需要分配
                return false;
            }

            //查询被分配人今日历史分配数据
            $today = strtotime(date('Y-m-d',time()));
            $user_receive = Db::name('crm_spread_resource_receive_log')->where('uid','in',$allot_sort)->where('date',$today)->select();
            $receive = array_column($user_receive,null,'uid');

            //检测资源数量是否达到每日上限并过滤分配上限设置未空或0的排序信息
            $rate_key = array_keys($rate);
            foreach ($allot_sort as $key => $val) {
                if(!in_array($val, $rate_key)){ //过滤上限为0
                    unset($allot_sort[$key]);
                    continue;
                }
                $allot_limit = $rate[$val]; //单人设置的分配上限
                $today_limit = isset($receive[$val])?$receive[$val]['num']:0; //今日已经分配数量
                $today_spare = $allot_limit-$today_limit; //今日剩余数量上限
                if($today_spare<=0){
                    unset($allot_sort[$key]);
                    unset($rate[$val]);
                }else{
                    $rate[$val]  = $today_spare;
                }
            }
            $rate_num = array_sum($rate); //本次应分配数量之和
            if($rate_num < $resource_count){ 
                $resource_ids = array_slice($resource_ids,0,$rate_num);
            }
            
            $sort_key = array_keys($allot_sort); //排序数组
            $child_user = Db::name('user')->where('id','in',$allot_sort)->field('id,job_number,true_name')->select();
            if(empty($child_user)){
                return false;
            }
            $child_info = array_column($child_user,null,'id'); //下属用户信息

            $update_data = [];
            $allot_user = [];
            $has_allot_num = [];//此次已分配数量（总的）
            $i = 0; //分配起始人
            //上次分配记录存在下次分配人时
            $spread_record = Db::name('crm_spread_record')->where('type',$type)->where('allot_id',1)->order('add_time desc')->field('allot_type,allot_sort,next_assigned_id')->find();
            if($spread_record['allot_type']==1 && $auto_allot['allot_sort']==$spread_record['allot_sort'] && $spread_record['next_assigned_id']>0){ //仅按上限自动分配且排序未发生变化时才紧跟上次分配记录处理
                $key = array_search($spread_record['next_assigned_id'], $allot_sort);
                $i = array_search($key, $sort_key); //上次记录中下次分配人
            }

            $pre_allot_sort = $allot_sort; //此次实际分配之前分配排序
            $pre_sort_key = $sort_key; //此次实际分配之前分配排序键名数组
            $last_i = $i;//最后一条资源对应$i；
            
            foreach ($resource_ids as $key => $val) {
                $user_num = count($allot_sort); //可分配人员数量

                //根据排序
                $y = $i%$user_num; //余数
                $user_id = $allot_sort[$sort_key[$y]]; //对应用户id
                $allot_limit = $rate[$user_id]; //对应设置上限
                $has_allot_num[$user_id] = isset($has_allot_num[$user_id])?$has_allot_num[$user_id]:0; //对应人员已分配数量
                
                if($has_allot_num[$user_id]>=$allot_limit){ //已分配达到上限
                    //循环分给团队人员
                    $next = 0;
                    for ($j=$y+1; $j<$user_num; $j++) { //在后续排序查找可分配人员
                        $user_id = $allot_sort[$sort_key[$j]]; //对应用户id
                        $allot_limit = $rate[$user_id]; //对应设置上限
                        
                        if($has_allot_num[$user_id]<$allot_limit){ //未分配达到上限
                        
                            $next = 1; //找到后面排序可分配人员
                            $allot_user[] = $user_id; //被分配人
                            $has_allot_num[$user_id] += 1;
                            break;
                        }
                    }
                    if($next==0){ //在后续排序未找到可分配人员
                        for ($j=0; $j <= $y; $j++) { //在前置排序查找可分配人员
                            // $y = $j%$user_num; //余数
                            $user_id = $allot_sort[$sort_key[$j]]; //对应用户id
                            $allot_limit = $rate[$user_id]; //对应设置上限

                            if($has_allot_num[$user_id]<$allot_limit){ //未分配达到上限
                            
                                $next = 1; //找到前置排序可分配人员
                                $allot_user[] = $user_id; //被分配人
                                $has_allot_num[$user_id] += 1;
                                break;
                            }
                        }
                    }
                    
                    if($next==0){ //全部达到今日分配上限
                        break; //退出循环
                    }

                }else{ //可分配
                    $allot_user[] = $user_id; //被分配人
                    $has_allot_num[$user_id] += 1;
                }
                $last_i = $i;
                $i++;

                $updateData['id'] = $val;
                $updateData['status'] = -1;
                $updateData['resource_allot_id'] = 1; //系统分配
                $updateData['resource_allot_time'] = time();
                $updateData['tg_id'] = $user_id;
                $updateData['tg_job_number'] = isset($child_info[$user_id])?$child_info[$user_id]['job_number']:'';
                $updateData['tg_name'] = isset($child_info[$user_id])?$child_info[$user_id]['true_name']:'';

                $update_data[] = $updateData;
            }

            $next_y = ($last_i+1)%$user_num; //余数
            $next_assigned_id = $pre_allot_sort[$pre_sort_key[$next_y]];//此次分配结束后下次该分配的人

            $allot_user_count = $has_allot_num; //本次分配数量详情
            break;
        case '2': //按概率自动平均分配
            //对应分配的下属
            $d_ids = [1,34,35,51,52]; //需要设置的营业部部门id
            
            $children = Db::name('user')->alias('a')
                ->join('auth_group_access b', 'b.uid = a.id', 'left')
                ->join('department c', 'c.id = a.d_id', 'left')
                ->where('a.status', 1)
                ->where('d_id','in',$d_ids)
                ->where('group_id', 101)
                ->group('a.d_id')
                ->column('a.id,a.job_number,a.true_name');

            if(empty($children))return false;
            $child_info = $children; //下属用户信息
            $users = array_column($children,'id');

            //查询被分配人今日历史分配数据
            $today = strtotime(date('Y-m-d',time()));
            $user_receive = Db::name('crm_spread_resource_receive_log')->where('uid','in',$users)->where('date',$today)->select();
            $receive = array_column($user_receive,null,'uid');
            
            shuffle($users); //打乱排序
            $update_data = [];
            $allot_user = [];
            foreach ($resource_ids as $key => $val) {
                //分配人
                $use_key = array_rand($users);
                $user_id = $users[$use_key];
                $allot_user[] = $user_id;

                $updateData['id'] = $val;
                $updateData['status'] = -1;
                $updateData['resource_allot_id'] = 1; //系统分配
                $updateData['resource_allot_time'] = time();
                $updateData['tg_id'] = $user_id;
                $updateData['tg_job_number'] = isset($child_info[$user_id])?$child_info[$user_id]['job_number']:'';
                $updateData['tg_name'] = isset($child_info[$user_id])?$child_info[$user_id]['true_name']:'';

                $update_data[] = $updateData;
            }

            $allot_user_count = array_count_values($allot_user); //本次分配数量详情
            $next_assigned_id = 0;
            break;
        default:
            break;
    }
    
    //批量更新
    $resource = new CrmResource();
    $affect = $resource->saveAll($update_data);
    if($affect!=false){
        //存入通知
        foreach ($allot_user_count as $key =>$value){
            Db::name("crm_allot_notice")->insert(array("uid"=>$key,"num"=>$value));
            get_url_contents(config("notice_address")."/?uid=".$key);
        }

        $batch = time().rand(1000,9999); //批次号：时间戳+4位随机数
        //记录分配日志
        $record = [
            'type'      => $type, //待分配资源池自动分配
            'batch'     => $batch,
            'rate'      => $auto_allot['rate'],
            'allot_num' => serialize($allot_user_count),
            'allot_id'  => 1, //待分配资源自动分配分配人设置为admin
            'add_time'  => time(),
            'allot_sort'  => $auto_allot['allot_sort'],
            'allot_type'  => $auto_allot['allot_type'],
            'next_assigned_id'  => $next_assigned_id,
        ];
        Db::name('crm_spread_record')->insert($record);

        //更新本日已接收资源数量记录表
        $resourceRreceiveLog = new CrmSpreadResourceReceiveLog();
        $receive_log = [];
        $receive_ids = array_keys($receive);
        foreach ($allot_user_count as $key => $val) {
            $log = [
                'uid' => $key,
                'num' => $val,
                'date' => $today,
            ];
            if(in_array($key, $receive_ids)){ //已存在本日记录
                $log['id'] = $receive[$key]['id'];
                $log['num'] += $receive[$key]['num'];
            }
            $receive_log[] = $log;
        }
        $resourceRreceiveLog->saveAll($receive_log);

        //下属员工销售以上的角色开启了自动分配则继续分配
        $staff_allot = Db::name('crm_spread_resource_auto_allot')->where('uid','in',array_keys($allot_user_count))->select();
        if(!empty($staff_allot)){
            foreach ($staff_allot as $key => $val) {
                auto_allot($val['uid'],3);//已分配资源再次自动下发；
            }  
        }
    }

    return true;
}

//已分配资源池自动下发
function wait_allot_auto_allot()
{
    $user_ids = Db::name('user')
        ->alias('a')
        ->join('auth_group_access b', 'b.uid = a.id', 'left')
        ->where('status', 1)
        ->where('d_id', 'in', get_child_pids(20, [], 'business_type = 1'))
        ->where('group_id', '<>', 112)->column('id');
    $resource = Db::name('crm_resource')
        ->where('catid', 1)
        ->where('status', -1)
        ->where('add_type', 3)
        ->where('tg_id', 'in', $user_ids)
        ->where('invalid_status', 0)
        ->column('tg_id,id');
    if (!empty($resource)) {
        $tg_ids = array_unique(array_column($resource,'tg_id')); //需要分配资源的用户id
        foreach ($tg_ids as $k => $val) {
            auto_allot($val,3);
        }
    }
}

/*
 * 自动分配-2019.11.14以前的
 */
function auto_allot_ori($tg_id)
{
    //当前账号是否开启了自动分配，没有直接返回
    $auto_allot = Db::name('crm_spread_resource_auto_allot')->where('uid', $tg_id)->find();
    if (!$auto_allot || $auto_allot['status'] != 1 || !$auto_allot['rate']) {
        return 2;
    }

    $rate = unserialize($auto_allot['rate']);
    if (!$rate) {
        return 2;
    }

    $resource_ids = Db::name('crm_resource')->where('catid', 1)->where('tg_id', $tg_id)->where('status', -1)->column('id');
    if (empty($resource_ids)) {
        return 2;
        //return json(array('code' => 0, 'msg' => '没有资源可分配'));
    }
    //获取自己的直属下级成员
//    $d_id = Db::name('user')->where('id', $tg_id)->value('d_id');
//    $d_ids = Db::name('department')->where('pid', $d_id)->where('business_type', 1)->column('id');
//    $d_ids = $d_ids ? $d_ids : [$d_id];
//    $group_id = Db::name('auth_group_access')->where('uid', $tg_id)->value('group_id');
//    $group_ids = Db::name('auth_group')->where('pid', $group_id)->column('id');
//    $group_ids = $group_ids ? $group_ids : [$group_id];
//    $users = Db::name('user')->alias('a')
//        ->join('auth_group_access b', 'b.uid = a.id', 'left')
//        ->where('d_id', 'in', $d_ids)->where('group_id', 'in', $group_ids)->where('status', 1)->field('id,job_number,username,true_name')->select();

    //打乱users数组
    $key = array_keys($rate);
    shuffle($key);
    foreach ($key as $value) {
        $arr[$value] = $rate[$value];
    }
    //shuffle($rate);
    //$chunk = ceil(count($resource_ids) / count($users));

    //chunk大于1一人分多条，小于1一人分一条
    //$resource_ids = array_chunk($resource_ids, $chunk);
    $resource_num = count($resource_ids);
    $offset = 0;
    $user = Db::name('user')->where('id','in',array_keys($rate))->column('id,job_number,username,true_name');
    foreach ($arr as $k => $val) {
        if(!$val){
            continue;
        }
        $num = ceil($resource_num * ($val / 100));
        $resource = array_slice($resource_ids, $offset, $num);
        if (empty($resource)) {
            break;   //资源分完即停止
        }
        $updateData['status'] = -1;
        $updateData['resource_allot_id'] = $tg_id;
        $updateData['resource_allot_time'] = time();
        $updateData['tg_id'] = $k;
        $updateData['tg_job_number'] = $user[$k]['job_number'];
        $updateData['tg_name'] = $user[$k]['true_name'] ? $user[$k]['true_name'] : [];
        $res = Db::name('crm_resource')->where('id', 'in', $resource)->update($updateData);
        if ($res) {
            //销售以上角色开启了自动分配则继续分配
            $auto_allot = Db::name('crm_spread_resource_auto_allot')->where('uid', $k)->value('status');
            $group_id = Db::name('auth_group_access')->where('uid', $k)->value('group_id');
            if ($group_id != 112 && $auto_allot == 1) {
                auto_allot($k);
            }
            $offset += $num;
        }
    }

    return 1;
}

//待分配资源池自动下发-2019.11.14以前的
function wait_resource_auto_allot_ori()
{
    $is_auto_allot = Db::name('system')->where('name', 'seo_resource_auto_rule')->value('value');
    $is_auto_allot = $is_auto_allot ? json_decode(unserialize($is_auto_allot), true) : false;
    if ($is_auto_allot && $is_auto_allot['auto'] == 1) {
        $resource = Db::name('crm_resource')
            ->where('status', 1)
            ->where('add_type', 3)
            ->where('invalid_status', 0)
            ->column('id');
        if (!empty($resource)) {
            $resource_num = count($resource);
            unset($is_auto_allot['auto']);
            $offset = 0;
            array_walk($is_auto_allot,function($value,$key) use ($resource_num,&$offset,$resource){
                if(!$value){
                    return;
                }

                $num = ceil($resource_num * ( $value / 100));

                $ids = array_slice($resource, $offset, $num);

                if (empty($ids)) {
                    exit;   //资源分完即停止
                }
                switch($key){
                    case 'area1':
                        $user = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 1)->where('group_id', 101)->find();
                        break;
                    case 'area3':
                        $user = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 34)->where('group_id', 101)->find();
                        break;
                    case 'area4':
                        $user = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 35)->where('group_id', 101)->find();
                        break;
                    case 'area5':
                        $user = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 51)->where('group_id', 101)->find();
                        break;
                    case 'area6':
                        $user = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 52)->where('group_id', 101)->find();
                        break;
                }

                $res = Db::name('crm_resource')
                    ->where('id', 'in', $ids)
                    ->update(['status' => -1, 'tg_id' => $user['id'], 'tg_name' => $user['true_name'] ? $user['true_name'] : $user['username'], 'tg_job_number' => $user['job_number']]);
                if($res){
                    $offset += $num;
                    auto_allot($user['id']);
                }
            });
        }
    }
}

//已分配资源池自动下发-2019.11.14以前的
function wait_allot_auto_allot_ori()
{
    $user_ids = Db::name('user')
        ->alias('a')
        ->join('auth_group_access b', 'b.uid = a.id', 'left')
        ->where('status', 1)
        ->where('d_id', 'in', get_child_pids(20))
        ->where('group_id', '<>', 112)->column('id');
    $resource = Db::name('crm_resource')
        ->where('catid', 1)
        ->where('status', -1)
        ->where('add_type', 3)
        ->where('tg_id', 'in', $user_ids)
        ->where('invalid_status', 0)
        ->column('tg_id,id');
    if (!empty($resource)) {
        foreach ($resource as $k => $val) {
            auto_allot($k);
        }
    }
}

function arrayToobject($array)
{
    if (is_array($array)) {
        $obj = new StdClass();
        foreach ($array as $key => $val) {
            $obj->$key = $val;
        }
    } else {
        $obj = $array;
    }
    return $obj;
}

function objectToarray($object)
{
    $array = [];
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    } else {
        $array = $object;
    }
    return $array;
}

/**
 * 手机号码替换加密
 * 数字 0 1 2 3 4 5 6 7 8 9
 * 字母 c m y a o t f g k e
 * 1、数字替换为字母
 * 2、随机插入大写字母和数字，解密时候过滤掉
 * @param $mobile int
 * @return string
 */
function lock_mobile($mobile)
{
    if (!isMobile($mobile)) {
        return '';
    }
    $arr = ['c', 'm', 'y', 'a', 'o', 't', 'f', 'g', 'k', 'e'];
    $salt = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
    $mobile = str_split($mobile);
    $str = '';
    $num = array_rand($arr, 1);
    switch ($num) {
        case 2:
            $str .= $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)];
            break;
        case 3:
            $str .= $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)];
            break;
        case 6:
            $str .= $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)];
            break;
        default:
            $str .= $salt[array_rand($salt, 1)];
            break;
    }
    try {
        foreach ($mobile as $k => $val) {
            if ($k > 10) {
                break;
            }
            $num = array_rand($arr, 1);
            switch ($num) {
                case 2:
                    $str .= $arr[$val] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)];
                    break;
                case 3:
                    $str .= $arr[$val] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)];
                    break;
                case 4:
                    $str .= $arr[$val] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)] . $salt[array_rand($salt, 1)];
                    break;
                case 6:
                    $str .= $arr[$val];
                    break;
                default:
                    $str .= $arr[$val] . $salt[array_rand($salt, 1)];
                    break;
            }
        }
    } catch (\Exception $e) {
        return '';
    }
    return $str;
}