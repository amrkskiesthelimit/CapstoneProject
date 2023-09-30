<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'start_date', 'end_date', 'reason', 'other_reason', 'status', 'leave_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}