@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <style>
        .table {
            margin-bottom: 0;
        }
    </style>

    <a href="{{ route('cost.list') }}"
       class="btn btn-primary">Indietro</a>

    <br />

    <h1 class="text-center">
        {{ $category }}
    </h1>

    <h3 class="text-center">
        Ad oggi rispetto lo scorso anno
    </h3>

    <h1 class="text-center
        @if($array_comparison[$category]['comparison'] < 0) text-success @else text-danger @endif
        "
        style="margin: 15px 0 20px 0;">
        @if($array_comparison[$category]['comparison'] < 0)
            -
        @else
            +
        @endif
        &euro; {{ number_format(abs($array_comparison[$category]['comparison']), 2, ',', '.') }}
    </h1>

    <table class="table table-hover table-striped table-sm table-bordered" style="font-size: .8em;">

        <thead>
        <tr>
            <th></th>

            @foreach($array_months as $month)
                <th class="text-center">{{ substr($month, 0, 3) }}</th>
            @endforeach

            <th></th>

        </tr>
        </thead>

        <tbody>

        @foreach($array_costs_by_months as $y => $cost)

            <tr>

                <td>
                    {{ $y }}
                </td>

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
