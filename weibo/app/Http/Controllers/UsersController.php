<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', [
            // 登录用户不可访问以下页面
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        $this->middleware('guest', [
            // 只让未登录用户访问注册页面
            'only' => ['create']
        ]);
    }

    // 列出所有用户的页面
    public function index()
    {
        // 默认状况下，页面的当前页数由 HTTP 请求所带的 page 参数决定 ?page=2
        // view 中可使用 {!! $users->render() !!} 渲染分页
        $users = User::paginate(10); // 分页
        return view('users.index', compact('users'));
    }

    // 创建用户页面 get
    public function create()
    {
        return view('users.create');
    }

    // 展示用户信息页面 get
    public function show(User $user)
    {
        $statuses = $user->statuses()
                        ->orderBy('created_at', 'desc')
                        ->paginate(10); // 分页
        return view('users.show', compact('user', 'statuses'));
    }

    // 创建用户 post
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // 登录
        // Auth::login($user);
        // session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        // return redirect()->route('users.show', [$user]);

        // 发送激活邮件
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }

    // 编辑用户页面 get
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    // 编辑用户 patch
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show', $user->id);
    }

    // 删除用户 delete
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    // 激活邮件
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        // 登录
        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    // 关注的人
    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = $user->name . '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    // 粉丝
    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = $user->name . '的粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }

    // 发送邮件的方法
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });

        /**
         * 如在 .env 文件中配置了邮件相关环境变量，比如 form，就可以这样
         * $view = 'emails.confirm';
         * $data = compact('user');
         * $to = $user->email;
         * $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";
         *
         * Mail::send($view, $data, function ($message) use ($to, $subject) {
         *     $message->to($to)->subject($subject);
         * });
         */
    }
}
