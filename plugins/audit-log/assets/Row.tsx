import { notify, t } from 'blessing-skin'
import React from 'react'
import type { AuditLog } from './scripts/types'
import { humanizeAction } from './scripts/utils'

interface Props {
  log: AuditLog
}

const Row: React.FC<Props> = (props) => {
  const { log } = props

  return (
    <tr>
      <td>{log.id}</td>
      <td>{log.user_id}</td>
      <td>{humanizeAction(log.action)}</td>
      <td>
        <a
          href="#"
          onClick={() => {
            notify.showModal({
              dangerousHTML: '<p class="text-break">' + log.details + '</p>',
            })
          }}
        >
          {t('audit-log.show-details')}
        </a>
      </td>
      <td>
        {log.ip} ({log.region})
      </td>
      <td title={log.user_agent}>{log.formatted_user_agent}</td>
      <td>{log.created_at}</td>
    </tr>
  )
}

export default Row
