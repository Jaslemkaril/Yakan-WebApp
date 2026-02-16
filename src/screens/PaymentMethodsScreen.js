// src/screens/PaymentMethodsScreen.js
import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  TextInput,
  Modal,
  Alert,
  FlatList,
} from 'react-native';
import colors from '../constants/colors';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';

export default function PaymentMethodsScreen({ navigation }) {
  const { theme } = useTheme();
  const [paymentMethods, setPaymentMethods] = useState([
    {
      id: '1',
      type: 'credit_card',
      cardName: 'My Visa Card',
      cardNumber: '**** **** **** 1234',
      expiryDate: '12/25',
      isDefault: true,
    },
  ]);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [paymentType, setPaymentType] = useState('credit_card');
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    cardName: '',
    cardNumber: '',
    expiryDate: '',
    cvv: '',
    gcashNumber: '',
    gcashName: '',
    isDefault: false,
  });

  const handleAddPaymentMethod = () => {
    if (paymentType === 'credit_card') {
      if (!formData.cardName.trim() || !formData.cardNumber.trim() || !formData.expiryDate.trim()) {
        Alert.alert('Error', 'Please fill in all required fields');
        return;
      }

      if (editingId) {
        setPaymentMethods(paymentMethods.map(method =>
          method.id === editingId
            ? {
                ...method,
                cardName: formData.cardName,
                cardNumber: `**** **** **** ${formData.cardNumber.slice(-4)}`,
                expiryDate: formData.expiryDate,
                isDefault: formData.isDefault,
              }
            : { ...method, isDefault: false }
        ));
      } else {
        const newMethod = {
          id: Date.now().toString(),
          type: 'credit_card',
          cardName: formData.cardName,
          cardNumber: `**** **** **** ${formData.cardNumber.slice(-4)}`,
          expiryDate: formData.expiryDate,
          isDefault: formData.isDefault,
        };
        setPaymentMethods([...paymentMethods, newMethod]);
      }
    } else if (paymentType === 'gcash') {
      if (!formData.gcashName.trim() || !formData.gcashNumber.trim()) {
        Alert.alert('Error', 'Please fill in all required fields');
        return;
      }

      if (editingId) {
        setPaymentMethods(paymentMethods.map(method =>
          method.id === editingId
            ? {
                ...method,
                gcashName: formData.gcashName,
                gcashNumber: `**** **** ${formData.gcashNumber.slice(-4)}`,
                isDefault: formData.isDefault,
              }
            : { ...method, isDefault: false }
        ));
      } else {
        const newMethod = {
          id: Date.now().toString(),
          type: 'gcash',
          gcashName: formData.gcashName,
          gcashNumber: `**** **** ${formData.gcashNumber.slice(-4)}`,
          isDefault: formData.isDefault,
        };
        setPaymentMethods([...paymentMethods, newMethod]);
      }
    }

    setIsModalVisible(false);
    setFormData({ cardName: '', cardNumber: '', expiryDate: '', cvv: '', gcashNumber: '', gcashName: '', isDefault: false });
    setEditingId(null);
  };

  const handleDeletePaymentMethod = (id) => {
    if (paymentMethods.length === 1) {
      Alert.alert('Error', 'You must have at least one payment method');
      return;
    }
    setPaymentMethods(paymentMethods.filter(method => method.id !== id));
  };

  const openEditModal = (method) => {
    setEditingId(method.id);
    setPaymentType(method.type);
    if (method.type === 'credit_card') {
      setFormData({
        cardName: method.cardName,
        cardNumber: method.cardNumber.replace(/\*/g, ''),
        expiryDate: method.expiryDate,
        cvv: '',
        gcashNumber: '',
        gcashName: '',
        isDefault: method.isDefault,
      });
    } else {
      setFormData({
        cardName: '',
        cardNumber: '',
        expiryDate: '',
        cvv: '',
        gcashNumber: method.gcashNumber.replace(/\*/g, ''),
        gcashName: method.gcashName,
        isDefault: method.isDefault,
      });
    }
    setIsModalVisible(true);
  };

  const renderPaymentMethod = ({ item }) => (
    <View style={styles.cardContainer}>
      <View style={styles.cardContent}>
        {item.type === 'credit_card' ? (
          <View style={styles.cardInfo}>
            <Text style={styles.cardName}>{item.cardName}</Text>
            <Text style={styles.cardNumber}>{item.cardNumber}</Text>
            <View style={styles.cardFooter}>
              <Text style={styles.expiryText}>Expires: {item.expiryDate}</Text>
              {item.isDefault && <Text style={styles.defaultBadge}>Default</Text>}
            </View>
          </View>
        ) : (
          <View style={styles.gcashInfo}>
            <View style={styles.gcashHeader}>
              <Text style={styles.gcashIcon}>₱</Text>
              <View>
                <Text style={styles.gcashName}>{item.gcashName}</Text>
                <Text style={styles.gcashLabel}>GCash Wallet</Text>
              </View>
            </View>
            <Text style={styles.gcashNumber}>{item.gcashNumber}</Text>
            <View style={styles.gcashFooter}>
              {item.isDefault && <Text style={styles.defaultBadge}>Default</Text>}
            </View>
          </View>
        )}
      </View>
      <View style={styles.cardActions}>
        <TouchableOpacity 
          style={styles.editBtn}
          onPress={() => openEditModal(item)}
        >
          <Text style={styles.actionText}>Edit</Text>
        </TouchableOpacity>
        <TouchableOpacity 
          style={styles.deleteBtn}
          onPress={() => handleDeletePaymentMethod(item.id)}
        >
          <Text style={styles.deleteText}>Delete</Text>
        </TouchableOpacity>
      </View>
    </View>
  );

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Payment Methods" 
        navigation={navigation} 
        showBack={true}
      />

      <ScrollView style={styles.content}>
        {paymentMethods.length > 0 ? (
          <FlatList
            data={paymentMethods}
            renderItem={renderPaymentMethod}
            keyExtractor={item => item.id}
            scrollEnabled={false}
          />
        ) : (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No payment methods added yet</Text>
          </View>
        )}
      </ScrollView>

      <TouchableOpacity
        style={styles.addButton}
        onPress={() => {
          setEditingId(null);
          setPaymentType('credit_card');
          setFormData({ cardName: '', cardNumber: '', expiryDate: '', cvv: '', gcashNumber: '', gcashName: '', isDefault: false });
          setIsModalVisible(true);
        }}
      >
        <Text style={styles.addButtonText}>+ Add Payment Method</Text>
      </TouchableOpacity>

      <Modal
        visible={isModalVisible}
        transparent
        animationType="slide"
        onRequestClose={() => setIsModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>
                {editingId ? 'Edit Payment Method' : 'Add Payment Method'}
              </Text>
              <TouchableOpacity onPress={() => setIsModalVisible(false)}>
                <Text style={styles.closeButton}>✕</Text>
              </TouchableOpacity>
            </View>

            {/* Payment Type Selector */}
            {!editingId && (
              <View style={styles.paymentTypeContainer}>
                <TouchableOpacity
                  style={[styles.typeButton, paymentType === 'credit_card' && styles.typeButtonActive]}
                  onPress={() => setPaymentType('credit_card')}
                >
                  <Text style={[styles.typeButtonText, paymentType === 'credit_card' && styles.typeButtonTextActive]}>
                    Credit Card
                  </Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[styles.typeButton, paymentType === 'gcash' && styles.typeButtonActive]}
                  onPress={() => setPaymentType('gcash')}
                >
                  <Text style={[styles.typeButtonText, paymentType === 'gcash' && styles.typeButtonTextActive]}>
                    GCash
                  </Text>
                </TouchableOpacity>
              </View>
            )}

            {/* Credit Card Form */}
            {paymentType === 'credit_card' && (
              <>
                <TextInput
                  style={styles.input}
                  placeholder="Card Name (e.g., My Visa Card)"
                  value={formData.cardName}
                  onChangeText={(text) => setFormData({ ...formData, cardName: text })}
                />

                <TextInput
                  style={styles.input}
                  placeholder="Card Number"
                  value={formData.cardNumber}
                  onChangeText={(text) => setFormData({ ...formData, cardNumber: text })}
                  keyboardType="numeric"
                  maxLength={16}
                />

                <View style={styles.row}>
                  <TextInput
                    style={[styles.input, styles.halfInput]}
                    placeholder="MM/YY"
                    value={formData.expiryDate}
                    onChangeText={(text) => setFormData({ ...formData, expiryDate: text })}
                    maxLength={5}
                  />
                  <TextInput
                    style={[styles.input, styles.halfInput]}
                    placeholder="CVV"
                    value={formData.cvv}
                    onChangeText={(text) => setFormData({ ...formData, cvv: text })}
                    keyboardType="numeric"
                    maxLength={3}
                    secureTextEntry
                  />
                </View>
              </>
            )}

            {/* GCash Form */}
            {paymentType === 'gcash' && (
              <>
                <TextInput
                  style={styles.input}
                  placeholder="Account Name"
                  value={formData.gcashName}
                  onChangeText={(text) => setFormData({ ...formData, gcashName: text })}
                />

                <TextInput
                  style={styles.input}
                  placeholder="GCash Mobile Number"
                  value={formData.gcashNumber}
                  onChangeText={(text) => setFormData({ ...formData, gcashNumber: text })}
                  keyboardType="phone-pad"
                  maxLength={11}
                />
              </>
            )}

            <TouchableOpacity
              style={styles.checkboxContainer}
              onPress={() => setFormData({ ...formData, isDefault: !formData.isDefault })}
            >
              <View style={[styles.checkbox, formData.isDefault && styles.checkboxChecked]}>
                {formData.isDefault && <Text style={styles.checkmark}>✓</Text>}
              </View>
              <Text style={styles.checkboxLabel}>Set as default payment method</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.saveButton}
              onPress={handleAddPaymentMethod}
            >
              <Text style={styles.saveButtonText}>
                {editingId ? 'Update Payment Method' : 'Add Payment Method'}
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.white,
  },
  header: {
    backgroundColor: colors.primary,
    padding: 20,
    paddingTop: 30,
    flexDirection: 'row',
    alignItems: 'center',
  },
  backButton: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '600',
    marginRight: 15,
  },
  title: {
    color: colors.white,
    fontSize: 24,
    fontWeight: 'bold',
    flex: 1,
  },
  content: {
    flex: 1,
    padding: 15,
  },
  emptyContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 40,
  },
  emptyText: {
    color: colors.text,
    fontSize: 16,
  },
  cardContainer: {
    backgroundColor: colors.lightGray,
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    borderLeftWidth: 4,
    borderLeftColor: colors.primary,
  },
  cardContent: {
    marginBottom: 12,
  },
  cardInfo: {
    backgroundColor: colors.primary,
    borderRadius: 8,
    padding: 15,
  },
  cardName: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  cardNumber: {
    color: colors.white,
    fontSize: 18,
    fontWeight: '600',
    letterSpacing: 2,
    marginBottom: 15,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  expiryText: {
    color: colors.white,
    fontSize: 12,
  },
  defaultBadge: {
    backgroundColor: colors.white,
    color: colors.primary,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    fontSize: 12,
    fontWeight: '600',
  },
  gcashInfo: {
    backgroundColor: '#1E90FF',
    borderRadius: 8,
    padding: 15,
  },
  gcashHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  gcashIcon: {
    color: colors.white,
    fontSize: 32,
    fontWeight: 'bold',
    marginRight: 12,
  },
  gcashName: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
  gcashLabel: {
    color: 'rgba(255, 255, 255, 0.8)',
    fontSize: 12,
    marginTop: 2,
  },
  gcashNumber: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 10,
  },
  gcashFooter: {
    alignItems: 'flex-end',
  },
  cardActions: {
    flexDirection: 'row',
    gap: 10,
  },
  editBtn: {
    flex: 1,
    backgroundColor: colors.primary,
    padding: 10,
    borderRadius: 8,
    alignItems: 'center',
  },
  deleteBtn: {
    flex: 1,
    backgroundColor: '#ffebee',
    padding: 10,
    borderRadius: 8,
    alignItems: 'center',
  },
  actionText: {
    color: colors.white,
    fontWeight: '600',
    fontSize: 14,
  },
  deleteText: {
    color: colors.primary,
    fontWeight: '600',
    fontSize: 14,
  },
  addButton: {
    backgroundColor: colors.primary,
    padding: 15,
    margin: 15,
    borderRadius: 12,
    alignItems: 'center',
  },
  addButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: colors.white,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 20,
    paddingBottom: 30,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: colors.dark,
  },
  closeButton: {
    fontSize: 24,
    color: colors.text,
  },
  paymentTypeContainer: {
    flexDirection: 'row',
    marginBottom: 20,
    gap: 10,
  },
  typeButton: {
    flex: 1,
    paddingVertical: 12,
    paddingHorizontal: 15,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: colors.border,
    alignItems: 'center',
  },
  typeButtonActive: {
    backgroundColor: colors.primary,
    borderColor: colors.primary,
  },
  typeButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
  },
  typeButtonTextActive: {
    color: colors.white,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    padding: 12,
    marginBottom: 15,
    fontSize: 14,
    color: colors.dark,
  },
  row: {
    flexDirection: 'row',
    gap: 10,
  },
  halfInput: {
    flex: 1,
    marginBottom: 15,
  },
  checkboxContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 4,
    borderWidth: 2,
    borderColor: colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
  },
  checkboxChecked: {
    backgroundColor: colors.primary,
  },
  checkmark: {
    color: colors.white,
    fontSize: 14,
    fontWeight: 'bold',
  },
  checkboxLabel: {
    fontSize: 14,
    color: colors.dark,
  },
  saveButton: {
    backgroundColor: colors.primary,
    padding: 14,
    borderRadius: 8,
    alignItems: 'center',
  },
  saveButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
});
