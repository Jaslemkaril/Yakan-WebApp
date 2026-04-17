import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TextInput,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Modal,
  Switch,
  Image,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';
import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from '../services/api';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';

const DOWNPAYMENT_RATE = 50;
const PICKUP_STORE_ADDRESS = {
  street: 'Yakan Village',
  barangay: 'Upper Calarian',
  city: 'Zamboanga City',
  province: 'Zamboanga del Sur',
  postalCode: '7000',
};

const CheckoutScreen = ({ navigation }) => {
  const { cartItems, checkoutItems, setCheckoutItems, updateQuantity, userInfo, clearCart } = useCart();
  const { theme } = useTheme();
  const styles = getStyles(theme);
  
  // Keep the selected checkout subset when user came from cart selection.
  const [useCheckoutSelection] = useState(() => Array.isArray(checkoutItems) && checkoutItems.length > 0);
  const itemsToCheckout = useCheckoutSelection ? checkoutItems : cartItems;
  
  const [savedAddresses, setSavedAddresses] = useState([]);
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [showAddressForm, setShowAddressForm] = useState(false);
  const [isEditingAddress, setIsEditingAddress] = useState(false);
  const [editingAddressId] = useState(null);
  
  // Delivery option state: 'pickup' or 'deliver'
  const [deliveryOption, setDeliveryOption] = useState('deliver');
  const [paymentOption, setPaymentOption] = useState('full');
  
  // Shipping fee state
  const [shippingFee, setShippingFee] = useState(0);
  const [calculatingShipping, setCalculatingShipping] = useState(false);
  
  // Coupon code state
  const [couponCode, setCouponCode] = useState('');
  const [appliedCoupon, setAppliedCoupon] = useState(null);
  const [couponError, setCouponError] = useState('');
  const [applyingCoupon, setApplyingCoupon] = useState(false);
  const [isSubmittingOrder, setIsSubmittingOrder] = useState(false);
  const [availableCoupons, setAvailableCoupons] = useState([]);
  
  const [addressForm, setAddressForm] = useState({
    fullName: '',
    phoneNumber: '',
    region: '',
    province: '',
    city: '',
    barangay: '',
    postalCode: '',
    street: '',
    isDefault: false,
    label: 'Home',
  });

  // Load saved addresses on mount and when screen gains focus
  useEffect(() => {
    loadAddresses();
    loadAvailableCoupons();
  }, []);

  // Reload addresses when returning from SavedAddresses screen
  useFocusEffect(
    React.useCallback(() => {
      loadAddresses();
    }, [])
  );

  // Calculate shipping fee when address or delivery option changes
  useEffect(() => {
    if (deliveryOption === 'deliver' && selectedAddressId) {
      calculateShippingFee();
    } else if (deliveryOption === 'pickup') {
      setShippingFee(0);
    }
  }, [selectedAddressId, deliveryOption]);

  const loadAvailableCoupons = async () => {
    try {
      const response = await ApiService.getAvailableCoupons();
      if (response.success) {
        const list = response.data?.data ?? response.data ?? [];
        setAvailableCoupons(Array.isArray(list) ? list : []);
      }
    } catch (e) {
      // silent — coupons are optional
    }
  };

  const calculateShippingFee = async () => {
    try {
      setCalculatingShipping(true);
      const selectedAddr = savedAddresses.find(addr => addr.id === selectedAddressId);

      if (!selectedAddr) {
        setShippingFee(100);
        return;
      }

      const cityLower     = (selectedAddr.city     || '').toLowerCase();
      const provinceLower = (selectedAddr.province || '').toLowerCase();

      // Helper — checks if city or province contains any of the keywords
      const matchesAny = (keywords) =>
        keywords.some(k => cityLower.includes(k) || provinceLower.includes(k));

      // Zone 1 — ₱100: Zamboanga Peninsula + BARMM
      // (store is in Zamboanga City — this is the base/nearest zone)
      if (matchesAny([
        'zamboanga', 'basilan', 'sulu', 'tawi',
        'maguindanao', 'lanao del sur', 'cotabato',
        'dipolog', 'dapitan', 'pagadian', 'ipil',
        'jolo', 'bongao', 'marawi', 'lamitan', 'isabela city',
      ])) {
        setShippingFee(100);
        console.log(`[Checkout] Zone 1 (₱100) for "${selectedAddr.city}, ${selectedAddr.province}"`);
        return;
      }

      // Zone 2 — ₱180: Rest of Mindanao
      if (matchesAny([
        'davao', 'sarangani', 'south cotabato', 'sultan kudarat',
        'north cotabato', 'misamis', 'bukidnon', 'lanao del norte',
        'camiguin', 'agusan', 'surigao', 'dinagat',
        'tagum', 'digos', 'panabo', 'general santos',
        'koronadal', 'kidapawan', 'cagayan de oro',
        'iligan', 'ozamiz', 'butuan', 'malaybalay',
      ])) {
        setShippingFee(180);
        console.log(`[Checkout] Zone 2 (₱180) for "${selectedAddr.city}, ${selectedAddr.province}"`);
        return;
      }

      // Zone 3 — ₱250: Visayas
      if (matchesAny([
        'cebu', 'bohol', 'negros', 'leyte', 'samar', 'biliran',
        'aklan', 'antique', 'capiz', 'iloilo', 'guimaras',
        'bacolod', 'tacloban', 'dumaguete', 'tagbilaran',
        'ormoc', 'calbayog', 'roxas city',
      ])) {
        setShippingFee(250);
        console.log(`[Checkout] Zone 3 (₱250) for "${selectedAddr.city}, ${selectedAddr.province}"`);
        return;
      }

      // Zone 4 — ₱300: NCR, Metro Manila, Central Luzon, CALABARZON
      if (matchesAny([
        'manila', 'makati', 'pasig', 'taguig', 'caloocan',
        'quezon city', 'antipolo', 'bulacan', 'cavite',
        'laguna', 'batangas', 'rizal', 'pampanga',
        'tarlac', 'nueva ecija', 'bataan', 'zambales', 'aurora',
        'angeles', 'san fernando', 'lucena', 'lipa',
      ])) {
        setShippingFee(300);
        console.log(`[Checkout] Zone 4 (₱300) for "${selectedAddr.city}, ${selectedAddr.province}"`);
        return;
      }

      // Zone 5 — ₱350: Far Luzon (Ilocos, CAR, Cagayan Valley, Bicol, MIMAROPA)
      setShippingFee(350);
      console.log(`[Checkout] Zone 5 (₱350) for "${selectedAddr.city}, ${selectedAddr.province}"`);
    } catch (error) {
      console.error('[Checkout] Error calculating shipping fee:', error);
      setShippingFee(100);
    } finally {
      setCalculatingShipping(false);
    }
  };

  const loadAddresses = async () => {
    try {
      console.log('[Checkout] Starting to load addresses using ApiService...');
      
      // Use ApiService just like SavedAddressesScreen does
      const response = await ApiService.getSavedAddresses();
      console.log('[Checkout] API Response:', JSON.stringify(response, null, 2));
      
      if (response.success) {
        // Handle nested data structure - response.data might contain another data property
        const dataArray = response.data?.data || response.data;
        const items = Array.isArray(dataArray) ? dataArray : [];
        console.log('[Checkout] Fetched addresses count:', items.length);
        
        if (items.length > 0) {
          // Map API response to mobile format
          const addresses = items.map(addr => ({
            id: addr.id,
            fullName: addr.full_name,
            phoneNumber: addr.phone || addr.phone_number,
            street: addr.street_address || addr.street,
            barangay: addr.barangay,
            city: addr.city,
            province: addr.province,
            region: addr.region || '',
            regionCode: addr.region_code || '',
            regionShipping: addr.shipping_fee || null,
            postalCode: addr.postal_code,
            isDefault: addr.is_default === 1 || addr.is_default === true,
            label: addr.label || 'Home',
          }));
          
          console.log('[Checkout] Mapped addresses:', JSON.stringify(addresses, null, 2));
          setSavedAddresses(addresses);
          
          // Set default address as selected
          const defaultAddress = addresses.find(addr => addr.isDefault);
          if (defaultAddress) {
            setSelectedAddressId(defaultAddress.id);
            console.log('[Checkout] Set default address:', defaultAddress.id);
          } else if (addresses.length > 0) {
            setSelectedAddressId(addresses[0].id);
            console.log('[Checkout] Set first address:', addresses[0].id);
          }
        } else {
          console.log('[Checkout] No addresses found in API response');
          setSavedAddresses([]);
        }
      } else {
        console.log('[Checkout] API response not successful:', response.error);
        setSavedAddresses([]);
      }
    } catch (error) {
      console.log('[Checkout] Error loading addresses:', error.message);
      console.log('[Checkout] Error details:', error);
      setSavedAddresses([]);
    }
  };

  const saveAddresses = async (addresses) => {
    try {
      await AsyncStorage.setItem('savedAddresses', JSON.stringify(addresses));
    } catch (error) {
      console.log('Error saving addresses:', error);
    }
  };

  useEffect(() => {
    if (!itemsToCheckout || itemsToCheckout.length === 0) {
      Alert.alert(
        'Empty Cart',
        'Your cart is empty. Please add items before checking out.',
        [
          {
            text: 'OK',
            onPress: () => navigation.navigate('Home'),
          },
        ]
      );
    }
  }, []);

  if (!itemsToCheckout) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#8B1A1A" />
        <Text style={styles.loadingText}>Loading...</Text>
      </View>
    );
  }

  if (itemsToCheckout.length === 0) {
    return (
      <View style={styles.emptyContainer}>
        <Text style={styles.emptyText}>Your cart is empty</Text>
        <TouchableOpacity
          style={styles.shopButton}
          onPress={() => navigation.navigate('Home')}
        >
          <Text style={styles.shopButtonText}>Start Shopping</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const shippingFeeDisplay = deliveryOption === 'pickup' ? 0 : shippingFee;
  const subtotal = itemsToCheckout.reduce((total, item) => total + (item.price * item.quantity), 0);
  const discount = appliedCoupon ? Math.min(parseFloat(appliedCoupon.discount) || 0, shippingFeeDisplay) : 0;
  const orderTotal = Math.max(0, subtotal + shippingFeeDisplay - discount);
  const downpaymentAmount = paymentOption === 'downpayment'
    ? Number((orderTotal * (DOWNPAYMENT_RATE / 100)).toFixed(2))
    : orderTotal;
  const remainingBalance = Math.max(0, Number((orderTotal - downpaymentAmount).toFixed(2)));
  const payableNow = downpaymentAmount;

  const generateOrderRef = () => {
    const now = new Date();
    const yy  = String(now.getFullYear()).slice(-2);
    const mm  = String(now.getMonth() + 1).padStart(2, '0');
    const dd  = String(now.getDate()).padStart(2, '0');
    const rand = String(Math.floor(Math.random() * 100000000)).padStart(8, '0');
    return yy + mm + dd + rand;
  };

  const handleApplyCoupon = async (code) => {
    setCouponError('');
    const normalizedCode = (code || couponCode).trim().toUpperCase();

    if (!normalizedCode) {
      setCouponError('Please select a coupon');
      return;
    }

    setApplyingCoupon(true);
    try {
      const response = await ApiService.validateCoupon(normalizedCode, subtotal, shippingFeeDisplay);
      const payload = response.data || {};
      if (response.success && payload.success) {
        setAppliedCoupon({ code: payload.code, discount: parseFloat(payload.discount) || 0, description: payload.description });
        setCouponCode(payload.code);
        setCouponError('');
      } else {
        setCouponError(payload.message || response.error || 'Invalid coupon code');
        setAppliedCoupon(null);
      }
    } catch {
      setCouponError('Failed to validate coupon. Please try again.');
      setAppliedCoupon(null);
    } finally {
      setApplyingCoupon(false);
    }
  };

  const handleRemoveCoupon = () => {
    setAppliedCoupon(null);
    setCouponCode('');
    setCouponError('');
  };

  const handleAddressSubmit = () => {
    if (!addressForm.fullName.trim() || !addressForm.phoneNumber.trim() || 
        !addressForm.street.trim() || !addressForm.city.trim() || 
        !addressForm.postalCode.trim() || !addressForm.province.trim()) {
      Alert.alert('Error', 'Please fill in all address fields');
      return;
    }

    let updatedAddresses = [...savedAddresses];
    
    if (isEditingAddress) {
      // Update existing address
      updatedAddresses = updatedAddresses.map(addr =>
        addr.id === editingAddressId
          ? { ...addr, ...addressForm }
          : addressForm.isDefault ? { ...addr, isDefault: false } : addr
      );
    } else {
      // Add new address
      const newAddress = {
        ...addressForm,
        id: Date.now().toString(),
      };
      updatedAddresses.push(newAddress);
      if (addressForm.isDefault) {
        updatedAddresses = updatedAddresses.map(addr =>
          addr.id === newAddress.id ? addr : { ...addr, isDefault: false }
        );
      }
    }

    saveAddresses(updatedAddresses);
    setSavedAddresses(updatedAddresses);
    
    // Select the new/edited address
    if (isEditingAddress) {
      setSelectedAddressId(editingAddressId);
    } else {
      setSelectedAddressId(addressForm.id || updatedAddresses[updatedAddresses.length - 1].id);
    }

    setShowAddressForm(false);
    setIsEditingAddress(false);
    setAddressForm({
      fullName: '',
      phoneNumber: '',
      region: '',
      province: '',
      city: '',
      barangay: '',
      postalCode: '',
      street: '',
      isDefault: false,
      label: 'Home',
    });
    
    Alert.alert('Success', isEditingAddress ? 'Address updated!' : 'Address added successfully!');
  };

  const handleDeleteAddress = (addressId) => {
    Alert.alert(
      'Delete Address',
      'Are you sure you want to delete this address?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: () => {
            const updatedAddresses = savedAddresses.filter(addr => addr.id !== addressId);
            saveAddresses(updatedAddresses);
            setSavedAddresses(updatedAddresses);
            
            if (selectedAddressId === addressId) {
              if (updatedAddresses.length > 0) {
                setSelectedAddressId(updatedAddresses[0].id);
              } else {
                setSelectedAddressId(null);
              }
            }
          },
        },
      ]
    );
  };

  const syncCheckoutSelectionQuantity = (itemId, nextQuantity) => {
    if (!useCheckoutSelection) {
      return;
    }

    setCheckoutItems((prev) => {
      const baseItems = Array.isArray(prev) ? prev : [];

      if (nextQuantity <= 0) {
        return baseItems.filter((entry) => entry.id !== itemId);
      }

      return baseItems.map((entry) =>
        entry.id === itemId ? { ...entry, quantity: nextQuantity } : entry
      );
    });
  };

  const handleOrderSummaryQuantityChange = (item, delta) => {
    if (deliveryOption !== 'pickup' || !item?.id) {
      return;
    }

    const currentQuantity = Number(item.quantity || 1);
    const nextQuantity = currentQuantity + delta;
    const maxStock = Number(item.stock || 0);

    if (delta > 0 && maxStock > 0 && currentQuantity >= maxStock) {
      Alert.alert('Stock limit reached', `Only ${maxStock} item(s) available for ${item.name}.`);
      return;
    }

    if (nextQuantity <= 0) {
      Alert.alert(
        'Remove Item',
        `Remove ${item.name} from this order?`,
        [
          { text: 'Cancel', style: 'cancel' },
          {
            text: 'Remove',
            style: 'destructive',
            onPress: () => {
              syncCheckoutSelectionQuantity(item.id, 0);
              updateQuantity(item.id, 0);
            },
          },
        ]
      );
      return;
    }

    syncCheckoutSelectionQuantity(item.id, nextQuantity);
    updateQuantity(item.id, nextQuantity);
  };

  const handlePlaceOrder = async () => {
    if (!itemsToCheckout || itemsToCheckout.length === 0) {
      Alert.alert('No Items', 'Your checkout has no items. Please add items before proceeding.');
      return;
    }

    if (deliveryOption === 'deliver' && !selectedAddressId) {
      Alert.alert('Error', 'Please select a shipping address');
      return;
    }

    const selectedAddr = savedAddresses.find(addr => addr.id === selectedAddressId);
    const pickupAddress = {
      fullName: userInfo?.name || userInfo?.first_name || 'Pickup Customer',
      phoneNumber: userInfo?.phone || '',
      ...PICKUP_STORE_ADDRESS,
    };

    const resolvedShippingAddress = deliveryOption === 'pickup'
      ? pickupAddress
      : {
          fullName: selectedAddr?.fullName || '',
          phoneNumber: selectedAddr?.phoneNumber || '',
          street: selectedAddr?.street || '',
          barangay: selectedAddr?.barangay || '',
          city: selectedAddr?.city || '',
          province: selectedAddr?.province || '',
          postalCode: selectedAddr?.postalCode || '',
        };

    if (!resolvedShippingAddress.phoneNumber?.trim()) {
      Alert.alert('Phone Number Required', 'Please provide a contact phone number before placing your order.');
      return;
    }
    
    const actualShippingFee = deliveryOption === 'pickup' ? 0 : shippingFee;
    const actualTotal = Math.max(0, subtotal + actualShippingFee - discount);
    const actualDownpaymentAmount = paymentOption === 'downpayment'
      ? Number((actualTotal * (DOWNPAYMENT_RATE / 100)).toFixed(2))
      : actualTotal;
    const actualRemainingBalance = Math.max(0, Number((actualTotal - actualDownpaymentAmount).toFixed(2)));

    const fullAddress = `${resolvedShippingAddress.street}, ${resolvedShippingAddress.barangay || ''}, ${resolvedShippingAddress.city}, ${resolvedShippingAddress.province} ${resolvedShippingAddress.postalCode}`
      .replace(/,\s*,/g, ',')
      .trim();

    const apiOrderData = {
      customer_name: resolvedShippingAddress.fullName,
      customer_email: userInfo?.email || null,
      customer_phone: resolvedShippingAddress.phoneNumber,
      shipping_address: fullAddress,
      delivery_address: fullAddress,
      shipping_city: resolvedShippingAddress.city || '',
      shipping_province: resolvedShippingAddress.province || '',
      shipping_zip: resolvedShippingAddress.postalCode || '',
      shipping_barangay: resolvedShippingAddress.barangay || '',
      shipping_street: resolvedShippingAddress.street || '',
      payment_method: 'paymongo',
      payment_status: 'pending',
      delivery_type: deliveryOption || 'deliver',
      items: itemsToCheckout.map(item => ({
        product_id: item.product_id || item.id,
        variant_id: item.variant_id || null,
        quantity: item.quantity || 1,
        price: item.price,
      })),
      subtotal,
      shipping_fee: actualShippingFee,
      discount,
      discount_amount: discount,
      coupon_code: appliedCoupon?.code || null,
      total: actualTotal,
      total_amount: actualTotal,
      payment_option: paymentOption,
      downpayment_rate: paymentOption === 'downpayment' ? DOWNPAYMENT_RATE : 100,
      downpayment_amount: actualDownpaymentAmount,
      remaining_balance: actualRemainingBalance,
      notes: 'Order from mobile app',
    };

    setIsSubmittingOrder(true);
    try {
      const response = await ApiService.createOrder(apiOrderData);

      if (!response.success) {
        Alert.alert('Order Failed', response.error || 'Could not create order. Please try again.');
        return;
      }

      const resBody = response.data || {};
      const createdOrder = resBody?.data || resBody;
      const backendOrderId = createdOrder?.id;
      const orderRef = createdOrder?.order_ref || createdOrder?.tracking_number || generateOrderRef();

      if (!backendOrderId) {
        Alert.alert('Order Error', 'Order was created but no order ID was returned. Please check My Orders.');
        return;
      }

      // Align with website flow: cart is cleared as soon as order is created.
      await clearCart();
      setCheckoutItems([]);

      const orderData = {
        orderRef,
        backendOrderId,
        id: backendOrderId,
        date: new Date().toISOString(),
        items: itemsToCheckout,
        deliveryOption,
        shippingAddress: resolvedShippingAddress,
        subtotal,
        shippingFee: actualShippingFee,
        discount,
        couponCode: appliedCoupon?.code || null,
        total: actualTotal,
        payableNow: actualDownpaymentAmount,
        remainingBalance: actualRemainingBalance,
        paymentOption,
        downpaymentRate: paymentOption === 'downpayment' ? DOWNPAYMENT_RATE : 100,
        status: 'pending_payment',
      };

      navigation.navigate('Payment', { orderData });
    } catch (error) {
      Alert.alert('Order Failed', 'Could not create your order right now. Please try again.');
    } finally {
      setIsSubmittingOrder(false);
    }
  };

  const isCheckoutDisabled =
    itemsToCheckout.length === 0 ||
    (deliveryOption === 'deliver' && !selectedAddressId) ||
    isSubmittingOrder;

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Checkout" 
        navigation={navigation} 
        showBack={true}
      />

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {/* Delivery Option Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Delivery Option</Text>
          <View style={styles.deliveryOptionsContainer}>
            <TouchableOpacity
              style={[
                styles.deliveryOptionCard,
                deliveryOption === 'deliver' && styles.deliveryOptionCardSelected,
              ]}
              onPress={() => setDeliveryOption('deliver')}
            >
              <View style={[
                styles.deliveryRadio,
                deliveryOption === 'deliver' && styles.deliveryRadioSelected,
              ]}>
                {deliveryOption === 'deliver' && <View style={styles.deliveryRadioInner} />}
              </View>
              <View style={styles.deliveryOptionContent}>
                <Ionicons name="car" size={28} color="#8B1A1A" style={styles.deliveryOptionIcon} />
                <View>
                  <Text style={styles.deliveryOptionTitle}>Deliver</Text>
                  <Text style={styles.deliveryOptionDesc}>
                    Deliver to your address {calculatingShipping ? '(calculating...)' : `(+₱${shippingFee.toFixed(2)})`}
                  </Text>
                </View>
              </View>
            </TouchableOpacity>

            <TouchableOpacity
              style={[
                styles.deliveryOptionCard,
                deliveryOption === 'pickup' && styles.deliveryOptionCardSelected,
              ]}
              onPress={() => setDeliveryOption('pickup')}
            >
              <View style={[
                styles.deliveryRadio,
                deliveryOption === 'pickup' && styles.deliveryRadioSelected,
              ]}>
                {deliveryOption === 'pickup' && <View style={styles.deliveryRadioInner} />}
              </View>
              <View style={styles.deliveryOptionContent}>
                <MaterialCommunityIcons name="store-24-hour" size={28} color="#8B1A1A" style={styles.deliveryOptionIcon} />
                <View>
                  <Text style={styles.deliveryOptionTitle}>Pick Up</Text>
                  <Text style={styles.deliveryOptionDesc}>Pick up at store location</Text>
                </View>
              </View>
            </TouchableOpacity>
          </View>
        </View>

        {/* Store Pickup Information - Only show when pickup is selected */}
        {deliveryOption === 'pickup' && (
          <View style={styles.section}>
            <View style={styles.sectionTitleContainer}>
              <MaterialCommunityIcons name="store" size={24} color="#8B1A1A" />
              <Text style={styles.sectionTitle}>Store Pickup Information</Text>
            </View>
            
            <View style={styles.storeInfoCard}>
              <View style={styles.storeHeaderRow}>
                <View style={styles.storeIconContainer}>
                  <Ionicons name="location" size={32} color="#fff" />
                </View>
                <Text style={styles.storeName}>Yakan Weaving Store</Text>
              </View>
              
              <View style={styles.storeDetailsContainer}>
                <View style={styles.storeDetailRow}>
                  <View style={styles.storeDetailLabelContainer}>
                    <Ionicons name="location-outline" size={18} color="#8B1A1A" />
                    <Text style={styles.storeDetailLabel}>Address:</Text>
                  </View>
                  <Text style={styles.storeDetailText}>
                    Yakan Village, Brgy. Upper Calarian, Zamboanga City, Philippines 7000
                  </Text>
                </View>

                <View style={styles.storeHoursBox}>
                  <View style={styles.storeDetailLabelContainer}>
                    <Ionicons name="time-outline" size={18} color="#8B1A1A" />
                    <Text style={styles.storeDetailLabel}>Store Hours</Text>
                  </View>
                  <Text style={styles.storeHoursText}>Monday - Saturday: 9:00 AM - 6:00 PM</Text>
                  <Text style={styles.storeClosedText}>Closed on Sundays & Holidays</Text>
                </View>

                <View style={styles.storeDetailRow}>
                  <View style={styles.storeDetailLabelContainer}>
                    <Ionicons name="call-outline" size={18} color="#8B1A1A" />
                    <Text style={styles.storeDetailLabel}>Contact Number</Text>
                  </View>
                  <Text style={styles.storeDetailText}>+63 917-123-4567</Text>
                </View>

                <View style={styles.importantReminders}>
                  <View style={styles.remindersTitleContainer}>
                    <Ionicons name="warning" size={18} color="#92400E" />
                    <Text style={styles.remindersTitle}>Important Reminders:</Text>
                  </View>
                  <View style={styles.reminderItem}>
                    <Text style={styles.reminderBullet}>•</Text>
                    <Text style={styles.reminderText}>Bring a valid government-issued ID</Text>
                  </View>
                  <View style={styles.reminderItem}>
                    <Text style={styles.reminderBullet}>•</Text>
                    <Text style={styles.reminderText}>Present your order confirmation number</Text>
                  </View>
                  <View style={styles.reminderItem}>
                    <Text style={styles.reminderBullet}>•</Text>
                    <Text style={styles.reminderText}>Orders can be picked up 1-3 business days after confirmation</Text>
                  </View>
                </View>
              </View>
            </View>
          </View>
        )}

        {/* Payment Option Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Payment Option</Text>
          <View style={styles.deliveryOptionsContainer}>
            <TouchableOpacity
              style={[
                styles.deliveryOptionCard,
                paymentOption === 'full' && styles.deliveryOptionCardSelected,
              ]}
              onPress={() => setPaymentOption('full')}
            >
              <View style={[
                styles.deliveryRadio,
                paymentOption === 'full' && styles.deliveryRadioSelected,
              ]}>
                {paymentOption === 'full' && <View style={styles.deliveryRadioInner} />}
              </View>
              <View style={styles.deliveryOptionContent}>
                <Ionicons name="wallet-outline" size={28} color="#8B1A1A" style={styles.deliveryOptionIcon} />
                <View>
                  <Text style={styles.deliveryOptionTitle}>Full Payment</Text>
                  <Text style={styles.deliveryOptionDesc}>Pay the entire amount now</Text>
                </View>
              </View>
            </TouchableOpacity>

            <TouchableOpacity
              style={[
                styles.deliveryOptionCard,
                paymentOption === 'downpayment' && styles.deliveryOptionCardSelected,
              ]}
              onPress={() => setPaymentOption('downpayment')}
            >
              <View style={[
                styles.deliveryRadio,
                paymentOption === 'downpayment' && styles.deliveryRadioSelected,
              ]}>
                {paymentOption === 'downpayment' && <View style={styles.deliveryRadioInner} />}
              </View>
              <View style={styles.deliveryOptionContent}>
                <Ionicons name="cash-outline" size={28} color="#8B1A1A" style={styles.deliveryOptionIcon} />
                <View>
                  <Text style={styles.deliveryOptionTitle}>Downpayment ({DOWNPAYMENT_RATE}%)</Text>
                  <Text style={styles.deliveryOptionDesc}>Pay half now, settle the balance on delivery/pickup</Text>
                </View>
              </View>
            </TouchableOpacity>
          </View>
        </View>

        {/* Address Selection Section - Only show when delivery is selected */}
        {deliveryOption === 'deliver' && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Delivery Address</Text>
            
            {savedAddresses.length > 0 && selectedAddressId ? (
              <View>
                {(() => {
                  const selectedAddress = savedAddresses.find(addr => addr.id === selectedAddressId);
                  if (!selectedAddress) return null;
                  
                  return (
                    <View key={selectedAddress.id} style={styles.selectedAddressCard}>
                      <View style={styles.addressHeader}>
                        <Text style={styles.addressNameWithPhone}>
                          {selectedAddress.fullName} ({selectedAddress.phoneNumber})
                        </Text>
                        {selectedAddress.isDefault && (
                          <View style={styles.defaultBadge}>
                            <Text style={styles.defaultBadgeText}>Default</Text>
                          </View>
                        )}
                      </View>
                      <Text style={styles.fullAddress}>
                        {[selectedAddress.street, selectedAddress.barangay, selectedAddress.city, selectedAddress.province, selectedAddress.region, selectedAddress.postalCode].filter(Boolean).join(', ')}
                      </Text>
                    </View>
                  );
                })()}
                
                <TouchableOpacity 
                  style={styles.changeAddressButton}
                  onPress={() => navigation.navigate('SavedAddresses')}
                >
                  <Text style={styles.changeAddressText}>Change</Text>
                </TouchableOpacity>
              </View>
            ) : (
              <Text style={styles.noAddressText}>No saved addresses. Add one to continue.</Text>
            )}

            {/* Add New Address Button */}
            <TouchableOpacity
              style={styles.addAddressButton}
              onPress={() => navigation.navigate('SavedAddresses')}
            >
              <Text style={styles.addAddressIcon}>+</Text>
              <Text style={styles.addAddressText}>Add a new address</Text>
            </TouchableOpacity>
          </View>
        )}

        {/* Order Summary Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Order Summary</Text>
          {itemsToCheckout.map((item, index) => {
            const currentQuantity = Number(item.quantity || 1);
            const unitPrice = Number(item.price || 0);
            const itemSubtotal = unitPrice * currentQuantity;
            const maxStock = Number(item.stock || 0);
            const isAtMaxStock = maxStock > 0 && currentQuantity >= maxStock;

            // Build image URI properly from the item
            const itemImage = item.image;
            let imageSource;
            if (itemImage && typeof itemImage === 'object' && itemImage.uri) {
              imageSource = itemImage;
            } else if (itemImage && typeof itemImage === 'string' && itemImage.startsWith('http')) {
              imageSource = { uri: itemImage };
            } else {
              imageSource = require('../assets/images/Saputangan.jpg');
            }
            
            return (
              <View key={item.id || index} style={styles.orderItemWithImage}>
                <Image
                  source={imageSource}
                  style={styles.orderItemImage}
                  resizeMode="cover"
                />
                <View style={styles.orderItemDetails}>
                  <Text style={styles.orderItemName}>
                    {item.name}
                  </Text>
                  {(item.variant_size || item.variant_color) ? (
                    <Text style={styles.orderItemVariantText}>
                      {[item.variant_size, item.variant_color].filter(Boolean).join(' / ')}
                    </Text>
                  ) : null}

                  <View style={styles.orderItemMetaRow}>
                    <Text style={styles.orderItemUnitPrice}>₱{unitPrice.toFixed(2)} each</Text>
                    <Text style={styles.orderItemPrice}>₱{itemSubtotal.toFixed(2)}</Text>
                  </View>

                  {deliveryOption === 'pickup' ? (
                    <View style={styles.orderItemQuantityStack}>
                      <Text style={styles.orderItemQuantityLabel}>Adjust quantity for pickup</Text>
                      <View style={styles.orderItemQuantityControls}>
                        <TouchableOpacity
                          style={styles.orderQtyButton}
                          onPress={() => handleOrderSummaryQuantityChange(item, -1)}
                        >
                          <Text style={styles.orderQtyButtonText}>−</Text>
                        </TouchableOpacity>

                        <View style={styles.orderQtyDisplay}>
                          <Text style={styles.orderQtyText}>{currentQuantity}</Text>
                        </View>

                        <TouchableOpacity
                          style={[styles.orderQtyButton, isAtMaxStock && styles.orderQtyButtonDisabled]}
                          onPress={() => handleOrderSummaryQuantityChange(item, 1)}
                          disabled={isAtMaxStock}
                        >
                          <Text style={styles.orderQtyButtonText}>+</Text>
                        </TouchableOpacity>
                      </View>

                      {isAtMaxStock ? (
                        <Text style={styles.orderQtyLimitText}>Maximum stock reached</Text>
                      ) : null}
                    </View>
                  ) : (
                    <Text style={styles.orderItemText}>Qty: {currentQuantity}</Text>
                  )}
                </View>
              </View>
            );
          })}

          <View style={styles.divider} />

          <View style={styles.orderItem}>
            <Text style={styles.orderItemText}>Subtotal</Text>
            <Text style={styles.orderItemPrice}>₱{subtotal.toFixed(2)}</Text>
          </View>

          <View style={styles.orderItem}>
            <Text style={styles.orderItemText}>Delivery Option</Text>
            <Text style={styles.orderItemPrice}>{deliveryOption === 'pickup' ? 'Pick Up' : 'Deliver'}</Text>
          </View>

          <View style={styles.orderItem}>
            <Text style={styles.orderItemText}>Shipping Fee</Text>
            <Text style={styles.orderItemPrice}>
              {deliveryOption === 'pickup' ? 'Free' : `₱${shippingFee.toFixed(2)}`}
            </Text>
          </View>

          <View style={styles.orderItem}>
            <Text style={styles.orderItemText}>Payment Option</Text>
            <Text style={styles.orderItemPrice}>
              {paymentOption === 'downpayment' ? `Downpayment (${DOWNPAYMENT_RATE}%)` : 'Full Payment'}
            </Text>
          </View>

          {/* Coupon Section */}
          <View style={styles.couponSection}>
            <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 8 }}>
              <MaterialCommunityIcons name="tag-outline" size={16} color="#92400e" style={{ marginRight: 6 }} />
              <Text style={{ fontSize: 13, fontWeight: '700', color: '#92400e' }}>🎟️ Available Coupons</Text>
            </View>

            {appliedCoupon ? (
              <View style={styles.couponAppliedBadge}>
                <MaterialCommunityIcons name="check-circle" size={16} color="#16A34A" />
                <Text style={[styles.couponAppliedText, { flex: 1 }]}>
                  {appliedCoupon.code} — {appliedCoupon.description}
                </Text>
                <TouchableOpacity onPress={handleRemoveCoupon} style={styles.couponRemoveBtn}>
                  <Text style={styles.couponRemoveBtnText}>Remove</Text>
                </TouchableOpacity>
              </View>
            ) : availableCoupons.length > 0 ? (
              <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8 }}>
                {availableCoupons.map(c => (
                  <TouchableOpacity
                    key={c.code}
                    onPress={() => handleApplyCoupon(c.code)}
                    disabled={applyingCoupon}
                    style={[{ paddingHorizontal: 12, paddingVertical: 7, borderRadius: 20, borderWidth: 1.5,
                              borderColor: '#d97706', backgroundColor: '#fffbeb' },
                              applyingCoupon && { opacity: 0.5 }]}>
                    <Text style={{ fontSize: 12, fontWeight: '700', color: '#92400e' }}>
                      🏷️ {c.code}: {c.description}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
            ) : (
              <Text style={{ fontSize: 12, color: theme.textMuted, fontStyle: 'italic' }}>No coupons available</Text>
            )}

            {applyingCoupon && (
              <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 8, gap: 6 }}>
                <ActivityIndicator size="small" color="#d97706" />
                <Text style={{ fontSize: 12, color: '#92400e' }}>Applying coupon...</Text>
              </View>
            )}
            {!!couponError && <Text style={styles.couponErrorText}>{couponError}</Text>}
          </View>

          {appliedCoupon && (
            <View style={styles.orderItem}>
              <Text style={[styles.orderItemText, { color: '#16A34A' }]}>Discount</Text>
              <Text style={[styles.orderItemPrice, { color: '#16A34A' }]}>−₱{discount.toFixed(2)}</Text>
            </View>
          )}

          <View style={styles.divider} />

          <View style={styles.orderItem}>
            <Text style={styles.totalText}>Total</Text>
            <Text style={styles.totalPrice}>₱{orderTotal.toFixed(2)}</Text>
          </View>

          <View style={styles.orderItem}>
            <Text style={styles.orderItemText}>Pay Now</Text>
            <Text style={styles.orderItemPrice}>₱{payableNow.toFixed(2)}</Text>
          </View>

          {paymentOption === 'downpayment' && (
            <View style={styles.orderItem}>
              <Text style={[styles.orderItemText, { color: '#92400E' }]}>Remaining Balance</Text>
              <Text style={[styles.orderItemPrice, { color: '#92400E' }]}>₱{remainingBalance.toFixed(2)}</Text>
            </View>
          )}
        </View>

        <TouchableOpacity 
          style={[
            styles.placeOrderButton,
            isCheckoutDisabled && styles.placeOrderButtonDisabled
          ]} 
          onPress={handlePlaceOrder}
          disabled={isCheckoutDisabled}
        >
          {isSubmittingOrder ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.placeOrderText}>
              Place Order - ₱{payableNow.toFixed(2)}
            </Text>
          )}
        </TouchableOpacity>

        <View style={{ height: 40 }} />
      </ScrollView>

      {/* Address Form Modal */}
      <Modal
        visible={showAddressForm}
        animationType="slide"
        onRequestClose={() => setShowAddressForm(false)}
      >
        <View style={styles.addressFormContainer}>
          <View style={styles.addressFormHeader}>
            <TouchableOpacity onPress={() => setShowAddressForm(false)}>
              <Text style={styles.backButton}>←</Text>
            </TouchableOpacity>
            <Text style={styles.addressFormTitle}>
              {isEditingAddress ? 'Edit Address' : 'New Address'}
            </Text>
            <View style={{ width: 40 }} />
          </View>

          <ScrollView style={styles.addressFormContent} showsVerticalScrollIndicator={false}>
            <View style={styles.addressFormSection}>
              <Text style={styles.addressFormSectionLabel}>Address</Text>

              <Text style={styles.addressFormLabel}>Full Name</Text>
              <TextInput
                style={styles.addressFormInput}
                placeholder="Enter full name"
                placeholderTextColor="#999"
                value={addressForm.fullName}
                onChangeText={(text) => setAddressForm({ ...addressForm, fullName: text })}
              />

              <Text style={styles.addressFormLabel}>Phone Number</Text>
              <TextInput
                style={styles.addressFormInput}
                placeholder="Enter phone number"
                placeholderTextColor="#999"
                keyboardType="phone-pad"
                value={addressForm.phoneNumber}
                onChangeText={(text) => setAddressForm({ ...addressForm, phoneNumber: text })}
              />

                <Text style={styles.addressFormLabel}>Province</Text>
                <TextInput
                  style={styles.addressFormInput}
                  placeholder="Enter province"
                  placeholderTextColor="#999"
                  value={addressForm.province}
                  onChangeText={(text) => setAddressForm({ ...addressForm, province: text })}
                />

                <Text style={styles.addressFormLabel}>City</Text>
                <TextInput
                  style={styles.addressFormInput}
                  placeholder="Enter city"
                  placeholderTextColor="#999"
                  value={addressForm.city}
                  onChangeText={(text) => setAddressForm({ ...addressForm, city: text })}
                />

                <Text style={styles.addressFormLabel}>Barangay</Text>
                <TextInput
                  style={styles.addressFormInput}
                  placeholder="Enter barangay"
                  placeholderTextColor="#999"
                  value={addressForm.barangay}
                  onChangeText={(text) => setAddressForm({ ...addressForm, barangay: text })}
                />

              <Text style={styles.addressFormLabel}>Postal Code</Text>
              <TextInput
                style={styles.addressFormInput}
                placeholder="Enter postal code"
                placeholderTextColor="#999"
                keyboardType="numeric"
                value={addressForm.postalCode}
                onChangeText={(text) => setAddressForm({ ...addressForm, postalCode: text })}
              />

              <Text style={styles.addressFormLabel}>Street Name, Building, House No.</Text>
              <TextInput
                style={[styles.addressFormInput, styles.addressFormInputLarge]}
                placeholder="Enter street address"
                placeholderTextColor="#999"
                multiline
                value={addressForm.street}
                onChangeText={(text) => setAddressForm({ ...addressForm, street: text })}
              />

              <View style={styles.defaultAddressRow}>
                <Text style={styles.defaultAddressText}>Set as Default Address</Text>
                <Switch
                  style={styles.switch}
                  trackColor={{ false: '#ccc', true: '#8B1A1A' }}
                  thumbColor={addressForm.isDefault ? '#fff' : '#f4f3f4'}
                  ios_backgroundColor="#ccc"
                  value={addressForm.isDefault}
                  onValueChange={(value) => setAddressForm({ ...addressForm, isDefault: value })}
                />
              </View>

              <View style={styles.labelAsRow}>
                <Text style={styles.labelAsText}>Label As:</Text>
                <View style={styles.labelButtons}>
                  {['Home', 'Work'].map((label) => (
                    <TouchableOpacity
                      key={label}
                      style={[
                        styles.labelButton,
                        addressForm.label === label && styles.labelButtonSelected,
                      ]}
                      onPress={() => setAddressForm({ ...addressForm, label })}
                    >
                      <Text style={[
                        styles.labelButtonText,
                        addressForm.label === label && styles.labelButtonTextSelected,
                      ]}>
                        {label}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>

              <View style={styles.addressFormButtons}>
                {isEditingAddress && (
                  <TouchableOpacity
                    style={styles.deleteButton}
                    onPress={() => {
                      handleDeleteAddress(editingAddressId);
                      setShowAddressForm(false);
                    }}
                  >
                    <Text style={styles.deleteButtonText}>Delete Address</Text>
                  </TouchableOpacity>
                )}
                <TouchableOpacity
                  style={styles.submitButton}
                  onPress={handleAddressSubmit}
                >
                  <Text style={styles.submitButtonText}>Submit</Text>
                </TouchableOpacity>
              </View>
            </View>
          </ScrollView>
        </View>
      </Modal>
    </View>
  );
};

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: theme.background,
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: theme.textSecondary,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: theme.background,
    padding: 20,
  },
  emptyText: {
    fontSize: 18,
    color: theme.textSecondary,
    marginBottom: 20,
  },
  shopButton: {
    backgroundColor: theme.primary,
    paddingHorizontal: 30,
    paddingVertical: 15,
    borderRadius: 8,
  },
  shopButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 15,
    backgroundColor: theme.cardBackground,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
  },
  backButton: {
    fontSize: 28,
    color: theme.primary,
    fontWeight: 'bold',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: theme.text,
  },
  content: {
    flex: 1,
  },
  section: {
    backgroundColor: theme.cardBackground,
    padding: 20,
    marginTop: 15,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: theme.text,
  },
  editButton: {
    color: theme.primary,
    fontSize: 14,
    fontWeight: 'bold',
  },
  infoRow: {
    flexDirection: 'row',
    marginBottom: 10,
  },
  infoLabel: {
    fontSize: 14,
    color: theme.textSecondary,
    width: 60,
  },
  infoValue: {
    fontSize: 14,
    color: theme.text,
    flex: 1,
  },
  // Customer Information Card Styles
  customerInfoCard: {
    backgroundColor: theme.surfaceBg,
    borderRadius: 10,
    padding: 15,
    borderWidth: 1,
    borderColor: theme.border,
  },
  customerInfoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
  },
  customerInfoIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: theme.dangerBg,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  customerInfoIcon: {
    fontSize: 18,
  },
  customerInfoContent: {
    flex: 1,
  },
  customerInfoLabel: {
    fontSize: 11,
    color: theme.textMuted,
    marginBottom: 2,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  customerInfoValue: {
    fontSize: 15,
    color: theme.text,
    fontWeight: '600',
  },
  customerInfoDivider: {
    height: 1,
    backgroundColor: theme.border,
    marginVertical: 5,
    marginLeft: 52,
  },
  customerInfoWarning: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: theme.warningBg,
    padding: 10,
    borderRadius: 8,
    marginTop: 10,
  },
  customerInfoWarningIcon: {
    fontSize: 14,
    marginRight: 8,
  },
  customerInfoWarningText: {
    fontSize: 12,
    color: theme.warningText,
    flex: 1,
  },
  addressList: {
    marginBottom: 20,
  },
  addressCard: {
    borderWidth: 1,
    borderColor: theme.border,
    borderRadius: 8,
    padding: 15,
    marginBottom: 12,
    backgroundColor: theme.cardBackground,
  },
  addressCardSelected: {
    borderColor: theme.primary,
    borderWidth: 2,
    backgroundColor: theme.dangerBg,
  },
  addressCardHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  addressRadioContainer: {
    marginRight: 12,
    paddingTop: 2,
  },
  radioButton: {
    width: 24,
    height: 24,
    borderRadius: 12,
    borderWidth: 2,
    borderColor: theme.border,
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioButtonSelected: {
    borderColor: theme.primary,
  },
  radioButtonInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: theme.primary,
  },
  addressNameSection: {
    flex: 1,
  },
  addressName: {
    fontSize: 16,
    fontWeight: '600',
    color: theme.text,
  },
  addressPhone: {
    fontSize: 13,
    color: theme.textSecondary,
    marginTop: 2,
  },
  editAddressButton: {
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  editAddressText: {
    color: theme.primary,
    fontSize: 13,
    fontWeight: '600',
  },
  addressStreet: {
    fontSize: 13,
    color: theme.text,
    marginBottom: 4,
    lineHeight: 18,
  },
  addressDetails: {
    fontSize: 12,
    color: theme.textSecondary,
    marginBottom: 3,
  },
  addressTags: {
    flexDirection: 'row',
    marginTop: 10,
    gap: 8,
  },
  tagDefault: {
    borderWidth: 1,
    borderColor: theme.primary,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  tagDefaultText: {
    color: theme.primary,
    fontSize: 11,
    fontWeight: '600',
  },
  tagLabel: {
    borderWidth: 1,
    borderColor: theme.textMuted,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  tagLabelText: {
    color: theme.textSecondary,
    fontSize: 11,
  },
  addAddressButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    borderWidth: 2,
    borderColor: theme.primary,
    borderStyle: 'dashed',
    borderRadius: 8,
    backgroundColor: theme.dangerBg,
  },
  addAddressIcon: {
    fontSize: 24,
    color: theme.primary,
    marginRight: 8,
    fontWeight: 'bold',
  },
  addAddressText: {
    color: theme.primary,
    fontSize: 15,
    fontWeight: '600',
  },
  orderItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  orderItemWithImage: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 15,
    backgroundColor: theme.surfaceBg,
    padding: 10,
    borderRadius: 8,
  },
  orderItemImage: {
    width: 60,
    height: 60,
    borderRadius: 8,
    marginRight: 12,
  },
  orderItemDetails: {
    flex: 1,
    gap: 6,
  },
  orderItemName: {
    fontSize: 14,
    color: theme.text,
    fontWeight: '600',
  },
  orderItemVariantText: {
    fontSize: 12,
    color: theme.textMuted,
  },
  orderItemMetaRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  orderItemUnitPrice: {
    fontSize: 12,
    color: theme.textSecondary,
  },
  orderItemText: {
    fontSize: 14,
    color: theme.text,
    flex: 1,
  },
  orderItemPrice: {
    fontSize: 14,
    color: theme.text,
    fontWeight: '600',
  },
  orderItemQuantityStack: {
    marginTop: 2,
    gap: 8,
  },
  orderItemQuantityLabel: {
    fontSize: 12,
    color: theme.textSecondary,
  },
  orderItemQuantityControls: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  orderQtyButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: theme.border,
    backgroundColor: theme.cardBackground,
    alignItems: 'center',
    justifyContent: 'center',
  },
  orderQtyButtonDisabled: {
    opacity: 0.45,
  },
  orderQtyButtonText: {
    fontSize: 18,
    color: theme.text,
    fontWeight: '700',
    lineHeight: 20,
  },
  orderQtyDisplay: {
    minWidth: 42,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: theme.border,
    backgroundColor: theme.surfaceBg,
    alignItems: 'center',
  },
  orderQtyText: {
    fontSize: 14,
    color: theme.text,
    fontWeight: '700',
  },
  orderQtyLimitText: {
    fontSize: 11,
    color: '#92400E',
    fontWeight: '500',
  },
  divider: {
    height: 1,
    backgroundColor: theme.border,
    marginVertical: 15,
  },
  couponSection: {
    backgroundColor: theme.cardBackground || '#FFF9F0',
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: '#FDE68A',
    padding: 12,
    marginVertical: 8,
  },
  couponRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  couponInput: {
    flex: 1,
    height: 38,
    borderWidth: 1.5,
    borderColor: '#FCD34D',
    borderRadius: 8,
    paddingHorizontal: 10,
    fontSize: 13,
    color: theme.text,
    backgroundColor: theme.background,
    marginRight: 6,
    letterSpacing: 1,
  },
  couponApplyBtn: {
    backgroundColor: '#8B1A1A',
    paddingHorizontal: 14,
    paddingVertical: 9,
    borderRadius: 8,
    minWidth: 64,
    alignItems: 'center',
  },
  couponApplyBtnText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 13,
  },
  couponRemoveBtn: {
    backgroundColor: '#6B7280',
    paddingHorizontal: 14,
    paddingVertical: 9,
    borderRadius: 8,
    minWidth: 64,
    alignItems: 'center',
  },
  couponRemoveBtnText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 13,
  },
  couponErrorText: {
    color: '#EF4444',
    fontSize: 12,
    marginTop: 6,
    marginLeft: 24,
  },
  couponSuccessText: {
    color: '#16A34A',
    fontSize: 12,
    marginTop: 6,
    marginLeft: 24,
    fontWeight: '600',
  },
  couponAppliedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#DCFCE7',
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 4,
    marginTop: 8,
    gap: 4,
  },
  couponAppliedText: {
    color: '#16A34A',
    fontSize: 12,
    fontWeight: '600',
    marginLeft: 4,
  },
  totalText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: theme.text,
  },
  totalPrice: {
    fontSize: 22,
    fontWeight: 'bold',
    color: theme.primary,
  },
  placeOrderButton: {
    backgroundColor: theme.primary,
    marginHorizontal: 20,
    marginTop: 20,
    padding: 18,
    borderRadius: 8,
    alignItems: 'center',
  },
  placeOrderButtonDisabled: {
    backgroundColor: theme.iconMuted,
  },
  placeOrderText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: theme.cardBackground,
    borderRadius: 12,
    padding: 24,
    width: '85%',
    maxWidth: 400,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: theme.text,
    marginBottom: 20,
  },
  modalLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: theme.text,
    marginBottom: 8,
  },
  modalInput: {
    backgroundColor: theme.surfaceBg,
    borderWidth: 1,
    borderColor: theme.border,
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    marginBottom: 15,
    color: theme.text,
  },
  modalButtonRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: 12,
    marginTop: 20,
  },
  modalCancelButton: {
    flex: 1,
    borderWidth: 1,
    borderColor: theme.border,
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalCancelText: {
    color: theme.textSecondary,
    fontSize: 14,
    fontWeight: '600',
  },
  modalSaveButton: {
    flex: 1,
    backgroundColor: theme.primary,
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalSaveText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  addressFormContainer: {
    flex: 1,
    backgroundColor: theme.background,
  },
  addressFormHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 15,
    backgroundColor: theme.cardBackground,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
  },
  addressFormTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: theme.text,
  },
  addressFormContent: {
    flex: 1,
  },
  addressFormSection: {
    backgroundColor: theme.cardBackground,
    padding: 20,
    marginTop: 15,
  },
  addressFormSectionLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    color: theme.text,
    marginBottom: 15,
  },
  addressFormLabel: {
    fontSize: 12,
    color: theme.textMuted,
    marginBottom: 8,
  },
  addressFormInput: {
    backgroundColor: theme.cardBackground,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
    padding: 12,
    fontSize: 14,
    marginBottom: 20,
    color: theme.text,
  },
  addressFormSelectInput: {
    backgroundColor: theme.cardBackground,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
    padding: 12,
    marginBottom: 20,
    justifyContent: 'center',
  },
  addressFormSelectText: {
    fontSize: 14,
    color: theme.text,
  },
  addressFormInputLarge: {
    height: 80,
    textAlignVertical: 'top',
  },
  defaultAddressRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
  },
  defaultAddressText: {
    fontSize: 14,
    color: theme.text,
  },
  switch: {
    marginHorizontal: 10,
  },
  labelAsRow: {
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
  },
  labelAsText: {
    fontSize: 14,
    color: theme.text,
    marginBottom: 10,
  },
  labelButtons: {
    flexDirection: 'row',
    gap: 10,
  },
  labelButton: {
    flex: 1,
    paddingVertical: 10,
    borderWidth: 1,
    borderColor: theme.border,
    borderRadius: 8,
    alignItems: 'center',
    backgroundColor: theme.cardBackground,
  },
  labelButtonSelected: {
    backgroundColor: theme.primary,
    borderColor: theme.primary,
  },
  labelButtonText: {
    fontSize: 13,
    color: theme.text,
    fontWeight: '600',
  },
  labelButtonTextSelected: {
    color: '#fff',
  },
  addressFormButtons: {
    marginTop: 20,
    gap: 10,
    marginBottom: 40,
  },
  deleteButton: {
    borderWidth: 2,
    borderColor: theme.primary,
    paddingVertical: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  deleteButtonText: {
    color: theme.primary,
    fontSize: 15,
    fontWeight: 'bold',
  },
  submitButton: {
    backgroundColor: theme.primary,
    paddingVertical: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  submitButtonText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: 'bold',
  },
  // Delivery Option Styles
  deliveryOptionsContainer: {
    gap: 12,
    marginTop: 10,
  },
  deliveryOptionCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 15,
    borderWidth: 1,
    borderColor: theme.border,
    borderRadius: 10,
    backgroundColor: theme.cardBackground,
  },
  deliveryOptionCardSelected: {
    borderColor: theme.primary,
    borderWidth: 2,
    backgroundColor: theme.dangerBg,
  },
  deliveryRadio: {
    width: 22,
    height: 22,
    borderRadius: 11,
    borderWidth: 2,
    borderColor: theme.border,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  deliveryRadioSelected: {
    borderColor: theme.primary,
  },
  deliveryRadioInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: theme.primary,
  },
  deliveryOptionContent: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  deliveryOptionIcon: {
    marginRight: 12,
  },
  deliveryOptionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: theme.text,
  },
  deliveryOptionDesc: {
    fontSize: 12,
    color: theme.textSecondary,
    marginTop: 2,
  },
  noAddressText: {
    textAlign: 'center',
    color: theme.textSecondary,
    fontSize: 14,
    marginVertical: 20,
    fontStyle: 'italic',
  },
  selectedAddressCard: {
    backgroundColor: theme.cardBackground,
    borderWidth: 1,
    borderColor: theme.border,
    borderRadius: 8,
    padding: 16,
    marginBottom: 12,
  },
  addressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  addressNameWithPhone: {
    fontSize: 15,
    fontWeight: '600',
    color: theme.text,
    flex: 1,
  },
  defaultBadge: {
    backgroundColor: theme.primary,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 4,
  },
  defaultBadgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: '600',
  },
  fullAddress: {
    fontSize: 13,
    color: theme.textSecondary,
    lineHeight: 20,
    marginBottom: 2,
  },
  changeAddressButton: {
    borderWidth: 2,
    borderColor: theme.primary,
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
    marginTop: 8,
  },
  changeAddressText: {
    color: theme.primary,
    fontSize: 15,
    fontWeight: '600',
  },
  // Store Pickup Information Styles
  sectionTitleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  storeInfoCard: {
    backgroundColor: theme.dangerBg,
    borderWidth: 1,
    borderColor: theme.border,
    borderRadius: 12,
    padding: 16,
    marginTop: 10,
  },
  storeHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 12,
  },
  storeIconContainer: {
    width: 50,
    height: 50,
    backgroundColor: theme.primary,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
  storeDetailsContainer: {
    gap: 12,
  },
  storeName: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.text,
    flex: 1,
  },
  storeDetailRow: {
    flexDirection: 'column',
    gap: 4,
  },
  storeDetailLabelContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 4,
  },
  storeDetailLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: theme.text,
  },
  storeDetailText: {
    fontSize: 13,
    color: theme.textSecondary,
    lineHeight: 20,
  },
  storeHoursBox: {
    backgroundColor: theme.cardBackground,
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: theme.border,
  },
  storeHoursText: {
    fontSize: 13,
    color: theme.text,
    marginTop: 4,
    lineHeight: 20,
  },
  storeClosedText: {
    fontSize: 12,
    color: theme.primary,
    marginTop: 4,
    fontWeight: '500',
  },
  importantReminders: {
    backgroundColor: '#FFFBEB',
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#FCD34D',
    marginTop: 4,
  },
  remindersTitleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 8,
  },
  remindersTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#92400E',
  },
  reminderItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 6,
  },
  reminderBullet: {
    fontSize: 13,
    color: '#92400E',
    marginRight: 8,
    marginTop: 1,
  },
  reminderText: {
    fontSize: 12,
    color: '#92400E',
    flex: 1,
    lineHeight: 18,
  },
});

export default CheckoutScreen;
