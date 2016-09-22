<?
set_time_limit(200);
header("Content-type: text/html; charset=utf-8");
include '../utils/setData.php';

$out = array();
$out['status'] = 'ok';
$out['error'] = '';
$data = array();

$game = $_POST['game'];
$state = $_POST['state'];

$technics = $game."_technics";
$medals = $game."_medals";
$technic_changes = $game."_technic_change";
$medal_changes = $game."_medal_change";
$medal_options = $game."_medal_options";

if($state == 'new'){
	if($_POST['type'] == 'technic'){
		$list = $_POST['list'];
		foreach($list as $key => $technicID){
			mysqli_query($link, "UPDATE `".$game."_technics` SET `state` = NULL WHERE `id` = '$technicID'");
			if($rs = mysqli_query($link, "SELECT * FROM `$technics` WHERE `id` = '$technicID'")){
				if($f = mysqli_fetch_assoc($rs)) $data[$game]['technics'][$f['id']] = $f;
			};
		};
	};
	if($_POST['type'] == 'medal'){
		$list = $_POST['list'];
		foreach($list as $k => $medal){
			mysqli_query($link, "UPDATE `".$game."_medals` SET `state` = NULL WHERE `id` = '$medal'");
			if($rs = mysqli_query($link, "SELECT * FROM `$medals` WHERE `id` = '$medal'")){
				if($f = mysqli_fetch_assoc($rs)) $data[$game]['medals'][$f['id']] = $f;
				if(isset($f['options']) && $f['options'] != null){
					$options = json_decode($f['options']);
					$data[$game]['medals'][$f['id']]['options'] = array();
					foreach($options as $key => $option){
						if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_options` WHERE `id` = '$option'"))){
							$data[$game]['medals'][$f['id']]['options'][$key] = $fo;
						};
					};
				};
			};
		};
	};
};
if($state == 'chn'){
	if($_POST['type'] == 'technic'){
		$list = $_POST['list'];
		foreach($list as $key => $technicID){
			$chng = $_POST['change'];
			$rs = mysqli_query($link, "SELECT * FROM `$technic_changes` WHERE `id` = '$technicID'");
			if($f = mysqli_fetch_assoc($rs)){
				foreach($f as $change => $value){
					if($change != 'id' && ($change == $chng || $chng == 'all')){
						$newValue = $f[$change];
						if($newValue != null){
							mysqli_query($link, "UPDATE `".$game."_technics` SET `$change` = '$newValue' WHERE id = '$technicID'");
							mysqli_query($link, "UPDATE `".$game."_technic_changes` SET `$change` = NULL WHERE id = '$technicID'");
						};
					};
				};
				$rs = mysqli_query($link, "SELECT * FROM `$technic_changes` WHERE `id` = '$technicID'");
				if($f = mysqli_fetch_assoc($rs)){
					$changes = false;
					foreach($f as $change => $value) if($change != 'id' && $value != null) $changes = true;
					if($changes) $data[$game]['technicChanges'][$f['id']] = $f;
					else {
						mysqli_query($link, "DELETE FROM `$technic_changes` WHERE `id` = '$technicID'");
						mysqli_query($link, "UPDATE `".$game."_technics` SET `state` = NULL WHERE `id` = '$technicID'");
						$data[$game]['technicChanges'][$f['id']] = null;
					};
				};
				$rs = mysqli_query($link, "SELECT * FROM `$technics` WHERE id = '$technicID'");
				if($f = mysqli_fetch_assoc($rs)) $data[$game]['technics'][$f['id']] = $f;
			};
		};
	};
	if($_POST['type'] == 'medal'){
		$chng = $_POST['change'];
		if($chng == 'myOrder'){
			$sort = $_POST['sort'];
			foreach($sort as $key => $medal){
				mysqli_query($link, "UPDATE `".$game."_medals` SET `myOrder` = '$key' WHERE id = '$medal'");
				$rs = mysqli_query($link, "SELECT * FROM `$medals` WHERE id = '$medal'");
				if($f = mysqli_fetch_assoc($rs)) $data[$game]['medals'][$f['id']]['myOrder'] = $f['myOrder'];
			};
		}else{
			$list = $_POST['list'];
			foreach($list as $key => $medal){
				if($chng == 'view'){
					$newValue = $_POST['view'];
					mysqli_query($link, "UPDATE `".$game."_medals` SET `view` = '$newValue' WHERE id = '$medal'");
					$rs = mysqli_query($link, "SELECT * FROM `$medals` WHERE id = '$medal'");
					if($f = mysqli_fetch_assoc($rs)){
						$data[$game]['medals'][$f['id']] = $f;
						if(isset($f['options']) && $f['options'] != null){
							$options = json_decode($f['options']);
							$data[$game]['medals'][$f['id']]['options'] = array();
							foreach($options as $key => $option){
								if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_options` WHERE `id` = '$option'"))){
									$data[$game]['medals'][$f['id']]['options'][$key] = $fo;
								};
							};
						};
					};
				}else{
					$rs = mysqli_query($link, "SELECT * FROM `$medal_changes` WHERE `id` = '$medal'");
					if($f = mysqli_fetch_assoc($rs)){
						foreach($f as $change => $value){
							if($change != 'id' && ($change == $chng || $chng == 'all')){
								$newValue = $f[$change];
								if($newValue != null){
									if($change == 'options'){
										$newOptions = objectToArray(json_decode($f['options']));
										$f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `$medals` WHERE id = '$medal'"));
										$oldOptions = objectToArray(json_decode($f['options']));
										foreach($newOptions as $num => $option){
											if($newOptions[$num] != $oldOptions[$num]){
												$fn = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `$medal_options` WHERE id = '".$newOptions[$num]."'"));
												$fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `$medal_options` WHERE id = '".$oldOptions[$num]."'"));
												if($fn['name'] != NULL && $fn['name'] != $fo['name']) $fo['name'] = $fn['name'];
												if($fn['image'] != NULL && $fn['image'] != $fo['image']) $fo['image'] = $fn['image'];
												mysqli_query($link, "UPDATE `".$game."_medal_options` SET `name` = '".$fo['name']."', `image` = '".$fo['image']."' WHERE id = '".$fo['id']."'");
												mysqli_query($link, "DELETE FROM `$medal_options` WHERE `id` = '".$fn['id']."'");
												mysqli_query($link, "UPDATE `".$game."_medal_changes` SET `options` = NULL WHERE id = '$medal'");
											};
										};
										
									}else{
										mysqli_query($link, "UPDATE `".$game."_medals` SET `$change` = '$newValue' WHERE id = '$medal'");
										mysqli_query($link, "UPDATE `".$game."_medal_changes` SET `$change` = NULL WHERE id = '$medal'");
									};
								};
							};
						};
						$rs = mysqli_query($link, "SELECT * FROM `$medal_changes` WHERE `id` = '$medal'");
						if($f = mysqli_fetch_assoc($rs)){
							$changes = false;
							foreach($f as $change => $value) if($change != 'id' && $value != null) $changes = true;
							if($changes){
								$data[$game]['medalChanges'][$f['id']] = $f;
								if(isset($f['options']) && $f['options'] != null){
									$options = json_decode($f['options']);
									$data[$game]['medalChanges'][$f['id']]['options'] = array();
									foreach($options as $key => $option){
										if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_options` WHERE `id` = '$option'"))){
											$data[$game]['medalChanges'][$f['id']]['options'][$key] = $fo;
										};
									};
								};
							}else {
								mysqli_query($link, "DELETE FROM `$medal_changes` WHERE `id` = '$medal'");
								mysqli_query($link, "UPDATE `".$game."_medals` SET `state` = NULL WHERE `id` = '$medal'");
								$data[$game]['medalChanges'][$f['id']] = null;
							};
						};
						$rs = mysqli_query($link, "SELECT * FROM `$medals` WHERE id = '$medal'");
						if($f = mysqli_fetch_assoc($rs)){
							$data[$game]['medals'][$f['id']] = $f;
							if(isset($f['options']) && $f['options'] != null){
								$options = json_decode($f['options']);
								$data[$game]['medals'][$f['id']]['options'] = array();
								foreach($options as $key => $option){
									if($fo = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `wot_medal_options` WHERE `id` = '$option'"))){
										$data[$game]['medals'][$f['id']]['options'][$key] = $fo;
									};
								};
							};
						};
					};
				};
			};
		};
	};
};
$out['data'] = $data;

mysqli_close($link);
echo json_encode($out);
?>