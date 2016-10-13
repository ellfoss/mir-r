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

	public static function close()
	{
		if (self::$sql) self::$sql->close();
	}

	public static function query($query)
	{
		if (!self::$sql) self::connect();
		if (self::$sql) {
			$res = self::$sql->query($query);
			if ($res) {
				if ($res === true) return $res;
				$out = array();
				while ($row = $res->fetch_assoc()) $out[] = $row;
				return $out;
			} else return false;
		} else return false;
	}

	public static function arr_to_list($arr, $field, $val = null)
	{
		if (!$arr) return $arr;
		$list = array();
		foreach ($arr as $key => $value) {
			if ($val) {
				if (is_array($value)) $list[$value[$field]] = self::json($value[$val]);
				if (is_object($value)) $list[$value->$field] = self::json($value->$val);
			} else {
				if (is_array($value)) $list[] = self::json($value[$field]);
				if (is_object($value)) $list[] = self::json($value->$field);
			}
		}
		return $list;
	}

	public static function list_to_arr($list, $field)
	{
		if (!$list) return $list;
		$arr = array();
		foreach ($list as $key => $value) {
			if (is_array($value)) $arr[$value[$field]] = $value;
			if (is_object($value)) $arr[$value->$field] = $value;
		}
		return $arr;
	}

	private static function json($value)
	{
		if (is_string($value) && (substr($value, 0, 1) == '{' || substr($value, 0, 1) == '[')) return json_decode($value);
		else return $value;
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
		$query = $query = "SELECT * FROM `members`" . ($id ? " WHERE `id` = '$id'" : "") . ($clan ? ($id === null ? " WHERE" : " AND") . " `clan` = '$clan'" : "");
		switch ($clan) {
			case 'new':
				if ($field instanceof Member) {
					$new_data = array();
					$new_data['id'] = $field->id;
					$new_data['name'] = $field->name;
					$new_data['rights'] = $field->rights;
					$new_data['regDate'] = $field->reg_date;
					$new_data['games'] = json_encode($field->games);
					$var = "`" . implode("`, `", array_keys($new_data)) . "`";
					$val = "'" . implode("', '", array_values($new_data)) . "'";
					$query = "INSERT INTO `members` ($var) VALUES ($val)";
				}
				break;

			case 'change':
				if ($field && $value) {
					$query = "UPDATE `members` SET `$field` = '$value' WHERE `id` = '$id'";
				}
				break;

			case 'delete':
				$query = "DELETE FROM `members`";
				break;

			case 'sessionID':
				$query = "SELECT * FROM `members` WHERE `sessionID` = '$clan'";
				break;
		}
		return self::query($query);
	}

	public static function member_rights($id, $session_id = null)
	{
		$rights = 'guest';
		$query = null;
		$res = null;
		if ($id !== null) $query = "SELECT `rights` FROM `members` WHERE `id` = '$id'";
		elseif ($session_id !== null) $query = "SELECT `rights` FROM `members` WHERE `sessionID` = '$session_id'";
		if ($query) $res = self::query($query);
		if ($res) $rights = $res[0]['rights'];
		Rights::set($rights);
		return $rights;
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

	public static function visitors($clear = false)
	{
		$date = new DateTime();
		$date->modify('-1 month');
		$date = $date->format('Y-m-d');
		if ($clear) $query = "DELETE FROM `visitors` WHERE `date` < '$date'";
		else $query = "SELECT * FROM `visitors`";
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

	public static function stat($member, $game, $date, $field = null, $value = null)
	{
		$table = $game . '_stat';
		switch ($date) {
			case 'check':
				$query = "SELECT * FROM `$table` WHERE `id` = '$member'";
				return self::query($query);
				break;

			case 'full':
				$query = "SELECT `date` FROM `$table` WHERE `id` = '$member' AND `type` = 'full' ORDER BY `date` DESC LIMIT 2";
				$res = self::query($query);
				if ($res) {
					if ($field === true) return $res[1]['date'];
					else return $res[0]['date'];
				} else return false;
				break;

			default:
				if ($field) {
					$today = date('Y-m-d');
					switch ($field) {
						case 'all':
							if ($value) $query = "SELECT * FROM `$table` WHERE `id` = '$member' AND `date` >= '$date' AND `date` < '$value'";
							else $query = "SELECT * FROM `$table` WHERE `id` = '$member' AND `date` >= '$date'";
							break;

						case 'full':
							$query = "SELECT `date` FROM `$table` WHERE `id` = '$member' AND `type` = 'full' AND `date` <= '$date' ORDER BY `date` DESC LIMIT 1";
							$res = self::query($query);
							if ($res) return $res[0]['date'];
							else return false;
							break;

						default:
							if (self::query("SELECT * FROM `$table` WHERE `id` = '$member' AND `date` = '$date'")) {
								if (is_array($field)) {
									foreach ($field as $num => $item) $field[$num] = "`" . $item . "` = " . self::format_value($value[$num]);
									$field = implode(",", $field);
								} else $field = "`$field` = " . self::format_value($value);
								$query = "UPDATE `$table` SET $field WHERE `id` = '$member' AND `date` = '$date'";
							} else {
								if (is_array($field)) {
									$field = "`" . implode("`,`", $field) . "`";
									$value = "'" . implode("','", $value) . "'";
								} else {
									$field = "`" . $field . "`";
									$value = self::format_value($value);
								}
								$query = "INSERT INTO `$table` (`date`, `id`, $field) VALUES ('$date', '$member', $value)";
							}
					}
				} else $query = "SELECT * FROM `$table` WHERE `id` = '$member' AND `date` = '$date'";
				return self::query($query);
		}
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

	public static function visit($id)
	{
		$today = new DateTime();
		$date = $today->format('Y-m-d');
		$time = $today->format('H:i:s');
		$ip = $_SERVER['REMOTE_ADDR'];
		$cookie = session_id();
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$visits = array();
		$update = false;
		if ($visitor = Sql::query("SELECT * FROM `visitors` WHERE `date` = '$date' AND `member` = '$id' AND `ip` = '$ip' AND `cookie` = '$cookie' AND `browser` = '$browser'")) {
			$visits = json_decode($visitor['visits']);
			$update = true;
		}
		$visits[] = $time;
		$str_visits = json_encode($visits);
		if ($update) $query = "UPDATE `visitors` SET `visits` = '$str_visits' WHERE `date` = '$date' AND `member` = '$id' AND `ip` = '$ip' AND `cookie` = '$cookie' AND `browser` = '$browser'";
		else $query = "INSERT INTO `visitors` (`date`, `ip`, `cookie`, `member`, `visits`, `browser`) VALUES ('$date', '$ip', '$cookie', $id, '$str_visits', '$browser')";
	}
}