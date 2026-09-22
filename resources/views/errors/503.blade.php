<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización en Curso</title>
    <style>
        :root {
            --bg-color: #f8f9fa;
            --text-color: #2d3436;
            --accent-color: #0984e3; /* Professional Blue */
            --secondary-text: #636e72;
            --font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-color);
            color: var(--text-color);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            overflow: hidden;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            animation: fadeIn 0.8s ease-out;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-color);
            letter-spacing: -0.5px;
        }

        h2 {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--accent-color);
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--secondary-text);
            margin-bottom: 30px;
        }

        /* Progress Bar Animation */
        .progress-container {
            width: 100%;
            background-color: #dfe6e9;
            border-radius: 50px;
            height: 6px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background-color: var(--accent-color);
            width: 30%;
            border-radius: 50px;
            position: absolute;
            top: 0;
            left: 0;
            animation: indeterminateProgress 2s infinite ease-in-out;
        }

        .status-text {
            font-size: 0.85rem;
            color: var(--secondary-text);
            font-style: italic;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 40px;
            font-size: 0.8rem;
            color: #b2bec3;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes indeterminateProgress {
            0% { left: -30%; width: 30%; }
            50% { width: 60%; }
            100% { left: 100%; width: 30%; }
        }

        /* Pulsing Circle Indicator (Optional addition for visual interest) */
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: var(--accent-color);
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(9, 132, 227, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(9, 132, 227, 0); }
            100% { box-shadow: 0 0 0 0 rgba(9, 132, 227, 0); }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>¡Volvemos Enseguida!</h1>
        <h2>Estamos en Proceso de Actualización</h2>
        
        <p>
            Disculpe las molestias. Estamos implementando mejoras críticas de seguridad y nuevas funcionalidades para ofrecerle una mejor experiencia. Nuestro equipo espera completar el mantenimiento y estar de vuelta en línea en las próximas horas.
        </p>

        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        <div class="status-text">
            <span class="status-indicator"></span>
            Optimizando rendimiento del sistema...
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Tucanaltv') }}
        </div>
    </div>

</body>
</html>
