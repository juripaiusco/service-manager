@extends('../layouts.app-blank')

@section('content')

    <style>

        .text-gray {
            color: #888;
        }
        .table .text-small,
        .privacy-content {
            font-size: 12px;
            color: #888;
        }
        .privacy-content {
            border: 1px dashed #888;
            border-radius: 6px;
            padding: 15px;
        }
        .table-total-container {
            background-color: #f5f5f5;
            border: 1px solid #28a745;
            color: #28a745;
        }
        .table-total-container .text-small {
            color: #28a745;
            font-weight: bold;
        }
        .table .text-big {
            font-size: 1.4em;
        }
        .table thead {
            font-weight: bold;
        }
        .table tbody .text-right {
            white-space: nowrap;
            text-align: right;
        }
        .date-exp-container {
            border: 4px dashed #f00;
            padding: 15px 0 15px 0;
            text-align: center;
            border-radius: 8px;
            margin: 30px 0 0 0;
        }
        .date-exp {
            font-size: 3em;
            font-weight: bold;
            white-space: nowrap;
        }
        .date-exp-msg {
            font-size: .75em;
            white-space: nowrap;
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            font-size: 0.9em;
            margin-bottom: 5px;
            margin-left: 10px;
            color: gray;
        }
        .form-group {
            margin-bottom: 15px;
        }

    </style>

    <script type="text/javascript">

        window.onload = function () {

            function paymentCheck(Obj) {

                var color = 'success';

                $('.alert.border-' + color)
                    .removeClass('border-' + color)
                    .removeClass('text-' + color);

                Obj.closest('.alert')
                    .addClass('border-' + color)
                    .addClass('text-' + color);

            }

            var ObjPayment = $('[name="payment"]');

            ObjPayment.each(function () {

                var Obj = $(this);

                if(Obj.prop('checked')) {
                    paymentCheck(Obj);
                }

            });

            ObjPayment.on('change', function () {

                paymentCheck($(this));

            });

            $('#privacy').on('change', function () {

                if($(this).prop('checked')) {

                    $('#btn-submit').removeClass('disabled');

                } else {

                    $('#btn-submit').addClass('disabled');
                }

            });

            $('#btn-submit').on('click', function () {

                if($(this).hasClass('disabled')) {

                    $('#privacyError').modal('show');

                    return false;

                }

            });

        };

    </script>

    <div class="container">

        <br>

        <h1 class="text-center">Conferma il tuo rinnovo.</h1>

        <div class="text-center">
            Il rinnovo è relativo al servizio <strong>{{ $customer_service->name }}</strong>  di <strong>{{ $customer_service->reference }}</strong>
        </div>

        <div class="row">
            <div class="col-lg-3"></div>
            <div class="col-lg-6">

                <div class="date-exp-container">
                    <div class="date-exp">{{ date('d-m-Y', strtotime($customer_service->expiration)) }}</div>
                    <div class="date-exp-msg">(data di scadenza e disattivazione dei servizi)</div>
                </div>

            </div>
            <div class="col-lg-3"></div>
        </div>

        <br><br>

        <div class="row">
            <div class="col-lg-7">

                <div class="card @if(!$cliente) border-danger @endif">

                    <div class="card-header @if(!$cliente) border-danger text-white bg-danger @endif">
                        @if($cliente)
                            I tuoi dati
                        @else
                            Dati non trovati
                        @endif
                    </div>
                    <div class="card-body @if(!$cliente) text-danger @endif">

                        @if($cliente)

                            <div class="form-group">
                                <label for="nome">Azienda</label>
                                <input type="text"
                                       class="form-control"
                                       id="nome"
                                       @if($cliente->getName())
                                       value="{{ $cliente->getName() }}"
                                       @endif
                                       readonly>
                            </div>

                            <div class="form-group">
                                <label for="indirizzo_via">Indirizzo</label>
                                <input type="text"
                                       class="form-control"
                                       id="indirizzo_via"
                                       @if($cliente->getAddressStreet())
                                       value="{{ $cliente->getAddressStreet() }}"
                                       @endif
                                       readonly>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">

                                    <div class="form-group">
                                        <label for="indirizzo_cap">CAP</label>
                                        <input type="text"
                                               class="form-control"
                                               id="indirizzo_cap"
                                               @if($cliente->getAddressPostalCode())
                                               value="{{ $cliente->getAddressPostalCode() }}"
                                               @endif
                                               readonly>
                                    </div>

                                </div>
                                <div class="col-lg-7">

                                    <div class="form-group">
                                        <label for="indirizzo_citta">Città</label>
                                        <input type="text"
                                               class="form-control"
                                               id="indirizzo_citta"
                                               @if($cliente->getAddressCity())
                                               value="{{ $cliente->getAddressCity() }}"
                                               @endif
                                               readonly>
                                    </div>

                                </div>
                                <div class="col-lg-2">

                                    <div class="form-group">
                                        <label for="indirizzo_provincia">Prov.</label>
                                        <input type="text"
                                               class="form-control"
                                               id="indirizzo_provincia"
                                               @if($cliente->getAddressProvince())
                                               value="{{ $cliente->getAddressProvince() }}"
                                               @endif
                                               readonly>
                                    </div>

                                </div>
                            </div>

                            <div class="form-group">
                                <label for="paese">Paese</label>
                                <input type="text"
                                       class="form-control"
                                       id="paese"
                                       @if($cliente->getCountry())
                                       value="{{ $cliente->getCountry() }}"
                                       @endif
                                       readonly>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">

                                    <div class="form-group">
                                        <label for="piva">P.IVA</label>
                                        <input type="text"
                                               class="form-control"
                                               id="piva"
                                               @if($cliente->getVatNumber())
                                               value="{{ $cliente->getVatNumber() }}"
                                               @endif
                                               readonly>
                                    </div>

                                </div>
                                <div class="col-lg-6">

                                    <div class="form-group">
                                        <label for="cf">C.F.</label>
                                        <input type="text"
                                               class="form-control"
                                               id="cf"
                                               @if($cliente->getTaxCode())
                                               value="{{ $cliente->getTaxCode() }}"
                                               @endif
                                               readonly>
                                    </div>

                                </div>
                            </div>

                            <hr>

                            <div class="form-group">
                                <label for="mail">Email</label>
                                <input type="text"
                                       class="form-control"
                                       id="mail"
                                       @if($cliente->getEmail())
                                       value="{{ $cliente->getEmail() }}"
                                       @endif
                                       readonly>
                            </div>

                        @else

                            <h2>I tuoi dati non sono stati trovati.</h2>
                            <br>
                            Non ti preoccupare, semplicemente non sei ancora presente nel nostro database.
                            <br><br>
                            Puoi rinnovare senza problemi il tuo servizio, la fattura (nel caso sia necessaria) ti verrà inviata correttamente.

                        @endif

                    </div>

                </div>

                <br>

            </div>
            <div class="col-lg-5">

                <div class="card">

                    <div class="card-header">
                        {{ $customer_service->name }} <strong>{{ $customer_service->reference }}</strong>
                    </div>
                    <div class="card-body">

                        <table class="table table-sm">
                            <thead class="text-small">
                            <tr>
                                <td>Servizio</td>
                                <td class="text-center"></td>
                                <td align="right" class="text-right">Importo</td>
                            </tr>
                            </thead>

                            <tbody class="text-small">

                            @php($services_total = 0)
                            @php($discount_total = 0)
                            @php($services_discount_total = 0)

                            @foreach($array_services_rows as $k => $v)

                                @if($v['price_customer_sell'] >= $v['price_sell'])
                                    @php($service_price = $v['price_customer_sell'])
                                @else
                                    @php($service_price = $v['price_sell'])
                                @endif

                                @if($v['is_share'] == 1)
                                    @if($v['price_customer_sell'])
                                        @php($service_price = $v['price_customer_sell'])
                                    @else
                                        @php($service_price = $v['price_sell'])
                                    @endif
                                @endif

                                @php($discount = 0)
                                @php($discount_alert = 0)
                                @if($v['price_sell'] > $v['price_customer_sell'] && $v['is_share'] != 1)
                                    @php($discount_alert = 1)
                                    @php($discount = (($v['price_sell'] - $v['price_customer_sell']) * count($v['reference'])))
                                @endif

                                <tr>
                                    <td>
                                        {{ $v['name'] }}
                                    </td>
                                    <td class="text-center">
                                        <small>
                                            @if(count($v['reference']) > 1)
                                                {{ count($v['reference']) }} x &euro; {{ number_format($service_price, 2, ',', '.') }}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-right">
                                        @if($discount_alert == 1)
                                            *
                                        @endif
                                        &euro; {{ number_format((count($v['reference']) * $service_price), 2, ',', '.') }}
                                    </td>
                                </tr>

                                @php($services_total += $service_price * count($v['reference']))
                                @php($discount_total += $discount)

                            @endforeach

                            @if($discount_total > 0)
                                <tr>
                                    <td>
                                        @php($discount_per = $discount_total/$services_total*100)
                                        Sconto incondizionato

                                        @if($discount_per >= 10)
                                            (<strong>
                                                {{ number_format($discount_per, 2, ',', '.') }}%
                                            </strong>)
                                        @endif
                                    </td>
                                    <td colspan="2" class="text-right">
                                        <strong>- &euro; {{ number_format($discount_total, 2, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endif

                            </tbody>
                        </table>

                        @php($total = $services_total - $discount_total)

                        <table class="table table-sm table-borderless table-total-container">

                            <tr class="text-small">
                                <td>Imponibile</td>
                                <td colspan="2" class="text-right">
                                    &euro; {{ number_format($total, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="text-small">
                                <td>Totale IVA</td>
                                <td colspan="2" class="text-right">
                                    &euro; {{ number_format(($total * 1.22 - $total), 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="text-big">
                                <th colspan="3" class="text-right">
                                    &euro; {{ number_format($total * 1.22, 2, ',', '.') }}
                                </th>
                            </tr>

                        </table>

                        @if($discount_total > 0)
                        <small class="text-gray">* servizi ai quali è stato applicato uno sconto.</small>
                        @endif

                    </div>

                </div>

                <br>

                <form action="{{ route('payment.update', $payment->sid) }}" method="post">

                    @csrf

                    <div class="card border-success">
                        <div class="card-header bg-success border-success text-white">

                            Metodo di pagamento

                        </div>
                        <div class="card-body">

                            <div class="alert text-secondary">
                                <div class="custom-control custom-radio">
                                    <input type="radio"
                                           class="custom-control-input"
                                           id="bonifico"
                                           name="payment"
                                           value="bonifico"
                                           checked>
                                    <label class="custom-control-label" for="bonifico">Bonifico bancario</label>
                                </div>
                            </div>

                            {{--<div class="alert text-secondary">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="paypal" name="payment" class="custom-control-input">
                                    <label class="custom-control-label" for="paypal">PayPal / Carta di Credito</label>
                                </div>
                            </div>--}}

                            <div class="privacy-content">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="privacy" name="privacy" class="custom-control-input">
                                    <label class="custom-control-label" for="privacy">
                                        <small>
                                            {!! $privacy_msg !!}
                                        </small>
                                    </label>
                                </div>
                            </div>

                            <br>

                            <button type="submit"
                                    id="btn-submit"
                                    class="btn btn-success btn-lg btn-block disabled">
                                Conferma il rinnovo
                            </button>

                        </div>
                    </div>

                    <input type="hidden" name="sid" value="{{ $payment->sid }}">
                </form>

                <br>

            </div>
        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="privacyError" tabindex="-1" role="dialog" aria-labelledby="privacyErrorTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyErrorTitle">Privacy Policy non accettata.</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    È necessario dare il consenso alla privacy policy per poter rinnovare il servizio.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
