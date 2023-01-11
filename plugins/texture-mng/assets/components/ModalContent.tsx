import React from 'react'

export interface Props {
  text?: string
}

const ModalContent: React.FC<Props> = (props) => {
  if (props.children) {
    return <>{props.children}</>
  }

  return <></>
}

export default ModalContent
