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

            <div class="mt-5 mb-12">

                <h2 class="text-[50px] font-semibold text-center"
                    :class="{
                'text-red-600': data!.years_diff < 0,
                'text-green-600': data!.years_diff > 0,
                }">
                    {{ __currency(data!.years_diff, 'EUR', true) }}
                </h2>
                <div class="text-center text-sm">
                    ( rispetto lo scorso anno a fine
                    <span class="font-semibold">{{ __(data!.months_list[data!.today_month]) }}</span> )
                </div>

            </div>

            <table class="table table-sm text-xs lg:text-sm">
                <thead>
                <tr>
                    <th class="hidden lg:table-cell"></th>
                    <th v-for="(month, index) in data!.months_list"
                        class="w-[120px]"
                        :class="{'table-primary': index == data!.today_month}">
                        {{ __(month) }}
                    </th>
                    <th class="w-[140px] hidden lg:table-cell"></th>
                </tr>
                </thead>
                <tbody>

                <tr v-for="months in data!.months_calc">

                    <th class="hidden lg:table-cell">
                        {{ months.y }}
                    </th>

                    <td v-for="(month, index) in months.m"
                        class="text-right align-middle"
                        :class="{'table-primary': index == data!.today_month}">

                        {{ __currency(month, 'EUR') }}

                    </td>

                    <th class="text-right hidden lg:table-cell">
                        {{ __currency(months.total, 'EUR') }}
                    </th>

                </tr>

                </tbody>
            </table>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
