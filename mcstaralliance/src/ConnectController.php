<?php

namespace mcstaralliance;

use App\Models\User;
use App\Http\Controllers\Controller;
use Auth;
use Blessing\Filter;
use Blessing\Rejection;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\Facades\Socialite;

class ConnectController extends Controller
{
    public function list()
    {
        $user = auth()->user();

        $mcbbsUser = McbbsUser::where('user_id', $user->uid)->first();

        if ($mcbbsUser) {
            $mcbbsUser->forum_groupname = $this->formatGroupId($mcbbsUser->forum_groupid);
        }

        return view('mcstaralliance::connect', [
            'mcbbs' => $mcbbsUser,
        ]);
    }

    private function formatGroupId($groupId)
    {
        $configs = [
            "1" => "管理员",
            "2" => "超级版主",
            "3" => "版主",
            "4" => "Lv-? 禁止发言",
            "5" => "Lv-? 禁止访问",
            "6" => "Lv-? 禁止 IP",
            "7" => "游客",
            "8" => "等待验证会员",
            "9" => "Lv.? Herobrine",
            "10" => "Lv.0 流浪者",
            "11" => "Lv.1 伐木工",
            "12" => "Lv.2 采石匠",
            "13" => "Lv.3 挖沙工",
            "14" => "Lv.4 矿工",
            "15" => "Lv.5 农夫",
            "16" => "实习版主",
            "20" => "Lv.6 手艺人",
            "21" => "Lv.7 猎手",
            "22" => "Lv.8 考古家",
            "23" => "Lv.9 牧场主",
            "27" => "Lv.10 附魔师",
            "28" => "Lv.11 领主",
            "29" => "Lv.12 屠龙者",
            "34" => "大区版主",
            "35" => "问答区版主",
            "40" => "常务版主",
            "41" => "QQ游客",
            "43" => "Lv.Inf 艺术家",
            "44" => "荣誉版主",
            "45" => "大区常务版主",
            "46" => "大区荣誉版主",
            "47" => "认证用户",
            "48" => "管理员助理",
            "51" => "村民",
            "52" => "专区版主",
            "54" => "电鳗",
        ];

        return (isset($configs[$groupId]) ? $configs[$groupId] : $groupId);
    }

    public function mcbbsLogin()
    {
        return Socialite::driver('mcbbs')->redirect();
    }

    public function mcbbsCallback(Dispatcher $dispatcher)
    {
        $user = auth()->user();
        $remoteUser = Socialite::driver('mcbbs')->user();
        $mcbbsUser = McbbsUser::where('forum_uid', $remoteUser->id)->first();

        if ($user) {
            if (!$mcbbsUser) {
                $mcbbsUser = new mcbbsUser();
                $mcbbsUser->user_id = $user->uid;
                $mcbbsUser->forum_uid = $remoteUser->id;
                $mcbbsUser->forum_username = $remoteUser->nickname;
                $mcbbsUser->forum_groupid = $remoteUser->groupid;

                $mcbbsUser->save();

                return redirect('/user/connect');
            } elseif ($mcbbsUser->user_id == $user->uid) {
                $mcbbsUser->forum_username = $remoteUser->nickname;
                $mcbbsUser->forum_groupid = $remoteUser->groupid;
                $mcbbsUser->updated_at = Carbon::now();

                $mcbbsUser->save();

                return redirect('/user/connect');
            } else {
                abort(403, "此 MCBBS 账号已被其他用户绑定");
            }
        } else {
            if ($mcbbsUser) {
                $user = User::where('uid', $mcbbsUser->user_id)->first();

                $dispatcher->dispatch('auth.login.ready', [$user]);
                Auth::login($user);
                $dispatcher->dispatch('auth.login.succeeded', [$user]);

                return redirect('/user');
            } else {
                abort(403, "请在「用户中心」中使用「账号绑定」关联账号");
            }
        }
    }
}
