@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <!--<a href="{{ route('cost.list') }}"
       class="btn btn-primary">Aggiorna</a>

    <br /><br />-->

    <style>
        .card-body {
            padding: 0;
        }
        .table {
            margin-bottom: 0;
        }
        .card .table-hover tbody tr:hover {
            background-color: lightskyblue;
            color: #fff;
        }
        tbody.date_selected {
            border: 3px solid deepskyblue !important;
        }
        .table-hover tbody.date_selected tr:hover {
            background-color: deepskyblue;
            color: #fff;
        }
    </style>

    <h3 class="text-center">
        Costi rispetto lo scorso anno
    </h3>

    <h1 class="text-center
        @if($array_comparison_by_year['comparison'] < 0) text-success @else text-danger @endif
        "
        style="margin: 15px 0 20px 0;">

        @if($array_comparison_by_year['comparison'] < 0)
            -
        @else
            +
        @endif
        &euro;&nbsp;{{ number_format(abs($array_comparison_by_year['comparison']), 2, ',', '.') }}
    </h1>

    <table class="table table-hover table-striped table-sm table-bordered">

        <thead>
            <tr>
                <th></th>

                @foreach($array_months as $k => $month)
                    <th class="text-center
                                @if(($k + 1) == date('n') && !$vs_select)
                                border-primary bg-primary text-white
                                @endif
                                @if($k + 1 == $vs_select)
                                table-success
                                @endif">
                        <a href="{{ route('cost.list', ['vs' => $k + 1]) }}" class="
                                @if(($k + 1) == date('n') && !$vs_select)
                                text-white
                                @endif">
                            {{ substr($month, 0, 3) }}
                        </a>
                    </th>
                @endforeach

                <th></th>

            </tr>
        </thead>

        <tbody>

        @foreach($array_costs_by_months as $y => $costs_array)

            <tr>

                <th class="text-center">
                    {{ $y }}
                </th>

                @php
                    $importo_netto_tot = 0;
                @endphp

                @for($m = 1; $m <= 12; $m++)

                    <td class="text-right
                                @if($m == date('n') && !$vs_select)
                                table-primary
                                @endif
                                @if($m == $vs_select)
                                table-success
                                @endif">
                        @php
                            if ($m < 10) $m = '0' . $m;
                        @endphp

                        @if(isset($costs_array[$y . $m]))

                            <a href="{{ route('cost.list', ['ym' => $y . $m]) }}#{{ $y . $m }}">
                                &euro;&nbsp;{{ number_format($costs_array[$y . $m], 2, ',', '.') }}
                            </a>

                            @php
                                $importo_netto_tot += $costs_array[$y . $m]
                            @endphp
                        @endif
                    </td>

                @endfor

                <td class="text-right">
                    <strong>
                        &euro;&nbsp;{{ number_format($importo_netto_tot, 2, ',', '.') }}
                    </strong>
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

    @if(isset($vs_select))

        <br>

        <h2 class="text-center">Confronto {{ $array_months[$vs_select - 1] }}</h2>
        <div class="text-center">
            <a href="{{ route('cost.list') }}" class="btn btn-secondary">Chiudi</a>
        </div>

        <br>

        <div class="row">

            @foreach($array_fatture_group as $y => $array_fatture_by_month)

                <div class="col-lg-6 col-sm-6">
                    <div class="card">

                        <div class="card-header">{{ $y }} - {{ $array_months[$vs_select - 1] }}</div>
                        <div class="card-body">

                            <table class="table table-hover table-striped table-sm">

                                <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th width="20%" class="text-right">Importo</th>
                                    <th width="20%" class="text-right">Iva</th>
                                    <th width="20%" class="text-right">Totale</th>
                                </tr>
                                </thead>

                                @foreach($array_fatture_by_month as $ym => $array_fatture_)

                                    @if($ym == $y . sprintf('%02d', $vs_select))

                                            <tbody class="@if($ym == $ym_select) date_selected @endif">

                                            @foreach($array_fatture_ as $k => $fattura)

                                                <tr @if($k <= 0) id="{{ $ym }}" @endif>
                                                    <td>
                                                        {{ \Illuminate\Support\Str::limit($fattura['nome'], 25) }}
                                                    </td>
                                                    <td class="text-right">
                                                        @if($fattura['importo_netto'] > 0)
                                                        &euro;&nbsp;{{ number_format($fattura['importo_netto'], 2, ',', '.') }}
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        @if($fattura['importo_iva'] > 0)
                                                        &euro;&nbsp;{{ number_format($fattura['importo_iva'], 2, ',', '.') }}
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        @if($fattura['importo_totale'] > 0)
                                                        &euro;&nbsp;{{ number_format($fattura['importo_totale'], 2, ',', '.') }}
                                                        @endif
                                                    </td>
                                                </tr>

                                            @endforeach

                                            </tbody>

                                    @endif

                                @endforeach

                            </table>

                        </div>

                    </div>
                </div>

            @endforeach

        </div>

    @endif

    <br>

    <div class="row">
        <div class="col-lg-6 col-sm-6">

            <div class="card border-success">
                <div class="card-header text-success border-success">Spese nella norma</div>
                <div class="card-body">

                    <table class="table">
                        <tbody>
                        @foreach($array_comparison_by_category as $cat => $comparison)

                            @if($comparison['comparison'] <= 0)

                                <tr>
                                    <td>
                                        <a href="{{ route('cost.detail', ['categoria' => $cat]) }}">
                                            {{ $cat }}
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        - &euro;&nbsp;{{ number_format(abs($comparison['comparison']), 2, ',', '.') }}
                                    </td>
                                </tr>

                            @endif

                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
        <div class="col-lg-6 col-sm-6">

            <div class="card border-danger">
                <div class="card-header text-danger border-danger">Spese fuori controllo</div>
                <div class="card-body">

                    <table class="table">
                        <tbody>
                        @foreach($array_comparison_by_category as $cat => $comparison)

                            @if($comparison['comparison'] > 0)

                                <tr>
                                    <td>
                                        <a href="{{ route('cost.detail', ['categoria' => $cat]) }}">
                                            {{ $cat }}
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        + &euro;&nbsp;{{ number_format($comparison['comparison'], 2, ',', '.') }}
                                    </td>
                                </tr>

                            @endif

                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>

    <br>

    <h2 class="text-center">Fatture</h2>

    @foreach($array_fatture as $y => $array_fatture_by_month)

        <div class="card">
            <div class="card-header">
                {{ $y }} {{--{{ $array_months[intval(substr($ym, 4, 2)) - 1] }}--}}
            </div>
            <div class="card-body">

                <table class="table table-hover table-striped table-sm">

                    <thead>
                    <tr>
                        <th width="20%">Numero</th>
                        <th>Nome</th>
                        <th width="10%">Tipo Doc.</th>
                        <th width="10%" class="text-center">Data</th>
                        <th width="10%" class="text-right">Importo</th>
                        <th width="10%" class="text-right">IVA</th>
                        <th width="10%" class="text-right">Totale</th>
                    </tr>
                    </thead>

                    @foreach($array_fatture_by_month as $ym => $array_fatture_obj)

                        <tbody class="@if($ym == $ym_select) date_selected @endif">

                        @foreach($array_fatture_obj as $k => $fattura)

                            <tr @if($k <= 0) id="{{ $ym }}" @endif>
                                <td>{{ $fattura->numero }}</td>
                                <td>{{ $fattura->nome }}</td>
                                <td>{{ $fattura->tipo_doc }}</td>
                                <td class="text-center">
                                    {{ date('d/m/Y', strtotime($fattura->data)) }}
                                </td>
                                <td class="text-right">
                                    &euro;&nbsp;{{ number_format($fattura->importo_netto, 2, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    &euro;&nbsp;{{ number_format($fattura->importo_iva, 2, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    &euro;&nbsp;{{ number_format($fattura->importo_totale, 2, ',', '.') }}
                                </td>
                            </tr>

                        @endforeach

                        </tbody>

                    @endforeach

                </table>

            </div>
        </div>

        <br>

    @endforeach

@endsection
