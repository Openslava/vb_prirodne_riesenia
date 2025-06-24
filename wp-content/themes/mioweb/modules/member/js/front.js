jQuery(document).ready(function($) {
    $(".member_noaccess").click(function(){
      return false;
    });
    //show user bar
    $('.member_user_avatar_hide').live("click",function(){
      $(this).removeClass('member_user_avatar_hide');
      $(this).addClass('member_user_avatar_show');
      $("#member_user_panel").animate({ top: "-=40px"}, 200 );
      $("html").animate({ 'padding-top': "0"}, 200 );
      return false;
    });
    $('.member_user_avatar_show').live("click",function(){
      $(this).removeClass('member_user_avatar_show');
      $(this).addClass('member_user_avatar_hide');
      $("#member_user_panel").animate({ top: "+=40px"}, 200 );
      $("html").animate({ 'padding-top': "40px"}, 200 );
      return false;
    }); 

    $("#member_user_avatar").hover(
    		function(){
          $(this).addClass('member_user_menu_open');
          $(this).removeClass('member_user_menu_close');			 
    		},
    		function(){
          $(this).removeClass('member_user_menu_open');
          $(this).addClass('member_user_menu_close');	     
    });

    //show user profile 
    $("#member_show_profile").click(function(){
        $("#member_profile_background").show();
        $("#member_profile").show();
        return false;
    });
    $("#member_close_profile").click(function(){
        $("#member_profile_background").hide();
        $("#member_profile").hide();
        return false;
    });
    
    //news 
    $(".member_new_show_text").click(function(){
        $(this).closest(".member_new").find(".member_new_short").toggle();
        $(this).closest(".member_new").find(".member_new_text").toggle();
        return false;
    });
});
