<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

interface NewsItem {
  id: number
  title: string
  post_interval: string
  created_at: string
}

const api = useApi()
const newsList = ref<NewsItem[]>([])
const pagination = ref<{ current_page: number; last_page: number }>({ current_page: 1, last_page: 1 })
const loading = ref(true)
const showModal = ref(false)
const editNews = ref<NewsItem | null>(null)
const deleteTarget = ref<NewsItem | null>(null)
const user = ref<{ id: number; is_admin?: boolean } | null>(null)

const fetchNews = async (page = 1) => {
  loading.value = true
  try {
    const res = await api.get<{ data: NewsItem[]; current_page: number; last_page: number }>(
      '/news',
      { page: String(page) }
    )
    newsList.value = (res as { data?: NewsItem[] }).data || (res as unknown as NewsItem[])
    if ('current_page' in res) {
      pagination.value = { current_page: res.current_page, last_page: res.last_page }
    }
  } catch {
    newsList.value = []
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editNews.value = null
  showModal.value = true
}

const openEdit = (item: NewsItem) => {
  editNews.value = item
  showModal.value = true
}

const onSave = async (item: { id?: number; title: string; post_interval: string }) => {
  try {
    if (item.id) {
      await api.put(`/news/${item.id}`, { title: item.title, post_interval: item.post_interval })
    } else {
      await api.post('/news', { title: item.title, post_interval: item.post_interval })
    }
    await fetchNews(pagination.value.current_page)
  } catch (e) {
    console.error(e)
  }
}

const confirmDelete = (item: NewsItem) => { deleteTarget.value = item }

const doDelete = async () => {
  if (!deleteTarget.value) return
  try {
    await api.delete(`/news/${deleteTarget.value.id}`)
    deleteTarget.value = null
    await fetchNews(pagination.value.current_page)
  } catch (e) {
    console.error(e)
  }
}

const logout = async () => {
  try {
    await api.post('/auth/logout')
  } catch {}
  const token = useCookie('auth_token')
  token.value = null
  navigateTo('/login')
}

onMounted(async () => {
  const token = useCookie('auth_token')
  if (!token.value) {
    navigateTo('/login')
    return
  }
  try {
    const res = await api.get<{ user?: { id: number } }>('/settings')
    user.value = (res as { user?: { id: number } })?.user ?? null
  } catch {}
  await fetchNews()
})
</script>

<template>
  <div class="dashboard">
    <header class="header">
      <h1>News Echo</h1>
      <div class="header-actions">
        <NuxtLink to="/settings" class="btn">設定</NuxtLink>
        <button class="btn btn-logout" @click="logout">ログアウト</button>
      </div>
    </header>

    <main class="main">
      <div class="toolbar">
        <h2>ニュース一覧</h2>
        <button class="btn btn-primary" @click="openCreate">ニュース登録</button>
      </div>

      <div v-if="loading" class="loading">読み込み中...</div>
      <ul v-else class="news-list">
        <li v-for="item in newsList" :key="item.id" class="news-item">
          <span class="title">{{ item.title }}</span>
          <span class="interval">{{ item.post_interval }}</span>
          <div class="actions">
            <button class="btn-sm" @click="openEdit(item)">編集</button>
            <button class="btn-sm btn-danger" @click="confirmDelete(item)">削除</button>
          </div>
        </li>
      </ul>

      <div v-if="pagination.last_page > 1" class="pagination">
        <button
          :disabled="pagination.current_page <= 1"
          @click="fetchNews(pagination.current_page - 1)"
        >
          前へ
        </button>
        <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <button
          :disabled="pagination.current_page >= pagination.last_page"
          @click="fetchNews(pagination.current_page + 1)"
        >
          次へ
        </button>
      </div>
    </main>
  </div>

  <ArticleModal
    v-model="showModal"
    :news="editNews"
    :user-id="user?.id"
    @save="onSave"
  />

  <Teleport to="body">
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal">
        <h3>削除確認</h3>
        <p>「{{ deleteTarget?.title }}」を削除しますか？</p>
        <div class="actions">
          <button class="btn-cancel" @click="deleteTarget = null">キャンセル</button>
          <button class="btn-danger" @click="doDelete">削除</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.dashboard { max-width: 900px; margin: 0 auto; padding: 2rem; }
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.header h1 { font-size: 1.5rem; }
.header-actions { display: flex; gap: 0.75rem; }
.btn {
  padding: 0.5rem 1rem;
  background: rgba(255,255,255,0.1);
  color: #fff;
  border: 1px solid #444;
  border-radius: 0.5rem;
  text-decoration: none;
  cursor: pointer;
}
.btn-primary { background: #e94560; border-color: #e94560; }
.btn-logout { background: transparent; }
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.news-list { list-style: none; }
.news-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255,255,255,0.03);
  border-radius: 0.5rem;
  margin-bottom: 0.5rem;
}
.news-item .title { flex: 1; }
.news-item .interval { color: #a0a0a0; font-size: 0.9rem; }
.news-item .actions { display: flex; gap: 0.5rem; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.85rem; }
.btn-danger { background: #c62828; border-color: #c62828; }
.loading { padding: 2rem; text-align: center; color: #a0a0a0; }
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal {
  background: #1a1a2e;
  padding: 1.5rem;
  border-radius: 0.75rem;
  max-width: 400px;
}
.modal .actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem; }
.btn-cancel { background: transparent; color: #a0a0a0; border: 1px solid #444; }
</style>
