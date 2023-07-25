<script setup>

import {Collapse} from "vue-collapsed";
import {__currency} from "@/ComponentsExt/Currency.js";
import {__date} from "@/ComponentsExt/Date.js";
import {ref} from "vue";
import ModalReady from "@/Components/ModalReady.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    data: Object,
    filters: Object,
});

const modalShow = ref(false);
const modalData = ref();
const modalInvoiceShow = ref(false);

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

        <template v-for="(service, index) in props.data.services_exp">

            <tr class="cursor-pointer service-header"
                :class="{
                    'table-danger': getDate(props.data.today) > getDate(service.expiration),
                    'table-warning': getDate(props.data.today, 60) > getDate(service.expiration),
                }"
                :id="'service-header-' + index">

                <td class="align-middle w-[140px]">

                    <div class="flex w-full"
                         :class="{
                            'hidden': getDate(props.data.today, 60) < getDate(service.expiration),
                         }">

                        <div class="flex-initial mr-2">

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
                                        'btn-danger': getDate(props.data.today) > getDate(service.expiration),
                                        'btn-warning': getDate(props.data.today, 60) > getDate(service.expiration),
                                        'btn-info': service.autorenew === '1',
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
                               :value="__date(props.data.today, 'day')" />

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

</template>
