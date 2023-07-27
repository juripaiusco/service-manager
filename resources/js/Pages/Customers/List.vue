<script setup lang="ts">
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

    <Head title="Clienti" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Clienti', 'Lista']" />

        </template>

        <ApplicationContainer>

            <div class="inline-flex w-full mb-6">

                <div class="w-3/4">

                    <Link class="btn btn-dark w-[120px]"
                          :href="route('customer.create')">
                        Nuovo
                    </Link>

                </div>

                <div class="w-1/4">

                    <TableSearch placeholder="Cerca cliente"
                                 route-search="customer.index"
                                 :filters="filters"></TableSearch>

                </div>

            </div>

            <Table class="table-striped"
                   :data="{
                        filters: filters,
                        tblName: 'customer',
                        routeSearch: 'customer.index',
                        data: data!.data,
                        structure: [{
                            class: 'text-left',
                            label: 'Nome',
                            field: 'company',
                            fnc: function (d) {

                                let html = '';
                                html += d.company;
                                html += '<br><span class=\'text-xs\'>Venduti ';
                                html += '<span class=\'font-semibold\'>' + d.email + '</span>';
                                html += '</span>';

                                return html;

                            }
                        }, {
                            class: 'text-right !align-top w-[10%]',
                            classData: '!text-green-600',
                            label: 'Entrate',
                            field: 'price_sell',
                            fnc: function (d) {

                                return d.price_sell > 0 ? __currency(d.price_sell, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%]',
                            classData: '!text-red-600',
                            label: 'Uscite',
                            field: 'price_buy',
                            fnc: function (d) {

                                return d.price_buy > 0 ? __currency(d.price_buy, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%] font-semibold',
                            label: 'Utile',
                            field: 'profit',
                            fnc: function (d) {

                                return d.profit > 0 ? __currency(d.profit, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%]',
                            label: '% Ut.Tot.',
                            field: 'total_customer_profit',
                            fnc: function (d) {

                                let html = '';
                                html += d.total_customer_profit > 0 ? __currency(d.total_customer_profit, 'EUR') : '-';
                                html += '<br>';

                                if (d.total_customer_profit > 0) {

                                    html += '<span class=\'text-xs\'>';
                                    html += parseFloat(
                                        parseFloat(d.total_customer_profit) / parseFloat(d.customers_total_profit) * 100
                                    ).toFixed(2);
                                    html += '%</span>';

                                }

                                return html;

                            }
                        }, {
                            class: 'w-[1%]',
                            classBtn: 'w-[48px] min-h-[48px] !pt-[14px] !pl-[15px] ml-[8px] btn-dark',
                            btnEdit: true,
                            route: 'customer.edit'
                        }, {
                            class: 'w-[1%]',
                            classBtn: 'w-[48px] min-h-[48px] !pt-[5px] !pl-[15px] mr-[8px] btn-dark',
                            btnDel: true,
                            route: 'customer.destroy'
                        }],
                    }"
                   @openModal="(data: any, route: any) => {
                       modalData = data;
                       modalData.confirmURL = route;
                       modalData.confirmBtnClass = 'btn-danger';
                       modalData.confirmBtnText = 'Sì';
                       modalShow = true;
                   }" />

            <ModalReady :show="modalShow"
                        :data="modalData"
                        @close="modalShow = false">

                <template #title>Elimina cliente</template>
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
