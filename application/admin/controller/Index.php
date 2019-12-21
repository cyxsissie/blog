<?php


namespace app\admin\controller;


use think\Controller;
use think\Db;

class Index extends Controller
{
    public function admin_index()
    {
        return $this->fetch();
    }
    public function index(){
        return $this->fetch();
    }
}
