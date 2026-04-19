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
  Modal,
  TextInput,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
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
    cancellation_requested: 'cancellation_requested',
    cancelled: 'cancelled',
    refunded: 'refunded',
  };

  let status = map[apiStatus] || apiStatus || fallback || 'pending';

  return status;
};

const CANCEL_REASONS = [
  'Changed my mind',
  'Found a better price elsewhere',
  'Ordered by mistake',
  'Delivery takes too long',
  'Duplicate order',
  'Want to change items',
  'Financial reasons',
  'Other',
];

const REFUND_REASONS = [
  'Item not as described',
  'Damaged item',
  'Wrong item received',
  'Incomplete order',
  'Changed my mind',
  'Other',
];

const OrderDetailsScreen = ({ navigation, route }) => {
  const { theme } = useTheme();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showCancelModal, setShowCancelModal] = useState(false);
  const [selectedCancelReason, setSelectedCancelReason] = useState('');
  const [customCancelReason, setCustomCancelReason] = useState('');
  const [isCancelling, setIsCancelling] = useState(false);
  const [showRefundModal, setShowRefundModal] = useState(false);
  const [selectedRefundReason, setSelectedRefundReason] = useState('');
  const [customRefundReason, setCustomRefundReason] = useState('');
  const [refundComment, setRefundComment] = useState('');
  const [refundEvidence, setRefundEvidence] = useState([]);
  const [isRequestingRefund, setIsRequestingRefund] = useState(false);
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
            paymentOption: apiOrder.payment_option || apiOrder.paymentOption || prev?.paymentOption,
            downpaymentAmount: apiOrder.downpayment_amount ?? apiOrder.downpaymentAmount ?? prev?.downpaymentAmount,
            remainingBalance: apiOrder.remaining_balance ?? apiOrder.remainingBalance ?? prev?.remainingBalance,
            subtotal: apiOrder.subtotal ?? prev?.subtotal ?? 0,
            shippingFee: apiOrder.shipping_fee ?? prev?.shippingFee ?? 0,
            total: apiOrder.total ?? apiOrder.total_amount ?? prev?.total ?? 0,
            can_request_refund: apiOrder.can_request_refund ?? prev?.can_request_refund,
            refund_request_in_progress: apiOrder.refund_request_in_progress ?? prev?.refund_request_in_progress,
            latest_refund_request: apiOrder.latest_refund_request ?? prev?.latest_refund_request,
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

  const canCancelOrder = ['pending', 'pending_confirmation', 'confirmed', 'processing'].includes(order?.status);

  const latestRefundRequest = order?.latest_refund_request || null;
  const latestRefundWorkflow = (latestRefundRequest?.workflow_status || latestRefundRequest?.status || '').toLowerCase();
  const latestRefundDecision = String(latestRefundRequest?.final_decision || latestRefundRequest?.recommended_decision || '').trim();
  const latestRefundAmount = latestRefundRequest?.approved_amount ?? latestRefundRequest?.refund_amount ?? latestRefundRequest?.recommended_refund_amount;
  const latestRefundEvidence = Array.isArray(latestRefundRequest?.evidence) ? latestRefundRequest.evidence : [];
  const refundRequestInProgress = Boolean(
    order?.refund_request_in_progress
    || ['requested', 'pending_review', 'under_review', 'awaiting_return_shipment', 'return_in_transit', 'pending_payout', 'approved'].includes(latestRefundWorkflow)
  );

  const openCancelModal = () => {
    setSelectedCancelReason('');
    setCustomCancelReason('');
    setShowCancelModal(true);
  };

  const openRefundModal = () => {
    setSelectedRefundReason('');
    setCustomRefundReason('');
    setRefundComment('');
    setRefundEvidence([]);
    setShowRefundModal(true);
  };

  const formatRefundStatusLabel = (rawStatus) => {
    const value = String(rawStatus || '').trim().toLowerCase();
    const map = {
      pending_review: 'Pending Review',
      under_review: 'Under Review',
      awaiting_return_shipment: 'Awaiting Return Shipment',
      return_in_transit: 'Return In Transit',
      return_received: 'Return Received',
      pending_payout: 'Pending Payout',
      approved: 'Approved',
      processed: 'Processed',
      rejected: 'Rejected',
      requested: 'Requested',
      completed: 'Completed',
    };

    if (!value) {
      return 'Pending Review';
    }

    return map[value] || value.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
  };

  const hasVideoEvidence = (items) => items.some((asset) => {
    const type = String(asset?.type || '').toLowerCase();
    const mimeType = String(asset?.mimeType || '').toLowerCase();
    const name = String(asset?.fileName || asset?.uri || '').toLowerCase();

    return type === 'video'
      || mimeType.startsWith('video/')
      || /\.(mp4|mov|webm)$/.test(name);
  });

  const pickRefundEvidence = async () => {
    try {
      if (refundEvidence.length >= 5) {
        Alert.alert('Maximum Reached', 'You can upload up to 5 evidence files.');
        return;
      }

      const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Permission Required', 'Please allow photo/video access to upload refund evidence.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.All,
        allowsMultipleSelection: true,
        quality: 0.8,
        selectionLimit: Math.max(1, 5 - refundEvidence.length),
      });

      if (result.canceled) {
        return;
      }

      const existing = new Set(refundEvidence.map((asset) => String(asset.uri)));
      const nextEvidence = [...refundEvidence];

      (result.assets || []).forEach((asset) => {
        const uri = String(asset?.uri || '');
        if (!uri || existing.has(uri) || nextEvidence.length >= 5) {
          return;
        }

        const fileName = asset?.fileName || uri.split('/').pop() || `evidence_${Date.now()}`;
        const lowerName = String(fileName).toLowerCase();
        const inferredType = asset?.type || (/\.(mp4|mov|webm)$/.test(lowerName) ? 'video' : 'image');
        const inferredMime = asset?.mimeType || (inferredType === 'video' ? 'video/mp4' : 'image/jpeg');

        nextEvidence.push({
          uri,
          fileName,
          mimeType: inferredMime,
          type: inferredType,
        });
        existing.add(uri);
      });

      setRefundEvidence(nextEvidence);
    } catch (error) {
      console.error('Error selecting refund evidence:', error);
      Alert.alert('Error', 'Failed to select evidence file.');
    }
  };

  const removeRefundEvidence = (uri) => {
    setRefundEvidence((prev) => prev.filter((asset) => String(asset.uri) !== String(uri)));
  };

  const confirmCancelOrder = async () => {
    const reason = selectedCancelReason === 'Other'
      ? customCancelReason.trim()
      : selectedCancelReason;

    if (!reason) {
      Alert.alert('Reason Required', 'Please select or enter a cancellation reason.');
      return;
    }

    const backendId = order?.backendOrderId || order?.id;
    if (!backendId) {
      Alert.alert('Error', 'Order ID not found');
      return;
    }

    setIsCancelling(true);
    try {
      const response = await ApiService.cancelOrder(backendId, reason);
      if (response?.success) {
        setOrder((prev) => ({
          ...prev,
          status: 'cancellation_requested',
          adminNotes: `Customer cancellation requested: ${reason}`,
        }));

        const ordersJson = await AsyncStorage.getItem('pendingOrders');
        if (ordersJson) {
          const orders = JSON.parse(ordersJson);
          const updatedOrders = orders.map((o) =>
            String(o.backendOrderId) === String(backendId) || String(o.id) === String(backendId)
              ? { ...o, status: 'cancellation_requested', adminNotes: `Customer cancellation requested: ${reason}` }
              : o
          );
          await AsyncStorage.setItem('pendingOrders', JSON.stringify(updatedOrders));
        }

        setShowCancelModal(false);
        Alert.alert('Request Submitted', 'Your cancellation request was sent to admin and is now pending review.');
        await refreshFromApi(backendId);
      } else {
        Alert.alert('Error', response?.error || response?.message || 'Failed to cancel order');
      }
    } catch (error) {
      console.error('Error cancelling order:', error);
      Alert.alert('Error', 'Failed to cancel order');
    } finally {
      setIsCancelling(false);
    }
  };

  const confirmRefundRequest = async () => {
    const reason = selectedRefundReason === 'Other'
      ? customRefundReason.trim()
      : selectedRefundReason;

    if (!reason) {
      Alert.alert('Reason Required', 'Please select or enter a refund reason.');
      return;
    }

    const comment = refundComment.trim();
    if (!comment) {
      Alert.alert('Details Required', 'Please provide a short explanation for your refund request.');
      return;
    }

    if (refundEvidence.length === 0) {
      Alert.alert('Evidence Required', 'Please upload at least one photo or video of the product.');
      return;
    }

    if (reason.toLowerCase() === 'damaged item' && !hasVideoEvidence(refundEvidence)) {
      Alert.alert('Video Required', 'For damaged items, upload at least one opening/unboxing video.');
      return;
    }

    const backendId = order?.backendOrderId || order?.id;
    if (!backendId) {
      Alert.alert('Error', 'Order ID not found');
      return;
    }

    setIsRequestingRefund(true);
    try {
      const response = await ApiService.requestOrderRefund(backendId, {
        reason,
        comment,
        details: comment,
        refund_type: reason.toLowerCase() === 'changed my mind' ? 'change_of_mind' : 'full',
      }, refundEvidence);

      if (response?.success) {
        const refundRequest = response?.data?.data?.refund_request
          || response?.data?.refund_request
          || {
            status: 'requested',
            workflow_status: 'pending_review',
            reason,
            comment,
            requested_at: new Date().toISOString(),
          };

        setOrder((prev) => ({
          ...prev,
          latest_refund_request: refundRequest,
          refund_request_in_progress: true,
          can_request_refund: false,
        }));

        setShowRefundModal(false);
        Alert.alert('Refund Requested', 'Your refund request was submitted and is now pending admin review.');
        await refreshFromApi(backendId);
      } else {
        Alert.alert('Error', response?.error || response?.message || 'Failed to submit refund request');
      }
    } catch (error) {
      console.error('Error requesting refund:', error);
      Alert.alert('Error', 'Failed to submit refund request');
    } finally {
      setIsRequestingRefund(false);
    }
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
  const isCancellationRequested = order.status === 'cancellation_requested';
  const isCancelled = order.status === 'cancelled';
  const isRefunded = order.status === 'refunded';
  const currentStatusColor = isCancellationRequested
    ? '#D97706'
    : isCancelled
    ? '#DC2626'
    : isRefunded
      ? '#92400E'
      : (trackingStages[displayStageIndex]?.color || colors.primary);
  const currentStatusLabel = isCancellationRequested
    ? 'Cancellation Requested'
    : isCancelled
    ? 'Cancelled'
    : isRefunded
      ? 'Refunded'
      : (trackingStages[displayStageIndex]?.label || 'Pending');
  const normalizedPaymentStatus = (order.paymentStatus || order.payment_status || 'pending').toLowerCase();
  const isPaymentSettled = normalizedPaymentStatus === 'paid' || normalizedPaymentStatus === 'verified';
  const paymentOption = ((order.paymentOption || order.payment_option || 'full') + '').toLowerCase();
  const isDownpaymentOrder = paymentOption === 'downpayment';
  const totalAmount = parseFloat(order.total ?? order.total_amount) || 0;
  const downpaymentAmount = parseFloat(order.payableNow ?? order.downpayment_amount) || 0;
  const remainingBalance = Math.max(0, parseFloat(order.remainingBalance ?? order.remaining_balance) || 0);
  const amountDueNow = isDownpaymentOrder
    ? (downpaymentAmount > 0 ? downpaymentAmount : Math.max(0, totalAmount - remainingBalance))
    : totalAmount;
  const paymentLabel = isDownpaymentOrder
    ? (isPaymentSettled ? (remainingBalance > 0 ? 'Downpayment Paid' : 'Fully Paid') : 'Downpayment Pending')
    : (isPaymentSettled ? 'Paid' : 'Pending');
  const canRequestRefund = !refundRequestInProgress
    && !isCancellationRequested
    && !isCancelled
    && !isRefunded
    && (
      order?.can_request_refund === true
      || order?.canRequestRefund === true
      || (['delivered', 'completed'].includes(order?.status) && isPaymentSettled)
    );

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
          <View style={[styles.orderRefStatusPill, { backgroundColor: currentStatusColor + '22' }]}>
            <View style={[styles.orderRefStatusDot, { backgroundColor: currentStatusColor }]} />
            <Text style={[styles.orderRefStatusPillText, { color: currentStatusColor }]}>
              {currentStatusLabel}
            </Text>
          </View>
        </View>

        {/* Status Summary Card */}
        <View style={styles.statusSummaryCard}>
          <View style={styles.statusSummaryItem}>
            <Ionicons name="cube-outline" size={28} color={colors.primary} style={styles.statusSummaryIconStyle} />
            <Text style={styles.statusSummaryLabel}>Status</Text>
            <Text style={styles.statusSummaryValue}>{currentStatusLabel}</Text>
          </View>
          <View style={styles.statusSummaryDivider} />
          <View style={styles.statusSummaryItem}>
            <Ionicons name="cash-outline" size={28} color={colors.primary} style={styles.statusSummaryIconStyle} />
            <Text style={styles.statusSummaryLabel}>Total</Text>
            <Text style={styles.statusSummaryValue}>₱{totalAmount.toFixed(2)}</Text>
          </View>
          <View style={styles.statusSummaryDivider} />
          <View style={styles.statusSummaryItem}>
            <Ionicons name="checkmark-circle-outline" size={28} color={isPaymentSettled ? '#6B1F1F' : '#f59e0b'} style={styles.statusSummaryIconStyle} />
            <Text style={styles.statusSummaryLabel}>Payment</Text>
            <Text style={[styles.statusSummaryValue, { color: isPaymentSettled ? '#6B1F1F' : '#f59e0b' }]}>
              {paymentLabel}
            </Text>
          </View>
        </View>

        {isCancellationRequested && (
          <View style={styles.cancelledNoticeCard}>
            <Ionicons name="time-outline" size={22} color="#B45309" />
            <View style={styles.cancelledNoticeContent}>
              <Text style={[styles.cancelledNoticeTitle, { color: '#B45309' }]}>Cancellation Pending</Text>
              <Text style={[styles.cancelledNoticeText, { color: '#78350F' }]}>Your cancellation request is pending admin review.</Text>
            </View>
          </View>
        )}

        {isCancelled && (
          <View style={styles.cancelledNoticeCard}>
            <Ionicons name="close-circle" size={22} color="#B91C1C" />
            <View style={styles.cancelledNoticeContent}>
              <Text style={styles.cancelledNoticeTitle}>Order Cancelled</Text>
              <Text style={styles.cancelledNoticeText}>
                {order.adminNotes || order.admin_notes || 'This order was cancelled by the customer.'}
              </Text>
            </View>
          </View>
        )}

        {latestRefundRequest && (
          <View style={styles.refundNoticeCard}>
            <Ionicons name="refresh-circle" size={22} color="#7C3AED" />
            <View style={styles.cancelledNoticeContent}>
              <Text style={[styles.cancelledNoticeTitle, { color: '#6D28D9' }]}>
                {refundRequestInProgress ? 'Refund Request In Progress' : 'Refund Request Update'}
              </Text>
              <Text style={[styles.cancelledNoticeText, { color: '#4C1D95' }]}>
                {latestRefundRequest?.comment || latestRefundRequest?.details || 'Your refund request is pending admin review.'}
              </Text>
              <View style={styles.refundMetaBlock}>
                <Text style={styles.refundMetaText}>Status: {formatRefundStatusLabel(latestRefundRequest?.workflow_status || latestRefundRequest?.status)}</Text>
                {latestRefundDecision ? (
                  <Text style={styles.refundMetaText}>Decision: {String(latestRefundDecision).replace(/_/g, ' ').toUpperCase()}</Text>
                ) : null}
                {latestRefundAmount !== null && latestRefundAmount !== undefined ? (
                  <Text style={styles.refundMetaText}>Approved Amount: PHP {Number(latestRefundAmount).toFixed(2)}</Text>
                ) : null}
                {latestRefundRequest?.payout_status ? (
                  <Text style={styles.refundMetaText}>Payout Status: {formatRefundStatusLabel(latestRefundRequest?.payout_status)}</Text>
                ) : null}
                {latestRefundRequest?.admin_note ? (
                  <Text style={styles.refundAdminNote}>Admin Response: {latestRefundRequest.admin_note}</Text>
                ) : null}
              </View>
              {latestRefundEvidence.length > 0 ? (
                <View style={styles.refundEvidenceList}>
                  {latestRefundEvidence.map((item, index) => (
                    <View key={`${item?.url || item?.path || 'evidence'}-${index}`} style={styles.refundEvidenceChip}>
                      <Ionicons name={item?.is_video ? 'videocam-outline' : 'image-outline'} size={14} color="#6D28D9" />
                      <Text style={styles.refundEvidenceChipText} numberOfLines={1}>Evidence {index + 1}</Text>
                    </View>
                  ))}
                </View>
              ) : null}
            </View>
          </View>
        )}

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
              const rawImage = item.product?.image;
              const baseUrl = API_CONFIG.API_BASE_URL.replace('/api/v1', '');
              const imageUrl = rawImage
                ? rawImage.startsWith('http')
                  ? rawImage
                  : rawImage.startsWith('/uploads') || rawImage.startsWith('/storage')
                    ? `${baseUrl}${rawImage}`
                    : `${baseUrl}/storage/products/${rawImage}`
                : null;
              
              return (
                <View key={index} style={styles.itemCard}>
                  <View style={styles.itemRow}>
                    {/* Product Image */}
                    {imageUrl ? (
                      <Image 
                        source={{ uri: imageUrl }}
                        style={styles.itemImage}
                        resizeMode="cover"
                        onError={() => {}}
                      />
                    ) : (
                      <View style={[styles.itemImage, styles.itemImagePlaceholder]}>
                        <Ionicons name="image-outline" size={24} color="#ccc" />
                      </View>
                    )}
                    
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
            <Text style={styles.summaryValue}>₱{(parseFloat(order.shippingFee ?? order.shipping_fee) || 0).toFixed(2)}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Payment Option</Text>
            <Text style={styles.summaryValue}>{isDownpaymentOrder ? 'Downpayment' : 'Full Payment'}</Text>
          </View>
          {isDownpaymentOrder && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Amount Due Now</Text>
              <Text style={styles.summaryValue}>₱{amountDueNow.toFixed(2)}</Text>
            </View>
          )}
          {isDownpaymentOrder && (
            <View style={styles.summaryRow}>
              <Text style={[styles.summaryLabel, { color: '#92400E' }]}>Remaining Balance</Text>
              <Text style={[styles.summaryValue, { color: '#92400E' }]}>₱{remainingBalance.toFixed(2)}</Text>
            </View>
          )}
          <View style={[styles.summaryRow, styles.summaryTotal]}>
            <Text style={styles.summaryTotalLabel}>Total</Text>
            <Text style={styles.summaryTotalValue}>₱{totalAmount.toFixed(2)}</Text>
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
        {canCancelOrder && (
          <TouchableOpacity
            style={[styles.cancelOrderButton, isCancelling && styles.cancelOrderButtonDisabled]}
            onPress={openCancelModal}
            disabled={isCancelling}
          >
            <Ionicons name="close-circle-outline" size={22} color={colors.white} />
            <Text style={styles.cancelOrderButtonText}>{isCancelling ? 'Cancelling...' : 'Cancel Order'}</Text>
          </TouchableOpacity>
        )}

        {canRequestRefund && (
          <TouchableOpacity
            style={[styles.refundOrderButton, isRequestingRefund && styles.cancelOrderButtonDisabled]}
            onPress={openRefundModal}
            disabled={isRequestingRefund}
          >
            <Ionicons name="cash-outline" size={22} color={colors.white} />
            <Text style={styles.cancelOrderButtonText}>{isRequestingRefund ? 'Submitting...' : 'Request Refund'}</Text>
          </TouchableOpacity>
        )}

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

      <Modal
        visible={showCancelModal}
        transparent
        animationType="fade"
        onRequestClose={() => setShowCancelModal(false)}
      >
        <View style={styles.cancelModalOverlay}>
          <View style={styles.cancelModalCard}>
            <Text style={styles.cancelModalTitle}>Cancel Order</Text>
            <Text style={styles.cancelModalSubtitle}>Please select a reason for cancellation.</Text>

            {CANCEL_REASONS.map((reason) => {
              const isSelected = selectedCancelReason === reason;
              return (
                <TouchableOpacity
                  key={reason}
                  style={[styles.cancelReasonOption, isSelected && styles.cancelReasonOptionSelected]}
                  onPress={() => setSelectedCancelReason(reason)}
                  activeOpacity={0.8}
                >
                  <Text style={[styles.cancelReasonText, isSelected && styles.cancelReasonTextSelected]}>{reason}</Text>
                  {isSelected ? <Ionicons name="checkmark-circle" size={18} color={colors.primary} /> : null}
                </TouchableOpacity>
              );
            })}

            {selectedCancelReason === 'Other' && (
              <TextInput
                value={customCancelReason}
                onChangeText={setCustomCancelReason}
                style={styles.cancelReasonInput}
                placeholder="Type your reason"
                placeholderTextColor="#9CA3AF"
              />
            )}

            <View style={styles.cancelModalActions}>
              <TouchableOpacity
                style={styles.cancelModalSecondaryButton}
                onPress={() => setShowCancelModal(false)}
                disabled={isCancelling}
              >
                <Text style={styles.cancelModalSecondaryText}>Close</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.cancelModalPrimaryButton, isCancelling && styles.cancelOrderButtonDisabled]}
                onPress={confirmCancelOrder}
                disabled={isCancelling}
              >
                <Text style={styles.cancelModalPrimaryText}>{isCancelling ? 'Cancelling...' : 'Confirm'}</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      <Modal
        visible={showRefundModal}
        transparent
        animationType="fade"
        onRequestClose={() => setShowRefundModal(false)}
      >
        <View style={styles.cancelModalOverlay}>
          <View style={styles.cancelModalCard}>
            <Text style={styles.cancelModalTitle}>Request Refund</Text>
            <Text style={styles.cancelModalSubtitle}>Tell us why you are requesting a refund.</Text>

            {REFUND_REASONS.map((reason) => {
              const isSelected = selectedRefundReason === reason;
              return (
                <TouchableOpacity
                  key={reason}
                  style={[styles.cancelReasonOption, isSelected && styles.cancelReasonOptionSelected]}
                  onPress={() => setSelectedRefundReason(reason)}
                  activeOpacity={0.8}
                >
                  <Text style={[styles.cancelReasonText, isSelected && styles.cancelReasonTextSelected]}>{reason}</Text>
                  {isSelected ? <Ionicons name="checkmark-circle" size={18} color={colors.primary} /> : null}
                </TouchableOpacity>
              );
            })}

            {selectedRefundReason === 'Other' && (
              <TextInput
                value={customRefundReason}
                onChangeText={setCustomRefundReason}
                style={styles.cancelReasonInput}
                placeholder="Type your refund reason"
                placeholderTextColor="#9CA3AF"
              />
            )}

            <TextInput
              value={refundComment}
              onChangeText={setRefundComment}
              style={[styles.cancelReasonInput, { minHeight: 88, textAlignVertical: 'top' }]}
              placeholder="Describe the issue and what happened"
              placeholderTextColor="#9CA3AF"
              multiline
            />

            <TouchableOpacity
              style={styles.refundEvidenceButton}
              onPress={pickRefundEvidence}
              disabled={isRequestingRefund}
            >
              <Ionicons name="images-outline" size={18} color="#6D28D9" />
              <Text style={styles.refundEvidenceButtonText}>Add Photo/Video Proof ({refundEvidence.length}/5)</Text>
            </TouchableOpacity>

            {selectedRefundReason === 'Damaged item' && !hasVideoEvidence(refundEvidence) ? (
              <Text style={styles.refundEvidenceHint}>Damaged item requires at least one video proof.</Text>
            ) : null}

            {refundEvidence.length > 0 ? (
              <View style={styles.refundSelectedEvidenceList}>
                {refundEvidence.map((asset, index) => (
                  <View key={`${asset.uri}-${index}`} style={styles.refundSelectedEvidenceRow}>
                    <View style={styles.refundSelectedEvidenceInfo}>
                      <Ionicons
                        name={hasVideoEvidence([asset]) ? 'videocam-outline' : 'image-outline'}
                        size={16}
                        color="#6D28D9"
                      />
                      <Text style={styles.refundSelectedEvidenceText} numberOfLines={1}>
                        {asset.fileName || `Evidence ${index + 1}`}
                      </Text>
                    </View>
                    <TouchableOpacity onPress={() => removeRefundEvidence(asset.uri)} disabled={isRequestingRefund}>
                      <Ionicons name="close-circle" size={18} color="#B91C1C" />
                    </TouchableOpacity>
                  </View>
                ))}
              </View>
            ) : null}

            <View style={styles.cancelModalActions}>
              <TouchableOpacity
                style={styles.cancelModalSecondaryButton}
                onPress={() => setShowRefundModal(false)}
                disabled={isRequestingRefund}
              >
                <Text style={styles.cancelModalSecondaryText}>Close</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.refundModalPrimaryButton, isRequestingRefund && styles.cancelOrderButtonDisabled]}
                onPress={confirmRefundRequest}
                disabled={isRequestingRefund}
              >
                <Text style={styles.cancelModalPrimaryText}>{isRequestingRefund ? 'Submitting...' : 'Submit'}</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
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
  itemImagePlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
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
  cancelOrderButton: {
    backgroundColor: '#DC2626',
    borderRadius: 14,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
  },
  refundOrderButton: {
    backgroundColor: '#7C3AED',
    borderRadius: 14,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
  },
  cancelOrderButtonDisabled: {
    opacity: 0.7,
  },
  cancelOrderButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '700',
    marginLeft: 10,
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
  cancelledNoticeCard: {
    backgroundColor: '#FEF2F2',
    borderColor: '#FCA5A5',
    borderWidth: 1,
    borderRadius: 12,
    padding: 14,
    marginBottom: 16,
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  refundNoticeCard: {
    backgroundColor: '#F5F3FF',
    borderColor: '#C4B5FD',
    borderWidth: 1,
    borderRadius: 12,
    padding: 14,
    marginBottom: 16,
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  refundMetaBlock: {
    marginTop: 8,
    borderTopWidth: 1,
    borderTopColor: '#DDD6FE',
    paddingTop: 8,
  },
  refundMetaText: {
    fontSize: 12,
    color: '#5B21B6',
    marginBottom: 2,
    fontWeight: '600',
  },
  refundAdminNote: {
    marginTop: 4,
    fontSize: 12,
    color: '#4C1D95',
    lineHeight: 18,
  },
  refundEvidenceList: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginTop: 8,
  },
  refundEvidenceChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EDE9FE',
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 4,
    gap: 4,
  },
  refundEvidenceChipText: {
    fontSize: 11,
    color: '#5B21B6',
    fontWeight: '700',
    maxWidth: 110,
  },
  cancelledNoticeContent: {
    flex: 1,
    marginLeft: 10,
  },
  cancelledNoticeTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#B91C1C',
    marginBottom: 4,
  },
  cancelledNoticeText: {
    fontSize: 12,
    color: '#7F1D1D',
    lineHeight: 18,
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
  cancelModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'center',
    padding: 18,
  },
  cancelModalCard: {
    backgroundColor: colors.white,
    borderRadius: 16,
    padding: 16,
  },
  cancelModalTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: colors.text,
    marginBottom: 4,
  },
  cancelModalSubtitle: {
    fontSize: 13,
    color: colors.textLight,
    marginBottom: 12,
  },
  cancelReasonOption: {
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 10,
    paddingVertical: 10,
    paddingHorizontal: 12,
    marginBottom: 8,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cancelReasonOptionSelected: {
    borderColor: colors.primary,
    backgroundColor: '#FFF5F5',
  },
  cancelReasonText: {
    fontSize: 13,
    color: colors.text,
    fontWeight: '500',
    flex: 1,
    paddingRight: 8,
  },
  cancelReasonTextSelected: {
    color: colors.primary,
    fontWeight: '700',
  },
  cancelReasonInput: {
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 10,
    marginTop: 6,
    marginBottom: 6,
    fontSize: 13,
    color: colors.text,
  },
  refundEvidenceButton: {
    marginTop: 8,
    marginBottom: 4,
    borderWidth: 1,
    borderColor: '#C4B5FD',
    borderRadius: 10,
    paddingVertical: 10,
    paddingHorizontal: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#F5F3FF',
    gap: 8,
  },
  refundEvidenceButtonText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#6D28D9',
  },
  refundEvidenceHint: {
    fontSize: 11,
    color: '#B91C1C',
    marginBottom: 4,
  },
  refundSelectedEvidenceList: {
    marginTop: 4,
    marginBottom: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 10,
    padding: 8,
    gap: 6,
  },
  refundSelectedEvidenceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 2,
  },
  refundSelectedEvidenceInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    flex: 1,
    marginRight: 8,
  },
  refundSelectedEvidenceText: {
    fontSize: 12,
    color: '#374151',
    fontWeight: '600',
    flex: 1,
  },
  cancelModalActions: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginTop: 10,
    gap: 10,
  },
  cancelModalSecondaryButton: {
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#D1D5DB',
  },
  cancelModalSecondaryText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#374151',
  },
  cancelModalPrimaryButton: {
    backgroundColor: '#DC2626',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 10,
  },
  refundModalPrimaryButton: {
    backgroundColor: '#7C3AED',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 10,
  },
  cancelModalPrimaryText: {
    fontSize: 13,
    fontWeight: '700',
    color: colors.white,
  },
});

export default OrderDetailsScreen;