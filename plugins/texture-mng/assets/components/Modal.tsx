import React, { useState, useEffect, useRef } from 'react'
import $ from 'jquery'
import 'bootstrap'
import { t } from 'blessing-skin'
import ModalHeader from './ModalHeader'
import ModalBody from './ModalBody'
import ModalFooter from './ModalFooter'
import type { Props as HeaderProps } from './ModalHeader'
import type { Props as BodyProps } from './ModalBody'

type BasicOptions = {
  show?: boolean
  input?: string
  children?: React.ReactNode
}

export type ModalOptions = BasicOptions & HeaderProps & BodyProps

type Props = {
  id?: string
  children?: React.ReactNode
  onConfirm?(payload: { value: string }): void
  onDismiss?(): void
  onClose?(): void
}

export type ModalResult = {
  value: string
}

const Modal: React.FC<ModalOptions & Props> = (props) => {
  const {
    title = t('general.tip'),
    input = '',
  } = props

  const [value, setValue] = useState(input)
  const ref = useRef<HTMLDivElement>(null)

  const { show } = props

  useEffect(() => {
    if (!show) {
      return
    }

    const onHidden = () => props.onClose?.()

    const el = $(ref.current!)
    el.on('hidden.bs.modal', onHidden)

    return () => {
      el.off('hidden.bs.modal', onHidden)
    }
  }, [show, props.onClose])

  const handleInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setValue(event.target.value)
  }

  const confirm = () => {
    props.onConfirm?.({ value })
    $(ref.current!).modal('hide')

    // The "hidden.bs.modal" event can't be trigged automatically when testing.
    /* istanbul ignore next */
    if (process.env.NODE_ENV === 'test') {
      $(ref.current!).trigger('hidden.bs.modal')
    }
  }

  const dismiss = () => {
    props.onDismiss?.()
    $(ref.current!).modal('hide')

    /* istanbul ignore next */
    if (process.env.NODE_ENV === 'test') {
      $(ref.current!).trigger('hidden.bs.modal')
    }
  }

  useEffect(() => {
    if (show) {
      setTimeout(() => $(ref.current!).modal('show'), 50)
    }
  }, [show])

  if (!show) {
    return null
  }

  return (
    <div id={props.id} className="modal fade" role="dialog" ref={ref}>
      <div
        className='modal-dialog modal-dialog-centered'
        role="document"
      >
        <div className={`modal-content bg-default`}>
          <ModalHeader title={title} onDismiss={dismiss} />
          <ModalBody
            value={value}
            onChange={handleInputChange}
          >
            {props.children}
          </ModalBody>
          <ModalFooter
            onConfirm={confirm}
            onDismiss={dismiss}
          >
          </ModalFooter>
        </div>
      </div>
    </div>
  )
}

export default Modal
