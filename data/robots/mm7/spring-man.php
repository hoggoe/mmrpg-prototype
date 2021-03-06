<?
// SPRING MAN
$robot = array(
    'robot_number' => 'DWN-053',
    'robot_game' => 'MM07',
    'robot_name' => 'Spring Man',
    'robot_token' => 'spring-man',
    'robot_core' => 'impact',
    'robot_description' => 'Bouncing Coil Robot',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('cutter', 'flame'),
    'robot_resistances' => array('impact'),
    'robot_immunities' => array('electric'),
    'robot_abilities' => array(
        'wild-coil',
        'buster-shot',
        'attack-boost', 'attack-break', 'attack-swap', 'attack-mode',
        'defense-boost', 'defense-break', 'defense-swap', 'defense-mode',
        'speed-boost', 'speed-break', 'speed-swap', 'speed-mode',
        'energy-boost', 'energy-break', 'energy-swap', 'energy-mode',
        'field-support', 'mecha-support',
        'light-buster', 'wily-buster', 'cossack-buster'
        ),
    'robot_rewards' => array(
        'abilities' => array(
                array('level' => 0, 'token' => 'wild-coil')
            )
        ),
    'robot_quotes' => array(
        'battle_start' => '',
        'battle_taunt' => '',
        'battle_victory' => '',
        'battle_defeat' => ''
        )
    );
?>