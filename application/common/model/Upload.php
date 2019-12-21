<?php
namespace app\common\model;
use think\Model;
use think\File;
use think\Db;
use app\common\model\File as FileModel;
use app\common\service\Ftp;
class Upload extends Model
{

    function initialize()
    {
        parent::initialize();
    }

    public function upfile($type,$filename = 'file',$is_water = false)
    {
        $file = request()->file($filename);
        $filemode = new FileModel();
        $md5 = $file->hash('md5');
        $n = $filemode->where('md5', $md5)->find();
        if (empty($n)) {

            $info = $file->validate(['size' => 5000000, 'ext' => 'jpg,png,gif,jpeg,icon'])->move(ROOT_PATH .DS.'public'. DS.'data'.DS . 'upload');
            if ($info) {
                $path = DS.'data'.DS . 'upload'. DS . $info->getSaveName();
                $path = str_replace("\\", "/", $path);
                $realpath = WEB_URL . $path;
                $data['sha1'] = $info->sha1();
                $data['md5'] = $info->md5();
                $data['create_time'] = time();//;
                $data['location'] = 1;
                $data['ext'] = $info->getExtension();
                $data['size'] = $info->getSize();
                $data['savepath'] = $path;
                $data['savename'] = $info->getFilename();
                $data['download'] = 0;
                $fileinfo = $info->getInfo();
                $data['name'] = $fileinfo['name'];
                $data['mime'] = $fileinfo['type'];
                if ($filemode->save($data)) {
                    return array('code' => 200, 'msg' => '上传成功', 'name'=>$filename,'id' => $filemode->id, 'path' => $path, 'headpath' => $realpath, 'savename' => $info->getSaveName(), 'filename' => $info->getFilename(), 'info' => $info->getInfo());
                } else {
                    return array('code' => 0, 'msg' => '上传失败');
                }
            } else {
                return array('code' => 0, 'msg' => $file->error());
            }

        } else {
            $path = $n['savepath'];
            $realpath = WEB_URL . $path;
            return array('code' => 200, 'msg' => '上传成功', 'name'=>$filename, 'id' => $n['id'], 'path' => $path, 'headpath' => $realpath, 'savename' => $n['savename'], 'filename' => $n['name'], 'info' => $n);
        }
    }

    public function live_upfile($type,$filename = 'file',$is_water = false)
    {

        $file = request()->file($filename);
        $from_type = request()->param('from_type');
        switch ($from_type) {
            case 'show_image':
                $size = 204800;
                break;
            case 'brand':
                $size = 102400;
                break;
            case 'head_image':
                $size = 102400;
                break;
        }
        $filemode = new FileModel();
        $md5 = $file->hash('md5');
        $n = $filemode->where('md5', $md5)->find();
        if (empty($n)) {
            $info = $file->validate(['size' => $size, 'ext' => 'jpg,png,jpeg'])->move(ROOT_PATH .DS.'public'. DS.'data'.DS . 'upload');
            /*$image = \think\Image::open(ROOT_PATH . 'public/data/upload/'. $info->getSaveName());
            $date_path = 'data/thumb/'.date('Ymd');
            if(!file_exists($date_path)){
                mkdir($date_path,0777,true);
            }
            $thumb_path = $date_path.'/'.$info->getFilename();
            $image->thumb(500,100,,\think\Image::THUMB_CENTER)->save($thumb_path);*/
            if ($info) {
                $path = DS.'data'.DS . 'upload'. DS . $info->getSaveName();
                $path = str_replace("\\", "/", $path);
                $realpath = WEB_URL . $path;
                $data['sha1'] = $info->sha1();
                $data['md5'] = $info->md5();
                $data['create_time'] = time();//;
                $data['location'] = 1;
                $data['ext'] = $info->getExtension();
                $data['size'] = $info->getSize();
                $data['savepath'] = $path;
                $data['savename'] = $info->getFilename();
                $data['download'] = 0;
                $fileinfo = $info->getInfo();
                $data['name'] = $fileinfo['name'];
                $data['mime'] = $fileinfo['type'];
                if ($filemode->save($data)) {
                    return array('code' => 200, 'msg' => '上传成功', 'name'=>$filename,'id' => $filemode->id, 'path' =>$path, 'headpath' => $realpath, 'savename' => $info->getSaveName(), 'filename' => $info->getFilename(), 'info' => $info->getInfo());
                } else {
                    return array('code' => 0, 'msg' => '上传失败');
                }
            } else {
                return array('code' => 0, 'msg' => $file->getError());
            }
        } else {
            $path = $n['savepath'];
            $realpath = WEB_URL . $path;
            return array('code' => 200, 'msg' => '上传成功', 'name'=>$filename, 'id' => $n['id'], 'path' => $path, 'headpath' => $realpath, 'savename' => $n['savename'], 'filename' => $n['name'], 'info' => $n);
        }
    }
    public function upVoiceFile($type,$filename = 'file',$is_water = false){
        $file = request()->file($filename);
        $filemode=new FileModel();
        $md5=$file->hash('md5');
        $n=$filemode->where('md5',$md5)->find();




        if(empty($n)){

            $info = $file->validate(['size'=>6000000,'ext'=>'mp3,wav,wma'])->move(ROOT_PATH . DS . 'uploads');

            if($info){

                $path = DS.'data'.DS . 'upload' . DS .$info->getSaveName();
                $path=str_replace("\\","/",$path);
                $realpath=WEB_URL.$path;
                $data['sha1']=$info->sha1();
                $data['md5']=$info->md5();
                $data['create_time']=time();//;
                $data['location']=1;
                $data['ext']=$info->getExtension();
                $data['size']=$info->getSize();
                $data['savepath']=$path;
                $data['savename']=$info->getFilename();
                $data['download']=0;
                $fileinfo=$info->getInfo();
                $data['name']=$fileinfo['name'];
                $data['mime']=$fileinfo['type'];
                if($filemode->save($data)){

                    return array('code'=>200,'msg'=>'上传成功','id'=>$filemode->id,'path'=>$path,'headpath'=>$realpath,'savename'=>$info->getSaveName(),'filename'=>$info->getFilename(),'info'=>$info->getInfo());
                }else{
                    return array('code'=>0,'msg'=>'上传失败');
                }



            }else{
                return array('code'=>0,'msg'=>$file->error());
            }


        }else{

            $path = $n['savepath'];

            $realpath=WEB_URL.$path;
            return array('code'=>200,'msg'=>'上传成功','id'=>$n['id'],'path'=>$path,'headpath'=>$realpath,'savename'=>$n['savename'],'filename'=>$n['name'],'info'=>$n);
        }
    }
    public function upVideo($type,$filename = 'file',$is_water = false){
        $file = request()->file($filename);

        $filemode=new FileModel();

        $md5=$file->hash('md5');
        $n=$filemode->where('md5',$md5)->find();



        if(empty($n)){

            $info = $file->validate(['size'=>600000000,'ext'=>'mp4,wav,wma'])->move(ROOT_PATH . DS . 'uploads');

            if($info){

                $path = DS . 'uploads' . DS .$info->getSaveName();
                $path=str_replace("\\","/",$path);
                $realpath=WEB_URL.$path;
                $data['sha1']=$info->sha1();

                $data['md5']=$info->md5();

                $data['create_time']=time();//;
                $data['location']=1;
                $data['ext']=$info->getExtension();
                $data['size']=$info->getSize();
                $data['savepath']=$path;
                $data['savename']=$info->getFilename();
                $data['download']=0;
                $fileinfo=$info->getInfo();
                $data['name']=$fileinfo['name'];
                $data['mime']=$fileinfo['type'];
                if($filemode->save($data)){
                    return array('code'=>200,'msg'=>'上传成功','id'=>$filemode->id,'path'=>$path,'headpath'=>$realpath,'savename'=>$info->getSaveName(),'filename'=>$info->getFilename(),'info'=>$info->getInfo());
                }else{
                    return array('code'=>0,'msg'=>'上传失败');
                }
            }else{
                return array('code'=>0,'msg'=>$file->error());
            }
        }else{

            $path = $n['savepath'];

            $realpath=WEB_URL.$path;
            return array('code'=>200,'msg'=>'上传成功','id'=>$n['id'],'path'=>$path,'headpath'=>$realpath,'savename'=>$n['savename'],'filename'=>$n['name'],'info'=>$n);
        }
    }

    /**
    *上传文件
    */
    public function up_ppt($type,$filename = 'file',$is_water = false)
    {
        $file = request()->file($filename);
        $filemode = new FileModel();
        $md5 = $file->hash('md5');
        $n = $filemode->where('md5', $md5)->find();
        if (empty($n)) {

            $info = $file->validate(['size' => 50000000, 'ext' => 'ppt,pdf,pptx'])->move(ROOT_PATH .DS.'public'. DS.'data'.DS . 'upload');
            if ($info) {

                $path = DS.'data'.DS . 'upload'. DS . $info->getSaveName();

                $path = str_replace("\\", "/", $path);
                $realpath = WEB_URL . $path;

                $data['sha1'] = $info->sha1();

                $data['md5'] = $info->md5();
                $data['create_time'] = time();//;
                $data['location'] = 1;
                $data['ext'] = $info->getExtension();
                $data['size'] = $info->getSize();
                $data['savepath'] = $path;
                $data['savename'] = $info->getFilename();
                $data['download'] = 0;
                $fileinfo = $info->getInfo();
                $data['name'] = $fileinfo['name'];
                $data['mime'] = $fileinfo['type'];
                if ($filemode->save($data)) {
                    return array('code' => 200, 'msg' => '上传成功', 'name'=>$filename,'id' => $filemode->id, 'path' => $path, 'headpath' => $realpath, 'savename' => $info->getSaveName(), 'filename' => $info->getFilename(), 'info' => $info->getInfo());
                } else {
                    return array('code' => 0, 'msg' => '上传失败');
                }


            } else {
                return array('code' => 0, 'msg' => $file->error());
            }


        } else {

            $path = $n['savepath'];

            $realpath = WEB_URL . $path;
            return array('code' => 200, 'msg' => '上传成功', 'name'=>$filename, 'id' => $n['id'], 'path' => $path, 'headpath' => $realpath, 'savename' => $n['savename'], 'filename' => $n['name'], 'info' => $n);
        }
    }
 
}
