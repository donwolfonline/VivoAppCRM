<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'log_type',
        'remark',
    ];

    private $user_id;
    private $getUser;
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function getLeadRemark()
    {

        $remark = json_decode($this->remark, true);

        if($remark)
        {
            
            // if($this->user_id == null)
            // {
                $user = $this->user;
            //     $this->user_id = $user->id;
            // }

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
            elseif($this->log_type == 'Add Product')
            {
                return $user_name . ' ' . __('Add new Products') . " <b>" . $remark['title'] . "</b>";
            }
            elseif($this->log_type == 'Update Sources')
            {
                return $user_name . ' ' . __('Update Sources');
            }
            elseif($this->log_type == 'Create Lead Call')
            {
                return $user_name . ' ' . __('Create new Lead Call');
            }
            elseif($this->log_type == 'Create Lead Email')
            {
                return $user_name . ' ' . __('Create new Lead Email');
            }
            elseif($this->log_type == 'Move')
            {
                return $user_name . " " . __('Moved the lead') . " <b>" . $remark['title'] . "</b> " . __('from') . " " . __(ucwords(!empty($remark['old_status']) ? $remark['old_status'] : '')) . " " . __('to') . " " . __(ucwords($remark['new_status']));
            }
            elseif($this->log_type == 'Add user')
            {
                return $user_name . " ". __('New User '.$remark['title']  .' Added to Lead. ')   ;
            }
            elseif($this->log_type == 'Add Discussion')
            {
                return $user_name . ' ' . __('Create new Discussion');
            }
            elseif($this->log_type == 'Add Notes')
            {
                return $user_name . ' ' . __('Create new Notes');
            }
        }
        else
        {
            return $this->remark;
        }
    }
}
