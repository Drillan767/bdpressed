<template>
    <RouterView />

    <p>
        <RouterLink to="/login">
            Connexion
        </RouterLink>
    </p>
    <p>
        <RouterLink to="/administration">
            Administration
        </RouterLink>
    </p>

    <p>
        <RouterLink to="/">
            Accueil
        </RouterLink>
    </p>
</template>
