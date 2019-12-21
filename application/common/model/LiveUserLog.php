<?php


namespace app\common\model;


use think\Model;

class LiveUserLog extends Model
{
    protected $table = "ea_live_user_log";

    public function user()
    {
        return $this->belongsTo("LiveUser", "uid", "id");
    }

    public function course()
    {
        return $this->belongsTo("Course", "course_id", "id");
    }
}
