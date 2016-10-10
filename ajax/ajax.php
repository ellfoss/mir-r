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

$out = array();
$out['status'] = 'ok';
$error = '';
$data = '';
$games = array('wot', 'wotb', 'wowp', 'wows');

$_POST['type'] = 'data';
$_SESSION['login'] = '1851278';

$rights = Sql::member_rights(isset($_SESSION['login']) ? $_SESSION['login'] : null, isset($_COOKIE['sessionID']) ? $_COOKIE['sessionID'] : null);

$type = $_POST['type'];
if ($type !== null) {
	if ($type == 'data') {
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
	} else $error = 'Неверный запрос';
} else $error = 'Неверный запрос';

Sql::close();

if ($error != '') {
	$out['status'] = 'error';
	$out['error'] = $error;
} else $out['data'] = $data;
echo json_encode($out);