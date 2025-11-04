<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reporte Solicitudes</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size:12px; }
    .pdf-header { text-align:center; margin-bottom:18px; }
    .pdf-header img { width:100%; max-height:110px; object-fit:contain; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    .title { font-size:18px; font-weight:700; }
    .summary { display:flex; gap:12px; margin-bottom:12px; }
    .card { background:#f7f7f7; padding:8px 12px; border-radius:6px; }
    table { width:100%; border-collapse: collapse; margin-top:8px; }
    th, td { padding:8px; border:1px solid #ddd; text-align:left; font-size:11px; }
    th { background:#f0f0f0; }
    .mono { font-family: monospace; }
  </style>
</head>
<body>
  <div class="pdf-header">
    <img src="{{ public_path('img/CINTILLO.png') }}" alt="Ministerio del Poder Popular para el Transporte">
  </div>

  <div class="header">
    <div>
      <div class="title">Reporte de Solicitudes</div>
      <div>Generado: {{ now()->format('Y-m-d H:i') }}</div>
    </div>
    <div>
      <!-- mostrar filtros -->
      @if(!empty($filters['from']) || !empty($filters['to']))
        <div>Periodo: {{ $filters['from'] ?? '-' }} → {{ $filters['to'] ?? '-' }}</div>
      @endif
      @if(!empty($filters['status'])) <div>Estado: {{ $filters['status'] }}</div> @endif
    </div>
  </div>

  <div class="summary">
    <div class="card"><strong>Total:</strong> {{ $totales['total'] ?? 0 }}</div>
    <div class="card"><strong>En proceso:</strong> {{ $totales['en_proceso'] ?? 0 }}</div>
    <div class="card"><strong>Aprobado:</strong> {{ $totales['aprobado'] ?? 0 }}</div>
    <div class="card"><strong>Rechazado:</strong> {{ $totales['rechazado'] ?? 0 }}</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Código</th><th>Producto</th><th>Origen</th><th>Destino</th><th>Cantidad</th><th>Estado</th><th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      @foreach($solicitudes as $s)
        <tr>
          <td class="mono">{{ $s->code }}</td>
          <td>{{ optional($s->product)->name }}</td>
          <td>{{ optional($s->fromGerencia)->name }}</td>
          <td>{{ optional($s->toGerencia)->name }}</td>
          <td>{{ $s->quantity }}</td>
          <td>{{ \Illuminate\Support\Str::of($s->status)->replace('_', ' ')->title() }}</td>
          <td>{{ optional($s->created_at)->toDateTimeString() }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
