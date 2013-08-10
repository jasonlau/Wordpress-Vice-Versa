<?php

/**
 * Plugin Name: Vice Versa
 * Plugin URI: http://jasonlau.biz
 * Description: Convert Pages to Posts and Vice Versa
 * Version: 2.2.3
 * Author: Jason Lau
 * Author URI: http://jasonlau.biz
 * Disclaimer: Use at your own risk. No warranty expressed or implied.
 * Always backup your database before making changes.
 * Copyright 2010-2012 http://jasonlau.biz

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

define('VICEVERSA', null);
define('VICEVERSA_VERSION', '2.2.3');

define("VICEVERSA_PATH", WP_PLUGIN_DIR . '/vice-versa/');

function viceversa_admin_menu(){
  add_submenu_page('tools.php', 'Vice Versa', 'Vice Versa', 'publish_pages', 'vice-versa', 'viceversa_get_modules');
  add_action('admin_enqueue_scripts', 'viceversa_load_scripts');
}

function viceversa_admin_init(){
    if(function_exists('load_plugin_textdomain')) {
      load_plugin_textdomain('vice-versa', false, dirname( plugin_basename( __FILE__ ) ) . '/language/' );
 }
}

function viceversa_load_scripts($hook) {
    if($hook && $hook == "tools_page_vice-versa"):
       wp_enqueue_script('jquery');
       wp_enqueue_script('scriptaculous');
       wp_enqueue_script('scriptaculous-effects');
       add_thickbox();
       wp_register_style('viceversa_css', plugins_url('/css/vice-versa.css', __FILE__));
       wp_enqueue_style('viceversa_css');
       wp_register_script('viceversa_js', plugins_url('/js/vice-versa.js', __FILE__));
       wp_enqueue_script('viceversa_js');
    endif;
}

add_action('admin_menu', 'viceversa_admin_menu');
add_action('admin_init', 'viceversa_admin_init');

$viceversa_debug = (isset($_REQUEST['viceversa_debug']) && $_REQUEST['viceversa_debug'] == 'true') ? true : false;
define('VICEVERSA_DEBUG', $viceversa_debug); // Test this plugin

$viceversa_mode = (!isset($_REQUEST['viceversa_mode']) || $_REQUEST['viceversa_mode'] == '') ? 'post2page' : $_REQUEST['viceversa_mode'];
$viceversa_mode = (!isset($_POST['viceversa_mode']) || $_POST['viceversa_mode'] == '') ? $viceversa_mode : $_POST['viceversa_mode'];

if(!class_exists('WP_List_Table')):
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
endif;

function viceversa_get_modules(){
    global $viceversa_mode;    
    $modulenames = array();
    $modulebuttons = array();
    $include_files = array();
    $viceversa_directory = opendir(VICEVERSA_PATH);
    while($file = readdir($viceversa_directory)){
        if((eregi("vice-versa-module-", $file) AND file_exists(VICEVERSA_PATH . $file) AND !in_array($file, $include_files))){
            array_push($include_files, $file);                      
        }
    }
    closedir($viceversa_directory);
    sort($include_files);
    foreach($include_files as $file_name){
        include_once(VICEVERSA_PATH . $file_name);
        array_push($modulenames, $_VICEVERSA_MODULE_NAME);
        array_push($modulebuttons, $_VICEVERSA_MODULE_BUTTON);
    }  
    $_VICEVERSA_ACTIVE_MODULE->set_module_buttons($modulebuttons);
    $_VICEVERSA_ACTIVE_MODULE->display_module();        
}

?>