<?php

/**
 * Plugin Name: Vice Versa
 * Plugin URI: http://jasonlau.biz
 * Description: Convert Pages to Posts and Vice Versa
 * Version: 2.1.5
 * Author: Jason Lau
 * Author URI: http://jasonlau.biz
 * Disclaimer: Use at your own risk. No warranty expressed or implied.
 * Always backup your database before making changes.
 * Copyright 2010-2011 http://jasonlau.biz

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


define('VICEVERSA_VERSION', '2.1.5');
define('VICEVERSA_DEBUG', false); // Test this plugin

load_plugin_textdomain('vice-versa', '/wp-content/plugins/vice-versa/vice-versa.pot');

function viceversa_admin_menu(){
  add_submenu_page('tools.php', 'Vice Versa', 'Vice Versa', 'publish_pages', 'vice-versa', 'viceversa_display');
}

add_action('admin_menu', 'viceversa_admin_menu');

if(!class_exists('WP_List_Table')):
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
endif;

class ViceVersa_List_Table extends WP_List_Table {
    
    function __construct(){
        global $status, $page;
        parent::__construct( array(
            'singular'  => __('post', 'vice-versa'),
            'plural'    => __('posts', 'vice-versa'),
            'ajax'      => false
        ) );           
    }
    
    function column_default($item, $column_name){
        switch($column_name){
            case 'ID':
            case 'post_title':
            case 'post_date':           
            return $item->$column_name;
            case 'assign_to':
            return '<div class="viceversa-assign-to" rel="'.$item->ID.'">Loading ...</div><div class="viceversa-assign-to-text" rel="'.$item->ID.'"></div>';
            default:
            return print_r($item,true);
        }
    }
    
    function column_ID($item){
        $actions = array(
            'convert'      => sprintf('<a href="?page=%s&action=%s&p=%s">' . __('Convert', 'vice-versa') . '</a>',$_REQUEST['page'],'convert',$item->ID)
        );
        return sprintf('%1$s %2$s',
            $item->ID,
            $this->row_actions($actions)
        );
    }
    
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" data-id="%3$s" />',
            $this->_args['singular'],
            $item->ID . "|vice-versa|" . $item->post_title,
            $item->ID
        );
    }
    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'ID'    => __('ID', 'vice-versa'),
            'post_title'     => __('Title', 'vice-versa'),            
            'post_date'  => __('Date', 'vice-versa'),
            'assign_to' => __('Assign To', 'vice-versa')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'ID'    => array('ID',false),
            'post_title'     => array('post_title',false),
            'post_date'  => array('post_date',false),
            'assign_to'  => array('assign_to',true)
        );
        return $sortable_columns;
    }
    
    function get_bulk_actions() {
        $actions = array(
            'convert' => __('Convert', 'vice-versa')
        );
        return $actions;
    }
    
    function process_bulk_action(){
        if('convert' === $this->current_action()):
            $output = "<div id=\"viceversa-status\" class=\"updated\">
            <input type=\"button\" class=\"viceversa-close-icon button-secondary\" title=\"Close\" value=\"x\" />";
            if(VICEVERSA_DEBUG):
                $output .= "<strong>" . __('Debug Mode', 'vice-versa') . "</strong>\n";
            endif;
            
            switch($_REQUEST['p_type']){
                case 'page':
                $categories = array('ids' => array(), 'titles' => array());
                $do_bulk = false;
                foreach($_REQUEST['parents'] as $category):
                    if($category != ""):
                        $data = explode("|", $category);
                        $categories[$data[3]]['ids'] = array();
                        $categories[$data[3]]['titles'] = array();
                        if($data[3] == 0) $do_bulk = true;
                    endif;
                endforeach;
                
                foreach($_REQUEST['parents'] as $category):
                    if($category != ""):
                        $data = explode("|", $category);
                        array_push($categories[$data[3]]['ids'], $data[0]);
                        array_push($categories[$data[3]]['titles'], $data[2]);
                    endif;
                endforeach;
                
                $output .= "<p>\n";
                
                foreach($_REQUEST['post'] as $viceversa_page):
                    $viceversa_data = explode("|", $viceversa_page);
                    $viceversa_url = get_bloginfo('url') . "/?page_id=" . $viceversa_data[0];
                    if($do_bulk):
                        $ids = $categories[0]['ids'];
                        $titles = $categories[0]['titles'];
                    else:
                        $ids = $categories[$viceversa_data[0]]['ids'];
                        $titles = $categories[$viceversa_data[0]]['titles'];
                    endif;
                    $catlist = "";
                    if($titles):
                        foreach($titles as $cat):
                            $catlist .= $cat.", ";
                        endforeach;
                    endif;
                    $catlist = (trim($catlist,', ') == "") ? get_cat_name(1) : trim($catlist,', ');
                    $cat_array = (count($ids)<1) ? array(1) : $ids;
                    $viceversa_page = array();
                    $viceversa_page['ID'] = intval($viceversa_data[0]);
                    $viceversa_page['guid'] = $viceversa_url;
                    $viceversa_page['post_type'] = 'post';
                    $viceversa_page['post_parent'] = $cat_array;
                    
                    if(!VICEVERSA_DEBUG):
                        wp_update_post($viceversa_page);
                        wp_set_post_categories(intval($viceversa_data[0]), $cat_array);
                    endif;
                    
                    $new_permalink = get_permalink(intval($viceversa_data[0]));
                    $output .= sprintf(__('<strong>' . __('Page', 'vice-versa') . '</strong> #%s <code><a href="%s" target="_blank" title="' . __('New Permalink', 'vice-versa') . '">%s</a></code> ' . __('was successfully converted to a <strong>Post</strong> and assigned to category(s)', 'vice-versa') . ' <code>%s</code>. <a href="%s" target="_blank" title="' . __('New Permalink', 'vice-versa') . '">' . __('New Permalink', 'vice-versa') . '</a>', 'vice-versa'), $viceversa_data[0], $new_permalink, $viceversa_data[2], $catlist, $new_permalink) . "<br />\n";
                endforeach;
                break;
                
                default :
                $parents = array();
                $do_bulk = false;
                foreach($_REQUEST['parents'] as $parent):
                    if($parent != ""):
                        $data = explode("|", $parent);
                        if($data[3] == 0) $do_bulk = true;
                        $parents[intval($data[3])] = $data[0] . "|vice-versa|" . $data[2];
                    endif;
                endforeach;
                $output .= "<p>\n";
                if(!$_REQUEST['post']):
                    $output .= __('No items were selected. Please select items using the checkboxes.', 'vice-versa');
                else:
                  foreach($_REQUEST['post'] as $viceversa_post):
                    $viceversa_data = explode("|", $viceversa_post);
                    $viceversa_url = get_bloginfo('url') . "/?page_id=" . $viceversa_data[0];
                    if($do_bulk):
                        $p = $parents[0];
                    else:
                        $p = $parents[$viceversa_data[0]];
                    endif;
                    $parent = ($p == "") ? "0|vice-versa|" . __('No Parent', 'vice-versa') . "" : $p;
                    $parent = explode("|vice-versa|", $parent);
                    $viceversa_page = array();
                    $viceversa_page['ID'] = intval($viceversa_data[0]);
                    $viceversa_page['guid'] = $viceversa_url;
                    $viceversa_page['post_type'] = 'page';
                    $viceversa_page['post_parent'] = $parent[0];
                    if(!VICEVERSA_DEBUG):
                        wp_update_post($viceversa_page);
                        wp_set_post_categories(intval($viceversa_data[0]), array($parent[0]));
                    endif;
                    $permalink = get_permalink(intval($viceversa_data[0]));
                    $output .= sprintf(__('<strong>' . __('Post', 'vice-versa') . '</strong> #%s <code><a href="%s" target="_blank" title="' . __('New Permalink', 'vice-versa') . '">%s</a></code> ' . __('was successfully converted to a <strong>Page</strong> and assigned to parent', 'vice-versa') . ' #%s <code>%s</code>. <a href="%s" target="_blank" title="' . __('New Permalink', 'vice-versa') . '">' . __('New Permalink', 'vice-versa') . '</a>', 'vice-versa'), $viceversa_data[0], $permalink, $viceversa_data[2], $parent[0], $parent[1], $permalink) . "<br />\n";
                 endforeach; 
                endif;                
            }
            $output .= "</p></div>\n";
            define("VICEVERSA_STATUS", $output);       
        endif; // $this->current_action        
    }
    
    function close_icon(){
        echo '<input type="button" class="viceversa-close-icon button-secondary" title="' . __('Close', 'vice-versa') . '" value="x" />';
    }
    
    function prepare_items() {
        global $wpdb;
        
        $this->process_bulk_action();
        
        $per_page = (!$_REQUEST['per_page']) ? 10 : $_REQUEST['per_page'];
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        if(!isset( $_REQUEST['p_type'])):
			$p_type = 'post';
		elseif(in_array( $_REQUEST['p_type'], get_post_types(array( 'show_ui' => true)))):
			$p_type = $_REQUEST['p_type'];
		else:
			wp_die(__('Invalid post type'));
        endif;
            
		$_REQUEST['p_type'] = $p_type;

		$post_type_object = get_post_type_object($p_type); 
        
        $orderby = (!$_REQUEST['orderby']) ? '' : ' ORDER BY ' . $_REQUEST['orderby'];
        $order = (!$_REQUEST['order']) ? '' : ' ' . $_REQUEST['order'];
        
        if(!current_user_can($post_type_object->cap->edit_others_posts)):
            $query = "SELECT * FROM $wpdb->posts  WHERE post_type = '$p_type' AND post_status NOT IN ( 'trash', 'auto-draft' ) AND post_author = " . get_current_user_id() . $orderby . $order;
        else:
         $query = "SELECT * FROM $wpdb->posts  WHERE post_type = '$p_type' AND post_status NOT IN ( 'trash', 'auto-draft' ) " . $orderby . $order;   
        endif;
        
        $data = $wpdb->get_results($query);
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->set_pagination_args(array('total_items' => $total_items, 'per_page'    => $per_page, 'total_pages' => ceil($total_items/$per_page)));
    }
    
    function assign_to($p_type){
        global $wpdb;
        $output = "";
        switch ($p_type){
            case 'page':
            $categories = get_categories('hide_empty=0&order=asc');
            $output .= "<select name=\"parents[]\">\n<option value=\"\">" . __('Select A Category', 'vice-versa') . "</option>\n";
            foreach($categories as $cat):
               $output .= "<option title=\"" . $cat->cat_name . "\" value=\"" . $cat->term_id . "|vice-versa|";
               $post_title = (strlen($cat->cat_name) > 25) ? substr($cat->cat_name, 0, 25) . '...' : $cat->cat_name;
               $output .= $cat->cat_name . "\">";
               $output .= $cat->cat_name . " (" . $cat->category_count . ")</option>\n";
            endforeach;
            $output .= "</select> <input type=\"button\" class=\"viceversa-add-category button-secondary\" value=\"+\" title=\"" . __('Add another category', 'vice-versa') . "\" /><input type=\"button\" class=\"viceversa-remove-category button-secondary\" value=\"-\" title=\"" . __('Remove this category', 'vice-versa') . "\" />\n";
            break;
            
            default :
            $query = "SELECT * FROM $wpdb->posts  WHERE post_type = 'page' AND post_status NOT IN ( 'trash', 'auto-draft' ) AND post_author = " . get_current_user_id() . " ORDER BY post_title ASC";
            $parents = $wpdb->get_results($query);
            $output .= "<select name=\"parents[]\">\n<option value=\"\">" . __('Select A Parent Page', 'vice-versa') . "</option>\n";
            foreach ($parents as $parent):
               $post = get_post(intval($parent->ID));
               $output .= "<option title=\"" . $post->post_title . "\" value=\"" . $post->ID . "|vice-versa|";
               $post_title = (strlen($post->post_title) > 25) ? substr($post->post_title, 0, 25) . '...' : $post->post_title;
               $output .= $post_title . "\">";
               $output .= $post_title . "</option>\n";
            endforeach;
            $output .= "</select>\n";
        }
        
        return $output;
    }
        
} // class ViceVersa_List_Table

/***************************** RENDER PAGE ********************************
 *******************************************************************************/

function viceversa_display(){
    $wp_list_table = new ViceVersa_List_Table();
    $wp_list_table->prepare_items();
    $p_type = (!$_REQUEST['p_type']) ? 'post' : $_REQUEST['p_type'];
    $per_page = (!$_REQUEST['per_page']) ? 10 : $_REQUEST['per_page'];
    ?>
    <style type="text/css">
    <!--
    
    #viceversa-left-panel{
        position: relative;
        margin: 0px 150px 0px 0px !important;
        /*padding: 0px 250px 0px 0px !important;*/
    }
    
    #viceversa-right-panel{
        text-align: center;
        position: absolute;
        right: 10px;
        margin: 10px 0px 0px 0px !important;
        padding: 10px;
        width: 120px !important;
        max-width: 120px !important;
        min-width: 120px !important;
        background: #ECECEC;
        border: 1px solid #CCC;
        border-radius: 5px;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
    }
    
	.viceversa-error-field{
	   border: 1px solid red;
       background-color: #FFFF99;
	}
    
    #viceversa-assign-error{
        color:red;
        font-weight: bold;
        font-variant: small-caps;
    }
    
    .viceversa-close-icon, .viceversa-close-info-icon{
        float: right;
        margin: 5px 0px;
    }
    
    .viceversa-info{
        background: #ECECEC;
        border: 1px solid #CCC;
        padding: 0 10px;
        margin-top: 5px;
        border-radius: 5px;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
    }
    
    .viceversa-hidden{
        display: none;
    }
    
    -->
    </style>
    <div class="wrap">
      <div id="icon-tools" class="icon32"><br/></div>
        <h2>Vice Versa</h2>
        <?php _e('Convert Pages To Posts And Vice Versa', 'vice-versa') ?><br />
        <div class="viceversa-info hidden">
        <input type="button" class="viceversa-close-info-icon button-secondary" title="<?php _e('Close', 'vice-versa') ?>" value="x" />
            <p><?php _e('Vice Versa is a post-type converter. Follow the steps below to convert items from one post-type to another.', 'vice-versa') ?></p>
            <ol start="1">
       <li><?php _e('Always backup your data before making changes to your website.', 'vice-versa') ?></li>     
	<li><?php _e('Select a conversion mode using the <code>Post To Page</code> and <code>Page To Post</code> buttons. The <input type="button" class="button-primary" value="blue" /> button is the selected conversion mode.', 'vice-versa') ?></li>
	<li><?php _e('Optional: Select a Parent or Categories to assign your Post or Page to. Use the bulk Parent or Category selector located at the bottom of the list table if needed. The Parents and Categories selectors will become visible depending on which conversion mode is selected.', 'vice-versa') ?></li>
	<li><?php _e('Select Posts or Pages to convert by either using the <code>Convert</code> link for each item or by using the checkboxes and <code>Bulk Actions</code> menu.', 'vice-versa') ?></li>
    <li><?php _e('<strong>Important</strong>: Vice Versa does not redirect. The links to the converted items will change automatically! You will be provided with new permalinks during the conversion process. Be prepared to change menu links if you have custom menus or have links hard-coded in your theme.', 'vice-versa') ?></li>
    <li>Premium Tech Support - $25.00/hr <form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="RZ8KMAZYEDURL"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form>
</ol>
<br /> 
        </div>
        <?php 
        if(defined("VICEVERSA_STATUS")) echo VICEVERSA_STATUS
        ?>
        <div id="viceversa-left-panel" class="alignleft">
        <form id="viceversa-form" method="get">        
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" id="p-type" name="p_type" value="<?php echo $p_type ?>" />
            <input type="hidden" id="per-page-hidden" name="per_page" value="10" />
            <?php $wp_list_table->display() ?>
        </form>
<div id="viceversa-assign-to" style="display: none;"><div class="viceversa-assign-to-menu"><?php echo $wp_list_table->assign_to($p_type);?></div></div>
<br />
<code><?php _e('<strong>Another <em><strong>Quality</strong></em> Work From', 'vice-versa') ?>  <a href="http://JasonLau.biz" target="_blank">JasonLau.biz</a></strong> - &copy;Jason Lau</code> <code>[<?php _e('Vice Versa Version', 'vice-versa') ?>: <?php echo VICEVERSA_VERSION; ?>]</code>

</div><div id="viceversa-right-panel" class="alignright">
<?php
// Check for updates
	$response = wp_remote_get('http://jasonlau.biz/viceversa/remote.php?version='.VICEVERSA_VERSION);
if(is_wp_error($response)):
   ?>
   <a href="http://jasonlau.biz/home/contact-me" target="_blank"><?php _e('Advertise Here', 'vice-versa') ?></a>
   <?php
else:
   echo $response['body'];
endif;
?>
</div>

<script type="text/javascript">

	/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
 
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
    jQuery(function($){
	   $("div.actions").first().prepend('<input type="button" class="vv-mode action <?php if($p_type == 'post') echo "button-primary"; else echo "button-secondary"; ?>" rel="post" value="<?php _e('Post To Page', 'vice-versa') ?>" /> <input type="button" class="vv-mode action <?php if($p_type == 'page') echo "button-primary"; else echo "button-secondary"; ?>" rel="page" value="<?php _e('Page To Post', 'vice-versa') ?>" /> <strong><?php _e('Items Per Page', 'vice-versa') ?>:</strong> <input type="text" id="per-page" size="4" value="<?php echo $per_page ?>" /><input class="vv-go button-secondary action" type="submit" value="<?php _e('Go', 'vice-versa') ?>" />');
       $("#doaction").after(' <input class="vv-help button-secondary hidden" type="button" value="?" title="Info" />');
       $('.vv-help').css('border-color','#FFFF00');
       $(".vv-mode").each(function(){
	   $(this).bind('mouseup',function(){
    	   $("#viceversa-form input[type='checkbox']").prop('checked',false);
           $("#viceversa-form select").val('');
	       $("#p-type").val($(this).attr('rel'));
           $("#viceversa-form").submit();
	   });
       });
       $(".vv-go").each(function(){
       $(this).bind('mouseup',function(){
	       $("#per-page-hidden").val($("#per-page").val());
           $("#viceversa-form").submit();
	   }); 
    });
    
    $(".vv-help").bind('mouseup',function(){
        $(".viceversa-info").show('slow');
	    $(this).hide('slow');
        $.cookie('vvinfo',1);
	   });
       
       if($.cookie('vvinfo') == 1){
        $(".viceversa-info").show();
        $(".vv-help").hide();
       } else {
        $(".viceversa-info").hide();
        $(".vv-help").show();
       }
<?php
	switch ($p_type){ 
	case 'page':
?>
    $(".viceversa-assign-to").each(function(){
        $(this).html($("#viceversa-assign-to").html());
    });
    $("#viceversa-form").append('<fieldset class="viceversa-bulk-assign-field viceversa-info"><legend style="padding: 0px 4px"><strong><?php _e('Bulk Category Selector', 'vice-versa') ?></strong></legend><div id="viceversa-assign-error"><?php _e('Error: Duplicate Selected!', 'vice-versa') ?></div><div id="viceversa-bulk-assign">' + $("#viceversa-assign-to").html() + '</div></fieldset>');
<?php        
	break;

	default :
?>
    $(".viceversa-assign-to").each(function(){
       $(this).html($("#viceversa-assign-to").html()); 
    });
    $("#viceversa-form").append('<fieldset class="viceversa-bulk-assign-field viceversa-info"><legend style="padding: 0px 4px"><strong><?php _e('Bulk Parent Selector', 'vice-versa') ?></strong></legend><div id="viceversa-bulk-assign">' + $("#viceversa-assign-to").html() + '</div></fieldset>');
<?php     
    }
?>
    $(".viceversa-remove-category").not(".viceversa-remove-category:last").hide(); 
    $(".viceversa-assign-to-text, #viceversa-assign-error").hide(); 
  
    var _activate = function(){
        $(".viceversa-remove-category").unbind('mouseup');
        $(".viceversa-remove-category").bind('mouseup',function(){
            $(this).parent().remove();
            if($("#viceversa-bulk-assign select:first").val() != "" && $("#viceversa-bulk-assign select").length < 2){
                if($(".viceversa-assign-to").is(':visible'))
                $(".viceversa-assign-to").fadeOut();
            } else if($("#viceversa-bulk-assign select:first").val() == "" && $("#viceversa-bulk-assign select").length < 2){
                if(!$(".viceversa-assign-to").is(':visible'))
                $(".viceversa-assign-to").fadeIn();
            }
            update_assignto_text(); 
        });
        
        $(".viceversa-add-category").unbind('mouseup');
        
        $(".viceversa-add-category").bind('mouseup',function(){
            $(this).parent().parent().append($("#viceversa-assign-to").html());
            _activate();
        });
        
        $(".assign_to option").each(function(){
            var check = $(this).val().split('|');
            if(check.length < 4 && check.length > 2){
                $(this).val($(this).val()+'|'+$(this).parent().parent().parent().attr('rel'));
            }
        });
        
        $("#viceversa-bulk-assign option").each(function(){
            var check = $(this).val().split('|');
            if(check.length < 4 && check.length > 2)
            $(this).val($(this).val()+'|0');
        });
        
        if($("#viceversa-bulk-assign select").length > 1){
            $(".viceversa-assign-to").hide();
            update_assignto_text();
        } else {
            if($("#viceversa-bulk-assign select").val() != "" && !$(".viceversa-assign-to").is(':visible'))
            $(".viceversa-assign-to").fadeIn();
        }
        
        $("#viceversa-bulk-assign select").bind('change',function(){
            if($(this).val() != "" && $("#viceversa-bulk-assign select").length < 2){
                if($(".viceversa-assign-to").is(':visible'))
                $(".viceversa-assign-to").hide();
                update_assignto_text();
            } else if($(this).val() == "" && $("#viceversa-bulk-assign select").length < 2){
                if(!$(".viceversa-assign-to").is(':visible'))
                $(".viceversa-assign-to").fadeIn();
                update_assignto_text();
            } else {
                update_assignto_text();
            }
        });
        
        update_assignto_text();
    };
    
    var update_assignto_text = function(){
        var v = '', assignto = '', data = [], i = 0, error = false;
        $("#viceversa-bulk-assign select").each(function(){
            if($(this).val() != ""){
                v = $(this).val().split('|');
                $.each(data, function(key,value){
                    if(value == v[0]){
                      error = true;
                      $('#viceversa-assign-error').fadeIn();
                    return false;
                    }                  
                });
                if(!error){
                    $(this).removeClass('viceversa-error-field');
                    $("#viceversa-assign-error").hide();
                  if(assignto == ''){
                    assignto += v[2];
                } else {
                    assignto += ', ' + v[2];
                }
                data[i] = v[0];                              
                i++;  
                } else {
                    $(this).addClass('viceversa-error-field');
                    $('#viceversa-assign-error').fadeIn();
                    return false;
                }
             }
        });
        if(assignto != ''){
            $(".viceversa-assign-to-text").html(assignto).fadeIn();      
        } else {
            if($(".viceversa-assign-to-text").is(':visible'))
        $(".viceversa-assign-to-text").html('').fadeOut();  
        }
    };
    
    $("a:contains('<?php _e('Convert', 'vice-versa') ?>')").each(function(){        
        var url = $(this).attr('href').split('='); 
        $(this).attr({'href':'javascript:void(0)'});
        $(this).bind('mouseup',function(){
            var c = confirm('<?php _e('Are you sure you want to convert that item? Press OK to continue or Cancel to return.', 'vice-versa') ?>');
            if(c == true){
               $("input[data-id='"+url[3]+"']").prop('checked',true);
               $("select[name='action'], select[name='action2']").val('convert');
               $("#doaction2").trigger('click'); 
            }       
        });
    });
    
    $(".viceversa-close-info-icon").css('text-decoration','none').click(function(){
       $(this).parent().hide('slow');
       $(".vv-help").show('slow');
       $.cookie('vvinfo',0);                  
    });
    
    $(".viceversa-close-icon").css('text-decoration','none').click(function(){
       $(this).parent().hide('slow');                  
    });
    
    _activate();
	
    }); // jQuery
      
</script>        
</div>
<?php
}
?>