// src/screens/OrderDetailsScreen.js - Enhanced UI/UX
import { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Animated,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import colors from '../constants/colors';
import { trackingStages } from '../constants/tracking';
import ApiService from '../services/api';
import { API_CONFIG } from '../config/config';
import { useOrderNotifications } from '../hooks/useOrderNotifications';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';

const normalizeStatus = (apiStatus, paymentStatus, fallback) => {
  // Map all variations to the 4 main stages: pending, processing, shipped, delivered
  // Keep 'completed' as-is so it doesn't revert to 'delivered'
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

  let status = map[apiStatus] || apiStatus || fallback || 'pending';

  return status;
};

const OrderDetailsScreen = ({ navigation, route }) => {
  const { theme } = useTheme();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const scaleAnim = new Animated.Value(0);
  const slideAnim = new Animated.Value(100);

  // Enable order notifications
  const orderId = order?.backendOrderId || order?.id;
  useOrderNotifications(orderId, !!orderId);

  useEffect(() => {
    loadOrderDetails();
    // Trigger animations
    Animated.parallel([
      Animated.spring(scaleAnim, {
        toValue: 1,
        useNativeDriver: true,
      }),
      Animated.timing(slideAnim, {
        toValue: 0,
        duration: 500,
        useNativeDriver: true,
      }),
    ]).start();
  }, []);

  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      if (order?.backendOrderId || order?.id) {
        refreshFromApi(order.backendOrderId || order.id);
      }
    });
    return unsubscribe;
  }, [navigation, order]);

  // Poll periodically while on this screen to pick up admin status changes
  useEffect(() => {
    if (!order?.backendOrderId && !order?.id) return;
    const backendId = order.backendOrderId || order.id;
    const interval = setInterval(() => {
      refreshFromApi(backendId);
    }, 8000);
    return () => clearInterval(interval);
  }, [order?.backendOrderId, order?.id]);

  const refreshFromApi = async (backendId) => {
    if (!backendId) return;
    try {
      const res = await ApiService.getOrder(backendId);
      if (res?.success && res.data) {
        // Some endpoints wrap the order inside data/order, others return the order directly
        const apiOrder = res.data.data?.order || res.data.data || res.data;
        setOrder((prev) => {
          const normalizedStatus = normalizeStatus(apiOrder.status, apiOrder.payment_status, prev?.status);
          return {
            ...prev,
            ...apiOrder,
            backendOrderId: apiOrder.id || prev?.backendOrderId,
            orderRef: apiOrder.orderRef || apiOrder.order_ref || prev?.orderRef,
            status: normalizedStatus,
            paymentStatus: apiOrder.payment_status || prev?.paymentStatus,
            subtotal: apiOrder.subtotal ?? prev?.subtotal ?? 0,
            shippingFee: apiOrder.shipping_fee ?? prev?.shippingFee ?? 0,
            total: apiOrder.total ?? apiOrder.total_amount ?? prev?.total ?? 0,
          };
        });
      }
    } catch (err) {
      console.warn('Failed to refresh order from API', err?.message || err);
    }
  };

  const loadOrderDetails = async () => {
    try {
      // First, try to get from route params
      if (route.params?.orderData) {
        console.log('Order from params:', route.params.orderData);
        setOrder(route.params.orderData);
        // Attempt to refresh from API to get latest status
        const backendId = route.params.orderData.backendOrderId || route.params.orderData.id;
        if (backendId) refreshFromApi(backendId);
        setLoading(false);
        return;
      }

      // If no params, try to get orderRef and load from storage
      if (route.params?.orderRef) {
        const ordersJson = await AsyncStorage.getItem('pendingOrders');
        if (ordersJson) {
          const orders = JSON.parse(ordersJson);
          const foundOrder = orders.find(o => o.orderRef === route.params.orderRef);
          if (foundOrder) {
            console.log('Order from storage:', foundOrder);
            setOrder(foundOrder);
            if (foundOrder.backendOrderId || foundOrder.id) {
              refreshFromApi(foundOrder.backendOrderId || foundOrder.id);
            }
          }
        }
      }
    } catch (error) {
      console.error('Error loading order:', error);
      Alert.alert('Error', 'Failed to load order details');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const handleContactSeller = () => {
    Alert.alert(
      'Contact Seller',
      'How would you like to contact the seller?',
      [
        {
          text: 'Messenger',
          onPress: () => Alert.alert('Opening Messenger...'),
        },
        {
          text: 'Call',
          onPress: () => Alert.alert('Opening Phone...'),
        },
        {
          text: 'Chat',
          onPress: () => Alert.alert('Opening Chat...'),
        },
        {
          text: 'Cancel',
          style: 'cancel',
        },
      ]
    );
  };

  const markAsCompleted = async () => {
    try {
      const backendId = order.backendOrderId || order.id;
      if (!backendId) {
        Alert.alert('Error', 'Order ID not found');
        return;
      }

      // Call API to update order status to completed using PATCH /orders/{id}/status
      const response = await ApiService.request('PATCH', `/orders/${backendId}/status`, { 
        status: 'completed' 
      });
      
      if (response?.success) {
        // Update local state
        setOrder(prev => ({ ...prev, status: 'completed' }));
        
        // Update in AsyncStorage if needed
        const ordersJson = await AsyncStorage.getItem('pendingOrders');
        if (ordersJson) {
          const orders = JSON.parse(ordersJson);
          const updatedOrders = orders.map(o => 
            (o.backendOrderId === backendId || o.id === backendId) 
              ? { ...o, status: 'completed' }
              : o
          );
          await AsyncStorage.setItem('pendingOrders', JSON.stringify(updatedOrders));
        }
        
        Alert.alert('Success', 'Order marked as completed!');
      } else {
        Alert.alert('Error', response?.error || 'Failed to update order status');
      }
    } catch (error) {
      console.error('Error marking order as completed:', error);
      Alert.alert('Error', 'Failed to mark order as completed');
    }
  };

  const handleReturnRequest = () => {
    Alert.alert(
      'Return Request',
      'Do you want to initiate a return for this order?',
      [
        {
          text: 'Yes',
          onPress: () => Alert.alert('Return initiated', 'Seller has been notified'),
        },
        {
          text: 'No',
          style: 'cancel',
        },
      ]
    );
  };

  // Show loading state
  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={colors.primary} />
        <Text style={styles.loadingText}>Loading order details...</Text>
      </View>
    );
  }

  // Show error if no order found
  if (!order) {
    return (
      <View style={styles.errorContainer}>
        <Text style={styles.errorText}>Order not found</Text>
        <TouchableOpacity 
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.backButtonText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const currentStageIndex = trackingStages.findIndex(s => s.key === order.status);
  const displayStageIndex = Math.max(0, currentStageIndex);

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Order Tracking" 
        navigation={navigation} 
        showBack={true}
      />

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {/* Order Reference Header */}
        <View style={styles.orderRefHeader}>
          <View style={{ flex: 1 }}>
            <Text style={styles.orderRefHeaderNumber}>
              {order.orderRef ? `Order #${order.orderRef}` : 'Your Order'}
            </Text>
            {(order.createdAt || order.date) ? (
              <Text style={styles.orderRefHeaderDate}>{formatDate(order.createdAt || order.date)}</Text>
            ) : null}
          </View>
          <View style={[styles.orderRefStatusPill, { backgroundColor: (trackingStages[displayStageIndex]?.color || colors.primary) + '22' }]}>
            <View style={[styles.orderRefStatusDot, { backgroundColor: trackingStages[displayStageIndex]?.color || colors.primary }]} />
            <Text style={[styles.orderRefStatusPillText, { color: trackingStages[displayStageIndex]?.color || colors.primary }]}>
              {trackingStages[displayStageIndex]?.label || 'Pending'}
            </Text>
          </View>
        </View>

        {/* Status Summary Card */}
        <View style={styles.statusSummaryCard}>
          <View style={styles.statusSummaryItem}>
            <Ionicons name="cube-outline" size={28} color={colors.primary} style={styles.statusSummaryIconStyle} />
            <Text style={styles.statusSummaryLabel}>Status</Text>
            <Text style={styles.statusSummaryValue}>{trackingStages[displayStageIndex]?.label || 'Pending'}</Text>
          </View>
          <View style={styles.statusSummaryDivider} />
          <View style={styles.statusSummaryItem}>
            <Ionicons name="cash-outline" size={28} color={colors.primary} style={styles.statusSummaryIconStyle} />
            <Text style={styles.statusSummaryLabel}>Total</Text>
            <Text style={styles.statusSummaryValue}>₱{(parseFloat(order.total) || 0).toFixed(2)}</Text>
          </View>
          <View style={styles.statusSummaryDivider} />
          <View style={styles.statusSummaryItem}>
            <Ionicons name="checkmark-circle-outline" size={28} color={order.paymentStatus === 'paid' ? '#6B1F1F' : '#f59e0b'} style={styles.statusSummaryIconStyle} />
            <Text style={styles.statusSummaryLabel}>Payment</Text>
            <Text style={[styles.statusSummaryValue, { color: order.paymentStatus === 'paid' ? '#6B1F1F' : '#f59e0b' }]}>
              {order.paymentStatus === 'paid' ? 'Paid' : 'Pending'}
            </Text>
          </View>
        </View>

        {/* Tracking Timeline - Enhanced */}
        <View style={styles.trackingSection}>
          <Text style={styles.sectionTitle}>Order Timeline</Text>
          
          <View style={styles.timeline}>
            {trackingStages.map((stage, index) => {
              const isCompleted = index <= currentStageIndex;
              const isCurrent = index === currentStageIndex;

              return (
                <View key={stage.key} style={styles.timelineItem}>
                  {/* Timeline Line */}
                  {index < trackingStages.length - 1 && (
                    <View
                      style={[
                        styles.timelineLine,
                        isCompleted && styles.timelineLineCompleted,
                      ]}
                    />
                  )}

                  {/* Stage Indicator - Enhanced */}
                  <View
                    style={[
                      styles.stageIndicator,
                      isCompleted && styles.stageIndicatorCompleted,
                      isCurrent && styles.stageIndicatorCurrent,
                      { 
                        borderColor: isCompleted ? stage.color : '#E0E0E0',
                        backgroundColor: isCompleted ? stage.color : '#F5F5F5'
                      },
                    ]}
                  >
                    {isCompleted ? (
                      <Ionicons name="checkmark" size={20} color={colors.white} />
                    ) : (
                      <Text style={styles.stageIcon}>{index + 1}</Text>
                    )}
                  </View>

                  {/* Stage Info */}
                  <View style={[styles.stageInfo, isCurrent && styles.stageInfoCurrent]}>
                    <Text style={[
                      styles.stageLabel,
                      isCompleted && styles.stageLabelCompleted,
                      !isCompleted && styles.stageLabelPending
                    ]}>
                      {stage.label}
                    </Text>
                    <Text style={[
                      styles.stageDescription,
                      !isCompleted && styles.stageDescriptionPending
                    ]}>{stage.description}</Text>
                  </View>
                </View>
              );
            })}
          </View>
        </View>

        {/* Items Summary - Enhanced */}
        <View style={styles.itemsSection}>
          <Text style={styles.sectionTitle}>Order Items</Text>
          
          {order.items && order.items.length > 0 ? (
            order.items.map((item, index) => {
              // Construct image URL from product image path
              const imageUrl = item.product?.image 
                ? `${API_CONFIG.STORAGE_BASE_URL.replace('/storage', '')}/uploads/products/${item.product.image}`
                : 'https://via.placeholder.com/60?text=No+Image';
              
              return (
                <View key={index} style={styles.itemCard}>
                  <View style={styles.itemRow}>
                    {/* Product Image */}
                    <Image 
                      source={{ uri: imageUrl }}
                      style={styles.itemImage}
                      resizeMode="cover"
                    />
                    
                    {/* Product Info */}
                    <View style={styles.itemInfo}>
                      <Text style={styles.itemName} numberOfLines={2}>
                        {item.product?.name || item.name || item.product_name || 'Product'}
                      </Text>
                      <Text style={styles.itemQuantity}>Quantity: {item.quantity}</Text>
                    </View>
                    
                    {/* Price Info */}
                    <View style={styles.itemPriceSection}>
                      <Text style={styles.itemPrice}>₱{(parseFloat(item.price) || 0).toFixed(2)}</Text>
                      <Text style={styles.itemPriceLabel}>₱{(parseFloat(item.price) || 0).toFixed(2)} each</Text>
                    </View>
                  </View>
                </View>
              );
            })
          ) : (
            <View style={styles.emptyCard}>
              <Text style={styles.emptyText}>No items in order</Text>
            </View>
          )}
        </View>

        {/* Order Summary - Enhanced */}
        <View style={styles.summaryCard}>
          <Text style={styles.summaryTitle}>Order Summary</Text>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>₱{(parseFloat(order.subtotal) || 0).toFixed(2)}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Shipping</Text>
            <Text style={styles.summaryValue}>₱{(parseFloat(order.shippingFee) || 0).toFixed(2)}</Text>
          </View>
          <View style={[styles.summaryRow, styles.summaryTotal]}>
            <Text style={styles.summaryTotalLabel}>Total</Text>
            <Text style={styles.summaryTotalValue}>₱{(parseFloat(order.total) || 0).toFixed(2)}</Text>
          </View>
        </View>

        {/* Shipping Address - Enhanced */}
        {order.shippingAddress && (
          <View style={styles.addressSection}>
            <Text style={styles.sectionTitle}>Delivery Address</Text>
            <View style={styles.addressCard}>
              <View style={styles.addressHeader}>
                <Text style={styles.addressIcon}>📍</Text>
                <Text style={styles.addressHeaderText}>Delivery Location</Text>
              </View>
              <Text style={styles.addressName}>{order.shippingAddress.fullName || 'N/A'}</Text>
              <Text style={styles.addressPhone}>{order.shippingAddress.phoneNumber || 'N/A'}</Text>
              <View style={styles.addressDivider} />
              <Text style={styles.addressText}>
                {order.shippingAddress.street || 'N/A'}
              </Text>
              <Text style={styles.addressText}>
                {order.shippingAddress.city || 'N/A'}, {order.shippingAddress.province || 'N/A'} {order.shippingAddress.postalCode || ''}
              </Text>
            </View>
          </View>
        )}

        {/* Courier Information - Enhanced */}
        {order.courier_name && (
          <View style={styles.courierSection}>
            <Text style={styles.sectionTitle}>Courier Information</Text>
            <View style={styles.courierCard}>
              <View style={styles.courierHeader}>
                <Text style={styles.courierIcon}>🚚</Text>
                <Text style={styles.courierName}>{order.courier_name}</Text>
              </View>
              {order.courier_contact && (
                <View style={styles.courierDetail}>
                  <Text style={styles.courierLabel}>Contact:</Text>
                  <Text style={styles.courierValue}>{order.courier_contact}</Text>
                </View>
              )}
              {order.courier_tracking_url && (
                <TouchableOpacity 
                  style={styles.courierTrackButton}
                  onPress={() => {
                    Alert.alert('Track on Courier', 'Opening courier tracking page...');
                  }}
                >
                  <Text style={styles.courierTrackButtonText}>Track on Courier Website →</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        )}

        {/* Order Received/Completed Button */}
        {order.status === 'delivered' && (
          <TouchableOpacity 
            style={styles.orderReceivedButton}
            onPress={() => {
              Alert.alert(
                'Confirm Order Received',
                'Have you received your order?',
                [
                  {
                    text: 'Yes, Received',
                    onPress: markAsCompleted,
                  },
                  {
                    text: 'Cancel',
                    style: 'cancel',
                  },
                ]
              );
            }}
          >
            <Ionicons name="checkmark-circle" size={24} color={colors.white} />
            <Text style={styles.orderReceivedText}>Order Received</Text>
          </TouchableOpacity>
        )}

        {order.status === 'completed' && (
          <View style={styles.orderCompletedButton}>
            <Ionicons name="checkmark-circle" size={24} color="#4CAF50" />
            <Text style={styles.orderCompletedText}>Order Completed</Text>
          </View>
        )}

        <View style={{ height: 40 }} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.background,
  },
  loadingText: {
    marginTop: 10,
    fontSize: 14,
    color: colors.textLight,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.background,
    padding: 20,
  },
  errorText: {
    fontSize: 16,
    color: colors.textLight,
    marginBottom: 20,
  },
  header: {
    backgroundColor: colors.primary,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 15,
    paddingTop: 40,
  },
  backButtonHeader: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '600',
  },
  title: {
    color: colors.white,
    fontSize: 18,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    padding: 15,
  },
  orderRefCard: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 15,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: colors.border,
  },
  orderRefLabel: {
    fontSize: 12,
    color: colors.textLight,
    marginBottom: 4,
  },
  orderRefNumber: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 4,
  },
  orderDate: {
    fontSize: 12,
    color: colors.textLight,
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 20,
  },
  statusText: {
    color: colors.white,
    fontSize: 12,
    fontWeight: '600',
  },
  trackingSection: {
    marginBottom: 25,
  },
  sectionTitle: {
    fontSize: 17,
    fontWeight: '800',
    color: colors.text,
    marginBottom: 16,
    paddingLeft: 12,
    borderLeftWidth: 3,
    borderLeftColor: colors.primary,
  },
  timeline: {
    paddingVertical: 10,
  },
  timelineItem: {
    flexDirection: 'row',
    marginBottom: 25,
    position: 'relative',
  },
  timelineLine: {
    position: 'absolute',
    left: 23,
    top: 52,
    width: 3,
    height: 58,
    backgroundColor: '#E0E0E0',
    borderRadius: 2,
  },
  timelineLineCompleted: {
    backgroundColor: colors.primary,
  },
  stageIndicator: {
    width: 46,
    height: 46,
    borderRadius: 23,
    borderWidth: 3,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
    backgroundColor: colors.white,
  },
  stageIndicatorCompleted: {
    backgroundColor: colors.primary,
  },
  stageIndicatorCurrent: {
    width: 52,
    height: 52,
    borderRadius: 26,
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.45,
    shadowRadius: 10,
    elevation: 10,
  },
  stageIcon: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#9E9E9E',
  },
  stageIconCompleted: {
    color: colors.white,
  },
  stageInfo: {
    flex: 1,
    justifyContent: 'center',
  },
  stageLabel: {
    fontSize: 15,
    fontWeight: '700',
    color: colors.textLight,
    marginBottom: 4,
  },
  stageLabelCompleted: {
    color: colors.text,
  },
  stageLabelPending: {
    color: '#BDBDBD',
  },
  stageDescription: {
    fontSize: 12,
    color: colors.textLight,
    lineHeight: 18,
  },
  stageDescriptionPending: {
    color: '#C8C8C8',
  },
  itemsSection: {
    marginBottom: 20,
  },
  itemCard: {
    backgroundColor: colors.white,
    borderRadius: 14,
    padding: 15,
    marginBottom: 10,
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.07,
    shadowRadius: 6,
    borderLeftWidth: 3,
    borderLeftColor: colors.primary,
  },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  itemImage: {
    width: 60,
    height: 60,
    borderRadius: 8,
    marginRight: 12,
    backgroundColor: '#F5F5F5',
  },
  itemInfo: {
    flex: 1,
    marginRight: 10,
  },
  itemName: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 6,
  },
  itemQuantity: {
    fontSize: 12,
    color: colors.textLight,
  },
  itemPriceSection: {
    alignItems: 'flex-end',
  },
  itemPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 2,
  },
  itemPriceLabel: {
    fontSize: 11,
    color: colors.textLight,
  },
  itemTotal: {
    fontSize: 12,
    color: colors.text,
    fontWeight: '600',
  },
  emptyCard: {
    backgroundColor: colors.white,
    borderRadius: 10,
    padding: 20,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
  },
  emptyText: {
    fontSize: 14,
    color: colors.textLight,
    marginBottom: 4,
  },
  emptySubtext: {
    fontSize: 12,
    color: colors.textLight,
  },
  summaryCard: {
    backgroundColor: colors.white,
    borderRadius: 18,
    padding: 18,
    marginBottom: 20,
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.09,
    shadowRadius: 8,
    borderTopWidth: 4,
    borderTopColor: colors.primary,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  summaryTotal: {
    borderBottomWidth: 0,
    marginTop: 5,
    paddingVertical: 12,
  },
  summaryLabel: {
    fontSize: 14,
    color: colors.text,
  },
  summaryValue: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
  },
  summaryTotalLabel: {
    fontSize: 17,
    fontWeight: '800',
    color: colors.text,
  },
  summaryTotalValue: {
    fontSize: 22,
    fontWeight: '800',
    color: colors.primary,
  },
  addressSection: {
    marginBottom: 20,
  },
  addressCard: {
    backgroundColor: colors.white,
    borderRadius: 14,
    padding: 16,
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.07,
    shadowRadius: 6,
  },
  addressLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 8,
  },
  addressName: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 4,
  },
  addressText: {
    fontSize: 13,
    color: colors.text,
    lineHeight: 20,
    marginBottom: 4,
  },
  actionsSection: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 20,
  },
  actionButton: {
    flex: 1,
    backgroundColor: colors.white,
    borderRadius: 10,
    paddingVertical: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
  },
  actionButtonIcon: {
    fontSize: 24,
    marginBottom: 4,
  },
  actionButtonText: {
    fontSize: 12,
    fontWeight: '600',
    color: colors.text,
  },
  helpSection: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 15,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
    marginBottom: 20,
  },
  helpIcon: {
    fontSize: 24,
    marginRight: 12,
  },
  helpText: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    flex: 1,
  },
  helpArrow: {
    fontSize: 18,
    color: colors.primary,
  },
  backButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
  },
  backButtonText: {
    color: colors.white,
    fontSize: 14,
    fontWeight: '600',
  },
  courierSection: {
    marginBottom: 20,
  },
  courierCard: {
    backgroundColor: colors.white,
    borderRadius: 14,
    padding: 16,
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.07,
    shadowRadius: 6,
  },
  courierRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  courierLabel: {
    fontSize: 14,
    color: colors.textLight,
  },
  courierValue: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
  },
  courierTrackButton: {
    marginTop: 12,
    paddingVertical: 12,
    paddingHorizontal: 15,
    backgroundColor: colors.primary,
    borderRadius: 8,
    alignItems: 'center',
  },
  courierTrackButtonText: {
    color: colors.white,
    fontSize: 14,
    fontWeight: '600',
  },
  orderReceivedButton: {
    backgroundColor: colors.primary,
    borderRadius: 14,
    padding: 18,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
    elevation: 4,
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.35,
    shadowRadius: 8,
  },
  orderReceivedText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '700',
    marginLeft: 10,
    letterSpacing: 0.3,
  },
  orderCompletedButton: {
    backgroundColor: '#F0FFF4',
    borderRadius: 14,
    padding: 18,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
    borderWidth: 2,
    borderColor: '#4CAF50',
    elevation: 2,
    shadowColor: '#4CAF50',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 6,
  },
  orderCompletedText: {
    color: '#388E3C',
    fontSize: 16,
    fontWeight: '700',
    marginLeft: 10,
  },
  // Missing styles for enhanced UI
  backBtn: {
    padding: 5,
  },
  orderRefContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
  },
  progressContainer: {
    marginTop: 15,
    paddingTop: 15,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  progressBar: {
    height: 6,
    backgroundColor: '#E5E7EB',
    borderRadius: 3,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 3,
  },
  progressText: {
    fontSize: 12,
    color: colors.textLight,
    marginTop: 8,
    textAlign: 'center',
  },
  statusSummaryCard: {
    backgroundColor: colors.white,
    borderRadius: 18,
    padding: 20,
    marginBottom: 20,
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
    elevation: 6,
    shadowColor: '#8B1A1A',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 10,
    borderTopWidth: 4,
    borderTopColor: colors.primary,
  },
  statusSummaryItem: {
    alignItems: 'center',
    flex: 1,
    paddingVertical: 4,
  },
  statusSummaryIconStyle: {
    marginBottom: 8,
  },
  statusSummaryLabel: {
    fontSize: 11,
    color: colors.textLight,
    marginBottom: 5,
    fontWeight: '600',
    letterSpacing: 0.5,
    textTransform: 'uppercase',
  },
  statusSummaryValue: {
    fontSize: 15,
    fontWeight: '700',
    color: colors.text,
  },
  statusSummaryDivider: {
    width: 1,
    height: 55,
    backgroundColor: colors.border,
  },
  stageInfoCurrent: {
    backgroundColor: '#FFF0F0',
    padding: 10,
    borderRadius: 10,
    marginLeft: -8,
    paddingLeft: 10,
    borderLeftWidth: 2,
    borderLeftColor: colors.primary,
  },
  itemNumberBadge: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  itemNumber: {
    color: colors.white,
    fontSize: 12,
    fontWeight: 'bold',
  },
  itemDetails: {
    flex: 1,
  },
  summaryTitle: {
    fontSize: 17,
    fontWeight: '800',
    color: colors.text,
    marginBottom: 14,
    paddingLeft: 10,
    borderLeftWidth: 3,
    borderLeftColor: colors.primary,
  },
  addressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  addressIcon: {
    fontSize: 20,
    marginRight: 8,
  },
  addressHeaderText: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
  },
  addressPhone: {
    fontSize: 13,
    color: colors.textLight,
    marginBottom: 8,
  },
  addressDivider: {
    height: 1,
    backgroundColor: colors.border,
    marginVertical: 10,
  },
  courierHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  courierIcon: {
    fontSize: 24,
    marginRight: 10,
  },
  courierName: {
    fontSize: 16,
    fontWeight: '600',
    color: colors.text,
  },
  courierDetail: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  helpContent: {
    flex: 1,
  },
  helpSubtext: {
    fontSize: 12,
    color: colors.textLight,
    marginTop: 2,
  },
  // Order Reference Header
  orderRefHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.white,
    borderRadius: 16,
    padding: 16,
    marginBottom: 14,
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
  },
  orderRefHeaderNumber: {
    fontSize: 18,
    fontWeight: '800',
    color: colors.text,
    marginBottom: 4,
  },
  orderRefHeaderDate: {
    fontSize: 12,
    color: colors.textLight,
    fontWeight: '500',
  },
  orderRefStatusPill: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 20,
    gap: 6,
  },
  orderRefStatusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  orderRefStatusPillText: {
    fontSize: 13,
    fontWeight: '700',
  },
});

export default OrderDetailsScreen;