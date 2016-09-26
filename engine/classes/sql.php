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
			if (isset($options['memb'])) {
				$game = "memb";
				$id = $options['memb']['id'];
				$type = $options['memb']['type'];
				$event = $options['memb']['event'];
			}
			if (isset($options['game'])) {
				$game = $options['game']['game'];
				$id = $options['game']['id'];
				$type = '';
				$event = $options['game']['event'];
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
		if ($type == 'data') {
			if ($opt) {
				if ($val) $query = "UPDATE `" . $game . "_data` SET `value` = '$val' WHERE `variable` = '$opt'";
				else $query = "SELECT * FROM `" . $game . "_data` WHERE `variable` = '$opt'";
			} else $query = "SELECT * FROM `$table`";
		}
		return self::query($query);
	}

	public static function medal($game, $medal, $field = null, $value = null)
	{
		$table = $game . '_medals';
		return self::medal_query($table, $medal, $field, $value);
	}

	public static function medal_changes($game, $medal, $field = null, $value = null)
	{
		$table = $game . '_medal_changes';
		$res = self::medal_query($table, $medal, $field, $value);
		self::check_medal_changes($table, $medal);
		return $res;
	}

	private static function check_medal_changes($table, $medal)
	{
		if ($res = self::query("SELECT * FROM `$table` WHERE `id` = '$medal'")) {
			$clear = true;
			foreach ($res as $field => $value) if ($value != null) $clear = false;
			if ($clear) {
				if (self::query("DELETE FROM `$table` WHERE `id` = '$medal'")) {
					$table = substr($table, 0, -8) . 's';
					return self::query("UPDATE `$table` SET `state` = NULL WHERE `id` = '$medal'");
				} else return false;
			} else return false;
		} else return true;
	}

	public static function medal_options($game, $option, $field = null, $value = null)
	{
		$table = $game . "_medal_options";
		if ($option == 'numbers') {
			$numbers = array();
			$num = 1;
			while (count($numbers) < 4) {
				while ($rs = self::query("SELECT `id` FROM `$table` WHERE `id` = '$num'")) $num++;
				$numbers[] = $num;
				$num++;
			}
			return $numbers;
		} elseif ($field == 'delete') {
			return self::query("DELETE FROM `$table` WHERE `id` = '$option'");
		} else return self::medal_query($table, $option, $field, $value);
	}

	private static function medal_query($table, $id, $field = null, $value = null)
	{
		if ($field) {
			if (self::medal_query($table, $id)) {
				if (is_array($field)) {
					foreach ($field as $num => $item) $field[$num] = "`" . $item . "` = " . self::format_value($value[$num]);
					$field = implode(",", $field);
				} else $field = "`$field` = " . self::format_value($value);
				$query = "UPDATE `$table` SET $field WHERE `id` = '$id'";
			} else {
				if (is_array($field)) {
					$field = "`" . implode("`,`", $field) . "`";
					$value = "'" . implode("','", $value) . "'";
				} else {
					$field = "`" . $field . "`";
					$value = self::format_value($value);
				}
				$query = "INSERT INTO `$table` (`id`, $field) VALUES ('$id', $value)";
			}
		} else {
			$query = "SELECT * FROM `$table` WHERE `id` = '$id'";
			if ($id == 'new') $query = "SELECT MAX(`id`) AS `max_id` FROM `$table`";
		}
		return self::query($query);
	}

	public static function technic($game, $technic, $field = null, $value = null)
	{
		$table = $game . '_technics';
		return self::technic_query($table, $technic, $field, $value);
	}

	public static function technic_changes($game, $technic, $field = null, $value = null)
	{
		$table = $game . '_technic_changes';
		$res = self::technic_query($table, $technic, $field, $value);
		self::check_technic_changes($table, $technic);
		return $res;
	}

	private static function check_technic_changes($table, $technic)
	{
		if ($res = self::query("SELECT * FROM `$table` WHERE `id` = '$technic'")) {
			$clear = true;
			foreach ($res as $field => $value) if ($value != null) $clear = false;
			if ($clear) {
				if (self::query("DELETE FROM `$table` WHERE `id` = '$technic'")) {
					$table = substr($table, 0, -8) . 's';
					return self::query("UPDATE `$table` SET `state` = NULL WHERE `id` = '$technic'");
				} else return false;
			} else return false;
		} else return true;
	}

	private static function technic_query($table, $id, $field = null, $value = null)
	{
		if ($field) {
			if (self::technic_query($table, $id)) {
				if (is_array($field)) {
					foreach ($field as $num => $item) $field[$num] = "`" . $item . "` = " . self::format_value($value[$num]);
					$field = implode(",", $field);
				} else $field = "`$field` = " . self::format_value($value);
				$query = "UPDATE `$table` SET $field WHERE `id` = '$id'";
			} else {
				if (is_array($field)) {
					$field = "`" . implode("`,`", $field) . "`";
					$value = "'" . implode("','", $value) . "'";
				} else {
					$field = "`" . $field . "`";
					$value = self::format_value($value);
				}
				$query = "INSERT INTO `$table` (`id`, $field) VALUES ('$id', $value)";
			}
		} else {
			$query = "SELECT * FROM `$table` WHERE `id` = '$id'";
			if ($id == 'new') $query = "SELECT MAX(`id`) AS `max_id` FROM `$table`";
		}
		return self::query($query);
	}

	private static function format_value($value)
	{
		if ($value !== null) return "'" . $value . "'";
		else return "NULL";
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