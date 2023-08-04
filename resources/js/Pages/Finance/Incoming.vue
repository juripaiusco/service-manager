<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "../../ComponentsExt/Translations";
import {__currency} from "../../ComponentsExt/Currency";

defineProps({
    data: Object,
});

</script>

<template>

    <Head title="Finanze" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Finanze', 'Entrate']" />

        </template>

        <ApplicationContainer>

            <h2 class="text-2xl text-center mb-6">
                Entrate rispetto lo scorso anno
            </h2>

            <h2 class="text-4xl font-semibold text-center mb-8"
                :class="{
                'text-red-600': data.years_diff < 0,
                'text-green-600': data.years_diff > 0,
                }">
                {{ __currency(data.years_diff, 'EUR') }}
            </h2>

            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th v-for="(month, index) in data.months_list"
                        :class="{'table-primary': index == data.today_month}">
                        {{ __(month) }}
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <tr v-for="months in data.months_incoming">

                    <th>
                        {{ months.y }}
                    </th>

                    <td v-for="(month, index) in months.m"
                        class="text-right"
                        :class="{'table-primary': index == data.today_month}">

                        {{ __currency(month, 'EUR') }}

                    </td>

                    <th>
                        {{ __currency(months.total, 'EUR') }}
                    </th>

                </tr>

                </tbody>
            </table>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
