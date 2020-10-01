<?php

namespace mcstaralliance;

use Illuminate\Routing\Controller;
use mcstaralliance\Models\QQUser;

require __DIR__.'/Utils/helpers.php';

class QQController extends Controller
{
    public function qqPage()
    {
        $records = QQUser::paginate(10);

        return view('mcstaralliance::qq', ['records' => $records]);
    }
}
