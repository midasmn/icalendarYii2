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
function f_update_flg($db_conn,$table,$id)
{
    $sql = "UPDATE `$table` SET `cronflg` = '-' WHERE `id` = '$id'";
    $result = mysql_query($sql, $db_conn);
}


//amazonランキング画像取得
// $get_url = 'http://www.amazon.co.jp/gp/bestsellers/books/2278488051'; //アマゾンコミックベストセラー
// function f_amazon_scrape_img($db_conn,$exm_url,$calendar_id,$yyyy,$mm,$dd)
// function f_amazon_scrape_img($db_conn,$get_url,$calendar_id,$description,$yyyy,$mm,$dd)
function f_google_scrape_img($db_conn,$exm_url,$calendar_id,$id,$mm,$dd,$name_k,$name)
{
// echo "<br>".$get_url."<br>";
    $yyyy = 9999;
    $list_title = "誕生花";
    $rtn = array();
    // 画像取得
    // // 文字化け対策のおまじない的（？）なもの。
   // mb_language('Japanese');//←これ
   //  $html = mb_convert_encoding(file_get_html($get_url),'UTF-8','auto');
    $html = file_get_html($exm_url);
   //  //
    $img_cnt=0;
    //画像
    foreach ($html->find('img') as $element)
    {
            $rtn['img'][$img_cnt] = $element->src; 
            f_insert_ymd($db_conn,$calendar_id,$yyyy,$mm,$dd,$list_title,$rtn['img'][$img_cnt],$name,"",$img_cnt+1);
// echo "<br>".$rtn['img'][$img_cnt] ;
            $img_cnt++;
    }
    //D
    // 解放する
    $html->clear();
    unset($rtn);
    return "ok";
}

$yyyy = date('Y');
$mm = date('m');
$dd = date('d');

//////////////////
// DBからNODES読み込み
/////////////////
$calendar_id = 2; //カレンダーID
// $get_url = "https://www.google.co.jp/search?hl=ja&source=lnms&tbm=isch&tbs=isz:l&q=";  //取得URL Lサイズ
$get_url = "https://www.google.co.jp/search?hl=ja&source=lnms&tbm=isch&tbs=isz:m&q=";  //取得URL Mサイズ


$rtn_array = array();
//クーロン対象で未処理＆表示対象ON
$sql = 'SELECT `id`, `mm`, `dd`, `name_k`, `name` FROM `tbl_birthflower` WHERE `cronflg` = "ON" and `onflg` = "ON" order by `order` limit 100;';
$result = mysql_query($sql,$db_conn);
$cnt = 1;
if($result)
{
    while($link = mysql_fetch_row($result))
    {
        list($id,$mm,$dd,$name_k,$name) = $link;
        //処理用URL
        $exm_url = $get_url.urlencode($name);
        //スクレイピング処理
        $rtn_imgs = f_google_scrape_img($db_conn,$exm_url,$calendar_id,$id,$mm,$dd,$name_k,$name);
        //フラグUPDATE
        f_update_flg($db_conn,'tbl_birthflower',$id);
        // echo $cnt."件目<br>";
        $cnt++;
        sleep(1); // サーバへの負荷を減らすため 1 秒間遅延処理
    }
}
echo "end";
?>