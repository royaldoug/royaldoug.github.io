function onLoadChange() {
    const primaryObject = document.getElementById('switch-landing');
    const words = ["vatten", "laser", "plasma", "gas"];
    let totalDuration = 0; 

    words.forEach(function(word, index) {
        const startTime = totalDuration;
        // Duration
        const visibleDuration = 5000; 
        // Time 
        const fadeOutDuration = 300; 
        // Gap 
        const gapDuration = 200; 

        // Schedule the fade-in
        setTimeout(function () {
            primaryObject.classList.remove('opacity-down'); 
            primaryObject.classList.add('opacity-none'); 
            primaryObject.innerHTML = word;
        }, startTime);

        // Schedule the fade-out
        setTimeout(function () {
            primaryObject.classList.remove('opacity-none'); 
            primaryObject.classList.add('opacity-down'); 
        }, startTime + visibleDuration);

        setTimeout(function () {
            primaryObject.classList.remove('opacity-down');
        }, startTime + visibleDuration + fadeOutDuration);

        totalDuration += visibleDuration + fadeOutDuration + gapDuration;
    });

    setTimeout(onLoadChange, totalDuration);
}

onLoadChange();
