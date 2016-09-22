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
		$data = Sql::medal($game, $id);
		$this->set_data($data);
		$changes = Sql::medal($game, $id, 'changes');
		if ($changes) $this->set_changes($changes);
	}

	protected function set_data($data)
	{
		foreach ($data[0] as $key => $value) {
			if (property_exists($this, $key)) $this->$key = $value;
			else {
				if ($key == 'nameRu') $this->name_ru = $value;
				if ($key == 'myOrder') $this->my_order = $value;
				if ($key == 'countPerBattle') $this->count_per_battle = $value;
				if ($key == 'isProgress') $this->is_progress = $value;
				if ($key == 'maxProgress') $this->max_progress = $value;
			}
			if ($key == 'options' && $value != null) {
				$this->options = json_decode($value);
				foreach ($this->options as $num => $item) {
					$option = Sql::medal($this->game, $item, 'options');
					$this->options[$num] = array(
						'name' => $option[0]['name'],
						'image' => $option[0]['image']
					);
				}
			}
		}
	}

	private function set_changes($changes)
	{
		$this->changes = new MedalChanges($this->game, $this->id, $changes);
	}

	public function compare($medal)
	{
		foreach ($this as $field => $value) {
			$fld = $field;
			if ($this->game == 'wot' && $field == 'name_ru') $fld = 'name_i18n';
			if (isset($medal->$fld)) {
				$new_value = str_replace('\n', '<br />', $medal->$fld);
				if ($value != $new_value) {
					if ($field == 'options') {
						foreach ($this->options as $num => $option) {
							foreach ($option as $option_field => $option_value) {
								$option_fld = $option_field;
								if ($option_field == 'name') $option_fld = 'name_i18n';
								$new_option_value = $medal->options[$num]->$option_fld;
								if ($option_value != $new_option_value) {
									$this->options[$num][$option_field] = $new_option_value;
								}
							}
						}
					} else {
						$this->$field = $new_value;
					}
				}
			}
		}
	}
}

