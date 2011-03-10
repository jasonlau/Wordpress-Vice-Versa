<?php

/*

Plugin Name: Vice Versa
Plugin URI: http://jasonlau.biz
Description: Convert Pages to Posts and Vice Versa
Version: 2.1.3
Author: Jason Lau
Author URI: http://jasonlau.biz
Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
Copyright 2010-2011 http://jasonlau.biz

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

define('VICEVERSA_VERSION', '2.1.2');
define('VICEVERSA_DEBUG', false); // Test this plugin

load_plugin_textdomain('vice-versa',
    '/wp-content/plugins/vice-versa/vice-versa.pot');

add_action('admin_menu', 'viceversa_admin_menu');

function viceversa_admin_menu()
{
  add_submenu_page('tools.php', 'Vice Versa', 'Vice Versa', 'publish_pages', 'vice-versa', 'viceversa_content');
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
    <div>
    <div class=\"postbox \">
    <h3><span>" . __('Page To Post', 'vice-versa') . "</span></h3>
    <div class=\"inside\">
    <form id=\"viceversa-pageform\" method=\"POST\" action=\"#\">
    <p>" . __('Select pages and categories to convert to posts.', 'vice-versa') . "</p>\n";
    
    // START PAGE TABLE
    
    $viceversa_sql = "SELECT ID FROM " . $wpdb->posts .
        " WHERE post_type = 'page' ORDER BY " . $viceversa_order;
    $viceversa_pages = $wpdb->get_col($viceversa_sql);
    $num_pages = count($viceversa_pages);
    $overflow = ($num_pages > 20) ? ' style="height:200px;overflow-x:hidden;overflow-y:auto;"' : ' style="overflow-x:hidden;overflow-y:auto;"';
    $x = 1;
    $output .= '<strong>' . __('Page', 'vice-versa') . ':</strong> <input type="button" value="&lt;&lt;" id="pagenum-pages-down" /><input type="text" value="1" id="pagenum-pages" size="5" /><input type="button" value="&gt;&gt;" id="pagenum-pages-up" /> <strong>' . __('Limit', 'vice-versa') . ':</strong> <input type="button" value="&nbsp;-&nbsp;" id="limit-pages-down" /><input type="text" value="10" id="limit-pages" size="5" /><input type="button" value="&nbsp;+&nbsp;" id="limit-pages-up" />  <strong>' . __('Total Items', 'vice-versa') . ':</strong> <span id="numpages-number">' . $num_pages . '</span>';
    $page_options = '<div id="page-options">
    <table id="page-options-table" class="widefat" style="margin-top:1px">
    <thead>
	<tr>
	<th scope="col" id="cb-pages" class="manage-column column-cb check-column"><input class="viceversa-checktable" type="checkbox" rel="page-options"></th>
    <th scope="col" id="idn" class="manage-column column-idn">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Page (Categories)', 'vice-versa') . '</th>
    <th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Date', 'vice-versa') . '</th>
	</tr>
	</thead>
    <tbody class="list:pages pages-list">
    '; 
    foreach ($viceversa_pages as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $altclass = ($x % 2) ? ' class="alternate hidden page-item item-' . $x . '"' : ' class="hidden page-item item-' . $x . '"';
        $page_options_c .= '<tr id="pages-' . $x . '"' . $altclass . '>';
        $page_options_c .= '<td scope="row" class="check-column" style="padding:3px"><input name="viceversa_page[]" type="checkbox" value="' . $post->ID . '|vice-versa|';
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $page_options_c .= $post_title;
        $page_options_c .= '" /></td>
        <td class="page-id column-page-id sortable" valign="middle" style="padding:3px">';
        $page_options_c .= $post->ID;
        $page_options_c .= '</td>
        <td class="name column-name sortable" valign="middle" style="padding:3px;width:50%" nowrap="nowrap">';
        $page_options_c .= $post_title;
        $page_options_c .= " (<a href=\"javascript:void(0)\" class=\"viceversa-subcat-opener\" rel=\"viceversa-subcat-" . $post->ID . "\" title=\"" . __('Toggle Categories', 'vice-versa') . "\">+</a>)<div class=\"viceversa-subcat viceversa-subcat-" . $post->ID . "\"  style=\"display:none\">" . viceversa_categories($post->ID, $cat_order) . "</div>";
        $page_options_c .= '</td>
        <td class="sortable" nowrap="nowrap">' . $post->post_date . '</td>       
        </tr>
        ';
        $x++;
    }
    $page_options .= $page_options_c;
    $page_options .= '</tbody>
    <tfoot>
	<tr>
	<th scope="col" id="cb-pages" class="manage-column column-cb check-column"><input class="viceversa-checktable" type="checkbox" rel="page-options"></th>
    <th scope="col" id="idn" class="manage-column column-idn">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Page (Categories)', 'vice-versa') . '</th>
    <th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Date', 'vice-versa') . '</th>
	</tr>
	</tfoot>
    </table></div>
    ';
    $output .= $page_options;
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
    
    $viceversa_sql2 = "SELECT ID FROM " . $wpdb->posts .
        " WHERE post_type = 'post'  ORDER BY " . $viceversa_order;
    $viceversa_posts = $wpdb->get_col($viceversa_sql2);
    $num_posts = count($viceversa_posts);
    $output .= '<strong>' . __('Page', 'vice-versa') . ':</strong> <input type="button" value="&lt;&lt;" id="pagenum-posts-down" /><input type="text" value="1" id="pagenum-posts" size="5" /><input type="button" value="&gt;&gt;" id="pagenum-posts-up" /> <strong>' . __('Limit', 'vice-versa') . ':</strong> <input type="button" value="&nbsp;-&nbsp;" id="limit-posts-down" /><input type="text" value="10" id="limit-posts" size="5" /><input type="button" value="&nbsp;+&nbsp;" id="limit-posts-up" />  <strong>' . __('Total Items', 'vice-versa') . ':</strong> <span id="numposts-number">' . $num_posts . '</span>';
    $overflow = ($num_posts > 20) ? ' style="height:200px;overflow-x:hidden;overflow-y:auto;"' : ' style="overflow-x:hidden;overflow-y:auto;"';
    $x = 1;
    $post_options = '<div id="post-options">
    <table class="widefat" style="margin-top:1px">
    <thead>
	<tr>
	<th scope="col" id="cb-posts" class="manage-column column-cb check-column"><input type="checkbox" class="viceversa-checktable" rel="post-options"></th>
    <th scope="col" class="manage-column column-name">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Post (Parents)', 'vice-versa') . '</th>
    <th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Date', 'vice-versa') . '</th>
    </tr>
	</thead>
    <tbody>
    '; 
    foreach ($viceversa_posts as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $altclass = ($x % 2) ? ' class="alternate hidden post-item item-' . $x . '"' : ' class="hidden post-item item-' . $x . '"';
        $post_options .= '<tr id="post-' . $x . '"' . $altclass . '>';
        $post_options .= '<td scope="row" class="check-column" style="padding:3px"><input name="viceversa_post[]" type="checkbox" value="' . $post->ID . '|vice-versa|';
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $post_options .= $post_title;
        $post_options .= '" /></td>
        <td class="page-id column-page-id sortable" valign="middle" style="padding:3px">';
        $post_options .= $post->ID;
        $post_options .= '</td>
        <td class="name column-name sortable" valign="middle" style="padding:3px" width="50%" nowrap="nowrap">';
        $post_options .= $post_title;
        $post_options .= " (<a href=\"javascript:void(0)\" class=\"viceversa-subcat-opener\" rel=\"viceversa-subcat-" . $post->ID . "\"title=\"" . __('Toggle Parents', 'vice-versa') . "\">+</a>)<div class=\"viceversa-subcat viceversa-subcat-" . $post->ID . "\"  style=\"display:none\">" . viceversa_parents($viceversa_pages, $post->ID) . "</div>";
        $post_options .= '</td> 
        <td nowrap="nowrap" class="sortable">' . $post->post_date . '</td>       
        </tr>
        ';
        $x++;
    }
    $post_options .= '</tbody>
    <tfoot>
	<tr>
	<th scope="col" id="cb-posts" class="manage-column column-cb check-column"><input type="checkbox" class="viceversa-checktable" rel="post-options"></th>
    <th scope="col" class="manage-column column-name">' . __('ID', 'vice-versa') . '</th>
	<th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Post (Parents)', 'vice-versa') . '</th>
    <th scope="col" id="name" class="manage-column column-name" style="width:50%">' . __('Date', 'vice-versa') . '</th>
	</tr>
	</tfoot>
    </table></div>
    ';
    $output .= $post_options;
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
    $output .= "<hr /><a href=\"http://www.gnu.org/licenses/gpl.html\" target=\"_blank\"><img src=\"http://www.gnu.org/graphics/gplv3-127x51.png\" alt=\"GNU/GPL\" border=\"0\" /></a><em><strong>Share And Share-Alike!</strong></em><br />
<code><strong>Another <em><strong>Quality</strong></em> Work From  <a href=\"http://JasonLau.biz\" target=\"_blank\">JasonLau.biz</a></strong> - &copy;2011 Jason Lau</code> <code>[" . __('Vice Versa', 'vice-versa') . " " . __('Version', 'vice-versa') . ": " . VICEVERSA_VERSION . "]</code>";

   
    $output .= "<div id=\"viceversa-mode\" style=\"display:none;\">" . $viceversa_mode . "</div></div><br />\n";
       
        
        
?>
<style type="text/css">
<!--
	.hidden{
	   display: none;
	}
-->
</style>
<script type="text/javascript">
<!--

jQuery(function(){
    
    jQuery("#viceversa-status").css('margin-bottom','0px');
    jQuery("#viceversa-pageform-container").css('display','none');
    jQuery("#viceversa-postform-container").css('display','none');
    jQuery("#viceversa-orderform-container").css({ 'display' : 'none', 'margin-top' : '-20px' });
    jQuery(".inside").css('padding','10px');
    jQuery("#viceversa-close-status-icon").css('text-decoration','none');
    
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
        jQuery("#viceversa-orderform-container").show('slow');
        jQuery("#viceversa-pageform-container").hide('slow');
        jQuery("#viceversa-postform-container").show('slow');
        show_content('post-item', jQuery("#pagenum-posts").val(), jQuery("#limit-posts").val());
        disabling('posts');        
    });
        
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
    
    jQuery("#pagenum-pages").change(function(){
        var tp = parseInt(jQuery("#numpages-number").html())/parseInt(jQuery("#limit-pages").val());
         show_content('page-item', jQuery("#limit-pages").val(), jQuery(this).val());
         if(parseInt(jQuery("#pagenum-pages").val()) >= tp){ 
            jQuery("#pagenum-pages-up").attr('disabled','disabled');
        } else {
            jQuery("#pagenum-pages-up").attr('disabled','');
        }
        if(parseInt(jQuery("#pagenum-pages").val()) < 2){ 
            jQuery("#pagenum-pages-down").attr('disabled','disabled');
        } else {
            jQuery("#pagenum-pages-down").attr('disabled','');
        }      
    });
    
    jQuery("#pagenum-pages-up").click(function(){        
        paging('pages', 'page', 'up');
    });
    
    jQuery("#pagenum-pages-down").click(function(){
        paging('pages', 'page', 'down');             
    });
    
    jQuery("#limit-pages-up").click(function(){ 
        paging('pages', 'limit', 'up');         
    });
    
    jQuery("#limit-pages-down").click(function(){
        paging('pages', 'limit', 'down');
    });
    
    jQuery("#limit-pages").bind('change', function(){
        var tp = parseInt(jQuery("#numpages-number").html());
         show_content('page-item', jQuery("#pagenum-pages").val(), jQuery(this).val());
         if(parseInt(jQuery("#limit-pages").val()) < 2){
            jQuery("#limit-pages-down").attr('disabled','disabled');
        } else {
            jQuery("#limit-pages-down").attr('disabled','');
        }
        if(parseInt(jQuery("#limit-pages").val()) == tp){
            jQuery("#limit-pages-up").attr('disabled','disabled');
        } else {
            jQuery("#limit-pages-up").attr('disabled','');
        }
               
    });
    
    jQuery("#pagenum-posts").bind('change', function(){
        var tp = parseInt(jQuery("#numposts-number").html())/parseInt(jQuery("#limit-posts").val());
         show_content('post-item', jQuery(this).val(), jQuery("#limit-posts").val());
         if(parseInt(jQuery("#pagenum-posts").val()) >= tp){ 
            jQuery("#pagenum-posts-up").attr('disabled','disabled');
        } else {
            jQuery("#pagenum-posts-up").attr('disabled','');
        }
        if(parseInt(jQuery("#pagenum-posts").val()) < 2){ 
            jQuery("#pagenum-posts-down").attr('disabled','disabled');
        } else {
            jQuery("#pagenum-posts-down").attr('disabled','');
        }
               
    });
    
    jQuery("#pagenum-posts-up").click(function(){        
        paging('posts', 'page', 'up');     
    });
    
    jQuery("#pagenum-posts-down").click(function(){  
        paging('posts', 'page', 'down');             
    });
    
    jQuery("#limit-posts-up").click(function(){ 
        paging('posts', 'limit', 'up');            
    });
    
    jQuery("#limit-posts-down").click(function(){
        paging('posts', 'limit', 'down');
    });
    
    jQuery("#limit-posts").bind('change',function(){
        var tp = parseInt(jQuery("#numposts-number").html());
         show_content('post-item', jQuery("#pagenum-posts").val(), jQuery(this).val());
         if(parseInt(jQuery("#limit-posts").val()) < 2){
            jQuery("#limit-posts-down").attr('disabled','disabled');
        } else {
            jQuery("#limit-posts-down").attr('disabled','');
        }
        if(parseInt(jQuery("#limit-posts").val()) == tp){
            jQuery("#limit-posts-up").attr('disabled','disabled');
        } else {
            jQuery("#limit-posts-up").attr('disabled','');
        }           
    });
    
    jQuery('.manage-column').not('check-column').css('cursor','pointer');
    jQuery('input:button').css('cursor','pointer');
     function show_content(class_name, page_number, limit){
       var p = parseInt(page_number);
       var l = parseInt(limit); 
       var pagenum = (!page_number) ? 1 : p;
       var start = (pagenum*l)-(l-1);
       var end = start+l;
       var num = 0;
       var startnum = start;      
       jQuery('.' + class_name).hide();
       for(var i=0; i<l; i++){
        jQuery('.' + class_name).each(function(){
           if(jQuery(this).hasClass('item-' + startnum)){
            jQuery(this).show();
           } 
        });       
        startnum++;
       }
    }
<?php if($viceversa_mode == "page" || $viceversa_mode == ''): ?> 
show_content('page-item', 1, 10); 
<?php else: ?> 
show_content('post-item', 1, 10); 
<?php endif; ?>
    
    function paging(pages_or_posts, page_or_limit, up_or_down){
        var page = parseInt(jQuery("#pagenum-" + pages_or_posts).val());
        var limit = parseInt(jQuery("#limit-" + pages_or_posts).val());
        var total = parseInt(jQuery("#num" + pages_or_posts + "-number").html());
        var total_pages = Math.ceil(total/limit);
        switch(up_or_down){
            case 'up':
            if(page_or_limit == 'limit'){
                var next = (parseInt(jQuery("#limit-" + pages_or_posts).val())+1 > total) ? total : parseInt(jQuery("#limit-" + pages_or_posts).val())+1;
                jQuery("#limit-" + pages_or_posts).val(next);
                if(next >= total){
                   jQuery("#pagenum-" + pages_or_posts).val('1');
                }                
            } else {
               var next = (parseInt(jQuery("#pagenum-" + pages_or_posts).val())+1 > total_pages) ? total_pages : parseInt(jQuery("#pagenum-" + pages_or_posts).val())+1;
               jQuery("#pagenum-" + pages_or_posts).val(next);
               if(next >= total){
                   jQuery("#pagenum-" + pages_or_posts).val(total);
                } 
            }           
            break;
            
            default:
            if(page_or_limit == 'limit'){
                var next = (parseInt(jQuery("#limit-" + pages_or_posts).val())-1 < 2) ? 1 : parseInt(jQuery("#limit-" + pages_or_posts).val())-1;
                jQuery("#limit-" + pages_or_posts).val(next);                
            } else {
               var next = (parseInt(jQuery("#pagenum-" + pages_or_posts).val())-1 > total) ? total : parseInt(jQuery("#pagenum-" + pages_or_posts).val())-1;
               jQuery("#pagenum-" + pages_or_posts).val(parseInt(jQuery("#pagenum-" + pages_or_posts).val())-1); 
            }            
        }
        var p = (pages_or_posts == 'pages') ? 'page' : 'post';
        show_content(p + '-item', jQuery("#pagenum-" + pages_or_posts).val(), jQuery("#limit-" + pages_or_posts).val()); 
        disabling(pages_or_posts);
    }
    
    function disabling(pages_or_posts){
        var limit = parseInt(jQuery("#limit-" + pages_or_posts).val());
        var total = parseInt(jQuery("#num" + pages_or_posts + "-number").html());
        var total_pages = Math.ceil(total/limit);
        if(parseInt(jQuery("#limit-" + pages_or_posts).val()) >= parseInt(jQuery("#num" + pages_or_posts + "-number").html())){
            jQuery("#limit-" + pages_or_posts).val(jQuery("#num" + pages_or_posts + "-number").html());
            jQuery("#limit-" + pages_or_posts + "-up").attr('disabled','disabled');
        } else {
            jQuery("#limit-" + pages_or_posts + "-up").attr('disabled','');
        }
        if(parseInt(jQuery("#limit-" + pages_or_posts).val()) < 2){
            jQuery("#limit-" + pages_or_posts + "-down").attr('disabled','disabled');
        } else {
            jQuery("#limit-" + pages_or_posts + "-down").attr('disabled','');
        }
        if(parseInt(jQuery("#pagenum-" + pages_or_posts).val()) >= total_pages){
            jQuery("#pagenum-" + pages_or_posts + "-up").attr('disabled','disabled');
        } else {
            jQuery("#pagenum-" + pages_or_posts + "-up").attr('disabled','');
        }
        if(parseInt(jQuery("#pagenum-" + pages_or_posts).val()) < 2){
            jQuery("#pagenum-" + pages_or_posts + "-down").attr('disabled','disabled');
        } else {
            jQuery("#pagenum-" + pages_or_posts + "-down").attr('disabled','');
        } 
    }
    disabling('pages');
});
   
 -->
</script>
<?php
    echo $output;
?>    
<script type="text/javascript">
jQuery.fn.sort = (function(){
    var sort = [].sort;
    return function(comparator, getSortable) {
        getSortable = getSortable || function(){return this;};
        var placements = this.map(function(){
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );
 
            return function() {
 
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
                
                parentNode.insertBefore(this, nextSibling);
                parentNode.removeChild(nextSibling);
                };
                });
 
        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });
 
    };
 
})();

var th = jQuery('th'),
                inverse = false;           
            th.click(function(){
                do{
                try{
                var header = jQuery(this),
                    index = parseInt(header.index());
                header
                    .closest('table')
                    .find('.sortable')
                    .filter(function(){
                        return jQuery(this).index() === parseInt(index);
                    })
                    .sort(function(a, b){
                        
                        a = jQuery(a).text();
                        b = jQuery(b).text();
                        
                        return (
                            isNaN(a) || isNaN(b) ?
                                a > b : +a > +b
                            ) ?
                                inverse ? -1 : 1 :
                                inverse ? 1 : -1;
                            
                    }, function(){
                        return this.parentNode;
                    });
                
                inverse = !inverse;
                } catch(e){};
                }while(e);
            });

</script>  
<?php    
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
    <th scope="col" id="count" class="manage-column column-count" style="padding:4px" nowrap="nowrap">' . __('Count', 'vice-versa') . '</th>';   
    if($num_cats > 10){
      $options .= '<th scope="col" id="count" class="manage-column column-count" style="padding:4px; width:90%; text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-cats-bigger" rel="' . $item_id . '" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-cats-smaller" rel="' . $item_id . '" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>';
      }
	$options .= '</tr>
	</thead>';
    if($num_cats > 10){
     $options .= '</table>
    <div id="the-cats-list-' . $item_id . '"' . $overflow . '>
    <table id="the-cats-list-table-' . $item_id . '" class="widefat" style="margin-top:1px">';   
    }
    $options .= '<tbody class="list:cats">
    ';   
    foreach ($categories as $cat) {
        $altclass = ($x % 2) ? ' class="alternate"' : '';
        $options .= '<tr id="cat-' . $x . '"' . $altclass . '>';
        $options .= '<th scope="row" class="cats check-column" style="padding:3px"><input' . $name . ' type="checkbox" rel="' . $cat->term_id . '" value="' . $cat->term_id . '" class="cat-item term-' . $cat->term_id . ' ' . $iclass . '" /></th>
        <td class="cats name column-name" valign="middle" style="padding:3px" nowrap="nowrap">';
        $options .= $cat->cat_name;
        $options .= '</td><td class="cats count column-count" valign="middle" style="padding:3px">' . $cat->category_count . '';
        $options .= '</td>
        <td style="width:90%;"></td>        
        </tr>
        ';
        $x++;
    }
    $options .= '</tbody>'; 
    if($num_cats > 10){
      $options .= '</table>
    </div>
    <table class="widefat" style="margin-top:1px">';  
    }    
    $options .= '<tfoot>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style="padding:4px"><input type="checkbox" rel="cats-list-' . $item_id . '" class="viceversa-checktable cat-item term-0 ' . $iclass . '' . $hclass . '"></th>
	<th scope="col" id="name" class="manage-column column-name" style="padding:4px" nowrap="nowrap">' . __('Category', 'vice-versa') . '</th>
    <th scope="col" id="count" class="manage-column column-count" style="padding:4px" nowrap="nowrap">' . __('Count', 'vice-versa') . '</th>';
    
    if($num_cats > 10){
      $options .= '<th scope="col" id="count" class="manage-column column-count" style="padding:4px; width:90%; text-align:right;" nowrap="nowrap">' . __('View', 'vice-versa') . ': <a href="javascript:void(0);" class="viceversa-resize-cats-bigger" rel="' . $item_id . '" title="' . __('Increase height', 'vice-versa') . '">+</a>  <a href="javascript:void(0);" class="viceversa-resize-cats-smaller" rel="' . $item_id . '" title="' . __('Decrease height', 'vice-versa') . '">-</a></th>';
      }
	$options .= '</tr>
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