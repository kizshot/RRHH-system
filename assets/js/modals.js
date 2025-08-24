/**
 * HR365 - Sistema de Modales
 * Sistema unificado de modales compatible con Bootstrap 5.3.2
 * Maneja todos los modales de la aplicación de manera eficiente
 */

class ModalManager {
    constructor() {
        this.activeModal = null;
        this.modalStack = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupKeyboardShortcuts();
    }

    bindEvents() {
        // Cerrar modales al hacer clic en backdrop
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop')) {
                this.closeActiveModal();
            }
        });

        // Cerrar modales con botones de cierre
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-close') || 
                e.target.closest('.modal-close')) {
                this.closeActiveModal();
            }
        });

        // Manejar formularios en modales
        document.addEventListener('submit', (e) => {
            if (e.target.closest('.modal')) {
                this.handleFormSubmit(e);
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.closeActiveModal();
            }
        });
    }

    openModal(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error(`Modal ${modalId} no encontrado`);
            return false;
        }

        // Si es un modal de Bootstrap, usar la API de Bootstrap
        if (modal.classList.contains('modal') && typeof bootstrap !== 'undefined') {
            const bootstrapModal = new bootstrap.Modal(modal, options);
            bootstrapModal.show();
            return true;
        }

        // Cerrar modal activo si existe
        if (this.activeModal) {
            this.closeActiveModal();
        }

        // Configurar opciones del modal
        this.configureModal(modal, options);

        // Mostrar modal
        modal.style.display = 'flex';
        modal.classList.add('show');
        
        // Agregar backdrop
        this.showBackdrop();

        // Guardar referencia
        this.activeModal = modal;
        this.modalStack.push(modal);

        // Enfocar primer input si existe
        setTimeout(() => {
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);

        // Evento personalizado
        modal.dispatchEvent(new CustomEvent('modal:opened', { detail: options }));

        return true;
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return false;

        // Si es un modal de Bootstrap, usar la API de Bootstrap
        if (modal.classList.contains('modal') && typeof bootstrap !== 'undefined') {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
                return true;
            }
        }

        modal.style.display = 'none';
        modal.classList.remove('show');
        
        // Remover del stack
        this.modalStack = this.modalStack.filter(m => m !== modal);
        
        // Actualizar modal activo
        if (this.activeModal === modal) {
            this.activeModal = this.modalStack[this.modalStack.length - 1] || null;
        }

        // Evento personalizado
        modal.dispatchEvent(new CustomEvent('modal:closed'));

        return true;
    }

    closeActiveModal() {
        if (this.activeModal) {
            this.closeModal(this.activeModal.id);
        }
    }

    closeAllModals() {
        this.modalStack.forEach(modal => {
            this.closeModal(modal.id);
        });
        this.modalStack = [];
        this.activeModal = null;
    }

    configureModal(modal, options) {
        // Configurar tamaño
        if (options.size) {
            modal.classList.add(`modal-${options.size}`);
        }

        // Configurar tipo
        if (options.type) {
            modal.classList.add(`modal-${options.type}`);
        }

        // Configurar scroll
        if (options.scrollable !== undefined) {
            modal.classList.toggle('modal-scrollable', options.scrollable);
        }

        // Configurar backdrop
        if (options.backdrop !== undefined) {
            modal.setAttribute('data-backdrop', options.backdrop);
        }
    }

    showBackdrop() {
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop';
            document.body.appendChild(backdrop);
        }
        backdrop.classList.add('show');
    }

    hideBackdrop() {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.classList.remove('show');
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        
        // Mostrar loading
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
        }

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            
            // Verificar si es JSON
            let data;
            try {
                data = JSON.parse(result);
            } catch {
                data = { success: false, message: result };
            }

            if (data.success) {
                this.showNotification('success', data.message || 'Operación completada exitosamente');
                
                // Cerrar modal después de un delay
                setTimeout(() => {
                    this.closeActiveModal();
                    // Recargar página si es necesario
                    if (data.reload !== false) {
                        window.location.reload();
                    }
                }, 1500);
            } else {
                this.showNotification('error', data.message || 'Ha ocurrido un error');
            }
        } catch (error) {
            this.showNotification('error', 'Error de conexión: ' + error.message);
        } finally {
            // Rehabilitar botón
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }

    showNotification(type, message) {
        // Crear notificación
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

    // Métodos de conveniencia para modales específicos
    openCreateModal() {
        return this.openModal('modal-create', {
            type: 'form',
            size: 'large'
        });
    }

    openEditModal(id) {
        this.loadModalContent(`/Recursos/index.php?route=personal.edit&id=${id}`, 'modal-dynamic', {
            title: 'Editar Personal',
            type: 'form'
        });
    }

    openViewModal(id) {
        this.loadModalContent(`/Recursos/index.php?route=personal.view&id=${id}`, 'modal-dynamic', {
            title: 'Ver Personal',
            type: 'view'
        });
    }

    openDeleteModal(id, name) {
        const modal = document.getElementById('modal-delete');
        if (modal) {
            const message = modal.querySelector('#delete-message');
            if (message) {
                message.innerHTML = `¿Estás seguro de que quieres eliminar a <strong>${name}</strong>?`;
            }
            
            // Configurar datos para eliminación
            modal.setAttribute('data-delete-id', id);
            modal.setAttribute('data-delete-name', name);
            
            return this.openModal('modal-delete', {
                type: 'confirm',
                size: 'small'
            });
        }
        return false;
    }

    async loadModalContent(url, modalId, options = {}) {
        try {
            const response = await fetch(url);
            const content = await response.text();
            
            const modal = document.getElementById(modalId);
            if (modal) {
                const contentContainer = modal.querySelector('#modal-content');
                if (contentContainer) {
                    contentContainer.innerHTML = content;
                }
                
                // Actualizar título si se especifica
                if (options.title) {
                    const titleElement = modal.querySelector('#modal-title');
                    if (titleElement) {
                        titleElement.textContent = options.title;
                    }
                }
                
                // Abrir modal
                return this.openModal(modalId, options);
            }
        } catch (error) {
            console.error('Error al cargar contenido del modal:', error);
            this.showNotification('error', 'Error al cargar datos');
        }
        return false;
    }

    // Método para confirmar eliminación
    confirmDelete() {
        const modal = document.getElementById('modal-delete');
        if (!modal) return;

        const id = modal.getAttribute('data-delete-id');
        const name = modal.getAttribute('data-delete-name');

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
}

// Inicializar gestor de modales
const modalManager = new ModalManager();

// Funciones globales para compatibilidad
window.openModal = (modalId, options) => modalManager.openModal(modalId, options);
window.closeModal = (modalId) => modalManager.closeModal(modalId);
window.openCreateModal = () => modalManager.openCreateModal();
window.openEditModal = (id) => modalManager.openEditModal(id);
window.openViewModal = (id) => modalManager.openViewModal(id);
window.openDeleteModal = (id, name) => modalManager.openDeleteModal(id, name);
window.confirmDelete = () => modalManager.confirmDelete();

// Exportar para uso en otros módulos
window.ModalManager = ModalManager; 
