<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 10.10.2016
 * Time: 14:42
 */
class Rights
{
	private static $rights = 'guest';
	private static $right_list = array('guest', 'member', 'admin', 'sadmin');

	public static function set($new_right)
	{
		foreach (self::$right_list as $right) {
			if ($right == $new_right) self::$rights = $right;
		}
	}

	public static function member(){
		if(self::$rights == 'member' || self::$rights == 'admin' || self::$rights == 'sadmin') return true;
		else return false;
	}

	public static function admin(){
		if(self::$rights == 'admin' || self::$rights == 'sadmin') return true;
		else return false;
	}

	public static function samin(){
		if(self::$rights == 'sadmin') return true;
		else return false;
	}
}