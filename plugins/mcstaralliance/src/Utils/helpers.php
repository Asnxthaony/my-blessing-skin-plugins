<?php

if (!function_exists('yx_get_group_names')) {
    function yx_get_group_names()
    {
        return [
            '1' => '管理员',
            '2' => '超级版主',
            '3' => '版主',
            '4' => 'Lv.? 禁止发言',
            '5' => 'Lv.? 禁止访问',
            '6' => 'Lv.? 禁止 IP',
            '7' => '游客',
            '8' => '等待验证会员',
            '9' => 'Lv.? Herobrine',
            '10' => 'Lv.0 流浪者',
            '11' => 'Lv.1 伐木工',
            '12' => 'Lv.2 采石匠',
            '13' => 'Lv.3 挖沙工',
            '14' => 'Lv.4 矿工',
            '15' => 'Lv.5 农夫',
            '16' => '实习版主',
            '20' => 'Lv.6 手艺人',
            '21' => 'Lv.7 猎手',
            '22' => 'Lv.8 考古家',
            '23' => 'Lv.9 牧场主',
            '27' => 'Lv.10 附魔师',
            '28' => 'Lv.11 领主',
            '29' => 'Lv.12 屠龙者',
            '34' => '大区版主',
            '35' => '问答区版主',
            '40' => '常务版主',
            '41' => 'QQ 游客',
            '43' => 'Lv.Inf 艺术家',
            '44' => '荣誉版主',
            '45' => '大区常务版主',
            '46' => '大区荣誉版主',
            '47' => '认证用户',
            '48' => '管理员助理',
            '51' => '村民',
            '52' => '专区版主',
            '54' => '电鳗',
            '57' => '哔',
        ];
    }
}

if (!function_exists('yx_gid_to_gn')) {
    function yx_gid_to_gn($group_id)
    {
        $group_names = yx_get_group_names();

        return isset($group_names[$group_id]) ? $group_names[$group_id] : $group_id;
    }
}
