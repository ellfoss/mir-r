<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 17.09.2016
 * Time: 9:06
 */
class MedalChanges extends Medal
{
	function __construct($game, $id)
	{
		$this->game = $game;
		$this->id = $id;
		$this->set_changes();
	}

	public function set_changes()
	{
		$changes = Sql::medal_changes($this->game, $this->id);
		if ($changes) $this->set_data($changes);
	}
}