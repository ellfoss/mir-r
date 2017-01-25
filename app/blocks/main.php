<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 13.10.2016
 * Time: 16:27
 */

include '../engine/main.php';

session_start();
$id = $_SESSION['login'];
$member = null;
if ($id) $member = new Member($id, false);
$name = $_SESSION['nickname'];
if ($member) Rights::set($member->rights);
else Rights::set('guest');
//Rights::set('admin');

$clan = Sql::clans(Clan::get_main_clan_id());

$main = file_get_contents('main.html');
$main = str_replace('{TAG}', $clan[0]['tag'], $main);
$main = str_replace('{NAME}', $clan[0]['name'], $main);
$main = str_replace('{MOTTO}', $clan[0]['motto'], $main);
$main = str_replace('{COLOR}', $clan[0]['color'], $main);
$main = str_replace('{ENTER}', ($member ? 'Выход' : 'Вход'), $main);
$main = str_replace('{RIGHTS}', ($member ? $member->rights : 'guest'), $main);
$main = str_replace('{MEMBER}', ($member ? $member->id : ''), $main);
$main = str_replace('{MEMBER_NAME}', ($member ? $member->name : ''), $main);
$main = preg_replace('/{RIGHTS:ADMIN}(.*){\/RIGHTS}/sU', (Rights::admin() ? '$1' : ''), $main);
$main = preg_replace('/{RIGHTS:MEMBER}(.*){\/RIGHTS}/sU', (Rights::member() ? '$1' : ''), $main);

echo $main;
