<?
// Require the application top file
require_once('../top.php');

// Unset the prototype temp variable
$_SESSION['PROTOTYPE_TEMP'] = array();

// Require the remote top in case we're in viewer mode
define('MMRPG_REMOTE_SKIP_INDEX', true);
define('MMRPG_REMOTE_SKIP_COMPLETE', true);
define('MMRPG_REMOTE_SKIP_FAILURE', true);
define('MMRPG_REMOTE_SKIP_SETTINGS', true);
define('MMRPG_REMOTE_SKIP_ITEMS', true);
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_top.php');

// Collect the session token
$session_token = mmrpg_game_token();

// Require the prototype data file
//require_once('../prototype/include.php');

// Require the prototype omega data file
require_once('../prototype/omega.php');
$unlocked_factor_one_robots = false;
$unlocked_factor_two_robots = false;
$unlocked_factor_three_robots = false;
$unlocked_factor_four_robots = false;
$temp_omega_factor_options = array();
$temp_omega_factor_options_unlocked = array();
if (mmrpg_prototype_complete('dr-light')){
    $temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_one);
    $unlocked_factor_one_robots = true;
}
if (mmrpg_prototype_complete('dr-wily')){
    $temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_two);
    $unlocked_factor_two_robots = true;
}
if (mmrpg_prototype_complete('dr-cossack')){
    $temp_omega_factor_options = array_merge($temp_omega_factor_options, $this_omega_factors_three);
    $unlocked_factor_three_robots = true;
}

// Collect any fields unlocked via other means
$temp_unlocked_fields = !empty($_SESSION[$session_token]['values']['battle_fields']) ? $_SESSION[$session_token]['values']['battle_fields'] : array();

// Loop through unlockable system fields with no type
foreach ($this_omega_factors_system AS $key => $factor){
    if (in_array($factor['field'], $temp_unlocked_fields)){
        $temp_omega_factor_options[] = $factor;
    }
}
// Loop through the unlockable MM3 fields (from omega factor four)
foreach ($this_omega_factors_four AS $key => $factor){
    if (in_array($factor['field'], $temp_unlocked_fields)){
        $temp_omega_factor_options[] = $factor;
        $unlocked_factor_four_robots = true;
    }
}

// Loop through the collected options and pull just the robot tokens
foreach ($temp_omega_factor_options AS $key => $factor){
    $temp_omega_factor_options_unlocked[] = $factor['field'];
}

// Require the starforce data file
require_once(MMRPG_CONFIG_ROOTDIR.'includes/starforce.php');

// Collect the editor flag if set
$global_allow_editing = isset($_GET['edit']) && $_GET['edit'] == 'false' ? false : true;

// Collect the robot's index for names and fields
$rpg_robots_index = rpg_robot::get_index();

// Collect all the robots that have been unlocked by the player
$rpg_robots_encountered = array();
if (!empty($_SESSION[$session_token]['values']['robot_database'])){
    $rpg_robots_encountered = array_keys($_SESSION[$session_token]['values']['robot_database']);
}

// Collect the omega factors that we should be printing links for
$temp_omega_factors_unlocked = array();
if ($unlocked_factor_one_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_one); }
if ($unlocked_factor_two_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_two); }
if ($unlocked_factor_four_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_four); }
if ($unlocked_factor_three_robots){ $temp_omega_factors_unlocked = array_merge($temp_omega_factors_unlocked, $this_omega_factors_three); }
$temp_omega_factors_unlocked_total = count($temp_omega_factors_unlocked);

// Define a function for printing out the robot links
function temp_print_omega_robot_links($info, $key, $kind){
    global $rpg_robots_encountered, $rpg_robots_index;
    $robot = $info['robot'];
    $type = $info['type'];
    $field = $info['field'];
    if (in_array($robot, $rpg_robots_encountered)){
        $info = $rpg_robots_index[$robot];
        $name = $info['robot_name'];
        $size = $info['robot_image_size'] ? $info['robot_image_size'] : 40;
        list($field_one, $field_two) = explode('-', $field);
        $class = 'robot robot_type robot_type_'.$type.' sprite_'.$size.'x'.$size.' ';
        $style = 'background-image: url(images/robots/'.$robot.'/mug_left_'.$size.'x'.$size.'.png); ';
        $title = '<div style="text-align: center;">';
            $title .= $name.' <br /> ';
            $title .= '<span style="font-size: 80%">'.ucfirst($field_one).' '.ucfirst($field_two).'</span>';
        $title .= '</div>';
        $title = htmlentities($title, ENT_QUOTES, 'UTF-8');
        echo '<span class="'.$class.'" data-'.$kind.'-key="'.$key.'" style="'.$style.'" title="'.$title.'">&nbsp;</span>'."\n";
    } else {
        $class = 'robot robot_type robot_type_empty sprite_40x40 ';
        echo '<span class="'.$class.'" data-'.$kind.'-key="'.$key.'">&nbsp;</span>'."\n";
    }
}

// Define a function for counting permutations
function temp_combination_number($k,$n){
    $n = intval($n);
    $k = intval($k);
    if ($k > $n){
            return 0;
    } elseif ($n == $k) {
            return 1;
    } else {
            if ($k >= $n - $k){
                    $l = $k+1;
                    for ($i = $l+1 ; $i <= $n ; $i++)
                            $l *= $i;
                    $m = 1;
                    for ($i = 2 ; $i <= $n-$k ; $i++)
                            $m *= $i;
            } else {
                    $l = ($n-$k) + 1;
                    for ($i = $l+1 ; $i <= $n ; $i++)
                            $l *= $i;
                    $m = 1;
                    for ($i = 2 ; $i <= $k ; $i++)
                            $m *= $i;
            }
    }
    return $l/$m;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Mega Man RPG Prototype | Rulebook | Last Updated <?= preg_replace('#([0-9]{4})([0-9]{2})([0-9]{2})-([0-9]{2})#', '$1/$2/$3', MMRPG_CONFIG_CACHE_DATE) ?></title>
<base href="<?=MMRPG_CONFIG_ROOTURL?>" />
<meta name="robots" content="noindex,nofollow" />
<meta name="format-detection" content="telephone=no" />
<link type="text/css" href="styles/style.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/starforce.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?if($flag_wap):?>
<link type="text/css" href="styles/style-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<link type="text/css" href="styles/prototype-mobile.css?<?=MMRPG_CONFIG_CACHE_DATE?>" rel="stylesheet" />
<?endif;?>
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/script.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/prototype.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript" src="scripts/starforce.js?<?=MMRPG_CONFIG_CACHE_DATE?>"></script>
<script type="text/javascript">
// Update game settings for this page
gameSettings.fadeIn = <?= isset($_GET['fadein']) ? $_GET['fadein'] : 'false' ?>;
gameSettings.wapFlag = <?= $flag_wap ? 'true' : 'false' ?>;
gameSettings.cacheTime = '<?= MMRPG_CONFIG_CACHE_DATE ?>';
gameSettings.autoScrollTop = false;
</script>
</head>
<body id="mmrpg" class="iframe" style="<?= !$global_allow_editing ? 'width: 100% !important; max-width: 1000px !important; ' : '' ?>">

    <div id="prototype" class="<?= empty($this_start_key) ? 'hidden' : '' ?>" style="<?= !$global_allow_editing ? 'width: 100% !important; ' : '' ?>">

        <div class="menu">

            <?php
            $this_battle_stars_boost = 0;
            foreach ($this_star_force AS $force_type => $force_count){ $this_battle_stars_boost += $force_count * MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT; }
            $temp_total_stars_label = $this_battle_stars_count; //$this_battle_stars_count == 1 ? '1 Star' : $this_battle_stars_count.' Stars';
            $temp_potential_count = ((temp_combination_number(2, $temp_omega_factors_unlocked_total) * 2) + $temp_omega_factors_unlocked_total);
            $temp_potential_stars_label = $temp_potential_count == 1 ? '1 Star' : $temp_potential_count.' Stars';
            $temp_total_boost_label = '+'.number_format($this_battle_stars_boost, 0, '.', ',').'% Boost';
            ?>
            <span class="header block_1 header_types type_<?= defined('MMRPG_SETTINGS_REMOTE_FIELDTYPE') ? MMRPG_SETTINGS_REMOTE_FIELDTYPE : MMRPG_SETTINGS_CURRENT_FIELDTYPE ?>">
                <span class="count">
                    StarForce <span style="opacity: 0.25;">(
                        <span><?= $temp_total_stars_label ?></span> /
                        <span><?= $temp_potential_stars_label ?></span> /
                        <span><?= $temp_total_boost_label ?></span>
                        )</span>
                </span>
            </span>

            <div class="starforce">
                <div class="wrapper" style="<?= $flag_wap ? 'margin-right: 0;' : '' ?>">

                    <div class="types_container" style="">
                        <?php

                        // Loop through all the field stars and print them out one-by-one
                        if (!empty($this_star_force)){

                            $temp_max_force = 0;
                            $temp_max_padding = 30;
                            foreach ($this_star_force AS $force_type => $force_count){
                                $temp_padding_amount = $force_count * 2;
                                if ($force_count > $temp_max_force){ $temp_max_force = $force_count; }
                                if ($temp_padding_amount > 117){ $temp_max_padding = 117; }
                            }


                            foreach ($this_star_force AS $force_type => $force_count){
                                $temp_padding_amount = $force_count * 2;
                                $temp_padding_amount = ceil(($force_count / $temp_max_force) * $temp_max_padding);
                                $force_count_strict = $this_star_force_strict[$force_type];
                                echo '<div data-tooltip="'.ucfirst($force_type).' +'.($force_count * MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT).'% Boost | '.$force_count_strict.' / '.$this_battle_stars_count.' Stars" class="field_type field_type_'.$force_type.'" style="padding-right: '.$temp_padding_amount.'px;" data-padding="'.$temp_padding_amount.'">'.ucfirst($force_type).' +'.($force_count * MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT).'%</div>';
                            }


                        }


                        ?>
                    </div>

                    <div class="page_links top_panel" data-max="19" data-key="0">
                        <a class="arrow" data-scroll="left"><span>&nbsp;</span></a>
                        <?php
                        // Loop through the omega fields and print out their buttons
                        foreach ($temp_omega_factors_unlocked AS $key => $info){
                            temp_print_omega_robot_links($info, $key, 'top');
                        }
                        ?>
                        <a class="arrow" data-scroll="right"><span>&nbsp;</span></a>
                    </div>

                    <div class="page_links side_panel" data-max="9" data-key="0">
                        <a class="arrow" data-scroll="up"><span>&nbsp;</span></a>
                        <?php
                        // Loop through the omega fields and print out their buttons
                        foreach ($temp_omega_factors_unlocked AS $key => $info){
                            temp_print_omega_robot_links($info, $key, 'side');
                        }
                        ?>
                        <a class="arrow" data-scroll="down"><span>&nbsp;</span></a>
                    </div>

                    <div class="stars_container" data-size="<?= $temp_omega_factors_unlocked_total ?>">
                        <div class="size_wrapper">
                            <?php

                            // Loop through all the field stars and print them out one-by-one
                            if (!empty($this_battle_stars)){

                                // Loop through all the omega factors firstly to create the side fields
                                $temp_key = 0;
                                foreach ($temp_omega_factors_unlocked AS $side_key => $side_field_info){

                                    // Define the tokens for this field
                                    $side_field_token = $side_field_info['field'];
                                    list($side_field_token_one, $side_field_token_two) = explode('-', $side_field_token);

                                    // Loop through all the omega factors firstly to create the side fields
                                    foreach ($temp_omega_factors_unlocked AS $top_key => $top_field_info){

                                        // Define the tokens for this field
                                        $top_field_token = $top_field_info['field'];
                                        list($top_field_token_one, $top_field_token_two) = explode('-', $top_field_token);

                                        // Generate the star token based on the two field tokens
                                        $star_token = $side_field_token_one.'-'.$top_field_token_two;
                                        //echo '$side_field_token_one = '.$side_field_token_one.' / $top_field_token_two = '.$top_field_token_two."\n";
                                        $star_data = !empty($this_battle_stars[$star_token]) ? $this_battle_stars[$star_token] : false;

                                        // If the star data exists, print out the star info
                                        if (!empty($star_data)){

                                            // Collect the star image info from the index based on type
                                            $temp_star_kind = $star_data['star_kind'];
                                            $temp_star_date = !empty($star_data['star_date']) ? $star_data['star_date']: 0;
                                            $temp_field_type_1 = !empty($star_data['star_type']) ? $star_data['star_type'] : 'none';
                                            $temp_field_type_2 = !empty($star_data['star_type2']) ? $star_data['star_type2'] : $temp_field_type_1;
                                            $temp_star_back_info = mmrpg_prototype_star_image($temp_field_type_2);
                                            $temp_star_front_info = mmrpg_prototype_star_image($temp_field_type_1);
                                            $temp_star_front = array('path' => 'images/items/star-base-'.$temp_star_front_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => str_pad($temp_star_front_info['frame'], 2, '0', STR_PAD_LEFT));
                                            $temp_star_back = array('path' => 'images/items/star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE, 'frame' => str_pad($temp_star_back_info['frame'], 2, '0', STR_PAD_LEFT));
                                            $temp_star_title = $star_data['star_name'].' Star <br />';
                                            $temp_star_title .= '<span style="font-size:80%;">';
                                            if ($temp_field_type_1 != $temp_field_type_2){ $temp_star_title .= ''.ucfirst($temp_field_type_1).(!empty($temp_field_type_2) ? ' / '.ucfirst($temp_field_type_2) : '').' Type'; }
                                            else { $temp_star_title .= ''.ucfirst($temp_field_type_1).' Type'; }
                                            $temp_star_title .= ' | '.ucfirst($temp_star_kind).' Star';
                                            if ($temp_field_type_1 != 'none'){
                                                if ($temp_star_kind == 'field'){
                                                    $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT);
                                                } elseif ($temp_star_kind == 'fusion'){
                                                    if ($temp_field_type_1 != $temp_field_type_2){
                                                        $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT);
                                                        $temp_star_title .= ' | '.ucfirst($temp_field_type_2).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT);
                                                    } else {
                                                        $temp_star_title .= ' <br />'.ucfirst($temp_field_type_1).' +'.(MMRPG_SETTINGS_STARFORCE_BOOSTPERCENT * 2);
                                                    }
                                                }
                                            }
                                            if (!empty($temp_star_date)){
                                                $temp_star_title .= ' <br />Found '.date('Y/m/d', $temp_star_date);
                                            }
                                            $temp_star_title .= '</span>';
                                            $temp_star_title = htmlentities($temp_star_title, ENT_QUOTES, 'UTF-8');

                                            // Print out the markup for the field or fusion star
                                            echo '<a href="#" data-side-key="'.$side_key.'" data-top-key="'.$top_key.'" data-tooltip="'.$temp_star_title.'" data-tooltip-type="field_type field_type_'.$temp_field_type_1.(!empty($temp_field_type_2) && ($temp_field_type_1 != $temp_field_type_2) ? '_'.$temp_field_type_2 : '').'" class="sprite sprite_40x40 sprite_star" style="">';
                                                echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_back['frame'].'" style="background-image: url('.$temp_star_back['path'].'); z-index: 10;">&nbsp;</div>';
                                                echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_'.$temp_star_front['frame'].'" style="background-image: url('.$temp_star_front['path'].'); z-index: 20;">&nbsp;</div>';
                                            echo '</a>';


                                        //echo('<pre>$star_data = $this_battle_stars['.$star_token.'] = '.print_r($star_data, true).'</pre>');
                                        //echo('<pre>$this_star_force = '.print_r($this_star_force, true).'</pre>');
                                        //echo('<pre>$this_battle_stars = '.print_r($this_battle_stars, true).'</pre>');
                                        //exit();

                                        }
                                        // Otherwise, print out an empty star placeholder
                                        else {

                                            // Print out the markup for the field or fusion star
                                            echo '<a href="#" data-side-key="'.$side_key.'" data-top-key="'.$top_key.'" data-tooltip-type="field_type field_type_empty" class="sprite sprite_40x40 sprite_star empty_star" style="">';
                                                echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00" style="">&nbsp;</div>';
                                                echo '<div class="sprite sprite_40x40 sprite_40x40_left sprite_40x40_left_00" style="">&nbsp;</div>';
                                            echo '</a>';

                                        }

                                        // Increment the key either way
                                        $temp_key++;

                                    }


                                }

                            }
                            ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
<script type="text/javascript">
$(document).ready(function(){

});
</script>
<?
// Google Analytics
if(MMRPG_CONFIG_IS_LIVE){ require(MMRPG_CONFIG_ROOTDIR.'data/analytics.php'); }
?>
</body>
</html>
<?
// Require the remote bottom in case we're in viewer mode
require(MMRPG_CONFIG_ROOTDIR.'/frames/remote_bottom.php');
// Unset the database variable
unset($db);
?>