<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "@/ComponentsExt/Translations";
import {__currency} from "@/ComponentsExt/Currency";

defineProps({
    data: Object,
});

</script>

<template>

    <Head title="Finanze" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Finanze', 'Uscite', data!.category]" />

        </template>

        <ApplicationContainer>

            <table class="table table-sm text-sm">
                <thead>
                <tr>
                    <th></th>
                    <th v-for="(month, n) in data.months_list"
                        :class="{
                            'table-primary': data.today_month === n
                        }" >
                        {{ __(month) }}
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(months_data, year) in data.category_count">
                    <th class="text-left">
                        {{ year }}
                    </th>
                    <td v-for="(month_data, n) in months_data['m']"
                        class="text-right"
                        :class="{
                            'table-primary': data.today_month === n
                        }" >
                        {{ month_data ? __currency(month_data, 'EUR') : '-' }}
                    </td>

                    <th class="text-right">
                        {{ __currency(months_data.total, 'EUR') }}
                    </th>
                </tr>
                </tbody>
            </table>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
