<?php


namespace app\common\model;


class LiveCourseRecord extends BaseModel
{
    public function user()
    {
        //一对一关联ea_live_user表 参数一表示关联的表名，参数二表示本表关联的字段名，参数三表示关联表的关联字段名
        return $this->belongsTo("LiveUser", "user_id", "id");
    }

    public function course()
    {
        return $this->belongsTo("Course", "course_id", "id");
    }
}
