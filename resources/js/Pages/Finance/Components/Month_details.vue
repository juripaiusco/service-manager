<script setup>

import {__currency} from "@/ComponentsExt/Currency";
import Table from "@/Components/Table/Table.vue";

defineProps({
    data: Object,
    year: String,
    className: String,
});

</script>

<template>

    <table class="table table-sm text-sm">

        <thead>
        <tr>
            <th :class="className" class="text-left">Anno</th>
            <th :class="className" class="text-left"></th>
            <th :class="className" class="text-left">Nome</th>
            <th :class="className" class="text-right">Importo</th>
            <th :class="className" class="text-right">IVA</th>
            <th :class="className" class="text-right">Totale</th>
        </tr>
        </thead>
        <tbody>
        <template v-for="invoice in data.month_details_by_name">

            <tr>
                <td :class="className">

                    {{ invoice[year].anno }}

                </td>
                <td class="align-middle">

                    <div v-if="invoice[year - 1]">

                        <div v-if="invoice[year].importo_netto > invoice[year - 1].importo_netto && invoice[year].importo_netto !== 0"
                             class="text-red-600">

                            <svg class="w-4 h-4"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                            </svg>

                        </div>

                        <div v-if="invoice[year].importo_netto < invoice[year - 1].importo_netto && invoice[year].importo_netto !== 0"
                             class="text-green-600">

                            <svg class="w-4 h-4"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                            </svg>

                        </div>

                    </div>

                </td>
                <td :class="className">

                    {{ invoice[year].nome }}

                </td>
                <td :class="className" class="text-right">

                    {{ invoice[year].importo_netto ? __currency(invoice[year].importo_netto, 'EUR') : '-' }}

                </td>
                <td :class="className" class="text-right">

                    {{ invoice[year].importo_iva ? __currency(invoice[year].importo_iva, 'EUR') : '-' }}

                </td>
                <td :class="className" class="text-right">

                    {{ invoice[year].importo_totale ? __currency(invoice[year].importo_totale, 'EUR') : '-' }}

                </td>
            </tr>

        </template>
        </tbody>

    </table>

</template>
