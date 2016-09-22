<?
include 'connectdb.php';

function getApiRequest($query, $id, $param){
	$serverWoT = 'http://api.worldoftanks.ru/';
	$serverWoWP = 'http://api.worldofwarplanes.ru/';
	$serverWoWS = 'http://api.worldofwarships.ru/';
	$serverWoTB = 'http://api.wotblitz.ru/';
	$appID = 'application_id=0795d98b340d4670eeafc2b63186b96e';
	$ret = '';
	$url = '';
	$str = '';
	if($query == 'clan'){
		if($id != '') $url = $serverWoT.'wgn/clans/info/?'.$appID.'&clan_id='.$id;
	};
	if($query == 'member' && $id != ''){
		if($param == '') $url = $serverWoT.'wgn/account/info/?'.$appID.'&account_id='.$id;
		if($param == 'clan') $url = $serverWoT.'wgn/clans/membersinfo/?'.$appID.'&account_id='.$id;
		if($param == 'wot') $url = $serverWoT.'wot/account/info/?'.$appID.'&account_id='.$id;
		if($param == 'wot_update') $url = $serverWoT.'wot/account/info/?'.$appID.'&fields=updated_at&account_id='.$id;
		if($param == 'wot_battle_time') $url = $serverWoT.'wot/account/info/?'.$appID.'&fields=last_battle_time&account_id='.$id;
		if($param == 'wot_tanks') $url = $serverWoT.'wot/account/tanks/?'.$appID.'&account_id='.$id;
		if($param == 'wot_medals') $url = $serverWoT.'wot/account/achievements/?'.$appID.'&account_id='.$id;
		if($param == 'wotb') $url = $serverWoTB.'wotb/account/info/?'.$appID.'&account_id='.$id;
		if($param == 'wotb_update') $url = $serverWoTB.'wotb/account/info/?'.$appID.'&fields=updated_at&account_id='.$id;
		if($param == 'wotb_battle_time') $url = $serverWoTB.'wotb/account/info/?'.$appID.'&fields=last_battle_time&account_id='.$id;
		if($param == 'wotb_tanks') $url = $serverWoTB.'wotb/tanks/stats/?'.$appID.'&account_id='.$id;
		if($param == 'wotb_medals') $url = $serverWoTB.'wotb/account/achievements/?'.$appID.'&account_id='.$id;
		if($param == 'wowp') $url = $serverWoWP.'wowp/account/info/?'.$appID.'&account_id='.$id;
		if($param == 'wowp_update') $url = $serverWoWP.'wowp/account/info/?'.$appID.'&fields=updated_at&account_id='.$id;
		if($param == 'wowp_battle_time') $url = $serverWoWP.'wowp/account/info/?'.$appID.'&fields=last_battle_time&account_id='.$id;
		if($param == 'wowp_planes') $url = $serverWoWP.'wowp/account/planes/?'.$appID.'&account_id='.$id;
		if($param == 'wows') $url = $serverWoWS.'wows/account/info/?'.$appID.'&account_id='.$id;
		if($param == 'wows_update') $url = $serverWoWS.'wows/account/info/?'.$appID.'&fields=stats_updated_at&account_id='.$id;
		if($param == 'wows_battle_time') $url = $serverWoWS.'wows/account/info/?'.$appID.'&fields=last_battle_time&account_id='.$id;
		if($param == 'wows_ships') $url = $serverWoWS.'wows/ships/stats/?'.$appID.'&fields=ship_id%2Cpvp.battles%2Cpvp.wins&account_id='.$id;
		if($param == 'wows_medals') $url = $serverWoWS.'wows/account/achievements/?'.$appID.'&account_id='.$id;
	};
	if($query == 'member' && $id == ''){
		if($param != '') $url = $serverWoT.'wgn/account/list/?'.$appID.'&search='.$param.'&limit=10';
	};
	if($query == 'wot'){
		if($param == 'info') $url = $serverWoT.'wot/encyclopedia/info/?'.$appID;
		if($param == 'tanks'){
			$url = $serverWoT.'wot/encyclopedia/vehicles/?'.$appID.'&fields=name%2Cshort_name%2Cimages.small_icon%2Ctier%2Ctype%2Cnation%2Cis_premium';
			if($id != '') $url .= '&tank_id='.$id;
		}
		if($param == 'medals') $url = $serverWoT.'wot/encyclopedia/achievements/?'.$appID;
		if($param == 'armor') $url = 'http://armor.kiev.ua/wot/api.php';
		if($param == 'wn8') $url = 'http://www.wnefficiency.net/exp/expected_tank_values_27.json';
	};
	if($query == 'wotb'){
		if($param == 'info') $url = $serverWoTB.'wotb/encyclopedia/info/?'.$appID;
		if($param == 'tanks'){
			$url = $serverWoTB.'wotb/encyclopedia/vehicles/?'.$appID.'&fields=name%2Cimages.preview%2Ctier%2Ctype%2Cnation%2Cis_premium';
			if($id != '') $url .= '&tank_id='.$id;
		}
		if($param == 'medals') $url = $serverWoTB.'wotb/encyclopedia/achievements/?'.$appID;
	};
	if($query == 'wowp'){
		if($param == 'info') $url = $serverWoWP.'wowp/encyclopedia/info/?'.$appID;
		if($param == 'planes') $url = $serverWoWP.'wowp/encyclopedia/planes/?'.$appID;
		if($param == 'medals') $url = $serverWoWP.'wowp/encyclopedia/achievements/?'.$appID;
	};
	if($query == 'wows'){
		if($param == 'info') $url = $serverWoWS.'wows/encyclopedia/info/?'.$appID;
		if($param == 'ships') $url = $serverWoWS.'wows/encyclopedia/ships/?'.$appID.'&fields=ship_id_str%2Cname%2Cnation%2Ctier%2Ctype%2Cis_premium%2Cdescription%2Cimages.small';
		if($param == 'medals') $url = $serverWoWS.'wows/encyclopedia/achievements/?'.$appID;
	};
	// echo $url.'<br />';
	if($url != ''){
		$info = file($url);
		foreach($info as $key => $value) $str .= $value;
		if($str != ''){
			$answer = objectToArray(json_decode($str));
			if($answer['status'] == 'ok' || $answer['header'] != ''){
				$ret = $answer['data'];
				if($param == 'armor') $ret = $answer['classRatings'];
				if($ret == null) $ret = array();
			}
			else echo $url.' ('.$query.', '.$id.', '.$param.') -> '.$str.'<br />';
		};
	}
	else echo $url.' -! ('.$query.', '.$id.', '.$param.')<br />';
	return $ret;
}

function objectToArray($object){
	if(!is_object($object) && !is_array($object)) return $object;
	if(is_object($object)) $object = get_object_vars($object);
	return array_map('objectToArray', $object);
}

function json_replace($str){
	if(gettype($str) == 'string'){
		$i = 0;
		while($i < 300){
			$ps = $str;
			if($pos = stripos($ps, '\u')){
				$ps = substr($ps, $pos);
				$pos = stripos($ps, '"');
				$k = substr($ps, 0, $pos);
				$h = '{"string":"'.$k.'"}';
				$g = json_decode($h);
				$str = str_replace($k, $g->string, $str);
			}
			else $i = 300;
			$i++;
		};
		$str = str_replace(chr(9), ' ', $str);
		$str = str_replace(chr(10), '<br />', $str);
		$str = str_replace(chr(13), '', $str);
	};
	return $str;
};

function setMembersColor(){
	global $link;

	$rs = mysqli_query($link, "SELECT * FROM `members` ORDER BY `regDate` DESC");
	$items = mysqli_num_rows($rs) - 1;
	$item = 0;
	while($f = mysqli_fetch_assoc($rs)){
		$id = $f['id'];
		$color = '';
		$cur = 100 * $item / $items;
		$part = 100 / 6;
		if($cur >= 0 && $cur <= $part){
			$d = $cur / $part;
			$r = 255; 
			$g = (int) ($d * 127); 
			$b = 0;
			$color = color($r, $g, $b);
		};
		if($cur > $part && $cur <= $part * 2){
			$d = ($cur - $part) / $part;
			$r = 255; 
			$g = (int) (128 + $d * 127); 
			$b = 0;
			$color = color($r, $g, $b);
		};
		if($cur > $part * 2 && $cur <= $part * 3){
			$d = ($cur - $part * 2) / $part;
			$r = (int) (255 - $d * 255); 
			$g = 255; 
			$b = 0;
			$color = color($r, $g, $b);
		};
		if($cur > $part * 3 && $cur <= $part * 4){
			$d = ($cur - $part * 3) / $part;
			$r = 0; 
			$g = 255; 
			$b = (int) ($d * 255);
			$color = color($r, $g, $b);
		};
		if($cur > $part * 4 && $cur <= $part * 5){
			$d = ($cur - $part * 4) / $part;
			$r = 0; 
			$g = (int) (255 - $d * 255); 
			$b = 255;
			$color = color($r, $g, $b);
		};
		if($cur > $part * 5 && $cur <= $part * 6){
			$d = ($cur - $part * 5) / $part;
			$r = (int) ($d * 255);
			$g = 0; 
			$b = 255;
			$color = color($r, $g, $b);
		};
		mysqli_query($link, "UPDATE `members` SET `color` = '$color' WHERE `id` = '$id'");
		$item++;
	};
}

function color($r, $g, $b){
	$rh = dechex($r);
	if(strlen($rh) == 1) $rh = '0'.$rh;
	$gh = dechex($g);
	if(strlen($gh) == 1) $gh = '0'.$gh;
	$bh = dechex($b);
	if(strlen($bh) == 1) $bh = '0'.$bh;
	return '#'.$rh.$gh.$bh;
}

