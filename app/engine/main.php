<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 09.09.2016
 * Time: 12:15
 */

function __autoload($class_name)
{
	include_once('classes/' . $class_name . '.php');
}

