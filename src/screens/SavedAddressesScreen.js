// src/screens/SavedAddressesScreen.js
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

export default function SavedAddressesScreen({ navigation }) {
  const [addresses, setAddresses] = useState([
    {
      id: '1',
      label: 'Home',
      address: '123 Main Street, City, Country',
      isDefault: true,
    },
  ]);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    label: '',
    address: '',
    isDefault: false,
  });

  const handleAddAddress = () => {
    if (!formData.label.trim() || !formData.address.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    if (editingId) {
      setAddresses(addresses.map(addr => 
        addr.id === editingId 
          ? { ...addr, ...formData }
          : { ...addr, isDefault: false }
      ));
    } else {
      const newAddress = {
        id: Date.now().toString(),
        ...formData,
      };
      setAddresses([...addresses, newAddress]);
    }

    setIsModalVisible(false);
    setFormData({ label: '', address: '', isDefault: false });
    setEditingId(null);
  };

  const handleDeleteAddress = (id) => {
    if (addresses.length === 1) {
      Alert.alert('Error', 'You must have at least one address');
      return;
    }
    setAddresses(addresses.filter(addr => addr.id !== id));
  };

  const openEditModal = (address) => {
    setEditingId(address.id);
    setFormData(address);
    setIsModalVisible(true);
  };

  const renderAddressItem = ({ item }) => (
    <View style={styles.addressCard}>
      <View style={styles.addressHeader}>
        <View>
          <Text style={styles.addressLabel}>{item.label}</Text>
          {item.isDefault && <Text style={styles.defaultBadge}>Default</Text>}
        </View>
      </View>
      <Text style={styles.addressText}>{item.address}</Text>
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
            keyExtractor={item => item.id}
            scrollEnabled={false}
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
          setFormData({ label: '', address: '', isDefault: false });
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
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>
                {editingId ? 'Edit Address' : 'Add New Address'}
              </Text>
              <TouchableOpacity onPress={() => setIsModalVisible(false)}>
                <Text style={styles.closeButton}>✕</Text>
              </TouchableOpacity>
            </View>

            <TextInput
              style={styles.input}
              placeholder="Address Label (e.g., Home, Office)"
              value={formData.label}
              onChangeText={(text) => setFormData({ ...formData, label: text })}
            />

            <TextInput
              style={[styles.input, styles.textArea]}
              placeholder="Full Address"
              value={formData.address}
              onChangeText={(text) => setFormData({ ...formData, address: text })}
              multiline
              numberOfLines={4}
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
  addressLabel: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.dark,
  },
  defaultBadge: {
    backgroundColor: colors.primary,
    color: colors.white,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
    fontSize: 12,
    marginTop: 5,
    overflow: 'hidden',
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
