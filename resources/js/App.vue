<template>
  <div id="app">
    <AppLayout>
      <router-view />
    </AppLayout>
  </div>
</template>

<script setup>
import AppLayout from './components/layout/AppLayout.vue'
</script>