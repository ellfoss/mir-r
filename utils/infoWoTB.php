<?
function checkWoTB(){
	global $out, $br, $check, $info, $startTime;
	
	$out .= 'Танчики!'.$br;
	$info = getApiRequest('wotb', '', 'info');
	
	if($check) checkWotbTanks();
	if((time() - $startTime) > 50) $check = false;
	if($check) checkWotbmedals();
	if((time() - $startTime) > 50) $check = false;
	if($check) checkWotbStat();
	if((time() - $startTime) > 50) $check = false;
}

function checkWotbStat(){
	global $out, $br, $startTime, $check, $users;
	
	$list = '';
	$members = array();
	foreach($users as $memberid => $user) $list .= $memberid.',';
	$list = substr($list, 0, -1);
	$updates = getApiRequest('member', $list, 'wotb_battle_time');
	if($updates != ''){
		foreach($updates as $member => $update){
			$time = time() - $startTime;
			if($time > 50) break;
			if($update['last_battle_time'] == 0) $update = null;
			if($update != null && (date('d') == '01' || date('Y-m-d H:i:s', $update['last_battle_time']) != $users[$member]['wotbUpdate'])) updateWotbMemberStat($member);
		};
	};
	if($check) $out .= 'Танчиковая статистика без изменений'.$br;
}

function updateWotbMemberStat($member){
	global $link, $out, $br, $check, $users;
	
	$out .= 'Проверка пользователя '.$users[$member]['name'].$br;
	$update = false;
	$date = date('Y-m-d');
	$startMonth = date('Y-m-01');
	
	$medals = array();
	$rs = mysqli_query($link, "SELECT `id`, `name` FROM `wotb_medals`");
	while($f = mysqli_fetch_assoc($rs)) $medals[$f['name']] = $f['id'];
	
	$updateMember = $users[$member]['wotbUpdate'];
	$memberUpdate = getApiRequest('member', $member, 'wotb_battle_time');
	$newUpdate = date('Y-m-d H:i:s', $memberUpdate[$member]['last_battle_time']);
	if(date('d') == '01' && !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_stat` WHERE `date` = '$date' AND `id` = '$member'")))){
		$update = true;
		$out .= $users[$member]['name'].' - условие 1 (Начало месяца, а записи ещё нет)'.$br;
	};
	if(!$update && (!(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_stat` WHERE `date` >= '$startMonth' AND `id` = '$member' AND `type` = 'full'"))))){
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
		if(date('d') == '01' || !(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' AND `type` = 'full'")))) $type = 'full';
		if($type == 'part'){
			$oldStat = array();
			if($rs = mysqli_query($link, "SELECT * FROM `wotb_stat` WHERE `date` >= '$startMonth' AND `date` < '$date' AND `id` = '$member' ORDER BY `date`")){
				while($f = mysqli_fetch_assoc($rs)){
					if($f['type'] == 'full'){
						$oldStat = $f;
						$oldTankStat = array();
						if($f['technicks'] != null) $oldTankStat = objectToArray(json_decode($f['technicks']));
						$oldMedalStat = array();
						if($f['medals'] != null) $oldMedalStat = objectToArray(json_decode($f['medals']));
					}
					else{
						foreach($f as $key => $value){
							if($value != null && $key != 'date' && $key != 'id' && $key != 'type' && $key != 'technicks' && $key != 'medals') $oldStat[$key] += $value;
						};
						if($f['technicks'] != null){
							$tanks = objectToArray(json_decode($f['technicks']));
							foreach($tanks as $tankid => $tank){
								if(isset($oldTankStat[$tankid])){
									$oldTankStat[$tankid][0] += $tank[0];
									$oldTankStat[$tankid][1] += $tank[1];
									$oldTankStat[$tankid][2] += $tank[2];
								}else $oldTankStat[$tankid] = $tank;
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
		$stat = getApiRequest('member', $member, 'wotb');
		$tankStat = getApiRequest('member', $member, 'wotb_tanks');
		$medalStat = getApiRequest('member', $member, 'wotb_medals');
		if($stat != '' && $tankStat != '' && $medalStat != ''){
			mysqli_query($link, "UPDATE `members` SET `wotbUpdate` = '$newUpdate' WHERE `id` = '$member'");
			
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
			if($tankStat[$member] != null) foreach($tankStat[$member] as $key => $tank){
				$tankid = $tank['tank_id'];
				$newTankStat[$tankid] = array();
				$newTankStat[$tankid][0] = $tank['all']['battles'];
				$newTankStat[$tankid][1] = $tank['all']['wins'];
				$newTankStat[$tankid][2] = $tank['mark_of_mastery'];
			};
			
			$newMedalStat = array();
			foreach($medalStat[$member]['achievements'] as $medal => $value){
				if(isset($medals[$medal])) $newMedalStat[$medals[$medal]] = $value;
			};
			
			if($type == 'part'){
				foreach($newStat as $key => $value){
					if($key != 'date' && $key != 'id' && $key != 'type'){
						$newStat[$key] -= $oldStat[$key];
						if($newStat[$key] == 0) $newStat[$key] = null;
					};
				};
				foreach($newTankStat as $tankid => $tank){
					if(isset($oldTankStat[$tankid]) && isset($newTankStat[$tankid][0]) && $newTankStat[$tankid][0] != 0){
						foreach($tank as $key => $value) $newTankStat[$tankid][$key] -= $oldTankStat[$tankid][$key];
						if($newTankStat[$tankid][0] == 0) unset($newTankStat[$tankid]);
					};
				};
				foreach($newMedalStat as $medal => $value){
					if(isset($oldMedalStat[$medal])){
						$newMedalStat[$medal] -= $oldMedalStat[$medal];
						if($newMedalStat[$medal] == 0) unset($newMedalStat[$medal]);
					};
				};
			};
			if(count($newTankStat) == 0) $newStat['technics'] = null;
			else $newStat['technics'] = json_encode($newTankStat);
			if(count($newMedalStat) == 0) $newStat['medals'] = null;
			else $newStat['medals'] = json_encode($newMedalStat);
			
			if($type == 'full' || $newStat['battles'] != 0){
				$check = false;
				if(mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_stat` WHERE `date` = '$date' AND `id` = '$member'"))){
					$val = '';
					foreach($newStat as $key => $value) if($key != 'date' && $key != 'id') $val .= "`$key` = ".($value == null ? "NULL, " : "'$value', ");
					$val = substr($val, 0, -2);
					$query = "UPDATE `wotb_stat` SET $val WHERE `date` = '".$newStat['date']."' AND `id` = '".$newStat['id']."'";
				}else{
					$var = $val = '';
					foreach($newStat as $key => $value) $var .= "`$key`, ";
					$var = substr($var, 0, -2);
					foreach($newStat as $key => $value) $val .= $value == null ? "NULL, " : "'$value', ";
					$val = substr($val, 0, -2);
					$query = "INSERT INTO `wotb_stat` ($var) VALUES ($val)";
				};
				mysqli_query($link, $query);
				$out .= $users[$member]['name'].' - '.$date.': '.$newStat['battles'].' боёв'.$br;
			}else $out .= $users[$member]['name'].' - нет боёв'.$br;
		};
	}else{
		mysqli_query($link, "UPDATE `members` SET `wotbUpdate` = '$newUpdate' WHERE `id` = '$member'");
		$out .= $users[$member]['name'].' - нет событий'.$br;
	};
}

function checkWotbTanks(){
	global $link, $out, $br, $check, $info;
	
	if($info != ''){
		$value = json_replace(json_encode($info['vehicle_types']));
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_data` WHERE `variable` = 'technicTypes'"))){
			if($f['value'] != $value) mysqli_query($link, "UPDATE `wotb_data` SET `value` = '$value' WHERE `variable` = 'technicTypes'");
		}else{
			mysqli_query($link, "INSERT INTO `wotb_data` (`variable`, `value`) VALUES ('technicTypes', '$value')");
		};
		
		$value = json_replace(json_encode($info['vehicle_nations']));
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_data` WHERE `variable` = 'technicNations'"))){
			if($f['value'] != $value) mysqli_query($link, "UPDATE `wotb_data` SET `value` = '$value' WHERE `variable` = 'technicNations'");
		}else{
			mysqli_query($link, "INSERT INTO `wotb_data` (`variable`, `value`) VALUES ('technicNations', '$value')");
		};
		
		$tanksUpdate = date('Y-m-d H:i:s', $info['tanks_updated_at']);
		$check = false;
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_data` WHERE `variable` = 'technicUpdate'"))){
			if($f['value'] == $tanksUpdate){
				$check = true;
				$out .= 'Проверка списка танчиков не требуется'.$br;
			}else mysqli_query($link, "UPDATE `wotb_data` SET `value` = '$tanksUpdate' WHERE `variable` = 'technicUpdate'");
		}else mysqli_query($link, "INSERT INTO `wotb_data` (`variable`, `value`) VALUES ('technicUpdate', '$tanksUpdate')");
	};
	if(!$check){
		$out .= 'Проверка танчиков'.$br;
		$wotb_tanks = getApiRequest('wotb', '', 'tanks');
		if($wotb_tanks != ''){
			foreach($wotb_tanks as $tankid => $tank){
				$curTank = array();
				$curTank['id'] = $tankid;
				$curTank['name'] = $tank['name'];
				$curTank['image'] = $tank['images']['preview'];
				$curTank['level'] = $tank['tier'];
				$curTank['type'] = $tank['type'];
				$curTank['nation'] = $tank['nation'];
				$curTank['isPrem'] = $tank['is_premium'] ? 1 : 0;
				
				if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_technics` WHERE `id` = '$tankid'"))){
					foreach($f as $key => $value){
						if($key != 'id' && $key != 'state'){
							$curvalue = $curTank[$key];
							if($curvalue != $value){
								if($f['state'] == null || $f['state'] == 'chn'){
									if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_technic_changes` WHERE `id` = '$tankid'"))) mysqli_query($link, "UPDATE `wotb_technic_changes` SET `$key` = '$curvalue' WHERE `id` = '$tankid'");
									else mysqli_query($link, "INSERT INTO `wotb_technic_changes` (`id`, `$key`) VALUES ('$tankid', '$curvalue')");
								}else mysqli_query($link, "UPDATE `wotb_technics` SET `$key` = '$curvalue' WHERE `id` = '$tankid'");
								if($key == 'name') $out .= 'Танчик "'.$curTank['name'].'" - изменение имени'.$br;
								if($key == 'shortName') $out .= 'Танчик "'.$curTank['name'].'" - изменение короткого имени'.$br;
								if($key == 'image') $out .= 'Танчик "'.$curTank['name'].'" - изменение изображения'.$br;
								if($key == 'level') $out .= 'Танчик "'.$curTank['name'].'" - изменение уровня'.$br;
								if($key == 'type') $out .= 'Танчик "'.$curTank['name'].'" - изменение типа'.$br;
								if($key == 'nation') $out .= 'Танчик "'.$curTank['name'].'" - изменение нации'.$br;
								if($key == 'isPrem') $out .= 'Танчик "'.$curTank['name'].'" - изменение премиумности'.$br;
							}else mysqli_query($link, "UPDATE `wotb_technic_changes` SET `$key` = NULL WHERE `id` = '$tankid'");
						};
					};
					$changes = false;
					if($rs = mysqli_query($link, "SELECT * FROM `wotb_technic_changes` WHERE `id` = '$tankid'")){
						if($fc = mysqli_fetch_assoc($rs)){
							if($fc['name'] != null || $fc['shortName'] != null || $fc['image'] != null || $fc['level'] != null || $fc['type'] != null || $fc['nation'] != null || $fc['isPrem'] != null) $changes = true;
						};
					};
					if($f['state'] == null || $f['state'] == 'chn'){
						if($changes) mysqli_query($link, "UPDATE `wotb_technics` SET `state` = 'chn' WHERE `id` = '$tankid'");
						else{
							mysqli_query($link, "UPDATE `wotb_technics` SET `state` = NULL WHERE `id` = '$tankid'");
							mysqli_query($link, "DELETE FROM `wotb_technic_changes` WHERE `id` = '$tankid'");
						};
					}else mysqli_query($link, "DELETE FROM `wotb_technic_changes` WHERE `id` = '$tankid'");
				}else{
					$var = $val = '';
					foreach($curTank as $key => $value) $var .= "`$key`, ";
					$var = substr($var, 0, -2);
					foreach($curTank as $key => $value) $val .= $value === null ? "NULL, " : "'$value', ";
					$val = substr($val, 0, -2);
					$query = "INSERT INTO `wotb_technics` ($var) VALUES ($val)";
					mysqli_query($link, $query);
					$out .= 'Новый танчик "'.$curTank['name'].'" ('.$curTank['id'].')'.$br;
				};
			};
		};
	};
}

function checkWotbmedals(){
	global $link, $out, $br, $check, $info;
	
	if($info != ''){
		$value = json_replace(json_encode($info['achievement_sections']));
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_data` WHERE `variable` = 'medalSections'"))){
			if($f['value'] != $value) mysqli_query($link, "UPDATE `wotb_data` SET `value` = '$value' WHERE `variable` = 'medalSections'");
		}else{
			mysqli_query($link, "INSERT INTO `wotb_data` (`variable`, `value`) VALUES ('medalSections', '$value')");
		};
		
		$medalsUpdate = date('Y-m-d');
		$check = false;
		if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_data` WHERE `variable` = 'medalUpdate'"))){
			if($f['value'] == $medalsUpdate){
				$check = true;
				$out .= 'Проверка списка танчиковых медалей не требуется'.$br;
			}else mysqli_query($link, "UPDATE `wotb_data` SET `value` = '$medalsUpdate' WHERE `variable` = 'medalUpdate'");
		}else mysqli_query($link, "INSERT INTO `wotb_data` (`variable`, `value`) VALUES ('medalUpdate', '$medalsUpdate')");
	};
	if(!$check){
		$out .= 'Проверка танчиковых медалей'.$br;
		$medals = getApiRequest('wotb', '', 'medals');
		if($medals != ''){
			foreach($medals as $key => $medal){
				$curMedal = array();
				$curMedal['name'] = $key;
				$curMedal['nameRu'] = $medal['name'];
				$curMedal['order'] = $medal['order'];
				$curMedal['section'] = $medal['section'];
				$curMedal['image'] = $medal['image'];
				$curMedal['description'] = json_replace($medal['description']);
				$curMedal['condition'] = json_replace($medal['condition']);
				$curMedal['view'] = 1;
				$curMedal['state'] = 'new';
				
				if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_medals` WHERE `name` = '$key'"))){
					$idMedal = $f['id'];
					$nameRu = $f['nameRu'];
					foreach($f as $key => $value){
						if($key != 'id' && $key != 'name' && $key != 'View' && $key != 'state'){
							$curvalue = $curMedal[$key];
							if($curvalue != $value){
								if($f['state'] == null || $f['state'] == 'chn'){
									if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_medal_changes` WHERE `id` = '$idMedal'"))) mysqli_query($link, "UPDATE `wotb_medal_changes` SET `$key` = '$curvalue' WHERE `id` = '$idMedal'");
									else mysqli_query($link, "INSERT INTO `wotb_medal_changes` (`id`, `$key`) VALUES ('$idMedal', '$curvalue')");
								}else mysqli_query($link, "UPDATE `wotb_medals` SET `$key` = '$curvalue' WHERE `id` = '$idMedal'");
								if($key == 'nameRu') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение имени'.$br;
								if($key == 'order') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение порядка сортировки'.$br;
								if($key == 'type') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение типа'.$br;
								if($key == 'section') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение секции'.$br;
								if($key == 'image') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение изображения'.$br;
								if($key == 'description') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение описания'.$br;
								if($key == 'condition') $out .= 'Медаль "'.$curMedal['nameRu'].'" - изменение условий получения'.$br;
							}else mysqli_query($link, "UPDATE `wotb_medal_changes` SET `$key` = NULL WHERE `id` = '$idMedal'");
						};
					};
					if($medal['options'] != null){
						if($f['options'] == null || $f['options'] == ''){
							$options = array();
							foreach($medal['options'] as $key => $option){
								$optName = $option['name'];
								$optImage = $option['image'];
								$optid = 1;
								if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wotb_medal_options`"))) $optid = $fo['id'] + 1;
								mysqli_query($link, "INSERT INTO `wotb_medal_options` (`id`, `name`, `image`) VALUES ('$optid', '$optName', '$optImage')");
								if($optImage == null) mysqli_query($link, "UPDATE `wotb_medal_options` SET `image` = NULL WHERE `id` = '$optid'");
								$options[] = $optid;
							};
							$opt = json_encode($options);
							mysqli_query($link, "UPDATE `wotb_medals` SET `options` = '$opt' WHERE `id` = '$idMedal'");
						}else{
							$options = json_decode($f['options']);
							$optionsChange = array();
							$changeOpt = false;
							foreach($medal['options'] as $key => $option){
								$numOpt = $options[$key];
								$optname = $option['name'];
								$optimage = $option['image'];
								if($opt = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_medal_options` WHERE `id` = '$numOpt'"))){
									if($opt['name'] != $optname){
										if($f['state'] == null || $f['state'] == 'chn') $newOpt['name'] = $optname;
										else mysqli_query($link, "UPDATE `wotb_medal_options` SET `name` = '$optname' WHERE `id` = '$numOpt'");
									}else $newOpt['name'] = null;
									if($opt['image'] != $optimage){
										if($f['state'] == null || $f['state'] == 'chn') $newOpt['image'] = $optimage;
										else mysqli_query($link, "UPDATE `wotb_medal_options` SET `image` = '$optimage' WHERE `id` = '$numOpt'");
									}else $newOpt['image'] = null;
								};
								if(($newOpt['name'] == null && $newOpt['image'] == null) || ($f['state'] != null && $f['state'] != 'chn')) $optionsChange[$key] = null;
								else {
									$optid = 1;
									if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wotb_medal_options`"))) $optid = $fo['id'] + 1;
									$optname = $newOpt['name'];
									$optimage = $newOpt['image'];
									$optDescription = $newOpt['description'];
									mysqli_query($link, "INSERT INTO `wotb_medal_options` (`id`, `name`, `image`) VALUES ('$optid', '$optname', '$optimage')");
									$optionsChange[$key] = $optid;
									$changeOpt = true;
								};
							};
							if($changeOpt){
								$setOpt = json_encode($optionsChange);
								if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_medal_changes` WHERE `id` = '$idMedal'"))) mysqli_query($link, "UPDATE `wotb_medal_changes` SET `options` = '$setOpt' WHERE `id` = '$idMedal'");
								else mysqli_query($link, "INSERT INTO `wotb_medal_changes` (`id`, `options`) VALUES ('$idMedal', '$setOpt')");
								$out .= 'Медаль "'.$nameRu.'" - изменение опций'.$br;
							};
						};
					};
					$changes = false;
					if($fc = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_medal_changes` WHERE `id` = '$idMedal'"))){
						if($fc['nameRu'] != null || $fc['order'] != null || $fc['section'] != null || $fc['image'] != null || $fc['description'] != null || $fc['condition'] != null || $fc['options'] != null) $changes = true;
					};
					if($f['state'] == null || $f['state'] == 'chn'){
						if($changes) mysqli_query($link, "UPDATE `wotb_medals` SET `state` = 'chn' WHERE `id` = '$idMedal'");
						else{
							mysqli_query($link, "UPDATE `wotb_medals` SET `state` = NULL WHERE `id` = '$idMedal'");
							mysqli_query($link, "DELETE FROM `wotb_medal_changes` WHERE `id` = '$idMedal'");
						};
					}else mysqli_query($link, "DELETE FROM `wotb_medal_changes` WHERE `id` = '$idMedal'");
				}else{
					$idMedal = 1;
					if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wotb_medals`"))) $idMedal = $f['id'] + 1;
					$curMedal['id'] = $idMedal;
					$var = $val = '';
					foreach($curMedal as $key => $value) $var .= "`$key`, ";
					$var = substr($var, 0, -2);
					foreach($curMedal as $key => $value) $val .= $value == null ? "NULL, " : "'$value', ";
					$val = substr($val, 0, -2);
					$query = "INSERT INTO `wotb_medals` ($var) VALUES ($val)";
					mysqli_query($link, $query);
					if($medal['options'] != null){
						$options = array();
						foreach($medal['options'] as $key => $option){
							$optname = $option['name'];
							$optimage = $option['image'];
							$optid = 1;
							if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT MAX(`id`) AS `id` FROM `wotb_medal_options`"))) $optid = $fo['id'] + 1;
							$query = "INSERT INTO `wotb_medal_options` (`id`, `name`, `image`) VALUES ('$optid', '$optname', '$optimage')";
							mysqli_query($link, $query);
							if($optimage == null) mysqli_query($link, "UPDATE `wotb_medal_options` SET `image` = NULL WHERE `id` = '$optid'");
							$options[] = $optid;
						};
						$opt = json_encode($options);
						mysqli_query($link, "UPDATE `wotb_medals` SET `options` = '$opt' WHERE `id` = '$idMedal'");
					};
					$out .= 'Новая медаль "'.$curMedal['nameRu'].'" ('.$idMedal.')'.$br;
				};
			};
		};
	};
}
?>