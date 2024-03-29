<?php
namespace app\common\model;

use think\Db;
use think\Model;
use app\common\model\AuthGroup as AuthGroupModel;


class AuthRule extends Model
{
	public function getAllNode($id,$auth_id) {
		$auth_group_model = new AuthGroupModel();
		$auth_group_data = $auth_group_model->find($id)->toArray();
		$auth_rules      = explode(',', $auth_group_data['rules']);
	
		//$map['remark']='';
		$idarr=$this->where('pid=0')->where(array('auth_id'=>$auth_id))->column('id');

		//$map['pid']=array('in',$idarr);
		$res = $this->field('id,pid,title')->where(array('auth_id'=>$auth_id))->select();
		$res = collection($res)->toArray();
		
		$all_node = array();
		$sub_node = array();
		
	
		foreach ($res as $v) {
			//if ($v['pid']==0){
			
			
			
				if (in_array($v['pid'], $idarr)){
				//为顶级菜单的子菜单
				$all_node[] = $v;
			}else{
				//次顶级菜单
				$sub_node[$v['pid']][] = $v;
			
			}
		
	
		}
		
		foreach ($all_node as $k => $v) {
			
			
			
			if (isset($sub_node[$v['id']]))
			{
				foreach ($sub_node[$v['id']] as $k1 => $v1)	{
					if(in_array($v1['id'], $auth_rules)){
						$sub_node[$v['id']][$k1]['checked'] = 1;
					}else{
						$sub_node[$v['id']][$k1]['checked'] =0;
					}
				}
				$all_node[$k]['subnode'] = $sub_node[$v['id']];
				
	
			}else{
				$all_node[$k]['subnode'] =0;
				
			}
			if(in_array($v['id'], $auth_rules)){
				$all_node[$k]['checked'] = 1;
			}else{
				$all_node[$k]['checked'] =0;
			}	
		}
		$res = $all_node;
		return $res;
	}

	public function getAllNodeList($id,$auth_id) {
		$auth_group_model = new AuthGroupModel();
		$auth_group_data = $auth_group_model->find($id)->toArray();
		$auth_rules      = explode(',', $auth_group_data['rules']);
	
		//$map['remark']='';
		$idarr=$this->where('pid=0')->where(array('auth_id'=>$auth_id))->where('status',1)->column('id');

		//$map['pid']=array('in',$idarr);
		$res = $this->field('id,pid,title')->where(array('auth_id'=>$auth_id))->where('status',1)->select();
		$res = collection($res)->toArray();
		
		$all_node = array();
		$sub_node = array();
		$node_list = $this->where('pid=0')->where(array('auth_id'=>$auth_id))->where('status',1)->field('id,title,sort')->order('sort desc,id asc')->select();
		$node_list = collection($node_list)->toArray();
		
		foreach ($res as $v) {
			
			if (in_array($v['pid'], $idarr)){
				//为顶级菜单的子菜单
				$all_node[] = $v;
			}else{
				//次顶级菜单
				$sub_node[$v['pid']][] = $v;
			
			}
		
	
		}
		
		foreach ($all_node as $k => $v) {
			
			if (isset($sub_node[$v['id']]))
			{
				foreach ($sub_node[$v['id']] as $k1 => $v1)	{
					if(in_array($v1['id'], $auth_rules)){
						$sub_node[$v['id']][$k1]['checked'] = 1;
					}else{
						$sub_node[$v['id']][$k1]['checked'] =0;
					}
				}
				$all_node[$k]['subnode'] = $sub_node[$v['id']];
				
	
			}else{
				$all_node[$k]['subnode'] =0;
				
			}
			if(in_array($v['id'], $auth_rules)){
				$all_node[$k]['checked'] = 1;
			}else{
				$all_node[$k]['checked'] =0;
			}	

		}
		
		$pid_all_node = array();
		foreach ($all_node as $key => $val) {
			$pid_all_node[$val['pid']][] = $val;
		}
		
		foreach ($node_list as $key => $val) {
			if(isset($pid_all_node[$val['id']])){
				$node_list[$key]['subnode'] = $pid_all_node[$val['id']];
			}else{
				$node_list[$key]['subnode'] = 0;
			}
			
		}
		
		$res = $node_list;
		return $res;
	}

}