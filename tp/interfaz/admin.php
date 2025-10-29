<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Chatbot (Admin) – Rows</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tus estilos -->
  <link rel="stylesheet" href="style.css">
</head>
<body class="min-vh-100 d-flex flex-column">
  <!-- Logo corporativo (usa tu clase .logo de style.css) -->
<img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />

<link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />
<script type="module">
	import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

createChat({
	webhookUrl: 'http://192.168.101.92:8084/webhook/923031e5-c20e-47fe-bf5b-ad7a37943ee2',
	webhookConfig: {
		method: 'POST',
		headers: {}
	},
	target: '#n8n-chat',
	mode: 'fullscreen',
	chatInputKey: 'chatInput',
	chatSessionKey: 'sessionId',
	loadPreviousSession: true,
	metadata: {},
	showWelcomeScreen: false,
	defaultLanguage: 'es',
	initialMessages: [
		'¡Hola! 👋',
		'Mi nombre es Electrobot. ¿Cómo puedo ayudarte hoy?'
	],
	i18n: {
		es: {
			title: '¡Hola! 👋',
			subtitle: "Inicia un chat. Estamos aquí para ayudarte 24/7.",
			footer: '',
			getStarted: 'Nueva Conversación',
			inputPlaceholder: 'Escribe tu pregunta..',
		},
	},
	enableStreaming: false,
});
</script>
</body>
</html>
