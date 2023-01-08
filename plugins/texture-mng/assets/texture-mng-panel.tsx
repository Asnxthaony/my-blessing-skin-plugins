import React, { useState } from 'react'
import * as ReactDOM from 'react-dom'
import ModalTextureMng from './ModalTextureMng'

const TextureMngPanel: React.FC = () => {
  const [showModal, setShowModal] = useState(false)
  const [isBan, setIsBan] = useState(false)

  const openWarnModal = () => {
    setIsBan(false)
    setShowModal(true)
  }

  const openBanModal = () => {
    setIsBan(true)
    setShowModal(true)
  }

  const closeModal = () => setShowModal(false)

  return (
    <>
      <div className="card card-secondary">
        <div className="card-header">
          <h3 className="card-title">材质管理</h3>
        </div>
        <div className="card-footer">
          <div className="container d-flex justify-content-between">
            <button className="btn bg-warning" onClick={openWarnModal}>警告</button>
            <button className="btn btn-danger" onClick={openBanModal}>封禁</button>
          </div>
        </div>
      </div>
      <ModalTextureMng
        show={showModal}
        isBan={isBan}
        onClose={closeModal}
      >
      </ModalTextureMng>
    </>
  )
}

ReactDOM.render(<TextureMngPanel />, document.querySelector('#texture-mng-panel'))
