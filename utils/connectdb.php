<?
$user = "kashni_den";
$password = "wWhYOhDP5r";
$base = "kashni_mirr";
$link = mysqli_connect("localhost",$user,$password) or die ('Not connected : ' . mysqli_error($link));
mysqli_query($link, "SET NAMES utf8") or die("Invalid query: " . mysqli_error($link));
mysqli_select_db($link, $base) or die ("Can\'t use $base : " . mysqli_error($link));
