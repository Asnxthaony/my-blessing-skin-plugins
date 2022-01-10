<?php

namespace Asnxthaony\MiniLogin\Controllers;

use App\Models\User;
use Asnxthaony\MiniLogin\Models\WechatUser;
use Auth;
use Cache;
use EasyWeChat\Factory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class MiniLoginController extends Controller
{
    public function list()
    {
        $wechatUser = WechatUser::where('user_id', auth()->id())->first();

        return view('Asnxthaony\MiniLogin::mini-login', [
            'wechat' => $wechatUser,
        ]);
    }

    public function wechatBind(Request $request)
    {
        $user = auth()->user();

        $wechatUser = WechatUser::where('user_id', $user->uid)->first();
        if ($wechatUser) {
            return json('当前帐号已绑定微信', 1);
        }

        $random = Str::random();
        $key = 'minilogin_wechat_'.$random;

        Cache::put($key, $user->uid, 300);

        $miniProgram = Factory::miniProgram(config('services.wechat'));

        $response = $miniProgram->app_code->getUnlimit('sid=1001&rid='.$random, [
            'page' => 'pages/index/index',
            'check_path' => true,
            'env_version' => 'release',
        ]);

        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $response->saveAs(public_path('mini-login/qrcode'), $random.'.png');

            return json('success', 0, [
                'random' => $random,
            ]);
        } else {
            return json('小程序码生成失败', 1);
        }
    }

    public function wechatLogin(Request $request)
    {
        $random = Str::random();
        $key = 'minilogin_wechat_'.$random;

        Cache::put($key, ['action' => 'init'], 300);

        $miniProgram = Factory::miniProgram(config('services.wechat'));

        $response = $miniProgram->app_code->getUnlimit('sid=1000&rid='.$random, [
            'page' => 'pages/index/index',
            'check_path' => true,
            'env_version' => 'release',
        ]);

        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $response->saveAs(public_path('mini-login/qrcode'), $random.'.png');

            return json('success', 0, [
                'random' => $random,
            ]);
        } else {
            return json('小程序码生成失败', 1);
        }
    }

    public function wechatLoginCheck(Request $request, Dispatcher $dispatcher)
    {
        $ticket = $request->input('ticket');

        if (empty($ticket)) {
            return json('缺少 ticket 参数', 41002);
        }

        $key = 'minilogin_wechat_'.$ticket;

        $ticket = Cache::get($key);

        if ($ticket) {
            if ($ticket['action'] === 'login') {
                Cache::forget($key);

                $user = User::where('uid', $ticket['user_id'])->first();

                $dispatcher->dispatch('auth.login.ready', [$user]);
                Auth::login($user);
                $dispatcher->dispatch('auth.login.succeeded', [$user]);

                return json('登录成功', 0);
            }

            return json('等待用户扫码中', -1);
        } else {
            return json('Ticket 已过期', 42001);
        }
    }

    public function wechatCallback(Request $request)
    {
        $code = $request->input('code');
        $sid = $request->input('sid');
        $rid = $request->input('rid');

        if (empty($code)) {
            return json('缺少 code 参数', 41002);
        }

        if (empty($sid)) {
            return json('缺少 sid 参数', 41002);
        }

        if (empty($rid)) {
            return json('缺少 rid 参数', 41002);
        }

        $key = 'minilogin_wechat_'.$rid;

        $miniProgram = Factory::miniProgram(config('services.wechat'));

        switch ($sid) {
            case '1000':
                // 登录
                $ticket = Cache::get($key);

                if ($ticket && $ticket['action'] === 'init') {
                    $data = $miniProgram->auth->session($code);

                    if ($data) {
                        Cache::forget($key);

                        $wechatUser = WechatUser::where('open_id', $data['openid'])->first();

                        if ($wechatUser) {
                            Cache::put($key, ['action' => 'login', 'user_id' => $wechatUser->user_id], 300);

                            return json('登录成功', 0);
                        } else {
                            return json('此微信号尚未绑定星域帐号', -1);
                        }
                    } else {
                        return json('微信登录凭证无效', 42001);
                    }
                } else {
                    return json('二维码无效', 42001);
                }

                break;
            case '1001':
                // 绑定
                $userId = Cache::get($key);

                if ($userId) {
                    $data = $miniProgram->auth->session($code);

                    if ($data) {
                        Cache::forget($key);

                        if (WechatUser::where('user_id', $userId)->first()) {
                            return json('请勿重复绑定', -1);
                        }

                        if (WechatUser::where('open_id', $data['openid'])->first()) {
                            return json('请勿重复绑定', -1);
                        }

                        $wechatUser = new WechatUser();
                        $wechatUser->user_id = $userId;
                        $wechatUser->open_id = $data['openid'];
                        // $wechatUser->union_id = $data['unionid'];

                        $wechatUser->save();

                        return json('绑定成功', 0, $data);
                    } else {
                        return json('微信登录凭证无效', 42001);
                    }
                } else {
                    return json('二维码无效', 42001);
                }

                break;
            default:
                return json('场景值无效', -1);
                break;
        }
    }
}
