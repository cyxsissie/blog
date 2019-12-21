<?php


namespace app\common\model;


use think\Model;

class ClassroomCate extends Model
{
    protected $table = "ea_live_classroom_cate";
    protected $autoWriteTimestamp = true;
    protected $field = true;

    public function parent()
    {
        return $this->belongsTo("ClassroomCate", "parent_id", "id");
    }

    public function children()
    {
        return $this->hasMany("ClassroomCate", "parent_id", "id");
    }

    public function scopeTopLevel($query)
    {
        return $query->where("parent_id", "0");
    }
}
