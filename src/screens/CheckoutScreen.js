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
import { useNotification } from '../context/NotificationContext';
import AsyncStorage from '@react-native-async-storage/async-storage';
import useShippingFee from '../hooks/useShippingFee';
import API_CONFIG from '../config/config';
import ApiService from '../services/api';

const CheckoutScreen = ({ navigation }) => {
  const { cartItems, checkoutItems, getCartTotal, clearCart, userInfo, updateUserInfo } = useCart();
  const { notifyOrderCreated } = useNotification();
  const { calculateFee, loading: shippingLoading, error: shippingError } = useShippingFee();
  
  // Use checkoutItems if available, otherwise fall back to all cartItems
  const itemsToCheckout = checkoutItems && checkoutItems.length > 0 ? checkoutItems : cartItems;
  
  const [savedAddresses, setSavedAddresses] = useState([]);
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [showAddressForm, setShowAddressForm] = useState(false);
  const [isEditingAddress, setIsEditingAddress] = useState(false);
  const [editingAddressId, setEditingAddressId] = useState(null);
  
  // Delivery option state: 'pickup' or 'deliver'
  const [deliveryOption, setDeliveryOption] = useState('deliver');
  
  // Shipping fee state
  const [shippingFee, setShippingFee] = useState(0);
  const [calculatingShipping, setCalculatingShipping] = useState(false);
  
  // Coupon code state
  const [showCouponInput, setShowCouponInput] = useState(false);
  const [couponCode, setCouponCode] = useState('');
  const [appliedCoupon, setAppliedCoupon] = useState(null);
  const [couponError, setCouponError] = useState('');
  
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

  const calculateShippingFee = async () => {
    try {
      setCalculatingShipping(true);
      const selectedAddr = savedAddresses.find(addr => addr.id === selectedAddressId);
      
      if (!selectedAddr) {
        setShippingFee(0);
        return;
      }

      // Zone-based shipping calculation (matching web implementation)
      const cityLower = (selectedAddr.city || '').toLowerCase();
      const provinceLower = (selectedAddr.province || '').toLowerCase();
      const postalCode = selectedAddr.postalCode || '';
      
      let shippingFee = 0;
      
      // FREE - Zamboanga City proper (postal code starts with '7')
      if (cityLower.includes('zamboanga') && postalCode.startsWith('7')) {
        shippingFee = 0;
        console.log('[Checkout] FREE shipping for Zamboanga City proper');
      }
      // ₱80 - Zamboanga Peninsula (nearby)
      else if (provinceLower.includes('zamboanga') || 
               ['isabela', 'dipolog', 'dapitan', 'pagadian'].includes(cityLower)) {
        shippingFee = 80;
        console.log('[Checkout] ₱80 shipping for Zamboanga Peninsula');
      }
      // ₱120 - Western Mindanao
      else if (['basilan', 'sulu', 'tawi-tawi', 'cotabato', 'maguindanao'].includes(cityLower) ||
               provinceLower.includes('barmm') || provinceLower.includes('armm')) {
        shippingFee = 120;
        console.log('[Checkout] ₱120 shipping for Western Mindanao');
      }
      // ₱150 - Other Mindanao regions
      else if (provinceLower.includes('mindanao') ||
               ['davao', 'cagayan de oro', 'iligan', 'general santos', 'butuan', 'koronadal'].includes(cityLower)) {
        shippingFee = 150;
        console.log('[Checkout] ₱150 shipping for Mindanao');
      }
      // ₱180 - Visayas
      else if (provinceLower.includes('visayas') ||
               ['cebu', 'iloilo', 'bacolod', 'tacloban', 'dumaguete', 'tagbilaran', 'ormoc'].includes(cityLower)) {
        shippingFee = 180;
        console.log('[Checkout] ₱180 shipping for Visayas');
      }
      // ₱220 - Metro Manila & nearby
      else if (cityLower.includes('manila') || provinceLower.includes('ncr') ||
               ['quezon city', 'makati', 'pasig', 'taguig', 'caloocan', 'cavite', 'laguna', 'bulacan', 'rizal', 'pampanga'].includes(cityLower)) {
        shippingFee = 220;
        console.log('[Checkout] ₱220 shipping for Metro Manila');
      }
      // ₱250 - Northern Luzon
      else if (provinceLower.includes('luzon') ||
               ['baguio', 'tuguegarao', 'laoag', 'santiago', 'vigan'].includes(cityLower)) {
        shippingFee = 250;
        console.log('[Checkout] ₱250 shipping for Northern Luzon');
      }
      // ₱280 - Remote islands & far areas
      else {
        shippingFee = 280;
        console.log('[Checkout] ₱280 shipping for remote areas');
      }
      
      console.log('[Checkout] Final shipping fee:', shippingFee, 'for', cityLower, provinceLower, postalCode);
      setShippingFee(shippingFee);
    } catch (error) {
      console.log('Error calculating shipping:', error);
      setShippingFee(50); // Fallback fee
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
  const discount = appliedCoupon ? appliedCoupon.discount : 0;
  const total = subtotal + shippingFeeDisplay - discount;

  const generateOrderRef = () => {
    return 'ORD-' + Date.now().toString().slice(-8);
  };

  // Sample valid coupon codes (in real app, this would come from backend)
  const validCoupons = {
    'SAVE10': { code: 'SAVE10', discount: 10, description: '₱10 off' },
    'SAVE20': { code: 'SAVE20', discount: 20, description: '₱20 off' },
    'FREESHIP': { code: 'FREESHIP', discount: 5, description: 'Free shipping' },
  };

  const handleApplyCoupon = () => {
    setCouponError('');
    const code = couponCode.trim().toUpperCase();
    
    if (!code) {
      setCouponError('Please enter a coupon code');
      return;
    }
    
    if (validCoupons[code]) {
      setAppliedCoupon(validCoupons[code]);
      setCouponError('');
      Alert.alert('Success', `Coupon "${code}" applied! ${validCoupons[code].description}`);
    } else {
      setCouponError('Invalid coupon code');
      setAppliedCoupon(null);
    }
  };

  const handleRemoveCoupon = () => {
    setAppliedCoupon(null);
    setCouponCode('');
    setCouponError('');
  };

  const saveOrder = async (orderData) => {
    try {
      const existingOrders = await AsyncStorage.getItem('pendingOrders');
      const orders = existingOrders ? JSON.parse(existingOrders) : [];
      orders.push(orderData);
      await AsyncStorage.setItem('pendingOrders', JSON.stringify(orders));
    } catch (error) {
      console.log('Error saving order:', error);
    }
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

  const handleEditAddress = (address) => {
    setAddressForm(address);
    setIsEditingAddress(true);
    setEditingAddressId(address.id);
    setShowAddressForm(true);
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

  const handleSetDefault = (addressId) => {
    const updatedAddresses = savedAddresses.map(addr =>
      addr.id === addressId ? { ...addr, isDefault: true } : { ...addr, isDefault: false }
    );
    saveAddresses(updatedAddresses);
    setSavedAddresses(updatedAddresses);
  };

  const handlePlaceOrder = async () => {
    if (!selectedAddressId) {
      Alert.alert('Error', 'Please select a shipping address');
      return;
    }

    const selectedAddr = savedAddresses.find(addr => addr.id === selectedAddressId);

    const orderRef = generateOrderRef();
    
    const actualShippingFee = deliveryOption === 'pickup' ? 0 : shippingFee;
    const actualTotal = subtotal + actualShippingFee - discount;
    
    const orderData = {
      orderRef,
      date: new Date().toISOString(),
      items: itemsToCheckout,
      deliveryOption: deliveryOption,
      shippingAddress: {
        fullName: selectedAddr.fullName,
        phoneNumber: selectedAddr.phoneNumber,
        street: selectedAddr.street,
        barangay: selectedAddr.barangay,
        city: selectedAddr.city,
        province: selectedAddr.province,
        postalCode: selectedAddr.postalCode,
      },
      subtotal,
      shippingFee: actualShippingFee,
      discount: discount,
      couponCode: appliedCoupon?.code || null,
      total: actualTotal,
      status: 'pending_payment',
    };

    await saveOrder(orderData);
    notifyOrderCreated(orderRef);
    navigation.navigate('Payment', { orderData });
  };

  const selectedAddress = savedAddresses.find(addr => addr.id === selectedAddressId);

  const handleAddressSetup = async (newAddress) => {
    setSavedAddresses([newAddress]);
    setSelectedAddressId(newAddress.id);
    setShowQuickAddressSetup(false);
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Text style={styles.backButton}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Checkout</Text>
        <View style={{ width: 40 }} />
      </View>

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
                        {selectedAddress.street}, {selectedAddress.barangay}, {selectedAddress.city}, {selectedAddress.province}, {selectedAddress.postalCode}
                      </Text>
                      <Text style={styles.fullAddress}>
                        {selectedAddress.province}, {selectedAddress.postalCode}
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
              onPress={() => {
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
                setShowAddressForm(true);
              }}
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
            const imageUrl = item.image 
              ? `http://192.168.1.203:8000/uploads/products/${item.image}`
              : 'https://via.placeholder.com/60';
            
            console.log('[Checkout] Product image URL:', imageUrl);
            
            return (
              <View key={item.id || index} style={styles.orderItemWithImage}>
                <Image
                  source={{ uri: imageUrl }}
                  style={styles.orderItemImage}
                  resizeMode="cover"
                />
                <View style={styles.orderItemDetails}>
                  <Text style={styles.orderItemText}>
                    {item.name} × {item.quantity}
                  </Text>
                  <Text style={styles.orderItemPrice}>
                    ₱{(item.price * item.quantity).toFixed(2)}
                  </Text>
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

          <View style={styles.divider} />

          <View style={styles.orderItem}>
            <Text style={styles.totalText}>Total</Text>
            <Text style={styles.totalPrice}>
              ₱{deliveryOption === 'pickup' ? subtotal.toFixed(2) : total.toFixed(2)}
            </Text>
          </View>
        </View>

        <TouchableOpacity 
          style={[
            styles.placeOrderButton,
            !selectedAddressId && styles.placeOrderButtonDisabled
          ]} 
          onPress={handlePlaceOrder}
          disabled={!selectedAddressId}
        >
          <Text style={styles.placeOrderText}>
            Proceed to Payment - ₱{deliveryOption === 'pickup' ? subtotal.toFixed(2) : total.toFixed(2)}
          </Text>
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

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#666',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
    padding: 20,
  },
  emptyText: {
    fontSize: 18,
    color: '#666',
    marginBottom: 20,
  },
  shopButton: {
    backgroundColor: '#8B1A1A',
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
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  backButton: {
    fontSize: 28,
    color: '#8B1A1A',
    fontWeight: 'bold',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  content: {
    flex: 1,
  },
  section: {
    backgroundColor: '#fff',
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
    color: '#333',
  },
  editButton: {
    color: '#8B1A1A',
    fontSize: 14,
    fontWeight: 'bold',
  },
  infoRow: {
    flexDirection: 'row',
    marginBottom: 10,
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
    width: 60,
  },
  infoValue: {
    fontSize: 14,
    color: '#333',
    flex: 1,
  },
  // Customer Information Card Styles
  customerInfoCard: {
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
    padding: 15,
    borderWidth: 1,
    borderColor: '#e8e8e8',
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
    backgroundColor: '#FFF5F5',
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
    color: '#999',
    marginBottom: 2,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  customerInfoValue: {
    fontSize: 15,
    color: '#333',
    fontWeight: '600',
  },
  customerInfoDivider: {
    height: 1,
    backgroundColor: '#e8e8e8',
    marginVertical: 5,
    marginLeft: 52,
  },
  customerInfoWarning: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF3E0',
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
    color: '#E65100',
    flex: 1,
  },
  addressList: {
    marginBottom: 20,
  },
  addressCard: {
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 8,
    padding: 15,
    marginBottom: 12,
    backgroundColor: '#fff',
  },
  addressCardSelected: {
    borderColor: '#8B1A1A',
    borderWidth: 2,
    backgroundColor: '#FFF5F5',
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
    borderColor: '#e0e0e0',
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioButtonSelected: {
    borderColor: '#8B1A1A',
  },
  radioButtonInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: '#8B1A1A',
  },
  addressNameSection: {
    flex: 1,
  },
  addressName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  addressPhone: {
    fontSize: 13,
    color: '#666',
    marginTop: 2,
  },
  editAddressButton: {
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  editAddressText: {
    color: '#8B1A1A',
    fontSize: 13,
    fontWeight: '600',
  },
  addressStreet: {
    fontSize: 13,
    color: '#333',
    marginBottom: 4,
    lineHeight: 18,
  },
  addressDetails: {
    fontSize: 12,
    color: '#666',
    marginBottom: 3,
  },
  addressTags: {
    flexDirection: 'row',
    marginTop: 10,
    gap: 8,
  },
  tagDefault: {
    borderWidth: 1,
    borderColor: '#8B1A1A',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  tagDefaultText: {
    color: '#8B1A1A',
    fontSize: 11,
    fontWeight: '600',
  },
  tagLabel: {
    borderWidth: 1,
    borderColor: '#999',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  tagLabelText: {
    color: '#666',
    fontSize: 11,
  },
  addAddressButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    borderWidth: 2,
    borderColor: '#8B1A1A',
    borderStyle: 'dashed',
    borderRadius: 8,
    backgroundColor: '#FFF5F5',
  },
  addAddressIcon: {
    fontSize: 24,
    color: '#8B1A1A',
    marginRight: 8,
    fontWeight: 'bold',
  },
  addAddressText: {
    color: '#8B1A1A',
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
    backgroundColor: '#f9f9f9',
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
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  orderItemText: {
    fontSize: 14,
    color: '#333',
    flex: 1,
  },
  orderItemPrice: {
    fontSize: 14,
    color: '#333',
    fontWeight: '600',
  },
  divider: {
    height: 1,
    backgroundColor: '#e0e0e0',
    marginVertical: 15,
  },
  totalText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  totalPrice: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#8B1A1A',
  },
  placeOrderButton: {
    backgroundColor: '#8B1A1A',
    marginHorizontal: 20,
    marginTop: 20,
    padding: 18,
    borderRadius: 8,
    alignItems: 'center',
  },
  placeOrderButtonDisabled: {
    backgroundColor: '#ccc',
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
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 24,
    width: '85%',
    maxWidth: 400,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 20,
  },
  modalLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  modalInput: {
    backgroundColor: '#f9f9f9',
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    marginBottom: 15,
    color: '#333',
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
    borderColor: '#e0e0e0',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalCancelText: {
    color: '#666',
    fontSize: 14,
    fontWeight: '600',
  },
  modalSaveButton: {
    flex: 1,
    backgroundColor: '#8B1A1A',
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
    backgroundColor: '#f5f5f5',
  },
  addressFormHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 15,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  addressFormTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  addressFormContent: {
    flex: 1,
  },
  addressFormSection: {
    backgroundColor: '#fff',
    padding: 20,
    marginTop: 15,
  },
  addressFormSectionLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  addressFormLabel: {
    fontSize: 12,
    color: '#999',
    marginBottom: 8,
  },
  addressFormInput: {
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
    padding: 12,
    fontSize: 14,
    marginBottom: 20,
    color: '#333',
  },
  addressFormSelectInput: {
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
    padding: 12,
    marginBottom: 20,
    justifyContent: 'center',
  },
  addressFormSelectText: {
    fontSize: 14,
    color: '#333',
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
    borderBottomColor: '#e0e0e0',
  },
  defaultAddressText: {
    fontSize: 14,
    color: '#333',
  },
  switch: {
    marginHorizontal: 10,
  },
  labelAsRow: {
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  labelAsText: {
    fontSize: 14,
    color: '#333',
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
    borderColor: '#e0e0e0',
    borderRadius: 8,
    alignItems: 'center',
    backgroundColor: '#fff',
  },
  labelButtonSelected: {
    backgroundColor: '#8B1A1A',
    borderColor: '#8B1A1A',
  },
  labelButtonText: {
    fontSize: 13,
    color: '#333',
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
    borderColor: '#8B1A1A',
    paddingVertical: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  deleteButtonText: {
    color: '#8B1A1A',
    fontSize: 15,
    fontWeight: 'bold',
  },
  submitButton: {
    backgroundColor: '#8B1A1A',
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
    borderColor: '#e0e0e0',
    borderRadius: 10,
    backgroundColor: '#fff',
  },
  deliveryOptionCardSelected: {
    borderColor: '#8B1A1A',
    borderWidth: 2,
    backgroundColor: '#FFF5F5',
  },
  deliveryRadio: {
    width: 22,
    height: 22,
    borderRadius: 11,
    borderWidth: 2,
    borderColor: '#e0e0e0',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  deliveryRadioSelected: {
    borderColor: '#8B1A1A',
  },
  deliveryRadioInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: '#8B1A1A',
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
    color: '#333',
  },
  deliveryOptionDesc: {
    fontSize: 12,
    color: '#666',
    marginTop: 2,
  },
  noAddressText: {
    textAlign: 'center',
    color: '#666',
    fontSize: 14,
    marginVertical: 20,
    fontStyle: 'italic',
  },
  selectedAddressCard: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#e0e0e0',
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
    color: '#1a1a1a',
    flex: 1,
  },
  defaultBadge: {
    backgroundColor: '#8B1A1A',
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
    color: '#666',
    lineHeight: 20,
    marginBottom: 2,
  },
  changeAddressButton: {
    borderWidth: 2,
    borderColor: '#8B1A1A',
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
    marginTop: 8,
  },
  changeAddressText: {
    color: '#8B1A1A',
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
    backgroundColor: '#FFF5F5',
    borderWidth: 1,
    borderColor: '#e0e0e0',
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
    backgroundColor: '#8B1A1A',
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
    color: '#1a1a1a',
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
    color: '#1a1a1a',
  },
  storeDetailText: {
    fontSize: 13,
    color: '#666',
    lineHeight: 20,
  },
  storeHoursBox: {
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#e0e0e0',
  },
  storeHoursText: {
    fontSize: 13,
    color: '#333',
    marginTop: 4,
    lineHeight: 20,
  },
  storeClosedText: {
    fontSize: 12,
    color: '#8B1A1A',
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
