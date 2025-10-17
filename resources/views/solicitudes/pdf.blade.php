<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Minuta Solicitud {{ $solicitud->code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .pdf-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .pdf-header img {
            width: 100%;
            max-height: 110px;
            object-fit: contain;
        }
        h1 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .section {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
            color: #555;
        }
        .value {
            display: inline-block;
        }
        .box {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="pdf-header">
        <img src="{{ public_path('img/CINTILLO.png') }}" alt="Ministerio del Poder Popular para el Transporte">
    </div>

    <h1>Minuta de Solicitud</h1>

    <div class="box">
        <div class="section">
            <span class="label">Código:</span>
            <span class="value">{{ $solicitud->code }}</span>
        </div>
        <div class="section">
            <span class="label">Producto:</span>
            <span class="value">{{ $solicitud->product->name ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Cantidad:</span>
            <span class="value">{{ $solicitud->quantity }}</span>
        </div>
        <div class="section">
            <span class="label">Fecha:</span>
            <span class="value">{{ $solicitud->created_at?->format('d/m/Y H:i') }}</span>
        </div>
        <div class="section">
            <span class="label">Origen:</span>
            <span class="value">{{ $solicitud->fromGerencia->name ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Destino:</span>
            <span class="value">{{ $solicitud->toGerencia->name ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Estado:</span>
            <span class="value">{{ ucfirst($solicitud->status) }}</span>
        </div>
        <div class="section">
            <span class="label">Descripción:</span>
            <span class="value">{{ $solicitud->description ?? '-' }}</span>
        </div>
        @php
            $ultimoLog = $solicitud->statusLogs->last();
        @endphp
        <div class="section">
            <span class="label">Justificación:</span>
            <span class="value">{{ $ultimoLog?->justification ?? 'Sin justificación' }}</span>
        </div>
        <div class="section">
            <span class="label">Responsable:</span>
            <span class="value">{{ $ultimoLog?->user->name ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="footer">
        Generado automáticamente | Sistema de Gestión de Solicitudes
    </div>

</body>
</html>
