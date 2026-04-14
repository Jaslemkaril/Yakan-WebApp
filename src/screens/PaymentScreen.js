// src/screens/PaymentScreen.js
import React, { useState, useEffect } from 'react';
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
import { useNotification } from '../context/NotificationContext';
import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from '../services/api';
import NotificationService from '../services/notificationService';
import colors from '../constants/colors';
import * as ImagePicker from 'expo-image-picker';
import * as WebBrowser from 'expo-web-browser';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import { MaterialCommunityIcons } from '@expo/vector-icons';

// PayMongo replaces Maya as the online payment gateway

// Payment account details — fetched from API at runtime, these are fallback defaults
const DEFAULT_PAYMENT_ACCOUNTS = {
  gcash: {
    number: '0956-739-0720',
    name: 'Oliver Hamja',
  },
  maya: {
    number: '0956-739-0720',
    name: 'Oliver Hamja',
  },
  bank_transfer: {
    bankName: 'BDO',
    accountNumber: '2129-169-586',
    accountName: 'Oliver O. Hamja',
    branch: '',
  },
};

export default function PaymentScreen({ navigation, route }) {
  const { theme } = useTheme();
  const { notifyOrderCreated } = useNotification();
  const { orderData } = route.params || {};
  
  if (!orderData) {
    return (
      <View style={styles.container}>
        <ScreenHeader 
          title="Payment" 
          navigation={navigation} 
          showBack={true}
        />
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
  const [receiptImage, setReceiptImage] = useState(null);
  const [paymentAccounts, setPaymentAccounts] = useState(DEFAULT_PAYMENT_ACCOUNTS);

  useEffect(() => {
    const fetchPaymentInfo = async () => {
      try {
        const response = await ApiService.request('GET', '/settings/payment-info');
        if (response?.success && response?.data) {
          const d = response.data?.data || response.data || {};
          setPaymentAccounts({
            gcash: {
              number: d.gcash_number || DEFAULT_PAYMENT_ACCOUNTS.gcash.number,
              name:   d.gcash_name   || DEFAULT_PAYMENT_ACCOUNTS.gcash.name,
            },
            maya: {
              number: d.maya_number || d.gcash_number || DEFAULT_PAYMENT_ACCOUNTS.maya.number,
              name:   d.maya_name   || d.gcash_name   || DEFAULT_PAYMENT_ACCOUNTS.maya.name,
            },
            bank_transfer: {
              bankName:      d.bank_name            || DEFAULT_PAYMENT_ACCOUNTS.bank_transfer.bankName,
              accountNumber: d.bank_account_number  || d.bank_account || DEFAULT_PAYMENT_ACCOUNTS.bank_transfer.accountNumber,
              accountName:   d.bank_account_name    || DEFAULT_PAYMENT_ACCOUNTS.bank_transfer.accountName,
              branch:        d.bank_branch          || DEFAULT_PAYMENT_ACCOUNTS.bank_transfer.branch,
            },
          });
        }
      } catch (e) {
        // silently use defaults if API unavailable
      }
    };
    fetchPaymentInfo();
  }, []);

  useEffect(() => {
    if (!selectedPaymentMethod) {
      setSelectedPaymentMethod('paymongo');
    }
  }, [selectedPaymentMethod]);

  const paymentMethods = [
    {
      id: 'paymongo',
      name: 'PayMongo',
      description: 'GCash, Maya, Credit/Debit Card, GrabPay',
      logo: null,
      fee: 0,
    },
  ];

  const subtotalAmount = Number(orderData?.subtotal) || 0;
  const shippingAmount = Number(orderData?.shippingFee) || 0;
  const discountAmount = Math.max(0, Number(orderData?.discount) || 0);
  const baseTotal = Math.max(
    0,
    Number(orderData?.total) || (subtotalAmount + shippingAmount - discountAmount)
  );

  const updateOrderInStorage = async (updatedOrder) => {
    try {
      const savedOrders = await AsyncStorage.getItem('pendingOrders');
      const orders = savedOrders ? JSON.parse(savedOrders) : [];
      // Upsert: update existing order or add new one
      const exists = orders.some(o => o.orderRef === updatedOrder.orderRef);
      const updatedOrders = exists
        ? orders.map(o => o.orderRef === updatedOrder.orderRef ? updatedOrder : o)
        : [...orders, updatedOrder];
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
    
    // For PayMongo: skip instructions and go directly to checkout
    if (selectedPaymentMethod === 'paymongo') {
      handleConfirmPayment();
      return;
    }

    // For Bank Transfer: show payment instructions
    setShowPaymentInstructions(true);
  };

  const handlePickReceipt = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission needed', 'Gallery permission is required to upload a receipt');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.8,
    });

    if (!result.canceled) {
      setReceiptImage(result.assets[0].uri);
    }
  };

  const handleConfirmPayment = async () => {
    setIsProcessing(true);

    try {
      const apiOrderData = {
        customer_name: orderData.shippingAddress.fullName,
        customer_phone: orderData.shippingAddress.phoneNumber,
        shipping_address: `${orderData.shippingAddress.street}, ${orderData.shippingAddress.barangay || ''}, ${orderData.shippingAddress.city}, ${orderData.shippingAddress.province} ${orderData.shippingAddress.postalCode}`.replace(/,\s*,/g, ',').trim(),
        delivery_address: `${orderData.shippingAddress.street}, ${orderData.shippingAddress.barangay || ''}, ${orderData.shippingAddress.city}, ${orderData.shippingAddress.province} ${orderData.shippingAddress.postalCode}`.replace(/,\s*,/g, ',').trim(),
        shipping_city: orderData.shippingAddress.city || '',
        shipping_province: orderData.shippingAddress.province || '',
        shipping_zip: orderData.shippingAddress.postalCode || '',
        shipping_barangay: orderData.shippingAddress.barangay || '',
        shipping_street: orderData.shippingAddress.street || '',
        payment_method: selectedPaymentMethod,
        payment_status: 'pending',
        payment_reference: referenceNumber || null,
        delivery_type: orderData.deliveryOption || 'deliver', // 'deliver' or 'pickup'
        items: orderData.items.map(item => ({
          product_id: item.id,
          quantity: item.quantity || 1,
          price: item.price,
        })),
        subtotal: subtotalAmount,
        shipping_fee: shippingAmount,
        discount: discountAmount,
        discount_amount: discountAmount,
        coupon_code: orderData.couponCode || null,
        total: finalTotal,
        total_amount: finalTotal,
        notes: 'Order from mobile app',
      };

      console.log('ðŸ”µ Sending order to API:', apiOrderData);

      const response = await ApiService.createOrder(apiOrderData);

      if (!response.success) {
        setIsProcessing(false);
        Alert.alert('Order Failed', response.error || 'Could not create order. Please try again.');
        return;
      }

      console.log('ðŸ”µ Order created successfully:', response);

      // Extract backend order ID - try all possible response structures
      const resBody = response.data || {};
      const backendId = resBody?.data?.id
        ?? resBody?.id
        ?? resBody?.order?.id
        ?? resBody?.data?.order_id
        ?? null;
      const orderRef = resBody?.data?.order_ref ?? resBody?.order_ref ?? null;
      console.log('[Payment] Full response:', JSON.stringify(resBody).substring(0, 400));
      console.log('[Payment] backendId:', backendId, 'orderRef:', orderRef);

      if (!backendId) {
        setIsProcessing(false);
        Alert.alert('Order Error', 'Order was placed but we could not retrieve the order ID. Please check My Orders for your order status.');
        return;
      }

      console.log('ðŸ”µ Backend order ID:', backendId);

      const finalOrderData = {
        ...orderData,
        subtotal: subtotalAmount,
        shippingFee: shippingAmount,
        discount: discountAmount,
        total: finalTotal,
        paymentMethod: selectedPaymentMethod,
        paymentReference: referenceNumber,
        status: isPaymongo ? 'pending_payment' : 'payment_verified', // align with timeline stage
        backendOrderId: backendId,
        id: backendId, // Also store as 'id' for compatibility
      };

      // If user attached receipt, upload it after order is created (manual payment methods)
      if (!isPaymongo && receiptImage && backendId) {
        try {
          await ApiService.uploadPaymentProof(backendId, receiptImage);
        } catch (uploadErr) {
          console.warn('Failed to upload receipt image', uploadErr?.message || uploadErr);
        }
      }

      // For PayMongo: open checkout in browser and detect redirect back to app
      if (isPaymongo && backendId) {
        try {
          const paymongoCheckout = await ApiService.createPaymongoCheckout(backendId);
          const checkoutUrl = paymongoCheckout?.data?.data?.checkout_url
            ?? paymongoCheckout?.data?.checkout_url
            ?? paymongoCheckout?.checkout_url;
          if (checkoutUrl) {
            setIsProcessing(false);
            // openAuthSessionAsync watches for the yakanapp:// redirect and auto-closes the browser
            const result = await WebBrowser.openAuthSessionAsync(checkoutUrl, 'yakanapp://');

            const paid =
              result.type === 'success' && result.url?.includes('payment/success');
            const cancelled =
              result.type === 'cancel' ||
              result.type === 'dismiss' ||
              (result.type === 'success' && result.url?.includes('payment/failed'));

            finalOrderData.status = paid ? 'processing' : 'pending_payment';
            await updateOrderInStorage(finalOrderData);
            clearCart();
            notifyOrderCreated(orderData.orderRef);

            if (cancelled) {
              Alert.alert(
                'Payment Not Completed',
                'Your order has been saved. You can complete payment from My Orders.',
                [{ text: 'View Order', onPress: () => navigation.navigate('OrderDetails', { orderData: finalOrderData }) }]
              );
            } else {
              navigation.navigate('OrderDetails', { orderData: finalOrderData });
            }
            return;
          } else {
            throw new Error('No checkout URL in PayMongo response: ' + JSON.stringify(paymongoCheckout?.data));
          }
        } catch (pmErr) {
          console.warn('PayMongo checkout failed:', pmErr?.message || pmErr);
          setIsProcessing(false);
          Alert.alert(
            'PayMongo Checkout Failed',
            pmErr?.message || 'Could not connect to PayMongo. Please try Bank Transfer or contact support.',
            [{ text: 'OK' }]
          );
          return;
        }
      }

      if (isPaymongo && !backendId) {
        setIsProcessing(false);
        Alert.alert('Error', 'Order was created but ID could not be retrieved. Please contact support.');
        return;
      }

      await updateOrderInStorage(finalOrderData);
      
      // ðŸ”” Notify admin via notification service
      console.log('ðŸ”” Triggering admin notification for new order:', finalOrderData.orderRef);
      NotificationService.notifyNewOrder({
        orderId: backendId || finalOrderData.orderRef,
        orderRef: finalOrderData.orderRef,
        customerName: orderData.shippingAddress.fullName,
        customerPhone: orderData.shippingAddress.phoneNumber,
        total: finalTotal,
        itemCount: orderData.items.length,
        paymentMethod: selectedPaymentMethod,
        items: orderData.items,
        shippingAddress: orderData.shippingAddress,
        status: isPaymongo ? 'pending_payment' : 'pending_confirmation',
      });

      // Start polling for order status updates
      if (backendId) {
        NotificationService.startOrderStatusPolling(
          backendId,
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
      notifyOrderCreated(orderData.orderRef);
      navigation.navigate('OrderDetails', { orderData: finalOrderData });
    } catch (error) {
      console.error('ðŸ”´ Error creating order:', error);
      setIsProcessing(false);
      
      const finalOrderData = {
        ...orderData,
        subtotal: subtotalAmount,
        shippingFee: shippingAmount,
        discount: discountAmount,
        total: finalTotal,
        paymentMethod: selectedPaymentMethod,
        paymentReference: referenceNumber,
        status: isPaymongo ? 'pending_payment' : 'payment_verified',
      };
      await updateOrderInStorage(finalOrderData);
      
      // Still notify admin even if backend sync failed (order saved locally)
      console.log('ðŸ”” Local order saved, notifying admin');
      NotificationService.notifyNewOrder({
        orderId: finalOrderData.orderRef,
        orderRef: finalOrderData.orderRef,
        customerName: orderData.shippingAddress.fullName,
        customerPhone: orderData.shippingAddress.phoneNumber,
        total: finalTotal,
        itemCount: orderData.items.length,
        paymentMethod: selectedPaymentMethod,
        items: orderData.items,
        shippingAddress: orderData.shippingAddress,
        status: isPaymongo ? 'pending_payment' : 'pending_confirmation',
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

  const selectedMethod = paymentMethods.find(m => m.id === selectedPaymentMethod);
  const finalTotal = Math.max(0, baseTotal + (selectedMethod?.fee || 0));
  const isPaymongo = selectedPaymentMethod === 'paymongo';
  const isBankTransfer = selectedPaymentMethod === 'bank_transfer';
  // Stub wallet vars — PayMongo uses redirect, only bank_transfer shows manual instructions
  const walletLabel = 'Online Payment';
  const walletNumber = '';
  const walletName = '';

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
            {isBankTransfer ? 'Bank Transfer' : `${walletLabel} Payment`}
          </Text>
          <View style={{ width: 50 }} />
        </View>

        {/* Instructions Banner */}
        <View style={styles.instructionsBanner}>
          <Text style={styles.instructionsBannerText}>Complete your payment below to confirm this order</Text>
        </View>

        <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
          <View style={styles.instructionsContainer}>
            {/* Left Side - Instructions */}
            <View style={styles.instructionsCard}>
              <View style={styles.instructionsHeader}>
                <Text style={styles.instructionsIcon}>
                  {isBankTransfer ? '🏦' : '💳'}
                </Text>
                <View>
                  <Text style={styles.instructionsTitle}>
                    {isBankTransfer ? 'Bank Transfer Instructions' : `${walletLabel} Payment Instructions`}
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
                    {isBankTransfer ? 'Open your Banking App' : `Open your ${walletLabel} App`}
                  </Text>
                  <Text style={styles.stepDescription}>
                    {!isBankTransfer 
                      ? `Launch the ${walletLabel} mobile application on your phone.`
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
                    {!isBankTransfer ? `Send Money to this ${walletLabel} Number` : 'Transfer to this Account'}
                  </Text>
                  {!isBankTransfer ? (
                    <View style={styles.accountBox}>
                      <Text style={styles.accountLabel}>{walletLabel} Number</Text>
                      <Text style={styles.accountNumber}>{walletNumber}</Text>
                      <Text style={styles.accountName}>Account Name: {walletName}</Text>
                    </View>
                  ) : (
                    <View style={styles.accountBox}>
                      <Text style={styles.accountLabel}>{paymentAccounts.bank_transfer.bankName}</Text>
                      <Text style={styles.accountNumber}>{paymentAccounts.bank_transfer.accountNumber}</Text>
                      <Text style={styles.accountName}>Account Name: {paymentAccounts.bank_transfer.accountName}</Text>
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
                    Include this reference in your {!isBankTransfer ? walletLabel : 'bank transfer'} message:
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
                  <Text style={styles.stepTitle}>{isPaymongo ? 'Proceed to PayMongo Checkout' : 'Submit Payment Confirmation'}</Text>
                  <Text style={styles.stepDescription}>
                    {isPaymongo
                      ? 'Tap the button below to open PayMongo Checkout and complete payment securely.'
                      : 'After sending the payment, click the button below to confirm. Your payment will be verified automatically and your order will start processing immediately!'}
                  </Text>
                  {!isPaymongo && (
                    <>
                      <TouchableOpacity style={styles.receiptButton} onPress={handlePickReceipt}>
                        <Text style={styles.receiptButtonText}>📎 Upload receipt image</Text>
                      </TouchableOpacity>
                      {receiptImage && (
                        <Text style={styles.receiptPreview}>Attached: {receiptImage.split('/').pop()}</Text>
                      )}
                    </>
                  )}
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
                <Text style={styles.orderSummaryValue}>₱{subtotalAmount.toFixed(2)}</Text>
              </View>
              
              <View style={styles.orderSummaryRow}>
                <Text style={styles.orderSummaryLabel}>Shipping</Text>
                <Text style={styles.orderSummaryValue}>₱{shippingAmount.toFixed(2)}</Text>
              </View>

              {discountAmount > 0 && (
                <View style={styles.orderSummaryRow}>
                  <Text style={[styles.orderSummaryLabel, { color: '#16A34A' }]}>Discount</Text>
                  <Text style={[styles.orderSummaryValue, { color: '#16A34A' }]}>-₱{discountAmount.toFixed(2)}</Text>
                </View>
              )}
              
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
                    {!isBankTransfer 
                      ? walletNumber 
                      : paymentAccounts.bank_transfer.accountNumber}
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
              <Text style={styles.noticeIcon}>✔</Text>
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
                {isPaymongo
                  ? 'Complete payment in the PayMongo Checkout page. Your order status will update after successful payment.'
                  : `Please make sure to send the EXACT amount (₱${finalTotal.toFixed(2)}) and include the reference number (${orderData.orderRef}) in your ${!isBankTransfer ? walletLabel : 'bank transfer'} message.`}
              </Text>
            </View>
          </View>

          {/* Reference Number Input */}
          {!isPaymongo && (
          <View style={styles.referenceInputCard}>
            <Text style={styles.referenceInputLabel}>
              {!isBankTransfer ? walletLabel : 'Bank'} Reference Number (optional)
            </Text>
            <TextInput
              style={styles.referenceInput}
              placeholder={`Enter ${!isBankTransfer ? walletLabel : 'bank'} reference number`}
              placeholderTextColor="#999"
              value={referenceNumber}
              onChangeText={setReferenceNumber}
            />
            <Text style={styles.referenceHelper}>
              Upload Proof of Payment *
              {'\n'}Upload a clear screenshot or photo of your {!isBankTransfer ? walletLabel : 'bank'} receipt showing the transaction details, amount, and reference number.
            </Text>
            <TouchableOpacity style={styles.receiptButton} onPress={handlePickReceipt}>
              <Text style={styles.receiptButtonText}>📎 Upload receipt image</Text>
            </TouchableOpacity>
            {receiptImage && (
              <Text style={styles.receiptPreview}>Attached: {receiptImage.split('/').pop()}</Text>
            )}
          </View>
          )}

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
                {isPaymongo ? 'Proceed to PayMongo Checkout' : `I have paid via ${selectedMethod?.name}`}
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
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Payment Method" 
        navigation={navigation} 
        showBack={true}
      />

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {/* Order Summary */}
        <View style={styles.summaryCard}>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>₱{subtotalAmount.toFixed(2)}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Shipping</Text>
            <Text style={styles.summaryValue}>₱{shippingAmount.toFixed(2)}</Text>
          </View>
          {discountAmount > 0 && (
            <View style={styles.summaryRow}>
              <Text style={[styles.summaryLabel, { color: '#16A34A' }]}>Discount</Text>
              <Text style={[styles.summaryValue, { color: '#16A34A' }]}>-₱{discountAmount.toFixed(2)}</Text>
            </View>
          )}
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
                {method.logo ? (
                  <Image
                    source={method.logo}
                    style={styles.methodLogoImage}
                    resizeMode="contain"
                  />
                ) : method.id === 'paymongo' ? (
                  <View style={styles.paymongoLogoBadge}>
                    <Text style={styles.paymongoPayText}>Pay</Text>
                    <Text style={styles.paymongoMongoText}>Mongo</Text>
                  </View>
                ) : (
                  <View style={styles.bankIconCircle}>
                    <MaterialCommunityIcons name="bank" size={26} color="#8B1A1A" />
                  </View>
                )}
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

  // Instructions Banner (neutral, replaces misleading success banner)
  instructionsBanner: {
    backgroundColor: '#FFF8E1',
    paddingVertical: 12,
    paddingHorizontal: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#FFE082',
  },
  instructionsBannerText: {
    color: '#795548',
    fontSize: 14,
    fontWeight: '600',
    textAlign: 'center',
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
  referenceHelper: {
    fontSize: 12,
    color: '#555',
    lineHeight: 18,
    marginTop: 8,
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
    width: 72,
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
  methodLogoImage: {
    width: 44,
    height: 44,
    borderRadius: 8,
  },
  bankIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 8,
    backgroundColor: '#FFF0F0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  paymongoLogoBadge: {
    width: 64,
    height: 32,
    borderRadius: 8,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#dbeafe',
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
  },
  paymongoPayText: {
    fontSize: 14,
    fontWeight: '800',
    color: '#0071ce',
    letterSpacing: -0.3,
    marginRight: 1,
  },
  paymongoMongoText: {
    fontSize: 14,
    fontWeight: '800',
    color: '#00c2cb',
    letterSpacing: -0.3,
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

  receiptButton: {
    marginTop: 10,
    paddingVertical: 10,
    paddingHorizontal: 12,
    backgroundColor: '#eef2ff',
    borderRadius: 8,
    alignSelf: 'flex-start',
  },
  receiptButtonText: {
    color: colors.primary,
    fontWeight: '600',
  },
  receiptPreview: {
    marginTop: 6,
    color: colors.textLight,
    fontSize: 12,
  },
});

