<?php

/**
 * Created by PhpStorm.
 * User: ellfoss
 * Date: 28.09.2016
 * Time: 10:19
 */
class State
{
	public $id;
	public $game;
	public $date;
	public $battles;
	public $wins;
	public $losses;
	public $survived;
	public $xp;
	public $damage;
	public $damage_received;
	public $frags;
	public $spotted;
	public $capture;
	public $dropped;
	public $hits;
	public $shots;
	public $objects;
	public $structure;
	public $bases;
	public $turrets;
	public $assists;
	public $technics;
	public $medals;
	private static $fields = array(
		'battles' => array(
			'wot' => array(
				'sql' => 'battles',
				'api' => 'statistics.all.battles'
			),
			'wotb' => array(
				'sql' => 'battles',
				'api' => 'statistics.all.battles'
			),
			'wowp' => array(
				'sql' => 'battles',
				'api' => 'statistics.battles'
			),
			'wows' => array(
				'sql' => 'battles',
				'api' => 'statistics.pvp.battles'
			)
		),
		'wins' => array(
			'wot' => array(
				'sql' => 'wins',
				'api' => 'statistics.all.wins'
			),
			'wotb' => array(
				'sql' => 'wins',
				'api' => 'statistics.all.wins'
			),
			'wowp' => array(
				'sql' => 'wins',
				'api' => 'statistics.wins'
			),
			'wows' => array(
				'sql' => 'wins',
				'api' => 'statistics.pvp.wins'
			)
		),
		'losses' => array(
			'wot' => array(
				'sql' => 'losses',
				'api' => 'statistics.all.losses'
			),
			'wotb' => array(
				'sql' => 'losses',
				'api' => 'statistics.all.losses'
			),
			'wowp' => array(
				'sql' => 'losses',
				'api' => 'statistics.losses'
			),
			'wows' => array(
				'sql' => 'losses',
				'api' => 'statistics.pvp.losses'
			)
		),
		'survived' => array(
			'wot' => array(
				'sql' => 'survived',
				'api' => 'statistics.all.survived_battles'
			),
			'wotb' => array(
				'sql' => 'survived',
				'api' => 'statistics.all.survived_battles'
			),
			'wowp' => array(
				'sql' => 'survived',
				'api' => 'statistics.survived_battles'
			),
			'wows' => array(
				'sql' => 'survived',
				'api' => 'statistics.pvp.survived_battles'
			)
		),
		'xp' => array(
			'wot' => array(
				'sql' => 'xp',
				'api' => 'statistics.all.xp'
			),
			'wotb' => array(
				'sql' => 'xp',
				'api' => 'statistics.all.xp'
			),
			'wowp' => array(
				'sql' => 'xp',
				'api' => 'statistics.xp'
			),
			'wows' => array(
				'sql' => 'xp',
				'api' => 'statistics.pvp.xp'
			)
		),
		'damage' => array(
			'wot' => array(
				'sql' => 'damageD',
				'api' => 'statistics.all.damage_dealt'
			),
			'wotb' => array(
				'sql' => 'damageD',
				'api' => 'statistics.all.damage_dealt'
			),
			'wowp' => array(
				'sql' => 'damage',
				'api' => 'statistics.damage_dealt.total'
			),
			'wows' => array(
				'sql' => 'damage',
				'api' => 'statistics.pvp.damage_dealt'
			)
		),
		'damage_received' => array(
			'wot' => array(
				'sql' => 'damageR',
				'api' => 'statistics.all.damage_received'
			),
			'wotb' => array(
				'sql' => 'damageR',
				'api' => 'statistics.all.damage_received'
			)
		),
		'frags' => array(
			'wot' => array(
				'sql' => 'frags',
				'api' => 'statistics.all.frags'
			),
			'wotb' => array(
				'sql' => 'frags',
				'api' => 'statistics.all.frags'
			),
			'wowp' => array(
				'sql' => 'frags',
				'api' => 'statistics.frags.total'
			),
			'wows' => array(
				'sql' => 'frags',
				'api' => 'statistics.pvp.frags'
			)
		),
		'spotted' => array(
			'wot' => array(
				'sql' => 'spotted',
				'api' => 'statistics.all.spotted'
			),
			'wotb' => array(
				'sql' => 'spotted',
				'api' => 'statistics.all.spotted'
			)
		),
		'capture' => array(
			'wot' => array(
				'sql' => 'capture',
				'api' => 'statistics.all.capture_points'
			),
			'wotb' => array(
				'sql' => 'capture',
				'api' => 'statistics.all.capture_points'
			),
			'wows' => array(
				'sql' => 'capture',
				'api' => 'statistics.pvp.capture_points'
			)
		),
		'dropped' => array(
			'wot' => array(
				'sql' => 'dropped',
				'api' => 'statistics.all.dropped_capture_points'
			),
			'wotb' => array(
				'sql' => 'dropped',
				'api' => 'statistics.all.dropped_capture_points'
			),
			'wows' => array(
				'sql' => 'dropped',
				'api' => 'statistics.pvp.dropped_capture_points'
			)
		),
		'hits' => array(
			'wot' => array(
				'sql' => 'hits',
				'api' => 'statistics.all.hits'
			),
			'wotb' => array(
				'sql' => 'hits',
				'api' => 'statistics.all.hits'
			),
			'wowp' => array(
				'sql' => 'hits',
				'api' => 'statistics.hits.total'
			)
		),
		'shots' => array(
			'wot' => array(
				'sql' => 'shots',
				'api' => 'statistics.all.shots'
			),
			'wotb' => array(
				'sql' => 'shots',
				'api' => 'statistics.all.shots'
			),
			'wowp' => array(
				'sql' => 'shots',
				'api' => 'statistics.shots.total'
			)
		),
		'objects' => array(
			'wowp' => array(
				'sql' => 'objectsD',
				'api' => 'statistics.ground_objects_destroyed.total'
			)
		),
		'structure' => array(
			'wowp' => array(
				'sql' => 'structureD',
				'api' => 'statistics.structure_damage.total'
			)
		),
		'bases' => array(
			'wowp' => array(
				'sql' => 'basesD',
				'api' => 'statistics.team_objects_destroyed.total'
			)
		),
		'turrets' => array(
			'wowp' => array(
				'sql' => 'turretsD',
				'api' => 'statistics.turrets_destroyed.total'
			)
		)
	);

	function __construct($member, $game, $type = false, $date = null)
	{
		if ($member instanceof Member) $this->id = $member->id;
		else $this->id = $member;
		$this->game = $game;
		$this->date = new DateTime($date);
		if ($type == 'api'){
			$this->set_state($type);
			$this->date = new DateTime();
		}
		elseif (Sql::stat($this->id, $game, 'check')) $this->set_state($type);
	}

	public function set_state($type = false)
	{
		if ($type == 'api') {
			$stat = Api::member($this->id, $this->game);
			$id = $this->id;
			$stat = $stat->$id;
			if ($stat !== null) {
				foreach ($this as $field => $value) {
					$api_field = $this->field($field, 'api');
					if ($api_field) {
						$val = $this->get_object_field($stat, $api_field);
						$this->$field = $val;
					}
				}
				$technics = Api::member($id, $this->game, 'technics');
				if ($technics->$id !== null) {
					$technics = $technics->$id;
					$this->technics = array();
					foreach ($technics as $num => $technic) {
						if ($this->game == 'wot') {
							$this->technics[$technic->tank_id][0] = $technic->statistics->battles;
							$this->technics[$technic->tank_id][1] = $technic->statistics->wins;
							$this->technics[$technic->tank_id][2] = $technic->mark_of_mastery;
						}
						if ($this->game == 'wotb') {
							$this->technics[$technic->tank_id][0] = $technic->all->battles;
							$this->technics[$technic->tank_id][1] = $technic->all->wins;
							$this->technics[$technic->tank_id][2] = $technic->mark_of_mastery;
						}
						if ($this->game == 'wowp') {
							$this->technics[$technic->plane_id][0] = $technic->battles;
							$this->technics[$technic->plane_id][1] = $technic->wins;
						}
						if ($this->game == 'wows') {
							$this->technics[(string)$technic->ship_id][0] = $technic->pvp->battles;
							$this->technics[(string)$technic->ship_id][1] = $technic->pvp->wins;
						}
					}
					ksort($this->technics);
				}
				$medals = Api::member($id, $this->game, 'medals');
				if ($medals->$id !== null) {
					if ($this->game == 'wows') $medals = $medals->$id->battle;
					else $medals = $medals->$id->achievements;
					$this->medals = array();
					foreach ($medals as $name => $value) {
						if (isset(Game::$map_medals[$this->game])) {
							if (isset(Game::$map_medals[$this->game][$name])) {
								$medal = $this->game == 'wowp' ? $value->name : $name;
								$val = $this->game == 'wowp' ? $value->number : $value;
								$this->medals[Game::$map_medals[$this->game][$medal]] = $val;
							}
						}
					}
					ksort($this->medals);
				}
			}
		} else {
			$date = $this->date->format('Y-m-d');
			$date = Sql::stat($this->id, $this->game, $date, $type == 'yesterday' ? true : false);
			$stats = Sql::stat($this->id, $this->game, $date, 'all', $type == 'yesterday' ? true : false);
			foreach ($stats as $num => $stat) {
				foreach ($this as $field => $value) {
					if ($field == 'technics') {
						if (!$this->technics) $this->technics = array();
						$list = json_decode($stat['technics']);
						if ($list) foreach ($list as $num => $technic) {
							if (!isset($this->technics[$num])) $this->technics[$num] = array();
							foreach ($technic as $n => $v) {
								if ($stat['type'] == 'full') $this->technics[$num][$n] = 1 * $v;
								if ($stat['type'] == 'part') $this->technics[$num][$n] += $v;
							}
						}
					} elseif ($field == 'medals') {
						if (!$this->medals) $this->medals = array();
						$list = json_decode($stat['medals']);
						if ($list) foreach ($list as $medal => $count) {
							if (!isset($this->medals[$medal])) $this->medals[$medal] = 0;
							if ($stat['type'] == 'full') $this->medals[$medal] = 1 * $count;
							if ($stat['type'] == 'part') $this->medals[$medal] += $count;
						}
					} else {
						$sql_field = $this->field($field, 'sql');
						if (isset($stat[$sql_field])) {
							if ($stat['type'] == 'full') $this->$field = 1 * $stat[$sql_field];
							if ($stat['type'] == 'part') $this->$field += $stat[$sql_field];
						}
					}
				}
			}
			if ($this->technics !== null) ksort($this->technics);
			if ($this->medals !== null) ksort($this->medals);
		}
	}

	public function compare($state, $type)
	{
		$today = date('Y-m-d');
		$fields = array();
		$values = array();
		foreach ($state as $field => $value) {
			if ($field != 'id' && $field != 'game') {
				$old_value = $this->$field;
				$new_value = $state->$field;
				if ($field == 'technics' || $field == 'medals') {
					$old_value = json_encode($this->$field);
					$new_value = json_encode($state->$field);
				}
				if ($old_value != $new_value || $type == 'full') {
					$this->check_stat($today, $type);
					if ($type == 'part' && $this->$field != null) {
						if ($field == 'technics') {
							foreach ($state->technics as $num => $technic) {
								if (isset($this->technics[$num])) {
									if ($this->technics[$num][0] != $state->technics[$num][0]) {
										foreach ($state->technics[$num] as $n => $v) $state->technics[$num][$n] -= $this->technics[$num][$n];
									} else unset($state->technics[$num]);
								}
							}
							$new_value = json_encode($state->technics);
						} elseif ($field == 'medals') {
							foreach ($state->medals as $medal => $val) {
								if (isset($this->medals[$medal])) {
									if ($this->medals[$medal] != $state->medals[$medal]) {
										$state->medals[$medal] -= $this->medals[$medal];
									} else unset($state->medals[$medal]);
								}
							}
							$new_value = json_encode($state->medals);
						} else $new_value -= $old_value;
					}
					if ($field == 'technics' || $field == 'medals') {
						$fields[] = $field;
						$values[] = $new_value;
					} else {
						$new_field = $this->field($field, 'sql');
						if ($new_field) {
							$fields[] = $new_field;
							$values[] = $new_value;
						}
					}
				}
			}
		}
		if (count($fields) > 0) Sql::stat($this->id, $this->game, $today, $fields, $values);
	}

	private function field($field, $type, $reverse = false)
	{
		if ($reverse) {
			foreach (self::$fields as $num => $fld) {
				if (isset($fld[$this->game])) {
					if ($fld[$this->game][$type] == $field) return $fld;
				}
			}
		} else {
			if (isset(self::$fields[$field][$this->game])) return self::$fields[$field][$this->game][$type];
			else return false;
		}
		return false;
	}

	private function get_object_field($object, $field)
	{
		$keys = explode('.', $field);
		if (count($keys) == 1) return $object->$keys[0];
		if (count($keys) == 2) return $object->$keys[0]->$keys[1];
		if (count($keys) == 3) return $object->$keys[0]->$keys[1]->$keys[2];
		return $object;
	}

	private function check_stat($date, $type)
	{
		$stat = Sql::stat($this->id, $this->game, $date);
		if (!$stat || count($stat) == 0 || $stat['type'] != $type) Sql::stat($this->id, $this->game, $date, 'type', $type);
	}

	public function out()
	{
		$out = array();
		foreach (self::$fields as $field => $value) {
			if (isset($value[$this->game])) $out[$field] = $this->$field;
		}
		$out['medals'] = $this->medals;
		$out['technics'] = $this->technics;
		return $out;
	}
}