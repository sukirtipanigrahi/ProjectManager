<?php

class Task extends Phalcon\Mvc\Model
{
    public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->hasMany('id', 'TaskUser', 'task_id');
        $this->belongsTo('assigned_to', 'User', 'id');
        $this->hasMany('id', 'Comment', 'task_id');
        $this->belongsTo('project_id', 'Project', 'id');
    }

    public function getCreatedBy()
    {
        $user = User::findFirst('id = "' . $this->created_by . '"');

        return $user;
    }

    public function getTimePercent()
    {
        if ($this->hours == 0 || $this->hours_spent == 0) {
            return 0;
        }

        $explode = explode(':', $this->hours_spent);
        $_tasksTime = ($explode[0] * 60) + $explode[1];
        $explode = explode(':', $this->hours);
        $_targetTime = ($explode[0] * 60) + $explode[1];

        return ceil(($_tasksTime / $_targetTime) * 100);
    }

    public function getHours()
    {
        if ($this->hours == 0) {
            return null;
        }

        $explode = explode(':', $this->hours);
        return $explode[0];
    }

    public function calculateTotalTimeSpent()
    {
        $tasksTimeStamp = 0;

        $attendances = Attendance::find('task_id = "' . $this->id . '"');

        foreach ($attendances AS $attendance) {
            $start = strtotime($attendance->start);

            if (is_null($attendance->end)) {
                $end = $start;
            }
            else {
                $end = strtotime($attendance->end);
            }

            $tasksTimeStamp += $end - $start;
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $tasksTime = date('j:H:i:s', $tasksTimeStamp);
        date_default_timezone_set($oldTimeZone);

        $explode = explode(':', $tasksTime);
        $tasksTime = ((($explode[0] - 1) * 24) + ($explode[1])) . ':' . $explode[2] . ':' . $explode[3];

        return $tasksTime;
    }

    public function getTimeSpent()
    {
        if ($this->hours_spent == 0) {
            return null;
        }

        $explode = explode(':', $this->hours_spent);
        return $explode[0] . ':' . $explode[1];
    }

    public function getComments()
    {
        $comments = Comment::find(array(
            'conditions' => 'task_id = "' . $this->id . '"',
            'order' => 'created_at ASC',
        ));

        return $comments;
    }
}
