<?
// ITEM : WEAPON UPGRADE
$ability = array(
  'ability_name' => 'Weapon Upgrade',
  'ability_token' => 'item-weapon-upgrade',
  'ability_game' => 'MM00',
  'ability_group' => 'MM00/Items/Upgrades',
  'ability_class' => 'item',
  'ability_subclass' => 'holdable',
  'ability_type' => 'weapons',
  'ability_description' => 'A mysterious drive containing some kind of weapon upgrade program.  When held by a robot master, this item doubles the user\'s maximum weapon energy in battle.',
  'ability_energy' => 0,
  'ability_speed' => 10,
  'ability_accuracy' => 100,
  'ability_function' => function($objects){

    // Return true on success
    return true;

  }
  );
?>