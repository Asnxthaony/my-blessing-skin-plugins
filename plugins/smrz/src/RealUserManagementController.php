<?php

namespace Asnxthaony\Smrz;

use Asnxthaony\Smrz\Models\RealUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RealUserManagementController extends Controller
{
    public function show(Request $request)
    {
        $records = RealUser::orderByDesc('created_at')->paginate(10);
        $states = [
            0 => '审核中',
            1 => '审核通过',
            2 => '审核未通过',
        ];

        return view('Asnxthaony\Smrz::admin', ['records' => $records, 'states' => $states]);
    }

    public function handle(Request $request)
    {
    }
}
