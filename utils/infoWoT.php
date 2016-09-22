<?
function checkWoT()
{
	global $out, $br, $check, $info, $startTime;

	$out .= 'Танки!' . $br;
	$info = getApiRequest('wot', '', 'info');

	if ($check) checkWotTanks();
	if ((time() - $startTime) > 50) $check = false;
	if ($check) checkWotmedals();
	if ((time() - $startTime) > 50) $check = false;
	if ($check) checkWotStat();
	if ((time() - $startTime) > 50) $check = false;
	if ($check) checkWotExpectedTankvalues();
	if ((time() - $startTime) > 50) $check = false;
	if ($check) checkWotEffectColor();
	if ((time() - $startTime) > 50) $check = false;
}

function checkWotStat()
{
	global $out, $br, $startTime, $check, $users;

	$list = '';
	foreach ($users as $memberid => $user) $list .= $memberid . ',';
	$list = substr($list, 0, -1);
	$updates = getApiRequest('member', $list, 'wot_battle_time');
	if ($updates != '') {
		foreach ($updates as $member => $update) {
			$time = time() - $startTime;
			if($time > 50) break;
			if ($update['last_battle_time'] == 0) $update = null;
			if ($update != null && (date('d') == '01' || date('Y-m-d H:i:s', $update['last_battle_time']) != $users[$member]['wotUpdate'])) updateWotMemberStat($member);
		};
	};
	if ($check) $out .= 'Танковая статистика без изменений' . $br;
}

function updateWotMemberStat($member)
{
	global $link, $out, $br, $check, $users;

	$out .= 'Проверка пользователя ' . $users[$member]['name'] . $br;
	$update = false;
	$date = date('Y-m-d');
	$startMonth = date('Y-m-01');

	$medals = array();
	$rs = mysqli_query($link, "SELECT `id`, `name` FROM `wot_medals`");
	while ($f = mysqli_fetch_assoc($rs)) $medals[$f['name']] = $f['id'];

	$updateMember = $users[$member]['wotUpdate'];
	$memberUpdate = getApiRequest('member', $member, 'wot_battle_time');
	$newUpdate = date('Y-m-d H:i:s', $memberUpdate[$member]['last_battle_time']);
	if (date('d') == '01' && !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_stat` WHERE `date` = '$date' AND `id` = '$member'")))) {
		$update = true;
		$out .= $users[$member]['name'] . ' - условие 1 (Начало месяца, а записи ещё нет)' . $br;
	};
	if (!$update && (!(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_stat` WHERE `date` >= '$startMonth' AND `id` = '$member' AND `type` = 'full'"))))) {
		$update = true;
		$out .= $users[$member]['name'] . ' - условие 2 (Нет полной записи за текущий месяц)' . $br;
	};
	if (!$update && $newUpdate != null && ($updateMember == null || $newUpdate > $updateMember)) {
		$update = true;
		$out .= $users[$member]['name'] . ' - условие 3 (Есть новые события)' . $br;
	};
	if ($update) {
		$check = false;
		$type = 'part';
		if (date('d') == '01' || !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' AND `type` = 'full'")))) $type = 'full';
		$oldStat = array();
		if ($type == 'part') {
			if ($rs = mysqli_query($link, "SELECT * FROM `wot_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' ORDER BY `date`")) {
				while ($f = mysqli_fetch_assoc($rs)) {
					if ($f['type'] == 'full') {
						$oldStat = $f;
						$oldTankStat = array();
						if ($f['technics'] != null) $oldTankStat = objectToArray(json_decode($f['technics']));
						$oldMedalStat = array();
						if ($f['medals'] != null) $oldMedalStat = objectToArray(json_decode($f['medals']));
					} else {
						foreach ($f as $key => $value) {
							if ($value != null && $key != 'date' && $key != 'id' && $key != 'type' && $key != 'technics' && $key != 'medals') $oldStat[$key] += $value;
						};
						if ($f['technics'] != null) {
							$tanks = objectToArray(json_decode($f['technics']));
							foreach ($tanks as $tankid => $tank) {
								if (isset($oldTankStat[$tankid])) {
									$oldTankStat[$tankid][0] += $tank[0];
									$oldTankStat[$tankid][1] += $tank[1];
									$oldTankStat[$tankid][2] += $tank[2];
								} else $oldTankStat[$tankid] = $tank;
							};
						};
						if ($f['medals'] != null) {
							$statmedals = objectToArray(json_decode($f['medals']));
							foreach ($statmedals as $medal => $value) {
								if (isset($oldMedalStat[$medal])) $oldMedalStat[$medal] += $value;
								else $oldMedalStat[$medal] = $value;
							};
						};
					};
				};
			} else $out .= $users[$member]['name'] . ' - нет записи предыдущих боёв' . $br;
		};
		$stat = getApiRequest('member', $member, 'wot');
		$tankStat = getApiRequest('member', $member, 'wot_tanks');
		$medalStat = getApiRequest('member', $member, 'wot_medals');
		if ($stat != '' && $tankStat != '' && $medalStat != '') {
			mysqli_query($link, "UPDATE `members` SET `wotUpdate` = '$newUpdate' WHERE `id` = '$member'");

			$newStat = array();
			$newStat['date'] = $date;
			$newStat['id'] = $member;
			$newStat['type'] = $type;
			$newStat['battles'] = $stat[$member]['statistics']['all']['battles'];
			$newStat['wins'] = $stat[$member]['statistics']['all']['wins'];
			$newStat['losses'] = $stat[$member]['statistics']['all']['losses'];
			$newStat['survived'] = $stat[$member]['statistics']['all']['survived_battles'];
			$newStat['xp'] = $stat[$member]['statistics']['all']['xp'];
			$newStat['damageD'] = $stat[$member]['statistics']['all']['damage_dealt'];
			$newStat['damageR'] = $stat[$member]['statistics']['all']['damage_received'];
			$newStat['frags'] = $stat[$member]['statistics']['all']['frags'];
			$newStat['spotted'] = $stat[$member]['statistics']['all']['spotted'];
			$newStat['capture'] = $stat[$member]['statistics']['all']['capture_points'];
			$newStat['dropped'] = $stat[$member]['statistics']['all']['dropped_capture_points'];
			$newStat['hits'] = $stat[$member]['statistics']['all']['hits'];
			$newStat['shots'] = $stat[$member]['statistics']['all']['shots'];

			$newTankStat = array();
			foreach ($tankStat[$member] as $key => $tank) {
				$tankid = $tank['tank_id'];
				$newTankStat[$tankid] = array();
				$newTankStat[$tankid][0] = $tank['statistics']['battles'];
				$newTankStat[$tankid][1] = $tank['statistics']['wins'];
				$newTankStat[$tankid][2] = $tank['mark_of_mastery'];
			};

			$newMedalStat = array();
			foreach ($medalStat[$member]['achievements'] as $medal => $value) {
				if (isset($medals[$medal])) $newMedalStat[$medals[$medal]] = $value;
			};

			if ($type == 'part') {
				foreach ($newStat as $key => $value) {
					if ($key != 'date' && $key != 'id' && $key != 'type') {
						$newStat[$key] -= $oldStat[$key];
						if ($newStat[$key] == 0) $newStat[$key] = null;
					};
				};
				foreach ($newTankStat as $tankid => $tank) {
					if (isset($oldTankStat[$tankid]) && isset($newTankStat[$tankid][0]) && $newTankStat[$tankid][0] != 0) {
						foreach ($tank as $key => $value) $newTankStat[$tankid][$key] -= $oldTankStat[$tankid][$key];
						if ($newTankStat[$tankid][0] == 0) unset($newTankStat[$tankid]);
					};
				};
				foreach ($newMedalStat as $medal => $value) {
					if (isset($oldMedalStat[$medal])) {
						$newMedalStat[$medal] -= $oldMedalStat[$medal];
						if ($newMedalStat[$medal] == 0) unset($newMedalStat[$medal]);
					};
				};
			};
			if (count($newTankStat) == 0) $newStat['technics'] = null;
			else $newStat['technics'] = json_encode($newTankStat);
			if (count($newMedalStat) == 0) $newStat['medals'] = null;
			else $newStat['medals'] = json_encode($newMedalStat);

			if ($type == 'full' || $newStat['battles'] != 0) {
				$check = false;
				if (mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_stat` WHERE `date` = '$date' AND `id` = '$member'"))) {
					$s = '';
					foreach ($newStat as $key => $value) if ($key != 'date' && $key != 'id') $s .= "`$key` = " . ($value == null ? "NULL, " : "'$value', ");
					$s = substr($s, 0, -2);
					$query = "UPDATE `wot_stat` SET $s WHERE `date` = '" . $newStat['date'] . "' AND `id` = '" . $newStat['id'] . "'";
				} else {
					$var = $val = '';
					foreach ($newStat as $key => $value) $var .= "`$key`, ";
					$var = substr($var, 0, -2);
					foreach ($newStat as $key => $value) $val .= $value == null ? "NULL, " : "'$value', ";
					$val = substr($val, 0, -2);
					$query = "INSERT INTO `wot_stat` ($var) VALUES ($val)";
				};
				mysqli_query($link, $query);
				$out .= $users[$member]['name'] . ' - ' . $date . ': ' . $newStat['battles'] . ' боёв' . $br;
			} else $out .= $users[$member]['name'] . ' - нет боёв' . $br;
		};
	} else {
		mysqli_query($link, "UPDATE `members` SET `wotUpdate` = '$newUpdate' WHERE `id` = '$member'");
		$out .= $users[$member]['name'] . ' - нет событий' . $br;
	};
}

function checkWotTanks()
{
	global $link, $out, $br, $check, $info;

	if ($info != '') {
		$value = json_replace(json_encode($info['vehicle_types']));
		if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'technicTypes'"))) {
			if ($f['value'] != $value) mysqli_query($link, "UPDATE `wot_data` SET `value` = '$value' WHERE `variable` = 'technicTypes'");
		} else {
			mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('technicTypes', '$value')");
		};
		$value = json_replace(json_encode($info['vehicle_nations']));
		if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'technicNations'"))) {
			if ($f['value'] != $value) mysqli_query($link, "UPDATE `wot_data` SET `value` = '$value' WHERE `variable` = 'technicNations'");
		} else {
			mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('technicNations', '$value')");
		};

		$tanksUpdate = date('Y-m-d H:i:s', $info['tanks_updated_at']);
		$check = false;
		if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'technicUpdate'"))) {
			if ($f['value'] == $tanksUpdate) {
				$check = true;
				$out .= 'Проверка списка танков не требуется' . $br;
			} else mysqli_query($link, "UPDATE `wot_data` SET `value` = '$tanksUpdate' WHERE `variable` = 'technicUpdate'");
		} else mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('technicUpdate', '$tanksUpdate')");
	};
	if (!$check) {
		$out .= 'Проверка танков' . $br;
		$wot_tanks = getApiRequest('wot', '', 'tanks');
		if ($wot_tanks != '') {
			foreach ($wot_tanks as $tankid => $tank) {
				$curTank = array();
				$curTank['id'] = $tankid;
				$curTank['name'] = $tank['name'];
				$curTank['shortName'] = $tank['short_name'];
				$curTank['image'] = $tank['images']['small_icon'];
				$curTank['level'] = $tank['tier'];
				$curTank['type'] = $tank['type'];
				$curTank['nation'] = $tank['nation'];
				$curTank['isPrem'] = $tank['is_premium'] ? 1 : 0;

				if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_technics` WHERE `id` = '$tankid'"))) {
					foreach ($f as $key => $value) {
						if ($key != 'id' && $key != 'state') {
							$curvalue = $curTank[$key];
							if ($curvalue != $value) {
								if ($f['state'] == null || $f['state'] == 'chn') {
									if ($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_technic_changes` WHERE `id` = '$tankid'"))) mysqli_query($link, "UPDATE `wot_technic_changes` SET `$key` = '$curvalue' WHERE `id` = '$tankid'");
									else mysqli_query($link, "INSERT INTO `wot_technic_changes` (`id`, `$key`) VALUES ('$tankid', '$curvalue')");
								} else mysqli_query($link, "UPDATE `wot_technics` SET `$key` = '$curvalue' WHERE `id` = '$tankid'");
								if ($key == 'name') $out .= 'Танк "' . $curTank['name'] . '" - изменение имени' . $br;
								if ($key == 'shortName') $out .= 'Танк "' . $curTank['name'] . '" - изменение короткого имени' . $br;
								if ($key == 'image') $out .= 'Танк "' . $curTank['name'] . '" - изменение изображения' . $br;
								if ($key == 'level') $out .= 'Танк "' . $curTank['name'] . '" - изменение уровня' . $br;
								if ($key == 'type') $out .= 'Танк "' . $curTank['name'] . '" - изменение типа' . $br;
								if ($key == 'nation') $out .= 'Танк "' . $curTank['name'] . '" - изменение нации' . $br;
								if ($key == 'isPrem') $out .= 'Танк "' . $curTank['name'] . '" - изменение премиумности' . $br;
							} else mysqli_query($link, "UPDATE `wot_technic_changes` SET `$key` = NULL WHERE `id` = '$tankid'");
						};
					};
					$changes = false;
					if ($rs = mysqli_query($link, "SELECT * FROM `wot_technic_changes` WHERE `id` = '$tankid'")) {
						if ($fc = mysqli_fetch_assoc($rs)) {
							if ($fc['name'] != null || $fc['shortName'] != null || $fc['image'] != null || $fc['level'] != null || $fc['type'] != null || $fc['nation'] != null || $fc['isPrem'] != null) $changes = true;
						};
					};
					if ($f['state'] == null || $f['state'] == 'chn') {
						if ($changes) mysqli_query($link, "UPDATE `wot_technics` SET `state` = 'chn' WHERE `id` = '$tankid'");
						else {
							mysqli_query($link, "UPDATE `wot_technics` SET `state` = NULL WHERE `id` = '$tankid'");
							mysqli_query($link, "DELETE FROM `wot_technic_changes` WHERE `id` = '$tankid'");
						};
					} else mysqli_query($link, "DELETE FROM `wot_technic_changes` WHERE `id` = '$tankid'");
				} else {
					$var = $val = '';
					foreach ($curTank as $key => $value) $var .= "`$key`, ";
					$var = substr($var, 0, -2);
					foreach ($curTank as $key => $value) $val .= $value === null ? "NULL, " : "'$value', ";
					$val = substr($val, 0, -2);
					$query = "INSERT INTO `wot_technics` ($var) VALUES ($val)";
					mysqli_query($link, $query);
					$out .= 'Новый танк "' . $curTank['name'] . '" (' . $curTank['id'] . ')' . $br;
				};
			};
		};
	};
}

function checkWotmedals()
{
	global $link, $out, $br, $check, $info;

	if ($info != '') {
		$value = json_replace(json_encode($info['achievement_sections']));
		if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'medalSections'"))) {
			if ($f['value'] != $value) mysqli_query($link, "UPDATE `wot_data` SET `value` = '$value' WHERE `variable` = 'medalSections'");
		} else {
			mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('medalSections', '$value')");
		};

		$medalsUpdate = date('Y-m-d');
		$check = false;
		if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'medalUpdate'"))) {
			if ($f['value'] == $medalsUpdate) {
				$check = true;
				$out .= 'Проверка списка танковых медалей не требуется' . $br;
			} else mysqli_query($link, "UPDATE `wot_data` SET `value` = '$medalsUpdate' WHERE `variable` = 'medalUpdate'");
		} else mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('medalUpdate', '$medalsUpdate')");
	};
	if (!$check) {
		$out .= 'Проверка танковых медалей' . $br;
		$medals = getApiRequest('wot', '', 'medals');
		if ($medals != '') {
			foreach ($medals as $k => $medal) {
				$curMedal = array();
				$curMedal['name'] = $medal['name'];
				$curMedal['nameRu'] = $medal['name_i18n'];
				$curMedal['order'] = $medal['order'];
				$curMedal['type'] = $medal['type'];
				$curMedal['section'] = $medal['section'];
				$curMedal['image'] = $medal['image'];
				$curMedal['description'] = json_replace($medal['description']);
				$curMedal['condition'] = json_replace($medal['condition']);
				$curMedal['view'] = 1;
				$curMedal['state'] = 'new';

				$name = $curMedal['name'];
				if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medals` WHERE `name` = '$name'"))) {
					$id = $f['id'];
					$nameRu = $f['nameRu'];
					foreach ($f as $key => $value) {
						if ($key != 'id' && $key != 'name' && $key != 'View' && $key != 'state') {
							$curvalue = $curMedal[$key];
							if ($curvalue != $value) {
								if ($f['state'] == null || $f['state'] == 'chn') {
									if ($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_changes` WHERE `id` = '$id'"))) mysqli_query($link, "UPDATE `wot_medal_changes` SET `$key` = '$curvalue' WHERE `id` = '$id'");
									else mysqli_query($link, "INSERT INTO `wot_medal_changes` (`id`, `$key`) VALUES ('$id', '$curvalue')");
								} else mysqli_query($link, "UPDATE `wot_medals` SET `$key` = '$curvalue' WHERE `id` = '$id'");
								if ($key == 'nameRu') $out .= 'Медаль "' . $curMedal['nameRu'] . '" - изменение имени' . $br;
								if ($key == 'order') $out .= 'Медаль "' . $curMedal['nameRu'] . '" - изменение порядка сортировки' . $br;
								if ($key == 'type') $out .= 'Медаль "' . $curMedal['nameRu'] . '" - изменение типа' . $br;
								if ($key == 'section') $out .= 'Медаль "' . $curMedal['nameRu'] . '" - изменение секции' . $br;
								if ($key == 'image') $out .= 'Медаль "' . $curMedal['nameRu'] . '" - изменение изображения' . $br;
								if ($key == 'description') $out .= 'Медаль "' . $curMedal['nameRu'] . '" - изменение описания' . $br;
								if ($key == 'condition') $out .= 'Медаль "' . $curMedal['nameRu'] . '" - изменение условий получения' . $br;
							} else mysqli_query($link, "UPDATE `wot_medal_changes` SET `$key` = NULL WHERE `id` = '$id'");
						};
					};
					if ($medal['options'] != null) {
						if ($f['options'] == null || $f['options'] == '') {
							$options = array();
							foreach ($medal['options'] as $key => $option) {
								$optName = $option['name_i18n'];
								$optImage = $option['image'];
								$optid = 1;
								if ($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wot_medal_options`"))) $optid = $fo['id'] + 1;
								mysqli_query($link, "INSERT INTO `wot_medal_options` (`id`, `name`, `image`) VALUES ('$optid', '$optName', '$optImage')");
								if ($optImage == null) mysqli_query($link, "UPDATE `wot_medal_options` SET `image` = NULL WHERE `id` = '$optid'");
								$options[] = $optid;
							};
							$opt = json_encode($options);
							mysqli_query($link, "UPDATE `wot_medals` SET `options` = '$opt' WHERE `id` = '$id'");
						} else {
							$options = json_decode($f['options']);
							$optionsChange = array();
							$changeOpt = false;
							foreach ($medal['options'] as $key => $option) {
								$numOpt = $options[$key];
								$optnameRu = $option['name_i18n'];
								$optImage = $option['image'];
								$opt = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_options` WHERE `id` = '$numOpt'"));
								if ($opt['name'] != $optnameRu) {
									if ($f['state'] == null || $f['state'] == 'chn') $newOpt['name'] = $optnameRu;
									else mysqli_query($link, "UPDATE `wot_medal_options` SET `name` = '$optnameRu' WHERE `id` = '$numOpt'");
								} else $newOpt['name'] = null;
								if ($opt['image'] != $optImage) {
									if ($f['state'] == null || $f['state'] == 'chn') $newOpt['image'] = $optImage;
									else mysqli_query($link, "UPDATE `wot_medal_options` SET `image` = '$optImage' WHERE `id` = '$numOpt'");
								} else $newOpt['image'] = null;
								if (($newOpt['name'] == null && $newOpt['image'] == null) || ($f['state'] != null && $f['state'] != 'chn')) $optionsChange[$key] = null;
								else {
									$optid = 1;
									if ($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wot_medal_options`"))) $optid = $fo['id'] + 1;
									$optnameRu = $newOpt['name'];
									$optImage = $newOpt['image'];
									mysqli_query($link, "INSERT INTO `wot_medal_options` (`id`, `name`, `image`) VALUES ('$optid', '$optnameRu', '$optImage')");
									$optionsChange[$key] = $optid;
									$changeOpt = true;
								};
							};
							if ($changeOpt) {
								$setOpt = json_encode($optionsChange);
								if ($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_changes` WHERE `id` = '$id'"))) mysqli_query($link, "UPDATE `wot_medal_changes` SET `options` = '$setOpt' WHERE `id` = '$id'");
								else mysqli_query($link, "INSERT INTO `wot_medal_changes` (`id`, `options`) VALUES ('$id', '$setOpt')");
								$out .= 'Медаль "' . $nameRu . '" - изменение опций' . $br;
							};
						};
					};
					$changes = false;
					if ($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_changes` WHERE `id` = '$id'"))) {
						if ($fc['nameRU'] != null || $fc['order'] != null || $fc['type'] != null || $fc['section'] != null || $fc['image'] != null || $fc['description'] != null || $fc['condition'] != null || $fc['options'] != null) $changes = true;
					};
					if ($f['state'] == null || $f['state'] == 'chn') {
						if ($changes) mysqli_query($link, "UPDATE `wot_medals` SET `state` = 'chn' WHERE `id` = '$id'");
						else {
							mysqli_query($link, "UPDATE `wot_medals` SET `state` = NULL WHERE `id` = '$id'");
							mysqli_query($link, "DELETE FROM `wot_medal_changes` WHERE `id` = '$id'");
						};
					} else mysqli_query($link, "DELETE FROM `wot_medal_changes` WHERE `id` = '$id'");
				} else {
					$id = 1;
					if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wot_medals`"))) $id = $f['id'] + 1;
					$curMedal['id'] = $id;
					$var = $val = '';
					foreach ($curMedal as $key => $value) $var .= "`$key`, ";
					$var = substr($var, 0, -2);
					foreach ($curMedal as $key => $value) $val .= $value == null ? "NULL, " : "'$value', ";
					$val = substr($val, 0, -2);
					$query = "INSERT INTO `wot_medals` ($var) VALUES ($val)";
					mysqli_query($link, $query);
					if ($medal['options'] != null) {
						$options = array();
						foreach ($medal['options'] as $key => $option) {
							$optnameRu = $option['name_i18n'];
							$optImage = $option['image'];
							$optid = 1;
							if ($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wot_medal_options`"))) $optid = $fo['id'] + 1;
							mysqli_query($link, "INSERT INTO `wot_medal_options` (`id`, `name`, `image`) VALUES ('$optid', '$optnameRu', '$optImage')");
							if ($optImage == null) mysqli_query($link, "UPDATE `wot_medal_options` SET `image` = NULL WHERE `id` = '$optid'");
							$options[] = $optid;
						};
						$opt = json_encode($options);
						mysqli_query($link, "UPDATE `wot_medals` SET `options` = '$opt' WHERE `id` = '$id'");
					};
					$out .= 'Новая медаль "' . $curMedal['name'] . '" (' . $id . ')' . $br;
				};
			};
		};
	};
}

function checkWotExpectedTankvalues()
{
	global $link, $out, $br, $check;
	$etvUpdate = date('Y-m-d');
	$check = false;
	if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'etvUpdate'"))) {
		if ($f['value'] == $etvUpdate) {
			$check = true;
			$out .= 'Проверка танковых коэффициентов не требуется' . $br;
		} else mysqli_query($link, "UPDATE `wot_data` SET `value` = '$etvUpdate' WHERE `variable` = 'etvUpdate'");
	} else mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('etvUpdate', '$etvUpdate')");

	if (!$check) {
		$out .= 'Проверка танковых коэффициентов' . $br;
		$etv = getApiRequest('wot', '', 'wn8');
		if ($etv != '') {
			$value = json_encode($etv);
			if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'expectedTankValues'"))) {
				if ($f['value'] != $value) mysqli_query($link, "UPDATE `wot_data` SET `value` = '$value' WHERE `variable` = 'expectedTankValues'");
			} else mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('expectedTankValues', '$value')");
		};
	};
}

function checkWotEffectColor()
{
	global $link, $out, $br, $check;
	$colorUpdate = date('Y-m-d');
	$check = false;
	if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'colorUpdate'"))) {
		if ($f['value'] == $colorUpdate) {
			$check = true;
			$out .= 'Проверка танковых рейтингов не требуется' . $br;
		} else mysqli_query($link, "UPDATE `wot_data` SET `value` = '$colorUpdate' WHERE `variable` = 'colorUpdate'");
	} else mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('colorUpdate', '$colorUpdate')");

	if (!$check) {
		$out .= 'Проверка танковых рейтингов' . $br;

		$color = array();

		$color['bs']['dm']['color'] = '#FF0000';
		$color['bs']['d1']['color'] = '#FF4400';
		$color['bs']['d2']['color'] = '#FF8800';
		$color['bs']['d3']['color'] = '#FFCC00';
		$color['bs']['c3']['color'] = '#CCFF00';
		$color['bs']['c2']['color'] = '#99FF00';
		$color['bs']['c1']['color'] = '#66FF00';
		$color['bs']['cm']['color'] = '#33FF00';
		$color['bs']['cv']['color'] = '#00FF00';

		$color['bs']['dm']['description'] = 'Мастер-оленевод';
		$color['bs']['d1']['description'] = 'Оленевод 1 степени';
		$color['bs']['d2']['description'] = 'Оленевод 2 степени';
		$color['bs']['d3']['description'] = 'Оленевод 3 степени';
		$color['bs']['c3']['description'] = 'Танкист 3 степени';
		$color['bs']['c2']['description'] = 'Танкист 2 степени';
		$color['bs']['c1']['description'] = 'Танкист 1 степени';
		$color['bs']['cm']['description'] = 'Мастер-танкист';
		$color['bs']['cv']['description'] = 'Виртуоз';

		$color['wr'][0]['value'] = 0;
		$color['wr'][1]['value'] = 46.5;
		$color['wr'][2]['value'] = 48.5;
		$color['wr'][3]['value'] = 51.5;
		$color['wr'][4]['value'] = 56.5;
		$color['wr'][5]['value'] = 64.5;

		$color['wn'][0]['value'] = 0;
		$color['wn'][1]['value'] = 610;
		$color['wn'][2]['value'] = 850;
		$color['wn'][3]['value'] = 1145;
		$color['wn'][4]['value'] = 1475;
		$color['wn'][5]['value'] = 1775;

		$color['wn6'][0]['value'] = 0;
		$color['wn6'][1]['value'] = 410;
		$color['wn6'][2]['value'] = 795;
		$color['wn6'][3]['value'] = 1185;
		$color['wn6'][4]['value'] = 1585;
		$color['wn6'][5]['value'] = 1925;

		$color['wn8'][0]['value'] = 0;
		$color['wn8'][1]['value'] = 370;
		$color['wn8'][2]['value'] = 845;
		$color['wn8'][3]['value'] = 1395;
		$color['wn8'][4]['value'] = 2070;
		$color['wn8'][5]['value'] = 2715;

		$effColor = array(0 => '#FE0E00', 1 => '#FE7903', 2 => '#F8F400', 3 => '#60FF00', 4 => '#02C9B3', 5 => '#D042F3');
		$effname = array(0 => 'Очень плохой игрок', 1 => 'Плохой игрок', 2 => 'Средний игрок', 3 => 'Хороший игрок', 4 => 'Великолепный игрок', 5 => 'Уникум');

		$armor = getApiRequest('wot', '', 'armor');
		if ($armor != '' && json_encode($armor) != '[]') foreach ($armor as $name => $val) $color['bs'][$name]['value'] = $val;
		foreach ($color as $effect => $names) {
			if ($effect != 'bs') foreach ($color[$effect] as $num => $var) {
				$color[$effect][$num]['color'] = $effColor[$num];
				$color[$effect][$num]['description'] = $effname[$num];
			};
		};

		$value = json_replace(json_encode($color));
		if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_data` WHERE `variable` = 'effectColor'"))) {
			if ($f['value'] != $value) mysqli_query($link, "UPDATE `wot_data` SET `value` = '$value' WHERE `variable` = 'effectColor'");
		} else mysqli_query($link, "INSERT INTO `wot_data` (`variable`, `value`) VALUES ('effectColor', '$value')");
	};
}

