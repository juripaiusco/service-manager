@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <a href="{{ route('cost.list') }}"
       class="btn btn-primary">Indietro</a>

    <br /><br />

    <h2>Confronto anno precedente</h2>

    <p>
        Nello spesso periodo
    </p>

    <h2>Confronto negli anni</h2>
    <table class="table table-hover table-striped table-sm table-bordered" style="font-size: .8em;">

        <thead>
        <tr>
            <th></th>

            @foreach($months as $month)
                <th class="text-center">{{ substr($month, 0, 3) }}</th>
            @endforeach

            <th></th>

        </tr>
        </thead>

        <tbody>

        @foreach($costs as $k => $costs_array)

            <tr>

                <td>
                    {{ $k }}
                </td>

                @foreach($costs_array as $y => $cost)

                    @php
                    $importo_netto_tot = 0;
                    @endphp

                    @for($m = 1; $m <= 12; $m++)

                        <td class="text-right">
                            @php
                            if ($m < 10) $m = '0' . $m;
                            @endphp

                            @if(isset($cost[$y . $m]))
                                &euro; {{ number_format($cost[$y . $m], 2, ',', '.') }}

                                @php
                                $importo_netto_tot += $cost[$y . $m]
                                @endphp
                            @endif
                        </td>

                    @endfor

                @endforeach

                <td class="text-right">
                    <strong>
                        &euro; {{ number_format($importo_netto_tot, 2, ',', '.') }}
                    </strong>
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

    <br>
    <h2>Fatture</h2>

    <table class="table table-hover table-striped table-sm" style="font-size: .8em;">

        <thead>
            <tr>
                <th></th>
                <th>Numero</th>
                <th>Nome</th>
                <th>Tipo Doc.</th>
                <th class="text-center">Data</th>
                <th class="text-right">Importo</th>
                <th class="text-right">IVA</th>
                <th class="text-right">Totale</th>
            </tr>
        </thead>

        <tbody>

        @php
            $rem_y = 0;
        @endphp

        @foreach($fatture as $fattura)

            <tr>
                <td>
                    @if($rem_y != $fattura->anno)
                    {{ $fattura->anno }}
                    @endif
                </td>
                <td>{{ $fattura->numero }}</td>
                <td>{{ $fattura->nome }}</td>
                <td>{{ $fattura->tipo_doc }}</td>
                <td class="text-center">
                    {{ date('d/m/Y', strtotime($fattura->data)) }}
                </td>
                <td class="text-right">
                    &euro; {{ number_format($fattura->importo_netto, 2, ',', '.') }}
                </td>
                <td class="text-right">
                    &euro; {{ number_format($fattura->importo_iva, 2, ',', '.') }}
                </td>
                <td class="text-right">
                    &euro; {{ number_format($fattura->importo_totale, 2, ',', '.') }}
                </td>
            </tr>

            @php
            $rem_y = $fattura->anno;
            @endphp

        @endforeach
        </tbody>
    </table>

@endsection
