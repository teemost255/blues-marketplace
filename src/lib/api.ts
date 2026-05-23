async function apiFetch(path: string, options?: RequestInit) {
  const res = await fetch(path, {
    credentials: "include",
    headers: { "Content-Type": "application/json", ...(options?.headers || {}) },
    ...options,
  });
  if (!res.ok) {
    const body = await res.json().catch(() => ({ error: res.statusText }));
    throw new Error(body.error || res.statusText);
  }
  return res.json();
}

export const api = {
  get: (path: string) => apiFetch(path),
  post: (path: string, body?: unknown) => apiFetch(path, { method: "POST", body: body !== undefined ? JSON.stringify(body) : undefined }),
  put: (path: string, body?: unknown) => apiFetch(path, { method: "PUT", body: body !== undefined ? JSON.stringify(body) : undefined }),
  delete: (path: string) => apiFetch(path, { method: "DELETE" }),
};
