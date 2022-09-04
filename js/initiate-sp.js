// function parallax() {
// 	var $slider = document.getElementById("parallax");

// 	var yPos = window.pageYOffset / $slider.dataset.speed;
// 	yPos = -yPos;

//     var coords = '40%' + 'calc(50% + (' + yPos + '% * 0.2))';

//     $slider.style.backgroundPosition = coords;

//     var zZoom = window.pageYOffset / $slider.dataset.zoom;
// 	zZoom = zZoom;
	
// 	var scope = 'calc(150% + (' + zZoom + '% * 0.8))';
	
// 	$slider.style.backgroundSize = scope;
// }

// window.addEventListener("scroll", function(){
// 	parallax();	
// });

// function parallax() {
// 	var $slider = document.getElementById("parallax");

// 	var yPos = window.pageYOffset / $slider.dataset.speed;
// 	yPos = yPos;

//     var coords = '20%' +  'calc(0% + (' + yPos + '% * 0.2))';
    
// 	if (yPos < 300) {
// 		$slider.style.backgroundPosition = coords;
// 		} else {
// 			null;
// 		}


// }

// window.addEventListener("scroll", function(){
// 	parallax();	
// });

var rellax = new Rellax('.rellax', {
	breakpoints:[576, 768, 1201, 1679]
});