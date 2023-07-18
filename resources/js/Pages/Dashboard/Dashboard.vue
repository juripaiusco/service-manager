<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import { __date } from "@/ComponentsExt/Date";
import { __currency } from "@/ComponentsExt/Currency";
import { Collapse } from "vue-collapsed";
import ModalReady from "@/Components/ModalReady.vue";
import { ref } from "vue";
import Modal from "@/Components/Modal.vue";
import number = CSS.number;

const props = defineProps({
    services_exp: Object,
    months_array: Object,
    months_incoming: Object,
    customers_count: Number,
    customers_avg: Number,
    services: Object,
    services_total_sell: Number,
    services_total_buy: Number,
    services_total_profit: Number,
    today: String,
    filters: Object,
});

const modalShow = ref(false);
const modalData = ref();

const modalInvoiceShow = ref(false);

function getDate(dateValute: any, addDays = 0)
{
    let date = new Date(dateValute);

    return new Date(date.setDate(date.getDate() + addDays));
}

function collapse(indexSelected: any)
{
    let services:any = props.services_exp;
    let document:any = window.document;

    services.forEach((_:any, index:any) => {

        services[index].isExpanded = indexSelected === index ? !services[index].isExpanded : false;

        document.getElementById('service-header-' + index).classList.remove('service-header-selected');
        document.getElementById('service-body-' + index).classList.remove('service-body-selected');

    });

    if (services[indexSelected].isExpanded === true) {

        document.getElementById('service-header-' + indexSelected).classList.add('service-header-selected');
        document.getElementById('service-body-' + indexSelected).classList.add('service-body-selected');

    }
}

</script>

<style>
.service-detail tr:hover {
    background-color: rgba(0, 0, 0, 0.1);
}
.service-header,
.service-header *,
.service-body,
.service-body * {
    transition: all .3s !important;
}
.service-header-selected {
    border-top: 2px solid #38bdf8 !important;
    /*border-right: 2px solid #38bdf8 !important;
    border-left: 2px solid #38bdf8 !important;*/
}
.service-body-selected {
    border-bottom: 2px solid #38bdf8 !important;
    /*border-right: 2px solid #38bdf8 !important;
    border-left: 2px solid #38bdf8 !important;*/
}
.table-hover > tbody > tr.service-header-selected > *,
.service-body-selected tr {
    background-color: rgb(240 249 255) !important;
    --bs-table-bg-state: none;
}
.table-hover > tbody > tr.service-header-selected:hover > *,
.service-body-selected tr:hover {
    background-color: white !important;
    --bs-table-bg-state: none;
}

.v-collapse {
    transition: height 300ms cubic-bezier(0.33, 1, 0.68, 1);
}
</style>

<template>
    <Head>
        <title>Dashboard</title>
    </Head>

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Dashboard']" />

        </template>

        <ApplicationContainer>

            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">

                    <button class="w-[180px] nav-link active"
                            id="nav-expiration-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#nav-expiration"
                            type="button"
                            role="tab"
                            aria-controls="nav-expiration"
                            aria-selected="true">Scadenze</button>

                    <button class="w-[180px] nav-link"
                            id="nav-incoming-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#nav-incoming"
                            type="button"
                            role="tab"
                            aria-controls="nav-incoming"
                            aria-selected="false">Entrate per mese</button>

                    <button class="w-[180px] nav-link"
                            id="nav-profit-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#nav-profit"
                            type="button"
                            role="tab"
                            aria-controls="nav-profit"
                            aria-selected="false">Utile</button>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">

                <div class="tab-pane fade show active pt-4"
                     id="nav-expiration"
                     role="tabpanel"
                     aria-labelledby="nav-expiration-tab"
                     tabindex="0">

                    <table class="table table-hover">

                        <thead>

                        <tr>
                            <th></th>
                            <th class="text-left">
                                Cliente
                                <span class="text-xs font-normal">(hai {{ services_exp!.length }} scadenze)</span>
                            </th>
                            <th class="text-left">
                                Servizio
                            </th>
                            <th>
                                Scadenza
                            </th>
                            <th>
                                Importo
                            </th>
                        </tr>

                        </thead>

                        <tbody>

                        <template v-for="(service, index) in services_exp">

                            <tr class="cursor-pointer service-header"
                                :class="{
                                    'table-danger': getDate(today) > getDate(service.expiration),
                                    'table-warning': getDate(today, 60) > getDate(service.expiration),
                                }"
                                :id="'service-header-' + index">

                                <td class="align-middle">

                                    <div class="flex w-full"
                                         :class="{
                                        'hidden': getDate(today, 60) < getDate(service.expiration),
                                     }">

                                        <div class="flex-initial mr-2">

                                            <button class="btn btn-primary btn-sm"
                                                    :class="{
                                                    'btn-danger': getDate(today) > getDate(service.expiration),
                                                    'btn-warning': getDate(today, 60) > getDate(service.expiration),
                                                    }"
                                                    @click="() => {
                                                        modalShow = true
                                                        modalData = {
                                                            title: 'Rinnova Servizio',
                                                            msg: 'Rinnova il servizio',
                                                            service: service.name + ' ' + service.reference,
                                                            confirmBtnClass: 'btn-success',
                                                            confirmBtnText: 'Sì, rinnova',
                                                            confirmURL: route('dashboard')
                                                        }
                                                    }">

                                                <svg class="w-4 h-4"
                                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>

                                            </button>

                                        </div>

                                        <div class="flex-initial mr-2">

                                            <button class="btn btn-primary btn-sm"
                                                    :class="{
                                                    'btn-danger': getDate(today) > getDate(service.expiration),
                                                    'btn-warning': getDate(today, 60) > getDate(service.expiration) && service.expiration_monthly ==! 1,
                                                    'btn-secondary disabled': service.expiration_monthly == 1,
                                                    }"
                                                    @click="() => {
                                                        modalShow = true
                                                        modalData = {
                                                            title: 'Avvisa Cliente',
                                                            msg: 'Avvisa ' + (service.customer_name ? service.customer_name : service.customer.name) + ' per la scadenza di ',
                                                            service: service.name + ' ' + service.reference,
                                                            confirmBtnClass: 'btn-success',
                                                            confirmBtnText: 'Invia avviso',
                                                            confirmURL: route('dashboard')
                                                        }
                                                    }">

                                                <svg class="w-4 h-4"
                                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 10-2.636 6.364M16.5 12V8.25" />
                                                </svg>

                                            </button>

                                        </div>

                                        <div class="flex-initial">

                                            <button class="btn btn-primary btn-sm"
                                                    :class="{
                                                    'btn-danger': getDate(today) > getDate(service.expiration),
                                                    'btn-warning': getDate(today, 60) > getDate(service.expiration) && service.autorenew ==! 1,
                                                    'btn-info': service.autorenew == 1,
                                                    }"
                                                    @click="() => {
                                                        modalInvoiceShow = true,
                                                        modalData = {
                                                            customer: service.customer_name ? service.customer_name : service.customer.name,
                                                            service: service.name + ' ' + service.reference,
                                                            price: __currency(service.total_sell_notax, 'EUR'),
                                                            confirmBtnClass: 'btn-success',
                                                            confirmBtnText: 'Invia avviso',
                                                            confirmURL: route('dashboard')
                                                        }
                                                    }">

                                                <svg class="w-4 h-4"
                                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.756a4.5 4.5 0 100 8.488M7.5 10.5h5.25m-5.25 3h5.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>

                                            </button>

                                        </div>

                                    </div>

                                </td>
                                <td class="align-middle"
                                    @click="collapse(index)">

                                    {{ service.company ? service.company : service.customer.company }}

                                    <br>
                                    <span class="text-xs">
                                        {{ service.customer_name ? service.customer_name : service.customer.name }}
                                        -
                                        {{ service.email ? service.email : service.customer.email }}
                                    </span>

                                </td>
                                <td class="align-middle"
                                    @click="collapse(index)">

                                    {{ service.name }}
                                    <br>
                                    <span class="text-xs">
                                        {{ service.reference }}
                                    </span>

                                </td>
                                <td class="align-middle text-center"
                                    @click="collapse(index)">

                                    {{ __date(service.expiration, 'day') }}

                                </td>
                                <td class="align-middle text-right"
                                    @click="collapse(index)">

                                    <span class="text-lg font-semibold">
                                        {{ __currency(service.total_sell_notax, 'EUR') }}
                                    </span>
                                    <br>
                                    <span class="text-xs">
                                        {{ __currency(service.total_sell_tax, 'EUR') }}
                                    </span>

                                </td>
                            </tr>

                            <tr class="!p-0 !m-0 no-hover service-body"
                                :id="'service-body-' + index">

                                <td class="!p-0 !m-0 !border-0"
                                    :class="{
                                    'table-danger': getDate(today) > getDate(service.expiration),
                                    'table-warning': getDate(today, 60) > getDate(service.expiration),
                                    }"
                                    colspan="5">

                                    <Collapse as="section" :when="Boolean(service.isExpanded)" class="v-collapse">

                                        <table class="table table-hover service-detail !mb-0 w-full"
                                               :class="{
                                                    'table-danger': getDate(today) > getDate(service.expiration),
                                                    'table-warning': getDate(today, 60) > getDate(service.expiration),
                                               }">

                                            <tr v-for="serviceDetail in service.details"
                                                class="!border-sky-600 !border-b-[0.5px]">

                                                <td class="w-[140px]"></td>
                                                <td class="pl-8 pt-1 pb-1">

                                                    {{ serviceDetail.service.name }}
                                                    <br>
                                                    <span class="!p-0 !m-0 !border-0 text-xs !bg-transparent">
                                                        {{ serviceDetail.reference }}
                                                    </span>

                                                </td>
                                                <td class="pr-3 align-bottom text-right">
                                                    {{ __currency(serviceDetail.price_sell, 'EUR') }}
                                                </td>

                                            </tr>

                                        </table>

                                    </Collapse>

                                </td>

                            </tr>

                        </template>

                        </tbody>

                    </table>


                </div>

                <div class="tab-pane fade pt-4"
                     id="nav-incoming"
                     role="tabpanel"
                     aria-labelledby="nav-incoming-tab"
                     tabindex="0">

                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="text-left">Mese</th>
                            <th class="text-right">Entrate</th>
                            <th class="text-right">Uscite</th>
                            <th class="text-right">Utile</th>
                            <th class="text-right">U. Trimestre</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(month, index) in months_incoming"
                            :class="{
                            'table-secondary': index < __date(today!, 'n'),
                            'line-through': index < __date(today!, 'n'),
                            'table-warning': index == __date(today!, 'n'),
                            }">
                            <td class="capitalize w-1/2">
                                {{ months_array[index - 1] }}
                            </td>
                            <td class="text-right"
                                :class="{
                                    '!text-green-600': index >= __date(today!, 'n'),
                                    '!font-semibold': index >= __date(today!, 'n'),
                                }">
                                {{ __currency(month.incoming, 'EUR') }}
                            </td>
                            <td class="text-right"
                                :class="{
                                    '!text-red-600': index >= __date(today!, 'n'),
                                    '!font-semibold': index >= __date(today!, 'n'),
                                }">
                                {{ __currency(month.outcoming, 'EUR') }}
                            </td>
                            <td class="text-right"
                                :class="{
                                    '!font-semibold': index >= __date(today!, 'n'),
                                }">
                                {{ __currency(month.profit, 'EUR') }}</td>
                            <td rowspan="">

                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th></th>
                            <th class="text-right">
                                {{ __currency(services_total_sell, 'EUR') }}
                            </th>
                            <th class="text-right">
                                {{ __currency(services_total_buy, 'EUR') }}
                            </th>
                            <th class="text-right">
                                {{ __currency(services_total_profit, 'EUR') }}
                            </th>
                            <th class="text-right"></th>
                        </tr>
                        </tfoot>
                    </table>

                </div>

                <div class="tab-pane fade pt-4"
                     id="nav-profit"
                     role="tabpanel"
                     aria-labelledby="nav-profit-tab"
                     tabindex="0">

                    <div class="row">
                        <div class="col-6">

                            <div class="card">
                                <div class="card-body">

                                    <div class="inline-flex w-full">

                                        <div class="w-1/2 text-3xl font-semibold text-center pt-6">

                                            {{ __currency(services_total_profit, 'EUR') }}

                                        </div>
                                        <div class="w-1/2">

                                            <div class="inline-flex w-full p-2">
                                                <div class="w-1/2">
                                                    Entrate
                                                </div>
                                                <div class="w-1/2 text-right text-green-600">
                                                    {{ __currency(services_total_sell, 'EUR') }}
                                                </div>
                                            </div>

                                            <div class="inline-flex w-full p-2">
                                                <div class="w-1/2">
                                                    Uscite
                                                </div>
                                                <div class="w-1/2 text-right text-red-600">
                                                    {{ __currency(services_total_buy, 'EUR') }}
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class="col">

                            <div class="card">
                                <div class="card-body">

                                    <div class="inline-flex w-full p-2">
                                        <div class="w-1/4 font-bold">
                                            {{ customers_count }}
                                        </div>
                                        <div>
                                            Clienti attivi
                                        </div>
                                    </div>

                                    <div class="inline-flex w-full p-2">
                                        <div class="w-1/4 font-bold">
                                            {{ services_exp!.length }}
                                        </div>
                                        <div>
                                            Abbonamenti attivi
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class="col">

                            <div class="card">
                                <div class="card-body text-center">

                                    <div class="p-2 font-bold">
                                        {{ __currency(customers_avg, 'EUR') }}
                                    </div>

                                    <div class="p-2">
                                        spende mediamente ogni cliente
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <br>

                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="w-1/2 text-left">Servizio</th>
                            <th class="text-right">Entrate</th>
                            <th class="text-right">Uscite</th>
                            <th class="text-right">Utile</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="service in services">
                            <td class="align-middle">

                                {{ service.name }}
                                <span class="text-xs">
                                    ( x {{ service.customers_count }} )
                                </span>

                            </td>
                            <td class="text-right align-middle !text-green-600">

                                {{ service.total_service_sell > 0 ? __currency(service.total_service_sell, 'EUR') : '-' }}

                            </td>
                            <td class="text-right !text-red-600">

                                {{ service.total_service_buy > 0 ? __currency(service.total_service_buy, 'EUR') : '-' }}

                            </td>
                            <td class="text-right align-middle font-bold"
                                :class="{
                                '!text-red-600': service.total_service_profit <= 0
                                }">

                                {{ __currency(service.total_service_profit, 'EUR') }}

                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>

            </div>

            <ModalReady :show="modalShow"
                        :data="modalData"
                        @close="modalShow = false">

                <template #title>{{ modalData.title }}</template>
                <template #body>
                    {{ modalData.msg }}
                    <br>
                    <span class="font-semibold">
                        {{ modalData.service }}
                    </span>
                </template>

            </ModalReady>

            <Modal :show="modalInvoiceShow"
                   @close="modalInvoiceShow = false">

                <div class="p-8 dark:text-white">

                    <div class="inline-flex w-full">

                        <div class="w-1/2 font-semibold">

                            Genera Fattura

                        </div>

                        <div class="w-1/2 text-right">

                            <button @click="modalInvoiceShow = false">

                                <svg class="w-5 h-5"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>

                            </button>

                        </div>

                    </div>

                    <div class="mt-6 mb-10">

                        <div class="inline-flex w-full">

                            <div class="w-1/2 mr-2">

                                <label for="invoice-date">Data fattura</label>
                                <input id="invoice-date"
                                       class="form-control"
                                       type="text"
                                       :value="__date(today, 'day')" />

                            </div>

                            <div class="w-1/2 ml-2">

                                <div class="form-check">
                                    <label class="form-check-label"
                                           for="invoice-payment">
                                        Pagamento ricevuto.
                                        <br>
                                        <span class="text-xs">
                                            (se lo switch è attivo, la fattura risulterà saldata)
                                        </span>
                                    </label>
                                    <input class="form-check-input"
                                           type="checkbox"
                                           value=""
                                           id="invoice-payment" checked>

                                </div>

                            </div>

                        </div>

                        <hr class="mt-10 mb-5">

                        <div class="text-sm text-center">
                            <span class="font-semibold">
                                Nota:
                            </span>
                            <span>
                                Generando la fattura rinnoverai automaticamente il servizio alla prossima scadenza.
                            </span>
                        </div>

                        <hr class="mt-5 mb-10">

                        <div class="text-xl text-center mb-10">

                            Vuoi inviare la fattura a <span class="font-semibold">{{ modalData.customer }}</span>?
                            <br>
                            <span class="text-sm">
                                {{ modalData.service }}
                                <span class="font-semibold">
                                    {{ modalData.price }} + IVA
                                </span>
                            </span>

                        </div>

                    </div>

                    <div class="inline-flex w-full">

                        <div class="w-1/2 mr-2">

                            <div class="btn btn-secondary w-full"
                                 @click="modalInvoiceShow = false">Annulla</div>

                        </div>

                        <div class="w-1/4 mr-2 ml-2">

                            <button class="btn btn-danger w-full">
                                No
                            </button>

                        </div>

                        <div class="w-1/4 ml-2">

                            <button class="btn btn-success w-full">
                                Sì
                            </button>

                        </div>

                    </div>

                </div>

            </Modal>

        </ApplicationContainer>

    </AuthenticatedLayout>
</template>
