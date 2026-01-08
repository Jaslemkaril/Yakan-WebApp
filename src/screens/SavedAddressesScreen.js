// src/screens/SavedAddressesScreen.js
import React, { useState, useEffect } from 'react';
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
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import colors from '../constants/colors';
import ApiService from '../services/api';
import { useCart } from '../context/CartContext';

export default function SavedAddressesScreen({ navigation }) {
  const { isLoggedIn } = useCart();
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    label: 'Home',
    full_name: '',
    phone_number: '',
    street: '',
    barangay: '',
    city: '',
    province: '',
    postal_code: '',
    isDefault: false,
  });

  useEffect(() => {
    if (isLoggedIn) {
      fetchAddresses();
    } else {
      setLoading(false);
      Alert.alert('Login Required', 'Please login to view saved addresses', [
        { text: 'OK', onPress: () => navigation.goBack() }
      ]);
    }
  }, [isLoggedIn]);

  const fetchAddresses = async () => {
    try {
      console.log('[SavedAddresses] Fetching addresses from API...');
      const response = await ApiService.getSavedAddresses();
      
      console.log('[SavedAddresses] Full response:', JSON.stringify(response, null, 2));
      
      if (response.success) {
        // Handle nested data structure - response.data might contain another data property
        const dataArray = response.data?.data || response.data;
        const items = Array.isArray(dataArray) ? dataArray : [];
        console.log('[SavedAddresses] Fetched addresses:', items.length);
        console.log('[SavedAddresses] First address:', items[0]);
        setAddresses(items);
      } else {
        console.error('[SavedAddresses] Failed to fetch:', response.error);
        setAddresses([]);
      }
    } catch (error) {
      console.error('[SavedAddresses] Error:', error);
      Alert.alert('Error', 'Failed to load addresses');
      setAddresses([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await fetchAddresses();
  };

  const handleAddAddress = async () => {
    // Validate required fields
    if (!formData.label.trim() || !formData.full_name.trim() || 
        !formData.phone_number.trim() || !formData.street.trim() || !formData.city.trim()) {
      Alert.alert('Error', 'Please fill in all required fields (Name, Phone, Street, City)');
      return;
    }

    try {
      const addressData = {
        label: formData.label,
        full_name: formData.full_name,
        phone_number: formData.phone_number,
        street: formData.street,
        barangay: formData.barangay || null,
        city: formData.city,
        province: formData.province || null,
        postal_code: formData.postal_code || null,
        is_default: formData.isDefault ? 1 : 0,
      };

      if (editingId) {
        // Update existing address
        const response = await ApiService.updateAddress(editingId, addressData);
        
        if (response.success) {
          Alert.alert('Success', 'Address updated successfully');
          await fetchAddresses();
        } else {
          Alert.alert('Error', response.error || 'Failed to update address');
        }
      } else {
        // Create new address
        const response = await ApiService.createAddress(addressData);
        
        if (response.success) {
          Alert.alert('Success', 'Address added successfully');
          await fetchAddresses();
        } else {
          Alert.alert('Error', response.error || 'Failed to add address');
        }
      }

      setIsModalVisible(false);
      setFormData({
        label: 'Home',
        full_name: '',
        phone_number: '',
        street: '',
        barangay: '',
        city: '',
        province: '',
        postal_code: '',
        isDefault: false,
      });
      setEditingId(null);
    } catch (error) {
      console.error('[SavedAddresses] Add/Update error:', error);
      Alert.alert('Error', 'Failed to save address');
    }
  };

  const handleDeleteAddress = async (id) => {
    Alert.alert(
      'Delete Address',
      'Are you sure you want to delete this address?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              const response = await ApiService.deleteAddress(id);
              
              if (response.success) {
                Alert.alert('Success', 'Address deleted successfully');
                await fetchAddresses();
              } else {
                Alert.alert('Error', response.error || 'Failed to delete address');
              }
            } catch (error) {
              console.error('[SavedAddresses] Delete error:', error);
              Alert.alert('Error', 'Failed to delete address');
            }
          }
        }
      ]
    );
  };

  const openEditModal = (address) => {
    setEditingId(address.id);
    setFormData({
      label: address.label || 'Home',
      full_name: address.full_name || '',
      phone_number: address.phone_number || '',
      street: address.street || '',
      barangay: address.barangay || '',
      city: address.city || '',
      province: address.province || '',
      postal_code: address.postal_code || '',
      isDefault: address.is_default === 1 || address.isDefault === true,
    });
    setIsModalVisible(true);
  };

  const renderAddressItem = ({ item }) => {
    // Build formatted address string
    const addressParts = [item.street, item.barangay, item.city, item.province, item.postal_code];
    const formattedAddress = addressParts.filter(part => part).join(', ');
    
    // Check if default
    const isDefault = item.is_default === 1 || item.is_default === true || item.isDefault === true;
    
    console.log(`[Address ${item.id}] is_default value:`, item.is_default, 'isDefault:', isDefault);

    return (
      <View style={styles.addressCard}>
        <View style={styles.addressHeader}>
          <View style={styles.addressHeaderLeft}>
            <Text style={styles.addressLabel}>{item.label}</Text>
            {isDefault && (
              <View style={styles.defaultBadge}>
                <Text style={styles.defaultBadgeText}>Default</Text>
              </View>
            )}
          </View>
        </View>
        <Text style={styles.addressName}>{item.full_name} ({item.phone_number})</Text>
        <Text style={styles.addressText}>{formattedAddress}</Text>
        <View style={styles.addressActions}>
          <TouchableOpacity 
            style={styles.editBtn}
            onPress={() => openEditModal(item)}
          >
            <Text style={styles.actionText}>Edit</Text>
          </TouchableOpacity>
          <TouchableOpacity 
            style={styles.deleteBtn}
            onPress={() => handleDeleteAddress(item.id)}
          >
            <Text style={styles.deleteText}>Delete</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()}>
            <Text style={styles.backButton}>← Back</Text>
          </TouchableOpacity>
          <Text style={styles.title}>Saved Addresses</Text>
        </View>

        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading addresses...</Text>
        </View>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Text style={styles.backButton}>← Back</Text>
        </TouchableOpacity>
        <Text style={styles.title}>Saved Addresses</Text>
      </View>

      <ScrollView style={styles.content}>
        {addresses.length > 0 ? (
          <FlatList
            data={addresses}
            renderItem={renderAddressItem}
            keyExtractor={item => item.id.toString()}
            scrollEnabled={false}
            refreshControl={
              <RefreshControl
                refreshing={refreshing}
                onRefresh={handleRefresh}
                colors={[colors.primary]}
              />
            }
          />
        ) : (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No addresses saved yet</Text>
          </View>
        )}
      </ScrollView>

      <TouchableOpacity
        style={styles.addButton}
        onPress={() => {
          setEditingId(null);
          setFormData({
            label: 'Home',
            full_name: '',
            phone_number: '',
            street: '',
            barangay: '',
            city: '',
            province: '',
            postal_code: '',
            isDefault: false,
          });
          setIsModalVisible(true);
        }}
      >
        <Text style={styles.addButtonText}>+ Add New Address</Text>
      </TouchableOpacity>

      <Modal
        visible={isModalVisible}
        transparent
        animationType="slide"
        onRequestClose={() => setIsModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <ScrollView style={styles.modalScrollView}>
            <View style={styles.modalContent}>
              <View style={styles.modalHeader}>
                <Text style={styles.modalTitle}>
                  {editingId ? 'Edit Address' : 'Add New Address'}
                </Text>
                <TouchableOpacity onPress={() => setIsModalVisible(false)}>
                  <Text style={styles.closeButton}>✕</Text>
                </TouchableOpacity>
              </View>

              <Text style={styles.sectionLabel}>Address Type</Text>
              <View style={styles.labelOptions}>
                {['Home', 'Office', 'Other'].map((option) => (
                  <TouchableOpacity
                    key={option}
                    style={[
                      styles.labelOption,
                      formData.label === option && styles.labelOptionActive
                    ]}
                    onPress={() => setFormData({ ...formData, label: option })}
                  >
                    <Text style={[
                      styles.labelOptionText,
                      formData.label === option && styles.labelOptionTextActive
                    ]}>
                      {option}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>

              <Text style={styles.sectionLabel}>Contact Information</Text>
              <TextInput
                style={styles.input}
                placeholder="Full Name *"
                value={formData.full_name}
                onChangeText={(text) => setFormData({ ...formData, full_name: text })}
              />

              <TextInput
                style={styles.input}
                placeholder="Phone Number *"
                value={formData.phone_number}
                onChangeText={(text) => setFormData({ ...formData, phone_number: text })}
                keyboardType="phone-pad"
              />

              <Text style={styles.sectionLabel}>Address Details</Text>
              <TextInput
                style={styles.input}
                placeholder="Street Address *"
                value={formData.street}
                onChangeText={(text) => setFormData({ ...formData, street: text })}
              />

              <TextInput
                style={styles.input}
                placeholder="Barangay (Optional)"
                value={formData.barangay}
                onChangeText={(text) => setFormData({ ...formData, barangay: text })}
              />

              <View style={styles.row}>
                <TextInput
                  style={[styles.input, styles.halfInput]}
                  placeholder="City *"
                  value={formData.city}
                  onChangeText={(text) => setFormData({ ...formData, city: text })}
                />

                <TextInput
                  style={[styles.input, styles.halfInput]}
                  placeholder="Province"
                  value={formData.province}
                  onChangeText={(text) => setFormData({ ...formData, province: text })}
                />
              </View>

              <TextInput
                style={styles.input}
                placeholder="Postal Code"
                value={formData.postal_code}
                onChangeText={(text) => setFormData({ ...formData, postal_code: text })}
                keyboardType="number-pad"
              />

              <TouchableOpacity
                style={styles.checkboxContainer}
                onPress={() => setFormData({ ...formData, isDefault: !formData.isDefault })}
              >
                <View style={[styles.checkbox, formData.isDefault && styles.checkboxChecked]}>
                  {formData.isDefault && <Text style={styles.checkmark}>✓</Text>}
                </View>
                <Text style={styles.checkboxLabel}>Set as default address</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.saveButton}
                onPress={handleAddAddress}
              >
                <Text style={styles.saveButtonText}>
                  {editingId ? 'Update Address' : 'Save Address'}
                </Text>
              </TouchableOpacity>
            </View>
          </ScrollView>
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
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    color: colors.text,
    fontSize: 14,
  },
  addressCard: {
    backgroundColor: colors.lightGray,
    borderRadius: 12,
    padding: 15,
    marginBottom: 15,
    borderLeftWidth: 4,
    borderLeftColor: colors.primary,
  },
  addressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 10,
  },
  addressHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  addressLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.dark,
  },
  addressName: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.dark,
    marginBottom: 5,
  },
  defaultBadge: {
    backgroundColor: colors.primary,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
    alignSelf: 'flex-start',
  },
  defaultBadgeText: {
    color: colors.white,
    fontSize: 11,
    fontWeight: '600',
  },
  addressText: {
    fontSize: 14,
    color: colors.text,
    marginBottom: 12,
    lineHeight: 20,
  },
  addressActions: {
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
  },
  modalScrollView: {
    flex: 1,
  },
  modalContent: {
    backgroundColor: colors.white,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 20,
    paddingBottom: 40,
    marginTop: 50,
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
  sectionLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.dark,
    marginBottom: 10,
    marginTop: 5,
  },
  labelOptions: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 20,
  },
  labelOption: {
    flex: 1,
    paddingVertical: 10,
    paddingHorizontal: 15,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
  },
  labelOptionActive: {
    backgroundColor: colors.primary,
    borderColor: colors.primary,
  },
  labelOptionText: {
    fontSize: 14,
    color: colors.text,
  },
  labelOptionTextActive: {
    color: colors.white,
    fontWeight: '600',
  },
  row: {
    flexDirection: 'row',
    gap: 10,
  },
  halfInput: {
    flex: 1,
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
  textArea: {
    textAlignVertical: 'top',
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
