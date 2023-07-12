<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";

defineProps({
    services: Object,
    filters: Object,
});

</script>

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

                    <table class="table">

                        <thead>

                        <tr>
                            <th class="w-[200px]"></th>
                            <th>Cliente</th>
                            <th>Servizio</th>
                            <th>Scadenza</th>
                            <th>Importo</th>
                        </tr>

                        </thead>

                        <tbody>

                        <tr v-for="service in services">
                            <td></td>
                            <td>

                                {{ service.company ? service.company : service.customer.company }}

                                <br>
                                <span class="text-xs">
                                    {{ service.customer_name ? service.customer_name : service.customer.name }}
                                    -
                                    {{ service.email ? service.email : service.customer.email }}
                                </span>
                            </td>
                            <td>

                                {{ service.name }}
                                <br>
                                <span class="text-xs">
                                    {{ service.reference }}
                                </span>

                            </td>
                            <td>

                                {{ service.expiration }}

                            </td>
                            <td></td>
                        </tr>

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
