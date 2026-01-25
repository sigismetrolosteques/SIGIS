<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Producto {{ $product->code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #111827;
            margin: 0;
            padding: 20px;
            padding-bottom: 110px;
        }
        .pdf-header img {
            width: 100%;
            max-height: 110px;
            object-fit: contain;
        }
        h1 {
            text-align: center;
            font-size: 20px;
            margin: 12px 0 18px;
            color: #AB2423;
        }
        .section {
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
            color: #111827;
        }
        .value {
            display: inline-block;
        }
        .box {
            border: 1px solid #F9FAFB;
            padding: 14px;
            border-radius: 6px;
            background: #FFFFFF;
        }
        .product-image {
            margin-top: 12px;
            text-align: center;
        }
        .product-image img {
            max-height: 180px;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #F9FAFB;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #F9FAFB;
        }
        .footer {
            margin-top: 26px;
            text-align: center;
            font-size: 11px;
            color: #6B7280;
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

    <h1>Ficha de Producto</h1>

    <div class="box">
        @php
            $tipoLabel = match ($product->tipo_material) {
                'equipos' => 'Equipos',
                'herramientas' => 'Herramientas',
                'consumibles' => 'Consumibles',
                default => 'Sin clasificación',
            };
        @endphp
        <div class="section">
            <span class="label">Código:</span>
            <span class="value">{{ $product->code }}</span>
        </div>
        <div class="section">
            <span class="label">Nombre:</span>
            <span class="value">{{ $product->name }}</span>
        </div>
        <div class="section">
            <span class="label">Sub-Área:</span>
            <span class="value">{{ $product->sub_area ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Tipo de material:</span>
            <span class="value">{{ $tipoLabel }}</span>
        </div>
        <div class="section">
            <span class="label">Modelo:</span>
            <span class="value">{{ $product->modelo ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Dimensiones:</span>
            <span class="value">{{ $product->dimensiones ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Marcas sugeridas:</span>
            <span class="value">{{ $product->marcas_sugeridas ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Especificaciones:</span>
            <span class="value">{{ $product->especificaciones_tecnicas ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Descripción:</span>
            <span class="value">{{ $product->description ?? '-' }}</span>
        </div>
        <div class="section">
            <span class="label">Fecha de creación:</span>
            <span class="value">{{ $product->created_at?->format('d/m/Y H:i') }}</span>
        </div>

        @if($product->image)
        <div class="product-image">
            <img src="{{ public_path('img/' . $product->image) }}" alt="Imagen del producto">
        </div>
        @endif
    </div>

    <h2 style="margin-top: 18px; color: #AB2423;">Inventario asociado</h2>
    <div class="box">
        <div class="section">
            <span class="label">Cantidad total:</span>
            <span class="value">{{ $totals['quantity'] }}</span>
        </div>
        <div class="section">
            <span class="label">Reservado:</span>
            <span class="value">{{ $totals['reserved'] }}</span>
        </div>
        <div class="section">
            <span class="label">Disponible:</span>
            <span class="value">{{ $totals['available'] }}</span>
        </div>

        @if($product->inventories->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Ubicación</th>
                    <th>Cantidad</th>
                    <th>Reservado</th>
                    <th>Disponible</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->inventories as $inventory)
                <tr>
                    <td>{{ $inventory->location ?? 'Sin ubicación' }}</td>
                    <td>{{ $inventory->quantity }}</td>
                    <td>{{ $inventory->reserved_quantity }}</td>
                    <td>{{ max(0, $inventory->quantity - $inventory->reserved_quantity) }}</td>
                    <td>{{ $inventory->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>Sin registros de inventario asociados.</p>
        @endif
    </div>

    <div class="footer">
        Generado automáticamente | Ficha de Producto
    </div>

    <div class="pdf-footer">
        <img src="{{ public_path('img/CINTILLO MANTENIMIENTO INFERIOR RECORTADO.png') }}" alt="Cintillo de mantenimiento">
    </div>
</body>
</html>
