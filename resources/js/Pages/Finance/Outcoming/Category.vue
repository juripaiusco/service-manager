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
                            'text-red-600': data!.years_diff > 0,
                            'text-green-600': data!.years_diff < 0,
                        }">
                    {{ __currency(data!.years_diff, 'EUR') }}
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
