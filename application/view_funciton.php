<?php

use think\Db;

/**
 * Created by PhpStorm.
 * User: cc
 * Date: 2018/4/22
 * Time: 15:53
 */

function to_time($time)
{
    if ($time) {
        return date('Y-m-d H:i:s', $time);
    } else {
        return '';
    }
}

function to_time_ms($time)
{
    if ($time) {
        return date('Y-m-d H:i:s', $time / 1000);
    } else {
        return '';
    }
}

function text_sub($str)
{
    return substr($str, 0, 50) . '...';
}

function city($id)
{
    return !empty($id) ? Db::name('city')->where(array('id' => $id))->field('city_name')->find()['city_name'] : '';
}


function secsToStr($secs)
{
    $r = '';
    if ($secs >= 86400) {
        $days = floor($secs / 86400);
        $secs = $secs % 86400;
        $r = $days . ' 天';
        if ($days <> 1) {
            $r .= '';
        }
        if ($secs > 0) {
            $r .= ', ';
        }
    }
    if ($secs >= 3600) {
        $hours = floor($secs / 3600);
        $secs = $secs % 3600;
        $r .= $hours . ' 小时';
        if ($hours <> 1) {
            $r .= '';
        }
        if ($secs > 0) {
            $r .= ', ';
        }
    }
    if ($secs >= 60) {
        $minutes = floor($secs / 60);
        $secs = $secs % 60;
        $r .= $minutes . ' 分钟';
        if ($minutes <> 1) {
            $r .= '';
        }
        if ($secs > 0) {
            $r .= ', ';
        }
    }
    $r .= $secs . ' 秒';
    if ($secs <> 1) {
        $r .= '';
    }
    return $r;
}

function label()
{
    return Db::name('label')->select();
}

function label_with($type, $id)
{
    return Db::name('label_with')->where(array('f_id' => $id, 'type' => $type))->select();
}

function time_search($cloum = 'create_time')
{
    empty(input('s_time')) ? $s_time = 0 : $s_time = strtotime(input('s_time'));
    empty(input('e_time')) ? $e_time = 0 : $e_time = strtotime(input('e_time'));
    $where = array();
    if ($s_time > $e_time && $e_time != 0) {
        return 1;
    }
    if ($s_time == 0 && $e_time != 0) {
        $where[$cloum] = array('lt', $e_time);
    }
    if ($s_time != 0 && $e_time == 0) {
        $where[$cloum] = array('gt', $s_time);
    }
    if ($s_time != 0 && $e_time != 0) {
        $where[$cloum] = array(array('gt', $s_time), array('lt', $e_time));
    }
    if ($s_time == 0 && $e_time == 0) {
        $where = 1;
    }
    return $where;
}

/**
 * 根据用户id获取名称
 * @param $id int 用户ID
 * @return string
 */
function get_username($id)
{
    $name = Db::name('user')->where('id', $id)->find();
    return $name['true_name'] ? $name['true_name'] : $name['username'];
}

/**
 * 根据用户id获取名称
 * @param $id int 用户ID
 * @return string
 */
function get_nickname($id)
{
    $name = Db::name('user')->where('id', $id)->value('nickname');
    return $name;
}
/**
 * 根据用户id获取名称
 * @param $id int 用户ID
 * @return string
 */
function get_job_number($id)
{
    $job_number = Db::name('user')->where('id', $id)->value('job_number');
    return $job_number;
}
/**
 * 根据用户工号获取名称
 * @param $id int 用户ID
 * @return string
 */
function get_username_by_job_number($id)
{
    $name = Db::name('user')->where('job_number', $id)->value('username');
    return $name;
}

/**
 * 根据用户工号获取名称
 * @param $id int 用户ID
 * @return string
 */
function get_username_by_id($id)
{
    $name = Db::name('user')->where('id', $id)->value('username');
    return $name;
}

/**
 * 根据问卷分返回风险等级
 * @param $score
 * @return int
 */
function get_protocol_level($score)
{
    switch ($score) {
        case $score < 20:
            $level = 1;
            break;
        case  20 <= $score && $score <= 36:
            $level = 2;
            break;
        case 37 <= $score && $score <= 53:
            $level = 3;
            break;
        case 54 <= $score && $score <= 82:
            $level = 4;
            break;
        case 83 <= $score:
            $level = 5;
            break;
        default:
            $level = 1;
    }

    return $level;
}

/**
 * 根据问卷分返回风险类型
 * @param $score int
 * @return string
 */
function get_protocol_type($score)
{
    $type = config('invest_type');
    foreach ($type as $val) {
        if ($val['score'][0] <= $score && $score <= $val['score'][1]) {
            return $val['type'];
        }
    }
    return '未知';
}

/**
 * 根据id获取自定义字段field 名称
 */
function get_field_name($id)
{
    return Db::name('crm_custom_field')->where('id', $id)->value('name');
}

/**
 * 人民币转大写
 * @param $num
 * @return mixed
 */
function cny($num)
{
    if (!$num) {
        return '';
    }
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        $num = $num / 10;
        $num = (int)$num;
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    if (empty($c)) {
        return "零元整";
    } else {
        return $c . "整";
    }
}

//问卷匹配客户
function get_match_custom($mobile)
{

}


function get_img_url($img)
{
    return "/data/" . $img;
}

//根据资源结束日期判断是否显示延期按钮
function show_delay_button($id)
{
    $resource = Db::name('crm_resource')->where('id', $id)->field('resource_end_time,delay_apply_status')->find();
    $time = $resource['resource_end_time'] - time();
    if ($time < 60 * 60 * 24 * 3 && $time > 0) { //结束时间在三天内时则显示“延期”
        if ($resource['delay_apply_status'] == 1) {
            $html = '<button type="button" class="btn btn-xs btn-default">延期审核中</button>';
        } elseif ($resource['delay_apply_status'] == 2) {
            $html = '<button type="button" class="btn btn-xs btn-success">已延期</button>';
        } elseif ($resource['delay_apply_status'] == 3) {
            $html = '<button type="button" class="btn btn-xs btn-default">延期驳回</button>';
        } else {
            $html = '<a href="javascript:void(0);" data-id="' . $id . '"  data-url="' . url('crm/resource/delay_apply') . '" class="btn btn-warning btn-xs btn-opera"><i class="fa fa-calendar-plus-o"></i> 延期申请</a>';
        }
        return $html;
    } else {
        return '';
    }

}

//根据工号获取经纪人联系方式
function get_mobile_by_job_number($job_number)
{
    return Db::name('user')->where('job_number', $job_number)->value('mobile');
}

//1234转为一二三四日期
function to_week($str)
{
    $transfer = [1 => '日', 2 => '一', 3 => '二', 4 => '三', 5 => '四', 6 => '五', 7 => '六'];
    $str = explode(',', $str);
    $week = '';
    foreach ($str as $val) {
        //$week .= '<span class="label label-primary">' . $transfer[$val] . '</span>';
    }
    return $week;
}

//隐藏部分字符串
function hidestr($string, $start = 0, $length = 0, $re = '*')
{
    if (empty($string)) return false;
    $strarr = array();
    $mb_strlen = mb_strlen($string);
    while ($mb_strlen) {//循环把字符串变为数组
        $strarr[] = mb_substr($string, 0, 1, 'utf8');
        $string = mb_substr($string, 1, $mb_strlen, 'utf8');
        $mb_strlen = mb_strlen($string);
    }
    $strlen = count($strarr);
    $begin = $start >= 0 ? $start : ($strlen - abs($start));
    $end = $last = $strlen - 1;
    if ($length > 0) {
        $end = $begin + $length - 1;
    } elseif ($length < 0) {
        $end -= abs($length);
    }
    for ($i = $begin; $i <= $end; $i++) {
        $strarr[$i] = $re;
    }
    if ($begin >= $end || $begin >= $last || $end > $last) return false;
    return implode('', $strarr);
}

//CMS根据栏目ID获取栏目名称
function get_channel_name($id)
{
    if (!$id) {
        return '首页';
    }
    return Db::name('cms_channel')->where('id|d_name', $id)->value('name');
}

//获取CMS中图片地址
function get_archive_img($img)
{
    return '/data/cms/archives/' . $img;
}

//获取当前栏目的父级ID
function get_channel_pid($id)
{
    return Db::name('cms_channel')->where('id', $id)->value('pid');
}

//获取当前栏目关键字
function get_channel_keyword($id)
{
    if (!$id) {
        return '客户管理系统，呼叫中心，微信营销';
    }
    return Db::name('cms_channel')->where('id', $id)->value('keyword');
}

//获取当前栏目描述
function get_channel_description($id)
{
    if (!$id) {
        return '客户管理系统，呼叫中心，微信营销';
    }
    return Db::name('cms_channel')->where('id', $id)->value('description');
}

//协议签订状态
function protocol_sign_status($id)
{
    $info = Db::name('crm_protocol')->field('contract_no,aprotocol,bprotocol,cprotocol,dprotocol,eprotocol,contract_protocol_version,is_new')->where('id', $id)->where('is_del', 2)->find(); //获取当条记录协议
    if (!$info['contract_no']) {
        $text = '<span style="color:red">未生成</span>';
    } else {
        $x = '<span style="color:red">未签</span>';
        $xx = '<span style="color:green">已签</span>';
        $y1 = ($info['cprotocol'] == 1) ? $xx : $x;
        $y2 = ($info['aprotocol'] == 1) ? $xx : $x;
        $y3 = ($info['bprotocol'] == 1) ? $xx : $x;
        $y3 = ($info['contract_protocol_version'] == 1) ? '<span style="color:gray">**</span>' : $y3;
        $y4 = ($info['dprotocol'] == 1) ? $xx : $x;
        $y5 = ($info['eprotocol'] == 1) ? $xx : $x;
        $y6 = ($info['is_new'] == 1) ? '<span style="color:blue">新</span>' : '';
        $text = $y2 . '-' . $y3 . '-' . $y1 . '-' . $y4 . '-' . $y5 . $y6;
    }
    return $text;
}

function protocol_audit_status($id)
{
    $info = Db::name('crm_protocol')->field('contract_no,a_is_audit,b_is_audit,c_is_audit,d_is_audit,e_is_audit,contract_protocol_version')->where('id', $id)->where('is_del', 2)->find(); //获取当条记录协议
    if (!$info['contract_no']) {
        $text = '<span style="color:gray">--</span>';
    } else {
        $x = '<span style="color:red">未审</span>';
        $xx = '<span style="color:green">审</span>';
        $y1 = ($info['c_is_audit'] == 1) ? $xx : $x;
        $y2 = ($info['a_is_audit'] == 1) ? $xx : $x;
        $y3 = ($info['b_is_audit'] == 1) ? $xx : $x;
        $y3 = ($info['contract_protocol_version'] == 1) ? '<span style="color:gray">**</span>' : $y3;
        $y4 = ($info['d_is_audit'] == 1) ? $xx : $x;
        $y5 = ($info['e_is_audit'] == 1) ? $xx : $x;
        $text = $y2 . '-' . $y3 . '-' . $y1 . '-' . $y4 . '-' . $y5;
    }

    return $text;
}

//问卷匹配客户
function getQuestionMatch($mobile)
{
    $name = Db::name('crm_resource')->where('mobile', $mobile)->where('status','gt',3)->order('id desc')->value('name');
    if (!$name) {
        $name = '-----';
    }
    return $name;
}

//根据手机号获取客户姓名
function get_resource_name($mobile)
{
    $name = Db::name('crm_resource')->where('mobile', lock_url($mobile))->value('name');
    if (!$name) {
        $name = '***';
    }
    return $name;
}

//管理员列表根据ID获取挂靠销售和执业编号
function get_bind_professional($id)
{
    $user_info = Db::name('user')->where('id', $id)->field('username,professional_number')->find();
    return '已关联' . $user_info['username'];
}

//根据ID获取销售姓名
function get_professional_name($id = 0)
{
    $id = $id ? $id : session('admin_id');
    $user = Db::name('user')->where('id', $id)->find();
    if ($user['professional_number']) {
        return $user['true_name'] ? $user['true_name'] : $user['username'];
    } else {
        if ($user['bind_account']) {
            $bind = Db::name('user')->where('id', $user['bind_account'])->find();
            return $bind['true_name'] ? $bind['true_name'] : $bind['username'];
        } else {
            return '';
        }
    }
}

//根据ID获取销售手机号
function get_professional_mobile($id = 0)
{
    $user = Db::name('user')->where('id', $id)->find();
    if ($user['professional_number']) {
        return $user['mobile'];
    } else {
        if ($user['bind_account']) {
            $bind = Db::name('user')->where('id', $user['bind_account'])->find();
            return $bind['mobile'];
        } else {
            return '';
        }
    }
}

//根据ID获取销售工号
function get_professional_job_number($id = 0)
{
    $user = Db::name('user')->where('id', $id)->find();
    if ($user['professional_number']) {
        return $user['job_number'];
    } else {
        if ($user['bind_account']) {
            $bind = Db::name('user')->where('id', $user['bind_account'])->find();
            return $bind['job_number'];
        } else {
            return '';
        }
    }
}

//根据ID获取销售工号
function get_professional_department($id = 0)
{
    $user = Db::name('user')->where('id', $id)->find();
    if ($user['professional_number']) {
        return get_d_pname($user['d_id']);
    } else {
        if ($user['bind_account']) {
            $bind = Db::name('user')->where('id', $user['bind_account'])->find();
            return get_d_pname($bind['d_id']);
        } else {
            return '';
        }
    }
}

//根据ID获取销售执业编号
function get_professional_number($id = 0)
{
    $id = $id ? $id : session('admin_id');
    $user = Db::name('user')->where('id', $id)->find();
    if ($user['professional_number']) {
        return $user['professional_number'];
    } else {
        if ($user['bind_account']) {
            $bind = Db::name('user')->where('id', $user['bind_account'])->value('professional_number');
            return $bind;
        } else {
            return '';
        }
    }
}

//根据直播老师ID获取老师名称
function get_live_teacher_name($id)
{
    return Db::name('live_user')->where('id', $id)->where('status', 1)->value('user_name');
}

//用户列表统计成交金额
function user_sum_money($mobile, $time = false)
{
    if ($time) {
        list($start, $end) = explode('~', $time);
    } else { //默认本月
        $start = date('Y-m-01 00:00:00', time());
        $end = date('Y-m-d 23:59:59', strtotime("$start +1 month -1 day"));
    }
    $money_where = array(
        'finance_verify_time' => array(array('egt', strtotime($start)), array('elt', strtotime($end))),
        'status' => array('egt', 5),
        'mobile' => $mobile
    );
    return Db::name('crm_resource')->alias('a')->join('crm_resource_flow b', 'b.resource_id = a.id')->where($money_where)->value('sum(money) as amount');
}

//根据id获取真实姓名
function get_true_name($id)
{
    return Db::name('user')->where('id', $id)->value('true_name');
}

function curl_post($url, array $params = array(), $timeout = 50)
{
    $ch = curl_init();//初始化
    curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return ($data);
}

//获取数据类型 A类B类等
function get_import_resource_type($id)
{
    return Db::name('type')->where('id', $id)->value('text');
}


/**
 * 列表号码检测结果显示
 * @param $resource_id int 资源ID
 * @param $own
 */
function resource_mobile_check_result($resource_id, $own = false)
{
    $where = [];
    $user_id = session('admin_id');
    if ($own) {
        $where['user_id'] = $user_id;
    } elseif (session('is_admin') != 1) {
        $group_id = Db::name('auth_group_access')->where('uid', session('admin_id'))->value('group_id');
        $group_ids = get_child_group($group_id); //所属职位及下属职位
        $uids = Db::name('auth_group_access')->where('group_id', 'in', $group_ids)->column('uid');
        $d_id = Db::name('user')->where('id', $user_id)->value('d_id');
        $d_ids = get_child_pids($d_id);//所属部门及下属部门
        $user_ids = Db::name('user')->where(['d_id' => ['in', $d_ids], 'id' => ['in', $uids]])->column('id');
        $where['user_id'] = ['in', $user_ids];
    }
    $where['resource_id'] = $resource_id;
    $status = Db::name('crm_resource_check')->where($where)->value('status');
    $html = '';
    if ($status !== null) {
        switch ($status) {
            case 0:
                $html = '<img style="margin-left:10px;width:25px;" src="/data/crm/0.png">';
                break;
            case 1:
                $html = '<img style="margin-left:10px;width:25px;" src="/data/crm/1.png">';
                break;
            case 2:
                $html = '<img style="margin-left:10px;width:25px;" src="/data/crm/2.png">';
                break;
            case 3:
                $html = '<img style="margin-left:10px;width:25px;" src="/data/crm/3.png">';
                break;
            case 4:
                $html = '<img style="margin-left:10px;width:25px;" src="/data/crm/4.png">';
                break;
            case 5:
                $html = '<img style="margin-left:10px;width:25px;" src="/data/crm/5.png">';
                break;
        }
    }
    return $html;
}



//获取随机股票值
//华东电脑（600850）  海欣股份（600851）    龙建股份(600853)    春兰股份（600854）    航天长峰(600855)    长百集团(600856)
//工大首创(600857)  银座股份（600858）    王府井(600859) 北人股份（600860）    北京城乡(600861)    ST纵横（600862）
//内蒙华电(600863)  岁宝热电(600864)    百大集团(600865)    星湖科技（600866）    通化东宝（600867）    梅雁股份(600868)
//三普药业(600869)  厦华电子（600870）    仪征化纤（600871）    中炬高新(600872)    五洲明珠（600873）    创业环保(600874)
//东电机电（600875）  洛阳玻璃（600876）    中国嘉陵（600877）    火箭股份（600879）    博瑞传播（600880）    亚泰集团（600881）
//大成股份（600882）  博闻科技(600883)    杉杉股份（600884）    力诺太阳（600885）    国投电力(600886)    伊利股份（600887）
//新疆众和(600888)  南京化纤(600889)    *ST中房（600890）   ST秋林（600891）    ST湖 科(600892)   华润生化(600893)
//广钢股份（600894）  张江高科（600895）    中海海盛（600896）    厦门空港(600897)    三联商社(600898)    长江电力（600900）
//600901～
//滨州活塞(600960)  株冶火炬(600961)    国投中鲁(600962)    岳阳纸业(600963)    福成五丰（600965）    博汇纸业(600966)
//北方创业(600967)  郴电国际（600969）    中材国际(600970)    恒源煤电(600971)    宝胜股份（600973）    新 五 丰（600975）
//武汉健民（600976）  宜华木业(600978)    广安爱众(600979)    北矿磁材（600980）    江苏纺织（600981）    宁波热电（600982）
//合肥三洋（600983）  建设机械（600984）    雷鸣科化（600985）    科达股份（600986）    航民股份（600987）    东方宝龙（600988）
//四创电子（600990）  长丰汽车（600991）    贵绳股份（600992）    马应龙（600993） 文山电力（600995）    开滦股份（600997）
function get_stock_code()
{
    $stock_code = [
        600850,600851,600853,600854,600855,600856,600857,600858,600859,600860,600861,600862,600863,600864,600865,600866,600867,600868,
        600869,600870,600871,600872,600873,600874,600875,600876,600877,600879,600880,600881,600882,600883,600884,600885,600886,600887,
        600888,600889,600890,600891,600892,600893,600894,600895,600896,600897,600898,600900,600960,600961,600962,600963,600965,600966,
        600967,600969,600970,600971,600973,600975,600976,600978,600979,600980,600981,600982,600983,600984,600985,600986,600987,600988,
        600990,600991,600992,600993,600995,600997
    ];
    return $stock_code[array_rand($stock_code)];
}

//随机获取来源地址
function get_origin_address()
{
    $array = [
        'http://zb.yuntougu888.com/index/zhibo?room_code=1',
        'http://zb.yuntougu888.com/index/zhibo?room_code=2',
        'http://zb.yuntougu888.com/index/zhibo?room_code=3',
        'http://www.yuntougu888.com/xiazai/login.html'
    ];
    return $array[array_rand($array)];
}
