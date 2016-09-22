<?
session_start();
include '../utils/setData.php';
$stat = array();
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

$member = $_POST['member'];
$game = $_POST['game'];
$initDate = $_POST['init'];
$endDate = $_POST['end'];

$table = $game."_stat";

$query = "SELECT * FROM `$table` WHERE `id` = '$member' AND `date` >= '$initDate' AND `date` <= '$endDate'";
if($rs = mysqli_query($link, $query)){
	while($f = mysqli_fetch_assoc($rs)){
		$f['medals'] = json_decode($f['medals']);
		$f['technics'] = json_decode($f['technics']);
		$stat[$f['date']] = $f;
	};
};
$query = "SELECT * FROM `$table` WHERE `id` = '$member' ORDER BY `date` LIMIT 1";
if($rs = mysqli_query($link, $query)){
	if($f = mysqli_fetch_assoc($rs)){
		$return['start'] = $f['date'];
	};
};

if($error == ''){
	$return['status'] = 'ok';
	$return['data'] = $stat;
}else{
	$return['status'] = 'error';
	$return['error'] = $error;
};
mysqli_close($link);
echo json_encode($return);
?>