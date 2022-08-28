//Navbutton query

const navButton = document.querySelector(".nav-button");

function toggleNav(){
    const hiddenNav = document.querySelector('.navbar-items:last-of-type');
    
    navButton.disabled = true;
    setTimeout(()=>{
        navButton.disabled = false;
    }, 300);

    hiddenNav.classList.toggle('transitioning');
    hiddenNav.classList.toggle('shown');
    
    setTimeout(function () {
        hiddenNav.classList.toggle('transitioning');
    },300);  
};

function animateNav(){   
    const buttonSpan = document.querySelectorAll('.nav-button>span');
    
    for (const spans of buttonSpan) {
        spans.classList.toggle('transitioning-two');
        spans.classList.toggle('toggled');

        setTimeout(function () {
            spans.classList.toggle('transitioning-two');
        },300); 
    }

};

navButton.addEventListener('click', toggleNav);
navButton.addEventListener('click', animateNav);

//'Vi skär med' -subtext- function

function onLoadChange(){
    const primaryObject = document.getElementById('switch-landing');

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Vatten";
        primaryObject.style.color = 'var(--vatten)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },0); 

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Laser";
        primaryObject.style.color = 'var(--laser)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },5000); 

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Plasma";
        primaryObject.style.color = 'var(--plasma)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },10000); 

    setTimeout(function () {
        primaryObject.classList.toggle('opacity-none');
        primaryObject.innerHTML = "Gas";
        primaryObject.style.color = 'var(--gas)';
        setTimeout(function () {
            primaryObject.classList.toggle('opacity-none');
        },4700);
    },15000); 

    setTimeout(onLoadChange, 20000);
};

// function getLandingHeight() {
//     const root = document.querySelector(':root');
//     var height = document.querySelector('.landing').offsetHeight;
//     var html = document.querySelector('html');

//     root.style.setProperty('--landing-height', height);
//     console.log(height);

//     let dataScroll = html.getAttribute('data-scroll');

//     dataScroll = height;
//     console.log(dataScroll);

// };

onLoadChange();
// getLandingHeight();
