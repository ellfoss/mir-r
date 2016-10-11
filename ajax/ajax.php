<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 10.10.2016
 * Time: 13:55
 */

set_time_limit(60);
header("Content-type: text/html; charset=utf-8");

include '../engine/main.php';

session_start();

$out = array();
$out['status'] = 'ok';
$error = '';
$data = '';
$games = array('wot', 'wotb', 'wowp', 'wows');

$_POST['type'] = 'state';
//$_POST['action'] = 'new';
$_POST['member'] = '1851278';
//$_POST['name'] = 'ellfoss';
$_POST['game'] = 'wot';
$_SESSION['login'] = '1851278';

$rights = Sql::member_rights(isset($_SESSION['login']) ? $_SESSION['login'] : null, isset($_COOKIE['sessionID']) ? $_COOKIE['sessionID'] : null);

$type = $_POST['type'];
if ($type !== null) {
	switch ($type) {
		case 'data':
			$data['data'] = Sql::data();
			foreach ($games as $game) {
				$data[$game]['data'] = Sql::arr_to_list(Sql::game($game, 'data'), 'variable', 'value');
				$data[$game]['technics'] = Sql::list_to_arr(Sql::game($game, 'technic'), 'id');
				if (Rights::admin()) {
					$data[$game]['changes']['technics'] = Sql::list_to_arr(Sql::game($game, 'technic', 'changes'), 'id');
					foreach ($data[$game]['changes']['technics'] as $num => $technc) {
						foreach ($technc as $name => $value) {
							if ($name == 'id' || $value === null) unset($data[$game]['changes']['technics'][$num][$name]);
						}
					}
					if (count($data[$game]['changes']['technics']) == 0) unset ($data[$game]['changes']);
				}
				$data[$game]['medals'] = Sql::list_to_arr(Sql::game($game, 'medal'), 'id');
				foreach ($data[$game]['medals'] as $num => $medal) {
					if (isset($medal['options']) && $medal['options'] != null) {
						$options = json_decode($medal['options']);
						$data[$game]['medals'][$num]['options'] = array();
						foreach ($options as $n => $option) {
							$value = Sql::medal_options($game, $option);
							$data[$game]['medals'][$num]['options'][$n] = $value[0];
						}
					}
				}
				if (Rights::admin()) {
					$data[$game]['changes']['medals'] = Sql::list_to_arr(Sql::game($game, 'medal', 'changes'), 'id');
					foreach ($data[$game]['changes']['medals'] as $num => $medal) {
						foreach ($medal as $name => $value) {
							if ($name == 'id' || $value === null) unset($data[$game]['changes']['medals'][$num][$name]);
							if ($name == 'options' && $value != null) {
								$options = json_decode($value);
								$data[$game]['changes']['medals'][$num]['options'] = array();
								foreach ($options as $n => $option) {
									$value = Sql::medal_options($game, $option);
									$data[$game]['changes']['medals'][$num]['options'][$n] = $value[0];
								}
							}
						}
					}
					if (count($data[$game]['changes']['medals']) == 0) unset ($data[$game]['changes']['medals']);
					if (count($data[$game]['changes']) == 0) unset ($data[$game]['changes']);
				}

			}
			if (Rights::member()) $data['clans'] = Sql::list_to_arr(Sql::clans(), 'id');
			if (Rights::admin()) $data['visitors'] = Sql::visitors();
			break;

		case 'member':
			$action = $_POST['action'];
			$member = null;
			$id = $_POST['member'];
			if ($id) $member = new Member($id);
			switch ($action) {
				case 'delete':
					if (Rights::admin()) {
						if (Rights::compare($member->rights)) {
							if (!$member->delete()) $error = 'Удаление неудачно';
						} else $error = 'Удаление невозможно';
					} else $error = 'Недостаточно прав';
					break;

				case 'rename':
					if (Rights::admin()) {
						$name = $_POST['name'];
						if ($name) {
							$member->real_name = $name;
							$member->save_member();
						}
					} else $error = 'Недостаточно прав';
					break;

				case 'find':
					$name = $_POST['name'];
					if ($name) $data['find'] = Api::member($name);
					break;

				case 'new':
					if ($member->name == null) $error = 'Данного игрока не существует';
					elseif (!$member->new) $error = 'Данный игрок уже присутствует в базе';
					break;

				default:
					$data['members'] = Sql::list_to_arr(Sql::member(null, Rights::admin() ? null : Clan::get_main_clan_id()), 'id');
					foreach ($data['members'] as $number => $member) {
						$games = json_decode($data['members'][$number]['games']);
						$updates = array();
						foreach ($games as $num => $game) {
							if ($game == 'wot') $updates['wot'] = $member['wotUpdate'];
							if ($game == 'wotb') $updates['wotb'] = $member['wotbUpdate'];
							if ($game == 'wowp') $updates['wowp'] = $member['wowpUpdate'];
							if ($game == 'wows') $updates['wows'] = $member['wowsUpdate'];
							if ($game == 'wotg') $updates['wotg'] = null;
						};
						unset($data['members'][$number]['wotUpdate']);
						unset($data['members'][$number]['wotbUpdate']);
						unset($data['members'][$number]['wowpUpdate']);
						unset($data['members'][$number]['wowsUpdate']);
						$data['members'][$number]['games'] = $updates;
					}
			}
			break;

		case 'state':
			$id = $_POST['member'];
			$game = $_POST['game'];
			if ($id) {
				$state = new State($id, $game);
				$data = $state->out();
			}
			break;

		default:
			$error = 'Неверный запрос';
	}
} else $error = 'Неверный запрос';

Sql::close();

if ($error != '') {
	$out['status'] = 'error';
	$out['error'] = $error;
} elseif ($data != '') $out['data'] = $data;
echo json_encode($out);