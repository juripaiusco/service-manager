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

            

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
