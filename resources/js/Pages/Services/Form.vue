<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "../../ComponentsExt/Translations";
import {__currency} from "@/ComponentsExt/Currency";
import {__date} from "@/ComponentsExt/Date";
import {Collapse} from "vue-collapsed";
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    data: Object,
    customers: Object,
})

const dataForm = Object.fromEntries(Object.entries(props.data).map((v) => {
    return props.data ? v : '';
}));

const form = useForm(dataForm);

function collapse(indexSelected)
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

                <div class="text-right mt-10">

                    <a href="#"
                       onclick="window.history.back(); return false;"
                       class="btn btn-secondary w-[120px]">Annulla</a>

                    <button type="submit"
                            class="btn btn-success ml-2 w-[120px]">Salva</button>

                </div>

            </form>

            <div v-if="form.id">

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
                        <th class="text-right w-[80px]">

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
                                {{ customer.customer_total_buy_notax === 0 ? '-' : __currency(customer.customer_total_buy_notax, 'EUR') }}
                            </td>
                            <td @click="collapse(index)" class="text-right font-semibold"
                                :class="{
                                '!text-red-600': (customer.customer_total_sell_notax - customer.customer_total_buy_notax < 0)
                            }">
                                {{ __currency(customer.customer_total_sell_notax - customer.customer_total_buy_notax, 'EUR') }}
                            </td>
                            <td class="text-right align-middle">

                                <Link class="btn btn-dark"
                                      :href="route('customer.edit', customer.id)">

                                    <svg class="w-4 h-4"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>

                                </Link>

                            </td>
                        </tr>
                        <tr class="!p-0 !m-0 no-hover customer-body"
                            :id="'customer-body-' + index">

                            <td class="!p-0 !m-0 !border-0" colspan="5">

                                <Collapse as="section" :when="Boolean(customer.isExpanded)" class="v-collapse">

                                    <table class="table table-hover customer-detail !mb-0 w-full">

                                        <template v-for="service in customer.customer_service">

                                            <tr v-for="service_detail in service.details">
                                                <td class="text-center w-[140px]">

                                                    <div class="text-sm font-semibold !bg-transparent !border-0">
                                                        <span class="font-normal text-xs">
                                                            scadenza
                                                        </span>
                                                        <br>
                                                        <span class="">
                                                            {{ __date(service.expiration, 'day') }}
                                                        </span>
                                                    </div>

                                                </td>
                                                <td>
                                                    <div class="text-sm font-semibold !bg-transparent !border-0">
                                                        <span class="font-normal text-xs">
                                                            {{ service.name }} {{ service.reference }}
                                                        </span>
                                                        <br>
                                                        {{ service_detail.reference }}
                                                    </div>

                                                </td>
                                                <td class="text-right text-sm w-[120px] p-[8px]">

                                                    {{ service_detail.price_sell === 0 ? '-' : __currency(service_detail.price_sell, 'EUR') }}

                                                </td>
                                                <td class="text-right text-sm w-[120px] p-[8px]">

                                                    <div v-if="service.details_service[0]['is_share'] === 1"
                                                         class="!bg-transparent !border-0 !p-0 !m-0">
                                                        {{ __currency(customer.customer_single_buy_share_notax, 'EUR') }}
                                                    </div>

                                                    <div v-if="!service.details_service[0]['is_share']"
                                                         class="!bg-transparent !border-0 !p-0 !m-0">
                                                        {{ __currency(service.details_service[0]['price_buy'], 'EUR') }}
                                                    </div>

                                                </td>
                                                <td class="text-right text-sm w-[120px] p-[8px]">

                                                    <div v-if="service.details_service[0]['is_share'] === 1"
                                                         class="!bg-transparent !border-0 !p-0 !m-0"
                                                         :class="{
                                                            '!text-red-600': (service_detail.price_sell - customer.customer_single_buy_share_notax) < 0
                                                         }">
                                                        {{ __currency(service_detail.price_sell - customer.customer_single_buy_share_notax, 'EUR') }}
                                                    </div>

                                                    <div v-if="!service.details_service[0]['is_share']"
                                                         class="!bg-transparent !border-0 !p-0 !m-0"
                                                         :class="{
                                                            '!text-red-600': (service_detail.price_sell - service.details_service[0]['price_buy']) < 0
                                                         }">
                                                        {{ __currency(service_detail.price_sell - service.details_service[0]['price_buy'], 'EUR') }}
                                                    </div>

                                                </td>
                                                <td class="text-right text-sm w-[80px] p-[8px]"></td>
                                            </tr>

                                        </template>

                                    </table>

                                </Collapse>

                            </td>
                        </tr>

                    </template>

                    </tbody>
                </table>

            </div>

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
