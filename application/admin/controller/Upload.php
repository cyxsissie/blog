<?php
namespace app\admin\controller;
use app\common\model\Upload as UploadModel;

use app\common\controller\AdminBase;
use think\Controller;

class Upload extends Controller
{
    function _initialize()
    {

        $this->model =new UploadModel();
    }
    public function upimage()
    {
        $file = request()->file('images');
        $info = $file->validate(['size' => 5000000, 'ext' => 'jpg,png,gif,jpeg,icon'])->move(ROOT_PATH .DS.'public'. DS.'data'.DS . 'upload');
        if (!$info->getError()) {
            return json(array("errno"=>0,"data"=>array('/data/upload/'.$info->getSaveName())));
        }
    }

    public function live_upimage()
    {
         return json($this->model->live_upfile('images'));
    }
    public function umeditor_upimage()
    {
        $result = $this->model->upfile('umeditor', 'upfile', true);
        if ($result['code'] == 200) {
            $data = array("originalName" => $result['info']['name'], "name" => $result['savename'], "url" => $result['path'], "size" => $result['info']['size'], "type" => $result['info']['type'], "state" => "SUCCESS");
        } else {
            $data = array("originalName" => $result['info']['name'], "name" => $result['savename'], "url" => $result['path'], "size" => $result['info']['size'], "type" => $result['info']['type'], "state" => $result['msg']);
        }
        echo json_encode($data);
        exit;
    }


}