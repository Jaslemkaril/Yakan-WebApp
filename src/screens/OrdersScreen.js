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
import { MaterialCommunityIcons, Ionicons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import ApiService from '../services/api';
import colors from '../constants/colors';
import BottomNav from '../components/BottomNav';

const OrdersScreen = ({ navigation }) => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const { isLoggedIn } = useCart();
  const { theme } = useTheme();
  const styles = getStyles(theme);

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

  const getStatusIcon = (status) => {
    const icons = {
      pending: 'clock-outline',
      pending_payment: 'credit-card-clock',
      payment_verified: 'check-circle-outline',
      pending_confirmation: 'checkbox-marked-circle-outline',
      confirmed: 'check-circle',
      processing: 'cog',
      shipped: 'truck-fast',
      delivered: 'home-check',
      completed: 'check-all',
      cancelled: 'close-circle',
    };
    return icons[status] || 'package-variant-closed';
  };

  const getStatusColor = (status) => {
    // Use consistent maroon color theme
    // Darker maroon for completed/delivered (success state)
    // Primary maroon for active states
    // Warning colors for pending
    const statusColors = {
      pending: '#f59e0b',
      pending_payment: '#f59e0b',
      payment_verified: '#3498DB',
      pending_confirmation: '#3498DB',
      confirmed: '#8B1A1A',
      processing: '#9B1C1C',
      shipped: '#27AE60',
      delivered: '#27AE60',
      completed: '#6B1F1F',
      cancelled: '#E74C3C',
    };
    return statusColors[status] || '#8B1A1A';
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
      <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
        <ScreenHeader 
          title="My Orders" 
          navigation={navigation}
          showBack={false}
          showHamburger={true}
        />
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
      <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
        <ScreenHeader 
          title="My Orders" 
          navigation={navigation}
          showBack={false}
          showHamburger={true}
        />
        <View style={styles.centerContent}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading orders...</Text>
        </View>
        <BottomNav navigation={navigation} activeRoute="Orders" />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="My Orders" 
        navigation={navigation}
        showBack={false}
        showHamburger={true}
      />

      <View style={styles.content}>
        {orders.length === 0 ? (
          <View style={styles.emptyContainer}>
            <MaterialCommunityIcons name="package-variant-off" size={80} color="#DDD" />
            <Text style={styles.emptyText}>No orders yet</Text>
            <Text style={styles.emptySubtext}>Start shopping to place your first order</Text>
            <TouchableOpacity
              style={styles.emptyButton}
              onPress={() => navigation.navigate('Home')}
              activeOpacity={0.8}
            >
              <MaterialCommunityIcons name="shopping" size={20} color="#fff" style={{ marginRight: 8 }} />
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
                <View style={styles.orderHeaderLeft}>
                  <View style={styles.orderNumberContainer}>
                    <MaterialCommunityIcons name="package-variant-closed" size={20} color="#8B1A1A" />
                  </View>
                  <View style={styles.orderInfo}>
                    <Text style={styles.orderRef}>Order #{item.id}</Text>
                    <Text style={styles.orderDate}>
                      {new Date(item.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                    </Text>
                  </View>
                </View>
                <View 
                  style={[
                    styles.statusBadge,
                    { backgroundColor: getStatusColor(item.status) }
                  ]}
                >
                  <MaterialCommunityIcons 
                    name={getStatusIcon(item.status)} 
                    size={14} 
                    color="#fff" 
                    style={{ marginRight: 4 }}
                  />
                  <Text style={styles.statusText}>{getStatusLabel(item.status)}</Text>
                </View>
              </View>

              <View style={styles.orderCardBody}>
                <View style={styles.orderDetail}>
                  <View style={styles.detailIconContainer}>
                    <MaterialCommunityIcons name="shopping-outline" size={16} color="#8B1A1A" />
                  </View>
                  <Text style={styles.detailLabel}>Items</Text>
                  <Text style={styles.detailValue}>
                    {item.orderItems?.length || item.items?.length || 0}
                  </Text>
                </View>
                <View style={styles.orderDetail}>
                  <View style={styles.detailIconContainer}>
                    <MaterialCommunityIcons name="currency-php" size={16} color="#27AE60" />
                  </View>
                  <Text style={styles.detailLabel}>Total</Text>
                  <Text style={[styles.detailValue, { color: '#27AE60' }]}>
                    ₱{(parseFloat(item.total_amount || item.total) || 0).toFixed(2)}
                  </Text>
                </View>
                <View style={styles.orderDetail}>
                  <View style={styles.detailIconContainer}>
                    <MaterialCommunityIcons 
                      name={item.payment_status === 'paid' ? 'check-circle' : 'clock-outline'} 
                      size={16} 
                      color={item.payment_status === 'paid' ? '#27AE60' : '#f59e0b'} 
                    />
                  </View>
                  <Text style={styles.detailLabel}>Payment</Text>
                  <Text style={[
                    styles.detailValue,
                    { color: item.payment_status === 'paid' ? '#27AE60' : '#f59e0b' }
                  ]}>
                    {item.payment_status === 'paid' ? 'Paid' : 'Pending'}
                  </Text>
                </View>
              </View>

              <View style={styles.orderCardFooter}>
                <MaterialCommunityIcons name="chevron-right" size={20} color="#8B1A1A" />
                <Text style={styles.viewDetails}>View Details</Text>
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

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
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
    backgroundColor: theme.cardBackground,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
  },
  title: {
    fontSize: 26,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 13,
    color: theme.textMuted,
    fontWeight: '500',
  },
  orderCount: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: theme.primary,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
  },
  orderCountText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 30,
  },
  emptyText: {
    fontSize: 20,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 12,
    marginTop: 20,
    textAlign: 'center',
  },
  emptySubtext: {
    fontSize: 15,
    color: theme.textSecondary,
    marginBottom: 32,
    textAlign: 'center',
    fontWeight: '500',
    lineHeight: 22,
  },
  emptyButton: {
    backgroundColor: theme.primary,
    paddingHorizontal: 32,
    paddingVertical: 14,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    elevation: 3,
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
  },
  emptyButtonText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 16,
  },
  loginButton: {
    backgroundColor: theme.primary,
    paddingHorizontal: 30,
    paddingVertical: 12,
    borderRadius: 10,
    marginTop: 20,
  },
  loginButtonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 16,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 16,
    color: theme.text,
  },
  ordersList: {
    paddingHorizontal: 15,
    paddingTop: 12,
    paddingBottom: 100,
  },
  orderCard: {
    backgroundColor: theme.cardBackground,
    borderRadius: 16,
    marginBottom: 14,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 4,
    borderLeftWidth: 4,
    borderLeftColor: theme.primary,
  },
  orderCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: theme.borderLight,
  },
  orderHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    marginRight: 12,
  },
  orderNumberContainer: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: theme.dangerBg,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  orderInfo: {
    flex: 1,
  },
  orderRef: {
    fontSize: 16,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 3,
  },
  orderDate: {
    fontSize: 13,
    color: theme.textSecondary,
    fontWeight: '500',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '700',
  },
  orderCardBody: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingVertical: 14,
    justifyContent: 'space-between',
  },
  orderDetail: {
    alignItems: 'center',
    flex: 1,
  },
  detailIconContainer: {
    marginBottom: 6,
  },
  detailLabel: {
    fontSize: 12,
    color: theme.textMuted,
    marginBottom: 4,
    fontWeight: '600',
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '700',
    color: theme.text,
  },
  orderCardFooter: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: theme.borderLight,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
  },
  viewDetails: {
    fontSize: 14,
    fontWeight: '700',
    color: theme.primary,
    marginLeft: 6,
  },
});

export default OrdersScreen;
