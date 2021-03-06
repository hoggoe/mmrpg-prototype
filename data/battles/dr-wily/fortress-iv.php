<?
// PROTOTYPE BATTLE 5 : VS MASTERS
$battle = array(
  'battle_name' => 'Chapter Five Final Battle 3/3',
  'battle_size' => '1x4',
  'battle_encore' => true,
  'battle_description' => 'Defeat the powered up army of dark Robot Master clones and download their data!',
  'battle_turns' => (MMRPG_SETTINGS_BATTLETURNS_PERROBOT * 8),
  'battle_points' => (MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * 50 * 8),
  'battle_field_base' => array('field_id' => 100, 'field_token' => 'final-destination-3', 'field_name' => 'Final Destination III', 'field_music' => 'final-destination', 'field_mechas' => array('batton-3', 'crazy-cannon-3', 'fan-fiend-3', 'killer-bullet-3', 'pierrobot-3', 'snapper-3', 'spring-head-3', 'telly-3')),
  'battle_target_player' => array(
    'player_id' => MMRPG_SETTINGS_TARGET_PLAYERID,
    'player_token' => 'player',
    'player_robots' => array(
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 1), 'robot_token' => 'air-man', 'robot_level' => 50, 'robot_abilities' => array('air-shooter')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 2), 'robot_token' => 'heat-man', 'robot_level' => 50, 'robot_abilities' => array('atomic-fire')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 3), 'robot_token' => 'crash-man', 'robot_level' => 50, 'robot_abilities' => array('crash-bomber')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 4), 'robot_token' => 'quick-man', 'robot_level' => 50, 'robot_abilities' => array('quick-boomerang')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 5), 'robot_token' => 'metal-man', 'robot_level' => 50, 'robot_abilities' => array('metal-blade')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 6), 'robot_token' => 'wood-man', 'robot_level' => 50, 'robot_abilities' => array('leaf-shield')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 7), 'robot_token' => 'bubble-man', 'robot_level' => 50, 'robot_abilities' => array('bubble-spray', 'bubble-lead')),
      array('robot_id' => (MMRPG_SETTINGS_TARGET_PLAYERID + 8), 'robot_token' => 'flash-man', 'robot_level' => 50, 'robot_abilities' => array('flash-stopper'))
      )
    ),
  'battle_rewards' => array(
    'abilities' => array(
      ),
    'items' => array(
      )
    )
  );
?>