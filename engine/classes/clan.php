<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 13.09.2016
 * Time: 10:04
 */
class Clan
{
	public $id;
	public $tag;
	public $name;
	public $color;
	public $emblem;
	public $motto;
	public $update_time;
	public $created;
	public $in_list = false;
	public $main = false;
	public $members = array();
	private static $main_id;
	private static $roles = array();

	function __construct($id = null)
	{
		$data = Sql::data();
		if (!$id) $id = $data['id'];
		$this->id = $id;
		$clan = Sql::clans($id);
		if ($clan) {
			$this->tag = $clan[0]['tag'];
			$this->name = $clan[0]['name'];
			$this->emblem = $clan[0]['emblem'];
			$this->color = $clan[0]['color'];
			$this->created = $clan[0]['created'];
			$this->motto = $clan[0]['motto'];
			$this->update_time = $clan[0]['updateTime'];
			$this->in_list = true;
			if ($id == $data['id']) {
				$this->main = true;
				$list = Sql::member_list($id);
				Log::add('Проверка списка игроков текущего клана');
				foreach ($list as $num => $item) $this->members[$item] = new Member($item);
			}
		} else $this->check();
	}

	public static function check_role($role, $role_ru)
	{
		if (!count(self::$roles)) self::$roles = Sql::get_roles();
		if (isset(self::$roles[$role])) {
			if (self::$roles[$role] != $role_ru) {
				self::$roles[$role] = $role_ru;
				Sql::update_roles(self::$roles);
			}
		} elseif ($role != null && $role_ru != null) {
			self::$roles[$role] = $role_ru;
			Sql::update_roles(self::$roles);
		}
	}

	public static function get_main_clan_id()
	{
		if (!self::$main_id) {
			$data = Sql::data();
			self::$main_id = $data['id'];
		}
		return self::$main_id;
	}

	public static function check_clan($clan)
	{
		$list = Sql::clan_list();
		foreach ($list as $num => $item) if ($item == $clan) return true;
		return new Clan($clan);
	}

	public function check()
	{
		Log::add('Проверка клана ' . $this->tag);
		$id = $this->id;
		if ($clan = Api::clan($id)) {
			if($clan->$id) {
				$new_data = array();
				$new_data['id'] = $id;
				$new_data['tag'] = $clan->$id->tag;
				$new_data['name'] = $clan->$id->name;
				$new_data['color'] = $clan->$id->color;
				$new_data['emblem'] = $clan->$id->emblems->x64->portal;
				$new_data['created'] = date('Y-m-d H:i:s', $clan->$id->created_at);
				$new_data['motto'] = $clan->$id->motto;
				if ($this->in_list) {
					foreach ($this as $key => $value) {
						if (isset($new_data[$key]) && $value != $new_data[$key]) {
							$this->$key = $new_data[$key];
							if (Sql::clans($id, $key, $new_data[$key])) {
								Event::clan($id, 'field', $key);
								switch ($key) {
									case 'tag':
										Log::add('У клана ' . $id . ' новый тэг ' . $new_data['tag']);
										break;
									case 'name':
										Log::add('У клана ' . $new_data['tag'] . ' новое имя ' . $new_data['name']);
										break;
									case 'color':
										Log::add('У клана ' . $new_data['tag'] . ' новый цвет ' . $new_data['color']);
										break;
									case 'emblem':
										Log::add('У клана ' . $new_data['tag'] . ' новая эмблема ' . $new_data['emblem']);
										break;
									case 'motto':
										Log::add('У клана ' . $new_data['tag'] . ' новый девиз ' . $new_data['motto']);
										break;
								}
							}
						}
					}
				} else {
					if (Sql::clans($id, 'new', $new_data)) {
						Event::clan($id, 'new');
						Log::add('Новый клан ' . $new_data['tag']);
					}
				}
				Sql::clans($id, 'updateTime', date('Y-m-d H:i:s', $clan->$id->updated_at));
				if ($this->main) {
					$list = Sql::arr_to_list($clan->$id->members, 'account_id');
					foreach ($list as $num => $item) if (isset($this->members[$item])) unset($list[$num]);
					if (count($list)) foreach ($list as $num => $item) {
						$member = new Member($item);
						$this->members[] = $member;
						Event::clan($id, 'newMember', $item);
						Log::add('В клане новый игрок ' . $member->name);
					}
				}
			}
		}
	}
}