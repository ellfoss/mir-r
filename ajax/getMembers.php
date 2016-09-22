<?
session_start();
include '../utils/setData.php';
$members = array();
$return = array();
$error = '';
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

if($rights == 'admin' || $rights == 'sadmin') $query = "SELECT * FROM `members`";
else $query = "SELECT * FROM `members` WHERE `clan` = '$clanID'";
if($db = mysqli_query($link, $query)){
	while($f = mysqli_fetch_assoc($db)){
		$members[$f['id']] = $f;
		$games = json_decode($f['games']);
		$updates = array();
		foreach($games as $num => $game){
			if($game == 'wot') $updates['wot'] = $f['wotUpdate'];
			if($game == 'wotb') $updates['wotb'] = $f['wotbUpdate'];
			if($game == 'wowp') $updates['wowp'] = $f['wowpUpdate'];
			if($game == 'wows') $updates['wows'] = $f['wowsUpdate'];
			if($game == 'wotg') $updates['wotg'] = null;
		};
		unset($members[$f['id']]['wotUpdate']);
		unset($members[$f['id']]['wotbUpdate']);
		unset($members[$f['id']]['wowpUpdate']);
		unset($members[$f['id']]['wowsUpdate']);
		$members[$f['id']]['games'] = $updates;
	};
};
if($error == ''){
	$return['status'] = 'ok';
	$return['data'] = $members;
}else{
	$return['status'] = 'error';
	$return['error'] = $error;
};
mysqli_close($link);
echo json_encode($return);
