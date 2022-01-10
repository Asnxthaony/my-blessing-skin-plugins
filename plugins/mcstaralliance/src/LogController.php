<?php

namespace mcstaralliance;

use Illuminate\Routing\Controller;
use mcstaralliance\Models\McbbsUser;

require __DIR__.'/Utils/helpers.php';

class LogController extends Controller
{
    public function mcbbsPage()
    {
        $records = McbbsUser::paginate(10);
        $group_names = yx_get_group_names();

        return view('mcstaralliance::log_mcbbs', ['records' => $records, 'group_names' => $group_names]);
    }
}
