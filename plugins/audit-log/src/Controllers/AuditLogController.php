<?php

namespace AuditLog\Controllers;

use AuditLog\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuditLogController extends Controller
{
    public function logPage()
    {
        $logs = AuditLog::where('user_id', auth()->id())->orderByDesc('created_at')->paginate(10);
        $actions = trans('AuditLog::log.actions');

        return view('AuditLog::user.log', ['logs' => $logs, 'actions' => $actions]);
    }

    public function adminLogPage()
    {
        return view('AuditLog::admin.log');
    }

    public function adminLogList(Request $request)
    {
        $q = $request->input('q');

        $logs = AuditLog::usingSearchString($q)->orderByDesc('created_at')->paginate(10);

        return $logs;
    }
}
