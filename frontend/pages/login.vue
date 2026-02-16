<script setup lang="ts">
definePageMeta({ layout: false, middleware: 'guest' })

const login = ref('')
const password = ref('')
const error = ref('')

const api = useApi()

const handleLogin = async () => {
  error.value = ''
  try {
    const res = await api.post<{ token: string }>('/auth/login', { login: login.value, password: password.value })
    const token = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7 })
    token.value = (res as { token: string }).token
    navigateTo('/dashboard')
  } catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || 'ログインに失敗しました。'
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>News Echo</h1>
      <p class="subtitle">あのニュースは今どうなった！？</p>
      <form @submit.prevent="handleLogin" class="auth-form">
        <div v-if="error" class="error">{{ error }}</div>
        <input v-model="login" type="text" placeholder="ユーザ名またはメールアドレス" required />
        <input v-model="password" type="password" placeholder="パスワード" required />
        <button type="submit">ログイン</button>
        <NuxtLink to="/email" class="link">新規ユーザ登録</NuxtLink>
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
h1 { font-size: 1.75rem; margin-bottom: 0.25rem; }
.subtitle { color: #a0a0a0; margin-bottom: 1.5rem; font-size: 0.9rem; }
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
