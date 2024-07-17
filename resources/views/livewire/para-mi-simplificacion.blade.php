<div>
    <div>
        
    </div>
    @if($paginatedResults->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Nro Legajo</th>
                    <th>Nro Liqui</th>

                </tr>
            </thead>
            <tbody>
                @foreach($paginatedResults as $result)
                    <tr>
                        <td>{{ $result->nro_legaj }}</td>
                        <td>{{ $result->nro_liqui }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>{{__('No results found.')}}</p>
    @endif
</div>

