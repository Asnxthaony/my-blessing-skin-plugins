<?php

namespace mcstaralliance;

use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Lcobucci\JWT;
use mcstaralliance\Models\DingtalkUser;
use mcstaralliance\Models\McbbsUser;
use mcstaralliance\Models\QQUser;

require __DIR__.'/Utils/helpers.php';
class ConnectController extends Controller
{
    public function list()
    {
        $mcbbsUser = McbbsUser::where('user_id', auth()->id())->first();
        $qqUser = QQUser::where('user_id', auth()->id())->first();
        $dingtalkUser = DingtalkUser::where('user_id', auth()->id())->first();

        if ($mcbbsUser) {
            $mcbbsUser->forum_groupname = yx_gid_to_gn($mcbbsUser->forum_groupid);
        }

        return view('mcstaralliance::connect', [
            'mcbbs' => $mcbbsUser,
            'qq' => $qqUser,
            'dingtalk' => $dingtalkUser,
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
                    ->withClaim('uid', (int) $remoteUser->id)
                    ->withClaim('name', $remoteUser->nickname)
                    ->withClaim('gid', (int) $remoteUser->groupid)
                    ->getToken(new JWT\Signer\Hmac\Sha256(), new JWT\Signer\Key(config('jwt.secret', '')));

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
                abort(403, '此 MCBBS 账号已被其他用户绑定');
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

    public function qqLogin()
    {
        $accessToken = request()->header('Authorization');

        if ($accessToken != config('services.qq.access_token')) {
            return response()->json(['error' => '无效的 access_token'], 403);
        }

        if (!request()->has('qq')) {
            return response()->json(['error' => '缺少 qq 参数'], 403);
        }

        $now = Carbon::now();
        $builder = new JWT\Builder();
        $token = (string) $builder->issuedBy('QQ-Auth')
            ->issuedAt($now->timestamp)
            ->expiresAt($now->addSeconds(300)->timestamp)
            ->withClaim('qq', (int) request()->input('qq'))
            ->getToken(new JWT\Signer\Hmac\Sha256(), new JWT\Signer\Key(config('jwt.secret', '')));

        return response()->json(['token' => $token]);
    }

    public function qqCallback(Dispatcher $dispatcher)
    {
        if (!request()->has('token')) {
            abort(403, '缺少 token 参数');
        }

        $user = auth()->user();
        $remoteUser = $this->getQQUserFromToken(request()->input('token'));

        if ($remoteUser) {
            $qqUser = QQUser::where('qq_id', $remoteUser->qq_id)->first();

            if ($user) {
                if (!$qqUser) {
                    $qqUser = new QQUser();
                    $qqUser->user_id = $user->uid;
                    $qqUser->qq_id = $remoteUser->qq_id;

                    $qqUser->save();

                    return redirect('/user/connect');
                } elseif ($qqUser->user_id == $user->uid) {
                    return redirect('/user/connect');
                } else {
                    abort(403, '此 QQ 账号已被其他用户绑定');
                }
            } else {
                if ($qqUser) {
                    $user = User::where('uid', $qqUser->user_id)->first();

                    $dispatcher->dispatch('auth.login.ready', [$user]);
                    Auth::login($user);
                    $dispatcher->dispatch('auth.login.succeeded', [$user]);

                    return redirect('/user');
                } else {
                    $now = Carbon::now();
                    $builder = new JWT\Builder();
                    $token = (string) $builder->issuedBy('QQ-Auth')
                        ->issuedAt($now->timestamp)
                        ->expiresAt($now->addSeconds(300)->timestamp)
                        ->withClaim('qq', (int) $remoteUser->qq_id)
                        ->getToken(new JWT\Signer\Hmac\Sha256(), new JWT\Signer\Key(config('jwt.secret', '')));

                    return redirect(route('auth.register', ['provider' => 'qq', 'token' => $token]));
                }
            }
        } else {
            abort(403, '令牌无效，请稍后再试。');
        }
    }

    public function qqNewBind(User $user)
    {
        $qqUser = $this->getQQUserFromToken(request()->input('token'));

        if ($qqUser) {
            $qqUser->user_id = $user->uid;

            $qqUser->save();
        } else {
            abort(403, '令牌无效，请稍后再试。');
        }
    }

    /**
     * 从 Token 中获取 QQ 用户.
     *
     * @return QQUser|null
     */
    public function getQQUserFromToken(string $token)
    {
        $token = (new JWT\Parser())->parse($token);

        $validationData = new JWT\ValidationData();
        $validationData->setIssuer('QQ-Auth');

        $isValid = $token->validate($validationData) && $token->verify(
            new JWT\Signer\Hmac\Sha256(),
            new JWT\Signer\Key(config('jwt.secret', ''))
        );

        if ($isValid) {
            $qqUser = new QQUser();
            $qqUser->qq_id = $token->getClaim('qq');

            return $qqUser;
        } else {
            return null;
        }
    }

    public function dingtalkLogin()
    {
        return Socialite::driver('dingtalk')->redirect();
    }

    public function dingtalkCallback(Dispatcher $dispatcher)
    {
        if (!request()->has('code')) {
            abort(403, '缺少 code 参数');
        }

        $user = auth()->user();
        $remoteUser = Socialite::driver('dingtalk')->user();
        $dingtalkUser = DingtalkUser::where('union_id', $remoteUser->union_id)->first();

        if ($remoteUser) {
            if ($user) {
                if (!$dingtalkUser) {
                    $dingtalkUser = new DingtalkUser();
                    $dingtalkUser->user_id = $user->uid;
                    $dingtalkUser->nickname = $remoteUser->nickname;
                    $dingtalkUser->open_id = $remoteUser->open_id;
                    $dingtalkUser->union_id = $remoteUser->union_id;

                    $dingtalkUser->save();

                    return redirect('/user/connect');
                } elseif ($dingtalkUser->user_id == $user->uid) {
                    $dingtalkUser->nickname = $remoteUser->nickname;
                    $dingtalkUser->updated_at = Carbon::now();

                    $dingtalkUser->save();

                    return redirect('/user/connect');
                } else {
                    abort(403, '此钉钉账号已被其他用户绑定');
                }
            } else {
                if ($dingtalkUser) {
                    $user = User::where('uid', $dingtalkUser->user_id)->first();

                    $dispatcher->dispatch('auth.login.ready', [$user]);
                    Auth::login($user);
                    $dispatcher->dispatch('auth.login.succeeded', [$user]);

                    return redirect('/user');
                } else {
                    $now = Carbon::now();
                    $builder = new JWT\Builder();
                    $token = (string) $builder->issuedBy('Dingtalk-Auth')
                        ->issuedAt($now->timestamp)
                        ->expiresAt($now->addSeconds(300)->timestamp)
                        ->withClaim('nickname', $remoteUser->nickname)
                        ->withClaim('openid', $remoteUser->open_id)
                        ->withClaim('unionid', $remoteUser->union_id)
                        ->getToken(new JWT\Signer\Hmac\Sha256(), new JWT\Signer\Key(config('jwt.secret', '')));

                    return redirect(route('auth.register', ['provider' => 'dingtalk', 'token' => $token]));
                }
            }
        } else {
            abort(403, '令牌无效，请稍后再试。');
        }
    }

    public function dingtalkNewBind(User $user)
    {
        $dingtalkUser = $this->getDingtalkUserFromToken(request()->input('token'));

        if ($dingtalkUser) {
            $dingtalkUser->user_id = $user->uid;

            $dingtalkUser->save();
        } else {
            abort(403, '令牌无效，请稍后再试。');
        }
    }

    /**
     * 从 Token 中获取 钉钉 用户.
     *
     * @return Dingtalkuser|null
     */
    public function getDingtalkUserFromToken(string $token)
    {
        $token = (new JWT\Parser())->parse($token);

        $validationData = new JWT\ValidationData();
        $validationData->setIssuer('Dingtalk-Auth');

        $isValid = $token->validate($validationData) && $token->verify(
            new JWT\Signer\Hmac\Sha256(),
            new JWT\Signer\Key(config('jwt.secret', ''))
        );

        if ($isValid) {
            $dingtalkUser = new DingtalkUser();
            $dingtalkUser->user_id = $user->uid;
            $dingtalkUser->nickname = $token->getClaim('nickname');
            $dingtalkUser->open_id = $token->getClaim('openid');
            $dingtalkUser->union_id = $token->getClaim('unionid');

            return $dingtalkUser;
        } else {
            return null;
        }
    }
}
