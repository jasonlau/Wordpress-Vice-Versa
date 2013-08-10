<?php

/**
 * Module Name: Vice Versa - Post To Page
 * Plugin URI: http://jasonlau.biz
 * Description: Post To Page conversion tool.
 * Version: 1.0.3
 * Author: Jason Lau
 * Author URI: http://jasonlau.biz 
 * Copyright 2010-2012 http://jasonlau.biz
 * Disclaimer: Use at your own risk. No warranty expressed or implied.
 * Always backup your database before making changes.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 */

	if(!defined("VICEVERSA")) die("Please don't access this file directly.");
    $_VICEVERSA_MODULE_NAME = "Post To Page";
    $viceversa_button_class = ($viceversa_mode == "post2page") ? "button-primary" : "button-secondary";
    $_VICEVERSA_MODULE_BUTTON = '<input type="button" class="vv-mode action '.$viceversa_button_class.'" rel="post2page" value="'.$_VICEVERSA_MODULE_NAME.'" />';
    if($viceversa_mode == "post2page") $_VICEVERSA_ACTIVE_MODULE = new VICEVERSA_POST2PAGE();
     
    class VICEVERSA_POST2PAGE extends WP_List_Table {
    
    public $modulebuttons = array();
    
    function __construct(){
        global $status, $page;
        parent::__construct( array(
            'singular'  => __('post', 'vice-versa'),
            'plural'    => __('posts', 'vice-versa'),
            'ajax'      => false
        ) );
        $this->title = "Convert A Post To A Page";
        $this->shortname = "Post2Page";
        $this->version = "1.0.3";           
    }
    
    function column_default($item, $column_name){
        switch($column_name){
            case 'ID':
            case 'post_title':
            case 'post_date':
            case 'post_type':           
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
            'post_type' => __('Post Type', 'vice-versa'),
            'assign_to' => __('Assign To', 'vice-versa')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'ID'    => array('ID',false),
            'post_title'     => array('post_title',false),
            'post_date'  => array('post_date',false),
            'post_type' => array('post_type',false),
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
        global $wp_rewrite, $wpdb;
        if('convert' === $this->current_action()):
            $output = "<div id=\"viceversa-status\" class=\"updated\">
            <input type=\"button\" class=\"viceversa-close-icon button-secondary\" title=\"Close\" value=\"x\" />";
            if(VICEVERSA_DEBUG):
                $output .= "<strong>" . __('Test Mode', 'vice-versa') . "</strong>\n";
            endif;
                $parents = array();
                
                $do_bulk = false;
                foreach($_POST['parents'] as $parent):
                    if($parent != ""):
                        $data = explode("|", $parent);
                        if($data[3] == 0) $do_bulk = true;
                        $parents[intval($data[3])] = $data[0] . "|" . $data[2];
                    endif;
                endforeach;
                $output .= "<p>\n";
                if(!$_POST['post']):
                    $output .= __('No items were selected. Please select items using the checkboxes.', 'vice-versa');
                else:
                  foreach($_POST['post'] as $viceversa_post):
                    $viceversa_data = explode("|", $viceversa_post);
                    $viceversa_url = site_url() . "/?page_id=" . $viceversa_data[0];
                    if($do_bulk):
                        $p = $parents[0];
                    else:
                        $p = $parents[$viceversa_data[0]];
                    endif;
                    
                    $parent = ($p == "") ? "0|" . __('No Parent', 'vice-versa') . "" : $p;
                    $parent = explode("|", $parent);
                    
                    if(!VICEVERSA_DEBUG):                   
                    $wpdb->update(
                    $wpdb->posts,
                    array(
                    'post_parent' => intval($parent[0])
                    ),
                    array( 'ID' => intval($viceversa_data[0]) ),
                    array(
                    '%d'
                    ),
                    array( '%d' )
                    );
                    clean_post_cache(intval($viceversa_data[0]));
                    set_post_type(intval($viceversa_data[0]), 'page');
                    wp_set_post_categories(intval($viceversa_data[0]), array(intval($parent[0])));
                    endif;
                    
                    $permalink = get_permalink(intval($viceversa_data[0]));
                    $output .= sprintf(__('<strong>' . __('Post', 'vice-versa') . '</strong> #%s <code><a href="%s" target="_blank" title="' . __('New Permalink', 'vice-versa') . '">%s</a></code> ' . __('was successfully converted to a <strong>Page</strong> and assigned to parent', 'vice-versa') . ' #%s <code>%s</code>. <a href="%s" target="_blank" title="' . __('New Permalink', 'vice-versa') . '">' . __('New Permalink', 'vice-versa') . '</a>', 'vice-versa'), $viceversa_data[0], $permalink, $viceversa_data[2], $parent[0], $parent[1], $permalink) . "<br />\n";
                 endforeach;
                 if(!VICEVERSA_DEBUG): 
                 $wp_rewrite->flush_rules();
                 endif; 
                endif; 
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
        $p_type = 'post';
        $post_type_object = get_post_type_object($p_type); 
        
        $orderby = (!$_REQUEST['orderby']) ? '' : ' ORDER BY ' . $_REQUEST['orderby'];
        $order = (!$_REQUEST['order']) ? '' : ' ' . $_REQUEST['order'];
        $search = (!$_REQUEST['s']) ? "" : " AND " . $_REQUEST['viceversa_search_mode'] . " REGEXP '" . $_REQUEST['s'] . "'";        
        if(!current_user_can($post_type_object->cap->edit_others_posts)):
            $query = "SELECT * FROM $wpdb->posts  WHERE post_type = '$p_type' AND post_status NOT IN ( 'trash', 'auto-draft', 'inherit' ) AND post_author = " . get_current_user_id() . $search . $orderby . $order;
            else:
            $query = "SELECT * FROM $wpdb->posts  WHERE post_type = '$p_type' AND post_status NOT IN ( 'trash', 'auto-draft', 'inherit' ) " . $search . $orderby . $order;   
            endif;        
        $data = $wpdb->get_results($query);
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->set_pagination_args(array('total_items' => $total_items, 'per_page' => $per_page, 'total_pages' => ceil($total_items/$per_page)));
        return $per_page;
    }
    
    function assign_to(){
        global $wpdb;
        $output = "";
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
        return $output;
    }
    
    function set_module_buttons($modulebuttons){
        $buttons = "";
        foreach($modulebuttons as $modulebutton){
            $buttons .= $modulebutton . " ";
        }
        $this->modulebuttons = $buttons;
    }
    
    function display_module_buttons(){
        echo $this->modulebuttons;
    }
    
    function display_module(){
        global $viceversa_mode;
        $p_type = 'post';
        $per_page = $this->prepare_items();  
    ?>
    <div class="wrap" data-bulk-selector="<?php _e('Bulk Parent Selector', 'vice-versa') ?>" data-convert="<?php _e('Convert', 'vice-versa') ?>" data-are-you-sure="<?php _e('Are you sure you want to convert that item? Press OK to continue or Cancel to return.', 'vice-versa') ?>" data-duplicate-selected="<?php _e('Error: Duplicate Selected!', 'vice-versa') ?>">
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
        if(VICEVERSA_DEBUG) echo '<div class="debug-on-top"><strong>Test Mode is enabled!</strong> No changes will be made to the posts database.</div>';
        if(defined("VICEVERSA_STATUS")) echo VICEVERSA_STATUS;
        ?>
        <div id="viceversa-left-panel" class="alignleft">
        <div id="viceversa-module-buttons" class="hidden post2page"><?php
        $this->display_module_buttons();
        ?> <input class="vv-help button-secondary hidden" type="button" value="?" title="Info" /><h3>Convert A Post To A Page</h3><strong><?php _e('Items Per Page', 'vice-versa') ?>:</strong> <input type="text" id="per-page" size="4" value="<?php echo $per_page ?>" /><input class="vv-go button-secondary action" type="submit" value="<?php _e('Go', 'vice-versa') ?>" /></div>
        <div id="viceversa-search-form" class="hidden"> <select class="vv-search-mode" name="viceversa_search_mode"><option value="ID"<?php if($_REQUEST['viceversa_search_mode'] == 'ID'): ?> selected="selected"<?php endif; ?>>ID</option><option value="post_title"<?php if(!$_REQUEST['viceversa_search_mode'] || $_REQUEST['viceversa_search_mode'] == 'post_title'): ?> selected="selected"<?php endif; ?>>Title</option><option value="post_date"<?php if($_REQUEST['viceversa_search_mode'] == 'post_date'): ?> selected="selected"<?php endif; ?>>Date</option><option value="post_content"<?php if($_REQUEST['viceversa_search_mode'] == 'post_content'): ?> selected="selected"<?php endif; ?>>Content</option></select></div>
        <form id="viceversa-form" method="get"> 
        <input class="viceversa-mode" type="hidden" name="viceversa_mode" value="<?php echo $_REQUEST['viceversa_mode'] ?>" />      
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="paged" value="<?php echo $_REQUEST['paged'] ?>" />
            <input type="hidden" name="viceversa_debug" value="<?php echo $_REQUEST['viceversa_debug'] ?>" />
            <input type="hidden" class="p-type" name="p_type" value="<?php echo $p_type ?>" /><input type="hidden" name="viceversa_debug" value="<?php echo $_REQUEST['viceversa_debug'] ?>" />
            <input type="hidden" id="per-page-hidden" name="per_page" value="<?php echo $per_page ?>" />
            <div id="viceversa-search"><?php $this->search_box(__('Search'), 'get') ?></div>
        </form>
        <form id="viceversa-list-table-form" class="post2page" method="post">        
            <?php $this->display() ?>
            <input type="hidden" class="p-type" name="p_type" value="<?php echo $p_type ?>" />
        </form>
<div id="viceversa-assign-to" style="display: none;"><div class="viceversa-assign-to-menu"><?php echo $this->assign_to($p_type);?></div></div>
<div class="viceversa-debug-container"><form id="viceversa-debug-form" method="get"> <label>Test Mode:</label> <select size="1" class="viceversa-debug" name="viceversa_debug" title="Run this module in test mode. This allows you to run test conversions.">
	<option value="false">No</option>
	<option value="true"<?php
	if(VICEVERSA_DEBUG) echo ' selected="selected"';
?>>Yes</option>
</select><?php
	if(VICEVERSA_DEBUG){echo ' <span class="debug-on">Test Mode Is On</span>';} 
?><input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="paged" value="<?php echo $_REQUEST['paged'] ?>" />
            <input class="viceversa-mode" type="hidden" name="viceversa_mode" value="<?php echo $_REQUEST['viceversa_mode'] ?>" />
            <input type="hidden" id="per-page-hidden" name="per_page" value="<?php echo $per_page ?>" />
            </form>
            </div>
<br />
<code><a href="http://JasonLau.biz" target="_blank">&copy;Jason Lau</a></code> <code>[<?php _e('Vice Versa Version', 'vice-versa') ?>: <?php echo VICEVERSA_VERSION; ?> + <?php echo $this->shortname; ?> <?php _e('Version', 'vice-versa') ?> <?php echo $this->version; ?><?php
	if(VICEVERSA_DEBUG) echo ' <span class="debug-on">Test Mode</span>';
?>]</code>

</div>
    
</div>
<?php
}
        
} // class VICEVERSA_POST2PAGE
?>