<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
</head>
<body>
    <h1>Bienvenido, {{ $name }}!</h1>
    <p>Tu cuenta ha sido creada exitosamente en nuestra plataforma.</p>
    <p>Estos son tus datos de acceso:</p>
    <ul>
        <li><strong>Usuario:</strong> {{ $email }}</li>
        <li><strong>Contraseña temporal:</strong> {{ $password }}</li>
    </ul>
    <p>Por favor, cambia tu contraseña después de iniciar sesión por primera vez.</p>
    <p>Gracias,<br>El equipo de {{ config('app.name') }}</p>
</body>
</html>