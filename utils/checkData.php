<?
set_time_limit(58);
header("Content-type: text/html; charset=utf-8");

include 'setData.php';
$out = '';
$br = '<br />';
$check = true;
$startTime = time();
$info = '';
$users = array();
$rs = mysqli_query($link, "SELECT * FROM `members`");
while($f = mysqli_fetch_assoc($rs)) $users[$f['id']] = $f;

include 'infoClan.php';
include 'infoWoT.php';
include 'infoWoTB.php';
include 'infoWoWP.php';
include 'infoWoWS.php';

checkClanData();
if($check) checkWoT();
if($check) checkWoTB();
if($check) checkWoWP();
if($check) checkWoWS();

$out .= 'Время выпонения скрипта '.(time() - $startTime).'с.'.$br;
if($check) $out .= $br.'Никаких изменений'.$br;
else{
	$dir = 'log/'.date('Y-m-d', $startTime);
	if(!(file_exists($dir))) mkdir($dir);
	$name = $dir.'/'.date('Y-m-d H-i', $startTime).'.txt';
	$fp = fopen($name, 'a');
	$test = fwrite($fp, str_replace($br, PHP_EOL, $out));
	fclose($fp);
};

echo $out;
