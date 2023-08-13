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

                <span class="hidden sm:inline">
                    {{ breadcrumb }}
                </span>
                <span class="sm:hidden">
                    {{ breadcrumb.length > 18 ? breadcrumb.substring(0, 18) + ' ...' : breadcrumb }}
                </span>

            </li>
        </ol>
    </nav>

</template>
