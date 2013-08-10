<?php

/*

Plugin Name: Vice Versa
Plugin URI: http://jasonlau.biz
Description: Convert Pages to Posts and Vice Versa
Version: 2.0.0
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
        $viceversa_data = explode("|vice-versa|", $_POST['viceversa_page']);
        $viceversa_url = get_bloginfo('url') . "/?p=" . $viceversa_data[0];

        // convert page to post

        $viceversa_post = array();
        $viceversa_post['ID'] = intval($viceversa_data[0]);
        $viceversa_post['guid'] = $viceversa_url;
        $viceversa_post['post_type'] = 'post';

        wp_update_post($viceversa_post);

        // assign to category(s)

        $cats = (!isset($_POST['cat']) || $_POST['cat'] == '') ? array(1) : $_POST['cat'];
        wp_set_post_categories(intval($viceversa_data[0]), $cats);
        $thisTitle = "[<em>" . $viceversa_data[1] . "</em>]";
        $output .= "<div id=\"viceversa-status\" class=\"updated\"><p>" . sprintf(__('Page #%s %s was successfully converted to a post.', 'vice-versa'), $viceversa_data[0], $thisTitle) . "</p></div>\n";
    endif;

    if ($_POST['viceversa_post']):
        $viceversa_data = explode("|vice-versa|", $_POST['viceversa_post']);
        $viceversa_url = get_bloginfo('url') . "/?page_id=" . $viceversa_data[0];
        $parent = ($_POST['viceversa_parent'] == '') ? 0 : $_POST['viceversa_parent'];

        $viceversa_page = array();
        $viceversa_page['ID'] = intval($viceversa_data[0]);
        $viceversa_page['guid'] = $viceversa_url;
        $viceversa_page['post_type'] = 'page';
        $viceversa_page['post_parent'] = $parent;
        wp_update_post($viceversa_page);
        wp_set_post_categories(intval($viceversa_data[0]), array(1));
        $thisTitle = "[<em>" . $viceversa_data[1] . "</em>]";
        $output .= "<div id=\"viceversa-status\" class=\"updated\"><p>" . sprintf(__('Post #%s %s was successfully converted to a page.', 'vice-versa'), $viceversa_data[0], $thisTitle) . "</p></div>\n";
    endif;
    $output .= "<div id=\"viceversa-pageform-container\" class=\"metabox-holder\">
    <div class=\"meta-box-sortables\">
    <div class=\"postbox \">
    <h3><span>" . __('Page To Post', 'vice-versa') . "</span></h3>
    <div class=\"inside\">
    <form id=\"viceversa-pageform\" method=\"POST\" action=\"#\">
    <p>" . __('Select a page and category(s) to convert to a post.', 'vice-versa') . "</p>\n
    <strong>" . __('Page', 'vice-versa') . ":</strong> <select name=\"viceversa_page\">
    <option selected=\"selected\" value=\"\">" . __('Select A Page', 'vice-versa') . "</option>\n";

    $viceversa_sql = "SELECT ID FROM " . $wpdb->posts .
        " WHERE post_type = 'page' ORDER BY " . $viceversa_order;
    $viceversa_pages = $wpdb->get_col($viceversa_sql);

    foreach ($viceversa_pages as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $output .= "<option value=\"" . $post->ID . "|vice-versa|";
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $output .= $post_title . "\">";
        $output .= $post->ID . " - " . $post_title . "</option>\n";
    }
    $output .= "</select><br /><strong>" . __('Category(s)', 'vice-versa') .
        "</strong>:<br />\n";
    $options = '<table class="widefat" style="margin-top:1px">
    <thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox"></th>
	<th scope="col" id="name" class="manage-column column-name" style="">' . __('Category (count)', 'vice-versa') . '</th>
	</tr>
	</thead>
    <tbody id="the-list" class="list:tag">
    ';
    $categories = get_categories('hide_empty=0&order=' . $cat_order);
    $num_cats = count($categories);
    $x = 1;
    foreach ($categories as $cat) {
        $altclass = ($x % 2) ? ' class="alternate"' : '';
        $options .= '<tr id="tag-' . $x . '"' . $altclass . '>';
        $options .= '<th scope="row" class="check-column" style="padding:3px"><input name="cat[]" type="checkbox" value="' .
            $cat->term_id . '" /></th>
        <td class="name column-name" valign="middle" style="padding:3px">';
        $options .= $cat->cat_name;
        $options .= ' (' . $cat->category_count . ')';
        $options .= '</td>        
        </tr>
        ';
        $x++;
    }
    $options .= '</tbody>
    <tfoot>
	<tr>
	<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
	<th scope="col" class="manage-column column-name">' . __('Category (count)', 'vice-versa') . '</th>
	</tr>
	</tfoot>
    </table>
    ';
    $output .= $options;
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
    <p>" . __('Select a post and a parent(optional) to convert to a page.', 'vice-versa') . "</p>\n
    <strong>" . __('Post', 'vice-versa') . ":</strong> <select name=\"viceversa_post\">
    <option selected=\"selected\" value=\"\">" . __('Select A Post', 'vice-versa') . "</option>\n";

    $viceversa_sql2 = "SELECT ID FROM " . $wpdb->posts .
        " WHERE post_type = 'post'  ORDER BY " . $viceversa_order;
    $viceversa_posts = $wpdb->get_col($viceversa_sql2);
    foreach ($viceversa_posts as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $output .= "<option value=\"" . $post->ID . "|vice-versa|";
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $output .= $post_title . "\">";
        $output .= $post->ID . " - " . $post_title . "</option>\n";
        $wpdb->flush();
    }
    $output .= "</select><br />\n";
    $output .= "<strong>" . __('To Parent', 'vice-versa') .
        "</strong>: <select name=\"viceversa_parent\">\n<option selected=\"selected\" value=\"\">" .
        __('Select A Page', 'vice-versa') . "</option>\n";
    foreach ($viceversa_pages as $viceversa_id) {
        $post = get_post(intval($viceversa_id));
        $output .= "<option value=\"" . $post->ID . "|vice-versa|";
        $post_title = (strlen($post->post_title) > 50) ? substr($post->post_title, 0, 50) .
            '...' : $post->post_title;
        $output .= $post_title . "\">";
        $output .= $post_title . "</option>\n";
        $wpdb->flush();
    }
    $output .= "</select><br />\n";
    $output .= "<input class=\"button-secondary action\" type=\"submit\" name=\"viceversa_postform_button\" value=\"" .
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
        " &copy;2010 <a href=\"http://JasonLau.biz\" target=\"_blank\">JasonLau.biz</a><div id=\"viceversa-mode\" style=\"display:none;\">" . $viceversa_mode . "</div></div><br />\n";
?>
<script type="text/javascript">
<!--
jQuery("#viceversa-status").css('display','none');
function viceversa_tip_fade(){
    if(jQuery("#viceversa-status").attr('display') != 'none'){
        jQuery("#viceversa-status").hide('slow');
    }	   
}
var viceversa_timeout = setTimeout("viceversa_tip_fade()", 8000);
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
});   
 -->
</script>
<?php
    echo $output;
}
?>