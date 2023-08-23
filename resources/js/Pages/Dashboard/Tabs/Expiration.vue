<script setup>

import {Collapse} from "vue-collapsed";
import {__currency} from "@/ComponentsExt/Currency.js";
import {__date} from "@/ComponentsExt/Date.js";
import {ref} from "vue";
import ModalReady from "@/Components/ModalReady.vue";
import Modal from "@/Components/Modal.vue";
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    data: Object,
    filters: Object,
});

const modalShow = ref(false);
const modalData = ref();
const modalInvoiceShow = ref(false);
const modalInvoiceData = ref();
const modalInvoiceForm = useForm({
    id: Number,
    date: Date,
    payment_received: Number,
    email_send: Number
});

function getDate(dateValute, addDays = 0)
{
    let date = new Date(dateValute);

    return new Date(date.setDate(date.getDate() + addDays));
}

function collapse(indexSelected)
{
    let services = props.data.services_exp;
    let document = window.document;

    services.forEach((_, index) => {

        services[index].isExpanded = indexSelected === index ? !services[index].isExpanded : false;

        document.getElementById('service-header-' + index).classList.remove('service-header-selected');
        document.getElementById('service-body-' + index).classList.remove('service-body-selected');

    });

    if (services[indexSelected].isExpanded === true) {

        document.getElementById('service-header-' + indexSelected).classList.add('service-header-selected');
        document.getElementById('service-body-' + indexSelected).classList.add('service-body-selected');

    }
}

function modalInvoiceInit(id)
{
    modalInvoiceForm.id = id;
    modalInvoiceForm.date = __date(props.data.today, 'date');
    modalInvoiceForm.payment_received = 1;
}
function invoiceCreate(email_send)
{
    modalInvoiceForm.email_send = email_send;
    modalInvoiceForm.get(route('dashboard.service_exp.invoice', modalInvoiceForm.id));
}

</script>

<template>

    <table class="table table-hover">

        <thead>

        <tr>
            <th></th>
            <th class="text-left">
                Cliente
                <span class="text-xs font-normal">(hai {{ props.data.services_exp.length }} scadenze)</span>
            </th>
            <th class="text-left hidden sm:table-cell">
                Servizio
            </th>
            <th class="hidden sm:table-cell">
                Scadenza
            </th>
            <th>
                Importo
            </th>
        </tr>

        </thead>

        <tbody>

        <template v-for="(service, index) in props.data.services_exp">

            <tr class="cursor-pointer service-header"
                :class="{
                    'table-danger': getDate(props.data.today) > getDate(service.expiration),
                    'table-warning': getDate(props.data.today, 60) > getDate(service.expiration),
                }"
                :id="'service-header-' + index">

                <td class="align-middle lg:w-[140px]">

                    <div class="lg:flex lg:w-full"
                         :class="{
                            '!hidden': getDate(props.data.today, 60) < getDate(service.expiration),
                         }">

                        <div class="flex-initial mr-2 mb-2 lg:mb-0">

                            <button class="btn btn-primary btn-sm"
                                    :class="{
                                        'btn-danger': getDate(props.data.today) > getDate(service.expiration),
                                        'btn-warning': getDate(props.data.today, 60) > getDate(service.expiration),
                                    }"
                                    @click="() => {
                                        modalShow = true
                                        modalData = {
                                            title: 'Rinnova Servizio',
                                            msg: 'Rinnova il servizio',
                                            service: service.name + ' ' + service.reference,
                                            confirmBtnClass: 'btn-success',
                                            confirmBtnText: 'Sì, rinnova',
                                            confirmURL: route('dashboard.service_exp.renew', service.id)
                                        }
                                    }">

                                <svg class="w-4 h-4"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>

                            </button>

                        </div>

                        <div class="flex-initial mr-2 mb-2 lg:mb-0">

                            <button class="btn btn-primary btn-sm"
                                    :class="{
                                        'btn-danger': getDate(props.data.today) > getDate(service.expiration),
                                        'btn-warning': getDate(props.data.today, 60) > getDate(service.expiration),
                                        'btn-secondary disabled': service.expiration_monthly === '1',
                                    }"
                                    @click="() => {
                                        modalShow = true
                                        modalData = {
                                            title: 'Avvisa Cliente',
                                            msg: 'Avvisa ' + (service.customer_name ? service.customer_name : service.customer.name) + ' per la scadenza di ',
                                            service: service.name + ' ' + service.reference,
                                            confirmBtnClass: 'btn-success',
                                            confirmBtnText: 'Invia avviso',
                                            confirmURL: route('dashboard.service_exp.alert', service.id)
                                        }
                                    }">

                                <svg class="w-4 h-4"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 10-2.636 6.364M16.5 12V8.25" />
                                </svg>

                            </button>

                        </div>

                        <div class="flex-initial">

                            <button class="btn btn-sm"
                                    :class="{
                                        'btn-danger': getDate(props.data.today) > getDate(service.expiration) && service.autorenew !== '1' && !service.payment_type,
                                        'btn-warning': getDate(props.data.today, 60) > getDate(service.expiration) && service.autorenew !== '1' && !service.payment_type,
                                        '!bg-sky-200 !border-1 !border-sky-500 !text-sky-500': service.autorenew === '1' && !service.payment_type,
                                        'btn-success': service.payment_type,
                                    }"
                                    @click="() => {
                                        modalInvoiceShow = true,
                                        modalInvoiceData = {
                                            customer: service.customer_name ? service.customer_name : service.customer.name,
                                            service: service,
                                            price: __currency(service.total_sell_notax, 'EUR')
                                        }

                                        modalInvoiceInit(service.id);
                                    }">

                                <svg class="w-4 h-4"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.756a4.5 4.5 0 100 8.488M7.5 10.5h5.25m-5.25 3h5.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>

                            </button>

                        </div>

                    </div>

                </td>
                <td class="align-middle hidden sm:table-cell"
                    @click="collapse(index)">

                    {{ service.company ? service.company : service.customer.company }}

                    <br>
                    <span class="text-xs">
                        {{ service.customer_name ? service.customer_name : service.customer.name }}
                        -

                        <!-- Mostro se invio multiplo -->
                        <span v-if="service.email">

                            {{ service.email.split(';')[0] }}

                            <span v-if="service.email.split(';').length > 1"
                                  class="tooltip">

                                + <svg class="inline mb-[1px] w-4 h-4 text-sky-400"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>

                                <span class="tooltiptext tooltip-top">
                                    {{ service.email.split(';').join("\n") }}
                                </span>

                            </span>

                        </span>

                        <span v-else-if="service.customer.email">

                            {{ service.customer.email.split(';')[0] }}

                            <span v-if="service.customer.email.split(';').length > 1"
                                  class="tooltip">

                                + <svg class="inline mb-[1px] w-4 h-4 text-sky-400"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>

                                <span class="tooltiptext tooltip-top">
                                    {{ service.customer.email.split(';').join("\n") }}
                                </span>

                            </span>

                        </span>

<!--                        {{
                            service.email ?

                                service.email.split(';').length > 1 ?
                                    service.email.split(';')[0] + ' + [CC]' : service.email

                                :

                                service.customer.email.split(';').length > 1 ?
                                    service.customer.email.split(';')[0] + ' + [CC]' : service.customer.email
                        }}-->
                    </span>

                </td>
                <td class="align-middle"
                    @click="collapse(index)">

                    {{ service.name }}
                    <br>
                    <span class="text-xs">
                        {{ service.reference }}
                        <br>
                        <span class="font-bold sm:hidden">
                            {{ __date(service.expiration, 'day') }}
                        </span>
                    </span>

                </td>
                <td class="align-middle text-center hidden sm:table-cell"
                    @click="collapse(index)">

                    {{ __date(service.expiration, 'day') }}

                </td>
                <td class="align-middle text-right"
                    @click="collapse(index)">

                    <span class="text-lg font-semibold">
                        {{ service.total_sell_notax ? __currency(service.total_sell_notax, 'EUR') : '-' }}
                    </span>
                    <br>
                    <span class="text-xs">
                        {{ service.total_sell_tax ? __currency(service.total_sell_tax, 'EUR') : '' }}
                    </span>

                </td>
            </tr>

            <tr class="!p-0 !m-0 no-hover service-body"
                :id="'service-body-' + index">

                <td class="!p-0 !m-0 !border-0"
                    :class="{
                        'table-danger': getDate(props.data.today) > getDate(service.expiration),
                        'table-warning': getDate(props.data.today, 60) > getDate(service.expiration),
                    }"
                    colspan="5">

                    <Collapse as="section" :when="Boolean(service.isExpanded)" class="v-collapse">

                        <table class="table table-hover service-detail !mb-0 w-full"
                               :class="{
                                    'table-danger': getDate(props.data.today) > getDate(service.expiration),
                                    'table-warning': getDate(props.data.today, 60) > getDate(service.expiration),
                               }">

                            <tr v-for="serviceDetail in service.details"
                                class="!border-sky-600 !border-b-[0.5px]">

                                <td class="w-[60px] lg:w-[140px] hidden sm:table-cell"></td>
                                <td class="pl-2 lg:pl-8 pt-1 pb-1">

                                    {{ serviceDetail.service.name }}
                                    <br>
                                    <span class="!p-0 !m-0 !border-0 text-xs !bg-transparent">
                                        {{ serviceDetail.reference }}
                                    </span>

                                </td>
                                <td class="pr-3 align-middle text-right">
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

                <div class="sm:inline-flex w-full">

                    <div class="mb-6 w-full sm:w-1/2 sm:mr-4 sm:mb-0">

                        <label for="invoice-date">Data fattura</label>
                        <input id="invoice-date"
                               class="form-control text-center"
                               type="date"
                               v-model="modalInvoiceForm.date" />

                    </div>

                    <div class="w-full sm:w-1/2 sm:ml-4">

                        <div class="form-check form-switch">

                            <input class="form-check-input sm:!mt-[30px]"
                                   type="checkbox"
                                   id="invoice-payment"
                                   true-value="1"
                                   false-value="0"
                                   v-model="modalInvoiceForm.payment_received"
                                   checked />

                            <label class="form-check-label"
                                   for="invoice-payment">
                                Pagamento ricevuto.
                                <br>
                                <span class="text-xs">
                                    (se lo switch è attivo, la fattura risulterà saldata)
                                </span>
                            </label>

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

                    Vuoi inviare la fattura via email a <span class="font-semibold">{{ modalInvoiceData.customer }}</span>?
                    <br>
                    <span class="text-sm">
                        {{ modalInvoiceData.service.name }} {{ modalInvoiceData.service.reference }}
                        <span class="font-semibold whitespace-nowrap">
                            {{ modalInvoiceData.price }} + IVA
                        </span>
                    </span>

                </div>

            </div>

            <div class="inline-flex w-full">

                <div class="w-1/3 sm:w-1/2 mr-2">

                    <div class="btn btn-secondary w-full"
                         @click="modalInvoiceShow = false">Annulla</div>

                </div>

                <div class="w-1/3 sm:w-1/4 mr-2 ml-2">

                    <button type="button"
                            class="btn btn-danger w-full"
                            @click="invoiceCreate(0)">
                        No Email
                    </button>

                </div>

                <div class="w-1/3 sm:w-1/4 ml-2">

                    <button type="button"
                            class="btn btn-success w-full"
                            @click="invoiceCreate(1)">
                        Sì Email
                    </button>

                </div>

            </div>

        </div>

    </Modal>

</template>

<style>
/* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
}

/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    /*width: 120px;*/
    bottom: 25px;
    left: 50%;
    margin-left: -60px; /* Use half of the width (120/2 = 60), to center the tooltip */
    background-color: black;
    color: #fff;
    text-align: center;
    padding: 15px;
    border-radius: 6px;

    /* Position the tooltip text - see examples below! */
    position: absolute;
    z-index: 1;
}
/*.tooltip .tooltiptext::after {
    content: " ";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: black transparent transparent transparent;
}*/

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
}
</style>
