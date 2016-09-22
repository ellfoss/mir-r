<?
set_time_limit(60);
header("Content-type: text/html; charset=utf-8");
include '../utils/setData.php';
$return = array();
$data = array();
$error = '';
if($rs = mysqli_query($link, "SELECT * FROM `data`")){
	while($f = mysqli_fetch_assoc($rs)){
		$data['data'][$f['variable']] = $f['value'];
		if($f['variable'] == 'sortNations' || $f['variable'] == 'sortTypes') $data['data'][$f['variable']] = objectToArray(json_decode($f['value']));
	};
};
if($rs = mysqli_query($link, "SELECT * FROM `member_role`")){
	while($f = mysqli_fetch_assoc($rs)) $data['roles'][$f['role']] = $f['roleRu'];
};
if($rs = mysqli_query($link, "SELECT * FROM `clans`")){
	while($f = mysqli_fetch_assoc($rs)) $data['clans'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wot_data`")){
	while($f = mysqli_fetch_assoc($rs)){
		$data['wot']['data'][$f['variable']] = objectToArray(json_decode($f['value']));
		if($f['variable'] == 'expectedTankValues'){
			$data['wot']['data']['expectedTankValues'] = array();
			foreach(objectToArray(json_decode($f['value'])) as $key => $value){
				$data['wot']['data']['expectedTankValues'][$value['IDNum']] = $value;
			};
		};
	};
};
if($rs = mysqli_query($link, "SELECT * FROM `wot_technics`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wot']['technics'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wot_medals`")){
	while($f = mysqli_fetch_assoc($rs)){
		$data['wot']['medals'][$f['id']] = $f;
		if($f['options'] != null){
			$options = json_decode($f['options']);
			$data['wot']['medals'][$f['id']]['options'] = array();
			foreach($options as $key => $option){
				if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_options` WHERE `id` = '$option'"))){
					$data['wot']['medals'][$f['id']]['options'][$key] = $fo;
				};
			};
		};
	};
};
if($rs = mysqli_query($link, "SELECT * FROM `wotb_data`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wotb']['data'][$f['variable']] = objectToArray(json_decode($f['value']));
};
if($rs = mysqli_query($link, "SELECT * FROM `wotb_technics`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wotb']['technics'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wotb_medals`")){
	while($f = mysqli_fetch_assoc($rs)){
		$data['wotb']['medals'][$f['id']] = $f;
		if($f['options'] != null){
			$options = json_decode($f['options']);
			$data['wotb']['medals'][$f['id']]['options'] = array();
			foreach($options as $key => $option){
				if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wotb_medal_options` WHERE `id` = '$option'"))){
					$data['wotb']['medals'][$f['id']]['options'][$key] = $fo;
				};
			};
		};
	};
};
if($rs = mysqli_query($link, "SELECT * FROM `wowp_data`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wowp']['data'][$f['variable']] = objectToArray(json_decode($f['value']));
};
if($rs = mysqli_query($link, "SELECT * FROM `wowp_technics`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wowp']['technics'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wowp_medals`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wowp']['medals'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wows_data`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wows']['data'][$f['variable']] = objectToArray(json_decode($f['value']));
};
if($rs = mysqli_query($link, "SELECT * FROM `wows_technics`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wows']['technics'][$f['id']] = $f;
};
if($rs = mysqli_query($link, "SELECT * FROM `wows_medals`")){
	while($f = mysqli_fetch_assoc($rs)) $data['wows']['medals'][$f['id']] = $f;
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
