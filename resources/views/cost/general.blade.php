@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

<!--    <a href="{{ route('cost.list') }}"
       class="btn btn-primary">Aggiorna</a>

    <br /><br />-->

    <table class="table table-hover table-striped table-sm">

        <thead>
            <tr>
                <th></th>

                @for($y = $y_start; $y <= $y_end; $y++)

                    <th class="text-right">{{ $y }}</th>

                @endfor

            </tr>
        </thead>

        <tbody>

        @foreach($costs as $k => $cost)

            <tr>

                <td>
                    <a href="{{ route('cost.detail', ['categoria' => $k]) }}">{{ $k }}</a>
                </td>

                @for($y = $y_start; $y <= $y_end; $y++)

                    <td class="text-right">
                        @if(isset($cost[$y]))
                        &euro; {{ number_format($cost[$y]->importo_netto, 2, ',', '.') }}
                        @endif
                    </td>

                @endfor

            </tr>

        @endforeach

        </tbody>

    </table>

@endsection
