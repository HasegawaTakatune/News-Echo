export const useApi = () => {
  const config = useRuntimeConfig()
  const token = useCookie('auth_token')

  const apiBase = config.public.apiBase as string

  const request = async <T>(
    path: string,
    options: RequestInit & { params?: Record<string, string>; body?: unknown } = {}
  ): Promise<T> => {
    const { params, body, ...rest } = options
    let url = `${apiBase}${path}`
    if (params) {
      url += '?' + new URLSearchParams(params).toString()
    }
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(options.headers as Record<string, string>),
    }
    if (token.value) {
      headers.Authorization = `Bearer ${token.value}`
    }
    const res = await $fetch<T>(url, { ...rest, headers, body })
    return res
  }

  return {
    get: <T>(path: string, params?: Record<string, string>) =>
      request<T>(path, { method: 'GET', params }),
    post: <T>(path: string, body?: object) =>
      request<T>(path, { method: 'POST', body }),
    put: <T>(path: string, body?: object) =>
      request<T>(path, { method: 'PUT', body }),
    delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
  }
}
