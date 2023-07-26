<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";

defineProps({
    data: Object,
    filters: Object,
})
</script>

<template>

    <Head title="Servizi" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Servizi', 'Nuovo']" />

        </template>

        <ApplicationContainer>

            Form servizi

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
