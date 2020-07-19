<?php

namespace mcstaralliance;

use App\Models\User;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\Facades\Socialite;
use mcstaralliance\Models\McbbsUser;

require __DIR__.'/Utils/helpers.php';

class ConnectController extends Controller
{
    public function list()
    {
        $user = auth()->user();

        $mcbbsUser = McbbsUser::where('user_id', $user->uid)->first();

        if ($mcbbsUser) {
            $mcbbsUser->forum_groupname = yx_gid_to_gn($mcbbsUser->forum_groupid);
        }

        return view('mcstaralliance::connect', [
            'mcbbs' => $mcbbsUser,
        ]);
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
                abort(403, '此 MCBBS 账号已被其他用户绑定');
            }
        } else {
            if ($mcbbsUser) {
                $user = User::where('uid', $mcbbsUser->user_id)->first();

                $dispatcher->dispatch('auth.login.ready', [$user]);
                Auth::login($user);
                $dispatcher->dispatch('auth.login.succeeded', [$user]);

                return redirect('/user');
            } else {
                abort(403, '请在「用户中心」内使用「账号绑定」功能关联账号');
            }
        }
    }
}
