import { fetch, notify } from 'blessing-skin'

function smrz_accept(id: Number) {
  notify.showModal({
    'title': `通过 ID: ${id} 的实名认证`
  }).then(() => {
    fetch.post('/admin/smrz/handle', {
      'id': id,
      'action': 'accept',
    }).then(response => {
      if (response.code === 0) {
        notify.toast.success(response.message)
        location.reload()
      } else {
        notify.toast.error(response.message)
      }
    }).catch(() => {

    })
  }).catch(() => {

  })
}

function smrz_reject(id: Number) {
  let reason: string = ''

  notify.showModal({
    'mode': 'prompt',
    'title': `拒绝 ID: ${id} 的实名认证`,
    'text': '请输入理由：',
    'placeholder': '理由',
  }).then(result => {
    reason = result.value
  }).then(() => {
    fetch.post('/admin/smrz/handle', {
      'id': id,
      'action': 'reject',
      'reason': reason,
    }).then(response => {
      if (response.code === 0) {
        notify.toast.success(response.message)
        location.reload()
      } else {
        notify.toast.error(response.message)
      }
    }).catch(() => {

    })
  })
  .catch(() => {

  })
}

Object.assign(window, { smrz_accept, smrz_reject })
