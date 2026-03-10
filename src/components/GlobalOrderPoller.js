/**
 * GlobalOrderPoller – runs in the background (renders nothing).
 * Polls the API every 60 s for order status changes and fires
 * in-app toast notifications via NotificationContext when a status
 * transitions to something new.
 */
import { useEffect, useRef, useCallback } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from '../services/api';
import { useNotification } from '../context/NotificationContext';
import { useCart } from '../context/CartContext';

const ORDERS_KEY = 'pendingOrders';
const POLL_INTERVAL_MS = 60_000; // 60 seconds

export default function GlobalOrderPoller() {
  const { isLoggedIn } = useCart();
  const {
    notifyOrderConfirmed,
    notifyOrderProcessing,
    notifyOrderShipped,
    notifyOrderDelivered,
    notifyOrderCancelled,
    notifyPaymentVerified,
    addNotification,
  } = useNotification();

  // Map of backendOrderId (string) → last known API status
  const prevStatuses = useRef({});

  const fireNotification = useCallback(
    (status, orderRef) => {
      switch (status) {
        case 'confirmed':
          notifyOrderConfirmed(orderRef);
          break;
        case 'processing':
          notifyOrderProcessing(orderRef);
          break;
        case 'shipped':
          notifyOrderShipped(orderRef);
          break;
        case 'out_for_delivery':
          addNotification(`🛵 Order ${orderRef} is out for delivery!`, 'info', 5000);
          break;
        case 'delivered':
          notifyOrderDelivered(orderRef);
          break;
        case 'cancelled':
          notifyOrderCancelled(orderRef);
          break;
        case 'refunded':
          addNotification(`💸 Order ${orderRef} has been refunded.`, 'info', 5000);
          break;
        case 'payment_verified':
          notifyPaymentVerified(orderRef);
          break;
        default:
          break;
      }
    },
    [
      notifyOrderConfirmed,
      notifyOrderProcessing,
      notifyOrderShipped,
      notifyOrderDelivered,
      notifyOrderCancelled,
      notifyPaymentVerified,
      addNotification,
    ]
  );

  const poll = useCallback(async () => {
    if (!isLoggedIn) return;
    try {
      const res = await ApiService.getOrders();
      if (!res?.success || !Array.isArray(res.data)) return;

      // Build a map of local orders so we can look up friendly orderRef strings
      const saved = await AsyncStorage.getItem(ORDERS_KEY);
      const localOrders = saved ? JSON.parse(saved) : [];
      const localById = {};
      localOrders.forEach((o) => {
        if (o.backendOrderId) localById[String(o.backendOrderId)] = o;
      });

      for (const apiOrder of res.data) {
        const id = String(apiOrder.id);
        const newStatus = apiOrder.status;
        const prevStatus = prevStatuses.current[id];
        const orderRef = localById[id]?.orderRef || `ORD-${id}`;

        if (prevStatus !== undefined && prevStatus !== newStatus) {
          // Status changed since last poll → fire toast
          fireNotification(newStatus, orderRef);
        }

        // Always update our cached status
        prevStatuses.current[id] = newStatus;
      }
    } catch (_err) {
      // Silent – never disrupt the user with polling errors
    }
  }, [isLoggedIn, fireNotification]);

  useEffect(() => {
    if (!isLoggedIn) {
      // Clear cached statuses when user logs out so we don't
      // fire stale notifications on the next login
      prevStatuses.current = {};
      return;
    }

    poll(); // immediate first poll to seed prevStatuses

    const intervalId = setInterval(poll, POLL_INTERVAL_MS);
    return () => clearInterval(intervalId);
  }, [isLoggedIn, poll]);

  return null; // this component renders nothing
}
