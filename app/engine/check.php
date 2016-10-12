<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 13.09.2016
 * Time: 9:47
 */

include 'main.php';

Log::set_debug(false);
Time::start();

$date = new DateTime();
if($date->format('d H') == '01 00') Sql::visitors(true);
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
	foreach ($games as $number => $item) {
		$game = new Game($item);
		if (Time::check()) {
			$game->check();
			$list = Api::member(implode('%2C', array_values($list_members)), $item, 'time');
			foreach ($list as $num => $member) {
				if($member !== null && $member->last_battle_time != 0) {
					$time = date('Y-m-d H:i:s', $member->last_battle_time);
					$field = $item . '_update';
					$last_time = $members[$num]->$field;
					if ($time != $last_time && Time::check()) {
						$members[$num]->check_state($item);
						$members[$num]->$field = $time;
						$members[$num]->save_member();
					}
				}
			}
		}
	}
}

Sql::close();

Log::add('Время выполнения скрипта ' . Time::script_time() . ' с.');
Log::end();
