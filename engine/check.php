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
$list_members = Sql::member_list();
Log::add('Проверка игроков');
$updates = Api::member(implode('%2C', array_values($list_members)), 'update');
foreach ($list_members as $num => $item) {
	$checkMember = date('Y-m-d H:i:s', $updates->$item->updated_at);
	$member = new Member($item);
	$members[$item] = $member;
	if (Time::check() && $member->check_clan != $checkMember) $member->check_member();
}

if (Time::check()) {
	Log::add('Проверка кланов');
	$list = Sql::clan_list();
	$updates = Api::clan(implode('%2C', array_values($list)), true);
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
		$list = Api::member(implode('%2C', array_values($list_members)), $item, 'time');
		foreach ($list as $num => $member) {
			$time = date('Y-m-d H:i:s', $member->last_battle_time);
			$field = $item . '_update';
			$last_time = $members[$num]->$field;
			if ($time != $last_time) $members[$num]->check_state($item);
		}
	}
}

Log::add('Время выполнения скрипта ' . Time::script_time() . ' с.');
Log::end();
