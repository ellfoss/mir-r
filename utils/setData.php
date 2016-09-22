<?
include 'api.php';

$clanID = 0;
$clanTag = '';
$clanName = '';
$clanColor = '';
$clanMotto = '';
$clanLogo = '';
if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'id'"))) $clanID = $f['value'];
if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'tag'"))) $clanTag = $f['value'];
else mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('tag', '')");
if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'name'"))) $clanName = $f['value'];
else mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('name', '')");
if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'color'"))) $clanColor = $f['value'];
else mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('color', '')");
if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'motto'"))) $clanMotto = $f['value'];
else mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('motto', '')");
if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'logo'"))) $clanLogo = $f['value'];
else mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('logo', '')");
?>
