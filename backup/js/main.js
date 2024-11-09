//Navbutton query

const documentBody = document.querySelector('body');
const navButton = document.querySelector(".nav-button");
const mobileNav = document.querySelector('.navbar-items:last-of-type')
const dropDown = document.querySelector('.navbar-items:last-of-type ul > li > .dropdown');

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

document.addEventListener('click', function(event) {
    document.querySelectorAll('.dropdown').forEach(function(el) {
      if (el !== event.target) el.classList.remove('dropdown-shown')
    });
    if (event.target.matches('#de')) {
        event.target.closest('.navbar-items:last-of-type ul li').querySelector('.dropdown').classList.toggle('dropdown-shown');
      }
  });

function toggleDropdown() {
    document.querySelector(".dropdown").classList.toggle("dropdown-shown");
  }

function animateNav(){   
    const buttonSpan = document.querySelectorAll('.nav-button > span');
    
    for (const spans of buttonSpan) {
        spans.classList.toggle('transitioning-two');
        spans.classList.toggle('toggled');

        setTimeout(function () {
            spans.classList.toggle('transitioning-two');
        },300); 
    }

    if (dropDown.classList.contains('dropdown-shown')) {
        dropDown.classList.toggle('dropdown-shown')
    } else {
        null;
    }

    documentBody.classList.toggle('kill-scroll');
    // dropDown.classList.toggle('kill-scroll');
};

navButton.addEventListener('click', toggleNav);
navButton.addEventListener('click', animateNav);




