<?php
/**
 * 资源流转定时处理
 * Created by PhpStorm.
 * User: fangjian
 * Date: 2019/9/20
 * Time: 13:58
 */

namespace app\index\controller;

use think\Controller;
use think\Db;



class Task extends Controller
{
    public function test(){

    }
    /**
     * 推广资源插入接口
     */
    public function spread_resource()
    {
        $data = input('post.data');
    
        $timestamp = input('post.timestamp');
        $sign = input('post.sign');
        file_put_contents('spread.txt', input());
        $key = '!!BrY2019..';
        if (!$data || !$timestamp || !$sign) {
            data_echo([], 0, '缺少必要参数！');
        }
        if (time() - $timestamp > 120) {
            //data_echo([], 0, '请求超时！');
        }
        $my_sign = md5('data=' . $data . '&timestamp=' . $timestamp . '&key=' . $key);
        if ($my_sign != $sign) {
            //data_echo([], 0, '签名有误！');
        }
        $data = json_decode($data, true);

        if (!empty($data) && is_array($data)) {
            $insertData = [];
            $num = 0;

            //手机号过滤
            $mobile_filter = [];
            $filter_rule = Db::name('system')->where('name', 'seo_import_resource_filter')->value('value');
            if ($filter_rule) {
                $filter_rule = json_decode(unserialize($filter_rule), true);
                if ($filter_rule['jiange'] && $filter_rule['days']) {
                    $mobiles = Db::name('crm_resource')->where('add_time', 'gt', time() - ($filter_rule['days'] * 86400))->column('mobile');
                    $mobile_filter = array_merge($mobile_filter, $mobiles);
                }
                if ($filter_rule['heimingdan']) {
                    $mobiles = Db::name('crm_filter_mobile')->where('type', 2)->column('mobile');
                    $mobile_filter = array_merge($mobile_filter, $mobiles);
                }
                if ($filter_rule['tousu']) {
                    $mobiles = Db::name('crm_filter_mobile')->where('type', 3)->column('mobile');
                    $mobile_filter = array_merge($mobile_filter, $mobiles);
                }
                if ($filter_rule['chengjiao']) {
                    $mobiles = Db::name('crm_resource')->where('status', 'gt', 4)->column('mobile');
                    $mobile_filter = array_merge($mobile_filter, $mobiles);
                }
            }

            //是否开启自动分配,自动分配规则，开启资源互换则西安南京互换，合肥根据概率分配三四五营业部
            $is_auto_allot = Db::name('system')->where('name', 'seo_resource_auto_rule')->value('value');
            $is_auto_allot = $is_auto_allot ? json_decode(unserialize($is_auto_allot), true) : false;
            
            $filter_mobiles =[];
            foreach ($data as $val) {
                //spread_page取汉字
                $res=array();
                preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$val["spread_page"],$res);
                $spread_cat = "";
                if (isset($res[0][0])){
                    $spread_cat = $res[0][0];
                }
                //spread_page取url
                $spread = "";
                $res = explode("href=",$val["spread_page"]);
                if (isset($res[1])){
                    $res_data = explode("target=",$res[1]);
                }
                if (!empty($res_data[0])){
                    $spread = $res_data[0];
                }
                //过滤
                $mobile = lock_url($val['mobile']);
                if (!check_mobile_number($val['mobile']) || in_array($mobile, $mobile_filter)) {
                    $a = $val;
                    $a['mobile'] = lock_url($val['mobile']);
                    $a['add_time'] = time();
                    $filter_mobiles[] = $a;
                    continue;
                }

                $insertData[] = [
                    'catid' => 1,
                    'name' => isset($val['name']) ? $val['name'] : null,
                    'mobile' => $mobile,
                    'resource_type' => 86,
                    'stock_code' => $val['stock_code'] ? $val['stock_code'] : null,
                    'status' => 1,
                    'add_id' => 1,
                    'add_time' => time(),
                    'add_type' => 3,
                    'spread_cat' => $spread_cat, //推广主题名称类型
                    'spread_page' => $spread,
                    'channel' => $val['channel'] ? $val['channel'] : null,
                    'time_slot' => $val['time_slot'] ? $val['time_slot'] : null,
                    'area' => $val['area'] ? $val['area'] : null,
                    'company' => $val['company'] ? $val['company'] : null,
                    'keyword' => $val['keyword'] ? $val['keyword'] : null,
                ];
                $num++;
            }

            if ($is_auto_allot && $is_auto_allot['auto'] == 1) {
                //自动分配
                wait_resource_auto_allot(1); //推广资源插入接口分配    
            }

            if(!empty($filter_mobiles)){
                Db::name('crm_spread_filter_num')->insertAll($filter_mobiles);
            }

            if (empty($insertData)) {
                data_echo(null, 1, '数据已全部去重');
            }
            $res = Db::name('crm_resource')->insertAll($insertData);
            if (!$res) {
                data_echo(null, 0, '提交失败');
            }
            //添加admin 营销副总裁 通知
            //查询含有待分配资源池的group
            $rule_id = Db::name("auth_rule")->where(array("name"=>"crm/seo/wait_resource"))->value("id");
            $groups = Db::name("auth_group")->where("rules","like","%$rule_id%")->field("id,rules")->select();
            $group_ids = "";
            foreach ($groups as $key =>$value){
                $arr = explode(",",$value["rules"]);
                if(in_array($rule_id,$arr)){
                   $group_ids.=$value["id"].",";
                }
            }
            //添加管理组  全部权限
            $group_ids = $group_ids."1";
            $uids = Db::name("auth_group_access")->where("group_id","in",$group_ids)->column("uid");
            $uids=  array_unique($uids);
            $insert = array();
            foreach ($uids as $k =>$v){
                array_push($insert,array("uid"=>$v,"num"=>$num,"type"=>1));
            }
            Db::name("crm_allot_notice")->insertAll($insert);
            foreach ($insert as $key =>$value){
                get_url_contents(config("notice_address")."/?uid=".$value["uid"]);
            }
            data_echo(null, 1, '请求成功，成功提交' . $num . '条');
        } else {
            data_echo(null, 0, '请提交要操作的数据');
        }
    }
    /**
     * 推广资源插入接口-2019.11.15之前
     */
    public function spread_resource_ori()
    {
        //try {
            $data = input('post.data');
            $timestamp = input('post.timestamp');
            $sign = input('post.sign');
            file_put_contents('spread.txt', input());
            $key = '!!BrY2019..';
            if (!$data || !$timestamp || !$sign) {
                data_echo([], 0, '缺少必要参数！');
            }
            if (time() - $timestamp > 120) {
                //data_echo([], 0, '请求超时！');
            }
            $my_sign = md5('data=' . $data . '&timestamp=' . $timestamp . '&key=' . $key);
            if ($my_sign != $sign) {
                //data_echo([], 0, '签名有误！');
            }
            $data = json_decode($data, true);
            if (!empty($data) && is_array($data)) {
                $insertData = [];
                $num = 0;

                //手机号过滤
                $mobile_filter = [];
                $filter_rule = Db::name('system')->where('name', 'seo_import_resource_filter')->value('value');
                if ($filter_rule) {
                    $filter_rule = json_decode(unserialize($filter_rule), true);
                    if ($filter_rule['jiange'] && $filter_rule['days']) {
                        $mobiles = Db::name('crm_resource')->where('add_time', 'gt', time() - ($filter_rule['days'] * 86400))->column('mobile');
                        $mobile_filter = array_merge($mobile_filter, $mobiles);
                    }
                    if ($filter_rule['heimingdan']) {
                        $mobiles = Db::name('crm_filter_mobile')->where('type', 2)->column('mobile');
                        $mobile_filter = array_merge($mobile_filter, $mobiles);
                    }
                    if ($filter_rule['tousu']) {
                        $mobiles = Db::name('crm_filter_mobile')->where('type', 3)->column('mobile');
                        $mobile_filter = array_merge($mobile_filter, $mobiles);
                    }
                    if ($filter_rule['chengjiao']) {
                        $mobiles = Db::name('crm_resource')->where('status', 'gt', 4)->column('mobile');
                        $mobile_filter = array_merge($mobile_filter, $mobiles);
                    }
                }

                //是否开启自动分配,自动分配规则，开启资源互换则西安南京互换，合肥根据概率分配三四五营业部
                $is_auto_allot = Db::name('system')->where('name', 'seo_resource_auto_rule')->value('value');
                $is_auto_allot = $is_auto_allot ? json_decode(unserialize($is_auto_allot), true) : false;
                $user1 = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 1)->where('group_id', 101)->find();
                $user3 = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 34)->where('group_id', 101)->find();
                $user4 = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 35)->where('group_id', 101)->find();
                $user5 = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 51)->where('group_id', 101)->find();
                $user6 = Db::name('user')->alias('a')->join('auth_group_access b', 'b.uid = a.id', 'left')->where('d_id', 52)->where('group_id', 101)->find();
                $user1allot = false;
                $user3allot = false;
                $user4allot = false;
                $user5allot = false;
                $user6allot = false;
                $filter_mobiles =[];
                if ($is_auto_allot && $is_auto_allot['auto'] == 1) {
                    $count = count($data);
                    $area1 = 0;
                    $area3 = 0;
                    $area4 = 0;
                    $area5 = 0;
                    $area6 = 0;
                    $a = array_rand([1 => 1, 3 => 3, 4 => 4, 5 => 5, 6 => 6], 1);
                    foreach ($data as $k => $val) {
                        $mobile = lock_url($val['mobile']);
                        if (!check_mobile_number($val['mobile']) || in_array($mobile, $mobile_filter)) {
                            $a = $val;
                            $a['mobile'] = lock_url($val['mobile']);
                            $a['add_time'] = time();
                            $filter_mobiles[] = $a;
                            continue;
                        }
                        switch ($a) {
                            case 1:
                                if ($area1 < ceil($count * ($is_auto_allot['area1'] / 100))) {
                                    $area1++;
                                    $user1allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user1['id'];
                                    $insertData[$k]['tg_name'] = $user1['true_name'] ? $user1['true_name'] : $user1['username'];
                                    $insertData[$k]['tg_job_number'] = $user1['job_number'];
                                } elseif ($area3 < ceil($count * ($is_auto_allot['area3'] / 100))) {
                                    $area3++;
                                    $user3allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user3['id'];
                                    $insertData[$k]['tg_name'] = $user3['true_name'] ? $user3['true_name'] : $user3['username'];
                                    $insertData[$k]['tg_job_number'] = $user3['job_number'];
                                } elseif ($area4 < ceil($count * ($is_auto_allot['area4'] / 100))) {
                                    $area4++;
                                    $user4allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user4['id'];
                                    $insertData[$k]['tg_name'] = $user4['true_name'] ? $user4['true_name'] : $user4['username'];
                                    $insertData[$k]['tg_job_number'] = $user4['job_number'];
                                } elseif ($area5 < ceil($count * ($is_auto_allot['area5'] / 100))) {
                                    $area5++;
                                    $user5allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user5['id'];
                                    $insertData[$k]['tg_name'] = $user5['true_name'] ? $user5['true_name'] : $user5['username'];
                                    $insertData[$k]['tg_job_number'] = $user5['job_number'];
                                } elseif ($area6 < ceil($count * ($is_auto_allot['area6'] / 100))) {
                                    $area6++;
                                    $user6allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user6['id'];
                                    $insertData[$k]['tg_name'] = $user6['true_name'] ? $user6['true_name'] : $user6['username'];
                                    $insertData[$k]['tg_job_number'] = $user6['job_number'];
                                }
                                break;
                            case 3:
                                if ($area3 < ceil($count * ($is_auto_allot['area3'] / 100))) {
                                    $area3++;
                                    $user3allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user3['id'];
                                    $insertData[$k]['tg_name'] = $user3['true_name'] ? $user3['true_name'] : $user3['username'];
                                    $insertData[$k]['tg_job_number'] = $user3['job_number'];
                                } elseif ($area4 < ceil($count * ($is_auto_allot['area4'] / 100))) {
                                    $area4++;
                                    $user4allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user4['id'];
                                    $insertData[$k]['tg_name'] = $user4['true_name'] ? $user4['true_name'] : $user4['username'];
                                    $insertData[$k]['tg_job_number'] = $user4['job_number'];
                                } elseif ($area5 < ceil($count * ($is_auto_allot['area5'] / 100))) {
                                    $area5++;
                                    $user5allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user5['id'];
                                    $insertData[$k]['tg_name'] = $user5['true_name'] ? $user5['true_name'] : $user5['username'];
                                    $insertData[$k]['tg_job_number'] = $user5['job_number'];
                                } elseif ($area6 < ceil($count * ($is_auto_allot['area6'] / 100))) {
                                    $area6++;
                                    $user6allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user6['id'];
                                    $insertData[$k]['tg_name'] = $user6['true_name'] ? $user6['true_name'] : $user6['username'];
                                    $insertData[$k]['tg_job_number'] = $user6['job_number'];
                                } elseif ($area1 < ceil($count * ($is_auto_allot['area1'] / 100))) {
                                    $area1++;
                                    $user1allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user1['id'];
                                    $insertData[$k]['tg_name'] = $user1['true_name'] ? $user1['true_name'] : $user1['username'];
                                    $insertData[$k]['tg_job_number'] = $user1['job_number'];
                                }
                                break;
                            case 4:
                                if ($area4 < ceil($count * ($is_auto_allot['area4'] / 100))) {
                                    $area4++;
                                    $user4allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user4['id'];
                                    $insertData[$k]['tg_name'] = $user4['true_name'] ? $user4['true_name'] : $user4['username'];
                                    $insertData[$k]['tg_job_number'] = $user4['job_number'];
                                } elseif ($area5 < ceil($count * ($is_auto_allot['area5'] / 100))) {
                                    $area5++;
                                    $user5allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user5['id'];
                                    $insertData[$k]['tg_name'] = $user5['true_name'] ? $user5['true_name'] : $user5['username'];
                                    $insertData[$k]['tg_job_number'] = $user5['job_number'];
                                } elseif ($area6 < ceil($count * ($is_auto_allot['area6'] / 100))) {
                                    $area6++;
                                    $user6allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user6['id'];
                                    $insertData[$k]['tg_name'] = $user6['true_name'] ? $user6['true_name'] : $user6['username'];
                                    $insertData[$k]['tg_job_number'] = $user6['job_number'];
                                } elseif ($area1 < ceil($count * ($is_auto_allot['area1'] / 100))) {
                                    $area1++;
                                    $user1allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user1['id'];
                                    $insertData[$k]['tg_name'] = $user1['true_name'] ? $user1['true_name'] : $user1['username'];
                                    $insertData[$k]['tg_job_number'] = $user1['job_number'];
                                } elseif ($area3 < ceil($count * ($is_auto_allot['area3'] / 100))) {
                                    $area3++;
                                    $user3allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user3['id'];
                                    $insertData[$k]['tg_name'] = $user3['true_name'] ? $user3['true_name'] : $user3['username'];
                                    $insertData[$k]['tg_job_number'] = $user3['job_number'];
                                }
                                break;
                            case 5:
                                if ($area5 < ceil($count * ($is_auto_allot['area5'] / 100))) {
                                    $area5++;
                                    $user5allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user5['id'];
                                    $insertData[$k]['tg_name'] = $user5['true_name'] ? $user5['true_name'] : $user5['username'];
                                    $insertData[$k]['tg_job_number'] = $user5['job_number'];
                                } elseif ($area6 < ceil($count * ($is_auto_allot['area6'] / 100))) {
                                    $area6++;
                                    $user6allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user6['id'];
                                    $insertData[$k]['tg_name'] = $user6['true_name'] ? $user6['true_name'] : $user6['username'];
                                    $insertData[$k]['tg_job_number'] = $user6['job_number'];
                                } elseif ($area1 < ceil($count * ($is_auto_allot['area1'] / 100))) {
                                    $area1++;
                                    $user1allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user1['id'];
                                    $insertData[$k]['tg_name'] = $user1['true_name'] ? $user1['true_name'] : $user1['username'];
                                    $insertData[$k]['tg_job_number'] = $user1['job_number'];
                                } elseif ($area3 < ceil($count * ($is_auto_allot['area3'] / 100))) {
                                    $area3++;
                                    $user3allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user3['id'];
                                    $insertData[$k]['tg_name'] = $user3['true_name'] ? $user3['true_name'] : $user3['username'];
                                    $insertData[$k]['tg_job_number'] = $user3['job_number'];
                                } elseif ($area4 < ceil($count * ($is_auto_allot['area4'] / 100))) {
                                    $area4++;
                                    $user4allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user4['id'];
                                    $insertData[$k]['tg_name'] = $user4['true_name'] ? $user4['true_name'] : $user4['username'];
                                    $insertData[$k]['tg_job_number'] = $user4['job_number'];
                                }
                                break;
                            case 6:
                                if ($area6 < ceil($count * ($is_auto_allot['area6'] / 100))) {
                                    $area6++;
                                    $user6allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user6['id'];
                                    $insertData[$k]['tg_name'] = $user6['true_name'] ? $user6['true_name'] : $user6['username'];
                                    $insertData[$k]['tg_job_number'] = $user6['job_number'];
                                } elseif ($area1 < ceil($count * ($is_auto_allot['area1'] / 100))) {
                                    $area1++;
                                    $user1allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user1['id'];
                                    $insertData[$k]['tg_name'] = $user1['true_name'] ? $user1['true_name'] : $user1['username'];
                                    $insertData[$k]['tg_job_number'] = $user1['job_number'];
                                } elseif ($area3 < ceil($count * ($is_auto_allot['area3'] / 100))) {
                                    $area3++;
                                    $user3allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user3['id'];
                                    $insertData[$k]['tg_name'] = $user3['true_name'] ? $user3['true_name'] : $user3['username'];
                                    $insertData[$k]['tg_job_number'] = $user3['job_number'];
                                } elseif ($area4 < ceil($count * ($is_auto_allot['area4'] / 100))) {
                                    $area4++;
                                    $user4allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user4['id'];
                                    $insertData[$k]['tg_name'] = $user4['true_name'] ? $user4['true_name'] : $user4['username'];
                                    $insertData[$k]['tg_job_number'] = $user4['job_number'];
                                } elseif ($area5 < ceil($count * ($is_auto_allot['area5'] / 100))) {
                                    $area5++;
                                    $user5allot = true;
                                    $insertData[$k]['status'] = -1;
                                    $insertData[$k]['tg_id'] = $user5['id'];
                                    $insertData[$k]['tg_name'] = $user5['true_name'] ? $user5['true_name'] : $user5['username'];
                                    $insertData[$k]['tg_job_number'] = $user5['job_number'];
                                }
                                break;
                        }


                        $insertData[$k]['catid'] = 1;
                        $insertData[$k]['name'] = isset($val['name']) ? $val['name'] : null;
                        $insertData[$k]['mobile'] = $mobile;
                        $insertData[$k]['resource_type'] = 86;
                        $insertData[$k]['stock_code'] = $val['stock_code'] ? $val['stock_code'] : null;
                        $insertData[$k]['add_id'] = 1;
                        $insertData[$k]['add_time'] = time();
                        $insertData[$k]['add_type'] = 3;
                        $insertData[$k]['spread_page'] = $val['spread_page'] ? $val['spread_page'] : null;
                        $insertData[$k]['channel'] = $val['channel'] ? $val['channel'] : null;
                        $insertData[$k]['time_slot'] = $val['time_slot'] ? $val['time_slot'] : null;
                        $insertData[$k]['area'] = $val['area'] ? $val['area'] : null;
                        $insertData[$k]['company'] = $val['company'] ? $val['company'] : null;
                        $insertData[$k]['keyword'] = $val['keyword'] ? $val['keyword'] : null;
                        $num++;
                    }
                } else {
                    foreach ($data as $val) {
                        $mobile = lock_url($val['mobile']);
                        if (!check_mobile_number($val['mobile']) || in_array($mobile, $mobile_filter)) {
                            continue;
                        }
                        $insertData[] = [
                            'catid' => 1,
                            'name' => null,
                            'mobile' => $mobile,
                            'resource_type' => 86,
                            'stock_code' => $val['stock_code'] ? $val['stock_code'] : null,
                            'status' => 1,
                            'add_id' => 1,
                            'add_time' => time(),
                            'add_type' => 3,
                            'spread_page' => $val['spread_page'] ? $val['spread_page'] : null,
                            'channel' => $val['channel'] ? $val['channel'] : null,
                            'time_slot' => $val['time_slot'] ? $val['time_slot'] : null,
                            'area' => $val['area'] ? $val['area'] : null,
                            'company' => $val['company'] ? $val['company'] : null,
                            'keyword' => $val['keyword'] ? $val['keyword'] : null,
                        ];
                        $num++;
                    }
                }
                if(!empty($filter_mobiles)){
                    Db::name('crm_spread_filter_num')->insertAll($filter_mobiles);
                }
                //dump($insertData);exit;
                if (empty($insertData)) {
                    data_echo(null, 0, '数据已全部去重');
                }
                $res = Db::name('crm_resource')->insertAll($insertData);
                if (!$res) {
                    data_echo(null, 0, '提交失败');
                }
                if ($is_auto_allot && $is_auto_allot['auto'] == 1) {
                    if ($user1allot) {
                        auto_allot($user1['id']);
                    }
                    if ($user3allot) {
                        auto_allot($user3['id']);
                    }
                    if ($user4allot) {
                        auto_allot($user4['id']);
                    }
                    if ($user5allot) {
                        auto_allot($user5['id']);
                    }
                    if ($user6allot) {
                        auto_allot($user6['id']);
                    }
                }
                data_echo(null, 1, '请求成功，成功提交' . $num . '条');
            } else {
                data_echo(null, 0, '请提交要操作的数据');
            }
//        } catch (\Exception $e) {
//            data_echo(null, 0, '请求失败');
//        }
    }

    //待分配资源池自动分配
    public function wait_resource_auto_allot()
    {
        wait_resource_auto_allot();
        exit;
    }

    //已分配资源再次自动下发
    public function wait_allot_auto_allot()
    {
        wait_allot_auto_allot();
        exit;
    }

    /**
     *  4.若资源类型为“网络推广资源”的记录，跟进间隔值达到设定值时，该记录顺位流转到同组的下一位客服，流转后，资源开始时间更新，跟进间隔更新。
     *  4.1.若流转到小组最后一位员工，仍然超期，流转到下一组的第一位员工,同一业务部下面，不能跨业务部。
     *  4.2.若流转到最后一组的最后一位员工，仍然超期，资源进入无效资源记录。
     *  4.3.若资源持有人是业务员以外的角色，资源超期，退回上级的已分配资源池，营销老总的资源超期，资源直接回到待分配资源池。
     */
    public function update_follow_resource()
    {
        if (date('H') < 8 || date('H') > 18) {
            return;
        }
        $resource_slot = input('resource_slot');
        if (!$resource_slot) {
            echo '丢失resource_slot参数';
            exit;
        }
        $catid = input('catid');
        if (!$catid) {
            echo '丢失catid参数';
            exit;
        }
        $slot = Db::name('system')->where('name', $resource_slot)->value('value');
        $t1 = microtime(true);
        $tg_ids = Db::name('crm_resource_transfer')->where('catid', $catid)->where('status', 1)->column('uid');
        if (empty($tg_ids)) {
            echo '流转已关闭';
            exit;
        }
        $ids = Db::name('crm_resource')->where('catid', $catid)->where('tg_id', 'in', $tg_ids)->where('add_type', 3)->where('status', 2)->where('follow_time', 'lt', time() - $slot * 60)->column('id,tg_id,ori_flow_id');
        if (empty($ids)) {
            echo '没有资源可流转';
            exit;
        }
        $nids1 = [];  //业务员
        $nids2 = [];  //业务员之上
        $nids3 = [];  //营销老总
        $group_access = Db::name('auth_group_access')->column('uid,group_id');
        //根据角色划分
        foreach ($ids as $k => $val) {
            if (!isset($group_access[$val['tg_id']])) {
                continue;
            }
            if ($group_access[$val['tg_id']] == 112) {  //销售
                $nids1[$val['tg_id']][] = $k;
            } elseif ($group_access[$val['tg_id']] == 101 || $group_access[$val['tg_id']] == 111) {
                $nids3[$val['tg_id']][] = $k;
            } else {
                $nids2[$val['tg_id']][] = $k;
            }
        }
        //资源流转到下一位
        $department = Db::name('department')->column('id,pid');
        $user = Db::name('user')->where('d_id', 'in', get_child_pids(20))->where('status', 1)->order('job_number')->column('id,job_number,true_name,d_id');
        if ($nids1) {
            //部门按业务部划分
            //$depart1 = Db::name('department')->where('pid', 20)->column('id');
            //$depart2 = Db::name('department')->where('pid', 'in', $depart1)->column('id');
            //$depart3 = Db::name('department')->where('pid', 'in', $depart2)->order('code')->column('id,pid');
            $depart = [];
            foreach ($user as $k => $val) {
                if (isset($department[$val['d_id']])) {
                    //去掉组长 不能流转到组长头上
                    if (!isset($group_access[$k]) || $group_access[$k] != 112) {
                        continue;
                    }
                    $depart[$department[$val['d_id']]][] = $k;
                }
            }
            foreach ($nids1 as $k => $val) {
                if (!isset($user[$k])) {
                    continue;
                }

                $keys = array_keys($depart[$department[$user[$k]['d_id']]], $k, true);
                $keys = $keys[0] + 1;
                if (!isset($depart[$department[$user[$k]['d_id']]][$keys])) {
                    $keys = 0;
                }
                $tg = $depart[$department[$user[$k]['d_id']]][$keys];
                //是否该业务部下成员已经流转完，流转完进入无效资源列表
                $updateIds = [];
                $invalidIds = [];
                foreach ($val as $v) {
                    if ($tg == $ids[$v]['ori_flow_id']) {
                        $invalidIds[] = $v;
                    } else {
                        $updateIds[] = $v;
                    }
                }
                if (!empty($invalidIds)) {
                    Db::name('crm_resource')->where('id', 'in', $invalidIds)->update(['invalid_status' => 1]);
                    Db::name('crm_resource_info')->where('resource_id', 'in', $invalidIds)->update(['invalid_time' => time()]);
                }
                if (!empty($updateIds)) {
                    Db::name('crm_resource')->where('id', 'in', $updateIds)->update(['tg_id' => $tg, 'tg_name' => $user[$tg]['true_name'], 'tg_job_number' => $user[$tg]['job_number'], 'resource_start_time' => time(), 'resource_end_time' => time() + 86400 * 30, 'follow_time' => time()]);
                }
            }
        }

        //退回上级的SEO已有资源池
        if ($nids2) {
            //获取当前所属人的上级
            $user = Db::name('user')
                ->alias('a')
                ->join('auth_group_access b', 'b.uid = a.id', 'left')
                ->join('auth_group c', 'c.id = b.group_id', 'left')
                ->join('department d', 'd.id = a.d_id', 'left')
                ->where('d_id', 'in', get_child_pids(20))
                ->where('a.status', 1)->column('a.id,job_number,true_name,d_id,group_id,c.pid as cpid,d.pid as dpid');
            $duser = [];
            foreach ($user as $k => $val) {    //所属直属上级只会有一个人
                $duser[$val['d_id'] . $val['group_id']] = $k;
            }
            foreach ($nids2 as $k => $val) {
                $tg = isset($duser[$user[$k]['d_id'] . $user[$k]['cpid']]) ? $duser[$user[$k]['d_id'] . $user[$k]['cpid']] : (isset($duser[$user[$k]['dpid'] . $user[$k]['cpid']]) ? $duser[$user[$k]['dpid'] . $user[$k]['cpid']] : 0);
                if (!$tg) {
                    continue;
                }
                Db::name('crm_resource')->where('id', 'in', $val)->update(['status' => -1, 'tg_id' => $tg, 'tg_name' => $user[$tg]['true_name'], 'tg_job_number' => $user[$tg]['job_number'], 'is_second' => 1]);
            }
        }

        //资源直接回到SEO资源总池
        if ($nids3) {
            Db::name('crm_resource')->where('id', 'in', array_values($nids3))->update(['status' => 1, 'is_second' => 1]);
        }

        $t2 = microtime(true);
        echo $t2 - $t1;
        exit;
    }

    /**
     * 当资源被标记为无法打通、未接听、拒接、空号时，设定阈值3小时，3小时后，若标签状态仍为上述值，资源退回到上级的已分配资源池。
     * 当被首次被标记为无法打通、未接听、拒接、空号，二次下发再度被标记为上述标签之一，3小时后，若标签仍为上述值之一，该记录进入无效资源记录。
     */
    public function update_invalid_resource()
    {
        $t1 = microtime(true);
        $invalid_resource_slot = Db::name('system')->where('name', 'invalid_resource_slot')->value('value');
        $invalid_resource_slot = $invalid_resource_slot!='' ? $invalid_resource_slot : 3;
        $where = [
            'invalid_status' => 0,
            'delete_status' => 0,
            'recover_status' => 0,
            'status' => 2,
            'add_type' => 3,
            'bd_status' => ['in', [80, 574, 575, 576]],
            'bd_time' => ['lt', time() - $invalid_resource_slot*60*60]
        ];
        $ids = Db::name('crm_resource')->alias('a')->join('crm_resource_info b', 'b.resource_id = a.id', 'left')->where($where)->column('a.id,tg_id,is_second');
        if (!empty($ids)) {
            $back_ids = [];
            $invalid_ids = [];
            foreach ($ids as $k => $val) {
                if ($val['is_second'] == 1) {
                    $invalid_ids[] = $k;
                } else {
                    $back_ids[$val['tg_id']][] = $k; //同一个用户下可能多条资源需自动退回
                }
            }
            if (!empty($invalid_ids)) {
                Db::name('crm_resource')->where('id', 'in', $invalid_ids)->update(['invalid_status' => 1]);
                Db::name('crm_resource_info')->where('resource_id', 'in', $invalid_ids)->update(['invalid_time' => time()]);
            }
            if (!empty($back_ids)) {
                $user = Db::name('user')
                    ->alias('a')
                    ->join('auth_group_access b', 'b.uid = a.id', 'left')
                    ->join('auth_group c', 'c.id = b.group_id', 'left')
                    ->join('department d', 'd.id = a.d_id', 'left')
                    ->where('d_id', 'in', get_child_pids(20))
                    ->where('a.status', 1)->column('a.id,job_number,true_name,d_id,group_id,c.pid as cpid,d.pid as dpid');
                $duser = [];
                foreach ($user as $k => $val) {    //所属直属上级只会有一个人
                    $duser[$val['d_id'] . $val['group_id']] = $k;
                }
                foreach ($back_ids as $k => $val) {
                    $tg = isset($duser[$user[$k]['d_id'] . $user[$k]['cpid']]) ? $duser[$user[$k]['d_id'] . $user[$k]['cpid']] : (isset($duser[$user[$k]['dpid'] . $user[$k]['cpid']]) ? $duser[$user[$k]['dpid'] . $user[$k]['cpid']] : 0);
                    if (!$tg) {
                        continue;
                    }
                    Db::name('crm_resource')->where('id', 'in', $val)->update(['status' => -1, 'tg_id' => $tg, 'tg_name' => $user[$tg]['true_name'], 'tg_job_number' => $user[$tg]['job_number'], 'is_second' => 1]);
                }
            }
        }
        
        $t2 = microtime(true);
        if(input('?get.type')){
            return json(array('code' => 200, 'msg' => '配置成功'));
        }else{
            echo $t2 - $t1;
            exit;
        }
        
    }

    /**
     * 1.若资源类型为“网络推广资源”在待开发/意向客户中停留Y天以上（包括30天）或开户时的金额小于指定小单金额，资源退回到SEO资源总池。
     * 1.1.在待开发停留15天，意向客户停留15天。也回退回到SEO资源总池。
     */
    public function update_undone_resource()
    {
        $resource_slot = input('resource_slot');
        if (!$resource_slot) {
            echo '丢失resource_slot参数';
        }
        $catid = input('catid');
        if (!$catid) {
            echo '丢失catid参数';
        }
        $spread_resource_slot = Db::name('system')->where('name', $resource_slot)->value('value');
        $spread_resource_slot = $spread_resource_slot ? $spread_resource_slot : 3;
        $res = Db::name('crm_resource')
            ->where('catid', $catid)
            ->where('invalid_status', 0)
            ->where('delete_status', 0)
            ->where('recover_status', 0)
            ->where('status', 'in', [2, 3])
            ->where('add_type', 3)
            ->where('resource_start_time', 'lt', time() - $spread_resource_slot * 86400)
            ->column('id');
        dump($res);
        if(!empty($res)){
            Db::name('crm_resource')->where('id','in',$res)->update(['status' => 1, 'tg_id' => null, 'tg_job_number' => null, 'tg_name' => null, 'is_second' => 1]);
        }
        exit;
    }
}