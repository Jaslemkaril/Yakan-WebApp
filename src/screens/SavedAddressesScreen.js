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
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import colors from '../constants/colors';
import ApiService from '../services/api';
import { useCart } from '../context/CartContext';

export default function SavedAddressesScreen({ navigation }) {
  const { theme } = useTheme();
  const styles = getStyles(theme);
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
    
    // Get icon based on label
    const getAddressIcon = (label) => {
      switch(label?.toLowerCase()) {
        case 'home': return 'home-outline';
        case 'office': return 'briefcase-outline';
        default: return 'map-marker-multiple';
      }
    };
    
    const getIconColor = (label) => {
      switch(label?.toLowerCase()) {
        case 'home': return '#E74C3C';
        case 'office': return '#3498DB';
        default: return '#9B59B6';
      }
    };
    
    console.log(`[Address ${item.id}] is_default value:`, item.is_default, 'isDefault:', isDefault);

    return (
      <View style={styles.addressCard}>
        <View style={styles.addressHeader}>
          <View style={styles.addressHeaderLeft}>
            <MaterialCommunityIcons 
              name={getAddressIcon(item.label)} 
              size={24} 
              color={getIconColor(item.label)}
              style={styles.addressTypeIcon}
            />
            <View>
              <Text style={styles.addressLabel}>{item.label}</Text>
              {isDefault && (
                <View style={styles.defaultBadge}>
                  <MaterialCommunityIcons name="check-circle" size={12} color="#fff" style={{ marginRight: 4 }} />
                  <Text style={styles.defaultBadgeText}>Default</Text>
                </View>
              )}
            </View>
          </View>
        </View>
        <Text style={styles.addressName}>
          <MaterialCommunityIcons name="account-circle" size={16} color="#2C3E50" /> {item.full_name}
        </Text>
        <Text style={styles.addressPhone}>
          <Ionicons name="call" size={14} color="#7F8C8D" /> {item.phone_number}
        </Text>
        <Text style={styles.addressText}>
          <MaterialCommunityIcons name="map-marker" size={16} color="#3498DB" /> {formattedAddress}
        </Text>
        <View style={styles.addressActions}>
          <TouchableOpacity 
            style={styles.editBtn}
            onPress={() => openEditModal(item)}
            activeOpacity={0.7}
          >
            <MaterialCommunityIcons name="pencil-outline" size={18} color="#fff" style={{ marginRight: 6 }} />
            <Text style={styles.actionText}>Edit</Text>
          </TouchableOpacity>
          <TouchableOpacity 
            style={styles.deleteBtn}
            onPress={() => handleDeleteAddress(item.id)}
            activeOpacity={0.7}
          >
            <MaterialCommunityIcons name="trash-can-outline" size={18} color="#E74C3C" style={{ marginRight: 6 }} />
            <Text style={styles.deleteText}>Delete</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.container}>
        <ScreenHeader 
          title="Saved Addresses" 
          navigation={navigation}
          showBack={false}
          showHamburger={true}
        />

        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading addresses...</Text>
        </View>
      </View>
    );
  }

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Saved Addresses" 
        navigation={navigation}
        showBack={false}
        showHamburger={true}
      />

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
            <MaterialCommunityIcons name="map-marker-off-outline" size={80} color="#DDD" />
            <Text style={styles.emptyText}>No addresses saved yet</Text>
            <Text style={styles.emptySubText}>Add your first address to get started</Text>
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
        activeOpacity={0.8}
      >
        <MaterialCommunityIcons name="plus-circle" size={22} color="#fff" style={{ marginRight: 8 }} />
        <Text style={styles.addButtonText}>Add Address</Text>
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
                  <MaterialCommunityIcons name="close" size={26} color="#BDC3C7" />
                </TouchableOpacity>
              </View>

              <Text style={styles.sectionLabel}>Address Type</Text>
              <View style={styles.labelOptions}>
                {[
                  { label: 'Home', icon: 'home-outline', color: '#E74C3C' },
                  { label: 'Office', icon: 'briefcase-outline', color: '#3498DB' },
                  { label: 'Other', icon: 'map-marker-multiple', color: '#9B59B6' }
                ].map((option) => (
                  <TouchableOpacity
                    key={option.label}
                    style={[
                      styles.labelOption,
                      formData.label === option.label && styles.labelOptionActive
                    ]}
                    onPress={() => setFormData({ ...formData, label: option.label })}
                    activeOpacity={0.7}
                  >
                    <MaterialCommunityIcons 
                      name={option.icon} 
                      size={20} 
                      color={formData.label === option.label ? '#fff' : option.color}
                      style={{ marginBottom: 4 }}
                    />
                    <Text style={[
                      styles.labelOptionText,
                      formData.label === option.label && styles.labelOptionTextActive
                    ]}>
                      {option.label}
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
                  {formData.isDefault && <MaterialCommunityIcons name="check" size={14} color="#fff" />}
                </View>
                <Text style={styles.checkboxLabel}>Set as default address</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.saveButton}
                onPress={handleAddAddress}
                activeOpacity={0.8}
              >
                <MaterialCommunityIcons name={editingId ? 'check-circle' : 'plus-circle'} size={20} color="#fff" style={{ marginRight: 6 }} />
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

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
  },
  content: {
    flex: 1,
    padding: 15,
  },
  emptyContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 80,
    paddingHorizontal: 20,
  },
  emptyText: {
    color: theme.text,
    fontSize: 18,
    fontWeight: '700',
    marginTop: 20,
    textAlign: 'center',
  },
  emptySubText: {
    color: theme.textSecondary,
    fontSize: 14,
    marginTop: 10,
    textAlign: 'center',
    fontWeight: '500',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 15,
    color: theme.text,
    fontSize: 16,
    fontWeight: '600',
  },
  addressCard: {
    backgroundColor: theme.cardBackground,
    borderRadius: 16,
    padding: 16,
    marginBottom: 15,
    borderLeftWidth: 5,
    borderLeftColor: theme.primary,
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
  },
  addressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  addressHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    flex: 1,
  },
  addressTypeIcon: {
    marginTop: 2,
  },
  addressLabel: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 4,
  },
  addressName: {
    fontSize: 15,
    fontWeight: '600',
    color: theme.text,
    marginBottom: 6,
  },
  addressPhone: {
    fontSize: 14,
    color: theme.textSecondary,
    marginBottom: 8,
    fontWeight: '500',
  },
  defaultBadge: {
    backgroundColor: theme.primary,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    alignSelf: 'flex-start',
    flexDirection: 'row',
    alignItems: 'center',
  },
  defaultBadgeText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '700',
  },
  addressText: {
    fontSize: 14,
    color: theme.textSecondary,
    marginBottom: 14,
    lineHeight: 20,
    fontWeight: '500',
  },
  addressActions: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 12,
  },
  editBtn: {
    flex: 1,
    backgroundColor: theme.primary,
    padding: 12,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    elevation: 3,
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
  },
  deleteBtn: {
    flex: 1,
    backgroundColor: theme.dangerBg,
    padding: 12,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    borderWidth: 1,
    borderColor: theme.dangerBorder,
  },
  actionText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 14,
  },
  deleteText: {
    color: theme.dangerText,
    fontWeight: '700',
    fontSize: 14,
  },
  addButton: {
    backgroundColor: theme.primary,
    padding: 16,
    margin: 15,
    marginBottom: 25,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    elevation: 5,
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 6,
  },
  addButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.3,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(44, 62, 80, 0.5)',
  },
  modalScrollView: {
    flex: 1,
  },
  modalContent: {
    backgroundColor: theme.cardBackground,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 24,
    paddingBottom: 40,
    marginTop: 80,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 24,
  },
  modalTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: theme.text,
  },
  closeButton: {
    fontSize: 28,
    color: theme.iconMuted,
  },
  sectionLabel: {
    fontSize: 14,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 12,
    marginTop: 8,
    letterSpacing: 0.3,
  },
  labelOptions: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 22,
  },
  labelOption: {
    flex: 1,
    paddingVertical: 12,
    paddingHorizontal: 12,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: theme.inputBorder,
    alignItems: 'center',
    backgroundColor: theme.inputBg,
  },
  labelOptionActive: {
    backgroundColor: theme.primary,
    borderColor: theme.primary,
  },
  labelOptionText: {
    fontSize: 13,
    color: theme.text,
    fontWeight: '600',
  },
  labelOptionTextActive: {
    color: '#fff',
    fontWeight: '700',
  },
  row: {
    flexDirection: 'row',
    gap: 10,
  },
  halfInput: {
    flex: 1,
  },
  input: {
    borderWidth: 1.5,
    borderColor: theme.inputBorder,
    borderRadius: 12,
    padding: 15,
    marginBottom: 18,
    fontSize: 15,
    color: theme.text,
    backgroundColor: theme.inputBg,
    fontWeight: '500',
  },
  textArea: {
    textAlignVertical: 'top',
  },
  checkboxContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 24,
    paddingVertical: 8,
  },
  checkbox: {
    width: 22,
    height: 22,
    borderRadius: 6,
    borderWidth: 2,
    borderColor: theme.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
    backgroundColor: theme.cardBackground,
  },
  checkboxChecked: {
    backgroundColor: theme.primary,
  },
  checkmark: {
    color: '#fff',
    fontSize: 14,
    fontWeight: 'bold',
  },
  checkboxLabel: {
    fontSize: 15,
    color: theme.text,
    fontWeight: '600',
  },
  saveButton: {
    backgroundColor: theme.primary,
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    marginTop: 8,
    elevation: 4,
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
  },
  saveButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.3,
  },
});
