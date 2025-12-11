// src/screens/PaymentScreen.js
import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  TextInput,
  Image,
} from 'react-native';
import { useCart } from '../context/CartContext';
import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from '../services/api';
import NotificationService from '../services/notificationService';
import colors from '../constants/colors';

// Payment account details (customize these for your store)
const PAYMENT_ACCOUNTS = {
  gcash: {
    number: '0917-123-4567',
    name: 'Your Store Name',
  },
  bank_transfer: {
    bankName: 'BDO',
    accountNumber: '1234-5678-9012',
    accountName: 'Your Store Name',
  },
};

export default function PaymentScreen({ navigation, route }) {
  const { orderData } = route.params || {};
  
  if (!orderData) {
    return (
      <View style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()}>
            <Text style={styles.backButton}>← Back</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Payment</Text>
        </View>
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>Order data not found. Please try again.</Text>
        </View>
      </View>
    );
  }
  
  const { clearCart } = useCart();
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState(null);
  const [showPaymentInstructions, setShowPaymentInstructions] = useState(false);
  const [referenceNumber, setReferenceNumber] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);

  const paymentMethods = [
    {
      id: 'gcash',
      name: 'GCash',
      description: 'Pay securely with GCash mobile wallet',
      icon: '📱',
      fee: 0,
    },
    {
      id: 'bank_transfer',
      name: 'Bank Transfer',
      description: 'Direct transfer to our bank account',
      icon: '🏦',
      fee: 0,
    },
  ];

  const updateOrderInStorage = async (updatedOrder) => {
    try {
      const savedOrders = await AsyncStorage.getItem('pendingOrders');
      const orders = savedOrders ? JSON.parse(savedOrders) : [];
      const updatedOrders = orders.map(o => o.orderRef === updatedOrder.orderRef ? updatedOrder : o);
      await AsyncStorage.setItem('pendingOrders', JSON.stringify(updatedOrders));
    } catch (error) {
      console.error('Failed to update order in local storage:', error);
    }
  };

  const handleSelectPaymentMethod = (methodId) => {
    setSelectedPaymentMethod(methodId);
  };

  const handleProceedToInstructions = () => {
    if (!selectedPaymentMethod) {
      Alert.alert('Error', 'Please select a payment method');
      return;
    }
    
    // Show payment instructions
    setShowPaymentInstructions(true);
  };

  const handleConfirmPayment = async () => {
    setIsProcessing(true);

    try {
      const apiOrderData = {
        customer_name: orderData.shippingAddress.fullName,
        customer_email: 'mobile@user.com',
        customer_phone: orderData.shippingAddress.phoneNumber,
        shipping_address: `${orderData.shippingAddress.street}, ${orderData.shippingAddress.city}, ${orderData.shippingAddress.province} ${orderData.shippingAddress.postalCode}`,
        delivery_address: `${orderData.shippingAddress.street}, ${orderData.shippingAddress.city}, ${orderData.shippingAddress.province} ${orderData.shippingAddress.postalCode}`,
        payment_method: selectedPaymentMethod,
        payment_status: 'paid',
        payment_reference: referenceNumber || null,
        items: orderData.items.map(item => ({
          product_id: item.id,
          quantity: item.quantity || 1,
          price: item.price,
        })),
        subtotal: orderData.subtotal,
        shipping_fee: orderData.shippingFee,
        total: orderData.total,
        total_amount: orderData.total,
        notes: 'Order from mobile app',
      };

      console.log('🔵 Sending order to API:', apiOrderData);

      const response = await ApiService.createOrder(apiOrderData);
      
      console.log('🔵 Order created successfully:', response);

      const finalOrderData = {
        ...orderData,
        paymentMethod: selectedPaymentMethod,
        paymentReference: referenceNumber,
        status: 'paid',
        backendOrderId: response.data?.id,
      };

      await updateOrderInStorage(finalOrderData);
      
      // 🔔 Notify admin via notification service
      console.log('🔔 Triggering admin notification for new order:', finalOrderData.orderRef);
      NotificationService.notifyNewOrder({
        orderId: response.data?.id || finalOrderData.orderRef,
        orderRef: finalOrderData.orderRef,
        customerName: orderData.shippingAddress.fullName,
        customerPhone: orderData.shippingAddress.phoneNumber,
        total: orderData.total,
        itemCount: orderData.items.length,
        paymentMethod: selectedPaymentMethod,
        items: orderData.items,
        shippingAddress: orderData.shippingAddress,
        status: 'pending_confirmation',
      });

      // Start polling for order status updates
      if (response.data?.id) {
        NotificationService.startOrderStatusPolling(
          response.data.id,
          (updatedOrder) => {
            console.log('📦 Order status updated:', updatedOrder.status);
            // Update local order data when status changes
            updateOrderInStorage({
              ...finalOrderData,
              status: updatedOrder.status,
            });
          }
        );
      }

      setIsProcessing(false);
      clearCart();
      navigation.navigate('OrderDetails', { orderData: finalOrderData });
    } catch (error) {
      console.error('🔴 Error creating order:', error);
      setIsProcessing(false);
      
      const finalOrderData = {
        ...orderData,
        paymentMethod: selectedPaymentMethod,
        paymentReference: referenceNumber,
        status: 'paid',
      };
      await updateOrderInStorage(finalOrderData);
      
      // Still notify admin even if backend sync failed (order saved locally)
      console.log('🔔 Local order saved, notifying admin');
      NotificationService.notifyNewOrder({
        orderId: finalOrderData.orderRef,
        orderRef: finalOrderData.orderRef,
        customerName: orderData.shippingAddress.fullName,
        customerPhone: orderData.shippingAddress.phoneNumber,
        total: orderData.total,
        itemCount: orderData.items.length,
        paymentMethod: selectedPaymentMethod,
        items: orderData.items,
        shippingAddress: orderData.shippingAddress,
        status: 'pending_confirmation',
        syncStatus: 'pending',
      });
      
      Alert.alert(
        'Order Saved',
        'Your order has been saved. It will sync when connection is restored.\n\nThe admin will be notified once synced.',
        [
          {
            text: 'View Order',
            onPress: () => {
              clearCart();
              navigation.navigate('OrderDetails', { orderData: finalOrderData });
            },
          },
        ]
      );
    }
  };

  const total = (orderData.subtotal || 0) + (orderData.shippingFee || 0);
  const selectedMethod = paymentMethods.find(m => m.id === selectedPaymentMethod);
  const finalTotal = total + (selectedMethod?.fee || 0);

  // Payment Instructions Screen
  if (showPaymentInstructions && selectedPaymentMethod) {
    return (
      <View style={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => setShowPaymentInstructions(false)}>
            <Text style={styles.backButton}>← Back</Text>
          </TouchableOpacity>
          <Text style={styles.title}>
            {selectedPaymentMethod === 'gcash' ? 'GCash Payment' : 'Bank Transfer'}
          </Text>
          <View style={{ width: 50 }} />
        </View>

        {/* Success Banner */}
        <View style={styles.successBanner}>
          <Text style={styles.successIcon}>✓</Text>
          <Text style={styles.successText}>Order placed! Complete payment online.</Text>
        </View>

        <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
          <View style={styles.instructionsContainer}>
            {/* Left Side - Instructions */}
            <View style={styles.instructionsCard}>
              <View style={styles.instructionsHeader}>
                <Text style={styles.instructionsIcon}>
                  {selectedPaymentMethod === 'gcash' ? '📱' : '🏦'}
                </Text>
                <View>
                  <Text style={styles.instructionsTitle}>
                    {selectedPaymentMethod === 'gcash' ? 'GCash Payment Instructions' : 'Bank Transfer Instructions'}
                  </Text>
                  <Text style={styles.instructionsSubtitle}>Follow these steps to complete your payment</Text>
                </View>
              </View>

              {/* Step 1 */}
              <View style={styles.stepItem}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>1</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>
                    {selectedPaymentMethod === 'gcash' ? 'Open your GCash App' : 'Open your Banking App'}
                  </Text>
                  <Text style={styles.stepDescription}>
                    {selectedPaymentMethod === 'gcash' 
                      ? 'Launch the GCash mobile application on your phone.'
                      : 'Open your mobile banking app or go to your bank\'s website.'}
                  </Text>
                </View>
              </View>

              {/* Step 2 */}
              <View style={styles.stepItem}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>2</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>
                    {selectedPaymentMethod === 'gcash' ? 'Send Money to this GCash Number' : 'Transfer to this Account'}
                  </Text>
                  {selectedPaymentMethod === 'gcash' ? (
                    <View style={styles.accountBox}>
                      <Text style={styles.accountLabel}>GCash Number</Text>
                      <Text style={styles.accountNumber}>{PAYMENT_ACCOUNTS.gcash.number}</Text>
                      <Text style={styles.accountName}>Account Name: {PAYMENT_ACCOUNTS.gcash.name}</Text>
                    </View>
                  ) : (
                    <View style={styles.accountBox}>
                      <Text style={styles.accountLabel}>{PAYMENT_ACCOUNTS.bank_transfer.bankName}</Text>
                      <Text style={styles.accountNumber}>{PAYMENT_ACCOUNTS.bank_transfer.accountNumber}</Text>
                      <Text style={styles.accountName}>Account Name: {PAYMENT_ACCOUNTS.bank_transfer.accountName}</Text>
                    </View>
                  )}
                </View>
              </View>

              {/* Step 3 */}
              <View style={styles.stepItem}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>3</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>Enter the exact amount</Text>
                  <View style={styles.amountBox}>
                    <Text style={styles.amountLabel}>Amount to Send</Text>
                    <Text style={styles.amountValue}>₱{finalTotal.toFixed(2)}</Text>
                  </View>
                </View>
              </View>

              {/* Step 4 */}
              <View style={styles.stepItem}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>4</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>Add Reference Number</Text>
                  <Text style={styles.stepDescription}>
                    Include this reference in your {selectedPaymentMethod === 'gcash' ? 'GCash' : 'bank transfer'} message:
                  </Text>
                  <View style={styles.referenceBox}>
                    <Text style={styles.referenceLabel}>Reference Number</Text>
                    <Text style={styles.referenceValue}>{orderData.orderRef}</Text>
                  </View>
                </View>
              </View>

              {/* Step 5 */}
              <View style={styles.stepItem}>
                <View style={styles.stepNumber}>
                  <Text style={styles.stepNumberText}>5</Text>
                </View>
                <View style={styles.stepContent}>
                  <Text style={styles.stepTitle}>Submit Payment Confirmation</Text>
                  <Text style={styles.stepDescription}>
                    After sending the payment, click the button below to confirm. Your payment will be verified automatically and your order will start processing immediately!
                  </Text>
                </View>
              </View>
            </View>

            {/* Order Summary Card */}
            <View style={styles.orderSummaryCard}>
              <Text style={styles.orderSummaryTitle}>Order Summary</Text>
              
              {/* Product Items with Images */}
              <View style={styles.productsList}>
                {orderData.items?.map((item, index) => {
                  // Handle image source - check if it's a valid URL string
                  const imageSource = item.image && typeof item.image === 'string' && item.image.startsWith('http')
                    ? { uri: item.image }
                    : null;
                  
                  return (
                    <View key={item.id || index} style={styles.productItem}>
                      {imageSource ? (
                        <Image
                          source={imageSource}
                          style={styles.productImage}
                          resizeMode="cover"
                        />
                      ) : (
                        <View style={styles.productImagePlaceholder}>
                          <Text style={styles.productImagePlaceholderText}>📦</Text>
                        </View>
                      )}
                      <View style={styles.productDetails}>
                        <Text style={styles.productName} numberOfLines={2}>{item.name}</Text>
                        <Text style={styles.productQuantity}>Qty: {item.quantity || 1}</Text>
                      </View>
                      <Text style={styles.productPrice}>₱{((item.price || 0) * (item.quantity || 1)).toFixed(2)}</Text>
                    </View>
                  );
                })}
              </View>

              <View style={styles.orderSummaryDivider} />

              {/* Shipping Address */}
              <View style={styles.addressSection}>
                <View style={styles.addressHeader}>
                  <Text style={styles.addressIcon}>
                    {orderData.deliveryOption === 'pickup' ? '🏪' : '📍'}
                  </Text>
                  <Text style={styles.addressTitle}>
                    {orderData.deliveryOption === 'pickup' ? 'Pick Up Details' : 'Delivery Address'}
                  </Text>
                </View>
                {orderData.deliveryOption === 'pickup' ? (
                  <View>
                    <Text style={styles.addressName}>{orderData.shippingAddress?.fullName}</Text>
                    <Text style={styles.addressPhone}>{orderData.shippingAddress?.phoneNumber}</Text>
                    <Text style={styles.pickupNote}>Pick up at store location</Text>
                  </View>
                ) : (
                  <View>
                    <Text style={styles.addressName}>{orderData.shippingAddress?.fullName}</Text>
                    <Text style={styles.addressPhone}>{orderData.shippingAddress?.phoneNumber}</Text>
                    <View style={styles.addressFullContainer}>
                      <View style={styles.addressRow}>
                        <Text style={styles.addressFieldLabel}>Street:</Text>
                        <Text style={styles.addressFieldValue}>{orderData.shippingAddress?.street}</Text>
                      </View>
                      {orderData.shippingAddress?.barangay && (
                        <View style={styles.addressRow}>
                          <Text style={styles.addressFieldLabel}>Barangay:</Text>
                          <Text style={styles.addressFieldValue}>{orderData.shippingAddress?.barangay}</Text>
                        </View>
                      )}
                      <View style={styles.addressRow}>
                        <Text style={styles.addressFieldLabel}>City:</Text>
                        <Text style={styles.addressFieldValue}>{orderData.shippingAddress?.city}</Text>
                      </View>
                      <View style={styles.addressRow}>
                        <Text style={styles.addressFieldLabel}>Province:</Text>
                        <Text style={styles.addressFieldValue}>{orderData.shippingAddress?.province}</Text>
                      </View>
                      <View style={styles.addressRow}>
                        <Text style={styles.addressFieldLabel}>Postal Code:</Text>
                        <Text style={styles.addressFieldValue}>{orderData.shippingAddress?.postalCode}</Text>
                      </View>
                    </View>
                  </View>
                )}
              </View>

              <View style={styles.orderSummaryDivider} />
              
              <View style={styles.orderSummaryRow}>
                <Text style={styles.orderSummaryLabel}>Subtotal</Text>
                <Text style={styles.orderSummaryValue}>₱{(orderData.subtotal || 0).toFixed(2)}</Text>
              </View>
              
              <View style={styles.orderSummaryRow}>
                <Text style={styles.orderSummaryLabel}>Shipping</Text>
                <Text style={styles.orderSummaryValue}>₱{(orderData.shippingFee || 0).toFixed(2)}</Text>
              </View>
              
              <View style={styles.orderSummaryDivider} />
              
              <View style={styles.orderSummaryRow}>
                <Text style={styles.orderSummaryTotalLabel}>Total</Text>
                <Text style={styles.orderSummaryTotalValue}>₱{finalTotal.toFixed(2)}</Text>
              </View>

              <View style={styles.orderInfoRow}>
                <Text style={styles.orderInfoLabel}>Order ID:</Text>
                <Text style={styles.orderInfoValue}>{orderData.orderRef}</Text>
              </View>

              <View style={styles.orderInfoRow}>
                <Text style={styles.orderInfoLabel}>Delivery Option:</Text>
                <Text style={styles.orderInfoValue}>
                  {orderData.deliveryOption === 'pickup' ? 'Pick Up' : 'Delivery'}
                </Text>
              </View>

              <View style={styles.orderInfoRow}>
                <Text style={styles.orderInfoLabel}>Payment Method:</Text>
                <Text style={styles.orderInfoValue}>{selectedMethod?.name}</Text>
              </View>

              <View style={styles.statusBadge}>
                <Text style={styles.statusBadgeText}>Pending</Text>
              </View>

              {/* Quick Reference */}
              <View style={styles.quickReferenceCard}>
                <Text style={styles.quickReferenceTitle}>QUICK REFERENCE</Text>
                
                <View style={styles.quickReferenceRow}>
                  <Text style={styles.quickReferenceLabel}>Send to:</Text>
                  <Text style={styles.quickReferenceValue}>
                    {selectedPaymentMethod === 'gcash' 
                      ? PAYMENT_ACCOUNTS.gcash.number 
                      : PAYMENT_ACCOUNTS.bank_transfer.accountNumber}
                  </Text>
                </View>
                
                <View style={styles.quickReferenceRow}>
                  <Text style={styles.quickReferenceLabel}>Amount:</Text>
                  <Text style={styles.quickReferenceAmountValue}>₱{finalTotal.toFixed(2)}</Text>
                </View>
                
                <View style={styles.quickReferenceRow}>
                  <Text style={styles.quickReferenceLabel}>Reference:</Text>
                  <Text style={styles.quickReferenceValue}>{orderData.orderRef}</Text>
                </View>
              </View>
            </View>
          </View>

          {/* Notices */}
          <View style={styles.noticeCard}>
            <View style={styles.noticeIconContainer}>
              <Text style={styles.noticeIcon}>✓</Text>
            </View>
            <View style={styles.noticeContent}>
              <Text style={styles.noticeTitle}>Instant Payment Verification</Text>
              <Text style={styles.noticeDescription}>
                Your order will be processed immediately once you submit your payment confirmation. No waiting time needed!
              </Text>
            </View>
          </View>

          <View style={styles.warningCard}>
            <View style={styles.warningIconContainer}>
              <Text style={styles.warningIcon}>⚠️</Text>
            </View>
            <View style={styles.noticeContent}>
              <Text style={styles.warningTitle}>Important Reminder</Text>
              <Text style={styles.noticeDescription}>
                Please make sure to send the EXACT amount (₱{finalTotal.toFixed(2)}) and include the reference number ({orderData.orderRef}) in your {selectedPaymentMethod === 'gcash' ? 'GCash' : 'bank transfer'} message.
              </Text>
            </View>
          </View>

          {/* Reference Number Input */}
          <View style={styles.referenceInputCard}>
            <Text style={styles.referenceInputLabel}>
              {selectedPaymentMethod === 'gcash' ? 'GCash' : 'Bank'} Reference Number (optional)
            </Text>
            <TextInput
              style={styles.referenceInput}
              placeholder={`Enter ${selectedPaymentMethod === 'gcash' ? 'GCash' : 'bank'} reference number`}
              placeholderTextColor="#999"
              value={referenceNumber}
              onChangeText={setReferenceNumber}
            />
          </View>

          <View style={{ height: 100 }} />
        </ScrollView>

        {/* Footer Buttons */}
        <View style={styles.footer}>
          <TouchableOpacity
            style={styles.confirmPaymentButton}
            onPress={handleConfirmPayment}
            disabled={isProcessing}
          >
            {isProcessing ? (
              <ActivityIndicator color={colors.white} />
            ) : (
              <Text style={styles.confirmPaymentButtonText}>
                I have paid via {selectedMethod?.name}
              </Text>
            )}
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.viewOrderButton}
            onPress={() => {
              navigation.navigate('TrackOrders');
            }}
          >
            <Text style={styles.viewOrderButtonText}>View My Order</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  // Payment Method Selection Screen
  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Text style={styles.backButton}>← Back</Text>
        </TouchableOpacity>
        <Text style={styles.title}>Payment Method</Text>
        <View style={{ width: 50 }} />
      </View>

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {/* Order Summary */}
        <View style={styles.summaryCard}>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>₱{(orderData.subtotal || 0).toFixed(2)}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Shipping</Text>
            <Text style={styles.summaryValue}>₱{(orderData.shippingFee || 0).toFixed(2)}</Text>
          </View>
          {selectedMethod && selectedMethod.fee > 0 && (
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Payment Fee</Text>
              <Text style={styles.summaryValue}>₱{selectedMethod.fee.toFixed(2)}</Text>
            </View>
          )}
          <View style={[styles.summaryRow, styles.summaryTotal]}>
            <Text style={styles.summaryTotalLabel}>Total</Text>
            <Text style={styles.summaryTotalValue}>₱{finalTotal.toFixed(2)}</Text>
          </View>
        </View>

        {/* Payment Methods */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Select Payment Method</Text>
          
          {paymentMethods.map((method) => (
            <TouchableOpacity
              key={method.id}
              style={[
                styles.paymentMethod,
                selectedPaymentMethod === method.id && styles.paymentMethodSelected,
              ]}
              onPress={() => handleSelectPaymentMethod(method.id)}
            >
              <View style={styles.paymentMethodIcon}>
                <Text style={styles.methodIcon}>{method.icon}</Text>
              </View>
              
              <View style={styles.paymentMethodInfo}>
                <Text style={styles.methodName}>{method.name}</Text>
                <Text style={styles.methodDescription}>{method.description}</Text>
              </View>

              {method.fee > 0 && (
                <View style={styles.methodFee}>
                  <Text style={styles.feeText}>+₱{method.fee}</Text>
                </View>
              )}

              <View style={[
                styles.radioOuter,
                selectedPaymentMethod === method.id && styles.radioOuterSelected
              ]}>
                {selectedPaymentMethod === method.id && (
                  <View style={styles.radioInner} />
                )}
              </View>
            </TouchableOpacity>
          ))}
        </View>

        {/* Security Notice */}
        <View style={styles.securityCard}>
          <Text style={styles.securityIcon}>🔒</Text>
          <Text style={styles.securityText}>Your payment information is encrypted and secure</Text>
        </View>
      </ScrollView>

      {/* Continue Button */}
      <View style={styles.footer}>
        <TouchableOpacity
          style={[
            styles.payButton,
            !selectedPaymentMethod && styles.payButtonDisabled,
          ]}
          onPress={handleProceedToInstructions}
          disabled={!selectedPaymentMethod}
        >
          <Text style={styles.payButtonText}>
            Continue to Payment - ₱{finalTotal.toFixed(2)}
          </Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    fontSize: 16,
    color: colors.text,
    textAlign: 'center',
  },
  header: {
    backgroundColor: colors.primary,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 15,
    paddingTop: 50,
  },
  backButton: {
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
  
  // Success Banner
  successBanner: {
    backgroundColor: '#e8f5e9',
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#c8e6c9',
  },
  successIcon: {
    color: '#4caf50',
    fontSize: 18,
    marginRight: 10,
    fontWeight: 'bold',
  },
  successText: {
    color: '#2e7d32',
    fontSize: 14,
    fontWeight: '500',
  },

  // Instructions Container
  instructionsContainer: {
    marginBottom: 15,
  },
  
  // Instructions Card
  instructionsCard: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  instructionsHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
    paddingBottom: 15,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  instructionsIcon: {
    fontSize: 32,
    marginRight: 12,
  },
  instructionsTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 2,
  },
  instructionsSubtitle: {
    fontSize: 12,
    color: colors.textLight,
  },

  // Step Items
  stepItem: {
    flexDirection: 'row',
    marginBottom: 20,
  },
  stepNumber: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  stepNumberText: {
    color: colors.white,
    fontSize: 14,
    fontWeight: 'bold',
  },
  stepContent: {
    flex: 1,
  },
  stepTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 6,
  },
  stepDescription: {
    fontSize: 13,
    color: colors.textLight,
    lineHeight: 20,
  },

  // Account Box
  accountBox: {
    backgroundColor: '#e3f2fd',
    borderRadius: 8,
    padding: 12,
    marginTop: 8,
    borderLeftWidth: 3,
    borderLeftColor: '#2196f3',
  },
  accountLabel: {
    fontSize: 11,
    color: '#1976d2',
    fontWeight: '600',
    marginBottom: 4,
  },
  accountNumber: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#1565c0',
    marginBottom: 4,
  },
  accountName: {
    fontSize: 12,
    color: '#1976d2',
  },

  // Amount Box
  amountBox: {
    backgroundColor: '#e8f5e9',
    borderRadius: 8,
    padding: 12,
    marginTop: 8,
    borderLeftWidth: 3,
    borderLeftColor: '#4caf50',
  },
  amountLabel: {
    fontSize: 11,
    color: '#388e3c',
    fontWeight: '600',
    marginBottom: 4,
  },
  amountValue: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#2e7d32',
  },

  // Reference Box
  referenceBox: {
    backgroundColor: '#fff3e0',
    borderRadius: 8,
    padding: 12,
    marginTop: 8,
    borderLeftWidth: 3,
    borderLeftColor: '#ff9800',
  },
  referenceLabel: {
    fontSize: 11,
    color: '#f57c00',
    fontWeight: '600',
    marginBottom: 4,
  },
  referenceValue: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#e65100',
  },

  // Order Summary Card
  orderSummaryCard: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 20,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  orderSummaryTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 15,
  },
  orderSummaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  orderSummaryLabel: {
    fontSize: 13,
    color: colors.textLight,
  },
  orderSummaryValue: {
    fontSize: 13,
    color: colors.text,
  },
  orderSummaryDivider: {
    height: 1,
    backgroundColor: colors.border,
    marginVertical: 10,
  },

  // Product Items
  productsList: {
    marginBottom: 5,
  },
  productItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  productImage: {
    width: 50,
    height: 50,
    borderRadius: 8,
    backgroundColor: '#f5f5f5',
  },
  productDetails: {
    flex: 1,
    marginLeft: 12,
  },
  productName: {
    fontSize: 13,
    fontWeight: '500',
    color: colors.text,
    marginBottom: 4,
  },
  productQuantity: {
    fontSize: 12,
    color: colors.textLight,
  },
  productPrice: {
    fontSize: 13,
    fontWeight: '600',
    color: colors.primary,
  },

  // Address Section
  addressSection: {
    paddingVertical: 5,
  },
  addressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  addressIcon: {
    fontSize: 16,
    marginRight: 6,
  },
  addressTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: colors.text,
  },
  addressName: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 2,
  },
  addressPhone: {
    fontSize: 12,
    color: colors.textLight,
    marginBottom: 4,
  },
  addressText: {
    fontSize: 12,
    color: colors.textLight,
    lineHeight: 18,
  },
  addressStreet: {
    fontSize: 13,
    color: colors.text,
    marginBottom: 2,
    lineHeight: 18,
  },
  addressPostal: {
    fontSize: 12,
    color: colors.textLight,
    marginTop: 2,
    fontWeight: '500',
  },
  pickupNote: {
    fontSize: 13,
    color: colors.primary,
    fontStyle: 'italic',
    marginTop: 4,
  },
  addressFullContainer: {
    backgroundColor: '#f8f9fa',
    borderRadius: 8,
    padding: 10,
    marginTop: 8,
  },
  addressRow: {
    flexDirection: 'row',
    marginBottom: 6,
  },
  addressFieldLabel: {
    fontSize: 12,
    color: '#666',
    width: 80,
    fontWeight: '500',
  },
  addressFieldValue: {
    fontSize: 12,
    color: '#333',
    flex: 1,
    fontWeight: '600',
  },

  orderSummaryTotalLabel: {
    fontSize: 15,
    fontWeight: 'bold',
    color: colors.text,
  },
  orderSummaryTotalValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: colors.primary,
  },
  orderInfoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 8,
  },
  orderInfoLabel: {
    fontSize: 12,
    color: colors.textLight,
  },
  orderInfoValue: {
    fontSize: 12,
    color: colors.text,
    fontWeight: '500',
  },
  statusBadge: {
    alignSelf: 'flex-start',
    backgroundColor: '#fff3e0',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 4,
    marginTop: 12,
  },
  statusBadgeText: {
    fontSize: 11,
    color: '#e65100',
    fontWeight: '600',
  },
  statusBadgePaid: {
    backgroundColor: '#e8f5e9',
  },
  statusBadgeTextPaid: {
    color: '#2e7d32',
  },

  // Quick Reference Card
  quickReferenceCard: {
    backgroundColor: '#e3f2fd',
    borderRadius: 8,
    padding: 15,
    marginTop: 15,
  },
  quickReferenceTitle: {
    fontSize: 11,
    fontWeight: 'bold',
    color: '#1565c0',
    marginBottom: 10,
    letterSpacing: 0.5,
  },
  quickReferenceRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  quickReferenceLabel: {
    fontSize: 12,
    color: '#1976d2',
  },
  quickReferenceValue: {
    fontSize: 13,
    fontWeight: 'bold',
    color: '#1565c0',
  },
  quickReferenceAmountValue: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#2e7d32',
  },

  // Notice Cards
  noticeCard: {
    backgroundColor: '#e8f5e9',
    borderRadius: 10,
    padding: 15,
    flexDirection: 'row',
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#c8e6c9',
  },
  noticeIconContainer: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: '#4caf50',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  noticeIcon: {
    color: colors.white,
    fontSize: 14,
    fontWeight: 'bold',
  },
  noticeContent: {
    flex: 1,
  },
  noticeTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#2e7d32',
    marginBottom: 4,
  },
  noticeDescription: {
    fontSize: 12,
    color: '#388e3c',
    lineHeight: 18,
  },

  // Warning Card
  warningCard: {
    backgroundColor: '#fff3e0',
    borderRadius: 10,
    padding: 15,
    flexDirection: 'row',
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#ffe0b2',
  },
  warningIconContainer: {
    marginRight: 12,
  },
  warningIcon: {
    fontSize: 20,
  },
  warningTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#e65100',
    marginBottom: 4,
  },

  // Reference Input
  referenceInputCard: {
    backgroundColor: colors.white,
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
  },
  referenceInputLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 10,
  },
  referenceInput: {
    backgroundColor: '#f5f5f5',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: colors.text,
    borderWidth: 1,
    borderColor: colors.border,
  },

  // Summary Card (Payment Selection Screen)
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

  // Section
  section: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 12,
  },

  // Payment Method Card
  paymentMethod: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.white,
    borderRadius: 10,
    padding: 15,
    marginBottom: 12,
    borderWidth: 2,
    borderColor: colors.border,
  },
  paymentMethodSelected: {
    borderColor: colors.primary,
    backgroundColor: 'rgba(139, 26, 26, 0.05)',
  },
  paymentMethodIcon: {
    width: 50,
    height: 50,
    borderRadius: 10,
    backgroundColor: colors.lightGray,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
  },
  methodIcon: {
    fontSize: 28,
  },
  paymentMethodInfo: {
    flex: 1,
  },
  methodName: {
    fontSize: 15,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 4,
  },
  methodDescription: {
    fontSize: 12,
    color: colors.textLight,
  },
  methodFee: {
    marginRight: 10,
  },
  feeText: {
    fontSize: 12,
    fontWeight: '600',
    color: colors.primary,
  },

  // Radio Button
  radioOuter: {
    width: 22,
    height: 22,
    borderRadius: 11,
    borderWidth: 2,
    borderColor: colors.border,
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioOuterSelected: {
    borderColor: colors.primary,
  },
  radioInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: colors.primary,
  },

  // Security Card
  securityCard: {
    backgroundColor: colors.white,
    borderRadius: 10,
    padding: 15,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
    marginBottom: 20,
  },
  securityIcon: {
    fontSize: 28,
    marginBottom: 8,
  },
  securityText: {
    fontSize: 13,
    color: colors.textLight,
    textAlign: 'center',
  },

  // Footer
  footer: {
    padding: 15,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    backgroundColor: colors.white,
  },
  payButton: {
    backgroundColor: colors.primary,
    paddingVertical: 14,
    borderRadius: 10,
    alignItems: 'center',
  },
  payButtonDisabled: {
    opacity: 0.5,
  },
  payButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },

  // Confirm Payment Button
  confirmPaymentButton: {
    backgroundColor: colors.primary,
    paddingVertical: 14,
    borderRadius: 10,
    alignItems: 'center',
    marginBottom: 10,
  },
  confirmPaymentButtonText: {
    color: colors.white,
    fontSize: 15,
    fontWeight: 'bold',
  },

  // View Order Button
  viewOrderButton: {
    backgroundColor: colors.white,
    paddingVertical: 12,
    borderRadius: 10,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
  },
  viewOrderButtonText: {
    color: colors.text,
    fontSize: 14,
    fontWeight: '600',
  },
});
