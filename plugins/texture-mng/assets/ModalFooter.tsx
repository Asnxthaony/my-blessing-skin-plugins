import React from 'react'
import { t } from 'blessing-skin'

interface InternalProps {
  onConfirm?(): void
  onDismiss?(): void
}

const ModalFooter: React.FC<InternalProps> = (props) => {
  return props.children ? (
    <div className='modal-footer'>{props.children}</div>
  ) : (
    <div className='modal-footer'>
      <button
        type="button"
        className={`btn btn-secondary`}
        data-dismiss="modal"
        onClick={props.onDismiss}
      >
        {t('general.cancel')}
      </button>
      <button
        type="button"
        className={`btn btn-primary`}
        onClick={props.onConfirm}
      >
        {t('general.confirm')}
      </button>
    </div>
  )
}

export default ModalFooter
