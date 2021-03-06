<?
// SOLAR MAN
$robot = array(
    'robot_number' => 'DWN-080',
    'robot_game' => 'MM10',
    'robot_name' => 'Solar Man',
    'robot_token' => 'solar-man',
    'robot_image_editor' => 3842,
    'robot_core' => 'flame',
    'robot_description' => 'Titanic Fireball Robot',
    'robot_energy' => 100,
    'robot_attack' => 100,
    'robot_defense' => 100,
    'robot_speed' => 100,
    'robot_weaknesses' => array('water', 'space'),
    'robot_resistances' => array('flame', 'freeze'),
    'robot_abilities' => array(
        'solar-blaze',
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
                array('level' => 0, 'token' => 'solar-blaze')
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