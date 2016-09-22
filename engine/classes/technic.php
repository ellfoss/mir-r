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
	public $is_prem;
	public $description;
	public $state;
	public $changes;

	function __construct($game, $id)
	{
		$this->game = $game;
		$this->id = $id;
		$data = Sql::technic($game, $id);
		$this->set_data($data);
		$changes = Sql::technic($game, $id, 'changes');
		if($changes) $this->set_changes($changes);
	}

	protected function set_data($data){
		foreach ($data[0] as $key => $value) {
			if (property_exists($this, $key)) $this->$key = $value;
			else {
				if ($key == 'shortName') $this->short_name = $value;
				if ($key == 'nameRu') $this->name_ru = $value;
				if ($key == 'shipID') $this->ship_id = $value;
				if ($key == 'isPrem') $this->is_prem = $value;
			}
		}
	}

	private function set_changes($changes){
		$this->changes = new TechnicChanges($this->game, $this->id, $changes);
	}
}