<?php
/**
 * User: yongli
 * Date: 17/4/13
 * Time: 09:36
 * Email: liyong@addnewer.com
 */
ini_set("memory_limit", "1024M");
require dirname(__FILE__) . '/../core/init.php';
//
///* Do NOT delete this comment */
///* 不要删除这段注释 */
//
$selector = [
    "//div[contains(@class,'left')]//div[contains(@class,'detail')]//h1",
    "//div[contains(@class,'detail')]//h4//span[contains(@class,'time')]",
    "//div[contains(@class,'detail')]//h4//span[contains(@class,'name')]",
    "//div[contains(@class,'detail')]//div",
    "//div[@id='comment']//div[contains(@class,'comFrame')]//ol[@id='top_comment']//li"
];
for ($i = 51932; $i < 315612; $i++) {
    $url = 'http://www.dongqiudi.com/article/' . $i;
    //        $url  = 'http://www.dongqiudi.com/article/' . 21032;
    $html = requests::get($url);
    // 抽取文章标题
    $title = selector::select($html, $selector[0]);
    // 文章时间
    $time = selector::select($html, $selector[1]);
    // 文章作者
    $author = selector::select($html, $selector[2]);
    // 文章内容
    $result  = selector::select($html, $selector[3]);
    $content = $result ? $result[0] : '';
    // 匹配视频
    preg_match('/<div class="video" .* src=\"(.*)\" .*>/', $content, $view);
    $url  = '';
    $type = 0;
    // 判断是否为视频
    if ($view) {
        $viewUrl = explode(' ', $view[1]);
        $url     = rtrim($viewUrl[0], '"');
        $type    = 1;
    }
    // 文章评论
    $comment = selector::select($html, $selector[4]);
    $com     = [];
    if (is_array($comment)) {
        $com = $comment;
    } else {
        if ($comment) {
            $com[] = $comment;
        }
    }
    $commentInfo = [];
    foreach ($com as $key => $value) {
        $img = $name = $time1 = $comment = $zan = [];
        preg_match('/<img.*?src="(.*?)".*>/is', $value, $img);
        preg_match('/<span class="name">(.*)<\/span>/isU', $value, $name);
        preg_match('/<span class="time">(.*)<\/span>/isU', $value, $time1);
        preg_match('/<p class="comCon">(.*)<\/p>/isU', $value, $comment);
        preg_match('/\（(.*)\）/isU', $value, $zan);
        $commentInfo[$key]['img']     = $img[1];
        $commentInfo[$key]['name']    = $name[1];
        $commentInfo[$key]['time']    = $time1[1];
        $commentInfo[$key]['comment'] = $comment[1];
        $commentInfo[$key]['zan']     = $zan[1];
    }
    $useInfo = db::get_one('select id,name from dqd_user where name="' . $author.'"');
    if ($useInfo) {
        $uid = $useInfo['id'];
    } else {
        // 插入用户表
        $uid = db::insert("dqd_user", ['name' => $author]);
        log::add('用户ID : ' . $uid . '插入成功');
        echo '用户ID : ' . $uid . '插入成功' . PHP_EOL;
    }
    $articleId = 0; // 文章ID
    // 插入文章数据
    if ($content) {
        $data      = [
            'id'          => $i,
            'url'         => $url,
            'type'        => $type,
            'title'       => $title,
            'content'     => $content,
            'date_time'   => $time,
            'create_by'   => $uid,
            'update_by'   => $uid,
            'create_time' => strtotime($time),
            'update_time' => strtotime($time),
        ];
        $articleId = db::insert("dqd_article", $data);
        log::add('文章ID : ' . $articleId . '插入成功');
        echo '文章ID : ' . $articleId . '插入成功' . PHP_EOL;
    }
    foreach ($commentInfo as $key => $value) {
        $useInfo = db::get_one('select id,name from dqd_user where name="' . $value['name'].'"');
        if ($useInfo) {
            $uid = $useInfo['id'];
        } else {
            $uid = db::insert('dqd_user', ['name' => $value['name'], 'img' => $value['img']]);
        }
        $commentData = [
            'comment'     => $value['comment'],
            'zan'         => $value['zan'],
            'create_time' => $value['time'],
            'update_time' => $value['time'],
            'create_by'   => $uid,
            'article_id'  => $articleId,
            'pid'         => 0,
        ];
        $commentId   = db::insert('dqd_comment', $commentData);
        log::add('评论ID : ' . $commentId . '插入成功');
        echo '评论ID : ' . $commentId . '插入成功' . PHP_EOL;
    }

}




