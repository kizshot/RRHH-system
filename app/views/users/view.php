<?php
// app/views/users/view.php - Ver usuario (versión para modal)
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { 
  echo '<div class="alert error">Sesión expirada. Por favor, recarga la página.</div>';
  exit; 
}

// Verificar si se está llamando desde modal
$isModal = isset($_GET['modal']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
?>
      <div class="card">
        <h2 class="page-title">Detalle de Usuario</h2>
        <?php if(!$user): ?>
          <div class="alert error">Usuario no encontrado</div>
        <?php else: ?>
          <div class="user-header">
            <?php if(!empty($user['avatar'])): ?>
              <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="avatar">
            <?php else: ?>
              <i class="fa fa-user-circle"></i>
            <?php endif; ?>
            <div>
              <h3><?= htmlspecialchars($user['username']) ?></h3>
              <div class="user-meta">
                <div><strong>ID:</strong> <?= (int)$user['id'] ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
              </div>
            </div>
          </div>
          
          <div class="user-info" style="margin-bottom:1.5rem;">
            <h4 style="margin:0 0 1rem 0; color:#667eea; font-size:1.125rem; border-bottom:2px solid #e5e7eb; padding-bottom:0.5rem;">Información Personal</h4>
            <div class="grid-2">
              <div class="info-item">
                <label class="info-label">Nombre</label>
                <div class="info-value"><?= htmlspecialchars($user['first_name'] ?? 'No especificado') ?></div>
              </div>
                             <div class="info-item">
                 <label class="info-label">Apellidos</label>
                 <div class="info-value"><?= htmlspecialchars($user['last_name'] ?? 'No especificado') ?></div>
               </div>
              <div class="info-item">
                <label class="info-label">Rol</label>
                <div class="info-value">
                  <span class="role-badge">
                    <?= htmlspecialchars($user['role'] ?? 'No especificado') ?>
                  </span>
                </div>
              </div>
              <div class="info-item">
                <label class="info-label">Estado</label>
                <div class="info-value">
                  <span class="status-badge <?= ($user['status'] ?? '') === 'ACTIVO' ? 'status-active' : 'status-inactive' ?>">
                    <?= htmlspecialchars($user['status'] ?? 'No especificado') ?>
                  </span>
                </div>
              </div>
              <div class="info-item">
                <label class="info-label">Código</label>
                <div class="info-value"><?= htmlspecialchars($user['code'] ?? 'No especificado') ?></div>
              </div>
              <div class="info-item">
                <label class="info-label">Fecha de Creación</label>
                <div class="info-value"><?= htmlspecialchars($user['created_at'] ?? 'No especificado') ?></div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

<style>
.page-title {
  margin: 0 0 1.5rem 0;
  color: #374151;
  font-size: 1.75rem;
  font-weight: 700;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0.75rem;
}

.user-header {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.user-header img {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #667eea;
}

.user-header i {
  font-size: 48px;
  color: #667eea;
}

.user-header h3 {
  margin: 0 0 0.5rem 0;
  color: #374151;
  font-size: 1.5rem;
  font-weight: 700;
}

.user-header .user-meta {
  color: #6b7280;
  font-size: 0.875rem;
}

.user-header .user-meta div {
  margin-bottom: 0.25rem;
}

.user-header .user-meta strong {
  color: #374151;
}

.user-info h4 {
  margin: 0 0 1rem 0;
  color: #667eea;
  font-size: 1.125rem;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0.5rem;
  font-weight: 600;
}

.info-item {
  margin-bottom: 1rem;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #6b7280;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-value {
  font-size: 1rem;
  color: #374151;
  font-weight: 500;
}

.role-badge {
  background: #667eea;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-block;
}

.status-active {
  background: #10b981;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-block;
}

.status-inactive {
  background: #ef4444;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-block;
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

@media (max-width: 768px) {
  .grid-2 {
    grid-template-columns: 1fr;
  }
  
  .user-header {
    flex-direction: column;
    text-align: center;
  }
}
</style>

