<?php
/**
 * User: yongli
 * Date: 17/4/17
 * Time: 09:36
 * Email: liyong@addnewer.com
 */
ini_set("memory_limit", "1024M");
require dirname(__FILE__) . '/../core/init.php';
//
///* Do NOT delete this comment */
///* 不要删除这段注释 */
$selector = [
    //    "//div[@id='pjax-container']//div[@id='news_list']//ol//li//h2//a",
    '//*[@id="news_list"]//ol//li//h2//a',
];
//foreach ($array as $i) {
$j = 68;
for ($i = 1; $i <= 41; $i++) {
    // 栏目文章链接
    $url  = 'http://www.dongqiudi.com/archives/' . $j . '?page=' . $i;
    $html = requests::get($url);
    $html = json_decode($html, true);
    $data = is_array($html) ? $html['data'] : [];
    $tmpData = [];
    foreach ($data as $key => $value) {
        $tmpData[$key]['category_id'] = $j;
        $tmpData[$key]['article_id']  = $value['id'];
        $tmpData[$key]['thumb']       = $value['thumb'];
    }
    $id  = db::insert_batch('dqd_category_article', $tmpData);
    $str = $id ? '批量插入:第 ' . $i . ' 条' : '批量插入:第 ' . $i . ' 条失败';
    log::msg($str . "\r\n", 'note');
    echo $str . "\r\n";
}
