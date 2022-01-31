import React from 'react'
import Skeleton from 'react-loading-skeleton'

const LoadingRow: React.FC = () => (
  <tr>
    <td colSpan={7}>
      <Skeleton />
    </td>
  </tr>
)

export default LoadingRow
