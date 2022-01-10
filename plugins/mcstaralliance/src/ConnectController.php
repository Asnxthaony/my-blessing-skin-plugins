<?php

namespace mcstaralliance;

use App\Models\User;
use Auth;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Lcobucci\JWT;
use Link\Models\LinkQq;
use mcstaralliance\Models\McbbsUser;

require __DIR__.'/Utils/helpers.php';
class ConnectController extends Controller
{
    public function list()
    {
        $uid = auth()->id();

        $mcbbsUser = McbbsUser::where('user_id', $uid)->first();
        $qqUser = LinkQq::where('user_id', $uid)->first();

        if ($mcbbsUser) {
            $mcbbsUser->forum_groupname = yx_gid_to_gn($mcbbsUser->forum_groupid);
        }

        return view('mcstaralliance::connect', [
            'mcbbs' => $mcbbsUser,
            'qq' => $qqUser,
        ]);
    }

    public function mcbbsLogin()
    {
        return Socialite::driver('mcbbs')->redirect();
    }

    public function mcbbsCallback(Dispatcher $dispatcher)
    {
        if (!request()->has('code')) {
            abort(403, '缺少 code 参数');
        }

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
                $mcbbsUser->updated_at = CarbonImmutable::now();

                $mcbbsUser->save();

                return redirect('/user/connect');
            } else {
                abort(403, '此 MCBBS 帐号已被其他用户绑定');
            }
        } else {
            if ($mcbbsUser) {
                $user = User::where('uid', $mcbbsUser->user_id)->first();

                $dispatcher->dispatch('auth.login.ready', [$user]);
                Auth::login($user);
                $dispatcher->dispatch('auth.login.succeeded', [$user]);

                return redirect('/user');
            } else {
                $now = CarbonImmutable::now();
                $jwtConfig = JWT\Configuration::forSymmetricSigner(
                    new JWT\Signer\Hmac\Sha256(),
                    JWT\Signer\Key\InMemory::plainText(config('jwt.secret', ''))
                );
                $builder = $jwtConfig->builder();
                $token = (string) $builder->issuedBy('Mcbbs-Auth')
                    ->issuedAt($now)
                    ->expiresAt($now->addSeconds(300))
                    ->withClaim('uid', (int) $remoteUser->id)
                    ->withClaim('name', $remoteUser->nickname)
                    ->withClaim('gid', (int) $remoteUser->groupid)
                    ->getToken($jwtConfig->signer(), $jwtConfig->signingKey())
                    ->toString();

                return redirect(route('auth.register', ['provider' => 'mcbbs', 'token' => $token]));
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
            $forum_uid = $token->getClaim('uid');

            $mcbbsUser = McbbsUser::where('forum_uid', $forum_uid)->first();
            if ($mcbbsUser) {
                abort(403, '此 MCBBS 帐号已被其他用户绑定');
            } else {
                $mcbbsUser = new McbbsUser();
                $mcbbsUser->user_id = $user->uid;
                $mcbbsUser->forum_uid = $forum_uid;
                $mcbbsUser->forum_username = $token->getClaim('name');
                $mcbbsUser->forum_groupid = $token->getClaim('gid');

                $mcbbsUser->save();
            }
        } else {
            abort(403, '令牌无效，请稍后再试。');
        }
    }
}
