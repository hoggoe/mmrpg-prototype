<?
// MEGA SLIDE
$ability = array(
    'ability_name' => 'Mega Slide',
    'ability_token' => 'mega-slide',
    'ability_game' => 'MM03',
    'ability_group' => 'MM00/Weapons/Mega',
    'ability_description' => 'The user quickly slides toward the target at blinding speed, inflicting damage with a {RECOVERY2}% chance of critical hit!',
    'ability_type' => '',
    'ability_energy' => 6,
    'ability_speed' => 6,
    'ability_damage' => 40,
    'ability_recovery2' => 60,
    'ability_recovery2_percent' => true,
    'ability_accuracy' => 100,
    'ability_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target the opposing robot
        $this_ability->target_options_update(array(
            'frame' => 'slide',
            'kickback' => array(150, 0, 0),
            'success' => array(0, 25, 0, -10, $this_robot->print_name().' uses '.$this_ability->print_name().'!')
            ));
        $this_robot->trigger_target($target_robot, $this_ability);

        // Inflict damage on the opposing robot
        $this_ability->damage_options_update(array(
            'kind' => 'energy',
            'kickback' => array(60, 0, 0),
            'rates' => array('auto', 'auto', $this_ability->ability_recovery2),
            'success' => array(1, -65, 0, 10, 'The '.$this_ability->print_name().' crashes into the target!'),
            'failure' => array(0, -85, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $this_ability->recovery_options_update(array(
            'kind' => 'energy',
            'frame' => 'taunt',
            'kickback' => array(20, 0, 0),
            'rates' => array('auto', 'auto', $this_ability->ability_recovery2),
            'success' => array(1, -35, 0, 10, 'The '.$this_ability->print_name().' was absorbed by the target!'),
            'failure' => array(1, -65, 0, -10, 'The '.$this_ability->print_name().' missed the target&hellip;')
            ));
        $energy_damage_amount = $this_ability->ability_damage;
        $target_robot->trigger_damage($this_robot, $this_ability, $energy_damage_amount);

        // Return true on success
        return true;

    }
    );
?>