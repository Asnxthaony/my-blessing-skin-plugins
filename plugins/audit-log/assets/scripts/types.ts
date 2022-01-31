export type AuditLog = {
  id: number
  user_id: number
  action: string
  details: string
  ip: string
  region: string
  user_agent: string
  formatted_user_agent: string
  created_at: string
}

export type Paginator<T> = {
  data: T[]
  current_page: number
  last_page: number
  from: number
  to: number
  total: number
}
