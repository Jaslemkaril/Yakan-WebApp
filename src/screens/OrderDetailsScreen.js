// src/screens/OrderDetailsScreen.js - Fixed version
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import colors from '../constants/colors';
import { trackingStages } from '../constants/tracking';

const OrderDetailsScreen = ({ navigation, route }) => {
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadOrderDetails();
  }, []);

  const loadOrderDetails = async () => {
    try {
      // First, try to get from route params
      if (route.params?.orderData) {
        console.log('Order from params:', route.params.orderData);
        setOrder(route.params.orderData);
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
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Text style={styles.backButtonHeader}>← Back</Text>
        </TouchableOpacity>
        <Text style={styles.title}>Order Details</Text>
        <View style={{ width: 50 }} />
      </View>

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {/* Order Reference Card */}
        <View style={styles.orderRefCard}>
          <View>
            <Text style={styles.orderRefLabel}>Order Reference</Text>
            <Text style={styles.orderRefNumber}>{order.orderRef}</Text>
            <Text style={styles.orderDate}>{formatDate(order.date)}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: trackingStages[displayStageIndex].color }]}>
            <Text style={styles.statusText}>
              {trackingStages[displayStageIndex].label}
            </Text>
          </View>
        </View>

        {/* Tracking Timeline */}
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

                  {/* Stage Indicator */}
                  <View
                    style={[
                      styles.stageIndicator,
                      isCompleted && styles.stageIndicatorCompleted,
                      isCurrent && styles.stageIndicatorCurrent,
                      { borderColor: stage.color, backgroundColor: isCompleted ? stage.color : colors.white },
                    ]}
                  >
                    <Text style={[styles.stageIcon, isCompleted && styles.stageIconCompleted]}>
                      {isCompleted ? stage.icon : index + 1}
                    </Text>
                  </View>

                  {/* Stage Info */}
                  <View style={styles.stageInfo}>
                    <Text style={[styles.stageLabel, isCompleted && styles.stageLabelCompleted]}>
                      {stage.label}
                    </Text>
                    <Text style={styles.stageDescription}>{stage.description}</Text>
                  </View>
                </View>
              );
            })}
          </View>
        </View>

        {/* Items Summary */}
        <View style={styles.itemsSection}>
          <Text style={styles.sectionTitle}>Items</Text>
          
          {order.items && order.items.length > 0 ? (
            order.items.map((item, index) => (
              <View key={index} style={styles.itemCard}>
                <View style={styles.itemHeader}>
                  <Text style={styles.itemName}>{item.name}</Text>
                  <Text style={styles.itemPrice}>₱{(item.price || 0).toFixed(2)}</Text>
                </View>
                <Text style={styles.itemQuantity}>Quantity: {item.quantity}</Text>
                <Text style={styles.itemTotal}>
                  Subtotal: ₱{((item.price || 0) * (item.quantity || 0)).toFixed(2)}
                </Text>
              </View>
            ))
          ) : (
            <View style={styles.emptyCard}>
              <Text style={styles.emptyText}>No items in order</Text>
              <Text style={styles.emptySubtext}>Order data may not have loaded correctly</Text>
            </View>
          )}
        </View>

        {/* Order Summary */}
        <View style={styles.summaryCard}>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>₱{(order.subtotal || 0).toFixed(2)}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Shipping</Text>
            <Text style={styles.summaryValue}>₱{(order.shippingFee || 0).toFixed(2)}</Text>
          </View>
          <View style={[styles.summaryRow, styles.summaryTotal]}>
            <Text style={styles.summaryTotalLabel}>Total</Text>
            <Text style={styles.summaryTotalValue}>₱{(order.total || 0).toFixed(2)}</Text>
          </View>
        </View>

        {/* Shipping Address */}
        {order.shippingAddress && (
          <View style={styles.addressSection}>
            <Text style={styles.sectionTitle}>Delivery Address</Text>
            <View style={styles.addressCard}>
              <Text style={styles.addressLabel}>🏠 Delivery Address</Text>
              <Text style={styles.addressName}>{order.shippingAddress.fullName || 'N/A'}</Text>
              <Text style={styles.addressText}>{order.shippingAddress.phoneNumber || 'N/A'}</Text>
              <Text style={styles.addressText}>
                {order.shippingAddress.street || 'N/A'}
              </Text>
              <Text style={styles.addressText}>
                {order.shippingAddress.city || 'N/A'}, {order.shippingAddress.province || 'N/A'} {order.shippingAddress.postalCode || ''}
              </Text>
            </View>
          </View>
        )}

        {/* Action Buttons */}
        <View style={styles.actionsSection}>
          <TouchableOpacity style={styles.actionButton} onPress={handleContactSeller}>
            <Text style={styles.actionButtonIcon}>💬</Text>
            <Text style={styles.actionButtonText}>Contact Seller</Text>
          </TouchableOpacity>

          {currentStageIndex >= trackingStages.length - 1 && (
            <TouchableOpacity style={styles.actionButton} onPress={handleReturnRequest}>
              <Text style={styles.actionButtonIcon}>↩️</Text>
              <Text style={styles.actionButtonText}>Return/Exchange</Text>
            </TouchableOpacity>
          )}
        </View>

        {/* Help Section */}
        <TouchableOpacity style={styles.helpSection}>
          <Text style={styles.helpIcon}>❓</Text>
          <Text style={styles.helpText}>Need help with your order?</Text>
          <Text style={styles.helpArrow}>→</Text>
        </TouchableOpacity>

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
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
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
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 15,
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
    left: 20,
    top: 50,
    width: 3,
    height: 60,
    backgroundColor: '#DDD',
  },
  timelineLineCompleted: {
    backgroundColor: colors.primary,
  },
  stageIndicator: {
    width: 40,
    height: 40,
    borderRadius: 20,
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
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3,
    elevation: 5,
  },
  stageIcon: {
    fontSize: 18,
    fontWeight: 'bold',
    color: colors.text,
  },
  stageIconCompleted: {
    color: colors.white,
  },
  stageInfo: {
    flex: 1,
    justifyContent: 'center',
  },
  stageLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.textLight,
    marginBottom: 4,
  },
  stageLabelCompleted: {
    color: colors.text,
  },
  stageDescription: {
    fontSize: 12,
    color: colors.textLight,
  },
  itemsSection: {
    marginBottom: 20,
  },
  itemCard: {
    backgroundColor: colors.white,
    borderRadius: 10,
    padding: 12,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: colors.border,
  },
  itemHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  itemName: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    flex: 1,
  },
  itemPrice: {
    fontSize: 14,
    fontWeight: 'bold',
    color: colors.primary,
  },
  itemQuantity: {
    fontSize: 12,
    color: colors.textLight,
    marginBottom: 4,
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
    borderRadius: 12,
    padding: 15,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: colors.border,
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
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
  },
  summaryTotalValue: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.primary,
  },
  addressSection: {
    marginBottom: 20,
  },
  addressCard: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 15,
    borderWidth: 1,
    borderColor: colors.border,
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
});

export default OrderDetailsScreen;