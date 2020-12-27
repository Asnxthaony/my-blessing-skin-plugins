<?php

namespace mcstaralliance;

use Illuminate\Routing\Controller;
use mcstaralliance\Models\DingtalkUser;
use mcstaralliance\Models\McbbsUser;
use mcstaralliance\Models\QQUser;

require __DIR__.'/Utils/helpers.php';

class LogController extends Controller
{
    public function mcbbsPage()
    {
        $records = McbbsUser::paginate(10);
        $group_names = yx_get_group_names();

        return view('mcstaralliance::log_mcbbs', ['records' => $records, 'group_names' => $group_names]);
    }

    public function qqPage()
    {
        $records = QQUser::paginate(10);

        return view('mcstaralliance::log_qq', ['records' => $records]);
    }

    public function dingtalkPage()
    {
        $records = DingtalkUser::paginate(10);

        return view('mcstaralliance::log_dingtalk', ['records' => $records]);
    }
}
