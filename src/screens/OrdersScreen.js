import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  SafeAreaView,
  StatusBar,
} from 'react-native';
import { useCart } from '../context/CartContext';
import ApiService from '../services/api';
import colors from '../constants/colors';
import BottomNav from '../components/BottomNav';

const OrdersScreen = ({ navigation }) => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const { isLoggedIn } = useCart();

  useEffect(() => {
    if (isLoggedIn) {
      fetchOrders();
      const interval = setInterval(fetchOrders, 10000); // Poll every 10 seconds
      return () => clearInterval(interval);
    }
  }, [isLoggedIn]);

  const fetchOrders = async () => {
    try {
      console.log('[OrdersScreen] Fetching orders...');
      const response = await ApiService.getOrders();
      console.log('[OrdersScreen] Response:', response);
      
      if (response && response.success) {
        const ordersData = response.data?.data || response.data || [];
        const ordersList = Array.isArray(ordersData) ? ordersData : [];
        console.log(`[OrdersScreen] Fetched ${ordersList.length} orders`);
        setOrders(ordersList);
      } else {
        console.error('[OrdersScreen] Failed to fetch orders:', response?.error);
        // Don't clear orders on error - keep existing data
      }
    } catch (error) {
      console.error('[OrdersScreen] Error fetching orders:', error);
      // Don't clear orders on error - keep existing data
    } finally {
      setLoading(false);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    try {
      await fetchOrders();
    } finally {
      setRefreshing(false);
    }
  };

  const getStatusColor = (status) => {
    // Use consistent maroon color theme
    // Darker maroon for completed/delivered (success state)
    // Primary maroon for active states
    // Warning colors for pending
    const statusColors = {
      pending: '#f59e0b',
      pending_payment: '#f59e0b',
      payment_verified: colors.primary,
      pending_confirmation: colors.primary,
      confirmed: colors.primary,
      processing: '#9B1C1C',
      shipped: colors.primary,
      delivered: '#6B1F1F',
      completed: '#6B1F1F',
      cancelled: '#ef4444',
    };
    return statusColors[status] || colors.primary;
  };

  const getStatusLabel = (status) => {
    const labels = {
      pending: 'Pending',
      processing: 'Processing',
      shipped: 'Shipping',
      delivered: 'Delivered',
      completed: 'Completed',
      cancelled: 'Cancelled',
    };
    return labels[status] || status;
  };

  if (!isLoggedIn) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
        <View style={styles.centerContent}>
          <Text style={styles.emptyIcon}>📦</Text>
          <Text style={styles.title}>Login Required</Text>
          <Text style={styles.subtitle}>Please login to view your orders</Text>
          <TouchableOpacity
            style={styles.loginButton}
            onPress={() => navigation.navigate('Login')}
          >
            <Text style={styles.loginButtonText}>Go to Login</Text>
          </TouchableOpacity>
        </View>
        <BottomNav navigation={navigation} activeRoute="Orders" />
      </SafeAreaView>
    );
  }

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
        <View style={styles.centerContent}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading orders...</Text>
        </View>
        <BottomNav navigation={navigation} activeRoute="Orders" />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={colors.white} />
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>My Orders</Text>
          <Text style={styles.subtitle}>Track your purchases</Text>
        </View>
        <View style={styles.orderCount}>
          <Text style={styles.orderCountText}>{orders.length}</Text>
        </View>
      </View>

      <View style={styles.content}>
        {orders.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>📦</Text>
            <Text style={styles.emptyText}>No orders yet</Text>
            <Text style={styles.emptySubtext}>Start shopping to place your first order</Text>
            <TouchableOpacity
              style={styles.emptyButton}
              onPress={() => navigation.navigate('Home')}
            >
              <Text style={styles.emptyButtonText}>Continue Shopping</Text>
            </TouchableOpacity>
          </View>
        ) : (
          <FlatList
            style={{flex: 1}}
            data={orders}
            keyExtractor={(item) => item.id.toString()}
            renderItem={({ item }) => (
            <TouchableOpacity
              style={styles.orderCard}
              onPress={() => navigation.navigate('OrderDetails', { orderData: item })}
              activeOpacity={0.7}
            >
              <View style={styles.orderCardHeader}>
                <View style={styles.orderInfo}>
                  <Text style={styles.orderRef}>Order #{item.id}</Text>
                  <Text style={styles.orderDate}>
                    {new Date(item.created_at).toLocaleDateString()}
                  </Text>
                </View>
                <View 
                  style={[
                    styles.statusBadge,
                    { backgroundColor: getStatusColor(item.status) }
                  ]}
                >
                  <Text style={styles.statusText}>{getStatusLabel(item.status)}</Text>
                </View>
              </View>

              <View style={styles.orderCardBody}>
                <View style={styles.orderDetail}>
                  <Text style={styles.detailLabel}>Items</Text>
                  <Text style={styles.detailValue}>
                    {item.orderItems?.length || item.items?.length || 0}
                  </Text>
                </View>
                <View style={styles.orderDetail}>
                  <Text style={styles.detailLabel}>Total</Text>
                  <Text style={styles.detailValue}>
                    ₱{(parseFloat(item.total_amount || item.total) || 0).toFixed(2)}
                  </Text>
                </View>
                <View style={styles.orderDetail}>
                  <Text style={styles.detailLabel}>Payment</Text>
                  <Text style={[
                    styles.detailValue,
                    { color: item.payment_status === 'paid' ? '#6B1F1F' : '#f59e0b' }
                  ]}>
                    {item.payment_status === 'paid' ? 'Paid' : 'Pending'}
                  </Text>
                </View>
              </View>

              <View style={styles.orderCardFooter}>
                <Text style={styles.viewDetails}>View Details →</Text>
              </View>
            </TouchableOpacity>
          )}
          contentContainerStyle={styles.ordersList}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              tintColor={colors.primary}
            />
          }
        />
        )}
      </View>

      <BottomNav navigation={navigation} activeRoute="Orders" />
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  content: {
    flex: 1,
  },
  centerContent: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 16,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  title: {
    fontSize: 26,
    fontWeight: '700',
    color: colors.text,
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 13,
    color: '#999',
    fontWeight: '500',
  },
  orderCount: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
  },
  orderCountText: {
    color: colors.white,
    fontSize: 18,
    fontWeight: 'bold',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  emptyIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 14,
    color: '#999',
    marginBottom: 24,
    textAlign: 'center',
  },
  emptyButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: 32,
    paddingVertical: 12,
    borderRadius: 10,
  },
  emptyButtonText: {
    color: colors.white,
    fontWeight: '600',
    fontSize: 15,
  },
  loginButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: 30,
    paddingVertical: 12,
    borderRadius: 10,
    marginTop: 20,
  },
  loginButtonText: {
    color: colors.white,
    fontWeight: '600',
    fontSize: 16,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 16,
    color: colors.text,
  },
  ordersList: {
    paddingHorizontal: 12,
    paddingTop: 12,
    paddingBottom: 100,
  },
  orderCard: {
    backgroundColor: colors.white,
    borderRadius: 14,
    marginBottom: 12,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
  },
  orderCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  orderInfo: {
    flex: 1,
  },
  orderRef: {
    fontSize: 14,
    fontWeight: '700',
    color: colors.text,
    marginBottom: 2,
  },
  orderDate: {
    fontSize: 12,
    color: '#999',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
  },
  statusText: {
    color: colors.white,
    fontSize: 11,
    fontWeight: '700',
  },
  orderCardBody: {
    flexDirection: 'row',
    paddingHorizontal: 14,
    paddingVertical: 12,
    justifyContent: 'space-around',
  },
  orderDetail: {
    alignItems: 'center',
  },
  detailLabel: {
    fontSize: 11,
    color: '#999',
    marginBottom: 4,
    fontWeight: '500',
  },
  detailValue: {
    fontSize: 13,
    fontWeight: '700',
    color: colors.text,
  },
  orderCardFooter: {
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderTopWidth: 1,
    borderTopColor: '#f0f0f0',
    alignItems: 'flex-end',
  },
  viewDetails: {
    fontSize: 12,
    fontWeight: '600',
    color: colors.primary,
  },
});

export default OrdersScreen;
