<?php

/*

Plugin Name: Vice Versa
Plugin URI: http://jasonlau.biz
Description: Convert Pages to Posts and Vice Versa
Version: 2.1.0
Author: Jason Lau
Author URI: http://jasonlau.biz
Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
Copyright 2010 http://jasonlau.biz

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

define('VICEVERSA_VERSION', '2.1.0');
define('VICEVERSA_DEBUG', false); // Test this plugin

load_plugin_textdomain('vice-versa',
    '/wp-content/plugins/vice-versa/vice-versa.pot');

add_action('admin_menu', 'viceversa_admin_menu');

function viceversa_admin_menu()
{
    add_management_page('Vice Versa', 'Vice Versa', 10, __file__, 'viceversa_content');
}

function viceversa_content()
{
    global $wpdb;
    $viceversa_mode = isset($_POST['viceversa_post']) ? 'post' : 'page';
    $viceversa_order = ($_POST['viceversa_order'] == '') ? 'post_title ASC' : $_POST['viceversa_order'];
    $cat_order = (eregi('desc', $viceversa_order)) ? 'desc' : 'asc';
    $output = "<div id=\"viceversa-container\" class=\"wrap\">
        <div id=\"icon-tools\" class=\"icon32\"><br /></div> 
        <h2>Vice Versa</h2>" . __('Convert Pages to Posts and Vice Versa', 'vice-versa') . "
        <br />
        <hr />\n";
    $output .= "<input class=\"button-secondary\" type=\"button\" id=\"viceversa_view_pageform_button\" value=\"" .
        __('Convert A Page To A Post', 'vice-versa') . "\"> <input class=\"button-secondary\" type=\"button\" id=\"viceversa_view_postform_button\" value=\"" .
        __('Convert A Post To A Page', 'vice-versa') . "\">\n";

    if ($_POST['viceversa_page']):
        $output .= "<div id=\"viceversa-status\" class=\"updated\"><div id=\"viceversa-close-status\" style=\"width:100%; text-align: right;\">[<a href=\"javascript:void(0);\" id=\"viceversa-close-status-icon\" title=\"" . __('Close', 'vice-versa') . "\">x</a>]</div>";
        if(VICEVERSA_DEBUG){
            $output .= "<strong>" . __('Debug Mode', 'vice-versa') . "</strong>\n";          
        }       
        $output .= "<p>\n";
        foreach($_POST['viceversa_page'] as $viceversa_page){
            $viceversa_data = explode("|vice-versa|", $viceversa_page);
        $viceversa_url = get_bloginfo('url') . "/?p=" . $viceversa_data[0];

        // convert page to post
        $viceversa_post = array();
        $viceversa_post['ID'] = intval($viceversa_data[0]);
        $viceversa_post['guid'] = $viceversa_url;
        $viceversa_post['post_type'] = 'post';
        if(!VICEVERSA_DEBUG){
            wp_update_post($viceversa_post);
        }
        // assign to category(s)
        $cats = (!isset($_POST['cat_'.$viceversa_data[0]]) || $_POST['cat_'.$viceversa_data[0]] == '') ? array(1) : $_POST['cat_'.$viceversa_data[0]];
        $catlist = "";
        foreach($cats as $cat){
            $catlist .= $cat." ,";
        }
        $catlist = trim($catlist,' ,');
        if(!VICEVERSA_DEBUG){
           wp_set_post_categories(intval($viceversa_data[0]), $cats); 
        }        
        $output .= sprintf(__('Page #%s [<em>%s</em>] was successfully converted to a post and added to categories %s.', 'vice-versa'), $viceversa_data[0], $viceversa_data[1], $catlist) . "<br />\n";
        
        }     
        $output .= "</p></div>\n";
    endif;

    if ($_POST['viceversa_post']):
    $output .= "<div id=\"viceversa-status\" class=\"updated\">
    <div id=\"viceversa-close-status\" style=\"width:100%; text-align: right;\">[<a href=\"javascript:void(0);\" id=\"viceversa-close-status-icon\" title=\"" . __('Close', 'vice-versa') . "\">x</a>]</div>";
        if(VICEVERSA_DEBUG){
            $output .= "<strong>" . __('Debug Mode', 'vice-versa') . "</strong>\n";          
        }       
        $output .= "<p>\n";
    foreach($_POST['viceversa_post'] as $viceversa_post){
        $viceversa_data = explode("|vice-versa|", $viceversa_post);
        $viceversa_url = get_bloginfo('url') . "/?page_id=" . $viceversa_data[0];
        $parent = ($_POST['viceversa_parent_'.$viceversa_data[0]] == '') ? "0|vice-versa|" . __('No parent', 'vice-versa') . "" : $_POST['viceversa_parent_'.$viceversa_data[0]];
        $parent = explode("|",$parent);
        $viceversa_page = array();
        $viceversa_page['ID'] = intval($viceversa_data[0]);
        $viceversa_page['guid'] = $viceversa_url;
        $viceversa_page['post_type'] = 'page';
        $viceversa_page['post_parent'] = $parent[0];
        if(!VICEVERSA_DEBUG){
            wp_update_post($viceversa_page);
            wp_set_post_categories(intval($viceversa_data[0]), array(1));
        }
        $output .= sprintf(__('Post #%s [<em>%s</em>] was successfully converted to a page and assigned to parent #%s [<em>%s</em>].', 'vice-versa'), $viceversa_data[0], $viceversa_data[1], $parent[0], $parent[2]) . "<br />\n";
    }
        
        $output .= "</p></div>\n";
    endif;
    $output .= "<div id=\"viceversa-pageform-container\" class=\"metabox-holder\">
    <div class=\"meta-box-sortables\">
    <div class=\"postbox \">
    <h3><span>" . __('Page To Post', 'vice-versa') . "</span></h3>
    <div class=\"inside\">
    <form id=\"viceversa-pageform\" method=\"POST\" action=\"#\">
    <p>" . __('Select pages and categories to convert to posts.', 'vice-versa') . "</p>\n";
    
    // START PAGE TABLE
    $output .= "<strong>" . __('Pages', 'vice-versa') . ":</strong>";
    $viceversa_sql = "SELECT ID FROM " . $wpdb->posts .
        " WHERE post_type = 'page' ORDER BY " . $viceversa_order;
    $viceversa_pages = $wpdb->get_col($viceversa_sql);
    $num_pages = count($viceversa_pages);
    $overflow = ($num_pages > 20) ? ' style="height:200px;overflow-x:hidden;overflow-y:auto;"' : ' style="overflow-x:hidden;overflow-y:auto;"';
    $x = 1;
    $page_options = '<div id="page-options">
    <table id="page-options-table" class="widefat" style="margin-top:1px">
    <thead>
	<tr>
	<th scope="col" id="cb-pages" class="manage-column column-cb check-column"><input class="viceversa-checktable" type="checkbox" rel="page-options"></th>
    <th scope="col" id="idn" class="manage-column column-idn">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:90%">' . __('Page (Categories)', 'vice-versa') . '</th>       <th scope="col" id="view" class="manage-column column-view" style="text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-pages-bigger" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-pages-smaller" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>
	</tr>
	</thead>
    </table>
    <div id="the-pages-list"' . $overflow . '>
    <table id="the-pages-list-table" class="widefat" style="margin-top:1px">
    <tbody class="list:tag">
    '; 
    foreach ($viceversa_pages as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $altclass = ($x % 2) ? ' class="alternate"' : '';
        $page_options .= '<tr id="tag-' . $x . '"' . $altclass . '>';
        $page_options .= '<th scope="row" class="check-column" style="padding:3px"><input name="viceversa_page[]" type="checkbox" value="' . $post->ID . '|vice-versa|';
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $page_options .= $post_title;
        $page_options .= '" /></th>
        <td class="page-id column-page-id" valign="middle" style="padding:3px">';
        $page_options .= $post->ID;
        $page_options .= '</td>
        <td class="name column-name" valign="middle" style="padding:3px;width:95%" nowrap="nowrap">';
        $page_options .= $post_title;
        $page_options .= " (<a href=\"javascript:void(0)\" class=\"viceversa-subcat-opener\" rel=\"viceversa-subcat-" . $post->ID . "\" title=\"" . __('Toggle Categories', 'vice-versa') . "\">+</a>)<div class=\"viceversa-subcat viceversa-subcat-" . $post->ID . "\"  style=\"display:none\">" . viceversa_categories($post->ID, $cat_order) . "</div>";
        $page_options .= '</td>        
        </tr>
        ';
        $x++;
    }
    $page_options .= '</tbody>
    </table>
    </div>
    <table class="widefat" style="margin-top:1px">
    <tfoot>
	<tr>
	<th scope="col" id="cb-pages" class="manage-column column-cb check-column"><input class="viceversa-checktable" type="checkbox" rel="page-options"></th>
    <th scope="col" id="idn" class="manage-column column-idn">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:90%">' . __('Page (Categories)', 'vice-versa') . '</th>       <th scope="col" id="view" class="manage-column column-view" style="text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-pages-bigger" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-pages-smaller" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>
	</tr>
	</tfoot>
    </table></div>
    ';
    $output .= $page_options;
    $wpdb->flush();
    // END PAGE TABLE
    $output .= "<br /><strong>" . __('Bulk Category Select', 'vice-versa') . ":</strong><br /> ";
    $output .= viceversa_categories('', $cat_order);
    
    $output .= "<br />\n";
    $output .= "<input class=\"button-secondary action\" type=\"submit\" name=\"viceversa_pageform_button\" value=\"" .
        __('Submit', 'vice-versa') . "\">\n";
    $output .= "</form>
    </div>
    </div>
    </div>
    </div>\n";

    $output .= "<div id=\"viceversa-postform-container\" class=\"metabox-holder\">
    <div class=\"meta-box-sortables\">
    <div class=\"postbox \">
    <h3><span>" . __('Post To Page', 'vice-versa') . "</span></h3>
    <div class=\"inside\">
    <form id=\"viceversa-postform\" method=\"POST\" action=\"#\">
    <p>" . __('Select posts and a parents(optional) to convert to pages.', 'vice-versa') . "</p>\n";
    
    
    // START POST TABLE
    $output .= "<strong>" . __('Posts', 'vice-versa') . ":</strong>";
    $viceversa_sql2 = "SELECT ID FROM " . $wpdb->posts .
        " WHERE post_type = 'post'  ORDER BY " . $viceversa_order;
    $viceversa_posts = $wpdb->get_col($viceversa_sql2);
    $num_posts = count($viceversa_posts);
    $overflow = ($num_posts > 20) ? ' style="height:200px;overflow-x:hidden;overflow-y:auto;"' : ' style="overflow-x:hidden;overflow-y:auto;"';
    $x = 1;
    $post_options = '<div id="post-options">
    <table class="widefat" style="margin-top:1px">
    <thead>
	<tr>
	<th scope="col" id="cb-posts" class="manage-column column-cb check-column"><input type="checkbox" class="viceversa-checktable" rel="post-options"></th>
    <th scope="col" class="manage-column column-name">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:90%">' . __('Post (Parents)', 'vice-versa') . '</th>
    <th scope="col" id="view" class="manage-column column-view" style="text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-posts-bigger" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-pages-smaller" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>
	</tr>
	</thead>
    </table>   
    <div id="the-posts-list"' . $overflow . '>
    <table id="the-posts-list-table" class="widefat" style="margin-top:1px;">
    '; 
    foreach ($viceversa_posts as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $altclass = ($x % 2) ? ' class="alternate"' : '';
        $post_options .= '<tr id="tag-' . $x . '"' . $altclass . '>';
        $post_options .= '<th scope="row" class="check-column" style="padding:3px"><input name="viceversa_post[]" type="checkbox" value="' . $post->ID . '|vice-versa|';
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $post_options .= $post_title;
        $post_options .= '" /></th>
        <td class="page-id column-page-id" valign="middle" style="padding:3px">';
        $post_options .= $post->ID;
        $post_options .= '</td>
        <td class="name column-name" valign="middle" style="padding:3px" width="95%" nowrap="nowrap">';
        $post_options .= $post_title;
        $post_options .= " (<a href=\"javascript:void(0)\" class=\"viceversa-subcat-opener\" rel=\"viceversa-subcat-" . $post->ID . "\"title=\"" . __('Toggle Parents', 'vice-versa') . "\">+</a>)<div class=\"viceversa-subcat viceversa-subcat-" . $post->ID . "\"  style=\"display:none\">" . viceversa_parents($viceversa_pages, $post->ID) . "</div>";
        $post_options .= '</td>        
        </tr>
        ';
        $x++;
    }
    $post_options .= '</table></div>
    <table class="widefat" style="margin-top:1px;">
    <tfoot>
	<tr>
	<th scope="col" id="cb-posts" class="manage-column column-cb check-column"><input type="checkbox" class="viceversa-checktable" rel="post-options"></th>
    <th scope="col" class="manage-column column-name">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:90%">' . __('Post (Parents)', 'vice-versa') . '</th>
    <th scope="col" id="view" class="manage-column column-view" style="text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-posts-bigger" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-pages-smaller" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>
	</tr>
	</tfoot>
    </table></div>
    ';
    $output .= $post_options;
    $wpdb->flush();
    // END POST TABLE
    $output .= "<br /><strong>" . __('Bulk Parent Select', 'vice-versa') . ":</strong> ";
    $output .= viceversa_parents($viceversa_pages, 0);
    
    $output .= "<br /><br /><input class=\"button-secondary action\" type=\"submit\" name=\"viceversa_postform_button\" value=\"" .
        __('Submit', 'vice-versa') . "\">\n";
    $output .= "</form>\n
    </div>
    </div>
    </div>
    </div>\n";

    $output .= "<div id=\"viceversa-orderform-container\" class=\"metabox-holder\">
    <div class=\"meta-box-sortables\"><div class=\"postbox \">
    <h3><span>" . __('Order Lists By', 'vice-versa') . "</span></h3>
    <div class=\"inside\"><form id=\"viceversa-orderform\" method=\"POST\" action=\"#\">
    <select name=\"viceversa_order\" onchange=\"this.form.submit();\">
       <option value=\"post_title ASC\"";
    if ($_POST['viceversa_order'] == '' || $_POST['viceversa_order'] ==
        'post_title ASC'):
        $output .= " selected=\"selected\"";
    endif;
    $output .= ">" . __('Title', 'vice-versa') . " " . __('Ascending', 'vice-versa') .
        "</option>
       <option value=\"post_title DESC\"";
    if ($_POST['viceversa_order'] == 'post_title DESC'):
        $output .= " selected=\"selected\"";
    endif;
    $output .= ">" . __('Title', 'vice-versa') . " " . __('Descending', 'vice-versa') .
        "</option>      
       <option value=\"ID DESC\"";
    if ($_POST['viceversa_order'] == 'ID DESC'):
        $output .= " selected=\"selected\"";
    endif;
    $output .= ">" . __('ID', 'vice-versa') . " " . __('Descending', 'vice-versa') .
        "</option>
       <option value=\"ID ASC\"";
    if ($_POST['viceversa_order'] == 'ID ASC'):
        $output .= " selected=\"selected\"";
    endif;
    $output .= ">" . __('ID', 'vice-versa') . " " . __('Ascending', 'vice-versa') .
        "</option>
       <option value=\"post_date DESC\"";
    if ($_POST['viceversa_order'] == 'post_date DESC'):
        $output .= " selected=\"selected\"";
    endif;
    $output .= ">" . __('Date', 'vice-versa') . " " . __('Descending', 'vice-versa') .
        "</option>
       <option value=\"post_date ASC\"";
    if ($_POST['viceversa_order'] == 'post_date ASC'):
        $output .= " selected=\"selected\"";
    endif;
    $output .= ">" . __('Date', 'vice-versa') . " " . __('Ascending', 'vice-versa') .
        "</option>
       </select>
       </form>
       </div>
       </div>
       </div>
       </div>\n";
    $output .= "<hr /><div id=\"icon-tools\" class=\"icon32\"><br /></div><h2>Vice Versa</h2>" .
        __('Convert Pages to Posts and Vice Versa', 'vice-versa') .
        " [" . __('Version', 'vice-versa') . ": " . VICEVERSA_VERSION . "] &copy;2010 <a href=\"http://JasonLau.biz\" target=\"_blank\">JasonLau.biz</a>";
    $output .= "<div id=\"viceversa-mode\" style=\"display:none;\">" . $viceversa_mode . "</div></div><br />\n";
       
        
        
?>
<script type="text/javascript">
<!--

jQuery(function(){
    jQuery("#viceversa-status").css('margin-bottom','0px');
    jQuery("#viceversa-pageform-container").css('display','none');
    jQuery("#viceversa-postform-container").css('display','none');
    jQuery("#viceversa-orderform-container").css({ 'display' : 'none', 'margin-top' : '-20px' });
    jQuery(".inside").css('padding','10px');
    jQuery("#viceversa_view_pageform_button").click(function(){
        jQuery(this).addClass("button-primary");
        jQuery("#viceversa_view_postform_button").removeClass("button-primary");
        jQuery("#viceversa-pageform-container").show('slow');
        jQuery("#viceversa-orderform-container").show('slow');
        jQuery("#viceversa-postform-container").hide('slow');
    });
    jQuery("#viceversa_view_postform_button").click(function(){
        jQuery(this).addClass("button-primary");
        jQuery("#viceversa_view_pageform_button").removeClass("button-primary");
        jQuery("#viceversa-postform-container").show('slow');
        jQuery("#viceversa-orderform-container").show('slow');
        jQuery("#viceversa-pageform-container").hide('slow');           
    });
    switch(jQuery("#viceversa-mode").html()){
        case 'post':
        jQuery("#viceversa_view_postform_button").addClass("button-primary");
        jQuery("#viceversa_view_pageform_button").removeClass("button-primary");
        jQuery("#viceversa-postform-container").show('slow');
        jQuery("#viceversa-orderform-container").show('slow');
        jQuery("#viceversa-pageform-container").hide('slow');
        break;
            
        case 'page':
        jQuery("#viceversa_view_pageform_button").addClass("button-primary");
        jQuery("#viceversa_view_postform_button").removeClass("button-primary");
        jQuery("#viceversa-pageform-container").show('slow');
        jQuery("#viceversa-orderform-container").show('slow');
        jQuery("#viceversa-postform-container").hide('slow');
        break;
    }
    
    jQuery(".viceversa-subcat-opener").click(function(){
        var c = jQuery(this).attr('rel');
        if(jQuery("." + c).is(':visible')){
          jQuery("." + c).hide('slow'); 
          jQuery(this).html('+');
        } else {
          jQuery("." + c).show('slow'); 
          jQuery(this).html('-'); 
        }       
    });
    
    jQuery(".viceversa-checkall").change(function(){
        var c = jQuery(this).attr('rel');
        if(c == '' || jQuery(this).hasClass("viceversa-master-checkall")){
            if(!jQuery(this).is(':checked')){
          jQuery(".cat-item").not(this).attr('checked',''); 
        } else {
          jQuery(".cat-item").not(this).attr('checked','checked'); 
        }
        } else {
            if(!jQuery(this).is(':checked')){
          jQuery(".term-" + c).not(this).attr('checked',''); 
        } else {
          jQuery(".term-" + c).not(this).attr('checked','checked'); 
        }
        }      
    });
    
    jQuery(".viceversa-select-all-parents").change(function(){        
        jQuery(".parent-select").not(this).val(jQuery(this).val());  
    });
    
    jQuery(".viceversa-resize-pages-bigger").click(function(){
        var h = jQuery('#the-pages-list').outerHeight();
        var maxh = jQuery('#the-pages-list-table').outerHeight()+1;
        if(jQuery('#the-pages-list').outerHeight()+100 > maxh){
            jQuery("#the-pages-list").css('height',maxh + 'px');
        } else {
           jQuery("#the-pages-list").css('height',jQuery('#the-pages-list').outerHeight()+100 + 'px'); 
        }       
    });
    
    jQuery(".viceversa-resize-pages-smaller").click(function(){
        var h = jQuery('#the-pages-list').outerHeight();
        if(h-100 > 100){
            jQuery("#the-pages-list").css('height',jQuery('#the-pages-list').outerHeight()-100 + 'px');
        }  else {
             jQuery("#the-pages-list").css('height','100px');
        }             
    });
    
    jQuery(".viceversa-resize-posts-bigger").click(function(){
        var h = jQuery('#the-posts-list').outerHeight();
        var maxh = jQuery('#the-posts-list-table').outerHeight()+1;
        if(jQuery('#the-posts-list').outerHeight()+100 > maxh){
            jQuery("#the-posts-list").css('height',maxh + 'px');
        } else {
           jQuery("#the-posts-list").css('height',jQuery('#the-posts-list').outerHeight()+100 + 'px'); 
        }       
    });
    
    jQuery(".viceversa-resize-posts-smaller").click(function(){
        var h = jQuery('#the-posts-list').outerHeight();
        if(h-100 > 100){
            jQuery("#the-posts-list").css('height',jQuery('#the-posts-list').outerHeight()-100 + 'px');
        } else {
            jQuery("#the-posts-list").css('height','100px');
        }             
    });
    
    jQuery(".viceversa-resize-cats-bigger").click(function(){
        var c = jQuery(this).attr('rel');
        var h = jQuery('#the-cats-list-'+c).outerHeight();
        var maxh = jQuery('#the-cats-list-table-'+c).outerHeight()+1;
        if(jQuery('#the-cats-list-'+c).outerHeight()+100 > maxh){
            jQuery('#the-cats-list-'+c).css('height',maxh + 'px');
        } else {
           jQuery('#the-cats-list-'+c).css('height',jQuery('#the-cats-list-'+c).outerHeight()+100 + 'px'); 
        }       
    });
    
    jQuery(".viceversa-resize-cats-smaller").click(function(){
        var c = jQuery(this).attr('rel');
        var h = jQuery('#the-cats-list-'+c).outerHeight();
        if(h-100 > 100){
            jQuery('#the-cats-list-'+c).css('height',jQuery('#the-cats-list-'+c).outerHeight()-100 + 'px');
        } else {
            jQuery('#the-cats-list-'+c).css('height','100px');
        }              
    });
    
    jQuery(".viceversa-checktable").click(function(){
        var c = jQuery(this).attr('rel');
        if(jQuery(this).is(':checked')){
           jQuery('#'+c+' input:checkbox').attr('checked','checked'); 
        } else {
            jQuery('#'+c+' input:checkbox').attr('checked','');
        }                 
    });
    
    jQuery("#viceversa-close-status-icon").click(function(){
       jQuery("#viceversa-status").hide('slow');                  
    });
    
    jQuery("#viceversa-close-status-icon").css('text-decoration','none');                
  
});   
 -->
</script>
<?php
    echo $output;
}

function viceversa_categories($item_id, $cat_order)
{
    $categories = get_categories('hide_empty=0&order=' . $cat_order);
    $num_cats = count($categories);
    $overflow = ($num_cats > 10) ? ' style="height:200px;overflow-x:hidden;overflow-y:auto;"' : ' style="overflow-x:hidden;overflow-y:auto;"';
    $x = 1;
    if($item_id == ""){
        $iclass = ' viceversa-checkall';
        $hclass = '  viceversa-master-checkall';
        $name = '';        
    } else {
        $hclass = '';
        $iclass = '"';
        $name =  ' name="cat_' . $item_id . '[]"';
    }
    $options = '<div id="cats-list-' . $item_id . '">
    <table class="widefat" style="margin-top:1px">
    <thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style="padding:4px"><input type="checkbox" rel="cats-list-' . $item_id . '" class="viceversa-checktable cat-item term-0 ' . $iclass . '' . $hclass . '"></th>
	<th scope="col" id="name" class="manage-column column-name" style="padding:4px" nowrap="nowrap">' . __('Category', 'vice-versa') . '</th>
    <th scope="col" id="count" class="manage-column column-count" style="padding:4px" nowrap="nowrap">' . __('Count', 'vice-versa') . '</th>
    <th scope="col" id="count" class="manage-column column-count" style="padding:4px; width:90%; text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-cats-bigger" rel="' . $item_id . '" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-cats-smaller" rel="' . $item_id . '" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>
	</tr>
	</thead>
    </table>
    <div id="the-cats-list-' . $item_id . '"' . $overflow . '>
    <table id="the-cats-list-table-' . $item_id . '" class="widefat" style="margin-top:1px">
    <tbody class="list:tag">
    ';   
    foreach ($categories as $cat) {
        $altclass = ($x % 2) ? ' class="alternate"' : '';
        $options .= '<tr id="tag-' . $x . '"' . $altclass . '>';
        $options .= '<th scope="row" class="check-column" style="padding:3px"><input' . $name . ' type="checkbox" rel="' . $cat->term_id . '" value="' . $cat->term_id . '" class="cat-item term-' . $cat->term_id . ' ' . $iclass . '" /></th>
        <td class="name column-name" valign="middle" style="padding:3px" nowrap="nowrap">';
        $options .= $cat->cat_name;
        $options .= '</td><td class="count column-count" valign="middle" style="padding:3px">' . $cat->category_count . '';
        $options .= '</td>
        <td style="width:90%;"></td>        
        </tr>
        ';
        $x++;
    }
    $options .= '</tbody>  
    </table>
    </div>
    <table class="widefat" style="margin-top:1px">
    <tfoot>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style="padding:4px"><input type="checkbox" rel="cats-list-' . $item_id . '" class="viceversa-checktable cat-item term-0 ' . $iclass . '' . $hclass . '"></th>
	<th scope="col" id="name" class="manage-column column-name" style="padding:4px" nowrap="nowrap">' . __('Category', 'vice-versa') . '</th>
    <th scope="col" id="count" class="manage-column column-count" style="padding:4px" nowrap="nowrap">' . __('Count', 'vice-versa') . '</th>
    <th scope="col" id="count" class="manage-column column-count" style="padding:4px; width:90%; text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-cats-bigger" rel="' . $item_id . '" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-cats-smaller" rel="' . $item_id . '" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>
	</tr>
	</tfoot>
    </table>
    </div>
    ';
    return $options;
}

function viceversa_parents($viceversa_pages, $item_id)
{
    if($item_id == ''){
        $name = '';
        $iclass = 'parent-select parent-item-0 viceversa-select-all-parents';
    } else {
       $name = ' name="viceversa_parent_'.$item_id.'"';
       $iclass = 'parent-select parent-item-'.$item_id.'';
    }
    $output .= "<select class=\"".$iclass."\"".$name.">\n<option selected=\"selected\" value=\"\">" .
        __('Select A Page', 'vice-versa') . "</option>\n";
    foreach ($viceversa_pages as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $output .= "<option value=\"" . $post->ID . "|vice-versa|";
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $output .= $post_title . "\">";
        $output .= $post_title . "</option>\n";
    }
    $output .= "</select>\n";
    return $output;
}

?>