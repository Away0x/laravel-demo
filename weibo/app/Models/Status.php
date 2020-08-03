<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = ['content'];

    // 用户模型与微博模型一对多关联
    // $user->statuses()->create()
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
