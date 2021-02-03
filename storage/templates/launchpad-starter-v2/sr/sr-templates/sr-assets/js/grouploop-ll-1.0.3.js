/*!
Name: jQuery grouploop plugin - Lean Labs version
Version: 1.0.3
Based on: https://github.com/scottalguire/grouploop by Scott Alguire
Forked and modified by: Adrian Sandu
Email: adrian@lean-labs.com
*/

(function($) {
  $.fn.extend({
    grouploop: function(options) {
      // Setup default settings
      var settings = $.extend(
        {
          velocity: 2,
          forward: true,
          childNode: ".item",
          childWrapper: ".item-wrap",
          pauseOnHover: true
        },
        options
      );

			// ===========================
			// Variables
			// ===========================

			var el = this;
			var myReq;
			var curXPos;
			var animationFlag = false;
			var children;

			var v = settings.velocity;
			var numChildren = $(el).find(settings.childWrapper + " " + settings.childNode).length;

      var windowWidth = $(window).width();
			var wrapperWidth = $(el).width();
			var itemWidth = parseInt($(settings.childNode).css('margin-left')) + parseInt($(settings.childNode).css('margin-right')) + parseInt($(settings.childNode).css('width'));
      var sliderWidth = numChildren * itemWidth;
			var resetPoint = sliderWidth;

			// ===========================
			// Functions
			// ===========================

			function activateScroll() {
				wrapperWidth = $(el).width();

				if(myReq) {
					cancelAnimationFrame(myReq);
					myReq = undefined;
				}

				if(2 * sliderWidth < wrapperWidth) {
					animationFlag = false;
					numChildren = $(children).length;
					sliderWidth = numChildren * itemWidth;

					// $(el).addClass('no-scroll');
					$(el).find(settings.childWrapper)
						.append(children);
				} else {
					var twins = $(children).clone();

					animationFlag = true;
					numChildren = $(children).length;
					sliderWidth = 2 * numChildren * itemWidth;
					resetPoint = numChildren * itemWidth;

					// $(el).removeClass('no-scroll');
					// append content twice for scrolling
					$(el).find(settings.childWrapper)
						.append(children)
						.append(twins);
				}


				if(animationFlag) {
		      myReq = window.requestAnimationFrame(step);
				}

			}

			function step() {
        if (settings.forward) {
          if (curXPos <= 0) {
            curXPos = curXPos + 1 * v;
            $(el)
              .find(settings.childWrapper)
              .css("transform", "translateX(" + curXPos + "px)");
          } else {
            $(el)
              .find(settings.childWrapper)
              .css("transform", "translateX(" + -sliderWidth + ")");
            curXPos = -resetPoint;
          }
        } else {
          if (curXPos >= -resetPoint) {
            curXPos = curXPos - 1 * v;
            $(el)
              .find(settings.childWrapper)
              .css("transform", "translateX(" + curXPos + "px)");
          } else {
            $(el)
              .find(settings.childWrapper)
              .css("transform", "translateX(" + 0 + ")");
            curXPos = 0;
          }
        }

        myReq = window.requestAnimationFrame(step);
      }

			// ===========================
			// Actions
			// ===========================

			children = $(el).find(settings.childNode).detach();

			activateScroll();

      window.addEventListener("resize", function() {
				activateScroll();
      });

			if (settings.pauseOnHover && animationFlag) {
				$(el).hover(
					function() {
						cancelAnimationFrame(myReq);
					},
					function() {
						myReq = window.requestAnimationFrame(step);

						// console.log("pauseOnHover:");
						// console.log(myReq);
					}
				);
			}
    }
  });
})(jQuery);
