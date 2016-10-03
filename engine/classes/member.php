<?php

/**
 * Created by PhpStorm.
 * User: KZS-Kashnikov-DS
 * Date: 13.09.2016
 * Time: 10:20
 */
class Member
{
	public $id;
	public $name;
	public $rights;
	public $real_name;
	public $reg_date;
	public $check_clan;
	public $reg_in_clan;
	public $clan;
	public $role;
	public $games;
	public $state;
	public $wot_update;
	public $wotb_update;
	public $wowp_update;
	public $wows_update;
	public $color;
	private $sync = array(
		'name' => 'name',
		'rights' => 'rights',
		'real_name' => 'rName',
		'reg_date' => 'regDate',
		'check_clan' => 'checkClan',
		'clan' => 'clan',
		'reg_in_clan' => 'regInClan',
		'role' => 'role',
		'games' => 'games',
		'wot_update' => 'wotUpdate',
		'wotb_update' => 'wotbUpdate',
		'wowp_update' => 'wowpUpdate',
		'wows_update' => 'wowsUpdate',
		'color' => 'color'
	);

	function __construct($id)
	{
		$this->id = $id;
		if (!$this->get_sql_data()) $this->check_member(true);
	}

	public function get_sql_data()
	{
		$data = Sql::member($this->id);
		$data = $data[0];
		if ($data) {
			foreach ($this->sync as $key => $value) {
				$this->$key = $data[$value];
				if ($key == 'games') {
					$games = json_decode($data[$value]);
					$this->games = array();
					foreach ($games as $num => $game) {
						$this->games[$game] = '';
						$this->state($game);
					}
				}
			}

			return true;
		} else return false;
	}

	public function check_member($new = false)
	{
		Log::add('Проверка игрока ' . $this->name);
		$id = $this->id;
		if ($member = Api::member($this->id)) {
			if ($member->$id) {
				Log::out('Данные игрока ' . $this->name . ' получены');
				$this->name = $member->$id->nickname;
				$this->reg_date = date('Y-m-d H:i:s', $member->$id->created_at);
				$this->games = $member->$id->games;

				$this->check_clan($new);
			}
		}
	}

	private function save_member($new = false)
	{
		if ($new) {
			if (Sql::member($this->id, 'new', $this)) Event::member($this->id, 'new');
		} else {
			$member = Sql::member($this->id);
			$member = $member[0];
			foreach ($this as $key => $value) {
				if (array_key_exists($this->sync[$key], $member) && $key != 'games' && $member[$this->sync[$key]] != $value) {
					Sql::member($this->id, 'change', $this->sync[$key], $value);
					switch ($key) {
						case 'name':
							Event::member($this->id, 'name', $member['name']);
							Log::add('Игрок ' . $this->id . ' изменил имя на ' . $this->name);
							break;
						case 'clan':
							if ($this->clan) {
								Event::member($this->id, 'clan', $this->clan);
								Log::add('Игрок ' . $this->name . ' принят в клан ' . $this->clan);
							} else {
								Event::member($this->id, 'outclan', $member['clan']);
								Log::add('Игрок ' . $this->name . ' вышел из клана ' . $member['clan']);
							}
							break;
						case 'role':
							Event::member($this->id, 'role', $this->role);
							Log::add('Игрок ' . $this->name . ' получил должность ' . $this->role);
							break;
					}
				}
				if ($key == 'games') {
					$val = json_encode($this->games);
					if ($val != $member[$key]) Sql::member($this->id, 'change', $this->sync[$key], $val);
				}
			}
		}
	}

	public function check_clan($new = false)
	{
		Log::out('Проверка клана игрока ' . $this->name);
		$id = $this->id;
		if ($member = Api::member($id, 'clan')) {
			if ($member->$id) {
				Log::out('Игрок ' . $this->name . ' в клане ' . $member->$id->clan->tag);
				$this->clan = $member->$id->clan->clan_id;
				Clan::check_clan($member->$id->clan->clan_id);
				$this->reg_in_clan = date('Y-m-d H:i:s', $member->$id->joined_at);
				$this->role = $member->$id->role;
				Clan::check_role($member->$id->role, $member->$id->role_i18n);
				if (!$this->rights || $this->rights == 'guest') {
					$this->rights = 'guest';
					if (Clan::get_main_clan_id() == $this->clan) $this->rights = 'member';
				}
			} else {
				Log::out('Игрок ' . $this->name . ' вне клана ');
				$this->clan = null;
				$this->reg_in_clan = null;
				$this->role = null;
				$this->rights = 'guest';
			}
		}
		$update = Api::member($id, 'update');
		$this->check_clan = date('Y-m-d H:i:s', $update->$id->updated_at);
		$this->save_member($new);
	}

	public function state($game)
	{
		if (isset($this->games[$game])) {
			$this->games[$game] = new State($this, $game);
		}
	}

	public function check_state($game)
	{
		$old_stat = new State($this, $game, 'yesterday');
		$new_stat = new State($this, $game, 'api');
		$date = Sql::stat($this->id, $game, 'full');
		$today = date('Y-m-d');
		$type = 'part';
		if (!$date || substr($today, -2) == '01' || substr($date, 0, 7) != substr($today, 0, 7) || $date == $today) $type = 'full';
		$old_stat->compare($new_stat, $type);
	}
}