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

