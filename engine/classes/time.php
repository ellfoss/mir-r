<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 20.09.2016
 * Time: 10:20
 */
class Time
{
	public static $start_time;
	private static $max_time = 50;

	public static function start(){
		self::$start_time = time();
	}

	public static function script_time(){
		return time() - self::$start_time;
	}

	public static function check(){
		if(!self::$start_time) self::start();
		if(self::script_time() < self::$max_time) return true;
		else return false;
	}
}