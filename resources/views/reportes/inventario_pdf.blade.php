<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
		@page {
			margin: 0cm; /* Margen general */
			margin-bottom: 220px; /* Espacio reservado para el cintillo inferior */
		}
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding-top: 20px;
			padding-left: 70px;
			padding-right: 70px;
            padding-bottom: 10px;
        }
        .pdf-header {
            text-align: center;
            margin-bottom: 18px;
        }
        .pdf-header img {
            width: 90%;
            max-height: 100px;
            object-fit: contain;
        }
        h1 {
            text-align: center;
            color: #AB2423;
            margin-bottom: 12px;
        }
        .meta {
            margin-bottom: 12px;
            font-size: 11px;
        }
        .summary {
            margin-bottom: 12px;
            padding: 10px;
            background: #F9FAFB;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #E5E7EB;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #F9FAFB;
        }
         .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
        }
		.pdf-footer {
			position: fixed;
			left: 0px;
			right: 0px;
			bottom: -140.5px;
			height: 150px;
			text-align: center;
		}
    
		.pdf-footer img {
			width: 100.3%;
			height: 250px;
			object-fit: contain;
		}
    </style>
</head>
<body>
    <div class="pdf-header">
        <img src="{{ public_path('img/CINTILLO.png') }}" alt="Ministerio del Poder Popular para el Transporte">
    </div>

    <h1>Reporte de Inventario</h1>

    <div class="meta">
        Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="summary">
        <strong>Resumen</strong><br>
        Total registros: {{ $totals['total'] }} |
        Cantidad total: {{ $totals['quantity_total'] }} |
        Reservado: {{ $totals['reserved_total'] }} |
        Disponible: {{ $totals['available_total'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Reservado</th>
                <th>Disponible</th>
                <th>Ubicación</th>
                <th>Fecha creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $inv)
            <tr>
                <td>{{ $inv->product?->code ?? 'N/D' }}</td>
                <td>{{ $inv->product?->name ?? 'Producto eliminado' }}</td>
                <td>{{ $inv->product?->tipo_material ?? '-' }}</td>
                <td>{{ $inv->quantity }}</td>
                <td>{{ $inv->reserved_quantity }}</td>
                <td>{{ max(0, $inv->quantity - $inv->reserved_quantity) }}</td>
                <td>{{ $inv->location ?? 'Sin ubicación' }}</td>
                <td>{{ $inv->created_at?->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Reporte generado automáticamente
    </div>

    <div class="pdf-footer">
        <img src="{{ public_path('img/CINTILLO MANTENIMIENTO INFERIOR RECORTADO.png') }}" alt="Cintillo de mantenimiento">
    </div>
</body>
</html>
