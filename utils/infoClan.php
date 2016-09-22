<?
function checkClanData(){
	global $link, $check, $startTime, $out, $br, $clanID, $clanTag, $clanName, $clanColor, $clanMotto, $clanLogo, $users;
	
	$updateTime = '';
	if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'updateTime'"))) $updateTime = $f['value'];
	else mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('updateTime', '')");
	$clanData = getApiRequest('clan', $clanID, '');
	if($clanData != ''){
		$curUpdateTime = date('Y-m-d H:i:s', $clanData[$clanID]['updated_at']);
		if($updateTime != $curUpdateTime){
			$out .= 'Проверка клана'.$br;
			$check = false;
			$newClanTag = $clanData[$clanID]['tag'];
			$newClanName = $clanData[$clanID]['name'];
			$newClanColor = $clanData[$clanID]['color'];
			$newClanMotto = $clanData[$clanID]['motto'];
			$newClanLogo = $clanData[$clanID]['emblems']['x64']['portal'];
			mysqli_query($link, "UPDATE `data` SET `value` = '$curUpdateTime' WHERE `variable` = 'updateTime'");
			if($clanTag != $newClanTag){
				mysqli_query($link, "UPDATE `data` SET `value` = '$newClanTag' WHERE `variable` = 'tag'");
				$clanTag = $newClanTag;
				$out .= 'У клана новый тег - '.$newClanTag.$br;
			};
			if($clanName != $newClanName){
				mysqli_query($link, "UPDATE `data` SET `value` = '$newClanName' WHERE `variable` = 'name'");
				$clanName = $newClanName;
				$out .= 'У клана новое имя - '.$newClanName.$br;
			};
			if($clanColor != $newClanColor){
				mysqli_query($link, "UPDATE `data` SET `value` = '$newClanColor' WHERE `variable` = 'color'");
				$clanColor = $newClanColor;
				$out .= 'У клана новый цвет - <span style="background-color:'.$newClanColor.'">___</span>'.$br;
			};
			if($clanMotto != $newClanMotto){
				mysqli_query($link, "UPDATE `data` SET `value` = '$newClanMotto' WHERE `variable` = 'motto'");
				$clanMotto = $newClanMotto;
				$out .= 'У клана новый девиз - '.$newClanMotto.$br;
			};
			if($clanLogo != $newClanLogo){
				mysqli_query($link, "UPDATE `data` SET `value` = '$newClanLogo' WHERE `variable` = 'logo'");
				$clanLogo = $newClanLogo;
				$out .= 'У клана новая эмблема - <img src="'.$newClanLogo.'" \>'.$br;
			};
			
			foreach($clanData[$clanID]['members'] as $num => $member){
				$memberID = $member['account_id'];
				if(!(mysqli_fetch_assoc(mysqli_query($link, "SELECT `id` FROM `members` WHERE `id` = '$memberID'")))){
					addNewMember($memberID);
				};
				if((time() - $startTime) > 50){
					$check = false;
					break;
				};
			};
		}else $out .= 'В клане без изменений'.$br;
	};
	$checkClan = date('Y-m-d H:00:00', $startTime);
	foreach($users as $userID => $user){
		if($user['checkClan'] != $checkClan){
			$check = false;
			checkMemberClanData($userID);
			mysqli_query($link, "UPDATE `members` SET `checkClan` = '$checkClan' WHERE `id` = '$userID'");
			if((time() - $startTime) > 50) break;
		};
	};
}

function addNewMember($memberID){
	global $link, $out, $br;
	
	$memberData = getApiRequest('member', $memberID, '');
	$name = $memberData[$memberID]['nickname'];
	$rights = 'guest';
	$regdate = date('Y-m-d H:i:s', $memberData[$memberID]['created_at']);
	$games = json_encode($memberData[$memberID]['games']);
	$query = "INSERT INTO `members` (`id`, `name`, `rights`, `regDate`, `games`) VALUES ('$memberID', '$name', '$rights', '$regdate', '$games')";
	mysqli_query($link, $query);
	$out .= 'Добавлен новый пользователь '.$name.$br;
	setMembersColor();
	checkMemberClanData($memberID);
}

function checkMemberClanData($memberID){
	global $link, $out, $br, $clanID;
	
	$clan = null;
	if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `clan`, `role`, `games` FROM `members` WHERE `id` = '$memberID'"))) $clan = $f['clan'];
	$newClan = null;
	$memberClanData = getApiRequest('member', $memberID, 'clan');
	if($memberClanData) $newClan = $memberClanData[$memberID]['clan']['clan_id'];
	if($clan != $newClan){
		if($newClan == null){
			mysqli_query($link, "UPDATE `members` SET `rights` = 'guest', `clan` = NULL, `regInClan` = NULL, `role` = NULL WHERE `id` = '$memberID'");
			$out .= 'Пользователь '.$memberID.' вышел из клана ['.$clan.']'.$br;
		}else{
			checkClan($newClan);
			$newRegInClan = date('Y-m-d H:i:s', $memberClanData[$memberID]['joined_at']);
			$newRole = $memberClanData[$memberID]['role'];
			checkRole($newRole, $memberClanData[$memberID]['role_i18n']);
			$rights = 'guest';
			if($newClan == $clanID) $rights = 'member';
			mysqli_query($link, "UPDATE `members` SET `rights` = '$rights', `clan` = '$newClan', `regInClan` = '$newRegInClan', `role` = '$newRole' WHERE `id` = '$memberID'");
			$out .= 'Пользователь '.$memberID.' принят в клан ['.$newClan.'] на должность '.$newRole.$br;
		};
	}else if($newClan != null && $f['role'] != $memberClanData[$memberID]['role']){
		$newRole = $memberClanData[$memberID]['role'];
		checkRole($newRole, $memberClanData[$memberID]['role_i18n']);
		mysqli_query($link, "UPDATE `members` SET `role` = '$newRole' WHERE `id` = '$memberID'");
		$out .= 'Пользователь '.$memberID.' получил новую должность '.$newRole.$br;
	};
	$memberData = getApiRequest('member', $memberID, '');
	$games = json_encode($memberData[$memberID]['games']);
	if($f['games'] != $games) mysqli_query($link, "UPDATE `members` SET `games` = '$games' WHERE `id` = '$memberID'");
}

function checkClan($clan){
	global $link;

	$clanData = getApiRequest('clan', $clan, '');
	if($clanData != ''){
		$tag = $clanData[$clan]['tag'];
		$name = $clanData[$clan]['name'];
		$created = date('Y-m-d H:i:s', $clanData[$clan]['created_at']);
		$emblem = $clanData[$clan]['emblems']['x64']['portal'];
		$color = $clanData[$clan]['color'];
		if(!($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `clans` WHERE `id` = '$clan'")))){
			mysqli_query($link, "INSERT INTO `clans` (`id`, `tag`, `name`, `created`, `emblem`, `color`) VALUES ('$clan', '$tag', '$name', '$created', '$emblem', '$color')");
		}else{
			if($tag != $f['tag']) mysqli_query($link, "UPDATE `clans` SET `tag` = '$tag' WHERE `id` = 'clan'");
			if($name != $f['name']) mysqli_query($link, "UPDATE `clans` SET `name` = '$name' WHERE `id` = 'clan'");
			if($created != $f['created']) mysqli_query($link, "UPDATE `clans` SET `created` = '$created' WHERE `id` = 'clan'");
			if($emblem != $f['emblem']) mysqli_query($link, "UPDATE `clans` SET `emblem` = '$emblem' WHERE `id` = 'clan'");
			if($color != $f['color']) mysqli_query($link, "UPDATE `clans` SET `color` = '$color' WHERE `id` = 'clan'");
		};
	};
}

function checkRole($role, $roleRu){
	global $link;

	$memberRole = array();
	if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `data` WHERE `variable` = 'memberRole'"))){
		if($f['value'] != null) $memberRole = objectToArray(json_decode($f['value']));
	}else{
		mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('memberRole', NULL)");
	};
	$updateRole = false;
	if(isset($memberRole[$role])){
		if($memberRole[$role] != $roleRu) $updateRole = true;
	}else $updateRole = true;
	if($updateRole){
		$memberRole[$role] = $roleRu;
		$value = json_encode($memberRole);
		mysqli_query($link, "UPDATE `data` SET `value` = '$value' WHERE `variable` = 'memberRole'");
	};
}
