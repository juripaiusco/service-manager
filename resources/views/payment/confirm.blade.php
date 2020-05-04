@extends('../layouts.app-blank')

@section('content')

    <style>

        .order-fields label {
            text-transform: uppercase;
            font-size: 12px;
        }
        .order-fields > div {
            border-left: 1px dashed #888;
        }
        .order-fields > div:first-child {
            border-left: 0;
        }

    </style>

    <div class="container">

        <br>

        <h1 class="text-center">Il tuo rinnovo è confermato.</h1>

        <div class="text-center">
            Rinnovo relativo al servizio <strong>{{ $customer_service->name }}</strong>  di <strong>{{ $customer_service->reference }}</strong>
        </div>

        <br><br><br>

        <h2 class="text-center" style="font-size: 3.2em;">Grazie.</h2>

    </div>

@endsection
