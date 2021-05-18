@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <!--<a href="{{ route('income.list') }}"
       class="btn btn-primary">Aggiorna</a>

    <br /><br />-->

    <style>
        .table {
            margin-bottom: 0;
        }
    </style>

    <h3 class="text-center">
        Entrate rispetto lo scorso anno
    </h3>

    <h1 class="text-center
        @if($array_comparison_by_year['comparison'] > 0) text-success @else text-danger @endif
        "
        style="margin: 15px 0 20px 0;">

        @if($array_comparison_by_year['comparison'] < 0)
            -
        @else
            +
        @endif
        &euro; {{ number_format(abs($array_comparison_by_year['comparison']), 2, ',', '.') }}
    </h1>

    <table class="table table-hover table-striped table-sm table-bordered" style="font-size: .8em;">

        <thead>
            <tr>
                <th></th>

                @foreach($array_months as $k => $month)
                    <th class="text-center @if(($k + 1) == 5) border-primary bg-primary text-white @endif">
                        {{ substr($month, 0, 3) }}
                    </th>
                @endforeach

                <th></th>

            </tr>
        </thead>

        <tbody>

        @foreach($array_income_by_months as $y => $income_array)

            <tr>

                <th class="text-center">
                    {{ $y }}
                </th>

                @php
                    $importo_netto_tot = 0;
                @endphp

                @for($m = 1; $m <= 12; $m++)

                    <td class="text-right @if($m == 5) table-primary @endif">
                        @php
                            if ($m < 10) $m = '0' . $m;
                        @endphp

                        @if(isset($income_array[$y . $m]))
                        &euro; {{ number_format($income_array[$y . $m], 2, ',', '.') }}

                        @php
                            $importo_netto_tot += $income_array[$y . $m]
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

{{--    <div class="row">--}}
{{--        <div class="col-lg-6">--}}

{{--            <div class="card border-success">--}}
{{--                <div class="card-header text-success border-success">Spese nella norma</div>--}}
{{--                <div class="card-body">--}}

{{--                    <table class="table">--}}
{{--                        <tbody>--}}
{{--                        @foreach($array_comparison_by_category as $cat => $comparison)--}}

{{--                            @if($comparison['comparison'] <= 0)--}}

{{--                                <tr>--}}
{{--                                    <td>--}}
{{--                                        <a href="{{ route('income.detail', ['categoria' => $cat]) }}">--}}
{{--                                            {{ $cat }}--}}
{{--                                        </a>--}}
{{--                                    </td>--}}
{{--                                    <td class="text-right">--}}
{{--                                        - &euro; {{ number_format(abs($comparison['comparison']), 2, ',', '.') }}--}}
{{--                                    </td>--}}
{{--                                </tr>--}}

{{--                            @endif--}}

{{--                        @endforeach--}}
{{--                        </tbody>--}}
{{--                    </table>--}}

{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}
{{--        <div class="col-lg-6">--}}

{{--            <div class="card border-danger">--}}
{{--                <div class="card-header text-danger border-danger">Spese fuori controllo</div>--}}
{{--                <div class="card-body">--}}

{{--                    <table class="table">--}}
{{--                        <tbody>--}}
{{--                        @foreach($array_comparison_by_category as $cat => $comparison)--}}

{{--                            @if($comparison['comparison'] > 0)--}}

{{--                                <tr>--}}
{{--                                    <td>--}}
{{--                                        <a href="{{ route('income.detail', ['categoria' => $cat]) }}">--}}
{{--                                            {{ $cat }}--}}
{{--                                        </a>--}}
{{--                                    </td>--}}
{{--                                    <td class="text-right">--}}
{{--                                        + &euro; {{ number_format($comparison['comparison'], 2, ',', '.') }}--}}
{{--                                    </td>--}}
{{--                                </tr>--}}

{{--                            @endif--}}

{{--                        @endforeach--}}
{{--                        </tbody>--}}
{{--                    </table>--}}

{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}
{{--    </div>--}}

@endsection
