<script setup lang="ts">
definePageMeta({ layout: false })
const token = useCookie('auth_token')

if (token.value) {
  navigateTo('/dashboard', { replace: true })
} else {
  navigateTo('/login', { replace: true })
}
</script>

<template>
  <div>リダイレクト中...</div>
</template>
