<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    public static function boot()
    {
        parent::boot();

        // 用于监听模型被创建之前的事件
        static::creating(function ($user) {

        });
    }

    // 表名$user->activation_token = Str::random(30);
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     * (这些字段可用于批量赋值)
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     * (数组/json 输出时，这些字段会隐藏)
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 用户头像
     *
     * $user->gravatar();
     * $user->gravatar('140');
     */
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    // 用户模型与微博模型一对多关联
    // $user->statuses()->create()
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    // 微博信息流
    public function feed()
    {
        // 取出所有关注用户的信息，再借助 pluck 方法将 id 进行分离并赋值给 user_ids
        //  User 模型里定义了关联方法 followings()，关联关系定义好后，
        //       我们就可以通过访问 followings 属性直接获取到关注用户的 集合
        //       Laravel Eloquent 提供的「动态属性」属性功能，我们可以像在访问模型中定义的属性一样，
        //       来访问所有的关联方法，$user->followings 与 $user->followings() 调用时返回的数据是不一样的，
        //       $user->followings 返回的是 Eloquent：集合 。而 $user->followings() 返回的是 数据库请求构建器
        //       $user->followings == $user->followings()->get() // 等于 true
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);

        return Status::whereIn('user_id', $user_ids)
                              ->with('user') // 预加载避免了 N+1 查找的问题
                              ->orderBy('created_at', 'desc');
    }

    // 关注人和被关注人 (多对多)
    //
    // 在 Laravel 中会默认将两个关联模型的名称进行合并，并按照字母排序，因此我们生成的关联关系表名称会是 user_user。
    // 我们也可以自定义生成的名称，把关联表名改为 followers
    //
    // user_id 是定义在关联中的模型外键名
    // 而第四个参数 follower_id 则是要合并的模型外键名
    //
    // $user->followers();  获取粉丝关系列表
    // $user->followings(); 获取用户关注人列表
    //
    // $user = App\Models\User::find(1)
    // $user->followings()->attach([2, 3])             // 关注 id 为 2 和 3 的用户
    // $user->followings()->allRelatedIds()->toArray() // [2, 3] (allRelatedIds: 获取关联模型的 ID 集合)
    public function followers()
    {

        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    // 关注
    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    // 取消关注
    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    // 是否已关注
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
