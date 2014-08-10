<?php

require 'simple_html_dom.php';

// header('Content-Type: text/html; charset=utf-8');

$host = "localhost";   // 接続するMySQLサーバー
$user = "root";      // MySQLのユーザー名
$pass = "mn9621mnZ+";      // MySQLのパスワード
$dbname= "icalendar";      // DBの名前

// データベースに接続
if(!$db_conn = mysql_connect($host, $user, $pass)){
print("データベースにアクセスできません。");
exit;
}
$rtn = mysql_query("SET NAMES utf8" , $db_conn);
mysql_select_db($dbname);

//インサート
function f_insert_ymd($db_conn,$calendar_id,$yyyy,$mm,$dd,$list_title,$img_path,$img_alt,$href,$order)
{
   mb_language('Japanese');//←これ
   $img_alt=mb_convert_encoding($img_alt,'UTF-8','auto');

    $sql = "INSERT INTO `tbl_ymd`(`id`, `calendar_id`, `yyyy`, `mm`, `dd`, `name`,`img_path`, `img_alt`, `href`, `order`, `createdate`) VALUES (NULL, '$calendar_id', '$yyyy', '$mm', '$dd', '$list_title','$img_path', '$img_alt', '$href', '$order', CURRENT_TIMESTAMP)";
    $result = mysql_query($sql, $db_conn);
    if(!$result)
    {
        $rtn =  "NG";
    }else{
        $rtn = "OK";
    }
    return $rtn;
}
/////////////
$yyyy = date('Y');
$mm = date('m');
$dd = date('d');
$w = date('w');//曜日 1=月曜日
// $dd = sprintf("%02d", $dd -1);
// $w = 1;
//////////////////
/////////////////
$calendar_id = 350; //yahoo人物デイリー総数
$list_title = "日本映画興行成績ランキング(月曜更新)";
$get_href = "http://movie.walkerplus.com/ranking/japan/";
// $get_url = "https://
// echo $get_href;

$rtn = array();
$img_cnt=0;
$title_cnt=0;
//ページ取得
$html = file_get_html($get_href);

//////////////////
if($w==1)
{
    //月曜なら処理
    foreach ($html->find('.movieMeta img') as $element)
    {
        $rtn['img'][$img_cnt] = $element->src; 
        // echo "<br>".$rtn['img'][$img_cnt];
        // $rtn['img'][$img_cnt] = str_replace('%22', '', $element->src); 
        // echo "<br>".$img_cnt."<img src=".$rtn['img'][$img_cnt].">";
        $img_cnt++;
    }
    //タイトル
     foreach ($html->find('.movieInfo strong a') as $element)
    {
        $rtn['title'][$title_cnt] = $element->plaintext; 
        // echo "<br>".$artist_cnt."title".$rtn['title'][$title_cnt];
        $title_cnt++;
    }
    $rtn_imgs = $rtn;
    //
    $cnt = count($rtn_imgs['title']);
    $i = 0;
    while ($i<$cnt) 
    {
        //insert
        $rtn = f_insert_ymd($db_conn,$calendar_id,$yyyy,$mm,$dd,$list_title,$rtn_imgs['img'][$i],$rtn_imgs['title'][$i],"",$i+1);
        $i++;
    // echo "<br>".$i;
    }  

    // 解放する
    $html->clear();
    unset($rtn);
    echo "<br>end";
}else{
    echo "月曜じゃない";
}


?>