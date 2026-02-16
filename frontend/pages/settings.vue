<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const api = useApi()
const currentPassword = ref('')
const newPassword = ref('')
const newPasswordConfirm = ref('')
const postAccount = ref('')
const researchPrompt = ref('')
const user = ref<{ is_admin?: boolean } | null>(null)
const message = ref('')
const error = ref('')

const fetchSettings = async () => {
  try {
    const res = await api.get<{ user?: { is_admin?: boolean }; post_account?: string; research_prompt?: string }>('/settings')
    user.value = res.user ?? null
    if (res.post_account) postAccount.value = res.post_account
    if (res.research_prompt) researchPrompt.value = res.research_prompt
  } catch {}
}

const updatePassword = async () => {
  error.value = ''
  message.value = ''
  if (newPassword.value !== newPasswordConfirm.value) {
    error.value = 'パスワードが一致しません。'
    return
  }
  try {
    await api.put('/settings/password', {
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: newPasswordConfirm.value,
    })
    message.value = 'パスワードを更新しました。'
    currentPassword.value = ''
    newPassword.value = ''
    newPasswordConfirm.value = ''
  } catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || '更新に失敗しました。'
  }
}

const updatePostAccount = async () => {
  error.value = ''
  message.value = ''
  try {
    await api.put('/settings/post-account', { post_account: postAccount.value })
    message.value = '投稿アカウントを変更しました。'
  } catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || '変更に失敗しました。'
  }
}

const updateResearchPrompt = async () => {
  error.value = ''
  message.value = ''
  try {
    await api.put('/settings/research-prompt', { research_prompt: researchPrompt.value })
    message.value = 'リサーチプロンプトを変更しました。'
  } catch (e: unknown) {
    const err = e as { data?: { message?: string } }
    error.value = err.data?.message || '変更に失敗しました。'
  }
}

onMounted(async () => {
  const token = useCookie('auth_token')
  if (!token.value) {
    navigateTo('/login')
    return
  }
  await fetchSettings()
})
</script>

<template>
  <div class="settings">
    <header class="header">
      <h1>設定</h1>
      <NuxtLink to="/dashboard" class="btn">戻る</NuxtLink>
    </header>

    <main class="main">
      <div v-if="message" class="message">{{ message }}</div>
      <div v-if="error" class="error">{{ error }}</div>

      <section class="section">
        <h2>パスワード更新</h2>
        <form @submit.prevent="updatePassword" class="form">
          <input v-model="currentPassword" type="password" placeholder="現在のパスワード" required />
          <input v-model="newPassword" type="password" placeholder="新しいパスワード" required />
          <input v-model="newPasswordConfirm" type="password" placeholder="新しいパスワード（確認）" required />
          <button type="submit">パスワード更新</button>
        </form>
      </section>

      <section v-if="user?.is_admin" class="section">
        <h2>投稿アカウント変更（管理者）</h2>
        <form @submit.prevent="updatePostAccount" class="form">
          <input v-model="postAccount" type="text" placeholder="投稿アカウント" />
          <button type="submit">変更</button>
        </form>
      </section>

      <section v-if="user?.is_admin" class="section">
        <h2>ニュースのリサーチプロンプト変更（管理者）</h2>
        <form @submit.prevent="updateResearchPrompt" class="form">
          <textarea v-model="researchPrompt" placeholder="リサーチプロンプト" rows="4" />
          <button type="submit">変更</button>
        </form>
      </section>
    </main>
  </div>
</template>

<style scoped>
.settings { max-width: 600px; margin: 0 auto; padding: 2rem; }
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.btn {
  padding: 0.5rem 1rem;
  background: rgba(255,255,255,0.1);
  color: #fff;
  border: 1px solid #444;
  border-radius: 0.5rem;
  text-decoration: none;
  cursor: pointer;
}
.section {
  background: rgba(255,255,255,0.03);
  padding: 1.5rem;
  border-radius: 0.75rem;
  margin-bottom: 1.5rem;
}
.section h2 { font-size: 1.1rem; margin-bottom: 1rem; }
.form { display: flex; flex-direction: column; gap: 0.75rem; }
.form input, .form textarea {
  padding: 0.75rem 1rem;
  border: 1px solid #333;
  border-radius: 0.5rem;
  background: rgba(0,0,0,0.3);
  color: #fff;
}
.form button {
  padding: 0.75rem 1rem;
  background: #e94560;
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
}
.message { color: #4caf50; margin-bottom: 1rem; }
.error { color: #ff6b6b; margin-bottom: 1rem; }
</style>
