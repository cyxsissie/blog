<?php


namespace app\common\model;


class Score extends BaseModel
{
    protected $table = "ea_live_score";

    public function teacher() {
        return $this->belongsTo("LiveUser","teacher_id", "id");
    }

    public function course() {
        return $this->belongsTo("Course", "course_id", "id");
    }

    public function user() {
        return $this->belongsTo("LiveUser", "user_id", "id");
    }

}
