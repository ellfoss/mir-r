<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 17.09.2016
 * Time: 9:06
 */
class MedalChanges extends Medal
{
	function __construct($game, $id, $data)
	{
		$this->game = $game;
		$this->id = $id;
		$this->set_data($data);
	}
}