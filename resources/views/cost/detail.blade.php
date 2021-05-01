@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <a href="{{ route('cost.list') }}"
       class="btn btn-primary">Indietro</a>

    <br /><br />

    <table class="table table-hover table-striped table-sm" style="font-size: .8em;">

        <thead>
        <tr>
            <th></th>

            @foreach($months as $month)
                <th class="text-right">{{ substr($month, 0, 3) }}</th>
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
                                &euro; {{ number_format($cost[$y . $m]->importo_netto, 2, ',', '.') }}

                                @php
                                $importo_netto_tot += $cost[$y . $m]->importo_netto
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

@endsection
