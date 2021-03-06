<?

// -- SHOP BUYING STARS -- //

?>

<table class="full" style="margin-bottom: 5px;">
    <colgroup>
        <col width="50%" />
        <col width="50%" />
    </colgroup>
    <thead>
        <tr>
            <th class="left">
                <span class="buy_button buy_button_header">&nbsp;</span>
                <label class="item_price item_price_header">Sell</label>
            </th>
            <th class="right">
                <span class="buy_button buy_button_header">&nbsp;</span>
                <label class="item_price item_price_header">Sell</label>
            </th>
        </tr>
    </thead>
</table>

<div class="scroll_wrapper">

    <table class="full" style="margin-bottom: 5px;">
        <colgroup>
            <col width="50%" />
            <col width="50%" />
        </colgroup>
        <tbody>
            <tr>

                <?

                // Collect the stars for buying and slice/shuffle if nessary
                $temp_session_key = 'star_list_array_raw';
                $star_list_array_raw = !empty($_SESSION[$session_token]['SHOP'][$temp_session_key]) ? $_SESSION[$session_token]['SHOP'][$temp_session_key] : array();
                if (empty($star_list_array_raw) || $star_list_array_raw['date'] != date('Y-m-d-H')){

                    $star_list_array_raw = array();
                    $star_list_array_raw['date'] = date('Y-m-d-H');
                    $star_list_array_raw['today'] = array();

                    // Collect all the star tokens sorted by their kind
                    $temp_star_tokens = array();

                    // Collect each player's current field selection from the omega session
                    $temp_base_tokens = array();
                    $temp_fusion_tokens = array();
                    $temp_fusion_tokens_index = array();
                    foreach ($mmrpg_index['players'] AS $temp_player_token => $temp_player_info){

                        // Collect this player's omega factors from the session
                        $temp_session_key = $temp_player_token.'_target-robot-omega_prototype';
                        $temp_target_robot_omega = !empty($_SESSION[$session_token]['values'][$temp_session_key]) ? $_SESSION[$session_token]['values'][$temp_session_key] : array();

                        // Loop through omega factors and collect base fields
                        foreach ($temp_target_robot_omega AS $key => $factor){
                            $base = $factor['field'];
                            $temp_base_tokens[] = $base;
                        }

                        // Loop through again and collect fusion fields
                        foreach ($temp_target_robot_omega AS $key => $factor){
                            if ($key % 2 != 0){ continue; }
                            $base = $factor['field'];
                            $base2 = $temp_target_robot_omega[$key + 1]['field'];
                            $fusion = preg_replace('/-([a-z0-9]+)$/i', '', $base).'-'.preg_replace('/^([a-z0-9]+)-/i', '', $base2);
                            if (!in_array($fusion, $temp_fusion_tokens)){
                                $temp_fusion_tokens[] = $fusion;
                                $temp_fusion_tokens_index[$fusion] = array($base, $base2);
                            }
                        }

                    }

                    // Shuffle the list of base and fusion tokens
                    shuffle($temp_base_tokens);
                    shuffle($temp_fusion_tokens);

                    // Define the first eight field and fusion star tokens
                    $temp_fusion_star_tokens = array_slice($temp_fusion_tokens, 0, 10);
                    $temp_field_star_tokens = array_slice($temp_base_tokens, 0, count($temp_fusion_star_tokens));

                    // Loop through and index collected field star info
                    foreach ($temp_field_star_tokens AS $key => $token){

                        // Collect the info for this base field and create the star
                        $field_info = rpg_field::parse_index_info($mmrpg_database_fields[$token]);
                        if (isset($_SESSION[$session_token]['values']['battle_stars'][$token])){ $star_info = $_SESSION[$session_token]['values']['battle_stars'][$token]; }
                        else { $star_info = array('star_token' => $token, 'star_name' => $field_info['field_name'], 'star_kind' => 'field', 'star_type' => $field_info['field_type'], 'star_type2' => '', 'star_player' => '', 'star_date' => ''); }
                        $star_list_array_raw['today'][$star_info['star_token']] = $star_info;
                        $temp_star_tokens[] = $star_info['star_token'];

                        // Collect the two fusion field token info and create stars
                        $fusion = $temp_fusion_star_tokens[$key];
                        $token2 = $temp_fusion_tokens_index[$fusion][0];
                        $token3 = $temp_fusion_tokens_index[$fusion][1];
                        $field_info2 = rpg_field::parse_index_info($mmrpg_database_fields[$token2]);
                        $field_info3 = rpg_field::parse_index_info($mmrpg_database_fields[$token3]);
                        $fusion_token = preg_replace('/-([a-z0-9]+)$/i', '', $token2).'-'.preg_replace('/^([a-z0-9]+)-/i', '', $token3);
                        $fusion_name = preg_replace('/\s+([a-z0-9]+)$/i', '', $field_info2['field_name']).' '.preg_replace('/^([a-z0-9]+)\s+/i', '', $field_info3['field_name']);
                        $fusion_type = !empty($field_info2['field_type']) ? $field_info2['field_type'] : '';
                        $fusion_type2 = !empty($field_info3['field_type']) ? $field_info3['field_type'] : '';
                        if (isset($_SESSION[$session_token]['values']['battle_stars'][$fusion_token])){ $star_info = $_SESSION[$session_token]['values']['battle_stars'][$fusion_token]; }
                        else { $star_info = array('star_token' => $fusion_token, 'star_name' => $fusion_name, 'star_kind' => 'fusion', 'star_type' => $fusion_type, 'star_type2' => $fusion_type2, 'star_player' => '', 'star_date' => ''); }
                        $star_list_array_raw['today'][$star_info['star_token']] = $star_info;
                        $temp_star_tokens[] = $star_info['star_token'];

                    }

                    // Update the session with the new array in raw format
                    $_SESSION[$session_token]['SHOP'][$temp_session_key] = $star_list_array_raw;

                }

                // Reformat the list arrays to what we need them for
                $star_list_array = array_keys($star_list_array_raw['today']);

                // Loop through the items and print them one by one
                $star_counter = 0;
                foreach ($star_list_array AS $key => $token){

                    $star_counter++;

                    $star_cell_float = $star_counter % 2 == 0 ? 'right' : 'left';

                    $star_info_token = $token;
                    $star_info = $star_list_array_raw['today'][$token];

                    $star_info_price = $shop_info['shop_stars']['stars_buying'][$star_info['star_kind']];
                    $star_info_name = $star_info['star_name'].' Star';
                    $star_info_date = !empty($star_info['star_date']) ? $star_info['star_date'] : 0;

                    $star_info_type = !empty($star_info['star_type']) ? $star_info['star_type'] : '';
                    $star_info_type2 = !empty($star_info['star_type2']) ? $star_info['star_type2'] : '';
                    $star_info_class = !empty($star_info_type) ? $star_info_type : 'none';
                    if (!empty($star_info_type2)){ $star_info_class .= '_'.$star_info_type2; }

                    if (!empty($star_info_type) && !empty($this_star_force[$star_info_type])){
                        $temp_force = $this_star_force[$star_info_type];
                        $star_info_price += round($temp_force * 10);
                    }

                    if (!empty($star_info_type2) && !empty($this_star_force[$star_info_type2])){
                        $temp_force2 = $this_star_force[$star_info_type2];
                        $star_info_price += round($temp_force2 * 10);
                    }

                    $global_item_quantities['star-'.$star_info_token] = !empty($_SESSION[$session_token]['values']['battle_stars'][$star_info_token]) ? 1 : 0;
                    $global_item_prices['sell']['star-'.$star_info_token] = $star_info_price;

                    $temp_info_tooltip = $star_info_name.'<br /> ';
                    $temp_info_tooltip .= '<span style="font-size:80%;">';
                    $temp_info_tooltip .= ucfirst($star_info['star_kind']).' Star | '.ucwords(str_replace('_', ' / ', $star_info_type)).' Type';
                    if (!empty($star_info_date)){ $temp_info_tooltip .= ' <br />Found '.date('Y/m/d', $star_info_date); }
                    $temp_info_tooltip = htmlentities($temp_info_tooltip, ENT_QUOTES, 'UTF-8', true);
                    $temp_info_tooltip .= '</span>';

                    ?>
                        <td class="<?= $star_cell_float ?> item_cell" data-kind="star" data-action="sell" data-token="<?= 'star-'.$star_info_token ?>">
                            <span class="item_name ability_type ability_type_<?= $star_info_class ?>" data-tooltip="<?= $temp_info_tooltip ?>"><?= $star_info_name ?></span>
                            <a class="sell_button ability_type ability_type_none" href="#">Sell</a>
                            <label class="item_quantity" data-quantity="1" style="display: none;">x 1</label>
                            <label class="item_price" data-price="<?= $star_info_price ?>">&hellip; <?= $star_info_price ?>z</label>
                        </td>
                    <?

                    if ($star_cell_float == 'right'){ echo '</tr><tr>'; }

                }

                if ($star_counter % 2 != 0){
                    ?>
                        <td class="right item_cell item_cell_disabled">
                            &nbsp;
                        </td>
                    <?
                }

                ?>

            </tr>
        </tbody>
    </table>

</div>
