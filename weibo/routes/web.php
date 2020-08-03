<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');

Route::get('signup', 'UsersController@create')->name('signup');

Route::get('login', 'SessionsController@create')->name('login');
Route::post('login', 'SessionsController@store')->name('login');
Route::delete('logout', 'SessionsController@destroy')->name('logout');

Route::resource('users', 'UsersController');
/**
 * Route::resource('users', 'UsersController');
 * 等同于
 * Route::get('/users', 'UsersController@index')->name('users.index');               展示多条数据的列表页面
 * Route::get('/users/create', 'UsersController@create')->name('users.create');      展示创建页面
 * Route::get('/users/{user}', 'UsersController@show')->name('users.show');          展示一条数据的详情页面
 * Route::post('/users', 'UsersController@store')->name('users.store');              创建
 * Route::get('/users/{user}/edit', 'UsersController@edit')->name('users.edit');     展示编辑页面
 * Route::patch('/users/{user}', 'UsersController@update')->name('users.update');    编辑
 * Route::delete('/users/{user}', 'UsersController@destroy')->name('users.destroy'); 删除
 */
Route::resource('users', 'UsersController');
// 激活邮件
Route::get('signup/confirm/{token}', 'UsersController@confirmEmail')->name('confirm_email');
// 关注/粉丝
Route::get('/users/{user}/followings', 'UsersController@followings')->name('users.followings');
Route::get('/users/{user}/followers', 'UsersController@followers')->name('users.followers');


Route::post('/users/followers/{user}', 'FollowersController@store')->name('followers.store');
Route::delete('/users/followers/{user}', 'FollowersController@destroy')->name('followers.destroy');


/** 密码重设相关路由 */
// 密码更新页面
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
// 邮箱发送重设链接
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// 显示重置密码的邮箱发送页面
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// 执行密码更新操作
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');


// 发布微博
Route::resource('statuses', 'StatusesController', ['only' => ['store', 'destroy']]);
