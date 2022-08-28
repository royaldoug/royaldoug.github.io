function parallax() {
	var $slider = document.getElementById("parallax");

	var yPos = window.pageYOffset / $slider.dataset.speed;
	yPos = -yPos;

    var coords = '40%' + 'calc(50% + (' + yPos + '% * 0.2))';

    $slider.style.backgroundPosition = coords;

    var zZoom = window.pageYOffset / $slider.dataset.zoom;
	zZoom = zZoom;
	
	var scope = 'calc(150% + (' + zZoom + '% * 0.8))';
	
	$slider.style.backgroundSize = scope;
}

window.addEventListener("scroll", function(){
	parallax();	
});