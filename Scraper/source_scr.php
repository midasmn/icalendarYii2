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

$mode = $_POST["mode"];
$htiSource = $_POST["htiSource"];
if ($mode=="scr")
{
    //スクレイピング処理
    // echo $htiSource;
    $rtn_imgs = f_wiki_source($db_conn,$htiSource);
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
function f_update_flg($db_conn,$node)
{
    $sql = "UPDATE `tbl_amazon` SET `cronflg` = 'ON' WHERE `node` = '$node'";
    $result = mysql_query($sql, $db_conn);
}


//
function f_wiki_source($db_conn,$htiSource)
{
    $rtn = array();
//     // // 文字列から
//     // $html = str_get_html( '<html><body>Hello!</body></html>' );
//     // // URLから
//     // $html = file_get_html( 'http://example.com/' );
//     // // HTMLファイルから
//     // $html = file_get_html( 'test.htm' );

    //スクレイピング       
    $html = str_get_html($htiSource);
// var_dump($html);
    $mm_cnt=0;
    // $href_cnt=0;
    // $date_cnt=0;
    // $item_cnt=0;
    $pattern = '/([1-9]|1[012])月([1-9]|[12][0-9]|3[01])日/';
    //date(title)
    foreach ($html->find('a') as $element)
    {
            $rtn['dd'][$mm_cnt]= $element->title; 
            //
            preg_match('/([1-9]|1[012])月([1-9]|[12][0-9]|3[01])日/', $rtn['dd'][$mm_cnt], $m);
            if($m)
            {
                echo "<br>".$m[1].",".$m[2].",";
                $month = $m[1];
                $day = $m[2];
            }else{
                echo "<br>".$month.",".$day.",".$rtn['dd'][$mm_cnt];
                $rtn['item'][$mm_cnt]= $element->plaintext;
                echo ",".$rtn['item'][$mm_cnt];
            }
            // echo ",".$rtn['item'][$mm_cnt];
            $mm_cnt++;
    }
    return "ok";
}

$yyyy = date('Y');
$mm = date('m');
$dd = date('d');
?>

<html>
<head>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
    <title>ソースペースト・スクレイピング</title>
</head> 
<body>
    <h1>wikiソーススクレイピング</h1>
    <hr>
    <a href="http://ja.wikipedia.org/wiki/%E8%AA%95%E7%94%9F%E8%8A%B1">wiki誕生花</a><br>
<form action="<?php echo basename($PHP_SELF); ?>" method="POST">
    <textarea name="htiSource" cols="50" rows="10"></textarea>
    <input type="hidden" name="mode" value="scr"> 
    <input type="submit" name="submit" value="ソース・スクレイピング"> 
</form>
<hr>
</body>
</html>
