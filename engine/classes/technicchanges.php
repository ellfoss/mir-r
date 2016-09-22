<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 19.09.2016
 * Time: 15:25
 */
class TechnicChanges extends Technic
{
	function __construct($game, $id, $data)
	{
		$this->game = $game;
		$this->id = $id;
		$this->set_data($data);
	}
}