<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CRUD Clientes (Frontend básico)</title>
  <style>
    :root { --primary:#2563eb; --danger:#dc2626; --gray:#e5e7eb; --dark:#111827; }
    * { box-sizing: border-box; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
    body { margin: 0; background: #f8fafc; color: #111827; }
    header { background: var(--primary); color: #fff; padding: 16px 24px; }
    main { max-width: 1000px; margin: 24px auto; padding: 0 16px; }
    h1 { margin: 0 0 8px; font-size: 22px; }
    .card { background: #fff; border: 1px solid var(--gray); border-radius: 10px; padding: 16px; margin-bottom: 16px; }
    .row { display: flex; gap: 12px; flex-wrap: wrap; }
    .col { flex: 1; min-width: 220px; }
    label { display:block; font-size: 12px; color:#374151; margin-bottom: 6px; }
    input { width: 100%; padding: 10px 12px; border: 1px solid var(--gray); border-radius: 8px; }
    button { cursor: pointer; border: 0; border-radius: 8px; padding: 10px 14px; font-weight: 600; }
    .btn { background: var(--primary); color: #fff; }
    .btn.secondary { background: #6b7280; }
    .btn.danger { background: var(--danger); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 12px; border-bottom: 1px solid var(--gray); text-align: left; }
    tr:hover { background: #f3f4f6; }
    .actions { display:flex; gap:8px; }
    .muted { color:#6b7280; font-size:12px; }
    .toast { position: fixed; right: 16px; bottom: 16px; background: #111827; color:#fff; padding:10px 12px; border-radius:8px; opacity:0; transform: translateY(8px); transition: .25s; }
    .toast.show { opacity:1; transform: translateY(0); }
  </style>
</head>
<body>
  <header>
    <h1>CRUD de Clientes</h1>
    <div class="muted">API base: /api/clients</div>
  </header>

  <main>
    <section class="card">
      <h2 style="margin-top:0">Crear / Editar</h2>
      <form id="clientForm">
        <input type="hidden" id="clientId" />
        <div class="row">
          <div class="col">
            <label for="name">Nombre</label>
            <input id="name" required />
          </div>
          <div class="col">
            <label for="email">Email</label>
            <input id="email" type="email" required />
          </div>
          <div class="col">
            <label for="phone">Teléfono</label>
            <input id="phone" />
          </div>
        </div>
        <div style="margin-top:12px; display:flex; gap:8px;">
          <button class="btn" type="submit">Guardar</button>
          <button class="btn secondary" type="button" id="resetBtn">Limpiar</button>
        </div>
      </form>
    </section>

    <section class="card">
      <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:8px;">
        <h2 style="margin:0">Listado</h2>
        <button class="btn secondary" id="reloadBtn">Recargar</button>
      </div>
      <div class="muted" id="meta"></div>
      <div style="overflow:auto;">
        <table id="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </section>
  </main>

  <div id="toast" class="toast"></div>

  <script>
    const API = '/api/clients';

    const el = sel => document.querySelector(sel);
    const tbody = el('#table tbody');
    const meta = el('#meta');
    const form = el('#clientForm');
    const toast = el('#toast');

    function showToast(msg) {
      toast.textContent = msg; toast.classList.add('show');
      setTimeout(()=> toast.classList.remove('show'), 2200);
    }

    function serializeForm() {
      return {
        name: el('#name').value.trim(),
        email: el('#email').value.trim(),
        phone: el('#phone').value.trim() || null,
      };
    }

    function resetForm() {
      form.reset(); el('#clientId').value = '';
    }

    function editRow(c) {
      el('#clientId').value = c.id;
      el('#name').value = c.name;
      el('#email').value = c.email;
      el('#phone').value = c.phone ?? '';
      window.scrollTo({ top: 0, behavior:'smooth' });
    }

    async function list(pageUrl = API) {
      const res = await fetch(pageUrl);
      if (!res.ok) { showToast('Error al leer clientes'); return; }
      const data = await res.json();

      const items = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
      const links = data.links || {};
      const metaData = data.meta || null;

      tbody.innerHTML = items.map(c => `
        <tr>
          <td>${c.id}</td>
          <td>${c.name}</td>
          <td>${c.email}</td>
          <td>${c.phone ?? ''}</td>
          <td class="actions">
            <button class="btn edit" 
              data-id="${c.id}"
              data-name="${c.name?.replaceAll('"','&quot;')}"
              data-email="${c.email?.replaceAll('"','&quot;')}"
              data-phone="${(c.phone ?? '').replaceAll('"','&quot;')}">Editar</button>
            <button class="btn danger delete" data-id="${c.id}">Eliminar</button>
          </td>
        </tr>`).join('');

      if (metaData) {
        meta.textContent = `Página ${metaData.current_page} de ${metaData.last_page} • Total: ${metaData.total}`;
      } else {
        meta.textContent = '';
      }

      // Paginación simple si existe
      if (links && links.next) {
        const btn = document.createElement('button');
        btn.textContent = 'Siguiente página';
        btn.className = 'btn secondary';
        btn.onclick = ()=> list(links.next);
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 5; td.appendChild(btn); tr.appendChild(td); tbody.appendChild(tr);
      }
    }

    async function createOrUpdate(evt) {
      evt.preventDefault();
      const id = el('#clientId').value;
      const body = JSON.stringify(serializeForm());
      const opts = { headers: { 'Content-Type': 'application/json' }, body };

      const res = await fetch(id ? `${API}/${id}` : API, { method: id ? 'PUT' : 'POST', ...opts });
      if (!res.ok) { const msg = await res.text(); showToast('Error: '+msg); return; }
      resetForm(); showToast('Guardado'); list();
    }

    async function deleteRow(id) {
      if (!confirm('¿Eliminar cliente #' + id + '?')) return;
      const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
      if (!res.ok) { showToast('Error eliminando'); return; }
      showToast('Eliminado'); list();
    }

    // Eventos
    tbody.addEventListener('click', (e) => {
      const t = e.target;
      if (t.matches('button.edit')) {
        const c = {
          id: Number(t.dataset.id),
          name: t.dataset.name || '',
          email: t.dataset.email || '',
          phone: t.dataset.phone || ''
        };
        editRow(c);
      }
      if (t.matches('button.delete')) {
        deleteRow(Number(t.dataset.id));
      }
    });
    form.addEventListener('submit', createOrUpdate);
    el('#resetBtn').addEventListener('click', resetForm);
    el('#reloadBtn').addEventListener('click', ()=> list());

    // Init
    list();
  </script>
</body>
</html>
