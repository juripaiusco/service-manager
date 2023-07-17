<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__date} from "../../ComponentsExt/Date";
import {__currency} from "../../ComponentsExt/Currency";

import { Collapse } from "vue-collapsed";

const props = defineProps({
    services: Object,
    today: String,
    filters: Object,
});

function getDate(dateValute: any, addDays = 0)
{
    let date = new Date(dateValute);
    let newDate = new Date(date.setDate(date.getDate() + addDays));

    return newDate;
}

function collapse(indexSelected: Number)
{
    let services = props.services;

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
    <Head title="Dashboard" />

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
                                <span class="text-xs font-normal">(hai {{ services.length }} scadenze)</span>
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

                        <template v-for="(service, index) in services">

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
                                        {{ __currency(service.total_notax, 'EUR') }}
                                    </span>
                                    <br>
                                    <span class="text-xs">
                                        {{ __currency(service.total_tax, 'EUR') }}
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

                <div class="tab-pane fade"
                     id="nav-incoming"
                     role="tabpanel"
                     aria-labelledby="nav-incoming-tab"
                     tabindex="0">

                    Guadagno per mese

                </div>

                <div class="tab-pane fade"
                     id="nav-profit"
                     role="tabpanel"
                     aria-labelledby="nav-profit-tab"
                     tabindex="0">

                    Utile

                </div>

            </div>

        </ApplicationContainer>

    </AuthenticatedLayout>
</template>
