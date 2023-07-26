<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import Checkbox from "@/Components/Checkbox.vue";

const props = defineProps({
    data: Object,
})

const dataForm = Object.fromEntries(Object.entries(props.data).map((v) => {
    return props.data ? v : '';
}));

const form = useForm(dataForm);

</script>

<template>

    <Head title="Servizi" />

    <AuthenticatedLayout>

        <template #header>

            <ApplicationHeader :breadcrumb-array="['Servizi', data.name !== null ? data.name : 'Nuovo Servizio']" />

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

                    </div>
                    <div class="col">

                        <label class="form-label">Nome del servizio visualizzato dal cliente</label>
                        <input type="text"
                               class="form-control"
                               placeholder="es. manutenzione annuale"
                               v-model="form.name_customer_view" />

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

        </ApplicationContainer>

    </AuthenticatedLayout>

</template>
