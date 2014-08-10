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

//google
function f_google_scrape_img_YAHOO($db_conn,$exm_url,$calendar_id,$list_title,$yyyy,$mm,$dd,$name,$order)
{
    $html = file_get_html($exm_url);
    $img_cnt=0;
    //画像
    foreach ($html->find('img') as $element)
    {
            $rtn['img'][$img_cnt] = $element->src;
            if($img_cnt==0)
            {
echo '<br><img src="'.$rtn['img'][$img_cnt].'">'.$name;
                f_insert_ymd($db_conn,$calendar_id,$yyyy,$mm,$dd,$list_title,$rtn['img'][$img_cnt],$name,"",$order);
                $img_cnt++;    
            }
    }
    // 解放する
    $html->clear();
    unset($rtn);
    return "ok";
}

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
//////////////////
/////////////////
$calendar_id = 343; //yahoo人物デイリー総数
$list_title = "Yahoo人物総数ランキング(デイリー)";
$get_href = "http://searchranking.yahoo.co.jp/total_ranking/people/";
// $get_url = "https://www.google.co.jp/search?hl=ja&source=lnms&tbm=isch&tbs=isz:l&q=";  //取得URL Lサイズ
// $get_img = "https://www.google.co.jp/search?hl=ja&source=lnms&tbm=isch&tbs=isz:l&q=";  //取得URL Mサイズ
$get_img = "https://www.google.co.jp/search?hl=ja&source=lnms&tbm=isch&tbs=isz:lt,islt:svga&q=";

$rtn = array();
$cnt=0;
//ページ取得
$html = file_get_html($get_href);
//キーワード取得
foreach ($html->find('.patD a') as $element)
{
    //ランク取得
    $rtn['rank'][$cnt] = $element->plaintext; 
    //画像検索用エンコード
    $exm_url = $get_img.urlencode("人物 ".$rtn['rank'][$cnt]);
    // 画像スクレイピング処理
    $rtn_img = f_google_scrape_img_YAHOO($db_conn,$exm_url,$calendar_id,$list_title,$yyyy,$mm,$dd,$rtn['rank'][$cnt],$cnt+1);
    $cnt++;
}
// 解放する
$html->clear();
unset($rtn);

echo "end";
?>