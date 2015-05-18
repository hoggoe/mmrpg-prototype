<?
// EXPLODE BUSTER
$ability = array(
  'ability_name' => 'Explode Buster',
  'ability_token' => 'explode-buster',
  'ability_game' => 'MMRPG',
  'ability_group' => 'MMRPG/Weapons/04/Explode',
  'ability_description' => 'The user charges itself with Explode type energy on the first turn to increase its elemental abilities, then releases a powerful explosive shot at the target on the second to inflict massive damage!',
  'ability_type' => 'explode',
  'ability_energy' => 2,
  'ability_damage' => 30,
  'ability_recovery2' => 10,
  'ability_recovery2_percent' => true,
  'ability_accuracy' => 100,
  'ability_target' => 'auto',
  'ability_function' => function($objects){

    // Call the common buster function from here
    return mmrpg_ability::ability_function_buster($objects, 'explosive', 'blasted', 'invigorated');

    },
  'ability_function_onload' => function($objects){

    // Call the common buster onload function from here
    return mmrpg_ability::ability_function_onload_buster($objects);

    }
  );
?>