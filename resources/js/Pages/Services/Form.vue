<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "../../ComponentsExt/Translations";
import {__currency} from "@/ComponentsExt/Currency";
import {__date} from "@/ComponentsExt/Date";
import {Collapse} from "vue-collapsed";

const props = defineProps({
    data: Object,
    customers: Object,
})

const dataForm = Object.fromEntries(Object.entries(props.data).map((v) => {
    return props.data ? v : '';
}));

const form = useForm(dataForm);

function collapse(indexSelected: any)
{
    let customers = props.customers;
    let document = window.document;

    customers.forEach((_, index) => {

        customers[index].isExpanded = indexSelected === index ? !customers[index].isExpanded : false;

        document.getElementById('customer-header-' + index).classList.remove('customer-header-selected');
        document.getElementById('customer-body-' + index).classList.remove('customer-body-selected');

    });

    if (customers[indexSelected].isExpanded === true) {

        document.getElementById('customer-header-' + indexSelected).classList.add('customer-header-selected');
        document.getElementById('customer-body-' + indexSelected).classList.add('customer-body-selected');

    }
}

</script>

<template>

    <Head title="Servizi" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="[
                'Servizi',
                data.id ?
                    form.name :
                        form.name.length > 0 ? form.name : 'Nuovo Servizio'
            ]" />

        </template>

        <ApplicationContainer>

            <form @submit.prevent="form.post(route(
                form.id ? 'service.update' : 'service.store',
                form.id ? form.id : ''
                ))">

                <h2 class="text-3xl mb-2">Dati servizio</h2>

                <br>

                <div class="row">
                    <div class="col">

                        <label class="form-label">Nome del servizio</label>
                        <input type="text"
                               class="form-control"
                               placeholder="es. manutenzione annuale"
                               v-model="form.name" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.name">{{ __(form.errors.name) }}</div>

                    </div>
                    <div class="col-3">

                        <label class="form-label text-right">Prezzo di vendita</label>
                        <div class="input-group">
                            <span class="input-group-text !text-green-600">&euro;</span>
                            <input type="text"
                                   class="form-control text-right !text-green-600"
                                   placeholder="0.00"
                                   v-model="form.price_sell" />
                        </div>
                        <div class="text-red-500 text-center"
                             v-if="form.errors.price_sell">{{ __(form.errors.price_sell) }}</div>

                    </div>
                    <div class="col-3">

                        <label class="form-label text-right">Prezzo di acquisto</label>
                        <div class="input-group">
                            <span class="input-group-text !text-red-600">&euro;</span>
                            <input type="text"
                                   class="form-control text-right !text-red-600"
                                   placeholder="0.00"
                                   v-model="form.price_buy" />
                        </div>
                        <div class="text-red-500 text-center"
                             v-if="form.errors.price_buy">{{ __(form.errors.price_buy) }}</div>

                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-3">

                        <label class="form-label">Codice (fatture in cloud)</label>
                        <input type="text"
                               class="form-control"
                               placeholder="F001"
                               v-model="form.fic_cod" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.fic_cod">{{ __(form.errors.fic_cod) }}</div>

                    </div>
                    <div class="col">

                        <label class="form-label">Nome del servizio visualizzato dal cliente</label>
                        <input type="text"
                               class="form-control"
                               placeholder="es. manutenzione annuale"
                               v-model="form.name_customer_view" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.name_customer_view">{{ __(form.errors.name_customer_view) }}</div>

                    </div>
                </div>

                <br>

                <div class="form-check form-switch">

                    <input class="form-check-input"
                           type="checkbox"
                           name="is_share"
                           id="is_share"
                           true-value="1"
                           false-value="0"
                           v-model="form.is_share">
                    <label class="form-check-label" for="is_share">Servizio condiviso su più clienti</label>

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

            <hr class="m-10">

            <h2 class="text-3xl mb-2">Clienti che utilizzano questo servizio</h2>

            <br>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th class="text-left">
                        Cliente
                    </th>
                    <th class="text-right w-[120px]">
                        Entrate
                    </th>
                    <th class="text-right w-[120px]">
                        Uscite
                    </th>
                    <th class="text-right w-[120px]">
                        Utile
                    </th>
                    <th class="text-right w-[120px]">

                    </th>
                </tr>
                </thead>
                <tbody>

                    <template v-for="(customer, index) in customers">

                        <tr :id="'customer-header-' + index">
                            <td @click="collapse(index)">
                                {{ customer.company }}
                                <br>
                                <span class="text-xs">
                                {{ customer.name }} - {{ customer.email }}
                            </span>
                            </td>
                            <td @click="collapse(index)" class="text-right !text-green-600">
                                {{ customer.customer_total_sell_notax === 0 ? '-' : __currency(customer.customer_total_sell_notax, 'EUR') }}
                            </td>
                            <td @click="collapse(index)" class="text-right !text-red-600">
                                {{ __currency(customer.customer_total_buy_notax, 'EUR') }}
                            </td>
                            <td @click="collapse(index)" class="text-right font-semibold"
                                :class="{
                                '!text-red-600': (customer.customer_total_sell_notax - customer.customer_total_buy_notax < 0)
                            }">
                                {{ __currency(customer.customer_total_sell_notax - customer.customer_total_buy_notax, 'EUR') }}
                            </td>
                            <td class="text-right"></td>
                        </tr>
                        <tr class="!p-0 !m-0 no-hover customer-body"
                            :id="'customer-body-' + index">

                            <td class="!p-0 !m-0 !border-0" colspan="5">

                                <Collapse as="section" :when="Boolean(customer.isExpanded)" class="v-collapse">

                                    <table class="table table-hover customer-detail !mb-0 w-full">

                                        <template v-for="service in customer.customer_service">

                                            <tr v-if="service.details.length > 0">
                                                <td class="text-center w-[140px]">

                                                    <div class="text-sm !bg-transparent !border-0">
                                                        scadenza
                                                        <br>
                                                        {{ __date(service.expiration, 'day') }}
                                                    </div>

                                                </td>
                                                <td>
                                                    {{ service.name }} {{ service.reference }}
                                                    <br>
                                                    {{ service.details[0]['reference'] }}

                                                    {{ service.details.length }}

                                                </td>
                                                <td class="text-right text-sm w-[120px] p-[8px]">

                                                    {{ __currency(service.details[0]['price_sell'], 'EUR') }}

                                                </td>
                                                <td class="text-right text-sm w-[120px] p-[8px]">

                                                    {{ __currency(service.details_service[0]['price_buy'], 'EUR') }}

                                                </td>
                                                <td class="text-right text-sm w-[120px] p-[8px]"></td>
                                                <td class="text-right text-sm w-[120px] p-[8px]"></td>
                                            </tr>

                                        </template>

                                    </table>

                                </Collapse>

                            </td>
                        </tr>

                    </template>

                </tbody>
            </table>

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>

<style>
.customer-detail tr:hover {
    background-color: rgba(0, 0, 0, 0.1);
}
.customer-header,
.customer-header *,
.customer-body,
.customer-body * {
    transition: all .3s !important;
}
.customer-header-selected {
    border-top: 2px solid #38bdf8 !important;
    /*border-right: 2px solid #38bdf8 !important;
    border-left: 2px solid #38bdf8 !important;*/
}
.customer-body-selected {
    border-bottom: 2px solid #38bdf8 !important;
    /*border-right: 2px solid #38bdf8 !important;
    border-left: 2px solid #38bdf8 !important;*/
}
.table-hover > tbody > tr.customer-header-selected > *,
.customer-body-selected tr {
    background-color: rgb(240 249 255) !important;
    --bs-table-bg-state: none;
}
.table-hover > tbody > tr.customer-header-selected:hover > *,
.customer-body-selected tr:hover {
    background-color: white !important;
    --bs-table-bg-state: none;
}

.v-collapse {
    transition: height 300ms cubic-bezier(0.33, 1, 0.68, 1);
}
</style>
