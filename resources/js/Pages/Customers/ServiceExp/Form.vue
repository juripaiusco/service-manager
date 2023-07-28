<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import ApplicationHeader from "@/Components/ApplicationHeader.vue";
import ApplicationContainer from "@/Components/ApplicationContainer.vue";
import {__} from "@/ComponentsExt/Translations";
import {__date} from "@/ComponentsExt/Date";

const props = defineProps({
    data: Object,
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
                'Servizio',
                data.id ?
                    form.name + ' - ' + form.reference :
                        form.name.length > 0 ? form.name + ' - ' + form.reference : 'Nuovo Cliente'
            ]" />

        </template>

        <ApplicationContainer>

            <form @submit.prevent="form.post(route(
                form.id ? 'customer.update' : 'customer.store',
                form.id ? form.id : ''
                ))">

                <h2 class="text-3xl mb-2">Anagrafica cliente scadenza servizio</h2>

                <br>

                <div class="row">
                    <div class="col">

                        <label class="form-label">Azienda</label>
                        <input type="text"
                               class="form-control"
                               placeholder="es. Pitture Rossi Srl"
                               v-model="form.company" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.company">{{ __(form.errors.company) }}</div>

                    </div>
                    <div class="col">

                        <label class="form-label">P.IVA</label>
                        <input type="text"
                               class="form-control"
                               placeholder="123456789"
                               v-model="form.piva" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.piva">{{ __(form.errors.piva) }}</div>

                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col">

                        <label class="form-label">Referente</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Mario"
                               v-model="form.customer_name" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.customer_name">{{ __(form.errors.customer_name) }}</div>

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

                <h2 class="text-3xl mb-2 mt-3">Dati scadenza servizio</h2>

                <br>

                <div class="row">
                    <div class="col-4">

                        <label class="form-label">Nome servizio in scadenza</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Mario"
                               v-model="form.name" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.name">{{ __(form.errors.name) }}</div>

                    </div>
                    <div class="col">

                        <label class="form-label">Riferimento</label>
                        <input type="text"
                               class="form-control"
                               placeholder="info@pitturerossi.it"
                               v-model="form.reference" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.reference">{{ __(form.errors.reference) }}</div>

                    </div>
                    <div class="col-3">

                        <label class="form-label">Data scadenza</label>
                        <input type="text"
                               class="form-control text-center"
                               placeholder="info@pitturerossi.it"
                               v-model="form.expiration" />
                        <div class="text-red-500 text-center"
                             v-if="form.errors.expiration">{{ form.errors.expiration }}</div>

                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-4">

                        <div class="card">
                            <div class="card-body">

                                <h2 class="text-3xl mb-2">
                                    Lista servizi
                                </h2>

                            </div>
                        </div>

                    </div>
                    <div class="col">

                        <div class="card">
                            <div class="card-body">

                                <h2 class="text-3xl mb-2">
                                    Servizi inseriti nella scadenza
                                </h2>

                                <br>

                                <div v-for="(detail, index) in form.details">

                                    <div class="row mb-2">
                                        <div class="col-5 text-sm pt-1">

                                            {{ detail.service.name }}

                                        </div>
                                        <div class="col-4">

                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   placeholder="info@pitturerossi.it"
                                                   :value="detail.reference" />

                                        </div>
                                        <div class="col-2">

                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">&euro;</span>
                                                <input type="text"
                                                       class="form-control form-control-sm text-right"
                                                       placeholder="0.00"
                                                       :value="detail.price_sell" />
                                            </div>

                                        </div>
                                        <div class="col-1">

                                            <button class="btn btn-sm w-full btn-secondary">

                                                <svg class="w-4 h-4 m-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                                </svg>

                                            </button>

                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

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
