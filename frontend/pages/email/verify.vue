<script setup lang="ts">
definePageMeta({ layout: false, middleware: 'guest' })

const route = useRoute()
const email = ref((route.query.email as string) || '')
const token = ref('')
const error = ref('')

const api = useApi()

const handleSubmit = async () => {
  error.value = ''
  if (token.value.length !== 5) {
    error.value = '5桁の確認コードを入力してください。'
    return
  }
  try {
    await api.post('/auth/email/verify', { email: email.value, token: token.value })
    navigateTo({ path: '/register', query: { email: email.value } })
  } catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || '確認に失敗しました。'
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>メールアドレス確認</h1>
      <p class="email-display">{{ email }}</p>
      <form @submit.prevent="handleSubmit" class="auth-form">
        <div v-if="error" class="error">{{ error }}</div>
        <input
          v-model="token"
          type="text"
          placeholder="5桁の確認コード"
          maxlength="5"
          pattern="[A-Za-z0-9]{5}"
        />
        <button type="submit">確認</button>
        <NuxtLink to="/email" class="link">戻る</NuxtLink>
      </form>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.auth-card {
  background: rgba(255, 255, 255, 0.05);
  padding: 2.5rem;
  border-radius: 1rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}
h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
.email-display { color: #a0a0a0; margin-bottom: 1.5rem; font-size: 0.9rem; }
.auth-form { display: flex; flex-direction: column; gap: 1rem; }
.auth-form input {
  padding: 0.75rem 1rem;
  border: 1px solid #333;
  border-radius: 0.5rem;
  background: rgba(0, 0, 0, 0.3);
  color: #fff;
}
.auth-form button {
  padding: 0.75rem 1rem;
  background: #e94560;
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
}
.auth-form button:hover { background: #ff6b6b; }
.error { color: #ff6b6b; font-size: 0.9rem; }
.link { color: #4fc3f7; text-decoration: none; text-align: center; margin-top: 0.5rem; }
.link:hover { text-decoration: underline; }
</style>
