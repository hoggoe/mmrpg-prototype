<?

// ROBOT MASTER DATABASE

// Define the index of counters for robot types
$mmrpg_database_robots_types = array('cores' => array(), 'weaknesses' => array(), 'resistances' => array(), 'affinities' => array(), 'immunities' => array());
foreach ($mmrpg_database_types AS $token => $info){
    $mmrpg_database_robots_types['cores'][$token] = 0;
    $mmrpg_database_robots_types['weaknesses'][$token] = 0;
    $mmrpg_database_robots_types['resistances'][$token] = 0;
    $mmrpg_database_robots_types['affinities'][$token] = 0;
    $mmrpg_database_robots_types['immunities'][$token] = 0;
}

// Define the index of hidden robots to not appear in the database
$hidden_database_robots = array();
//$hidden_database_robots = array_merge($hidden_database_robots, array('bomb-man', 'cut-man', 'elec-man', 'fire-man', 'guts-man', 'ice-man', 'oil-man', 'time-man'));
//$hidden_database_robots = array_merge($hidden_database_robots, array('air-man', 'bubble-man', 'crash-man', 'flash-man', 'heat-man', 'metal-man', 'quick-man', 'wood-man'));
//$hidden_database_robots = array_merge($hidden_database_robots, array('needle-man', 'magnet-man', 'gemini-man', 'hard-man', 'top-man', 'snake-man', 'spark-man', 'shadow-man'));
$hidden_database_robots_count = !empty($hidden_database_robots) ? count($hidden_database_robots) : 0;


// Define the hidden robot query condition
$temp_condition = '';
$temp_condition .= "AND robot_class = 'master' ";
if (!empty($hidden_database_robots)){
    $temp_tokens = array();
    foreach ($hidden_database_robots AS $token){ $temp_tokens[] = "'".$token."'"; }
    $temp_condition .= 'AND robot_token NOT IN ('.implode(',', $temp_tokens).') ';
}
// If additional database filters were provided
$temp_condition_unfiltered = $temp_condition;
if (isset($mmrpg_database_robots_filter)){
    if (!preg_match('/^\s?(AND|OR)\s+/i', $mmrpg_database_robots_filter)){ $temp_condition .= 'AND ';  }
    $temp_condition .= $mmrpg_database_robots_filter;
}

// Collect the database robots and fields
$field_fields = rpg_field::get_index_fields(true);
$robot_fields = rpg_robot::get_index_fields(true);
$db->query("SET @robot_row_number = 0;");
$mmrpg_database_fields = $db->get_array_list("SELECT {$field_fields} FROM mmrpg_index_fields WHERE field_flag_published = 1;", 'field_token');
$mmrpg_database_robots = $db->get_array_list("SELECT {$robot_fields} FROM mmrpg_index_robots WHERE robot_flag_published = 1 AND (robot_flag_hidden = 0 OR robot_token = '{$this_current_token}') {$temp_condition} ORDER BY robot_flag_hidden ASC, robot_order ASC;", 'robot_token');
$mmrpg_database_robots_count = $db->get_value("SELECT COUNT(robot_id) AS robot_count FROM mmrpg_index_robots WHERE robot_flag_published = 1 AND robot_flag_hidden = 0 {$temp_condition_unfiltered};", 'robot_count');
$mmrpg_database_robots_numbers = $db->get_array_list("SELECT robot_token, (@robot_row_number:=@robot_row_number + 1) AS robot_key FROM mmrpg_index_robots WHERE robot_flag_published = 1 {$temp_condition_unfiltered} ORDER BY robot_flag_hidden ASC, robot_order ASC;", 'robot_token');

// Remove unallowed robots from the database, and increment type counters
foreach ($mmrpg_database_robots AS $temp_token => $temp_info){

    // Define first robot token if not set
    if (!isset($first_robot_token)){ $first_robot_token = $temp_token; }

    // Send this data through the robot index parser
    $temp_info = rpg_robot::parse_index_info($temp_info);

    // Collect this robot's key in the index
    $temp_info['robot_key'] = $mmrpg_database_robots_numbers[$temp_token]['robot_key'];

    // Ensure this robot's image exists, else default to the placeholder
    if ($temp_info['robot_flag_complete']){ $temp_info['robot_image'] = $temp_token; }
    else { $temp_info['robot_image'] = 'robot'; }

    // Modify the name of this robot if it is of the mecha class
    if ($temp_info['robot_class'] == 'mecha' && defined('DATA_DATABASE_SHOW_MECHAS')){
        // Collect this mecha's field token, then robot master token, then robot master number
        $temp_field_token = $temp_info['robot_field'];
        $temp_field_info = !empty($mmrpg_database_fields[$temp_field_token]) ? rpg_field::parse_index_info($mmrpg_database_fields[$temp_field_token]) : array();
        $temp_master_token = !empty($temp_field_info['field_master']) ? $temp_field_info['field_master'] : 'met';
        $temp_master_number = $mmrpg_database_robots[$temp_master_token]['robot_number'];
        $temp_info['robot_master_number'] = $temp_master_number;
    } elseif ($temp_info['robot_class'] == 'master'){
        $temp_info['robot_master_number'] = $temp_info['robot_number'];
    } elseif ($temp_info['robot_class'] == 'boss'){
        $temp_info['robot_master_number'] = $temp_info['robot_number'];
    }


    // Increment the robot core counter if not empty
    if (!empty($temp_info['robot_core'])){ $mmrpg_database_robots_types['cores'][$temp_info['robot_core']]++; }
    else { $mmrpg_database_robots_types['cores']['none']++; }
    if (!empty($temp_info['robot_core2'])){ $mmrpg_database_robots_types['cores'][$temp_info['robot_core2']]++; }

    // Loop through the robot weaknesses if there are any to loop through
    if (!empty($temp_info['robot_weaknesses'])){
        foreach ($temp_info['robot_weaknesses'] AS $weakness){ $mmrpg_database_robots_types['weaknesses'][$weakness]++; }
    } else {
        $mmrpg_database_robots_types['weaknesses']['none']++;
    }
    // Loop through the robot resistances if there are any to loop through
    if (!empty($temp_info['robot_resistances'])){
        foreach ($temp_info['robot_resistances'] AS $weakness){ $mmrpg_database_robots_types['resistances'][$weakness]++; }
    } else {
        $mmrpg_database_robots_types['resistances']['none']++;
    }
    // Loop through the robot affinities if there are any to loop through
    if (!empty($temp_info['robot_affinities'])){
        foreach ($temp_info['robot_affinities'] AS $weakness){ $mmrpg_database_robots_types['affinities'][$weakness]++; }
    } else {
        $mmrpg_database_robots_types['affinities']['none']++;
    }
    // Loop through the robot immunities if there are any to loop through
    if (!empty($temp_info['robot_immunities'])){
        foreach ($temp_info['robot_immunities'] AS $weakness){ $mmrpg_database_robots_types['immunities'][$weakness]++; }
    } else {
        $mmrpg_database_robots_types['immunities']['none']++;
    }

    // Update the main database array with the changes
    $mmrpg_database_robots[$temp_token] = $temp_info;
}

// Define the max stat value before we filter and update
if (!isset($mmrpg_stat_base_max_value)){ $mmrpg_stat_base_max_value = array(); }
$mmrpg_stat_base_max_value['master'] = 0;


// Define database variables we'll be using to generate links
$key_counter = 0;
$last_game_code = '';
$mmrpg_database_robots_links = '';
$mmrpg_database_robots_links_counter = 0;
$mmrpg_database_robots_count_complete = 0;

// Loop through the results and generate the links for these robots
foreach ($mmrpg_database_robots AS $robot_key => $robot_info){

    // Do not show hidden robots in the link list
    if (!empty($robot_info['robot_flag_hidden'])){ continue; }

    // If a type filter has been applied to the robot page
    if (isset($this_current_filter) && $this_current_filter == 'none' && $robot_info['robot_core'] != ''){ $key_counter++; continue; }
    elseif (isset($this_current_filter) && $this_current_filter != 'none' && $robot_info['robot_core'] != $this_current_filter && $robot_info['robot_core2'] != $this_current_filter){ $key_counter++; continue; }

    // If this is the first in a new group
    $game_code = !empty($robot_info['robot_group']) ? $robot_info['robot_group'] : (!empty($robot_info['robot_game']) ? $robot_info['robot_game'] : 'MMRPG');
    if ($game_code != $last_game_code){
        if ($key_counter != 0){ $mmrpg_database_robots_links .= '</div>'; }
        $mmrpg_database_robots_links .= '<div class="float link group" data-game="'.$game_code.'">';
        $last_game_code = $game_code;
    }

    // Check if this is a mecha and prepare extra text
    $robot_info['robot_name_append'] = '';

    // Collect the robot sprite dimensions
    $robot_flag_complete = !empty($robot_info['robot_flag_complete']) ? true : false;
    $robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
    $robot_image_size_text = $robot_image_size.'x'.$robot_image_size;
    $robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
    $robot_image_incomplete = $robot_image_token == 'robot' ? true : false;
    $robot_is_active = !empty($this_current_token) && $this_current_token == $robot_info['robot_token'] ? true : false;
    $robot_title_text = $robot_info['robot_name'].$robot_info['robot_name_append'].' | '.$robot_info['robot_number'].' | '.(!empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral').' Core';
    $robot_title_text .= '|| [[E:'.$robot_info['robot_energy'].' | W:'.$robot_info['robot_weapons'].' | A:'.$robot_info['robot_attack'].' | D:'.$robot_info['robot_defense'].' | S:'.$robot_info['robot_speed'].']]';
    //$robot_title_text .= ' | game:'.$robot_info['robot_game'].' | group:'.$robot_info['robot_group'];
    $robot_image_path = 'images/robots/'.$robot_image_token.'/mug_right_'.$robot_image_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE;
    $robot_stat_max = $robot_info['robot_energy'] + $robot_info['robot_attack'] + $robot_info['robot_defense'] + $robot_info['robot_speed'];
    if ($robot_stat_max > $mmrpg_stat_base_max_value['master']){ $mmrpg_stat_base_max_value['master'] = $robot_stat_max; }

    // Start the output buffer and collect the generated markup
    ob_start();
    ?>
    <div title="<?= $robot_title_text ?>" data-token="<?= $robot_info['robot_token'] ?>" class="float left link type <?= ($robot_image_incomplete ? 'inactive ' : '').(!empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none').(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') ?>">
        <a class="sprite robot link mugshot size<?= $robot_image_size.($robot_key == $first_robot_token ? ' current' : '') ?>" href="<?= 'database/robots/'.$robot_info['robot_token']?>/" rel="<?= $robot_image_incomplete ? 'nofollow' : 'follow' ?>">
            <?php if($robot_image_token != 'robot'): ?>
                <img src="<?= $robot_image_path ?>" width="<?= $robot_image_size ?>" height="<?= $robot_image_size ?>" alt="<?= $robot_title_text ?>" />
            <?php else: ?>
                <span><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></span>
            <?php endif; ?>
        </a>
    </div>
    <?php
    if ($robot_flag_complete){ $mmrpg_database_robots_count_complete++; }
    $mmrpg_database_robots_links .= ob_get_clean();
    $mmrpg_database_robots_links_counter++;
    $key_counter++;

}

// End the groups, however many there were
$mmrpg_database_robots_links .= '</div>';

?>