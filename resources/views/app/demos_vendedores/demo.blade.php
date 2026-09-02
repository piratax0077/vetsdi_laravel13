<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MEDICHILE Demo</title>
  <style>
    :root {
      --bg: #eef4f7;
      --card: #ffffff;
      --primary: #0d6efd;
      --primary-dark: #0a58ca;
      --secondary: #20c997;
      --text: #1f2937;
      --muted: #6b7280;
      --border: #dbe3ea;
      --danger: #dc3545;
      --shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
      --radius: 22px;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: linear-gradient(160deg, #f5fbff 0%, #edf3f7 45%, #e8f0f5 100%);
      color: var(--text);
    }

    .app-shell {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .phone {
      width: 390px;
      min-height: 800px;
      background: var(--card);
      border-radius: 32px;
      box-shadow: var(--shadow);
      overflow: hidden;
      border: 1px solid #e9eef3;
      position: relative;
    }

    .status-bar {
      padding: 14px 20px 6px;
      font-size: 12px;
      color: var(--muted);
      display: flex;
      justify-content: space-between;
    }

    .screen {
      display: none;
      padding: 18px 20px 28px;
      min-height: calc(800px - 32px);
    }

    .screen.active {
      display: block;
    }

    .hero {
      padding: 18px 0 8px;
    }

    .logo {
      width: 64px;
      height: 64px;
      border-radius: 18px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 18px;
    }

    h1, h2, h3, p {
      margin: 0;
    }

    h1 {
      font-size: 28px;
      line-height: 1.15;
      margin-bottom: 10px;
    }

    h2 {
      font-size: 22px;
      margin-bottom: 8px;
    }

    .subtext {
      color: var(--muted);
      font-size: 14px;
      line-height: 1.5;
    }

    .card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 16px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .card + .card,
    .section + .section {
      margin-top: 14px;
    }

    .feature-list {
      display: grid;
      gap: 10px;
      margin-top: 16px;
    }

    .feature-item {
      background: #f8fbfd;
      border: 1px solid #e7eef5;
      padding: 12px 14px;
      border-radius: 16px;
      font-size: 14px;
    }

    .actions {
      display: grid;
      gap: 12px;
      margin-top: 22px;
    }

    button {
      border: 0;
      border-radius: 16px;
      padding: 14px 16px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.12s ease, opacity 0.2s ease, background 0.2s ease;
    }

    button:hover { transform: translateY(-1px); }
    button:active { transform: translateY(0); }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
    }

    .btn-secondary {
      background: #eef6ff;
      color: var(--primary);
      border: 1px solid #cfe1ff;
    }

    .btn-ghost {
      background: transparent;
      color: var(--muted);
      border: 1px dashed #cfd8e3;
    }

    .login-form {
      display: grid;
      gap: 12px;
      margin-top: 18px;
    }

    label {
      font-size: 13px;
      color: #4b5563;
      font-weight: 700;
      margin-bottom: 6px;
      display: block;
    }

    input, select {
      width: 100%;
      border: 1px solid #d9e3ec;
      background: #fbfdff;
      border-radius: 14px;
      padding: 13px 14px;
      font-size: 14px;
      outline: none;
    }

    input:focus, select:focus {
      border-color: #8ab4ff;
      box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }

    .row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
    }

    .link-btn {
      background: none;
      padding: 0;
      font-size: 13px;
      color: var(--primary);
      font-weight: 700;
    }

    .hint {
      color: var(--muted);
      font-size: 12px;
      line-height: 1.4;
    }

    .error {
      color: var(--danger);
      font-size: 13px;
      display: none;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    .welcome {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 4px;
    }

    .dashboard-title {
      font-size: 20px;
      font-weight: 800;
    }

    .summary {
      background: linear-gradient(135deg, #0d6efd, #20c997);
      color: white;
      border-radius: 22px;
      padding: 18px;
    }

    .summary small {
      opacity: 0.88;
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-top: 14px;
    }

    .mini-card {
      background: #f8fbfd;
      border: 1px solid #e5edf3;
      border-radius: 18px;
      padding: 14px;
      min-height: 96px;
    }

    .mini-card strong {
      display: block;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .mini-card span {
      color: var(--muted);
      font-size: 13px;
      line-height: 1.4;
    }

    .bottom-nav {
      position: sticky;
      bottom: 0;
      margin-top: 18px;
      background: rgba(255,255,255,0.96);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 10px;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
      backdrop-filter: blur(10px);
    }

    .nav-item {
      text-align: center;
      font-size: 11px;
      color: var(--muted);
      padding: 8px 4px;
      border-radius: 12px;
    }

    .nav-item.active {
      background: #eef6ff;
      color: var(--primary);
      font-weight: 700;
    }

    .badge {
      display: inline-block;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      background: #ebf8f3;
      color: #15803d;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <div class="phone">
      <div class="status-bar">
        <span>9:41</span>
        <span>MEDICHILE Demo</span>
      </div>

      <section id="screen-home" class="screen active">
        <div class="hero">
          <div class="logo">M</div>
          <h1>Tu salud digital en un solo lugar</h1>
          <p class="subtext">
            Plataforma integrada para pacientes, profesionales y centros médicos.
            Acceso seguro a recetas, exámenes, fichas clínicas y documentos firmados.
          </p>
        </div>

        <div class="card section">
          <h3>¿Qué puedes mostrar en este demo?</h3>
          <div class="feature-list">
            <div class="feature-item">Login único por perfil</div>
            <div class="feature-item">Panel para paciente</div>
            <div class="feature-item">Panel para profesional</div>
            <div class="feature-item">Panel para centro médico</div>
          </div>
        </div>

        <div class="actions">
          <button class="btn-primary" onclick="showScreen('login')">Iniciar sesión</button>
          <button class="btn-secondary" onclick="showScreen('login')">Crear cuenta</button>
        </div>
      </section>

      <section id="screen-login" class="screen">
        <div class="row" style="margin-bottom: 10px;">
          <button class="link-btn" onclick="showScreen('home')">← Volver</button>
          <span class="hint">Acceso demo</span>
        </div>

        <div class="hero" style="padding-top: 8px;">
          <h2>Iniciar sesión</h2>
          <p class="subtext">Ingresa como paciente, profesional o centro médico.</p>
        </div>

        <div class="card">
          <div class="login-form">
            <div>
              <label for="perfil">Perfil</label>
              <select id="perfil">
                <option value="paciente">Paciente</option>
                <option value="profesional">Profesional</option>
                <option value="centro">Centro médico</option>
              </select>
            </div>

            <div>
              <label for="correo">Correo o RUT</label>
              <input id="correo" type="text" placeholder="ejemplo@correo.cl o 12.345.678-9" />
            </div>

            <div>
              <label for="clave">Contraseña</label>
              <input id="clave" type="password" placeholder="Ingresa tu contraseña" />
            </div>

            <div class="row">
              <button class="link-btn" onclick="alert('Demo: flujo de recuperación de contraseña')">¿Olvidaste tu contraseña?</button>
            </div>

            <div id="login-error" class="error">Completa correo/RUT y contraseña para entrar al demo.</div>

            <button class="btn-primary" onclick="loginDemo()">Ingresar</button>
            <p class="hint">
              Demo libre: puedes entrar con cualquier correo/RUT y cualquier contraseña.
            </p>
          </div>
        </div>
      </section>

      <section id="screen-paciente" class="screen">
        <div class="topbar">
          <div>
            <div class="welcome">Bienvenido</div>
            <div class="dashboard-title">Panel Paciente</div>
          </div>
          <div class="avatar">JP</div>
        </div>

        <div class="summary">
          <small>Próxima atención</small>
          <h3 style="margin-top: 6px; font-size: 24px;">Lunes 10:30</h3>
          <div class="badge">Documentos al día</div>
        </div>

        <div class="grid">
          <div class="mini-card"><strong>Mis recetas</strong><span>Visualiza recetas activas y emitidas.</span></div>
          <div class="mini-card"><strong>Exámenes</strong><span>Resultados y documentos adjuntos.</span></div>
          <div class="mini-card"><strong>Crónicos</strong><span>Control de tratamientos y reposición.</span></div>
          <div class="mini-card"><strong>Despacho</strong><span>Solicitud de reparto a domicilio.</span></div>
        </div>

        <div class="bottom-nav">
          <div class="nav-item active">Inicio</div>
          <div class="nav-item">Recetas</div>
          <div class="nav-item">Agenda</div>
          <div class="nav-item" onclick="logout()">Salir</div>
        </div>
      </section>

      <section id="screen-profesional" class="screen">
        <div class="topbar">
          <div>
            <div class="welcome">Bienvenido</div>
            <div class="dashboard-title">Panel Profesional</div>
          </div>
          <div class="avatar">DR</div>
        </div>

        <div class="summary">
          <small>Agenda del día</small>
          <h3 style="margin-top: 6px; font-size: 24px;">8 pacientes agendados</h3>
          <div class="badge">Firma electrónica activa</div>
        </div>

        <div class="grid">
          <div class="mini-card"><strong>Agenda</strong><span>Control de horas y próximas atenciones.</span></div>
          <div class="mini-card"><strong>Ficha clínica</strong><span>Registro y seguimiento del paciente.</span></div>
          <div class="mini-card"><strong>Recetas</strong><span>Emisión rápida con validación.</span></div>
          <div class="mini-card"><strong>Certificados</strong><span>Documentos firmados digitalmente.</span></div>
        </div>

        <div class="bottom-nav">
          <div class="nav-item active">Inicio</div>
          <div class="nav-item">Pacientes</div>
          <div class="nav-item">Firma</div>
          <div class="nav-item" onclick="logout()">Salir</div>
        </div>
      </section>

      <section id="screen-centro" class="screen">
        <div class="topbar">
          <div>
            <div class="welcome">Bienvenido</div>
            <div class="dashboard-title">Panel Centro Médico</div>
          </div>
          <div class="avatar">CM</div>
        </div>

        <div class="summary">
          <small>Operación del centro</small>
          <h3 style="margin-top: 6px; font-size: 24px;">24 profesionales activos</h3>
          <div class="badge">Sistema sincronizado</div>
        </div>

        <div class="grid">
          <div class="mini-card"><strong>Profesionales</strong><span>Alta, control y administración interna.</span></div>
          <div class="mini-card"><strong>Pacientes</strong><span>Gestión y trazabilidad de atención.</span></div>
          <div class="mini-card"><strong>Documentos</strong><span>Recetas, certificados y archivos.</span></div>
          <div class="mini-card"><strong>Reportes</strong><span>Indicadores, uso y productividad.</span></div>
        </div>

        <div class="bottom-nav">
          <div class="nav-item active">Inicio</div>
          <div class="nav-item">Gestión</div>
          <div class="nav-item">Reportes</div>
          <div class="nav-item" onclick="logout()">Salir</div>
        </div>
      </section>
    </div>
  </div>

  <script>
    function showScreen(name) {
      document.querySelectorAll('.screen').forEach(screen => screen.classList.remove('active'));
      document.getElementById('screen-' + name).classList.add('active');
    }

    function loginDemo() {
      const perfil = document.getElementById('perfil').value;
      const correo = document.getElementById('correo').value.trim();
      const clave = document.getElementById('clave').value.trim();
      const error = document.getElementById('login-error');

      if (!correo || !clave) {
        error.style.display = 'block';
        return;
      }

      error.style.display = 'none';
      showScreen(perfil);
    }

    function logout() {
      showScreen('login');
    }
  </script>
</body>
</html>
