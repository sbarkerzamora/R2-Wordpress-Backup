(function () {
  'use strict';

  // Mobile nav toggle
  var navToggle = document.getElementById('nav-toggle');
  var navMenu = document.getElementById('nav-menu');
  var iconOpen = document.querySelector('[data-icon-open]');
  var iconClose = document.querySelector('[data-icon-close]');

  if (navToggle && navMenu) {
    function openMenu() {
      navMenu.classList.remove('closed');
      navMenu.classList.add('open');
      navMenu.setAttribute('aria-hidden', 'false');
      navToggle.setAttribute('aria-expanded', 'true');
      if (iconOpen) iconOpen.classList.add('hidden');
      if (iconClose) iconClose.classList.remove('hidden');
    }
    function closeMenu() {
      navMenu.classList.remove('open');
      navMenu.classList.add('closed');
      navMenu.setAttribute('aria-hidden', 'true');
      navToggle.setAttribute('aria-expanded', 'false');
      if (iconOpen) iconOpen.classList.remove('hidden');
      if (iconClose) iconClose.classList.add('hidden');
    }
    navToggle.addEventListener('click', function () {
      if (navMenu.classList.contains('closed')) {
        openMenu();
      } else {
        closeMenu();
      }
    });
    // Close menu when a nav link is clicked (mobile)
    navMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth < 768) closeMenu();
      });
    });
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var targetId = this.getAttribute('href');
      if (targetId === '#') return;
      var target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
})();
