import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Image,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as ImagePicker from 'expo-image-picker';
import colors from '../constants/colors';
import { useOrders } from '../../useOrders';
import { trackingStages } from '../constants/tracking';

const TrackOrderScreen = ({ navigation }) => {
  const {
    orders,
    loading,
    refreshing,
    loadOrders,
    onRefresh,
    savePaymentProof,
  } = useOrders();
  
  useEffect(() => {
    // Add a listener to refresh orders when the screen is focused
    const unsubscribe = navigation.addListener('focus', () => {
      loadOrders();
    });

    return unsubscribe;
  }, [navigation]);

  const getStatusInfo = (status) => {
    const stage = trackingStages.find(s => s.key === status);
    if (stage) {
      return {
        text: stage.label,
        color: stage.color,
      };
    }
    return {
      text: 'Unknown',
      color: '#757575',
    };
  };


  useEffect(() => {
    loadOrders();
  }, []);

  const handleUploadPaymentProof = async (order) => {
    Alert.alert(
      'Upload Payment Proof',
      'Choose how to upload your payment proof',
      [
        {
          text: 'Take Photo',
          onPress: () => takePhoto(order),
        },
        {
          text: 'Choose from Gallery',
          onPress: () => pickImage(order),
        },
        {
          text: 'Cancel',
          style: 'cancel',
        },
      ]
    );
  };

  const takePhoto = async (order) => {
    const { status } = await ImagePicker.requestCameraPermissionsAsync();
    
    if (status !== 'granted') {
      Alert.alert('Permission needed', 'Camera permission is required');
      return;
    }

    const result = await ImagePicker.launchCameraAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.8,
    });

    if (!result.canceled) {
      savePaymentProof(order, result.assets[0].uri);
    }
  };

  const pickImage = async (order) => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    
    if (status !== 'granted') {
      Alert.alert('Permission needed', 'Gallery permission is required');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.8,
    });

    if (!result.canceled) {
      savePaymentProof(order, result.assets[0].uri);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const renderOrderItem = (order, index) => {
    const { color: statusColor, text: statusText } = getStatusInfo(order.status);

    return (
      <TouchableOpacity
        key={order.orderRef || index}
        style={styles.orderCard}
        onPress={() => navigation.navigate('OrderDetails', { orderData: order })}
        activeOpacity={0.7}
      >
        <View style={styles.orderHeader}>
          <View style={styles.orderRefContainer}>
            <Text style={styles.orderRefLabel}>Order</Text>
            <Text style={styles.orderRef}>{order.orderRef}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: statusColor }]}>
            <Text style={styles.statusText}>{statusText}</Text>
          </View>
        </View>

        <Text style={styles.orderDate}>{formatDate(order.date)}</Text>

        <View style={styles.divider} />

        <View style={styles.itemsContainer}>
          {order.isCustom ? (
            <View>
              <Text style={styles.itemText}>{order.items[0].name}</Text>
              <Text style={styles.customDetailText}>{order.items[0].details}</Text>
            </View>
          ) : (
            <>
              {order.items.slice(0, 2).map((item, idx) => (
                <Text key={idx} style={styles.itemText}>
                  {item.name} x {item.quantity}
                </Text>
              ))}
              {order.items.length > 2 && (
                <Text style={styles.moreItems}>
                  +{order.items.length - 2} more item{order.items.length - 2 > 1 ? 's' : ''}
                </Text>
              )}
            </>
          )}
        </View>

        <View style={styles.orderFooter}>
          <Text style={styles.totalLabel}>Total Amount</Text>
          <Text style={styles.totalAmount}>₱{order.total.toFixed(2)}</Text>
        </View>

        {order.status === 'pending_payment' && !order.isCustom && (
          <TouchableOpacity 
            style={styles.paymentReminder}
            onPress={() => handleUploadPaymentProof(order)}
            activeOpacity={0.7}
          >
            <View style={styles.reminderContent}>
              <Text style={styles.reminderText}>
                Please send your payment proof to complete this order
              </Text>
              <Text style={styles.uploadButtonText}>📸 Upload</Text>
            </View>
          </TouchableOpacity>
        )}

        {order.paymentProof && (
          <View style={styles.proofUploaded}>
            <Text style={styles.proofUploadedText}>✓ Payment proof uploaded</Text>
            <Image source={{ uri: order.paymentProof }} style={styles.proofThumbnail} />
          </View>
        )}

        <View style={styles.viewDetailsContainer}>
          <Text style={styles.viewDetailsText}>View Details →</Text>
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#8B1A1A" />
        <Text style={styles.loadingText}>Loading orders...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Text style={styles.backButton}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Track Orders</Text>
        <View style={{ width: 40 }} />
      </View>

      {orders.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Text style={styles.emptyIcon}>📦</Text>
          <Text style={styles.emptyTitle}>No Orders Yet</Text>
          <Text style={styles.emptyText}>
            You haven't placed any orders yet. Start shopping to see your orders here.
          </Text>
          <TouchableOpacity
            style={styles.shopButton}
            onPress={() => navigation.navigate('Home')}
          >
            <Text style={styles.shopButtonText}>Start Shopping</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={onRefresh}
              colors={['#8B1A1A']}
              tintColor="#8B1A1A"
            />
          }
        >
          <Text style={styles.orderCount}>
            {orders.length} Order{orders.length > 1 ? 's' : ''}
          </Text>
          {orders.map((order, index) => renderOrderItem(order, index))}
          <View style={{ height: 20 }} />
        </ScrollView>
      )}
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
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 40,
  },
  emptyIcon: {
    fontSize: 80,
    marginBottom: 20,
  },
  emptyTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  emptyText: {
    fontSize: 15,
    color: '#666',
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 30,
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
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 15,
  },
  orderCount: {
    fontSize: 15,
    color: '#666',
    marginBottom: 10,
    marginLeft: 5,
  },
  orderCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 15,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  orderRefContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  orderRefLabel: {
    fontSize: 13,
    color: '#666',
    marginRight: 6,
  },
  orderRef: {
    fontSize: 15,
    fontWeight: 'bold',
    color: '#333',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
  },
  orderDate: {
    fontSize: 13,
    color: '#999',
    marginBottom: 12,
  },
  divider: {
    height: 1,
    backgroundColor: '#f0f0f0',
    marginVertical: 12,
  },
  itemsContainer: {
    marginBottom: 12,
  },
  itemText: {
    fontSize: 14,
    color: '#333',
    marginBottom: 4,
  },
  moreItems: {
    fontSize: 13,
    color: '#8B1A1A',
    fontStyle: 'italic',
    marginTop: 4,
  },
  customDetailText: {
    fontSize: 13,
    color: '#666',
    fontStyle: 'italic',
    marginTop: 4,
  },
  orderFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#f0f0f0',
  },
  totalLabel: {
    fontSize: 14,
    color: '#666',
  },
  totalAmount: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#8B1A1A',
  },
  paymentReminder: {
    backgroundColor: '#FFF3F3',
    borderLeftWidth: 3,
    borderLeftColor: '#FF9800',
    padding: 12,
    marginTop: 12,
    borderRadius: 4,
  },
  reminderContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  reminderText: {
    fontSize: 12,
    color: '#D84315',
    lineHeight: 18,
    flex: 1,
    marginRight: 10,
  },
  uploadButtonText: {
    fontSize: 12,
    color: '#8B1A1A',
    fontWeight: 'bold',
    backgroundColor: '#FFF',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#FF9800',
  },
  proofUploaded: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#E8F5E9',
    padding: 10,
    marginTop: 12,
    borderRadius: 6,
    borderLeftWidth: 3,
    borderLeftColor: '#4CAF50',
  },
  proofUploadedText: {
    fontSize: 12,
    color: '#2E7D32',
    fontWeight: '600',
  },
  proofThumbnail: {
    width: 40,
    height: 40,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#4CAF50',
  },
  viewDetailsContainer: {
    marginTop: 12,
    alignItems: 'flex-end',
  },
  viewDetailsText: {
    fontSize: 13,
    color: '#8B1A1A',
    fontWeight: '600',
  },
});

export default TrackOrderScreen;