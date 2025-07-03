document.addEventListener("DOMContentLoaded", () => {
  // --- Navigation & Mobile Menü Logik ---
  const burger = document.getElementById('burger');
  const mobileNav = document.getElementById('mobileNav');
  const closeBtn = document.getElementById('closeBtn');
  const mobileOverlay = document.getElementById('mobileOverlay');

  function closeMenu() {
    mobileNav.classList.remove('open');
    mobileOverlay.classList.remove('active');
    document.body.classList.remove('menu-open');
  }

  burger.addEventListener('click', () => {
    mobileNav.classList.add('open');
    mobileOverlay.classList.add('active');
    document.body.classList.add('menu-open');
  });

  closeBtn.addEventListener('click', closeMenu);
  mobileOverlay.addEventListener('click', closeMenu);


  // --- Sticky CTA (Call to Action) Logik ---
  const stickyCta = document.getElementById("stickyCta");

  function toggleStickyCta() {
    // Annahme: stickyCta existiert nur, wenn es die ID hat.
    // Wenn es einen mobilen Sticky-Button gibt, der bei <= 768px angezeigt werden soll
    if (stickyCta) { // Stelle sicher, dass das Element existiert
        if (window.innerWidth <= 768) {
            stickyCta.style.display = "block";
        } else {
            stickyCta.style.display = "none";
        }
    }
  }

  toggleStickyCta(); // Initialer Aufruf beim Laden der Seite
  window.addEventListener("resize", toggleStickyCta); // Bei Größenänderung aktualisieren


  // --- Scrollbalken-Versteckungs-Stil ---
  // HINWEIS: Diese CSS-Regeln für Scrollbalken sind hier in JavaScript eingefügt.
  // Für eine bessere Wartbarkeit und Sauberkeit des Codes
  // wäre es ideal, diese Regeln direkt in deine 'frontend/css/style.css' zu verschieben.
  const style = document.createElement('style');
  style.innerHTML = `
    /* Verstecke vertikalen Scrollbalken, aber lasse Scrollen zu */
    ::-webkit-scrollbar {
      display: none;
    }

    html {
      -ms-overflow-style: none;   /* für Internet Explorer / Edge */
      scrollbar-width: none;      /* für Firefox */
    }
  `;
  document.head.appendChild(style);

  // console.log("Website geladen."); // Dieser Aufruf wurde entfernt/auskommentiert für die Produktion
});

document.addEventListener('DOMContentLoaded', () => {
  const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

  dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', () => {
      const dropdownContent = document.getElementById(toggle.getAttribute('aria-controls'));

      if (dropdownContent.style.display === 'block') {
        dropdownContent.style.display = 'none';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.classList.remove('active');
      } else {
        dropdownContent.style.display = 'block';
        toggle.setAttribute('aria-expanded', 'true');
        toggle.classList.add('active');
      }
    });
  });
});