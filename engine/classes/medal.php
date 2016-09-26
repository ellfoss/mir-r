<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 16.09.2016
 * Time: 14:37
 */
class Medal
{
	public $game;
	public $id;
	public $name;
	public $name_ru;
	public $order;
	public $my_order;
	public $type;
	public $sub_type;
	public $section;
	public $reward;
	public $count_per_battle;
	public $is_progress;
	public $max_progress;
	public $image;
	public $description;
	public $condition;
	public $options;
	public $view;
	public $state;
	public $changes;

	function __construct($game, $id)
	{
		$this->game = $game;
		$this->id = $id;
		if ($data = Sql::medal($game, $id)) $this->set_data($data[0]);
		else $this->new_medal();
		$this->changes = new MedalChanges($this->game, $this->id);
	}

	private function new_medal(){
		$this->state = 'new';
		Sql::medal($this->game, $this->id, 'state', 'new');
	}

	protected function set_data($data)
	{
		if ($data != null) {
			foreach ($data as $key => $value) {
				if (property_exists($this, $key)) $this->$key = $value;
				else {
					if ($key == 'nameRu') $this->name_ru = $value;
					if ($key == 'myOrder') $this->my_order = $value;
					if ($key == 'countPerBattle') $this->count_per_battle = $value;
					if ($key == 'isProgress') $this->is_progress = $value;
					if ($key == 'maxProgress') $this->max_progress = $value;
				}
				if ($key == 'options' && $value != null) {
					$this->options = array();
					$value = json_decode($value);
					foreach ($value as $num => $item) {
						$option = Sql::medal_options($this->game, $item);
						$this->options[$item] = array(
							'name' => $option[0]['name'],
							'image' => $option[0]['image']
						);
					}
				}
			}
		}
	}

	public function compare($medal)
	{
		foreach ($this as $field => $value) {
			$fld = $field;
			if (($this->game == 'wot' || $this->game == 'wowp') && $field == 'name_ru') $fld = 'name_i18n';
			if (($this->game == 'wotb' || $this->game == 'wows') && $field == 'name') $fld = 'achievement_id';
			if (($this->game == 'wotb' || $this->game == 'wows') && $field == 'name_ru') $fld = 'name';
			if (isset($medal->$fld)) {
				$change = false;
				$new_value = is_array($medal->$fld) ? $medal->$fld : str_replace(array(chr(10), chr(13)), '', nl2br($medal->$fld));
				if ($field == 'options') {
					if ($new_value != null) {
						if (is_array($value)) {
							$numbers = $this->options_numbers();
							for ($num = 0; $num < 4; $num++) {
								$option = $this->options[$numbers[$num]];
								$new_name = $medal->options[$num]->name;
								if ($this->game == 'wot') $new_name = $medal->options[$num]->name_i18n;
								$new_image = $medal->options[$num]->image;
								if ($option['name'] != $new_name || $option['image'] != $new_image) $change = true;
							}
						} else $change = true;
					}
				} elseif ($value != $new_value) $change = true;
				if ($change) {
					if ($this->state == null || $this->state == 'chn') {
						$this->changes->set_change($field, $new_value);
						$this->set_change('state', 'chn');
					} else $this->set_change($field, $new_value);
				}
			}
		}
	}

	public function set_change($field, $value)
	{
		$fld = $this->field($field);
		if ($fld == 'options') {
			if (!$this->options) $this->set_options();
			$numbers = $this->options_numbers();
			for ($num = 0; $num < 4; $num++) {
				$option = $this->options[$numbers[$num]];
				foreach ($option as $option_field => $option_value) {
					$option_fld = $option_field;
					if ($option_field == 'name' && $this->game == 'wot') $option_fld = 'name_i18n';
					$new_option_value = $value[$num]->$option_fld;
					if ($option_value != $new_option_value) {
						if (Sql::medal_options($this->game, $numbers[$num], $option_field, $option_value))
							$this->options[$num][$option_field] = $new_option_value;
					}
				}
			}
		} else {
			if (get_class($this) == 'MedalChanges') $res = Sql::medal_changes($this->game, $this->id, $fld, $value);
			else $res = Sql::medal($this->game, $this->id, $fld, $value);
			if ($res) {
				$this->$field = $value;
				return true;
			} else return false;
		}
		return false;
	}

	public function accept_changes($field)
	{
		foreach ($this as $key => $value) {
			if ($key != 'id' && ($field == 'all' || $field == $key)) {
				if (isset($this->changes->$key) && $this->changes->$key != null) {
					$this->set_change($key, $this->changes->$key);
					$this->changes->unset_field($key);
				}
			}
		}
	}

	private function set_options()
	{
		$numbers = Sql::medal_options($this->game, 'numbers');
		foreach ($numbers as $num) {
			$this->options[$num] = array('name' => null, 'image' => null);
			foreach ($this->options[$num] as $field => $value) Sql::medal_options($this->game, $num, $field, $value);
		}
		Sql::medal($this->game, $this->id, 'options', json_encode($this->options_numbers()));
	}

	private function options_numbers()
	{
		if ($this->options) {
			$numbers = array();
			foreach ($this->options as $num => $option) $numbers[] = $num;
			return $numbers;
		} else return false;
	}

	private function field($field, $sql = true)
	{
		$fields = array(
			'name_ru' => 'nameRu',
			'my_order' => 'myOrder',
			'count_per_battle' => 'countPerBattle',
			'is_progress' => 'isProgress',
			'max_progress' => 'maxProgress'
		);
		if ($sql) if (isset($fields[$field])) return $fields[$field];
		else {
			foreach ($fields as $num => $name) if ($name == $field) return $num;
		}
		return $field;
	}
}

