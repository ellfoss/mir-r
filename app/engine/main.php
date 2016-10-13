<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 09.09.2016
 * Time: 12:15
 */

spl_autoload_register(function ($class_name) {
	spl_autoload_extensions('.php');
	spl_autoload('classes/' . mb_strtolower($class_name));
});

