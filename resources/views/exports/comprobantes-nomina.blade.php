<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 200px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .description { text-align: justify; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 8px; background: #f3f4f6; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .amount { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo" style="height: 120px;">
        <div class="title">
            Orden de Pago de Descuento
            <h2>Liquidacion: {{$liquidacion->desc_liqui}} Nro: {{ $liquidacion->nro_liqui }} </h2>
        </div>
    </div>

    <div class="description">
        Visto las novedades informadas por las dependencias en el mes de noviembre del corriente,
        se procedió a la liquidación de haberes arrojando la orden de pago presupuestaria y el informe
        gerencial que se adjuntan a la presente, totalizando un importe de descuentos de PESOS
        {{ NumberFormatter::create('es_AR', NumberFormatter::SPELLOUT)->format(abs($total)) }}
        ({{ NumberFormatter::create('es_AR', NumberFormatter::CURRENCY)->format($total) }})
    </div>

    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="amount">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $registro)
            <tr>
                <td>{{ $registro->descripcion_retencion }}</td>
                <td class="amount">
                    {{ NumberFormatter::create('es_AR', NumberFormatter::CURRENCY)->format($registro->importe) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
