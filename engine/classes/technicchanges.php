<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 19.09.2016
 * Time: 15:25
 */
class TechnicChanges extends Technic
{
	function __construct($game, $id)
	{
		$this->game = $game;
		$this->id = $id;
		$this->set_changes();
	}

	public function set_changes()
	{
		$changes = Sql::technic_changes($this->game, $this->id);
		if ($changes) $this->set_data($changes[0]);
	}

	public function unset_field($field)
	{
		if (isset($this->$field)) {
			$this->$field = null;
			Sql::technic_changes($this->game, $this->id, $field);
		}
	}
}