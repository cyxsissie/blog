<?php


namespace app\common\model;


use think\Model;

class BaseModel extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = "add_time";
    protected $updateTime = false;
    protected $field = true;
}
