import * as React from 'react'
import * as ReactDOM from 'react-dom'
import { fetch, t } from 'blessing-skin'
import { AuditLog, Paginator } from './scripts/types'
import Pagination from './components/Pagination'
import Row from './Row'
import LoadingRow from './LoadingRow'

const AuditLog = () => {
  const [logs, setLogs] = React.useState<AuditLog[]>([])
  const [page, setPage] = React.useState(1)
  const [totalPages, setTotalPages] = React.useState(1)
  const [isLoading, setIsLoading] = React.useState(false)
  const [query, setQuery] = React.useState('')

  const getLogs = async () => {
    setIsLoading(true)
    const { data, last_page }: Paginator<AuditLog> = await fetch.get(
      '/admin/audit-log/list',
      {
        q: query,
        page,
      },
    )
    setLogs(() => data)
    setTotalPages(last_page)
    setIsLoading(false)
  }

  React.useEffect(() => {
    getLogs()
  }, [page])

  const handleQueryChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setQuery(event.target.value)
  }

  const handleSubmitQuery = (event: React.FormEvent) => {
    event.preventDefault()
    getLogs()
  }

  return (
    <>
      <div className="card-header">
        <form className="input-group" onSubmit={handleSubmitQuery}>
          <input
            type="text"
            inputMode="search"
            className="form-control"
            title={t('vendor.datatable.search')}
            value={query}
            onChange={handleQueryChange}
          />
          <div className="input-group-append">
            <button className="btn btn-primary" type="submit">
              {t('vendor.datatable.search')}
            </button>
          </div>
        </form>
      </div>
      <div className="card-body table-responsive p-0">
        <table className={`table ${isLoading ? '' : 'table-striped'}`}>
          <thead>
            <tr>
              <th>#</th>
              <th>UID</th>
              <th>{t('audit-log.header.action')}</th>
              <th>{t('audit-log.header.detail')}</th>
              <th>IP</th>
              <th>{t('audit-log.header.user-agent')}</th>
              <th>{t('audit-log.header.created_at')}</th>
            </tr>
          </thead>
          <tbody>
            {isLoading
              ? new Array(10).fill(null).map((_, i) => <LoadingRow key={i} />)
              : logs.map((log, _i) => <Row key={log.id} log={log} />)}
          </tbody>
        </table>
      </div>
      <div className="card-footer">
        <div className="float-right">
          <Pagination page={page} totalPages={totalPages} onChange={setPage} />
        </div>
      </div>
    </>
  )
}

ReactDOM.render(<AuditLog />, document.querySelector('#audit-log'))
