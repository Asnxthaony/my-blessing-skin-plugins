import { fetch, notify } from 'blessing-skin'

function wechat_bind() {
  fetch.get('/mini-login/wechat/bind')
    .then(response => {
      if (response.code === 0) {
        notify.showModal({
          'mode': 'alert',
          'title': '搜索小程序 星登录 扫码绑定',
          'dangerousHTML': '<div align="center"><img src="/mini-login/qrcode/' + response.data.random + '.png" width="240", height="240"></img></div>',
        })
      } else {
        notify.toast.error(response.message)
      }
    })
}

function wechat_login() {
  fetch.get('/mini-login/wechat/login')
    .then(response => {
      if (response.code === 0) {
        notify.showModal({
          'mode': 'alert',
          'title': '搜索小程序 星登录 扫码登录',
          'dangerousHTML': '<div align="center"><img src="/mini-login/qrcode/' + response.data.random + '.png" width="240", height="240"></img></div>',
        })

        let startTime = new Date().getTime()
        let interval = setInterval(() => {
          if (new Date().getTime() - startTime > 3 * 60 * 1000) {
            notify.showModal({
              'mode': 'alert',
              'text': '登录超时'
            }).then(() => {
              location.reload()
            })
            clearInterval(interval)
            return
          }

          fetch.post('/mini-login/wechat/login/check', {
            'ticket': response.data.random
          }).then(response => {
            if (response.code === 0) {
              clearInterval(interval)
              notify.toast.success(response.message)
              location.reload()
            }
          })
        }, 1000)

      } else {
        notify.toast.error(response.message)
      }
    })
}

Object.assign(window, { wechat_bind, wechat_login })
