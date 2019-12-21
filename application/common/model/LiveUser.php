<?php


namespace app\common\model;


class LiveUser extends BaseModel
{
    protected $table = "ea_live_user";

    public function scopeTeacher($query)
    {
        return $query->where("user_type", 3)->field("id, user_name");
    }
}
