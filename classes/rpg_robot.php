<?
// Define a class for the robots
class rpg_robot {

    // Define global class variables
    public $flags;
    public $counters;
    public $values;
    public $history;

    // Define the constructor class
    public function rpg_robot(){

        // Collect any provided arguments
        $args = func_get_args();

        // Define the internal class identifier
        $this->class = 'robot';

        // Define the internal battle pointer
        $this->battle = isset($args[0]) ? $args[0] : $GLOBALS['this_battle'];
        $this->battle_id = $this->battle->battle_id;
        $this->battle_token = $this->battle->battle_token;

        // Define the internal battle pointer
        $this->field = isset($this->battle->field) ? $this->battle->field : $GLOBALS['this_field'];
        $this->field_id = $this->battle->battle_id;
        $this->field_token = $this->battle->battle_token;

        // Define the internal player values using the provided array
        $this->player = isset($args[1]) ? $args[1] : $GLOBALS['this_player'];
        $this->player_id = $this->player->player_id;
        $this->player_token = $this->player->player_token;

        // Collect current robot data from the function if available
        $this_robotinfo = isset($args[2]) && !empty($args[2]) ? $args[2] : array('robot_id' => 0, 'robot_token' => 'robot');

        // Now load the robot data from the session or index
        if (!$this->robot_load($this_robotinfo)){
            // Robot data could not be loaded
            die('Robot data could not be loaded :<br />$this_robotinfo = <pre>'.print_r($this_robotinfo, true).'</pre>');
        }

        // Return true on success
        return true;

    }

    // Define a function for getting the session info
    public static function get_session_field($robot_id, $field_token){
        if (empty($robot_id) || empty($field_token)){ return false; }
        elseif (!empty($_SESSION['ROBOTS'][$robot_id][$field_token])){ return $_SESSION['ROBOTS'][$robot_id][$field_token]; }
        else { return false; }
    }

    // Define a function for setting the session info
    public static function set_session_field($robot_id, $field_token, $field_value){
        if (empty($robot_id) || empty($field_token)){ return false; }
        else { $_SESSION['ROBOTS'][$robot_id][$field_token] = $field_value; }
        return true;
    }

    // Define a public function for manually loading data
    public function robot_load($this_robotinfo){

        // If the robot info was not an array, return false
        if (!is_array($this_robotinfo)){ die("robot info must be an array!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }
        // If the robot ID was not provided, return false
        if (!isset($this_robotinfo['robot_id'])){ die("robot id must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }
        // If the robot token was not provided, return false
        if (!isset($this_robotinfo['robot_token'])){ die("robot token must be set!\n\$this_robotinfo\n".print_r($this_robotinfo, true)); return false; }

        // Collect current robot data from the session if available
        $this_robotinfo_backup = $this_robotinfo;
        if (isset($_SESSION['ROBOTS'][$this_robotinfo['robot_id']])){
            $this_robotinfo = $_SESSION['ROBOTS'][$this_robotinfo['robot_id']];
        }
        // Otherwise, collect robot data from the index
        else {
            if (empty($this_robotinfo_backup['_parsed'])){
                if (MMRPG_CONFIG_DEBUG_MODE){ $_SESSION['DEBUG']['checkpoint_queries'][] = "\$this_robotinfo = rpg_robot::get_index_info({$this_robotinfo['robot_token']}); on line ".__LINE__;  }
                $this_robotinfo = rpg_robot::get_index_info($this_robotinfo['robot_token']);
                $this_robotinfo = array_replace($this_robotinfo, $this_robotinfo_backup);
            }
        }

        // DEBUG
        /*
        if (false && $this_robotinfo['robot_token'] == 'mega-man'){
            die(__LINE__.
                ':: <pre>$this_robotinfo_backup:'.print_r($this_robotinfo_backup, true).'</pre>'.
                ':: <pre>$this_robotinfo:'.print_r($this_robotinfo, true).'</pre>');
        }
        */


        // Define the internal robot values using the provided array
        $this->flags = isset($this_robotinfo['flags']) ? $this_robotinfo['flags'] : array();
        $this->counters = isset($this_robotinfo['counters']) ? $this_robotinfo['counters'] : array();
        $this->values = isset($this_robotinfo['values']) ? $this_robotinfo['values'] : array();
        $this->history = isset($this_robotinfo['history']) ? $this_robotinfo['history'] : array();
        $this->robot_key = isset($this_robotinfo['robot_key']) ? $this_robotinfo['robot_key'] : 0;
        $this->robot_id = isset($this_robotinfo['robot_id']) ? $this_robotinfo['robot_id'] : false;
        $this->robot_number = isset($this_robotinfo['robot_number']) ? $this_robotinfo['robot_number'] : 'RPG000';
        $this->robot_name = isset($this_robotinfo['robot_name']) ? $this_robotinfo['robot_name'] : 'Robot';
        $this->robot_token = isset($this_robotinfo['robot_token']) ? $this_robotinfo['robot_token'] : 'robot';
        $this->robot_field = isset($this_robotinfo['robot_field']) ? $this_robotinfo['robot_field'] : 'field';
        $this->robot_class = isset($this_robotinfo['robot_class']) ? $this_robotinfo['robot_class'] : 'master';
        $this->robot_image = isset($this_robotinfo['robot_image']) ? $this_robotinfo['robot_image'] : $this->robot_token;
        $this->robot_image_size = isset($this_robotinfo['robot_image_size']) ? $this_robotinfo['robot_image_size'] : 40;
        $this->robot_image_overlay = isset($this_robotinfo['robot_image_overlay']) ? $this_robotinfo['robot_image_overlay'] : array();
        $this->robot_core = isset($this_robotinfo['robot_core']) ? $this_robotinfo['robot_core'] : false;
        $this->robot_core2 = isset($this_robotinfo['robot_core2']) ? $this_robotinfo['robot_core2'] : false;
        $this->robot_description = isset($this_robotinfo['robot_description']) ? $this_robotinfo['robot_description'] : '';
        $this->robot_experience = isset($this_robotinfo['robot_experience']) ? $this_robotinfo['robot_experience'] : (isset($this_robotinfo['robot_points']) ? $this_robotinfo['robot_points'] : 0);
        $this->robot_level = isset($this_robotinfo['robot_level']) ? $this_robotinfo['robot_level'] : (!empty($this->robot_experience) ? $this->robot_experience / 1000 : 0) + 1;
        $this->robot_energy = isset($this_robotinfo['robot_energy']) ? $this_robotinfo['robot_energy'] : 1;
        $this->robot_weapons = isset($this_robotinfo['robot_weapons']) ? $this_robotinfo['robot_weapons'] : 10;
        $this->robot_attack = isset($this_robotinfo['robot_attack']) ? $this_robotinfo['robot_attack'] : 1;
        $this->robot_defense = isset($this_robotinfo['robot_defense']) ? $this_robotinfo['robot_defense'] : 1;
        $this->robot_speed = isset($this_robotinfo['robot_speed']) ? $this_robotinfo['robot_speed'] : 1;
        $this->robot_weaknesses = isset($this_robotinfo['robot_weaknesses']) ? $this_robotinfo['robot_weaknesses'] : array();
        $this->robot_resistances = isset($this_robotinfo['robot_resistances']) ? $this_robotinfo['robot_resistances'] : array();
        $this->robot_affinities = isset($this_robotinfo['robot_affinities']) ? $this_robotinfo['robot_affinities'] : array();
        $this->robot_immunities = isset($this_robotinfo['robot_immunities']) ? $this_robotinfo['robot_immunities'] : array();
        $this->robot_abilities = isset($this_robotinfo['robot_abilities']) ? $this_robotinfo['robot_abilities'] : array();
        $this->robot_attachments = isset($this_robotinfo['robot_attachments']) ? $this_robotinfo['robot_attachments'] : array();
        $this->robot_quotes = isset($this_robotinfo['robot_quotes']) ? $this_robotinfo['robot_quotes'] : array();
        $this->robot_status = isset($this_robotinfo['robot_status']) ? $this_robotinfo['robot_status'] : 'active';
        $this->robot_position = isset($this_robotinfo['robot_position']) ? $this_robotinfo['robot_position'] : 'bench';
        $this->robot_stance = isset($this_robotinfo['robot_stance']) ? $this_robotinfo['robot_stance'] : 'base';
        $this->robot_rewards = isset($this_robotinfo['robot_rewards']) ? $this_robotinfo['robot_rewards'] : array();
        $this->robot_functions = isset($this_robotinfo['robot_functions']) ? $this_robotinfo['robot_functions'] : 'robots/robot.php';
        $this->robot_frame = isset($this_robotinfo['robot_frame']) ? $this_robotinfo['robot_frame'] : 'base';
        //$this->robot_frame_index = isset($this_robotinfo['robot_frame_index']) ? $this_robotinfo['robot_frame_index'] : array('base','taunt','victory','defeat','shoot','throw','summon','slide','defend','damage','base2');
        $this->robot_frame_offset = isset($this_robotinfo['robot_frame_offset']) ? $this_robotinfo['robot_frame_offset'] : array('x' => 0, 'y' => 0, 'z' => 0);
        $this->robot_frame_classes = isset($this_robotinfo['robot_frame_classes']) ? $this_robotinfo['robot_frame_classes'] : '';
        $this->robot_frame_styles = isset($this_robotinfo['robot_frame_styles']) ? $this_robotinfo['robot_frame_styles'] : '';
        $this->robot_detail_styles = isset($this_robotinfo['robot_detail_styles']) ? $this_robotinfo['robot_detail_styles'] : '';
        $this->robot_original_player = isset($this_robotinfo['robot_original_player']) ? $this_robotinfo['robot_original_player'] : $this->player_token;
        $this->robot_string = isset($this_robotinfo['robot_string']) ? $this_robotinfo['robot_string'] : $this->robot_id.'_'.$this->robot_token;

        // Collect any functions associated with this ability
        $temp_functions_path = file_exists(MMRPG_CONFIG_ROOTDIR.'data/'.$this->robot_functions) ? $this->robot_functions : 'robots/functions.php';
        require(MMRPG_CONFIG_ROOTDIR.'data/'.$temp_functions_path);
        $this->robot_function = isset($ability['robot_function']) ? $ability['robot_function'] : function(){};
        $this->robot_function_onload = isset($ability['robot_function_onload']) ? $ability['robot_function_onload'] : function(){};
        unset($ability);

        // Define the internal robot base values using the robots index array
        $this->robot_base_name = isset($this_robotinfo['robot_base_name']) ? $this_robotinfo['robot_base_name'] : $this->robot_name;
        $this->robot_base_token = isset($this_robotinfo['robot_base_token']) ? $this_robotinfo['robot_base_token'] : $this->robot_token;

        $this->robot_base_image = isset($this_robotinfo['robot_base_image']) ? $this_robotinfo['robot_base_image'] : $this->robot_base_token;
        $this->robot_base_image_size = isset($this_robotinfo['robot_base_image_size']) ? $this_robotinfo['robot_base_image_size'] : $this->robot_image_size;
        $this->robot_base_image_overlay = isset($this_robotinfo['robot_base_image_overlay']) ? $this_robotinfo['robot_base_image_overlay'] : $this->robot_image_overlay;

        $this->robot_base_core = isset($this_robotinfo['robot_base_core']) ? $this_robotinfo['robot_base_core'] : $this->robot_core;
        $this->robot_base_core2 = isset($this_robotinfo['robot_base_core2']) ? $this_robotinfo['robot_base_core2'] : $this->robot_core2;

        $this->robot_base_description = isset($this_robotinfo['robot_base_description']) ? $this_robotinfo['robot_base_description'] : $this->robot_description;

        $this->robot_base_experience = isset($this_robotinfo['robot_base_experience']) ? $this_robotinfo['robot_base_experience'] : $this->robot_experience;
        $this->robot_base_level = isset($this_robotinfo['robot_base_level']) ? $this_robotinfo['robot_base_level'] : $this->robot_level;

        $this->robot_base_energy = isset($this_robotinfo['robot_base_energy']) ? $this_robotinfo['robot_base_energy'] : $this->robot_energy;
        $this->robot_base_weapons = isset($this_robotinfo['robot_base_weapons']) ? $this_robotinfo['robot_base_weapons'] : $this->robot_weapons;
        $this->robot_base_attack = isset($this_robotinfo['robot_base_attack']) ? $this_robotinfo['robot_base_attack'] : $this->robot_attack;
        $this->robot_base_defense = isset($this_robotinfo['robot_base_defense']) ? $this_robotinfo['robot_base_defense'] : $this->robot_defense;
        $this->robot_base_speed = isset($this_robotinfo['robot_base_speed']) ? $this_robotinfo['robot_base_speed'] : $this->robot_speed;

        $this->robot_max_energy = isset($this_robotinfo['robot_max_energy']) ? $this_robotinfo['robot_max_energy'] : $this->robot_base_energy;
        $this->robot_max_weapons = isset($this_robotinfo['robot_max_weapons']) ? $this_robotinfo['robot_max_weapons'] : $this->robot_base_weapons;
        $this->robot_max_attack = isset($this_robotinfo['robot_max_attack']) ? $this_robotinfo['robot_max_attack'] : $this->robot_base_attack;
        $this->robot_max_defense = isset($this_robotinfo['robot_max_defense']) ? $this_robotinfo['robot_max_defense'] : $this->robot_base_defense;
        $this->robot_max_speed = isset($this_robotinfo['robot_max_speed']) ? $this_robotinfo['robot_max_speed'] : $this->robot_base_speed;

        $this->robot_base_weaknesses = isset($this_robotinfo['robot_base_weaknesses']) ? $this_robotinfo['robot_base_weaknesses'] : $this->robot_weaknesses;
        $this->robot_base_resistances = isset($this_robotinfo['robot_base_resistances']) ? $this_robotinfo['robot_base_resistances'] : $this->robot_resistances;
        $this->robot_base_affinities = isset($this_robotinfo['robot_base_affinities']) ? $this_robotinfo['robot_base_affinities'] : $this->robot_affinities;
        $this->robot_base_immunities = isset($this_robotinfo['robot_base_immunities']) ? $this_robotinfo['robot_base_immunities'] : $this->robot_immunities;

        //$this->robot_base_abilities = isset($this_robotinfo['robot_base_abilities']) ? $this_robotinfo['robot_base_abilities'] : $this->robot_abilities;
        $this->robot_base_attachments = isset($this_robotinfo['robot_base_attachments']) ? $this_robotinfo['robot_base_attachments'] : $this->robot_attachments;

        $this->robot_base_quotes = isset($this_robotinfo['robot_base_quotes']) ? $this_robotinfo['robot_base_quotes'] : $this->robot_quotes;

        // Limit all stats to 9999 for display purposes (and balance I guess)
        if ($this->robot_energy > MMRPG_SETTINGS_STATS_MAX){ $this->robot_energy = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_energy > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_energy = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_weapons > MMRPG_SETTINGS_STATS_MAX){ $this->robot_weapons = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_weapons > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_weapons = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_attack > MMRPG_SETTINGS_STATS_MAX){ $this->robot_attack = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_attack > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_attack = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_defense > MMRPG_SETTINGS_STATS_MAX){ $this->robot_defense = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_defense > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_defense = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_speed > MMRPG_SETTINGS_STATS_MAX){ $this->robot_speed = MMRPG_SETTINGS_STATS_MAX; }
        if ($this->robot_base_speed > MMRPG_SETTINGS_STATS_MAX){ $this->robot_base_speed = MMRPG_SETTINGS_STATS_MAX; }

        // If this is a player-controlled robot, load abilities from session
        if ($this->player->player_side == 'left' && empty($this->flags['apply_session_abilities'])){
            // Collect the abilities for this robot from the session
            $temp_robot_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);
            if (!empty($temp_robot_settings['robot_abilities'])){
                $temp_robot_abilities = $temp_robot_settings['robot_abilities'];
                $this->robot_abilities = array();
                foreach ($temp_robot_abilities AS $token => $info){ $this->robot_abilities[] = $token; }
            }
            // Set the session ability flag to true
            $this->flags['apply_session_abilities'] = true;
        }

        // Remove any abilities that do not exist in the index
        if (!empty($this->robot_abilities)){
            foreach ($this->robot_abilities AS $key => $token){
                if ($token == 'ability' || empty($token)){ unset($this->robot_abilities[$key]); }
            }
            $this->robot_abilities = array_values($this->robot_abilities);
        }

        // If this robot is already disabled, make sure their status reflects it
        if (!empty($this->flags['hidden'])){
            $this->flags['apply_disabled_state'] = true;
            $this->robot_status = 'disabled';
            $this->robot_energy = 0;
        }



        // Update the session variable
        $this->update_session();

        // DEBUG
        /*
        if ($this_robotinfo['robot_token'] == 'mega-man'){
            die("\nrpg_robot()::".__LINE__.
                "\n".':: <pre>$this_robotinfo_backup:'.print_r($this_robotinfo_backup, true).'</pre>'.
                "\n".':: <pre>$this_robotinfo:'.print_r($this_robotinfo, true).'</pre>');
        }
        */

        // Return true on success
        return true;

    }

    // Define a public function for applying robot stat bonuses
    public function apply_stat_bonuses(){

        // Pull in the global index
        global $mmrpg_index;

        // Only continue if this hasn't been done already
        if (!empty($this->flags['apply_stat_bonuses'])){ return false; }
        /*
         * ROBOT CLASS FUNCTION APPLY STAT BONUSES
         * public function apply_stat_bonuses(){}
         */

        // If this is robot's player is human controlled
        if ($this->player->player_autopilot != true && $this->robot_class != 'mecha'){

            // Collect this robot's rewards and settings
            $this_settings = mmrpg_prototype_robot_settings($this->player_token, $this->robot_token);
            $this_rewards = mmrpg_prototype_robot_rewards($this->player_token, $this->robot_token);

            // Update this robot's original player with any session settings
            $this->robot_original_player = mmrpg_prototype_robot_original_player($this->player_token, $this->robot_token);

            // Update this robot's level with any session rewards
            $this->robot_base_experience = $this->robot_experience = mmrpg_prototype_robot_experience($this->player_token, $this->robot_token);
            $this->robot_base_level = $this->robot_level = mmrpg_prototype_robot_level($this->player_token, $this->robot_token);

        }
        // Otherwise, if this player is on autopilot
        else {

            // Create an empty reward array to prevent errors
            $this_settings = !empty($this->values['robot_settings']) ? $this->values['robot_settings'] : array();
            $this_rewards = !empty($this->values['robot_rewards']) ? $this->values['robot_rewards'] : array();

        }

        // If the robot experience is over 1000 points, level up and reset
        if ($this->robot_experience > 1000){
            $level_boost = floor($this->robot_experience / 1000);
            $this->robot_level += $level_boost;
            $this->robot_base_level = $this->robot_level;
            $this->robot_experience -= $level_boost * 1000;
            $this->robot_base_experience = $this->robot_experience;
        }

        // Fix the level if it's over 100
        if ($this->robot_level > 100){ $this->robot_level = 100;  }
        if ($this->robot_base_level > 100){ $this->robot_base_level = 100;  }

        // Collect this robot's stat values for later reference
        $this_index_info = self::get_index_info($this->robot_token);
        $this_robot_stats = self::calculate_stat_values($this->robot_level, $this_index_info, $this_rewards, true);

        // Update the robot's stat values with calculated totals
        $stat_tokens = array('energy', 'attack', 'defense', 'speed');
        foreach ($stat_tokens AS $stat){
            // Collect and apply this robot's current stats and max
            $prop_stat = 'robot_'.$stat;
            $prop_stat_base = 'robot_base_'.$stat;
            $prop_stat_max = 'robot_max_'.$stat;
            $this->$prop_stat = $this_robot_stats[$stat]['current'];
            $this->$prop_stat_base = $this_robot_stats[$stat]['current'];
            $this->$prop_stat_max = $this_robot_stats[$stat]['max'];
            // If this robot's player has any stat bonuses, apply them as well
            $prop_player_stat = 'player_'.$stat;
            if (!empty($this->player->$prop_player_stat)){
                $temp_boost = ceil($this->$prop_stat * ($this->player->$prop_player_stat / 100));
                $this->$prop_stat += $temp_boost;
                $this->$prop_stat_base += $temp_boost;
            }

        }

        // Create the stat boost flag
        $this->flags['apply_stat_bonuses'] = true;

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define public print functions for markup generation
    public function print_robot_number(){ return '<span class="robot_number">'.$this->robot_number.'</span>'; }
    public function print_robot_name(){ return '<span class="robot_name robot_type">'.$this->robot_name.'</span>'; } //.'<span>('.preg_replace('#\s+#', ' ', print_r($this->flags, true)).(!empty($this->flags['triggered_weakness']) ? 'true' : 'false').')</span>'
    public function print_robot_token(){ return '<span class="robot_token">'.$this->robot_token.'</span>'; }
    public function print_robot_core(){ return '<span class="robot_core '.(!empty($this->robot_core) ? 'robot_type_'.$this->robot_core : '').'">'.(!empty($this->robot_core) ? ucfirst($this->robot_core) : 'Neutral').'</span>'; }
    public function print_robot_description(){ return '<span class="robot_description">'.$this->robot_description.'</span>'; }
    public function print_robot_energy(){ return '<span class="robot_stat robot_stat_energy">'.$this->robot_energy.'</span>'; }
    public function print_robot_base_energy(){ return '<span class="robot_stat robot_stat_base_energy">'.$this->robot_base_energy.'</span>'; }
    public function print_robot_attack(){ return '<span class="robot_stat robot_stat_attack">'.$this->robot_attack.'</span>'; }
    public function print_robot_base_attack(){ return '<span class="robot_stat robot_stat_base_attack">'.$this->robot_base_attack.'</span>'; }
    public function print_robot_defense(){ return '<span class="robot_stat robot_stat_defense">'.$this->robot_defense.'</span>'; }
    public function print_robot_base_defense(){ return '<span class="robot_stat robot_stat_base_defense">'.$this->robot_base_defense.'</span>'; }
    public function print_robot_speed(){ return '<span class="robot_stat robot_stat_speed">'.$this->robot_speed.'</span>'; }
    public function print_robot_base_speed(){ return '<span class="robot_stat robot_stat_base_speed">'.$this->robot_base_speed.'</span>'; }
    public function print_robot_weaknesses(){
        $this_markup = array();
        foreach ($this->robot_weaknesses AS $this_type){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_resistances(){
        $this_markup = array();
        foreach ($this->robot_resistances AS $this_type){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_affinities(){
        $this_markup = array();
        foreach ($this->robot_affinities AS $this_type){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_immunities(){
        $this_markup = array();
        foreach ($this->robot_immunities AS $this_type){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public function print_robot_quote($quote_type, $this_find = array(), $this_replace = array()){
        global $mmrpg_index;
        // Define the quote text variable
        $quote_text = '';
        // If the robot is visible and has the requested quote text
        if ($this->robot_token != 'robot' && isset($this->robot_quotes[$quote_type])){
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $this->robot_quotes[$quote_type]);
            // Collect the text colour for this robot
            $this_type_token = !empty($this->robot_core) ? $this->robot_core : 'none';
            $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
            $this_text_colour_bak = $this_text_colour;
            $temp_saturator = 1.25;
            if (in_array($this_type_token, array('water','wind'))){ $temp_saturator = 1.5; }
            elseif (in_array($this_type_token, array('earth', 'time', 'impact'))){ $temp_saturator = 1.75; }
            elseif (in_array($this_type_token, array('space', 'shadow'))){ $temp_saturator = 2.0; }
            if ($temp_saturator > 1){
                $temp_overflow = 0;
                foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] = ceil($val * $temp_saturator); if ($this_text_colour[$key] > 255){ $temp_overflow = $this_text_colour[$key] - 255; $this_text_colour[$key] = 255; } }
                if ($temp_overflow > 0){ foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += ceil($temp_overflow / 3); if ($this_text_colour[$key] > 255){ $this_text_colour[$key] = 255; } } }
            }
            // Generate the quote text markup with the appropriate RGB values
            $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
        }
        return $quote_text;
    }




    // Define public print functions for markup generation
    public static function print_robot_info_number($robot_info){ return '<span class="robot_number">'.$robot_info['robot_number'].'</span>'; }
    public static function print_robot_info_name($robot_info){ return '<span class="robot_name robot_type">'.$robot_info['robot_name'].'</span>'; } //.'<span>('.preg_replace('#\s+#', ' ', print_r($this->flags, true)).(!empty($this->flags['triggered_weakness']) ? 'true' : 'false').')</span>'
    public static function print_robot_info_token($robot_info){ return '<span class="robot_token">'.$robot_info['robot_token'].'</span>'; }
    public static function print_robot_info_core($robot_info){ return '<span class="robot_core '.(!empty($robot_info['robot_core']) ? 'robot_type_'.$robot_info['robot_core'] : '').'">'.(!empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral').'</span>'; }
    public static function print_robot_info_description($robot_info){ return '<span class="robot_description">'.$robot_info['robot_description'].'</span>'; }
    public static function print_robot_info_energy($robot_info){ return '<span class="robot_stat robot_stat_energy">'.$robot_info['robot_energy'].'</span>'; }
    public static function print_robot_info_base_energy($robot_info){ return '<span class="robot_stat robot_stat_base_energy">'.$robot_info['robot_base_energy'].'</span>'; }
    public static function print_robot_info_attack($robot_info){ return '<span class="robot_stat robot_stat_attack">'.$robot_info['robot_attack'].'</span>'; }
    public static function print_robot_info_base_attack($robot_info){ return '<span class="robot_stat robot_stat_base_attack">'.$robot_info['robot_base_attack'].'</span>'; }
    public static function print_robot_info_defense($robot_info){ return '<span class="robot_stat robot_stat_defense">'.$robot_info['robot_defense'].'</span>'; }
    public static function print_robot_info_base_defense($robot_info){ return '<span class="robot_stat robot_stat_base_defense">'.$robot_info['robot_base_defense'].'</span>'; }
    public static function print_robot_info_speed($robot_info){ return '<span class="robot_stat robot_stat_speed">'.$robot_info['robot_speed'].'</span>'; }
    public static function print_robot_info_base_speed($robot_info){ return '<span class="robot_stat robot_stat_base_speed">'.$robot_info['robot_base_speed'].'</span>'; }
    public static function print_robot_info_weaknesses($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_weaknesses'] AS $this_type){
            $this_markup[] = '<span class="robot_weakness robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_resistances($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_resistances'] AS $this_type){
            $this_markup[] = '<span class="robot_resistance robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_affinities($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_affinities'] AS $this_type){
            $this_markup[] = '<span class="robot_affinity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_immunities($robot_info){
        $this_markup = array();
        foreach ($robot_info['robot_immunities'] AS $this_type){
            $this_markup[] = '<span class="robot_immunity robot_type robot_type_'.$this_type.'">'.ucfirst($this_type).'</span>';
        }
        $this_markup = implode(', ', $this_markup);
        return $this_markup;
    }
    public static function print_robot_info_quote($robot_info, $quote_type, $this_find = array(), $this_replace = array()){
        global $mmrpg_index;
        // Define the quote text variable
        $quote_text = '';
        // If the robot is visible and has the requested quote text
        if ($robot_info['robot_token'] != 'robot' && isset($robot_info['robot_quotes'][$quote_type])){
            // Collect the quote text with any search/replace modifications
            $this_quote_text = str_replace($this_find, $this_replace, $robot_info['robot_quotes'][$quote_type]);
            // Collect the text colour for this robot
            $this_type_token = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none';
            $this_text_colour = !empty($mmrpg_index['types'][$this_type_token]) ? $mmrpg_index['types'][$this_type_token]['type_colour_light'] : array(200, 200, 200);
            $this_text_colour_bak = $this_text_colour;
            $temp_saturator = 1.25;
            if (in_array($this_type_token, array('water','wind'))){ $temp_saturator = 1.5; }
            elseif (in_array($this_type_token, array('earth', 'time', 'impact'))){ $temp_saturator = 1.75; }
            elseif (in_array($this_type_token, array('space', 'shadow'))){ $temp_saturator = 2.0; }
            if ($temp_saturator > 1){
                $temp_overflow = 0;
                foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] = ceil($val * $temp_saturator); if ($this_text_colour[$key] > 255){ $temp_overflow = $this_text_colour[$key] - 255; $this_text_colour[$key] = 255; } }
                if ($temp_overflow > 0){ foreach ($this_text_colour AS $key => $val){ $this_text_colour[$key] += ceil($temp_overflow / 3); if ($this_text_colour[$key] > 255){ $this_text_colour[$key] = 255; } } }
            }
            // Generate the quote text markup with the appropriate RGB values
            $quote_text = '<span style="color: rgb('.implode(',', $this_text_colour).');">&quot;<em>'.$this_quote_text.'</em>&quot;</span>';
        }
        return $quote_text;
    }


    // Define a function for checking if this robot has a specific ability
    public function has_ability($ability_token){
        if (empty($this->robot_abilities) || empty($ability_token)){ return false; }
        elseif (in_array($ability_token, $this->robot_abilities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is compatible with a specific ability
    static public function has_ability_compatibility($robot_token, $ability_token){
        global $mmrpg_index;
        if (empty($robot_token) || empty($ability_token)){ return false; }
        $robot_info = is_array($robot_token) ? $robot_token : rpg_robot::get_index_info($robot_token);
        $ability_info = is_array($ability_token) ? $ability_token : rpg_ability::get_index_info($ability_token);
        if (empty($robot_info) || empty($ability_info)){ return false; }
        $robot_token = $robot_info['robot_token'];
        $ability_token = $ability_info['ability_token'];
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'has_ability_compatibility('.$robot_token.', '.$ability_token.')');  }
        // Define the compatibility flag and default to false
        $temp_compatible = false;
        // If this ability has a type, check it against this robot
        if (!empty($ability_info['ability_type']) || !empty($ability_info['ability_type2'])){
            //$debug_fragment .= 'has-type '; // DEBUG
            if (!empty($robot_info['robot_core'])){
            //$debug_fragment .= 'has-core '; // DEBUG
                if ($robot_info['robot_core'] == 'copy'){
                    //$debug_fragment .= 'copy-core '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type'])
                    && $ability_info['ability_type'] == $robot_info['robot_core']){
                    //$debug_fragment .= 'core-match1 '; // DEBUG
                    $temp_compatible = true;
                }
                elseif (!empty($ability_info['ability_type2'])
                    && $ability_info['ability_type2'] == $robot_info['robot_core']){
                    //$debug_fragment .= 'core-match2 '; // DEBUG
                    $temp_compatible = true;
                }
            }
        }
        // Otherwise, check to see if this ability is in the robot's level up set
        if (!$temp_compatible && !empty($robot_info['robot_rewards']['abilities'])){
            //$debug_fragment .= 'has-levelup '; // DEBUG
            foreach ($robot_info['robot_rewards']['abilities'] AS $info){
                if ($info['token'] == $ability_info['ability_token']){
                    //$debug_fragment .= ''.$ability_info['ability_token'].'-matched '; // DEBUG
                    $temp_compatible = true;
                    break;
                }
            }
        }
        // Otherwise, see if this robot can be taught vis player only
        if (!$temp_compatible && in_array($ability_info['ability_token'], $robot_info['robot_abilities'])){
            //$debug_fragment .= 'has-playeronly '; // DEBUG
            $temp_compatible = true;
        }
        //$robot_info['robot_abilities']
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'has_ability_compatibility('.$robot_token.', '.$ability_token.') = '.($temp_compatible ? 'true' : 'false').'<br /> <pre>'.print_r($robot_info['robot_abilities'], true).'</pre>');  }
        // DEBUG
        //die('Found '.$debug_fragment.' - robot '.($temp_compatible ? 'is' : 'is not').' compatible!');
        // Return the temp compatible result
        return $temp_compatible;
    }

    // Define a function for checking if this robot has a specific weakness
    public function has_weakness($weakness_token){
        if (empty($this->robot_weaknesses) || empty($weakness_token)){ return false; }
        elseif (in_array($weakness_token, $this->robot_weaknesses)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific resistance
    public function has_resistance($resistance_token){
        if (empty($this->robot_resistances) || empty($resistance_token)){ return false; }
        elseif (in_array($resistance_token, $this->robot_resistances)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific affinity
    public function has_affinity($affinity_token){
        if (empty($this->robot_affinities) || empty($affinity_token)){ return false; }
        elseif (in_array($affinity_token, $this->robot_affinities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot has a specific immunity
    public function has_immunity($immunity_token){
        if (empty($this->robot_immunities) || empty($immunity_token)){ return false; }
        elseif (in_array($immunity_token, $this->robot_immunities)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is above a certain energy percent
    public function above_energy_percent($this_energy_percent){
        $actual_energy_percent = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        if ($actual_energy_percent > $this_energy_percent){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is below a certain energy percent
    public function below_energy_percent($this_energy_percent){
        $actual_energy_percent = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        if ($actual_energy_percent < $this_energy_percent){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in attack boost status
    public function has_attack_boost(){
        if ($this->robot_attack >= ($this->robot_base_attack * 2)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in attack break status
    public function has_attack_break(){
        if ($this->robot_attack <= 0){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in defense boost status
    public function has_defense_boost(){
        if ($this->robot_defense >= ($this->robot_base_defense * 2)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in defense break status
    public function has_defense_break(){
        if ($this->robot_defense <= 0){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in speed boost status
    public function has_speed_boost(){
        if ($this->robot_speed >= ($this->robot_base_speed * 2)){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in speed break status
    public function has_speed_break(){
        if ($this->robot_speed <= 0){ return true; }
        else { return false; }
    }

    // Define a function for checking if this robot is in speed break status
    public static function robot_choices_abilities($objects){
        // Extract all objects into the current scope
        extract($objects);
        global $db;
        // Create the ability options and weights variables
        $options = array();
        $weights = array();
        // Define the support multiplier for this robot
        $support_multiplier = 1;
        if (in_array($this_robot->robot_token, array('roll', 'disco', 'rhythm'))){ $support_multiplier += 1; }
        // Define the freency of the default buster ability if set
        if ($this_robot->has_ability('buster-shot')){ $options[] = 'buster-shot'; $weights[] = $this_robot->robot_token == 'met' ? 90 : 1;  }
        if ($this_robot->has_ability('super-throw')){ $options[] = 'super-throw'; $weights[] = 1;  }
        // Define the frequency of the energy boost ability if set
        if ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy >= $this_robot->robot_base_energy){ $options[] = 'energy-boost'; $weights[] = 0;  }
        elseif ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy < ($this_robot->robot_base_energy / 4)){ $options[] = 'energy-boost'; $weights[] = 14 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy < ($this_robot->robot_base_energy / 3)){ $options[] = 'energy-boost'; $weights[] = 12 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-boost') && $this_robot->robot_energy < ($this_robot->robot_base_energy / 2)){ $options[] = 'energy-boost'; $weights[] = 10 * $support_multiplier;  }
        // Define the frequency of the energy break ability if set
        if ($this_robot->has_ability('energy-break') && $target_robot->robot_energy >= $target_robot->robot_base_energy){ $options[] = 'energy-break'; $weights[] = 28 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-break') && $target_robot->robot_energy < ($target_robot->robot_base_energy / 4)){ $options[] = 'energy-break'; $weights[] = 10 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-break') && $target_robot->robot_energy < ($target_robot->robot_base_energy / 3)){ $options[] = 'energy-break'; $weights[] = 12 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-break') && $target_robot->robot_energy < ($target_robot->robot_base_energy / 2)){ $options[] = 'energy-break'; $weights[] = 14 * $support_multiplier;  }
        // Define the frequency of the energy swap ability if set
        if ($this_robot->has_ability('energy-swap') && $target_robot->robot_energy > $this_robot->robot_energy){ $options[] = 'energy-swap'; $weights[] = 28 * $support_multiplier;  }
        elseif ($this_robot->has_ability('energy-swap') && $target_robot->robot_energy <= $this_robot->robot_energy){ $options[] = 'energy-swap'; $weights[] = 0;  }
        // Define the frequency of the attack, defense, and speed boost abiliies if set
        if ($this_robot->has_ability('attack-boost') && $this_robot->robot_attack < ($this_robot->robot_base_attack * 0.5)){ $options[] = 'attack-boost'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-boost')){ $options[] = 'attack-boost'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-boost') && $this_robot->robot_defense < ($this_robot->robot_base_defense * 0.5)){ $options[] = 'defense-boost'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-boost')){ $options[] = 'defense-boost'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-boost') && $this_robot->robot_speed < ($this_robot->robot_base_speed * 0.5)){ $options[] = 'speed-boost'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-boost')){ $options[] = 'speed-boost'; $weights[] = 1;  }
        // Define the frequency of the attack, defense, and speed break abilities if set
        if ($this_robot->has_ability('attack-break') && $target_robot->robot_attack > $this_robot->robot_defense){ $options[] = 'attack-break'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-break')){ $options[] = 'attack-break'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-break') && $target_robot->robot_defense > $this_robot->robot_attack){ $options[] = 'defense-break'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-break')){ $options[] = 'defense-break'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-break') && $this_robot->robot_speed < $target_robot->robot_speed){ $options[] = 'speed-break'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-break')){ $options[] = 'speed-break'; $weights[] = 1;  }
        // Define the frequency of the attack, defense, and speed swap abilities if set
        if ($this_robot->has_ability('attack-swap') && $target_robot->robot_attack > $this_robot->robot_attack){ $options[] = 'attack-swap'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-swap')){ $options[] = 'attack-swap'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-swap') && $target_robot->robot_defense > $this_robot->robot_defense){ $options[] = 'defense-swap'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-swap')){ $options[] = 'defense-swap'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-swap') && $target_robot->robot_speed > $this_robot->robot_speed){ $options[] = 'speed-swap'; $weights[] = 3 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-swap')){ $options[] = 'speed-swap'; $weights[] = 1;  }
        // Define the frequency of the repair mode ability if set
        if ($this_robot->has_ability('repair-mode') && $this_robot->robot_energy < ($this_robot->robot_base_energy * 0.5)){ $options[] = 'repair-mode'; $weights[] = 9 * $support_multiplier;  }
        elseif ($this_robot->has_ability('repair-mode')){ $options[] = 'repair-mode'; $weights[] = 1;  }
        // Define the frequency of the attack, defense, and speed mode abilities if set
        if ($this_robot->has_ability('attack-mode') && $this_robot->robot_attack < ($this_robot->robot_base_attack * 0.10)){ $options[] = 'attack-mode'; $weights[] = 6 * $support_multiplier;  }
        elseif ($this_robot->has_ability('attack-mode')){ $options[] = 'attack-mode'; $weights[] = 1;  }
        if ($this_robot->has_ability('defense-mode') && $this_robot->robot_defense < ($this_robot->robot_base_defense * 0.10)){ $options[] = 'defense-mode'; $weights[] = 6 * $support_multiplier;  }
        elseif ($this_robot->has_ability('defense-mode')){ $options[] = 'defense-mode'; $weights[] = 1;  }
        if ($this_robot->has_ability('speed-mode') && $this_robot->robot_speed < ($this_robot->robot_base_speed * 0.10)){ $options[] = 'speed-mode'; $weights[] = 6 * $support_multiplier;  }
        elseif ($this_robot->has_ability('speed-mode')){ $options[] = 'speed-mode'; $weights[] = 1;  }
        // Loop through any leftover abilities and add them to the weighted ability options
        $temp_ability_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
        foreach ($this_robot->robot_abilities AS $key => $token){
            if (!in_array($token, $options)){
                $info = rpg_ability::parse_index_info($temp_ability_index[$token]);
                $value = 3;
                if (!empty($this_robot->robot_core) && !empty($info['ability_type'])){
                    if ($this_robot->robot_core == $info['ability_type']){ $value = 50; }
                    elseif ($this_robot->robot_core == 'copy'){ $value = 40; }
                    elseif ($this_robot->robot_core != $info['ability_type']){ $value = 30; }
                    if (preg_match('/^(attack|defense|speed)-(burn|blaze)$/i', $token)){ $value = ceil($value * 0.10); }
                } elseif (empty($this_robot->robot_core)){
                    $value = 30;
                } else {
                    $value = 3;
                }
                $options[] = $token;
                $weights[] = $value;
            }
        }
        // Return an ability based on a weighted chance
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'robot_choices_abilities('.$this_robot->robot_token.')<br /> $options = '.implode(',', $options).'<br /> $weights = '.implode(',', $weights).'<br /> $this_robot->robot_abilities = '.implode(',', $this_robot->robot_abilities));  }
        return $this_battle->weighted_chance($options, $weights);
    }

    // Define a trigger for using one of this robot's abilities
    public function trigger_ability($target_robot, $this_ability){
        global $db;
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

        // Update this robot's history with the triggered ability
        $this->history['triggered_abilities'][] = $this_ability->ability_token;

        // Define a variable to hold the ability results
        $this_ability->ability_results = array();
        $this_ability->ability_results['total_result'] = '';
        $this_ability->ability_results['total_actions'] = 0;
        $this_ability->ability_results['total_strikes'] = 0;
        $this_ability->ability_results['total_misses'] = 0;
        $this_ability->ability_results['total_amount'] = 0;
        $this_ability->ability_results['total_overkill'] = 0;
        $this_ability->ability_results['this_result'] = '';
        $this_ability->ability_results['this_amount'] = 0;
        $this_ability->ability_results['this_overkill'] = 0;
        $this_ability->ability_results['this_text'] = '';
        $this_ability->ability_results['counter_criticals'] = 0;
        $this_ability->ability_results['counter_affinities'] = 0;
        $this_ability->ability_results['counter_weaknesses'] = 0;
        $this_ability->ability_results['counter_resistances'] = 0;
        $this_ability->ability_results['counter_immunities'] = 0;
        $this_ability->ability_results['counter_coreboosts'] = 0;
        $this_ability->ability_results['flag_critical'] = false;
        $this_ability->ability_results['flag_affinity'] = false;
        $this_ability->ability_results['flag_weakness'] = false;
        $this_ability->ability_results['flag_resistance'] = false;
        $this_ability->ability_results['flag_immunity'] = false;

        // Reset the ability options to default
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_ability->target_options_reset();
        $this_ability->damage_options_reset();
        $this_ability->recovery_options_reset();

        // Determine how much weapon energy this should take
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $temp_ability_energy = $this->calculate_weapon_energy($this_ability);

        // Decrease this robot's weapon energy
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this->robot_weapons = $this->robot_weapons - $temp_ability_energy;
        if ($this->robot_weapons < 0){ $this->robot_weapons = 0; }
        $this->update_session();

        // Default this and the target robot's frames to their base
        $this->robot_frame = 'base';
        $target_robot->robot_frame = 'base';

        // Default the robot's stances to attack/defend
        $this->robot_stance = 'attack';
        $target_robot->robot_stance = 'defend';

        // If this is a copy core robot and the ability type does not match its core
        $temp_image_changed = false;
        $temp_ability_type = !empty($this_ability->ability_type) ? $this_ability->ability_type : '';
        $temp_ability_type2 = !empty($this_ability->ability_type2) ? $this_ability->ability_type2 : $temp_ability_type;
        if (!preg_match('/^item-/', $this_ability->ability_token) && !empty($temp_ability_type) && $this->robot_base_core == 'copy'){
            $this->robot_image_overlay['copy_type1'] = $this->robot_base_image.'_'.$temp_ability_type.'2';
            $this->robot_image_overlay['copy_type2'] = $this->robot_base_image.'_'.$temp_ability_type2.'3';
            $this->update_session();
            $temp_image_changed = true;
        }

        // Copy the ability function to local scope and execute it
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_ability_function = $this_ability->ability_function;
        $this_ability_function(array(
            'this_battle' => $this->battle,
            'this_field' => $this->field,
            'this_player' => $this->player,
            'this_robot' => $this,
            'target_player' => $target_robot->player,
            'target_robot' => $target_robot,
            'this_ability' => $this_ability
            ));


        // If this robot's image has been changed, reveert it back to what it was
        if ($temp_image_changed){
            unset($this->robot_image_overlay['copy_type1']);
            unset($this->robot_image_overlay['copy_type2']);
            $this->update_session();
        }

        // DEBUG DEBUG DEBUG
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;
        // Update this ability's history with the triggered ability damage options
        $this_ability->history['ability_options'][] = $this_ability->ability_options;

        // Reset the robot's stances to the base
        $this->robot_stance = 'base';
        $target_robot->robot_stance = 'base';

        // Update internal variables
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $target_robot->update_session();
        $this_ability->update_session();


        // -- CHECK ATTACHMENTS -- //

        // If this robot has any attachments, loop through them
        if (!empty($this->robot_attachments)){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
            $temp_attachments_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
            foreach ($this->robot_attachments AS $attachment_token => $attachment_info){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                // Ensure this ability has a type before checking weaknesses, resistances, etc.
                if (!empty($this_ability->ability_type)){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    // If this attachment has weaknesses defined and this ability is a match
                    if (!empty($attachment_info['attachment_weaknesses'])
                        && (in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses'])
                            || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))
                            ){
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                        // Remove this attachment and inflict damage on the robot
                        unset($this->robot_attachments[$attachment_token]);
                        $this->update_session();
                        if ($attachment_info['attachment_destroy'] !== false){
                            $temp_ability = rpg_ability::parse_index_info($temp_attachments_index[$attachment_info['ability_token']]);
                            $attachment_info = array_merge($temp_ability, $attachment_info);
                            $temp_attachment = new rpg_ability($this->battle, $this->player, $this, $attachment_info);
                            $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                            //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
                            if ($temp_trigger_type == 'damage'){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                    $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_damage($target_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'recovery'){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                    $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                    $temp_trigger_options = array('apply_modifiers' => false);
                                    $this->trigger_recovery($target_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                                }
                            } elseif ($temp_trigger_type == 'special'){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                $temp_attachment->update_session();
                                //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                            }
                        }
                        // If this robot was disabled, process experience for the target
                        if ($this->robot_status == 'disabled'){
                            break;
                        }
                    }

                }

            }
        }

        // Update internal variables
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        return $this_ability->ability_results;
    }

    // Define a function for setting and saving a robot attachment
    public function has_attachment($attachment_token){
        return isset($this->robot_attachments[$attachment_token]);
    }

    // Define a function for setting and saving a robot attachment
    public function set_attachment($attachment_token, $attachment_info){
        $this->robot_attachments[$attachment_token] = $attachment_info;
        $this->update_session();
        return true;
    }

    // Define a function for unsetting a robot attachment
    public function unset_attachment($attachment_token){
        unset($this->robot_attachments[$attachment_token]);
        $this->update_session();
        return true;
    }

    // Define a trigger for using one of this robot's attachments
    public function trigger_attachment($attachment_info){
        global $db;

        // If this is an ability attachment
        if ($attachment_info['class'] == 'ability'){

            // Create the temporary ability object
            $this_ability = new rpg_ability($this->battle, $this->player, $this, array('ability_token' => $attachment_info['ability_token']));

            // Update this robot's history with the triggered attachment
            $this->history['triggered_attachments'][] = 'ability_'.$this_ability->ability_token;

            // Define a variable to hold the ability results
            $this_ability->attachment_results = array();
            $this_ability->attachment_results['total_result'] = '';
            $this_ability->attachment_results['total_actions'] = 0;
            $this_ability->attachment_results['total_strikes'] = 0;
            $this_ability->attachment_results['total_misses'] = 0;
            $this_ability->attachment_results['total_amount'] = 0;
            $this_ability->attachment_results['total_overkill'] = 0;
            $this_ability->attachment_results['this_result'] = '';
            $this_ability->attachment_results['this_amount'] = 0;
            $this_ability->attachment_results['this_overkill'] = 0;
            $this_ability->attachment_results['this_text'] = '';
            $this_ability->attachment_results['counter_critical'] = 0;
            $this_ability->attachment_results['counter_affinity'] = 0;
            $this_ability->attachment_results['counter_weakness'] = 0;
            $this_ability->attachment_results['counter_resistance'] = 0;
            $this_ability->attachment_results['counter_immunity'] = 0;
            $this_ability->attachment_results['counter_coreboosts'] = 0;
            $this_ability->attachment_results['flag_critical'] = false;
            $this_ability->attachment_results['flag_affinity'] = false;
            $this_ability->attachment_results['flag_weakness'] = false;
            $this_ability->attachment_results['flag_resistance'] = false;
            $this_ability->attachment_results['flag_immunity'] = false;

            // Reset the ability options to default
            $this_ability->attachment_options_reset();

            // Default this and the target robot's frames to their base
            $this->robot_frame = 'base';
            //$target_robot->robot_frame = 'base';

            // Collect the target robot and player objects
            //$target_robot_info = $this->battle->values['robots'][];

            // Copy the attachment function to local scope and execute it
            $this_attachment_function = $this_ability->ability_function_attachment;
            $this_attachment_function(array(
                'this_battle' => $this->battle,
                'this_field' => $this->field,
                'this_player' => $this->player,
                'this_robot' => $this,
                //'target_player' => $target_robot->player,
                //'target_robot' => $target_robot,
                'this_ability' => $this_ability
                ));

            // Update this ability's attachment history with the triggered attachment data and results
            $this_ability->history['attachment_results'][] = $this_ability->attachment_results;
            // Update this ability's attachment history with the triggered attachment damage options
            $this_ability->history['attachment_options'][] = $this_ability->attachment_options;

            // Reset the robot's stances to the base
            $this->robot_stance = 'base';
            //$target_robot->robot_stance = 'base';

            // Update internal variables
            $this->update_session();
            $this_ability->update_session();

            // Return the ability results
            return $this_ability->attachment_results;

        }

    }

//  // Define separate trigger functions for each type of damage on this robot
//  public function trigger_energy_damage($target_robot, $this_ability, &$ability_results, $damage_amount, &$damage_options){
//    $this->trigger_damage('energy', $target_robot, $this_ability, &$ability_results, $damage_amount, &$damage_options);
//  }

    // Define a trigger for using one of this robot's abilities
    public function trigger_target($target_robot, $this_ability, $trigger_options = array()){
        global $db;

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
        $event_options['this_ability_target_key'] = $target_robot->robot_key;
        $event_options['this_ability_target_position'] = $target_robot->robot_position;
        $event_options['this_ability_results'] = array();
        $event_options['console_show_target'] = false;

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update this robot's history with the triggered ability
        $this->history['triggered_targets'][] = $target_robot->robot_token;

        // Backup this and the target robot's frames to revert later
        $this_robot_backup_frame = $this->robot_frame;
        $this_player_backup_frame = $this->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Update this robot's frames using the target options
        $this->robot_frame = $this_ability->target_options['target_frame'];
        if ($this->robot_id != $target_robot->robot_id){ $target_robot->robot_frame = 'defend'; }
        $this->player->player_frame = 'command';
        $this->player->update_session();
        $this_ability->ability_frame = $this_ability->target_options['ability_success_frame'];
        $this_ability->ability_frame_span = $this_ability->target_options['ability_success_frame_span'];
        $this_ability->ability_frame_offset = $this_ability->target_options['ability_success_frame_offset'];

        // If the target player is on the bench, alter the ability scale
        $temp_ability_styles_backup = $this_ability->ability_frame_styles;
        if ($target_robot->robot_position == 'bench' && $event_options['this_ability_target'] != $this->robot_id.'_'.$this->robot_token){
            $temp_scale = 1 - ($target_robot->robot_key * 0.06);
            $temp_translate = 20 + ($target_robot->robot_key * 20);
            $temp_translate2 = ceil($temp_translate / 10) * -1;
            $temp_translate = $temp_translate * ($target_robot->player->player_side == 'left' ? -1 : 1);
            //$this_ability->ability_frame_styles .= 'border: 1px solid red !important; ';
            $this_ability->ability_frame_styles .= 'transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -webkit-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); -moz-transform: scale('.$temp_scale.', '.$temp_scale.') translate('.$temp_translate.'px, '.$temp_translate2.'px); ';
        }

        // Create a message to show the initial targeting action
        if ($this->robot_id != $target_robot->robot_id && empty($trigger_options['prevent_default_text'])){
            $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} targets {$target_robot->print_robot_name()}!<br />";
        } else {
            //$this_ability->ability_results['this_text'] .= ''; //"{$this->print_robot_name()} targets itself&hellip;<br />";
        }

        // Append the targetting text to the event body
        $this_ability->ability_results['this_text'] .= $this_ability->target_options['target_text'];

        // Update the ability results with the the trigger kind
        $this_ability->ability_results['trigger_kind'] = 'target';
        $this_ability->ability_results['this_result'] = 'success';

        // Update the event options with the ability results
        $event_options['this_ability_results'] = $this_ability->ability_results;
        if (isset($trigger_options['canvas_show_this_ability'])){ $event_options['canvas_show_this_ability'] = $trigger_options['canvas_show_this_ability'];  }

        /*
        // If this is a non-transformed copy robot, change its colour
        $temp_image_changed = false;
        $temp_ability_type = !empty($this_ability->ability_type) && $this_ability->ability_type != 'copy' ? $this_ability->ability_type : '';
        if ($this->robot_base_core == 'copy' && $this->robot_core != $temp_ability_type){
            $this_backup_image = $this->robot_image;
            $this->robot_image = $this->robot_base_image.'_'.$temp_ability_type;
            $this->update_session();
            $temp_image_changed = true;
        }
        */

        // Create a new entry in the event log for the targeting event
        $this->battle->events_create($this, $target_robot, $this_ability->target_options['target_header'], $this_ability->ability_results['this_text'], $event_options);

        /*
        // If this is a non-transformed copy robot, change its colour
        if ($temp_image_changed){
            $this->robot_image = $this_backup_image;
            $this->update_session();
        }
        */

        // Update this ability's history with the triggered ability data and results
        $this_ability->history['ability_results'][] = $this_ability->ability_results;

        // Refresh the ability styles from any changes
        $this_ability->ability_frame_styles = ''; //$temp_ability_styles_backup;

        // restore this and the target robot's frames to their backed up state
        $this->robot_frame = $this_robot_backup_frame;
        $this->player->player_frame = $this_player_backup_frame;
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->player_frame = $target_player_backup_frame;
        $this_ability->ability_frame = $this_ability_backup_frame;
        $this_ability->target_options_reset();

        // Update internal variables
        $this->update_session();
        $this->player->update_session();
        $target_robot->update_session();
        $this_ability->update_session();

        // Return the ability results
        return $this_ability->ability_results;

    }

    // Define a trigger for inflicting all types of damage on this robot
    public function trigger_damage($target_robot, $this_ability, $damage_amount, $trigger_disabled = true, $trigger_options = array()){
        global $db;

        // Generate default trigger options if not set
        if (!isset($trigger_options['apply_modifiers'])){ $trigger_options['apply_modifiers'] = true; }
        if (!isset($trigger_options['apply_type_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_type_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_core_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_core_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_position_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_position_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_field_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_field_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_starforce_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_starforce_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_stat_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_stat_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['referred_damage'])){ $trigger_options['referred_damage'] = false; }
        /*
         * ROBOT CLASS FUNCTION TRIGGER DAMAGE
         * public function trigger_damage($target_robot, $this_ability, $damage_amount, $trigger_disabled = true){}
         */

        // Backup this and the target robot's frames to revert later
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_robot_backup_frame = $this->robot_frame;
        $this_player_backup_frame = $this->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Check if this robot is at full health before triggering
        $this_robot_energy_start = $this->robot_energy;
        $this_robot_energy_start_max = $this_robot_energy_start >= $this->robot_base_energy ? true : false;

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_results'] = array();

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update the damage to whatever was supplied in the argument
        //if ($this_ability->damage_options['damage_percent'] && $damage_amount > 100){ $damage_amount = 100; }
        $this_ability->damage_options['damage_amount'] = $damage_amount;

        // Collect the damage amount argument from the function
        $this_ability->ability_results['this_amount'] = $damage_amount;
        // DEBUG
        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | this('.$this->robot_id.') vs target('.$target_robot->robot_id.') | damage_start_amount |<br /> '.'amount:'.$this_ability->ability_results['this_amount'].' | '.'percent:'.($this_ability->damage_options['damage_percent'] ? 'true' : 'false').' | '.'kind:'.$this_ability->damage_options['damage_kind'].' | type1:'.(!empty($this_ability->damage_options['damage_type']) ? $this_ability->damage_options['damage_type'] : 'none').' | type2:'.(!empty($this_ability->damage_options['damage_type2']) ? $this_ability->damage_options['damage_type2'] : 'none').'');


        // DEBUG
        $debug = '';
        foreach ($trigger_options AS $key => $value){ $debug .= $key.'='.($value === true ? 'true' : ($value === false ? 'false' : $value)).'; '; }
        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' : damage_trigger_options : '.$debug);

        /*
        // Only apply weakness, resistance, etc. if not percent
        if (true || !$this_ability->damage_options['damage_percent']){
        */
        // Only apply modifiers if they have not been disabled
        if ($trigger_options['apply_modifiers'] != false){

            // Skip all weakness, resistance, etc. calculations if robot is targetting self
            if ($trigger_options['apply_type_modifiers'] != false && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_damage'])){

                // If target robot has affinity to the ability (based on type)
                if ($this->has_affinity($this_ability->damage_options['damage_type']) && !$this->has_weakness($this_ability->damage_options['damage_type2'])){
                    //$this_ability->ability_results['counter_affinities'] += 1;
                    //$this_ability->ability_results['flag_affinity'] = true;
                    return $this->trigger_recovery($target_robot, $this_ability, $damage_amount);
                } else {
                    $this_ability->ability_results['flag_affinity'] = false;
                }

                // If target robot has affinity to the ability (based on type2)
                if ($this->has_affinity($this_ability->damage_options['damage_type2']) && !$this->has_weakness($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_affinities'] += 1;
                    $this_ability->ability_results['flag_affinity'] = true;
                    return $this->trigger_recovery($target_robot, $this_ability, $damage_amount);
                }

                // If this robot has weakness to the ability (based on type)
                if ($this->has_weakness($this_ability->damage_options['damage_type']) && !$this->has_affinity($this_ability->damage_options['damage_type2'])){
                    $this_ability->ability_results['counter_weaknesses'] += 1;
                    $this_ability->ability_results['flag_weakness'] = true;
                } else {
                    $this_ability->ability_results['flag_weakness'] = false;
                }

                // If this robot has weakness to the ability (based on type2)
                if ($this->has_weakness($this_ability->damage_options['damage_type2']) && !$this->has_affinity($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_weaknesses'] += 1;
                    $this_ability->ability_results['flag_weakness'] = true;
                }

                // If target robot has resistance tp the ability (based on type)
                if ($this->has_resistance($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                } else {
                    $this_ability->ability_results['flag_resistance'] = false;
                }

                // If target robot has resistance tp the ability (based on type2)
                if ($this->has_resistance($this_ability->damage_options['damage_type2'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                }

                // If target robot has immunity to the ability (based on type)
                if ($this->has_immunity($this_ability->damage_options['damage_type'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                } else {
                    $this_ability->ability_results['flag_immunity'] = false;
                }

                // If target robot has immunity to the ability (based on type2)
                if ($this->has_immunity($this_ability->damage_options['damage_type2'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                }

            }

            // Apply core boosts if allowed to
            if ($trigger_options['apply_core_modifiers'] != false){

                // If the target robot is using an ability that it is the same type as its core
                if (!empty($target_robot->robot_core) && $target_robot->robot_core == $this_ability->damage_options['damage_type']){
                    $this_ability->ability_results['counter_coreboosts'] += 1;
                    $this_ability->ability_results['flag_coreboost'] = true;
                } else {
                    $this_ability->ability_results['flag_coreboost'] = false;
                }

                // If the target robot is using an ability that it is the same type as its core
                if (!empty($target_robot->robot_core) && $target_robot->robot_core == $this_ability->damage_options['damage_type2']){
                    $this_ability->ability_results['counter_coreboosts'] += 1;
                    $this_ability->ability_results['flag_coreboost'] = true;
                }

            }

            // Apply position boosts if allowed to
            if ($trigger_options['apply_position_modifiers'] != false){

                // If this robot is not in the active position
                if ($this->robot_position != 'active'){
                    // Collect the current key of the robot and apply damage mods
                    $temp_damage_key = $this->robot_key + 1;
                    $temp_damage_resistor = (10 - $temp_damage_key) / 10;
                    $new_damage_amount = round($damage_amount * $temp_damage_resistor);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | position_modifier_damage | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_damage_resistor.') = '.$new_damage_amount.'');
                    $damage_amount = $new_damage_amount;
                }

            }

        }

        // Apply field multipliers preemtively if there are any
        if ($trigger_options['apply_field_modifiers'] != false && $this_ability->damage_options['damage_modifiers'] && !empty($this->field->field_multipliers)){

            // Collect the multipliters for easier
            $field_multipliers = $this->field->field_multipliers;

            // Collect the ability types else "none" for multipliers
            $temp_ability_damage_type = !empty($this_ability->damage_options['damage_type']) ? $this_ability->damage_options['damage_type'] : 'none';
            $temp_ability_damage_type2 = !empty($this_ability->damage_options['damage_type2']) ? $this_ability->damage_options['damage_type2'] : '';

            // If there's a damage booster, apply that first
            if (isset($field_multipliers['damage'])){
                $new_damage_amount = round($damage_amount * $field_multipliers['damage']);
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_damage | '.$damage_amount.' = round('.$damage_amount.' * '.$field_multipliers['damage'].') = '.$new_damage_amount.'');
                $damage_amount = $new_damage_amount;
            }

            // Loop through all the other type multipliers one by one if this ability has a type
            $skip_types = array('damage', 'recovery', 'experience');
            foreach ($field_multipliers AS $temp_type => $temp_multiplier){
                // Skip non-type and special fields for this calculation
                if (in_array($temp_type, $skip_types)){ continue; }
                // If this ability's type matches the multiplier, apply it
                if ($temp_ability_damage_type == $temp_type){
                    $new_damage_amount = round($damage_amount * $temp_multiplier);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_multiplier.') = '.$new_damage_amount.'');
                    $damage_amount = $new_damage_amount;
                }
                // If this ability's type2 matches the multiplier, apply it
                if ($temp_ability_damage_type2 == $temp_type){
                    $new_damage_amount = round($damage_amount * $temp_multiplier);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_multiplier.') = '.$new_damage_amount.'');
                    $damage_amount = $new_damage_amount;
                }
            }


        }

        // Apply starforce multipliers preemtively if there are any
        if ($trigger_options['apply_starforce_modifiers'] != false && $this_ability->damage_options['damage_modifiers'] && !empty($target_robot->player->player_starforce) && $this->robot_id != $target_robot->robot_id){

            // Collect this and the target player's starforce levels
            $this_starforce = $this->player->player_starforce;
            $target_starforce = $target_robot->player->player_starforce;

            // Loop through and neutralize target starforce levels if possible
            $target_starforce_modified = $target_starforce;
            foreach ($target_starforce_modified AS $type => $target_value){
                if (!isset($this_starforce[$type])){ $this_starforce[$type] = 0; }
                $target_value -= $this_starforce[$type];
                if ($target_value < 0){ $target_value = 0; }
                $target_starforce_modified[$type] = $target_value;
            }

            // Collect the ability types else "none" for multipliers
            $temp_ability_damage_type = !empty($this_ability->damage_options['damage_type']) ? $this_ability->damage_options['damage_type'] : '';
            $temp_ability_damage_type2 = !empty($this_ability->damage_options['damage_type2']) ? $this_ability->damage_options['damage_type2'] : '';

            // Apply any starforce bonuses for ability type
            if (!empty($temp_ability_damage_type) && !empty($target_starforce_modified[$temp_ability_damage_type])){
                $temp_multiplier = 1 + ($target_starforce_modified[$temp_ability_damage_type] / 10);
                $new_damage_amount = round($damage_amount * $temp_multiplier);
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_damage_type.' | force:'.$target_starforce[$temp_ability_damage_type].' vs resist:'.$this_starforce[$temp_ability_damage_type].' = '.($target_starforce_modified[$temp_ability_damage_type] * 10).'% boost | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_multiplier.') = '.$new_damage_amount.'');
                $damage_amount = $new_damage_amount;
            } elseif (!empty($temp_ability_damage_type) && isset($target_starforce_modified[$temp_ability_damage_type])){
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_damage_type.' | force:'.$target_starforce[$temp_ability_damage_type].' vs resist:'.$this_starforce[$temp_ability_damage_type].' = no boost');
            }

            // Apply any starforce bonuses for ability type2
            if (!empty($temp_ability_damage_type2) && !empty($target_starforce_modified[$temp_ability_damage_type2])){
                $temp_multiplier = 1 + ($target_starforce_modified[$temp_ability_damage_type2] / 10);
                $new_damage_amount = round($damage_amount * $temp_multiplier);
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_damage_type2.' | force:'.$target_starforce[$temp_ability_damage_type2].' vs resist:'.$this_starforce[$temp_ability_damage_type2].' = '.($target_starforce_modified[$temp_ability_damage_type2] * 10).'% boost | '.$damage_amount.' = round('.$damage_amount.' * '.$temp_multiplier.') = '.$new_damage_amount.'');
                $damage_amount = $new_damage_amount;
            } elseif (!empty($temp_ability_damage_type2) && isset($target_starforce_modified[$temp_ability_damage_type2])){
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_damage_type2.' | force:'.$target_starforce[$temp_ability_damage_type2].' vs resist:'.$this_starforce[$temp_ability_damage_type2].' = no boost');
            }

        }

        // Update the ability results with the the trigger kind and damage details
        $this_ability->ability_results['trigger_kind'] = 'damage';
        $this_ability->ability_results['damage_kind'] = $this_ability->damage_options['damage_kind'];
        $this_ability->ability_results['damage_type'] = $this_ability->damage_options['damage_type'];

        // If the success rate was not provided, auto-calculate
        if ($this_ability->damage_options['success_rate'] == 'auto'){
            // If this robot is targetting itself, default to ability accuracy
            if ($this->robot_id == $target_robot->robot_id){
                // Update the success rate to the ability accuracy value
                $this_ability->damage_options['success_rate'] = $this_ability->ability_accuracy;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($target_robot->robot_speed <= 0 && $this->robot_speed > 0){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->damage_options['success_rate'] = 0;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($this->robot_speed <= 0 || $this_ability->ability_accuracy == 100){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->damage_options['success_rate'] = 100;
            }
            // Otherwise, calculate the success rate based on relative speeds
            else {
                // Collect this ability's accuracy stat for modification
                $this_ability_accuracy = $this_ability->ability_accuracy;
                // If the target was faster/slower, boost/lower the ability accuracy
                if ($target_robot->robot_speed > $this->robot_speed
                    || $target_robot->robot_speed < $this->robot_speed){
                    $this_modifier = $target_robot->robot_speed / $this->robot_speed;
                    //$this_ability_accuracy = ceil($this_ability_accuracy * $this_modifier);
                    $this_ability_accuracy = ceil($this_ability_accuracy * 0.95) + ceil(($this_ability_accuracy * 0.05) * $this_modifier);
                    if ($this_ability_accuracy > 100){ $this_ability_accuracy = 100; }
                    elseif ($this_ability_accuracy < 0){ $this_ability_accuracy = 0; }
                }
                // Update the success rate to the ability accuracy value
                $this_ability->damage_options['success_rate'] = $this_ability_accuracy;
                //$this_ability->ability_results['this_text'] .= '';
            }
        }

        // If the failure rate was not provided, auto-calculate
        if ($this_ability->damage_options['failure_rate'] == 'auto'){
            // Set the failure rate to the difference of success vs failure (100% base)
            $this_ability->damage_options['failure_rate'] = 100 - $this_ability->damage_options['success_rate'];
            if ($this_ability->damage_options['failure_rate'] < 0){
                $this_ability->damage_options['failure_rate'] = 0;
            }
        }

        // If this robot is in speed break, increase success rate, reduce failure
        if ($this->robot_speed == 0 && $this_ability->damage_options['success_rate'] > 0){
            $this_ability->damage_options['success_rate'] = ceil($this_ability->damage_options['success_rate'] * 2);
            $this_ability->damage_options['failure_rate'] = ceil($this_ability->damage_options['failure_rate'] / 2);
        }
        // If the target robot is in speed break, decease the success rate, increase failure
        elseif ($target_robot->robot_speed == 0 && $this_ability->damage_options['success_rate'] > 0){
            $this_ability->damage_options['success_rate'] = ceil($this_ability->damage_options['success_rate'] / 2);
            $this_ability->damage_options['failure_rate'] = ceil($this_ability->damage_options['failure_rate'] * 2);
        }

        // If success rate is at 100%, auto-set the result to success
        if ($this_ability->damage_options['success_rate'] == 100){
            // Set this ability result as a success
            $this_ability->damage_options['failure_rate'] = 0;
            $this_ability->ability_results['this_result'] = 'success';
        }
        // Else if the success rate is at 0%, auto-set the result to failure
        elseif ($this_ability->damage_options['success_rate'] == 0){
            // Set this ability result as a failure
            $this_ability->damage_options['failure_rate'] = 100;
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise, use a weighted random generation to get the result
        else {
            // Calculate whether this attack was a success, based on the success vs. failure rate
            $this_ability->ability_results['this_result'] = $this->battle->weighted_chance(
                array('success','failure'),
                array($this_ability->damage_options['success_rate'], $this_ability->damage_options['failure_rate'])
                );
        }

        // If this is ENERGY damage and this robot is already disabled
        if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->robot_energy <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // If this is WEAPONS recovery and this robot is already at empty ammo
        elseif ($this_ability->damage_options['damage_kind'] == 'weapons' && $this->robot_weapons <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if ATTACK damage but attack is already zero
        elseif ($this_ability->damage_options['damage_kind'] == 'attack' && $this->robot_attack <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if DEFENSE damage but defense is already zero
        elseif ($this_ability->damage_options['damage_kind'] == 'defense' && $this->robot_defense <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if SPEED damage but speed is already zero
        elseif ($this_ability->damage_options['damage_kind'] == 'speed' && $this->robot_speed <= 0){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }

        // If this robot has immunity to the ability, hard-code a failure result
        if ($this_ability->ability_results['flag_immunity']){
            $this_ability->ability_results['this_result'] = 'failure';
            $this->flags['triggered_immunity'] = true;
            // Generate the status text based on flags
            $this_flag_name = 'immunity_text';
            if (isset($this_ability->damage_options[$this_flag_name])){
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->damage_options[$this_flag_name].'<br /> ';
            }
        }

        // If the attack was a success, proceed normally
        if ($this_ability->ability_results['this_result'] == 'success'){

            // Create the experience multiplier if not already set
            if (!isset($this->field->field_multipliers['experience'])){ $this->field->field_multipliers['experience'] = 1; }
            elseif ($this->field->field_multipliers['experience'] < 0.1){ $this->field->field_multipliers['experience'] = 0.1; }
            elseif ($this->field->field_multipliers['experience'] > 9.9){ $this->field->field_multipliers['experience'] = 9.9; }

            // If modifiers are not turned off
            if ($trigger_options['apply_modifiers'] != false){

                // Update this robot's internal flags based on ability effects
                if (!empty($this_ability->ability_results['flag_weakness'])){
                    $this->flags['triggered_weakness'] = true;
                    if (isset($this->counters['triggered_weakness'])){ $this->counters['triggered_weakness'] += 1; }
                    else { $this->counters['triggered_weakness'] = 1; }
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){
                        $this->field->field_multipliers['experience'] += 0.1;
                        $this_ability->damage_options['damage_kickback']['x'] = ceil($this_ability->damage_options['damage_kickback']['x'] * 2);
                    }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_affinity'])){
                    $this->flags['triggered_affinity'] = true;
                    if (isset($this->counters['triggered_affinity'])){ $this->counters['triggered_affinity'] += 1; }
                    else { $this->counters['triggered_affinity'] = 1; }
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_resistance'])){
                    $this->flags['triggered_resistance'] = true;
                    if (isset($this->counters['triggered_resistance'])){ $this->counters['triggered_resistance'] += 1; }
                    else { $this->counters['triggered_resistance'] = 1; }
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_critical'])){
                    $this->flags['triggered_critical'] = true;
                    if (isset($this->counters['triggered_critical'])){ $this->counters['triggered_critical'] += 1; }
                    else { $this->counters['triggered_critical'] = 1; }
                    if ($this_ability->damage_options['damage_kind'] == 'energy' && $this->player->player_side == 'right'){
                        $this->field->field_multipliers['experience'] += 0.1;
                        $this_ability->damage_options['damage_kickback']['x'] = ceil($this_ability->damage_options['damage_kickback']['x'] * 2);
                    }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }

            }

            // Update the field session with any changes
            $this->field->update_session();

            // Update this robot's frame based on damage type
            $this->robot_frame = $this_ability->damage_options['damage_frame'];
            $this->player->player_frame = ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_damage']) ? 'damage' : 'base';
            $this_ability->ability_frame = $this_ability->damage_options['ability_success_frame'];
            $this_ability->ability_frame_offset = $this_ability->damage_options['ability_success_frame_offset'];

            // Display the success text, if text has been provided
            if (!empty($this_ability->damage_options['success_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->damage_options['success_text'];
            }

            // Collect the damage amount argument from the function
            $this_ability->ability_results['this_amount'] = $damage_amount;

            // Only apply core modifiers if allowed to
            if ($trigger_options['apply_core_modifiers'] != false){

                // If target robot has core boost for the ability (based on type)
                if ($this_ability->ability_results['flag_coreboost']){
                    $temp_multiplier = MMRPG_SETTINGS_COREBOOST_MULTIPLIER;
                    $this_ability->ability_results['this_amount'] = ceil($this_ability->ability_results['this_amount'] * $temp_multiplier);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | apply_core_modifiers | x '.$temp_multiplier.' = '.$this_ability->ability_results['this_amount'].'');
                }

            }

            // If we're not dealing with a percentage-based amount, apply stat mods
            if ($trigger_options['apply_stat_modifiers'] != false && !$this_ability->damage_options['damage_percent']){

                // Only apply ATTACK/DEFENSE mods if this robot is not targetting itself and it's ENERGY based damage
                if ($this_ability->damage_options['damage_kind'] == 'energy' && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_damage'])){

                    // Backup the current ammount before stat multipliers
                    $temp_amount_backup = $this_ability->ability_results['this_amount'];

                    // If this robot's defense is at absolute zero, and the target's attack isnt, OHKO
                    if ($this->robot_defense <= 0 && $target_robot->robot_attack >= 1){
                        // Set the new damage amount to OHKO this robot
                        $temp_new_amount = $this->robot_base_energy;
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$this->robot_token.'_defense_break | D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif the target robot's attack is at absolute zero, and the this's defense isnt, NOKO
                    elseif ($target_robot->robot_attack <= 0 && $this->robot_defense >= 1){
                        // Set the new damage amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break | A:'.$target_robot->robot_attack.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif this robot's defense is at absolute zero and the target's attack is too, NOKO
                    elseif ($this->robot_defense <= 0 && $target_robot->robot_attack <= 0){
                        // Set the new damage amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break and '.$this->robot_token.'_defense_break | A:'.$target_robot->robot_attack.' D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Otherwise if both robots have normal stats, calculate the new amount normally
                    else {
                        // Set the new damage amount relative to this robot's defense and the target robot's attack
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * ($target_robot->robot_attack / $this->robot_defense));
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | normal_damage | A:'.$target_robot->robot_attack.' D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * ('.$target_robot->robot_attack.' / '.$this->robot_defense.')) = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }

                    // If this robot started out above zero but is now absolute zero, round up
                    if ($temp_amount_backup > 0 && $this_ability->ability_results['this_amount'] == 0){ $this_ability->ability_results['this_amount'] = 1; }

                }

                // If this is a critical hit (random chance)
                if ($this->battle->critical_chance($this_ability->damage_options['critical_rate'])){
                    $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] * $this_ability->damage_options['critical_multiplier'];
                    $this_ability->ability_results['flag_critical'] = true;
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_critical | x '.$this_ability->damage_options['critical_multiplier'].' = '.$this_ability->ability_results['this_amount'].'');
                } else {
                    $this_ability->ability_results['flag_critical'] = false;
                }

            }

            // Only apply weakness, resistance, etc. if allowed to
            if ($trigger_options['apply_type_modifiers'] != false){

                // If this robot has a weakness to the ability (based on type)
                if ($this_ability->ability_results['flag_weakness']){
                    $loop_count = $this_ability->ability_results['counter_weaknesses'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->damage_options['weakness_multiplier']);
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_weakness ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->damage_options['weakness_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot resists the ability (based on type)
                if ($this_ability->ability_results['flag_resistance']){
                    $loop_count = $this_ability->ability_results['counter_resistances'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->damage_options['resistance_multiplier']);
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_resistance ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->damage_options['resistance_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot is immune to the ability (based on type)
                if ($this_ability->ability_results['flag_immunity']){
                    $loop_count = $this_ability->ability_results['counter_immunities'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $this_ability->ability_results['this_amount'] = round($this_ability->ability_results['this_amount'] * $this_ability->damage_options['immunity_multiplier']);
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_immunity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->damage_options['immunity_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

            }

            // Only apply other modifiers if allowed to
            if ($trigger_options['apply_modifiers'] != false){

                // If this robot has an attachment with a damage multiplier
                if (!empty($this->robot_attachments)){
                    foreach ($this->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage input breaker value set
                            if (isset($temp_info['attachment_damage_input_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage input booster value set
                            if (isset($temp_info['attachment_damage_input_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type.' ='.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_input_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_input_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_input_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_input_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_input_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }

                // If the target robot has an attachment with a damage multiplier
                if (!empty($target_robot->robot_attachments)){
                    foreach ($target_robot->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage output breaker value set
                            if (isset($temp_info['attachment_damage_output_breaker'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage output booster value set
                            if (isset($temp_info['attachment_damage_output_booster'])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_output_booster_'.$this_ability->ability_type])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_breaker_'.$this_ability->ability_type2.' ='.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage breaker value set
                            if (isset($temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a damage booster value set
                            if (isset($temp_info['attachment_damage_output_booster_'.$this_ability->ability_type2])){
                                // Apply the damage breaker multiplier to the current damage amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_damage_output_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_damage_output_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_damage_output_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }


            }

            // Generate the flag string for easier parsing
            $this_flag_string = array();
            if ($this_ability->ability_results['flag_immunity']){ $this_flag_string[] = 'immunity'; }
            elseif ($trigger_options['apply_type_modifiers'] != false){
                if (!empty($this_ability->ability_results['flag_weakness'])){ $this_flag_string[] = 'weakness'; }
                if (!empty($this_ability->ability_results['flag_affinity'])){ $this_flag_string[] = 'affinity'; }
                if (!empty($this_ability->ability_results['flag_resistance'])){ $this_flag_string[] = 'resistance'; }
                if ($trigger_options['apply_modifiers'] != false && !$this_ability->damage_options['damage_percent']){
                if (!empty($this_ability->ability_results['flag_critical'])){ $this_flag_string[] = 'critical'; }
                }
            }
            $this_flag_name = (!empty($this_flag_string) ? implode('_', $this_flag_string).'_' : '').'text';

            // Generate the status text based on flags
            if (isset($this_ability->damage_options[$this_flag_name])){
                //$event_options['console_container_height'] = 2;
                //$this_ability->ability_results['this_text'] .= '<br />';
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->damage_options[$this_flag_name];
            }

            // Display a break before the damage amount if other text was generated
            if (!empty($this_ability->ability_results['this_text'])){
                $this_ability->ability_results['this_text'] .= '<br />';
            }

            // Ensure the damage amount is always at least one, unless absolute zero
            if ($this_ability->ability_results['this_amount'] < 1 && $this_ability->ability_results['this_amount'] > 0){ $this_ability->ability_results['this_amount'] = 1; }

            // Reference the requested damage kind with a shorter variable
            $this_ability->damage_options['damage_kind'] = strtolower($this_ability->damage_options['damage_kind']);
            $damage_stat_name = 'robot_'.$this_ability->damage_options['damage_kind'];

            // Inflict the approiate damage type based on the damage options
            switch ($damage_stat_name){

                // If this is an ATTACK type damage trigger
                case 'robot_attack': {
                    // Inflict attack damage on the target's internal stat
                    $this->robot_attack = $this->robot_attack - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's attack below zero
                    if ($this->robot_attack < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_attack * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_attack;
                        // Zero out the robots attack
                        $this->robot_attack = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the ATTACK case
                    break;
                }
                // If this is an DEFENSE type damage trigger
                case 'robot_defense': {
                    // Inflict defense damage on the target's internal stat
                    $this->robot_defense = $this->robot_defense - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's defense below zero
                    if ($this->robot_defense < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_defense * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_defense;
                        // Zero out the robots defense
                        $this->robot_defense = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the DEFENSE case
                    break;
                }
                // If this is an SPEED type damage trigger
                case 'robot_speed': {
                    // Inflict attack damage on the target's internal stat
                    $this->robot_speed = $this->robot_speed - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's speed below zero
                    if ($this->robot_speed < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_speed * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_speed;
                        // Zero out the robots speed
                        $this->robot_speed = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the SPEED case
                    break;
                }
                // If this is a WEAPONS type damage trigger
                case 'robot_weapons': {
                    // Inflict weapon damage on the target's internal stat
                    $this->robot_weapons = $this->robot_weapons - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot's weapons below zero
                    if ($this->robot_weapons < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_weapons * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_weapons;
                        // Zero out the robots weapons
                        $this->robot_weapons = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // Break from the WEAPONS case
                    break;
                }
                // If this is an ENERGY type damage trigger
                case 'robot_energy': default: {
                    // Inflict the actual damage on the robot
                    $this->robot_energy = $this->robot_energy - $this_ability->ability_results['this_amount'];
                    // If the damage put the robot into overkill, recalculate the damage
                    if ($this->robot_energy < MMRPG_SETTINGS_STATS_MIN){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = $this->robot_energy * -1;
                        // Calculate the actual damage amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] + $this->robot_energy;
                        // Zero out the robots energy
                        $this->robot_energy = MMRPG_SETTINGS_STATS_MIN;
                    }
                    // If the robot's energy has dropped to zero, disable them
                    if ($this->robot_energy == 0){
                        // Change the status to disabled
                        $this->robot_status = 'disabled';
                        // Remove any attachments this robot has
                        if (!empty($this->robot_attachments)){
                            foreach ($this->robot_attachments AS $token => $info){
                                if (empty($info['sticky'])){ unset($this->robot_attachments[$token]); }
                            }
                        }
                    }
                    // Break from the ENERGY case
                    break;
                }

            }

            // Define the print variables to return
            $this_ability->ability_results['print_strikes'] = '<span class="damage_strikes">'.(!empty($this_ability->ability_results['total_strikes']) ? $this_ability->ability_results['total_strikes'] : 0).'</span>';
            $this_ability->ability_results['print_misses'] = '<span class="damage_misses">'.(!empty($this_ability->ability_results['total_misses']) ? $this_ability->ability_results['total_misses'] : 0).'</span>';
            $this_ability->ability_results['print_result'] = '<span class="damage_result">'.(!empty($this_ability->ability_results['total_result']) ? $this_ability->ability_results['total_result'] : 0).'</span>';
            $this_ability->ability_results['print_amount'] = '<span class="damage_amount">'.(!empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0).'</span>';
            $this_ability->ability_results['print_overkill'] = '<span class="damage_overkill">'.(!empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0).'</span>';

            // Add the final damage text showing the amount based on damage type
            if ($this_ability->damage_options['damage_kind'] == 'energy'){
                $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} takes {$this_ability->ability_results['print_amount']} life energy damage";
                $this_ability->ability_results['this_text'] .= ($this_ability->ability_results['this_overkill'] > 0 && $this->player->player_side == 'right' ? " and {$this_ability->ability_results['print_overkill']} overkill" : '');
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise add the final damage text showing the amount based on weapon energy damage
            elseif ($this_ability->damage_options['damage_kind'] == 'weapons'){
                $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} takes {$this_ability->ability_results['print_amount']} weapon energy damage";
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise, if this is one of the robot's other internal stats
            elseif ($this_ability->damage_options['damage_kind'] == 'attack'
                || $this_ability->damage_options['damage_kind'] == 'defense'
                || $this_ability->damage_options['damage_kind'] == 'speed'){
                // Print the result based on if the stat will go any lower
                if ($this_ability->ability_results['this_amount'] > 0){
                    $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()}&#39;s {$this_ability->damage_options['damage_kind']} fell by {$this_ability->ability_results['print_amount']}";
                    $this_ability->ability_results['this_text'] .= '!<br />';
                }
                // Otherwise if the stat wouldn't go any lower
                else {

                    // Update this robot's frame based on damage type
                    $this_ability->ability_frame = $this_ability->damage_options['ability_failure_frame'];
                    $this_ability->ability_frame_span = $this_ability->damage_options['ability_failure_frame_span'];
                    $this_ability->ability_frame_offset = $this_ability->damage_options['ability_failure_frame_offset'];

                    // Display the failure text, if text has been provided
                    if (!empty($this_ability->damage_options['failure_text'])){
                        $this_ability->ability_results['this_text'] .= $this_ability->damage_options['failure_text'].' ';
                    }
                }
            }

        }
        // Otherwise, if the attack was a failure
        else {

            // Update this robot's frame based on damage type
            $this_ability->ability_frame = $this_ability->damage_options['ability_failure_frame'];
            $this_ability->ability_frame_span = $this_ability->damage_options['ability_failure_frame_span'];
            $this_ability->ability_frame_offset = $this_ability->damage_options['ability_failure_frame_offset'];

            // Update the damage and overkilll amounts to reflect zero damage
            $this_ability->ability_results['this_amount'] = 0;
            $this_ability->ability_results['this_overkill'] = 0;

            // Display the failure text, if text has been provided
            if (!$this_ability->ability_results['flag_immunity'] && !empty($this_ability->damage_options['failure_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->damage_options['failure_text'].' ';
            }

        }

        // Update this robot's history with the triggered damage amount
        $this->history['triggered_damage'][] = $this_ability->ability_results['this_amount'];
        // Update the robot's history with the triggered damage types
        if (!empty($this_ability->ability_results['damage_type'])){
            $temp_types = array();
            $temp_types[] = $this_ability->ability_results['damage_type'];
            if (!empty($this_ability->ability_results['damage_type2'])){ $temp_types[] = $this_ability->ability_results['damage_type2']; }
            $this->history['triggered_damage_types'][] = $temp_types;
        } else {
            $this->history['triggered_damage_types'][] = array();
        }
        // Update this robot's history with the overkill if applicable
        if (!empty($this_ability->ability_results['this_overkill'])){
            $this->counters['defeat_overkill'] = isset($this->counters['defeat_overkill']) ? $this->counters['defeat_overkill'] + $this_ability->ability_results['this_overkill'] : $this_ability->ability_results['this_overkill'];
        }

        // Update the damage result total variables
        $this_ability->ability_results['total_amount'] += !empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0;
        $this_ability->ability_results['total_overkill'] += !empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0;
        if ($this_ability->ability_results['this_result'] == 'success'){ $this_ability->ability_results['total_strikes']++; }
        else { $this_ability->ability_results['total_misses']++; }
        $this_ability->ability_results['total_actions'] = $this_ability->ability_results['total_strikes'] + $this_ability->ability_results['total_misses'];
        if ($this_ability->ability_results['total_result'] != 'success'){ $this_ability->ability_results['total_result'] = $this_ability->ability_results['this_result']; }
        $event_options['this_ability_results'] = $this_ability->ability_results;

        // Update internal variables
        $target_robot->update_session();
        $target_robot->player->update_session();
        $this->update_session();
        $this->player->update_session();

        // If this robot was at full energy but is now at zero, it's a OHKO
        $this_robot_energy_ohko = false;
        if ($this->robot_energy <= 0 && $this_robot_energy_start_max){
            // DEBUG
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | damage_result_OHKO! | Start:'.$this_robot_energy_start.' '.($this_robot_energy_start_max ? '(MAX!)' : '-').' | Finish:'.$this->robot_energy);
            // Ensure the attacking player was a human
            if ($this->player->player_side == 'right'){
                $this_robot_energy_ohko = true;
                // Increment the field multipliers for items
                //if (!isset($this->field->field_multipliers['items'])){ $this->field->field_multipliers['items'] = 1; }
                //$this->field->field_multipliers['items'] += 0.1;
                //$this->field->update_session();
            }
        }

        // Generate an event with the collected damage results based on damage type
        if ($this->robot_id == $target_robot->robot_id){ //$this_ability->damage_options['damage_kind'] == 'energy'
            $event_options['console_show_target'] = false;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;;
            $this->battle->events_create($target_robot, $this, $this_ability->damage_options['damage_header'], $this_ability->ability_results['this_text'], $event_options);
        } else {
            $event_options['console_show_target'] = false;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;;
            $this->battle->events_create($this, $target_robot, $this_ability->damage_options['damage_header'], $this_ability->ability_results['this_text'], $event_options);
        }

        // Restore this and the target robot's frames to their backed up state
        $this->robot_frame = $this_robot_backup_frame;
        $this->player->player_frame = $this_player_backup_frame;
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->player_frame = $target_player_backup_frame;
        $this_ability->ability_frame = $this_ability_backup_frame;

        // Update internal variables
        $target_robot->update_session();
        $target_robot->player->update_session();
        $this->update_session();
        $this->player->update_session();
        $this_ability->update_session();

        // If this robot has been disabled, add a defeat attachment
        if ($this->robot_status == 'disabled'){

            // Define this ability's attachment token
            $temp_frames = array(0,4,1,5,2,6,3,7,4,8,5,9,0,1,2,3,4,5,6,7,8,9);
            shuffle($temp_frames);
            $this_attachment_token = 'ability_attachment-defeat';
            $this_attachment_info = array(
                'class' => 'ability',
                'ability_token' => 'attachment-defeat',
                'attachment_flag_defeat' => true,
                'ability_frame' => 0,
                'ability_frame_animate' => $temp_frames,
                'ability_frame_offset' => array('x' => 0, 'y' => -10, 'z' => -10)
                );

            // If the attachment doesn't already exists, add it to the robot
            if (!isset($this->robot_attachments[$this_attachment_token])){
                $this->robot_attachments[$this_attachment_token] =  $this_attachment_info;
                $this->update_session();
            }

        }

        // If this robot was disabled, process experience for the target
        if ($this->robot_status == 'disabled' && $trigger_disabled){
            $trigger_options = array();
            if ($this_robot_energy_ohko){ $trigger_options['item_multiplier'] = 2.0; }
            $this->trigger_disabled($target_robot, $this_ability, $trigger_options);
        }
        // Otherwise, if the target robot was not disabled
        elseif ($this->robot_status != 'disabled'){

            // -- CHECK ATTACHMENTS -- //

            // Ensure the ability was a success before checking attachments
            if ($this_ability->ability_results['this_result'] == 'success'){
                // If this robot has any attachments, loop through them
                if (!empty($this->robot_attachments)){
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
                    foreach ($this->robot_attachments AS $attachment_token => $attachment_info){

                        // Ensure this ability has a type before checking weaknesses, resistances, etc.
                        if (!empty($this_ability->ability_type) || in_array('*', $attachment_info['attachment_weaknesses'])){

                            // If this attachment has weaknesses defined and this ability is a match
                            if (!empty($attachment_info['attachment_weaknesses'])
                                && (in_array('*', $attachment_info['attachment_weaknesses'])
                                    || in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses'])
                                    || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))
                                    ){

                                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                                // Remove this attachment and inflict damage on the robot
                                unset($this->robot_attachments[$attachment_token]);
                                $this->update_session();
                                if ($attachment_info['attachment_destroy'] !== false){
                                    $temp_attachment = new rpg_ability($this->battle, $this->player, $this, array('ability_token' => $attachment_info['ability_token']));
                                    $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                                    //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                                    //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
                                    if ($temp_trigger_type == 'damage'){
                                        $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->update_session();
                                        $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                        if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                                            $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                            $temp_trigger_options = array('apply_modifiers' => false);
                                            $this->trigger_damage($target_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                                        }
                                    } elseif ($temp_trigger_type == 'recovery'){
                                        $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->update_session();
                                        $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                        if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                                            $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                            $temp_trigger_options = array('apply_modifiers' => false);
                                            $this->trigger_recovery($target_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                                        }
                                    } elseif ($temp_trigger_type == 'special'){
                                        $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->update_session();
                                        //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                        $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                                    }
                                }
                                // If this robot was disabled, process experience for the target
                                if ($this->robot_status == 'disabled'){ break; }

                            }

                        }

                    }
                }

            }

        }

        // Return the final damage results
        return $this_ability->ability_results;

    }


    // Define a trigger for inflicting all types of recovery on this robot
    public function trigger_recovery($target_robot, $this_ability, $recovery_amount, $trigger_disabled = true, $trigger_options = array()){
        global $db;

        // Generate default trigger options if not set
        if (!isset($trigger_options['apply_modifiers'])){ $trigger_options['apply_modifiers'] = true; }
        if (!isset($trigger_options['apply_type_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_type_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_core_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_core_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_field_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_field_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_starforce_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_starforce_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_position_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_position_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['apply_stat_modifiers']) || $trigger_options['apply_modifiers'] == false){ $trigger_options['apply_stat_modifiers'] = $trigger_options['apply_modifiers']; }
        if (!isset($trigger_options['referred_recovery'])){ $trigger_options['referred_recovery'] = false; }
        /*
         * ROBOT CLASS FUNCTION TRIGGER RECOVERY
         * public function trigger_recovery($target_robot, $this_ability, $recovery_amount, $trigger_disabled = true){}
         */

        // Backup this and the target robot's frames to revert later
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_robot_backup_frame = $this->robot_frame;
        $this_player_backup_frame = $this->player->player_frame;
        $target_robot_backup_frame = $target_robot->robot_frame;
        $target_player_backup_frame = $target_robot->player->player_frame;
        $this_ability_backup_frame = $this_ability->ability_frame;

        // Define the event console options
        $event_options = array();
        $event_options['console_container_height'] = 1;
        $event_options['this_ability'] = $this_ability;
        $event_options['this_ability_results'] = array();

        // Empty any text from the previous ability result
        $this_ability->ability_results['this_text'] = '';

        // Update the recovery to whatever was supplied in the argument
        //if ($this_ability->recovery_options['recovery_percent'] && $recovery_amount > 100){ $recovery_amount = 100; }
        $this_ability->recovery_options['recovery_amount'] = $recovery_amount;

        // Collect the recovery amount argument from the function
        $this_ability->ability_results['this_amount'] = $recovery_amount;
        // DEBUG
        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | recovery_start_amount |<br /> '.'amount:'.$this_ability->ability_results['this_amount'].' | '.'percent:'.($this_ability->recovery_options['recovery_percent'] ? 'true' : 'false').' | '.'kind:'.$this_ability->recovery_options['recovery_kind'].'');


        // DEBUG
        $debug = '';
        foreach ($trigger_options AS $key => $value){ $debug .= $key.'='.($value === true ? 'true' : ($value === false ? 'false' : $value)).'; '; }
        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' : recovery_trigger_options : '.$debug);

        /*
        // Only apply weakness, resistance, etc. if not percent
        if (true || !$this_ability->recovery_options['recovery_percent']){
        */
        // Only apply modifiers if they have not been disabled
        if ($trigger_options['apply_modifiers'] != false){

            // Skip all weakness, resistance, etc. calculations if robot is targetting self
            if ($trigger_options['apply_type_modifiers'] != false && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery'])){

                // If this robot has weakness to the ability (based on type)
                if ($this->has_weakness($this_ability->recovery_options['recovery_type']) && !$this->has_affinity($this_ability->recovery_options['recovery_type2'])){
                    //$this_ability->ability_results['counter_weaknesses'] += 1;
                    //$this_ability->ability_results['flag_weakness'] = true;
                    return $this->trigger_damage($target_robot, $this_ability, $recovery_amount);
                } else {
                    $this_ability->ability_results['flag_weakness'] = false;
                }

                // If this robot has weakness to the ability (based on type2)
                if ($this->has_weakness($this_ability->recovery_options['recovery_type2']) && !$this->has_affinity($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_weaknesses'] += 1;
                    $this_ability->ability_results['flag_weakness'] = true;
                    return $this->trigger_damage($target_robot, $this_ability, $recovery_amount);
                }

                // If target robot has affinity to the ability (based on type)
                if ($this->has_affinity($this_ability->recovery_options['recovery_type']) && !$this->has_weakness($this_ability->recovery_options['recovery_type2'])){
                    $this_ability->ability_results['counter_affinities'] += 1;
                    $this_ability->ability_results['flag_affinity'] = true;
                } else {
                    $this_ability->ability_results['flag_affinity'] = false;
                }

                // If target robot has affinity to the ability (based on type2)
                if ($this->has_affinity($this_ability->recovery_options['recovery_type2']) && !$this->has_weakness($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_affinities'] += 1;
                    $this_ability->ability_results['flag_affinity'] = true;
                }

                // If target robot has resistance tp the ability (based on type)
                if ($this->has_resistance($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                } else {
                    $this_ability->ability_results['flag_resistance'] = false;
                }

                // If target robot has resistance tp the ability (based on type2)
                if ($this->has_resistance($this_ability->recovery_options['recovery_type2'])){
                    $this_ability->ability_results['counter_resistances'] += 1;
                    $this_ability->ability_results['flag_resistance'] = true;
                }

                // If target robot has immunity to the ability (based on type)
                if ($this->has_immunity($this_ability->recovery_options['recovery_type'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                } else {
                    $this_ability->ability_results['flag_immunity'] = false;
                }

                // If target robot has immunity to the ability (based on type2)
                if ($this->has_immunity($this_ability->recovery_options['recovery_type2'])){
                    $this_ability->ability_results['counter_immunities'] += 1;
                    $this_ability->ability_results['flag_immunity'] = true;
                }

            }

            // Apply core boosts if allowed to
            if ($trigger_options['apply_core_modifiers'] != false){

                // If the target robot is using an ability that it is the same type as its core
                if (!empty($target_robot->robot_core) && $target_robot->robot_core == $this_ability->recovery_options['recovery_type']){
                    $this_ability->ability_results['counter_coreboosts'] += 1;
                    $this_ability->ability_results['flag_coreboost'] = true;
                } else {
                    $this_ability->ability_results['flag_coreboost'] = false;
                }

                // If the target robot is using an ability that it is the same type as its core
                if (!empty($target_robot->robot_core) && $target_robot->robot_core == $this_ability->recovery_options['recovery_type2']){
                    $this_ability->ability_results['counter_coreboosts'] += 1;
                    $this_ability->ability_results['flag_coreboost'] = true;
                }

            }

            // Apply position boosts if allowed to
            if ($trigger_options['apply_position_modifiers'] != false){

                // If this robot is not in the active position
                if ($this->robot_position != 'active'){
                    // Collect the current key of the robot and apply recovery mods
                    $temp_recovery_key = $this->robot_key + 1;
                    $temp_recovery_resistor = (10 - $temp_recovery_key) / 10;
                    $new_recovery_amount = round($recovery_amount * $temp_recovery_resistor);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | position_modifier_recovery | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_recovery_resistor.') = '.$new_recovery_amount.'');
                    $recovery_amount = $new_recovery_amount;
                }

            }

        }

        // Apply field multipliers preemtively if there are any
        if ($trigger_options['apply_field_modifiers'] != false && $this_ability->recovery_options['recovery_modifiers'] && !empty($this->field->field_multipliers)){

            // Collect the multipliters for easier
            $field_multipliers = $this->field->field_multipliers;

            // Collect the ability types else "none" for multipliers
            $temp_ability_recovery_type = !empty($this_ability->recovery_options['recovery_type']) ? $this_ability->recovery_options['recovery_type'] : 'none';
            $temp_ability_recovery_type2 = !empty($this_ability->recovery_options['recovery_type2']) ? $this_ability->recovery_options['recovery_type2'] : '';

            // If there's a recovery booster, apply that first
            if (isset($field_multipliers['recovery'])){
                $new_recovery_amount = round($recovery_amount * $field_multipliers['recovery']);
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_recovery | '.$recovery_amount.' = round('.$recovery_amount.' * '.$field_multipliers['recovery'].') = '.$new_recovery_amount.'');
                $recovery_amount = $new_recovery_amount;
            }

            // Loop through all the other type multipliers one by one if this ability has a type
            $skip_types = array('recovery', 'recovery', 'experience');
            foreach ($field_multipliers AS $temp_type => $temp_multiplier){
                // Skip non-type and special fields for this calculation
                if (in_array($temp_type, $skip_types)){ continue; }
                // If this ability's type matches the multiplier, apply it
                if ($temp_ability_recovery_type == $temp_type){
                    $new_recovery_amount = round($recovery_amount * $temp_multiplier);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.'');
                    $recovery_amount = $new_recovery_amount;
                }
                // If this ability's type2 matches the multiplier, apply it
                if ($temp_ability_recovery_type2 == $temp_type){
                    $new_recovery_amount = round($recovery_amount * $temp_multiplier);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | field_multiplier_'.$temp_type.' | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.'');
                    $recovery_amount = $new_recovery_amount;
                }
            }


        }

        // Apply starforce multipliers preemtively if there are any
        if ($trigger_options['apply_starforce_modifiers'] != false && $this_ability->recovery_options['recovery_modifiers'] && !empty($target_robot->player->player_starforce) && $this->robot_id != $target_robot->robot_id){

            // Collect this and the target player's starforce levels
            $this_starforce = $this->player->player_starforce;
            $target_starforce = $target_robot->player->player_starforce;

            // Loop through and neutralize target starforce levels if possible
            $target_starforce_modified = $target_starforce;
            foreach ($target_starforce_modified AS $type => $target_value){
                if (!isset($this_starforce[$type])){ $this_starforce[$type] = 0; }
                $target_value -= $this_starforce[$type];
                if ($target_value < 0){ $target_value = 0; }
                $target_starforce_modified[$type] = $target_value;
            }

            // Collect the ability types else "none" for multipliers
            $temp_ability_recovery_type = !empty($this_ability->recovery_options['recovery_type']) ? $this_ability->recovery_options['recovery_type'] : '';
            $temp_ability_recovery_type2 = !empty($this_ability->recovery_options['recovery_type2']) ? $this_ability->recovery_options['recovery_type2'] : '';

            // Apply any starforce bonuses for ability type
            if (!empty($temp_ability_recovery_type) && !empty($target_starforce_modified[$temp_ability_recovery_type])){
                $temp_multiplier = 1 + ($target_starforce_modified[$temp_ability_recovery_type] / 10);
                $new_recovery_amount = round($recovery_amount * $temp_multiplier);
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_recovery_type.' | force:'.$target_starforce[$temp_ability_recovery_type].' vs resist:'.$this_starforce[$temp_ability_recovery_type].' = '.($target_starforce_modified[$temp_ability_recovery_type] * 10).'% boost | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.'');
                $recovery_amount = $new_recovery_amount;
            } elseif (!empty($temp_ability_recovery_type) && isset($target_starforce_modified[$temp_ability_recovery_type])){
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_recovery_type.' | force:'.$target_starforce[$temp_ability_recovery_type].' vs resist:'.$this_starforce[$temp_ability_recovery_type].' = no boost');
            }

            // Apply any starforce bonuses for ability type2
            if (!empty($temp_ability_recovery_type2) && !empty($target_starforce_modified[$temp_ability_recovery_type2])){
                $temp_multiplier = 1 + ($target_starforce_modified[$temp_ability_recovery_type2] / 10);
                $new_recovery_amount = round($recovery_amount * $temp_multiplier);
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_recovery_type2.' | force:'.$target_starforce[$temp_ability_recovery_type2].' vs resist:'.$this_starforce[$temp_ability_recovery_type2].' = '.($target_starforce_modified[$temp_ability_recovery_type2] * 10).'% boost | '.$recovery_amount.' = round('.$recovery_amount.' * '.$temp_multiplier.') = '.$new_recovery_amount.'');
                $recovery_amount = $new_recovery_amount;
            } elseif (!empty($temp_ability_recovery_type2) && isset($target_starforce_modified[$temp_ability_recovery_type2])){
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | starforce_multiplier_'.$temp_ability_recovery_type2.' | force:'.$target_starforce[$temp_ability_recovery_type2].' vs resist:'.$this_starforce[$temp_ability_recovery_type2].' = no boost');
            }

        }

        // Update the ability results with the the trigger kind and recovery details
        $this_ability->ability_results['trigger_kind'] = 'recovery';
        $this_ability->ability_results['recovery_kind'] = $this_ability->recovery_options['recovery_kind'];
        $this_ability->ability_results['recovery_type'] = $this_ability->recovery_options['recovery_type'];

        // If the success rate was not provided, auto-calculate
        if ($this_ability->recovery_options['success_rate'] == 'auto'){
            // If this robot is targetting itself, default to ability accuracy
            if ($this->robot_id == $target_robot->robot_id){
                // Update the success rate to the ability accuracy value
                $this_ability->recovery_options['success_rate'] = $this_ability->ability_accuracy;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($target_robot->robot_speed <= 0 && $this->robot_speed > 0){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->recovery_options['success_rate'] = 0;
            }
            // Otherwise, if this robot is in speed break or ability accuracy 100%
            elseif ($this->robot_speed <= 0 || $this_ability->ability_accuracy == 100){
                // Hard-code the success rate at 100% accuracy
                    $this_ability->recovery_options['success_rate'] = 100;
            }
            // Otherwise, calculate the success rate based on relative speeds
            else {
                // Collect this ability's accuracy stat for modification
                $this_ability_accuracy = $this_ability->ability_accuracy;
                // If the target was faster/slower, boost/lower the ability accuracy
                if ($target_robot->robot_speed > $this->robot_speed
                    || $target_robot->robot_speed < $this->robot_speed){
                    $this_modifier = $target_robot->robot_speed / $this->robot_speed;
                    //$this_ability_accuracy = ceil($this_ability_accuracy * $this_modifier);
                    $this_ability_accuracy = ceil($this_ability_accuracy * 0.95) + ceil(($this_ability_accuracy * 0.05) * $this_modifier);
                    if ($this_ability_accuracy > 100){ $this_ability_accuracy = 100; }
                    elseif ($this_ability_accuracy < 0){ $this_ability_accuracy = 0; }
                }
                // Update the success rate to the ability accuracy value
                $this_ability->recovery_options['success_rate'] = $this_ability_accuracy;
                //$this_ability->ability_results['this_text'] .= '';
            }
        }

        // If the failure rate was not provided, auto-calculate
        if ($this_ability->recovery_options['failure_rate'] == 'auto'){
            // Set the failure rate to the difference of success vs failure (100% base)
            $this_ability->recovery_options['failure_rate'] = 100 - $this_ability->recovery_options['success_rate'];
            if ($this_ability->recovery_options['failure_rate'] < 0){
                $this_ability->recovery_options['failure_rate'] = 0;
            }
        }

        // If this robot is in speed break, increase success rate, reduce failure
        if ($this->robot_speed == 0 && $this_ability->recovery_options['success_rate'] > 0){
            $this_ability->recovery_options['success_rate'] = ceil($this_ability->recovery_options['success_rate'] * 2);
            $this_ability->recovery_options['failure_rate'] = ceil($this_ability->recovery_options['failure_rate'] / 2);
        }
        // If the target robot is in speed break, decease the success rate, increase failure
        elseif ($target_robot->robot_speed == 0 && $this_ability->recovery_options['success_rate'] > 0){
            $this_ability->recovery_options['success_rate'] = ceil($this_ability->recovery_options['success_rate'] / 2);
            $this_ability->recovery_options['failure_rate'] = ceil($this_ability->recovery_options['failure_rate'] * 2);
        }

        // If success rate is at 100%, auto-set the result to success
        if ($this_ability->recovery_options['success_rate'] == 100){
            // Set this ability result as a success
            $this_ability->recovery_options['failure_rate'] = 0;
            $this_ability->ability_results['this_result'] = 'success';
        }
        // Else if the success rate is at 0%, auto-set the result to failure
        elseif ($this_ability->recovery_options['success_rate'] == 0){
            // Set this ability result as a failure
            $this_ability->recovery_options['failure_rate'] = 100;
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise, use a weighted random generation to get the result
        else {
            // Calculate whether this attack was a success, based on the success vs. failure rate
            $this_ability->ability_results['this_result'] = $this->battle->weighted_chance(
                array('success','failure'),
                array($this_ability->recovery_options['success_rate'], $this_ability->recovery_options['failure_rate'])
                );
        }

        // If this is ENERGY recovery and this robot is already at full health
        if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->robot_energy >= $this->robot_base_energy){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // If this is WEAPONS recovery and this robot is already at full ammo
        elseif ($this_ability->recovery_options['recovery_kind'] == 'weapons' && $this->robot_weapons >= $this->robot_base_weapons){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if ATTACK recovery but attack is already at 9999
        elseif ($this_ability->recovery_options['recovery_kind'] == 'attack' && $this->robot_attack >= MMRPG_SETTINGS_STATS_MAX){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if DEFENSE recovery but defense is already at 9999
        elseif ($this_ability->recovery_options['recovery_kind'] == 'defense' && $this->robot_defense >= MMRPG_SETTINGS_STATS_MAX){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }
        // Otherwise if SPEED recovery but speed is already at 9999
        elseif ($this_ability->recovery_options['recovery_kind'] == 'speed' && $this->robot_speed >= MMRPG_SETTINGS_STATS_MAX){
            // Hard code the result to failure
            $this_ability->ability_results['this_result'] = 'failure';
        }

        // If this robot has immunity to the ability, hard-code a failure result
        if ($this_ability->ability_results['flag_immunity']){
            $this_ability->ability_results['this_result'] = 'failure';
            $this->flags['triggered_immunity'] = true;
            // Generate the status text based on flags
            $this_flag_name = 'immunity_text';
            if (isset($this_ability->recovery_options[$this_flag_name])){
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->recovery_options[$this_flag_name].'<br /> ';
            }
        }

        // If the attack was a success, proceed normally
        if ($this_ability->ability_results['this_result'] == 'success'){

            // Create the experience multiplier if not already set
            if (!isset($this->field->field_multipliers['experience'])){ $this->field->field_multipliers['experience'] = 1; }
            elseif ($this->field->field_multipliers['experience'] < 0.1){ $this->field->field_multipliers['experience'] = 0.1; }
            elseif ($this->field->field_multipliers['experience'] > 9.9){ $this->field->field_multipliers['experience'] = 9.9; }

            // If modifiers are not turned off
            if ($trigger_options['apply_modifiers'] != false){

                // Update this robot's internal flags based on ability effects
                if (!empty($this_ability->ability_results['flag_weakness'])){
                    $this->flags['triggered_weakness'] = true;
                    if (isset($this->counters['triggered_weakness'])){ $this->counters['triggered_weakness'] += 1; }
                    else { $this->counters['triggered_weakness'] = 1; }
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] += 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_affinity'])){
                    $this->flags['triggered_affinity'] = true;
                    if (isset($this->counters['triggered_affinity'])){ $this->counters['triggered_affinity'] += 1; }
                    else { $this->counters['triggered_affinity'] = 1; }
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_resistance'])){
                    $this->flags['triggered_resistance'] = true;
                    if (isset($this->counters['triggered_resistance'])){ $this->counters['triggered_resistance'] += 1; }
                    else { $this->counters['triggered_resistance'] = 1; }
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] -= 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] += 0.1; }
                }
                if (!empty($this_ability->ability_results['flag_critical'])){
                    $this->flags['triggered_critical'] = true;
                    if (isset($this->counters['triggered_critical'])){ $this->counters['triggered_critical'] += 1; }
                    else { $this->counters['triggered_critical'] = 1; }
                    if ($this_ability->recovery_options['recovery_kind'] == 'energy' && $this->player->player_side == 'right'){ $this->field->field_multipliers['experience'] += 0.1; }
                    //elseif ($this->player->player_side == 'left'){ $this->field->field_multipliers['experience'] -= 0.1; }
                }

            }

            // Update the field session with any changes
            $this->field->update_session();

            // Update this robot's frame based on recovery type
            $this->robot_frame = $this_ability->recovery_options['recovery_frame'];
            $this->player->player_frame = ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery']) ? 'taunt' : 'base';
            $this_ability->ability_frame = $this_ability->recovery_options['ability_success_frame'];
            $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_success_frame_offset'];

            // Display the success text, if text has been provided
            if (!empty($this_ability->recovery_options['success_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['success_text'];
            }

            // Collect the recovery amount argument from the function
            $this_ability->ability_results['this_amount'] = $recovery_amount;

            // Only apply core modifiers if allowed to
            if ($trigger_options['apply_core_modifiers'] != false){

                // If target robot has core boost for the ability (based on type)
                if ($this_ability->ability_results['flag_coreboost']){
                    $temp_multiplier = MMRPG_SETTINGS_COREBOOST_MULTIPLIER;
                    $this_ability->ability_results['this_amount'] = ceil($this_ability->ability_results['this_amount'] * $temp_multiplier);
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | apply_core_modifiers | x '.$temp_multiplier.' = '.$this_ability->ability_results['this_amount'].'');
                }

            }

            // If we're not dealing with a percentage-based amount, apply stat mods
            if ($trigger_options['apply_stat_modifiers'] != false && !$this_ability->recovery_options['recovery_percent']){

                // Only apply ATTACK/DEFENSE mods if this robot is not targetting itself and it's ENERGY based recovery
                if ($this_ability->recovery_options['recovery_kind'] == 'energy' && ($this->robot_id != $target_robot->robot_id || $trigger_options['referred_recovery'])){

                    // Backup the current ammount before stat multipliers
                    $temp_amount_backup = $this_ability->ability_results['this_amount'];

                    // If this robot's defense is at absolute zero, and the target's attack isnt, OHKO
                    if ($this->robot_defense <= 0 && $target_robot->robot_attack >= 1){
                        // Set the new recovery amount to OHKO this robot
                        $temp_new_amount = $this->robot_base_energy;
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$this->robot_token.'_defense_break | D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif the target robot's attack is at absolute zero, and the this's defense isnt, NOKO
                    elseif ($target_robot->robot_attack <= 0 && $this->robot_defense >= 1){
                        // Set the new recovery amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break | A:'.$target_robot->robot_attack.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Elseif this robot's defense is at absolute zero and the target's attack is too, NOKO
                    elseif ($this->robot_defense <= 0 && $target_robot->robot_attack <= 0){
                        // Set the new recovery amount to NOKO this robot
                        $temp_new_amount = 0;
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | '.$target_robot->robot_token.'_attack_break and '.$this->robot_token.'_defense_break | A:'.$target_robot->robot_attack.' D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                    // Otherwise if both robots have normal stats, calculate the new amount normally
                    else {
                        // Set the new recovery amount relative to this robot's defense and the target robot's attack
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * ($target_robot->robot_attack / $this->robot_defense));
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | normal_recovery | A:'.$target_robot->robot_attack.' D:'.$this->robot_defense.' | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * ('.$target_robot->robot_attack.' / '.$this->robot_defense.')) = '.$temp_new_amount.'');
                        // Update the amount with the new calculation
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }

                    // If this robot started out above zero but is now absolute zero, round up
                    if ($temp_amount_backup > 0 && $this_ability->ability_results['this_amount'] == 0){ $this_ability->ability_results['this_amount'] = 1; }

                }

                // If this is a critical hit (random chance)
                if ($this->battle->critical_chance($this_ability->recovery_options['critical_rate'])){
                    $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] * $this_ability->recovery_options['critical_multiplier'];
                    $this_ability->ability_results['flag_critical'] = true;
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_critical | x '.$this_ability->recovery_options['critical_multiplier'].' = '.$this_ability->ability_results['this_amount'].'');
                } else {
                    $this_ability->ability_results['flag_critical'] = false;
                }

            }

            // Only apply weakness, resistance, etc. if allowed to
            if ($trigger_options['apply_type_modifiers'] != false){

                // If this robot has an affinity to the ability (based on type)
                if ($this_ability->ability_results['flag_affinity']){
                    $loop_count = $this_ability->ability_results['counter_affinities'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['affinity_multiplier']);
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_affinity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['affinity_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot resists the ability (based on type)
                if ($this_ability->ability_results['flag_resistance']){
                    $loop_count = $this_ability->ability_results['counter_resistances'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $temp_new_amount = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['resistance_multiplier']);
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_resistance ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['resistance_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

                // If target robot is immune to the ability (based on type)
                if ($this_ability->ability_results['flag_immunity']){
                    $loop_count = $this_ability->ability_results['counter_immunities'] / ($this_ability->ability_results['total_strikes'] + 1);
                    for ($i = 1; $i <= $loop_count; $i++){
                        $this_ability->ability_results['this_amount'] = round($this_ability->ability_results['this_amount'] * $this_ability->recovery_options['immunity_multiplier']);
                        // DEBUG
                        //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this_ability->ability_token.' | flag_immunity ('.$i.'/'.$loop_count.') | '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$this_ability->recovery_options['immunity_multiplier'].') = '.$temp_new_amount.'');
                        $this_ability->ability_results['this_amount'] = $temp_new_amount;
                    }
                }

            }

            // Only apply other modifiers if allowed to
            if ($trigger_options['apply_modifiers'] != false){

                // If this robot has an attachment with a recovery multiplier
                if (!empty($this->robot_attachments)){
                    foreach ($this->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery input breaker value set
                            if (isset($temp_info['attachment_recovery_input_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery input booster value set
                            if (isset($temp_info['attachment_recovery_input_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_input_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_input_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }

                // If this robot has an attachment with a recovery multiplier
                if (!empty($target_robot->robot_attachments)){
                    foreach ($target_robot->robot_attachments AS $temp_token => $temp_info){
                        $temp_token_debug = str_replace('ability_', 'attachment_', $temp_token);

                        // First check to see if any basic boosters or breakers have been created for this robot
                        if (true){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery output breaker value set
                            if (isset($temp_info['attachment_recovery_output_breaker'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery output booster value set
                            if (isset($temp_info['attachment_recovery_output_booster'])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster']);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster'].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster_'.$this_ability->ability_type.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }
                        // Next check to see if any boosters or breakers for either of this ability's types
                        if (!empty($this_ability->ability_type2)){
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery breaker value set
                            if (isset($temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_breaker_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_breaker_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                            // If this robot's attachment has a recovery booster value set
                            if (isset($temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2])){
                                // Apply the recovery breaker multiplier to the current recovery amount
                                $temp_new_amount = round($this_ability->ability_results['this_amount'] * $temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2]);
                                // DEBUG
                                //$this_battle->events_debug(__FILE__, __LINE__, 'ability_'.$this_ability->ability_token.' vs. '.$temp_token_debug.' <br /> attachment_recovery_output_booster_'.$this_ability->ability_type2.' = '.$this_ability->ability_results['this_amount'].' = round('.$this_ability->ability_results['this_amount'].' * '.$temp_info['attachment_recovery_output_booster_'.$this_ability->ability_type2].') = '.$temp_new_amount.'');
                                $this_ability->ability_results['this_amount'] = $temp_new_amount;
                            }
                        }

                    }
                }


            }

            // Generate the flag string for easier parsing
            $this_flag_string = array();
            if ($this_ability->ability_results['flag_immunity']){ $this_flag_string[] = 'immunity'; }
            elseif ($trigger_options['apply_type_modifiers'] != false){
                if (!empty($this_ability->ability_results['flag_weakness'])){ $this_flag_string[] = 'weakness'; }
                if (!empty($this_ability->ability_results['flag_affinity'])){ $this_flag_string[] = 'affinity'; }
                if (!empty($this_ability->ability_results['flag_resistance'])){ $this_flag_string[] = 'resistance'; }
                if ($trigger_options['apply_modifiers'] != false && !$this_ability->recovery_options['recovery_percent']){
                if (!empty($this_ability->ability_results['flag_critical'])){ $this_flag_string[] = 'critical'; }
                }
            }
            $this_flag_name = (!empty($this_flag_string) ? implode('_', $this_flag_string).'_' : '').'text';

            // Generate the status text based on flags
            if (isset($this_ability->recovery_options[$this_flag_name])){
                //$event_options['console_container_height'] = 2;
                //$this_ability->ability_results['this_text'] .= '<br />';
                $this_ability->ability_results['this_text'] .= ' '.$this_ability->recovery_options[$this_flag_name];
            }

            // Display a break before the recovery amount if other text was generated
            if (!empty($this_ability->ability_results['this_text'])){
                $this_ability->ability_results['this_text'] .= '<br />';
            }

            // Ensure the recovery amount is always at least one, unless absolute zero
            if ($this_ability->ability_results['this_amount'] < 1 && $this_ability->ability_results['this_amount'] > 0){ $this_ability->ability_results['this_amount'] = 1; }

            // Reference the requested recovery kind with a shorter variable
            $this_ability->recovery_options['recovery_kind'] = strtolower($this_ability->recovery_options['recovery_kind']);
            $recovery_stat_name = 'robot_'.$this_ability->recovery_options['recovery_kind'];

            // Inflict the approiate recovery type based on the recovery options
            switch ($recovery_stat_name){

                // If this is an ATTACK type recovery trigger
                case 'robot_attack': {
                    // Inflict attack recovery on the target's internal stat
                    $this->robot_attack = $this->robot_attack + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's attack above 9999
                    if ($this->robot_attack > MMRPG_SETTINGS_STATS_MAX){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_attack) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots attack
                        $this->robot_attack = MMRPG_SETTINGS_STATS_MAX;
                    }
                    // Break from the ATTACK case
                    break;
                }
                // If this is an DEFENSE type recovery trigger
                case 'robot_defense': {
                    // Inflict defense recovery on the target's internal stat
                    $this->robot_defense = $this->robot_defense + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's defense above 9999
                    if ($this->robot_defense > MMRPG_SETTINGS_STATS_MAX){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_defense) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots defense
                        $this->robot_defense = MMRPG_SETTINGS_STATS_MAX;
                    }
                    // Break from the DEFENSE case
                    break;
                }
                // If this is an SPEED type recovery trigger
                case 'robot_speed': {
                    // Inflict speed recovery on the target's internal stat
                    $this->robot_speed = $this->robot_speed + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's speed above 9999
                    if ($this->robot_speed > MMRPG_SETTINGS_STATS_MAX){
                        // Calculate the overkill amount
                        $this_ability->ability_results['this_overkill'] = (MMRPG_SETTINGS_STATS_MAX - $this->robot_speed) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots speed
                        $this->robot_speed = MMRPG_SETTINGS_STATS_MAX;
                    }
                    // Break from the SPEED case
                    break;
                }
                // If this is a WEAPONS type recovery trigger
                case 'robot_weapons': {
                    // Inflict weapon recovery on the target's internal stat
                    $this->robot_weapons = $this->robot_weapons + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot's weapons above the base
                    if ($this->robot_weapons > $this->robot_base_weapons){
                        // Calculate the overcure amount
                        $this_ability->ability_results['this_overkill'] = ($this->robot_base_weapons - $this->robot_weapons) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots weapons
                        $this->robot_weapons = $this->robot_base_weapons;
                    }
                    // Break from the WEAPONS case
                    break;
                }
                // If this is an ENERGY type recovery trigger
                case 'robot_energy': default: {
                    // Inflict the actual recovery on the robot
                    $this->robot_energy = $this->robot_energy + $this_ability->ability_results['this_amount'];
                    // If the recovery put the robot into overboost, recalculate the recovery
                    if ($this->robot_energy > $this->robot_base_energy){
                        // Calculate the overcure amount
                        $this_ability->ability_results['this_overkill'] = ($this->robot_base_energy - $this->robot_energy) * -1;
                        // Calculate the actual recovery amount
                        $this_ability->ability_results['this_amount'] = $this_ability->ability_results['this_amount'] - $this_ability->ability_results['this_overkill'];
                        // Max out the robots energy
                        $this->robot_energy = $this->robot_base_energy;
                    }
                    // If the robot's energy has dropped to zero, disable them
                    if ($this->robot_energy == 0){
                        // Change the status to disabled
                        $this->robot_status = 'disabled';
                        // Remove any attachments this robot has
                        if (!empty($this->robot_attachments)){
                            foreach ($this->robot_attachments AS $token => $info){
                                if (empty($info['sticky'])){ unset($this->robot_attachments[$token]); }
                            }
                        }
                    }
                    // Break from the ENERGY case
                    break;
                }

            }

            // Define the print variables to return
            $this_ability->ability_results['print_strikes'] = '<span class="recovery_strikes">'.(!empty($this_ability->ability_results['total_strikes']) ? $this_ability->ability_results['total_strikes'] : 0).'</span>';
            $this_ability->ability_results['print_misses'] = '<span class="recovery_misses">'.(!empty($this_ability->ability_results['total_misses']) ? $this_ability->ability_results['total_misses'] : 0).'</span>';
            $this_ability->ability_results['print_result'] = '<span class="recovery_result">'.(!empty($this_ability->ability_results['total_result']) ? $this_ability->ability_results['total_result'] : 0).'</span>';
            $this_ability->ability_results['print_amount'] = '<span class="recovery_amount">'.(!empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0).'</span>';
            $this_ability->ability_results['print_overkill'] = '<span class="recovery_overkill">'.(!empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0).'</span>';

            // Add the final recovery text showing the amount based on life energy recovery
            if ($this_ability->recovery_options['recovery_kind'] == 'energy'){
                $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} recovers {$this_ability->ability_results['print_amount']} life energy";
                //$this_ability->ability_results['this_text'] .= ($this_ability->ability_results['this_overkill'] > 0 ? " and {$this_ability->ability_results['print_overkill']} overkill" : '');
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise add the final recovery text showing the amount based on weapon energy recovery
            elseif ($this_ability->recovery_options['recovery_kind'] == 'weapons'){
                $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()} recovers {$this_ability->ability_results['print_amount']} weapon energy";
                $this_ability->ability_results['this_text'] .= '!<br />';
            }
            // Otherwise, if this is one of the robot's other internal stats
            elseif ($this_ability->recovery_options['recovery_kind'] == 'attack'
                || $this_ability->recovery_options['recovery_kind'] == 'defense'
                || $this_ability->recovery_options['recovery_kind'] == 'speed'){
                // Print the result based on if the stat will go any lower
                if ($this_ability->ability_results['this_amount'] > 0){
                    $this_ability->ability_results['this_text'] .= "{$this->print_robot_name()}&#39;s {$this_ability->recovery_options['recovery_kind']} rose by {$this_ability->ability_results['print_amount']}";
                    $this_ability->ability_results['this_text'] .= '!<br />';
                }
                // Otherwise if the stat wouldn't go any lower
                else {

                    // Update this robot's frame based on recovery type
                    $this_ability->ability_frame = $this_ability->recovery_options['ability_failure_frame'];
                    $this_ability->ability_frame_span = $this_ability->recovery_options['ability_failure_frame_span'];
                    $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_failure_frame_offset'];

                    // Display the failure text, if text has been provided
                    if (!empty($this_ability->recovery_options['failure_text'])){
                        $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['failure_text'].' ';
                    }
                }
            }

        }
        // Otherwise, if the attack was a failure
        else {

            // Update this robot's frame based on recovery type
            $this_ability->ability_frame = $this_ability->recovery_options['ability_failure_frame'];
            $this_ability->ability_frame_span = $this_ability->recovery_options['ability_failure_frame_span'];
            $this_ability->ability_frame_offset = $this_ability->recovery_options['ability_failure_frame_offset'];

            // Update the recovery and overkilll amounts to reflect zero recovery
            $this_ability->ability_results['this_amount'] = 0;
            $this_ability->ability_results['this_overkill'] = 0;

            // Display the failure text, if text has been provided
            if (!$this_ability->ability_results['flag_immunity'] && !empty($this_ability->recovery_options['failure_text'])){
                $this_ability->ability_results['this_text'] .= $this_ability->recovery_options['failure_text'].' ';
            }

        }

        // Update this robot's history with the triggered recovery amount
        $this->history['triggered_recovery'][] = $this_ability->ability_results['this_amount'];
        // Update the robot's history with the triggered recovery types
        if (!empty($this_ability->ability_results['recovery_type'])){
            $temp_types = array();
            $temp_types[] = $this_ability->ability_results['recovery_type'];
            if (!empty($this_ability->ability_results['recovery_type2'])){ $temp_types[] = $this_ability->ability_results['recovery_type2']; }
            $this->history['triggered_recovery_types'][] = $temp_types;
        } else {
            $this->history['triggered_recovery_types'][] = array();
        }

        // Update the recovery result total variables
        $this_ability->ability_results['total_amount'] += !empty($this_ability->ability_results['this_amount']) ? $this_ability->ability_results['this_amount'] : 0;
        $this_ability->ability_results['total_overkill'] += !empty($this_ability->ability_results['this_overkill']) ? $this_ability->ability_results['this_overkill'] : 0;
        if ($this_ability->ability_results['this_result'] == 'success'){ $this_ability->ability_results['total_strikes']++; }
        else { $this_ability->ability_results['total_misses']++; }
        $this_ability->ability_results['total_actions'] = $this_ability->ability_results['total_strikes'] + $this_ability->ability_results['total_misses'];
        if ($this_ability->ability_results['total_result'] != 'success'){ $this_ability->ability_results['total_result'] = $this_ability->ability_results['this_result']; }
        $event_options['this_ability_results'] = $this_ability->ability_results;

        // Update internal variables
        $target_robot->update_session();
        $target_robot->player->update_session();
        $this->update_session();
        $this->player->update_session();

        // Generate an event with the collected recovery results based on recovery type
        if ($this->robot_id == $target_robot->robot_id){ //$this_ability->recovery_options['recovery_kind'] == 'energy'
            $event_options['console_show_target'] = false;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;
            $this->battle->events_create($target_robot, $this, $this_ability->recovery_options['recovery_header'], $this_ability->ability_results['this_text'], $event_options);
        } else {
            $event_options['console_show_target'] = false;
            $event_options['this_ability_target'] = $this->robot_id.'_'.$this->robot_token;;
            $this->battle->events_create($this, $target_robot, $this_ability->recovery_options['recovery_header'], $this_ability->ability_results['this_text'], $event_options);
        }

        // Restore this and the target robot's frames to their backed up state
        $this->robot_frame = $this_robot_backup_frame;
        $this->player->player_frame = $this_player_backup_frame;
        $target_robot->robot_frame = $target_robot_backup_frame;
        $target_robot->player->player_frame = $target_player_backup_frame;
        $this_ability->ability_frame = $this_ability_backup_frame;

        // Update internal variables
        $target_robot->update_session();
        $target_robot->player->update_session();
        $this->update_session();
        $this->player->update_session();
        $this_ability->update_session();

        // If this robot has been disabled, add a defeat attachment
        if ($this->robot_status == 'disabled'){

            // Define this ability's attachment token
            $temp_frames = array(0,4,1,5,2,6,3,7,4,8,5,9,0,1,2,3,4,5,6,7,8,9);
            shuffle($temp_frames);
            $this_attachment_token = 'ability_attachment-defeat';
            $this_attachment_info = array(
                'class' => 'ability',
                'ability_token' => 'attachment-defeat',
                'attachment_flag_defeat' => true,
                'ability_frame' => 0,
                'ability_frame_animate' => $temp_frames,
                'ability_frame_offset' => array('x' => 0, 'y' => -10, 'z' => -10)
                );

            // If the attachment doesn't already exists, add it to the robot
            if (!isset($this->robot_attachments[$this_attachment_token])){
                $this->robot_attachments[$this_attachment_token] =  $this_attachment_info;
                $this->update_session();
            }

        }

        // If this robot was disabled, process experience for the target
        if ($this->robot_status == 'disabled' && $trigger_disabled){
            $this->trigger_disabled($target_robot, $this_ability);
        }
        // Otherwise, if the target robot was not disabled
        elseif ($this->robot_status != 'disabled'){

            // -- CHECK ATTACHMENTS -- //

            // Ensure the ability was a success before checking attachments
            if ($this_ability->ability_results['this_result'] == 'success'){
                // If this robot has any attachments, loop through them
                if (!empty($this->robot_attachments)){
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments');
                    foreach ($this->robot_attachments AS $attachment_token => $attachment_info){

                        // Ensure this ability has a type before checking weaknesses, resistances, etc.
                        if (!empty($this_ability->ability_type) || in_array('*', $attachment_info['attachment_weaknesses'])){

                            // If this attachment has weaknesses defined and this ability is a match
                            if (!empty($attachment_info['attachment_weaknesses'])
                                && (in_array('*', $attachment_info['attachment_weaknesses'])
                                    || in_array($this_ability->ability_type, $attachment_info['attachment_weaknesses'])
                                    || in_array($this_ability->ability_type2, $attachment_info['attachment_weaknesses']))
                                    ){
                                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint weaknesses');
                                // Remove this attachment and inflict damage on the robot
                                unset($this->robot_attachments[$attachment_token]);
                                $this->update_session();
                                if ($attachment_info['attachment_destroy'] !== false){
                                    $temp_attachment = new rpg_ability($this->battle, $this->player, $this, array('ability_token' => $attachment_info['ability_token']));
                                    $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                                    //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                                    //$this_battle->events_create(false, false, 'DEBUG', 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
                                    if ($temp_trigger_type == 'damage'){
                                        $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->update_session();
                                        $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                        if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                                            $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                            $temp_trigger_options = array('apply_modifiers' => false);
                                            $this->trigger_damage($target_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                                        }
                                    } elseif ($temp_trigger_type == 'recovery'){
                                        $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->update_session();
                                        $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                        if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                                            $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                            $temp_trigger_options = array('apply_modifiers' => false);
                                            $this->trigger_recovery($target_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                                        }
                                    } elseif ($temp_trigger_type == 'special'){
                                        $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                        $temp_attachment->update_session();
                                        //$this->trigger_damage($target_robot, $temp_attachment, 0, false);
                                        $this->trigger_target($target_robot, $temp_attachment, array('canvas_show_this_ability' => false, 'prevent_default_text' => true));
                                    }
                                }
                                // If this robot was disabled, process experience for the target
                                if ($this->robot_status == 'disabled'){ break; }
                            }

                        }

                    }
                }

            }

        }

        // Return the final recovery results
        return $this_ability->ability_results;

    }

    // Define a trigger for processing disabled events
    public function trigger_disabled($target_robot, $this_ability, $trigger_options = array()){

        // Pull in the global variable
        global $mmrpg_index, $db;

        // Generate default trigger options if not set
        if (!isset($trigger_options['item_multiplier'])){ $trigger_options['item_multiplier'] = 1.0; }
        /*
         * ROBOT CLASS FUNCTION TRIGGER DISABLED
         * public function trigger_disabled($target_robot, $this_ability){}
         */

        // Create references to save time 'cause I'm tired
        // (rather than replace all target references to this references)
        $this_battle = &$this->battle;
        $this_player = &$this->player; // the player of the robot being disabled
        $this_robot = &$this; // the robot being disabled
        $target_player = &$target_robot->player; // the player of the other robot
        $target_robot = &$target_robot; // the other robot that isn't this one

        // If the target player is the same as the current
        if ($this_player->player_id == $target_player->player_id){
            //$this->battle->events_create(false, false, 'DEBUG', 'It appears the target and the subject player are the same... ('.$this_player->player_id.' == '.$target_player->player_id.')');
            // Collect the actual target player from the battle values
            if (!empty($this->battle->values['players'])){
                foreach ($this->battle->values['players'] AS $id => $info){
                    if ($this_player->player_id != $id){
                        unset($target_player);
                        $target_player = new rpg_player($this_battle, $info);
                        //$this->battle->events_create(false, false, 'DEBUG', 'Assiging $this->battle->values[\'players\']['.$id.'] = '.$info['player_token']);
                    }
                }
            }
            // Collect the actual target robot from the battle values
            if (!empty($target_player->values['robots_active'])){
                foreach ($target_player->values['robots_active'] AS $key => $info){
                    if ($info['robot_position'] == 'active'){
                        $target_robot->robot_load($info);
                        //unset($target_robot);
                        //$target_robot = new rpg_robot($this_battle, $target_player, $info);
                        //$this->battle->events_create(false, false, 'DEBUG', 'Assiging $target_player->values[\'robots_active\']['.$key.'] = '.$info['robot_token']);
                    }
                }
            }
            //$this->battle->events_create(false, false, 'DEBUG', 'But after some magic...! ('.$this_player->player_id.' == '.$target_player->player_id.')');
        }

        // Update the target player's session
        $this_player->update_session();

        // Create the robot disabled event
        $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
        $event_body = ($this_player->player_token != 'player' ? $this_player->print_player_name().'&#39;s ' : 'The target ').' '.$this_robot->print_robot_name().' was disabled!<br />'; //'.($this_robot->robot_position == 'bench' ? ' and removed from battle' : '').'
        if (isset($this_robot->robot_quotes['battle_defeat'])){
            $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
            $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
            $event_body .= $this_robot->print_robot_quote('battle_defeat', $this_find, $this_replace);
            //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_defeat']);
            //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
        }
        $target_robot->robot_frame = 'base';
        $this_robot->robot_frame = 'defeat';
        $target_robot->update_session();
        $this_robot->update_session();
        $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, array('console_show_target' => false, 'canvas_show_disabled_bench' => $this_robot->robot_id.'_'.$this_robot->robot_token));


        /*
         * EFFORT VALUES / STAT BOOST BONUSES
         */

        // Define the event options array
        $event_options = array();
        $event_options['this_ability_results']['total_actions'] = 0;

        // Calculate the bonus boosts from defeating the target robot (if NOT player battle)
        if ($target_player->player_side == 'left' && $this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID){

            // Collect this robot's stat details for reference
            $temp_index_info = self::get_index_info($target_robot->robot_token);
            $temp_reward_info = mmrpg_prototype_robot_rewards($target_player->player_token, $target_robot->robot_token);
            $temp_robot_stats = self::calculate_stat_values($target_robot->robot_level, $temp_index_info, $temp_reward_info);

            // Define the stats to loop through and alter
            //$stat_tokens = array('energy', 'attack', 'defense', 'speed');
            $stat_tokens = array('attack', 'defense', 'speed');
            $stat_system = array('attack' => 'weapons', 'defense' => 'shield', 'speed' => 'mobility');

            // Define the temporary boost actions counter
            $temp_boost_actions = 1;

            // Loop through the stats applying STAT BONUSES to any that apply
            foreach ($stat_tokens AS $stat){

                // Boost this robot's stat if a boost is in order
                $prop_stat = "robot_{$stat}";
                $prop_stat_base = "robot_base_{$stat}";
                $prop_stat_pending = "robot_{$stat}_pending";
                $this_stat_boost = $this_robot->$prop_stat_base / 100;
                if ($this_robot->robot_class == 'mecha'){ $this_stat_boost = $this_stat_boost / 2; }
                if ($target_player->player_side == 'left' && $target_robot->robot_class == 'mecha'){ $this_stat_boost = $this_stat_boost * 2; }
                if ($target_robot->$prop_stat + $this_stat_boost > MMRPG_SETTINGS_STATS_MAX){
                    $this_stat_overboost = (MMRPG_SETTINGS_STATS_MAX - $target_robot->$prop_stat) * -1;
                    $this_stat_boost = $this_stat_boost - $this_stat_overboost;
                } elseif ($temp_robot_stats[$stat]['current'] >= $temp_robot_stats[$stat]['max']){
                    $this_stat_overboost = 0;
                    $this_stat_boost = 0;
                }
                $this_stat_boost = round($this_stat_boost);

                // If the stat was not empty, process it
                if ($this_stat_boost > 0){

                    // If the robot is under level 100, stat boosts are pending
                    if ($target_player->player_side == 'left' && $target_robot->robot_level < 100 && $target_robot->robot_class == 'master'){

                        // Update the session variables with the pending stat boost
                        if (empty($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat_pending])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat_pending] = 0; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat_pending] += $this_stat_boost;

                    }
                    // If the robot is at level 100 or a mecha, stat boosts are immediately rewarded
                    elseif ($target_player->player_side == 'left' && (($target_robot->robot_level == 100 && $target_robot->robot_class == 'master') || $target_robot->robot_class == 'mecha') && $target_robot->$prop_stat_base < MMRPG_SETTINGS_STATS_MAX){

                        // Define the base stat boost based on robot base stats
                        $temp_stat_boost = ceil($this_stat_boost);
                        $temp_stat_base_boost = $temp_stat_boost;
                        if ($temp_stat_boost + $target_robot->$prop_stat > MMRPG_SETTINGS_STATS_MAX){ $temp_stat_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->$prop_stat; }
                        if ($temp_stat_base_boost + $target_robot->$prop_stat_base > MMRPG_SETTINGS_STATS_MAX){ $temp_stat_base_boost = MMRPG_SETTINGS_STATS_MAX - $target_robot->$prop_stat_base; }

                        // Increment this robot's stat by the calculated amount and display an event
                        $target_robot->$prop_stat = ceil($target_robot->$prop_stat + $temp_stat_boost);
                        $target_robot->$prop_stat_base = ceil($target_robot->$prop_stat_base + $temp_stat_base_boost);
                        $event_options = array();
                        $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                        $event_options['this_ability_results']['recovery_kind'] = $stat;
                        $event_options['this_ability_results']['recovery_type'] = '';
                        $event_options['this_ability_results']['flag_affinity'] = true;
                        $event_options['this_ability_results']['flag_critical'] = true;
                        $event_options['this_ability_results']['this_amount'] = $temp_stat_boost;
                        $event_options['this_ability_results']['this_result'] = 'success';
                        $event_options['this_ability_results']['total_actions'] = $temp_boost_actions++;
                        $event_options['this_ability_target'] = $target_robot->robot_id.'_'.$target_robot->robot_token;
                        $event_options['console_show_target'] = false;
                        $event_body = $target_robot->print_robot_name().' downloads '.$stat_system[$stat].' data from the target robot! ';
                        $event_body .= '<br />';
                        $event_body .= $target_robot->print_robot_name().'&#39;s '.$stat.' grew by <span class="recovery_amount">'.$temp_stat_boost.'</span>! ';
                        $target_robot->robot_frame = 'shoot';
                        $target_robot->update_session();
                        $target_player->update_session();
                        $this_battle->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

                        // Update the session variables with the rewarded stat boost if not mecha
                        if ($target_robot->robot_class == 'master'){
                            if (!isset($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat] = 0; }
                            $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat] = ceil($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat]);
                            $temp_stat_session_boost = round($this_stat_boost);
                            if ($temp_stat_session_boost < 1){ $temp_stat_session_boost = 1; }
                            $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$target_robot->robot_token][$prop_stat] += $temp_stat_session_boost;
                        }


                    }

                }

            }

            // Update the target robot frame
            $target_robot->robot_frame = 'base';
            $target_robot->update_session();

        }

        // Ensure player and robot variables are updated
        $target_robot->update_session();
        $target_player->update_session();
        $this_robot->update_session();
        $this_player->update_session();

        /*
        // DEBUG
        $this->battle->events_create(false, false, 'DEBUG', 'we made it past the stat boosts... <br />'.
            '$this_robot->robot_token='.$this_robot->robot_token.'; $target_robot->robot_token='.$target_robot->robot_token.';<br />'.
            '$target_player->player_token='.$target_player->player_token.'; $target_player->player_side='.$target_player->player_side.';<br />'
            );
        */

        /*
         * ITEM REWARDS / EXPERIENCE POINTS / LEVEL UP
         * Reward the player and robots with items and experience if not in demo mode
         */

        if ($target_player->player_side == 'left' && $this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && empty($_SESSION['GAME']['DEMO'])){

            // -- EXPERIENCE POINTS / LEVEL UP -- //

            // Filter out robots who were active in this battle in at least some way
            $temp_robots_active = $target_player->values['robots_active'];
            usort($temp_robots_active, array('rpg_player','robot_sort_by_active'));


            // Define the boost multiplier and start out at zero
            $temp_boost_multiplier = 0;

            // DEBUG
            //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $this_robot->counters = <pre>'.print_r($this_robot->counters, true).'</pre>');
            //$this_battle->events_create(false, false, 'DEBUG', $event_body);

            // If the target has had any damage flags triggered, update the multiplier
            //if ($this_robot->flags['triggered_immunity']){ $temp_boost_multiplier += 0; }
            //if (!empty($this_robot->flags['triggered_resistance'])){ $temp_boost_multiplier -= $this_robot->counters['triggered_resistance'] * 0.10; }
            //if (!empty($this_robot->flags['triggered_affinity'])){ $temp_boost_multiplier -= $this_robot->counters['triggered_affinity'] * 0.10; }
            //if (!empty($this_robot->flags['triggered_weakness'])){ $temp_boost_multiplier += $this_robot->counters['triggered_weakness'] * 0.10; }
            //if (!empty($this_robot->flags['triggered_critical'])){ $temp_boost_multiplier += $this_robot->counters['triggered_critical'] * 0.10; }

            // If we're in DEMO mode, give a 100% experience boost
            //if (!empty($_SESSION['GAME']['DEMO'])){ $temp_boost_multiplier += 1; }

            // Ensure the multiplier has not gone below 100%
            if ($temp_boost_multiplier < -0.99){ $temp_boost_multiplier = -0.99; }
            elseif ($temp_boost_multiplier > 0.99){ $temp_boost_multiplier = 0.99; }

            // Define the boost text to match the multiplier
            $temp_boost_text = '';
            if ($temp_boost_multiplier < 0){ $temp_boost_text = 'a lowered '; }
            elseif ($temp_boost_multiplier > 0){ $temp_boost_text = 'a boosted '; }

            /*
            $event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.'<pre>'.print_r($this_robot->flags, true).'</pre>');
            $this_battle->events_create(false, false, 'DEBUG', $event_body);

            $event_body = preg_replace('/\s+/', ' ', $target_robot->robot_token.'<pre>'.print_r($target_robot->flags, true).'</pre>');
            $this_battle->events_create(false, false, 'DEBUG', $event_body);
            */


            // Define the base experience for the target robot
            $temp_experience = $this_robot->robot_base_energy + $this_robot->robot_base_attack + $this_robot->robot_base_defense + $this_robot->robot_base_speed;

            // DEBUG
            //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_boost_multiplier = '.$temp_boost_multiplier.'; $temp_experience = '.$temp_experience.'; ');
            //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $event_body);

            // Apply any boost multipliers to the experience earned
            if ($temp_boost_multiplier > 0 || $temp_boost_multiplier < 0){ $temp_experience += $temp_experience * $temp_boost_multiplier; }
            if ($temp_experience <= 0){ $temp_experience = 1; }
            $temp_experience = round($temp_experience);
            $temp_target_experience = array('level' => $this_robot->robot_level, 'experience' => $temp_experience);

            // DEBUG
            //$event_body = preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_target_experience = <pre>'.print_r($temp_target_experience, true).'</pre>');
            //$this_battle->events_create(false, false, 'DEBUG', $event_body);

            // Define the robot experience level and start at zero
            $target_robot_experience = 0;

            // Sort the active robots based on active or not
            /*
            function mmrpg_sort_temp_active_robots($info1, $info2){
                if ($info1['robot_position'] == 'active'){ return -1; }
                else { return 1; }
            }
            usort($temp_robots_active, 'mmrpg_sort_temp_active_robots');
            */

            // Increment each of this player's robots
            $temp_robots_active_num = count($temp_robots_active);
            $temp_robots_active_num2 = $temp_robots_active_num; // This will be decremented for each non-experience gaining level 100 robots
            $temp_robots_active = array_reverse($temp_robots_active, true);
            usort($temp_robots_active, array('rpg_player', 'robot_sort_by_active'));
            $temp_robot_active_position = false;
            foreach ($temp_robots_active AS $temp_id => $temp_info){
                $temp_robot = $target_robot->robot_id == $temp_info['robot_id'] ? $target_robot : new rpg_robot($this, $target_player, $temp_info);
                if ($temp_robot->robot_level >= 100 || $temp_robot->robot_class != 'master'){ $temp_robots_active_num2--; }
                if ($temp_robot->robot_position == 'active'){
                    $temp_robot_active_position = $temp_robots_active[$temp_id];
                    unset($temp_robots_active[$temp_id]);
                }
            }
            $temp_unshift = array_unshift($temp_robots_active, $temp_robot_active_position);

            foreach ($temp_robots_active AS $temp_id => $temp_info){

                // Collect or define the robot points and robot rewards variables
                $temp_robot = $target_robot->robot_id == $temp_info['robot_id'] ? $target_robot : new rpg_robot($this, $target_player, $temp_info);
                //if ($temp_robot->robot_class == 'mecha'){ continue; }
                $temp_robot_token = $temp_info['robot_token'];
                if ($temp_robot_token == 'robot'){ continue; }
                $temp_robot_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_info['robot_token']);
                $temp_robot_rewards = !empty($temp_info['robot_rewards']) ? $temp_info['robot_rewards'] : array();
                if (empty($temp_robots_active_num2)){ break; }

                // Continue if over already at level 100
                //if ($temp_robot->robot_level >= 100){ continue; }

                // Reset the robot experience points to zero
                $target_robot_experience = 0;

                // Continue with experience mods only if under level 100
                if ($temp_robot->robot_level < 100 && $temp_robot->robot_class == 'master'){

                    // Give a proportionate amount of experience based on this and the target robot's levels
                    if ($temp_robot->robot_level == $temp_target_experience['level']){
                        $temp_experience_boost = $temp_target_experience['experience'];
                    } elseif ($temp_robot->robot_level < $temp_target_experience['level']){
                        $temp_experience_boost = $temp_target_experience['experience'] + round((($temp_target_experience['level'] - $temp_robot->robot_level) / 100)  * $temp_target_experience['experience']);
                        //$temp_experience_boost = $temp_target_experience['experience'] + ((($temp_target_experience['level']) / $temp_robot->robot_level) * $temp_target_experience['experience']);
                    } elseif ($temp_robot->robot_level > $temp_target_experience['level']){
                        $temp_experience_boost = $temp_target_experience['experience'] - round((($temp_robot->robot_level - $temp_target_experience['level']) / 100)  * $temp_target_experience['experience']);
                        //$temp_experience_boost = $temp_target_experience['experience'] - ((($temp_robot->robot_level - $temp_target_experience['level']) / 100) * $temp_target_experience['experience']);
                    }

                    // DEBUG
                    //$event_body = 'START EXPERIENCE | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    //$temp_experience_boost = ceil($temp_experience_boost / 10);
                    $temp_experience_boost = ceil($temp_experience_boost / $temp_robots_active_num);
                    //$temp_experience_boost = ceil($temp_experience_boost / ($temp_robots_active_num * 2));
                    //$temp_experience_boost = ceil($temp_experience_boost / ($temp_robots_active_num2 * 2));
                    //$temp_experience_boost = ceil(($temp_experience_boost / $temp_robots_active_num2) * 1.00);

                    if ($temp_experience_boost > MMRPG_SETTINGS_STATS_MAX){ $temp_experience_boost = MMRPG_SETTINGS_STATS_MAX; }
                    $target_robot_experience += $temp_experience_boost;

                    // DEBUG
                    //$event_body = 'ACTIVE ROBOT DIVISION | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; $temp_robots_active_num = '.$temp_robots_active_num.'; $temp_robots_active_num2 = '.$temp_robots_active_num2.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    // If this robot has been traded, give it an additional experience boost
                    $temp_experience_boost = 0;
                    $temp_robot_boost_text = $temp_boost_text;
                    if ($temp_robot->player_token != $temp_robot->robot_original_player){
                        $temp_robot_boost_text = 'a player boosted ';
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience = $target_robot_experience * 2;
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                    }

                    // DEBUG
                    //$event_body = 'PLAYER BOOSTED | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    // If there are field multipliers in place, apply them now
                    $temp_experience_boost = 0;
                    if (isset($this->field->field_multipliers['experience'])){
                        //$temp_robot_boost_text = '(and '.$target_robot_experience.' multiplied by '.number_format($this->field->field_multipliers['experience'], 1).') ';
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience = ceil($target_robot_experience * $this->field->field_multipliers['experience']);
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                    }

                    // DEBUG
                    //$event_body = 'FIELD MULTIPLIERS | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    // If this robot has any overkill, add that to the temp experience modifier
                    $temp_experience_boost = 0;
                    if (!empty($this_robot->counters['defeat_overkill'])){
                        if (empty($temp_robot_boost_text)){ $temp_robot_boost_text = 'an overkill boosted '; }
                        else { $temp_robot_boost_text = 'a player and overkill boosted '; }
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience += ceil($this_robot->counters['defeat_overkill'] / $temp_robots_active_num2);
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                    }

                    // DEBUG
                    //$event_body = 'OVERKILL BONUS | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $this_robot->robot_token.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    // If the target robot's core type has been boosted by starforce
                    if (!empty($temp_robot->robot_core) && !empty($_SESSION['GAME']['values']['star_force'][$temp_robot->robot_core])){
                        if (empty($temp_robot_boost_text)){ $temp_robot_boost_text = 'a starforce boosted '; }
                        elseif ($temp_robot_boost_text == 'an overkill boosted '){ $temp_robot_boost_text = 'an overkill and starforce boosted '; }
                        elseif ($temp_robot_boost_text == 'a player boosted '){ $temp_robot_boost_text = 'a player and starforce boosted '; }
                        else { $temp_robot_boost_text = 'a player, overkill, and starforce boosted '; }
                        $temp_starforce = $_SESSION['GAME']['values']['star_force'][$temp_robot->robot_core];
                        $temp_experience_bak = $target_robot_experience;
                        $target_robot_experience += ceil($target_robot_experience * ($temp_starforce / 10));
                        $temp_experience_boost = $target_robot_experience - $temp_experience_bak;
                    }

                    // DEBUG
                    //$event_body = 'STARFORCE BONUS | ';
                    //$event_body .= preg_replace('/\s+/', ' ', $temp_robot->robot_token.' : '.$temp_robot->robot_core.' : $temp_experience_boost = '.$temp_experience_boost.'; $target_robot_experience = '.$target_robot_experience.'; ');
                    //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                    // If the experience is greater then the max, level it off at the max (sorry guys!)
                    if ($target_robot_experience > MMRPG_SETTINGS_STATS_MAX){ $target_robot_experience = MMRPG_SETTINGS_STATS_MAX; }
                    if ($target_robot_experience < MMRPG_SETTINGS_STATS_MIN){ $target_robot_experience = MMRPG_SETTINGS_STATS_MIN; }

                    // Collect the robot's current experience and level for reference later
                    $temp_start_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_robot_token);
                    $temp_start_level = mmrpg_prototype_robot_level($target_player->player_token, $temp_robot_token);

                    // Increment this robots's points total with the battle points
                    $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] += $target_robot_experience;

                    // Define the new experience for this robot
                    $temp_new_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_info['robot_token']);// If the new experience is over 1000, level up the robot
                    $level_boost = 0;
                    if ($temp_new_experience > 1000){
                        $level_boost = floor($temp_new_experience / 1000);
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] += $level_boost;
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_experience'] -= $level_boost * 1000;
                        if ($_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] > 100){
                            $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_level'] = 100;
                        }
                        $temp_new_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_info['robot_token']);
                    }

                    // Define the new level for this robot
                    $temp_new_level = mmrpg_prototype_robot_level($target_player->player_token, $temp_robot_token); //floor($temp_new_experience / 1000) + 1;

                }
                // Otherwise if this is a level 100 robot already
                else {

                    // Collect the robot's current experience and level for reference later
                    $temp_start_experience = mmrpg_prototype_robot_experience($target_player->player_token, $temp_robot_token);
                    $temp_start_level = mmrpg_prototype_robot_level($target_player->player_token, $temp_robot_token);

                    // Define the new experience for this robot
                    $temp_new_experience = $temp_start_experience;
                    $temp_new_level = $temp_start_level;

                }

                // Define the event options
                $event_options = array();
                $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                $event_options['this_ability_results']['recovery_kind'] = 'experience';
                $event_options['this_ability_results']['recovery_type'] = '';
                $event_options['this_ability_results']['this_amount'] = $target_robot_experience;
                $event_options['this_ability_results']['this_result'] = 'success';
                $event_options['this_ability_results']['flag_affinity'] = true;
                $event_options['this_ability_results']['total_actions'] = 1;
                $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

                // Update player/robot frames and points for the victory
                $temp_robot->robot_frame = 'victory';
                $temp_robot->robot_level = $temp_new_level;
                $temp_robot->robot_experience = $temp_new_experience;
                $target_player->player_frame = 'victory';
                $temp_robot->update_session();
                $target_player->update_session();

                // Only display the event if the player is under level 100
                if ($temp_robot->robot_level < 100 && $temp_robot->robot_class == 'master'){
                    // Display the win message for this robot with battle points
                    $temp_robot->robot_frame = 'taunt';
                    $temp_robot->robot_level = $temp_new_level;
                    if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = 1000; }
                    $target_player->player_frame = 'victory';
                    $event_header = $temp_robot->robot_name.'&#39;s Rewards';
                    $event_multiplier_text = $temp_robot_boost_text;
                    $event_body = $temp_robot->print_robot_name().' collects '.$event_multiplier_text.'<span class="recovery_amount ability_type ability_type_cutter">'.$target_robot_experience.'</span> experience points! ';
                    $event_body .= '<br />';
                    if (isset($temp_robot->robot_quotes['battle_victory'])){
                        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                        $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $temp_robot->robot_name);
                        $event_body .= $temp_robot->print_robot_quote('battle_victory', $this_find, $this_replace);
                    }
                    //$event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);
                    if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = $temp_new_experience; }
                    $temp_robot->update_session();
                    $target_player->update_session();
                }

                // Floor the robot's experience with or without the event
                $target_player->player_frame = 'victory';
                $target_player->update_session();
                $temp_robot->robot_frame = 'base';
                if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = 0; }
                $temp_robot->update_session();

                // If the level has been boosted, display the stat increases
                if ($temp_start_level != $temp_new_level){

                    // Define the event options
                    $event_options = array();
                    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                    $event_options['this_ability_results']['recovery_kind'] = 'level';
                    $event_options['this_ability_results']['recovery_type'] = '';
                    $event_options['this_ability_results']['flag_affinity'] = true;
                    $event_options['this_ability_results']['flag_critical'] = true;
                    $event_options['this_ability_results']['this_amount'] = $temp_new_level - $temp_start_level;
                    $event_options['this_ability_results']['this_result'] = 'success';
                    $event_options['this_ability_results']['total_actions'] = 2;
                    $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

                    // Display the win message for this robot with battle points
                    $temp_robot->robot_frame = 'taunt';
                    $temp_robot->robot_level = $temp_new_level;
                    if ($temp_start_level != $temp_new_level){ $temp_robot->robot_experience = 1000; }
                    else { $temp_robot->robot_experience = $temp_new_experience; }
                    $target_player->player_frame = 'victory';
                    $event_header = $temp_robot->robot_name.'&#39;s Rewards';
                    //$event_body = $temp_robot->print_robot_name().' grew to <span class="recovery_amount'.($temp_new_level >= 100 ? ' ability_type ability_type_electric' : '').'">Level '.$temp_new_level.'</span>!<br /> ';
                    $event_body = $temp_robot->print_robot_name().' grew to <span class="recovery_amount ability_type ability_type_level">Level '.$temp_new_level.($temp_new_level >= 100 ? ' &#9733;' : '').'</span>!<br /> ';
                    $event_body .= $temp_robot->robot_name.'&#39;s energy, weapons, shields, and mobility were upgraded!';
                    //$event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);
                    $temp_robot->robot_experience = 0;
                    $temp_robot->update_session();

                    // Collect the base robot template from the index for calculations
                    $temp_index_robot = rpg_robot::get_index_info($temp_robot->robot_token);

                    // Define the event options
                    $event_options['this_ability_results']['trigger_kind'] = 'recovery';
                    $event_options['this_ability_results']['recovery_type'] = '';
                    $event_options['this_ability_results']['this_amount'] = $level_boost;
                    $event_options['this_ability_results']['this_result'] = 'success';
                    $event_options['this_ability_results']['total_actions'] = 0;
                    $event_options['this_ability_target'] = $temp_robot->robot_id.'_'.$temp_robot->robot_token;

                    // Update the robot rewards array with any recent info
                    $temp_robot_rewards = mmrpg_prototype_robot_rewards($target_player->player_token, $temp_robot->robot_token);
                    //$this_battle->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('/\s+/', ' ', print_r($temp_robot_rewards, true)).'</pre>', $event_options);

                    // Define the base energy boost based on robot base stats
                    $temp_energy_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_energy']));

                    // If this robot has reached level 100, the max level, create the flag in their session
                    if ($temp_new_level >= 100){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['flags']['reached_max_level'] = true; }

                    // Check if there are eny pending energy stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_energy_pending'])){
                        $temp_robot_rewards['robot_energy_pending'] = round($temp_robot_rewards['robot_energy_pending']);
                        $temp_energy_boost += $temp_robot_rewards['robot_energy_pending'];
                        if (!empty($temp_robot_rewards['robot_energy'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy'] += $temp_robot_rewards['robot_energy_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy'] = $temp_robot_rewards['robot_energy_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_energy_pending'] = 0;
                    }

                    // Increment this robot's energy by the calculated amount and display an event
                    $temp_robot->robot_energy += $temp_energy_boost;
                    $temp_base_energy_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_energy']));
                    $temp_robot->robot_base_energy += $temp_base_energy_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'energy';
                    $event_options['this_ability_results']['this_amount'] = $temp_energy_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $event_body = $temp_robot->print_robot_name().'&#39;s health improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_robot_name().'&#39;s energy grew by <span class="recovery_amount">'.$temp_energy_boost.'</span>! ';
                    $temp_robot->robot_frame = 'summon';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


                    // Define the base attack boost based on robot base stats
                    $temp_stat_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_attack']));

                    // Check if there are eny pending attack stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_attack_pending'])){
                        $temp_robot_rewards['robot_attack_pending'] = round($temp_robot_rewards['robot_attack_pending']);
                        $temp_stat_boost += $temp_robot_rewards['robot_attack_pending'];
                        if (!empty($temp_robot_rewards['robot_attack'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack'] += $temp_robot_rewards['robot_attack_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack'] = $temp_robot_rewards['robot_attack_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_attack_pending'] = 0;
                    }

                    // Increment this robot's attack by the calculated amount and display an event
                    $temp_robot->robot_attack += $temp_stat_boost;
                    $temp_base_attack_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_attack']));
                    $temp_robot->robot_base_attack += $temp_base_attack_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'attack';
                    $event_options['this_ability_results']['this_amount'] = $temp_stat_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $event_body = $temp_robot->print_robot_name().'&#39;s weapons improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_robot_name().'&#39;s attack grew by <span class="recovery_amount">'.$temp_stat_boost.'</span>! ';
                    $temp_robot->robot_frame = 'shoot';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


                    // Define the base defense boost based on robot base stats
                    $temp_defense_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_defense']));

                    // Check if there are eny pending defense stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_defense_pending'])){
                        $temp_robot_rewards['robot_defense_pending'] = round($temp_robot_rewards['robot_defense_pending']);
                        $temp_defense_boost += $temp_robot_rewards['robot_defense_pending'];
                        if (!empty($temp_robot_rewards['robot_defense'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense'] += $temp_robot_rewards['robot_defense_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense'] = $temp_robot_rewards['robot_defense_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_defense_pending'] = 0;
                    }

                    // Increment this robot's defense by the calculated amount and display an event
                    $temp_robot->robot_defense += $temp_defense_boost;
                    $temp_base_defense_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_defense']));
                    $temp_robot->robot_base_defense += $temp_base_defense_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'defense';
                    $event_options['this_ability_results']['this_amount'] = $temp_defense_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $event_body = $temp_robot->print_robot_name().'&#39;s shields improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_robot_name().'&#39;s defense grew by <span class="recovery_amount">'.$temp_defense_boost.'</span>! ';
                    $temp_robot->robot_frame = 'defend';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);


                    // Define the base speed boost based on robot base stats
                    $temp_speed_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_speed']));

                    // Check if there are eny pending speed stat boosts for level up
                    if (!empty($temp_robot_rewards['robot_speed_pending'])){
                        $temp_robot_rewards['robot_speed_pending'] = round($temp_robot_rewards['robot_speed_pending']);
                        $temp_speed_boost += $temp_robot_rewards['robot_speed_pending'];
                        if (!empty($temp_robot_rewards['robot_speed'])){ $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_speed'] += $temp_robot_rewards['robot_speed_pending']; }
                        else { $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot->robot_token]['robot_speed'] = $temp_robot_rewards['robot_speed_pending']; }
                        $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_robots'][$temp_robot_token]['robot_speed_pending'] = 0;
                    }

                    // Increment this robot's speed by the calculated amount and display an event
                    $temp_robot->robot_speed += $temp_speed_boost;
                    $event_options['this_ability_results']['recovery_kind'] = 'speed';
                    $event_options['this_ability_results']['this_amount'] = $temp_speed_boost;
                    $event_options['this_ability_results']['total_actions']++;
                    $temp_base_speed_boost = ceil($level_boost * (0.05 * $temp_index_robot['robot_speed']));
                    $temp_robot->robot_base_speed += $temp_base_speed_boost;
                    $event_body = $temp_robot->print_robot_name().'&#39;s mobility improved! ';
                    $event_body .= '<br />';
                    $event_body .= $temp_robot->print_robot_name().'&#39;s speed grew by <span class="recovery_amount">'.$temp_speed_boost.'</span>! ';
                    $temp_robot->robot_frame = 'slide';
                    $temp_robot->update_session();
                    $target_player->update_session();
                    $this_battle->events_create($temp_robot, $this_robot, $event_header, $event_body, $event_options);

                    // Update the robot frame
                    $temp_robot->robot_frame = 'base';
                    $temp_robot->update_session();

                }

                // Update the experience level for real this time
                $temp_robot->robot_experience = $temp_new_experience;
                $temp_robot->update_session();

                // Collect the robot info array
                $temp_robot_info = $temp_robot->export_array();

                // Collect the indexed robot rewards for new abilities
                $index_robot_rewards = $temp_robot_info['robot_rewards'];
                //$event_body = preg_replace('/\s+/', ' ', '<pre>'.print_r($index_robot_rewards, true).'</pre>');
                //$this_battle->events_create(false, false, 'DEBUG', $event_body);

                // Loop through the ability rewards for this robot if set
                if ($temp_robot->robot_class != 'mecha' && ($temp_start_level == 100 || ($temp_start_level != $temp_new_level && !empty($index_robot_rewards['abilities'])))){
                    $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                    foreach ($index_robot_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){

                        // If this ability is already unlocked, continue
                        if (mmrpg_prototype_ability_unlocked($target_player->player_token, $temp_robot_token, $ability_reward_info['token'])){ continue; }
                        // If we're in DEMO mode, continue
                        if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

                        // Check if the required level has been met by this robot
                        if ($temp_new_level >= $ability_reward_info['level']){

                            // Collect the ability info from the index
                            $ability_info = rpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
                            // Create the temporary ability object for event creation
                            $temp_ability = new rpg_ability($this->battle, $target_player, $temp_robot, $ability_info);

                            // Collect or define the ability variables
                            $temp_ability_token = $ability_info['ability_token'];

                            // Display the robot reward message markup
                            $event_header = $ability_info['ability_name'].' Unlocked';
                            $event_body = '<span class="robot_name">'.$temp_info['robot_name'].'</span> unlocked new ability data!<br />';
                            $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
                            $event_options = array();
                            $event_options['console_show_target'] = false;
                            $event_options['this_header_float'] = $target_player->player_side;
                            $event_options['this_body_float'] = $target_player->player_side;
                            $event_options['this_ability'] = $temp_ability;
                            $event_options['this_ability_image'] = 'icon';
                            $event_options['console_show_this_player'] = false;
                            $event_options['console_show_this_robot'] = false;
                            $event_options['console_show_this_ability'] = true;
                            $event_options['canvas_show_this_ability'] = false;
                            $temp_robot->robot_frame = $ability_reward_key % 2 == 2 ? 'taunt' : 'victory';
                            $temp_robot->update_session();
                            $temp_ability->ability_frame = 'base';
                            $temp_ability->update_session();
                            $this_battle->events_create($temp_robot, false, $event_header, $event_body, $event_options);
                            $temp_robot->robot_frame = 'base';
                            $temp_robot->update_session();

                            // Automatically unlock this ability for use in battle
                            $this_reward = array('ability_token' => $temp_ability_token);
                            $temp_player_info = $target_player->export_array();
                            mmrpg_game_unlock_ability($temp_player_info, $temp_robot_info, $this_reward, true);
                            if ($temp_robot_info['robot_original_player'] == $temp_player_info['player_token']){ mmrpg_game_unlock_ability($temp_player_info, false, $this_reward); }
                            else { mmrpg_game_unlock_ability(array('player_token' => $temp_robot_info['robot_original_player']), false, $this_reward, true); }
                            //$_SESSION['GAME']['values']['battle_rewards'][$target_player_token]['player_robots'][$temp_robot_token]['robot_abilities'][$temp_ability_token] = $this_reward;

                        }

                    }
                }

            }


            // -- ITEM REWARDS -- //
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'item rewards');  }

            // Define the temp player rewards array
            $target_player_rewards = array();

            // Define the chance multiplier and start at one
            $temp_chance_multiplier = $trigger_options['item_multiplier'];
            // Increase the item chance multiplier if one is set for the stage
            if (isset($this_battle->field->field_multipliers['items'])){ $temp_chance_multiplier = ($temp_chance_multiplier * $this_battle->field->field_multipliers['items']); }

            // Define the available item drops for this battle
            $target_player_rewards['items'] = !empty($this_battle->battle_rewards['items']) ? $this_battle->battle_rewards['items'] : array();

            // If this robot was a MECHA class, it may drop PELLETS and SMALL SCREWS
            if ($this_robot->robot_class == 'mecha'){
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'add item energy and weapon pellets for mechas');  }
                $target_player_rewards['items'][] =  array('chance' => 15, 'token' => 'item-energy-pellet');
                $target_player_rewards['items'][] =  array('chance' => 15, 'token' => 'item-weapon-pellet');
                $target_player_rewards['items'][] =  array('chance' => 30, 'token' => 'item-screw-small');
            }

            // If this robot was a MASTER class, it may drop CAPSULES and LARGE SCREWS
            if ($this_robot->robot_class == 'master'){
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'add item energy and weapon capsules for masters');  }
                $target_player_rewards['items'][] =  array('chance' => 25, 'token' => 'item-energy-capsule');
                $target_player_rewards['items'][] =  array('chance' => 25, 'token' => 'item-weapon-capsule');
                $target_player_rewards['items'][] =  array('chance' => 50, 'token' => 'item-screw-large');
            }

            // Precount the item values for later use
            $temp_value_total = 0;
            $temp_count_total = 0;
            foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $temp_value_total += $item_reward_info['chance']; $temp_count_total += 1; }
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$temp_count_total = '.$temp_count_total.';<br /> $temp_value_total = '.$temp_value_total.'; ');  }

            // If this robot was a MASTER class and destroyed by WEAKNESS, it may drop a CORE
            if ($this_robot->robot_class == 'master' && !empty($this_robot->flags['triggered_weakness'])){
                $temp_core_type = !empty($this->robot_core) ? $this->robot_core : 'none';
                $temp_chance_value = ($temp_value_total * 4);
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'add item core '.$temp_core_type.' with '.$temp_chance_value.' chance');  }
                $target_player_rewards['items'][] =  array('chance' => $temp_chance_value, 'token' => 'item-core-'.$temp_core_type);
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$target_player_rewards[\'items\'] = '.json_encode($target_player_rewards['items']));  }
            }

            // Shuffle the rewards so it doesn't look to formulaic
            shuffle($target_player_rewards['items']);

            // DEBUG
            //$this_battle->events_create(false, false, 'DEBUG', '$target_player_rewards[\'items\'] = '.count($target_player_rewards['items']));

            // Define a function for dealing with item drops
            if (!function_exists('temp_player_rewards_items')){
                function temp_player_rewards_items($this_battle, $target_player, $target_robot, $this_robot, $item_reward_key, $item_reward_info){
                    global $mmrpg_index;
                    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'temp_player_rewards_items('.$item_reward_info['ability_token'].')');  }

                    // Create the temporary ability object for event creation
                    $temp_ability = new rpg_ability($this_battle, $target_player, $target_robot, $item_reward_info);
                    $temp_ability->ability_name = $item_reward_info['ability_name'];
                    $temp_ability->ability_image = $item_reward_info['ability_token'];
                    $temp_ability->update_session();

                    // Collect or define the ability variables
                    $temp_item_token = $item_reward_info['ability_token'];
                    $temp_item_name = $item_reward_info['ability_name'];
                    $temp_item_colour = !empty($item_reward_info['ability_type']) ? $item_reward_info['ability_type'] : 'none';
                    if (!empty($item_reward_info['ability_type2'])){ $temp_item_colour .= '_'.$item_reward_info['ability_type2']; }
                    // Create the session variable for this item if it does not exist and collect its value
                    if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
                    $temp_item_quantity = $_SESSION['GAME']['values']['battle_items'][$temp_item_token];
                    // If this item is already at the quantity limit, skip it entirely
                    if ($temp_item_quantity >= MMRPG_SETTINGS_ITEMS_MAXQUANTITY){
                        $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
                        $temp_item_quantity = MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
                        return true;
                    }

                    // Display the robot reward message markup
                    $event_header = $temp_item_name.' Item Drop';
                    $event_body = rpg_battle::random_positive_word().' The disabled '.$this_robot->print_robot_name().' dropped '.(preg_match('/^(a|e|i|o|u)/i', $temp_item_name) ? 'an' : 'a').' <span class="ability_name ability_type ability_type_'.$temp_item_colour.'">'.$temp_item_name.'</span>!<br />';
                    $event_body .= $target_player->print_player_name().' added the dropped item to the inventory.';
                    $event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $target_player->player_side;
                    $event_options['this_body_float'] = $target_player->player_side;
                    $event_options['this_ability'] = $temp_ability;
                    $event_options['this_ability_image'] = 'icon';
                    $event_options['event_flag_victory'] = true;
                    $event_options['console_show_this_player'] = false;
                    $event_options['console_show_this_robot'] = false;
                    $event_options['console_show_this_ability'] = true;
                    $event_options['canvas_show_this_ability'] = true;
                    $target_player->player_frame = $item_reward_key % 3 == 0 ? 'victory' : 'taunt';
                    $target_player->update_session();
                    $target_robot->robot_frame = $item_reward_key % 2 == 0 ? 'taunt' : 'base';
                    $target_robot->update_session();
                    $temp_ability->ability_frame = 'base';
                    $temp_ability->ability_frame_offset = array('x' => 220, 'y' => 0, 'z' => 10);
                    $temp_ability->update_session();
                    $this_battle->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

                    // Create and/or increment the session variable for this item increasing its quantity
                    if (empty($_SESSION['GAME']['values']['battle_items'][$temp_item_token])){ $_SESSION['GAME']['values']['battle_items'][$temp_item_token] = 0; }
                    $_SESSION['GAME']['values']['battle_items'][$temp_item_token] += 1;

                    // If this item is not on the list of key items (un-equippable), don't add it
                    $temp_key_items = array('item-screw-large', 'item-screw-small', 'item-heart', 'item-star');
                    if (!in_array($temp_item_token, $temp_key_items)){
                        // If there is room in this player's current item omega, add the new item
                        $temp_session_token = $target_player->player_token.'_this-item-omega_prototype';
                        if (!empty($_SESSION['GAME']['values'][$temp_session_token])){
                            $temp_count = count($_SESSION['GAME']['values'][$temp_session_token]);
                            if ($temp_count < 8 && !in_array($temp_item_token, $_SESSION['GAME']['values'][$temp_session_token])){
                                $_SESSION['GAME']['values'][$temp_session_token][] = $temp_item_token;
                                $target_player->player_items[] = $temp_item_token;
                                $target_player->update_session();
                            }
                        }
                    }

                    // Return true on success
                    return true;

                }
            }

            // Loop through the ability rewards for this robot if set and NOT demo mode
            if (empty($_SESSION['GAME']['DEMO']) && !empty($target_player_rewards['items']) && $this->player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID){
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'let us unlock item drops now..');  }
                $temp_items_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                $temp_success_value = $this_robot->robot_class == 'master' ? 50 : 25;
                $temp_success_value = ceil($temp_success_value * $temp_chance_multiplier);
                if ($temp_success_value > 100){ $temp_success_value = 100; }
                $temp_failure_value = 100 - $temp_success_value;
                $temp_dropping_result = $temp_success_value == 100 ? 'success' : $this_battle->weighted_chance(array('success', 'failure'), array($temp_success_value, $temp_failure_value));
                if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '..and the result of the drop ('.$temp_success_value.' / '.$temp_failure_value.') is '.$temp_dropping_result);  }
                if ($temp_dropping_result == 'success'){
                    $temp_value_total = 0;
                    $temp_count_total = 0;
                    foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $temp_value_total += $item_reward_info['chance']; $temp_count_total += 1; }
                    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$temp_count_total = '.$temp_count_total.';<br /> $temp_value_total = '.$temp_value_total.'; ');  }
                    $temp_item_tokens = array();
                    $temp_item_weights = array();
                    foreach ($target_player_rewards['items'] AS $item_reward_key => $item_reward_info){ $temp_item_tokens[] = $item_reward_info['token']; $temp_item_weights[] = ceil(($item_reward_info['chance'] / $temp_value_total) * 100); }
                    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$temp_item_tokens = '.implode(',', $temp_item_tokens).';<br /> $temp_item_weights = '.implode(',', $temp_item_weights).'; ');  }
                    $temp_random_item = $this_battle->weighted_chance($temp_item_tokens, $temp_item_weights);
                    if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, '$temp_random_item = '.$temp_random_item);  }
                    $item_index_info = rpg_ability::parse_index_info($temp_items_index[$temp_random_item]);
                    temp_player_rewards_items($this_battle, $target_player, $target_robot, $this, $item_reward_key, $item_index_info);
                }
            }

        }

        // DEBUG
        //$this->battle->events_create(false, false, 'DEBUG', 'we made it past the experience boosts');

        // If the player has replacement robots and the knocked-out one was active
        if ($this_player->counters['robots_active'] > 0){

            // Try to find at least one active POSITION robot before requiring a switch
            $has_active_positon_robot = false;
            foreach ($this_player->values['robots_active'] AS $key => $robot){
                if ($robot['robot_position'] == 'active'){ $has_active_positon_robot = true; }
            }

            // If the player does NOT have an active position robot, trigger a switch
            if (!$has_active_positon_robot){

                // If the target player is not on autopilot, require input
                if ($this_player->player_autopilot == false){
                    // Empty the action queue to allow the player switch time
                    $this_battle->actions = array();
                }
                // Otherwise, if the target player is on autopilot, automate input
                elseif ($this_player->player_autopilot == true){  // && $this_player->player_next_action != 'switch'

                    // Empty the action queue to allow the player switch time
                    $this_battle->actions = array();

                    // Remove any previous switch actions for this player
                    $backup_switch_actions = $this_battle->actions_extract(array(
                        'this_player_id' => $this_player->player_id,
                        'this_action' => 'switch'
                        ));

                    //$this_battle->events_create(false, false, 'DEBUG DEBUG', 'This is a test from inside the dead trigger ['.count($backup_switch_actions).'].');

                    // If there were any previous switches removed
                    if (!empty($backup_switch_actions)){
                        // If the target robot was faster, it should attack first
                        if ($this_robot->robot_speed > $target_robot->robot_speed){
                            // Prepend an ability action for this robot
                            $this_battle->actions_prepend(
                                $this_player,
                                $this_robot,
                                $target_player,
                                $target_robot,
                                'ability',
                                ''
                                );
                        }
                        // Otherwise, if the target was slower, if should attack second
                        else {
                            // Prepend an ability action for this robot
                            $this_battle->actions_append(
                                $this_player,
                                $this_robot,
                                $target_player,
                                $target_robot,
                                'ability',
                                ''
                                );
                        }
                    }

                    // Prepend a switch action for the target robot
                    $this_battle->actions_prepend(
                        $this_player,
                        $this_robot,
                        $target_player,
                        $target_robot,
                        'switch',
                        ''
                        );

                }

            }

        }
        // Otherwise, if the target is out of robots...
        else {

            // Trigger a battle complete action
            //$this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');

        }

        /*
        // If this robot was a mecha, remove it from view by incrementing its key
        if ($this_robot->robot_class == 'mecha'){
            if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, 'is mecha so increment key');  }
            $this_robot->robot_key += 1000;
            $this_robot->update_session();
        }
        */

        // Either way, set the hidden flag on the robot
        //if (($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1) && $this_robot->robot_position == 'bench'){
        if ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1){
            $this_robot->robot_status == 'disabled';
            $this_robot->flags['apply_disabled_state'] = true;
            if ($this_robot->robot_position == 'bench'){ $this_robot->flags['hidden'] = true; }
            $this_robot->update_session();
        }

        // -- ROBOT UNLOCKING STUFF!!! -- //

        // Check if this target winner was a HUMAN player and update the robot database counter for defeats
        if ($target_player->player_side == 'left'){
            // Add this robot to the global robot database array
            if (!isset($_SESSION['GAME']['values']['robot_database'][$this->robot_token])){ $_SESSION['GAME']['values']['robot_database'][$this->robot_token] = array('robot_token' => $this->robot_token); }
            if (!isset($_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated'])){ $_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated'] = 0; }
            $_SESSION['GAME']['values']['robot_database'][$this->robot_token]['robot_defeated']++;
        }

        // Check if this battle has any robot rewards to unlock and the winner was a HUMAN player
        if ($target_player->player_side == 'left' && !empty($this->battle->battle_rewards['robots'])){
            // DEBUG
            //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | battle_rewards_robots = '.count($this->battle->battle_rewards['robots']).'');
            foreach ($this->battle->battle_rewards['robots'] AS $temp_reward_key => $temp_reward_info){
                // DEBUG
                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | checking '.$this->robot_token.' == '.preg_replace('/\s+/', ' ', print_r($temp_reward_info, true)).'...');
                // Check if this robot was part of the rewards for this battle
                if (!mmrpg_prototype_robot_unlocked(false, $temp_reward_info['token']) && $this->robot_token == $temp_reward_info['token']){
                    // DEBUG
                    //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | '.$this->robot_token.' == '.$temp_reward_info['token'].' is a match!');
                    // Check if this robot has been attacked with any elemental moves
                    if (!empty($this->history['triggered_damage_types'])){
                        // Loop through all the damage types and check if they're not empty
                        foreach ($this->history['triggered_damage_types'] AS $key => $types){
                            if (!empty($types)){

                                // DEBUG
                                //$this->battle->events_create(false, false, 'DEBUG_'.__LINE__, $this->robot_token.' | trigger_disabled | '.$this->robot_token.' was attacked with a '.implode(', ', $types).' type ability!<br />Removing from the battle rewards!');

                                // Generate the robot removed event showing the destruction
                                /*
                                $event_header = $this->robot_name.'&#39;s Data Destroyed';
                                $event_body = $this->print_robot_name().'&#39;s battle data was damaged beyond repair!<br />';
                                $event_body .= $this->print_robot_name().' could not be unlocked for use in battle&hellip;';
                                $event_options = array();
                                $event_options['console_show_target'] = false;
                                $event_options['this_header_float'] = $this_player->player_side;
                                $event_options['this_body_float'] = $this_player->player_side;
                                $event_options['console_show_this_player'] = false;
                                $event_options['console_show_this_robot'] = true;
                                $this_robot->robot_frame = 'defeat';
                                $this_robot->update_session();
                                $this_battle->events_create($this, false, $event_header, $event_body, $event_options);
                                */

                                // Remove this robot from the battle rewards array
                                unset($this->battle->battle_rewards['robots'][$temp_reward_key]);
                                $this->battle->update_session();

                                // Break, we know all we need to
                                break;
                            }
                        }
                    }
                    // If this robot is somehow still a reward, print a message showing a good job
                    if (!empty($this->battle->battle_rewards['robots'][$temp_reward_key])){

                        // Collect this reward's information
                        $robot_reward_info = $this->battle->battle_rewards['robots'][$temp_reward_key];

                        // Collect or define the robot points and robot rewards variables
                        $this_robot_token = $robot_reward_info['token'];
                        $this_robot_level = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
                        $this_robot_experience = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
                        $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();

                        // Create the temp new robot for the player
                        $temp_index_robot = rpg_robot::get_index_info($this_robot_token);
                        $temp_index_robot['robot_id'] = MMRPG_SETTINGS_TARGET_PLAYERID * 2;
                        $temp_index_robot['robot_level'] = $this_robot_level;
                        $temp_index_robot['robot_experience'] = $this_robot_experience;
                        $temp_unlocked_robot = new rpg_robot($this_battle, $target_player, $temp_index_robot);

                        // Automatically unlock this robot for use in battle
                        $temp_unlocked_player = $mmrpg_index['players'][$target_player->player_token];
                        mmrpg_game_unlock_robot($temp_unlocked_player, $temp_index_robot, true, true);

                        // Display the robot reward message markup
                        $event_header = $temp_unlocked_robot->robot_name.' Unlocked';
                        $event_body = rpg_battle::random_positive_word().' '.$target_player->print_player_name().' unlocked new robot data!<br />';
                        $event_body .= $temp_unlocked_robot->print_robot_name().' can now be used in battle!';
                        $event_options = array();
                        $event_options['console_show_target'] = false;
                        $event_options['this_header_float'] = $target_player->player_side;
                        $event_options['this_body_float'] = $target_player->player_side;
                        $event_options['this_robot_image'] = 'mug';
                        $temp_unlocked_robot->robot_frame = 'base';
                        $temp_unlocked_robot->update_session();
                        $this_battle->events_create($temp_unlocked_robot, false, $event_header, $event_body, $event_options);

                    }

                }

            }
        }

        // Return true on success
        return true;

    }

    // Define a function for calculating required weapon energy
    public function calculate_weapon_energy($this_ability, &$energy_base = 0, &$energy_mods = 0){
        // Determine how much weapon energy this should take
        $energy_new = $this_ability->ability_energy;
        $energy_base = $energy_new;
        $energy_mods = 0;
        if ($this_ability->ability_token != 'action-noweapons'){
            if (!empty($this->robot_core) && ($this->robot_core == $this_ability->ability_type || $this->robot_core == $this_ability->ability_type2)){
                $energy_mods++;
                $energy_new = ceil($energy_new * 0.5);
            }
            if (!empty($this->robot_rewards['abilities'])){
                foreach ($this->robot_rewards['abilities'] AS $key => $info){
                    if ($info['token'] == $this_ability->ability_token){
                        $energy_mods++;
                        $energy_new = ceil($energy_new * 0.5);
                        break;
                    }
                }
            }
        } else {
            $this_ability->ability_energy = 0;
        }
        // Return the resulting weapon energy
        return $energy_new;
    }

    // Define a function for calculating required weapon energy without using objects
    static function calculate_weapon_energy_static($this_robot, $this_ability, &$energy_base = 0, &$energy_mods = 0){
        // Determine how much weapon energy this should take
        $energy_new = isset($this_ability['ability_energy']) ? $this_ability['ability_energy'] : 0;
        $energy_base = $energy_new;
        $energy_mods = 0;
        if (!isset($this_robot['robot_core'])){ $this_robot['robot_core'] = ''; }
        if (!isset($this_ability['ability_type'])){ $this_ability['ability_type'] = ''; }
        if (!isset($this_ability['ability_type2'])){ $this_ability['ability_type2'] = ''; }
        if ($this_ability['ability_token'] != 'action-noweapons'){
            if (!empty($this_robot['robot_core']) && ($this_robot['robot_core'] == $this_ability['ability_type'] || $this_robot['robot_core'] == $this_ability['ability_type2'])){
                $energy_mods++;
                $energy_new = ceil($energy_new * 0.5);
            }
            if (!empty($this_robot['robot_rewards']['abilities'])){
                foreach ($this_robot['robot_rewards']['abilities'] AS $key => $info){
                    if ($info['token'] == $this_ability['ability_token']){
                        $energy_mods++;
                        $energy_new = ceil($energy_new * 0.5);
                        break;
                    }
                }
            }
        } else {
            $this_ability['ability_energy'] = 0;
        }
        // Return the resulting weapon energy
        return $energy_new;
    }

    // Define a function for generating robot canvas variables
    public function canvas_markup($options, $player_data){
        /*
         * ROBOT CLASS FUNCTION CANVAS MARKUP
         * public function canvas_markup($options, $player_data){}
         */

        // Define the variable to hold the console robot data
        $this_data = array();
        $this_target_options = !empty($options['this_ability']->target_options) ? $options['this_ability']->target_options : array();
        $this_damage_options = !empty($options['this_ability']->damage_options) ? $options['this_ability']->damage_options : array();
        $this_recovery_options = !empty($options['this_ability']->recovery_options) ? $options['this_ability']->recovery_options : array();
        $this_results = !empty($options['this_ability']->ability_results) ? $options['this_ability']->ability_results : array();

        // Define and calculate the simpler markup and positioning variables for this robot
        $this_data['data_type'] = !empty($options['data_type']) ? $options['data_type'] : 'robot';
        $this_data['data_debug'] = !empty($options['data_debug']) ? $options['data_debug'] : '';
        $this_data['robot_id'] = $this->robot_id;
        $this_data['robot_token'] = $this->robot_token;
        $this_data['robot_id_token'] = $this->robot_id.'_'.$this->robot_token;
        $this_data['robot_key'] = !empty($this->robot_key) ? $this->robot_key : 0;
        $this_data['robot_core'] = !empty($this->robot_core) ? $this->robot_core : 'none';
        $this_data['robot_class'] = !empty($this->robot_class) ? $this->robot_class : 'master';
        $this_data['robot_stance'] = !empty($this->robot_stance) ? $this->robot_stance : 'base';
        $this_data['robot_frame'] = !empty($this->robot_frame) ? $this->robot_frame : 'base';
        $this_data['robot_frame_index'] = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
        $this_data['robot_frame_classes'] = !empty($this->robot_frame_classes) ? $this->robot_frame_classes : '';
        $this_data['robot_frame_styles'] = !empty($this->robot_frame_styles) ? $this->robot_frame_styles : '';
        $this_data['robot_detail_styles'] = !empty($this->robot_detail_styles) ? $this->robot_detail_styles : '';
        $this_data['robot_image'] = $this->robot_image;
        $this_data['robot_image_overlay'] = !empty($this->robot_image_overlay) ? $this->robot_image_overlay : array(0);
        $this_data['robot_float'] = $this->player->player_side;
        $this_data['robot_direction'] = $this->player->player_side == 'left' ? 'right' : 'left';
        $this_data['robot_status'] = $this->robot_status;
        $this_data['robot_position'] = !empty($this->robot_position) ? $this->robot_position : 'bench';
        $this_data['robot_action'] = 'scan_'.$this->robot_id.'_'.$this->robot_token;
        $this_data['robot_size'] = $this_data['robot_position'] == 'active' ? ($this->robot_image_size * 2) : $this->robot_image_size;
        $this_data['robot_size_base'] = $this->robot_image_size;
        $this_data['robot_size_path'] = ($this->robot_image_size * 2).'x'.($this->robot_image_size * 2);
        //$this_data['robot_scale'] = $this_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_data['robot_key']) / 8) * 0.5);
        //$this_data['robot_title'] = $this->robot_number.' '.$this->robot_name.' (Lv. '.$this->robot_level.')';
        $this_data['robot_title'] = $this->robot_name.' (Lv. '.$this->robot_level.')';
        $this_data['robot_title'] .= ' <br />'.(!empty($this_data['robot_core']) && $this_data['robot_core'] != 'none' ? ucfirst($this_data['robot_core']).' Core' : 'Neutral Core');
        $this_data['robot_title'] .= ' | '.ucfirst($this_data['robot_position']).' Position';

        // Calculate the canvas offset variables for this robot
        $temp_data = $this->battle->canvas_markup_offset($this_data['robot_key'], $this_data['robot_position'], $this_data['robot_size']);
        $this_data['canvas_offset_x'] = $temp_data['canvas_offset_x'];
        $this_data['canvas_offset_y'] = $temp_data['canvas_offset_y'];
        $this_data['canvas_offset_z'] = $temp_data['canvas_offset_z'];
        $this_data['canvas_offset_rotate'] = 0;
        $this_data['robot_scale'] = $temp_data['canvas_scale'];

        // Calculate the zoom properties for the robot sprite
        $zoom_size = $this->robot_image_size * 2;
        $frame_index = explode('/', MMRPG_SETTINGS_ROBOT_FRAMEINDEX);
        $this_data['robot_sprite_size'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_sprite_width'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_sprite_height'] = ceil($this_data['robot_scale'] * $zoom_size);
        $this_data['robot_file_width'] = ceil($this_data['robot_scale'] * $zoom_size * count($frame_index));
        $this_data['robot_file_height'] = ceil($this_data['robot_scale'] * $zoom_size);

        /* DEBUG
        $this_data['robot_title'] = $this->robot_name
            .' | ID '.str_pad($this->robot_id, 3, '0', STR_PAD_LEFT).''
            //.' | '.strtoupper($this->robot_position)
            .' | '.$this->robot_energy.' LE'
            .' | '.$this->robot_attack.' AT'
            .' | '.$this->robot_defense.' DF'
            .' | '.$this->robot_speed.' SP';
            */

        // If this robot is on the bench and inactive, override default sprite frames
        if ($this_data['robot_position'] == 'bench' && $this_data['robot_frame'] == 'base' && $this_data['robot_status'] != 'disabled'){
            // Define a randomly generated integer value
            $random_int = mt_rand(1, 10);
            // If the random number was one, show an attack frame
            if ($random_int == 1){ $this_data['robot_frame'] = 'taunt'; }
            // Else if the random number was two, show a defense frame
            elseif ($random_int == 2){ $this_data['robot_frame'] = 'defend'; }
            // Else if the random number was anything else, show the base frame
            else { $this_data['robot_frame'] = 'base'; }
        }

        // If the robot is defeated, move its sprite accorss the field
        if ($this_data['robot_frame'] == 'defeat'){
            //$this_data['canvas_offset_x'] -= ceil($this_data['robot_size'] * 0.10);
        }

        // Fix the robot x position if it's size if greater than 80
        //$this_data['canvas_offset_x'] -= ceil(($this_data['robot_size'] - 80) * 0.10);

        // If this robot is being damaged of is defending
        if ($this_data['robot_status'] == 'disabled' && $this_data['robot_frame'] != 'damage'){
            //$this_data['robot_frame'] = 'defeat';
            $this_data['canvas_offset_x'] -= 10;
        } elseif ($this_data['robot_frame'] == 'damage' || $this_data['robot_stance'] == 'defend'){
            if (!empty($this_results['total_strikes']) || (!empty($this_results['this_result']) && $this_results['this_result'] == 'success')){ //checkpoint
                if ($this_results['trigger_kind'] == 'damage' && !empty($this_damage_options['damage_kickback']['x'])){
                    $this_data['canvas_offset_rotate'] += ceil(($this_damage_options['damage_kickback']['x'] / 100) * 45);
                    $this_data['canvas_offset_x'] -= ceil($this_damage_options['damage_kickback']['x'] * 1.5); //isset($this_results['total_strikes']) ? $this_damage_options['damage_kickback']['x'] + ($this_damage_options['damage_kickback']['x'] * $this_results['total_strikes']) : $this_damage_options['damage_kickback']['x'];
                }
                elseif ($this_results['trigger_kind'] == 'recovery' && !empty($this_recovery_options['recovery_kickback']['x'])){
                    $this_data['canvas_offset_rotate'] += ceil(($this_recovery_options['recovery_kickback']['x'] / 100) * 50);
                    $this_data['canvas_offset_x'] -= ceil($this_recovery_options['recovery_kickback']['x'] * 1.5); //isset($this_results['total_strikes']) ? $this_recovery_options['recovery_kickback']['x'] + ($this_recovery_options['recovery_kickback']['x'] * $this_results['total_strikes']) : $this_recovery_options['recovery_kickback']['x'];
                }
                $this_data['canvas_offset_rotate'] += ceil($this_results['total_strikes'] * 10);
            }
            if (!empty($this_results['this_result']) && $this_results['this_result'] == 'success'){
                if ($this_results['trigger_kind'] == 'damage' && !empty($this_damage_options['damage_kickback']['y'])){
                    $this_data['canvas_offset_y'] += $this_damage_options['damage_kickback']['y']; //isset($this_results['total_strikes']) ? ($this_damage_options['damage_kickback']['y'] * $this_results['total_strikes']) : $this_damage_options['damage_kickback']['y'];
                }
                elseif ($this_results['trigger_kind'] == 'recovery' && !empty($this_recovery_options['recovery_kickback']['y'])){
                    $this_data['canvas_offset_y'] += $this_recovery_options['recovery_kickback']['y']; //isset($this_results['total_strikes']) ? ($this_recovery_options['recovery_kickback']['y'] * $this_results['total_strikes']) : $this_recovery_options['recovery_kickback']['y'];
                }
            }
        }

        // Either way, apply target offsets if they exist
        if (isset($options['this_ability_target']) && $options['this_ability_target'] != $this_data['robot_id_token']){
            if (!empty($this_target_options['target_kickback']['x'])
                || !empty($this_target_options['target_kickback']['y'])
                || !empty($this_target_options['target_kickback']['z'])){
                $this_data['canvas_offset_x'] += $this_target_options['target_kickback']['x'];
                $this_data['canvas_offset_y'] += $this_target_options['target_kickback']['y'];
                $this_data['canvas_offset_z'] += $this_target_options['target_kickback']['z'];
            }
        }

        // Calculate the energy bar amount and display properties
        $this_data['energy_fraction'] = $this->robot_energy.' / '.$this->robot_base_energy;
        $this_data['energy_percent'] = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        if ($this_data['energy_percent'] == 100 && $this->robot_energy < $this->robot_base_energy){ $this_data['energy_percent'] = 99; }
        // Calculate the energy bar positioning variables based on float
        if ($this_data['robot_float'] == 'left'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -3;  }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -111 + floor(111 * ($this_data['energy_percent'] / 100)) - 2;  }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -111; }
            else { $this_data['energy_x_position'] = -112; }
            if ($this_data['energy_percent'] > 0 && $this_data['energy_percent'] < 100 && $this_data['energy_x_position'] % 2 == 0){ $this_data['energy_x_position']--; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; $this_data['energy_tooltip_type'] = 'nature'; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -12; $this_data['energy_tooltip_type'] = 'electric'; }
            else { $this_data['energy_y_position'] = -24; $this_data['energy_tooltip_type'] = 'flame'; }
        }
        elseif ($this_data['robot_float'] == 'right'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -112; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -3 - floor(111 * ($this_data['energy_percent'] / 100)) + 2; }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -3; }
            else { $this_data['energy_x_position'] = -2; }
            if ($this_data['energy_percent'] > 0 && $this_data['energy_percent'] < 100 && $this_data['energy_x_position'] % 2 != 0){ $this_data['energy_x_position']--; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = -36; $this_data['energy_tooltip_type'] = 'nature'; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -48; $this_data['energy_tooltip_type'] = 'electric'; }
            else { $this_data['energy_y_position'] = -60; $this_data['energy_tooltip_type'] = 'flame'; }
        }

        // Calculate the weapons bar amount and display properties for both robots
        if (true){
            // Define the fraction and percent text for the weapons
            $this_data['weapons_fraction'] = $this->robot_weapons.' / '.$this->robot_base_weapons;
            $this_data['weapons_percent'] = floor(($this->robot_weapons / $this->robot_base_weapons) * 100);
            $this_data['weapons_percent_used'] = 100 - $this_data['weapons_percent'];
            // Calculate the energy bar positioning variables based on float
            if ($this_data['robot_float'] == 'left'){
                // Define the x and y position of the weapons bar background
                if ($this_data['weapons_percent'] == 100){ $this_data['weapons_x_position'] = 0; }
                elseif ($this_data['weapons_percent'] > 1){ $this_data['weapons_x_position'] = 0 - ceil(60 * ($this_data['weapons_percent_used'] / 100));  }
                elseif ($this_data['weapons_percent'] == 1){ $this_data['weapons_x_position'] = -54; }
                else { $this_data['weapons_x_position'] = -60; }
                //if ($this_data['weapons_percent'] > 0 && $this_data['weapons_percent'] < 100 && $this_data['weapons_x_position'] % 2 != 0){ $this_data['weapons_x_position']++; }
                $this_data['weapons_y_position'] = 0;
            }
            elseif ($this_data['robot_float'] == 'right'){
                // Define the x and y position of the weapons bar background
                if ($this_data['weapons_percent'] == 100){ $this_data['weapons_x_position'] = -61; }
                elseif ($this_data['weapons_percent'] > 1){ $this_data['weapons_x_position'] = -61 + ceil(60 * ($this_data['weapons_percent_used'] / 100));  }
                elseif ($this_data['weapons_percent'] == 1){ $this_data['weapons_x_position'] = -7; }
                else { $this_data['weapons_x_position'] = -1; }
                //if ($this_data['weapons_percent'] > 0 && $this_data['weapons_percent'] < 100 && $this_data['weapons_x_position'] % 2 != 0){ $this_data['weapons_x_position']++; }
                $this_data['weapons_y_position'] = -6;
            }

        }


        // Calculate the experience bar amount and display properties if a player robot
        if ($this_data['robot_float'] == 'left'){
            // Define the fraction and percent text for the experience
            if ($this->robot_level < 100){
                $this_data['experience_fraction'] = $this->robot_experience.' / 1000';
                $this_data['experience_percent'] = floor(($this->robot_experience / 1000) * 100);
                $this_data['experience_percent_remaining'] = 100 - $this_data['experience_percent'];
            } else {
                $this_data['experience_fraction'] = '&#8734;';
                $this_data['experience_percent'] = 100;
                $this_data['experience_percent_remaining'] = 0;
            }
            // Define the x and y position of the experience bar background
            if ($this_data['experience_percent'] == 100){ $this_data['experience_x_position'] = 0; }
            elseif ($this_data['experience_percent'] > 1){ $this_data['experience_x_position'] = 0 - ceil(60 * ($this_data['experience_percent_remaining'] / 100));  }
            elseif ($this_data['experience_percent'] == 1){ $this_data['experience_x_position'] = -54; }
            else { $this_data['experience_x_position'] = -60; }
            if ($this_data['experience_percent'] > 0 && $this_data['experience_percent'] < 100 && $this_data['experience_x_position'] % 2 != 0){ $this_data['experience_x_position']++; }
            $this_data['experience_y_position'] = 0;
        }



        // Generate the final markup for the canvas robot
        ob_start();

            // Precalculate this robot's stat for later comparrison
            $index_info = rpg_robot::get_index_info($this->robot_token);
            $reward_info = mmrpg_prototype_robot_rewards($this->player->player_token, $this->robot_token);
            $this_stats = rpg_robot::calculate_stat_values($this->robot_level, $index_info, $reward_info);

            // Define the rest of the display variables
            //$this_data['robot_file'] = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['robot_file'] = 'images/robots/'.$this_data['robot_image'].'/sprite_'.$this_data['robot_direction'].'_'.$this_data['robot_size_path'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
            $this_data['robot_markup_class'] = 'sprite ';
            //$this_data['robot_markup_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].' sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_'.$this_data['robot_frame'].' ';
            $this_data['robot_markup_class'] .= 'sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].' sprite_'.$this_data['robot_sprite_size'].'x'.$this_data['robot_sprite_size'].'_'.$this_data['robot_frame'].' ';
            $this_data['robot_markup_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
            $frame_position = is_numeric($this_data['robot_frame']) ? (int)($this_data['robot_frame']) : array_search($this_data['robot_frame'], $this_data['robot_frame_index']);
            if ($frame_position === false){ $frame_position = 0; }
            $this_data['robot_markup_class'] .= $this_data['robot_frame_classes'];
            $frame_background_offset = -1 * ceil(($this_data['robot_sprite_size'] * $frame_position));
            $this_data['robot_markup_style'] = 'background-position: '.(!empty($frame_background_offset) ? $frame_background_offset.'px' : '0').' 0; ';
            $this_data['robot_markup_style'] .= 'z-index: '.$this_data['canvas_offset_z'].'; '.$this_data['robot_float'].': '.$this_data['canvas_offset_x'].'px; bottom: '.$this_data['canvas_offset_y'].'px; ';
            if ($this_data['robot_frame'] == 'damage'){
                $temp_rotate_amount = $this_data['canvas_offset_rotate'];
                if ($this_data['robot_direction'] == 'right'){ $temp_rotate_amount = $temp_rotate_amount * -1; }
                $this_data['robot_markup_style'] .= 'transform: rotate('.$temp_rotate_amount.'deg); -webkit-transform: rotate('.$temp_rotate_amount.'deg); -moz-transform: rotate('.$temp_rotate_amount.'deg); ';
            }
            //$this_data['robot_markup_style'] .= 'background-image: url('.$this_data['robot_file'].'); ';
            $this_data['robot_markup_style'] .= 'background-image: url('.$this_data['robot_file'].'); width: '.$this_data['robot_sprite_size'].'px; height: '.$this_data['robot_sprite_size'].'px; background-size: '.$this_data['robot_file_width'].'px '.$this_data['robot_file_height'].'px; ';
            $this_data['robot_markup_style'] .= $this_data['robot_frame_styles'];
            $this_data['energy_class'] = 'energy';
            $this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';
            $this_data['weapons_class'] = 'weapons';
            $this_data['weapons_style'] = 'background-position: '.$this_data['weapons_x_position'].'px '.$this_data['weapons_y_position'].'px;';

            // Check if this robot's energy has been maxed out
            $temp_energy_maxed = $this_stats['energy']['current'] >= $this_stats['energy']['max'] ? true : false;

            if ($this_data['robot_float'] == 'left'){

                $this_data['experience_class'] = 'experience';
                $this_data['experience_style'] = 'background-position: '.$this_data['experience_x_position'].'px '.$this_data['experience_y_position'].'px;';

                //$this_data['energy_title'] = $this_data['energy_fraction'].' LE | '.$this_data['energy_percent'].'%'.($temp_energy_maxed ? ' | &#9733;' : '');
                $this_data['energy_title'] = $this_data['energy_fraction'].' LE | '.$this_data['energy_percent'].'%';
                $this_data['robot_title'] .= ' <br />'.$this_data['energy_fraction'].' LE';

                $this_data['weapons_title'] = $this_data['weapons_fraction'].' WE | '.$this_data['weapons_percent'].'%';
                $this_data['robot_title'] .= ' | '.$this_data['weapons_fraction'].' WE';

                if ($this_data['robot_class'] == 'master'){
                    $this_data['experience_title'] = $this_data['experience_fraction'].' EXP | '.$this_data['experience_percent'].'%';
                    $this_data['robot_title'] .= ' | '.$this_data['experience_fraction'].' EXP';
                } elseif ($this_data['robot_class'] == 'mecha'){
                    $temp_generation = '1st';
                    if (preg_match('/-2$/', $this_data['robot_token'])){ $temp_generation = '2nd'; }
                    elseif (preg_match('/-3$/', $this_data['robot_token'])){ $temp_generation = '3rd'; }
                    $this_data['experience_title'] = $temp_generation.' Gen';
                    $this_data['robot_title'] .= ' | '.$temp_generation.' Gen';
                }

                $this_data['robot_title'] .= ' <br />'.$this->robot_attack.' / '.$this->robot_base_attack.' AT';
                $this_data['robot_title'] .= ' | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF';
                $this_data['robot_title'] .= ' | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP';

            }
            elseif ($this_data['robot_float'] == 'right'){

                //$this_data['energy_title'] = ($temp_energy_maxed ? '&#9733; | ' : '').$this_data['energy_percent'].'% | '.$this_data['energy_fraction'].' LE';
                $this_data['energy_title'] = $this_data['energy_percent'].'% | '.$this_data['energy_fraction'].' LE';
                $this_data['robot_title'] .= ' <br />'.$this_data['energy_fraction'].' LE';

                $this_data['weapons_title'] = $this_data['weapons_percent'].'% | '.$this_data['weapons_fraction'].' WE';
                $this_data['robot_title'] .= ' | '.$this_data['weapons_fraction'].' WE';

                if ($this_data['robot_class'] == 'mecha'){
                    $temp_generation = '1st';
                    if (preg_match('/-2$/', $this_data['robot_token'])){ $temp_generation = '2nd'; }
                    elseif (preg_match('/-3$/', $this_data['robot_token'])){ $temp_generation = '3rd'; }
                    $this_data['experience_title'] = $temp_generation.' Gen';
                    $this_data['robot_title'] .= ' | '.$temp_generation.' Gen';
                }

                $this_data['robot_title'] .= ' <br />'.$this->robot_attack.' / '.$this->robot_base_attack.' AT';
                $this_data['robot_title'] .= ' | '.$this->robot_defense.' / '.$this->robot_base_defense.' DF';
                $this_data['robot_title'] .= ' | '.$this->robot_speed.' / '.$this->robot_base_speed.' SP';

            }

            $this_data['robot_title_plain'] = strip_tags(str_replace('<br />', '&#10;', $this_data['robot_title']));
            $this_data['robot_title_tooltip'] = htmlentities($this_data['robot_title'], ENT_QUOTES, 'UTF-8');

            // Display the robot's shadow sprite if allowed sprite
            global $flag_wap, $flag_ipad, $flag_iphone;
            if (!$flag_wap && !$flag_ipad && !$flag_iphone){
                $shadow_offset_z = $this_data['canvas_offset_z'] - 4;
                $shadow_scale = array(1.5, 0.25);
                $shadow_skew = $this_data['robot_direction'] == 'right' ? 30 : -30;
                $shadow_translate = array(
                    ceil($this_data['robot_sprite_width'] + ($this_data['robot_sprite_width'] * $shadow_scale[1]) + ($shadow_skew * $shadow_scale[1]) - (($this_data['robot_direction'] == 'right' ? 15 : 5) * $this_data['robot_scale'])),
                    ceil(($this_data['robot_sprite_height'] * $shadow_scale[0]) - (5 * $this_data['robot_scale'])),
                    );
                //if ($this_data['robot_size_base'] >= 80 && $this_data['robot_position'] == 'active'){ $shadow_translate[0] += ceil(10 * $this_data['robot_scale']); $shadow_translate[1] += ceil(120 * $this_data['robot_scale']); }
                $shadow_translate[0] = $shadow_translate[0] * ($this_data['robot_direction'] == 'right' ? -1 : 1);
                $shadow_styles = 'z-index: '.$shadow_offset_z.'; transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); -webkit-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); -moz-transform: scale('.$shadow_scale[0].','.$shadow_scale[1].') skew('.$shadow_skew.'deg) translate('.$shadow_translate[0].'px,'.$shadow_translate[1].'px); ';
                $shadow_token = 'shadow-'.$this->robot_class;
                if ($this->robot_class == 'mecha'){ $shadow_image_token = preg_replace('/(-2|-3)$/', '', $this_data['robot_image']); }
                elseif (strstr($this_data['robot_image'], '_')){ list($shadow_image_token) = explode('_', $this_data['robot_image']); }
                else { $shadow_image_token = $this_data['robot_image']; }
                //$shadow_image_token = $this->robot_class == 'mecha' ? preg_replace('/(-2|-3)$/', '', $this_data['robot_image']) : $this_data['robot_image'];
                echo '<div data-shadowid="'.$this_data['robot_id'].
                    '" class="'.str_replace($this_data['robot_token'], $shadow_token, $this_data['robot_markup_class']).
                    '" style="'.str_replace('robots/'.$this_data['robot_image'], 'robots_shadows/'.$shadow_image_token, $this_data['robot_markup_style']).$shadow_styles.
                    '" data-key="'.$this_data['robot_key'].
                    '" data-type="'.$this_data['data_type'].'_shadow'.
                    '" data-size="'.$this_data['robot_sprite_size'].
                    '" data-direction="'.$this_data['robot_direction'].
                    '" data-frame="'.$this_data['robot_frame'].
                    '" data-position="'.$this_data['robot_position'].
                    '" data-status="'.$this_data['robot_status'].
                    '" data-scale="'.$this_data['robot_scale'].
                    '"></div>';
            }

            // Display this robot's battle sprite
            //echo '<div data-robotid="'.$this_data['robot_id'].'" class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" title="'.$this_data['robot_title_plain'].'" data-tooltip="'.$this_data['robot_title_tooltip'].'" data-key="'.$this_data['robot_key'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['robot_sprite_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-status="'.$this_data['robot_status'].'" data-scale="'.$this_data['robot_scale'].'">'.$this_data['robot_token'].'</div>';
            echo '<div data-robotid="'.$this_data['robot_id'].'" class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" data-key="'.$this_data['robot_key'].'" data-type="'.$this_data['data_type'].'" data-size="'.$this_data['robot_sprite_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-status="'.$this_data['robot_status'].'" data-scale="'.$this_data['robot_scale'].'">'.$this_data['robot_token'].'</div>';
            //echo '<a class="'.$this_data['robot_markup_class'].'" style="'.$this_data['robot_markup_style'].'" title="'.$this_data['robot_title'].'" data-type="robot" data-size="'.$this_data['robot_size'].'" data-direction="'.$this_data['robot_direction'].'" data-frame="'.$this_data['robot_frame'].'" data-position="'.$this_data['robot_position'].'" data-action="'.$this_data['robot_action'].'" data-status="'.$this_data['robot_status'].'">'.$this_data['robot_title'].'</a>';

            // If this robot has any overlays, display them too
            if (!empty($this_data['robot_image_overlay'])){
                foreach ($this_data['robot_image_overlay'] AS $key => $overlay_token){
                    if (empty($overlay_token)){ continue; }
                    $overlay_offset_z = $this_data['canvas_offset_z'] + 2;
                    $overlay_styles = ' z-index: '.$overlay_offset_z.'; ';
                    echo '<div data-overlayid="'.$this_data['robot_id'].
                        '" class="'.str_replace($this_data['robot_token'], $overlay_token, $this_data['robot_markup_class']).
                        '" style="'.str_replace('robots/'.$this_data['robot_image'], 'robots/'.$overlay_token, $this_data['robot_markup_style']).$overlay_styles.
                        '" data-key="'.$this_data['robot_key'].
                        '" data-type="'.$this_data['data_type'].'_overlay'.
                        '" data-size="'.$this_data['robot_sprite_size'].
                        '" data-direction="'.$this_data['robot_direction'].
                        '" data-frame="'.$this_data['robot_frame'].
                        '" data-position="'.$this_data['robot_position'].
                        '" data-status="'.$this_data['robot_status'].
                        '" data-scale="'.$this_data['robot_scale'].
                        '"></div>';
                }
            }

            // Check if his player has any other active robots
            $temp_player_active_robots = false;
            foreach ($this->player->values['robots_active'] AS $info){
                if ($info['robot_position'] == 'active'){ $temp_player_active_robots = true; }
            }

            // Check if this is an active position robot
            if ($this_data['robot_position'] != 'bench' || ($temp_player_active_robots == false && $this_data['robot_frame'] == 'damage')){

                // Define the mugshot and detail variables for the GUI
                $details_data = $this_data;
                $details_data['robot_file'] = 'images/robots/'.$details_data['robot_image'].'/sprite_'.$details_data['robot_direction'].'_'.$details_data['robot_size'].'x'.$details_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $details_data['robot_details'] = '<div class="robot_name">'.$this->robot_name.'</div>';
                $details_data['robot_details'] .= '<div class="robot_level robot_type robot_type_'.($this->robot_level >= 100 ? 'electric' : 'none').'">Lv. '.$this->robot_level.'</div>';
                $details_data['robot_details'] .= '<div class="'.$details_data['energy_class'].'" style="'.$details_data['energy_style'].'" title="'.$details_data['energy_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$this_data['energy_tooltip_type'].'">'.$details_data['energy_title'].'</div>';
                $details_data['robot_details'] .= '<div class="'.$details_data['weapons_class'].'" style="'.$details_data['weapons_style'].'" title="'.$details_data['weapons_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_weapons">'.$details_data['weapons_title'].'</div>';
                if ($this_data['robot_float'] == 'left'){ $details_data['robot_details'] .= '<div class="'.$details_data['experience_class'].'" style="'.$details_data['experience_style'].'" title="'.$details_data['experience_title'].'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_experience">'.$details_data['experience_title'].'</div>'; }

                /*
                $robot_attack_markup = '<div class="robot_attack'.($this->robot_attack < 1 ? ' robot_attack_break' : ($this->robot_attack < ($this->robot_base_attack / 2) ? ' robot_attack_break_chance' : '')).'">'.str_pad($this->robot_attack, 3, '0', STR_PAD_LEFT).'</div>';
                $robot_defense_markup = '<div class="robot_defense'.($this->robot_defense < 1 ? ' robot_defense_break' : ($this->robot_defense < ($this->robot_base_defense / 2) ? ' robot_defense_break_chance' : '')).'">'.str_pad($this->robot_defense, 3, '0', STR_PAD_LEFT).'</div>';
                $robot_speed_markup = '<div class="robot_speed'.($this->robot_speed < 1 ? ' robot_speed_break' : ($this->robot_speed < ($this->robot_base_speed / 2) ? ' robot_speed_break_chance' : '')).'">'.str_pad($this->robot_speed, 3, '0', STR_PAD_LEFT).'</div>';
                */

                // Loop through and define the other stat variables and markup
                $stat_tokens = array('attack' => 'AT', 'defense' => 'DF', 'speed' => 'SP');
                foreach ($stat_tokens AS $stat => $letters){
                    $prop_stat = 'robot_'.$stat;
                    $prop_stat_base = 'robot_base_'.$stat;
                    $prop_stat_max = 'robot_max_'.$stat;
                    $prop_markup = 'robot_'.$stat.'_markup';
                    $temp_stat_break = $this->$prop_stat < 1 ? true : false;
                    $temp_stat_break_chance = $this->$prop_stat < ($this->$prop_stat_base / 2) ? true : false;
                    $temp_stat_maxed = $this_stats[$stat]['current'] >= $this_stats[$stat]['max'] ? true : false;
                    $temp_stat_percent = round(($this->$prop_stat / $this->$prop_stat_base) * 100);
                    if ($this_data['robot_float'] == 'left'){ $temp_stat_title = $this->$prop_stat.' / '.$this->$prop_stat_base.' '.$letters.' | '.$temp_stat_percent.'%'.($temp_stat_break ? ' | BREAK!' : '').($temp_stat_maxed ? ' | &#9733;' : ''); }
                    elseif ($this_data['robot_float'] == 'right'){ $temp_stat_title = ($temp_stat_maxed ? '&#9733; |' : '').($temp_stat_break ? 'BREAK! | ' : '').$temp_stat_percent.'% | '.$this->$prop_stat.' / '.$this->$prop_stat_base.' '.$letters; }
                    $$prop_markup = '<div class="robot_'.$stat.''.($temp_stat_break ? ' robot_'.$stat.'_break' : ($temp_stat_break_chance ? ' robot_'.$stat.'_break_chance' : '')).'" title="'.$temp_stat_title.'" data-tooltip-align="'.$this_data['robot_float'].'" data-tooltip-type="robot_type robot_type_'.$stat.'">'.$this->$prop_stat.'</div>';

                }

                // Add these markup variables to the details string
                if ($details_data['robot_float'] == 'left'){
                    $details_data['robot_details'] .= $robot_attack_markup;
                    $details_data['robot_details'] .= $robot_defense_markup;
                    $details_data['robot_details'] .= $robot_speed_markup;
                } else {
                    $details_data['robot_details'] .= $robot_speed_markup;
                    $details_data['robot_details'] .= $robot_defense_markup;
                    $details_data['robot_details'] .= $robot_attack_markup;
                }

                $details_data['mugshot_file'] = 'images/robots/'.$details_data['robot_image'].'/mug_'.$details_data['robot_direction'].'_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
                $details_data['mugshot_class'] = 'sprite details robot_mugshot ';
                $details_data['mugshot_class'] .= 'sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot sprite_mugshot_'.$details_data['robot_float'].' sprite_'.$details_data['robot_size_base'].'x'.$details_data['robot_size_base'].'_mugshot_'.$details_data['robot_float'].' ';
                $details_data['mugshot_class'] .= 'robot_status_'.$details_data['robot_status'].' robot_position_'.$details_data['robot_position'].' ';
                $details_data['mugshot_style'] = 'z-index: 9100; ';
                $details_data['mugshot_style'] .= 'background-image: url('.$details_data['mugshot_file'].'); ';

                // Display the robot's mugshot sprite and detail fields
                echo '<div data-detailsid="'.$this_data['robot_id'].'" class="sprite details robot_details robot_details_'.$details_data['robot_float'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').'><div class="container">'.$details_data['robot_details'].'</div></div>';
                echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.str_replace('80x80', '40x40', $details_data['mugshot_class']).' robot_mugshot_type robot_type robot_type_'.$this_data['robot_core'].'"'.(!empty($this_data['robot_detail_styles']) ? ' style="'.$this_data['robot_detail_styles'].'"' : '').' data-tooltip="'.$details_data['robot_title_tooltip'].'"><div class="sprite">&nbsp;</div></div>';
                //echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'" title="'.$details_data['robot_title_plain'].'" data-tooltip="'.$details_data['robot_title_tooltip'].'">'.$details_data['robot_token'].'</div>';
                echo '<div data-mugshotid="'.$this_data['robot_id'].'" class="'.$details_data['mugshot_class'].'" style="'.$details_data['mugshot_style'].$this_data['robot_detail_styles'].'">'.$details_data['robot_token'].'</div>';

                // Update the main data array with this markup
                $this_data['details'] = $details_data;
            }

        // Collect the generated robot markup
        $this_data['robot_markup'] = trim(ob_get_clean());

        // Return the robot canvas data
        return $this_data;

    }

    // Define a function for generating robot console variables
    public function console_markup($options, $player_data){
        /*
         * ROBOT CLASS FUNCTION CONSOLE MARKUP
         * public function console_markup($options, $player_data){}
         */

        // Define the variable to hold the console robot data
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_data = array();

        // Define and calculate the simpler markup and positioning variables for this robot
        $this_data['robot_frame'] = !empty($this->robot_frame) ? $this->robot_frame : 'base';
        $this_data['robot_key'] = !empty($this->robot_key) ? $this->robot_key : 0;
        $this_data['robot_title'] = $this->robot_name;
        $this_data['robot_token'] = $this->robot_token;
        $this_data['robot_image'] = $this->robot_image;
        $this_data['robot_float'] = $this->player->player_side;
        $this_data['robot_direction'] = $this->player->player_side == 'left' ? 'right' : 'left';
        $this_data['robot_status'] = $this->robot_status;
        $this_data['robot_position'] = !empty($this->robot_position) ? $this->robot_position : 'bench';
        $this_data['image_type'] = !empty($options['this_robot_image']) ? $options['this_robot_image'] : 'sprite';

        // Calculate the energy bar amount and display properties
        $this_data['energy_fraction'] = $this->robot_energy.' / '.$this->robot_base_energy;
        $this_data['energy_percent'] = ceil(($this->robot_energy / $this->robot_base_energy) * 100);
        // Calculate the energy bar positioning variables based on float
        if ($this_data['robot_float'] == 'left'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -82; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -119 + floor(37 * ($this_data['energy_percent'] / 100));  }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -119; }
            else { $this_data['energy_x_position'] = -120; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5;}
            else { $this_data['energy_y_position'] = -10; }
        }
        elseif ($this_data['robot_float'] == 'right'){
            // Define the x position of the energy bar background
            if ($this_data['energy_percent'] == 100){ $this_data['energy_x_position'] = -40; }
            elseif ($this_data['energy_percent'] > 1){ $this_data['energy_x_position'] = -3 - floor(37 * ($this_data['energy_percent'] / 100)); }
            elseif ($this_data['energy_percent'] == 1){ $this_data['energy_x_position'] = -3; }
            else { $this_data['energy_x_position'] = -2; }
            // Define the y position of the energy bar background
            if ($this_data['energy_percent'] > 50){ $this_data['energy_y_position'] = 0; }
            elseif ($this_data['energy_percent'] > 30){ $this_data['energy_y_position'] = -5; }
            else { $this_data['energy_y_position'] = -10; }
        }

        // Calculate the weapons bar amount and display properties for both robots
        if (true){
            // Define the fraction and percent text for the weapons
            $this_data['weapons_fraction'] = $this->robot_weapons.' / '.$this->robot_base_weapons;
            $this_data['weapons_percent'] = floor(($this->robot_weapons / $this->robot_base_weapons) * 100);
        }

        // Calculate the experience bar amount and display properties if a player robot
        if ($this_data['robot_float'] == 'left'){
            // Define the fraction and percent text for the experience
            if ($this->robot_level < 100){
                $this_data['experience_fraction'] = $this->robot_experience.' / 1000';
                $this_data['experience_percent'] = floor(($this->robot_experience / 1000) * 100);
            } else {
                $this_data['experience_fraction'] = '&#8734;';
                $this_data['experience_percent'] = 100;
            }
        }

        // Define the rest of the display variables
        $this_data['container_class'] = 'this_sprite sprite_'.$this_data['robot_float'];
        $this_data['container_style'] = '';
        //$this_data['robot_class'] = 'sprite sprite_robot_'.$this_data['robot_status'];
        $this_data['robot_class'] = 'sprite sprite_robot sprite_robot_'.$this_data['image_type'].' ';
        $this_data['robot_style'] = '';
        $this_data['robot_size'] = $this->robot_image_size;
        $this_data['robot_image'] = 'images/robots/'.$this_data['robot_image'].'/'.$this_data['image_type'].'_'.$this_data['robot_direction'].'_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].' sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_'.$this_data['robot_frame'].' ';
        $this_data['robot_class'] .= 'robot_status_'.$this_data['robot_status'].' robot_position_'.$this_data['robot_position'].' ';
        if ($this_data['image_type'] == 'mug'){ $this_data['robot_class'] .= 'sprite_'.$this_data['robot_size'].'x'.$this_data['robot_size'].'_mugshot '; }
        $this_data['robot_style'] .= 'background-image: url('.$this_data['robot_image'].'); ';
        $this_data['energy_title'] = $this_data['energy_fraction'].' LE ('.$this_data['energy_percent'].'%)';
        $this_data['robot_title'] .= ' <br />'.$this_data['energy_title'];
        $this_data['weapons_title'] = $this_data['weapons_fraction'].' WE ('.$this_data['weapons_percent'].'%)';
        $this_data['robot_title'] .= ' <br />'.$this_data['weapons_title'];
        if ($this_data['robot_float'] == 'left'){
            $this_data['experience_title'] = $this_data['experience_fraction'].' EXP ('.$this_data['experience_percent'].'%)';
            $this_data['robot_title'] .= ' <br />'.$this_data['experience_title'];
        }
        $this_data['energy_class'] = 'energy';
        $this_data['energy_style'] = 'background-position: '.$this_data['energy_x_position'].'px '.$this_data['energy_y_position'].'px;';

        // Generate the final markup for the console robot
        $this_data['robot_markup'] = '';
        $this_data['robot_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['robot_markup'] .= '<div class="'.$this_data['robot_class'].'" style="'.$this_data['robot_style'].'" title="'.$this_data['robot_title'].'">'.$this_data['robot_title'].'</div>';
        if ($this_data['image_type'] != 'mug'){ $this_data['robot_markup'] .= '<div class="'.$this_data['energy_class'].'" style="'.$this_data['energy_style'].'" title="'.$this_data['energy_title'].'">'.$this_data['energy_title'].'</div>'; }
        $this_data['robot_markup'] .= '</div>';

        // Return the robot console data
        return $this_data;
    }

    // Define a function for pulling the full robot index
    public static function get_index($filter = array()){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "get_index()");  }
        global $db;

        // If a filter was defined, parse it's values for the query
        if (!empty($filter) && is_array($filter)){
                $where_filter = array();
                $filter_ids = array();
                $filter_tokens = array();
                foreach ($filter AS $key => $value){
                        if (is_numeric($value)){ $filter_ids[] = $value; }
                        else { $filter_tokens[] = "'{$value}'"; }
                }
                if (!empty($filter_ids)){ $where_filter[] = 'robot_id IN ('.implode(', ', $filter_ids).')'; }
                if (!empty($filter_tokens)){ $where_filter[] = 'robot_token IN ('.implode(', ', $filter_tokens).')'; }
                if (!empty($where_filter)){ $where_filter = 'AND ('.implode(' OR ', $where_filter).') '; }
                else { $where_filter = ''; }
        } else {
                $where_filter = '';
        }

        // Collect the robot index from the database using any filters
        $robot_index = $db->get_array_list("SELECT *
                FROM mmrpg_index_robots
                WHERE robot_flag_complete = 1 {$where_filter}
                ;", 'robot_token');

        // Return the robot index, empty or not
        if (!empty($robot_index)){ return $robot_index; }
        else { return array(); }

    }
    // Define a public function for collecting index data from the database
    public static function get_index_info($robot_token){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "get_index_info('{$robot_token}')");  }
        global $db;
        $robot_index = rpg_robot::get_index(array($robot_token));
        if (!empty($robot_index[$robot_token])){ $robot_info = rpg_robot::parse_index_info($robot_index[$robot_token]); }
        else { $robot_info = array(); }
        return $robot_info;
    }
    // Define a public function for reformatting database data into proper arrays
    public static function parse_index_info($robot_info){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "parse_index_info(\$robot_info:{$robot_info['robot_token']})");  }

        // Return false if empty
        if (empty($robot_info)){ return false; }

        // If the information has already been parsed, return as-is
        if (!empty($robot_info['_parsed'])){ return $robot_info; }
        else { $robot_info['_parsed'] = true; }

        // Explode the weaknesses, resistances, affinities, and immunities into an array
        $temp_field_names = array('robot_field2', 'robot_weaknesses', 'robot_resistances', 'robot_affinities', 'robot_immunities');
        foreach ($temp_field_names AS $field_name){
            if (!empty($robot_info[$field_name])){ $robot_info[$field_name] = json_decode($robot_info[$field_name], true); }
            else { $robot_info[$field_name] = array(); }
        }

        // Explode the abilities into the appropriate array
        $robot_info['robot_abilities'] = !empty($robot_info['robot_abilities_compatible']) ? json_decode($robot_info['robot_abilities_compatible'], true) : array();
        unset($robot_info['robot_abilities_compatible']);

        // Explode the abilities into the appropriate array
        $robot_info['robot_rewards']['abilities'] = !empty($robot_info['robot_abilities_rewards']) ? json_decode($robot_info['robot_abilities_rewards'], true) : array();
        unset($robot_info['robot_abilities_rewards']);

        // Collect the quotes into the proper arrays
        $robot_info['robot_quotes']['battle_start'] = !empty($robot_info['robot_quotes_start']) ? $robot_info['robot_quotes_start']: '';
        $robot_info['robot_quotes']['battle_taunt'] = !empty($robot_info['robot_quotes_taunt']) ? $robot_info['robot_quotes_taunt']: '';
        $robot_info['robot_quotes']['battle_victory'] = !empty($robot_info['robot_quotes_victory']) ? $robot_info['robot_quotes_victory']: '';
        $robot_info['robot_quotes']['battle_defeat'] = !empty($robot_info['robot_quotes_defeat']) ? $robot_info['robot_quotes_defeat']: '';
        unset($robot_info['robot_quotes_start'], $robot_info['robot_quotes_taunt'], $robot_info['robot_quotes_victory'], $robot_info['robot_quotes_defeat']);

        // Return the parsed robot info
        return $robot_info;
    }

    // Define a public function for recalculating internal counters
    public function update_variables(){

        // Update parent objects first
        //$this->player->update_variables();

        // Calculate this robot's count variables
        $this->counters['abilities_total'] = count($this->robot_abilities);

        // Now collect an export array for this object
        $this_data = $this->export_array();

        // Update the parent battle variable
        $this->battle->values['robots'][$this->robot_id] = $this_data;

        // Find and update the parent's robot variable
        foreach ($this->player->player_robots AS $this_key => $this_robotinfo){
            if ($this_robotinfo['robot_id'] == $this->robot_id){
                $this->player->player_robots[$this_key] = $this_data;
                break;
            }
        }

        // Return true on success
        return true;

    }

    // Define a public, static function for resetting robot values to base
    public static function reset_variables($this_data){
        $this_data['robot_flags'] = array();
        $this_data['robot_counters'] = array();
        $this_data['robot_values'] = array();
        $this_data['robot_history'] = array();
        $this_data['robot_name'] = $this_data['robot_base_name'];
        $this_data['robot_token'] = $this_data['robot_base_token'];
        $this_data['robot_description'] = $this_data['robot_base_description'];
        $this_data['robot_energy'] = $this_data['robot_base_energy'];
        $this_data['robot_weapons'] = $this_data['robot_base_weapons'];
        $this_data['robot_attack'] = $this_data['robot_base_attack'];
        $this_data['robot_defense'] = $this_data['robot_base_defense'];
        $this_data['robot_speed'] = $this_data['robot_base_speed'];
        $this_data['robot_weaknesses'] = $this_data['robot_base_weaknesses'];
        $this_data['robot_resistances'] = $this_data['robot_base_resistances'];
        $this_data['robot_affinities'] = $this_data['robot_base_affinities'];
        $this_data['robot_immunities'] = $this_data['robot_base_immunities'];
        //$this_data['robot_abilities'] = $this_data['robot_base_abilities'];
        $this_data['robot_attachments'] = $this_data['robot_base_attachments'];
        $this_data['robot_quotes'] = $this_data['robot_base_quotes'];
        return $this_data;

    }

    // Define a public function for updating this player's session
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Request parent player object to update as well
        //$this->player->update_session();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION['ROBOTS'][$this->robot_id] = $this_data;
        $this->battle->values['robots'][$this->robot_id] = $this_data;
        //$this->player->values['robots'][$this->robot_id] = $this_data;

        // Return true on success
        return true;

    }


    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal robot fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_token' => $this->battle_token,
            'player_id' => $this->player_id,
            'player_token' => $this->player_token,
            'robot_key' => $this->robot_key,
            'robot_id' => $this->robot_id,
            'robot_number' => $this->robot_number,
            'robot_name' => $this->robot_name,
            'robot_token' => $this->robot_token,
            'robot_field' => $this->robot_field,
            'robot_class' => $this->robot_class,
            'robot_image' => $this->robot_image,
            'robot_image_size' => $this->robot_image_size,
            'robot_image_overlay' => $this->robot_image_overlay,
            'robot_core' => $this->robot_core,
            'robot_description' => $this->robot_description,
            'robot_experience' => $this->robot_experience,
            'robot_level' => $this->robot_level,
            'robot_energy' => $this->robot_energy,
            'robot_weapons' => $this->robot_weapons,
            'robot_attack' => $this->robot_attack,
            'robot_defense' => $this->robot_defense,
            'robot_speed' => $this->robot_speed,
            'robot_weaknesses' => $this->robot_weaknesses,
            'robot_resistances' => $this->robot_resistances,
            'robot_affinities' => $this->robot_affinities,
            'robot_immunities' => $this->robot_immunities,
            'robot_abilities' => $this->robot_abilities,
            'robot_attachments' => $this->robot_attachments,
            'robot_quotes' => $this->robot_quotes,
            'robot_rewards' => $this->robot_rewards,
            'robot_functions' => $this->robot_functions,
            'robot_base_name' => $this->robot_base_name,
            'robot_base_token' => $this->robot_base_token,
            'robot_base_image' => $this->robot_base_image,
            'robot_base_image_size' => $this->robot_base_image_size,
            'robot_base_image_overlay' => $this->robot_base_image_overlay,
            'robot_base_core' => $this->robot_base_core,
            'robot_base_core2' => $this->robot_base_core2,
            'robot_base_description' => $this->robot_base_description,
            'robot_base_experience' => $this->robot_base_experience,
            'robot_base_level' => $this->robot_base_level,
            'robot_base_energy' => $this->robot_base_energy,
            'robot_base_weapons' => $this->robot_base_weapons,
            'robot_base_attack' => $this->robot_base_attack,
            'robot_base_defense' => $this->robot_base_defense,
            'robot_base_speed' => $this->robot_base_speed,
            'robot_max_energy' => $this->robot_max_energy,
            'robot_max_weapons' => $this->robot_max_weapons,
            'robot_max_attack' => $this->robot_max_attack,
            'robot_max_defense' => $this->robot_max_defense,
            'robot_max_speed' => $this->robot_max_speed,
            'robot_base_weaknesses' => $this->robot_base_weaknesses,
            'robot_base_resistances' => $this->robot_base_resistances,
            'robot_base_affinities' => $this->robot_base_affinities,
            'robot_base_immunities' => $this->robot_base_immunities,
            //'robot_base_abilities' => $this->robot_base_abilities,
            'robot_base_attachments' => $this->robot_base_attachments,
            'robot_base_quotes' => $this->robot_base_quotes,
            //'robot_base_rewards' => $this->robot_base_rewards,
            'robot_status' => $this->robot_status,
            'robot_position' => $this->robot_position,
            'robot_stance' => $this->robot_stance,
            'robot_frame' => $this->robot_frame,
            //'robot_frame_index' => $this->robot_frame_index,
            'robot_frame_offset' => $this->robot_frame_offset,
            'robot_frame_classes' => $this->robot_frame_classes,
            'robot_frame_styles' => $this->robot_frame_styles,
            'robot_detail_styles' => $this->robot_detail_styles,
            'robot_original_player' => $this->robot_original_player,
            'robot_string' => $this->robot_string,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

    // Define a static function for printing out the robot's database markup
    public static function print_database_markup($robot_info, $print_options = array()){

        // Define the markup variable
        $this_markup = '';
        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $mmrpg_database_players, $mmrpg_database_robots, $mmrpg_database_abilities, $mmrpg_database_types;
        static $mmrpg_database_fields;
        if (empty($mmrpg_database_fields)){ $mmrpg_database_fields = rpg_field::get_index(); }

        // Define the print style defaults
        if (!isset($print_options['layout_style'])){ $print_options['layout_style'] = 'website'; }
        if ($print_options['layout_style'] == 'website'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = true; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = true; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = true; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = true; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = true; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'website_compact'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = true; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = false; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = true; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        } elseif ($print_options['layout_style'] == 'event'){
            if (!isset($print_options['show_basics'])){ $print_options['show_basics'] = true; }
            if (!isset($print_options['show_mugshot'])){ $print_options['show_mugshot'] = false; }
            if (!isset($print_options['show_quotes'])){ $print_options['show_quotes'] = false; }
            if (!isset($print_options['show_description'])){ $print_options['show_description'] = false; }
            if (!isset($print_options['show_sprites'])){ $print_options['show_sprites'] = false; }
            if (!isset($print_options['show_abilities'])){ $print_options['show_abilities'] = false; }
            if (!isset($print_options['show_records'])){ $print_options['show_records'] = false; }
            if (!isset($print_options['show_footer'])){ $print_options['show_footer'] = false; }
            if (!isset($print_options['show_key'])){ $print_options['show_key'] = false; }
        }

        // Collect the robot sprite dimensions
        $robot_image_size = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
        $robot_image_size_text = $robot_image_size.'x'.$robot_image_size;
        $robot_image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];

        // Collect the robot's type for background display
        $robot_header_types = 'robot_type_'.(!empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none').' ';

        // Define the sprite sheet alt and title text
        $robot_sprite_size = $robot_image_size * 2;
        $robot_sprite_size_text = $robot_sprite_size.'x'.$robot_sprite_size;
        $robot_sprite_title = $robot_info['robot_name'];
        //$robot_sprite_title = $robot_info['robot_number'].' '.$robot_info['robot_name'];
        //$robot_sprite_title .= ' Sprite Sheet | Robot Database | Mega Man RPG Prototype';

        // If this is a mecha, define it's generation for display
        $robot_info['robot_name_append'] = '';
        if (!empty($robot_info['robot_class']) && $robot_info['robot_class'] == 'mecha'){
            $robot_info['robot_generation'] = '1st';
            if (preg_match('/-2$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '2nd'; $robot_info['robot_name_append'] = ' 2'; }
            elseif (preg_match('/-3$/', $robot_info['robot_token'])){ $robot_info['robot_generation'] = '3rd'; $robot_info['robot_name_append'] = ' 3'; }
        } elseif (preg_match('/^duo/i', $robot_info['robot_token'])){

        }

        // Define the sprite frame index for robot images
        $robot_sprite_frames = array('base','taunt','victory','defeat','shoot','throw','summon','slide','defend','damage','base2');

        // Collect the field info if applicable
        $field_info_array = array();
        $temp_robot_fields = array();
        if (!empty($robot_info['robot_field']) && $robot_info['robot_field'] != 'field'){ $temp_robot_fields[] = $robot_info['robot_field']; }
        if (!empty($robot_info['robot_field2'])){ $temp_robot_fields = array_merge($temp_robot_fields, $robot_info['robot_field2']); }
        if ($temp_robot_fields){
            foreach ($temp_robot_fields AS $key => $token){
                if (!empty($mmrpg_database_fields[$token])){
                    $field_info_array[] = rpg_field::parse_index_info($mmrpg_database_fields[$token]);
                }
            }
        }

        // Start the output buffer
        ob_start();
        ?>
        <div class="database_container database_<?= $robot_info['robot_class'] == 'mecha' ? 'mecha' : 'robot' ?>_container" data-token="<?=$robot_info['robot_token']?>" style="<?= $print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important;' : '' ?>">

            <? if($print_options['layout_style'] == 'website' || $print_options['layout_style'] == 'website_compact'): ?>
                <a class="anchor" id="<?=$robot_info['robot_token']?>">&nbsp;</a>
            <? endif; ?>

            <div class="subbody event event_triple event_visible" data-token="<?=$robot_info['robot_token']?>" style="<?= ($print_options['layout_style'] == 'event' ? 'margin: 0 !important; ' : '').($print_options['layout_style'] == 'website_compact' ? 'margin-bottom: 2px !important; ' : '') ?>">

                <? if($print_options['show_mugshot']): ?>

                    <div class="this_sprite sprite_left" style="height: 40px;">
                        <? if($print_options['show_mugshot']): ?>
                            <? if($print_options['show_key'] !== false): ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>" style="font-size: 9px; line-height: 11px; text-align: center; margin-bottom: 2px; padding: 0 0 1px !important;"><?= 'No.'.($print_options['show_key'] + 1) ?></div>
                            <? endif; ?>
                            <? if ($robot_image_token != 'robot'){ ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: url(images/robots/<?= $robot_image_token ?>/mug_right_<?= $robot_image_size_text ?>.png?<?=MMRPG_CONFIG_CACHE_DATE?>); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active"><?=$robot_info['robot_name']?>'s Mugshot</div></div>
                            <? } else { ?>
                                <div class="mugshot robot_type <?= $robot_header_types ?>"><div style="background-image: none; background-color: #000000; background-color: rgba(0, 0, 0, 0.6); " class="sprite sprite_robot sprite_40x40 sprite_40x40_mug sprite_size_<?= $robot_image_size_text ?> sprite_size_<?= $robot_image_size_text ?>_mug robot_status_active robot_position_active">No Image</div></div>
                            <? } ?>
                        <? endif; ?>
                    </div>

                <? endif; ?>

                <? if($print_options['show_basics']): ?>

                    <h2 class="header header_left <?= $robot_header_types ?>" style="margin-right: 0; <?= (!$print_options['show_mugshot']) ? 'margin-left: 0;' : '' ?>">
                        <? if($print_options['layout_style'] == 'website_compact'): ?>
                            <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_token'] ?>/"><?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?></a>
                        <? else: ?>
                            <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Data
                        <? endif; ?>
                        <div class="header_core robot_type" style="border-color: rgba(0, 0, 0, 0.2) !important; background-color: rgba(0, 0, 0, 0.2) !important;"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></div>
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px; <?= (!$print_options['show_mugshot']) ? 'margin-left: 0; ' : '' ?><?= $print_options['layout_style'] == 'event' ? 'font-size: 10px; min-height: 150px; ' : '' ?>">
                        <table class="full" style="<?= $print_options['layout_style'] == 'website' ? 'margin: 5px auto 10px;' : 'margin: 5px auto -2px;' ?>">
                            <colgroup>
                                <? if($print_options['layout_style'] == 'website'): ?>
                                    <col width="48%" />
                                    <col width="1%" />
                                    <col width="48%" />
                                <? else: ?>
                                    <col width="40%" />
                                    <col width="1%" />
                                    <col width="59%" />
                                <? endif; ?>
                            </colgroup>
                            <tbody>
                                <? if($print_options['layout_style'] != 'event'): ?>
                                    <tr>
                                        <td  class="right">
                                            <label style="display: block; float: left;">Name :</label>
                                            <span class="robot_type" style="width: auto;"><?=$robot_info['robot_name']?></span>
                                            <? if (!empty($robot_info['robot_generation'])){ ?><span class="robot_type" style="width: auto;"><?=$robot_info['robot_generation']?> Gen</span><? } ?>
                                        </td>
                                        <td class="center">&nbsp;</td>
                                        <td class="right">
                                            <?
                                            // Define the source game string
                                            if ($robot_info['robot_token'] == 'mega-man' || $robot_info['robot_token'] == 'roll'){ $temp_source_string = 'Mega Man'; }
                                            elseif ($robot_info['robot_token'] == 'proto-man'){ $temp_source_string = 'Mega Man 3'; }
                                            elseif ($robot_info['robot_token'] == 'bass'){ $temp_source_string = 'Mega Man 7'; }
                                            elseif ($robot_info['robot_token'] == 'disco' || $robot_info['robot_token'] == 'rhythm'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif (preg_match('/^flutter-fly/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif (preg_match('/^beetle-borg/i', $robot_info['robot_token'])){ $temp_source_string = '<span title="Rockman &amp; Forte 2 : Challenger from the Future (JP)">Mega Man &amp; Bass 2</span>'; }
                                            elseif ($robot_info['robot_token'] == 'bond-man'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif ($robot_info['robot_token'] == 'enker'){ $temp_source_string = 'Mega Man : Dr. Wily\'s Revenge'; }
                                            elseif ($robot_info['robot_token'] == 'punk'){ $temp_source_string = 'Mega Man III'; }
                                            elseif ($robot_info['robot_token'] == 'ballade'){ $temp_source_string = 'Mega Man IV'; }
                                            elseif ($robot_info['robot_token'] == 'quint'){ $temp_source_string = 'Mega Man II'; }
                                            elseif ($robot_info['robot_token'] == 'oil-man' || $robot_info['robot_token'] == 'time-man'){ $temp_source_string = 'Mega Man Powered Up'; }
                                            elseif ($robot_info['robot_token'] == 'solo'){ $temp_source_string = 'Mega Man Star Force 3'; }
                                            elseif (preg_match('/^duo-2/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man 8'; }
                                            elseif (preg_match('/^duo/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man Power Battles'; }
                                            elseif (preg_match('/^trio/i', $robot_info['robot_token'])){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif ($robot_info['robot_token'] == 'cosmo-man' || $robot_info['robot_token'] == 'lark-man'){ $temp_source_string = 'Mega Man Battle Network 5'; }
                                            elseif ($robot_info['robot_token'] == 'laser-man'){ $temp_source_string = 'Mega Man Battle Network 4'; }
                                            elseif ($robot_info['robot_token'] == 'desert-man'){ $temp_source_string = 'Mega Man Battle Network 3'; }
                                            elseif ($robot_info['robot_token'] == 'planet-man' || $robot_info['robot_token'] == 'gate-man'){ $temp_source_string = 'Mega Man Battle Network 2'; }
                                            elseif ($robot_info['robot_token'] == 'shark-man' || $robot_info['robot_token'] == 'number-man' || $robot_info['robot_token'] == 'color-man'){ $temp_source_string = 'Mega Man Battle Network'; }
                                            elseif ($robot_info['robot_token'] == 'trill' || $robot_info['robot_token'] == 'slur'){ $temp_source_string = '<span title="Rockman.EXE Stream (JP)">Mega Man NT Warrior</span>'; }
                                            elseif ($robot_info['robot_game'] == 'MM085'){ $temp_source_string = '<span title="Rockman &amp; Forte (JP)">Mega Man &amp; Bass</span>'; }
                                            elseif ($robot_info['robot_game'] == 'MM30'){ $temp_source_string = 'Mega Man V'; }
                                            elseif ($robot_info['robot_game'] == 'MM21'){ $temp_source_string = 'Mega Man : The Wily Wars'; }
                                            elseif ($robot_info['robot_game'] == 'MM19'){ $temp_source_string = 'Mega Man RPG Prototype'; }
                                            elseif ($robot_info['robot_game'] == 'MMEXE'){ $temp_source_string = 'Mega Man EXE'; }
                                            elseif ($robot_info['robot_game'] == 'MM00' || $robot_info['robot_game'] == 'MM01'){ $temp_source_string = 'Mega Man'; }
                                            elseif (preg_match('/^MM([0-9]{2})$/', $robot_info['robot_game'])){ $temp_source_string = 'Mega Man '.ltrim(str_replace('MM', '', $robot_info['robot_game']), '0'); }
                                            elseif (!empty($robot_info['robot_game'])){ $temp_source_string = $robot_info['robot_game']; }
                                            else { $temp_source_string = '???'; }
                                            ?>
                                            <label style="display: block; float: left;">Source :</label>
                                            <span class="robot_type"><?= $temp_source_string ?></span>
                                        </td>
                                    </tr>
                                <? endif; ?>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Model :</label>
                                        <span class="robot_type"><?=$robot_info['robot_number']?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Class :</label>
                                        <span class="robot_type"><?= !empty($robot_info['robot_description']) ? $robot_info['robot_description'] : '&hellip;' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Type :</label>
                                        <? if($print_options['layout_style'] != 'event'): ?>
                                            <? if(!empty($robot_info['robot_core2'])): ?>
                                                <span class="robot_type robot_type_<?= $robot_info['robot_core'].'_'.$robot_info['robot_core2'] ?>">
                                                    <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_core'] ?>/"><?= ucfirst($robot_info['robot_core']) ?></a> /
                                                    <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_core2'] ?>/"><?= ucfirst($robot_info['robot_core2']) ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                                                </span>
                                            <? else: ?>
                                                <a href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/" class="robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></a>
                                            <? endif; ?>
                                        <? else: ?>
                                            <span class="robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? '_'.$robot_info['robot_core2'] : '') : 'none' ?>"><?= !empty($robot_info['robot_core']) ? ucwords($robot_info['robot_core'].(!empty($robot_info['robot_core2']) ? ' / '.$robot_info['robot_core2'] : '')) : 'Neutral' ?><?= $robot_info['robot_class'] == 'master' ? ' Core' : ' Type' ?></span>
                                        <? endif; ?>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td  class="right">
                                        <label style="display: block; float: left;"><?= empty($field_info_array) || count($field_info_array) == 1 ? 'Field' : 'Fields' ?> :</label>
                                        <?
                                        /*

                                        <? if($print_options['layout_style'] != 'event'): ?>

                                        <? else: ?>

                                        <? endif; ?>


                                         */

                                        // Loop through the robots fields if available
                                        if (!empty($field_info_array)){
                                            foreach ($field_info_array AS $key => $field_info){
                                                ?>
                                                    <? if($print_options['layout_style'] != 'event'): ?>
                                                        <a href="database/fields/<?= $field_info['field_token'] ?>/" class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= $key > 0 ? 'title="'.$field_info['field_name'].'"' : '' ?>><?= $key == 0 ? $field_info['field_name'] : preg_replace('/^([a-z0-9]+)\s([a-z0-9]+)$/i', '$1&hellip;', $field_info['field_name']) ?></a>
                                                    <? else: ?>
                                                        <span class="field_type field_type_<?= (!empty($field_info['field_type']) ? $field_info['field_type'] : 'none').(!empty($field_info['field_type2']) ? '_'.$field_info['field_type2'] : '') ?>" <?= $key > 0 ? 'title="'.$field_info['field_name'].'"' : '' ?>><?= $key == 0 ? $field_info['field_name'] : preg_replace('/^([a-z0-9]+)\s([a-z0-9]+)$/i', '$1&hellip;', $field_info['field_name']) ?></span>
                                                    <? endif; ?>
                                                <?
                                            }
                                        }
                                        // Otherwise, print an empty field
                                        else {
                                            ?>
                                                <span class="field_type">&hellip;</span>
                                            <?
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Energy :</label>
                                        <span class="robot_stat robot_type robot_type_energy" style="padding-left: <?= ceil($robot_info['robot_energy'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_energy'] ?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Weaknesses :</label>
                                        <?
                                        if (!empty($robot_info['robot_weaknesses'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_weakness.'/" class="robot_weakness robot_type robot_type_'.$robot_weakness.'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.$robot_weakness.'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_weakness robot_type robot_type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Attack :</label>
                                        <span class="robot_stat robot_type robot_type_attack" style="padding-left: <?= ceil($robot_info['robot_attack'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_attack'] ?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Resistances :</label>
                                        <?
                                        if (!empty($robot_info['robot_resistances'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_resistance.'/" class="robot_resistance robot_type robot_type_'.$robot_resistance.'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.$robot_resistance.'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_resistance robot_type robot_type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  class="right">
                                        <label style="display: block; float: left;">Defense :</label>
                                        <span class="robot_stat robot_type robot_type_defense" style="padding-left: <?= ceil($robot_info['robot_defense'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_defense'] ?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Affinities :</label>
                                        <?
                                        if (!empty($robot_info['robot_affinities'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_affinity.'/" class="robot_affinity robot_type robot_type_'.$robot_affinity.'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.$robot_affinity.'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_affinity robot_type robot_type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Speed :</label>
                                        <span class="robot_stat robot_type robot_type_speed" style="padding-left: <?= ceil($robot_info['robot_speed'] * ($print_options['layout_style'] == 'website' ? 1 : 0.9)) ?>px;"><?= $robot_info['robot_speed'] ?></span>
                                    </td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">
                                        <label style="display: block; float: left;">Immunities :</label>
                                        <?
                                        if (!empty($robot_info['robot_immunities'])){
                                            $temp_string = array();
                                            foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                                if ($print_options['layout_style'] != 'event'){ $temp_string[] = '<a href="database/abilities/'.$robot_immunity.'/" class="robot_immunity robot_type robot_type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</a>'; }
                                                else { $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.$robot_immunity.'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>'; }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_immunity robot_type robot_type_none">None</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <? if($print_options['layout_style'] == 'event'): ?>

                                    <?
                                    // Define the search and replace arrays for the robot quotes
                                    $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
                                    $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
                                    ?>
                                    <tr>
                                        <td colspan="3" class="center" style="font-size: 13px; padding: 5px 0; ">
                                            <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
                                        </td>
                                    </tr>

                                <? endif; ?>

                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_quotes']): ?>

                    <h2 id="quotes" class="header header_left <?= $robot_header_types ?>" style="margin-right: 0;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Quotes
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <?
                        // Define the search and replace arrays for the robot quotes
                        $temp_find = array('{this_player}', '{this_robot}', '{target_player}', '{target_robot}');
                        $temp_replace = array('Doctor', $robot_info['robot_name'], 'Doctor', 'Robot');
                        ?>
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Start Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_start']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_start']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Taunt Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_taunt']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_taunt']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Victory Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_victory']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_victory']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Defeat Quote : </label>
                                        <span class="robot_quote">&quot;<?= !empty($robot_info['robot_quotes']['battle_defeat']) ? str_replace($temp_find, $temp_replace, $robot_info['robot_quotes']['battle_defeat']) : '&hellip;' ?>&quot;</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_description'] && !empty($robot_info['robot_description2'])): ?>

                    <h2 class="header header_left <?= $robot_header_types ?>" style="margin-right: 0;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Description
                    </h2>
                    <div class="body body_left" style="margin-right: 0; margin-bottom: 5px; padding: 2px 0; min-height: 10px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="robot_description" style="text-align: left; padding: 0 4px;"><?= $robot_info['robot_description2'] ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_sprites'] && (!isset($robot_info['robot_image_sheets']) || $robot_info['robot_image_sheets'] !== 0) && $robot_image_token != 'robot' ): ?>
                    <h2 id="sprites" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Sprites
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 10px; min-height: 10px;">
                        <div style="border: 1px solid rgba(0, 0, 0, 0.20); border-radius: 0.5em; -moz-border-radius: 0.5em; -webkit-border-radius: 0.5em; background: #4d4d4d url(images/sprite-grid.gif) scroll repeat -10px -30px; overflow: hidden; padding: 10px 30px;">
                            <?
                            // Collect the number of sheets
                            $temp_sheet_number = !empty($robot_info['robot_image_sheets']) ? $robot_info['robot_image_sheets'] : 1;
                            // Loop through the different frames and print out the sprite sheets
                            for ($temp_sheet = 1; $temp_sheet <= $temp_sheet_number; $temp_sheet++){
                                foreach (array('right', 'left') AS $temp_direction){
                                    $temp_title = $robot_sprite_title.' | Mugshot Sprite '.ucfirst($temp_direction);
                                    $temp_label = 'Mugshot '.ucfirst(substr($temp_direction, 0, 1));
                                    echo '<div style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
                                        echo '<img style="margin-left: 0;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/robots/'.$robot_image_token.($temp_sheet > 1 ? '-'.$temp_sheet : '').'/mug_'.$temp_direction.'_'.$robot_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                        echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                    echo '</div>';
                                }
                            }
                            // Loop through the different frames and print out the sprite sheets
                            for ($temp_sheet = 1; $temp_sheet <= $temp_sheet_number; $temp_sheet++){
                                foreach ($robot_sprite_frames AS $this_key => $this_frame){
                                    $margin_left = ceil((0 - $this_key) * $robot_sprite_size);
                                    $frame_relative = $this_frame;
                                    //if ($temp_sheet > 1){ $frame_relative = 'frame_'.str_pad((($temp_sheet - 1) * count($robot_sprite_frames) + $this_key + 1), 2, '0', STR_PAD_LEFT); }
                                    $frame_relative_text = ucfirst(str_replace('_', ' ', $frame_relative));
                                    foreach (array('right', 'left') AS $temp_direction){
                                        $temp_title = $robot_sprite_title.' | '.$frame_relative_text.' Sprite '.ucfirst($temp_direction);
                                        $temp_label = $frame_relative_text.' '.ucfirst(substr($temp_direction, 0, 1));
                                        $image_token = !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'];
                                        if ($temp_sheet > 1){ $image_token .= '-'.$temp_sheet; }
                                        echo '<div style="padding-top: 20px; float: left; position: relative; margin: 0; box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.75); width: '.$robot_sprite_size.'px; height: '.$robot_sprite_size.'px; overflow: hidden;">';
                                            echo '<img style="margin-left: '.$margin_left.'px;" title="'.$temp_title.'" alt="'.$temp_title.'" src="images/robots/'.$image_token.'/sprite_'.$temp_direction.'_'.$robot_sprite_size_text.'.png?'.MMRPG_CONFIG_CACHE_DATE.'" />';
                                            echo '<label style="position: absolute; left: 5px; top: 0; color: #EFEFEF; font-size: 10px; text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);">'.$temp_label.'</label>';
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
                        </div>
                        <?
                        // Define the editor title based on ID
                        $temp_editor_title = 'Undefined';
                        $temp_final_divider = '<span style="color: #565656;"> | </span>';
                        if (!empty($robot_info['robot_image_editor'])){
                            $temp_break = false;
                            if ($robot_info['robot_image_editor'] == 412){ $temp_editor_title = 'Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 110){ $temp_break = true; $temp_editor_title = 'MetalMarioX100 / EliteP1</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 18){ $temp_break = true; $temp_editor_title = 'Sean Adamson / MetalMan</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 4117 && in_array($robot_info['robot_token'], array('splash-woman'))){ $temp_break = true; $temp_editor_title = 'Jonathan Backstrom / Rhythm_BCA</strong> <span style="color: #565656;"> | </span> Assembly by <strong>MegaBossMan / milansaponja'; }
                            elseif ($robot_info['robot_image_editor'] == 4117){ $temp_break = true; $temp_editor_title = 'Jonathan Backstrom / Rhythm_BCA</strong> <span style="color: #565656;"> | </span> Assembly by <strong>Adrian Marceau / Ageman20XX'; }
                            elseif ($robot_info['robot_image_editor'] == 3842){ $temp_break = true; $temp_editor_title = 'MegaBossMan / milansaponja'; }
                            if ($temp_break){ $temp_final_divider = '<br />'; }
                        }
                        $temp_is_capcom = true;
                        $temp_is_original = array('disco', 'rhythm', 'flutter-fly', 'flutter-fly-2', 'flutter-fly-3');
                        if (in_array($robot_info['robot_token'], $temp_is_original)){ $temp_is_capcom = false; }
                        if ($temp_is_capcom){
                            echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Artwork by <strong>Capcom</strong></p>'."\n";
                        } else {
                            echo '<p class="text text_editor" style="text-align: center; color: #868686; font-size: 10px; line-height: 13px; margin-top: 6px;">Sprite Editing by <strong>'.$temp_editor_title.'</strong> '.$temp_final_divider.' Original Character by <strong>Adrian Marceau</strong></p>'."\n";
                        }
                        ?>
                    </div>
                <? endif; ?>

                <? if($print_options['show_abilities']): ?>

                    <h2 id="abilities" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Abilities
                    </h2>
                    <div class="body body_full" style="margin: 0; padding: 2px 3px;">
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="right">
                                        <div class="ability_container">
                                        <?
                                        $robot_ability_class = !empty($robot_info['robot_class']) ? $robot_info['robot_class'] : 'master';
                                        $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
                                        $robot_ability_core2 = !empty($robot_info['robot_core2']) ? $robot_info['robot_core2'] : false;
                                        $robot_ability_list = !empty($robot_info['robot_abilities']) ? $robot_info['robot_abilities'] : array();
                                        $robot_ability_rewards = !empty($robot_info['robot_rewards']['abilities']) ? $robot_info['robot_rewards']['abilities'] : array();
                                        $new_ability_rewards = array();
                                        foreach ($robot_ability_rewards AS $this_info){
                                            $new_ability_rewards[$this_info['token']] = $this_info;
                                        }
                                        $robot_copy_program = $robot_ability_core == 'copy' || $robot_ability_core2 == 'copy' ? true : false;
                                        //if ($robot_copy_program){ $robot_ability_list = $temp_all_ability_tokens; }
                                        $robot_ability_core_list = array();
                                        if ((!empty($robot_ability_core) || !empty($robot_ability_core2))
                                            && $robot_ability_class != 'mecha'){ // only robot masters can core match abilities
                                            foreach ($mmrpg_database_abilities AS $token => $info){
                                                if (
                                                    (!empty($info['ability_type']) && ($robot_copy_program || $info['ability_type'] == $robot_ability_core || $info['ability_type'] == $robot_ability_core2)) ||
                                                    (!empty($info['ability_type2']) && ($info['ability_type2'] == $robot_ability_core || $info['ability_type2'] == $robot_ability_core2))
                                                    ){
                                                    $robot_ability_list[] = $info['ability_token'];
                                                    $robot_ability_core_list[] = $info['ability_token'];
                                                }
                                            }
                                        }
                                        foreach ($robot_ability_list AS $this_token){
                                            if ($this_token == '*'){ continue; }
                                            if (!isset($new_ability_rewards[$this_token])){
                                                if (in_array($this_token, $robot_ability_core_list)){ $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }
                                                else { $new_ability_rewards[$this_token] = array('level' => 'Player', 'token' => $this_token); }

                                            }
                                        }
                                        $robot_ability_rewards = $new_ability_rewards;

                                        //die('<pre>'.print_r($robot_ability_rewards, true).'</pre>');

                                        if (!empty($robot_ability_rewards)){
                                            $temp_string = array();
                                            $ability_key = 0;
                                            $ability_method_key = 0;
                                            $ability_method = '';
                                            foreach ($robot_ability_rewards AS $this_info){
                                                $this_level = $this_info['level'];
                                                $this_ability = $mmrpg_database_abilities[$this_info['token']];
                                                $this_ability_token = $this_ability['ability_token'];
                                                $this_ability_name = $this_ability['ability_name'];
                                                $this_ability_class = !empty($this_ability['ability_class']) ? $this_ability['ability_class'] : 'master';
                                                $this_ability_image = !empty($this_ability['ability_image']) ? $this_ability['ability_image']: $this_ability['ability_token'];
                                                $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                                $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                                                if (!empty($this_ability_type) && !empty($mmrpg_index['types'][$this_ability_type])){ $this_ability_type = $mmrpg_index['types'][$this_ability_type]['type_name'].' Type'; }
                                                else { $this_ability_type = ''; }
                                                if (!empty($this_ability_type2) && !empty($mmrpg_index['types'][$this_ability_type2])){ $this_ability_type = str_replace('Type', '/ '.$mmrpg_index['types'][$this_ability_type2]['type_name'], $this_ability_type); }
                                                $this_ability_damage = !empty($this_ability['ability_damage']) ? $this_ability['ability_damage'] : 0;
                                                $this_ability_damage2 = !empty($this_ability['ability_damage2']) ? $this_ability['ability_damage2'] : 0;
                                                $this_ability_damage_percent = !empty($this_ability['ability_damage_percent']) ? true : false;
                                                $this_ability_damage2_percent = !empty($this_ability['ability_damage2_percent']) ? true : false;
                                                if ($this_ability_damage_percent && $this_ability_damage > 100){ $this_ability_damage = 100; }
                                                if ($this_ability_damage2_percent && $this_ability_damage2 > 100){ $this_ability_damage2 = 100; }
                                                $this_ability_recovery = !empty($this_ability['ability_recovery']) ? $this_ability['ability_recovery'] : 0;
                                                $this_ability_recovery2 = !empty($this_ability['ability_recovery2']) ? $this_ability['ability_recovery2'] : 0;
                                                $this_ability_recovery_percent = !empty($this_ability['ability_recovery_percent']) ? true : false;
                                                $this_ability_recovery2_percent = !empty($this_ability['ability_recovery2_percent']) ? true : false;
                                                if ($this_ability_recovery_percent && $this_ability_recovery > 100){ $this_ability_recovery = 100; }
                                                if ($this_ability_recovery2_percent && $this_ability_recovery2 > 100){ $this_ability_recovery2 = 100; }
                                                $this_ability_accuracy = !empty($this_ability['ability_accuracy']) ? $this_ability['ability_accuracy'] : 0;
                                                $this_ability_description = !empty($this_ability['ability_description']) ? $this_ability['ability_description'] : '';
                                                $this_ability_description = str_replace('{DAMAGE}', $this_ability_damage, $this_ability_description);
                                                $this_ability_description = str_replace('{RECOVERY}', $this_ability_recovery, $this_ability_description);
                                                $this_ability_description = str_replace('{DAMAGE2}', $this_ability_damage2, $this_ability_description);
                                                $this_ability_description = str_replace('{RECOVERY2}', $this_ability_recovery2, $this_ability_description);
                                                //$this_ability_title_plain = $this_ability_name;
                                                //if (!empty($this_ability_type)){ $this_ability_title_plain .= ' | '.$this_ability_type; }
                                                //if (!empty($this_ability_damage)){ $this_ability_title_plain .= ' | '.$this_ability_damage.' Damage'; }
                                                //if (!empty($this_ability_recovery)){ $this_ability_title_plain .= ' | '.$this_ability_recovery.' Recovery'; }
                                                //if (!empty($this_ability_accuracy)){ $this_ability_title_plain .= ' | '.$this_ability_accuracy.'% Accuracy'; }
                                                //if (!empty($this_ability_description)){ $this_ability_title_plain .= ' | '.$this_ability_description; }
                                                $this_ability_title_plain = rpg_ability::print_editor_title_markup($robot_info, $this_ability);
                                                $this_ability_method = 'level';
                                                $this_ability_method_text = 'Level Up';
                                                $this_ability_title_html = '<strong class="name">'.$this_ability_name.'</strong>';
                                                if (is_numeric($this_level)){
                                                    if ($this_level > 1){ $this_ability_title_html .= '<span class="level">Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).'</span>'; }
                                                    else { $this_ability_title_html .= '<span class="level">Start</span>'; }
                                                } else {
                                                    $this_ability_method = 'player';
                                                    $this_ability_method_text = 'Player Only';
                                                    if (!in_array($this_ability_token, $robot_info['robot_abilities'])){
                                                        $this_ability_method = 'core';
                                                        $this_ability_method_text = 'Core Match';
                                                    }
                                                    $this_ability_title_html .= '<span class="level">&nbsp;</span>';
                                                }
                                                if (!empty($this_ability_type)){ $this_ability_title_html .= '<span class="type">'.$this_ability_type.'</span>'; }
                                                if (!empty($this_ability_damage)){ $this_ability_title_html .= '<span class="damage">'.$this_ability_damage.(!empty($this_ability_damage_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'D' : 'Damage').'</span>'; }
                                                if (!empty($this_ability_recovery)){ $this_ability_title_html .= '<span class="recovery">'.$this_ability_recovery.(!empty($this_ability_recovery_percent) ? '%' : '').' '.($this_ability_damage && $this_ability_recovery ? 'R' : 'Recovery').'</span>'; }
                                                if (!empty($this_ability_accuracy)){ $this_ability_title_html .= '<span class="accuracy">'.$this_ability_accuracy.'% Accuracy</span>'; }
                                                $this_ability_sprite_path = 'images/abilities/'.$this_ability_image.'/icon_left_40x40.png';
                                                if (!file_exists(MMRPG_CONFIG_ROOTDIR.$this_ability_sprite_path)){ $this_ability_image = 'ability'; $this_ability_sprite_path = 'images/abilities/ability/icon_left_40x40.png'; }
                                                $this_ability_sprite_html = '<span class="icon"><img src="'.$this_ability_sprite_path.'?'.MMRPG_CONFIG_CACHE_DATE.'" alt="'.$this_ability_name.' Icon" /></span>';
                                                $this_ability_title_html = '<span class="label">'.$this_ability_title_html.'</span>';
                                                //$this_ability_title_html = (is_numeric($this_level) && $this_level > 1 ? 'Lv '.str_pad($this_level, 2, '0', STR_PAD_LEFT).' : ' : $this_level.' : ').$this_ability_title_html;
                                                // Show the ability method separator if necessary
                                                if ($ability_method != $this_ability_method){
                                                    $temp_separator = '<div class="ability_separator">'.$this_ability_method_text.'</div>';
                                                    $temp_string[] = $temp_separator;
                                                    $ability_method = $this_ability_method;
                                                    $ability_method_key++;
                                                    // Print out the disclaimer if a copy-core robot
                                                    if ($this_ability_method != 'level' && $robot_copy_program){
                                                        $temp_string[] = '<div class="" style="margin: 10px auto; text-align: center; color: #767676; font-size: 11px;">Copy Core robots can equip <em>any</em> '.($this_ability_method == 'player' ? 'player' : 'type').' ability!</div>';
                                                    }
                                                }
                                                // If this is a copy core robot, don't bother showing EVERY core-match ability
                                                if ($this_ability_method != 'level' && $robot_copy_program){ continue; }
                                                // Only show if this ability is greater than level 0 OR it's not copy core (?)
                                                elseif ($this_level >= 0 || !$robot_copy_program){
                                                    $temp_element = $this_ability_class != 'mecha' ? 'a' : 'span';
                                                    $temp_markup = '<'.$temp_element.' '.($this_ability_class != 'mecha' ? 'href="'.MMRPG_CONFIG_ROOTURL.'database/abilities/'.$this_ability['ability_token'].'/"' : '').' class="ability_name ability_class_'.$this_ability_class.' ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'" title="'.$this_ability_title_plain.'" style="'.($this_ability_image == 'ability' ? 'opacity: 0.3; ' : '').'">';
                                                    $temp_markup .= '<span class="chrome">'.$this_ability_sprite_html.$this_ability_title_html.'</span>';
                                                    $temp_markup .= '</'.$temp_element.'>';
                                                    $temp_string[] = $temp_markup;
                                                    $ability_key++;
                                                    continue;
                                                }
                                            }
                                            echo implode(' ', $temp_string);
                                        } else {
                                            echo '<span class="robot_ability robot_type_none"><span class="chrome">None</span></span>';
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_records']): ?>

                    <h2 id="records" class="header header_full <?= $robot_header_types ?>" style="margin: 10px 0 0; text-align: left;">
                        <?= $robot_info['robot_name'].$robot_info['robot_name_append'] ?>&#39;s Records
                    </h2>
                    <div class="body body_full" style="margin: 0 auto 5px; padding: 2px 0; min-height: 10px;">
                        <?
                        // Collect the database records for this robot
                        global $db;
                        $temp_robot_records = array('robot_encountered' => 0, 'robot_defeated' => 0, 'robot_unlocked' => 0, 'robot_summoned' => 0, 'robot_scanned' => 0);
                        //$temp_robot_records['player_count'] = $db->get_value("SELECT COUNT(board_id) AS player_count  FROM mmrpg_leaderboard WHERE board_robots LIKE '%[".$robot_info['robot_token'].":%' AND board_points > 0", 'player_count');
                        $temp_player_query = "SELECT
                            mmrpg_saves.user_id,
                            mmrpg_saves.save_values_robot_database,
                            mmrpg_leaderboard.board_points
                            FROM mmrpg_saves
                            LEFT JOIN mmrpg_leaderboard ON mmrpg_leaderboard.user_id = mmrpg_saves.user_id
                            WHERE mmrpg_saves.save_values_robot_database LIKE '%\"{$robot_info['robot_token']}\"%' AND mmrpg_leaderboard.board_points > 0;";
                        $temp_player_list = $db->get_array_list($temp_player_query);
                        if (!empty($temp_player_list)){
                            foreach ($temp_player_list AS $temp_data){
                                $temp_values = !empty($temp_data['save_values_robot_database']) ? json_decode($temp_data['save_values_robot_database'], true) : array();
                                $temp_entry = !empty($temp_values[$robot_info['robot_token']]) ? $temp_values[$robot_info['robot_token']] : array();
                                foreach ($temp_robot_records AS $temp_record => $temp_count){
                                    if (!empty($temp_entry[$temp_record])){ $temp_robot_records[$temp_record] += $temp_entry[$temp_record]; }
                                }
                            }
                        }
                        $temp_values = array();
                        //echo '<pre>'.print_r($temp_robot_records, true).'</pre>';
                        ?>
                        <table class="full" style="margin: 5px auto 10px;">
                            <colgroup>
                                <col width="100%" />
                            </colgroup>
                            <tbody>
                                <? if($robot_info['robot_class'] == 'master'): ?>
                                    <tr>
                                        <td class="right">
                                            <label style="display: block; float: left;">Unlocked By : </label>
                                            <span class="robot_quote"><?= $temp_robot_records['robot_unlocked'] == 1 ? '1 Player' : number_format($temp_robot_records['robot_unlocked'], 0, '.', ',').' Players' ?></span>
                                        </td>
                                    </tr>
                                <? endif; ?>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Encountered : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_encountered'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_encountered'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Summoned : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_summoned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_summoned'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Defeated : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_defeated'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_defeated'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="right">
                                        <label style="display: block; float: left;">Scanned : </label>
                                        <span class="robot_quote"><?= $temp_robot_records['robot_scanned'] == 1 ? '1 Time' : number_format($temp_robot_records['robot_scanned'], 0, '.', ',').' Times' ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <? endif; ?>

                <? if($print_options['show_footer'] && $print_options['layout_style'] == 'website'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_token'] ?>/" rel="permalink">+ Permalink</a>

                <? elseif($print_options['show_footer'] && $print_options['layout_style'] == 'website_compact'): ?>

                    <a class="link link_top" data-href="#top" rel="nofollow">^ Top</a>
                    <a class="link link_permalink permalink" href="database/<?= $robot_info['robot_class'] == 'mecha' ? 'mechas' : 'robots' ?>/<?= $robot_info['robot_token'] ?>/" rel="permalink">+ View More</a>

                <? endif; ?>
            </div>
        </div>
        <?
        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;

    }

    // Define a static function for printing out the robot's editor markup
    public static function print_editor_markup($player_info, $robot_info, $mmrpg_database_abilities = array()){
        // Define the global variables
        global $mmrpg_index, $this_current_uri, $this_current_url, $db;
        global $allowed_edit_players, $allowed_edit_robots, $allowed_edit_abilities;
        global $allowed_edit_data_count, $allowed_edit_player_count, $allowed_edit_robot_count, $first_robot_token, $global_allow_editing;
        global $key_counter, $player_counter, $player_rewards, $player_ability_rewards, $player_robot_favourites, $player_robot_database, $temp_robot_totals, $player_options_markup;
        global $mmrpg_database_abilities;
        $session_token = mmrpg_game_token();

        // If either fo empty, return error
        if (empty($player_info)){ return 'error:player-empty'; }
        if (empty($robot_info)){ return 'error:robot-empty'; }

        // Collect the approriate database indexes
        if (empty($mmrpg_database_abilities)){ $mmrpg_database_abilities = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token'); }

        // Define the quick-access variables for later use
        $player_token = $player_info['player_token'];
        $robot_token = $robot_info['robot_token'];
        if (!isset($first_robot_token)){ $first_robot_token = $robot_token; }

        // Start the output buffer
        ob_start();

            // Check how many robots this player has and see if they should be able to transfer
            $counter_player_robots = !empty($player_info['player_robots']) ? count($player_info['player_robots']) : false;
            $counter_player_missions = mmrpg_prototype_battles_complete($player_info['player_token']);
            $allow_player_selector = $player_counter > 1 && $counter_player_missions > 0 ? true : false;

            // If this player has fewer robots than any other player
            //$temp_flag_most_robots = true;
            foreach ($temp_robot_totals AS $temp_player => $temp_total){
                //if ($temp_player == $player_token){ continue; }
                //elseif ($temp_total > $counter_player_robots){ $allow_player_selector = false; }
            }

            // Update the robot key to the current counter
            $robot_key = $key_counter;
            // Make a backup of the player selector
            $allow_player_selector_backup = $allow_player_selector;
            // Collect or define the image size
            $robot_info['robot_image_size'] = !empty($robot_info['robot_image_size']) ? $robot_info['robot_image_size'] : 40;
            $robot_image_offset = $robot_info['robot_image_size'] > 40 ? ceil(($robot_info['robot_image_size'] - 40) * 0.5) : 0;
            $robot_image_size_text = $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'];
            $robot_image_offset_top = -1 * $robot_image_offset;
            // Collect the robot level and experience
            $robot_info['robot_level'] = mmrpg_prototype_robot_level($player_info['player_token'], $robot_info['robot_token']);
            $robot_info['robot_experience'] = mmrpg_prototype_robot_experience($player_info['player_token'], $robot_info['robot_token']);
            // Collect the rewards for this robot
            $robot_rewards = mmrpg_prototype_robot_rewards($player_token, $robot_token);
            // Collect the settings for this robot
            $robot_settings = mmrpg_prototype_robot_settings($player_token, $robot_token);
            // Collect the database for this robot
            $robot_database = !empty($player_robot_database[$robot_token]) ? $player_robot_database[$robot_token] : array();
            // Collect the stat details for this robot
            $robot_stats = rpg_robot::calculate_stat_values($robot_info['robot_level'], $robot_info, $robot_rewards, true);
            // Collect the robot ability core if it exists
            $robot_ability_core = !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : false;
            // Check if this robot has the copy shot ability
            $robot_flag_copycore = $robot_ability_core == 'copy' ? true : false;

            // Loop through and update this robot's stats with calculated values
            $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
            foreach ($stat_tokens As $stat_token){
                // Update this robot's stat with the calculated current totals
                $robot_info['robot_'.$stat_token] = $robot_stats[$stat_token]['current'];
                $robot_info['robot_'.$stat_token.'_base'] = $robot_stats[$stat_token]['current_noboost'];
                $robot_info['robot_'.$stat_token.'_rewards'] = $robot_stats[$stat_token]['bonus'];
                if (!empty($player_info['player_'.$stat_token])){
                    $robot_stats[$stat_token]['player'] = ceil($robot_info['robot_'.$stat_token] * ($player_info['player_'.$stat_token] / 100));
                    $robot_info['robot_'.$stat_token.'_player'] = $robot_stats[$stat_token]['player'];
                    $robot_info['robot_'.$stat_token] += $robot_stats[$stat_token]['player'];
                }
            }

            // Define a temp function for printing out robot stat blocks
            $print_robot_stat_function = function($stat_token) use($robot_info, $robot_stats, $player_info){

                $level_max = $robot_stats['level'] >= 100 ? true : false;
                $is_maxed = $robot_stats[$stat_token]['bonus'] >= $robot_stats[$stat_token]['bonus_max'] ? true : false;

                if ($stat_token == 'energy' || $stat_token == 'weapons'){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
                elseif ($level_max && $is_maxed){ echo '<span class="robot_stat robot_type_'.$stat_token.'"> '; }
                else { echo '<span class="robot_stat"> '; }

                    if ($stat_token != 'energy' && $stat_token != 'weapons'){
                        echo $is_maxed ? ($level_max ? '<span>&#9733;</span> ' : '<span>&bull;</span> ') : '';
                        echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;">';
                            $base_text = 'Base '.ucfirst($stat_token).' <br /> <span style="font-size: 90%">'.number_format($robot_stats[$stat_token]['base'], 0, '.', ',').' <span style="font-size: 90%">@</span>  Lv.'.$robot_stats['level'].' = '.number_format($robot_stats[$stat_token]['current_noboost'], 0, '.', ',').'</span>';
                            echo '<span data-tooltip="'.htmlentities($base_text, ENT_QUOTES, 'UTF-8', true).'" data-tooltip-type="robot_type robot_type_none">'.$robot_stats[$stat_token]['current_noboost'].'</span> ';
                            if (!empty($robot_stats[$stat_token]['bonus'])){
                                $robot_bonus_text = 'Robot Bonuses <br /> <span style="font-size: 90%">'.number_format($robot_stats[$stat_token]['bonus'], 0, '.', ',').' / '.number_format($robot_stats[$stat_token]['bonus_max'], 0, '.', ',').' Max</span>';
                                echo '+ <span data-tooltip="'.htmlentities($robot_bonus_text, ENT_QUOTES, 'UTF-8', true).'" class="statboost_robot" data-tooltip-type="robot_stat robot_type_shield">'.$robot_stats[$stat_token]['bonus'].'</span> ';
                            }
                            if (!empty($robot_stats[$stat_token]['player'])){
                                $player_bonus_text = 'Player Bonuses <br /> <span style="font-size: 90%">'.number_format(($robot_stats[$stat_token]['current']), 0, '.', ',').' x '.$player_info['player_'.$stat_token].'% = '.number_format($robot_stats[$stat_token]['player'], 0, '.', ',').'</span>';
                                echo '+ <span data-tooltip="'.htmlentities($player_bonus_text, ENT_QUOTES, 'UTF-8', true).'" class="statboost_player_'.$player_info['player_token'].'" data-tooltip-type="robot_stat robot_type_'.$stat_token.'">'.$robot_stats[$stat_token]['player'].'</span> ';
                            }
                        echo ' = </span>';
                        echo preg_replace('/^(0+)/', '<span style="color: rgba(255, 255, 255, 0.05); text-shadow: 0 0 0 transparent; ">$1</span>', str_pad($robot_info['robot_'.$stat_token], 4, '0', STR_PAD_LEFT));
                    } else {
                        echo $robot_info['robot_'.$stat_token];
                    }

                    if ($stat_token == 'energy'){ echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> LE</span>'; }
                    elseif ($stat_token == 'weapons'){ echo '<span style="font-weight: normal; font-size: 9px; position: relative; bottom: 1px;"> WE</span>'; }

                echo '</span>'."\n";
                };

            // Collect this robot's ability rewards and add them to the dropdown
            $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
            $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
            foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }

            // Collect the summon count from the session if it exists
            $robot_info['robot_summoned'] = !empty($robot_database['robot_summoned']) ? $robot_database['robot_summoned'] : 0;

            // Collect the alt images if there are any that are unlocked
            $robot_alt_count = 1 + (!empty($robot_info['robot_image_alts']) ? count($robot_info['robot_image_alts']) : 0);
            $robot_alt_options = array();
            if (!empty($robot_info['robot_image_alts'])){
                foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                    if ($robot_info['robot_summoned'] < $alt_info['summons']){ continue; }
                    $robot_alt_options[] = $alt_info['token'];
                }
            }

            // Collect the current unlock image token for this robot
            $robot_image_unlock_current = 'base';
            if (!empty($robot_settings['robot_image']) && strstr($robot_settings['robot_image'], '_')){
                list($token, $robot_image_unlock_current) = explode('_', $robot_settings['robot_image']);
            }

            // Define the offsets for the image tokens based on count
            $token_first_offset = 2;
            $token_other_offset = 6;
            if ($robot_alt_count == 1){ $token_first_offset = 17; }
            elseif ($robot_alt_count == 3){ $token_first_offset = 10; }

            // Loop through and generate the robot image display token markup
            $robot_image_unlock_tokens = '';
            $temp_total_alts_count = 0;
            for ($i = 0; $i < 6; $i++){
                $temp_enabled = true;
                $temp_active = false;
                if ($i + 1 > $robot_alt_count){ break; }
                if ($i > 0 && !isset($robot_alt_options[$i - 1])){ $temp_enabled = false; }
                if ($temp_enabled && $i == 0 && $robot_image_unlock_current == 'base'){ $temp_active = true; }
                elseif ($temp_enabled && $i >= 1 && $robot_image_unlock_current == $robot_alt_options[$i - 1]){ $temp_active = true; }
                $robot_image_unlock_tokens .= '<span class="token token_'.($temp_enabled ? 'enabled' : 'disabled').' '.($temp_active ? 'token_active' : '').'" style="left: '.($token_first_offset + ($i * $token_other_offset)).'px;">&bull;</span>';
                $temp_total_alts_count += 1;
            }
            $temp_unlocked_alts_count = count($robot_alt_options) + 1;
            $temp_image_alt_title = '';
            if ($temp_total_alts_count > 1){
                $temp_image_alt_title = '<strong>'.$temp_unlocked_alts_count.' / '.$temp_total_alts_count.' Outfits Unlocked</strong><br />';
                //$temp_image_alt_title .= '<span style="font-size: 90%;">';
                    $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$robot_info['robot_name'].'</span><br />';
                    foreach ($robot_info['robot_image_alts'] AS $alt_key => $alt_info){
                        if ($robot_info['robot_summoned'] >= $alt_info['summons']){
                            $temp_image_alt_title .= '&#8226; <span style="font-size: 90%;">'.$alt_info['name'].'</span><br />';
                        } else {
                            $temp_image_alt_title .= '&#9702; <span style="font-size: 90%;">???</span><br />';
                        }
                    }
                //$temp_image_alt_title .= '</span>';
                $temp_image_alt_title = htmlentities($temp_image_alt_title, ENT_QUOTES, 'UTF-8', true);
            }

            // Define whether or not this robot has coreswap enabled
            $temp_allow_coreswap = $robot_info['robot_level'] >= 100 ? true : false;

            //echo $robot_info['robot_token'].' robot_image_unlock_current = '.$robot_image_unlock_current.' | robot_alt_options = '.implode(',',array_keys($robot_alt_options)).'<br />';

            ?>
            <div class="event event_double event_<?= $robot_key == $first_robot_token ? 'visible' : 'hidden' ?>" data-token="<?=$player_info['player_token'].'_'.$robot_info['robot_token']?>">

                <div class="this_sprite sprite_left event_robot_mugshot" style="">
                    <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                    <div class="sprite_wrapper robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="width: 33px;">
                        <div class="sprite_wrapper robot_type robot_type_empty" style="position: absolute; width: 27px; height: 34px; left: 2px; top: 2px;"></div>
                        <div style="left: <?= $temp_offset ?>; bottom: <?= $temp_offset ?>; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/mug_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_mug robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_robot_images" style="">
                    <?php if($global_allow_editing && !empty($robot_alt_options)): ?>
                        <a class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </a>
                    <?php else: ?>
                        <span class="robot_image_alts" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" data-alt-index="base<?= !empty($robot_alt_options) ? ','.implode(',', $robot_alt_options) : '' ?>" data-alt-current="<?= $robot_image_unlock_current ?>" data-tooltip="<?= $temp_image_alt_title ?>">
                            <?php $temp_offset = $robot_info['robot_image_size'] == 80 ? '-20px' : '0'; ?>
                            <span class="sprite_wrapper" style="">
                                <?= $robot_image_unlock_tokens ?>
                                <div style="left: <?= $temp_offset ?>; bottom: 0; background-image: url(images/robots/<?= !empty($robot_info['robot_image']) ? $robot_info['robot_image'] : $robot_info['robot_token'] ?>/sprite_right_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); " class="sprite sprite_robot sprite_robot_sprite sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?> sprite_<?= $robot_info['robot_image_size'].'x'.$robot_info['robot_image_size'] ?>_base robot_status_active robot_position_active"><?= $robot_info['robot_name']?></div>
                            </span>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="this_sprite sprite_left event_robot_summons" style="">
                    <div class="robot_summons">
                        <span class="summons_count"><?= $robot_info['robot_summoned'] ?></span>
                        <span class="summons_label"><?= $robot_info['robot_summoned'] == 1 ? 'Summon' : 'Summons' ?></span>
                    </div>
                </div>

                <div class="this_sprite sprite_left event_robot_favourite" style="" >
                    <?php if($global_allow_editing): ?>
                        <a class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>" data-player="<?= $player_token ?>" data-robot="<?= $robot_token ?>" title="Toggle Favourite?">&hearts;</a>
                    <?php else: ?>
                        <span class="robot_favourite <?= in_array($robot_token, $player_robot_favourites) ? 'robot_favourite_active ' : '' ?>">&hearts;</span>
                    <?php endif; ?>
                </div>

                <div class="header header_left robot_type robot_type_<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>" style="margin-right: 0;">
                    <span class="title robot_type"><?= $robot_info['robot_name']?></span>
                    <span class="core robot_type">
                        <span class="wrap"><span class="sprite sprite_40x40 sprite_40x40_00" style="background-image: url(images/abilities/item-core-<?= !empty($robot_info['robot_core']) ? $robot_info['robot_core'] : 'none' ?>/icon_left_40x40.png);"></span></span>
                        <span class="text"><?= !empty($robot_info['robot_core']) ? ucfirst($robot_info['robot_core']) : 'Neutral' ?> Core</span>
                    </span>
                </div>

                <div class="body body_left" style="margin-right: 0; padding: 2px 3px; height: auto;">
                    <table class="full" style="margin-bottom: 5px;">
                        <colgroup>
                            <col width="64%" />
                            <col width="1%" />
                            <col width="35%" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Name :</label>
                                    <span class="robot_name robot_type robot_type_none"><?=$robot_info['robot_name']?></span>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Level :</label>
                                    <? if($robot_info['robot_level'] >= 100): ?>
                                        <a data-tooltip-align="center" data-tooltip="<?= htmlentities(('Congratulations! '.$robot_info['robot_name'].' has reached Level 100!<br /> <span style="font-size: 90%;">Stat bonuses will now be awarded immediately when this robot lands the finishing blow on a target! Try to max out your other stats!</span>'), ENT_QUOTES, 'UTF-8') ?>" class="robot_stat robot_type_electric"><span>&#9733;</span> <?= $robot_info['robot_level'] ?></a>
                                    <? else: ?>
                                        <span class="robot_stat robot_level_reset robot_type_<?= !empty($robot_rewards['flags']['reached_max_level']) ? 'electric' : 'none' ?>"><?= !empty($robot_rewards['flags']['reached_max_level']) ? '<span>&#9733;</span>' : '' ?> <?= $robot_info['robot_level'] ?></span>
                                    <? endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="player_select_block right">
                                    <?
                                    $player_style = '';
                                    $robot_info['original_player'] = !empty($robot_info['original_player']) ? $robot_info['original_player'] : $player_info['player_token'];
                                    if ($player_info['player_token'] != $robot_info['original_player']){
                                        if ($counter_player_robots > 1){ $allow_player_selector = true; }
                                    }
                                    ?>
                                    <? if($robot_info['original_player'] != $player_info['player_token']): ?>
                                        <label title="<?= 'Transferred from Dr. '.ucfirst(str_replace('dr-', '', $robot_info['original_player'])) ?>"  class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                                    <? else: ?>
                                        <label class="original_player original_player_<?= $robot_info['original_player'] ?>" data-tooltip-type="player_type player_type_<?= str_replace('dr-', '', $robot_info['original_player']) ?>" style="display: block; float: left; <?= $player_style ?>"><span class="current_player current_player_<?= $player_info['player_token'] ?>">Player</span> :</label>
                                    <? endif; ?>

                                    <?if($global_allow_editing && $allow_player_selector):?>
                                        <a title="Transfer Robot?" class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>"><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?><span class="arrow">&#8711;</span></label><select class="player_name" <?= !$allow_player_selector ? 'disabled="disabled"' : '' ?> data-player="<?=$player_info['player_token']?>" data-robot="<?=$robot_info['robot_token']?>"><?= str_replace('value="'.$player_info['player_token'].'"', 'value="'.$player_info['player_token'].'" selected="selected"', $player_options_markup) ?></select></a>
                                    <?elseif(!$global_allow_editing && $allow_player_selector):?>
                                        <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="cursor: default; "><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>); cursor: default; "><?=$player_info['player_name']?></label></a>
                                    <?else:?>
                                        <a class="player_name player_type player_type_<?= str_replace('dr-', '', $player_info['player_token']) ?>" style="opacity: 0.5; filter: alpha(opacity=50); cursor: default;"><label style="background-image: url(images/players/<?=$player_info['player_token']?>/mug_left_40x40.png?<?= MMRPG_CONFIG_CACHE_DATE ?>);"><?=$player_info['player_name']?></label><select class="player_name" disabled="disabled" data-player="<?=$player_info['player_token']?>" data-robot="<?=$robot_info['robot_token']?>"><?= str_replace('value="'.$player_info['player_token'].'"', 'value="'.$player_info['player_token'].'" selected="selected"', $player_options_markup) ?></select></a>
                                    <?endif;?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td  class="right">
                                    <label style="display: block; float: left;">Exp :</label>
                                    <? if($robot_info['robot_level'] >= 100): ?>
                                        <span class="robot_stat robot_type_experience" title="Max Experience!"><span>&#8734;</span> / 1000</span>
                                    <? else: ?>
                                        <span class="robot_stat"><?= $robot_info['robot_experience'] ?> / 1000</span>
                                    <? endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Weaknesses :</label>
                                    <?
                                    if (!empty($robot_info['robot_weaknesses'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_weaknesses'] AS $robot_weakness){
                                            $temp_string[] = '<span class="robot_weakness robot_type robot_type_'.(!empty($robot_weakness) ? $robot_weakness : 'none').'">'.$mmrpg_index['types'][$robot_weakness]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_weakness">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_energy']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Energy :</label>
                                    <?
                                    // Print out the energy stat breakdown
                                    $print_robot_stat_function('energy');
                                    $print_robot_stat_function('weapons');
                                    ?>
                                </td>

                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Resistances :</label>
                                    <?
                                    if (!empty($robot_info['robot_resistances'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_resistances'] AS $robot_resistance){
                                            $temp_string[] = '<span class="robot_resistance robot_type robot_type_'.(!empty($robot_resistance) ? $robot_resistance : 'none').'">'.$mmrpg_index['types'][$robot_resistance]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_resistance">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_attack']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Attack :</label>
                                    <?
                                    // Print out the attack stat breakdown
                                    $print_robot_stat_function('attack');
                                    ?>
                            </tr>
                            <tr>
                                <td  class="right">
                                    <label style="display: block; float: left;">Affinities :</label>
                                    <?
                                    if (!empty($robot_info['robot_affinities'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_affinities'] AS $robot_affinity){
                                            $temp_string[] = '<span class="robot_affinity robot_type robot_type_'.(!empty($robot_affinity) ? $robot_affinity : 'none').'">'.$mmrpg_index['types'][$robot_affinity]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_affinity">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_defense']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Defense :</label>
                                    <?
                                    // Print out the defense stat breakdown
                                    $print_robot_stat_function('defense');
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="right">
                                    <label style="display: block; float: left;">Immunities :</label>
                                    <?
                                    if (!empty($robot_info['robot_immunities'])){
                                        $temp_string = array();
                                        foreach ($robot_info['robot_immunities'] AS $robot_immunity){
                                            $temp_string[] = '<span class="robot_immunity robot_type robot_type_'.(!empty($robot_immunity) ? $robot_immunity : 'none').'">'.$mmrpg_index['types'][$robot_immunity]['type_name'].'</span>';
                                        }
                                        echo implode(' ', $temp_string);
                                    } else {
                                        echo '<span class="robot_immunity">None</span>';
                                    }
                                    ?>
                                </td>
                                <td class="center">&nbsp;</td>
                                <td class="right">
                                    <label class="<?= !empty($player_info['player_speed']) ? 'statboost_player_'.$player_info['player_token'] : '' ?>" style="display: block; float: left;">Speed :</label>
                                    <?
                                    // Print out the speed stat breakdown
                                    $print_robot_stat_function('speed');
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="full">
                        <colgroup>
                            <col width="100%" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <td class="right" style="padding-top: 4px;">
                                    <label style="display: block; float: left; font-size: 12px;">Abilities :</label>
                                    <div class="ability_container" style="height: auto;">
                                    <?

                                    // Define the array to hold ALL the reward option markup
                                    $ability_rewards_options = '';

                                    // Sort the ability index based on ability number
                                    uasort($player_ability_rewards, array('rpg_player', 'abilities_sort_for_editor'));

                                    // Dont' bother generating option dropdowns if editing is disabled
                                    if ($global_allow_editing){

                                        $player_ability_rewards_options = array();
                                        foreach ($player_ability_rewards AS $temp_ability_key => $temp_ability_info){
                                            if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                            $temp_token = $temp_ability_info['ability_token'];
                                            $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                            $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                            if (!empty($temp_option_markup)){ $player_ability_rewards_options[] = $temp_option_markup; }
                                        }
                                        $player_ability_rewards_options = '<optgroup label="Player Abilities">'.implode('', $player_ability_rewards_options).'</optgroup>';
                                        $ability_rewards_options .= $player_ability_rewards_options;

                                        // Collect this robot's ability rewards and add them to the dropdown
                                        $robot_ability_rewards = !empty($robot_rewards['robot_abilities']) ? $robot_rewards['robot_abilities'] : array();
                                        $robot_ability_settings = !empty($robot_settings['robot_abilities']) ? $robot_settings['robot_abilities'] : array();
                                        foreach ($robot_ability_settings AS $token => $info){ if (empty($robot_ability_rewards[$token])){ $robot_ability_rewards[$token] = $info; } }
                                        if (!empty($robot_ability_rewards)){ sort($robot_ability_rewards); }
                                        $robot_ability_rewards_options = array();
                                        foreach ($robot_ability_rewards AS $temp_ability_info){
                                            if (empty($temp_ability_info['ability_token']) || !isset($mmrpg_database_abilities[$temp_ability_info['ability_token']])){ continue; }
                                            $temp_token = $temp_ability_info['ability_token'];
                                            $temp_ability_info = rpg_ability::parse_index_info($mmrpg_database_abilities[$temp_token]);
                                            $temp_option_markup = rpg_ability::print_editor_option_markup($robot_info, $temp_ability_info);
                                            if (!empty($temp_option_markup)){ $robot_ability_rewards_options[] = $temp_option_markup; }
                                        }
                                        $robot_ability_rewards_options = '<optgroup label="Robot Abilities">'.implode('', $robot_ability_rewards_options).'</optgroup>';
                                        $ability_rewards_options .= $robot_ability_rewards_options;

                                        // Add an option at the bottom to remove the ability
                                        $ability_rewards_options .= '<optgroup label="Ability Actions">';
                                        $ability_rewards_options .= '<option value="" title="">- Remove Ability -</option>';
                                        $ability_rewards_options .= '</optgroup>';

                                    }

                                    // Loop through the robot's current abilities and list them one by one
                                    $empty_ability_counter = 0;
                                    if (!empty($robot_info['robot_abilities'])){
                                        $temp_string = array();
                                        $temp_inputs = array();
                                        $ability_key = 0;

                                        // DEBUG
                                        //echo 'robot-ability:';
                                        foreach ($robot_info['robot_abilities'] AS $robot_ability){
                                            if (empty($robot_ability['ability_token'])){ continue; }
                                            elseif ($robot_ability['ability_token'] == '*'){ continue; }
                                            elseif ($robot_ability['ability_token'] == 'ability'){ continue; }
                                            elseif (!isset($mmrpg_database_abilities[$robot_ability['ability_token']])){ continue; }
                                            elseif ($ability_key > 7){ continue; }
                                            $this_ability = rpg_ability::parse_index_info($mmrpg_database_abilities[$robot_ability['ability_token']]);
                                            if (empty($this_ability)){ continue; }
                                            $this_ability_token = $this_ability['ability_token'];
                                            $this_ability_name = $this_ability['ability_name'];
                                            $this_ability_type = !empty($this_ability['ability_type']) ? $this_ability['ability_type'] : false;
                                            $this_ability_type2 = !empty($this_ability['ability_type2']) ? $this_ability['ability_type2'] : false;
                                            if (!empty($this_ability_type) && !empty($mmrpg_index['types'][$this_ability_type])){
                                                $this_ability_type = $mmrpg_index['types'][$this_ability_type]['type_name'].' Type';
                                                if (!empty($this_ability_type2) && !empty($mmrpg_index['types'][$this_ability_type2])){
                                                    $this_ability_type = str_replace(' Type', ' / '.$mmrpg_index['types'][$this_ability_type2]['type_name'].' Type', $this_ability_type);
                                                }
                                            } else {
                                                $this_ability_type = '';
                                            }
                                            $this_ability_energy = isset($this_ability['ability_energy']) ? $this_ability['ability_energy'] : 4;
                                            $this_ability_damage = !empty($this_ability['ability_damage']) ? $this_ability['ability_damage'] : 0;
                                            $this_ability_damage2 = !empty($this_ability['ability_damage2']) ? $this_ability['ability_damage2'] : 0;
                                            $this_ability_damage_percent = !empty($this_ability['ability_damage_percent']) ? true : false;
                                            $this_ability_damage2_percent = !empty($this_ability['ability_damage2_percent']) ? true : false;
                                            if ($this_ability_damage_percent && $this_ability_damage > 100){ $this_ability_damage = 100; }
                                            if ($this_ability_damage2_percent && $this_ability_damage2 > 100){ $this_ability_damage2 = 100; }
                                            $this_ability_recovery = !empty($this_ability['ability_recovery']) ? $this_ability['ability_recovery'] : 0;
                                            $this_ability_recovery2 = !empty($this_ability['ability_recovery2']) ? $this_ability['ability_recovery2'] : 0;
                                            $this_ability_recovery_percent = !empty($this_ability['ability_recovery_percent']) ? true : false;
                                            $this_ability_recovery2_percent = !empty($this_ability['ability_recovery2_percent']) ? true : false;
                                            if ($this_ability_recovery_percent && $this_ability_recovery > 100){ $this_ability_recovery = 100; }
                                            if ($this_ability_recovery2_percent && $this_ability_recovery2 > 100){ $this_ability_recovery2 = 100; }
                                            $this_ability_accuracy = !empty($this_ability['ability_accuracy']) ? $this_ability['ability_accuracy'] : 0;
                                            $this_ability_description = !empty($this_ability['ability_description']) ? $this_ability['ability_description'] : '';
                                            $this_ability_description = str_replace('{DAMAGE}', $this_ability_damage, $this_ability_description);
                                            $this_ability_description = str_replace('{RECOVERY}', $this_ability_recovery, $this_ability_description);
                                            $this_ability_description = str_replace('{DAMAGE2}', $this_ability_damage2, $this_ability_description);
                                            $this_ability_description = str_replace('{RECOVERY2}', $this_ability_recovery2, $this_ability_description);
                                            $this_ability_title = rpg_ability::print_editor_title_markup($robot_info, $this_ability);
                                            $this_ability_title_plain = strip_tags(str_replace('<br />', '&#10;', $this_ability_title));
                                            $this_ability_title_tooltip = htmlentities($this_ability_title, ENT_QUOTES, 'UTF-8');
                                            $this_ability_title_html = str_replace(' ', '&nbsp;', $this_ability_name);
                                            $temp_select_options = str_replace('value="'.$this_ability_token.'"', 'value="'.$this_ability_token.'" selected="selected" disabled="disabled"', $ability_rewards_options);
                                            $this_ability_title_html = '<label style="background-image: url(images/abilities/'.$this_ability_token.'/icon_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_ability_title_html.'</label>';
                                            if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'">'.$temp_select_options.'</select>'; }
                                            $temp_string[] = '<a class="ability_name ability_type ability_type_'.(!empty($this_ability['ability_type']) ? $this_ability['ability_type'] : 'none').(!empty($this_ability['ability_type2']) ? '_'.$this_ability['ability_type2'] : '').'" style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="'.$this_ability_token.'" title="'.$this_ability_title_plain.'" data-tooltip="'.$this_ability_title_tooltip.'">'.$this_ability_title_html.'</a>';
                                            $ability_key++;
                                        }

                                        if ($ability_key <= 7){
                                            for ($ability_key; $ability_key <= 7; $ability_key++){
                                                $empty_ability_counter++;
                                                if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                                else { $empty_ability_disable = false; }
                                                $temp_select_options = str_replace('value=""', 'value="" selected="selected" disabled="disabled"', $ability_rewards_options);
                                                $this_ability_title_html = '<label>-</label>';
                                                if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                                $temp_string[] = '<a class="ability_name " style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="" title="" data-tooltip="">'.$this_ability_title_html.'</a>';
                                            }
                                        }


                                    } else {

                                        for ($ability_key = 0; $ability_key <= 7; $ability_key++){
                                            $empty_ability_counter++;
                                            if ($empty_ability_counter >= 2){ $empty_ability_disable = true; }
                                            else { $empty_ability_disable = false; }
                                            $temp_select_options = str_replace('value=""', 'value="" selected="selected"', $ability_rewards_options);
                                            $this_ability_title_html = '<label>-</label>';
                                            if ($global_allow_editing){ $this_ability_title_html .= '<select class="ability_name" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" '.($empty_ability_disable ? 'disabled="disabled" ' : '').'>'.$temp_select_options.'</select>'; }
                                            $temp_string[] = '<a class="ability_name " style="'.(($ability_key + 1) % 4 == 0 ? 'margin-right: 0; ' : '').($empty_ability_disable ? 'opacity:0.25; ' : '').(!$global_allow_editing ? 'cursor: default; ' : '').'" data-key="'.$ability_key.'" data-player="'.$player_info['player_token'].'" data-robot="'.$robot_info['robot_token'].'" data-ability="">'.$this_ability_title_html.'</a>';
                                        }

                                    }

                                    echo !empty($temp_string) ? implode(' ', $temp_string) : '';
                                    echo !empty($temp_inputs) ? implode(' ', $temp_inputs) : '';

                                    ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?
            $key_counter++;

            // Return the backup of the player selector
            $allow_player_selector = $allow_player_selector_backup;

        // Collect the outbut buffer contents
        $this_markup = trim(ob_get_clean());

        // Return the generated markup
        return $this_markup;
    }

    // Define a function for calculating robot stat details
    public static function calculate_stat_values($level, $base_stats, $bonus_stats = array(), $limit = false){
        // Define the four basic stat tokens
        $stat_tokens = array('energy', 'weapons', 'attack', 'defense', 'speed');
        // Define the robot stats array to return
        $robot_stats = array();
        // Collect the robot's current level
        $robot_stats['level'] = $level;
        $robot_stats['level_max'] = 100;
        // Loop through each stat and calculate values
        foreach ($stat_tokens AS $key => $stat){
            $robot_stats[$stat]['base'] = $base_stats['robot_'.$stat];
            if ($stat != 'weapons'){
                $robot_stats[$stat]['base_max'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level_max']);
                $robot_stats[$stat]['bonus'] = isset($bonus_stats['robot_'.$stat]) ? $bonus_stats['robot_'.$stat] : 0;
                $robot_stats[$stat]['bonus_max'] = $stat != 'energy' ? round($robot_stats[$stat]['base_max'] * MMRPG_SETTINGS_STATS_BONUS_MAX) : 0;
                if ($limit && $robot_stats[$stat]['bonus'] > $robot_stats[$stat]['bonus_max']){ $robot_stats[$stat]['bonus'] = $robot_stats[$stat]['bonus_max']; }
                $robot_stats[$stat]['current'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $robot_stats['level']) + $robot_stats[$stat]['bonus'];
                $robot_stats[$stat]['current_noboost'] = self::calculate_level_boosted_stat($robot_stats[$stat]['base'], $level);
                $robot_stats[$stat]['max'] = $robot_stats[$stat]['base_max'] + $robot_stats[$stat]['bonus_max'];
                if ($robot_stats[$stat]['current'] > $robot_stats[$stat]['max']){
                    $robot_stats[$stat]['over'] = $robot_stats[$stat]['current'] - $robot_stats[$stat]['max'];
                }
            } else {
                $robot_stats[$stat]['base_max'] = $robot_stats[$stat]['base'];
                $robot_stats[$stat]['bonus'] = 0;
                $robot_stats[$stat]['bonus_max'] = 0;
                $robot_stats[$stat]['current'] = $robot_stats[$stat]['base'];
                $robot_stats[$stat]['current_noboost'] = $robot_stats[$stat]['base'];
                $robot_stats[$stat]['max'] = $robot_stats[$stat]['base'];

            }
        }
        return $robot_stats;
    }

    // Define a function for calculating a robot stat level boost
    public static function calculate_level_boosted_stat($base, $level){
        $stat_boost = round( $base + ($base * 0.05 * ($level - 1)) );
        return $stat_boost;
    }


}
?>