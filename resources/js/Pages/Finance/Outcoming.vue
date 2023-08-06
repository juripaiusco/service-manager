<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__date} from "@/ComponentsExt/Date";
import {__currency} from "@/ComponentsExt/Currency";
import {__} from "@/ComponentsExt/Translations";
import Table from "@/Components/Table/Table.vue";
import TableSearch from "@/Components/Table/TableSearch.vue";
import Month_details from "@/Pages/Finance/Components/Month_details.vue";
import {ref} from "vue";

defineProps({
    data: Object,
    filters: Object
});

const month_details = ref(0);

</script>

<template>

    <Head title="Finanze" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Finanze', 'Uscite']" />

        </template>

        <ApplicationContainer>

            <div class="mt-5 mb-12">

                <h2 class="text-[50px] font-semibold text-center"
                    :class="{
                'text-red-600': data.years_diff > 0,
                'text-green-600': data.years_diff < 0,
                }">
                    {{ __currency(data.years_diff, 'EUR') }}
                </h2>
                <div class="text-center text-sm">( rispetto lo scorso anno ad oggi )</div>

            </div>

            <table class="table table-sm">
                <thead>
                <tr>
                    <th></th>
                    <th v-for="(month, index) in data!.months_list"
                        class="w-[120px] text-sm"
                        :class="{
                            'table-primary': index == data!.today_month && month_details === 0,
                            'table-info': index == month_details && month_details !== 0,
                        }">

                        <Link :href="route('finance.outcoming', {
                                month_details: index
                              })"
                              @click="month_details = index"
                              preserve-state
                              preserve-scroll>
                            {{ __(month) }}
                        </Link>

                    </th>
                    <th class="w-[140px]"></th>
                </tr>
                </thead>
                <tbody>

                <tr v-for="months in data!.months_calc">

                    <th>
                        {{ months.y }}
                    </th>

                    <td v-for="(month, index) in months.m"
                        class="text-right align-middle !text-sm"
                        :class="{
                            'table-primary': index == data!.today_month && month_details === 0,
                            'table-info': index == month_details && month_details !== 0,
                        }">

                        {{ __currency(month, 'EUR') }}

                    </td>

                    <th class="text-right">
                        {{ __currency(months.total, 'EUR') }}
                    </th>

                </tr>

                </tbody>
            </table>

            <div v-if="month_details !== 0">

                <div class="text-center mt-8">

                    <button class="btn btn-primary w-[120px]"
                            type="button"
                            @click="month_details = 0">
                        Chiudi
                    </button>

                </div>

                <div class="card-group mt-8">

                    <div class="card">
                        <div class="card-header font-bold">

                            {{ data!.this_year }}

                        </div>
                        <div class="card-body">

                            <Month_details :data="data!.month_details"
                                           :year="data!.this_year" />

                        </div>
                    </div>
                    <div class="card !text-gray-400">
                        <div class="card-header">

                            {{ data!.last_year }}

                        </div>
                        <div class="card-body">

                            <Month_details :data="data!.month_details"
                                           :year="data!.last_year"
                                           class-name="!text-gray-400" />

                        </div>
                    </div>

                </div>

            </div>

            <div class="card-group mt-8">
                <div class="card !border-green-600 !border-r-gray-200">
                    <div class="card-header text-center !border-green-600 !text-green-600">Spese nella norma</div>
                    <div class="card-body">

                        <table class="table table-sm">

                            <tbody>
                            <template v-for="category_profit in data.categories_profit.slice().reverse()">

                                <tr v-if="category_profit.profit === true">
                                    <td>

                                        <Link :href="route('finance.outcoming.category', category_profit.categoria)">
                                            {{ category_profit.categoria }}
                                        </Link>

                                    </td>
                                    <td class="text-right">

                                        {{ category_profit.diff ? __currency(category_profit.diff, 'EUR') : '-' }}

                                    </td>
                                </tr>

                            </template>
                            </tbody>

                        </table>

                    </div>
                </div>
                <div class="card !border-red-600">
                    <div class="card-header text-center !border-red-600 !text-red-600">Spese fuori controllo</div>
                    <div class="card-body !pb-0">

                        <table class="table table-sm">

                            <tbody>
                            <template v-for="category_profit in data.categories_profit">

                                <tr v-if="category_profit.profit === false">
                                    <td>

                                        <Link :href="route('finance.outcoming.category', category_profit.categoria)">
                                            {{ category_profit.categoria }}
                                        </Link>

                                    </td>
                                    <td class="text-right">

                                        {{ __currency(category_profit.diff, 'EUR') }}

                                    </td>
                                </tr>

                            </template>
                            </tbody>

                        </table>

                    </div>
                </div>
            </div>

            <hr class="m-10">

            <h2  class="text-4xl text-center mb-6">Fatture</h2>

            <div class="inline-flex w-full mb-6">

                <div class="w-3/4"></div>

                <div class="w-1/4">

                    <TableSearch placeholder="Cerca fattura passiva"
                                 route-search="finance.outcoming"
                                 :filters="filters"></TableSearch>

                </div>

            </div>

            <div class="card">
                <div class="card-header">
                    {{ __date(data!.invoice_this_year[0].data, 'y') }}
                </div>
                <div class="card-body">

                    <Table class="table-sm table-striped text-sm"
                           :data="{
                                filters: filters,
                                tblName: 'invoice',
                                routeSearch: 'finance.outcoming',
                                data: data!.invoice_this_year,
                                structure: [{
                                    class: 'text-left',
                                    label: 'Numero',
                                    field: 'numero',
                                }, {
                                    class: 'text-left',
                                    label: 'Nome',
                                    field: 'nome',
                                }, {
                                    class: 'text-left',
                                    label: 'Tipo',
                                    field: 'tipo_doc',
                                }, {
                                    class: 'text-center',
                                    label: 'Data',
                                    field: 'data',
                                    fnc: function (d) {
                                        return __date(d.data, 'day');
                                    }
                                }, {
                                    class: 'text-right !align-top w-[10%]',
                                    label: 'Importo',
                                    field: 'importo_netto',
                                    fnc: function (d) {

                                        return d.importo_netto > 0 ? __currency(d.importo_netto, 'EUR') : '-'

                                    }
                                }, {
                                    class: 'text-right !align-top w-[10%]',
                                    label: 'IVA',
                                    field: 'importo_iva',
                                    fnc: function (d) {

                                        return d.importo_iva > 0 ? __currency(d.importo_iva, 'EUR') : '-'

                                    }
                                }, {
                                    class: 'text-right !align-top w-[10%]',
                                    label: 'Totale',
                                    field: 'importo_totale',
                                    fnc: function (d) {

                                        return d.importo_totale > 0 ? __currency(d.importo_totale, 'EUR') : '-'

                                    }
                                }]
                    }" />

                </div>
            </div>

            <br>

            <div class="card">
                <div class="card-header">
                    {{ __date(data!.invoice_last_year[0].data, 'y') }}
                </div>
                <div class="card-body">

                    <Table class="table-sm table-striped text-sm"
                           :data="{
                                filters: filters,
                                tblName: 'invoice',
                                routeSearch: 'finance.outcoming',
                                data: data!.invoice_last_year,
                                structure: [{
                                    class: 'text-left',
                                    label: 'Numero',
                                    field: 'numero',
                                }, {
                                    class: 'text-left',
                                    label: 'Nome',
                                    field: 'nome',
                                }, {
                                    class: 'text-left',
                                    label: 'Tipo',
                                    field: 'tipo_doc',
                                }, {
                                    class: 'text-center',
                                    label: 'Data',
                                    field: 'data',
                                    fnc: function (d) {
                                        return __date(d.data, 'day');
                                    }
                                }, {
                                    class: 'text-right !align-top w-[10%]',
                                    label: 'Importo',
                                    field: 'importo_netto',
                                    fnc: function (d) {

                                        return d.importo_netto > 0 ? __currency(d.importo_netto, 'EUR') : '-'

                                    }
                                }, {
                                    class: 'text-right !align-top w-[10%]',
                                    label: 'IVA',
                                    field: 'importo_iva',
                                    fnc: function (d) {

                                        return d.importo_iva > 0 ? __currency(d.importo_iva, 'EUR') : '-'

                                    }
                                }, {
                                    class: 'text-right !align-top w-[10%]',
                                    label: 'Totale',
                                    field: 'importo_totale',
                                    fnc: function (d) {

                                        return d.importo_totale > 0 ? __currency(d.importo_totale, 'EUR') : '-'

                                    }
                                }]
                    }" />

                </div>
            </div>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
