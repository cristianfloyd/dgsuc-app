<table border="1">
    <thead>
        <tr>
            <th colspan="8">Dependencia {{ $unidad }}</th>
        </tr>
        <tr>
            <th>Programa</th>
            <th>Sueldo</th>
            <th>Estipendio</th>
            <th>Productividad</th>
            <th>Méd. Resid.</th>
            <th>Sal. Fam.</th>
            <th>Hs. Extras</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                <td>{{ $row->codn_progr }}</td>
                <td>{{ $row->remunerativo }}</td>
                <td>{{ $row->no_remunerativo }}</td>
                <td>{{ $row->productividad ?? 'N/A' }}</td>
                <td>{{ $row->med_resid ?? 'N/A' }}</td>
                <td>{{ $row->sal_fam ?? 'N/A' }}</td>
                <td>{{ $row->hs_extras }}</td>
                <td>{{ $row->total }}</td>
            </tr>
        @endforeach
        <tr>
            <td>Total</td>
            <td>{{ $data->sum('remunerativo') }}</td>
            <td>{{ $data->sum('no_remunerativo') }}</td>
            <td>{{ $data->sum('productividad') ?? 'N/A' }}</td>
            <td>{{ $data->sum('med_resid') ?? 'N/A' }}</td>
            <td>{{ $data->sum('sal_fam') ?? 'N/A' }}</td>
            <td>{{ $data->sum('hs_extras') }}</td>
            <td>{{ $data->sum('total') }}</td>
        </tr>
    </tbody>
</table>
