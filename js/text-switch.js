//'Vi skär med' -subtext- function

function onLoadChange(){
    const primaryObject = document.getElementById('switch-landing');

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Vatten";
        // primaryObject.style.color = 'var(--vatten)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },0); 

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Laser";
        // primaryObject.style.color = 'var(--laser)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },5000); 

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Plasma";
        // primaryObject.style.color = 'var(--plasma)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },10000); 

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Gas";
        // primaryObject.style.color = 'var(--gas)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },15000); 

    setTimeout(onLoadChange, 20000);
};

onLoadChange();