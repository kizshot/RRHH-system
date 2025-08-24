<?php
// app/views/personal/test_modals.php - Archivo de prueba para modales
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Modales - HR365</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Recursos/assets/css/style.css">
    <link rel="stylesheet" href="/Recursos/assets/css/components.css">
    <link rel="stylesheet" href="/Recursos/assets/css/modals.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Prueba de Modales - HR365</h1>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Botones de Prueba</h5>
                        <p class="card-text">Haz clic en los botones para probar los diferentes tipos de modales:</p>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-primary" onclick="openCreateModal()">
                                <i class="fa fa-user-plus"></i> Modal Crear
                            </button>
                            
                            <button class="btn btn-info" onclick="openViewModal(1)">
                                <i class="fa fa-eye"></i> Modal Ver
                            </button>
                            
                            <button class="btn btn-warning" onclick="openEditModal(1)">
                                <i class="fa fa-edit"></i> Modal Editar
                            </button>
                            
                            <button class="btn btn-danger" onclick="openDeleteModal(1, 'Juan Pérez')">
                                <i class="fa fa-trash"></i> Modal Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Crear Personal -->
    <div class="modal fade" id="modal-create" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-user-plus"></i> Agregar Personal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="create-form" method="POST" action="/Recursos/index.php?route=personal.create">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="employee_code" class="form-label">Código de Empleado *</label>
                                <input type="text" class="form-control" id="employee_code" name="employee_code" required>
                            </div>
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="form-label">Departamento</label>
                                <input type="text" class="form-control" id="department" name="department">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="create-form" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="modal-delete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-exclamation-triangle"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fa fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <p id="delete-message" class="mb-2">¿Estás seguro de que quieres eliminar este personal?</p>
                    <p class="text-muted small"><strong>Esta acción no se puede deshacer.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fa fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Dinámico -->
    <div class="modal fade" id="modal-dynamic" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-text">
                        <i class="fa fa-user"></i> Personal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-content">
                    <!-- El contenido se cargará dinámicamente aquí -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando contenido...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/Recursos/assets/js/main.js"></script>
    <script src="/Recursos/assets/js/modals.js"></script>

    <script>
        // Funciones de prueba para modales
        
        function openCreateModal() {
            const modal = new bootstrap.Modal(document.getElementById('modal-create'));
            modal.show();
        }

        function openViewModal(id) {
            showLoadingModal('Ver Personal');
            
            // Simular carga de datos
            setTimeout(() => {
                document.getElementById('modal-content').innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Código:</label>
                            <p>EMP-${id}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre:</label>
                            <p>Juan Pérez</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Departamento:</label>
                            <p>Recursos Humanos</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado:</label>
                            <span class="badge bg-success">Activo</span>
                        </div>
                    </div>
                `;
                document.getElementById('modal-title-text').innerHTML = '<i class="fa fa-eye"></i> Ver Personal';
                
                const modal = new bootstrap.Modal(document.getElementById('modal-dynamic'));
                modal.show();
            }, 1000);
        }

        function openEditModal(id) {
            showLoadingModal('Editar Personal');
            
            // Simular carga de datos
            setTimeout(() => {
                document.getElementById('modal-content').innerHTML = `
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_code" class="form-label">Código</label>
                                <input type="text" class="form-control" id="edit_code" value="EMP-${id}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="edit_name" value="Juan Pérez">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_department" class="form-label">Departamento</label>
                                <input type="text" class="form-control" id="edit_department" value="Recursos Humanos">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Estado</label>
                                <select class="form-select" id="edit_status">
                                    <option value="ACTIVO" selected>Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                    <option value="VACACIONES">Vacaciones</option>
                                </select>
                            </div>
                        </div>
                    </form>
                `;
                document.getElementById('modal-title-text').innerHTML = '<i class="fa fa-edit"></i> Editar Personal';
                
                const modal = new bootstrap.Modal(document.getElementById('modal-dynamic'));
                modal.show();
            }, 1000);
        }

        function openDeleteModal(id, name) {
            const modal = document.getElementById('modal-delete');
            const message = modal.querySelector('#delete-message');
            if (message) {
                message.innerHTML = `¿Estás seguro de que quieres eliminar a <strong>${name}</strong>?`;
            }
            
            modal.setAttribute('data-delete-id', id);
            modal.setAttribute('data-delete-name', name);
            
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        }

        function confirmDelete() {
            const modal = document.getElementById('modal-delete');
            const id = modal.getAttribute('data-delete-id');
            const name = modal.getAttribute('data-delete-name');
            
            alert(`Eliminando a ${name} (ID: ${id})`);
            
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            bootstrapModal.hide();
        }

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
    </script>
</body>
</html>
