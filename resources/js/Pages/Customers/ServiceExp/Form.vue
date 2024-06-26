<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, Link, useForm, usePage} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "@/ComponentsExt/Translations";
import {__date} from "@/ComponentsExt/Date";
import {__currency} from "@/ComponentsExt/Currency";
import TableSearch from "@/Components/Table/TableSearch.vue";
import Table from "@/Components/Table/Table.vue";
import TablePagination from "@/Components/Table/TablePagination.vue";

const props = defineProps({
    data: Object,
    services: Object,
    customer: Object,
    filters: Object,
    create_url: String
})

const dataForm = Object.fromEntries(Object.entries(props.data).map((v) => {
    return props.data ? v : '';
}));

dataForm.customer_id = props.customer.id;
dataForm.expiration = __date(props.data.expiration, 'date');
dataForm.details = usePage().props.serviceExp;

const form = useForm(dataForm);

function serviceExpActionRoute (route, data, action) {

    let formSession = useForm({
        serviceExp_data: form.details,
        serviceExp_id: data.id,
        serviceExp_index: action === 'remove' ? data.serviceExp_index : null,
        serviceExpAddTo: action === 'add' ? true : null,
        serviceExpRemove: action === 'remove' ? true : null,
        currentUrl: window.location.href,
    });

    formSession.post(window.location.href, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.details = usePage().props.serviceExp;
        }
    });

}

</script>

<template>

    <Head title="Clienti" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="[
                'Clienti',
                customer.company,
                data.id ?
                    form.name + ' - ' + form.reference :
                        form.name.length > 0 ? form.name + ' - ' + form.reference : 'Nuova Scadenza'
            ]" />

        </template>

        <ApplicationContainer>

            <form @submit.prevent="form.post(route(
                form.id ? 'customer.serviceExpiration.update' : 'customer.serviceExpiration.store',
                form.id ? form.id : ''
                ))">

                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">

                        <button class="w-[180px] nav-link active"
                                id="nav-expiration-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#nav-expiration"
                                type="button"
                                role="tab"
                                aria-controls="nav-expiration"
                                aria-selected="true">Scadenza servizio</button>

                        <button class="w-[180px] nav-link"
                                id="nav-customer-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#nav-customer"
                                type="button"
                                role="tab"
                                aria-controls="nav-customer"
                                aria-selected="false">Anagrafica cliente</button>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">

                    <div class="tab-pane fade show active pt-4"
                         id="nav-expiration"
                         role="tabpanel"
                         aria-labelledby="nav-expiration-tab"
                         tabindex="0">

                        <h2 class="text-3xl mb-2 mt-2">Dettagli servizio in scadenza</h2>
                        <span class="text-sm">
                            In questa sezione puoi creare il tuo pacchetto scadenza,
                            associando i servizi desiderati.
                            Il servizio sarà collegato al cliente selezionato.
                        </span>

                        <br><br>

                        <div class="row">
                            <div class="col-lg">

                                <label class="form-label">Nome servizio in scadenza</label>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Manutenzione"
                                       v-model="form.name" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.name">{{ __(form.errors.name) }}</div>

                                <br>

                                <div class="row">
                                    <div class="col-lg mb-6 lg:mb-0">

                                        <label class="form-label">Riferimento</label>
                                        <input type="text"
                                               class="form-control"
                                               placeholder="riferimento del servizio"
                                               v-model="form.reference" />
                                        <div class="text-red-500 text-center"
                                             v-if="form.errors.reference">{{ __(form.errors.reference) }}</div>

                                    </div>
                                    <div class="col-lg mb-6 lg:mb-0">

                                        <label class="form-label"
                                               for="expiration">Data scadenza</label>
                                        <input class="form-control lg:text-center"
                                               type="date"
                                               id="expiration"
                                               v-model="form.expiration" />
                                        <div class="text-red-500 text-center"
                                             v-if="form.errors.expiration">{{ form.errors.expiration }}</div>

                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-5">

                                <div class="form-check form-switch !mb-3">

                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="autorenew"
                                           true-value="1"
                                           false-value="0"
                                           v-model="form.autorenew"
                                           checked />

                                    <label class="form-check-label"
                                           for="autorenew">
                                        <span class="text-gray-500 text-[0.9em]">Auto rinnovo</span>
                                        <br>
                                        <span class="text-xs">
                                            (se lo switch è attivo, il servizio si auto rinnoverà, inviando la fattura)
                                        </span>
                                    </label>

                                </div>

                                <div class="form-check form-switch !mb-3">

                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="no_email_alert"
                                           true-value="1"
                                           false-value="0"
                                           v-model="form.no_email_alert"
                                           checked />

                                    <label class="form-check-label"
                                           for="no_email_alert">
                                        <span class="text-gray-500 text-[0.9em]">Disattiva avviso via email</span>
                                        <br>
                                        <span class="text-xs">
                                    (se lo switch è attivo, il cliente non riceverà nessun avviso scadenza via mail)
                                </span>
                                    </label>

                                </div>

                                <div class="form-check form-switch !mb-3">

                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="no_sms_alert"
                                           true-value="1"
                                           false-value="0"
                                           v-model="form.no_sms_alert"
                                           checked />

                                    <label class="form-check-label"
                                           for="no_sms_alert">
                                        <span class="text-gray-500 text-[0.9em]">Disattiva avviso via SMS</span>
                                        <br>
                                        <span class="text-xs">
                                    (se lo switch è attivo, il cliente non riceverà nessun avviso scadenza via SMS)
                                </span>
                                    </label>

                                </div>

                            </div>
                        </div>

                        <br><br>

                        <div class="row">
                            <div class="col-lg-5 mb-6 lg:mb-0">

                                <div class="card !border-gray-200">
                                    <div class="card-body">

                                        <h2 class="text-3xl mb-2">
                                            Lista servizi
                                        </h2>

                                        <br>

                                        <TableSearch placeholder="Cerca..."
                                                     class="mb-4"
                                                     :route-search="form.id ? route('customer.serviceExpiration.edit', form.id) : route('customer.serviceExpiration.create', customer.id)"
                                                     :filters="filters" />

                                        <Table class="table table-sm text-sm"
                                               :data="{
                                                    filters: filters,
                                                    routeSearch: form.id ? route('customer.serviceExpiration.edit', form.id) : route('customer.serviceExpiration.create', customer.id),
                                                    data: services.data,
                                                    structure: [{
                                                        class: 'text-left',
                                                        label: 'Nome',
                                                        field: 'name',
                                                    }, {
                                                        class: 'text-right w-[10%]',
                                                        // classData: 'text-sm',
                                                        label: 'Costo',
                                                        field: 'price_sell',
                                                        order: false,
                                                        fnc: function (d) {

                                                            let html = ''

                                                            html += __currency(d.price_sell, 'EUR')

                                                            return html
                                                        }
                                                    }, {
                                                        class: 'w-[1%]',
                                                        btnCustom: true,
                                                        route: form.id ? route('customer.serviceExpiration.edit', form.id) : route('customer.serviceExpiration.create', customer.id),
                                                        emit: 'serviceAddTo',
                                                        fnc: function (d) {

                                                            let html = '';

                                                            html += '<div class=\'btn btn-primary\'>';
                                                            html += '<svg class=\'w-3 h-3\' xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4.5v15m7.5-7.5h-15\' /></svg>';
                                                            html += '</div>';

                                                            return html;
                                                        }
                                                    }],
                                                }"
                                               @serviceAddTo="(data, route) => {

                                                   serviceExpActionRoute(route, data, 'add');

                                        }"/>

                                        <TablePagination class="mt-6"
                                                         :links="services.links"></TablePagination>

                                    </div>
                                </div>

                            </div>
                            <div class="col-lg mb-6 lg:mb-0">

                                <div class="card !border-gray-200">
                                    <div class="card-body">

                                        <h2 class="text-3xl mb-2">
                                            Servizi inseriti nella scadenza
                                        </h2>

                                        <br>

                                        <div v-for="(detail, index) in form.details" :key="index"
                                             class="text-sm alert alert-light !pb-1">

                                            <div class="ml-2 mb-2 text-xs">
                                                {{ detail.service.name }}
                                            </div>

                                            <div class="row mb-2">
                                                <!-- <div class="col-sm-3 text-sm pt-1 mb-2">

                                                    {{ detail.service.name }}

                                                </div> -->
                                                <div class="col-sm-5 mb-2">

                                                    <input type="text"
                                                           class="form-control form-control-sm"
                                                           placeholder="rif. servizio"
                                                           v-model="detail.reference" />

                                                </div>
                                                <div class="col-sm-4 mb-2">

                                                    <div class="w-full inline-flex">

                                                        <div class="w-3/4 sm:w-full">
                                                            <input type="date"
                                                                   class="form-control form-control-sm"
                                                                   :value="__date(detail.expiration, 'date')"
                                                                   @change="(e) => detail.expiration = e.target.value"/>
                                                        </div>
                                                        <div class="w-1/4 ml-2 sm:hidden">

                                                            <button type="button"
                                                                    class="btn btn-primary btn-sm w-full"
                                                                    @click="detail.expiration = null">
                                                                Annulla
                                                            </button>

                                                        </div>

                                                    </div>

                                                </div>
                                                <div class="col-sm-2 mb-2">

                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">&euro;</span>
                                                        <input type="number"
                                                               step="any"
                                                               inputmode="decimal"
                                                               class="form-control form-control-sm text-right"
                                                               placeholder="0.00"
                                                               v-model="detail.price_sell" />
                                                    </div>

                                                </div>
                                                <div class="col-sm-1 mb-2">

                                                    <button type="button"
                                                            class="btn btn-sm sm:w-full btn-secondary sm:h-[30px]"
                                                            @click="serviceExpActionRoute(
                                                                form.id ? route('customer.serviceExpiration.edit', form.id) : route('customer.serviceExpiration.create', customer.id),
                                                                detail,
                                                                'remove'
                                                            )">

                                                        <svg class="w-3 h-3 m-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                                        </svg>

                                                    </button>

                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="tab-pane fade show pt-4"
                         id="nav-customer"
                         role="tabpanel"
                         aria-labelledby="nav-customer-tab"
                         tabindex="0">

                        <div class="alert alert-warning">
                            <h2 class="text-3xl mb-2 mt-2">Anagrafica cliente scadenza servizio</h2>
                            <span class="text-sm">
                                Qui puoi inserire dati alternativi ai dati cliente,
                                per esempio una seconda azienda del cliente. In questo modo
                                il servizio verrà fatturato a questo cliente.
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-sm mb-6">

                                <br class="md:hidden">
                                <br class="md:hidden">

                                <h2 class="text-3xl mb-6">Fatturazione</h2>

                                <label class="form-label">Azienda / Nominativo fatturazione</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.customer_company" />

                                <br>

                                <div class="row">
                                    <div class="col-lg">

                                        <label class="form-label">Partita IVA</label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.customer_piva" />

                                    </div>
                                    <div class="col-lg">

                                        <br class="md:hidden">

                                        <label class="form-label">Codice Fiscale</label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.customer_cf" />

                                    </div>
                                </div>

                                <br>

                                <div class="row">
                                    <div class="col-lg-6">

                                        <label class="form-label">Indirizzo</label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.customer_address" />

                                    </div>
                                    <div class="col-lg-3">

                                        <br class="md:hidden">

                                        <label class="form-label">Città</label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.customer_city" />

                                    </div>
                                    <div class="col-lg-3">

                                        <br class="md:hidden">

                                        <label class="form-label">CAP</label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.customer_cap" />

                                    </div>
                                </div>

                            </div>
                            <div class="col-sm mb-6">

                                <h2 class="text-3xl mb-6">Contatto</h2>

                                <div class="row">
                                    <div class="col-lg-7">

                                        <label class="form-label">Referente</label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.customer_name" />

                                        <br>

                                        <div class="row">
                                            <div class="col-lg">

                                                <label class="form-label">
                                                    Cellulare
                                                </label>
                                                <input type="text"
                                                       class="form-control"
                                                       v-model="form.customer_cellphone" />

                                                <br class="md:hidden">

                                            </div>
                                            <div class="col-lg">

                                                <label class="form-label">
                                                    Telefono
                                                </label>
                                                <input type="text"
                                                       class="form-control"
                                                       v-model="form.customer_telephone" />

                                            </div>
                                        </div>

                                        <br>

                                        <label class="form-label">Email</label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.customer_email" />
                                        <label class="form-label !text-xs mt-2">( separare con ";" per invio multiplo )</label>

                                    </div>
                                    <div class="col-lg">

                                        <br class="md:hidden">

                                        <label class="form-label">Note</label>
                                        <textarea class="form-control h-[216px]"
                                                  v-model="form.customer_note"></textarea>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="text-right mt-0 sm:mt-2">

                    <Link class="btn btn-secondary w-[120px]"
                          :href="route('customer.edit', customer.id)">
                        Annulla
                    </Link>

                    <button type="submit"
                            class="btn btn-success ml-2 w-[120px]">Salva</button>

                </div>

            </form>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>

<script>
import {router} from "@inertiajs/vue3";

export default {
    data () {
        return {}
    },
    mounted () {

        if (this.create_url !== null) {
            router.get(this.create_url);
        }

    }
}
</script>
