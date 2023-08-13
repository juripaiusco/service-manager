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

    <Head title="Clienti" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Clienti', 'Lista']" />

        </template>

        <ApplicationContainer>

            <div class="inline-flex w-full mb-6">

                <div class="lg:w-3/4 mr-4 lg:mr-0">

                    <Link class="btn btn-dark w-[120px]"
                          :href="route('customer.create')">
                        Nuovo
                    </Link>

                </div>

                <div class="w-full lg:w-1/4">

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
                        data: data.data,
                        structure: [{
                            class: 'text-left',
                            label: 'Nome',
                            field: 'company',
                            fnc: function (d) {

                                let html = ''
                                html += d.company
                                html += '<div class=\'hidden sm:block\'><span class=\'text-xs\'>email: '
                                html += '<span class=\'font-semibold\'>' + d.email + '</span>'
                                html += '</span></div>'

                                return html

                            }
                        }, {
                            class: 'text-right !align-top w-[10%] hidden sm:table-cell',
                            classData: '!text-green-600',
                            label: 'Entrate',
                            field: 'incoming',
                            fnc: function (d) {

                                return d.incoming > 0 ? __currency(d.incoming, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%] hidden sm:table-cell',
                            classData: '!text-red-600',
                            label: 'Uscite',
                            field: 'outcoming',
                            fnc: function (d) {

                                return d.outcoming > 0 ? __currency(d.outcoming, 'EUR') : '-'

                            }
                        }, {
                            class: 'text-right !align-top w-[10%] font-semibold hidden sm:table-cell',
                            label: 'Utile',
                            field: 'profit',
                            fnc: function (d) {

                                let html = ''

                                html += d.profit > 0 ? __currency(d.profit, 'EUR') : '-'
                                html += '<br>'
                                html += '<span class=\'font-normal text-xs\'>'
                                html += 'R. '
                                html += parseFloat((d.incoming - d.outcoming) / d.outcoming * 100).toFixed(2)
                                html += '%</span>'

                                return html

                            }
                        }, {
                            class: 'text-center !align-middle w-[10%] hidden sm:table-cell',
                            label: '% Ut.Tot.',
                            field: 'total_customer_profit',
                            order: false,
                            fnc: function (d) {

                                let html = ''
                                let percentage = parseFloat(d.profit / d.services_total_profit * 100).toFixed(2)

                                if (percentage < 0)
                                {
                                    html += '<div class=\'text-xs text-red-600\'>'
                                } else {
                                    html += '<div class=\'text-xs\'>'
                                }
                                html += percentage
                                html += '%</div>'

                                if (percentage >= 0) {
                                    html += '<div class=\'progress mt-2 m-auto w-[65%] !h-[6px] !bg-sky-100 border border-sky-900\'>'
                                    html += '<div class=\'progress-bar !bg-sky-900\' style=\'width: ' + percentage + '%\'></div>'
                                    html += '</div>'
                                }

                                return html

                            }
                        }, {
                            class: 'w-[1%]',
                            classBtn: 'ml-[8px] btn-dark',
                            btnEdit: true,
                            route: 'customer.edit'
                        }, {
                            class: 'w-[1%]',
                            classBtn: 'mr-[8px] btn-dark',
                            btnDel: true,
                            route: 'customer.destroy'
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
