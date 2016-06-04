<?
// Define a class for the battles
class rpg_battle {

    // Define global class variables
    public $index;
    public $flags;
    public $counters;
    public $values;
    public $events;
    public $actions;
    public $history;

    // Define the constructor class
    public function rpg_battle(){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

        // Collect any provided arguments
        $args = func_get_args();

        // Collect current battle data from the function if available
        $this_battleinfo = isset($args[0]) ? $args[0] : array('battle_id' => 0, 'battle_token' => 'battle');

        // Now load the battle data from the session or index
        $this->battle_load($this_battleinfo);

        // Return true on success
        return true;

    }

    // Define a public function for updating index info
    public static function update_index_info($battle_token, $battle_info){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "update_index_info('{$battle_token}', \$battle_info)");  }
        global $db;

        // If the internal index has not been created yet, load it into memory
        if (!isset($db->INDEX['BATTLES'])){ rpg_battle::load_battle_index(); }

        // Update and/or overwrite the current info in the index
        $db->INDEX['BATTLES'][$battle_token] = json_encode($battle_info);
        // Update the data in the session as well with provided
        $_SESSION['GAME']['values']['battle_index'][$battle_token] = json_encode($battle_info);

        // Return true on success
        return true;

    }

    // Define a public function requesting a battle index entry
    public static function get_index_info($battle_token){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "get_index_info('{$battle_token}')");  }
        global $db;

        // If the internal index has not been created yet, load it into memory
        if (!isset($db->INDEX['BATTLES'])){ rpg_battle::load_battle_index(); }

        // If the requested index is not empty, return the entry
        if (!empty($db->INDEX['BATTLES'][$battle_token])){
            // Decode the info and return the array
            $battle_info = json_decode($db->INDEX['BATTLES'][$battle_token], true);
            //die('$battle_info = <pre>'.print_r($battle_info, true).'</pre>');
            return $battle_info;
        }
        // Otherwise if the battle index doesn't exist at all
        else {
            // Return false on failure
            return array();
        }

    }

    // Define a function for loading the battle index cache file
    public static function load_battle_index(){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__, "load_battle_index()");  }
        global $db;
        // Create the index as an empty array
        $db->INDEX['BATTLES'] = array();
        // Default the battles index to an empty array
        $mmrpg_battles_index = array();
        // If caching is turned OFF, or a cache has not been created
        if (!MMRPG_CONFIG_CACHE_INDEXES || !file_exists(MMRPG_CONFIG_BATTLES_CACHE_PATH)){
            // Start indexing the battle data files
            $battles_cache_markup = rpg_battle::index_battle_data();
            // Implode the markup into a single string and enclose in PHP tags
            $battles_cache_markup = implode('', $battles_cache_markup);
            $battles_cache_markup = "<?\n".$battles_cache_markup."\n?>";
            // Write the index to a cache file, if caching is enabled
            $battles_cache_file = @fopen(MMRPG_CONFIG_BATTLES_CACHE_PATH, 'w');
            if (!empty($battles_cache_file)){
                @fwrite($battles_cache_file, $battles_cache_markup);
                @fclose($battles_cache_file);
            }
        }
        // Include the cache file so it can be evaluated
        require_once(MMRPG_CONFIG_BATTLES_CACHE_PATH);
        // Return false if we got nothing from the index
        if (empty($mmrpg_battles_index)){ return false; }
        // Loop through the battles and index them after serializing
        foreach ($mmrpg_battles_index AS $token => $array){ $db->INDEX['BATTLES'][$token] = json_encode($array); }
        // Additionally, include any dynamic session-based battles
        if (!empty($_SESSION['GAME']['values']['battle_index'])){
            // The session-based battles exist, so merge them with the index
            $db->INDEX['BATTLES'] = array_merge($db->INDEX['BATTLES'], $_SESSION['GAME']['values']['battle_index']);
        }
        // Return true on success
        return true;
    }

    // Define the function used for scanning the battle directory
    public static function index_battle_data($this_path = ''){

        // Default the battles markup index to an empty array
        $battles_cache_markup = array();

        // Open the type data directory for scanning
        $data_battles  = opendir(MMRPG_CONFIG_BATTLES_INDEX_PATH.$this_path);

        //echo 'Scanning '.MMRPG_CONFIG_BATTLES_INDEX_PATH.$this_path.'<br />';

        // Loop through all the files in the directory
        while (false !== ($filename = readdir($data_battles))) {

            // If this is a directory, initiate a recusive scan
            if (is_dir(MMRPG_CONFIG_BATTLES_INDEX_PATH.$this_path.$filename.'/') && $filename != '.' && $filename != '..'){
                // Collect the markup from the recursive scan
                $append_cache_markup = rpg_battle::index_battle_data($this_path.$filename.'/');
                // If markup was found, append if to the main container
                if (!empty($append_cache_markup)){ $battles_cache_markup = array_merge($battles_cache_markup, $append_cache_markup); }
            }
            // Else, ensure the file matches the naming format
            elseif ($filename != '_index.php' && preg_match('#^[-_a-z0-9]+\.php$#i', $filename)){
                // Collect the battle token from the filename
                $this_battle_token = preg_replace('#^([-_a-z0-9]+)\.php$#i', '$1', $filename);
                if (!empty($this_path)){ $this_battle_token = trim(str_replace('/', '-', $this_path), '-').'-'.$this_battle_token; }

                //echo '+ Adding battle token '.$this_battle_token.'...<br />';

                // Read the file into memory as a string and crop slice out the imporant part
                $this_battle_markup = trim(file_get_contents(MMRPG_CONFIG_BATTLES_INDEX_PATH.$this_path.$filename));
                $this_battle_markup = explode("\n", $this_battle_markup);
                $this_battle_markup = array_slice($this_battle_markup, 1, -1);
                // Replace the first line with the appropriate index key
                $this_battle_markup[1] = preg_replace('#\$battle = array\(#i', "\$mmrpg_battles_index['{$this_battle_token}'] = array(\n  'battle_token' => '{$this_battle_token}', 'battle_functions' => 'battles/{$this_path}{$filename}',", $this_battle_markup[1]);
                // Implode the markup into a single string
                $this_battle_markup = implode("\n", $this_battle_markup);
                // Copy this battle's data to the markup cache
                $battles_cache_markup[] = $this_battle_markup;
            }

        }

        // Close the battle data directory
        closedir($data_battles);

        // Return the generated cache markup
        return $battles_cache_markup;

    }

    // Define a public function for manually loading data
    public function battle_load($this_battleinfo){
        if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

        // Pull in the mmrpg index
        global $mmrpg_index;

        // Collect current battle data from the session if available
        $this_battleinfo_backup = $this_battleinfo;
        if (isset($_SESSION['BATTLES'][$this_battleinfo['battle_id']])){
            $this_battleinfo = $_SESSION['BATTLES'][$this_battleinfo['battle_id']];
        }
        // Otherwise, collect battle data from the index
        else {
            //die(print_r($this_battleinfo, true));
            $this_battleinfo = rpg_battle::get_index_info($this_battleinfo['battle_token']);
        }
        $this_battleinfo = array_replace($this_battleinfo, $this_battleinfo_backup);

        // Define the internal ability values using the provided array
        $this->flags = isset($this_battleinfo['flags']) ? $this_battleinfo['flags'] : array();
        $this->counters = isset($this_battleinfo['counters']) ? $this_battleinfo['counters'] : array();
        $this->values = isset($this_battleinfo['values']) ? $this_battleinfo['values'] : array();
        $this->history = isset($this_battleinfo['history']) ? $this_battleinfo['history'] : array();
        $this->events = isset($this_battleinfo['events']) ? $this_battleinfo['events'] : array();
        $this->battle_id = isset($this_battleinfo['battle_id']) ? $this_battleinfo['battle_id'] : 0;
        $this->battle_name = isset($this_battleinfo['battle_name']) ? $this_battleinfo['battle_name'] : 'Default';
        $this->battle_token = isset($this_battleinfo['battle_token']) ? $this_battleinfo['battle_token'] : 'default';
        $this->battle_description = isset($this_battleinfo['battle_description']) ? $this_battleinfo['battle_description'] : '';
        $this->battle_turns = isset($this_battleinfo['battle_turns']) ? $this_battleinfo['battle_turns'] : 1;
        $this->battle_counts = isset($this_battleinfo['battle_counts']) ? $this_battleinfo['battle_counts'] : true;
        $this->battle_status = isset($this_battleinfo['battle_status']) ? $this_battleinfo['battle_status'] : 'active';
        $this->battle_result = isset($this_battleinfo['battle_result']) ? $this_battleinfo['battle_result'] : 'pending';
        $this->battle_robot_limit = isset($this_battleinfo['battle_robot_limit']) ? $this_battleinfo['battle_robot_limit'] : 1;
        $this->battle_field_base = isset($this_battleinfo['battle_field_base']) ? $this_battleinfo['battle_field_base'] : array();
        $this->battle_target_player = isset($this_battleinfo['battle_target_player']) ? $this_battleinfo['battle_target_player'] : array();
        $this->battle_rewards = isset($this_battleinfo['battle_rewards']) ? $this_battleinfo['battle_rewards'] : array();
        $this->battle_points = isset($this_battleinfo['battle_points']) ? $this_battleinfo['battle_points'] : 0;
        $this->battle_level = isset($this_battleinfo['battle_level']) ? $this_battleinfo['battle_level'] : 0;

        // Define the internal robot base values using the robots index array
        $this->battle_base_name = isset($this_battleinfo['battle_base_name']) ? $this_battleinfo['battle_base_name'] : $this->battle_name;
        $this->battle_base_token = isset($this_battleinfo['battle_base_token']) ? $this_battleinfo['battle_base_token'] : $this->battle_token;
        $this->battle_base_description = isset($this_battleinfo['battle_base_description']) ? $this_battleinfo['battle_base_description'] : $this->battle_description;
        $this->battle_base_turns = isset($this_battleinfo['battle_base_turns']) ? $this_battleinfo['battle_base_turns'] : $this->battle_turns;
        $this->battle_base_rewards = isset($this_battleinfo['battle_base_rewards']) ? $this_battleinfo['battle_base_rewards'] : $this->battle_rewards;
        $this->battle_base_points = isset($this_battleinfo['battle_base_points']) ? $this_battleinfo['battle_base_points'] : $this->battle_points;
        $this->battle_base_level = isset($this_battleinfo['battle_base_level']) ? $this_battleinfo['battle_base_level'] : $this->battle_level;

        // Update the session variable
        $this->update_session();

        // Return true on success
        return true;

    }

    // Define public print functions for markup generation
    //public function print_battle_name(){ return '<span class="battle_name battle_type battle_type_none">'.$this->battle_name.'</span>'; }
    public function print_battle_name(){ return '<span class="battle_name battle_type">'.$this->battle_name.'</span>'; }
    public function print_battle_token(){ return '<span class="battle_token">'.$this->battle_token.'</span>'; }
    public function print_battle_description(){ return '<span class="battle_description">'.$this->battle_description.'</span>'; }
    public function print_battle_points(){ return '<span class="battle_points">'.$this->battle_points.'</span>'; }

    // Define a static public function for encouraging battle words
    public static function random_positive_word(){
        $temp_text_options = array('Awesome!', 'Nice!', 'Fantastic!', 'Yeah!', 'Yay!', 'Yes!', 'Great!', 'Super!', 'Rock on!', 'Amazing!', 'Fabulous!', 'Wild!', 'Sweet!', 'Wow!', 'Oh my!');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

// Define a static public function for encouraging battle victory quotes
    public static function random_victory_quote(){
        $temp_text_options = array('Awesome work!', 'Nice work!', 'Fantastic work!', 'Great work!', 'Super work!', 'Amazing work!', 'Fabulous work!');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

    // Define a static public function for discouraging battle words
    public static function random_negative_word(){
        $temp_text_options = array('Yikes!', 'Oh no!', 'Ouch...', 'Awwwww...', 'Bummer...', 'Boooo...', 'Harsh!', 'Sorry...');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

    // Define a static public function for discouraging battle defeat quotes
    public static function random_defeat_quote(){
        $temp_text_options = array('Maybe try again?', 'Bad luck maybe?', 'Maybe try another stage?', 'Better luck next time?', 'At least you tried... right?');
        $temp_text = $temp_text_options[array_rand($temp_text_options)];
        return $temp_text;
    }

    // Define a public function for extracting actions from the queue
    public function actions_extract($filters){

        $extracted_actions = array();
        foreach($this->actions AS $action_key => $action_array){
            $is_match = true;
            if (!empty($filters['this_player_id']) && $action_array['this_player']->player_id != $filters['this_player_id']){ $is_match = false; }
            if (!empty($filters['this_robot_id']) && $action_array['this_robot']->robot_id != $filters['this_robot_id']){ $is_match = false; }
            if (!empty($filters['target_player_id']) && $action_array['target_player']->player_id != $filters['target_player_id']){ $is_match = false; }
            if (!empty($filters['target_robot_id']) && $action_array['target_robot']->robot_id != $filters['target_robot_id']){ $is_match = false; }
            if (!empty($filters['this_action']) && $action_array['this_action'] != $filters['this_action']){ $is_match = false; }
            if (!empty($filters['this_action_token']) && $action_array['this_action_token'] != $filters['this_action_token']){ $is_match = false; }
            if ($is_match){ $extracted_actions = array_slice($this->actions, $action_key, 1, false); }
        }
        return $extracted_actions;

    }

    // Define a public function for inserting actions into the queue
    public function actions_insert($inserted_actions){

        if (!empty($inserted_actions)){
            $this->actions = array_merge($this->actions, $inserted_actions);
        }

    }

    // Define a public function for prepending to the action array
    public function actions_prepend(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_action_token){

        // Prepend the new action to the array
        array_unshift($this->actions, array(
            'this_field' => &$this->battle_field,
            'this_player' => &$this_player,
            'this_robot' => &$this_robot,
            'target_player' => &$target_player,
            'target_robot' => &$target_robot,
            'this_action' => $this_action,
            'this_action_token' => $this_action_token
            ));

        // Return the resulting array
        return $this->actions;

    }

    // Define a public function for appending to the action array
    public function actions_append(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_action_token){

        // DEBUG
        //$this->events_create(false, false, 'DEBUG_'.__LINE__.'_BATTLE', ' $this_battle->actions_append('.$this_player->player_id.'_'.$this_player->player_token.', '.$this_robot->robot_id.'_'.$this_robot->robot_token.', '.$target_player->player_id.'_'.$target_player->player_token.', '.$target_robot->robot_id.'_'.$target_robot->robot_token.', '.$this_action.', '.$this_action_token.');');

        // Append the new action to the array
        $this->actions[] = array(
            'this_field' => &$this->battle_field,
            'this_player' => &$this_player,
            'this_robot' => &$this_robot,
            'target_player' => &$target_player,
            'target_robot' => &$target_robot,
            'this_action' => $this_action,
            'this_action_token' => $this_action_token
            );

        // Return the resulting array
        return $this->actions;

    }

    // Define a public function for emptying the actions array
    public function actions_empty(){

        // Empty the internal actions array
        $this->actions = array();

        // Return the resulting array
        return $this->actions;

    }

    // Define a public function for execution queued items in the actions array
    public function actions_execute(){

        // Back up the IDs of this and the target robot in the global space
        $temp_this_robot_backup = array('robot_id' => $GLOBALS['this_robot']->robot_id, 'robot_token' => $GLOBALS['this_robot']->robot_token);
        $temp_target_robot_backup = array('robot_id' => $GLOBALS['target_robot']->robot_id, 'robot_token' => $GLOBALS['target_robot']->robot_token);

        // Loop through the non-empty action queue and trigger actions
        while (!empty($this->actions) && $this->battle_status != 'complete'){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

            // Shift and collect the oldest action from the queue
            $current_action = array_shift($this->actions);

            // Reload each player and robot from session to prevent bugs
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            if (!empty($current_action['this_player'])){ $current_action['this_player']->player_load(array('player_id' => $current_action['this_player']->player_id, 'player_token' => $current_action['this_player']->player_token)); }
            if (!empty($current_action['target_player'])){ $current_action['target_player']->player_load(array('player_id' => $current_action['target_player']->player_id, 'player_token' => $current_action['target_player']->player_token)); }
            if (!empty($current_action['this_robot'])){ $current_action['this_robot']->robot_load(array('robot_id' => $current_action['this_robot']->robot_id, 'robot_token' => $current_action['this_robot']->robot_token)); }
            if (!empty($current_action['target_robot'])){ $current_action['target_robot']->robot_load(array('robot_id' => $current_action['target_robot']->robot_id, 'robot_token' => $current_action['target_robot']->robot_token)); }

            // If the robot's player is on autopilot and the action is empty, automate input
            if (empty($current_action['this_action']) && $current_action['this_player']->player_autopilot == true){
                $current_action['this_action'] = 'ability';
            }

            // Based on the action type, trigger the appropriate battle function
            switch ($current_action['this_action']){
                // If the battle start action was called
                case 'start': {
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Initiate the battle start event for this robot
                    $battle_action = $this->actions_trigger(
                        $current_action['this_player'],
                        $current_action['this_robot'],
                        $current_action['target_player'],
                        $current_action['target_robot'],
                        'start',
                        ''
                        );
                    break;
                }
                // If the robot ability action was called
                case 'ability': {
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Initiate the ability event for this player's robot
                    $battle_action = $this->actions_trigger(
                        $current_action['this_player'],
                        $current_action['this_robot'],
                        $current_action['target_player'],
                        $current_action['target_robot'],
                        'ability',
                        $current_action['this_action_token']
                        );
                    break;
                }
                // If the robot switch action was called
                case 'switch': {
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Initiate the switch event for this player's robot
                    $battle_action = $this->actions_trigger(
                        $current_action['this_player'],
                        $current_action['this_robot'],
                        $current_action['target_player'],
                        $current_action['target_robot'],
                        'switch',
                        $current_action['this_action_token']
                        );
                    break;
                }
                // If the robot scan action was called
                case 'scan': {
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Initiate the scan event for this player's robot
                    $battle_action = $this->actions_trigger(
                        $current_action['this_player'],
                        $current_action['this_robot'],
                        $current_action['target_player'],
                        $current_action['target_robot'],
                        'scan',
                        $current_action['this_action_token']
                        );
                    break;
                }
            }

            // Create a closing event with robots in base frames, if the battle is not over
            if ($this->battle_status != 'complete'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $temp_this_robot = false;
                $temp_target_robot = false;
                if (!empty($current_action['this_robot'])){
                    $current_action['this_robot']->robot_frame = $current_action['this_robot']->robot_status != 'disabled' ? 'base' : 'defeat';
                    $current_action['this_robot']->update_session();
                    $current_action['this_player']->player_frame = $current_action['this_robot']->robot_status != 'disabled' ? 'base' : 'defeat';
                    $current_action['this_player']->update_session();
                    //$this_robot = $current_action['this_robot'];
                    $temp_this_robot = &$current_action['this_robot'];
                }
                if (!empty($current_action['target_robot'])){
                    $current_action['target_robot']->robot_frame = $current_action['target_robot']->robot_status != 'disabled' ? 'base' : 'defeat';
                    $current_action['target_robot']->update_session();
                    $current_action['target_player']->player_frame = $current_action['target_robot']->robot_status != 'disabled' ? 'base' : 'defeat';
                    $current_action['target_player']->update_session();
                    //$target_robot = $current_action['target_robot'];
                    $temp_target_robot = &$current_action['target_robot'];
                }
                if (!empty($battle_action) && $battle_action != 'start'){
                    //$this->events_create($temp_this_robot, $temp_target_robot, '', '');
                    $this->events_create(false, false, '', '');
                }
            }

        }

        // Recreate this and the target robot in the global space with backed up info
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        if (empty($GLOBALS['this_robot'])){ $GLOBALS['this_robot'] = new rpg_robot($this, $GLOBALS['this_player'], $temp_this_robot_backup); }
        if (empty($GLOBALS['target_robot'])){ $GLOBALS['target_robot'] = new rpg_robot($this, $GLOBALS['target_player'], $temp_target_robot_backup); }

        // Return true on loop completion
        return true;
    }

    // Define a public function for triggering battle actions
    public function battle_complete_trigger(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_token = ''){
        global $mmrpg_index, $db;
        // DEBUG
        //$this->events_create(false, false, 'DEBUG', 'Battle complete trigger triggered!');

        // Define a variable for forcing zenny rewards if required
        $force_zenny_rewards = false;

        // Return false if anything is missing
        if (empty($this_player) || empty($this_robot)){ return false; }
        if (empty($target_player) || empty($target_robot)){ return false; }

        // Return true if the battle status is already complete
        if ($this->battle_status == 'complete'){ return true; }

        // Update the battle status to complete
        $this->battle_status = 'complete';
        if ($this->battle_result == 'pending'){
            $this->battle_result = $target_player->player_side == 'right' ? 'victory' : 'defeat';
            $this->update_session();
            $event_options = array();
            if ($this->battle_result == 'victory'){
                $event_options['event_flag_victory'] = true;
            }
            elseif ($this->battle_result == 'defeat'){
                $event_options['event_flag_defeat'] = true;
            }
            $this->events_create(false, false, '', '', $event_options);
        }

        // Define variables for the human's rewards in this scenario
        $temp_human_token = $target_player->player_side == 'left' ? $target_player->player_token : $this_player->player_token;
        $temp_human_rewards = array();
        $temp_human_rewards['battle_points'] = 0;
        $temp_human_rewards['battle_complete'] = isset($_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_complete'][$temp_human_token][$this->battle_token]['battle_count'] : 0;
        $temp_human_rewards['battle_failure'] = isset($_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this->battle_token]['battle_count']) ? $_SESSION['GAME']['values']['battle_failure'][$temp_human_token][$this->battle_token]['battle_count'] : 0;
        $temp_human_rewards['checkpoint'] = 'start: ';

        // Check to see if this is a player battle
        $this_is_player_battle = false;
        if ($this_player->player_side == 'right' && $this_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID){
            $this_is_player_battle = true;
        } elseif ($target_player->player_side == 'right' && $target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID){
            $this_is_player_battle = true;
        }

        // Check if this battle's points count
        $this_mission_counts = $this->battle_counts ? true : false;

        // Ensure the system knows to reward zenny instead of points for player battles
        if (!$this_mission_counts){
            $force_zenny_rewards = true;
        }

        // (HUMAN) TARGET DEFEATED
        // Check if the target was the human character
        if ($target_player->player_side == 'left'){

            // DEBUG
            //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

            // Calculate the number of battle points for the target player
            $this_base_points = 0; //$this->battle_points;
            $this_turn_points = 100 * $this->counters['battle_turn'];
            $this_stat_points = 0;
            $target_battle_points = $this_base_points + $this_turn_points + $this_stat_points;
            // Prevent players from loosing points
            if ($target_battle_points == 0){ $target_battle_points = 1; }
            elseif ($target_battle_points < 0){ $target_battle_points = -1 * $target_battle_points; }

            // Recalculate the main game's points total with the battle points
            $_SESSION['GAME']['counters']['battle_points'] = mmrpg_prototype_calculate_battle_points();

            // Increment this player's points total with the battle points
            $_SESSION['GAME']['values']['battle_rewards'][$target_player->player_token]['player_points'] += $target_battle_points;

            // Update the global variable with the points reward
            $temp_human_rewards['battle_points'] = $target_battle_points;

            // Update the GAME session variable with the failed battle token
            if ($this->battle_counts){
                // DEBUG
                //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;
                $bak_session_array = isset($_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token]) ? $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token] : array();
                $new_session_array = array('battle_token' => $this->battle_token, 'battle_count' => 0, 'battle_level' => 0);
                if (!empty($bak_session_array['battle_count'])){ $new_session_array['battle_count'] = $bak_session_array['battle_count']; }
                if (!empty($bak_session_array['battle_level'])){ $new_session_array['battle_level'] = $bak_session_array['battle_level']; }
                $new_session_array['battle_level'] = $this->battle_level;
                $new_session_array['battle_count']++;
                $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token] = $new_session_array;
                $temp_human_rewards['battle_failure'] = $_SESSION['GAME']['values']['battle_failure'][$target_player->player_token][$this->battle_token]['battle_count'];
            }

        }
        // (GHOST/COMPUTER) TARGET DEFEATED
        // Otherwise if the target was a computer-controlled human character
        elseif ($target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID){

            // Calculate the battle points based on how many turns they lasted
            $target_battle_points = ceil($this->counters['battle_turn'] * 100 * MMRPG_SETTINGS_BATTLEPOINTS_PERZENNY_MULTIPLIER);

        }
        // (COMPUTER) TARGET DEFEATED
        // Otherwise, zero target battle points
        else {
            // DEBUG
            //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;
            // Target is computer, no battle points for them
            $target_battle_points = 0;
        }


        // NON-INVISIBLE PLAYER DEFEATED
        // Display the defeat message for the target character if not default/hidden
        if ($target_player->player_token != 'player'){

            // DEBUG
            //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

            // (HUMAN) TARGET DEFEATED BY (INVISIBLE/COMPUTER)
            // If this was a player battle and the human user lost against the ghost target (this/computer/victory | target/human/defeat)
            if ($this_player->player_id == MMRPG_SETTINGS_TARGET_PLAYERID && $target_player->player_side == 'left' && $this_robot->robot_class != 'mecha'){

                // Calculate how many points the other player is rewarded for winning
                $target_player_robots = $target_player->values['robots_disabled'];
                $target_player_robots_count = count($target_player_robots);
                $other_player_points = 0;
                $other_player_turns = $target_player_robots_count * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
                foreach ($target_player_robots AS $disabled_robotinfo){
                    $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER;
                }

                // Collect the battle points from the function
                $other_battle_points_modded = $this->calculate_battle_points($target_player, $other_player_points, $other_player_turns);

                // Create the victory event for the target player
                $this_robot->robot_frame = 'victory';
                $this_robot->update_session();
                $event_header = $this_robot->robot_name.' Undefeated';
                $event_body = '';
                $event_body .= $this_robot->print_robot_name().' could not be defeated! ';
                //$event_body .= $this_robot->print_robot_name().' downloads the '.($target_robot->counters['robots_disabled'] > 1 ? 'targets#39;' : 'target#39;s').' battle data!';
                $event_body .= '<br />';
                $event_options = array();
                $event_options['console_show_this_robot'] = true;
                $event_options['console_show_target'] = false;
                $event_options['event_flag_defeat'] = true;
                $event_options['this_header_float'] = $event_options['this_body_float'] = $this_robot->player->player_side;
                if ($this_robot->robot_token != 'robot'
                    && isset($this_robot->robot_quotes['battle_victory'])){
                    $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                    $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
                    $event_body .= $this_robot->print_robot_quote('battle_victory', $this_find, $this_replace);
                    //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_victory']);
                    //$this_text_colour = !empty($mmrpg_index['types'][$this_robot->robot_token]) ? $mmrpg_index['types'][$this_robot->robot_token]['type_colour_light'] : array(200, 200, 200);
                    //$event_body .= '&quot;<em style="color: rgb('.implode(',', $this_text_colour).');">'.$this_quote_text.'</em>&quot;';
                }
                $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

            }

            $target_player->player_frame = 'defeat';
            $target_robot->update_session();
            $target_player->update_session();
            $event_header = $target_player->player_name.' Defeated';
            $event_body = $target_player->print_player_name().' was defeated'.($target_player->player_side == 'left' ? '&hellip;' : '!').' ';
            //if (!empty($target_battle_points)){ $event_body .= $target_player->print_player_name().' collects <span class="recovery_amount">'.number_format($target_battle_points, 0, '.', ',').'</span> battle points&hellip;'; }
            $event_body .= '<br />';
            $event_options = array();
            $event_options['console_show_this_player'] = true;
            $event_options['console_show_target'] = false;
            $event_options['event_flag_defeat'] = true;
            $event_options['this_header_float'] = $event_options['this_body_float'] = $target_player->player_side;
            if ($target_player->player_token != 'player'
                && isset($target_player->player_quotes['battle_defeat'])){
                $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                $this_replace = array($this_player->player_name, $this_robot->robot_name, $target_player->player_name, $target_robot->robot_name);
                $this_quote_text = str_replace($this_find, $this_replace, $target_player->player_quotes['battle_defeat']);
                $event_body .= $target_player->print_player_quote('battle_defeat', $this_find, $this_replace);
                //$this_text_colour = !empty($mmrpg_index['types'][$target_player->player_token]) ? $mmrpg_index['types'][$target_player->player_token]['type_colour_light'] : array(200, 200, 200);
                //$event_body .= '&quot;<em style="color: rgb('.implode(',', $this_text_colour).');">'.$this_quote_text.'</em>&quot;';
            }
            $this->events_create($target_robot, $this_robot, $event_header, $event_body, $event_options);

            // (HUMAN) TARGET DEFEATED BY (GHOST/COMPUTER)
            // If this was a player battle and the human user lost against the ghost target (this/computer/victory | target/human/defeat)
            if ($this_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID && $target_player->player_side == 'left'){

                // Calculate how many points the other player is rewarded for winning
                $target_player_robots = $target_player->values['robots_disabled'];
                $target_player_robots_count = count($target_player_robots);
                $other_player_points = 0;
                $other_player_turns = $target_player_robots_count * MMRPG_SETTINGS_BATTLETURNS_PERROBOT;
                foreach ($target_player_robots AS $disabled_robotinfo){
                    $other_player_points += $disabled_robotinfo['robot_level'] * MMRPG_SETTINGS_BATTLEPOINTS_PERLEVEL * MMRPG_SETTINGS_BATTLEPOINTS_PLAYERBATTLE_MULTIPLIER;
                }

                // Collect the battle points from the function
                $other_battle_points_modded = $this->calculate_battle_points($target_player, $other_player_points, $other_player_turns);

                // Create the victory event for the target player
                $this_player->player_frame = 'victory';
                $target_robot->update_session();
                $this_player->update_session();
                $event_header = $this_player->player_name.' Victorious';
                $event_body = $this_player->print_player_name().' was victorious! ';
                $event_body .= $this_player->print_player_name().' collects <span class="recovery_amount">'.number_format($other_battle_points_modded, 0, '.', ',').'</span> battle points!';
                //$event_body .= $this_player->print_player_name().' downloads the '.($target_player->counters['robots_disabled'] > 1 ? 'targets#39;' : 'target#39;s').' battle data!';
                $event_body .= '<br />';
                $event_options = array();
                $event_options['console_show_this_player'] = true;
                $event_options['console_show_target'] = false;
                $event_options['event_flag_defeat'] = true;
                $event_options['this_header_float'] = $event_options['this_body_float'] = $this_player->player_side;
                if ($this_player->player_token != 'player'
                    && isset($this_player->player_quotes['battle_victory'])){
                    $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                    $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
                    $event_body .= $this_player->print_player_quote('battle_victory', $this_find, $this_replace);
                    //$this_quote_text = str_replace($this_find, $this_replace, $this_player->player_quotes['battle_victory']);
                    //$this_text_colour = !empty($mmrpg_index['types'][$this_player->player_token]) ? $mmrpg_index['types'][$this_player->player_token]['type_colour_light'] : array(200, 200, 200);
                    //$event_body .= '&quot;<em style="color: rgb('.implode(',', $this_text_colour).');">'.$this_quote_text.'</em>&quot;';
                }
                $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

                // Create the temp robot sprites for the database
                $temp_this_player_robots = array();
                $temp_target_player_robots = array();
                foreach ($target_player->player_robots AS $key => $info){ $temp_this_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
                foreach ($this_player->player_robots AS $key => $info){ $temp_target_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
                $temp_this_player_robots = !empty($temp_this_player_robots) ? implode(',', $temp_this_player_robots) : '';
                $temp_target_player_robots = !empty($temp_target_player_robots) ? implode(',', $temp_target_player_robots) : '';
                // Collect the userinfo for the target player
                //$target_player_userinfo = $db->get_array("SELECT user_name, user_name_clean, user_name_public FROM mmrpg_users WHERE user_id = {$target_player->player_id};");
                //if (!isset($_SESSION['PROTOTYPE_TEMP']['player_targets_defeated'])){ $_SESSION['PROTOTYPE_TEMP']['player_targets_defeated'] = array(); }
                //$_SESSION['PROTOTYPE_TEMP']['player_targets_defeated'][] = $target_player_userinfo['user_name_clean'];
                // Update the database with these pending rewards for each player
                global $db;
                $db->insert('mmrpg_battles', array(
                    'battle_field_name' => $this->battle_field->field_name,
                    'battle_field_background' => $this->battle_field->field_background,
                    'battle_field_foreground' => $this->battle_field->field_foreground,
                    'battle_turns' => $this->counters['battle_turn'],
                    'this_user_id' => $target_player->player_id,
                    'this_player_token' => $target_player->player_token,
                    'this_player_robots' => $temp_this_player_robots,
                    'this_player_points' => $target_battle_points,
                    'this_player_result' => 'defeat',
                    'this_reward_pending' => 0,
                    'target_user_id' => $this_player->player_id,
                    'target_player_token' => $this_player->player_token,
                    'target_player_robots' => $temp_target_player_robots,
                    'target_player_points' => $other_battle_points_modded,
                    'target_player_result' => 'victory',
                    'target_reward_pending' => 1
                    ));

            }

        }


        // (HUMAN) TARGET DEFEATED BY (COMPUTER)
        // Check if the target was the human character (and they LOST)
        if ($target_player->player_side == 'left'){

                // DEBUG
                //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

                // Collect the robot info array
                $temp_player_info = $target_player->export_array();

                // Collect or define the player points and player rewards variables
                $temp_player_token = $temp_player_info['player_token'];
                $temp_player_points = mmrpg_prototype_player_points($temp_player_info['player_token']);
                $temp_player_rewards = mmrpg_prototype_player_rewards($temp_player_info['player_token']); //!empty($temp_player_info['player_rewards']) ? $temp_player_info['player_rewards'] : array();

                // -- ABILITY REWARDS for HUMAN PLAYER -- //

                // Loop through the ability rewards for this robot if set
                if (!empty($temp_player_rewards['abilities']) && empty($_SESSION['GAME']['DEMO'])){
                    $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                    foreach ($temp_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){

                        // If this ability is already unlocked, continue
                        if (mmrpg_prototype_ability_unlocked($target_player->player_token, false, $ability_reward_info['token'])){ continue; }
                        // If we're in DEMO mode, continue
                        //if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

                        // Check if the required level has been met by this robot
                        if ($temp_player_points >= $ability_reward_info['points'] && empty($_SESSION['GAME']['DEMO'])){

                            // Collect the ability info from the index
                            $ability_info = rpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
                            // Create the temporary ability object for event creation
                            $temp_ability = new rpg_ability($this, $target_player, $target_robot, $ability_info);

                            // Collect or define the ability variables
                            $temp_ability_token = $ability_info['ability_token'];

                            // Display the robot reward message markup
                            $event_header = $ability_info['ability_name'].' Unlocked';
                            $event_body = rpg_battle::random_positive_word().' <span class="player_name">'.$temp_player_info['player_name'].'</span> unlocked new ability data!<br />';
                            $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
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
                            $event_options['canvas_show_this_ability'] = false;
                            $target_player->player_frame = $ability_reward_key % 2 == 0 ? 'victory' : 'taunt';
                            $target_player->update_session();
                            $temp_ability->ability_frame = 'base';
                            $temp_ability->update_session();
                            $this->events_create($target_robot, $target_robot, $event_header, $event_body, $event_options);

                            // Automatically unlock this ability for use in battle
                            $this_reward = array('ability_token' => $temp_ability_token);
                            mmrpg_game_unlock_ability($temp_player_info, false, $this_reward, true);

                        }

                    }
                }


        }

        // (COMPUTER) TARGET DEFEATED BY (HUMAN)
        // Check if this player was the human player (and they WON)
        if ($this_player->player_side == 'left'){

            // DEBUG
            //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

            // Collect the battle points from the function
            $this_battle_points = $this->calculate_battle_points($this_player, $this->battle_points, $this->battle_turns);

            // Increment the main game's points total with the battle points
            $_SESSION['GAME']['counters']['battle_points'] = mmrpg_prototype_calculate_battle_points();

            // Reference the number of points this player gets
            $this_player_points = $this_battle_points;

            // Increment this player's points total with the battle points
            $player_token = $this_player->player_token;
            $player_info = $this_player->export_array();
            if (!isset($_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'])){ $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'] = 0; }
            $_SESSION['GAME']['values']['battle_rewards'][$player_token]['player_points'] = mmrpg_prototype_calculate_player_points($player_token);

            // Update the global variable with the points reward
            $temp_human_rewards['battle_points'] = $this_player_points;

            // Display the win message for this player with battle points
            $this_robot->robot_frame = 'victory';
            $this_player->player_frame = 'victory';
            $this_robot->update_session();
            $this_player->update_session();
            $event_header = $this_player->player_name.' Victorious';
            $event_body = $this_player->print_player_name().' was victorious! ';
            //$event_body .= $this_player->print_player_name().' collects <span class="recovery_amount">'.number_format($this_player_points, 0, '.', ',').'</span> battle points!';
            $event_body .= 'The '.($target_player->counters['robots_disabled'] > 1 ? 'targets were' : 'target was').' defeated!';
            $event_body .= '<br />';
            $event_options = array();
            $event_options['console_show_this_player'] = true;
            $event_options['console_show_target'] = false;
            $event_options['event_flag_victory'] = true;
            $event_options['this_header_float'] = $event_options['this_body_float'] = $this_player->player_side;
            if ($this_player->player_token != 'player'
                && isset($this_player->player_quotes['battle_victory'])){
                $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
                $event_body .= $this_player->print_player_quote('battle_victory', $this_find, $this_replace);
                //$this_quote_text = str_replace($this_find, $this_replace, $this_player->player_quotes['battle_victory']);
                //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
            }
            $this->events_create($this_robot, $target_robot, $event_header, $event_body, $event_options);

            // If this was a PLAYER BATTLE and the human user won against them (this/human/victory | target/computer/defeat)
            if ($target_player->player_id != MMRPG_SETTINGS_TARGET_PLAYERID && $this_player->player_side == 'left'){

                // DEBUG
                //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

                // Ensure the system knows to reward zenny instead of points
                $force_zenny_rewards = true;

                // Create the temp robot sprites for the database
                $temp_this_player_robots = array();
                $temp_target_player_robots = array();
                foreach ($this_player->player_robots AS $key => $info){ $temp_this_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
                foreach ($target_player->player_robots AS $key => $info){ $temp_target_player_robots[] = '['.$info['robot_token'].':'.$info['robot_level'].']'; }
                $temp_this_player_robots = !empty($temp_this_player_robots) ? implode(',', $temp_this_player_robots) : '';
                $temp_target_player_robots = !empty($temp_target_player_robots) ? implode(',', $temp_target_player_robots) : '';
                // Collect the userinfo for the target player
                $target_player_userinfo = $db->get_array("SELECT user_name, user_name_clean, user_name_public FROM mmrpg_users WHERE user_id = {$target_player->player_id};");
                if (!isset($_SESSION['LEADERBOARD']['player_targets_defeated'])){ $_SESSION['LEADERBOARD']['player_targets_defeated'] = array(); }
                $_SESSION['LEADERBOARD']['player_targets_defeated'][] = $target_player_userinfo['user_name_clean'];
                // Update the database with these pending rewards for each player
                global $db;
                $db->insert('mmrpg_battles', array(
                    'battle_field_name' => $this->battle_field->field_name,
                    'battle_field_background' => $this->battle_field->field_background,
                    'battle_field_foreground' => $this->battle_field->field_foreground,
                    'battle_turns' => $this->counters['battle_turn'],
                    'this_user_id' => $this_player->player_id,
                    'this_player_token' => $this_player->player_token,
                    'this_player_robots' => $temp_this_player_robots,
                    'this_player_points' => $this_player_points,
                    'this_player_result' => 'victory',
                    'this_reward_pending' => 0,
                    'target_user_id' => $target_player->player_id,
                    'target_player_token' => $target_player->player_token,
                    'target_player_robots' => $temp_target_player_robots,
                    'target_player_points' => $target_battle_points,
                    'target_player_result' => 'defeat',
                    'target_reward_pending' => 1
                    ));

            }


            /*
             * PLAYER REWARDS
             */

            // Check if the the player was a human character
            if ($this_player->player_side == 'left'){

                // DEBUG
                //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;


                // Collect the robot info array
                $temp_player_info = $this_player->export_array();

                // Collect or define the player points and player rewards variables
                $temp_player_token = $temp_player_info['player_token'];
                $temp_player_points = mmrpg_prototype_player_points($temp_player_info['player_token']);
                $temp_player_rewards = !empty($temp_player_info['player_rewards']) ? $temp_player_info['player_rewards'] : array();

                // -- ABILITY REWARDS for HUMAN PLAYER -- //

                // Loop through the ability rewards for this player if set
                if (!empty($temp_player_rewards['abilities']) && empty($_SESSION['GAME']['DEMO'])){
                    $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                    foreach ($temp_player_rewards['abilities'] AS $ability_reward_key => $ability_reward_info){

                        // If this ability is already unlocked, continue
                        if (mmrpg_prototype_ability_unlocked($this_player->player_token, false, $ability_reward_info['token'])){ continue; }
                        // If this is the copy shot ability and we're in DEMO mode, continue
                        //if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

                        // Check if the required level has been met by this robot
                        if ($temp_player_points >= $ability_reward_info['points']){

                            // Collect the ability info from the index
                            $ability_info = rpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
                            // Create the temporary ability object for event creation
                            $temp_ability = new rpg_ability($this, $this_player, $this_robot, $ability_info);

                            // Collect or define the ability variables
                            $temp_ability_token = $ability_info['ability_token'];

                            // Display the robot reward message markup
                            $event_header = $ability_info['ability_name'].' Unlocked';
                            $event_body = rpg_battle::random_positive_word().' <span class="player_name">'.$temp_player_info['player_name'].'</span> unlocked new ability data!<br />';
                            $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
                            $event_options = array();
                            $event_options['console_show_target'] = false;
                            $event_options['this_header_float'] = $this_player->player_side;
                            $event_options['this_body_float'] = $this_player->player_side;
                            $event_options['this_ability'] = $temp_ability;
                            $event_options['this_ability_image'] = 'icon';
                            $event_options['event_flag_victory'] = true;
                            $event_options['console_show_this_player'] = false;
                            $event_options['console_show_this_robot'] = false;
                            $event_options['console_show_this_ability'] = true;
                            $event_options['canvas_show_this_ability'] = false;
                            $this_player->player_frame = $ability_reward_key % 2 == 0 ? 'victory' : 'taunt';
                            $this_player->update_session();
                            $this_robot->robot_frame = $ability_reward_key % 2 == 0 ? 'taunt' : 'base';
                            $this_robot->update_session();
                            $temp_ability->ability_frame = 'base';
                            $temp_ability->update_session();
                            $this->events_create($this_robot, $this_robot, $event_header, $event_body, $event_options);

                            // Automatically unlock this ability for use in battle
                            $this_reward = array('ability_token' => $temp_ability_token);
                            mmrpg_game_unlock_ability($temp_player_info, false, $this_reward, true);

                        }

                    }
                }

            }


            /*
             * ROBOT DATABASE UPDATE
             */

            // Loop through all the target robot's and add them to the database
            /*
            if (!empty($target_player->values['robots_disabled'])){
                foreach ($target_player->values['robots_disabled'] AS $temp_key => $temp_info){
                    // Add this robot to the global robot database array
                    if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']])){ $_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']] = array('robot_token' => $temp_info['robot_token']); }
                    if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']]['robot_defeated'])){ $_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']]['robot_defeated'] = 0; }
                    $_SESSION['GAME']['values']['robot_database'][$temp_info['robot_token']]['robot_defeated']++;
                }
            }
            */



        }


        /*
         * BATTLE REWARDS
         */

        // Check if this player was the human player
        if ($this_player->player_side == 'left'){

            // DEBUG
            //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;

            // Update the GAME session variable with the completed battle token
            if ($this->battle_counts){
                // DEBUG
                //$temp_human_rewards['checkpoint'] .= '; '.__LINE__;
                // Back up the current session array for this battle complete counter
                $bak_session_array = isset($_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token]) ? $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token] : array();
                // Create the new session array from scratch to ensure all values exist
                $new_session_array = array(
                    'battle_token' => $this->battle_token,
                    'battle_count' => 0,
                    'battle_min_level' => 0,
                    'battle_max_level' => 0,
                    'battle_min_turns' => 0,
                    'battle_max_turns' => 0,
                    'battle_min_points' => 0,
                    'battle_max_points' => 0,
                    'battle_min_robots' => 0,
                    'battle_max_robots' => 0
                    );
                // Recollect applicable battle values from the backup session array
                if (!empty($bak_session_array['battle_count'])){ $new_session_array['battle_count'] = $bak_session_array['battle_count']; }
                if (!empty($bak_session_array['battle_level'])){ $new_session_array['battle_min_level'] = $bak_session_array['battle_level']; } // LEGACY
                if (!empty($bak_session_array['battle_min_level'])){ $new_session_array['battle_min_level'] = $bak_session_array['battle_min_level']; }
                if (!empty($bak_session_array['battle_max_level'])){ $new_session_array['battle_max_level'] = $bak_session_array['battle_max_level']; }
                if (!empty($bak_session_array['battle_min_turns'])){ $new_session_array['battle_min_turns'] = $bak_session_array['battle_min_turns']; }
                if (!empty($bak_session_array['battle_max_turns'])){ $new_session_array['battle_max_turns'] = $bak_session_array['battle_max_turns']; }
                if (!empty($bak_session_array['battle_min_points'])){ $new_session_array['battle_min_points'] = $bak_session_array['battle_min_points']; }
                if (!empty($bak_session_array['battle_max_points'])){ $new_session_array['battle_max_points'] = $bak_session_array['battle_max_points']; }
                if (!empty($bak_session_array['battle_min_robots'])){ $new_session_array['battle_min_robots'] = $bak_session_array['battle_min_robots']; }
                if (!empty($bak_session_array['battle_max_robots'])){ $new_session_array['battle_max_robots'] = $bak_session_array['battle_max_robots']; }
                // Update and/or increment the appropriate battle variables in the new array
                if ($new_session_array['battle_max_level'] == 0 || $this->battle_level > $new_session_array['battle_max_level']){ $new_session_array['battle_max_level'] = $this->battle_level; }
                if ($new_session_array['battle_min_level'] == 0 || $this->battle_level < $new_session_array['battle_min_level']){ $new_session_array['battle_min_level'] = $this->battle_level; }
                if ($new_session_array['battle_max_turns'] == 0 || $this->counters['battle_turn'] > $new_session_array['battle_max_turns']){ $new_session_array['battle_max_turns'] = $this->counters['battle_turn']; }
                if ($new_session_array['battle_min_turns'] == 0 || $this->counters['battle_turn'] < $new_session_array['battle_min_turns']){ $new_session_array['battle_min_turns'] = $this->counters['battle_turn']; }
                if ($new_session_array['battle_max_points'] == 0 || $temp_human_rewards['battle_points'] > $new_session_array['battle_max_points']){ $new_session_array['battle_max_points'] = $temp_human_rewards['battle_points']; }
                if ($new_session_array['battle_min_points'] == 0 || $temp_human_rewards['battle_points'] < $new_session_array['battle_min_points']){ $new_session_array['battle_min_points'] = $temp_human_rewards['battle_points']; }
                if ($new_session_array['battle_max_robots'] == 0 || $this_player->counters['robots_total'] > $new_session_array['battle_max_robots']){ $new_session_array['battle_max_robots'] = $this_player->counters['robots_total']; }
                if ($new_session_array['battle_min_robots'] == 0 || $this_player->counters['robots_total'] < $new_session_array['battle_min_robots']){ $new_session_array['battle_min_robots'] = $this_player->counters['robots_total']; }
                $new_session_array['battle_count']++;
                // Update the session variable for this player with the updated battle values
                $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token] = $new_session_array;
                $temp_human_rewards['battle_complete'] = $_SESSION['GAME']['values']['battle_complete'][$this_player->player_token][$this->battle_token]['battle_count'];
                // Update the main game's points total with the new battle points
                $_SESSION['GAME']['counters']['battle_points'] = mmrpg_prototype_calculate_battle_points();
            }

            // Collect or define the player variables
            $this_player_token = $this_player->player_token;
            $this_player_info = $this_player->export_array();

            // ROBOT REWARDS

            // Loop through any robot rewards for this battle
            $this_robot_rewards = !empty($this->battle_rewards['robots']) ? $this->battle_rewards['robots'] : array();
            if (!empty($this_robot_rewards)){
                foreach ($this_robot_rewards AS $robot_reward_key => $robot_reward_info){

                    // If this is the copy shot ability and we're in DEMO mode, continue
                    if (!empty($_SESSION['GAME']['DEMO'])){ continue; }

                    // If this robot has already been unlocked, continue
                    //if (mmrpg_prototype_robot_unlocked($this_player_token, $robot_reward_info['token'])){ continue; }

                    // If this robot has already been unlocked by anyone, continue
                    if (mmrpg_prototype_robot_unlocked(false, $robot_reward_info['token'])){ continue; }

                    // Collect the robot info from the index
                    $robot_info = rpg_robot::get_index_info($robot_reward_info['token']);
                    // Search this player's base robots for the robot ID
                    $robot_info['robot_id'] = 0;
                    foreach ($this_player->player_base_robots AS $base_robot){
                        if ($robot_info['robot_token'] == $base_robot['robot_token']){
                            $robot_info['robot_id'] = $base_robot['robot_id'];
                            break;
                        }
                    }
                    // Create the temporary robot object for event creation
                    $temp_robot = new rpg_robot($this, $this_player, $robot_info);

                    // Collect or define the robot points and robot rewards variables
                    $this_robot_token = $robot_reward_info['token'];
                    $this_robot_level = !empty($robot_reward_info['level']) ? $robot_reward_info['level'] : 1;
                    $this_robot_experience = !empty($robot_reward_info['experience']) ? $robot_reward_info['experience'] : 0;
                    $this_robot_rewards = !empty($robot_info['robot_rewards']) ? $robot_info['robot_rewards'] : array();

                    // Automatically unlock this robot for use in battle
                    $this_reward = $robot_info;
                    $this_reward['robot_level'] = $this_robot_level;
                    $this_reward['robot_experience'] = $this_robot_experience;
                    mmrpg_game_unlock_robot($this_player_info, $this_reward, true, true);
                    //$_SESSION['GAME']['values']['battle_rewards'][$this_player_token]['player_robots'][$this_robot_token] = $this_reward;

                    // DEBUG
                    //$debug_body = '<pre>$robot_reward_info:'.preg_replace('/\s+/', ' ', print_r($robot_reward_info, true)).'</pre>';
                    //$debug_body .= '<pre>$this_reward:'.preg_replace('/\s+/', ' ', print_r($this_reward, true)).'</pre>';
                    //$this->events_create(false, false, 'DEBUG', $debug_body);

                    // Display the robot reward message markup
                    /*
                    $event_header = $robot_info['robot_name'].' Unlocked';
                    $event_body = 'A new robot has been unlocked!<br />';
                    $event_body .= '<span class="robot_name">'.$robot_info['robot_name'].'</span> can now be used in battle!';
                    $event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $this_player->player_side;
                    $event_options['this_body_float'] = $this_player->player_side;
                    $event_options['this_robot_image'] = 'mug';
                    $temp_robot->robot_frame = 'base';
                    $temp_robot->update_session();
                    $this->events_create($temp_robot, false, $event_header, $event_body, $event_options);
                    */

                }
            }

            // ABILITY REWARDS

            // Loop through any ability rewards for this battle
            $this_ability_rewards = !empty($this->battle_rewards['abilities']) ? $this->battle_rewards['abilities'] : array();
            if (!empty($this_ability_rewards) && empty($_SESSION['GAME']['DEMO'])){
                $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                foreach ($this_ability_rewards AS $ability_reward_key => $ability_reward_info){

                    // Collect the ability info from the index
                    $ability_info = rpg_ability::parse_index_info($temp_abilities_index[$ability_reward_info['token']]);
                    // Create the temporary robot object for event creation
                    $temp_ability = new rpg_ability($this, $this_player, $this_robot, $ability_info);

                    // Collect or define the robot points and robot rewards variables
                    $this_ability_token = $ability_info['ability_token'];

                    // Now loop through all active robots on this side of the field
                    foreach ($this_player_info['values']['robots_active'] AS $temp_key => $temp_info){
                        // DEBUG
                        //$this->events_create(false, false, 'DEBUG', 'Checking '.$temp_info['robot_name'].' for compatibility with the '.$ability_info['ability_name']);
                        //$debug_fragment = '';
                        // If this robot is a mecha, skip it!
                        if (!empty($temp_info['robot_class']) && $temp_info['robot_class'] == 'mecha'){ continue; }
                        // Equip this ability to the robot is there was a match found
                        if (rpg_robot::has_ability_compatibility($temp_info['robot_token'], $ability_info['ability_token'])){
                            if (!isset( $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'] )){ $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'] = array(); }
                            if (count($_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities']) < 8){ $_SESSION['GAME']['values']['battle_settings'][$this_player_info['player_token']]['player_robots'][$temp_info['robot_token']]['robot_abilities'][$ability_info['ability_token']] = array('ability_token' => $ability_info['ability_token']); }
                        }
                    }

                    // If this ability has already been unlocked by the player, continue
                    if (mmrpg_prototype_ability_unlocked($this_player_token, false, $ability_reward_info['token'])){ continue; }

                    // Automatically unlock this ability for use in battle
                    $this_reward = array('ability_token' => $this_ability_token);
                    mmrpg_game_unlock_ability($this_player_info, false, $this_reward, true);

                    // Display the robot reward message markup
                    $event_header = $ability_info['ability_name'].' Unlocked';
                    $event_body = rpg_battle::random_positive_word().' <span class="player_name">'.$this_player_info['player_name'].'</span> unlocked new ability data!<br />';
                    $event_body .= '<span class="ability_name">'.$ability_info['ability_name'].'</span> can now be used in battle!';
                    $event_options = array();
                    $event_options['console_show_target'] = false;
                    $event_options['this_header_float'] = $this_player->player_side;
                    $event_options['this_body_float'] = $this_player->player_side;
                    $event_options['this_ability'] = $temp_ability;
                    $event_options['this_ability_image'] = 'icon';
                    $event_options['console_show_this_player'] = false;
                    $event_options['console_show_this_robot'] = false;
                    $event_options['console_show_this_ability'] = true;
                    $event_options['canvas_show_this_ability'] = false;
                    $this_player->player_frame = 'victory';
                    $this_player->update_session();
                    $temp_ability->ability_frame = 'base';
                    $temp_ability->update_session();
                    $this->events_create($this_robot, false, $event_header, $event_body, $event_options);

                }
            }




        } // end of BATTLE REWARDS

        // Check if there is a field star for this stage to collect
        if ($this->battle_result == 'victory' && !empty($this->values['field_star'])){

            // Collect the field star data for this battle
            $temp_field_star = $this->values['field_star'];

            // Print out the event for collecting the new field star
            $temp_name_markup = '<span class="field_name field_type field_type_'.(!empty($temp_field_star['star_type']) ? $temp_field_star['star_type'] : 'none').(!empty($temp_field_star['star_type2']) ? '_'.$temp_field_star['star_type2'] : '').'">'.$temp_field_star['star_name'].' Star</span>';
            $temp_event_header = $this_player->player_name.'&#39;s '.ucfirst($temp_field_star['star_kind']).' Star';
            $temp_event_body = $this_player->print_player_name().' collected the '.$temp_name_markup.'!<br />';
            $temp_event_body .= 'The new '.ucfirst($temp_field_star['star_kind']).' Star amplifies your Star Force!';
            $temp_event_options = array();
            $temp_event_options['console_show_this_player'] = false;
            $temp_event_options['console_show_target_player'] = false;
            $temp_event_options['console_show_this_robot'] = false;
            $temp_event_options['console_show_target_robot'] = false;
            $temp_event_options['console_show_this_ability'] = false;
            $temp_event_options['console_show_this'] = true;
            $temp_event_options['console_show_this_star'] = true;
            $temp_event_options['this_header_float'] = $temp_event_options['this_body_float'] = $this_player->player_side;
            $temp_event_options['this_star'] = $temp_field_star;
            $temp_event_options['this_ability'] = false;
            $this->events_create(false, false, $temp_event_header, $temp_event_body, $temp_event_options);

            // Update the session with this field star data
            $_SESSION['GAME']['values']['battle_stars'][$temp_field_star['star_token']] = $temp_field_star;

            // DEBUG DEBUG
            //$this->events_create($this_robot, $target_robot, 'DEBUG FIELD STAR', 'You got a field star! The field star names '.implode(' | ', $temp_field_star));

        }


        // Define the first event body markup, regardless of player type
        $first_event_header = $this->battle_name.($this->battle_result == 'victory' ? ' Complete' : ' Failure').' <span style="opacity:0.25;">|</span> '.$this->battle_field->field_name;
        if ($this->battle_result == 'victory'){ $first_event_body = 'Mission complete! '.($temp_human_rewards['battle_complete'] > 1 ? rpg_battle::random_positive_word().' That&#39;s '.$temp_human_rewards['battle_complete'].' times now! ' : '').rpg_battle::random_victory_quote(); }
        elseif ($this->battle_result == 'defeat'){ $first_event_body = 'Mission failure. '.($temp_human_rewards['battle_failure'] > 1 ? 'That&#39;s '.$temp_human_rewards['battle_failure'].' times now&hellip; ' : '').rpg_battle::random_defeat_quote(); }
        $first_event_body .= '<br />';

        // Print out the current vs allowed turns for this mission
        $first_event_body .= 'Turns : '.$this->counters['battle_turn'].' / '.$this->battle_turns.' <span style="opacity:0.25;">|</span> ';

        // Print out the base reward amount
        $first_event_body .= 'Reward : '.number_format($this->battle_points, 0, '.', ',').' <span style="opacity:0.25;">|</span> ';

        // Print out the bonus and rewards based on the above stats
        if ($this->battle_result == 'victory'){

            // If the user was over or under the exact turns, print out bonuses
            if ($this->counters['battle_turn'] != $this->battle_turns){

                // If the user gets a turn BONUS
                if ($this->counters['battle_turn'] < $this->battle_turns){
                    $temp_bonus = round((($this->battle_turns / $this->counters['battle_turn']) - 1) * 100);
                    $first_event_body .= 'Bonus : +'.$temp_bonus.'% <span style="opacity:0.25;">|</span> ';
                }
                // Else if the user gets a turn PENALTY
                else {
                    $temp_bonus = round((($this->battle_turns / $this->counters['battle_turn']) - 1) * 100) * -1;
                    $first_event_body .= 'Penalty : -'.$temp_bonus.'% <span style="opacity:0.25;">|</span> ';
                }

            }

            // Print out the final reward amounts after mods (points or zenny)
            if (!$force_zenny_rewards){
                $first_event_body .= 'Points : '.number_format($temp_human_rewards['battle_points'], 0, '.', ',').' ';
            } else {
                $first_event_body .= 'Zenny : '.number_format($temp_human_rewards['battle_points'], 0, '.', ',').'z ';
                $_SESSION['GAME']['counters']['battle_zenny'] += $temp_human_rewards['battle_points'];
            }

        }
        // Otherwise if defeated, do nothing
        else {

            // Do nothing for now
            $first_event_body .= 'Points : 0 ';

        }

        // Print the battle complete message
        $event_options = array();
        $event_options['this_header_float'] = 'center';
        $event_options['this_body_float'] = 'center';
        $event_options['this_event_class'] = false;
        $event_options['console_show_this'] = false;
        $event_options['console_show_target'] = false;
        $event_options['console_container_classes'] = 'field_type field_type_event field_type_'.($this->battle_result == 'victory' ? 'nature' : 'flame');
        $this->events_create($target_robot, $this_robot, $first_event_header, $first_event_body, $event_options);

        // Create one final frame for the blank frame
        //$this->events_create(false, false, '', '');

    }

    // Define a public function for triggering battle actions
    public function actions_trigger(&$this_player, &$this_robot, &$target_player, &$target_robot, $this_action, $this_token = ''){
        global $db;

        // Default the return variable to false
        $this_return = false;

        // Reload all variables to ensure values are fresh
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_player->player_load(array('player_id' => $this_player->player_id, 'player_token' => $this_player->player_token));
        $target_player->player_load(array('player_id' => $target_player->player_id, 'player_token' => $target_player->player_token));
        $this_robot->robot_load(array('robot_id' => $this_robot->robot_id, 'robot_token' => $this_robot->robot_token));
        $target_robot->robot_load(array('robot_id' => $target_robot->robot_id, 'robot_token' => $target_robot->robot_token));
        $this_player->update_session();
        $target_robot->update_session();
        $this_robot->update_session();
        $target_robot->update_session();

        // Create the action array in the history object if not exist
        if (!isset($this_player->history['actions'])){
            $this_player->history['actions'] = array();
        }

        // Update the session with recent changes
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_player->update_session();

        // If the target player does not have any robots left
        if ($target_player->counters['robots_active'] == 0){

            // Trigger the battle complete action to update status and result
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_token);

        }


        // Start the battle loop to allow breaking
        $battle_loop = true;
        while ($battle_loop == true && $this->battle_status != 'complete'){

            // If the battle is just starting
            if ($this_action == 'start'){
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                // Ensure this is an actual player
                if ($this_player->player_token != 'player'){

                    /*
                    // Create the enter event for this robot
                    $event_header = $this_player->player_name.'&#39;s '.$this_robot->robot_name;
                    if ($target_player->player_token != 'player'){ $event_body = "{$this_robot->print_robot_name()} enters the battle!<br />"; }
                    else { $event_body = "{$this_robot->print_robot_name()} prepares for battle!<br />"; }
                    $this_robot->robot_frame = 'base';
                    $this_player->player_frame = 'command';
                    $this_robot->robot_position = 'active';
                    if (isset($this_robot->robot_quotes['battle_start'])){
                        $this_robot->robot_frame = 'taunt';
                        $event_body .= '&quot;<em>'.$this_robot->robot_quotes['battle_start'].'</em>&quot;';
                    }
                    $this_robot->update_session();
                    $this_player->update_session();
                    $this->events_create($this_robot, false, $event_header, $event_body, array('canvas_show_target' => false, 'console_show_target' => false));
                    */

                }
                // Otherwise, if the player is empty
                else {

                    // Create the enter event for this robot
                    $event_header = $this_robot->robot_name;
                    $event_body = "{$this_robot->print_robot_name()} wants to fight!<br />";
                    $this_robot->robot_frame = 'defend';
                    $this_robot->robot_frame_styles = '';
                    $this_robot->robot_detail_styles = '';
                    $this_robot->robot_position = 'active';
                    if (isset($this_robot->robot_quotes['battle_start'])){
                        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
                        $event_body .= $this_robot->print_robot_quote('battle_start', $this_find, $this_replace);
                        //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_start']);
                        //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
                    }
                    $this_robot->update_session();
                    $this_player->update_session();
                    $this->events_create($this_robot, false, $event_header, $event_body, array('canvas_show_target' => false, 'console_show_target' => false));

                    // Create an event for this robot teleporting in
                    if ($this_player->counters['robots_active'] == 1){
                        $this_robot->robot_frame = 'taunt';
                        $this_robot->update_session();
                        $this->events_create(false, false, '', '');
                    }
                    $this_robot->robot_frame = 'base';
                    $this_robot->robot_frame_styles = '';
                    $this_robot->robot_detail_styles = '';
                    $this_robot->update_session();

                }

                // Show the player's other robots one by one
                foreach ($this_player->values['robots_active'] AS $key => $info){
                    if (!preg_match('/display:\s?none;/i', $info['robot_frame_styles'])){ continue; }
                    if ($this_robot->robot_id == $info['robot_id']){
                        $this_robot->robot_frame = 'taunt';
                        $this_robot->robot_frame_styles = '';
                        $this_robot->robot_detail_styles = '';
                        $this_robot->update_session();
                        $this->events_create(false, false, '', '');
                        $this_robot->robot_frame = 'base';
                        $this_robot->update_session();
                    } else {
                        $temp_robot = new rpg_robot($this, $this_player, $info);
                        $temp_robot->robot_frame = 'taunt';
                        $temp_robot->robot_frame_styles = '';
                        $temp_robot->robot_detail_styles = '';
                        $temp_robot->update_session();
                        $this->events_create(false, false, '', '');
                        $temp_robot->robot_frame = 'base';
                        $temp_robot->update_session();
                    }
                }

                // Ensure this robot has abilities to loop through
                if (!isset($this_robot->flags['ability_startup']) && !empty($this_robot->robot_abilities)){
                    // Loop through each of this robot's abilities and trigger the start event
                    $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                    foreach ($this_robot->robot_abilities AS $this_key => $this_token){
                        // Define the current ability object using the loaded ability data
                        $temp_abilityinfo = rpg_ability::parse_index_info($temp_abilities_index[$this_token]);
                        $temp_ability = new rpg_ability($this, $this_player, $this_robot, $temp_abilityinfo);
                        // Trigger this abilities start event, if it has one
                        // Update or create this abilities session object
                        $temp_ability->update_session();
                    }
                    // And now update the robot with the flag
                    $this_robot->flags['ability_startup'] = true;
                    $this_robot->update_session();
                }

                // Set this token to the ID and token of the starting robot
                $this_token = $this_robot->robot_id.'_'.$this_robot->robot_token;

                // Return from the battle function with the start results
                $this_return = true;
                break;

            }
            // Else if the player has chosen to use an ability
            elseif ($this_action == 'ability'){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                // Combine into the actions index
                $temp_actions_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');

                // DEFINE ABILITY TOKEN

                // If an ability token was not collected
                if (empty($this_token)){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Collect the ability choice from the robot
                    $temp_token = rpg_robot::robot_choices_abilities(array(
                        'this_battle' => &$this,
                        'this_field' => &$this->battle_field,
                        'this_player' => &$this_player,
                        'this_robot' => &$this_robot,
                        'target_player' => &$target_player,
                        'target_robot' => &$target_robot
                        ));
                    $temp_id = $this->index['abilities'][$temp_token]['ability_id'];//array_search($temp_token, $this_robot->robot_abilities);
                    $temp_id = $this_robot->robot_id.str_pad($temp_id, '3', '0', STR_PAD_LEFT);
                    //$this_token = array('ability_id' => $temp_id, 'ability_token' => $temp_token);
                    $this_token = rpg_ability::parse_index_info($temp_actions_index[$temp_token]);
                    $this_token['ability_id'] = $temp_id;
                }
                // Otherwise, parse the token for data
                else {
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Define the ability choice data for this robot
                    list($temp_id, $temp_token) = explode('_', $this_token);
                    //$this_token = array('ability_id' => $temp_id, 'ability_token' => $temp_token);
                    $this_token = rpg_ability::parse_index_info($temp_actions_index[$temp_token]);
                    $this_token['ability_id'] = $temp_id;
                }

                // If the current robot has been already disabled
                if ($this_robot->robot_status == 'disabled'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Break from this queued action as the robot cannot fight
                    break;
                }

                // Define the current ability object using the loaded ability data
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $this_ability = new rpg_ability($this, $this_player, $this_robot, $this_token);
                // Trigger this robot's ability
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $this_ability->ability_results = $this_robot->trigger_ability($target_robot, $this_ability);

                // Ensure the battle has not completed before triggering the taunt event
                if ($this->battle_status != 'complete'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    // Check to ensure this robot hasn't taunted already
                    if (!isset($this_robot->flags['robot_quotes']['battle_taunt'])
                        && isset($this_robot->robot_quotes['battle_taunt'])
                        && $this_robot->robot_quotes['battle_taunt'] != '...'
                        && $this_ability->ability_results['this_amount'] > 0
                        && $target_robot->robot_status != 'disabled'
                        && $this->critical_chance(3)){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        // Generate this robot's taunt event after dealing damage, which only happens once per battle
                        $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
                        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
                        //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_taunt']);
                        $event_body = ($this_player->player_token != 'player' ? $this_player->print_player_name().'&#39;s ' : '').$this_robot->print_robot_name().' taunts the opponent!<br />';
                        $event_body .= $this_robot->print_robot_quote('battle_taunt', $this_find, $this_replace);
                        //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
                        $this_robot->robot_frame = 'taunt';
                        $target_robot->robot_frame = 'base';
                        $this->events_create($this_robot, $target_robot, $event_header, $event_body, array('console_show_target' => false));
                        $this_robot->robot_frame = 'base';
                        // Create the quote flag to ensure robots don't repeat themselves
                        $this_robot->flags['robot_quotes']['battle_taunt'] = true;
                    }

                }

                // Set this token to the ID and token of the triggered ability
                $this_token = $this_token['ability_id'].'_'.$this_token['ability_token'];

                // Return from the battle function with the used ability
                $this_return = &$this_ability;
                break;

            }
            // Else if the player has chosen to switch
            elseif ($this_action == 'switch'){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                // Collect this player's last action if it exists
                if (!empty($this_player->history['actions'])){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_recent_switches = array_slice($this_player->history['actions'], -5, 5, false);
                    foreach ($this_recent_switches AS $key => $info){
                        if ($info['this_action'] == 'switch' || $info['this_action'] == 'start'){ $this_recent_switches[$key] = $info['this_action_token']; } //$info['this_action_token'];
                        else { unset($this_recent_switches[$key]); }
                    }
                    $this_recent_switches = array_values($this_recent_switches);
                    $this_recent_switches_count = count($this_recent_switches);
                }
                // Otherwise define an empty action
                else {
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_recent_switches = array();
                    $this_recent_switches_count = 0;
                }

                // If the robot token was not collected and this player is NOT on autopilot
                if (empty($this_token) && $this_player->player_side == 'left'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    // Clear any pending actions
                    $this->actions_empty();
                    // Return from the battle function
                    $this_return = true;
                    break;

                }
                // Else If a robot token was not collected and this player IS on autopilot
                elseif (empty($this_token) && $this_player->player_side == 'right'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    // Decide which robot the target should use (random)
                    $active_robot_count = count($this_player->values['robots_active']);
                    if ($active_robot_count == 1){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $this_robotinfo = $this_player->values['robots_active'][0];
                    }
                    elseif ($active_robot_count > 1) {
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $this_last_switch = !empty($this_recent_switches) ? array_slice($this_recent_switches, -1, 1, false) : array('');
                        $this_last_switch = $this_last_switch[0];
                        $this_current_token = $this_robot->robot_id.'_'.$this_robot->robot_token;
                        do {
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            $this_robotinfo = $this_player->values['robots_active'][mt_rand(0, ($active_robot_count - 1))];
                            if ($this_robotinfo['robot_id'] == $this_robot->robot_id ){ continue; }
                            $this_temp_token = $this_robotinfo['robot_id'].'_'.$this_robotinfo['robot_token'];
                            //$this->events_create(false, false, 'DEBUG', '!empty('.$this_last_switch.') && '.$this_temp_token.' == '.$this_last_switch);
                        } while(empty($this_temp_token));
                    }
                    else {
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $this_robotinfo = array('robot_id' => 0, 'robot_token' => 'robot');
                    }
                    //$this->events_create(false, false, 'DEBUG', 'auto switch picked ['.print_r($this_robotinfo['robot_name'], true).'] | recent : ['.preg_replace('#\s+#', ' ', print_r($this_recent_switches, true)).']');
                }
                // Otherwise, parse the token for data
                else {
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    list($temp_id, $temp_token) = explode('_', $this_token);
                    $this_robotinfo = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
                }

                //$this->events_create(false, false, 'DEBUG', 'switch picked ['.print_r($this_robotinfo['robot_token'], true).'] | other : []');

                // Update this player and robot's session data before switching
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $this_player->update_session();
                $this_robot->update_session();

                // Define the switch reason based on if this robot is disabled
                $this_switch_reason = $this_robot->robot_status != 'disabled' ? 'withdrawn' : 'removed';
                //if ($this_robot->robot_position == 'bench'){ $this_switch_reason = 'auto'; }

                /*
                $this->events_create(false, false, 'DEBUG',
                    '$this_switch_reason = '.$this_switch_reason.'<br />'.
                    '$this_player->values[\'current_robot\'] = '.$this_player->values['current_robot'].'<br />'.
                    '$this_player->values[\'current_robot_enter\'] = '.$this_player->values['current_robot_enter'].'<br />'.
                    '');
                */

                // If this robot is being withdrawn on the same turn it entered, return false
                if ($this_player->player_side == 'right' && $this_switch_reason == 'withdrawn' && $this_player->values['current_robot_enter'] == $this->counters['battle_turn']){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Return false to cancel the switch action
                    return false;
                }

                // If the switch reason was removal, make sure this robot stays hidden
                if ($this_switch_reason == 'removed' && $this_player->player_side == 'right'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_robot->flags['hidden'] = true;
                    $this_robot->update_session();
                }

                // Withdraw the player's robot and display an event for it
                if ($this_robot->robot_position != 'bench'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_robot->robot_frame = $this_robot->robot_status != 'disabled' ? 'base' : 'defeat';
                    $this_robot->robot_position = 'bench';
                    $this_player->player_frame = 'base';
                    $this_player->values['current_robot'] = false;
                    $this_player->values['current_robot_enter'] = false;
                    $this_robot->update_session();
                    $this_player->update_session();
                    $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
                    $event_body = $this_robot->print_robot_name().' is '.$this_switch_reason.' from battle!';
                    if ($this_robot->robot_status != 'disabled' && isset($this_robot->robot_quotes['battle_retreat'])){
                        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
                        $event_body .= $this_robot->print_robot_quote('battle_retreat', $this_find, $this_replace);
                        //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_retreat']);
                        //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
                    }
                    // Only show the removed event or the withdraw event if there's more than one robot
                    if ($this_switch_reason == 'removed' || $this_player->counters['robots_active'] > 1){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $this->events_create($this_robot, false, $event_header, $event_body, array('canvas_show_disabled_bench' => $this_robot->robot_id.'_'.$this_robot->robot_token));
                    }
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_robot->update_session();
                }

                // If the switch reason was removal, hide the robot from view
                if ($this_switch_reason == 'removed'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_robot->flags['hidden'] = true;
                    $this_robot->update_session();
                }

                // Ensure all robots have been withdrawn to the bench at this point
                if (!empty($this_player->player_robots)){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    foreach ($this_player->player_robots AS $temp_key => $temp_robotinfo){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $temp_robot = new rpg_robot($this, $this_player, $temp_robotinfo);
                        $temp_robot->robot_position = 'bench';
                        $temp_robot->update_session();
                    }
                }

                // Switch in the player's new robot and display an event for it
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $this_robot->robot_load($this_robotinfo);
                if ($this_robot->robot_position != 'active'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_robot->robot_position = 'active';
                    $this_player->player_frame = 'command';
                    $this_player->values['current_robot'] = $this_robot->robot_string;
                    $this_player->values['current_robot_enter'] = $this->counters['battle_turn'];
                    $this_robot->update_session();
                    $this_player->update_session();
                    $event_header = ($this_player->player_token != 'player' ? $this_player->player_name.'&#39;s ' : '').$this_robot->robot_name;
                    $event_body = "{$this_robot->print_robot_name()} joins the battle!<br />";
                    if (isset($this_robot->robot_quotes['battle_start'])){
                        $this_robot->robot_frame = 'taunt';
                        $this_find = array('{target_player}', '{target_robot}', '{this_player}', '{this_robot}');
                        $this_replace = array($target_player->player_name, $target_robot->robot_name, $this_player->player_name, $this_robot->robot_name);
                        $event_body .= $this_robot->print_robot_quote('battle_start', $this_find, $this_replace);
                        //$this_quote_text = str_replace($this_find, $this_replace, $this_robot->robot_quotes['battle_start']);
                        //$event_body .= '&quot;<em>'.$this_quote_text.'</em>&quot;';
                    }
                    // Only show the enter event if the switch reason was removed or if there is more then one robot
                    if ($this_switch_reason == 'removed' || $this_player->counters['robots_active'] > 1){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $this->events_create($this_robot, false, $event_header, $event_body);
                    }
                }

                // Ensure this robot has abilities to loop through
                if (!isset($this_robot->flags['ability_startup']) && !empty($this_robot->robot_abilities)){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    // Loop through each of this robot's abilities and trigger the start event
                    $temp_abilities_index = $db->get_array_list("SELECT * FROM mmrpg_index_abilities WHERE ability_flag_complete = 1;", 'ability_token');
                    foreach ($this_robot->robot_abilities AS $this_key => $this_token){
                        if (!isset($temp_abilities_index[$this_token])){ continue; }
                        // Define the current ability object using the loaded ability data
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $temp_abilityinfo = rpg_ability::parse_index_info($temp_abilities_index[$this_token]);
                        $temp_ability = new rpg_ability($this, $this_player, $this_robot, $temp_abilityinfo);
                        // Update or create this abilities session object
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $temp_ability->update_session();
                    }
                    // And now update the robot with the flag
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_robot->flags['ability_startup'] = true;
                    $this_robot->update_session();
                }

                // Now we can update the current robot's frame regardless of what happened
                $this_robot->robot_frame = $this_robot->robot_status != 'disabled' ? 'base' : 'defeat';
                $this_robot->update_session();

                // Set this token to the ID and token of the switched robot
                $this_token = $this_robotinfo['robot_id'].'_'.$this_robotinfo['robot_token'];

                //$this->events_create(false, false, 'DEBUG', 'checkpoint ['.$this_token.'] | other : []');

                // Return from the battle function
                $this_return = true;
                break;
            }
            // Else if the player has chosen to scan the target
            elseif ($this_action == 'scan'){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                // Otherwise, parse the token for data
                if (!empty($this_token)){
                    list($temp_id, $temp_token) = explode('_', $this_token);
                    $this_token = array('robot_id' => $temp_id, 'robot_token' => $temp_token);
                }

                // If an ability token was not collected
                if (empty($this_token)){
                    // Decide which robot should be scanned
                    foreach ($target_player->player_robots AS $this_key => $this_robotinfo){
                        if ($this_robotinfo['robot_position'] == 'active'){ $this_token = $this_robotinfo;  }
                    }
                }

                //die('<pre>'.print_r($temp_target_robot_info, true).'</pre>');

                // Create the temporary target player and robot objects
                $temp_target_robot_info = !empty($this->values['robots'][$this_token['robot_id']]) ? $this->values['robots'][$this_token['robot_id']] : array();
                $temp_target_player_info = !empty($this->values['players'][$temp_target_robot_info['player_id']]) ? $this->values['players'][$temp_target_robot_info['player_id']] : array();
                $temp_target_player = new rpg_player($this, $temp_target_player_info);
                $temp_target_robot = new rpg_robot($this, $temp_target_player, $temp_target_robot_info);
                //die('<pre>'.print_r($temp_target_robot, true).'</pre>');

                // Ensure the target robot's frame is set to its base
                $temp_target_robot->robot_frame = 'base';
                $temp_target_robot->update_session();

                // Collect the weakness, resistsance, affinity, and immunity text
                $temp_target_robot_weaknesses = $temp_target_robot->print_robot_weaknesses();
                $temp_target_robot_resistances = $temp_target_robot->print_robot_resistances();
                $temp_target_robot_affinities = $temp_target_robot->print_robot_affinities();
                $temp_target_robot_immunities = $temp_target_robot->print_robot_immunities();

                // Change the target robot's frame to defend base and save
                $temp_target_robot->robot_frame = 'taunt';
                $temp_target_robot->update_session();

                // Now change the target robot's frame is set to its mugshot
                $temp_target_robot->robot_frame = 'taunt'; //taunt';

                $temp_stat_padding_total = 300;
                $temp_stat_counter_total = $temp_target_robot->robot_energy + $temp_target_robot->robot_attack + $temp_target_robot->robot_defense + $temp_target_robot->robot_speed;
                $temp_stat_counter_base_total = $temp_target_robot->robot_base_energy + $temp_target_robot->robot_base_attack + $temp_target_robot->robot_base_defense + $temp_target_robot->robot_base_speed;

                $temp_energy_padding = ceil(($temp_target_robot->robot_energy / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_energy_base_padding = ceil(($temp_target_robot->robot_base_energy / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_energy_base_padding = $temp_energy_base_padding - $temp_energy_padding;

                $temp_attack_padding = ceil(($temp_target_robot->robot_attack / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_attack_base_padding = ceil(($temp_target_robot->robot_base_attack / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_attack_base_padding = $temp_attack_base_padding - $temp_attack_padding;
                if ($temp_attack_padding < 1){ $temp_attack_padding = 0; }
                if ($temp_attack_base_padding < 1){ $temp_attack_base_padding = 0; }

                $temp_defense_padding = ceil(($temp_target_robot->robot_defense / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_defense_base_padding = ceil(($temp_target_robot->robot_base_defense / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_defense_base_padding = $temp_defense_base_padding - $temp_defense_padding;
                if ($temp_defense_padding < 1){ $temp_defense_padding = 0; }
                if ($temp_defense_base_padding < 1){ $temp_defense_base_padding = 0; }

                $temp_speed_padding = ceil(($temp_target_robot->robot_speed / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_speed_base_padding = ceil(($temp_target_robot->robot_base_speed / $temp_stat_counter_base_total) * $temp_stat_padding_total);
                $temp_speed_base_padding = $temp_speed_base_padding - $temp_speed_padding;
                if ($temp_speed_padding < 1){ $temp_speed_padding = 0; }
                if ($temp_speed_base_padding < 1){ $temp_speed_base_padding = 0; }

                // Create an event showing the scanned robot's data
                $event_header = ($temp_target_player->player_token != 'player' ? $temp_target_player->player_name.'&#39;s ' : '').$temp_target_robot->robot_name;
                if (empty($_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned'])){ $event_header .= ' (New!)'; }
                $event_body = '';
                ob_start();
                ?>
                        <table class="full">
                            <colgroup>
                                <col width="20%" />
                                <col width="43%" />
                                <col width="4%" />
                                <col width="13%" />
                                <col width="20%" />
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td class="left">Name  : </td>
                                    <td  class="right"><?= $temp_target_robot->print_robot_number() ?> <?= $temp_target_robot->print_robot_name() ?></td>
                                    <td class="center">&nbsp;</td>
                                    <td class="left">Core : </td>
                                    <td  class="right"><?= $temp_target_robot->print_robot_core() ?></td>
                                </tr>
                                <tr>
                                    <td class="left">Weaknesses : </td>
                                    <td  class="right"><?= !empty($temp_target_robot_weaknesses) ? $temp_target_robot_weaknesses : '<span class="robot_weakness">None</span>' ?></td>
                                    <td class="center">&nbsp;</td>
                                    <td class="left">Energy : </td>
                                    <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_energy / $temp_target_robot->robot_base_energy) * 100).'% | '.$temp_target_robot->robot_energy.' / '.$temp_target_robot->robot_base_energy ?>"data-tooltip-type="robot_type robot_type_energy" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_energy_base_padding ?>px;"><span class="robot_stat robot_type robot_type_energy" style="padding-left: <?= $temp_energy_padding ?>px;"><?= $temp_target_robot->robot_energy ?></span></span></td>
                                </tr>
                                <tr>
                                    <td class="left">Resistances : </td>
                                    <td  class="right"><?= !empty($temp_target_robot_resistances) ? $temp_target_robot_resistances : '<span class="robot_resistance">None</span>' ?></td>
                                    <td class="center">&nbsp;</td>
                                    <td class="left">Attack : </td>
                                    <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_attack / $temp_target_robot->robot_base_attack) * 100).'% | '.$temp_target_robot->robot_attack.' / '.$temp_target_robot->robot_base_attack ?>"data-tooltip-type="robot_type robot_type_attack" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_attack_base_padding ?>px;"><span class="robot_stat robot_type robot_type_attack" style="padding-left: <?= $temp_attack_padding ?>px;"><?= $temp_target_robot->robot_attack ?></span></span></td>
                                </tr>
                                <tr>
                                    <td class="left">Affinities : </td>
                                    <td  class="right"><?= !empty($temp_target_robot_affinities) ? $temp_target_robot_affinities : '<span class="robot_affinity">None</span>' ?></td>
                                    <td class="center">&nbsp;</td>
                                    <td class="left">Defense : </td>
                                    <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_defense / $temp_target_robot->robot_base_defense) * 100).'% | '.$temp_target_robot->robot_defense.' / '.$temp_target_robot->robot_base_defense ?>"data-tooltip-type="robot_type robot_type_defense" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_defense_base_padding ?>px;"><span class="robot_stat robot_type robot_type_defense" style="padding-left: <?= $temp_defense_padding ?>px;"><?= $temp_target_robot->robot_defense ?></span></span></td>
                                </tr>
                                <tr>
                                    <td class="left">Immunities : </td>
                                    <td  class="right"><?= !empty($temp_target_robot_immunities) ? $temp_target_robot_immunities : '<span class="robot_immunity">None</span>' ?></td>
                                    <td class="center">&nbsp;</td>
                                    <td class="left">Speed : </td>
                                    <td  class="right"><span title="<?= ceil(($temp_target_robot->robot_speed / $temp_target_robot->robot_base_speed) * 100).'% | '.$temp_target_robot->robot_speed.' / '.$temp_target_robot->robot_base_speed ?>"data-tooltip-type="robot_type robot_type_speed" data-tooltip-align="right" class="robot_stat robot_type robot_type_empty" style="padding: 0 0 0 <?= $temp_speed_base_padding ?>px;"><span class="robot_stat robot_type robot_type_speed" style="padding-left: <?= $temp_speed_padding ?>px;"><?= $temp_target_robot->robot_speed ?></span></span></td>
                                </tr>
                            </tbody>
                        </table>
                <?
                $event_body .= preg_replace('#\s+#', ' ', trim(ob_get_clean()));
                $this->events_create($temp_target_robot, false, $event_header, $event_body, array('console_container_height' => 2, 'canvas_show_this' => false)); //, 'event_flag_autoplay' => false

                // Ensure the target robot's frame is set to its base
                $temp_target_robot->robot_frame = 'base';
                $temp_target_robot->update_session();

                // Add this robot to the global robot database array
                if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token])){ $_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token] = array('robot_token' => $temp_target_robot->robot_token); }
                if (!isset($_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned'])){ $_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned'] = 0; }
                $_SESSION['GAME']['values']['robot_database'][$temp_target_robot->robot_token]['robot_scanned']++;

                // Set this token to the ID and token of the triggered ability
                $this_token = $this_token['robot_id'].'_'.$this_token['robot_token'];

                // Return from the battle function with the scanned robot
                $this_return = &$this_ability;
                break;

            }

            // Break out of the battle loop by default
            break;
        }

        // Set the hidden flag on this robot if necessary
        if ($this_robot->robot_position == 'bench' && ($this_robot->robot_status == 'disabled' || $this_robot->robot_energy < 1)){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this_robot->flags['apply_disabled_state'] = true;
            $this_robot->flags['hidden'] = true;
            $this_robot->update_session();
        }

        // Set the hidden flag on the target robot if necessary
        if ($target_robot->robot_position == 'bench' && ($target_robot->robot_status == 'disabled' || $target_robot->robot_energy < 1)){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $target_robot->flags['apply_disabled_state'] = true;
            $target_robot->flags['hidden'] = true;
            $target_robot->update_session();
        }

        // If the target player does not have any robots left
        if ($target_player->counters['robots_active'] == 0){

            // Trigger the battle complete action to update status and result
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this->battle_complete_trigger($this_player, $this_robot, $target_player, $target_robot, $this_action, $this_token);

        }

        // Update this player's history object with this action
        $this_player->history['actions'][] = array(
                'this_action' => $this_action,
                'this_action_token' => $this_token
                );

        // Update this battle's session data
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this->update_session();

        // Update this player's session data
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_player->update_session();
        // Update the target player's session data
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $target_player->update_session();

        // Update this robot's session data
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_robot->update_session();
        // Update the target robot's session data
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $target_robot->update_session();

        // Update the current ability's session data
        if (isset($this_ability)){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            $this_ability->update_session();
        }

        // Return the result for this battle function
        return $this_return;

    }

    /**
     * Create a new debug entry in the global battle event queue
     * @param string $file_name
     * @param int $line_number
     * @param string $debug_message
     */
    public function events_debug($file_name, $line_number, $debug_message){
        if (MMRPG_CONFIG_DEBUG_MODE){
            $file_name = basename($file_name);
            $line_number = 'Line '.$line_number;
            $this->events_create(false, false, 'DEBUG | '.$file_name.' | '.$line_number, $debug_message);
        }
    }

    // Define a publicfunction for adding to the event array
    public function events_create($this_robot, $target_robot, $event_header, $event_body, $event_options = array()){

        // Clone or define the event objects
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_battle = $this;
        $this_field = $this->battle_field; //array_slice($this->values['fields'];
        $this_player = false;
        $this_robot = !empty($this_robot) ? $this_robot : false;
        if (!empty($this_robot)){ $this_player = new rpg_player($this, $this->values['players'][$this_robot->player_id]); }
        $target_player = false;
        $target_robot = !empty($target_robot) ? $target_robot : false;
        if (!empty($target_robot)){ $target_player = new rpg_player($this, $this->values['players'][$target_robot->player_id]); }

        // Increment the internal events counter
        if (!isset($this->counters['events'])){ $this->counters['events'] = 1; }
        else { $this->counters['events']++; }

        // Generate the event markup and add it to the array
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this->events[] = $this->events_markup_generate(array(
            'this_battle' => $this_battle,
            'this_field' => $this_field,
            'this_player' => $this_player,
            'this_robot' => $this_robot,
            'target_player' => $target_player,
            'target_robot' => $target_robot,
            'event_header' => $event_header,
            'event_body' => $event_body,
            'event_options' => $event_options
            ));

        // Return the resulting array
        return $this->events;

    }

    // Define a public function for emptying the events array
    public function events_empty(){

        // Empty the internal events array
        $this->events = array();

        // Return the resulting array
        return $this->events;

    }

    // Define a function for generating console message markup
    public function console_markup($eventinfo, $options){
        // Define the console markup string
        $this_markup = '';

        // Ensure this side is allowed to be shown before generating any markup
        if ($options['console_show_this'] != false){

                // Define the necessary text markup for the current player if allowed and exists
            if (!empty($eventinfo['this_player'])){
                // Collect the console data for this player
                $this_player_data = $eventinfo['this_player']->console_markup($options);
            } else {
                // Define empty console data for this player
                $this_player_data = array();
                $options['console_show_this_player'] = false;
            }
            // Define the necessary text markup for the current robot if allowed and exists
            if (!empty($eventinfo['this_robot'])){
                // Collect the console data for this robot
                $this_robot_data = $eventinfo['this_robot']->console_markup($options, $this_player_data);
            } else {
                // Define empty console data for this robot
                $this_robot_data = array();
                $options['console_show_this_robot'] = false;
            }
            // Define the necessary text markup for the current ability if allowed and exists
            if (!empty($options['this_ability'])){
                // Collect the console data for this ability
                $this_ability_data = $options['this_ability']->console_markup($options, $this_player_data, $this_robot_data);
            } else {
                // Define empty console data for this ability
                $this_ability_data = array();
                $options['console_show_this_ability'] = false;
            }
            // Define the necessary text markup for the current star if allowed and exists
            if (!empty($options['this_star'])){
                // Collect the console data for this star
                $this_star_data = $this->star_console_markup($options['this_star'], $this_player_data, $this_robot_data);
                //die('FINALLY : '.implode(' | ', $this_star_data));
            } else {
                // Define empty console data for this star
                $this_star_data = array();
                $options['console_show_this_star'] = false;
            }

            // If no objects would found to display, turn the left side off
            if (empty($options['console_show_this_player'])
                && empty($options['console_show_this_robot'])
                && empty($options['console_show_this_ability'])
                && empty($options['console_show_this_star'])){
                // Automatically set the console option to false
                $options['console_show_this'] = false;
            }

        }
        // Otherwise, if this side is not allowed to be shown at all
        else {

            // Default all of this side's objects to empty arrays
            $this_player_data = array();
            $this_robot_data = array();
            $this_ability_data = array();
            $this_star_data = array();

        }


        // Ensure the target side is allowed to be shown before generating any markup
        if ($options['console_show_target'] != false){

            // Define the necessary text markup for the target player if allowed and exists
            if (!empty($eventinfo['target_player'])){
                // Collect the console data for this player
                $target_player_data = $eventinfo['target_player']->console_markup($options);
            } else {
                // Define empty console data for this player
                $target_player_data = array();
                $options['console_show_target_player'] = false;
            }
            // Define the necessary text markup for the target robot if allowed and exists
            if (!empty($eventinfo['target_robot'])){
                // Collect the console data for this robot
                $target_robot_data = $eventinfo['target_robot']->console_markup($options, $target_player_data);
            } else {
                // Define empty console data for this robot
                $target_robot_data = array();
                $options['console_show_target_robot'] = false;
            }
            // Define the necessary text markup for the target ability if allowed and exists
            if (!empty($options['target_ability'])){
                // Collect the console data for this ability
                $target_ability_data = $options['target_ability']->console_markup($options, $target_player_data, $target_robot_data);
            } else {
                // Define empty console data for this ability
                $target_ability_data = array();
                $options['console_show_target_ability'] = false;
            }

            // If no objects would found to display, turn the right side off
            if (empty($options['console_show_target_player'])
                && empty($options['console_show_target_robot'])
                && empty($options['console_show_target_ability'])){
                // Automatically set the console option to false
                $options['console_show_target'] = false;
            }

        }
        // Otherwise, if the target side is not allowed to be shown at all
        else {

            // Default all of the target side's objects to empty arrays
            $target_player_data = array();
            $target_robot_data = array();
            $target_ability_data = array();

        }

        // Assign player-side based floats for the header and body if not set
        if (empty($options['console_header_float']) && !empty($this_robot_data)){
            $options['console_header_float'] = $this_robot_data['robot_float'];
        }
        if (empty($options['console_body_float']) && !empty($this_robot_data)){
            $options['console_body_float'] = $this_robot_data['robot_float'];
        }

        // Append the generated console markup if not empty
        if (!empty($eventinfo['event_header']) && !empty($eventinfo['event_body'])){

            // Define the container class based on height
            $event_class = 'event ';
            $event_style = '';
            if ($options['console_container_height'] == 1){ $event_class .= 'event_single '; }
            if ($options['console_container_height'] == 2){ $event_class .= 'event_double '; }
            if ($options['console_container_height'] == 3){ $event_class .= 'event_triple '; }
            if (!empty($options['console_container_classes'])){ $event_class .= $options['console_container_classes']; }
            if (!empty($options['console_container_styles'])){ $event_style .= $options['console_container_styles']; }

            // Generate the opening event tag
            $this_markup .= '<div class="'.$event_class.'" style="'.$event_style.'">';

            // Generate this side's markup if allowed
            if ($options['console_show_this'] != false){
                // Append this player's markup if allowed
                if ($options['console_show_this_player'] != false){ $this_markup .= $this_player_data['player_markup']; }
                // Otherwise, append this robot's markup if allowed
                elseif ($options['console_show_this_robot'] != false){ $this_markup .= $this_robot_data['robot_markup']; }
                // Otherwise, append this ability's markup if allowed
                elseif ($options['console_show_this_ability'] != false){ $this_markup .= $this_ability_data['ability_markup']; }
                // Otherwise, append this star's markup if allowed
                elseif ($options['console_show_this_star'] != false){ $this_markup .= $this_star_data['star_markup']; }
            }

            // Generate the target side's markup if allowed
            if ($options['console_show_target'] != false){
                // Append the target player's markup if allowed
                if ($options['console_show_target_player'] != false){ $this_markup .= $target_player_data['player_markup']; }
                // Otherwise, append the target robot's markup if allowed
                elseif ($options['console_show_target_robot'] != false){ $this_markup .= $target_robot_data['robot_markup']; }
                // Otherwise, append the target ability's markup if allowed
                elseif ($options['console_show_target_ability'] != false){ $this_markup .= $target_ability_data['ability_markup']; }
            }

            /*
            $eventinfo['event_body'] .= '<div>';
            $eventinfo['event_body'] .= 'console_show_this_player : '.($options['console_show_this_player'] != false ? 'true : '.$this_player_data['player_markup'] : 'false : -').'<br />';
            $eventinfo['event_body'] .= 'console_show_this_robot : '.($options['console_show_this_robot'] != false ? 'true : '.$this_robot_data['robot_markup'] : 'false : -').'<br />';
            $eventinfo['event_body'] .= 'console_show_this_ability : '.($options['console_show_this_ability'] != false ? 'true : '.$this_ability_data['ability_markup'] : 'false : -').'<br />';
            $eventinfo['event_body'] .= '</div>';
            */

            // Prepend the turn counter to the header if necessary
            if (!empty($this->counters['battle_turn']) && $this->battle_status != 'complete'){ $eventinfo['event_header'] = 'Turn #'.$this->counters['battle_turn'].' : '.$eventinfo['event_header']; }

            // Display the event header and event body
            $this_markup .= '<div class="header header_'.$options['console_header_float'].'">'.$eventinfo['event_header'].'</div>';
            $this_markup .= '<div class="body body_'.$options['console_body_float'].'">'.$eventinfo['event_body'].'</div>';

            // Displat the closing event tag
            $this_markup .= '</div>';

        }

        // Return the generated markup and robot data
        return $this_markup;
    }

    // Define a function for generating canvas scene markup
    public function canvas_markup($eventinfo, $options = array()){
        // Define the console markup string
        $this_markup = '';

        // If this robot was not provided or allowed by the function
        if (empty($eventinfo['this_player']) || empty($eventinfo['this_robot']) || $options['canvas_show_this'] == false){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            // Set both this player and robot to false
            $eventinfo['this_player'] = false;
            $eventinfo['this_robot'] = false;
            // Collect the target player ID if set
            $target_player_id = !empty($eventinfo['target_player']) ? $eventinfo['target_player']->player_id : false;
            // Loop through the players index looking for this player
            foreach ($this->values['players'] AS $this_player_id => $this_playerinfo){
                if (empty($target_player_id) || $target_player_id != $this_player_id){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $eventinfo['this_player'] = new rpg_player($this, $this_playerinfo);
                    break;
                }
            }
            // Now loop through this player's robots looking for an active one
            foreach ($eventinfo['this_player']->player_robots AS $this_key => $this_robotinfo){
                if ($this_robotinfo['robot_position'] == 'active' && $this_robotinfo['robot_status'] != 'disabled'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $eventinfo['this_robot'] = new rpg_robot($this, $eventinfo['this_player'], $this_robotinfo);
                    break;
                }
            }
        }

        // If this robot was targetting itself, set the target to false
        if (!empty($eventinfo['this_robot']) && !empty($eventinfo['target_robot'])){
            if ($eventinfo['this_robot']->robot_id == $eventinfo['target_robot']->robot_id
                || ($eventinfo['this_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id < MMRPG_SETTINGS_TARGET_PLAYERID)
                || ($eventinfo['this_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID && $eventinfo['target_robot']->robot_id >= MMRPG_SETTINGS_TARGET_PLAYERID)
                ){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $eventinfo['target_robot'] = array();
            }
        }

        // If the target robot was not provided or allowed by the function
        if (empty($eventinfo['target_player']) || empty($eventinfo['target_robot']) || $options['canvas_show_target'] == false){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
            // Set both this player and robot to false
            $eventinfo['target_player'] = false;
            $eventinfo['target_robot'] = false;
            // Collect this player ID if set
            $this_player_id = !empty($eventinfo['this_player']) ? $eventinfo['this_player']->player_id : false;
            // Loop through the players index looking for this player
            foreach ($this->values['players'] AS $target_player_id => $target_playerinfo){
                if (empty($this_player_id) || $this_player_id != $target_player_id){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $eventinfo['target_player'] = new rpg_player($this, $target_playerinfo);
                    break;
                }
            }
            // Now loop through the target player's robots looking for an active one
            foreach ($eventinfo['target_player']->player_robots AS $target_key => $target_robotinfo){
                if ($target_robotinfo['robot_position'] == 'active' && $target_robotinfo['robot_status'] != 'disabled'){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $eventinfo['target_robot'] = new rpg_robot($this, $eventinfo['target_player'], $target_robotinfo);
                    break;
                }
            }
        }

        // Collect this player's markup data
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_player_data = $eventinfo['this_player']->canvas_markup($options);
        // Append this player's markup to the main markup array
        $this_markup .= $this_player_data['player_markup'];

        // Loop through and display this player's robots
        if ($options['canvas_show_this_robots'] && !empty($eventinfo['this_player']->player_robots)){
            $num_player_robots = count($eventinfo['this_player']->player_robots);
            foreach ($eventinfo['this_player']->player_robots AS $this_key => $this_robotinfo){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $this_robot = new rpg_robot($this, $eventinfo['this_player'], $this_robotinfo);
                $this_options = $options;
                //if ($this_robot->robot_status == 'disabled' && $this_robot->robot_position == 'bench'){ continue; }
                if (!empty($this_robot->flags['hidden'])){ continue; }
                elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id != $this_robot->robot_id){ $this_options['this_ability'] = false; }
                elseif (!empty($eventinfo['this_robot']->robot_id) && $eventinfo['this_robot']->robot_id == $this_robot->robot_id && $options['canvas_show_this'] != false){ $this_robot->robot_frame =  $eventinfo['this_robot']->robot_frame; }
                $this_robot->robot_key = $this_robot->robot_key !== false ? $this_robot->robot_key : ($this_key > 0 ? $this_key : $num_player_robots);
                $this_robot_data = $this_robot->canvas_markup($this_options, $this_player_data);
                $this_robot_id_token = $this_robot_data['robot_id'].'_'.$this_robot_data['robot_token'];

                // ABILITY OVERLAY STUFF
                if (!empty($this_options['this_ability_results']) && $this_options['this_ability_target'] == $this_robot_id_token){

                    $this_markup .= '<div class="ability_overlay overlay1" data-target="'.$this_options['this_ability_target'].'" data-key="'.$this_robot_data['robot_key'].'" style="z-index: '.(($this_robot_data['robot_position'] == 'active' ? 5050 : (4900 - ($this_robot_data['robot_key'] * 100)))).';">&nbsp;</div>';


                }
                elseif ($this_robot_data['robot_position'] != 'bench' && !empty($this_options['this_ability']) && !empty($options['canvas_show_this_ability'])){

                    $this_markup .= '<div class="ability_overlay overlay2" data-target="'.$this_options['this_ability_target'].'" data-key="'.$this_robot_data['robot_key'].'" style="z-index: 5050;">&nbsp;</div>';

                }
                elseif ($this_robot_data['robot_position'] != 'bench' && !empty($options['canvas_show_this_ability_overlay'])){

                    $this_markup .= '<div class="ability_overlay overlay3" style="z-index: 100;">&nbsp;</div>';

                }

                /*
                if ($this_robot_data['robot_position'] != 'bench' && (!empty($this_options['this_ability']) || !empty($this_options['this_ability_results']))){
                        //$temp_z_index = 5000;
                        if (!empty($this_options['this_ability_results']) && $this_options['this_ability_target'] == $this_robot_id_token){
                            $this_markup .= '<div class="ability_overlay" data-target="'.$this_options['this_ability_target'].'" data-key="'.$this_robot_data['robot_key'].'" style="z-index: '.(4999 - ($this_robot_data['robot_key'] * 100)).';">&nbsp;</div>';
                        } else {
                            $this_markup .= '<div class="ability_overlay" data-target="'.$this_options['this_ability_target'].'">&nbsp;</div>';
                        }
                }
                */

                // RESULTS ANIMATION STUFF
                if (!empty($this_options['this_ability_results']) && $this_options['this_ability_target'] == $this_robot_id_token){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    /*
                     * ABILITY EFFECT OFFSETS
                     * Frame 01 : Energy +
                     * Frame 02 : Energy -
                     * Frame 03 : Attack +
                     * Frame 04 : Attack -
                     * Frame 05 : Defense +
                     * Frame 06 : Defense -
                     * Frame 07 : Speed +
                     * Frame 08 : Speed -
                     */

                    // Define the results data array and populate with basic fields
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_results_data = array();
                    $this_results_data['results_amount_markup'] = '';
                    $this_results_data['results_effect_markup'] = '';

                    // Calculate the results effect canvas offsets
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_results_data['canvas_offset_x'] = ceil($this_robot_data['canvas_offset_x'] - (4 * $this_options['this_ability_results']['total_actions']));
                    $this_results_data['canvas_offset_y'] = ceil($this_robot_data['canvas_offset_y'] + 0);
                    $this_results_data['canvas_offset_z'] = ceil($this_robot_data['canvas_offset_z'] - 20);
                    $temp_size_diff = $this_robot_data['robot_size'] > 80 ? ceil(($this_robot_data['robot_size'] - 80) * 0.5) : 0;
                    $this_results_data['canvas_offset_x'] += $temp_size_diff;
                    if ($this_robot_data['robot_position'] == 'bench' && $this_robot_data['robot_size'] >= 80){
                        $this_results_data['canvas_offset_x'] += ceil($this_robot_data['robot_size'] / 2);
                    }


                    // Define the style and class variables for these results
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $base_image_size = 40;
                    $this_results_data['ability_size'] = $this_robot_data['robot_position'] == 'active' ? ($base_image_size * 2) : $base_image_size;
                    $this_results_data['ability_scale'] = isset($this_robot_data['robot_scale']) ? $this_robot_data['robot_scale'] : ($this_robot_data['robot_position'] == 'active' ? 1 : 0.5 + (((8 - $this_robot_data['robot_key']) / 8) * 0.5));
                    $zoom_size = $base_image_size * 2;
                    $this_results_data['ability_sprite_size'] = ceil($this_results_data['ability_scale'] * $zoom_size);
                    $this_results_data['ability_sprite_width'] = ceil($this_results_data['ability_scale'] * $zoom_size);
                    $this_results_data['ability_sprite_height'] = ceil($this_results_data['ability_scale'] * $zoom_size);
                    $this_results_data['ability_image_width'] = ceil($this_results_data['ability_scale'] * $zoom_size * 10);
                    $this_results_data['ability_image_height'] = ceil($this_results_data['ability_scale'] * $zoom_size);
                    $this_results_data['results_amount_class'] = 'sprite ';
                    $this_results_data['results_amount_canvas_offset_y'] = $this_robot_data['canvas_offset_y'] + 50;
                    $this_results_data['results_amount_canvas_offset_x'] = $this_robot_data['canvas_offset_x'] - 40;
                    $this_results_data['results_amount_canvas_offset_z'] = $this_robot_data['canvas_offset_z'] + 100;
                    if (!empty($this_options['this_ability_results']['total_actions'])){
                        $total_actions = $this_options['this_ability_results']['total_actions'];
                        if ($this_options['this_ability_results']['trigger_kind'] == 'damage'){
                            $this_results_data['results_amount_canvas_offset_y'] -= ceil((1.5 * $total_actions) * $total_actions);
                            $this_results_data['results_amount_canvas_offset_x'] -= $total_actions * 4;
                        } elseif ($this_options['this_ability_results']['trigger_kind'] == 'recovery'){
                            $this_results_data['results_amount_canvas_offset_y'] = $this_robot_data['canvas_offset_y'] + 20;
                            $this_results_data['results_amount_canvas_offset_x'] = $this_robot_data['canvas_offset_x'] - 40;
                            $this_results_data['results_amount_canvas_offset_y'] += ceil((1.5 * $total_actions) * $total_actions);
                            $this_results_data['results_amount_canvas_offset_x'] -= $total_actions * 4;
                        }
                    }
                    $this_results_data['results_amount_canvas_opacity'] = 1.00;
                    if ($this_robot_data['robot_position'] == 'bench'){
                        $this_results_data['results_amount_canvas_offset_x'] += 105; //$this_results_data['results_amount_canvas_offset_x'] * -1;
                        $this_results_data['results_amount_canvas_offset_y'] += 5; //10;
                        $this_results_data['results_amount_canvas_offset_z'] = $this_robot_data['canvas_offset_z'] + 1000;
                        $this_results_data['results_amount_canvas_opacity'] -= 0.10;
                    } else {
                        $this_results_data['canvas_offset_x'] += mt_rand(0, 10); //jitter
                        $this_results_data['canvas_offset_y'] += mt_rand(0, 10); //jitter
                    }
                    $this_results_data['results_amount_style'] = 'bottom: '.$this_results_data['results_amount_canvas_offset_y'].'px; '.$this_robot_data['robot_float'].': '.$this_results_data['results_amount_canvas_offset_x'].'px; z-index: '.$this_results_data['results_amount_canvas_offset_z'].'; opacity: '.$this_results_data['results_amount_canvas_opacity'].'; ';
                    $this_results_data['results_effect_class'] = 'sprite sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].' ability_status_active ability_position_active '; //sprite_'.$this_robot_data['robot_size'].'x'.$this_robot_data['robot_size'].'
                    $this_results_data['results_effect_style'] = 'z-index: '.$this_results_data['canvas_offset_z'].'; '.$this_robot_data['robot_float'].': '.$this_results_data['canvas_offset_x'].'px; bottom: '.$this_results_data['canvas_offset_y'].'px; background-image: url(images/abilities/ability-results/sprite_'.$this_robot_data['robot_direction'].'_80x80.png?'.MMRPG_CONFIG_CACHE_DATE.'); ';

                    // Ensure a damage/recovery trigger has been sent and actual damage/recovery was done
                    if (!empty($this_options['this_ability_results']['this_amount'])
                        && in_array($this_options['this_ability_results']['trigger_kind'], array('damage', 'recovery'))){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                        // Define the results effect index
                        $this_results_data['results_effect_index'] = array();
                        // Check if the results effect index was already generated
                        if (!empty($this->index['results_effects'])){
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            // Collect the results effect index from the battle index
                            $this_results_data['results_effect_index'] = $this->index['results_effects'];
                        }
                        // Otherwise, generate the results effect index
                        else {
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            // Define the results effect index for quick programatic lookups
                            $this_results_data['results_effect_index']['recovery']['energy'] = '00';
                            $this_results_data['results_effect_index']['damage']['energy'] = '01';
                            $this_results_data['results_effect_index']['recovery']['attack'] = '02';
                            $this_results_data['results_effect_index']['damage']['attack'] = '03';
                            $this_results_data['results_effect_index']['recovery']['defense'] = '04';
                            $this_results_data['results_effect_index']['damage']['defense'] = '05';
                            $this_results_data['results_effect_index']['recovery']['speed'] = '06';
                            $this_results_data['results_effect_index']['damage']['speed'] = '07';
                            $this_results_data['results_effect_index']['recovery']['weapons'] = '04';
                            $this_results_data['results_effect_index']['damage']['weapons'] = '05';
                            $this_results_data['results_effect_index']['recovery']['experience'] = '10';
                            $this_results_data['results_effect_index']['damage']['experience'] = '10';
                            $this_results_data['results_effect_index']['recovery']['level'] = '10';
                            $this_results_data['results_effect_index']['damage']['level'] = '10';
                            $this->index['results_effects'] = $this_results_data['results_effect_index'];
                        }


                        //$this_results_data['results_amount_markup'] .= '<div class="debug">'.preg_replace('#\s+#', ' ', print_r($this_options['this_ability_target'], true)).' = '.($this_robot_data['robot_id'].'_'.$this_robot_data['robot_token']).'</div>';
                        //$this_results_data['results_amount_markup'] .= '<div class="debug">this_ability_target = '.$this_options['this_ability_target'].' | robot-id_robot-token = '.($this_robot_data['robot_id'].'_'.$this_robot_data['robot_token']).'</div>';

                        // Check if a damage trigger was sent with the ability results
                        if ($this_options['this_ability_results']['trigger_kind'] == 'damage'){
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                            // Append the ability damage kind to the class
                            $this_results_data['results_amount_class'] .= 'ability_damage ability_damage_'.$this_options['this_ability_results']['damage_kind'].' ';
                            if (!empty($this_options['this_ability_results']['flag_resistance'])){ $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_low '; }
                            elseif (!empty($this_options['this_ability_results']['flag_weakness']) || !empty($this_options['this_ability_results']['flag_critical'])){ $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_high '; }
                            else { $this_results_data['results_amount_class'] .= 'ability_damage_'.$this_options['this_ability_results']['damage_kind'].'_base '; }
                            $frame_number = $this_results_data['results_effect_index']['damage'][$this_options['this_ability_results']['damage_kind']];
                            $frame_int = (int)$frame_number;
                            $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data['ability_sprite_size']) : 0;
                            $frame_position = $frame_int;
                            if ($frame_position === false){ $frame_position = 0; }
                            $frame_background_offset = -1 * ceil(($this_results_data['ability_sprite_size'] * $frame_position));
                            $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].'_'.$frame_number.' ';
                            $this_results_data['results_effect_style'] .= 'width: '.$this_results_data['ability_sprite_size'].'px; height: '.$this_results_data['ability_sprite_size'].'px; background-size: '.$this_results_data['ability_image_width'].'px '.$this_results_data['ability_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';
                            // Append the final damage results markup to the markup array
                            $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'">-'.$this_options['this_ability_results']['this_amount'].'</div>';
                            $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'">-'.$this_options['this_ability_results']['damage_kind'].'</div>';

                        }
                        // Check if a recovery trigger was sent with the ability results
                        elseif ($this_options['this_ability_results']['trigger_kind'] == 'recovery'){
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                            // Append the ability recovery kind to the class
                            $this_results_data['results_amount_class'] .= 'ability_recovery ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].' ';
                            if (!empty($this_options['this_ability_results']['flag_resistance'])){ $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_low '; }
                            elseif (!empty($this_options['this_ability_results']['flag_affinity']) || !empty($this_options['this_ability_results']['flag_critical'])){ $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_high '; }
                            else { $this_results_data['results_amount_class'] .= 'ability_recovery_'.$this_options['this_ability_results']['recovery_kind'].'_base '; }
                            $frame_number = $this_results_data['results_effect_index']['recovery'][$this_options['this_ability_results']['recovery_kind']];
                            $frame_int = (int)$frame_number;
                            $frame_offset = $frame_int > 0 ? '-'.($frame_int * $this_results_data['ability_size']) : 0;
                            $frame_position = $frame_int;
                            if ($frame_position === false){ $frame_position = 0; }
                            $frame_background_offset = -1 * ceil(($this_results_data['ability_sprite_size'] * $frame_position));
                            $this_results_data['results_effect_class'] .= 'sprite_'.$this_results_data['ability_sprite_size'].'x'.$this_results_data['ability_sprite_size'].'_'.$frame_number.' ';
                            $this_results_data['results_effect_style'] .= 'width: '.$this_results_data['ability_sprite_size'].'px; height: '.$this_results_data['ability_sprite_size'].'px; background-size: '.$this_results_data['ability_image_width'].'px '.$this_results_data['ability_image_height'].'px; background-position: '.$frame_background_offset.'px 0; ';
                            // Append the final recovery results markup to the markup array
                            $this_results_data['results_amount_markup'] .= '<div class="'.$this_results_data['results_amount_class'].'" style="'.$this_results_data['results_amount_style'].'">+'.$this_options['this_ability_results']['this_amount'].'</div>';
                            $this_results_data['results_effect_markup'] .= '<div class="'.$this_results_data['results_effect_class'].'" style="'.$this_results_data['results_effect_style'].'">+'.$this_options['this_ability_results']['recovery_kind'].'</div>';

                        }

                    }

                    // Append this result's markup to the main markup array
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_markup .= $this_results_data['results_amount_markup'];
                    $this_markup .= $this_results_data['results_effect_markup'];

                }

                // ATTACHMENT ANIMATION STUFF
                if (!empty($this_robot->robot_attachments)){

                    // Loop through each attachment and process it
                    foreach ($this_robot->robot_attachments AS $attachment_token => $attachment_info){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                        // If this is an ability attachment
                        if ($attachment_info['class'] == 'ability'){
                            // Create the temporary ability object using the provided data and generate its markup data
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            $this_ability = new rpg_ability($this, $eventinfo['this_player'], $this_robot, $attachment_info);
                            // Define this ability data array and generate the markup data
                            $this_attachment_options = $this_options;
                            $this_attachment_options['data_sticky'] = !empty($this_options['sticky']) || !empty($attachment_info['sticky']) ? true : false;
                            $this_attachment_options['data_type'] = 'attachment';
                            $this_attachment_options['data_debug'] = ''; //$attachment_token;
                            $this_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $this_ability->ability_image;
                            $this_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $this_ability->ability_frame;
                            $this_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $this_ability->ability_frame_span;
                            $this_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $this_ability->ability_frame_animate;
                            $attachment_frame_count = !empty($this_attachment_options['ability_frame_animate']) ? sizeof($this_attachment_options['ability_frame_animate']) : sizeof($this_attachment_options['ability_frame']);
                            $temp_event_frame = $this->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($this_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $this_attachment_options['ability_frame'] = $this_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
                            $this_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $this_ability->ability_frame_offset;
                            $this_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $this_ability->ability_frame_styles;
                            $this_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $this_ability->ability_frame_classes;
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            $this_ability_data = $this_ability->canvas_markup($this_attachment_options, $this_player_data, $this_robot_data);
                            // Append this ability's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $this_robot->robot_frame_styles)){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $this_markup .= $this_ability_data['ability_markup'];
                            }
                        }

                    }

                }

                // ABILITY ANIMATION STUFF
                if (/*true //$this_robot_data['robot_id'] == $this_options['this_ability_target']
                    && $this_robot_data['robot_position'] != 'bench'
                    &&*/ !empty($this_options['this_ability'])
                    && !empty($options['canvas_show_this_ability'])){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    // Define the ability data array and generate markup data
                    $attachment_options['data_type'] = 'ability';
                    $this_ability_data = $this_options['this_ability']->canvas_markup($this_options, $this_player_data, $this_robot_data);

                    // Display the ability's mugshot sprite
                    if (empty($this_options['this_ability_results']['total_actions'])){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                        $this_mugshot_markup_left = '<div class="sprite ability_icon ability_icon_left" style="background-image: url(images/abilities/'.(!empty($this_options['this_ability']->ability_image) ? $this_options['this_ability']->ability_image : $this_options['this_ability']->ability_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_options['this_ability']->ability_name.'</div>';
                        $this_mugshot_markup_right = '<div class="sprite ability_icon ability_icon_right" style="background-image: url(images/abilities/'.(!empty($this_options['this_ability']->ability_image) ? $this_options['this_ability']->ability_image : $this_options['this_ability']->ability_token).'/icon_'.$this_robot_data['robot_direction'].'_40x40.png?'.MMRPG_CONFIG_CACHE_DATE.');">'.$this_options['this_ability']->ability_name.'</div>';
                        $this_markup .=  '<div class="'.$this_ability_data['ability_markup_class'].' canvas_ability_details ability_type ability_type_'.(!empty($this_options['this_ability']->ability_type) ? $this_options['this_ability']->ability_type : 'none').(!empty($this_options['this_ability']->ability_type2) ? '_'.$this_options['this_ability']->ability_type2 : '').'" style="">'.$this_mugshot_markup_left.'<div class="ability_name" style="">'.$this_ability_data['ability_title'].'</div>'.$this_mugshot_markup_right.'</div>';
                    }

                    // Append this ability's markup to the main markup array
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                    $this_markup .= $this_ability_data['ability_markup'];

                }

                // Append this robot's markup to the main markup array
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $this_markup .= $this_robot_data['robot_markup'];

            }
        }

        // Collect the target player's markup data
        $target_player_data = $eventinfo['target_player']->canvas_markup($options);
        // Append the target player's markup to the main markup array
        $this_markup .= $target_player_data['player_markup'];

        // Loop through and display the target player's robots
        if ($options['canvas_show_target_robots'] && !empty($eventinfo['target_player']->player_robots)){
            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

            // Count the number of robots on the target's side of the field
            $num_player_robots = count($eventinfo['target_player']->player_robots);

            // Loop through each target robot and generate it's markup
            foreach ($eventinfo['target_player']->player_robots AS $target_key => $target_robotinfo){
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                // Create the temporary target robot ovject
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $target_robot = new rpg_robot($this, $eventinfo['target_player'], $target_robotinfo);
                $target_options = $options;
                //if ($target_robot->robot_status == 'disabled' && $target_robot->robot_position == 'bench'){ continue; }
                if (!empty($target_robot->flags['hidden'])){ continue; }
                elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id != $target_robot->robot_id){ $target_options['this_ability'] = false;  }
                elseif (!empty($eventinfo['target_robot']->robot_id) && $eventinfo['target_robot']->robot_id == $target_robot->robot_id && $options['canvas_show_target'] != false){ $target_robot->robot_frame =  $eventinfo['target_robot']->robot_frame; }
                $target_robot->robot_key = $target_robot->robot_key !== false ? $target_robot->robot_key : ($target_key > 0 ? $target_key : $num_player_robots);
                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                $target_robot_data = $target_robot->canvas_markup($target_options, $target_player_data);

                // ATTACHMENT ANIMATION STUFF
                if (!empty($target_robot->robot_attachments)){
                    //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                    // Loop through each attachment and process it
                    foreach ($target_robot->robot_attachments AS $attachment_token => $attachment_info){
                        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

                        // If this is an ability attachment
                        if ($attachment_info['class'] == 'ability'){
                            // Create the target's temporary ability object using the provided data
                            //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                            $target_ability = new rpg_ability($this, $eventinfo['target_player'], $target_robot, $attachment_info);
                            // Define this ability data array and generate the markup data
                            $target_attachment_options = $target_options;
                            $target_attachment_options['sticky'] = isset($attachment_info['sticky']) ? $attachment_info['sticky'] : false;
                            $target_attachment_options['data_sticky'] = $target_attachment_options['sticky'];
                            $target_attachment_options['data_type'] = 'attachment';
                            $target_attachment_options['data_debug'] = ''; //$attachment_token;
                            $target_attachment_options['ability_image'] = isset($attachment_info['ability_image']) ? $attachment_info['ability_image'] : $target_ability->ability_image;
                            $target_attachment_options['ability_frame'] = isset($attachment_info['ability_frame']) ? $attachment_info['ability_frame'] : $target_ability->ability_frame;
                            $target_attachment_options['ability_frame_span'] = isset($attachment_info['ability_frame_span']) ? $attachment_info['ability_frame_span'] : $target_ability->ability_frame_span;
                            $target_attachment_options['ability_frame_animate'] = isset($attachment_info['ability_frame_animate']) ? $attachment_info['ability_frame_animate'] : $target_ability->ability_frame_animate;
                            $attachment_frame_key = 0;
                            $attachment_frame_count = sizeof($target_attachment_options['ability_frame_animate']);
                            $temp_event_frame = $this->counters['event_frames'];
                            if ($temp_event_frame == 1 || $attachment_frame_count == 1){ $attachment_frame_key = 0;  }
                            elseif ($temp_event_frame < $attachment_frame_count){ $attachment_frame_key = $temp_event_frame; }
                            elseif ($attachment_frame_count > 0 && $temp_event_frame >= $attachment_frame_count){ $attachment_frame_key = $temp_event_frame % $attachment_frame_count; }
                            if (isset($target_attachment_options['ability_frame_animate'][$attachment_frame_key])){ $target_attachment_options['ability_frame'] = $target_attachment_options['ability_frame_animate'][$attachment_frame_key]; }
                            else { $target_attachment_options['ability_frame'] = 0; }
                            $target_attachment_options['ability_frame_offset'] = isset($attachment_info['ability_frame_offset']) ? $attachment_info['ability_frame_offset'] : $target_ability->ability_frame_offset;
                            $target_attachment_options['ability_frame_styles'] = isset($attachment_info['ability_frame_styles']) ? $attachment_info['ability_frame_styles'] : $target_ability->ability_frame_styles;
                            $target_attachment_options['ability_frame_classes'] = isset($attachment_info['ability_frame_classes']) ? $attachment_info['ability_frame_classes'] : $target_ability->ability_frame_classes;
                            $target_ability_data = $target_ability->canvas_markup($target_attachment_options, $target_player_data, $target_robot_data);
                            // Append this target's ability's markup to the main markup array
                            if (!preg_match('/display:\s?none;/i', $target_robot->robot_frame_styles)){
                                //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
                                $this_markup .= $target_ability_data['ability_markup'];
                            }
                        }

                    }

                }

                $this_markup .= $target_robot_data['robot_markup'];

            }

        }

        // Append the field multipliers to the canvas markup
        if (!empty($this->battle_field->field_multipliers)){
            $temp_multipliers = $this->battle_field->field_multipliers;
            asort($temp_multipliers);
            $temp_multipliers = array_reverse($temp_multipliers, true);
            $temp_multipliers_count = count($temp_multipliers);
            $this_special_types = array('experience', 'damage', 'recovery', 'items');
            $multiplier_markup_left = '';
            $multiplier_markup_right = '';
            foreach ($temp_multipliers AS $this_type => $this_multiplier){
                if ($this_type == 'experience' && !empty($_SESSION['GAME']['DEMO'])){ continue; }
                if ($this_multiplier == 1){ continue; }
                if ($this_multiplier < MMRPG_SETTINGS_MULTIPLIER_MIN){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MIN; }
                elseif ($this_multiplier > MMRPG_SETTINGS_MULTIPLIER_MAX){ $this_multiplier = MMRPG_SETTINGS_MULTIPLIER_MAX; }
                $temp_name = $this_type != 'none' ? ucfirst($this_type) : 'Neutral';
                $temp_number = number_format($this_multiplier, 1);
                $temp_title = $temp_name.' x '.$temp_number;
                if ($temp_multipliers_count >= 8){ $temp_name = substr($temp_name, 0, 2); }
                $temp_markup = '<span title="'.$temp_title.'" data-tooltip-align="center" class="field_multiplier field_multiplier_'.$this_type.' field_multiplier_count_'.$temp_multipliers_count.' field_type field_type_'.$this_type.'"><span class="text"><span class="type">'.$temp_name.' </span><span class="cross">x</span><span class="number"> '.$temp_number.'</span></span></span>';
                if (in_array($this_type, $this_special_types)){ $multiplier_markup_left .= $temp_markup; }
                else { $multiplier_markup_right .= $temp_markup; }
            }
            if (!empty($multiplier_markup_left) || !empty($multiplier_markup_right)){
                $this_markup .= '<div class="canvas_overlay_footer"><strong class="overlay_label">Field Multipliers</strong><span class="overlay_multiplier_count_'.$temp_multipliers_count.'">'.$multiplier_markup_left.$multiplier_markup_right.'</div></div>';
            }

        }


        //die($this_markup);

        // If this battle is over, display the mission complete/failed result
        if ($this->battle_status == 'complete'){
            if ($this->battle_result == 'victory'){
                $result_text = 'Mission Complete!';
                $result_class = 'nature';
            }
            elseif ($this->battle_result == 'defeat') {
                $result_text = 'Mission Failure&hellip;';
                $result_class = 'flame';
            }
            if (!empty($this_markup) && $this->battle_status == 'complete' || $this->battle_result == 'defeat'){
                $this_mugshot_markup_left = '<div class="sprite ability_icon ability_icon_left">&nbsp;</div>';
                $this_mugshot_markup_right = '<div class="sprite ability_icon ability_icon_right">&nbsp;</div>';
                //$this_markup = '<div>test</div>'.$this_markup;
                $this_markup =  '<div class="sprite canvas_ability_details ability_type ability_type_'.$result_class.'">'.$this_mugshot_markup_left.'<div class="ability_name">'.$result_text.'</div>'.$this_mugshot_markup_right.'</div>'.$this_markup;
            }
        }

        // Return the generated markup and robot data
        return $this_markup;

    }

    // Define a public function for calculating canvas markup offsets
    public function canvas_markup_offset($sprite_key, $sprite_position, $sprite_size){

        // Define the data array to be returned later
        $this_data = array();

        // Define the base canvas offsets for this sprite
        $this_data['canvas_offset_x'] = 165;
        $this_data['canvas_offset_y'] = 55;
        $this_data['canvas_offset_z'] = $sprite_position == 'active' ? 5100 : 4900;
        $this_data['canvas_scale'] = $sprite_position == 'active' ? 1 : 0.5 + (((8 - $sprite_key) / 8) * 0.5);

        // If the robot is on the bench, calculate position offsets based on key
        if ($sprite_position == 'bench'){
            $this_data['canvas_offset_z'] -= 100 * $sprite_key;
            $position_modifier = ($sprite_key + 1) / 8;
            $position_modifier_2 = 1 - $position_modifier;
            $temp_seed_1 = 40; //$sprite_size;
            $temp_seed_2 = 20; //ceil($sprite_size / 2);
            $this_data['canvas_offset_x'] = (-1 * $temp_seed_2) + ceil(($sprite_key + 1) * ($temp_seed_1 + 2)) - ceil(($sprite_key + 1) * $temp_seed_2);
            //if ($sprite_size > 40){ $this_data['canvas_offset_x'] -= 40; }
            //if ($sprite_size > 40){ $this_data['canvas_offset_x'] = ceil($this_data['canvas_offset_x'] / 4); }
            $temp_seed_1 = $sprite_size;
            $temp_seed_2 = ceil($sprite_size / 2);
            $this_data['canvas_offset_y'] = ($temp_seed_1 + 6) + ceil(($sprite_key + 1) * 14) - ceil(($sprite_key + 1) * 7) - ($sprite_size - 40);
            $temp_seed_3 = 0;
            if ($sprite_key == 0){ $temp_seed_3 = -10; }
            elseif ($sprite_key == 1){ $temp_seed_3 = 0; }
            elseif ($sprite_key == 2){ $temp_seed_3 = 10; }
            elseif ($sprite_key == 3){ $temp_seed_3 = 20; }
            elseif ($sprite_key == 4){ $temp_seed_3 = 30; }
            elseif ($sprite_key == 5){ $temp_seed_3 = 40; }
            elseif ($sprite_key == 6){ $temp_seed_3 = 50; }
            elseif ($sprite_key == 7){ $temp_seed_3 = 60; }
            if ($sprite_size > 40){ $temp_seed_3 -= ceil(40 * $this_data['canvas_scale']); }
            //$temp_seed_3 = ceil($temp_seed_3 * 0.5);
            $this_data['canvas_offset_x'] += $temp_seed_3;
            $this_data['canvas_offset_x'] += 20;
        }
        // Otherwise, if the robot is in active position
        elseif ($sprite_position == 'active'){
            if ($sprite_size > 80){
                $this_data['canvas_offset_x'] -= 60;
            }
        }

        // Return the generated canvas data for this robot
        return $this_data;

    }

    // Define a public function for generating event markup
    public function events_markup_generate($eventinfo){
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }

        // Create the frames counter if not exists
        if (!isset($this->counters['event_frames'])){ $this->counters['event_frames'] = 0; }

        // Define defaults for event options
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $options = array();
        $options['event_flag_autoplay'] = isset($eventinfo['event_options']['event_flag_autoplay']) ? $eventinfo['event_options']['event_flag_autoplay'] : true;
        $options['event_flag_victory'] = isset($eventinfo['event_options']['event_flag_victory']) ? $eventinfo['event_options']['event_flag_victory'] : false;
        $options['event_flag_defeat'] = isset($eventinfo['event_options']['event_flag_defeat']) ? $eventinfo['event_options']['event_flag_defeat'] : false;
        $options['console_container_height'] = isset($eventinfo['event_options']['console_container_height']) ? $eventinfo['event_options']['console_container_height'] : 1;
        $options['console_container_classes'] = isset($eventinfo['event_options']['console_container_classes']) ? $eventinfo['event_options']['console_container_classes'] : '';
        $options['console_container_styles'] = isset($eventinfo['event_options']['console_container_styles']) ? $eventinfo['event_options']['console_container_styles'] : '';
        $options['console_header_float'] = isset($eventinfo['event_options']['this_header_float']) ? $eventinfo['event_options']['this_header_float'] : '';
        $options['console_body_float'] = isset($eventinfo['event_options']['this_body_float']) ? $eventinfo['event_options']['this_body_float'] : '';
        $options['console_show_this'] = isset($eventinfo['event_options']['console_show_this']) ? $eventinfo['event_options']['console_show_this'] : true;
        $options['console_show_this_player'] = isset($eventinfo['event_options']['console_show_this_player']) ? $eventinfo['event_options']['console_show_this_player'] : false;
        $options['console_show_this_robot'] = isset($eventinfo['event_options']['console_show_this_robot']) ? $eventinfo['event_options']['console_show_this_robot'] : true;
        $options['console_show_this_ability'] = isset($eventinfo['event_options']['console_show_this_ability']) ? $eventinfo['event_options']['console_show_this_ability'] : false;
        $options['console_show_this_star'] = isset($eventinfo['event_options']['console_show_this_star']) ? $eventinfo['event_options']['console_show_this_star'] : false;
        $options['console_show_target'] = isset($eventinfo['event_options']['console_show_target']) ? $eventinfo['event_options']['console_show_target'] : true;
        $options['console_show_target_player'] = isset($eventinfo['event_options']['console_show_target_player']) ? $eventinfo['event_options']['console_show_target_player'] : true;
        $options['console_show_target_robot'] = isset($eventinfo['event_options']['console_show_target_robot']) ? $eventinfo['event_options']['console_show_target_robot'] : true;
        $options['console_show_target_ability'] = isset($eventinfo['event_options']['console_show_target_ability']) ? $eventinfo['event_options']['console_show_target_ability'] : true;
        $options['canvas_show_this'] = isset($eventinfo['event_options']['canvas_show_this']) ? $eventinfo['event_options']['canvas_show_this'] : true;
        $options['canvas_show_this_robots'] = isset($eventinfo['event_options']['canvas_show_this_robots']) ? $eventinfo['event_options']['canvas_show_this_robots'] : true;
        $options['canvas_show_this_ability'] = isset($eventinfo['event_options']['canvas_show_this_ability']) ? $eventinfo['event_options']['canvas_show_this_ability'] : true;
        $options['canvas_show_this_ability_overlay'] = isset($eventinfo['event_options']['canvas_show_this_ability_overlay']) ? $eventinfo['event_options']['canvas_show_this_ability_overlay'] : false;
        $options['canvas_show_target'] = isset($eventinfo['event_options']['canvas_show_target']) ? $eventinfo['event_options']['canvas_show_target'] : true;
        $options['canvas_show_target_robots'] = isset($eventinfo['event_options']['canvas_show_target_robots']) ? $eventinfo['event_options']['canvas_show_target_robots'] : true;
        $options['canvas_show_target_ability'] = isset($eventinfo['event_options']['canvas_show_target_ability']) ? $eventinfo['event_options']['canvas_show_target_ability'] : true;
        $options['this_ability'] = isset($eventinfo['event_options']['this_ability']) ? $eventinfo['event_options']['this_ability'] : false;
        $options['this_ability_target'] = isset($eventinfo['event_options']['this_ability_target']) ? $eventinfo['event_options']['this_ability_target'] : false;
        $options['this_ability_target_key'] = isset($eventinfo['event_options']['this_ability_target_key']) ? $eventinfo['event_options']['this_ability_target_key'] : 0;
        $options['this_ability_target_position'] = isset($eventinfo['event_options']['this_ability_target_position']) ? $eventinfo['event_options']['this_ability_target_position'] : 'active';
        $options['this_ability_results'] = isset($eventinfo['event_options']['this_ability_results']) ? $eventinfo['event_options']['this_ability_results'] : false;
        $options['this_star'] = isset($eventinfo['event_options']['this_star']) ? $eventinfo['event_options']['this_star'] : false;
        $options['this_player_image'] = isset($eventinfo['event_options']['this_player_image']) ? $eventinfo['event_options']['this_player_image'] : 'sprite';
        $options['this_robot_image'] = isset($eventinfo['event_options']['this_robot_image']) ? $eventinfo['event_options']['this_robot_image'] : 'sprite';
        $options['this_ability_image'] = isset($eventinfo['event_options']['this_ability_image']) ? $eventinfo['event_options']['this_ability_image'] : 'sprite';

        // Define the variable to collect markup
        $this_markup = array();

        // Generate the event flags markup
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $event_flags = array();
        //$event_flags['testing'] = true;
        $event_flags['autoplay'] = $options['event_flag_autoplay'];
        $event_flags['victory'] = $options['event_flag_victory'];
        $event_flags['defeat'] = $options['event_flag_defeat'];
        $this_markup['flags'] = json_encode($event_flags);

        // Generate the console message markup
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_markup['console'] = $this->console_markup($eventinfo, $options);

        // Generate the canvas scene markup
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_markup['canvas'] = $this->canvas_markup($eventinfo, $options);

        // Generate the jSON encoded event data markup
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this_markup['data'] = array();
        //$this_markup['data']['this_battle'] = $eventinfo['this_battle']->export_array();
        $this_markup['data']['this_battle'] = '';
        $this_markup['data']['this_field'] = '';
        $this_markup['data']['this_player'] = ''; //!empty($eventinfo['this_player']) ? $eventinfo['this_player']->export_array() : false;
        $this_markup['data']['this_robot'] = ''; //!empty($eventinfo['this_robot']) ? $eventinfo['this_robot']->export_array() : false;
        $this_markup['data']['target_player'] = ''; //!empty($eventinfo['target_player']) ? $eventinfo['target_player']->export_array() : false;
        $this_markup['data']['target_robot'] = ''; //!empty($eventinfo['target_robot']) ? $eventinfo['target_robot']->export_array() : false;
        $this_markup['data'] = json_encode($this_markup['data']);

        // Increment this battle's frames counter
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        $this->counters['event_frames'] += 1;
        $this->update_session();

        // Return the generated event markup
        //if (MMRPG_CONFIG_DEBUG_MODE){ mmrpg_debug_checkpoint(__FILE__, __LINE__);  }
        return $this_markup;

    }

    // Define a public function for collecting event markup
    public function events_markup_collect(){

        // Return the events markup array
        return $this->events;

    }

    // Define a function for calculating the amount of BATTLE POINTS a player gets in battle
    public function calculate_battle_points($this_player, $base_points = 0, $base_turns = 0){

        // Calculate the number of turn points for this player using the base amounts
        $this_base_points = $base_points;
        if ($this->counters['battle_turn'] < $base_turns
            || $this->counters['battle_turn'] > $base_turns){
            //$this_half_points = $base_points * 0.10;
            //$this_turn_points = ceil($this_half_points * ($base_turns / $this->counters['battle_turn']));
            $this_base_points = ceil($this_base_points * ($base_turns / $this->counters['battle_turn']));
        }

        //$this_battle_points = $this_base_points + $this_turn_points + $this_stat_points;
        $this_battle_points = $this_base_points;

        // Prevent players from loosing points
        if ($this_battle_points == 0){ $this_battle_points = 1; }
        elseif ($this_battle_points < 0){ $this_battle_points = -1 * $this_battle_points; }


        // Return the calculated battle points
        return $this_battle_points;

    }

    // Define a function for returning a weighted random chance
    public function weighted_chance($values, $weights = array(), $debug = ''){

        /*
        $debug2 = array();
        foreach ($values AS $k => $v){ $debug2[$v] = $weights[$k]; }
        $this->events_create(false, false, 'DEBUG', trim(preg_replace('/\s+/', ' ', (
            (!empty($debug) ? '$debug:'.$debug.'<br />' : '').
            '$values/weights:'.nl2br(print_r($debug2, true)).'<br />'.
            ''
            ))));
        */

        // Count the number of values passed
        $value_amount = count($values);

        // If no weights have been defined, auto-generate
        if (empty($weights)){
            $weights = array();
            for ($i = 0; $i < $value_amount; $i++){
                $weights[] = 1;
            }
        }

        // Calculate the sum of all weights
        $weight_sum = array_sum($weights);

        // Define the two counter variables
        $value_counter = 0;
        $weight_counter = 0;

        // Randomly generate a number from zero to the sum of weights
        $random_number = mt_rand(0, array_sum($weights));
        while($value_counter < $value_amount){
            $weight_counter += $weights[$value_counter];
            if ($weight_counter >= $random_number){ break; }
            $value_counter++;
        }

        //$debug = array('$values' => $values, '$weights' => $weights);
        //$this->events_create(false, false, 'DEBUG', '<pre>'.preg_replace('#\s+#', ' ', print_r($debug, true)).'</pre>');

        // Return the random element
        return $values[$value_counter];

    }

    // Define a function for returning a critical chance
    public function critical_chance($chance_percent = 10){

        // Invert if negative for some reason
        if ($chance_percent < 0){ $chance_percent = -1 * $chance_percent; }
        // Round up to a whole number
        $chance_percent = ceil($chance_percent);
        // If zero, automatically return false
        if ($chance_percent == 0){ return false; }
        // Return true of false at random
        $random_int = mt_rand(1, 100);
        return ($random_int <= $chance_percent) ? true : false;

    }

    // Define a function for finding a target player based on field side
    public function find_target_player($target_side){
        // Define the target player variable to start
        $target_player = false;
        // Ensure the player array is not empty
        if (!empty($this->values['players'])){
            // Loop through the battle's player characters one by one
            foreach ($this->values['players'] AS $player_id => $player_info){
                // If the player matches the request side, return the player
                if ($player_info['player_side'] == $target_side){
                    $target_player = new rpg_player($this, $player_info);
                }
            }
        }
        // Return the final value of the target player
        return $target_player;
    }

    // Define a function for finding a target robot based on field side
    public function find_target_robot($target_side){
        // Define the target robot variable to start
        $target_player = $this->find_target_player($target_side);
        $target_robot = false;
        // Ensure the robot array is not empty
        if (!empty($this->values['robots'])){
            // Loop through the battle's robot characters one by one
            foreach ($this->values['robots'] AS $robot_id => $robot_info){
                // If the robot matches the request side, return the robot
                if ($robot_info['player_id'] == $target_player->player_id && $robot_info['robot_position'] == 'active'){
                    $target_robot = new rpg_robot($this, $target_player, $robot_info);
                }
            }
        }
        // Return the final value of the target robot
        return $target_robot;
    }


    // -- CHECK ATTACHMENTS FUNCTION -- //

    // Define a function for checking attachment status
    public static function temp_check_robot_attachments(&$this_battle, &$this_player, &$this_robot, &$target_player, &$target_robot){

        // Loop through all the target player's robots and carry out any end-turn events
        $temp_robot = false;
        foreach ($this_player->values['robots_active'] AS $temp_robotinfo){

            // Create the temp robot object
            if (empty($temp_robot)){ $temp_robot = new rpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
            else { $temp_robot->robot_load(array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
            //if ($temp_robotinfo['robot_id'] == $this_robot->robot_id){ $temp_robot = &$this_robot; }
            //else { $temp_robot = new rpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }

            // Hide any disabled robots that have not been hidden yet
            if ($temp_robotinfo['robot_status'] == 'disabled'){
                // Hide robot and update session
                $temp_robot->flags['apply_disabled_state'] = true;
                //$temp_robot->flags['hidden'] = true;
                $temp_robot->update_session();
                // Create an empty field to remove any leftover frames
                $this_battle->events_create(false, false, '', '');
                // Continue
                continue;
            }

            // If this robot has any attachments, loop through them
            if (!empty($temp_robot->robot_attachments)){
                //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments');
                foreach ($temp_robot->robot_attachments AS $attachment_token => $attachment_info){
                    //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments '.$attachment_token);
                    // If this attachment has a duration set
                    if (isset($attachment_info['attachment_duration'])){
                        //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments '.$attachment_token.' duration '.$attachment_info['attachment_duration']);
                        // If the duration is not empty, decrement it and continue
                        if ($attachment_info['attachment_duration'] > 0){
                            $attachment_info['attachment_duration'] = $attachment_info['attachment_duration'] - 1;
                            $temp_robot->robot_attachments[$attachment_token] = $attachment_info;
                            $temp_robot->update_session();
                            //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, $temp_robot->robot_token.' checkpoint has attachments '.$attachment_token.' duration '.$temp_robot->robot_attachments[$attachment_token]['attachment_duration']);
                        }
                        // Otherwise, trigger the destory action for this attachment
                        else {
                            // Remove this attachment and inflict damage on the robot
                            unset($temp_robot->robot_attachments[$attachment_token]);
                            $temp_robot->update_session();
                            if ($attachment_info['attachment_destroy'] !== false){
                                $temp_attachment = new rpg_ability($this_battle, $this_player, $temp_robot, array('ability_token' => $attachment_info['ability_token']));
                                $temp_trigger_type = !empty($attachment_info['attachment_destroy']['trigger']) ? $attachment_info['attachment_destroy']['trigger'] : 'damage';
                                //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.'!');
                                //$this_battle->events_create(false, false, 'DEBUG_'.__LINE__, 'checkpoint has attachments '.$attachment_token.' trigger '.$temp_trigger_type.' info:<br />'.preg_replace('/\s+/', ' ', htmlentities(print_r($attachment_info['attachment_destroy'], true), ENT_QUOTES, 'UTF-8', true)));
                                if ($temp_trigger_type == 'damage'){
                                    $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                    $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                    $temp_attachment->update_session();
                                    $temp_damage_kind = $attachment_info['attachment_destroy']['kind'];
                                    $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array('apply_modifiers' => false);
                                    if (isset($attachment_info['attachment_'.$temp_damage_kind])){
                                        $temp_damage_amount = $attachment_info['attachment_'.$temp_damage_kind];
                                        $temp_robot->trigger_damage($temp_robot, $temp_attachment, $temp_damage_amount, false, $temp_trigger_options);
                                    }
                                } elseif ($temp_trigger_type == 'recovery'){
                                    $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                    $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                    $temp_attachment->update_session();
                                    $temp_recovery_kind = $attachment_info['attachment_destroy']['kind'];
                                    $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array('apply_modifiers' => false);
                                    if (isset($attachment_info['attachment_'.$temp_recovery_kind])){
                                        $temp_recovery_amount = $attachment_info['attachment_'.$temp_recovery_kind];
                                        $temp_robot->trigger_recovery($temp_robot, $temp_attachment, $temp_recovery_amount, false, $temp_trigger_options);
                                    }
                                } elseif ($temp_trigger_type == 'special'){
                                    $temp_attachment->target_options_update($attachment_info['attachment_destroy']);
                                    $temp_attachment->recovery_options_update($attachment_info['attachment_destroy']);
                                    $temp_attachment->damage_options_update($attachment_info['attachment_destroy']);
                                    $temp_attachment->update_session();
                                    $temp_trigger_options = isset($attachment_info['attachment_destroy']['options']) ? $attachment_info['attachment_destroy']['options'] : array();
                                    $temp_robot->trigger_damage($temp_robot, $temp_attachment, 0, false, $temp_trigger_options);
                                }
                                // If the temp robot was disabled, trigger the event
                                if ($temp_robot->robot_energy < 1){
                                    $temp_robot->trigger_disabled($target_robot, $temp_attachment);
                                    // If this the player's last robot
                                    if ($this_player->counters['robots_active'] < 1){
                                        // Trigger the battle complete event
                                        $this_battle->battle_complete_trigger($target_player, $target_robot, $this_player, $this_robot, '', '');
                                    }
                                }
                                // Create an empty field to remove any leftover frames
                                $this_battle->events_create(false, false, '', '');
                            }
                        }
                    }

                }
            }

        }

        // Return true on success
        return true;

    }

    // -- CHECK WEAPONS FUNCTION -- //

    // Define a function for checking weapons status
    public static function temp_check_robot_weapons(&$this_battle, &$this_player, &$this_robot, &$target_player, &$target_robot, $regen_weapons = true){

        // Loop through all the target player's robots and carry out any end-turn events
        $temp_robot = false;
        foreach ($this_player->values['robots_active'] AS $temp_robotinfo){

            // Create the temp robot object
            if (empty($temp_robot)){ $temp_robot = new rpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
            else { $temp_robot->robot_load(array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }
            //if ($temp_robotinfo['robot_id'] == $this_robot->robot_id){ $temp_robot = &$this_robot; }
            //else { $temp_robot = new rpg_robot($this_battle, $this_player, array('robot_id' => $temp_robotinfo['robot_id'], 'robot_token' => $temp_robotinfo['robot_token'])); }

            // Ensure this robot has not been disabled already
            if ($temp_robotinfo['robot_status'] == 'disabled'){
                // Hide robot and update session
                $temp_robot->flags['apply_disabled_state'] = true;
                //$temp_robot->flags['hidden'] = true;
                $temp_robot->update_session();
                // Create an empty field to remove any leftover frames
                $this_battle->events_create(false, false, '', '');
                // Continue
                continue;
            }

            // If this robot is not at full weapon energy, increase it by one
            if ($temp_robot->robot_weapons < $temp_robot->robot_base_weapons
                || $temp_robot->robot_attack < $temp_robot->robot_base_attack
                || $temp_robot->robot_defense < $temp_robot->robot_base_defense
                || $temp_robot->robot_speed < $temp_robot->robot_base_speed){
                // Ensure the regen weapons flag has been set to true
                if ($regen_weapons){
                    // Define the multiplier based on position
                    $temp_multiplier = $temp_robot->robot_position == 'bench' ? 2 : 1;
                    // Increment this robot's weapons by one point and update
                    $temp_robot->robot_weapons += MMRPG_SETTINGS_RECHARGE_WEAPONS * $temp_multiplier;
                    // If any of this robot's stats are in break, recover by one
                    if ($temp_robot->robot_attack <= 0){ $temp_robot->robot_attack += MMRPG_SETTINGS_RECHARGE_ATTACK * $temp_multiplier; }
                    if ($temp_robot->robot_defense <= 0){ $temp_robot->robot_defense += MMRPG_SETTINGS_RECHARGE_DEFENSE * $temp_multiplier; }
                    if ($temp_robot->robot_speed <= 0){ $temp_robot->robot_speed += MMRPG_SETTINGS_RECHARGE_SPEED * $temp_multiplier; }
                    // If this robot is over its base, zero it out
                    if ($temp_robot->robot_weapons > $temp_robot->robot_base_weapons){ $temp_robot->robot_weapons = $temp_robot->robot_base_weapons; }
                }
                // Update just to be sure
                $temp_robot->update_session();
                // If this robot was in the active position, create a frame
                if ($temp_robot->robot_position == 'active'){
                    // Create an empty field to remove any leftover frames
                    //$this_battle->events_create(false, false, '', '');
                }
            }

        }

        // Return true on success
        return true;

    }


    // Define a function for generating star console variables
    public function star_console_markup($options, $player_data, $robot_data){

        // Define the variable to hold the console star data
        $this_data = array();

        // Collect the star image info from the index based on type
        $temp_star_kind = $options['star_kind'];
        $temp_field_type_1 = !empty($options['star_type']) ? $options['star_type'] : 'none';
        $temp_field_type_2 = !empty($options['star_type2']) ? $options['star_type2'] : $temp_field_type_1;
        $temp_star_back_info = mmrpg_prototype_star_image($temp_field_type_2);
        $temp_star_front_info = mmrpg_prototype_star_image($temp_field_type_1);

        // Define and calculate the simpler markup and positioning variables for this star
        $this_data['star_name'] = isset($options['star_name']) ? $options['star_name'] : 'Battle Star';
        $this_data['star_title'] = $this_data['star_name'];
        $this_data['star_token'] = $options['star_token'];
        $this_data['container_class'] = 'this_sprite sprite_left';
        $this_data['container_style'] = '';

        // Define the back star's markup
        $this_data['star_image'] = 'images/abilities/item-star-'.$temp_star_kind.'-'.$temp_star_back_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['star_markup_class'] = 'sprite sprite_star sprite_star_sprite sprite_40x40 sprite_40x40_'.str_pad($temp_star_back_info['frame'], 2, '0', STR_PAD_LEFT).' ';
        $this_data['star_markup_style'] = 'background-image: url('.$this_data['star_image'].'); margin-top: 5px; ';
        $temp_back_markup = '<div class="'.$this_data['star_markup_class'].'" style="'.$this_data['star_markup_style'].'" title="'.$this_data['star_title'].'">'.$this_data['star_title'].'</div>';

        // Define the back star's markup
        $this_data['star_image'] = 'images/abilities/item-star-base-'.$temp_star_front_info['sheet'].'/sprite_left_40x40.png?'.MMRPG_CONFIG_CACHE_DATE;
        $this_data['star_markup_class'] = 'sprite sprite_star sprite_star_sprite sprite_40x40 sprite_40x40_'.str_pad($temp_star_front_info['frame'], 2, '0', STR_PAD_LEFT).' ';
        $this_data['star_markup_style'] = 'background-image: url('.$this_data['star_image'].'); margin-top: -42px; ';
        $temp_front_markup = '<div class="'.$this_data['star_markup_class'].'" style="'.$this_data['star_markup_style'].'" title="'.$this_data['star_title'].'">'.$this_data['star_title'].'</div>';

        // Generate the final markup for the console star
        $this_data['star_markup'] = '';
        $this_data['star_markup'] .= '<div class="'.$this_data['container_class'].'" style="'.$this_data['container_style'].'">';
        $this_data['star_markup'] .= $temp_back_markup;
        $this_data['star_markup'] .= $temp_front_markup;
        $this_data['star_markup'] .= '</div>';

        // Return the star console data
        return $this_data;

    }

    // Define a public function for recalculating internal counters
    public function update_variables(){

        // Calculate this battle's count variables
        //$this->counters['thing'] = count($this->robot_stuff);

        // Return true on success
        return true;

    }

    // Define a public function for updating this player's session
    public function update_session(){

        // Update any internal counters
        $this->update_variables();

        // Update the session with the export array
        $this_data = $this->export_array();
        $_SESSION['BATTLES'][$this->battle_id] = $this_data;

        // Return true on success
        return true;

    }

    // Define a function for exporting the current data
    public function export_array(){

        // Return all internal ability fields in array format
        return array(
            'battle_id' => $this->battle_id,
            'battle_name' => $this->battle_name,
            'battle_token' => $this->battle_token,
            'battle_description' => $this->battle_description,
            'battle_turns' => $this->battle_turns,
            'battle_rewards' => $this->battle_rewards,
            'battle_points' => $this->battle_points,
            'battle_level' => $this->battle_level,
            'battle_base_name' => $this->battle_base_name,
            'battle_base_token' => $this->battle_base_token,
            'battle_base_description' => $this->battle_base_description,
            'battle_base_turns' => $this->battle_base_turns,
            'battle_base_rewards' => $this->battle_base_rewards,
            'battle_base_points' => $this->battle_base_points,
            'battle_base_level' => $this->battle_base_level,
            'battle_counts' => $this->battle_counts,
            'battle_status' => $this->battle_status,
            'battle_result' => $this->battle_result,
            'battle_robot_limit' => $this->battle_robot_limit,
            'battle_field_base' => $this->battle_field_base,
            'battle_target_player' => $this->battle_target_player,
            'flags' => $this->flags,
            'counters' => $this->counters,
            'values' => $this->values,
            'history' => $this->history
            );

    }

}
?>