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

$_POST['type'] = 'medal';
$_POST['medal'] = '174';
$_POST['game'] = 'wot';
$_POST['action'] = 'new';
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
				foreach ($data[$game]['medals'] as $num => $technic) {
					if (isset($technic['options']) && $technic['options'] != null) {
						$options = json_decode($technic['options']);
						$data[$game]['medals'][$num]['options'] = array();
						foreach ($options as $n => $option) {
							$value = Sql::medal_options($game, $option);
							$data[$game]['medals'][$num]['options'][$n] = $value[0];
						}
					}
				}
				if (Rights::admin()) {
					$data[$game]['changes']['medals'] = Sql::list_to_arr(Sql::game($game, 'medal', 'changes'), 'id');
					foreach ($data[$game]['changes']['medals'] as $num => $technic) {
						foreach ($technic as $name => $value) {
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
				$start_date = $_POST['start_date'];
				$stop_date = $_POST['stop_date'];
				if ($start_date && $stop_date) {
					$start_date = Sql::stat($id, $game, $start_date, 'full');
					if (!$start_date) $start_date = Sql::stat($id, $game, $stop_date, 'full');
					if ($start_date) {
						$data['start_date'] = $start_date;
						$data['stop_date'] = $stop_date;
						$start_date = new DateTime($start_date);
						$stop_date = new DateTime($stop_date);
						$interval = new DateInterval('P1D');
						$period = new DatePeriod($start_date, $interval, $stop_date);
						foreach ($period as $date) {
							$stat = new State($id, $game, 'part', $date->format('Y-m-d'));
							if ($stat->battles != null && $stat->battles != 0) {
								$data['stat'][$date->format('Y-m-d')] = $stat->out();
							}
						}
					}
				} else {
					$state = new State($id, $game);
					$data['stat'][date('Y-m-d')] = $state->out();
				}
			}
			break;

		case 'medal':
			$game = $_POST['game'];
			$id = $_POST['medal'];
			if ($game && $id) {
				$medal = new Medal($game, $id);
				$action = $_POST['action'];
				if ($action) {
					switch ($action) {
						case 'new':
							if ($medal->state == 'new') {
								$medal->change_field('state');
								$data[$game]['medals'][$id]['state'] = $medal->state;
							}
							break;

						case 'change':
							$field = $_POST['field'];
							if ($field) {
								if ($field == 'my_order' || $field == 'view') {
									$value = $_POST['value'];
									if ($value) {
										if ($medal->change_field($field, $value)) $data[$game]['medals'][$id][$field] = $medal->$field;
									}
								} else {
									$medal->accept_changes($field);
									$data[$game]['medals'][$id] = $medal->out();
								}
							}
							break;

						default:
							$error = 'Неверное действие';
					}
				}
			}
			break;

		case 'technic':
			$game = $_POST['game'];
			$id = $_POST['technic'];
			if ($game && $id) {
				$technic = new Technic($game, $id);
				$action = $_POST['action'];
				if ($action) {
					switch ($action) {
						case 'new':
							if ($technic->state == 'new') {
								$technic->change_field('state');
								$data[$game]['medals'][$id]['state'] = $technic->state;
							}
							break;

						case 'change':
							$field = $_POST['field'];
							if ($field) {
								if ($field == 'my_order' || $field == 'view') {
									$value = $_POST['value'];
									if ($value) {
										if ($technic->change_field($field, $value)) $data[$game]['medals'][$id][$field] = $technic->$field;
									}
								} else {
									$technic->accept_changes($field);
									$data[$game]['medals'][$id] = $technic->out();
								}
							}
							break;

						default:
							$error = 'Неверное действие';
					}
				}
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