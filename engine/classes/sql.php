<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 09.09.2016
 * Time: 11:01
 */
class Sql
{
	private static $host = 'localhost';
	private static $user = 'kashni_den';
	private static $password = 'wWhYOhDP5r';
	private static $base = 'kashni_mirr';
	private static $sql = false;

	function __construct()
	{
		$this->connect();
	}

	private static function connect()
	{
		if (!self::$sql) {
			self::$sql = new mysqli(self::$host, self::$user, self::$password, self::$base);
			if (self::$sql->connect_error) self::$sql = false;
			else self::$sql->set_charset('utf8');
		}
	}

	private static function query($query)
	{
		if (!self::$sql) self::connect();
		if (self::$sql) {
			$res = self::$sql->query($query);
			if ($res) {
				if ($res === true) return $res;
				return $res->fetch_all(MYSQLI_ASSOC);
			} else return false;
		} else return false;
	}

	public static function arr_to_list($arr, $field, $val = null)
	{
		if (!$arr) return $arr;
		$list = array();
		foreach ($arr as $key => $value) {
			if ($val) {
				if (is_array($value)) $list[$value[$field]] = $value[$val];
				if (is_object($value)) $list[$value->$field] = $value->$val;
			} else {
				if (is_array($value)) $list[] = $value[$field];
				if (is_object($value)) $list[] = $value->$field;
			}
		}
		return $list;
	}

	public static function get_roles()
	{
		$query = "SELECT * FROM `data` WHERE `variable` = 'memberRole'";
		$res = self::query($query);
		if (!$res) return $res;
		$roles = json_decode($res['value']);
		return $roles;
	}

	public static function update_roles($roles)
	{
		$value = self::i18n(json_encode($roles));
		$query = "UPDATE `data` SET `value` = '$value' WHERE `variable` = 'memberRoles'";
		return self::query($query);
	}

	public static function data()
	{
		$query = "SELECT * FROM `data`";
		return self::arr_to_list(self::query($query), 'variable', 'value');
	}

	public static function member_list($clan = null)
	{
		$query = "SELECT `id` FROM `members`" . ($clan === null ? "" : " WHERE `clan` = '$clan'");
		return self::arr_to_list(self::query($query), 'id');
	}

	public static function member($id = null, $clan = null, $field = null, $value = null)
	{
		if ($clan && $clan == 'new' && $field instanceof Member) {
			$new_data = array();
			$new_data['id'] = $field->id;
			$new_data['name'] = $field->name;
			$new_data['rights'] = $field->rights;
			$new_data['regDate'] = $field->reg_date;
			$new_data['games'] = json_encode($field->games);
			$var = "`" . implode("`, `", array_keys($new_data)) . "`";
			$val = "'" . implode("', '", array_values($new_data)) . "'";
			$query = "INSERT INTO `members` ($var) VALUES ($val)";
			return self::query($query);
		} elseif ($clan && $clan == 'change' && $field && $value !== null) {
			$query = "UPDATE `members` SET `$field` = '$value' WHERE `id` = '$id'";
			return self::query($query);
		} else $query = "SELECT * FROM `members`" . ($id === null ? "" : " WHERE `id` = '$id'") . ($clan === null ? "" : ($id === null ? " WHERE" : " AND") . " `clan` = '$clan'");
		return self::query($query);
	}

	public static function clan_list()
	{
		$query = "SELECT `id` FROM `clans`";
		return self::arr_to_list(self::query($query), 'id');
	}

	public static function clans($clan = null, $field = null, $value = null)
	{
		if (!$field) {
			$query = "SELECT * FROM `clans`" . ($clan === null ? "" : "WHERE `id` = '$clan'");
			return self::query($query);
		} else {
			if ($field == 'new') {
				$var = "`" . implode("`, `", array_keys($value)) . "`";
				$val = "'" . implode("', '", array_values($value)) . "'";
				$query = "INSERT INTO `clans` ($var) VALUES ($val)";
				return self::query($query);
			} elseif ($clan && $value) {
				$query = "UPDATE `clans` SET `$field` = '$value' WHERE `id` = '$clan'";
				return self::query($query);
			} else return false;
		}
	}

	public static function visitors()
	{
		$query = "SELECT * FROM `visitors`";
		return self::query($query);
	}

	public static function event($options)
	{
		if (isset($options['initDate']) || isset($options['endDate'])) {
			$query = "SELECT * FROM `events` WHERE " . (isset($options['initDate']) ? " `date` >= '" . $options['initDate'] . "'" : "") . (isset($options['endDate']) ? (isset($options['initDate']) ? " AND" : "") . " `date` >= '" . $options['endDate'] . "'" : "");
			return self::query($query);
		} else {
			$date = date('Y-m-d');
			$time = date('H:i:s');
			$game = $type = $id = $event = "NULL";
			if (isset($options['clan'])) {
				$game = "clan";
				$id = $options['clan']['id'];
				$type = $options['clan']['type'];
				$event = $options['clan']['event'];
			}
			if (isset($options['member'])) {
				$game = "member";
				$id = $options['member']['id'];
				$type = $options['member']['type'];
				$event = $options['clan']['event'];
			}
			$query = "INSERT INTO `events` (`date`, `time`, `game`, `type`, `id`, `event`) VALUES ('$date', '$time', '$game', '$type', '$id', '$event')";
			return self::query($query);
		}
	}

	public static function game($game, $type = null, $opt = null, $val = null)
	{
		$table = $game . ($type === null ? '' : '_' . $type);
		if ($opt === null) {
			if ($type == 'medal' || $type == 'technic') $table .= 's';
		} else {
			if ($type != 'stat') $table .= '_' . $opt;
		}
		$query = "SELECT * FROM `$table`";
		if ($type == 'stat' && gettype($opt) == 'array') $query .= " WHERE `date` >= '" . $opt['init'] . "' AND `date` <= '" . $opt['fin'] . "'" . (isset($opt['member']) ? " AND `id` = '" . $opt['member'] . "'" : "");
		if ($type == 'medal_list') return self::arr_to_list(self::query("SELECT `id` FROM `" . $game . "_medals`"), 'id');
		if ($type == 'technic_list') return self::arr_to_list(self::query("SELECT `id` FROM `" . $game . "_technics`"), 'id');
		if($type == 'data'){
			if($opt) {
				if ($val) $query = "UPDATE `" . $game . "_data` SET `value` = '$val' WHERE `variable` = '$opt'";
				else $query = "SELECT * FROM `" . $game . "_data` WHERE `variable` = '$opt'";
			}else $query = "SELECT * FROM `$table`";
		}
		return self::query($query);
	}

	public static function medal($game, $id, $type = null)
	{
		if ($type) {
			if ($type == 'options') $query = "SELECT * FROM `" . $game . "_medal_options` WHERE `id` = '$id'";
			if ($type == 'changes') $query = "SELECT * FROM `" . $game . "_medal_changes` WHERE `id` = '$id'";
		} else $query = "SELECT * FROM `" . $game . "_medals` WHERE `id` = '$id'";
		return self::query($query);
	}

	public static function technic($game, $id, $type = null)
	{
		if ($type && $type == 'changes') $query = "SELECT * FROM `" . $game . "_technic_changes` WHERE `id` = '$id'";
		else $query = "SELECT * FROM `" . $game . "_technics` WHERE `id` = '$id'";
		return self::query($query);
	}

	public static function i18n($str)
	{
		if (gettype($str) == 'string') {
			$i = 0;
			while ($i < 300) {
				$ps = $str;
				if ($pos = stripos($ps, '\u')) {
					$ps = substr($ps, $pos);
					$pos = stripos($ps, '"');
					$k = substr($ps, 0, $pos);
					$h = '{"string":"' . $k . '"}';
					$g = json_decode($h);
					$str = str_replace($k, $g->string, $str);
				} else $i = 300;
				$i++;
			};
			$str = str_replace(chr(9), ' ', $str);
			$str = str_replace(chr(10), '<br />', $str);
			$str = str_replace(chr(13), '', $str);
		};
		return $str;
	}
}