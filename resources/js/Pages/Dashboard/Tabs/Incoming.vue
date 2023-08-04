<script setup>

import {__} from "@/ComponentsExt/Translations.js";
import {__date} from "@/ComponentsExt/Date.js";
import {__currency} from "@/ComponentsExt/Currency.js";

const props = defineProps({
    data: Object,
    filters: Object,
});

</script>

<template>

    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="text-left">Mese</th>
            <th class="text-right">Entrate</th>
            <th class="text-right">Uscite</th>
            <th class="text-right">Utile</th>
            <th class="text-right">U. Trimestre</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(month, index) in props.data.months_incoming"
            :class="{
                'table-secondary': index < __date(props.data.today, 'n'),
                'line-through': index < __date(props.data.today, 'n'),
                'table-warning': index == __date(props.data.today, 'n'),
            }">
            <td class="capitalize w-1/2">
                {{ __(props.data.months_array[index]) }}
            </td>
            <td class="text-right"
                :class="{
                    '!text-green-600': index >= __date(props.data.today, 'n'),
                    '!font-semibold': index >= __date(props.data.today, 'n'),
                }">
                {{ __currency(month.incoming, 'EUR') }}
            </td>
            <td class="text-right"
                :class="{
                    '!text-red-600': index >= __date(props.data.today, 'n'),
                    '!font-semibold': index >= __date(props.data.today, 'n'),
                }">
                {{ __currency(month.outcoming, 'EUR') }}
            </td>
            <td class="text-right"
                :class="{
                    '!font-semibold': index >= __date(props.data.today, 'n'),
                }">
                {{ __currency(month.profit, 'EUR') }}</td>
            <td class="text-right align-middle"
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
            <th class="text-right"></th>
        </tr>
        </tfoot>
    </table>

</template>
