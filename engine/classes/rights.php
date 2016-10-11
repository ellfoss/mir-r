<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 10.10.2016
 * Time: 14:42
 */
class Rights
{
	private static $right = 'guest';
	private static $right_list = array('0' => 'guest', '1' => 'member', '2' => 'admin', '3' => 'sadmin');

	public static function set($new_right)
	{
		foreach (self::$right_list as $number => $right) {
			if ($right == $new_right) self::$right = $right;
		}
	}

	public static function member()
	{
		if (self::$right == 'member' || self::$right == 'admin' || self::$right == 'sadmin') return true;
		else return false;
	}

	public static function admin()
	{
		if (self::$right == 'admin' || self::$right == 'sadmin') return true;
		else return false;
	}

	public static function samin()
	{
		if (self::$right == 'sadmin') return true;
		else return false;
	}

	public static function compare($right)
	{
		$current = 0;
		$compare = 0;
		foreach (self::$right_list as $number => $value) {
			if ($value == self::$right) $current = $number;
			if ($value == $right) $compare = $number;
		}
		if ($current > $compare) return true;
		else return false;
	}
}