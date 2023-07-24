<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import { __currency } from "@/ComponentsExt/Currency";
import TableSearch from "@/Components/Table/TableSearch.vue";

import Expiration from "@/Pages/Dashboard/Tabs/Expiration.vue";
import Incoming from "@/Pages/Dashboard/Tabs/Incoming.vue";

const props = defineProps({
    data: Object,
    filters: Object,
});

</script>

<template>
    <Head>
        <title>Dashboard</title>
    </Head>

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Dashboard']" />

        </template>

        <ApplicationContainer>

            <div class="inline-flex w-full">

                <div class="w-3/4"></div>

                <div class="w-1/4">

                    <TableSearch placeholder="Cerca servizio o cliente"
                                 route-search="dashboard"
                                 :filters="filters"></TableSearch>

                </div>

            </div>

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

                    <Expiration :data="data"></Expiration>

                </div>

                <div class="tab-pane fade pt-4"
                     id="nav-incoming"
                     role="tabpanel"
                     aria-labelledby="nav-incoming-tab"
                     tabindex="0">

                    <Incoming :data="data"></Incoming>

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

                                            {{ __currency(props.data!.services_total_profit, 'EUR') }}

                                        </div>
                                        <div class="w-1/2">

                                            <div class="inline-flex w-full p-2">
                                                <div class="w-1/2">
                                                    Entrate
                                                </div>
                                                <div class="w-1/2 text-right text-green-600">
                                                    {{ __currency(props.data!.services_total_sell, 'EUR') }}
                                                </div>
                                            </div>

                                            <div class="inline-flex w-full p-2">
                                                <div class="w-1/2">
                                                    Uscite
                                                </div>
                                                <div class="w-1/2 text-right text-red-600">
                                                    {{ __currency(props.data!.services_total_buy, 'EUR') }}
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
                                            {{ props.data!.customers_count }}
                                        </div>
                                        <div>
                                            Clienti attivi
                                        </div>
                                    </div>

                                    <div class="inline-flex w-full p-2">
                                        <div class="w-1/4 font-bold">
                                            {{ props.data!.services_exp!.length }}
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
                                        {{ __currency(props.data!.customers_avg, 'EUR') }}
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
                        <tr v-for="service in props.data!.services">
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

        </ApplicationContainer>

    </AuthenticatedLayout>
</template>

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
