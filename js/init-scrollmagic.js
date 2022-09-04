var controller = new ScrollMagic.Controller();

var scene = new ScrollMagic.Scene({
    triggerElement: '.card-container',
    triggerHook: .5,
    reverse: false
});

scene.setClassToggle('.card-container', 'boxshadow');
scene.addTo(controller);


var process = new ScrollMagic.Scene({
    triggerElement: '.navbar',
    triggerHook: 0,
    reverse: false
});

process.setClassToggle('.process-section', 'process-show');
process.addTo(controller);


