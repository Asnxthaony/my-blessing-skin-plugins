import React, { useState } from 'react'
import { fetch, route, notify } from 'blessing-skin'
import Modal from './Modal'

interface Props {
  show: boolean
  isBan: boolean
  onClose(): void
}

const ModalTextureMng: React.FC<Props> = (props) => {
  const [reason, setReason] = useState('')

  const handleReasonChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
    setReason(event.target.value)
  }

  const handleConfirm = async () => {
    if (reason === '') {
      notify.toast.error('请选择驳回理由')
      return
    }

    const tidString = route.split('/').pop() as string
    const tid = parseInt(tidString)

    const {
      code,
      message,
    } = await fetch.post('/admin/texture-mng/handle', {
      'tid': tid,
      'action': props.isBan ? 'ban' : 'warn',
      'reason': reason,
    })
    if (code === 0) {
      notify.toast.success(message)
    } else {
      notify.toast.error(message)
    }
  }

  const handleClose = () => {
    setReason('')
    props.onClose()
  }

  return (
    <Modal
      show={props.show}
      title={props.isBan ? '选择封禁理由' : '选择警告理由'}
      onConfirm={handleConfirm}
      onClose={handleClose}
    >
      <div className="form-group">
        <select
          className="form-control"
          id="reason"
          value={reason}
          onChange={handleReasonChange}
        >
          <option value="" selected>----- 请选择理由 -----</option>
          <option value="涉及低俗信息">涉及低俗信息</option>
          <option value="涉及色情信息">涉及色情信息</option>
          <option value="涉及违禁信息">涉及违禁信息</option>
          <option value="涉及不适宜内容">涉及不适宜内容</option>
          <option value="涉及人身攻击信息">涉及人身攻击信息</option>
          <option value="版权方要求">版权方要求</option>
        </select>
      </div>
    </Modal>
  )
}

export default ModalTextureMng
