import { base_url, event, route } from 'blessing-skin'

const searchParams = new URLSearchParams(location.search)

const provider = searchParams.get('provider')
const token = searchParams.get('token')

event.on('mounted', () => {
  if (token) {
    const div = document.createElement('div')
    div.className = 'alert alert-warning'

    switch (provider) {
      case 'mcbbs':
        div.textContent = '该 MCBBS 账号尚未关联 星域联盟 Skin 账号，请注册新账号或登录现有账号来绑定。'
        break;
      case 'qq':
        div.textContent = '该 QQ 账号尚未关联 星域联盟 Skin 账号，请注册新账号或登录现有账号来绑定。'
        break;
    }

    setTimeout(() => {
      document.querySelector('.login-box-msg')?.after(div)

      if (route === 'auth/register') {
        document.querySelector('.d-flex:last-child > a')?.setAttribute('href', `${base_url}/auth/login?token=${token}`)
      } else if (route === 'auth/login') {
        document.querySelector('.mt-3 > a')?.setAttribute('href', `${base_url}/auth/register?token=${token}`)
      }
    }, 0)
  }
})

event.on('beforeFetch', (request: { data: Record<string, string> }) => {
  if (token) {
    request.data.token = token
  }
})
