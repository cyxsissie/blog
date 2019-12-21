<?php


namespace app\admin\controller;


use think\Controller;
use think\Db;

class Article extends Controller
{
    public function index()
    {
        $data = Db::name("article")->paginate(10);
        $this->assign("data",$data);
        return $this->fetch();
    }
    public function edit(){
        return $this->fetch();
    }
    public function add(){
        return $this->fetch();
    }
}
