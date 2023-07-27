<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import Form from "@/Pages/Services/Form.vue";
import {__} from "@/ComponentsExt/Translations";
import {__currency} from "@/ComponentsExt/Currency";
import {__date} from "@/ComponentsExt/Date";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    data: Object,
    filters: Object,
})

const dataForm = Object.fromEntries(Object.entries(props.data).map((v) => {
    return props.data ? v : '';
}));

const form = useForm(dataForm);

</script>

<template>

    <Head title="Clienti" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="[
                'Clienti',
                data.id ?
                    form.company :
                        form.company.length > 0 ? form.company : 'Nuovo Cliente'
            ]" />

        </template>

        <ApplicationContainer>

            <form @submit.prevent="form.post(route(
                form.id ? 'customer.update' : 'customer.store',
                form.id ? form.id : ''
                ))">

                <div class="row">
                    <div class="col">

                        <h2 class="text-3xl mb-2">Dati cliente</h2>

                        <br>

                        <label class="form-label">Azienda</label>
                        <input type="text"
                               class="form-control"
                               placeholder="es. Pitture Rossi Srl"
                               v-model="form.company" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.company">{{ __(form.errors.company) }}</div>

                        <br>

                        <div class="row">
                            <div class="col">

                                <label class="form-label">Referente</label>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Mario"
                                       v-model="form.name" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.name">{{ __(form.errors.name) }}</div>

                            </div>
                            <div class="col">

                                <label class="form-label">Email</label>
                                <input type="text"
                                       class="form-control"
                                       placeholder="info@pitturerossi.it"
                                       v-model="form.email" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.email">{{ __(form.errors.email) }}</div>

                            </div>
                        </div>

                        <br>

                        <label class="form-label">P.IVA</label>
                        <input type="text"
                               class="form-control"
                               placeholder="123456789"
                               v-model="form.piva" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                    </div>
                    <div class="col">

                        <h2 class="text-3xl mb-5">Servizi in scadenza</h2>

                        <Table class=""
                               :data="{
                                    filters: filters,
                                    tblName: 'customer_service',
                                    routeSearch: 'customer.index',
                                    data: data!.customer_service,
                                    structure: [{
                                        class: 'text-left',
                                        label: 'Nome',
                                        field: 'name',
                                        fnc: function (d) {

                                            let html = ''

                                            html += d.name
                                            html += '<br><span class=\'text-xs\'>'
                                            html += d.reference
                                            html += '</span>'

                                            return html

                                        }
                                    }, {
                                        class: 'text-center',
                                        label: 'Scadenza',
                                        field: 'expiration',
                                        fnc: function (d) {

                                            let html = ''

                                            html += __date(d.expiration, 'day')

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
                               @openModal="(data: any, route: any) => {
                                   modalData = data;
                                   modalData.confirmURL = route;
                                   modalData.confirmBtnClass = 'btn-danger';
                                   modalData.confirmBtnText = 'Sì';
                                   modalShow = true;
                               }" />

                    </div>
                </div>

                <br>

                <div class="text-right mt-8">

                    <a href="#"
                       onclick="window.history.back(); return false;"
                       class="btn btn-secondary w-[100px]">Annulla</a>

                    <button type="submit"
                            class="btn btn-success ml-2 w-[100px]">Salva</button>

                </div>

            </form>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
