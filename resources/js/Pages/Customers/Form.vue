<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import Form from "@/Pages/Services/Form.vue";
import {__} from "@/ComponentsExt/Translations";
import {__currency} from "@/ComponentsExt/Currency";
import {__date} from "@/ComponentsExt/Date";
import Table from "@/Components/Table/Table.vue";
import ModalReady from "@/Components/ModalReady.vue";

const props = defineProps({
    data: Object,
    filters: Object,
    modalShow: false,
    modalData: Object,
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
                    <div class="col-lg-6">

                        <h2 class="text-3xl mb-6">Contatto</h2>

                        <div class="row">
                            <div class="col-lg-7">

                                <label class="form-label">Referente</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.name" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.name">{{ __(form.errors.name) }}</div>

                                <br>

                                <div class="row">
                                    <div class="col-lg">

                                        <label class="form-label">
                                            Cellulare
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.email" />
                                        <div class="text-red-500 text-center"
                                             v-if="form.errors.email">{{ __(form.errors.email) }}</div>

                                        <br class="md:hidden">

                                    </div>
                                    <div class="col-lg">

                                        <label class="form-label">
                                            Telefono
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               v-model="form.email" />
                                        <div class="text-red-500 text-center"
                                             v-if="form.errors.email">{{ __(form.errors.email) }}</div>

                                    </div>
                                </div>

                                <br>

                                <label class="form-label">Email</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.email" />
                                <label class="form-label !text-xs mt-2">( separare con ";" per invio multiplo )</label>
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.email">{{ __(form.errors.email) }}</div>

                            </div>
                            <div class="col-lg">

                                <br class="md:hidden">

                                <label class="form-label">Note</label>
                                <textarea class="form-control h-[216px]"></textarea>
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">

                        <br class="md:hidden">
                        <br class="md:hidden">

                        <h2 class="text-3xl mb-6">Fatturazione</h2>

                        <label class="form-label">Azienda / Nominativo fatturazione</label>
                        <input type="text"
                               class="form-control"
                               v-model="form.company" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.company">{{ __(form.errors.company) }}</div>

                        <br>

                        <div class="row">
                            <div class="col-lg">

                                <label class="form-label">Partita IVA</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.piva" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                            </div>
                            <div class="col-lg">

                                <br class="md:hidden">

                                <label class="form-label">Codice Fiscale</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.piva" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                            </div>
                        </div>

                        <br>

                        <div class="row">
                            <div class="col-lg-6">

                                <label class="form-label">Indirizzo</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.piva" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                            </div>
                            <div class="col-lg-3">

                                <br class="md:hidden">

                                <label class="form-label">Città</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.piva" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                            </div>
                            <div class="col-lg-3">

                                <br class="md:hidden">

                                <label class="form-label">CAP</label>
                                <input type="text"
                                       class="form-control"
                                       v-model="form.piva" />
                                <div class="text-red-500 text-center"
                                     v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="text-right mt-10">

                    <Link class="btn btn-secondary w-[120px]"
                          :href="data.saveRedirect">
                        Annulla
                    </Link>

                    <button type="submit"
                            class="btn btn-success ml-2 w-[120px]">Salva</button>

                </div>

                <div v-if="form.id">

                    <hr class="m-10">

                    <div class="w-full sm:inline-flex">
                        <div class="w-full sm:w-1/2">

                            <h2 class="text-3xl mb-5">
                                Servizi in scadenza
                            </h2>

                        </div>
                        <div class="w-full sm:w-1/2 sm:text-right mb-8 sm:mb-0">

                            <Link class="btn btn-dark w-[120px]"
                                  :href="route('customer.serviceExpiration.create', data.id)">
                                Aggiungi
                            </Link>

                        </div>
                    </div>

                    <Table class="text-sm"
                           :data="{
                            filters: filters,
                            tblName: 'customer_service',
                            routeSearch: 'customer.edit',
                            data: data.customer_service,
                            structure: [{
                                class: 'text-center sm:w-[140px]',
                                label: 'Scadenza',
                                field: 'expiration',
                                order: false,
                                fnc: function (d) {

                                    let html = ''

                                    html += __date(d.expiration, 'day')

                                    return html

                                }
                            }, {
                                class: 'text-left',
                                label: 'Nome',
                                field: 'name',
                                order: false,
                                fnc: function (d) {

                                    let html = ''

                                    html += d.name
                                    html += '<br><span class=\'text-xs\'>'
                                    html += d.reference
                                    html += '</span>'

                                    return html

                                }
                            }, {
                                class: 'text-center w-[120px] hidden sm:table-cell',
                                label: 'Auto Rinnovo',
                                field: 'autorenew',
                                order: false,
                                fnc: function (d) {

                                    let html = ''

                                    html += d.autorenew == 1 ? 'Sì' : 'No'

                                    return html

                                }
                            }, {
                                class: 'text-center w-[120px] hidden sm:table-cell',
                                label: 'Avviso Email',
                                field: 'no_email_alert',
                                order: false,
                                fnc: function (d) {

                                    let html = ''

                                    html += d.no_email_alert == 1 ? 'No' : 'Sì'

                                    return html

                                }
                            }, {
                                class: 'text-right w-[140px] hidden sm:table-cell',
                                label: 'Spesa',
                                field: 'details_sum_price_sell',
                                order: false,
                                fnc: function (d) {

                                    let html = ''

                                    html += __currency(d.details_sum_price_sell, 'EUR')

                                    return html

                                }
                            }, {
                                class: 'w-[1%]',
                                classBtn: 'ml-[8px] btn-dark',
                                btnEdit: true,
                                route: 'customer.serviceExpiration.edit'
                            }, {
                                class: 'w-[1%]',
                                classBtn: 'mr-[8px] btn-dark',
                                btnDel: true,
                                route: 'customer.serviceExpiration.destroy'
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

                        <template #title>Elimina servizio in scadenza</template>
                        <template #body>
                            Vuoi eliminare
                            <br>
                            <span class="font-semibold">
                                {{ modalData.name }}
                                {{ modalData.reference }}
                            </span>
                            ?
                        </template>

                    </ModalReady>

                </div>

            </form>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
