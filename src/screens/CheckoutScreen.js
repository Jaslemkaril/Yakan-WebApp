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
} from 'react-native';
import { useCart } from '../context/CartContext';
import AsyncStorage from '@react-native-async-storage/async-storage';

const CheckoutScreen = ({ navigation }) => {
  const { cartItems, getCartTotal, clearCart, userInfo, updateUserInfo } = useCart();
  const [isEditCustomerModalVisible, setIsEditCustomerModalVisible] = useState(false);
  const [editedName, setEditedName] = useState(userInfo?.name || '');
  const [editedEmail, setEditedEmail] = useState(userInfo?.email || '');
  
  const [savedAddresses, setSavedAddresses] = useState([]);
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [showAddressForm, setShowAddressForm] = useState(false);
  const [isEditingAddress, setIsEditingAddress] = useState(false);
  const [editingAddressId, setEditingAddressId] = useState(null);
  
  // Delivery option state: 'pickup' or 'deliver'
  const [deliveryOption, setDeliveryOption] = useState('deliver');
  
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

  // Load saved addresses on mount
  useEffect(() => {
    loadAddresses();
  }, []);

  const loadAddresses = async () => {
    try {
      const addresses = await AsyncStorage.getItem('savedAddresses');
      if (addresses) {
        const parsedAddresses = JSON.parse(addresses);
        setSavedAddresses(parsedAddresses);
        
        // Set default address as selected
        const defaultAddress = parsedAddresses.find(addr => addr.isDefault);
        if (defaultAddress) {
          setSelectedAddressId(defaultAddress.id);
        } else if (parsedAddresses.length > 0) {
          setSelectedAddressId(parsedAddresses[0].id);
        }
      }
    } catch (error) {
      console.log('Error loading addresses:', error);
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
    if (!cartItems || cartItems.length === 0) {
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

  if (!cartItems) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#8B1A1A" />
        <Text style={styles.loadingText}>Loading...</Text>
      </View>
    );
  }

  if (cartItems.length === 0) {
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

  const shippingFee = 5.00;
  const subtotal = getCartTotal();
  const discount = appliedCoupon ? appliedCoupon.discount : 0;
  const total = subtotal + shippingFee - discount;

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
      items: cartItems,
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
    navigation.navigate('Payment', { orderData });
  };

  const handleSaveCustomerInfo = () => {
    if (!editedName.trim() || !editedEmail.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    updateUserInfo({ name: editedName, email: editedEmail });
    setIsEditCustomerModalVisible(false);
    Alert.alert('Success', 'Customer information updated!');
  };

  const selectedAddress = savedAddresses.find(addr => addr.id === selectedAddressId);

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
                <Text style={styles.deliveryOptionIcon}>🏪</Text>
                <View>
                  <Text style={styles.deliveryOptionTitle}>Pick Up</Text>
                  <Text style={styles.deliveryOptionDesc}>Pick up at store location</Text>
                </View>
              </View>
            </TouchableOpacity>

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
                <Text style={styles.deliveryOptionIcon}>🚚</Text>
                <View>
                  <Text style={styles.deliveryOptionTitle}>Deliver</Text>
                  <Text style={styles.deliveryOptionDesc}>Deliver to your address (+₱{shippingFee.toFixed(2)})</Text>
                </View>
              </View>
            </TouchableOpacity>
          </View>
        </View>

        {/* Customer Information Section */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Customer Information</Text>
            <TouchableOpacity 
              onPress={() => {
                setEditedName(userInfo?.name || '');
                setEditedEmail(userInfo?.email || '');
                setIsEditCustomerModalVisible(true);
              }}
            >
              <Text style={styles.editButton}>Edit</Text>
            </TouchableOpacity>
          </View>
          <View style={styles.customerInfoCard}>
            <View style={styles.customerInfoRow}>
              <View style={styles.customerInfoIconContainer}>
                <Text style={styles.customerInfoIcon}>👤</Text>
              </View>
              <View style={styles.customerInfoContent}>
                <Text style={styles.customerInfoLabel}>Full Name</Text>
                <Text style={styles.customerInfoValue}>{userInfo?.name || 'Not set'}</Text>
              </View>
            </View>
            <View style={styles.customerInfoDivider} />
            <View style={styles.customerInfoRow}>
              <View style={styles.customerInfoIconContainer}>
                <Text style={styles.customerInfoIcon}>✉️</Text>
              </View>
              <View style={styles.customerInfoContent}>
                <Text style={styles.customerInfoLabel}>Email Address</Text>
                <Text style={styles.customerInfoValue}>{userInfo?.email || 'Not set'}</Text>
              </View>
            </View>
          </View>
          {(!userInfo?.name || !userInfo?.email) && (
            <View style={styles.customerInfoWarning}>
              <Text style={styles.customerInfoWarningIcon}>⚠️</Text>
              <Text style={styles.customerInfoWarningText}>
                Please complete your customer information
              </Text>
            </View>
          )}
        </View>

        {/* Address Selection Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Address Selection</Text>
          
          {savedAddresses.length > 0 && (
            <View style={styles.addressList}>
              {savedAddresses.map((address) => (
                <TouchableOpacity
                  key={address.id}
                  style={[
                    styles.addressCard,
                    selectedAddressId === address.id && styles.addressCardSelected,
                  ]}
                  onPress={() => setSelectedAddressId(address.id)}
                >
                  <View style={styles.addressCardHeader}>
                    <View style={styles.addressRadioContainer}>
                      <View style={[
                        styles.radioButton,
                        selectedAddressId === address.id && styles.radioButtonSelected,
                      ]}>
                        {selectedAddressId === address.id && (
                          <View style={styles.radioButtonInner} />
                        )}
                      </View>
                    </View>
                    <View style={styles.addressNameSection}>
                      <Text style={styles.addressName}>{address.fullName}</Text>
                      <Text style={styles.addressPhone}>{address.phoneNumber}</Text>
                    </View>
                    <TouchableOpacity 
                      onPress={() => handleEditAddress(address)}
                      style={styles.editAddressButton}
                    >
                      <Text style={styles.editAddressText}>Edit</Text>
                    </TouchableOpacity>
                  </View>
                  
                  <Text style={styles.addressStreet}>{address.street}</Text>
                  <Text style={styles.addressDetails}>
                    {address.barangay && `${address.barangay}, `}
                    {address.city && `${address.city}, `}
                    {address.province && `${address.province}`}
                  </Text>
                  <Text style={styles.addressDetails}>
                    {address.province}, {address.city}, {address.postalCode}
                  </Text>

                  <View style={styles.addressTags}>
                    {address.isDefault && (
                      <View style={styles.tagDefault}>
                        <Text style={styles.tagDefaultText}>Default</Text>
                      </View>
                    )}
                    {address.label && (
                      <View style={styles.tagLabel}>
                        <Text style={styles.tagLabelText}>{address.label}</Text>
                      </View>
                    )}
                  </View>
                </TouchableOpacity>
              ))}
            </View>
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

        {/* Order Summary Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Order Summary</Text>
          {cartItems.map((item, index) => (
            <View key={item.id || index} style={styles.orderItem}>
              <Text style={styles.orderItemText}>
                {item.name} × {item.quantity}
              </Text>
              <Text style={styles.orderItemPrice}>
                ₱{(item.price * item.quantity).toFixed(2)}
              </Text>
            </View>
          ))}

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

      {/* Customer Information Edit Modal */}
      <Modal
        visible={isEditCustomerModalVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setIsEditCustomerModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Edit Customer Information</Text>
            
            <Text style={styles.modalLabel}>Name</Text>
            <TextInput
              style={styles.modalInput}
              placeholder="Enter your name"
              placeholderTextColor="#999"
              value={editedName}
              onChangeText={setEditedName}
            />

            <Text style={styles.modalLabel}>Email</Text>
            <TextInput
              style={styles.modalInput}
              placeholder="Enter your email"
              placeholderTextColor="#999"
              value={editedEmail}
              onChangeText={setEditedEmail}
              keyboardType="email-address"
            />

            <View style={styles.modalButtonRow}>
              <TouchableOpacity 
                style={styles.modalCancelButton}
                onPress={() => setIsEditCustomerModalVisible(false)}
              >
                <Text style={styles.modalCancelText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity 
                style={styles.modalSaveButton}
                onPress={handleSaveCustomerInfo}
              >
                <Text style={styles.modalSaveText}>Save</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

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
  orderItemText: {
    fontSize: 14,
    color: '#333',
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
    fontSize: 28,
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
});

export default CheckoutScreen;
