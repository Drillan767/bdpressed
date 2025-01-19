<template>
    <VCard
        prepend-icon="mdi-cog-outline"
        title="Paramètres"
    >
        <template #text>
            <VTabs>
                <VTab
                    prepend-icon="mdi-application-cog-outline"
                    text="Site"
                    value="site"
                />
                <VTab
                    prepend-icon="mdi-palette-outline"
                    text="Illustrations"
                />
            </VTabs>

            <slot />
        </template>
    </VCard>
</template>
