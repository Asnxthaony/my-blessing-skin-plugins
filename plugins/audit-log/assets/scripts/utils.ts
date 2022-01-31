import { t } from 'blessing-skin'

export function humanizeAction(action: string): string {
  return t('audit-log.actions.' + action)
}
