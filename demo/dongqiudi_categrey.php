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
$i = 2;
//for ($i = 1; $i <= 7517; $i++) {
// 栏目文章链接
$url  = 'http://www.dongqiudi.com/archives/56?page=' . $i;
$html = requests::get($url);
$html = json_decode($html, true);
$data = is_array($html) ? $html['data'] : [];
//    $result  = array_column($data, 'id');
$tmpData = [];
foreach ($data as $key => $value) {
    $tmpData[$key]['category_id'] = 1;
    $tmpData[$key]['article_id']  = $value['id'];
    $tmpData[$key]['thumb']       = $value['thumb'];
}
//$id  = db::insert_batch('dqd_category_article', $tmpData);
$id  = 1;
$str = $id ? '批量插入:第 ' . $i . ' 条' . PHP_EOL : '批量插入:第 ' . $i . ' 条失败' . PHP_EOL;
$a   = log::msg($str, 'note');
print_r($a);
echo $str;
//}
