<?
function checkWoWS(){
	global $out, $br, $check, $info, $startTime;

	$out .= 'Корабли!'.$br;
	$info = getApiRequest('wows', '', 'info');
	
	if($check) checkWowsShips();
	if((time() - $startTime) > 50) $check = false;
	if($check) checkWowsmedals();
	if((time() - $startTime) > 50) $check = false;
	if($check) checkWowsStat();
	if((time() - $startTime) > 50) $check = false;
}

function checkWowsStat(){
	global $out, $br, $startTime, $check, $users;
	
	$list = '';
	$members = array();
	foreach($users as $memberid => $user) $list .= $memberid.',';
	$list = substr($list, 0, -1);
	$updates = getApiRequest('member', $list, 'wows_battle_time');
	if($updates != ''){
		foreach($updates as $member => $update){
			$time = time() - $startTime;
			if($time > 50) break;
			if($update['last_battle_time'] == 0) $update = null;
			if($update != null && (date('d') == '01' || date('Y-m-d H:i:s', $update['last_battle_time']) != $users[$member]['wowsUpdate'])) updateWowsMemberStat($member);
		};
	};
	if($check) $out .= 'Корабельная статистика без изменений'.$br;
}

function updateWowsMemberStat($member){
	global $link, $out, $br, $check, $users;
	
	$out .= 'Проверка пользователя '.$users[$member]['name'].$br;
	$update = false;
	$date = date('Y-m-d');
	$startMonth = date('Y-m-01');
	
	$medals = array();
	$rs = mysqli_query($link, "SELECT `id`, `name` FROM `wows_medals`");
	while($f = mysqli_fetch_assoc($rs)) $medals[$f['name']] = $f['id'];
	
	$updateMember = $users[$member]['wowsUpdate'];
	$memberUpdate = getApiRequest('member', $member, 'wows_battle_time');
	$newUpdate = date('Y-m-d H:i:s', $memberUpdate[$member]['last_battle_time']);
	if(date('d') == '01' && !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_stat` WHERE `date` = '$date' AND `id` = '$member'")))){
		$update = true;
		$out .= $users[$member]['name'].' - условие 1 (Начало месяца, а записи ещё нет)'.$br;
	};
	if(!$update && (!(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_stat` WHERE `date` >= '$startMonth' AND `id` = '$member' AND `type` = 'full'"))))){
		$update = true;
		$out .= $users[$member]['name'].' - условие 2 (Нет полной записи за текущий месяц)'.$br;
	};
	if(!$update && $newUpdate != null && ($updateMember == null || $newUpdate > $updateMember)){
		$update = true;
		$out .= $users[$member]['name'].' - условие 3 (Есть новые события)'.$br;
	};
	if($update){
		$check = false;
		$type = 'part';
		if(date('d') == '01' || !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' AND `type` = 'full'")))) $type = 'full';
		if($type == 'part'){
			$oldStat = array();
			if($rs = mysqli_query($link, "SELECT * FROM `wows_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' ORDER BY `date`")){
				while($f = mysqli_fetch_assoc($rs)){
					if($f['type'] == 'full'){
						$oldStat = $f;
						$oldShipStat = array();
						if($f['technics'] != null && $f['technics'] != 0) $oldShipStat = objectToArray(json_decode($f['technics']));
						$oldMedalStat = array();
						if($f['medals'] != null && $f['medals'] != 0) $oldMedalStat = objectToArray(json_decode($f['medals']));
					}else{
						foreach($f as $key => $value){
							if($value != null && $key != 'date' && $key != 'id' && $key != 'type' && $key != 'technics' && $key != 'medals') $oldStat[$key] += $value;
						};
						if($f['technics'] != null && $f['technics'] != 0){
							$ships = objectToArray(json_decode($f['technics']));
							foreach($ships as $shipid => $ship){
								if(isset($oldShipStat[$shipid])){
									$oldShipStat[$shipid][0] += $ship[0];
									$oldShipStat[$shipid][1] += $ship[1];
								}else $oldShipStat[$shipid] = $ship;
							};
						};
						if($f['medals'] != null && $f['medals'] != 0){
							$statmedals = objectToArray(json_decode($f['medals']));
							foreach($statmedals as $medal => $value){
								if(isset($oldMedalStat[$medal])) $oldMedalStat[$medal] += $value;
								else $oldMedalStat[$medal] = $value;
							};
						};
					};
				};
			}else $out .= $users[$member]['name'].' - нет записи предыдущих боёв'.$br;
		};
		$stat = getApiRequest('member', $member, 'wows');
		$shipStat = getApiRequest('member', $member, 'wows_ships');
		$medalStat = getApiRequest('member', $member, 'wows_medals');
		if($stat != '' && !$stat[$member]['hidden_profile'] && $stat[$member]['statistics']['pvp']['battles'] != 0){
			mysqli_query($link, "UPDATE `members` SET `wowsUpdate` = '$newUpdate' WHERE `id` = '$member'");
			
			$newStat = array();
			$newStat['date'] = $date;
			$newStat['id'] = $member;
			$newStat['type'] = $type;
			$newStat['battles'] = $stat[$member]['statistics']['pvp']['battles'];
			$newStat['wins'] = $stat[$member]['statistics']['pvp']['wins'];
			$newStat['losses'] = $stat[$member]['statistics']['pvp']['losses'];
			$newStat['survived'] = $stat[$member]['statistics']['pvp']['survived_battles'];
			$newStat['xp'] = $stat[$member]['statistics']['pvp']['xp'];
			$newStat['damage'] = $stat[$member]['statistics']['pvp']['damage_dealt'];
			$newStat['frags'] = $stat[$member]['statistics']['pvp']['frags'];
			$newStat['capture'] = $stat[$member]['statistics']['pvp']['capture_points'];
			$newStat['dropped'] = $stat[$member]['statistics']['pvp']['dropped_capture_points'];
			
			$newShipStat = array();
			if(count($shipStat[$member]) > 0) foreach($shipStat[$member] as $key => $ship){
				$shipid = (string)$ship['ship_id'];
				$newShipStat[$shipid] = array();
				$newShipStat[$shipid][0] = $ship['pvp']['battles'];
				$newShipStat[$shipid][1] = $ship['pvp']['wins'];
			};
			
			$newMedalStat = array();
			if($medalStat[$member]['battle'] != null) foreach($medalStat[$member]['battle'] as $key => $value){
				if(isset($medals[$key])) $newMedalStat[$medals[$key]] = $value;
			};
			
			if($type == 'part'){
				foreach($newStat as $key => $value){
					if($key != 'date' && $key != 'id' && $key != 'type'){
						$newStat[$key] -= $oldStat[$key];
						if($newStat[$key] == 0) $newStat[$key] = null;
					};
				};
				foreach($newShipStat as $shipid => $ship){
					if(isset($oldShipStat[$shipid]) && isset($newShipStat[$shipid][0]) && $newShipStat[$shipid][0] != 0){
						foreach($ship as $key => $value) $newShipStat[$shipid][$key] -= $oldShipStat[$shipid][$key];
						if($newShipStat[$shipid][0] == 0) unset($newShipStat[$shipid]);
					};
				};
				foreach($newMedalStat as $medal => $value){
					if(isset($oldMedalStat[$medal])){
						$newMedalStat[$medal] -= $oldMedalStat[$medal];
						if($newMedalStat[$medal] == 0) unset($newMedalStat[$medal]);
					};
				};
			};
			if(count($newShipStat) == 0) $newStat['technics'] = null;
			else $newStat['technics'] = json_encode($newShipStat);
			if(count($newMedalStat) == 0) $newStat['medals'] = null;
			else $newStat['medals'] = json_encode($newMedalStat);
			
			if($type == 'full' || $newStat['battles'] != 0){
				$check = false;
				if(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_stat` WHERE `date` = '$date' AND `id` = '$member'"))){
					$query = "UPDATE `wows_stat` SET ";
					foreach($newStat as $key => $value) if($key != 'date' && $key != 'id') $query .= "`$key` = ".($value == null ? "NULL, " : "'$value', ");
					$query = substr($query, 0, -2);
					$query .= "WHERE `date` = '".$newStat['date']."' AND `id` = '".$newStat['id']."'";
				}else{
					$query = "INSERT INTO `wows_stat` (";
					foreach($newStat as $key => $value) $query .= "`$key`, ";
					$query = substr($query, 0, -2);
					$query .= ") VALUES (";
					foreach($newStat as $key => $value) $query .= $value == null ? "NULL, " : "'$value', ";
					$query = substr($query, 0, -2);
					$query .= ")";
				};
				mysqli_query($link, $query);
				$out .= $users[$member]['name'].' - '.$date.': '.$newStat['battles'].' боёв'.$br;
			}else $out .= $users[$member]['name'].' - нет боёв'.$br;
		}else{
			mysqli_query($link, "UPDATE `members` SET `wowsUpdate` = '$newUpdate' WHERE `id` = '$member'");
			$out .= $users[$member]['name'].' - нет сатистики'.$br;
		};
	}else{
		mysqli_query($link, "UPDATE `members` SET `wowsUpdate` = '$newUpdate' WHERE `id` = '$member'");
		$out .= $users[$member]['name'].' - нет событий'.$br;
	};
}

function checkWowsShips(){
	global $link, $out, $br, $check, $info;
	
	$shipnations = array();
	if($info != ''){
		$value = json_replace(json_encode($info['ship_types']));
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_data` WHERE `variable` = 'technicTypes'"))){
			if($f['value'] != $value) mysqli_query($link, "UPDATE `wows_data` SET `value` = '$value' WHERE `variable` = 'technicTypes'");
		}else{
			mysqli_query($link, "INSERT INTO `wows_data` (`variable`, `value`) VALUES ('technicTypes', '$value')");
		};
		
		$value = json_replace(json_encode($info['ship_nations']));
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_data` WHERE `variable` = 'technicNations'"))){
			if($f['value'] != $value) mysqli_query($link, "UPDATE `wows_data` SET `value` = '$value' WHERE `variable` = 'technicNations'");
		}else{
			mysqli_query($link, "INSERT INTO `wows_data` (`variable`, `value`) VALUES ('technicNations', '$value')");
		};
		
		$shipsUpdate = date('Y-m-d');
		$check = false;
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_data` WHERE `variable` = 'technicUpdate'"))){
			if($f['value'] == $shipsUpdate){
				$check = true;
				$out .= 'Проверка списка кораблей не требуется'.$br;
			}else mysqli_query($link, "UPDATE `wows_data` SET `value` = '$shipsUpdate' WHERE `variable` = 'technicUpdate'");
		}else mysqli_query($link, "INSERT INTO `wows_data` (`variable`, `value`) VALUES ('technicUpdate', '$shipsUpdate')");
	};
	if(!$check){
		$out .= 'Проверка кораблей'.$br;
		$wows_ships = getApiRequest('wows', '', 'ships');
		if($wows_ships != ''){
			foreach($wows_ships as $shipid => $ship){
				$curShip = array();
				$curShip['id'] = $shipid;
				$curShip['name'] = $ship['name'];
				$curShip['shipID'] = $ship['ship_id_str'];
				$curShip['nation'] = $ship['nation'];
				$curShip['type'] = $ship['type'];
				$curShip['level'] = $ship['tier'];
				$curShip['isPrem'] = $ship['is_premium'] ? 1 : 0;
				$curShip['image'] = $ship['images']['small'];
				$curShip['description'] = $ship['description'];
				$curShip['state'] = 'new';
				
				if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_technics` WHERE `id` = '$shipid'"))){
					foreach($f as $key => $value){
						if($key != 'id' && $key != 'state'){
							$curvalue = $curShip[$key];
							if($curvalue != $value){
								if($f['state'] == null || $f['state'] == 'chn'){
									if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_technic_changes` WHERE `id` = '$shipid'"))) mysqli_query($link, "UPDATE `wows_technic_changes` SET `$key` = '$curvalue' WHERE `id` = '$shipid'");
									else mysqli_query($link, "INSERT INTO `wows_technic_changes` (`id`, `$key`) VALUES ('$shipid', '$curvalue')");
								}else mysqli_query($link, "UPDATE `wows_technics` SET `$key` = '$curvalue' WHERE `id` = '$shipid'");
								if($key == 'name') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение имени'.$br;
								if($key == 'shipID') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение идентификатора'.$br;
								if($key == 'nation') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение нации'.$br;
								if($key == 'type') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение типа'.$br;
								if($key == 'level') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение уровня'.$br;
								if($key == 'isPrem') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение премиумности'.$br;
								if($key == 'image') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение изображения'.$br;
								if($key == 'description') $out .= 'Корабль "'.$curShip['nameRu'].'" - изменение описания'.$br;
							}else mysqli_query($link, "UPDATE `wows_technic_changes` SET `$key` = NULL WHERE `id` = '$shipid'");
						};
					};
					$changes = false;
					if($rs = mysqli_query($link, "SELECT * FROM `wows_technic_changes` WHERE `id` = '$shipid'")){
						if($fc = mysqli_fetch_assoc($rs)){
							if($fc['name'] != null || $fc['shipID'] != null || $fc['nation'] != null || $fc['type'] != null || $fc['level'] != null || $fc['isPrem'] != null || $fc['image'] != null || $fc['description'] != null) $changes = true;
						};
					};
					if($f['state'] == null || $f['state'] == 'chn'){
						if($changes) mysqli_query($link, "UPDATE `wows_technics` SET `state` = 'chn' WHERE `id` = '$shipid'");
						else{
							mysqli_query($link, "UPDATE `wows_technics` SET `state` = NULL WHERE `id` = '$shipid'");
							mysqli_query($link, "DELETE FROM `wows_technic_changes` WHERE `id` = '$shipid'");
						};
					}else mysqli_query($link, "DELETE FROM `wows_technic_changes` WHERE `id` = '$shipid'");
				}else{
					$query = "INSERT INTO `wows_technics` (";
					foreach($curShip as $key => $value) $query .= "`$key`, ";
					$query = substr($query, 0, -2);
					$query .= ") VALUES (";
					foreach($curShip as $key => $value) $query .= $value === null ? "NULL, " : "'$value', ";
					$query = substr($query, 0, -2);
					$query .= ")";
					mysqli_query($link, $query);
					$out .= 'Новый корабль "'.$curShip['name'].'" ('.$curShip['id'].')'.$br;
				};
			};
		};
	};
}

function checkWowsmedals(){
	global $link, $out, $br, $check, $info;
	
	$sections = array();
	if($info != ''){
		$medalsUpdate = date('Y-m-d');
		$check = false;
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_data` WHERE `variable` = 'medalUpdate'"))){
			if($f['value'] == $medalsUpdate){
				$check = true;
				$out .= 'Проверка списка корабельных медалей не требуется'.$br;
			}else mysqli_query($link, "UPDATE `wows_data` SET `value` = '$medalsUpdate' WHERE `variable` = 'medalUpdate'");
		}else mysqli_query($link, "INSERT INTO `wows_data` (`variable`, `value`) VALUES ('medalUpdate', '$medalsUpdate')");
	};
	
	if(!$check){
		$out .= 'Проверка корабельных медалей'.$br;
		$medals = getApiRequest('wows', '', 'medals');
		if($medals != ''){
			foreach($medals['battle'] as $key => $medal){
				$curMedal = array();
				$curMedal['name'] = $medal['achievement_id'];
				$curMedal['nameRu'] = $medal['name'];
				$curMedal['type'] = $medal['type'];
				$curMedal['subType'] = $medal['sub_type'];
				$curMedal['reward'] = $medal['reward'] ? 1 : 0;
				$curMedal['countPerBattle'] = $medal['count_per_battle'];
				$curMedal['isProgress'] = $medal['is_progress'];
				$curMedal['maxProgress'] = $medal['max_progress'];
				$curMedal['image'] = $medal['image'];
				$curMedal['description'] = json_replace($medal['description']);
				$curMedal['view'] = 1;
				$curMedal['state'] = 'new';
				
				$name = $curMedal['name'];
				if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_medals` WHERE `name` = '$name'"))){
					$id = $f['id'];
					foreach($f as $key => $value){
						if($key != 'id' && $key != 'name' && $key != 'View' && $key != 'state'){
							$curvalue = $curMedal[$key];
							if($curvalue != $value){
								if($f['state'] == null || $f['state'] == 'chn'){
									if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wows_medal_changes` WHERE `id` = '$id'"))) mysqli_query($link, "UPDATE `wows_medal_changes` SET `$key` = '$curvalue' WHERE `id` = '$id'");
									else mysqli_query($link, "INSERT INTO `wows_medal_changes` (`id`, `$key`) VALUES ('$id', '$curvalue')");
								}else mysqli_query($link, "UPDATE `wows_medals` SET `$key` = '$curvalue' WHERE `id` = '$id'");
								if($key == 'nameRu') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение имени'.$br;
								if($key == 'type') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение типа'.$br;
								if($key == 'subType') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение подтипа'.$br;
								if($key == 'countPerBattle') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение количества за бой'.$br;
								if($key == 'isProgress') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение прогресса'.$br;
								if($key == 'maxProgress') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение максимального прогресса'.$br;
								if($key == 'image') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение изображения'.$br;
								if($key == 'description') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение описания'.$br;
							}else mysqli_query($link, "UPDATE `wows_medal_changes` SET `$key` = NULL WHERE `id` = '$id'");
						};
					};
					$changes = false;
					if($rs = mysqli_query($link, "SELECT * FROM `wows_medal_changes` WHERE `id` = '$id'")){
						if($fc = mysqli_fetch_assoc($rs)){
							if($fc['nameRU'] != null || $fc['type'] != null || $fc['subType'] != null || $fc['countPerBattle'] != null || $fc['isProgress'] != null || $fc['maxProgress'] != null || $fc['image'] != null || $fc['description'] != null) $changes = true;
						};
					};
					if($f['state'] == null || $f['state'] == 'chn'){
						if($changes) mysqli_query($link, "UPDATE `wows_medals` SET `state` = 'chn' WHERE `id` = '$id'");
						else{
							mysqli_query($link, "UPDATE `wows_medals` SET `state` = NULL WHERE `id` = '$id'");
							mysqli_query($link, "DELETE FROM `wows_medal_changes` WHERE `id` = '$id'");
						};
					}else mysqli_query($link, "DELETE FROM `wows_medal_changes` WHERE `id` = '$id'");
				}else{
					$id = 1;
					if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wows_medals`"))) $id = $f['id'] + 1;
					$curMedal['id'] = $id;
					$query = "INSERT INTO `wows_medals` (";
					foreach($curMedal as $key => $value) $query .= "`$key`, ";
					$query = substr($query, 0, -2);
					$query .= ") VALUES (";
					foreach($curMedal as $key => $value) $query .= $value == null ? "NULL, " : "'$value', ";
					$query = substr($query, 0, -2);
					$query .= ")";
					mysqli_query($link, $query);
					$out .= 'Новая медаль "'.$curMedal['nameRu'].'" ('.$id.')'.$br;
				};
			};
		};
	};
}
?>