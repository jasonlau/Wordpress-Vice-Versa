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
        if($.cookie('noupdater') == 1){
            $(".viceversa-updater").hide();
            $(".viceversa-updater-open").show();
        } else {
            $(".viceversa-updater").show();
            $(".viceversa-updater-open").hide();
        }
        $("div.actions").first().prepend($("#viceversa-module-buttons").html());
       $("#viceversa-module-buttons.page2post, #viceversa-module-buttons.post2page, #viceversa-module-buttons.post2ptype").html('');
       $("#get-search-input").after($("#viceversa-search-form").html());
       $("#viceversa-search-form").html('')
       $('.vv-help').css('border-color','#FFFF00');
       $(".vv-mode").each(function(){
	   $(this).bind('mouseup',function(){
           try{
            $("#viceversa-list-table-form input[type='checkbox']").prop('checked',false);
           $("#viceversa-form select").val('');
           }catch(e){}    	   
	       $(".viceversa-mode").val($(this).attr('rel'));
           $("#viceversa-form").submit();
	   });
       });
       $(".vv-go").each(function(){
       $(this).bind('click',function(){
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
       
    $(".viceversa-assign-to").each(function(){
       $(this).html($("#viceversa-assign-to").html()); 
    });
    
    $("#viceversa-list-table-form").append('<fieldset class="viceversa-bulk-assign-field viceversa-info"><legend style="padding: 0px 4px"><strong>' + $(".wrap").data('bulk-selector') + '</strong></legend><div id="viceversa-assign-error">' + $(".wrap").data('duplicate-selected') + '</div><div id="viceversa-bulk-assign">' + $("#viceversa-assign-to").html() + '</div></fieldset>');

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
    
    $("form a:contains('" + $(".wrap").data('convert') + "')").each(function(){        
        var url = $(this).attr('href').split('='); 
        $(this).attr({'href':'javascript:void(0)'});
        $(this).bind('mouseup',function(){
            var c = confirm($(".wrap").data('are-you-sure'));
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
    
    $("div.tablenav-pages:first").css({
        'margin': '60px 0px 0px 0px !important'
    });
    
    $("div.actions:last").css({
        'margin': '0px 0px 0px 0px !important'
    });
    
    $(".viceversa-debug").change(function(){
        $(this).parent().submit();
    });
    
    $("select[name='vv_cat_name1'], select[name='vv_cat_name2']").prepend('<option value="" selected="selected">Categories</option>');
    
    $("#cat_name").prepend('<option value="" selected="selected">Categories</option>');
    
    $(".bulk-post-types-menu").bind('change',function(){
            var v = $(this).val();
            $(".viceversa-post-type-menu").val(v);
    });
    
    $(".viceversa-updater-dismiss").click(function(){
        $(".viceversa-updater-open").show('slow');
        $(".viceversa-updater").hide('slow');
        $.cookie('noupdater', 1);
    });
    
    $(".viceversa-updater-open").click(function(){
        $(this).hide('slow');
        $(".viceversa-updater").show('slow');
        $.cookie('noupdater', 0);
    });
    
    _activate();
	
    }); // jQuery