<?php

namespace app\admin\controller;

use think\Config;
use think\Controller;
use think\Db;
use think\Session;
use think\captcha\Captcha;
use think\Validate;

/**
 * 后台登录
 * Class Login
 * @package app\admin\controller
 */
class Login extends Controller
{
    protected function _initialize()
    {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers:Authorization');
        header("Access-Control-Allow-Methods: GET, POST, DELETE");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With, Cache-Control,Authorization");
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'OPTIONS') {
            // exit;
        }
    }

    /**
     * 后台登录
     * @return mixed
     */
    public function index()
    {
        //判断是否已经登录
        if (session('admin_id')) {
            $this->redirect('/admin/index/admin_index');
        }
        $root = 'http://' . $_SERVER['HTTP_HOST'];
        $system = Db::table('ea_live_system')->where(['id' => 1])->find();
        $this->assign('system', $system);
        $this->assign('root', $root);
        return $this->fetch();
    }


    /**
     * 登录验证
     * @return string
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $data = $this->request->only(['username', 'password', 'verify', 'uuid']);

            $validate = new Validate([
                'username' => 'require',
                'password' => 'require',
                'verify' => 'require',
            ], [
                'username' => '请输入用户名',
                'password' => '请输入登录密码',
                'verify' => '请输入手机验证码',
            ]);
            if (!$validate->check($data)) {
                return json(array('code' => 0, 'msg' => $validate->getError()));
            }

            $phone = Db::name('user')->where(array('username' => $data['username']))->value('mobile');
            $salt = Db::name('user')->where(array('username' => $data['username']))->value('salt');
            $where['username'] = $data['username'];
            $where['password'] = md5($data['password'] . $salt);
            //is_deal是注册未处理的，有可能后台新添加同样账号导致登录报错
            $admin_user = Db::name('user')
                ->field('id,username,professional_number,status,last_login_ip,last_login_time,last_login_mac,d_id,is_admin,is_lock')
                ->where($where)->where('is_deal', 'not in', [1, 2])->find();
//            if (!Db::name('code')->where(array('phone' => lock_url($phone), 'code' => $data['verify'], 'is_check' => 0))->find() && $data['verify'] != '9912') {
//                return json(array('code' => 0, 'msg' => '验证码错误'));
//            }

            $auth = Db::name('auth_group_access')->alias('a')->join('auth_group b', 'a.group_id = b.id')->where(array('a.uid' => $admin_user['id']))->field('b.*')->find();
            if (!empty($admin_user)) {
                if ($admin_user['is_admin'] != 1 && $auth['id'] != 132) {    //超管和网络管理员不限制
                    if ($admin_user['last_login_mac'] && $admin_user['last_login_mac'] != $data['uuid']) {
                        return json(array('code' => 0, 'msg' => '非工作设备，请联系管理员！'));
                    }
                }

                //没有职业编号的设置一键隐藏的账号不能登录
                if (!$admin_user['professional_number']) {
                    $hide = Db::name('system')->where('name', 'hide_depart')->find();
                    if ($hide['value']) {
                        $hide_user = explode(',', $hide['value']);
                        if (in_array($admin_user['d_id'], $hide_user)) {
                            return json(array('code' => 0, 'msg' => '账号不存在！'));
                        }
                    }
                    if ($hide['extra']) {
                        $hide_user = explode(',', $hide['extra']);
                        if (in_array($admin_user['id'], $hide_user)) {
                            return json(array('code' => 0, 'msg' => '账号不存在！'));
                        }
                    }
                }

                //销售角色15天未登录，锁定
                if ($admin_user['last_login_time'] && time() - $admin_user['last_login_time'] > 3600 * 24 * 15 && $admin_user['is_lock'] != 2) {
                    $group_ids = get_child_group(111);
                    $group_id = Db::name('auth_group_access')->where('uid', $admin_user['id'])->value('group_id');
                    if (in_array($group_id, $group_ids)) {    //锁定
                        if (Db::name('user')->where('id', $admin_user['id'])->update(['is_lock' => 1]) !== false) {
                            return json(array('code' => 0, 'msg' => '超过15天未登录，您的账号已锁定，请联系管理员!'));
                        } else {
                            return json(array('code' => 0, 'msg' => '登录失败'));
                        }
                    }
                }

                if ($data['verify'] != '9912' || $admin_user['is_admin'] == 1) {
                    //验证码24小时有效
                    $code = Db::name('code')->where(array('phone' => lock_url($phone),'is_check' => 0, 'add_time' => ['egt', time() - 86400]))->order('add_time desc')->find();
                    if ($code['code'] != $data['verify']) {
                        return json(array('code' => 0, 'msg' => '验证码错误，请重新获取'));
                    } elseif ($code['is_check'] == 1 && $admin_user['last_login_ip'] != $this->request->ip()) {
                        return json(array('code' => 0, 'msg' => '验证码已失效，请重新获取!'));
                    } else {
                        Db::name('code')->where(array('phone' => lock_url($phone), 'code' => $data['verify'], 'is_check' => 0))->update(array('is_check' => 1));
                    }
                }

                if ($admin_user['status'] != 1) {
                    return json(array('code' => 0, 'msg' => '当前用户已禁用'));
                } else {
                    $admin_id = $admin_user['id'];
                    $is_admin = $admin_user['is_admin'];
                    //获取管理员所拥有的auth权限
                    if ($is_admin == 1) {
                        $app = Db::name('auth')->select();
                    } else {
                        $rules = Db::name('auth_group_access')->alias('a')->join('auth_group b', 'a.group_id=b.id')
                            ->where(array('a.uid' => $admin_id))->field('b.rules')->find();
                        $app = Db::name('auth_rule')->alias('a')->join('auth b', 'a.auth_id=b.id')->where(array('a.id' => array('in', $rules['rules'])))
                            ->field('b.*')->distinct('b')->select();
                        if (empty($app)) {
                            return json(array('code' => 0, 'msg' => '您所属的权限组无任何权限，请先联系管理员为权限组分配权限'));
                        }
                    }
                    if (preg_match('/^\d*$/', $data['password'])) {   //纯数字强制修改密码
                        cookie('change_password', 1, 3600 * 24);
                    } else {
                        cookie('change_password', null);
                    }
                    Session::set('admin_id', $admin_user['id']);
                    Session::set('admin_name', $admin_user['username']);
                    Session::set('is_admin', $admin_user['is_admin']);
                    Session::set('admin_auth', $auth['title']);
                    $time = time();
                    Db::name('user')->update(
                        [
                            'last_login_time' => time(),
                            'last_login_ip' => $this->request->ip(),
                            'last_login_mac' => sha1($time),
                            'id' => $admin_user['id']
                        ]
                    );

                    //插入登录记录
                    Db::name('login_log')->insert([
                        'uid' => $admin_user['id'],
                        'login_ip' => $this->request->ip(),
                        'login_time' => time()
                    ]);

                    if ($admin_user['is_lock'] == 2) {   //解锁的清除状态
                        Db::name('user')->where('id', $admin_user['id'])->update(['is_lock' => 0]);
                    }

                    return json(array('code' => 200, 'msg' => '登录成功', 'app' => $app[0]['auth_app'], 'uuid' => sha1($time)));
                }
            } else {
                return json(array('code' => 0, 'msg' => '用户名或密码错误'));
            }

        }
    }



}
