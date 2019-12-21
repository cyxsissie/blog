<?php
namespace app\common\model;

use think\Config;
use think\Db;
use think\Model;

/**
 * 退款记录
 * Class CrmRefund
 * @package app\common\model
 */
class CrmRefund extends Model
{
	
    /**
     * 插入一条退款记录
     */
    public function insert_one($id,$reason)
    {
        
        if(!$id){echo json_encode(array('code' => 0, 'msg' => '参数缺失！'));exit;}
        if(!$reason){echo json_encode(array('code' => 0, 'msg' => '请填写退款原因！'));exit;}
        
        //添加申请记录
        $resource = Db::name('crm_resource')->where('id',$id)->field('mobile,name')->find();
        
        $data = array(
            'resource_id' => $id,
            'name' => $resource['name'],
            'mobile' => $resource['mobile'],
            'reason' => $reason,
            'add_id' => session('admin_id'),
            'add_time' => time(),
        );
        
        //判断是否已经添加
        $has = $this->field('id')->where(array('resource_id'=>$id))->find();
        if($has){echo json_encode(array('code' => 0, 'msg' => '退款已申请！'));exit;}
        
        $affect = $this->insert($data);
        if($affect){
            echo json_encode(array('code' => 200, 'msg' => '申请成功，等待审批！'));
            exit;
        }else{
            echo json_encode(array('code' => 0, 'msg' => '退款申请失败！'));
            exit;
        }
    }

    //资源退款信息
    public function get_refund_info(){
        $list = $this->field('resource_id,sort,status')->select();
        $list = collection($list)->toArray();
        $list = array_column($list,null,'resource_id');
        return $list;
    }

    //获取退款记录资源id
    public function get_resource_ids(){
        $ids = $this->column('resource_id');
        return implode(',',$ids);
    }

    //获取退款记录审批状态
    public function get_resource_verify(){
        $verify = Db::name('crm_refund_verify')->alias('a')->join('ea_crm_refund r','a.refund_id = r.id','LEFT')->field('a.refund_id,avg(a.opera_status) as status,max(a.sort) as sort,r.resource_id')->group('refund_id')->select();

        $verify = collection($verify)->toArray();
        $verify = array_column($verify,null,'resource_id');
        return $verify;
    }

}