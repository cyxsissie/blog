<?php


namespace app\common\model;


class Course extends BaseModel
{
    protected $table = "ea_live_course";

    public function base($query) {
        return $query->where("is_del", 0);
    }
    public function scores()
    {
        return $this->hasMany("score");
    }

    public function LiveCourseRecords()
    {
        return $this->hasMany("LiveCourseRecord");
    }

    public function LivePvs() {
        return $this->hasMany("LivePv");
    }
    public function room(){
        return $this->belongsTo("Room","room_id","id");
    }

    public function classroomCate()
    {
        return $this->belongsTo("ClassroomCate", "stype", "id");
    }

    public function teacher()
    {
        return $this->belongsTo("LiveUser", "teacher_id", "id");
    }


    public function liveUserLogs()
    {

        return $this->hasMany("LiveUserLog", "course_id", "id");
    }


    public function classroomType()
    {
        return $this->belongsTo("ClassroomCate", "stype", "id");
    }

    /**
     * 课程平均分
     * @return float
     */
    public function getScoreAvgAttr()
    {
        return round($this->scores()->avg("score"),2);
    }

    public function getDateStrAttr($value)
    {
        return $value ? date("Y-m-d H:i:s", $value) : "";
    }

    public function getDateEndAttr($value)
    {
        return $value ? date("Y-m-d H:i:s", $value) : "";
    }

    /**
     * 获取听课人数
     * @return int|string
     */
    public function getLearningCountAttr()
    {
        if ($this->room && $this->room->room_type === 1 ) {
            return $this->LiveCourseRecords()->field('user_id')->count('DISTINCT user_id');
        } 

        return $this->liveUserLogs()->field('uid')->count("DISTINCT uid");
    }

    /**
     * 获取平均听课时长
     * @return float
     */
    public function getLearningAvgTimeAttr()
    {
        if (empty($this->learningCount))
            return null;
        return round($this->liveUserLogs()->sum("online_time") / $this->learningCount,2);
    }

    /**
     * 获取课程峰值人数
     * @param $value
     * @return bool|string
     */
    public function getMaxUserNumAttr($value)
    {
        // 如果课程是播放中的话，从redis获取
        if($this->status == 1) {
            $redis = new \Redis();
            $redis->connect("127.0.0.1");
            return $redis->get("max_user_num_by_course:".$this->id);
        }
        return $value;
    }

}
