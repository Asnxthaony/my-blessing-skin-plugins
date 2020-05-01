<?php

namespace mcstaralliance;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

class ConnectController extends Controller
{
    public function list()
    {
        $user = auth()->user();

        $mcbbsUser = McbbsUser::where('user_id', $user->uid)->first();

        return view('mcstaralliance::connect', [
            'mcbbs' => $mcbbsUser,
        ]);
    }

    public function mcbbs_connect()
    {
        return Socialite::driver('mcbbs')->redirect();
    }

    public function handleMcbbsCallback()
    {
        $user = auth()->user();
        $remoteUser = Socialite::driver('mcbbs')->user();

        $mcbbsUser = McbbsUser::where('forum_uid', $remoteUser->id)->first();
        if (!$mcbbsUser) {
            $mcbbsUser = new mcbbsUser();
            $mcbbsUser->user_id = $user->uid;
            $mcbbsUser->forum_uid = $remoteUser->id;
            $mcbbsUser->forum_username = $remoteUser->nickname;
            $mcbbsUser->forum_groupid = $remoteUser->groupid;

            $mcbbsUser->save();
        } else {
            abort(500, "该用户已被绑定");
        }

        return redirect('/user/connect');
    }
}
