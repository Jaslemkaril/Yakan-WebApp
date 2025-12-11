import { useState, useCallback } from 'react';
import { Alert } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

const ORDERS_KEY = 'pendingOrders';

export const useOrders = () => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadOrders = useCallback(async () => {
    setLoading(true);
    try {
      const savedOrders = await AsyncStorage.getItem(ORDERS_KEY);
      const localOrders = savedOrders ? JSON.parse(savedOrders) : [];
      setOrders(localOrders.sort((a, b) => new Date(b.date) - new Date(a.date)));
    } catch (error) {
      console.error('Failed to load orders from local storage:', error);
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