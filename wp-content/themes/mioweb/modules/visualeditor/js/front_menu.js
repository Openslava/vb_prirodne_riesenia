/* global jQuery */
( function( $ ){

	var FrontMenu = {

		init: function() {

			this.header_wrap = $( '#header' );
			this.header_wrap_in = this.header_wrap.find( '#header_in' );
			this.menu_wrap = this.header_wrap.find( '#site_header_nav' );
			this.top_level_menu_items = this.menu_wrap.find( '.menu > .menu-item' );
			this.items_with_submenu = this.menu_wrap.find( '.menu-item-has-children' );
			this.items_with_submenu_anchors = this.items_with_submenu.children( 'span' );
			this.menu_on_the_right = this.header_wrap.find( '#header_in' ).hasClass( 'fix_width header_in_s1' );
			this.logo = this.header_wrap.find( '#site_title' );
			this.submenus = this.top_level_menu_items.find( '.sub-menu' );
			this.mobile_nav_button = $( '#mobile_nav' );

			this.tolerance_margin = 10;
			this.placeholders_added = false;
			this.is_mobile_menu = false;
			this.is_full_width_menu = this.header_wrap.hasClass( 'header_in_s2' );

			this.bindEvents();

		},

		bindEvents: function() {

			this.checkForMobileMenu() ;//DOM ready
			$( window ).load( $.proxy( this.checkForMobileMenu, this ) );
			$( window ).on( 'resize', $.proxy( this.checkForMobileMenu, this ) );


		},

		checkForMobileMenu: function() {

			var available_width = 0,
				all_items_width = 0;

			this.header_wrap.removeClass( 'is-mobile_menu' );
			this.is_mobile_menu = false;

			for( var i = 0, length = this.top_level_menu_items.length; i < length; i++ ) {
				all_items_width = all_items_width + this.top_level_menu_items.eq( i ).outerWidth( true );
			}
			
			if( this.menu_on_the_right ) {
 				available_width = this.header_wrap_in.width() - this.logo.outerWidth();
 			} else {
 				available_width = this.menu_wrap.width();
 			}
			
			if( ( available_width - this.tolerance_margin ) <= all_items_width ) {
				this.header_wrap.addClass( 'is-mobile_menu' );
				this.is_mobile_menu = true;
			}
			
			//Bind specific events
			this.unbindDesktopEvents();
			this.unbindMobileEvents();

			if( this.is_mobile_menu ) {
				this.bindMobileEvents();
			} else {
				this.bindDesktopEvents();
			}

		},

		bindDesktopEvents: function() {

			this.checkForOverflowedSubmenus();
			$( window ).on( 'load.mio_menu__desktop__checkoverflow', $.proxy( this.checkForOverflowedSubmenus, this ) );
			$( window ).on( 'resize.mio_menu__desktop__checkoverflow', $.proxy( this.checkForOverflowedSubmenus, this ) );

			//Hovers
			var t = this;
			this.items_with_submenu.on( 'mouseenter.mio_menu__desktop__hovers', function(){
				t.showHideSubmenu( $( this ), 'show' );
			} );
			this.items_with_submenu.on( 'mouseleave.mio_menu__desktop__hovers', function(){
				t.showHideSubmenu( $( this ), 'hide' );
			} );

			//Click on parent item
			this.items_with_submenu_anchors.on( 'touchstart.mio_menu__desktop__taps', function(){
				var el = $( this );
				el.data( 'scrolling', false );
			} );
			this.items_with_submenu_anchors.on( 'touchmove.mio_menu__desktop__taps', function(){
				var el = $( this );
				el.data( 'scrolling', true );
			} );
			this.items_with_submenu_anchors.on( 'touchend.mio__front_menu__open_submenu', function( evt ){
				var el = $( this );
				if( el.data( 'scrolling' ) !== true ){
					t.checkForTap( evt, el );
				}
				el.data( 'scrolling', false );
			} );

		},

		unbindDesktopEvents: function() {

			$( window ).off( 'load.mio_menu__desktop__checkoverflow' );
			$( window ).off( 'resize.mio_menu__desktop__checkoverflow' );

			//Hovers
			this.items_with_submenu.off( 'mouseenter.mio_menu__desktop__hovers' );
			this.items_with_submenu.off( 'mouseleave.mio_menu__desktop__hovers' );

			//Click on parent item
			this.items_with_submenu_anchors.off( 'touchstart.mio_menu__desktop__taps' );
			this.items_with_submenu_anchors.off( 'touchmove.mio_menu__desktop__taps' );
			this.items_with_submenu_anchors.off( 'touchend.mio__front_menu__open_submenu' );

			//Runtimes
			this.items_with_submenu_anchors.off( 'click.mio_menu__desktop__prevent_click' );
			$( document ).off( 'click.mio__front_menu__close_submenu' );


		},

		bindMobileEvents: function() {

			var t = this;
			this.items_with_submenu_anchors.on( 'click.mio_menu__mobile__toggle_submenu', function( evt ){
				evt.preventDefault();
				t.showHideSubmenu( $( this ).parent(), 'toggle', true );
			} );

			this.mobile_nav_button.on( 'click.mio_menu__mobile__toggle_menu', function( evt ){
				evt.preventDefault();
				t.toggleMobileMenu();
			} );

		},

		unbindMobileEvents: function() {

			this.items_with_submenu_anchors.off( 'click.mio_menu__mobile__toggle_submenu' );
			this.mobile_nav_button.off( 'click.mio_menu__mobile__toggle_menu' );

		},

		checkForOverflowedSubmenus: function() {


			var body = $( 'html' );
			body.css( 'overflow-x', '' );

			var document_width = $( window ).width();

			this.items_with_submenu.removeClass( 'is-submenu_on_left' );

			this.submenus.show();

			var t = this;
			this.submenus.each( function(){
				var submenu = $( this );
				var adjusted = false;

				var is_top_level_on_s2 = ( t.is_full_width_menu && submenu.parents( '.sub-menu' ).length === 0 );

				if( is_top_level_on_s2 ) { //Only on top-level menus on s2 header style
					submenu.css({
						'left': '50%',
						'right': '',
						'margin-left': ''
					});
				} else {
					submenu.css({
						'left': '',
						'right': ''
					});
				}

				var submenu_right_side_offset = ( submenu.offset().left + submenu.outerWidth( true ) );

				if( submenu_right_side_offset > document_width ) {
					adjusted = true;

					var is_third_level = ( $( this ).parents( '.sub-menu' ).length > 0 );

					if( is_third_level ) {
						$( this ).parent().addClass( 'is-submenu_on_left' );
					}

					if( is_top_level_on_s2 ) {
						submenu.css({
							'margin-left': ( document_width - submenu_right_side_offset )
						});
					} else {
						submenu.css({
							'left': 'auto',
							'right': ( is_third_level ) ? '100%' : '0'
						});
					}

				}

				//Additional check for S2 menus on left side
				if( is_top_level_on_s2 && adjusted === false ) {

					var submenu_left_offset = submenu.offset().left,
						menu_left_offset = t.menu_wrap.offset().left;


					if( submenu_left_offset < menu_left_offset ) {
						submenu.css( {
							'margin-left': ( menu_left_offset - submenu_left_offset )
						} );
					}

				}

			} );

			this.submenus.hide();

			body.css( 'overflow-x', 'hidden' );

		},

		/*
		 * @param action 'show'|'hide'|'toggle'
		 */
		showHideSubmenu: function( element, action, slide ) {
			slide = typeof slide === 'undefined' ? false : true;

			if( action === 'toggle' ) {
				action = ( element.data( 'mio_hovering' ) === true ) ? 'hide' : 'show'; //If submenu is open, close it and vice versa
			}

			element.data( 'mio_hovering', ( action === 'show' ) );

			var submenu = element.children( '.sub-menu' );
			var velocity_action;
			if( action === 'show' ) {
				velocity_action = ( slide ) ? 'slideDown' : 'fadeIn';
			} else {
				velocity_action = ( slide ) ? 'slideUp' : 'fadeOut';
			}
      
      element.children( 'span' ).toggleClass('opened');


			var complete_function = ( action === 'show' ) ? null : function( el ) {
				$( el ).css( 'opacity', '' );
			};

			submenu.velocity( 'stop' ).velocity( velocity_action, { duration: 200, complete: complete_function } );

		},


		/*
		 * If we detect click on parent and submenu is not visible at this time - it was probably tap and we want just to show submenu
		 */
		checkForTap: function( evt, anchor_element ) {

			//Prevent clicks legacy event
			evt.preventDefault();
			anchor_element.on( 'click.mio_menu__desktop__prevent_click', function( evt ){
				evt.preventDefault();
			} );

			//Add placeholder items
			if( this.placeholders_added === false ) {
				this.addPlaceholderItems();
			}

			//Close all other parent submenus - only on desktop menu
			if( this.is_mobile_menu === false ) {
				this.closeAllOpenSubmenus( anchor_element.parent() );
			}

			//Open submenu
			this.showHideSubmenu( anchor_element.parent(), 'show' );


			//Close on tap outside menu
			this.bindCloseSubmenuOnClickOutside( anchor_element.parent() );

		},

		bindCloseSubmenuOnClickOutside: function( submenu_parent ) {

			this.items_with_submenu.on( 'click', function( evt ){
				evt.stopPropagation();
			} );

			var t = this;
			$( document ).on( 'click.mio__front_menu__close_submenu', function(){
				t.showHideSubmenu( submenu_parent, 'hide' );
				$( document ).off( 'click.mio__front_menu__close_submenu' );
			} );

		},

		/*
		 * @param jQuery li-element of currently clicked submenu - it's parent submenu won't be closed
		 */
		closeAllOpenSubmenus: function( clicked_parent ) {
			clicked_parent = clicked_parent || false;

			var open_menus = this.items_with_submenu.filter( function(){
				var submenu_parent = $( this );
				if( clicked_parent !== false ) {
					if( submenu_parent.is( clicked_parent.parents( 'ul:first' ).parent( 'li' ) ) ) return false; //We don't want to close parent submenu_wrapper of current submenu wrapper
				}
				return submenu_parent.data( 'mio_hovering' ); //Mio hovering is also indicating, if current menu is open
			} );

			var t = this;
			open_menus.each( function(){
				t.showHideSubmenu( $( this ), 'hide' );
			} );
		},

		addPlaceholderItems: function() {

			this.items_with_submenu.each( function(){
				var parent = $( this ),
					new_element = parent.clone().empty().removeClass( 'menu-item-has-children' );
				new_element.append( parent.clone().children( 'a' ) );
				parent.find('.sub-menu:first').prepend( new_element );
			} );

			this.checkForOverflowedSubmenus();

			this.placeholders_added = true;

		},

		toggleMobileMenu: function() {

			var menu_open = this.menu_wrap.is( ':visible' ),
				velocity_action = ( menu_open ) ? 'slideUp' : 'slideDown',
				complete_function = ( menu_open ) ? function( el ){
					$( el ).attr( 'style', '' );  
          $('.ve_fixed_header #site_header_nav').removeClass('over_window_menu');                 
				} : function( el ){
					var m_pos=$('header').position();  
          $('.ve_fixed_header #site_header_nav').css('max-height',($(window).height()-m_pos.top-$('header').height())+'px'); 
          $('.ve_fixed_header #site_header_nav').addClass('over_window_menu');              
				};

			this.menu_wrap.velocity( 'stop' ).velocity( velocity_action, { duration: 500, complete: complete_function } );
      
      $('#mobile_nav span').hide();
      
      if(menu_open) {
          $('.mobile_nav_menu').show();
          
      } else {
          $('.mobile_nav_close').show();
      }

		}



	};


	//Dom ready
	$( function(){
		FrontMenu.init();
	} );

	//Menu changed in editor
	$( document ).on( 'mio_editor__replaced_menu', function() {
		FrontMenu.unbindDesktopEvents();
		FrontMenu.unbindMobileEvents();
		FrontMenu.init();
	} );


} )( jQuery );
