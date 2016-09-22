<?php

/**
 * Created by PhpStorm.
 * User: KZS-Kashnikov-DS
 * Date: 13.09.2016
 * Time: 15:35
 */
class Event
{
	public static function clan($id, $type, $event = null)
	{
		$options = array();
		$options['clan'] = array();
		$options['clan']['id'] = $id;
		$options['clan']['type'] = $type;
		$options['clan']['event'] = $event || '';
		Sql::event($options);
	}

	public static function member($id, $type, $event = null){
		$options = array();
		$options['memb'] = array();
		$options['memb']['id'] = $id;
		$options['memb']['type'] = $type;
		$options['memb']['event'] = $event || '';
		Sql::event($options);
	}
}