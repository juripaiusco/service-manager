<script setup lang="ts">

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";

import TableSearch from "@/Components/Table/TableSearch.vue";

import Expiration from "@/Pages/Dashboard/Tabs/Expiration.vue";
import Incoming from "@/Pages/Dashboard/Tabs/Incoming.vue";
import Profit from "@/Pages/Dashboard/Tabs/Profit.vue";

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

                    <Expiration :data="data" />

                </div>

                <div class="tab-pane fade pt-4"
                     id="nav-incoming"
                     role="tabpanel"
                     aria-labelledby="nav-incoming-tab"
                     tabindex="0">

                    <Incoming :data="data" />

                </div>

                <div class="tab-pane fade pt-4"
                     id="nav-profit"
                     role="tabpanel"
                     aria-labelledby="nav-profit-tab"
                     tabindex="0">

                    <Profit :data="data" />

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
