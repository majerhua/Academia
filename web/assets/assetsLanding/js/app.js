

// Equivalent of jQuery .ready
document.addEventListener('DOMContentLoaded',function(){

	// Initialize variables
	var lastScrollTop = window.pageYOffset || document.documentElement.scrollTop; // Scroll position of body

	// Listener to resizes
	window.onresize = function(event) {
    	lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
	};

	// Helper functions
	// Detect offset of element
	function getOffset( el ) {
		var _x = 0;
		var _y = 0;
		while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
			_x += el.offsetLeft - el.scrollLeft;
			_y += el.offsetTop - el.scrollTop;
			el = el.offsetParent;
		}
		return { top: _y, left: _x };
	};

	// Add class to element => https://www.sitepoint.com/add-remove-css-class-vanilla-js/
	function addNewClass(elements, myClass) {
		// if there are no elements, we're done
		if (!elements) { return; }
		// if we have a selector, get the chosen elements
		if (typeof(elements) === 'string') {
			elements = document.querySelectorAll(elements);
		}
		// if we have a single DOM element, make it an array to simplify behavior
		else if (elements.tagName) { elements=[elements]; }
		// add class to all chosen elements
		for (var i=0; i<elements.length; i++) {
			// if class is not already found
			if ( (' '+elements[i].className+' ').indexOf(' '+myClass+' ') < 0 ) {
			// add class
			elements[i].className += ' ' + myClass;
			}
		}
	};

	// Remove class from element => https://www.sitepoint.com/add-remove-css-class-vanilla-js/
	function removeClass(elements, myClass) {
		// if there are no elements, we're done
		if (!elements) { return; }

		// if we have a selector, get the chosen elements
		if (typeof(elements) === 'string') {
			elements = document.querySelectorAll(elements);
		}
		// if we have a single DOM element, make it an array to simplify behavior
		else if (elements.tagName) { elements=[elements]; }
		// create pattern to find class name
		var reg = new RegExp('(^| )'+myClass+'($| )','g');
		// remove class from all chosen elements
		for (var i=0; i<elements.length; i++) {
			elements[i].className = elements[i].className.replace(reg,' ');
		}
	}

	// Smooth scrolling => https://codepen.io/andylobban/pen/qOLKVW
	if ( 'querySelector' in document && 'addEventListener' in window && Array.prototype.forEach ) {
		// Function to animate the scroll
		var smoothScroll = function (anchor, duration) {
		// Calculate how far and how fast to scroll
		var startLocation = window.pageYOffset;
		var endLocation = anchor.offsetTop - 130; // Remove 40 pixels for padding
		var distance = endLocation - startLocation;
		var increments = distance/(duration/16);
		var stopAnimation;
		// Scroll the page by an increment, and check if it's time to stop
		var animateScroll = function () {
			window.scrollBy(0, increments);
			stopAnimation();
		};
	// If scrolling down
		if ( increments >= 0 ) {
		// Stop animation when you reach the anchor OR the bottom of the page
			stopAnimation = function () {
				var travelled = window.pageYOffset;
				if ( (travelled >= (endLocation - increments)) || ((window.innerHeight + travelled) >= document.body.offsetHeight) ) {
					clearInterval(runAnimation);
				}
			};
		}
            // Loop the animation function
            var runAnimation = setInterval(animateScroll, 16);
        };
		// Define smooth scroll links
		var scrollToggle = document.querySelectorAll('.scroll');
		// For each smooth scroll link
		[].forEach.call(scrollToggle, function (toggle) {
			// When the smooth scroll link is clicked
			toggle.addEventListener('click', function(e) {
		// Prevent the default link behavior
		e.preventDefault();
		// Get anchor link and calculate distance from the top
		var dataTarget = document.querySelector('.title__etapas__deportivas');
		var dataSpeed = toggle.getAttribute('data-speed');
	 	// If the anchor exists
		if (dataTarget) {
			// Scroll to the anchor
				smoothScroll(dataTarget, dataSpeed || 700);
			}
		}, false);
		});
	}

	
		// Listen to scroll position changes
	window.addEventListener("scroll",function(){
		// NAVIGATION BAR ON LANDING FIXED
		// If there is a #navConverter element then attach listener to scroll events
		if (document.body.contains(document.getElementById("title-etapas-deportivas"))){

			var lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
			// if the current body position is less than 20 pixels away from our converter, convert
			if (lastScrollTop > (getOffset( document.getElementById('title-etapas-deportivas') ).top - 150)){
				
			 removeClass(document.querySelector('.container__menu-movile'),'container__menu-movile-transparent');
			} else {
				addNewClass(document.querySelector('.container__menu-movile'),'container__menu-movile-transparent');
			}
		}

		// SCROLL TO NEXT ELEMENT ON LANDING
		if (document.body.contains(document.getElementById('scrollToNext'))){
			var lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
			// if the current body position is less than 20 pixels away from the top, hide the icon
			if (lastScrollTop > 20){ 
				addNewClass(document.getElementById('scrollToNext'),'invisible');
			} else {
				removeClass(document.getElementById('scrollToNext'),'invisible');
			}
		}
	});

	// Responsive mobile menu
	// Create the menu 

}(document, window, 0));