<?php
/**
 * User: yongli
 * Date: 17/4/17
 * Time: 09:36
 * Email: liyong@addnewer.com
 */
//ini_set("memory_limit", "1024M");
require dirname(__FILE__) . '/../core/init.php';
//
///* Do NOT delete this comment */
///* 不要删除这段注释 */
$selector = [
    "//div[@id='stat_detail']//table[@id='table']//tr",
];
$array    = [
    51,
    8,
    7,
    9,
    13,
    10,
    16,
    148,
    251,
    18,
    276,
    93,
    138,
    104,
    95,
    135,
    70,
    1,
    63,
    14,
    12,
    11,
    17,
    121,
    19,
    109,
    113,
    136,
    225,
    33,
    87,
    241,
    26,
    284
];
foreach ($array as $i) {
    // 射手榜链接地址
    $url  = 'http://www.dongqiudi.com/data?competition=' . $i . '&type=goal_rank';
    $html = requests::get($url);
    // 排名信息
    $playerArr = selector::select($html, $selector[0]);
    $playerArr = is_array($playerArr) ? $playerArr : [];
    // 去掉数组第一个值
    if ($playerArr) {
        //        echo 'teamID: ' . $i . PHP_EOL;
        array_shift($playerArr);
    }
    $rankArr = $player = $teamArr = [];
    foreach ($playerArr as $key => $value) {
        $head_img = $logo_img = $tmp_name = $name = $tmp_team_name = $team_name = $num = [];
        //    preg_match('/<img.*?src="(.*?)".*>/is', $value, $img);
        preg_match('/<td class="player">(.*)<\/td>/isU', $value, $name);
        preg_match('/<img.*?src="(.*)".*>(.*?)/isU', $name[1], $tmp_name);
        preg_match('/<td class="team">(.*)<\/td>/isU', $value, $team_name);
        preg_match('/<img.*?src="(.*)".*>(.*?)/isU', $team_name[1], $tmp_team_name);
        preg_match('/<td class="stat">(.*)<\/td>/isU', $value, $num);
        $rankArr[$key]['head_img']  = $tmp_name[1];
        $rankArr[$key]['name']      = $tmp_name[2];
        $rankArr[$key]['logo_img']  = $tmp_team_name[1];
        $rankArr[$key]['team_name'] = $tmp_team_name[2];
        $rankArr[$key]['num']       = $num[1];
    }
    foreach ($rankArr as $key => $value) {
        $playerId = $team_id = 0;
        // 添加球队信息
        $teamInfo = db::get_one('select id,name from dqd_ball_team where name="' . $value['team_name'] . '"');
        if ($teamInfo) {
            $team_id = $teamInfo['id'];
        } else {
            $teamData = [
                'name'        => $value['team_name'],
                'logo_img'    => $value['logo_img'],
                'create_time' => strtotime('-10 days'),
                'update_time' => strtotime('-10 days'),
                'create_by'   => 1,
                'update_by'   => 1,
            ];
            $team_id  = db::insert('dqd_ball_team', $teamData);
            log::add('团队ID : ' . $team_id . '插入成功');
            echo '团队ID : ' . $team_id . '插入成功' . PHP_EOL;
            unset($teamData);
        }
        // 添加球员信息
        $playerInfo = db::get_one('select id,name from dqd_player where name="' . $value['name'] . '"');
        if ($playerInfo) {
            $playerId = $playerInfo['id'];
        } else {
            $playerData = [
                'name'        => $value['name'],
                'team_id'     => $team_id,
                'head_img'    => $value['head_img'],
                'create_time' => strtotime('-10 days'),
                'update_time' => strtotime('-10 days'),
                'create_by'   => 1,
                'update_by'   => 1,
            ];
            $playerId   = db::insert('dqd_player', $playerData);
            log::add('球员ID : ' . $playerId . '插入成功');
            echo '球员ID : ' . $playerId . '插入成功' . PHP_EOL;
        }
        // 添加助攻信息
        $rankArr = db::get_one('select id from dqd_rank where game_id=' . $i . ' and player_id=' . $playerId);
        if (!$rankArr) {
            $rank   = [
                'player_id'   => $playerId,
                'team_id'     => $team_id,
                'game_id'     => $i,
                'num'         => $value['num'],
                'create_time' => strtotime('-10 days'),
                'update_time' => strtotime('-10 days'),
                'create_by'   => 1,
                'update_by'   => 1,
            ];
            $rankId = db::insert('dqd_rank', $rank);
            log::add('射手榜ID : ' . $rankId . '插入成功');
            echo '射手榜ID : ' . $rankId . '插入成功' . PHP_EOL;
        }

    }
}