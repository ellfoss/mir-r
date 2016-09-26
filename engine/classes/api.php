<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 09.09.2016
 * Time: 13:18
 */
class Api
{
	private static $appID = 'application_id=0795d98b340d4670eeafc2b63186b96e';
	private static $serverWoT = 'http://api.worldoftanks.ru/';
	private static $serverWoWP = 'http://api.worldofwarplanes.ru/';
	private static $serverWoWS = 'http://api.worldofwarships.ru/';
	private static $serverWoTB = 'http://api.wotblitz.ru/';
	private static $query_interval = 0.1;
	private static $last_query_time = 0;

	private static function wargaming($url)
	{
		Log::out('url = '.$url);
		$time = microtime(true) - self::$last_query_time;
		if ($time < self::$query_interval) usleep((int)((self::$query_interval - $time) * 1000000));
		try {
			$str = '';
			$info = file($url);
			self::$last_query_time = microtime(true);
			foreach ($info as $key => $value) $str .= $value;
			$res = json_decode($str);
		} catch (Exception $e) {
			$res = false;
		}
		if ($res) {
			if (isset($res->status) && $res->status == 'ok') {
				return $res->data;
			} else $res = false;
		}
		return $res;
	}

	private static function request($url, $field = null)
	{
		Log::out('url = '.$url);
		if ($url && $url != '') {
			try {
				$str = '';
				$info = file($url);
				foreach ($info as $key => $value) $str .= $value;
				$res = json_decode($str);
				if ($field) $res = $res->$field;
			} catch (Exception $e) {
				$res = false;
			}
			return $res;
		} else return false;
	}

	private static function query($type, $id = null, $param = null)
	{
		$url = false;
		if ($type == 'clan') {
			if ($id != null) {
				$url = self::$serverWoT . 'wgn/clans/info/?clan_id=' . $id;
				if ($param == 'update') $url .= '&fields=updated_at';
			}
		};
		if ($type == 'member' && $id != null) {
			if ($param == null) $url = self::$serverWoT . 'wgn/account/info/?account_id=' . $id;
			if ($param == 'clan') $url = self::$serverWoT . 'wgn/clans/membersinfo/?account_id=' . $id;
			if ($param == 'wot') $url = self::$serverWoT . 'wot/account/info/?account_id=' . $id;
			if ($param == 'wot_update') $url = self::$serverWoT . 'wot/account/info/?fields=updated_at&account_id=' . $id;
			if ($param == 'wot_time') $url = self::$serverWoT . 'wot/account/info/?fields=last_battle_time&account_id=' . $id;
			if ($param == 'wot_technics') $url = self::$serverWoT . 'wot/account/tanks/?account_id=' . $id;
			if ($param == 'wot_medals') $url = self::$serverWoT . 'wot/account/achievements/?account_id=' . $id;
			if ($param == 'wotb') $url = self::$serverWoTB . 'wotb/account/info/?account_id=' . $id;
			if ($param == 'wotb_update') $url = self::$serverWoTB . 'wotb/account/info/?fields=updated_at&account_id=' . $id;
			if ($param == 'wotb_time') $url = self::$serverWoTB . 'wotb/account/info/?fields=last_battle_time&account_id=' . $id;
			if ($param == 'wotb_technics') $url = self::$serverWoTB . 'wotb/tanks/stats/?account_id=' . $id;
			if ($param == 'wotb_medals') $url = self::$serverWoTB . 'wotb/account/achievements/?account_id=' . $id;
			if ($param == 'wowp') $url = self::$serverWoWP . 'wowp/account/info/?account_id=' . $id;
			if ($param == 'wowp_update') $url = self::$serverWoWP . 'wowp/account/info/?fields=updated_at&account_id=' . $id;
			if ($param == 'wowp_time') $url = self::$serverWoWP . 'wowp/account/info/?fields=last_battle_time&account_id=' . $id;
			if ($param == 'wowp_technics') $url = self::$serverWoWP . 'wowp/account/planes/?account_id=' . $id;
			if ($param == 'wowp_medals') $url = self::$serverWoWP . 'wowp/account/info/?account_id=' . $id . '&fields=achievements';
			if ($param == 'wows') $url = self::$serverWoWS . 'wows/account/info/?account_id=' . $id;
			if ($param == 'wows_update') $url = self::$serverWoWS . 'wows/account/info/?fields=stats_updated_at&account_id=' . $id;
			if ($param == 'wows_time') $url = self::$serverWoWS . 'wows/account/info/?fields=last_battle_time&account_id=' . $id;
			if ($param == 'wows_technics') $url = self::$serverWoWS . 'wows/ships/stats/?fields=ship_id%2Cpvp.battles%2Cpvp.wins&account_id=' . $id;
			if ($param == 'wows_medals') $url = self::$serverWoWS . 'wows/account/achievements/?account_id=' . $id;
		};
		if ($type == 'member' && $id == null) {
			if ($param != null) $url = self::$serverWoT . 'wgn/account/list/?search=' . $param . '&limit=10';
		};
		if ($type == 'wot') {
			if ($param == 'info' || $param === null) $url = self::$serverWoT . 'wot/encyclopedia/info/?';
			if ($param == 'technics') {
				$url = self::$serverWoT . 'wot/encyclopedia/vehicles/?fields=name%2Cshort_name%2Cimages.small_icon%2Ctier%2Ctype%2Cnation%2Cis_premium';
			}
			if ($param == 'medals') $url = self::$serverWoT . 'wot/encyclopedia/achievements/?';
		};
		if ($type == 'wotb') {
			if ($param == 'info' || $param === null) $url = self::$serverWoTB . 'wotb/encyclopedia/info/?';
			if ($param == 'technics') {
				$url = self::$serverWoTB . 'wotb/encyclopedia/vehicles/?fields=name%2Cimages.preview%2Ctier%2Ctype%2Cnation%2Cis_premium';
			}
			if ($param == 'medals') $url = self::$serverWoTB . 'wotb/encyclopedia/achievements/?';
		};
		if ($type == 'wowp') {
			if ($param == 'info' || $param === null) $url = self::$serverWoWP . 'wowp/encyclopedia/info/?';
			if ($param == 'technics') $url = self::$serverWoWP . 'wowp/encyclopedia/planes/?';
			if ($param == 'medals') $url = self::$serverWoWP . 'wowp/encyclopedia/achievements/?';
		};
		if ($type == 'wows') {
			if ($param == 'info' || $param === null) $url = self::$serverWoWS . 'wows/encyclopedia/info/?';
			if ($param == 'technics') $url = self::$serverWoWS . 'wows/encyclopedia/ships/?fields=ship_id_str%2Cname%2Cnation%2Ctier%2Ctype%2Cis_premium%2Cdescription%2Cimages.small';
			if ($param == 'medals') $url = self::$serverWoWS . 'wows/encyclopedia/achievements/?';
		};
		if ($url) {
			$url .= (substr($url, -1) == '?' ? '' : '&') . self::$appID;
			return self::wargaming($url);
		} else return $url;
	}

	public static function clan($clan_id, $update = false)
	{
		return self::query('clan', $clan_id, $update ? 'update' : '');
	}

	public static function member($member_id = null, $game = null, $param = null)
	{
		if ($member_id !== null) {
			if (is_numeric($member_id)) {
				if ($game == 'clan') return self::query('member', $member_id, 'clan');
				$game .= $param === null ? '' : '_' . $param;
				return self::query('member', $member_id, $game);
			} else {
				return self::query('member', null, $member_id);
			}
		} else return false;
	}

	public static function game($game = null, $param = null)
	{
		if ($game == 'wot' && $param == 'armor') return self::request('http://armor.kiev.ua/wot/api.php', 'classRatings');
		if ($game == 'wot' && $param == 'etv') return self::request('http://www.wnefficiency.net/exp/expected_tank_values_27.json', 'data');
		return self::query($game, null, $param);
	}
}