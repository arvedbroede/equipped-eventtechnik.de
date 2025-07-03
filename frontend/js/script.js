document.addEventListener("DOMContentLoaded",()=>{console.log("Website geladen.")});const burger=document.getElementById('burger');const mobileNav=document.getElementById('mobileNav');const closeBtn=document.getElementById('closeBtn');const mobileOverlay=document.getElementById('mobileOverlay');burger.addEventListener('click',()=>{mobileNav.classList.add('open');mobileOverlay.classList.add('active');document.body.classList.add('menu-open')});closeBtn.addEventListener('click',closeMenu);mobileOverlay.addEventListener('click',closeMenu);function closeMenu(){mobileNav.classList.remove('open');mobileOverlay.classList.remove('active');document.body.classList.remove('menu-open')}
document.addEventListener("DOMContentLoaded",()=>{const stickyCta=document.getElementById("stickyCta");function toggleStickyCta(){if(window.innerWidth<=768){stickyCta.style.display="block"}else{stickyCta.style.display="none"}}
toggleStickyCta();window.addEventListener("resize",toggleStickyCta)});const style=document.createElement('style');style.innerHTML=`
    /* Verstecke vertikalen Scrollbalken, aber lasse Scrollen zu */
    ::-webkit-scrollbar {
      display: none;
    }
  
    html {
      -ms-overflow-style: none;  /* für Internet Explorer / Edge */
      scrollbar-width: none;     /* für Firefox */
    }
  `;document.head.appendChild(style)