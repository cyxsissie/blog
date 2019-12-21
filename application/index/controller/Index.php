<?php
/**
 * Created by PhpStorm.
 * User: cc
 * Date: 2018/8/18
 * Time: 12:09
 */

namespace app\index\controller;

use Driver\Cache\Redis as redis;
use think\Controller as controller;
use think\Db;

class Index extends controller
{
    public function cs(){
        $rule_id = Db::name("auth_rule")->where(array("name"=>"crm/seo/wait_resource"))->value("id");
        $groups = Db::name("auth_group")->where("rules","like","%$rule_id%")->field("id,rules")->select();

        $group_ids = "";
        foreach ($groups as $key =>$value){
            $arr = explode(",",$value["rules"]);
            if(in_array($rule_id,$arr)){
                $group_ids.=$value["id"].",";
            }
        }
        $group_ids = $group_ids."1";
        var_dump($group_ids);
        $uids = Db::name("auth_group_access")->where("group_id","in",$group_ids)->column("uid");
        $uids=  array_unique($uids);
        var_dump($uids);exit;
    }
    public function dd(){


        $data =  Db::name("crm_resource")->where("spread_page",'like',"%href%")->field("id,spread_page")->select();

        foreach ($data as $key =>$value){


            $res = explode("href=",$value["spread_page"]);
            if (isset($res[1])){
                $res_data = explode("target=",$res[1]);
            }

            if (!empty($res_data[0])){
                //var_dump($res_data);
                Db::name("crm_resource")->where(array("id"=>$value["id"]))->update(array("spread_page"=>$res_data[0]));
            }

        }


    }
    public function cc(){


        $data =  Db::name("crm_resource")->where("spread_page",'like',"%href%")->field("id,spread_page")->select();


        foreach ($data as $key =>$value){
            $res=array();
            preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$value["spread_page"],$res);
            if (!empty($res[0])){
                Db::name("crm_resource")->where(array("id"=>$value["id"]))->update(array("spread_cat"=>$res[0][0]));
            }
            var_dump($res);
        }
        $str="&lt;a href=http://sem.jxhl8.com/zt/zg/ahsm/wap/mobile16/?keyword=GeGuZhenDuan target='_blank'&gt;诊股&lt;/a&gt;";
        preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$str,$res);
        var_dump($res);
    }
    //同步信易赢最后登录时间
    public function update_login_time()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $t1 = microtime(true);
        try {
            $mobiles = Db::name('crm_resource')->where('status', 11)->column('id,mobile');
            $nmobiles = [];
            //同一个手机号可能对应多个ID都更新掉
            foreach ($mobiles as $k => $val) {
                $nmobiles[$val][] = $k;
            }
            //dump($nmobiles);

            $url = 'http://139.196.248.255:8081/client/getLoginData';
            $params = [
                'key' => 'Q1JNY3Jt',
                'isAll' => 0
            ];
            $result = json_decode(request_post($url, $params), true);

            if (isset($result['code']) && $result['code'] == 201 && $result['data']) {
                //$data = [];
                foreach ($result['data'] as $val) {
                    //$data[lock_url(base64_decode($val['mphone']))] = strtotime($val['operTime']);
                    $mobile = lock_url(base64_decode($val['mphone']));
                    if (isset($nmobiles[$mobile]) && $val['operTime']) {
                        Db::name('crm_resource_info')->where('resource_id', 'in', $nmobiles[$mobile])->update(['last_login_time' => strtotime($val['operTime'])]);
                    }
                }
                $t2 = microtime(true);
                return 'success';
            } else {
                //短信通知下
                $code = 5555;
                $sign = config('sms_sign');
                $tpl = 'SMS_155620057';
                $content = array('code' => $code);
                sendOneSms(18130134072, $sign, $tpl, $content);
                return 'error';
            }
        } catch (\Exception $e) {
            //短信通知下
            $code = 5555;
            $sign = config('sms_sign');
            $tpl = 'SMS_155620057';
            $content = array('code' => $code);
            sendOneSms(18130134072, $sign, $tpl, $content);
            return 'error';
        }
    }

    //根据客户手机号获取相关信息
    public function getCustomerInfo()
    {
        try {
            $data = input();
            $key = '!!BrY2019..';
            if (!$data['mobile'] || !$data['timestamp'] || !$data['sign']) {
                data_echo([], 0, '缺少必要参数！');
            }
            if (time() - $data['timestamp'] > 120) {
                data_echo([], 0, '请求超时！');
            }
            ksort($data);
            $str = '';
            $sign = $data['sign'];
            unset($data['sign']);
            foreach ($data as $k => $val) {
                $str .= $k . '=' . $val . '&';
            }
            $str .= 'key=' . $key;
            if (md5($str) != $sign) {
                data_echo([], 0, '参数有误！');
            }
            $resource = Db::name('crm_resource')->alias('a')
                ->join('crm_resource_flow b', 'b.resource_id = a.id', 'left')
                ->where('mobile', lock_url($data['mobile']))
                ->field('a.id,name,mobile,tg_id,install_version,pact_start_time,pact_end_time,kefu_id')->select();
            if (!empty($resource)) {

                $user = Db::name('user')->where('status', 1)->column('id,job_number,username,true_name,mobile,d_id');
                $department = Db::name('department')->column('id,name,pid');
                $install_version = Db::name('crm_custom_field')->where('style', 8)->column('id,name');

                foreach ($resource as $k => $val) {
                    $tg_depart = '';
                    $tg_d_id = $user[$val['tg_id']]['d_id'];
                    while ($tg_d_id != 20 && isset($department[$tg_d_id])) {
                        $tg_depart = $department[$tg_d_id]['name'] . '-' . $tg_depart;
                        $tg_d_id = $department[$tg_d_id]['pid'];
                    }
                    $kf_depart = '';
                    $kf_d_id = $user[$val['kefu_id']]['d_id'];
                    while ($kf_d_id != 54 && isset($department[$kf_d_id])) {
                        $kf_depart = $department[$kf_d_id]['name'] . '-' . $kf_depart;
                        $kf_d_id = $department[$kf_d_id]['pid'];
                    }
                    $resource[$k]['mobile'] = lock_mobile(unlock_url($val['mobile']));
                    $resource[$k]['tg_department'] = trim($tg_depart, '-');
                    $resource[$k]['tg_job_number'] = isset($user[$val['tg_id']]['job_number']) ? $user[$val['tg_id']]['job_number'] : '';
                    $resource[$k]['tg_name'] = isset($user[$val['tg_id']]['true_name']) ? $user[$val['tg_id']]['true_name'] : $user[$val['tg_id']]['username'];
                    $resource[$k]['tg_mobile'] = isset($user[$val['tg_id']]['mobile']) ? $user[$val['tg_id']]['mobile'] : '';
                    $resource[$k]['kf_department'] = trim($kf_depart, '-');
                    $resource[$k]['kf_job_number'] = isset($user[$val['kefu_id']]['job_number']) ? $user[$val['kefu_id']]['job_number'] : '';
                    $resource[$k]['kf_name'] = isset($user[$val['kefu_id']]['true_name']) ? $user[$val['kefu_id']]['true_name'] : $user[$val['kefu_id']]['username'];
                    $resource[$k]['kf_mobile'] = isset($user[$val['kefu_id']]['mobile']) ? $user[$val['kefu_id']]['mobile'] : '';
                    $resource[$k]['install_version'] = isset($install_version[$val['install_version']]) ? $install_version[$val['install_version']] : '';
                    $resource[$k]['pact_start_time'] = to_time($val['pact_start_time']);
                    $resource[$k]['pact_end_time'] = to_time($val['pact_end_time']);
                    unset($resource[$k]['tg_id']);
                    unset($resource[$k]['kefu_id']);
                }
                data_echo($resource, 200, 'success');
            } else {
                data_echo([], 200, 'empty');
            }
        } catch (\Exception $e) {
            data_echo([], 0, '请求失败');
        }
    }

    //根据工号获取相关信息
    public function getStaffInfo()
    {
        try {
            $data = input();
            dump($data);
            $key = '!!BrY2019..';
            if (!$data['job_number'] || !$data['timestamp'] || !$data['sign']) {
                data_echo([], 0, '缺少必要参数！');
            }
            if (time() - $data['timestamp'] > 120) {
                data_echo([], 0, '请求超时！');
            }
            ksort($data);
            $str = '';
            $sign = $data['sign'];
            unset($data['sign']);
            foreach ($data as $k => $val) {
                $str .= $k . '=' . $val . '&';
            }
            $str .= 'key=' . $key;
            if (md5($str) != $sign) {
                data_echo([], 0, '参数有误！');
            }
            $user = Db::name('user')->where('job_number', $data['job_number'])->where('status', 1)->find();
            $xs_d_ids = get_child_pids(20);
            $kf_d_ids = get_child_pids(54);
            if (in_array($user['d_id'], $xs_d_ids)) {   //当工号为销售时，返回该销售下所有的已提交开户申请的客户基本信息
                $where = [
                    'status' => ['gt', 3]
                ];
                $group_id = Db::name('auth_group_access')->where('uid', $user['d_id'])->value('group_id');
                $group_ids = get_child_group($group_id); //所属职位及下属职位
                if (count($group_ids) == 1) {
                    $where['tg_id'] = $user['id'];
                } else {
                    $uids = Db::name('auth_group_access')->where(array('group_id' => array('in', $group_ids)))->column('uid');
                    $user_d_id = Db::name('user')->where(array('id' => $user['id']))->value('d_id');
                    $d_ids = get_child_pids($user_d_id);//所属部门及下属部门
                    $all_user = Db::name('user')->where(array('d_id' => array('in', $d_ids), 'id' => array('in', $uids)))->column('id');
                    $where['tg_id'] = array('in', $all_user);
                }
                $user = Db::name('user')->where('status', 1)->column('id,username,true_name');
                $install_version = Db::name('crm_custom_field')->where('style', 8)->column('id,name');
                $resource = Db::name('crm_resource')->alias('a')
                    ->join('crm_resource_flow b', 'b.resource_id = a.id', 'left')
                    ->where($where)
                    ->field('name,mobile,tg_id,install_version,open_apply_time,pact_start_time,pact_end_time')->select();
                if (!empty($resource)) {
                    foreach ($resource as $k => $val) {
                        $resource[$k]['mobile'] = lock_mobile(unlock_url($val['mobile']));
                        $resource[$k]['tg_name'] = isset($user[$val['tg_id']]['true_name']) ? $user[$val['tg_id']]['true_name'] : (isset($user[$val['tg_id']]['username']) ? $user[$val['tg_id']]['username'] : '');;
                        $resource[$k]['install_version'] = isset($install_version[$val['install_version']]) ? $install_version[$val['install_version']] : '';
                        $resource[$k]['open_apply_time'] = to_time($val['open_apply_time']);
                        $resource[$k]['pact_start_time'] = to_time($val['pact_start_time']);
                        $resource[$k]['pact_end_time'] = to_time($val['pact_end_time']);
                        unset($resource[$k]['tg_id']);
                    }
                    data_echo($resource, 200, 'success');
                } else {
                    data_echo([], 200, 'empty');
                }
            } elseif (in_array($user['d_id'], $kf_d_ids)) {    //当工号为客服时，返回该客服维护的所有资源
                $where = [
                    'status' => 11
                ];
                $group_id = Db::name('auth_group_access')->where('uid', $user['d_id'])->value('group_id');
                $group_ids = get_child_group($group_id); //所属职位及下属职位
                if (count($group_ids) == 1) {
                    $where['kefu_id'] = $user['id'];
                } else {
                    $uids = Db::name('auth_group_access')->where(array('group_id' => array('in', $group_ids)))->column('uid');
                    $user_d_id = Db::name('user')->where(array('id' => $user['id']))->value('d_id');
                    $d_ids = get_child_pids($user_d_id);//所属部门及下属部门
                    $all_user = Db::name('user')->where(array('d_id' => array('in', $d_ids), 'id' => array('in', $uids)))->column('id');
                    $where['kefu_id'] = array('in', $all_user);
                }
                $user = Db::name('user')->where('status', 1)->column('id,username,true_name');
                $install_version = Db::name('crm_custom_field')->where('style', 8)->column('id,name');
                $resource = Db::name('crm_resource')->alias('a')
                    ->join('crm_resource_flow b', 'b.resource_id = a.id', 'left')
                    ->where($where)
                    ->field('name,mobile,tg_id,install_version,open_apply_time,pact_start_time,pact_end_time')->select();
                if (!empty($resource)) {
                    foreach ($resource as $k => $val) {
                        $resource[$k]['mobile'] = lock_mobile(unlock_url($val['mobile']));
                        $resource[$k]['tg_id'] = isset($user[$val['tg_id']]['true_name']) ? $user[$val['tg_id']]['true_name'] : (isset($user[$val['tg_id']]['username']) ? $user[$val['tg_id']]['username'] : '');
                        $resource[$k]['install_version'] = isset($install_version[$val['install_version']]) ? $install_version[$val['install_version']] : '';
                        $resource[$k]['open_apply_time'] = to_time($val['open_apply_time']);
                        $resource[$k]['pact_start_time'] = to_time($val['pact_start_time']);
                        $resource[$k]['pact_end_time'] = to_time($val['pact_end_time']);
                    }
                    data_echo($resource, 200, 'success');
                } else {
                    data_echo([], 200, 'empty');
                }
            } else {
                data_echo([], 200, 'empty');
            }
        } catch (\Exception $e) {
            data_echo([], 0, '请求失败');
        }
    }

    //输入账号密码，判断是否可以登录
    public function checkLogin()
    {
        try {
            $data = input();
            dump($data);
            $key = '!!BrY2019..';
            if (!$data['username'] || !$data['password'] || !$data['timestamp'] || !$data['sign']) {
                data_echo([], 0, '缺少必要参数！');
            }
            if (time() - $data['timestamp'] > 120) {
                data_echo([], 0, '请求超时！');
            }
            ksort($data);
            $str = '';
            $sign = $data['sign'];
            unset($data['sign']);
            foreach ($data as $k => $val) {
                $str .= $k . '=' . $val . '&';
            }
            $str .= 'key=' . $key;
            if (md5($str) != $sign) {
                data_echo([], 0, '参数有误！');
            }
            $salt = Db::name('user')->where(array('username' => $data['username']))->value('salt');
            if (!$salt) {
                data_echo([], 0, '账号不存在！');
            }
            $where['username'] = $data['username'];
            $where['password'] = md5($data['password'] . $salt);
            if (Db::name('user')->where($where)->find()) {
                data_echo([], 0, '验证通过！');
            } else {
                data_echo([], 0, '账号密码错误！');
            }
        } catch (\Exception $e) {
            data_echo([], 0, '请求失败');
        }
    }


    //批量更新直播到期时间
    public function update_time()
    {
        exit;
        set_time_limit(0);
        $list = Db::name('crm_resource_flow')->where('pact_end_time', 'gt', 0)->where('live_delay_time is null')->column('id,pact_end_time');
        $time = 86400 * 30;
        foreach ($list as $k => $val) {
            Db::name('crm_resource_flow')->where('id', $k)->update(['live_delay_time' => $val + $time]);
        }
        set_time_limit(0);
        $list = Db::name('crm_resource')->alias('a')->join('crm_resource_flow b', 'b.resource_id = a.id')->where('live_delay_time', 'gt', 0)->column('mobile,live_delay_time');
        foreach ($list as $k => $val) {
            Db::name('live_user')->where('phone', $k)->update(['crm_open_status' => 1, 'live_delay_time' => $val]);
        }
    }

    public function getUserInfo()
    {
        $mobile = input('post.mobile');
        $timestamp = input('post.timestamp');
        $sign = input('post.sign');
        $key = '!!BrY2019..';
        if (!$mobile || !$timestamp || !$sign) {
            data_echo([], 0, '缺少必要参数！');
        }
        if (time() - $timestamp > 120) {
            data_echo([], 0, '请求超时！');
        }
        $my_sign = md5('mobile=' . $mobile . '&timestamp=' . $timestamp . '&key=' . $key);
        if ($my_sign != $sign) {
            data_echo([], 0, '参数有误！');
        }
        $info = Db::name('crm_resource')->alias('a')->join('crm_resource_flow b', 'b.resource_id = a.id')->where('a.mobile', lock_url($mobile))->field('name,pact_end_time')->find();
        if (!$info) {
            data_echo([], 0, '手机号不存在！');
        } elseif (!$info['pact_end_time']) {
            data_echo([], 0, '手机号未成交！');
        } elseif ($info['pact_end_time'] && $info['pact_end_time'] < time()) {
            data_echo([], 0, '成交服务已过期！');
        } else {
            data_echo(['name' => $info['name']], 1, '获取成功！');
        }
    }

    //手机号去重检测
    public function mobile_check()
    {
        exit;
        $mobile = input('post.mobile');
        if (!$mobile) {
            data_echo([], 0, '查询失败');
        }
        $mobile = explode(',', $mobile);
        $filter_mobile = [];
        foreach ($mobile as $k => $val) {
            $mobile[$k] = lock_url($val);
        }

        if (count($mobile) > 5000) {
            $mobiles = array_chunk($mobile, 5000);
            foreach ($mobiles as $v) {
                $data = Db::name('crm_filter_mobile')->where('mobile', 'in', $v)->column('mobile');
                if (!empty($data)) {
                    $filter_mobile = array_merge($filter_mobile, $data);
                }
            }
            unset($mobiles);
        } else {
            $filter_mobile = Db::name('crm_filter_mobile')->where('mobile', 'in', $mobile)->column('mobile');
        }
        if (!empty($filter_mobile)) {
            $no_repeat = array_diff($mobile, $filter_mobile);
            $repeat = array_intersect($mobile, $filter_mobile);
        } else {
            $no_repeat = $mobile;
            $repeat = [];
        }
        data_echo(['repeat' => json_encode($repeat), 'no_repeat' => json_encode($no_repeat)], 1, '获取成功！');
    }

    /**推广接口
     * http://192.168.0.130:8088/index/index/spread.html?mobile=18100000000&num=10
     * mobile为起始手机号
     * num为递增数，例如num=10，则提交十条18100000001~18100000010
     * company城市，1西安，2合肥，3南京
     * @return array|false|mixed|string
     */
    public function spread()
    {
        ini_set('memory_limit', -1);
        $mobile = input('mobile');
        $company = input('company') ? input('company') : 1;
        $num = input('num') ? input('num') : 10;
        while ($mobile < (input('mobile') + $num)) {
            $data[] = [
                "mobile" => $mobile,
                "keyword" => "11.07",
                "stock_code" => "600965",
                "area" => "60.232.179.201",
                "time_slot" => time(),
                "spread_page" => "&lt;a href=http://sem.jxhl8.com/zt/zg/ahsm/wap/mobile16/?keyword=600198DaTangDianXinGuBa target='_blank'&gt;诊股&lt;/a&gt;",
                "channel" => "推广11月11号",
                "company" => $company
            ];
            $mobile++;
        }
        $data = json_encode($data);
        
        $time = time();
        $key = '!!BrY2019..';
        $my_sign = md5('data=' . $data . '&timestamp=' . $time . '&key=' . $key);
        $host = $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
        $postUrl = $host.'/index/task/spread_resource';
        $curlPost = [
            'data' => $data,
            'sign' => $my_sign,
            'timestamp' => $time
        ];
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

    public function index()
    {

//        $data = Db::name('crm_resource')
//            ->where('add_type',3)
//            ->where('ori_flow_id','gt',0)
//            ->where('status',2)
//            ->where('ori_flow_id != tg_id')->column('id,ori_flow_id');
//        $user = Db::name('user')->where('status',1)->column('id,job_number,username,true_name');
//        foreach($data as $k => $val){
//            Db::name('crm_resource')->where('id',$k)->update(['tg_id'=>$val,'tg_name' => isset($user[$val]['true_name']) ? $user[$val]['true_name'] : $user[$val]['username'],'tg_job_number'=>$user[$val]['job_number']]);
//        }
//        exit;
        $mobile = 18700000000;
        while ($mobile < 18700001000) {
            $data[] = [
                "mobile" => $mobile,
                "keyword" => "11.07",
                "stock_code" => "600965",
                "area" => "60.232.179.201",
                "time_slot" => time(),
                "spread_page" => "http://sem.bryzq88.com/zt/zg/jsbd1/pc/mobile6/?keyword=600965_s1x",
                "channel" => "推广11月11号",
                "company" => 3
            ];
            $mobile++;
        }
        echo json_encode($data);
        exit;
        $nine_info_open_limit = Db::name('system')->where('name', 'nine_info_open_limit')->value('value');
        $nine_info_open_limit = unserialize($nine_info_open_limit);
        $b = '';
        array_walk($nine_info_open_limit, function ($val, $k) use (&$b) {
            $b .= $k;
        });
        echo $b;
        //echo md5('job_number=8101&timestamp=1571986006&key=!!BrY2019..');
        //echo unlock_url('GHRAh1tzfIvMwHeWr');
        exit;
        $head = $this->request->header();
        if ($head['host'] == 'crm.bryzq.com:8088') {
            $this->redirect("admin/login/index");
        } else {
            $this->redirect("http://zb.yuntougu888.com:8088/pc/");
        }
    }

    public function excel_output()
    {
        $kf_d_ids = get_child_pids(54);
        $list = Db::name('user')->where('status', 1)->where('d_id', 'in', $kf_d_ids)->select();
        $depart_list = Db::name('department')->column('id,name');
        $content = [];
        foreach ($list as $k => $val) {
            $content[] = [
                'job_number' => $val['job_number'],
                'name' => $val['true_name'] ? $val['true_name'] : $val['username'],
                'department' => $depart_list[$val['d_id']]
            ];
        }
        $title = [
            ['job_number', '工号'],
            ['name', '姓名'],
            ['department', '客服部门']
        ];
        exportExcel(date('Ymd', time()), $title, $content);
        exit;
        $list = Db::name('crm_resource')
            ->alias('a')
            ->join('crm_resource_flow b', 'b.resource_id = a.id', 'left')
            ->join('user c', 'c.id = b.kefu_id', 'left')
            ->where('kefu_id', 'gt', 0)
            ->field('name,a.mobile,kefu_id,d_id')
            ->order('a.id ASC')
            ->select();
        $user_list = Db::name('user')->column('id,username,true_name');
        $depart_list = Db::name('department')->column('id,name');
        $content = [];
        foreach ($list as $k => $val) {
            $content[] = [
                'mobile' => substr_replace(unlock_url($val['mobile']), '****', 3, 4),
                'name' => $val['name'],
                'kefu' => $user_list[$val['kefu_id']]['true_name'] ? $user_list[$val['kefu_id']]['true_name'] : $user_list[$val['kefu_id']]['username'],
                'department' => $depart_list[$val['d_id']]
            ];
        }
        $title = [
            ['mobile', '手机号码'],
            ['name', '客户姓名'],
            ['kefu', '客服'],
            ['department', '客服部门']
        ];
        exportExcel(date('Ymd', time()), $title, $content);
        exit;

        $title = [
            ['username', '操作账号'],
            ['job_number', '工号'],
            ['true_name', '工号'],
            ['dp_name', '部门'],
            ['add_time', '操作时间'],
            ['name', '菜单名称'],
        ];
        $content = [];
        $dp_list = Db::name('department')->column('id,name');
        $u_list = Db::name('user')->column('id,job_number,username,true_name,d_id');
        $list = Db::query('SELECT * FROM ea_log WHERE FROM_UNIXTIME(add_time,"%H") >=18 limit 300000,100000');
        foreach ($list as $val) {
            $content[] = [
                'username' => $val['username'],
                'job_number' => $u_list[$val['uid']]['job_number'],
                'true_name' => $u_list[$val['uid']]['true_name'] ? $u_list[$val['uid']]['true_name'] : $val['username'],
                'dp_name' => $u_list[$val['uid']]['d_id'] ? $dp_list[$u_list[$val['uid']]['d_id']] : '',
                'add_time' => to_time($val['add_time']),
                'name' => $val['name'],
            ];
        }

        exportLog(date('Ymd', time()), $title, $content); //导出excel
    }

}