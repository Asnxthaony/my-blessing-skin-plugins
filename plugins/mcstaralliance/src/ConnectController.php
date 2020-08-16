<?php

namespace mcstaralliance;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Socialite\Facades\Socialite;
use Lcobucci\JWT;
use mcstaralliance\Models\McbbsUser;

require __DIR__.'/Utils/helpers.php';

class ConnectController extends Controller
{
    public function list()
    {
        $mcbbsUser = McbbsUser::where('user_id', auth()->id())->first();

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
                $mcbbsUser = new McbbsUser();
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
                $now = Carbon::now();
                $builder = new JWT\Builder();
                $token = (string) $builder->issuedBy('Mcbbs-Auth')
                    ->issuedAt($now->timestamp)
                    ->expiresAt($now->addSeconds(300)->timestamp)
                    ->withClaim('uid', $remoteUser->id)
                    ->withClaim('name', $remoteUser->nickname)
                    ->withClaim('gid', $remoteUser->groupid)
                    ->getToken(new JWT\Signer\Hmac\Sha256(), new JWT\Signer\Key(config('jwt.secret', '')));

                return redirect(route('auth.register', ['token' => $token]));
            }
        }
    }

    public function mcbbsNewBind(User $user)
    {
        $token = (new JWT\Parser())->parse(request()->input('token'));

        $validationData = new JWT\ValidationData();
        $validationData->setIssuer('Mcbbs-Auth');

        $isValid = $token->validate($validationData) && $token->verify(
            new JWT\Signer\Hmac\Sha256(),
            new JWT\Signer\Key(config('jwt.secret', ''))
        );

        if ($isValid) {
            $mcbbsUser = new McbbsUser();
            $mcbbsUser->user_id = $user->uid;
            $mcbbsUser->forum_uid = $token->getClaim('uid');
            $mcbbsUser->forum_username = $token->getClaim('name');
            $mcbbsUser->forum_groupid = $token->getClaim('gid');

            $mcbbsUser->save();
        } else {
            abort(403, 'Token 无效，请稍后再试。');
        }
    }
}
