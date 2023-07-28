<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "@/ComponentsExt/Translations";
import {__date} from "@/ComponentsExt/Date";
import {__currency} from "@/ComponentsExt/Currency";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    data: Object,
    services: Object,
    filters: Object,
    create_url: String
})

const dataForm = Object.fromEntries(Object.entries(props.data).map((v) => {
    return props.data ? v : '';
}));

const form = useForm(dataForm);

</script>

<template>

    <Head title="Clienti" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="[
                'Clienti',
                'Servizio',
                data.id ?
                    form.name + ' - ' + form.reference :
                        form.name.length > 0 ? form.name + ' - ' + form.reference : 'Nuovo Cliente'
            ]" />

        </template>

        <ApplicationContainer>

            <form @submit.prevent="form.post(route(
                form.id ? 'customer.serviceExpiration.update' : 'customer.serviceExpiration.store',
                form.id ? form.id : ''
                ))">

                <h2 class="text-3xl mb-2">Anagrafica cliente scadenza servizio</h2>

                <br>

                <div class="row">
                    <div class="col">

                        <label class="form-label">Azienda</label>
                        <input type="text"
                               class="form-control"
                               placeholder="es. Pitture Rossi Srl"
                               v-model="form.company" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.company">{{ __(form.errors.company) }}</div>

                    </div>
                    <div class="col">

                        <label class="form-label">P.IVA</label>
                        <input type="text"
                               class="form-control"
                               placeholder="123456789"
                               v-model="form.piva" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col">

                        <label class="form-label">Referente</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Mario"
                               v-model="form.customer_name" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.customer_name">{{ __(form.errors.customer_name) }}</div>

                    </div>
                    <div class="col">

                        <label class="form-label">Email</label>
                        <input type="text"
                               class="form-control"
                               placeholder="info@pitturerossi.it"
                               v-model="form.email" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.email">{{ __(form.errors.email) }}</div>

                    </div>
                </div>

                <br>

                <h2 class="text-3xl mb-2 mt-3">Dati scadenza servizio</h2>

                <br>

                <div class="row">
                    <div class="col-4">

                        <label class="form-label">Nome servizio in scadenza</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Mario"
                               v-model="form.name" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.name">{{ __(form.errors.name) }}</div>

                    </div>
                    <div class="col">

                        <label class="form-label">Riferimento</label>
                        <input type="text"
                               class="form-control"
                               placeholder="info@pitturerossi.it"
                               v-model="form.reference" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.reference">{{ __(form.errors.reference) }}</div>

                    </div>
                    <div class="col-3">

                        <label class="form-label">Data scadenza</label>
                        <input type="text"
                               class="form-control text-center"
                               placeholder="info@pitturerossi.it"
                               v-model="form.expiration" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.expiration">{{ form.errors.expiration }}</div>

                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-5">

                        <div class="card">
                            <div class="card-body">

                                <h2 class="text-3xl mb-2">
                                    Lista servizi
                                </h2>

                                <br>

                                <Table class="table table-sm"
                                       :data="{
                                            filters: filters,
                                            // routeSearch: form.id ? route('products.service.edit', form.id) : 'products.service.create',
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
                                                fnc: function (d) {

                                                    let html = ''

                                                    html += __currency(d.price_sell, 'EUR')

                                                    return html
                                                }
                                            }, {
                                                class: 'w-[1%]',
                                                btnCustom: true,
                                                route: form.id ? route('customer.serviceExpiration.edit', form.id) : route('customer.serviceExpiration.create'),
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

                            </div>
                        </div>

                    </div>
                    <div class="col">

                        <div class="card">
                            <div class="card-body">

                                <h2 class="text-3xl mb-2">
                                    Servizi inseriti nella scadenza
                                </h2>

                                <br>

                                {{ usePage().props.serviceExp }}
                                <div v-for="(detail, index) in usePage().props.serviceExp">

                                    <div class="row mb-2">
                                        <div class="col-5 text-sm pt-1">

                                            {{ detail.service === undefined ? detail.name : detail.service.name }}

                                        </div>
                                        <div class="col-4">

                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   placeholder="info@pitturerossi.it"
                                                   :value="detail.reference" />

                                        </div>
                                        <div class="col-2">

                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">&euro;</span>
                                                <input type="text"
                                                       class="form-control form-control-sm text-right"
                                                       placeholder="0.00"
                                                       :value="detail.price_sell" />
                                            </div>

                                        </div>
                                        <div class="col-1">

                                            <button class="btn btn-sm w-full btn-secondary"
                                                    @click="serviceExpActionRoute(
                                                                form.id ? route('customer.serviceExpiration.edit', form.id) : route('customer.serviceExpiration.create'),
                                                                detail,
                                                                'remove'
                                                            )">

                                                <svg class="w-4 h-4 m-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

                <br>

                <div class="text-right mt-8">

                    <a href="#"
                       onclick="window.history.back(); return false;"
                       class="btn btn-secondary w-[100px]">Annulla</a>

                    <button type="submit"
                            class="btn btn-success ml-2 w-[100px]">Salva</button>

                </div>

            </form>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>

<script lang="ts">
import {router, useForm} from "@inertiajs/vue3";

export default {
    data () {
        return {}
    },
    methods: {
        serviceExpActionRoute (route, data, action) {

            let form = useForm({
                serviceExp_id: data.id,
                serviceExp_index: action === 'remove' ? data.index : null,
                serviceExpAddTo: action === 'add' ? true : null,
                serviceExpRemove: action === 'remove' ? true : null,
                currentUrl: window.location.href,
            });

            form.get(window.location.href, {
                preserveScroll: true,
                preserveState: true,
            });

        }
    },
    mounted () {

        if (this.create_url !== null) {
            router.get(this.create_url);
        }

    }
}
</script>
