<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Entrega {{ $solicitud->code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 20px 24px 120px;
        }
        .pdf-header {
            text-align: center;
            margin-bottom: 8px;
        }
        .pdf-header img {
            width: 100%;
            max-height: 110px;
            object-fit: contain;
        }
        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin: 6px 0 12px;
            letter-spacing: 0.6px;
            color: #111827;
        }
        .text {
            line-height: 1.45;
            text-align: justify;
            margin-bottom: 12px;
        }
        .line {
            display: inline-block;
            border-bottom: 1px solid #111827;
            padding: 0 4px 2px;
            min-width: 60px;
            text-align: center;
        }
        .line-sm { min-width: 60px; }
        .line-md { min-width: 120px; }
        .line-lg { min-width: 180px; }
        .line-xl { min-width: 260px; }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11px;
        }
        .items th,
        .items td {
            border: 1px solid #111827;
            padding: 6px 6px;
        }
        .items th {
            text-align: center;
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        .signatures {
            margin-top: 26px;
            width: 100%;
        }
        .signatures table {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            vertical-align: top;
            padding-right: 18px;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 6px;
        }
        .signature-row {
            margin-bottom: 6px;
            font-size: 11px;
        }
        .signature-label {
            display: inline-block;
            width: 115px;
        }
        .signature-line {
            display: inline-block;
            border-bottom: 1px solid #111827;
            min-width: 180px;
            height: 14px;
            vertical-align: bottom;
            padding: 0 4px;
        }
        .footer {
            margin-top: 18px;
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
            width: 100%;
            max-height: 90px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="pdf-header">
        <img src="{{ public_path('img/CINTILLO.png') }}" alt="Ministerio del Poder Popular para el Transporte">
    </div>

    <div class="title">NOTA DE ENTREGA</div>

    @php
        $fecha = \Carbon\Carbon::now();
        $mes = ucfirst($fecha->locale('es')->translatedFormat('F'));
        $ultimoLog = $solicitud->statusLogs->last();
        $entregaNombre = $ultimoLog?->user?->name ?? '';
        $entregaCargo = $ultimoLog?->user?->gerencia?->name ?? '';
        $recibeNombre = $solicitud->creator?->name ?? '';
        $recibeCargo = $solicitud->fromGerencia?->name ?? '';
        $uso = $solicitud->description ?? '';
        $cantidadAprobada = $solicitud->approved_quantity > 0 ? $solicitud->approved_quantity : $solicitud->quantity;
    @endphp

    <p class="text">
        En Los Teques, a los
        <span class="line line-sm">{{ $fecha->format('d') }}</span>
        dias del mes
        <span class="line line-md">{{ $mes }}</span>
        del anio
        <span class="line line-sm">{{ $fecha->format('Y') }}</span>,
        quien suscribe, ciudadano:
        <span class="line line-lg">{{ $entregaNombre }}</span>
        en su caracter de:
        <span class="line line-md">{{ $entregaCargo }}</span>,
        hace entrega al ciudadano (a):
        <span class="line line-lg">{{ $recibeNombre }}</span>
        en su caracter de:
        <span class="line line-md">{{ $recibeCargo }}</span>,
        de los materiales e insumos que a continuacion se mencionan. Los mismos seran utilizados para:
        <span class="line line-xl">{{ $uso }}</span>.
    </p>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 10%;">ITEM</th>
                <th>DESCRIPCION</th>
                <th style="width: 18%;">UNIDAD</th>
                <th style="width: 16%;">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            @php
                $itemCount = $solicitud->product ? 1 : 0;
                $blankRows = max(0, 8 - $itemCount);
            @endphp
            @if($solicitud->product)
            <tr>
                <td class="center">1</td>
                <td>{{ $solicitud->product->name }}</td>
                <td class="center">UND</td>
                <td class="center">{{ $cantidadAprobada }}</td>
            </tr>
            @endif
            @for($i = 0; $i < $blankRows; $i++)
            <tr>
                <td class="center">&nbsp;</td>
                <td>&nbsp;</td>
                <td class="center">&nbsp;</td>
                <td class="center">&nbsp;</td>
            </tr>
            @endfor
        </tbody>
    </table>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-title">ENTREGA:</div>
                    <div class="signature-row">
                        <span class="signature-label">Nombres y Apellidos:</span>
                        <span class="signature-line">{{ $entregaNombre }}</span>
                    </div>
                    <div class="signature-row">
                        <span class="signature-label">Carnet o Cedula:</span>
                        <span class="signature-line">&nbsp;</span>
                    </div>
                    <div class="signature-row">
                        <span class="signature-label">Firma:</span>
                        <span class="signature-line">&nbsp;</span>
                    </div>
                </td>
                <td>
                    <div class="signature-title">RECIBE:</div>
                    <div class="signature-row">
                        <span class="signature-label">Nombres y Apellidos:</span>
                        <span class="signature-line">{{ $recibeNombre }}</span>
                    </div>
                    <div class="signature-row">
                        <span class="signature-label">Carnet o Cedula:</span>
                        <span class="signature-line">&nbsp;</span>
                    </div>
                    <div class="signature-row">
                        <span class="signature-label">Firma:</span>
                        <span class="signature-line">&nbsp;</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generado automaticamente | Nota de Entrega
    </div>

    <div class="pdf-footer">
        <img src="{{ public_path('img/CINTILLO MANTENIMIENTO INFERIOR RECORTADO.png') }}" alt="Cintillo de mantenimiento">
    </div>
</body>
</html>
