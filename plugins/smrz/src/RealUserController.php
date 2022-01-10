<?php

namespace Asnxthaony\Smrz;

use Asnxthaony\Smrz\Models\RealUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Option;

class RealUserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $realUser = RealUser::where('user_id', $user->uid)->first();

        if ($realUser && $realUser->state != RealUser::REJECTED) {
            $form = Option::form('smrz', '实名认证', function ($form) use ($realUser) {
                if ($realUser->state == RealUser::PENDING) {
                    $form->addMessage('审核中', 'success');
                }

                $form->text('realname', '真实姓名')->value($realUser->getRealname())->disabled();

                $form->text('id_card', '身份证号')->value($realUser->getIdCard())->disabled();
            })->renderWithoutSubmitButton();
        } else {
            $form = Option::form('smrz', '实名认证', function ($form) use ($realUser) {
                if ($realUser->state == RealUser::REJECTED) {
                    $form->addMessage("审核不通过，理由：{$realUser->reason}", 'danger');
                }

                $form->text('realname', '真实姓名');

                $form->text('id_card', '身份证号');
            })->handle();
        }

        return view('Asnxthaony\Smrz::user', compact('form'));
    }

    public function verify(Request $request)
    {
        $user = $request->user();

        $realname = $request->input('realname');
        $id_card = $request->input('id_card');

        if (empty($realname)) {
            return back()->withErrors('真实姓名不能为空');
        }

        if (empty($id_card)) {
            return back()->withErrors('身份证号不能为空');
        }

        $idValidator = new IdValidator();
        if (!$idValidator->isValid($id_card)) {
            return back()->withErrors('身份证号不合法');
        }

        $realUser = RealUser::where('user_id', $user->uid)->first();

        if ($realUser) {
            if ($realUser->state == RealUser::PENDING) {
                return back()->withErrors('您的认证信息已提交，请等待系统审核。');
            } elseif ($realUser->state == RealUser::ACCEPTED) {
                return back()->withErrors('您已通过实名认证!');
            } elseif ($realUser->state == RealUser::REJECTED) {
                $realUser->realname = Crypt::encryptString($realname);
                $realUser->id_card = Crypt::encryptString($id_card);
                $realUser->state = RealUser::PENDING;

                $realUser->save();

                return back()->withErrors('您的认证信息已提交，请等待系统审核。');
            }
        } else {
            $realUser = new RealUser();

            $realUser->user_id = $user->uid;
            $realUser->realname = Crypt::encryptString($realname);
            $realUser->id_card = Crypt::encryptString($id_card);
            $realUser->state = RealUser::PENDING;

            $realUser->save();

            return back()->withErrors('您的认证信息已提交，请等待系统审核。');
        }
    }
}
