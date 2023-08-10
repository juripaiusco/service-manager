<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {Link} from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import {__currency} from "@/ComponentsExt/Currency";
import TableSearch from "@/Components/Table/TableSearch.vue";
import ModalReady from "@/Components/ModalReady.vue";

defineProps({
    data: Object,
    filters: Object,
    modalShow: false,
    modalData: Object,
});

</script>

<template>

    <Head title="Servizi" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Servizi', 'Lista']" />

        </template>

        <ApplicationContainer>

            <div class="inline-flex w-full mb-6">

                <div class="w-3/4">

                    <Link class="btn btn-dark w-[120px]"
                          :href="route('service.create')">
                        Nuovo
                    </Link>

                </div>

                <div class="w-1/4">

                    <TableSearch placeholder="Cerca servizio"
                                 route-search="service.index"
                                 :filters="filters"></TableSearch>

                </div>

            </div>

            <Table class="table-striped"
                   :data="{
                        filters: filters,
                        tblName: 'service',
                        routeSearch: 'service.index',
                        data: data.data,
                        structure: [{
                            class: 'text-left !align-top w-[5%] hidden sm:table-cell',
                            label: 'Cod.',
                            field: 'fic_cod',
                        }, {
                            class: 'text-left',
                            label: 'Nome',
                            field: 'name',
                            fnc: function (d) {

                                let html = '';
                                html += d.name;
                                html += '<br><span class=\'text-xs\'>Venduti ';
                                html += '<span class=\'font-semibold\'>' + d.customers_count + '</span>';
                                html += '</span>';

                                return html;

                            }
                        }, {
                            class: 'text-center !align-middle w-[5%] hidden lg:table-cell',
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
                            class: 'text-right !align-top w-[10%] hidden sm:table-cell',
                            classData: '!text-green-600',
                            label: 'Entrate',
                            field: 'price_sell',
                            fnc: function (d) {

                                return d.price_sell > 0 ? __currency(d.price_sell, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%] hidden sm:table-cell',
                            classData: '!text-red-600',
                            label: 'Uscite',
                            field: 'price_buy',
                            fnc: function (d) {

                                return d.price_buy > 0 ? __currency(d.price_buy, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%] font-semibold hidden lg:table-cell',
                            label: 'Utile',
                            field: 'profit',
                            fnc: function (d) {

                                return d.profit > 0 ? __currency(d.profit, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%] hidden sm:table-cell',
                            label: '%&nbsp;Ut.Tot.',
                            field: 'total_service_profit',
                            fnc: function (d) {

                                let html = '';
                                html += d.total_service_profit > 0 ? __currency(d.total_service_profit, 'EUR') : '-';
                                html += '<br>';

                                if (d.total_service_profit > 0) {

                                    html += '<span class=\'text-xs\'>';
                                    html += parseFloat(
                                        parseFloat(d.total_service_profit) / parseFloat(d.services_total_profit) * 100
                                    ).toFixed(2);
                                    html += '%</span>';

                                }

                                return html;

                            }
                        }, {
                            class: 'w-[1%]',
                            // classBtn: 'w-[48px] min-h-[48px] !pt-[14px] !pl-[15px] ml-[8px] btn-dark',
                            classBtn: 'ml-[8px] btn-dark',
                            btnEdit: true,
                            route: 'service.edit'
                        }, {
                            class: 'w-[1%]',
                            // classBtn: 'w-[48px] min-h-[48px] !pt-[5px] !pl-[15px] mr-[8px] btn-dark',
                            classBtn: 'mr-[8px] btn-dark',
                            btnDel: true,
                            route: 'service.destroy'
                        }],
                    }"
                   @openModal="(data, route) => {
                       modalData = data;
                       modalData.confirmURL = route;
                       modalData.confirmBtnClass = 'btn-danger';
                       modalData.confirmBtnText = 'Sì';
                       modalShow = true;
                   }" />

            <ModalReady :show="modalShow"
                        :data="modalData"
                        @close="modalShow = false">

                <template #title>Elimina servizio</template>
                <template #body>
                    Vuoi eliminare
                    <span class="font-semibold">
                        {{ modalData.name }}
                    </span>
                    ?
                </template>

            </ModalReady>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
