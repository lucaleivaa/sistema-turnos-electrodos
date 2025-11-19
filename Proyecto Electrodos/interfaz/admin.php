<?php
session_start();

// ✅ VERIFICAR SESIÓN Y ROL
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit;
}

// ✅ EVITAR CACHÉ
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$usuario = $_SESSION['usuario'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Chatbot (Admin) – Rows</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    /* ✅ Estilos críticos que DEBEN estar inline para no conflictuar con n8n */
    body {
      margin: 0 !important;
      padding: 0 !important;
      overflow: hidden !important;
    }
    
    .admin-header {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      background: #ffffff !important;
      padding: 10px 20px !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
      z-index: 99999 !important;
      height: 60px !important;
    }
    
    .admin-header-info {
      display: flex !important;
      align-items: center !important;
      gap: 15px !important;
    }
    
    .admin-logo {
      height: 40px !important;
      width: auto !important;
    }
    
    .admin-usuario-nombre {
      font-weight: 600 !important;
      color: #013761 !important;
      font-size: 16px !important;
    }
    
.btn-cerrar-sesion {
  background: #19a9e5 !important;  /* ✅ AHORA: Celeste */
  color: white !important;
  padding: 10px 20px !important;
  border-radius: 6px !important;
  text-decoration: none !important;
  font-weight: 500 !important;
  transition: all 0.3s ease !important;
  border: none !important;
}

.btn-cerrar-sesion:hover {
  background: #0d7db8 !important;  /* ✅ AHORA: Celeste oscuro */
  transform: translateY(-1px) !important;
}

    
    #n8n-chat {
      position: fixed !important;
      top: 60px !important;
      left: 0 !important;
      width: 100% !important;
      height: calc(100% - 60px) !important;
      z-index: 1 !important;
    }
  </style>
</head>
<body>
  
  <!-- ✅ HEADER SIEMPRE VISIBLE -->
  <div class="admin-header">
    <div class="admin-header-info">
      <img src="https://electrodos.com.ar/electrodos/images/logo.png" 
           alt="Logo Electrodos" 
           class="admin-logo">
      <span class="admin-usuario-nombre">
        👤 <?= htmlspecialchars($usuario) ?> (Admin)
      </span>
    </div>
    <a href="logout_admin.php" class="btn-cerrar-sesion">Cerrar sesión</a>
  </div>

  <!-- ✅ CHATBOT DEBAJO DEL HEADER -->
  <div id="n8n-chat"></div>

  <link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />
  <script type="module">
    import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

createChat({
    webhookUrl: 'http://192.168.101.92:5678/webhook/3e3dc6fb-7edf-4ec7-8015-7bcae87b9c8c/chat',
    webhookConfig: {
        method: 'POST',
        headers: {}
    },
    target: '#n8n-chat',
    mode: 'fullscreen',
    chatInputKey: 'chatInput',
    chatSessionKey: 'sessionId',
    loadPreviousSession: false,
    metadata: {},
    showWelcomeScreen: false,              // ← AGREGÁ ESTO (pone false)
    defaultLanguage: 'es',
    initialMessages: [],
    i18n: {
        es: {
            title: '¡Hola! 👋',
            subtitle: "Estamos aquí para ayudarte 24/7.",
            footer: '',
            getStarted: 'Nueva Conversación',
            inputPlaceholder: 'Escribe tu pregunta..',
        },
    },
    enableStreaming: false,
});


  </script>

  <script>
    window.addEventListener('pageshow', function(event) {
      if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
      }
    });
  </script>

</body>
</html>
