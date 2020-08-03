<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $users = factory(User::class)->times(50)->make();
        // makeVisible: 临时显示 User 模型里指定的隐藏属性 $hidden
        // insert: 将生成假用户列表数据批量插入到数据库中
        // User::insert($users->makeVisible(['password', 'remember_token'])->toArray());

        factory(User::class)->times(50)->create();

        // 修改第一个用户
        $user = User::find(1);
        $user->name = 'admin';
        $user->email = 'admin@qq.com';
        // $user->is_admin = true;
        $user->save();
    }
}
