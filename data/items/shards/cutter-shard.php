<?
// ITEM : CUTTER SHARD
$item = array(
    'item_name' => 'Cutter Shard',
    'item_token' => 'cutter-shard',
    'item_game' => 'MMRPG',
    'item_group' => 'MMRPG/Items/Cutter',
    'item_class' => 'item',
    'item_subclass' => 'collectible',
    'item_type' => 'cutter',
    'item_description' => 'A mysterious elemental shard that radiates with the Cutter type energy of a defeated support mecha.  Collect four of these items to generate a new core that can be traded in at the shop for a variable amount of Zenny.',
    'item_energy' => 0,
    'item_speed' => 10,
    'item_accuracy' => 100,
    'item_price' => 100,
    'item_target' => 'auto',
    'item_function' => function($objects){
        return rpg_item::item_function_shard($objects);
    }
    );
?>