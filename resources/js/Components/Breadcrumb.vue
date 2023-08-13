<script setup>

defineProps({
    breadcrumbArray: Array,
})

</script>

<template>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li v-for="(breadcrumb, i) in breadcrumbArray"
                class="breadcrumb-item lg:text-xl text-gray-800 dark:text-gray-200"
                :class="{
                'active font-semibold': (i === breadcrumbArray.length - 1),
                '': (i === 0)
                }">

                {{ breadcrumb }}
                <!-- {{ breadcrumb.length > 40 ? breadcrumb.substring(0, 40) + ' ...' : breadcrumb }} -->

            </li>
        </ol>
    </nav>

</template>
