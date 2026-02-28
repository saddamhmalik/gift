import { createContext, useContext, useState, useCallback, useEffect } from 'react'
import { createOrder, setOrderItem, updateOrderItem, clearOrderItem, getOrder } from '../api/orders'

const OrderContext = createContext(null)

export function OrderProvider({ children }) {
  const [order, setOrder]     = useState(null)
  const [loading, setLoading] = useState(false)
  const [error, setError]     = useState(null)

  // Clear order state when user logs out
  useEffect(() => {
    const handleLogout = () => {
      localStorage.removeItem('order_token')
      setOrder(null)
      setError(null)
    }
    window.addEventListener('auth:logout', handleLogout)
    return () => window.removeEventListener('auth:logout', handleLogout)
  }, [])

  const getToken = () => order?.order_token ?? localStorage.getItem('order_token')
  const saveToken = (token) => { if (token) localStorage.setItem('order_token', token) }

  const ensureOrder = useCallback(async () => {
    if (order) return order
    setLoading(true)
    try {
      const token = localStorage.getItem('order_token')
      const res = await createOrder(token ? { order_token: token } : {})
      saveToken(res.data?.order_token)
      setOrder(res.data)
      return res.data
    } finally {
      setLoading(false)
    }
  }, [order])

  const refreshOrder = useCallback(async () => {
    const token = getToken()
    if (!token) return
    setLoading(true)
    try {
      const res = await getOrder({ order_token: token })
      setOrder(res.data)
      return res.data
    } catch {
      setOrder(null)
    } finally {
      setLoading(false)
    }
  }, [order])

  const addItem = useCallback(async ({
    productId, quantity, unitPrice, selectedDenomination,
    // Gift fields (optional)
    orderMode, deliveryMode,
    giftRecipientName, giftRecipientEmail, giftRecipientPhone, giftMessage,
  }) => {
    setLoading(true)
    setError(null)
    try {
      const o = await ensureOrder()
      const payload = {
        order_token:           o.order_token,
        product_id:            productId,
        quantity,
        unit_price:            unitPrice,
        selected_denomination: selectedDenomination,
        order_mode:            orderMode   || 'SELF',
        delivery_mode:         deliveryMode || (orderMode === 'GIFT' ? 'EMAIL' : 'API'),
      }
      if (orderMode === 'GIFT') {
        if (giftRecipientName)  payload.gift_recipient_name  = giftRecipientName
        if (giftRecipientEmail) payload.gift_recipient_email = giftRecipientEmail
        if (giftRecipientPhone) payload.gift_recipient_phone = giftRecipientPhone
        if (giftMessage)        payload.gift_message         = giftMessage
      }
      const res = await setOrderItem(payload)
      setOrder(res.data)
      return res.data
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to add item'
      setError(msg)
      throw new Error(msg)
    } finally {
      setLoading(false)
    }
  }, [ensureOrder])

  const updateItem = useCallback(async ({ quantity, unitPrice, selectedDenomination }) => {
    setLoading(true)
    setError(null)
    try {
      const res = await updateOrderItem({
        order_token: getToken(),
        quantity,
        unit_price: unitPrice,
        selected_denomination: selectedDenomination,
      })
      setOrder(res.data)
      return res.data
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to update item'
      setError(msg)
      throw new Error(msg)
    } finally {
      setLoading(false)
    }
  }, [])

  const clearItem = useCallback(async () => {
    setLoading(true)
    try {
      const res = await clearOrderItem()
      setOrder(res.data)
    } finally {
      setLoading(false)
    }
  }, [])

  const resetOrder = useCallback(() => {
    localStorage.removeItem('order_token')
    setOrder(null)
  }, [])

  return (
    <OrderContext.Provider value={{ order, loading, error, ensureOrder, refreshOrder, addItem, updateItem, clearItem, resetOrder }}>
      {children}
    </OrderContext.Provider>
  )
}

export const useOrder = () => {
  const ctx = useContext(OrderContext)
  if (!ctx) throw new Error('useOrder must be used inside OrderProvider')
  return ctx
}
