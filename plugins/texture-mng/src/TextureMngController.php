<?php

namespace TextureMng;

use App\Models\Report;
use App\Models\Texture;
use App\Models\User;
use App\Notifications\SiteMessage;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Notification;
use TextureMng\Models\TextureMngRecord;

class TextureMngController extends Controller
{
    public function show(Request $request)
    {
        $records = TextureMngRecord::orderByDesc('created_at')->paginate(10);

        return view('TextureMng::admin', ['records' => $records]);
    }

    public function handle(Request $request, Dispatcher $events)
    {
        $tid = $request->input('tid');
        $action = $request->input('action', '');
        $reason = $request->input('reason', '');

        // Sanity check
        if (empty($tid)) {
            return json('tid 不能为空', 1);
        }
        if (empty($action)) {
            return json('action 不能为空', 1);
        }
        if (empty($reason)) {
            return json('理由不能为空', 1);
        }

        $texture = Texture::where('tid', $tid)->first();
        if (!$texture) {
            return json('材质不存在', 1);
        }

        $uploader = $texture->owner;

        if ($uploader && $uploader->permission >= $request->user()->permission) {
            return json('权限不足', 1);
        }

        // 操作记录
        $record = new TextureMngRecord();
        $record->user_id = $uploader ? $uploader->uid : -1;
        $record->texture_id = $texture->tid;
        $record->operator = $request->user()->uid;
        $record->reason = "[$action] {$reason}";
        $record->save();

        if ($uploader) {
            // 违规通知
            $content = view('TextureMng::violation-notification', [
                'texture' => $texture,
                'reason' => $reason,
            ])->render();
            $notification = new SiteMessage('材质违规处理通知', $content);
            Notification::send(collect([$uploader]), $notification);

            if ($action === 'ban') {
                $uploader->permission = User::BANNED;
                $uploader->save();
                $events->dispatch('user.banned', [$uploader]);
            }
        }

        $texture->delete();
        $events->dispatch('texture.deleted', [$texture]);

        // 关联举报处理
        Report::where('tid', $tid)->get()->each(function ($report) use ($events) {
            $events->dispatch('report.reviewing', [$report, 'delete']);

            \App\Http\Controllers\ReportController::giveAward($report);
            \App\Http\Controllers\ReportController::returnScore($report);

            $report->status = Report::RESOLVED;
            $report->save();

            $events->dispatch('report.resolved', [$report, 'delete']);
        });

        return json('操作成功', 0);
    }
}
