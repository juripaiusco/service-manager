<script setup>

defineProps({
    breadcrumbArray: Array,
})

</script>

<template>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li v-for="(breadcrumb, i) in breadcrumbArray"
                class="breadcrumb-item text-xl text-gray-800 dark:text-gray-200"
                :class="{
                'active': (i === breadcrumbArray.length - 1),
                'font-semibold': (i === 0)
                }">

                {{ breadcrumb }}

            </li>
        </ol>
    </nav>

</template>
