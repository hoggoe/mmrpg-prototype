<?php
/*
 * INDEX PAGE : ADMIN
 */

// Prevent lower level users from accessing the admin page
if (!isset($this_userinfo['role_level']) || $this_userinfo['role_level'] < 5){
  header('Location: '.MMRPG_CONFIG_ROOTURL);
  exit();
}

//  If this is a USER editor request, include the appropriate file
if ($this_current_sub == 'users'){
  // Require the admin users file
  require('page.admin_users.php');
}
//  If this is a BATTLE editor request, include the appropriate file
elseif ($this_current_sub == 'battles'){
  // Require the admin battles file
  require('page.admin_battles.php');
}
//  If this is a PLAYERS editor request, include the appropriate file
elseif ($this_current_sub == 'players'){
  // Require the admin players file
  require('page.admin_players.php');
}
//  If this is a MECHAS/ROBOTS/BOSSES editor request, include the appropriate file
elseif ($this_current_sub == 'mechas' || $this_current_sub == 'robots' || $this_current_sub == 'bosses'){
  // Require the admin robots file
  require('page.admin_robots.php');
}
//  If this is a ABILITIES editor request, include the appropriate file
elseif ($this_current_sub == 'abilities'){
  // Require the admin abilities file
  require('page.admin_players.php');
}
//  If this is a ITEMS editor request, include the appropriate file
elseif ($this_current_sub == 'items'){
  // Require the admin abilities file
  require('page.admin_players.php');
}
//  If this is a FIELDS editor request, include the appropriate file
elseif ($this_current_sub == 'fields'){
  // Require the admin fields file
  require('page.admin_fields.php');
}
//  Otherwise, include the INDEX file if the request is empty or invalid
else {
  // Require the admin index file
  require('page.admin_index.php');
}

?>