<?php
// app/views/users/index.php - Listado y modal Agregar Usuario
require_once __DIR__ . '/../../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Recursos/index.php?route=login'); exit; }
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/sidebar.php';

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$roles = ['SUPER USER','TRABAJADOR','VENDEDOR','RECEPCIONISTA','CHOFER','PROGRAMADOR'];
$statuses = ['ACTIVO','INACTIVO'];
?>
      <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:.75rem">
          <h2 style="margin:0">Usuarios</h2>
          <button class="button" id="btn-open-modal"><i class="fa fa-user-plus"></i> Agregar Usuario</button>
        </div>
        <?php if($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
        <form method="get" action="/Recursos/index.php" style="margin-bottom:.75rem; display:flex; gap:.5rem; flex-wrap:wrap">
          <input type="hidden" name="route" value="users.index">
          <input class="input" type="text" name="q" placeholder="Buscar (usuario, email, nombre, código)" value="<?= htmlspecialchars($q ?? '') ?>" style="max-width:360px">
          <select class="input" name="role">
            <option value="">-- Rol --</option>
            <?php foreach($roles as $r): ?>
              <option value="<?= $r ?>" <?= (isset($role) && $role===$r)?'selected':'' ?>><?= $r ?></option>
            <?php endforeach; ?>
          </select>
          <select class="input" name="status">
            <option value="">-- Estado --</option>
            <?php foreach($statuses as $s): ?>
              <option value="<?= $s ?>" <?= (isset($status) && $status===$s)?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <select class="input" name="sort">
            <?php $sorts = ['id'=>'ID','username'=>'Usuario','email'=>'Email','role'=>'Rol','status'=>'Estado','code'=>'Código','created_at'=>'Creado']; foreach($sorts as $k=>$label): ?>
              <option value="<?= $k ?>" <?= (isset($sort) && $sort===$k)?'selected':'' ?>>Ordenar: <?= $label ?></option>
            <?php endforeach; ?>
          </select>
          <select class="input" name="dir">
            <option value="DESC" <?= (!isset($dir) || strtoupper($dir)==='DESC')?'selected':'' ?>>Desc</option>
            <option value="ASC" <?= (isset($dir) && strtoupper($dir)==='ASC')?'selected':'' ?>>Asc</option>
          </select>
          <button class="button" type="submit"><i class="fa fa-search"></i> Buscar</button>
        </form>
        <div style="overflow:auto">
          <table style="width:100%; border-collapse:collapse">
            <thead>
              <tr style="text-align:left; border-bottom:1px solid var(--border)">
                <th style="padding:.5rem">ID</th>
                <th style="padding:.5rem">Usuario</th>
                <th style="padding:.5rem">Nombre</th>
                <th style="padding:.5rem">Rol Usuario</th>
                <th style="padding:.5rem">Estado</th>
                <th style="padding:.5rem">Código</th>
                <th style="padding:.5rem">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($users as $u): ?>
                <tr style="border-bottom:1px solid var(--border)">
                  <td style="padding:.5rem"><?= (int)$u['id'] ?></td>
                  <td style="padding:.5rem; display:flex; align-items:center; gap:.5rem">
                    <?php if(!empty($u['avatar'])): ?><img src="<?= htmlspecialchars($u['avatar']) ?>" alt="avatar" style="width:28px;height:28px;border-radius:50%"><?php else: ?><i class="fa fa-user-circle"></i><?php endif; ?>
                    <div>
                      <div><?= htmlspecialchars($u['username']) ?></div>
                      <small class="helper"><?= htmlspecialchars($u['email']) ?></small>
                    </div>
                  </td>
                  <td style="padding:.5rem"><?= htmlspecialchars(trim(($u['first_name'] ?? '').' '.($u['last_name'] ?? ''))) ?></td>
                  <td style="padding:.5rem"><?= htmlspecialchars($u['role'] ?? '') ?></td>
                  <td style="padding:.5rem"><?= htmlspecialchars($u['status'] ?? '') ?></td>
                  <td style="padding:.5rem"><?= htmlspecialchars($u['code'] ?? '') ?></td>
                  <td style="padding:.5rem; display:flex; gap:.35rem">
                    <button class="button" style="background:#10b981" data-modal-href="/Recursos/index.php?route=users.view&id=<?= (int)$u['id'] ?>"><i class="fa fa-eye"></i> Ver</button>
                    <button class="button" style="background:#f59e0b" data-modal-href="/Recursos/index.php?route=users.edit&id=<?= (int)$u['id'] ?>"><i class="fa fa-pen"></i> Editar</button>
                    <form action="/Recursos/index.php?route=users.delete" method="post" onsubmit="return confirm('¿Eliminar usuario?');" style="display:inline">
                      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                      <button class="button" style="background:#ef4444" type="submit"><i class="fa fa-trash"></i> Eliminar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (($pageCount ?? 1) > 1): ?>
        <div style="margin-top:.75rem; display:flex; gap:.35rem; flex-wrap:wrap">
          <?php $qparam = $q ? ('&q=' . urlencode($q)) : ''; ?>
          <?php if (($page ?? 1) > 1): ?>
            <a class="button" href="/Recursos/index.php?route=users.index&page=<?= ($page-1) . $qparam ?>" style="background:#6b7280"><i class="fa fa-chevron-left"></i> Anterior</a>
          <?php endif; ?>
          <span class="helper" style="align-self:center">Página <?= (int)$page ?> de <?= (int)$pageCount ?></span>
          <?php if (($page ?? 1) < ($pageCount ?? 1)): ?>
            <a class="button" href="/Recursos/index.php?route=users.index&page=<?= ($page+1) . $qparam ?>" style="background:#6b7280">Siguiente <i class="fa fa-chevron-right"></i></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Ventana dinámica Ver/Editar -->
      <div class="modal-backdrop" id="modal-overlay"></div>
      <div class="modal" id="modal-dynamic" role="dialog" aria-modal="true" aria-labelledby="modalDynTitle" style="display:none">
        <header>
          <h3 id="modalDynTitle" style="margin:0"><i class="fa fa-window-restore"></i> Ventana</h3>
          <button class="icon-btn" id="btn-close-dyn" title="Cerrar"><i class="fa fa-times"></i></button>
        </header>
        <div class="modal-body" style="padding:0">
          <iframe id="modal-iframe" style="width:100%;height:70vh;border:0;background:white"></iframe>
        </div>
      </div>

      <!-- Modal crear usuario -->
      <div class="modal" id="modal-create" role="dialog" aria-modal="true" aria-labelledby="modalTitle" style="display:none">
        <header>
          <h3 id="modalTitle" style="margin:0"><i class="fa fa-user-plus"></i> Agregar Usuario</h3>
          <button class="icon-btn" id="btn-close-modal" title="Cerrar"><i class="fa fa-times"></i></button>
        </header>
        <form action="/Recursos/index.php?route=users.create" method="post" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="form-row">
              <label><input type="checkbox" name="include_existing" id="include_existing"> Incluir Personas Existentes?</label>
            </div>
            <div class="form-row" id="existing_select" style="display:none">
              <label for="existing_user_id">Selecciona usuario existente</label>
              <select class="input" name="existing_user_id" id="existing_user_id">
                <option value="">-- Seleccionar --</option>
                <?php foreach($users as $u): ?>
                  <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
              <small class="helper">Si seleccionas uno existente, solo se actualizarán Nombre/Apellidos/Rol/Estado/Código/Imagen.</small>
            </div>

            <div class="grid-2">
              <div class="form-row">
                <label for="first_name">Nombre</label>
                <input class="input" type="text" id="first_name" name="first_name" maxlength="100">
              </div>
              <div class="form-row">
                <label for="last_name">Apellidos</label>
                <input class="input" type="text" id="last_name" name="last_name" maxlength="100">
              </div>
              <div class="form-row">
                <label for="username">Usuario</label>
                <input class="input" type="text" id="username" name="username" maxlength="50" placeholder="usuario">
              </div>
              <div class="form-row">
                <label for="email">Email</label>
                <input class="input" type="email" id="email" name="email" maxlength="100" placeholder="correo@dominio.com">
              </div>
              <div class="form-row">
                <label for="role">Rol Usuario</label>
                <select class="input" id="role" name="role" required>
                  <option value="">-- Seleccionar --</option>
                  <?php foreach($roles as $r): ?>
                    <option value="<?= $r ?>"><?= $r ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-row">
                <label for="status">Estado</label>
                <select class="input" id="status" name="status">
                  <?php foreach($statuses as $s): ?>
                    <option value="<?= $s ?>"><?= $s ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-row">
                <label for="code">Código</label>
                <input class="input" type="text" id="code" name="code" maxlength="32" placeholder="Código interno">
              </div>
              <div class="form-row">
                <label for="avatar">Imagen</label>
                <input class="input" type="file" id="avatar" name="avatar" accept="image/*">
              </div>
              <div class="form-row">
                <label for="password">Contraseña</label>
                <input class="input" type="password" id="password" name="password">
              </div>
              <div class="form-row">
                <label for="password2">Repite Contraseña</label>
                <input class="input" type="password" id="password2" name="password2">
              </div>
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" class="button" id="btn-cancel">Cancelar</button>
            <button type="submit" class="button"><i class="fa fa-save"></i> Guardar</button>
          </div>
        </form>
      </div>

    </main>
  </div>
  <script src="/Recursos/assets/js/main.js"></script>
  <script>
    (function(){
      var openBtn = document.getElementById('btn-open-modal');
      var closeBtn = document.getElementById('btn-close-modal');
      var cancelBtn = document.getElementById('btn-cancel');
      var modal = document.getElementById('modal-create');
      var overlay = document.getElementById('modal-overlay');
      var existing = document.getElementById('include_existing');
      var existingSelect = document.getElementById('existing_select');

      function showModal(){ modal.style.display = 'block'; overlay.classList.add('show'); }
      function hideModal(){ modal.style.display = 'none'; overlay.classList.remove('show'); }
      openBtn && openBtn.addEventListener('click', showModal);
      closeBtn && closeBtn.addEventListener('click', hideModal);
      cancelBtn && cancelBtn.addEventListener('click', hideModal);
      overlay && overlay.addEventListener('click', hideModal);
      existing && existing.addEventListener('change', function(){
        existingSelect.style.display = this.checked ? 'block' : 'none';
        // Si incluir existentes, deshabilitar campos de credenciales
        var disabled = this.checked;
        ['username','email','password','password2'].forEach(function(id){
          var el = document.getElementById(id); if (el){ el.disabled = disabled; if(disabled){ el.removeAttribute('required'); } }
        });
      });
    })();
  </script>
</body>
</html>

