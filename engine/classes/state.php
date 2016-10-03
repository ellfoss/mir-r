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
	private $fields = array(
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

	function __construct($member, $game, $type = false)
	{
		$this->id = $member->id;
		$this->game = $game;
		if ($type == 'api') $this->set_state($type);
		elseif (Sql::stat($member->id, $game, 'check')) $this->set_state();
	}

	function set_state($type = false)
	{
		if ($type == 'api') {
			$stat = Api::member($this->id, $this->game);
			$id = $this->id;
			$stat = $stat->$id;
			foreach ($this as $field => $value) {
				$api_field = $this->field($field, 'api');
				if ($api_field) {
					$val = $this->get_object_field($stat, $api_field);
					$this->$field = $val;
				}
			}
		} else {
			$date = Sql::stat($this->id, $this->game, 'full');
			$stats = Sql::stat($this->id, $this->game, $date, 'all', $type == 'yesterday' ? true : false);
			foreach ($stats as $num => $stat) {
				foreach ($this as $field => $value) {
					$sql_field = $this->field($field, 'sql');
					if (isset($stat[$sql_field])) {
						if ($stat['type'] == 'full') $this->$field = 1 * $stat[$sql_field];
						if ($stat['type'] == 'part') $this->$field += $stat[$sql_field];
					}
				}
			}
		}
	}

	public function compare($state, $type)
	{
		$today = date('Y-m-d');
		foreach ($state as $field => $value) {
			if ($state->$field != $this->$field) {
				$this->check_stat($today, $type);
				$val = $state->$field;
				if ($type == 'part' && $this->$field != null) $val = $state->$field - $this->$field;
				Sql::stat($this->id, $this->game, $today, $this->field($field, 'sql'), $val);
			}
		}
	}

	private function field($field, $type, $reverse = false)
	{
		if ($reverse) {
			foreach ($this->fields as $num => $fld) {
				if (isset($fld[$this->game])) {
					if ($fld[$this->game][$type] == $field) return $fld;
				}
			}
		} else {
			if (isset($this->fields[$field][$this->game])) return $this->fields[$field][$this->game][$type];
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
}