<?php

namespace mcstaralliance;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

require __DIR__.'/Utils/helpers.php';

class McbbsController extends Controller
{
    public function mcbbsPage()
    {
        $records = DB::table('connect_mcbbs')->paginate(10);
        $group_names = yx_get_group_names();

        return view('mcstaralliance::mcbbs', ['records' => $records, 'group_names' => $group_names]);
    }
}
