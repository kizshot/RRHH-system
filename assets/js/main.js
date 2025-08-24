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

  // Mejorar confirmaciones de eliminación
  document.addEventListener('click', function(e) {
    if (e.target.matches('[data-confirm-delete]')) {
      e.preventDefault();
      const itemName = e.target.getAttribute('data-confirm-delete');
      const deleteUrl = e.target.getAttribute('href') || e.target.getAttribute('data-href');
      
      if (confirm(`¿Estás seguro de que deseas eliminar "${itemName}"?`)) {
        if (deleteUrl) {
          window.location.href = deleteUrl;
        }
      }
    }
  });

  // Mejorar formularios con notificaciones
  document.addEventListener('submit', function(e) {
    if (e.target.matches('form[data-ajax]')) {
      e.preventDefault();
      
      const form = e.target;
      const submitBtn = form.querySelector('[type="submit"]');
      const originalText = submitBtn ? submitBtn.innerHTML : '';
      
      // Mostrar loading
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
      }
      
      const formData = new FormData(form);
      const url = form.action || window.location.href;
      
      fetch(url, {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        // Mostrar notificación
        if (data.includes('success') || data.includes('exitosamente')) {
          showNotification('success', 'Operación completada exitosamente');
        } else if (data.includes('error') || data.includes('Error')) {
          showNotification('error', 'Ha ocurrido un error');
        }
        
        // Recargar página después de un delay
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      })
      .catch(error => {
        showNotification('error', 'Error de conexión');
        
        // Rehabilitar botón
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
      });
    }
  });

  // Función para mostrar notificaciones
  function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification`;
    notification.innerHTML = `
      <i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
      ${message}
    `;

    // Agregar al DOM
    document.body.appendChild(notification);

    // Mostrar con animación
    setTimeout(() => notification.classList.add('show'), 100);

    // Ocultar después de 5 segundos
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => notification.remove(), 300);
    }, 5000);
  }

  // Inicializar tooltips de Bootstrap si está disponible
  if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Inicializar popovers de Bootstrap si está disponible
  if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  }

  // Mejorar tablas con funcionalidades adicionales
  document.addEventListener('DOMContentLoaded', function() {
    // Agregar funcionalidad de ordenamiento a tablas
    const sortableTables = document.querySelectorAll('.table-sortable');
    sortableTables.forEach(table => {
      const headers = table.querySelectorAll('th[data-sort]');
      headers.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
          const column = this.getAttribute('data-sort');
          const direction = this.getAttribute('data-direction') === 'asc' ? 'desc' : 'asc';
          
          // Actualizar indicadores
          headers.forEach(h => h.removeAttribute('data-direction'));
          this.setAttribute('data-direction', direction);
          
          // Ordenar tabla
          sortTable(table, column, direction);
        });
      });
    });

    // Agregar funcionalidad de búsqueda a tablas
    const searchableTables = document.querySelectorAll('.table-searchable');
    searchableTables.forEach(table => {
      const searchInput = document.createElement('input');
      searchInput.type = 'text';
      searchInput.className = 'form-control mb-3';
      searchInput.placeholder = 'Buscar en la tabla...';
      
      table.parentNode.insertBefore(searchInput, table);
      
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
      });
    });
  });

  // Función para ordenar tablas
  function sortTable(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
      const aValue = a.querySelector(`td[data-${column}]`).textContent;
      const bValue = b.querySelector(`td[data-${column}]`).textContent;
      
      if (direction === 'asc') {
        return aValue.localeCompare(bValue);
      } else {
        return bValue.localeCompare(aValue);
      }
    });
    
    // Reordenar filas
    rows.forEach(row => tbody.appendChild(row));
  }

  // Funciones de utilidad para modales
  function showModal(modalId) {
    if (typeof bootstrap !== 'undefined') {
      const modal = new bootstrap.Modal(document.getElementById(modalId));
      modal.show();
    }
  }

  function hideModal(modalId) {
    if (typeof bootstrap !== 'undefined') {
      const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
      if (modal) modal.hide();
    }
  }

  function showLoadingInModal(modalContentId, message = 'Cargando...') {
    const content = document.getElementById(modalContentId);
    if (content) {
      content.innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="mt-3">${message}</p>
        </div>
      `;
    }
  }

  // Exportar funciones útiles
  window.HR365 = {
    showNotification: showNotification,
    sortTable: sortTable,
    showModal: showModal,
    hideModal: hideModal,
    showLoadingInModal: showLoadingInModal
  };

})();

