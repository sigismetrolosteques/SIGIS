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
            padding-bottom: 150px;
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
            color: #D62828;
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
            border: 1px solid #F9FAFB;
            padding: 15px;
            border-radius: 6px;
            background: #FFFFFF;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .pdf-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            text-align: center;
        }
        .pdf-footer img {
            width: 1190px;
            height: auto;
            max-width: 100%;
            object-fit: contain;
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
        @php
            $statusLabel = \Illuminate\Support\Str::of($solicitud->status)->replace('_', ' ')->title();
        @endphp
        <div class="section">
            <span class="label">Estado:</span>
            <span class="value">{{ $statusLabel }}</span>
        </div>
        <div class="section">
            <span class="label">Aprobado:</span>
            <span class="value">{{ $solicitud->approved_quantity }} / {{ $solicitud->quantity }}</span>
        </div>
        @if($solicitud->return_date)
        <div class="section">
            <span class="label">Devolución:</span>
            <span class="value">{{ \Carbon\Carbon::parse($solicitud->return_date)->format('d/m/Y') }}</span>
        </div>
        @endif
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

    <div class="pdf-footer">
        <img src="{{ public_path('img/CINTILLO MANTENIMIENTO INFERIOR RECORTADO.png') }}" alt="Cintillo de mantenimiento">
    </div>

</body>
</html>
