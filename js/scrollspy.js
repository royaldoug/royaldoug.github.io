//#region - start of - scrollSpy
const isoScrollSpy = (targetSelectorAll = "", toggleClass = "") => {
  const targets = document.querySelectorAll(targetSelectorAll),
   options = {
    threshold: 0.8
   };
  const inView = (target) => {
   const interSecObs = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
     const currentElement = entry.target,
      currentNav = document.querySelector(`.machine-dom a[href='#${currentElement.id}']`);
     if (entry.isIntersecting) {
      currentNav.classList.add(toggleClass);
     } else {
      currentNav.classList.remove(toggleClass);
     }
    });
   }, options);
   interSecObs.observe(target);
  };
  targets.forEach(inView);
 };
 //#endregion - end of - scrollSpy
 document.addEventListener("DOMContentLoaded", () => {
  isoScrollSpy(".machine-content[id]", "machine-active");
 });
 