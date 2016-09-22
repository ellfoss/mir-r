<?
session_start();
set_time_limit(200);
header("Content-type: text/html; charset=utf-8");
include '../utils/setData.php';

$out = array();
$out['status'] = 'ok';
$out['error'] = '';

$state = $_POST['state'];
$member = $_POST['member'];
$out['member'] = $member;
$rights = 'guest';
if(isset($_SESSION['login'])){
	if($rs = mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '".$_SESSION['login']."'")){
		if($f = mysqli_fetch_assoc($rs)){
			$rights = $f['rights'];
		};
	};
}else if(isset($_COOKIE['sessionID'])){
	if($rs = mysqli_query($link, "SELECT * FROM `members` WHERE `sessionID` = '".$_COOKIE['sessionID']."'")){
		if($f = mysqli_fetch_assoc($rs)){
			$rights = $f['rights'];
		};
	};
}
if ($rights == 'admin' || $rights == 'sadmin') {
	if ($state == 'del') {
		$f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '$member'"));
		if ($rights == $f['rights'] || $f['rights'] == 'sadmin') {
			$out['status'] = 'error';
			$out['error'] = 'Удаление невозможно';
		} else {
			mysqli_query($link, "DELETE FROM `members` WHERE `id` = '$member'");
			setMembersColor();
			$rs = mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '$member'");
			if ($f = mysqli_fetch_assoc($rs)) {
				$out['status'] = 'error';
				$out['error'] = 'Удаление неудачно';
			} else {
				mysqli_query($link, "DELETE FROM `wot_stat` WHERE `id` = '$member'");
				mysqli_query($link, "DELETE FROM `wotb_stat` WHERE `id` = '$member'");
				mysqli_query($link, "DELETE FROM `wowp_stat` WHERE `id` = '$member'");
				mysqli_query($link, "DELETE FROM `wows_stat` WHERE `id` = '$member'");
			};
		};
	};
	if ($state == 'chn') {
		$f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '$member'"));
		if ($rights == 'admin' && $f['rights'] == 'sadmin') {
			$out['status'] = 'error';
			$out['error'] = 'Изменение невозможно';
		} else {
			$name = $_POST['name'];
			mysqli_query($link, "UPDATE `members` SET `rName` = '$name' WHERE `id` = '$member'");
			$rs = mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '$member'");
			if ($f = mysqli_fetch_assoc($rs)) {
				$out['name'] = $f['rName'];
				if ($name != $f['rName']) {
					$out['status'] = 'error';
					$out['error'] = 'Изменение неудачно';
				};
			};
		};
	};
	if ($state == 'find') {
		$name = $_POST['name'];
		$data = getApiRequest('member', '', $name);
		foreach($data as $key => $member) {
			$out['data'][] = $member;
		};
	};
	if ($state == 'new') {
		$member = $_POST['member'];
		$rs = mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '$member'");
		if ($f = mysqli_fetch_assoc($rs)) {
			$out['status'] = 'error';
			$out['error'] = 'Данный игрок уже присутствует';
		} else {
			$memberData = getApiRequest('member', $member, '');
			$name = $memberData[$member]['nickname'];
			$rights = 'guest';
			$regdate = date('Y-m-d H:i:s', $memberData[$member]['created_at']);
			$games = json_encode($memberData[$member]['games']);
			mysqli_query($link, "INSERT INTO `members` (`id`, `name`, `rights`, `regDate`, `games`) VALUES ('$member', '$name', '$rights', '$regdate', '$games')");
			$clan = checkMemberClanData($member);
			setMembersColor();
			if($clan) $out['clan'] = $clan;
			$rs = mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '$member'");
			if($f = mysqli_fetch_assoc($rs)){
				$out['member'] = array();
				$games = json_decode($f['games']);
				$updates = array();
				foreach($games as $num => $game){
					if($game == 'wot') $updates['wot'] = $f['wotUpdate'];
					if($game == 'wotb') $updates['wotb'] = $f['wotbUpdate'];
					if($game == 'wowp') $updates['wowp'] = $f['wowpUpdate'];
					if($game == 'wows') $updates['wows'] = $f['wowsUpdate'];
					if($game == 'wotg') $updates['wotg'] = null;
				};
				unset($f['wotUpdate']);
				unset($f['wotbUpdate']);
				unset($f['wowpUpdate']);
				unset($f['wowsUpdate']);
				$f['games'] = $updates;
				$out['member'][] = $f;
			};
		};
	};
} else {
	$out['status'] = 'error';
	$out['error'] = 'Отсутствуют необходимые права';
};

mysqli_close($link);
echo json_encode($out);

function checkMemberClanData($member) {
	global $link, $clanID;
	
	$ret = false;
	$clan = null;
	$newClan = null;
	$memberClanData = getApiRequest('member', $member, 'clan');
	if ($memberClanData)
		$newClan = $memberClanData[$member]['clan']['clan_id'];
	if ($clan != $newClan) {
		$ret = checkClan($newClan);
		$newRegInClan = date('Y-m-d H:i:s', $memberClanData[$member]['joined_at']);
		$newRole = $memberClanData[$member]['role'];
		checkRole($newRole, $memberClanData[$member]['role_i18n']);
		$rights = 'guest';
		if ($newClan == $clanID)
			$rights = 'member';
		mysqli_query($link, "UPDATE `members` SET `rights` = '$rights', `clan` = '$newClan', `regInClan` = '$newRegInClan', `role` = '$newRole' WHERE `id` = '$member'");
	};
	return $ret;
}

function checkClan($clan) {
	global $link;

	$ret = false;
	if (!($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `clans` WHERE `id` = '$clan'")))) {
		$clanData = getApiRequest('clan', $clan, '');
		if ($clanData != '') {
			$tag = $clanData[$clan]['tag'];
			$name = $clanData[$clan]['name'];
			$created = date('Y-m-d H:i:s', $clanData[$clan]['created_at']);
			$emblem = $clanData[$clan]['emblems']['x64']['portal'];
			$color = $clanData[$clan]['color'];
			mysqli_query($link, "INSERT INTO `clans` (`id`, `tag`, `name`, `created`, `emblem`, `color`) VALUES ('$clan', '$tag', '$name', '$created', '$emblem', '$color')");
			$rs = mysqli_query($link, "SELECT * FROM `clans` WHERE `id` = '$clan'");
			if($f = mysqli_fetch_assoc($rs)){
				$ret = $f;
			};
		};
	};
	return $ret;
}

function checkRole($role, $roleRu) {
	global $link;

	$memberRole = array();
	if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `data` WHERE `variable` = 'memberRole'"))) {
		if ($f['value'] != null)
			$memberRole = objectToArray(json_decode($f['value']));
	} else {
		mysqli_query($link, "INSERT INTO `data` (`variable`, `value`) VALUES ('memberRole', NULL)");
	};
	$updateRole = false;
	if (isset($memberRole[$role])) {
		if ($memberRole[$role] != $roleRu)
			$updateRole = true;
	} else
		$updateRole = true;
	if ($updateRole) {
		$memberRole[$role] = $roleRu;
		$value = json_encode($memberRole);
		mysqli_query($link, "UPDATE `data` SET `value` = '$value' WHERE `variable` = 'memberRole'");
	};
}
