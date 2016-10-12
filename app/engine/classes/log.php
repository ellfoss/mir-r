<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 15.09.2016
 * Time: 15:05
 */
class Log
{
	private static $log = PHP_EOL;
	private static $debug = false;

	public static function set_debug($debug){
		self::$debug = $debug;
	}

	private static function format($text){
		return date('H:i:s', time()).' '.$text.PHP_EOL;
	}

	public static function add($text)
	{
		self::$log .= self::format($text);
		self::out($text);
	}

	public static function out($text){
		if(self::$debug) echo self::format($text);
	}

	public static function end(){
		self::$log .= PHP_EOL;
		self::save();
	}

	public static function save(){
		$dir = '../log';
		if(!(file_exists($dir))) mkdir($dir);
		$name = $dir.'/'.date('Y-m-d', time()).'.txt';
		$fp = fopen($name, 'a');
		fwrite($fp, self::$log);
		fclose($fp);
	}
}