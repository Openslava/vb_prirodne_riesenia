jQuery(document).ready(function($) {  

/* ********************* FORMs  ******************** */
   $(".ve_content_form_antispam").each(function(){
      $(this).attr('action',$(this).attr('data-action'));
   });
   $(".ve_check_form").submit(function(e){
        var ret=true;
        var err_class="ve_error_form";
        $(".ve_error_form", this).removeClass("ve_error_form");
        
        // check required
        $(".ve_form_required", this).each(function() {
          
            if($(this).hasClass('ve_form_checkbox')) {
              if(!$(this).is(':checked')) ret=false;
            } else if($(this).hasClass('ve_form_checkbox_container')) {
              
              if($('input:checkbox:checked', this).length < 1) {                
                ret=false;
                err_class="ve_form_checkbox_container_error";
              }
              
            } else {
              if($(this).val()=="") ret=false;
            }
            
            if(!ret) {
                $(this).addClass(err_class); 
                var err=$(this).attr('data-errorm');
                if(err) alert(err);
                else alert(front_texts.required);
                if(($(this).offset().top-50)<$(window).scrollTop()) {
                    $('html, body').animate({
                        scrollTop: ($(this).offset().top-50)
                    },500);
                }
                return false; 
            }
        });     
        
        //check number
        if(ret) {
        $(".ve_form_number", this).each(function() { 
            var number = $.trim($(this).val());

            if(!$.isNumeric( number )) {
                  $(this).addClass("ve_error_form");
                  ret=false;
                  alert(front_texts.wrongnumber);
                  if(($(this).offset().top-50)>$('body').offset().top) {
                      $('html, body').animate({
                          scrollTop: ($(this).offset().top-50)
                      },500);
                  }
                  return false; 
            }
        });
        }  
        
        //check email adress
        if(ret) {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/; 
        $(".ve_form_email", this).each(function() { 
            var emailaddressVal = $.trim($(this).val());
            if(!emailReg.test(emailaddressVal) || emailaddressVal=="") {
                  $(this).addClass("ve_error_form");
                  ret=false;
                  alert(front_texts.wrongemail);
                  if(($(this).offset().top-50)>$('body').offset().top) {
                      $('html, body').animate({
                          scrollTop: ($(this).offset().top-50)
                      },500);
                  }
                  return false; 
            }
        });
        }    

        return ret; 

   }); 

   $(".comment-ajax-approve").live("click", function(e){
      e.preventDefault();

      var comment_id = $(this).attr('href').replace('#','');
      var data = {
        'action' : 'approve_comments',
        'comment_approve_id' : comment_id
      };
      
      $(".comment-approve-loading" + comment_id).show();
      $(".comment-ajax-approve" + comment_id).hide();

      $.post(ajaxurl, data, function(response){
        if(response == 1){
          $(".comment-awaiting-moderation" + comment_id).hide();
          $(".commet-approve-hide-divider").hide();
          $(".comment-approve-loading" + comment_id).after('<span class="comment-approved comment-approved'+ comment_id +'">Schváleno</span>');
          $(".comment-approve-loading" + comment_id).hide();
          $(".comment-approved" + comment_id).delay(1500).fadeOut();
        }  
        else{
          $(".comment-approve-loading" + comment_id).hide();
          $(".comment-approve-loading" + comment_id).after('<span class="comment-inapproved comment-inapproved'+ comment_id +'">Došlo k chybě</span>');
          $(".comment-inapproved" + comment_id).delay(1500).fadeOut();
          $(".comment-ajax-approve" + comment_id).delay(2200).fadeIn();
        }      
      });
   });
   
   $(".ve_error_form").live("click",function(){
      $(this).removeClass("ve_error_form");
   });

/* ********************* ContactForm  ******************** */   
   $(".ve_check_contact_form").submit(function(e){
        var error=false;
        $(".ve_error_form", this).removeClass("ve_error_form");
        
        $(".ve_form_required", this).each(function() {
            if($(this).val()=="") {
                error=true;
                $(this).addClass("ve_error_form"); 
            }
        });
        if(error) {
              alert(front_texts.required);
              return false; 
        }        
        
        var emailReg = /^([\w-+\.]+@([\w-]+\.)+[\w-]{2,4})?$/; 
        $(".ve_form_email", this).each(function() { 
            var emailaddressVal = $(this).val();
            if(!emailReg.test(emailaddressVal) || emailaddressVal=="") {                  
                  $(this).addClass("ve_error_form");
                  error=true;
            }
        });
        if(error) {
              alert(front_texts.wrongemail);
               
        }  
        else {
            var sendform=$(this);
            var form=$(this).serialize();
            var loading=$(".ve_contact_form_buttonrow button span",this);
            loading.show();
            $.post(ajaxurl, 'action=ve_send_contact_form&'+form , function(data) {
                alert(data);
                loading.hide();
                $(".ve_contact_form_row input", sendform).each(function() {
                    $(this).val("");
                });
                $(".ve_contact_form_row textarea", sendform).val('');                
            }); 
        }
        return false;
    
   }); 




/* ********************* Menu  ******************** */   
    // mobile menu
    //$('#mobile_nav').on('click', function(e) {
    //    e.preventDefault();
    //    $("#site_header_nav").slideToggle();
    //});
    
    // submenu
    $(".ve_menu li").hover(
    		function(){

        			$("> ul",this).show();
              $(this).removeClass('ve_menu_toremove'); 
              $(this).addClass('ve_menu_active');  

    		},
    		function(){
               $(this).addClass('ve_menu_toremove'); 
               delayHover (this);
              //$("> ul",this).fadeOut(200);
              //$(this).removeClass('ve_menu_active');

    			
    });
    function delayHover (element) {
        timer = setTimeout ( function () {
            $(".ve_menu_toremove > ul").hide(); 
            $('.ve_menu_toremove').removeClass('ve_menu_active');    
            $('.ve_menu_toremove').removeClass('ve_menu_toremove');     
        }, 200);
    };
    
    // delay show
    $( ".element_container_delay, .row_container_delay" ).each( function() {
        var el=this;
        setTimeout(function () {
            $(el).show();
        }, $(el).attr('data-delay')*1000);
    });

/* ********************* Row setting  ******************** */
 
    setWindowHeight('.row_window_height',false);
    setWindowHeight('.row_window_height_noheader',true);
    setCenteredContent('.row_fix_width','.row_centered_content');
    
/* ********************* Scroll  ******************** */   

    $('.mw_scroll_tonext').live('click',function() {                                             
        $('html,body').animate({
            scrollTop: ($(this).offset().top + 56)
        }, 1000);    
        return false;
    });
   
/* ********************* Fixed header  ******************** */   
    
    if ($('.ve_fixed_header').length) {
        var header_position=$(".ve_fixed_header #header").position();
            var header_height=$(".ve_fixed_header").height();
            $(window).scroll(function() {    
                
                var scroll = $(window).scrollTop();
        
                if (scroll > header_position.top) {
                    $(".ve_fixed_header").addClass("ve_fixed_header_scrolled");
                    $(".ve_fixed_header").height(header_height);  
                } else {
                    $(".ve_fixed_header_scrolled").removeClass("ve_fixed_header_scrolled");
                    $(".ve_fixed_header").height('auto'); 
                }
            });
    }
    
    /* ********************* Tabulators  ******************** */
    $(".mw_tabs a").live('click',function() {
        var target = $(this).attr('href'); 
        var group = $(this).attr('data-group'); 
    		$(".mw_tabs_"+group+" a").removeClass("active"); 
    		$(this).addClass("active"); 
    		$("."+group+"_container > li").hide(); 
    		$(target).show(); 
    		return false;
    }); 
        
    /* ********************* Toggle target ******************** */
    $('.mw_toggle_container').live('click', function(event){
        var $this = $(this);
        var tar = $this.attr('data-target');
        if ($this.is('input[type=checkbox]')) {
            var checked = $this.prop('checked');
            if (checked)
                $('#' + tar).show();
            else
                $('#' + tar).hide();
        } else {
            $('#' + tar).toggle();
        }
    });

 
});

var editorPanelHeight=0;
if(jQuery('#ve_editor_top_panel').length) editorPanelHeight=40;

/* ********************* Row height  ******************** */ 
function setWindowHeight(selector,noheader){

    windowheight = jQuery(window).height()-editorPanelHeight;

    if(noheader) {
      windowheight = windowheight-jQuery('#header').height(); 
      //alert(jQuery('header .header_in').height()); 
    }
    jQuery(selector).css("min-height",windowheight);
}
function setCenteredContent(element, container) {
    jQuery(container+' > '+element).each(function(){
        jQuery(this).css({
            marginTop: ((jQuery(this).closest('.row_centered_content').height() - jQuery(this).innerHeight())/2)
        });
    });
}

/* ********************* FAQ  ******************** */
function faqClick(element, cssid) {
    jQuery(element).toggleClass('ve_faq_question_open');
    jQuery(element).toggleClass('ve_faq_question_close');
    jQuery(element).next(cssid+" .ve_faq_answer").slideToggle();
}

// smooth link scroll
jQuery(function() {
  jQuery('.menu a[href*="#"]:not([href="#"]),.ve_content_button[href*="#"]:not([href="#"]),.element_image a[href*="#"]:not([href="#"]),.entry_content a[href*="#"]:not([href="#"])').on('click',function() {                                             
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {       
      var target = jQuery(this.hash);
      var fix_header_h=0;
      fix_header_h=jQuery('.ve_fixed_header').height();
      target = target.length ? target : jQuery('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        jQuery('html,body').animate({
          scrollTop: (target.offset().top-fix_header_h)
        }, 1000);
        if(jQuery('#mobile_nav').is( ':visible' )) {
				        jQuery('#site_header_nav').slideUp();
        }
        return false;
      }
    }
  });
});


function initialize_google_maps() {
    jQuery('.mw_google_map_container').each(function(){
      var def_setting = {
        address : 'Praha',
        zoom : 12,
        scrollwheel : false,
      };

      var setting=JSON.parse(jQuery(this).attr('data-setting'));
      
      setting=jQuery.extend( def_setting, setting );
      
      initialize_google_map(jQuery(this).attr('id'), setting);
    });
}

function initialize_google_map(map_id, setting) {
  var address = setting.address;
  var geocoder = new google.maps.Geocoder();
  var map = new google.maps.Map(document.getElementById(map_id), {
    zoom: setting.zoom,
    scrollwheel: setting.scrollwheel,
    center: {lat: -25.363, lng: 131.044}
  });
  if (geocoder) {
    geocoder.geocode({
      'address': address
    }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
          map.setCenter(results[0].geometry.location);

          var infowindow = new google.maps.InfoWindow({
            content: '<b>' + address + '</b>',
            size: new google.maps.Size(150, 50)
          });

          var marker = new google.maps.Marker({
            position: results[0].geometry.location,
            map: map,
            title: address
          });
          google.maps.event.addListener(marker, 'click', function() {
            infowindow.open(map, marker);
          });

        } else {
          jQuery('#'+map_id+'_error').show();
        }
      } else {
        jQuery('#'+map_id+'_error').show();
      }
    });
  }  
}
