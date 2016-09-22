<?
session_start();

$mcn = mysql_connect("localhost","kashni_den","wWhYOhDP5r") or die ('Not connected : ' . mysql_error());
mysqli_query($link, "SET NAMES utf8") or die("Invalid query: " . mysql_error());
mysql_select_db("kashni_mirr") or die ('Can\'t use kashni_wot : ' . mysql_error());

toLog('<br />LOG START openID.php '.date('Y-m-d H:i:s').'<br />');
toLog('session_id = '.session_id().'<br />');
if(isset($_GET['action'])){
	if($_GET['action'] == 'verify'){
		toLog('Action verify<br />');
		$url = 'https://api.worldoftanks.ru/wot/auth/login/?application_id=0795d98b340d4670eeafc2b63186b96e&redirect_uri=http://den.kashnikoff.ru/MIR-R/openID.php';
		header('Location: '.$url);
		mysqli_close($link);
		exit;
	}else if($_GET['action'] == 'exit'){
		toLog('Action exit<br />');
		setcookie('sessionID', session_id(), time() - 1209600);
		session_destroy();
		header('Location: index.php');
		mysqli_close($link);
		exit;
	};
}else if(isset($_GET['status'])){
	if($_GET['status'] == 'ok'){
		if(isset($_GET['account_id'])){
			toLog('Enter<br />');
			toLog('account_id = '.$_GET['account_id'].'<br />');
			toLog('GET = '.json_encode($_GET).'<br />');
			$_SESSION['login'] = $_GET['account_id'];
			if($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '".$_GET['account_id']."'"))){
				$cookie = $f['sessionID'];
				toLog('cookie = '.$cookie.'<br />');
				if($f['sessionID'] == null){
					toLog('save sessionID = '.session_id().'<br />');
					mysqli_query($link, "UPDATE `members` SET `sessionID` = '".session_id()."' WHERE `id` = '".$_SESSION['login']."'");
					$cookie = session_id();
				};
				setcookie('sessionID', $cookie, time() + 1209600); //кука на 2 недели
				if(isset($_GET['access_token']) && isset($_GET['expires_at'])) mysqli_query($link, "UPDATE `members` SET `accessToken` = '".$_GET['access_token']."', `expiresAt` = '".date('Y-m-d H:i:s', $_GET['expires_at'])."' WHERE `id` = '".$_GET['account_id']."'");
			};
			if(isset($_GET['nickname'])) $_SESSION['nickname'] = $_GET['nickname'];
		};
	};
	header('Location: index.php');
}else header('Location: index.php');
mysqli_close($link);

function toLog($data){
	$dir = 'utils/log';
	if(!(file_exists($dir))) mkdir($dir);
	$name = $dir.'/visitors.txt';
	$fp = fopen($name, 'a');
	$test = fwrite($fp, str_replace('<br />', PHP_EOL, $data));
	fclose($fp);
}
?>