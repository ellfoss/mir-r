<?
function checkWoWP(){
	global $out, $br, $check, $info, $startTime;
	
	$out .= 'Самолёты!'.$br;
	$info = getApiRequest('wowp', '', 'info');
	
	if($check) checkWowpTechnics();
	if((time() - $startTime) > 50) $check = false;
	if($check) checkWowpMedals();
	if((time() - $startTime) > 50) $check = false;
	if($check) checkWowpStat();
	if((time() - $startTime) > 50) $check = false;
}

function checkWowpStat(){
	global $out, $br, $startTime, $check, $users;
	
	$list = '';
	$members = array();
	foreach($users as $memberid => $user) $list .= $memberid.',';
	$list = substr($list, 0, -1);
	$updates = getApiRequest('member', $list, 'wowp_battle_time');
	if($updates != ''){
		foreach($updates as $member => $update){
			$time = time() - $startTime;
			if($time > 50) break;
			if($update['last_battle_time'] == 0) $update = null;
			if($update != null && (date('d') == '01' || date('Y-m-d H:i:s', $update['last_battle_time']) != $users[$member]['wowpUpdate'])) updateWowpMemberStat($member);
		};
	};
	if($check) $out .= 'Самолетная статистика без изменений'.$br;
}

function updateWowpMemberStat($member){
	global $link, $out, $br, $check, $users;
	
	$out .= 'Проверка пользователя '.$users[$member]['name'].$br;
	$update = false;
	$date = date('Y-m-d');
	$startMonth = date('Y-m-01');
	
	$medals = array();
	$rs = mysqli_query($link, "SELECT `id`, `name` FROM `wowp_medals`");
	while($f = mysqli_fetch_assoc($rs)) $medals[$f['name']] = $f['id'];
	
	$updateMember = $users[$member]['wowpUpdate'];
	$memberUpdate = getApiRequest('member', $member, 'wowp_battle_time');
	$newUpdate = date('Y-m-d H:i:s', $memberUpdate[$member]['last_battle_time']);
	if(date('d') == '01' && !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_stat` WHERE `date` = '$date' AND `id` = '$member'")))){
		$update = true;
		$out .= $users[$member]['name'].' - условие 1 (Начало месяца, а записи ещё нет)'.$br;
	};
	if(!$update && (!(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_stat` WHERE `date` >= '$startMonth' AND `id` = '$member' AND `type` = 'full'"))))){
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
		if(date('d') == '01' || !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' AND `type` = 'full'")))) $type = 'full';
		if($type == 'part'){
			$oldStat = array();
			if($rs = mysqli_query($link, "SELECT * FROM `wowp_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' ORDER BY `date`")){
				while($f = mysqli_fetch_assoc($rs)){
					if($f['type'] == 'full'){
						$oldStat = $f;
						$oldPlaneStat = array();
						if($f['technics'] != null) $oldPlaneStat = objectToArray(json_decode($f['technics']));
						$oldMedalStat = array();
						if($f['medals'] != null) $oldMedalStat = objectToArray(json_decode($f['medals']));
					}else{
						foreach($f as $key => $value){
							if($value != null && $key != 'date' && $key != 'id' && $key != 'type' && $key != 'technics' && $key != 'medals') $oldStat[$key] += $value;
						};
						if($f['technics'] != null){
							$planes = objectToArray(json_decode($f['technics']));
							foreach($planes as $planeid => $plane){
								if(isset($oldPlaneStat[$planeid])){
									$oldPlaneStat[$planeid][0] += $plane[0];
									$oldPlaneStat[$planeid][1] += $plane[1];
									$oldPlaneStat[$planeid][2] += $plane[2];
								}else $oldPlaneStat[$planeid] = $plane;
							};
						};
						if($f['medals'] != null){
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
		$stat = getApiRequest('member', $member, 'wowp');
		$planeStat = getApiRequest('member', $member, 'wowp_planes');
		if($stat != '' && $planeStat != ''){
			mysqli_query($link, "UPDATE `members` SET `wowpUpdate` = '$newUpdate' WHERE `id` = '$member'");
			
			$newStat = array();
			$newStat['date'] = $date;
			$newStat['id'] = $member;
			$newStat['type'] = $type;
			$newStat['battles'] = $stat[$member]['statistics']['battles'];
			$newStat['wins'] = $stat[$member]['statistics']['wins'];
			$newStat['losses'] = $stat[$member]['statistics']['losses'];
			$newStat['survived'] = $stat[$member]['statistics']['survived_battles'];
			$newStat['xp'] = $stat[$member]['statistics']['xp'];
			$newStat['frags'] = $stat[$member]['statistics']['frags']['total'];
			$newStat['damage'] = $stat[$member]['statistics']['damage_dealt']['total'];
			$newStat['hits'] = $stat[$member]['statistics']['hits']['total'];
			$newStat['shots'] = $stat[$member]['statistics']['shots']['total'];
			$newStat['objectsD'] = $stat[$member]['statistics']['ground_objects_destroyed']['total'];
			$newStat['structureD'] = $stat[$member]['statistics']['structure_damage']['total'];
			$newStat['basesD'] = $stat[$member]['statistics']['team_objects_destroyed']['total'];
			$newStat['turretsD'] = $stat[$member]['statistics']['turrets_destroyed']['total'];
			$newStat['assists'] = $stat[$member]['statistics']['assists'];
			
			$newPlaneStat = array();
			if(count($planeStat[$member]) > 0) foreach($planeStat[$member] as $key => $plane){
				$planeid = $plane['plane_id'];
				$newPlaneStat[$planeid] = array();
				$newPlaneStat[$planeid][0] = $plane['battles'];
				$newPlaneStat[$planeid][1] = $plane['wins'];
			};
			
			$newMedalStat = array();
			if($stat[$member]['achievements'] != null) foreach($stat[$member]['achievements'] as $key => $medal){
				if(isset($medals[$medal['name']])) $newMedalStat[$medals[$medal['name']]] = $medal['number'];
			};
			
			if($type == 'part'){
				foreach($newStat as $key => $value){
					if($key != 'date' && $key != 'id' && $key != 'type'){
						$newStat[$key] -= $oldStat[$key];
						if($newStat[$key] == 0) $newStat[$key] = null;
					};
				};
				foreach($newPlaneStat as $planeid => $plane){
					if(isset($oldPlaneStat[$planeid]) && isset($newPlaneStat[$planeid][0]) && $newPlaneStat[$planeid][0] != 0){
						foreach($plane as $key => $value) $newPlaneStat[$planeid][$key] -= $oldPlaneStat[$planeid][$key];
						if($newPlaneStat[$planeid][0] == 0) unset($newPlaneStat[$planeid]);
					};
				};
				foreach($newMedalStat as $medal => $value){
					if(isset($oldMedalStat[$medal])){
						$newMedalStat[$medal] -= $oldMedalStat[$medal];
						if($newMedalStat[$medal] == 0) unset($newMedalStat[$medal]);
					};
				};
			};
			if(count($newPlaneStat) == 0) $newStat['technics'] = null;
			else $newStat['technics'] = json_encode($newPlaneStat);
			if(count($newMedalStat) == 0) $newStat['medals'] = null;
			else $newStat['medals'] = json_encode($newMedalStat);
			
			if($type == 'full' || $newStat['battles'] != 0){
				$check = false;
				if(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_stat` WHERE `date` = '$date' AND `id` = '$member'"))){
					$query = "UPDATE `wowp_stat` SET ";
					foreach($newStat as $key => $value) if($key != 'date' && $key != 'id') $query .= "`$key` = ".($value == null ? "NULL, " : "'$value', ");
					$query = substr($query, 0, -2);
					$query .= "WHERE `date` = '".$newStat['date']."' AND `id` = '".$newStat['id']."'";
				}else{
					$query = "INSERT INTO `wowp_stat` (";
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
		};
	}else{
		mysqli_query($link, "UPDATE `members` SET `wowpUpdate` = '$newUpdate' WHERE `id` = '$member'");
		$out .= $users[$member]['name'].' - нет событий'.$br;
	};
}

function checkWowpTechnics(){
	global $link, $out, $br, $check, $info;
	
	$planeNations = array();
	if($info != ''){
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_data` WHERE `variable` = 'technicNations'"))){
			if($f['value'] != null) $planeNations = objectToArray(json_decode($f['value']));
		}else{
			mysqli_query($link, "INSERT INTO `wowp_data` (`variable`, `value`) VALUES ('technicNations', NULL)");
		};
		
		$planesUpdate = date('Y-m-d');
		$check = false;
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_data` WHERE `variable` = 'technicUpdate'"))){
			if($f['value'] == $planesUpdate){
				$check = true;
				$out .= 'Проверка списка самолетов не требуется'.$br;
			}else mysqli_query($link, "UPDATE `wowp_data` SET `value` = '$planesUpdate' WHERE `variable` = 'technicUpdate'");
		}else mysqli_query($link, "INSERT INTO `wowp_data` (`variable`, `value`) VALUES ('technicUpdate', '$planesUpdate')");
	};
	if(!$check){
		$out .= 'Проверка самолетов'.$br;
		$wowp_planes = getApiRequest('wowp', '', 'planes');
		if($wowp_planes != ''){
			foreach($wowp_planes as $planeid => $plane){
				$curPlane['id'] = $planeid;
				$curPlane['name'] = $plane['name'];
				$curPlane['nameRu'] = $plane['name_i18n'];
				$curPlane['image'] = $plane['images']['small'];
				$curPlane['level'] = $plane['level'];
				$curPlane['type'] = $plane['type'];
				$curPlane['nation'] = $plane['nation'];
				$curPlane['isPrem'] = $plane['is_premium'];
				$planeNations = checkWowpNations($planeNations, $plane['nation'], $plane['nation_i18n']);
				
				if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_technics` WHERE `id` = '$planeid'"))){
					foreach($f as $key => $value){
						if($key != 'id' && $key != 'state'){
							$curvalue = $curPlane[$key];
							if($curvalue != $value){
								if($f['state'] == null || $f['state'] == 'chn'){
									if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_technic_changes` WHERE `id` = '$planeid'"))) mysqli_query($link, "UPDATE `wowp_technic_changes` SET `$key` = '$curvalue' WHERE `id` = '$planeid'");
									else mysqli_query($link, "INSERT INTO `wowp_technic_changes` (`id`, `$key`) VALUES ('$planeid', '$curvalue')");
								}else mysqli_query($link, "UPDATE `wowp_technics` SET `$key` = '$curvalue' WHERE `id` = '$planeid'");
								if($key == 'name') $out .= 'Самолёт "'.$curPlane['name'].'" - изменение имени'.$br;
								if($key == 'nameRu') $out .= 'Самолёт "'.$curPlane['name'].'" - изменение русского имени'.$br;
								if($key == 'image') $out .= 'Самолёт "'.$curPlane['name'].'" - изменение изображения'.$br;
								if($key == 'level') $out .= 'Самолёт "'.$curPlane['name'].'" - изменение уровня'.$br;
								if($key == 'type') $out .= 'Самолёт "'.$curPlane['name'].'" - изменение типа'.$br;
								if($key == 'nation') $out .= 'Самолёт "'.$curPlane['name'].'" - изменение нации'.$br;
								if($key == 'isPrem') $out .= 'Самолёт "'.$curPlane['name'].'" - изменение премиумности'.$br;
							}else mysqli_query($link, "UPDATE `wowp_technic_changes` SET `$key` = NULL WHERE `id` = '$planeid'");
						};
					};
					$changes = false;
					if($rs = mysqli_query($link, "SELECT * FROM `wowp_technic_changes` WHERE `id` = '$planeid'")){
						if($fc = mysqli_fetch_assoc($rs)){
							if($fc['name'] != null || $fc['shortName'] != null || $fc['image'] != null || $fc['level'] != null || $fc['type'] != null || $fc['nation'] != null || $fc['isPrem'] != null) $changes = true;
						};
					};
					if($f['state'] == null || $f['state'] == 'chn'){
						if($changes) mysqli_query($link, "UPDATE `wowp_technics` SET `state` = 'chn' WHERE `id` = '$planeid'");
						else{
							mysqli_query($link, "UPDATE `wowp_technics` SET `state` = NULL WHERE `id` = '$planeid'");
							mysqli_query($link, "DELETE FROM `wowp_technic_changes` WHERE `id` = '$planeid'");
						};
					}else mysqli_query($link, "DELETE FROM `wowp_technic_changes` WHERE `id` = '$planeid'");
				}else{
					$query = "INSERT INTO `wowp_technics` (";
					foreach($curPlane as $key => $value) $query .= "`$key`, ";
					$query = substr($query, 0, -2);
					$query .= ") VALUES (";
					foreach($curPlane as $key => $value) $query .= $value === null ? "NULL, " : "'$value', ";
					$query = substr($query, 0, -2);
					$query .= ")";
					mysqli_query($link, $query);
					$out .= 'Новый самолёт "'.$curPlane['nameRu'].'" ('.$curPlane['id'].')'.$br;
				};
			};
		};
	};
}

function checkWowpNations($nations, $nation, $nationRu){
	global $link;

	if(!(isset($nations[$nation])) || $nations[$nation] != $nationRu){
		$nations[$nation] = $nationRu;
		$value = json_replace(json_encode($nations));
		mysqli_query($link, "UPDATE `wowp_data` SET `value` = '$value' WHERE `variable` = 'technicNations'");
	};
	return $nations;
}

function checkWowpmedals(){
	global $link, $out, $br, $check, $info;
	
	$sections = array();
	if($info != ''){
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_data` WHERE `variable` = 'medalSections'"))){
			if($f['value'] != NULL) $sections = objectToArray(json_decode($f['value']));
		}else{
			mysqli_query($link, "INSERT INTO `wowp_data` (`variable`, `value`) VALUES ('medalSections', NULL)");
		};
		
		$medalsUpdate = date('Y-m-d');
		$check = false;
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_data` WHERE `variable` = 'medalUpdate'"))){
			if($f['value'] == $medalsUpdate){
				$check = true;
				$out .= 'Проверка списка самолетных медалей не требуется'.$br;
			}else mysqli_query($link, "UPDATE `wowp_data` SET `value` = '$medalsUpdate' WHERE `variable` = 'medalUpdate'");
		}else mysqli_query($link, "INSERT INTO `wowp_data` (`variable`, `value`) VALUES ('medalUpdate', '$medalsUpdate')");
	};
	
	if(!$check){
		$out .= 'Проверка самолётных медалей'.$br;
		$medals = getApiRequest('wowp', '', 'medals');
		if($medals != ''){
			foreach($medals as $key => $medal){
				$curMedal = array();
				$curMedal['name'] = $medal['name'];
				$curMedal['nameRu'] = $medal['name_i18n'];
				$curMedal['order'] = $medal['order'];
				$curMedal['section'] = $medal['section'];
				$curMedal['image'] = $medal['image'];
				$curMedal['description'] = json_replace($medal['description']);
				$curMedal['view'] = 1;
				$curMedal['state'] = 'new';
				$sections = checkWowpMedalSections($sections, $medal['section'], $medal['section_i18n']);
				
				$name = $curMedal['name'];
				if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_medals` WHERE `name` = '$name'"))){
					$id = $f['id'];
					foreach($f as $key => $value){
						if($key != 'id' && $key != 'name' && $key != 'view' && $key != 'state'){
							$curvalue = $curMedal[$key];
							if($curvalue != $value){
								if($f['state'] == null || $f['state'] == 'chn'){
									if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wowp_medal_changes` WHERE `id` = '$id'"))) mysqli_query($link, "UPDATE `wowp_medal_changes` SET `$key` = '$curvalue' WHERE `id` = '$id'");
									else mysqli_query($link, "INSERT INTO `wowp_medal_changes` (`id`, `$key`) VALUES ('$id', '$curvalue')");
								}else mysqli_query($link, "UPDATE `wowp_medals` SET `$key` = '$curvalue' WHERE `id` = '$id'");
								if($key == 'nameRu') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение имени'.$br;
								if($key == 'order') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение порядка сортировки'.$br;
								if($key == 'section') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение секции'.$br;
								if($key == 'image') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение изображения'.$br;
								if($key == 'description') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение описания'.$br;
							}else mysqli_query($link, "UPDATE `wowp_medal_changes` SET `$key` = NULL WHERE `id` = '$id'");
						};
					};
					$changes = false;
					if($rs = mysqli_query($link, "SELECT * FROM `wowp_medal_changes` WHERE `id` = '$id'")){
						if($fc = mysqli_fetch_assoc($rs)){
							if($fc['nameRU'] != null || $fc['type'] != null || $fc['subType'] != null || $fc['countPerBattle'] != null || $fc['isProgress'] != null || $fc['maxProgress'] != null || $fc['image'] != null || $fc['description'] != null) $changes = true;
						};
					};
					if($f['state'] == null || $f['state'] == 'chn'){
						if($changes) mysqli_query($link, "UPDATE `wowp_medals` SET `state` = 'chn' WHERE `id` = '$id'");
						else{
							mysqli_query($link, "UPDATE `wowp_medals` SET `state` = NULL WHERE `id` = '$id'");
							mysqli_query($link, "DELETE FROM `wowp_medal_changes` WHERE `id` = '$id'");
						};
					}else mysqli_query($link, "DELETE FROM `wowp_medal_changes` WHERE `id` = '$id'");
				}else{
					$id = 1;
					if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wowp_medals`"))) $id = $f['id'] + 1;
					$curMedal['id'] = $id;
					$query = "INSERT INTO `wowp_medals` (";
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

function checkWowpMedalSections($sections, $section, $sectionRu){
	global $link;

	if(!(isset($sections[$section])) || $sections[$section] != $sectionRu){
		$sections[$section] = $sectionRu;
		$value = json_replace(json_encode($sections));
		mysqli_query($link, "UPDATE `wowp_data` SET `value` = '$value' WHERE `variable` = 'medalSections'");
	};
	return $sections;
}
?>