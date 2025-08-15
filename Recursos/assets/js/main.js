'use strict';
// HR365 - JS principal para interactividad del UI
(function(){
  // Toggle sidebar en pantallas pequeñas y escritorio
  var hamburger = document.querySelector('.hamburger');
  var sidebar = document.querySelector('.sidebar');
  if (hamburger && sidebar){
    hamburger.addEventListener('click', function(){
      // En móvil, usa clase .open (translateX)
      sidebar.classList.toggle('open');
      // En escritorio, alterna colapsado
      document.body.classList.toggle('sidebar-collapsed');
    });
  }
  // Permite colapsar/expandir con tecla [
  document.addEventListener('keydown', function(e){
    if(e.key === '[' && sidebar){
      document.body.classList.toggle('sidebar-collapsed');
    }
  });
  // Dropdown de usuario en topbar
  var userMenu = document.querySelector('.user-menu');
  if(userMenu){
    var name = userMenu.querySelector('.user-name');
    name && name.addEventListener('click', function(){
      userMenu.classList.toggle('open');
    });
    document.addEventListener('click', function(e){
      if(!userMenu.contains(e.target)) userMenu.classList.remove('open');
    });
  }
  // Modo oscuro/día toggle con persistencia
  var themeBtn = document.querySelector('#theme-toggle');
  var savedTheme = localStorage.getItem('hr365_theme');
  if(savedTheme === 'dark'){ document.body.classList.add('dark'); }
  if(themeBtn){
    themeBtn.addEventListener('click', function(){
      document.body.classList.toggle('dark');
      localStorage.setItem('hr365_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
      // Alternar icono
      var i = themeBtn.querySelector('i');
      if(i){ i.className = document.body.classList.contains('dark') ? 'fa-solid fa-sun' : 'fa-solid fa-moon'; }
    });
  }
  // Modal dinámico con iframe para Ver/Editar en Usuarios
  var overlay2 = document.getElementById('modal-overlay');
  var dyn = document.getElementById('modal-dynamic');
  var dynClose = dyn ? dyn.querySelector('#btn-close-dyn') : null;
  var dynFrame = dyn ? dyn.querySelector('#modal-iframe') : null;
  function openDyn(url){ if(!dyn || !overlay2 || !dynFrame) return; dynFrame.src = url; dyn.style.display='block'; overlay2.classList.add('show'); }
  function closeDyn(){ if(!dyn || !overlay2 || !dynFrame) return; dyn.style.display='none'; overlay2.classList.remove('show'); dynFrame.src='about:blank'; }
  if (dynClose) dynClose.addEventListener('click', closeDyn);
  if (overlay2) overlay2.addEventListener('click', function(){ if(dyn && dyn.style.display==='block'){ closeDyn(); }});
  document.addEventListener('click', function(e){
    var t = e.target;
    if (t && t.matches('[data-modal-href]')){ e.preventDefault(); openDyn(t.getAttribute('data-modal-href')); }
  });
})();

