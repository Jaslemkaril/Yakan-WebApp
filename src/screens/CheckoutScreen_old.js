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
  FlatList,
} from 'react-native';
import { useCart } from '../context/CartContext';
import AsyncStorage from '@react-native-async-storage/async-storage';

const CheckoutScreen = ({ navigation }) => {
  const { cartItems, getCartTotal, clearCart, userInfo, updateUserInfo } = useCart();
  const [isEditModalVisible, setIsEditModalVisible] = useState(false);
  const [editedName, setEditedName] = useState(userInfo?.name || '');
  const [editedEmail, setEditedEmail] = useState(userInfo?.email || '');
  const [shippingAddress, setShippingAddress] = useState({
    street: '',
    city: '',
    province: '',
    zipCode: '',
    phoneNumber: '',
  });

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
  const total = subtotal + shippingFee;

  const generateOrderRef = () => {
    return 'ORD-' + Date.now().toString().slice(-8);
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

  const handlePlaceOrder = async () => {
    if (!shippingAddress.street || !shippingAddress.city || 
        !shippingAddress.province || !shippingAddress.zipCode || 
        !shippingAddress.phoneNumber) {
      Alert.alert('Error', 'Please fill in all shipping address fields');
      return;
    }

    const orderRef = generateOrderRef();
    
    const orderData = {
      orderRef,
      date: new Date().toISOString(),
      items: cartItems,
      shippingAddress,
      subtotal,
      shippingFee,
      total,
      status: 'pending_payment',
    };

    // Save order before navigating to payment
    await saveOrder(orderData);

    // Navigate to payment screen with order data
    navigation.navigate('Payment', { orderData });
  };

  const handleSaveCustomerInfo = () => {
    if (!editedName.trim() || !editedEmail.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    updateUserInfo({ name: editedName, email: editedEmail });
    setIsEditModalVisible(false);
    Alert.alert('Success', 'Customer information updated!');
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
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Customer Information</Text>
            <TouchableOpacity 
              onPress={() => {
                setEditedName(userInfo?.name || '');
                setEditedEmail(userInfo?.email || '');
                setIsEditModalVisible(true);
              }}
            >
              <Text style={styles.editButton}>Edit</Text>
            </TouchableOpacity>
          </View>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Name:</Text>
            <Text style={styles.infoValue}>{userInfo?.name || 'Not set'}</Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Email:</Text>
            <Text style={styles.infoValue}>{userInfo?.email || 'Not set'}</Text>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Shipping Address</Text>
          <TextInput
            style={styles.input}
            placeholder="Street Address"
            placeholderTextColor="#999"
            value={shippingAddress.street}
            onChangeText={(text) => setShippingAddress({ ...shippingAddress, street: text })}
          />
          <TextInput
            style={styles.input}
            placeholder="City"
            placeholderTextColor="#999"
            value={shippingAddress.city}
            onChangeText={(text) => setShippingAddress({ ...shippingAddress, city: text })}
          />
          <TextInput
            style={styles.input}
            placeholder="Province"
            placeholderTextColor="#999"
            value={shippingAddress.province}
            onChangeText={(text) => setShippingAddress({ ...shippingAddress, province: text })}
          />
          <TextInput
            style={styles.input}
            placeholder="Zip Code"
            placeholderTextColor="#999"
            keyboardType="numeric"
            value={shippingAddress.zipCode}
            onChangeText={(text) => setShippingAddress({ ...shippingAddress, zipCode: text })}
          />
          <TextInput
            style={styles.input}
            placeholder="Phone Number"
            placeholderTextColor="#999"
            keyboardType="phone-pad"
            value={shippingAddress.phoneNumber}
            onChangeText={(text) => setShippingAddress({ ...shippingAddress, phoneNumber: text })}
          />
        </View>

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
            <Text style={styles.orderItemText}>Shipping Fee</Text>
            <Text style={styles.orderItemPrice}>₱{shippingFee.toFixed(2)}</Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.orderItem}>
            <Text style={styles.totalText}>Total</Text>
            <Text style={styles.totalPrice}>₱{total.toFixed(2)}</Text>
          </View>
        </View>

        <TouchableOpacity style={styles.placeOrderButton} onPress={handlePlaceOrder}>
          <Text style={styles.placeOrderText}>Proceed to Payment - ₱{total.toFixed(2)}</Text>
        </TouchableOpacity>

        <View style={{ height: 40 }} />
      </ScrollView>

      <Modal
        visible={isEditModalVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setIsEditModalVisible(false)}
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
                onPress={() => setIsEditModalVisible(false)}
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
    color: '#333',
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
  input: {
    backgroundColor: '#f9f9f9',
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 8,
    padding: 15,
    fontSize: 14,
    marginBottom: 12,
    color: '#333',
  },
  paymentNote: {
    fontSize: 13,
    color: '#666',
    marginBottom: 15,
    fontStyle: 'italic',
  },
  paymentOption: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 18,
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 8,
    marginBottom: 12,
    backgroundColor: '#fff',
  },
  paymentOptionSelected: {
    borderColor: '#8B1A1A',
    borderWidth: 2,
    backgroundColor: '#FFF5F5',
  },
  radioButton: {
    width: 24,
    height: 24,
    borderRadius: 12,
    borderWidth: 2,
    borderColor: '#8B1A1A',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  radioButtonInner: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: '#8B1A1A',
  },
  paymentContent: {
    flex: 1,
  },
  paymentText: {
    fontSize: 16,
    color: '#333',
    fontWeight: '600',
  },
  paymentSubtext: {
    fontSize: 12,
    color: '#666',
    marginTop: 2,
  },
  instructionsBox: {
    backgroundColor: '#FFF9F0',
    borderWidth: 1,
    borderColor: '#FFE4B5',
    borderRadius: 8,
    padding: 15,
    marginTop: 15,
  },
  instructionsTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#8B1A1A',
    marginBottom: 12,
  },
  accountDetails: {
    backgroundColor: '#fff',
    borderRadius: 6,
    padding: 12,
    marginBottom: 12,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  detailLabel: {
    fontSize: 14,
    color: '#666',
    fontWeight: '500',
  },
  detailValue: {
    fontSize: 14,
    color: '#333',
    fontWeight: 'bold',
  },
  instructionSteps: {
    marginTop: 8,
  },
  stepText: {
    fontSize: 13,
    color: '#333',
    marginBottom: 6,
    lineHeight: 20,
  },
  contactText: {
    fontSize: 12,
    color: '#666',
    marginBottom: 3,
  },
  warningBox: {
    backgroundColor: '#FFF3F3',
    borderLeftWidth: 3,
    borderLeftColor: '#8B1A1A',
    padding: 10,
    marginTop: 12,
    borderRadius: 4,
  },
  warningText: {
    fontSize: 12,
    color: '#8B1A1A',
    fontWeight: '500',
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
});

export default CheckoutScreen;