
(function($){
	
	// Number of seconds in every time division
	var days	= 24*60*60,
		hours	= 60*60,
		minutes	= 60;
	
	// Creating the plugin
	$.fn.countdown = function(prop){
		
		var options = $.extend({
			callback	: function(){},
			timestamp	: 0,
      text_before	: ''
		},prop); 
    
		var left, d, h, m, s, positions; 
    var onesIndex, tensIndex; 
    var lotsOfDays = false;
    var tickCount = 0; 

		// Initialize the plugin
		init(this, options);
		
		positions = this.find('.position');
		
		(function tick(){
    
      tickCount++;

      left = Math.floor((options.timestamp - (new Date())) / 1000); 
			

			
			if(left < 0){
				left = 0;
			}
			
			// Number of days left
			d = Math.floor(left / days);

      lotsOfDays = (positions.length>8);
			//$(this).find('.countDays').children().length > 3);
      if (lotsOfDays) { onesIndex = 2; tensIndex = 1; } else { onesIndex = 1; tensIndex = 0; }
      updateDisplay(d, true);

			//updateDuo(0, 1, d);
			left -= d*days;
			
			// Number of hours left
			h = Math.floor(left / hours);
			//updateDuo(2, 3, h);
      updateDisplay(h);
			left -= h*hours;
			
			// Number of minutes left
			m = Math.floor(left / minutes);
			//updateDuo(4, 5, m);
      updateDisplay(m);
			left -= m*minutes;
			
			// Number of seconds left
			s = left;
			//updateDuo(6, 7, s);
      updateDisplay(s);
			
			// Calling an optional user supplied callback
			options.callback(d, h, m, s);
			
			// Scheduling another call of this function in 1s
			setTimeout(tick, 1000);
		})();
		
		// This function updates two digit positions at once
		/*
		function updateDuo(minor,major,value){
			switchDigit(positions.eq(minor),Math.floor(value/10)%10);
			switchDigit(positions.eq(major),value%10);
		} */
    function updateDisplay(value, updatingDays) { 
        switchDigit(positions.eq(tensIndex), Math.floor(value / 10) % 10); 
        switchDigit(positions.eq(onesIndex), value % 10);
        if (updatingDays) if (lotsOfDays) { 
            
            switchDigit(positions.eq(0), Math.floor(value / 100) % 10); 
						
        }
        tensIndex += 2; 
				onesIndex = tensIndex + 1; 
    }
		
		return this; 
	};


	function init(elem, options){
		elem.addClass('countdownHolder');
    var lang = {"Days":velang.days, "Hours":velang.hours, "Minutes":velang.minutes, "Seconds":velang.seconds};
    var text='';
    
    var left = Math.floor((options.timestamp - (new Date())) / 1000); 
    // Number of days left 
    var d = Math.floor(left / days);
    
		// Creating the markup inside the container
		$.each(['Days','Hours','Minutes','Seconds'],function(i){

      text='<span class="count_time count'+this+'" style="display:inline-block;">';
      if(i==0 && options.text_before) text+='<span class="position_before">'+options.text_before+'</span>';
      text+='<span class="position position1">' +
      		'<span class="digit static">0</span>' +
      	'</span>' +
      	'<span class="position position2">' +
      		'<span class="digit static">0</span>' +
      	'</span>';
      
      if (this == 'Days' && d > 99) text+='<span class="position position3">' +
      		'<span class="digit static">0</span>' +
      	'</span>'; 
        
      text+='<span class="position_title">'+lang[this]+'</span></span>';
      
      $(text).appendTo(elem);
			
		});

	}

	// Creates an animated transition between the two numbers
	function switchDigit(position,number){
		
		var digit = position.find('.digit')
		
		if(digit.is(':animated')){
			return false;
		}
		
		if(position.data('digit') == number){
			// We are already showing this number
			return false;
		}
		
		position.data('digit', number);
		
		var replacement = $('<span>',{
			'class':'digit',
			css:{
				top:'-2.1em',
				opacity:0
			},
			html:number
		});
		
		// The .static class is added when the animation
		// completes. This makes it run smoother.
		
		digit
			.before(replacement)
			.removeClass('static')
			.animate({top:'2.5em',opacity:0},'fast',function(){
				digit.remove();
			})

		replacement
			.delay(100)
			.animate({top:0,opacity:1},'fast',function(){
				replacement.addClass('static');
			});
	}
})(jQuery);
