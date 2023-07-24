<script setup>

import {__currency} from "@/ComponentsExt/Currency.js";

const props = defineProps({
    data: Object,
    filters: Object,
});

</script>

<template>

    <div class="row">
        <div class="col-6">

            <div class="card">
                <div class="card-body">

                    <div class="inline-flex w-full">

                        <div class="w-1/2 text-3xl font-semibold text-center pt-6">

                            {{ __currency(props.data.services_total_profit, 'EUR') }}

                        </div>
                        <div class="w-1/2">

                            <div class="inline-flex w-full p-2">
                                <div class="w-1/2">
                                    Entrate
                                </div>
                                <div class="w-1/2 text-right text-green-600">
                                    {{ __currency(props.data.services_total_sell, 'EUR') }}
                                </div>
                            </div>

                            <div class="inline-flex w-full p-2">
                                <div class="w-1/2">
                                    Uscite
                                </div>
                                <div class="w-1/2 text-right text-red-600">
                                    {{ __currency(props.data.services_total_buy, 'EUR') }}
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
                            {{ props.data.customers_count }}
                        </div>
                        <div>
                            Clienti attivi
                        </div>
                    </div>

                    <div class="inline-flex w-full p-2">
                        <div class="w-1/4 font-bold">
                            {{ props.data.services_exp.length }}
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
                        {{ __currency(props.data.customers_avg, 'EUR') }}
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
        <tr v-for="service in props.data.services">
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

</template>
