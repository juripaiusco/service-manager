<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import Table from "@/Components/Table/Table.vue";
import {__currency} from "@/ComponentsExt/Currency";

defineProps({
    data: Object,
    filters: Object,
})
</script>

<template>

    <Head title="Servizi" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Servizi', 'Lista']" />

        </template>

        <ApplicationContainer>

            <Table class="table-striped"
                   :data="{
                        filters: filters,
                        routeSearch: 'products.index',
                        data: data.data,
                        structure: [{
                            class: 'text-left w-[5%]',
                            label: 'Cod.',
                            field: 'fic_cod',
                        }, {
                            class: 'text-left',
                            label: 'Nome',
                            field: 'name',
                        }, {
                            class: 'text-center w-[5%]',
                            label: 'Share',
                            field: 'is_share',
                            fnc: function (d) {

                                let html = '';

                                if (d.is_share == 1) {
                                    html += '<svg class=\'w-6 h-6\' xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'>';
                                    html += '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z\' />';
                                    html += '</svg>';
                                }

                                return html;

                            }
                        }, {
                            class: 'text-right w-[10%]',
                            classData: '!text-green-600',
                            label: 'Entrate',
                            field: 'total_service_sell',
                            fnc: function (d) {

                                return d.total_service_sell > 0 ? __currency(d.total_service_sell, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right w-[10%]',
                            classData: '!text-red-600',
                            label: 'Uscite',
                            field: 'price_buy',
                            fnc: function (d) {

                                return d.price_buy > 0 ? __currency(d.price_buy, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right w-[10%]',
                            label: 'Utile',
                            field: 'total_service_profit',
                            fnc: function (d) {

                                return d.total_service_profit > 0 ? __currency(d.total_service_profit, 'EUR') : '-'

                            }
                        }/*, {
                            class: 'w-[1%]',
                            btnEdit: true,
                            route: 'products.edit'
                        }, {
                            class: 'w-[1%]',
                            btnDel: true,
                            route: 'products.destroy'
                        }*/],
                    }" />

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
