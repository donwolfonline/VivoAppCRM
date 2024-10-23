<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'log_type',
        'remark',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function getRemark()
    {
        $remark = json_decode($this->remark, true);

        if($remark)
        {
            $user = $this->user;

            if($user)
            {
                $user_name = $user->name;
            }
            else
            {
                $user_name = '';
            }
            if($this->log_type == 'Upload File')
            {
                return $user_name . ' ' . __('Upload new file') . ' <b>' . $remark['file_name'] . '</b>';
            }
            elseif($this->log_type == 'Create Milestone')
            {
                return $user_name . ' ' . __('Create new milestone') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Task')
            {
                return $user_name . ' ' . __('Create new Task') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Create Bug')
            {
                return $user_name . ' ' . __('Create new Bug') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Move')
            {
                return $user_name . " " . __('Moved the Task') . " <b>" . $remark['title'] . "</b> " . __('from') . " " . __(ucwords($remark['old_status'])) . " " . __('to') . " " . __(ucwords($remark['new_status']));
            }
            elseif($this->log_type == 'Create Invoice')
            {
                return $user_name . ' ' . __('Create new invoice') . " <b>" . $remark['title'] . "</b>";
            }
        }
        else
        {
            return $this->remark;
        }
    }
}
