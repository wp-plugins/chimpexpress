/*!
 * equalWidths jQuery Plugin
 * Examples and documentation at: hhttp://aloestudios.com/tools/jquery/equalwidths/
 * Copyright (c) 2010 Andy Ford
 * Version: 0.1 (2010-04-13)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Requires: jQuery v1.2.6+
 */

(function($){
	$.fn.equalWidths = function(options) {
		var opts = $.extend({
			stripPadding: 'none' // options: 'child', 'grand-child', 'both'
		},options);
		
		var max_width = 0;
		
		this.each(function(){
			var child_count = $(this).children().size();
			if (child_count > 0) {
				$(this).css({ 'width' : '' });
				if( $(this).width() > max_width ){
					max_width = $(this).width();
				}
			}
		});
		
		this.each(function(){
			$(this).css({ 'width' : max_width + 'px' });
		});
		
		var bg_line_width = (max_width / 2.7).toFixed(0);
		$('.bgLine').each( function(){
			$(this).css({ 'margin' : '0 -' + bg_line_width + 'px' });
		});
		var stepfirstLast_margin = (max_width / 4).toFixed(0);
		$('#step1').css({ 'margin-left' : '-' + stepfirstLast_margin + 'px' });
		$('.lastStep').css({ 'margin-right' : '-' + stepfirstLast_margin + 'px' });
	};
})(jQuery);
