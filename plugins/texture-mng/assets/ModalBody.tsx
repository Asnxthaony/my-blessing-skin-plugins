import React from 'react'
import ModalContent from './ModalContent'
import type { Props as ContentProps } from './ModalContent'

interface InternalProps {
  value?: string
  onChange?: React.ChangeEventHandler<HTMLInputElement>
}

export type Props = ContentProps

const ModalBody: React.FC<InternalProps & Props> = (
  props,
) => {
  return (
    <div className="modal-body">
      <ModalContent text={props.text}>
        {props.children}
      </ModalContent>
    </div>
  )
}

export default ModalBody
