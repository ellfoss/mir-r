<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 15.09.2016
 * Time: 14:10
 */
class Game
{
	public $game;
	public $data;
	public $info;
	public $technics;
	public $medals;
	public $map_medals;

	function __construct($game)
	{
		$this->game = $game;
		$this->data = Sql::arr_to_list(Sql::game($game, 'data'), 'variable', 'value');
		foreach ($this->data as $var => $val) if (is_string($val) && (substr($val, 0, 1) == '{' || substr($val, 0, 1) == '[')) $this->data[$var] = json_decode($val);
		$medal_list = Sql::game($game, 'medal_list');
		foreach ($medal_list as $num => $item) {
			$this->medals[$item] = new Medal($game, $item);
			$this->map_medals[$this->medals[$item]->name] = $item;
		}
		$technic_list = Sql::game($game, 'technic_list');
		foreach ($technic_list as $num => $item) $this->technics[$item] = new Technic($game, $item);
	}

	public function check()
	{
		Log::add('Проверка игры ' . $this->game);
		if ($this->info = Api::game($this->game)) {
			if ($this->game == 'wot') {
				if (Time::check()) $this->check_effects();
				if (Time::check()) $this->check_etv();
			}
			if (Time::check()) $this->check_medals();
			if (Time::check()) $this->check_technics();
		}
	}

	public function check_stat($member)
	{
		
	}

	private function check_effects()
	{
		$effectsUpdate = date('Y-m-d');
		if ($effectsUpdate != $this->data['colorUpdate']) {
			Log::add('Проверка танковых рейтингов');

			$this->data['colorUpdate'] = $effectsUpdate;
			Sql::game($this->game, 'data', 'colorUpdate', $effectsUpdate);
			$color = array();

			$color['bs']['dm']['color'] = '#FF0000';
			$color['bs']['d1']['color'] = '#FF4400';
			$color['bs']['d2']['color'] = '#FF8800';
			$color['bs']['d3']['color'] = '#FFCC00';
			$color['bs']['c3']['color'] = '#CCFF00';
			$color['bs']['c2']['color'] = '#99FF00';
			$color['bs']['c1']['color'] = '#66FF00';
			$color['bs']['cm']['color'] = '#33FF00';
			$color['bs']['cv']['color'] = '#00FF00';

			$color['bs']['dm']['description'] = 'Мастер-оленевод';
			$color['bs']['d1']['description'] = 'Оленевод 1 степени';
			$color['bs']['d2']['description'] = 'Оленевод 2 степени';
			$color['bs']['d3']['description'] = 'Оленевод 3 степени';
			$color['bs']['c3']['description'] = 'Танкист 3 степени';
			$color['bs']['c2']['description'] = 'Танкист 2 степени';
			$color['bs']['c1']['description'] = 'Танкист 1 степени';
			$color['bs']['cm']['description'] = 'Мастер-танкист';
			$color['bs']['cv']['description'] = 'Виртуоз';

			$color['wr'][0]['value'] = 0;
			$color['wr'][1]['value'] = 46.5;
			$color['wr'][2]['value'] = 48.5;
			$color['wr'][3]['value'] = 51.5;
			$color['wr'][4]['value'] = 56.5;
			$color['wr'][5]['value'] = 64.5;

			$color['wn'][0]['value'] = 0;
			$color['wn'][1]['value'] = 610;
			$color['wn'][2]['value'] = 850;
			$color['wn'][3]['value'] = 1145;
			$color['wn'][4]['value'] = 1475;
			$color['wn'][5]['value'] = 1775;

			$color['wn6'][0]['value'] = 0;
			$color['wn6'][1]['value'] = 410;
			$color['wn6'][2]['value'] = 795;
			$color['wn6'][3]['value'] = 1185;
			$color['wn6'][4]['value'] = 1585;
			$color['wn6'][5]['value'] = 1925;

			$color['wn8'][0]['value'] = 0;
			$color['wn8'][1]['value'] = 370;
			$color['wn8'][2]['value'] = 845;
			$color['wn8'][3]['value'] = 1395;
			$color['wn8'][4]['value'] = 2070;
			$color['wn8'][5]['value'] = 2715;

			$effColor = array(0 => '#FE0E00', 1 => '#FE7903', 2 => '#F8F400', 3 => '#60FF00', 4 => '#02C9B3', 5 => '#D042F3');
			$effname = array(0 => 'Очень плохой игрок', 1 => 'Плохой игрок', 2 => 'Средний игрок', 3 => 'Хороший игрок', 4 => 'Великолепный игрок', 5 => 'Уникум');

			$armor = Api::game('wot', 'armor');
			if ($armor && count($armor) > 1) {
				foreach ($armor as $name => $val) $color['bs'][$name]['value'] = $val;
				foreach ($color as $effect => $names) {
					if ($effect != 'bs') foreach ($color[$effect] as $num => $var) {
						$color[$effect][$num]['color'] = $effColor[$num];
						$color[$effect][$num]['description'] = $effname[$num];
					}
				}
				$value = Sql::i18n(json_encode($color));
				if (Sql::game('wot', 'data', 'effectColor') != $value) {
					$this->data['effectColor'] = json_decode($value);
					if (Sql::game('wot', 'data', 'effectColor', $value)) Log::add('Рейтинги обновлены');
				}
			}
		}
	}

	private function check_etv()
	{
		$etvUpdate = date('Y-m-d');
		if ($etvUpdate != $this->data['etvUpdate']) {
			Log::add('Проверка танковых коэффициентов');
			$this->data['etvUpdate'] = $etvUpdate;
			Sql::game($this->game, 'data', 'etvUpdate', $etvUpdate);
			if ($etv = Api::game('wot', 'etv')) {
				$value = json_encode($etv);
				if (Sql::game('wot', 'data', 'expectedTankValues') != $value) {
					$this->data['expectedTankValues'] = $etv;
					if (Sql::game('wot', 'data', 'expectedTankValues', $value)) Log::add('Коэффициенты обновлены');
				}
			}
		}
	}

	private function check_medals()
	{
		$medalUpdate = date('Y-m-d');
		if ($medalUpdate != $this->data['medalUpdate']) {
			Log::add('Проверка медалей');
			$this->data['medalUpdate'] = $medalUpdate;
			Sql::game($this->game, 'data', 'medalUpdate', $medalUpdate);
			if (isset($this->data['medalSections']) && $this->game != 'wowp') {
				$value = Sql::i18n(json_encode($this->info->achievement_sections));
				if ($value != Sql::i18n(json_encode($this->data['medalSections']))) {
					$this->data['medalSections'] = $this->info->achievement_sections;
					if (Sql::game($this->game, 'data', 'medalSections', $value)) Log::add('Секции медалей обновлены');
				}
			}
			if ($medals = Api::game($this->game, 'medals')) {
				if ($this->game == 'wows') $medals = $medals->battle;
				foreach ($this->medals as $number => $medal) {
					$name = $medal->name;
					if (isset($medals->$name) || isset($medals->battle->$name)) {
						if ($this->game == 'wows') $medal->compare($medals->battle->$name);
						else $medal->compare($medals->$name);
						unset($medals->$name);
					}
				}
				if (count($medals) > 0) {
					foreach ($medals as $name => $medal) {
						$number = Sql::medal($this->game, 'new');
						$number = $number[0]['max_id'] + 1;
						$this->medals[$number] = new Medal($this->game, $number);
						$this->medals[$number]->compare($medal);
						Event::game($this->game, 'new_medal', $number);
						Log::add($this->game . ' new Medal ' . $medal->name);
					}
				}
			}
		}
	}

	private function check_technics()
	{
		$technicUpdate = date('Y-m-d');
		if ($this->game == 'wot' || $this->game == 'wotb') $technicUpdate = date('Y-m-d H:i:s', $this->info->tanks_updated_at);
		if ($technicUpdate != $this->data['technicUpdate']) {
			Log::add('Проверка техники');
			$this->data['technicUpdate'] = $technicUpdate;
			Sql::game($this->game, 'data', 'technicUpdate', $technicUpdate);
			if ($technics = Api::game($this->game, 'technics')) {
				foreach ($this->technics as $number => $technic) {
					if (isset($technics->$number)) {
						$technic->compare($technics->$number);
						unset($technics->$number);
					}
				}
				if (count($technics) > 0) {
					foreach ($technics as $number => $technic) {
						$this->technics[$number] = new Technic($this->game, $number);
						$this->technics[$number]->compare($technic);
						Event::game($this->game, 'new_technic', $number);
						Log::add($this->game . ' new Technic ' . $technic->name);
					}
				}
			}
		}
	}
}