<?php
/**
 * Created by PhpStorm.
 * User: cc
 * Date: 2018/8/19
 * Time: 0:21
 */

namespace app\common\controller;

use Qiniu\Qauth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use org\Auth;
use think\App;
use think\exception\ErrorException;
use think\Loader;
use think\Cache;
use think\Controller;
use think\Db;
use think\Config;
use think\Session;

class Base extends Controller
{
    protected $is_admin;
    protected $admin_id;
    protected $page;
    protected $size;

    protected function _initialize()
    {
        parent::_initialize();

        $root = 'http://' . $_SERVER['HTTP_HOST'];
        $this->checkAuth();
        $this->getMenu();
        $system = Db::table('ea_live_system')->where(['id'=>1])->find();
        $this->assign('system',$system);

        $this->assign('root', $root);
        // 输出当前请求控制器（配合后台侧边菜单选中状态）
        $this->assign('controller', Loader::parseName($this->request->controller()));

        $this->is_admin = session('is_admin');

        $this->admin_id = session('admin_id');
        $this->assign('admin_id', $this->admin_id);
        $this->page = request()->param("page");
        $this->size = request()->param("size");
        $system = Db::table('ea_live_system')->where(['id'=>1])->find();
        $this->assign('system',$system);
        $this->assign('root',$root);
        empty($this->page) || $this->page < 1 ? $this->page = 1 : false;
        empty($this->size) || $this->size < 1 ? $this->size = 10 : false;

    }

    public function get_data()
    {
        //获取信息前把time处理
        $data = $this->request->param();
        foreach ($data as $key => $value) {
            if (strpos($key, 'time')) {
                $data[$key] = strtotime($value);
            }
        }
        return $data;
    }

    /**
     * 权限检查
     * @return bool
     */
    protected function checkAuth()
    {
        if (!Session::get('admin_id')) {
            $this->redirect('admin/login/index');
        }

        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();
        // 排除权限
        $not_check = Config::get('auth_pass');
        if (!in_array($module . '/' . $controller . '/' . $action, $not_check)) {
            $auth = new Auth();
            $admin_id = Session::get('admin_id');
            $is_admin = Session::get('is_admin');

            if (!$auth->check($module . '/' . $controller . '/' . $action, $admin_id) && $is_admin != 1) {
                //return json(array('code' => 0, 'msg' => '没有权限'));
                $this->error('没有权限');
            }
        }
        $not_log = Config::get('log_pass');
        if (strtolower($controller) != 'log') {
            if (!in_array($module . '/' . $controller . '/' . $action, $not_log)) {
                $data['uid'] = session('admin_id');
                $data['add_time'] = time();
                $data['controller'] = $module . '/' . $controller . '/' . $action;
                $data['username'] = session('admin_name');
                $rule = Db::table('ea_auth_rule')->where(['name' => $data['controller']])->find();
                $data['name'] = $rule['title'];
                Db::name('log')->insert($data);
            }
        }
    }

    /**
     * 获取侧边栏菜单
     */
    protected function getMenu()
    {
        $menu = [];
        $admin_id = Session::get('admin_id');
        $is_admin = Session::get('is_admin');

        //获取管理员所拥有的auth权限
        if ($is_admin == 1) {
            $app = Db::name('auth')->select();
        } else {
            $rules = Db::name('auth_group_access')->alias('a')->join('auth_group b', 'a.group_id=b.id')
                ->where(array('a.uid' => $admin_id))->field('b.rules')->find();
            $app = Db::name('auth_rule')->alias('a')->join('auth b', 'a.auth_id=b.id')->where(array('a.id' => array('in', $rules['rules'])))
                ->field('b.*')->distinct('b')->select();
        }

        $this->assign('app', $app);

        $auth = new Auth();
        $auth_id = $app['0']['id'];
        if (!empty(input('auth_id'))) {
            $auth_id = input('auth_id');
        }
        // $auth_rule_list = Db::name('auth_rule')->where('status', 1)->where(array('auth_id' => $auth_id))->order(['sort' => 'DESC', 'id' => 'ASC'])->select();

        // foreach ($auth_rule_list as $value) {
        //     if ($auth->check($value['name'], $admin_id) || $admin_id == 1) {
        //         if ($value['pid'] != 0 || $value['id'] == 104) {
        //             $value['href'] = url($value['name']);
        //         }
        //         $menu[] = $value;
        //     }
        // }


        $auth_rule_list = Db::name('auth_rule')->where('status', 1)->where('auth_id', $auth_id)->select();

        foreach ($auth_rule_list as $value) {
            if ($auth->check($value['name'], $admin_id) || $is_admin == 1) {
                if (in_array($value['name'], ['crm/resource/batch_allot', 'admin/resource_get','crm/dataAnalyse/achievement','crm/dataCallBack/analyse']) && session('admin_name') == 'admin') {
                    continue;
                }
                if ($value['pid'] != 0) {
                    $value['href'] = url($value['name']);
                }
                $menu[] = $value;
            }
        }

        $menu = !empty($menu) ? array2tree($menu) : [];
        $sort = array_column($menu, 'sort');
        // array_multisort($sort,SORT_ASC,$menu);
        foreach ($menu as $k => $val) {
            if (isset($val['children'])) {
                $sort = array_column($val['children'], 'sort');
                array_multisort($sort, SORT_ASC, $val['children']);
                $menu[$k] = $val;
            }
        }

        $menu = !empty($menu) ? array2tree($menu) : [];
        $this->assign('menu', $menu);
    }

    /**
     *获取七牛token
     */
    public function qiniu()
    {
        $accessKey = Config::get('qiniu.ACCESSKEY');
        $secretKey = Config::get('qiniu.SECRETKEY');
        $bucket = Config::get('qiniu.BUCKET');
        $domain = Config::get('qiniu.DOMAIN');
        Vendor('qiniu.autoload');
        //  include_once APP_PATH.'../vendor/qiniu/src/Qiniu/Qauth.php';
        // 构建鉴权对象
        $auth = new Qauth($accessKey, $secretKey);
        // 要上传的空间
        $token = $auth->uploadToken($bucket);
        return ['domain' => $domain, 'token' => $token];
    }
}
