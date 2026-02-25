import { useState, useCallback } from 'react';
import { Alert } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from './src/services/api';

const ORDERS_KEY = 'pendingOrders';

export const useOrders = () => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const normalizeStatus = (status) => {
    const map = {
      pending: 'pending',
      pending_payment: 'pending',
      payment_verified: 'pending',
      pending_confirmation: 'pending',
      confirmed: 'processing',
      processing: 'processing',
      shipped: 'shipped',
      delivered: 'delivered',
      completed: 'completed',
      cancelled: 'cancelled',
    };
    return map[status] || 'pending';
  };

  const loadOrders = useCallback(async () => {
    setLoading(true);
    try {
      // Load local orders from AsyncStorage
      const savedOrders = await AsyncStorage.getItem(ORDERS_KEY);
      const localOrders = savedOrders ? JSON.parse(savedOrders) : [];

      // Also fetch orders from the API (server is source of truth)
      let apiOrders = [];
      try {
        const res = await ApiService.getOrders();
        if (res?.success && Array.isArray(res.data)) {
          apiOrders = res.data;
        }
      } catch (err) {
        console.warn('[useOrders] Failed to fetch from API:', err?.message || err);
      }

      // Build a map of local orders keyed by backendOrderId
      const localByBackendId = {};
      localOrders.forEach((o) => {
        if (o.backendOrderId) localByBackendId[o.backendOrderId] = o;
      });

      // Merge: update local orders with API data, and add any API-only orders
      const seenBackendIds = new Set();

      // First pass – refresh local orders that have a backend id
      const refreshedLocal = localOrders.map((order) => {
        if (!order.backendOrderId) return order;
        seenBackendIds.add(order.backendOrderId);
        const apiOrder = apiOrders.find((a) => String(a.id) === String(order.backendOrderId));
        if (apiOrder) {
          return {
            ...order,
            status: normalizeStatus(apiOrder.status),
            paymentStatus: apiOrder.payment_status || order.paymentStatus,
            total: apiOrder.total_amount ?? apiOrder.total ?? order.total,
            subtotal: apiOrder.subtotal ?? order.subtotal,
            shippingFee: apiOrder.shipping_fee ?? order.shippingFee,
          };
        }
        return order;
      });

      // Second pass – add API orders not yet in local storage
      const newFromApi = apiOrders
        .filter((a) => !seenBackendIds.has(String(a.id)))
        .map((apiOrder) => ({
          orderRef: `ORD-${apiOrder.id}`,
          backendOrderId: apiOrder.id,
          status: normalizeStatus(apiOrder.status),
          paymentMethod: apiOrder.payment_method || 'unknown',
          paymentStatus: apiOrder.payment_status || 'pending',
          total: apiOrder.total_amount ?? apiOrder.total ?? 0,
          subtotal: apiOrder.subtotal ?? 0,
          shippingFee: apiOrder.shipping_fee ?? 0,
          items: (apiOrder.items || []).map((item) => ({
            id: item.product_id || item.id,
            name: item.product?.name || item.name || 'Product',
            price: item.price,
            quantity: item.quantity,
            image: item.product?.image || null,
          })),
          date: apiOrder.created_at || new Date().toISOString(),
        }));

      const mergedOrders = [...refreshedLocal, ...newFromApi];

      // Persist merged data back
      await AsyncStorage.setItem(ORDERS_KEY, JSON.stringify(mergedOrders));

      setOrders(mergedOrders.sort((a, b) => new Date(b.date) - new Date(a.date)));
    } catch (error) {
      console.error('Failed to load orders:', error);
      setOrders([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    loadOrders();
  }, [loadOrders]);

  const savePaymentProof = async (order, imageUri) => {
    setLoading(true);
    try {
      const savedOrders = await AsyncStorage.getItem(ORDERS_KEY);
      const currentOrders = savedOrders ? JSON.parse(savedOrders) : [];

      const updatedOrders = currentOrders.map(o => {
        if (o.orderRef === order.orderRef) {
          return {
            ...o,
            paymentProof: imageUri,
            paymentProofDate: new Date().toISOString(),
            status: 'payment_verified',
          };
        }
        return o;
      });

      await AsyncStorage.setItem(ORDERS_KEY, JSON.stringify(updatedOrders));
      Alert.alert(
        'Success!',
        'Payment proof uploaded successfully. Your order will be processed soon.',
        [{ text: 'OK', onPress: () => loadOrders() }]
      );
    } catch (error) {
      console.error('Error saving payment proof:', error);
      Alert.alert('Error', 'Failed to upload payment proof');
    } finally {
      setLoading(false);
    }
  };

  return {
    orders,
    loading,
    refreshing,
    loadOrders,
    onRefresh,
    savePaymentProof,
  };
};