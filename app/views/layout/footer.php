    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Custom JS -->
  <script src="/Recursos/assets/js/main.js"></script>
  <script src="/Recursos/assets/js/modals.js"></script>

  <script>
    // Sistema unificado de modales para HR365
    
    // Función para abrir modal de crear personal
    function openCreateModal() {
      const modal = new bootstrap.Modal(document.getElementById('modal-create'));
      modal.show();
    }

    // Función para abrir modal de ver personal
    function openViewModal(id) {
      showLoadingModal('Ver Personal');
      
      fetch('/Recursos/index.php?route=personal.view&id=' + id)
        .then(response => response.text())
        .then(data => {
          document.getElementById('modal-content').innerHTML = data;
          document.getElementById('modal-title-text').innerHTML = '<i class="fa fa-eye"></i> Ver Personal';
          
          const modal = new bootstrap.Modal(document.getElementById('modal-dynamic'));
          modal.show();
        })
        .catch(error => {
          console.error('Error al cargar datos:', error);
          showErrorModal('Error al cargar datos: ' + error.message);
        });
    }

    // Función para abrir modal de editar personal
    function openEditModal(id) {
      showLoadingModal('Editar Personal');
      
      fetch('/Recursos/index.php?route=personal.edit&id=' + id)
        .then(response => response.text())
        .then(data => {
          document.getElementById('modal-content').innerHTML = data;
          document.getElementById('modal-title-text').innerHTML = '<i class="fa fa-edit"></i> Editar Personal';
          
          const modal = new bootstrap.Modal(document.getElementById('modal-dynamic'));
          modal.show();
        })
        .catch(error => {
          console.error('Error al cargar datos:', error);
          showErrorModal('Error al cargar datos: ' + error.message);
        });
    }

    // Función para abrir modal de eliminación
    function openDeleteModal(id, name) {
      const modal = document.getElementById('modal-delete');
      const message = modal.querySelector('#delete-message');
      if (message) {
        message.innerHTML = `¿Estás seguro de que quieres eliminar a <strong>${name}</strong>?`;
      }
      
      // Configurar datos para eliminación
      modal.setAttribute('data-delete-id', id);
      modal.setAttribute('data-delete-name', name);
      
      const bootstrapModal = new bootstrap.Modal(modal);
      bootstrapModal.show();
    }

    // Función para confirmar eliminación
    function confirmDelete() {
      const modal = document.getElementById('modal-delete');
      const id = modal.getAttribute('data-delete-id');
      
      if (id) {
        // Crear formulario temporal y enviarlo
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/Recursos/index.php?route=personal.delete';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id';
        input.value = id;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
      }
    }

    // Función para mostrar modal de carga
    function showLoadingModal(title) {
      document.getElementById('modal-title-text').innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${title}`;
      document.getElementById('modal-content').innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="mt-3">Cargando contenido...</p>
        </div>
      `;
      
      const modal = new bootstrap.Modal(document.getElementById('modal-dynamic'));
      modal.show();
    }

    // Función para mostrar modal de error
    function showErrorModal(message) {
      document.getElementById('modal-title-text').innerHTML = '<i class="fa fa-exclamation-triangle text-danger"></i> Error';
      document.getElementById('modal-content').innerHTML = `
        <div class="text-center py-4">
          <div class="text-danger mb-3">
            <i class="fa fa-exclamation-triangle fa-3x"></i>
          </div>
          <p class="text-danger">${message}</p>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      `;
    }

    // Manejar formulario de crear personal
    document.addEventListener('DOMContentLoaded', function() {
      const createForm = document.getElementById('create-form');
      if (createForm) {
        createForm.addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          const submitBtn = this.querySelector('[type="submit"]');
          const originalText = submitBtn.innerHTML;
          
          // Mostrar loading
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
          
          fetch('/Recursos/index.php?route=personal.create', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            try {
              const result = JSON.parse(data);
              if (result.success) {
                // Mostrar notificación de éxito
                if (window.HR365 && window.HR365.showNotification) {
                  window.HR365.showNotification('success', result.message || 'Personal creado exitosamente');
                }
                
                // Cerrar modal y recargar página
                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-create'));
                if (modal) modal.hide();
                
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              } else {
                // Mostrar error
                if (window.HR365 && window.HR365.showNotification) {
                  window.HR365.showNotification('error', result.message || 'Error al crear personal');
                } else {
                  alert('Error: ' + (result.message || 'Error al crear personal'));
                }
              }
            } catch (e) {
              // Si no es JSON, verificar si contiene mensajes de éxito/error
              if (data.includes('success') || data.includes('exitosamente')) {
                if (window.HR365 && window.HR365.showNotification) {
                  window.HR365.showNotification('success', 'Personal creado exitosamente');
                }
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-create'));
                if (modal) modal.hide();
                
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              } else {
                if (window.HR365 && window.HR365.showNotification) {
                  window.HR365.showNotification('error', 'Error al crear personal');
                } else {
                  alert('Error al crear personal');
                }
              }
            }
          })
          .catch(error => {
            console.error('Error:', error);
            if (window.HR365 && window.HR365.showNotification) {
              window.HR365.showNotification('error', 'Error de conexión');
            } else {
              alert('Error de conexión: ' + error.message);
            }
          })
          .finally(() => {
            // Rehabilitar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          });
        });
      }
    });
  </script>
</body>
</html>

