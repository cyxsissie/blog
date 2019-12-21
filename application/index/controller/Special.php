<?php
/**
 * 特殊处理控制器
 */

namespace app\index\controller;

use Driver\Cache\Redis as redis;
use think\Controller as controller;
use think\Db;

class Special extends controller
{
    public function cs(){
        dump(lock_url(18100206995));
    }

    /**
     * 第六大区今年成交客户信息（2019年）
     */
    public function opera_export(){
        //部门查询
        $d_ids = get_child_pids(52);
        $six_user = Db::name('user')->where('d_id', 'in', $d_ids)->column('id');

        $resource_info = Db::name('crm_resource')->where(['catid'=>2,'status'=>['egt',9]])->where('pay_type','<>',9)->where('tg_id','in',$six_user)->alias('a')->join('crm_resource_flow b','a.id = b.resource_id','left')->where('b.risk_call_time','egt',1546272000)->field('name,mobile,spare_mobile,tg_id')->select();

        // dump(count($resource_info));
        // exit;
        //导出csv
        $title = [
            1 => '客户姓名',
            2 => '电话号码',
            3 => '备用号码',
        ];
        $excel_data[] = $title;
        $has_mobile = [];
        foreach ($resource_info as $key => $v) {
            $mobile = $v['mobile'];
            $spare_mobile = $v['spare_mobile'];
            // if(!$mobile && !$spare_mobile){
            //     continue;
            // }
            if(!in_array($v['tg_id'], $six_user)){
                continue;
            }
            
            $mobile = $mobile!=''?unlock_url($mobile):$mobile;
            $spare_mobile = $mobile!=''?unlock_url($spare_mobile):$spare_mobile;

            $data = [
                'name' => $v['name'],
                'mobile' => $mobile,
                'spare_mobile' => $spare_mobile,
            ];
            if(in_array($data, $has_mobile)){ //过滤重复的客户
                continue;
            }
            $has_mobile[] = $data;
            $mobile = strlen($mobile) == 11 ? substr_replace($mobile, '****', 3, 4) : $mobile;
            $spare_mobile = strlen($spare_mobile) == 11 ? substr_replace($spare_mobile, '****', 3, 4) : $spare_mobile;

            $content = array(
                $v['name'],
                $mobile,
                $spare_mobile,
            );

            $excel_data[] = $content;
            
        }
        if (count($excel_data)<=1) {
            $this->error('无可用数据！');
        }

        // dump($excel_data);
        // dump(count($excel_data));
        // exit;
        $result = ouputCsv(date('Ymd', time()).'第六大区', $excel_data); 
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