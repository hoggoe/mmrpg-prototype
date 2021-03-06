<?
// BLADE MAN
$robot = array(
    'robot_number' => 'DWN-073',
    'robot_game' => 'MM10',
    'robot_name' => 'Blade Man',
    'robot_token' => 'blade-man',
    'robot_core' => 'cutter',
    'robot_description' => 'Sword Sparring Robot',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('missile', 'earth'),
    'robot_resistances' => array('wind'),
    'robot_abilities' => array(
        'triple-blade',
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
                array('level' => 0, 'token' => 'triple-blade')
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