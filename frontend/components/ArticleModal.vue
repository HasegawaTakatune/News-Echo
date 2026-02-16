<script setup lang="ts">
export interface NewsItem {
  id?: number
  title: string
  post_interval: string
  user_id?: number
  research_prompt?: string
}

const props = defineProps<{
  modelValue: boolean
  news?: NewsItem | null
  userId?: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  save: [item: NewsItem]
}>()

const title = ref(props.news?.title || '')
const postInterval = ref(props.news?.post_interval || '1month')
const userId = ref(props.userId)

const options = [
  { value: '1month', label: '1ヶ月' },
  { value: '3months', label: '3ヶ月' },
  { value: '6months', label: '6ヶ月' },
  { value: '1year', label: '1年' },
]

watch(() => props.news, (n) => {
  title.value = n?.title || ''
  postInterval.value = n?.post_interval || '1month'
}, { immediate: true })

watch(() => props.userId, (id) => { userId.value = id })

const close = () => emit('update:modelValue', false)

const save = () => {
  emit('save', {
    id: props.news?.id,
    title: title.value,
    post_interval: postInterval.value,
    user_id: userId.value,
  })
  close()
}
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
      <div class="modal">
        <h2>{{ news ? 'ニュース更新' : 'ニュース登録' }}</h2>
        <form @submit.prevent="save" class="form">
          <input v-model="title" type="text" placeholder="タイトル" required />
          <select v-model="postInterval">
            <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <input v-if="userId" v-model="userId" type="hidden" />
          <div class="actions">
            <button type="button" class="btn-cancel" @click="close">キャンセル</button>
            <button type="submit" class="btn-save">登録</button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal {
  background: #1a1a2e;
  padding: 1.5rem;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}
h2 { font-size: 1.25rem; margin-bottom: 1rem; }
.form { display: flex; flex-direction: column; gap: 1rem; }
.form input, .form select {
  padding: 0.75rem 1rem;
  border: 1px solid #333;
  border-radius: 0.5rem;
  background: rgba(0, 0, 0, 0.3);
  color: #fff;
}
.actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
.btn-cancel {
  padding: 0.5rem 1rem;
  background: transparent;
  color: #a0a0a0;
  border: 1px solid #444;
  border-radius: 0.5rem;
  cursor: pointer;
}
.btn-save {
  padding: 0.5rem 1rem;
  background: #e94560;
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
}
</style>
