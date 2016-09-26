<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 13.09.2016
 * Time: 9:47
 */

include 'main.php';

Log::set_debug(true);
Time::start();

$members = array();
$list = Sql::member_list();
$check_clan = date('Y-m-d H:00:00', Time::$start_time);
Log::add('Проверка игроков');
foreach ($list as $num => $item) {
	$member = new Member($item);
	$members[] = $member;
	if (Time::check() && $member->check_clan != $check_clan) $member->check_member();
}

if (Time::check()) {
	Log::add('Проверка кланов');
	$list = Sql::clan_list();
	Log::add('Получение информации о кланах');
	$updates = Api::clan(implode(',', array_values($list)), true);
	foreach ($list as $num => $item) {
		if (Time::check()) {
			$clan = new Clan($item);
			if ($clan->update_time != date('Y-m-d H:i:s', $updates->$item->updated_at)) $clan->check();
		}
	}
}

if (Time::check()) {
	Log::add('Проверка игр');
	$games = array('wot', 'wotb', 'wowp', 'wows');
	foreach ($games as $num => $item) {
		$game = new Game($item);
		if (Time::check()) $game->check();
	}
}

Log::add('Время выполнения скрипта ' . Time::script_time() . ' с.');
Log::end();
