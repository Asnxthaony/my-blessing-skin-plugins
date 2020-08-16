<?php

namespace mcstaralliance;

use App\Http\Controllers\Controller;
use DB;

require __DIR__.'/Utils/helpers.php';

class ConfigController extends Controller
{
    public function mcbbsPage()
    {
        $logs = DB::table('connect_mcbbs')->paginate(10);
        $group_names = yx_get_group_names();

        return view('mcstaralliance::mcbbs', ['logs' => $logs, 'group_names' => $group_names]);
    }
}
