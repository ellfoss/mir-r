<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 19.09.2016
 * Time: 10:53
 */
class Technic
{
	public $game;
	public $id;
	public $ship_id;
	public $name;
	public $short_name;
	public $name_ru;
	public $image;
	public $level;
	public $type;
	public $nation;
	public $is_premium;
	public $description;
	public $state;
	public $changes;

	function __construct($game, $id)
	{
		$this->game = $game;
		$this->id = $id;
		if ($data = Sql::technic($game, $id)) $this->set_data($data[0]);
		else $this->new_technic();
		$this->changes = new TechnicChanges($this->game, $this->id);
	}

	private function new_technic(){
		$this->state = 'new';
		Sql::technic($this->game, $this->id, 'state', 'new');
	}

	protected function set_data($data)
	{
		if ($data != null) {
			foreach ($data as $key => $value) {
				$fld = $this->field($key);
				if (property_exists($this, $fld)) $this->$fld = $value;
			}
		}
	}

	public function compare($technic)
	{
		foreach ($this as $field => $value) {
			$fld = $field;
			if ($this->game != 'wowp' && $field == 'level') $fld = 'tier';
			if ($field == 'name_ru') $fld = 'name_i18n';
			if ($field == 'ship_id') $fld = 'ship_id_str';
			if (isset($technic->$fld) || $field == 'image') {
				$new_value = str_replace(array(chr(10), chr(13)), '', nl2br($technic->$fld));
				if ($this->game == 'wot' && $field == 'image') $new_value = $technic->images->small_icon;
				if ($this->game == 'wotb' && $field == 'image') $new_value = $technic->images->preview;
				if (($this->game == 'wowp' || $this->game == 'wows') && $field == 'image') $new_value = $technic->images->small;
				if ($fld == 'is_premium') {
					if ($technic->$fld) $new_value = '1';
					else $new_value = '0';
				}
				if ($value != $new_value) {
					if ($this->state == null || $this->state == 'chn') {
						$this->changes->set_change($field, $new_value);
						$this->set_change('state', 'chn');
					} else $this->set_change($field, $new_value);
				}
			}
		}
	}

	public function set_change($field, $value)
	{
		$fld = $this->field($field);
		if (get_class($this) == 'TechnicChanges') $res = Sql::technic_changes($this->game, $this->id, $fld, $value);
		else $res = Sql::technic($this->game, $this->id, $fld, $value);
		if ($res) {
			$this->$field = $value;
			return true;
		} else return false;
	}

	public function accept_changes($field)
	{
		foreach ($this as $key => $value) {
			if ($key != 'id' && ($field == 'all' || $field == $key)) {
				if (isset($this->changes->$key) && $this->changes->$key != null) {
					$this->set_change($key, $this->changes->$key);
					$this->changes->unset_field($key);
				}
			}
		}
	}

	private function field($field, $sql = true)
	{
		$fields = array(
			'short_name' => 'shortName',
			'name_ru' => 'nameRu',
			'ship_id' => 'shipID',
			'is_premium' => 'isPrem'
		);
		if ($sql) if (isset($fields[$field])) return $fields[$field];
		else {
			foreach ($fields as $num => $name) if ($name == $field) return $num;
		}
		return $field;
	}
}