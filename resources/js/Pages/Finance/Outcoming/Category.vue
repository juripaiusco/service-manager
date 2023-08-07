<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "@/ComponentsExt/Translations";
import {__currency} from "@/ComponentsExt/Currency";
import {__date} from "@/ComponentsExt/Date";
import Table from "@/Components/Table/Table.vue";
import {ref} from "vue";

defineProps({
    data: Object,
    filters: Object
});

const year_month_selected = ref(null);

</script>

<template>

    <Head title="Finanze" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Finanze', 'Uscite', data!.category]" />

        </template>

        <ApplicationContainer>

            <div class="mt-5 mb-12">

                <Link class="btn btn-light absolute mt-[-20px]"
                      :href="route('finance.outcoming')"
                      preserve-state
                      preserve-scroll >

                    <svg class="w-6 h-6"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>

                </Link>

                <h2 class="text-[50px] font-semibold text-center"
                    :class="{
                            'text-red-600': data!.category_diff > 0,
                            'text-green-600': data!.category_diff < 0,
                        }">
                    {{ __currency(data!.category_diff, 'EUR') }}
                </h2>
                <div class="text-center text-sm">
                    ( rispetto lo scorso anno a fine
                    <span class="font-semibold">{{ __(data!.months_list[data!.today_month]) }}</span> )
                </div>

            </div>

            <table class="table table-sm text-sm">
                <thead>
                <tr>
                    <th></th>
                    <th v-for="(month, n) in data!.months_list"
                        :class="{
                            'table-primary': data!.today_month === n
                        }" >
                        {{ __(month) }}
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(months_data, year) in data!.category_count">
                    <th class="text-left">
                        {{ year }}
                    </th>
                    <td v-for="(month_data, n) in months_data['m']"
                        class="text-right"
                        :class="{
                            'table-primary': data!.today_month === n
                        }" >
                        <a :href="'#' + year + '-' + n"
                           @click="year_month_selected=year + '-' + n">
                            {{ month_data ? __currency(month_data, 'EUR') : '-' }}
                        </a>
                    </td>

                    <th class="text-right">
                        {{ __currency(months_data!.total, 'EUR') }}
                    </th>
                </tr>
                </tbody>
            </table>

            <template v-for="(invoices_data, year) in data!.invoices">

                <br>

                <div class="card">
                    <div class="card-header">{{ year }}</div>
                    <div class="card-body">

                        <table class="table table-sm text-sm">
                            <thead>
                            <tr>
                                <th class="text-left w-[140px]">
                                    Numero
                                </th>
                                <th class="text-left">
                                    Nome
                                </th>
                                <th class="text-center w-[80px]">
                                    Tipo
                                </th>
                                <th class="text-center w-[80px]">
                                    Data
                                </th>
                                <th class="text-right !align-top w-[100px]">
                                    Importo
                                </th>
                                <th class="text-right !align-top w-[100px]">
                                    IVA
                                </th>
                                <th class="text-right !align-top w-[100px]">
                                    Totale
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="invoice in invoices_data"
                                :id="year + '-' + __date(invoice.data, 'n')"
                                :class="{
                                    'year-month-selected': year_month_selected == year + '-' + __date(invoice.data, 'n')
                                }">
                                <td>
                                    {{ invoice.numero }}
                                </td>
                                <td>
                                    {{ invoice.nome }}
                                </td>
                                <td class="text-center">
                                    {{ invoice.tipo_doc }}
                                </td>
                                <td class="text-center">
                                    {{ __date(invoice.data, 'day') }}
                                </td>
                                <td class="text-right">
                                    {{ __currency(invoice.importo_netto, 'EUR') }}
                                </td>
                                <td class="text-right">
                                    {{ __currency(invoice.importo_iva, 'EUR') }}
                                </td>
                                <td class="text-right">
                                    {{ __currency(invoice.importo_totale, 'EUR') }}
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- <Table class="table-sm table-striped text-sm"
                               :data="{
                                filters: filters,
                                tblName: 'invoice',
                                routeSearch: 'finance.outcoming',
                                data: invoices_data,
                                structure: [{
                                    class: 'text-left w-[140px]',
                                    label: 'Numero',
                                    field: 'numero',
                                }, {
                                    class: 'text-left',
                                    label: 'Nome',
                                    field: 'nome',
                                }, {
                                    class: 'text-left w-[40px]',
                                    label: 'Tipo',
                                    field: 'tipo_doc',
                                }, {
                                    class: 'text-center w-[40px]',
                                    label: 'Data',
                                    field: 'data',
                                    fnc: function (d) {
                                        return __date(d.data, 'day');
                                    }
                                }, {
                                    class: 'text-right !align-top w-[100px]',
                                    label: 'Importo',
                                    field: 'importo_netto',
                                    fnc: function (d) {

                                        return d.importo_netto > 0 ? __currency(d.importo_netto, 'EUR') : '-'

                                    }
                                }, {
                                    class: 'text-right !align-top w-[100px]',
                                    label: 'IVA',
                                    field: 'importo_iva',
                                    fnc: function (d) {

                                        return d.importo_iva > 0 ? __currency(d.importo_iva, 'EUR') : '-'

                                    }
                                }, {
                                    class: 'text-right !align-top w-[100px]',
                                    label: 'Totale',
                                    field: 'importo_totale',
                                    fnc: function (d) {

                                        return d.importo_totale > 0 ? __currency(d.importo_totale, 'EUR') : '-'

                                    }
                                }]
                    }" /> -->

                    </div>
                </div>

            </template>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>

<style>
.year-month-selected td {
    background-color: lightblue !important;
}
</style>
