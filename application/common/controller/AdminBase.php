<?php

namespace app\common\controller;

use org\Auth;
use think\Loader;
use think\Cache;
use think\Controller;
use think\Db;
use think\Session;

/**
 * 后台公用基础控制器
 * Class AdminBase
 * @package app\common\controller
 */
class AdminBase extends Base
{
    protected function _initialize()
    {
        parent::_initialize();
    }



    protected function build_time_where($alias = null,$field = 'add_time'){
        $data = input();
        $alias = !empty($alias) ? $alias . '.' : '';
        $where = array();
        if (isset($data['start_time']) && $data['start_time']) {
            $where[$alias . $field][] = array('egt', 0);
            $where[$alias . $field][] = array('egt', strtotime($data['start_time']));
        }
        if (isset($data['end_time']) && $data['end_time']) {
            $where[$alias . $field][] = array('egt', 0);
            $where[$alias . $field][] = array('elt', strtotime($data['end_time']));
        }
        return $where;
    }

}