<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 13.10.2016
 * Time: 10:52
 */

include 'engine/main.php';

$id = null;
$name = null;
$rights = 'guest';
if (isset($_COOKIE['sessionID'])) {
	if ($member = Sql::member($_COOKIE['sessionID'], 'sessionID')) {
		$id = $member['id'];
		$name = $member['name'];
		Rights::set($member['rights']);
		setcookie('sessionID', $f['sessionID'], time() + 1209600);
		session_id($_COOKIE['sessionID']);
	} else {
		setcookie('sessionID', $f['sessionID'], time() - 1209600);
	}
}
session_start();
if ($id) $_SESSION['login'] = $id;
else $id = $_SESSION['login'];
if ($id && $id != '') {
	if ($member = Sql::member($id)) {
		$name = $member['name'];
		Rights::set($member['rights']);
	}
}
if (isset($_SESSION['nickname']) && $name == null) $name = $_SESSION['nickname'];
Sql::visit($id);

$clan = new Clan(Clan::get_main_clan_id());

$loader = file_get_contents('blocks/loader.html');
$loader = str_replace('{TAG}', $clan->tag, $loader);
$loader = str_replace('{NAME}', $clan->name, $loader);
$loader = str_replace('{MOTTO}', $clan->motto, $loader);
$loader = str_replace('{COLOR}', $clan->color, $loader);

echo $loader;




