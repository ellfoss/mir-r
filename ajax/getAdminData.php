<?
set_time_limit(60);
header("Content-type: text/html; charset=utf-8");
include '../utils/setData.php';
$return = array();
$data = array();
$error = '';
if($rs = mysqli_query($link, "SELECT * FROM `wot_technic_changes`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wot']['technicChanges'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wot_medal_changes`")){
	while($f = mysqli_fetch_assoc($rs)){
		$data['wot']['medalChanges'][$f['id']] = $f;
		if($f['options'] != null){
			$options = json_decode($f['options']);
			$data['wot']['medalChanges'][$f['id']]['options'] = array();
			foreach($options as $key => $option){
				if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_options` WHERE `id` = '$option'"))){
					$data['wot']['medalChanges'][$f['id']]['options'][$key] = $fo;
				};
			};
		};
	};
};
if($rs = mysqli_query($link, "SELECT * FROM `wotb_technic_changes`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wotb']['technicChanges'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wotb_medal_changes`")){
	while($f = mysqli_fetch_assoc($rs)){
		$data['wotb']['medalChanges'][$f['id']] = $f;
		if($f['options'] != null){
			$options = json_decode($f['options']);
			$data['wotb']['medalChanges'][$f['id']]['options'] = array();
			foreach($options as $key => $option){
				if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_medal_options` WHERE `id` = '$option'"))){
					$data['wotb']['medalChanges'][$f['id']]['options'][$key] = $fo;
				};
			};
		};
	};
};
if($rs = mysqli_query($link, "SELECT * FROM `wowp_technic_changes`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wowp']['technicChanges'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wowp_medal_changes`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wowp']['medalChanges'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wows_technic_changes`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wows']['technicChanges'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wows_medal_changes`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wows']['medalChanges'][$f['id']] = $f;
};

if($rs = mysqli_query($link, "SELECT * FROM `visitors`")){
	while($f = mysqli_fetch_assoc($rs)){
		$visit = array();
		$visit['member'] = $f['member'];
		$visit['ip'] = $f['ip'];
		$visit['cookie'] = $f['cookie'];
		$visit['browser'] = $f['browser'];
		$visit['time'] = json_decode($f['visits']);
		$data['visitors'][$f['date']][] = $visit;
	};
};


mysqli_close($link);
if($error == ''){
	$return['status'] = 'ok';
	$return['data'] = $data;
}else{
	$return['status'] = 'error';
	$return['error'] = $error;
};
echo json_encode($return);
