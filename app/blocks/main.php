<?php
/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 13.10.2016
 * Time: 16:27
 */

include '../engine/main.php';

$clan = new Clan(Clan::get_main_clan_id());

$main = file_get_contents('main.html');
$main = str_replace('{TAG}', $clan->tag, $main);
$main = str_replace('{NAME}', $clan->name, $main);
$main = str_replace('{MOTTO}', $clan->motto, $main);
$main = str_replace('{COLOR}', $clan->color, $main);

echo $main;