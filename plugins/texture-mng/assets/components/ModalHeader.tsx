import React from 'react'

export interface Props {
  title?: string
}

interface InternalProps {
  onDismiss?(): void
}

const ModalHeader: React.FC<Props & InternalProps> = (props) => {
  return (
    <div className="modal-header">
      <h5 className="modal-title">{props.title}</h5>
      <button
        type="button"
        className="close"
        data-dismiss="modal"
        aria-label="Close"
        onClick={props.onDismiss}
      >
        <span aria-hidden>&times;</span>
      </button>
    </div>
  )
}

export default ModalHeader
