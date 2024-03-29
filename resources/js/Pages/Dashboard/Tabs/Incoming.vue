<script setup>

import {__} from "@/ComponentsExt/Translations.js";
import {__date} from "@/ComponentsExt/Date.js";
import {__currency} from "@/ComponentsExt/Currency.js";
import Modal from "@/Components/Modal.vue";
import {ref} from "vue";

const props = defineProps({
    data: Object,
    filters: Object,
});

const modalCostsExp = ref({
    index: 0,
    show: false,
});

</script>

<template>

    <table class="table table-striped table-hover text-sm sm:text-base">
        <thead>
        <tr>
            <th class="text-left">Mese</th>
            <th class="text-right">Entrate</th>
            <th class="text-right">Uscite</th>
            <th class="text-right">Utile</th>
            <th class="text-right hidden sm:table-cell">U. Trimestre</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(month, index) in props.data.months_incoming"
            :class="{
                'table-secondary': index < __date(props.data.today, 'n'),
                'line-through': index < __date(props.data.today, 'n'),
                'table-warning': parseInt(index) === parseInt(__date(props.data.today, 'n')),
            }"
            @click="() => {
                modalCostsExp.show = true
                modalCostsExp.index = index
            }">
            <td class="capitalize w-1/2">
                {{ __(props.data.months_array[index]) }}
            </td>
            <td class="text-right"
                :class="{
                    '!text-green-600': index >= __date(props.data.today, 'n'),
                    '!font-semibold': index == __date(props.data.today, 'n'),
                }">
                {{ __currency(month.incoming, 'EUR') }}
            </td>
            <td class="text-right"
                :class="{
                    '!text-red-600': index >= __date(props.data.today, 'n'),
                    '!font-semibold': index == __date(props.data.today, 'n'),
                }">
                {{ __currency(month.outcoming, 'EUR') }}
            </td>
            <td class="text-right"
                :class="{
                    '!font-semibold': index == __date(props.data.today, 'n'),
                }">
                {{ __currency(month.profit, 'EUR') }}</td>
            <td class="text-right align-middle hidden sm:table-cell"
                v-if="index % 3 === 1"
                :rowspan="index % 3 === 1 ? 3 : ''">
                {{ __currency(props.data.trim_incoming[index], 'EUR') }}
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <th></th>
            <th class="text-right">
                {{ __currency(props.data.services_total_sell, 'EUR') }}
            </th>
            <th class="text-right">
                {{ __currency(props.data.services_total_buy, 'EUR') }}
            </th>
            <th class="text-right">
                {{ __currency(props.data.services_total_profit, 'EUR') }}
            </th>
            <th class="text-right hidden sm:table-cell"></th>
        </tr>
        </tfoot>
    </table>

    <Modal :show="modalCostsExp.show"
           max-width="max"
           @close="modalCostsExp.show = false">

        <div class="p-8 dark:text-white">

            <div class="inline-flex w-full">

                <div class="w-3/4 font-semibold">

                    Pagamenti di {{ __(props.data.months_array[modalCostsExp.index]) }}

                </div>

                <div class="w-1/4 text-right">

                    <button @click="modalCostsExp.show = false">

                        <svg class="w-5 h-5"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>

                    </button>

                </div>

            </div>

            <div class="mt-6">

                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th class="text-left">Nome</th>
                        <th class="text-right w-1/3 sm:w-1/6">Totale</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="service in props.data.months_incoming[modalCostsExp.index].details"
                        :class="{
                            'table-info': service.pref
                        }">
                        <td class="text-sm sm:text-base">

                            <span>
                                {{ service.name }}
                            </span>

                            <br>

                            <div class="hidden sm:block">
                                <span v-if="service.pref"
                                      class="text-xs italic font-bold">{{ service.pref }}: </span>

                                <span class="text-xs">{{ service.references }}</span>
                            </div>

                        </td>
                        <td class="text-right font-semibold">
                            {{ __currency(service.price_buy_total, 'EUR') }}
                            <br>
                            <span class="text-xs font-normal">
                                ( {{ service.amount }} x {{ __currency(service.price_buy, 'EUR') }} )
                            </span>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th class="text-left !pt-4 !border-b-0">
                            Totale
                        </th>
                        <th class="text-right !pt-4 !border-b-0">
                            {{ __currency(props.data.months_incoming[modalCostsExp.index].outcoming, 'EUR') }}
                        </th>
                    </tr>
                    </tfoot>
                </table>

            </div>

        </div>

    </Modal>

</template>
