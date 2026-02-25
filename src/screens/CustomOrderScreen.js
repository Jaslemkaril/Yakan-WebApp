import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Alert,
  ActivityIndicator,
  RefreshControl,
  FlatList,
} from 'react-native';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import BottomNav from '../components/BottomNav';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import { useCart } from '../context/CartContext';
import colors from '../constants/colors';
import ApiService from '../services/api';

const { width } = require('react-native').Dimensions.get('window');

const CUSTOM_ORDER_STATUSES = [
  { key: 'pending', label: 'Pending', icon: 'clipboard-outline', color: '#FF9800', description: 'Order submitted, awaiting review.' },
  { key: 'approved', label: 'Approved', icon: 'checkmark-circle-outline', color: '#4CAF50', description: 'Your order has been approved.' },
  { key: 'in_production', label: 'In Production', icon: 'construct-outline', color: '#2196F3', description: 'Artisans are weaving your piece.' },
  { key: 'quality_check', label: 'Quality Check', icon: 'eye-outline', color: '#9C27B0', description: 'Final quality inspection.' },
  { key: 'ready', label: 'Ready', icon: 'gift-outline', color: '#009688', description: 'Your order is ready for pickup/shipping.' },
  { key: 'delivered', label: 'Delivered', icon: 'checkmark-done-outline', color: '#4CAF50', description: 'Order delivered successfully.' },
];

const CustomOrderScreen = ({ navigation }) => {
  const { theme } = useTheme();
  const { isLoggedIn } = useCart();
  const [activeTab, setActiveTab] = useState('create'); // 'create' or 'orders'
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [customOrders, setCustomOrders] = useState([]);
  const [selectedOrder, setSelectedOrder] = useState(null);
  
  // Form state
  const [formData, setFormData] = useState({
    fabric_type: '',
    fabric_quantity_meters: '',
    intended_use: '',
    preferred_colors: '',
    dimensions: '',
    additional_notes: '',
    phone: '',
    email: '',
    delivery_address: '',
    delivery_type: 'deliver',
    budget_range: '',
    urgency: 'normal',
  });

  useEffect(() => {
    if (isLoggedIn && activeTab === 'orders') {
      fetchCustomOrders();
    }
  }, [isLoggedIn, activeTab]);

  // Refresh when screen gains focus
  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      if (isLoggedIn && activeTab === 'orders') {
        fetchCustomOrders();
      }
    });
    return unsubscribe;
  }, [navigation, isLoggedIn, activeTab]);

  const fetchCustomOrders = async () => {
    try {
      setLoading(true);
      const response = await ApiService.getCustomOrders();
      console.log('[CustomOrder] Fetch response:', response);
      if (response.success) {
        const data = response.data?.data || response.data || [];
        const orders = Array.isArray(data) ? data : [];
        setCustomOrders(orders);
      }
    } catch (error) {
      console.error('[CustomOrder] Fetch error:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const handleRefresh = () => {
    setRefreshing(true);
    fetchCustomOrders();
  };

  const handleSubmit = async () => {
    if (!isLoggedIn) {
      Alert.alert('Login Required', 'Please login to create a custom order', [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Login', onPress: () => navigation.navigate('Auth', { screen: 'Login' }) },
      ]);
      return;
    }

    // Validate required fields
    if (!formData.fabric_type.trim() || !formData.intended_use.trim()) {
      Alert.alert('Missing Information', 'Please fill in at least the fabric type and intended use.');
      return;
    }

    try {
      setLoading(true);
      const response = await ApiService.createCustomOrder({
        ...formData,
        fabric_quantity_meters: parseFloat(formData.fabric_quantity_meters) || 1,
      });

      if (response.success) {
        Alert.alert('Success!', 'Your custom order has been submitted. We will review it shortly.', [
          { text: 'View Orders', onPress: () => { setActiveTab('orders'); fetchCustomOrders(); } },
          { text: 'OK' },
        ]);
        // Reset form
        setFormData({
          fabric_type: '', fabric_quantity_meters: '', intended_use: '', preferred_colors: '',
          dimensions: '', additional_notes: '', phone: '', email: '',
          delivery_address: '', delivery_type: 'deliver', budget_range: '', urgency: 'normal',
        });
      } else {
        Alert.alert('Error', response.error || 'Failed to create custom order');
      }
    } catch (error) {
      console.error('[CustomOrder] Submit error:', error);
      Alert.alert('Error', 'Failed to submit custom order. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const getStatusIndex = (status) => {
    const map = {
      pending: 0, submitted: 0,
      approved: 1, price_quoted: 1,
      in_production: 2, production: 2, processing: 2,
      quality_check: 3,
      ready: 4, completed: 4, out_for_delivery: 4,
      delivered: 5,
    };
    return map[status] ?? 0;
  };

  const renderTimeline = (order) => {
    const currentIndex = getStatusIndex(order.status);
    return (
      <View style={dynStyles.timeline}>
        {CUSTOM_ORDER_STATUSES.map((stage, index) => {
          const isCompleted = index <= currentIndex;
          const isCurrent = index === currentIndex;
          return (
            <View key={stage.key} style={dynStyles.timelineItem}>
              {index < CUSTOM_ORDER_STATUSES.length - 1 && (
                <View style={[dynStyles.timelineLine, isCompleted && { backgroundColor: stage.color }]} />
              )}
              <View style={[
                dynStyles.timelineDot,
                { borderColor: isCompleted ? stage.color : '#ddd', backgroundColor: isCompleted ? stage.color : '#f5f5f5' },
                isCurrent && { shadowColor: stage.color, shadowOpacity: 0.4, shadowRadius: 6, elevation: 4 },
              ]}>
                {isCompleted ? (
                  <Ionicons name="checkmark" size={14} color="#fff" />
                ) : (
                  <Text style={{ fontSize: 10, color: '#999' }}>{index + 1}</Text>
                )}
              </View>
              <View style={dynStyles.timelineContent}>
                <Text style={[dynStyles.timelineLabel, isCompleted && { color: stage.color, fontWeight: '700' }]}>
                  {stage.label}
                </Text>
                {isCurrent && <Text style={dynStyles.timelineDesc}>{stage.description}</Text>}
              </View>
            </View>
          );
        })}
      </View>
    );
  };

  const renderOrderCard = ({ item }) => {
    const statusIndex = getStatusIndex(item.status);
    const statusInfo = CUSTOM_ORDER_STATUSES[statusIndex] || CUSTOM_ORDER_STATUSES[0];
    
    return (
      <TouchableOpacity
        style={dynStyles.orderCard}
        onPress={() => setSelectedOrder(selectedOrder?.id === item.id ? null : item)}
        activeOpacity={0.7}
      >
        <View style={dynStyles.orderCardHeader}>
          <View style={{ flex: 1 }}>
            <Text style={dynStyles.orderCardTitle}>
              Custom Order #{item.id}
            </Text>
            <Text style={dynStyles.orderCardSubtitle}>
              {item.fabric_type || item.specifications || 'Yakan Fabric'}
            </Text>
          </View>
          <View style={[dynStyles.statusBadge, { backgroundColor: statusInfo.color }]}>
            <Text style={dynStyles.statusBadgeText}>{statusInfo.label}</Text>
          </View>
        </View>

        {item.estimated_price && (
          <Text style={dynStyles.orderPrice}>Est. Price: ₱{parseFloat(item.estimated_price).toFixed(2)}</Text>
        )}
        {item.final_price && (
          <Text style={[dynStyles.orderPrice, { color: colors.primary, fontWeight: '700' }]}>
            Final Price: ₱{parseFloat(item.final_price).toFixed(2)}
          </Text>
        )}

        <Text style={dynStyles.orderDate}>
          Placed: {new Date(item.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
        </Text>

        {/* Expandable Timeline */}
        {selectedOrder?.id === item.id && (
          <View style={dynStyles.expandedSection}>
            <Text style={dynStyles.timelineTitle}>Order Timeline</Text>
            {renderTimeline(item)}
            
            {item.additional_notes && (
              <View style={dynStyles.notesSection}>
                <Text style={dynStyles.notesLabel}>Notes:</Text>
                <Text style={dynStyles.notesText}>{item.additional_notes}</Text>
              </View>
            )}
            {item.admin_notes && (
              <View style={dynStyles.notesSection}>
                <Text style={dynStyles.notesLabel}>Admin Notes:</Text>
                <Text style={dynStyles.notesText}>{item.admin_notes}</Text>
              </View>
            )}
          </View>
        )}

        <View style={dynStyles.expandHint}>
          <Ionicons name={selectedOrder?.id === item.id ? 'chevron-up' : 'chevron-down'} size={20} color="#999" />
        </View>
      </TouchableOpacity>
    );
  };

  const renderCreateForm = () => (
    <ScrollView style={dynStyles.scrollView} showsVerticalScrollIndicator={false}>
      <View style={dynStyles.contentContainer}>
        <View style={dynStyles.formIconContainer}>
          <Ionicons name="color-palette" size={60} color={colors.primary} />
        </View>

        <Text style={dynStyles.mainTitle}>Create Your Custom Yakan Piece</Text>
        <Text style={dynStyles.subtitle}>Fill in the details below and our artisans will craft your unique traditional weave.</Text>

        {/* Fabric Type */}
        <Text style={dynStyles.inputLabel}>Fabric Type *</Text>
        <TextInput
          style={dynStyles.input}
          placeholder="e.g., Seputangan, Kandit, Pis Siyabit"
          value={formData.fabric_type}
          onChangeText={(text) => setFormData({ ...formData, fabric_type: text })}
          placeholderTextColor="#999"
        />

        {/* Intended Use */}
        <Text style={dynStyles.inputLabel}>Intended Use *</Text>
        <TextInput
          style={dynStyles.input}
          placeholder="e.g., Wall decor, Clothing, Table runner"
          value={formData.intended_use}
          onChangeText={(text) => setFormData({ ...formData, intended_use: text })}
          placeholderTextColor="#999"
        />

        {/* Preferred Colors */}
        <Text style={dynStyles.inputLabel}>Preferred Colors</Text>
        <TextInput
          style={dynStyles.input}
          placeholder="e.g., Red and gold, Traditional colors"
          value={formData.preferred_colors}
          onChangeText={(text) => setFormData({ ...formData, preferred_colors: text })}
          placeholderTextColor="#999"
        />

        {/* Dimensions */}
        <Text style={dynStyles.inputLabel}>Dimensions</Text>
        <TextInput
          style={dynStyles.input}
          placeholder='e.g., 42" x 42"'
          value={formData.dimensions}
          onChangeText={(text) => setFormData({ ...formData, dimensions: text })}
          placeholderTextColor="#999"
        />

        {/* Quantity (meters) */}
        <Text style={dynStyles.inputLabel}>Fabric Quantity (meters)</Text>
        <TextInput
          style={dynStyles.input}
          placeholder="e.g., 2"
          value={formData.fabric_quantity_meters}
          onChangeText={(text) => setFormData({ ...formData, fabric_quantity_meters: text })}
          keyboardType="decimal-pad"
          placeholderTextColor="#999"
        />

        {/* Budget */}
        <Text style={dynStyles.inputLabel}>Budget Range</Text>
        <TextInput
          style={dynStyles.input}
          placeholder="e.g., ₱500-₱1500"
          value={formData.budget_range}
          onChangeText={(text) => setFormData({ ...formData, budget_range: text })}
          placeholderTextColor="#999"
        />

        {/* Urgency */}
        <Text style={dynStyles.inputLabel}>Urgency</Text>
        <View style={dynStyles.urgencyRow}>
          {['normal', 'rush', 'flexible'].map((level) => (
            <TouchableOpacity
              key={level}
              style={[dynStyles.urgencyBtn, formData.urgency === level && dynStyles.urgencyBtnActive]}
              onPress={() => setFormData({ ...formData, urgency: level })}
            >
              <Text style={[dynStyles.urgencyText, formData.urgency === level && dynStyles.urgencyTextActive]}>
                {level.charAt(0).toUpperCase() + level.slice(1)}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* Contact Info */}
        <Text style={[dynStyles.inputLabel, { marginTop: 10 }]}>Phone Number</Text>
        <TextInput
          style={dynStyles.input}
          placeholder="e.g., 0917-xxx-xxxx"
          value={formData.phone}
          onChangeText={(text) => setFormData({ ...formData, phone: text })}
          keyboardType="phone-pad"
          placeholderTextColor="#999"
        />

        <Text style={dynStyles.inputLabel}>Delivery Address</Text>
        <TextInput
          style={[dynStyles.input, { height: 80, textAlignVertical: 'top' }]}
          placeholder="Full delivery address"
          value={formData.delivery_address}
          onChangeText={(text) => setFormData({ ...formData, delivery_address: text })}
          multiline
          placeholderTextColor="#999"
        />

        {/* Additional Notes */}
        <Text style={dynStyles.inputLabel}>Additional Notes</Text>
        <TextInput
          style={[dynStyles.input, { height: 100, textAlignVertical: 'top' }]}
          placeholder="Any special instructions or design details..."
          value={formData.additional_notes}
          onChangeText={(text) => setFormData({ ...formData, additional_notes: text })}
          multiline
          placeholderTextColor="#999"
        />

        {/* Submit Button */}
        <TouchableOpacity
          style={[dynStyles.submitButton, loading && { opacity: 0.6 }]}
          onPress={handleSubmit}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <>
              <Ionicons name="send" size={20} color="#fff" style={{ marginRight: 8 }} />
              <Text style={dynStyles.submitButtonText}>Submit Custom Order</Text>
            </>
          )}
        </TouchableOpacity>

        <View style={{ height: 100 }} />
      </View>
    </ScrollView>
  );

  const renderOrdersList = () => (
    <View style={{ flex: 1 }}>
      {loading && customOrders.length === 0 ? (
        <View style={dynStyles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={dynStyles.loadingText}>Loading orders...</Text>
        </View>
      ) : customOrders.length === 0 ? (
        <View style={dynStyles.emptyContainer}>
          <MaterialCommunityIcons name="package-variant" size={80} color="#ddd" />
          <Text style={dynStyles.emptyTitle}>No Custom Orders</Text>
          <Text style={dynStyles.emptyText}>Create your first custom order to see it here.</Text>
          <TouchableOpacity style={dynStyles.createBtn} onPress={() => setActiveTab('create')}>
            <Text style={dynStyles.createBtnText}>Create Order</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <FlatList
          data={customOrders}
          renderItem={renderOrderCard}
          keyExtractor={(item) => (item.id || Math.random()).toString()}
          contentContainerStyle={{ padding: 16, paddingBottom: 100 }}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} colors={[colors.primary]} />
          }
        />
      )}
    </View>
  );

  return (
    <View style={[dynStyles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader
        title="Custom Order"
        navigation={navigation}
        showBack={false}
      />

      {/* Tab Selector */}
      <View style={dynStyles.tabContainer}>
        <TouchableOpacity
          style={[dynStyles.tab, activeTab === 'create' && dynStyles.tabActive]}
          onPress={() => setActiveTab('create')}
        >
          <Ionicons name="create-outline" size={18} color={activeTab === 'create' ? '#fff' : colors.primary} />
          <Text style={[dynStyles.tabText, activeTab === 'create' && dynStyles.tabTextActive]}>Create Order</Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[dynStyles.tab, activeTab === 'orders' && dynStyles.tabActive]}
          onPress={() => setActiveTab('orders')}
        >
          <Ionicons name="list-outline" size={18} color={activeTab === 'orders' ? '#fff' : colors.primary} />
          <Text style={[dynStyles.tabText, activeTab === 'orders' && dynStyles.tabTextActive]}>My Orders</Text>
        </TouchableOpacity>
      </View>

      {activeTab === 'create' ? renderCreateForm() : renderOrdersList()}

      <BottomNav navigation={navigation} activeRoute="CustomOrder" />
    </View>
  );
};

const dynStyles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background },
  scrollView: { flex: 1 },
  tabContainer: {
    flexDirection: 'row', paddingHorizontal: 16, paddingVertical: 10, gap: 10,
    backgroundColor: colors.white, borderBottomWidth: 1, borderBottomColor: '#eee',
  },
  tab: {
    flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    paddingVertical: 10, borderRadius: 10, borderWidth: 1.5, borderColor: colors.primary, gap: 6,
  },
  tabActive: { backgroundColor: colors.primary },
  tabText: { fontSize: 14, fontWeight: '600', color: colors.primary },
  tabTextActive: { color: '#fff' },
  contentContainer: { padding: 20, paddingBottom: 60 },
  formIconContainer: { alignItems: 'center', marginVertical: 16 },
  mainTitle: { fontSize: 22, fontWeight: 'bold', color: colors.text, textAlign: 'center', marginBottom: 8 },
  subtitle: { fontSize: 14, color: colors.textLight, textAlign: 'center', marginBottom: 24, lineHeight: 20 },
  inputLabel: { fontSize: 14, fontWeight: '700', color: colors.text, marginBottom: 6, marginTop: 4 },
  input: {
    borderWidth: 1.5, borderColor: '#ddd', borderRadius: 10, padding: 14, marginBottom: 14,
    fontSize: 15, color: colors.text, backgroundColor: '#fafafa',
  },
  urgencyRow: { flexDirection: 'row', gap: 10, marginBottom: 14 },
  urgencyBtn: {
    flex: 1, paddingVertical: 10, borderRadius: 8, borderWidth: 1.5, borderColor: '#ddd',
    alignItems: 'center', backgroundColor: '#fafafa',
  },
  urgencyBtnActive: { backgroundColor: colors.primary, borderColor: colors.primary },
  urgencyText: { fontSize: 13, fontWeight: '600', color: colors.text },
  urgencyTextActive: { color: '#fff' },
  submitButton: {
    backgroundColor: colors.primary, flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    paddingVertical: 16, borderRadius: 12, marginTop: 20,
    shadowColor: colors.primary, shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8, elevation: 5,
  },
  submitButtonText: { color: '#fff', fontSize: 17, fontWeight: 'bold' },
  loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 10, fontSize: 16, color: colors.textLight },
  emptyContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 30 },
  emptyTitle: { fontSize: 20, fontWeight: '700', color: colors.text, marginTop: 16 },
  emptyText: { fontSize: 14, color: colors.textLight, textAlign: 'center', marginTop: 8 },
  createBtn: { backgroundColor: colors.primary, paddingVertical: 12, paddingHorizontal: 30, borderRadius: 10, marginTop: 20 },
  createBtnText: { color: '#fff', fontWeight: '700', fontSize: 15 },
  orderCard: {
    backgroundColor: colors.white, borderRadius: 14, padding: 16, marginBottom: 14,
    shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.08, shadowRadius: 6, elevation: 3,
  },
  orderCardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 },
  orderCardTitle: { fontSize: 16, fontWeight: '700', color: colors.text },
  orderCardSubtitle: { fontSize: 13, color: colors.textLight, marginTop: 2 },
  statusBadge: { paddingHorizontal: 10, paddingVertical: 5, borderRadius: 12 },
  statusBadgeText: { color: '#fff', fontSize: 12, fontWeight: '700' },
  orderPrice: { fontSize: 14, color: colors.textLight, marginBottom: 4 },
  orderDate: { fontSize: 12, color: '#999', marginBottom: 4 },
  expandHint: { alignItems: 'center', marginTop: 4 },
  expandedSection: { marginTop: 16, paddingTop: 16, borderTopWidth: 1, borderTopColor: '#eee' },
  timelineTitle: { fontSize: 16, fontWeight: '700', color: colors.text, marginBottom: 12 },
  timeline: { paddingLeft: 10 },
  timelineItem: { flexDirection: 'row', alignItems: 'flex-start', marginBottom: 18, position: 'relative' },
  timelineLine: {
    position: 'absolute', left: 11, top: 26, width: 2, height: 30,
    backgroundColor: '#ddd',
  },
  timelineDot: {
    width: 24, height: 24, borderRadius: 12, borderWidth: 2,
    justifyContent: 'center', alignItems: 'center', marginRight: 12,
  },
  timelineContent: { flex: 1, paddingTop: 2 },
  timelineLabel: { fontSize: 14, fontWeight: '600', color: '#999' },
  timelineDesc: { fontSize: 12, color: colors.textLight, marginTop: 2 },
  notesSection: { marginTop: 12, backgroundColor: '#f9f9f9', padding: 12, borderRadius: 8 },
  notesLabel: { fontSize: 13, fontWeight: '700', color: colors.text, marginBottom: 4 },
  notesText: { fontSize: 13, color: colors.textLight, lineHeight: 18 },
});

export default CustomOrderScreen;