<?
// ITEM : ATTACK STAR
$item = array(
    'item_name' => 'Attack Star',
    'item_token' => 'attack-star',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Attack',
    'item_class' => 'item',
    'item_subclass' => 'holdable',
    'item_type' => 'attack',
    'item_description' => 'A mysterious elemental star that radiates with the Attack type energy of a distant planet.  These items have no effect in battle, but are loved by a certain character and can be traded in for an impressive amount of Zenny.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_price' => 6000,
    'item_target' => 'auto',
    'item_function' => function($objects){
        return rpg_item::item_function_core($objects);
    }
    );
?>