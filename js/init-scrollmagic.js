var controller = new ScrollMagic.Controller();

var process = new ScrollMagic.Scene({
    triggerElement: '.navbar',
    triggerHook: 0,
    reverse: false
});

process.setClassToggle('.process-section', 'process-show');
process.setClassToggle('.machine-wrapper', 'process-show');
process.setClassToggle('.empty-wrapper', 'process-show');
process.addTo(controller);




