import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  SafeAreaView,
  StatusBar,
  ScrollView,
  Image,
} from 'react-native';
import { MaterialCommunityIcons, Ionicons } from '@expo/vector-icons';
import { API_CONFIG } from '../config/config';
import { useCart } from '../context/CartContext';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import ApiService from '../services/api';
import colors from '../constants/colors';
import BottomNav from '../components/BottomNav';

const OrdersScreen = ({ navigation }) => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [activeFilter, setActiveFilter] = useState('all');
  const { isLoggedIn } = useCart();
  const { theme } = useTheme();
  const styles = getStyles(theme);

  useEffect(() => {
    if (isLoggedIn) {
      fetchOrders();
      const interval = setInterval(fetchOrders, 10000); // Poll every 10 seconds
      return () => clearInterval(interval);
    }
  }, [isLoggedIn]);

  const fetchOrders = async () => {
    try {
      console.log('[OrdersScreen] Fetching orders...');
      const response = await ApiService.getOrders();
      console.log('[OrdersScreen] Response:', response);
      
      if (response && response.success) {
        const ordersData = response.data?.data || response.data || [];
        const ordersList = Array.isArray(ordersData) ? ordersData : [];
        console.log(`[OrdersScreen] Fetched ${ordersList.length} orders`);
        setOrders(ordersList);
      } else {
        console.error('[OrdersScreen] Failed to fetch orders:', response?.error);
        // Don't clear orders on error - keep existing data
      }
    } catch (error) {
      console.error('[OrdersScreen] Error fetching orders:', error);
      // Don't clear orders on error - keep existing data
    } finally {
      setLoading(false);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    try {
      await fetchOrders();
    } finally {
      setRefreshing(false);
    }
  };

  const getStatusIcon = (status) => {
    const icons = {
      pending:              'clock-outline',
      pending_payment:      'credit-card-clock',
      payment_verified:     'check-circle-outline',
      pending_confirmation: 'checkbox-marked-circle-outline',
      confirmed:            'check-circle',
      processing:           'cog',
      shipped:              'truck-fast',
      out_for_delivery:     'moped-outline',
      delivered:            'home-check',
      completed:            'check-all',
      cancelled:            'close-circle',
    };
    return icons[status] || 'package-variant-closed';
  };

  const getStatusColor = (status) => {
    const statusColors = {
      pending:              '#F59E0B',
      pending_payment:      '#EF4444',
      payment_verified:     '#3B82F6',
      pending_confirmation: '#3B82F6',
      confirmed:            '#6366F1',
      processing:           '#8B5CF6',
      shipped:              '#0EA5E9',
      out_for_delivery:     '#FF5722',
      delivered:            '#22C55E',
      completed:            '#16A34A',
      cancelled:            '#EF4444',
    };
    return statusColors[status] || '#8B1A1A';
  };

  const MINI_STAGES = ['pending','confirmed','processing','shipped','delivered'];
  const getMiniProgress = (status) => {
    if (['cancelled','refunded'].includes(status)) return -1;
    const idx = MINI_STAGES.indexOf(status);
    return idx === -1 ? 0 : idx;
  };

  const FILTERS = [
    { key: 'all',       label: 'All',       icon: 'view-list' },
    { key: 'active',    label: 'Active',    icon: 'clock-outline' },
    { key: 'transit',   label: 'In Transit',icon: 'truck-fast' },
    { key: 'delivered', label: 'Delivered', icon: 'home-check' },
    { key: 'cancelled', label: 'Cancelled', icon: 'close-circle' },
  ];

  const filterOrders = (list) => {
    switch (activeFilter) {
      case 'active':    return list.filter(o => ['pending','pending_payment','confirmed','processing'].includes(o.status));
      case 'transit':   return list.filter(o => ['shipped','out_for_delivery'].includes(o.status));
      case 'delivered': return list.filter(o => ['delivered','completed'].includes(o.status));
      case 'cancelled': return list.filter(o => o.status === 'cancelled');
      default:          return list;
    }
  };

  const getStatusLabel = (status) => {
    const labels = {
      pending:              'Pending',
      pending_payment:      'Awaiting Payment',
      payment_verified:     'Payment Verified',
      pending_confirmation: 'Pending Confirm',
      confirmed:            'Confirmed',
      processing:           'Processing',
      shipped:              'Shipped',
      out_for_delivery:     'Out for Delivery',
      delivered:            'Delivered',
      completed:            'Completed',
      cancelled:            'Cancelled',
    };
    return labels[status] || status;
  };

  if (!isLoggedIn) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
        <ScreenHeader title="My Orders" navigation={navigation} showBack={false} showHamburger={true} />
        <View style={styles.centerContent}>
          <View style={styles.emptyIconWrap}>
            <MaterialCommunityIcons name="package-variant-closed" size={56} color="#8B1A1A" />
          </View>
          <Text style={styles.title}>Login Required</Text>
          <Text style={styles.subtitle}>Please login to view your orders</Text>
          <TouchableOpacity style={styles.loginButton} onPress={() => navigation.navigate('Login')}>
            <MaterialCommunityIcons name="login" size={18} color="#fff" style={{ marginRight: 8 }} />
            <Text style={styles.loginButtonText}>Go to Login</Text>
          </TouchableOpacity>
        </View>
        <BottomNav navigation={navigation} activeRoute="Orders" />
      </SafeAreaView>
    );
  }

  if (loading) {
    return (
      <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
        <ScreenHeader title="My Orders" navigation={navigation} showBack={false} showHamburger={true} />
        <View style={styles.centerContent}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading orders...</Text>
        </View>
        <BottomNav navigation={navigation} activeRoute="Orders" />
      </SafeAreaView>
    );
  }

  const filteredOrders = filterOrders(orders);

  const filterCount = (key) => {
    switch (key) {
      case 'active':    return orders.filter(o => ['pending','pending_payment','confirmed','processing'].includes(o.status)).length;
      case 'transit':   return orders.filter(o => ['shipped','out_for_delivery'].includes(o.status)).length;
      case 'delivered': return orders.filter(o => ['delivered','completed'].includes(o.status)).length;
      case 'cancelled': return orders.filter(o => o.status === 'cancelled').length;
      default: return orders.length;
    }
  };

  const stats = [
    { label: 'Total',      count: orders.length,                                                                                      color: '#8B1A1A',  icon: 'package-variant-closed' },
    { label: 'Active',     count: orders.filter(o => ['pending','pending_payment','confirmed','processing'].includes(o.status)).length, color: '#F59E0B',  icon: 'clock-outline' },
    { label: 'In Transit', count: orders.filter(o => ['shipped','out_for_delivery'].includes(o.status)).length,                       color: '#0EA5E9',  icon: 'truck-fast' },
    { label: 'Delivered',  count: orders.filter(o => ['delivered','completed'].includes(o.status)).length,                            color: '#22C55E',  icon: 'home-check' },
  ];

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader title="My Orders" navigation={navigation} showBack={false} showHamburger={true} />

      {/* Stats row */}
      <View style={styles.statsRow}>
        {stats.map((s, i) => (
          <View key={i} style={[styles.statCard, { borderTopColor: s.color }]}>
            <MaterialCommunityIcons name={s.icon} size={18} color={s.color} />
            <Text style={[styles.statCount, { color: s.color }]}>{s.count}</Text>
            <Text style={styles.statLabel}>{s.label}</Text>
          </View>
        ))}
      </View>

      {/* Filter tabs */}
      <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.filterScroll} contentContainerStyle={styles.filterContent}>
        {FILTERS.map(f => (
          <TouchableOpacity
            key={f.key}
            style={[styles.filterChip, activeFilter === f.key && styles.filterChipActive]}
            onPress={() => setActiveFilter(f.key)}
            activeOpacity={0.7}
          >
            <MaterialCommunityIcons
              name={f.icon}
              size={13}
              color={activeFilter === f.key ? '#fff' : theme.textSecondary}
              style={{ marginRight: 4 }}
            />
            <Text style={[styles.filterChipText, activeFilter === f.key && styles.filterChipTextActive]}>{f.label}</Text>
            {f.key !== 'all' && (
              <View style={[styles.filterBadge, activeFilter === f.key && styles.filterBadgeActive]}>
                <Text style={[styles.filterBadgeText, activeFilter === f.key && { color: '#8B1A1A' }]}>
                  {filterCount(f.key)}
                </Text>
              </View>
            )}
          </TouchableOpacity>
        ))}
      </ScrollView>

      <View style={styles.content}>
        {filteredOrders.length === 0 ? (
          <View style={styles.emptyContainer}>
            <View style={styles.emptyIconWrap}>
              <MaterialCommunityIcons name="package-variant-off" size={56} color="#DDD" />
            </View>
            <Text style={styles.emptyText}>{activeFilter === 'all' ? 'No orders yet' : `No ${FILTERS.find(f=>f.key===activeFilter)?.label} orders`}</Text>
            <Text style={styles.emptySubtext}>
              {activeFilter === 'all' ? 'Start shopping to place your first order' : 'Try a different filter'}
            </Text>
            {activeFilter === 'all' && (
              <TouchableOpacity style={styles.emptyButton} onPress={() => navigation.navigate('Home')} activeOpacity={0.8}>
                <MaterialCommunityIcons name="shopping" size={20} color="#fff" style={{ marginRight: 8 }} />
                <Text style={styles.emptyButtonText}>Continue Shopping</Text>
              </TouchableOpacity>
            )}
          </View>
        ) : (
          <FlatList
            style={{flex: 1}}
            data={filteredOrders}
            keyExtractor={(item) => item.id.toString()}
            renderItem={({ item }) => {
              const sc = getStatusColor(item.status);
              const items = item.orderItems || item.items || [];
              const baseUrl = API_CONFIG.API_BASE_URL.replace('/api/v1', '');
              const firstImage = items[0]?.product?.image;
              const imageUrl = firstImage
                ? firstImage.startsWith('http')
                  ? firstImage
                  : firstImage.startsWith('/uploads') || firstImage.startsWith('/storage')
                    ? `${baseUrl}${firstImage}`
                    : `${baseUrl}/storage/products/${firstImage}`
                : null;
              const orderRef = item.order_number || item.order_ref || `ORD-${item.id}`;
              const isPaid = item.payment_status === 'paid' || item.payment_status === 'verified';

              return (
                <TouchableOpacity
                  style={styles.orderCard}
                  onPress={() => navigation.navigate('OrderDetails', { orderData: item })}
                  activeOpacity={0.7}
                >
                  {/* Header: order ref + badges */}
                  <View style={styles.cardHeader}>
                    <View style={styles.cardHeaderLeft}>
                      <Text style={styles.orderRef}>{orderRef}</Text>
                      <Text style={styles.orderDate}>
                        {new Date(item.created_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </Text>
                    </View>
                    <View style={styles.badgesRow}>
                      <View style={[styles.statusPill, { borderColor: sc, backgroundColor: sc + '15' }]}>
                        <Text style={[styles.statusPillText, { color: sc }]}>{getStatusLabel(item.status)}</Text>
                      </View>
                    </View>
                  </View>

                  {/* Image + items */}
                  <View style={styles.cardBody}>
                    {imageUrl ? (
                      <Image source={{ uri: imageUrl }} style={styles.productThumb} resizeMode="cover" />
                    ) : (
                      <View style={[styles.productThumb, styles.productThumbPlaceholder]}>
                        <MaterialCommunityIcons name="image-outline" size={28} color="#ccc" />
                      </View>
                    )}
                    <View style={styles.itemsList}>
                      <Text style={styles.itemsLabel}>ITEMS ({items.length})</Text>
                      <View style={styles.itemChips}>
                        {items.slice(0, 3).map((it, idx) => (
                          <View key={idx} style={styles.itemChip}>
                            <Text style={styles.itemChipText} numberOfLines={1}>
                              {it.product?.name || it.name || it.product_name || 'Item'}
                            </Text>
                          </View>
                        ))}
                        {items.length > 3 && (
                          <Text style={styles.moreItems}>+{items.length - 3} more</Text>
                        )}
                      </View>
                    </View>
                  </View>

                  {/* Footer: total + payment */}
                  <View style={styles.cardFooter}>
                    <View style={styles.footerLeft}>
                      <Text style={styles.totalLabel}>Total Amount</Text>
                    </View>
                    <View style={styles.footerRight}>
                      <View style={[styles.paymentPill, { borderColor: isPaid ? '#22C55E' : '#F59E0B', backgroundColor: isPaid ? '#F0FDF4' : '#FFFBEB' }]}>
                        <MaterialCommunityIcons name={isPaid ? 'check-circle' : 'clock-outline'} size={12} color={isPaid ? '#22C55E' : '#F59E0B'} style={{ marginRight: 3 }} />
                        <Text style={[styles.paymentPillText, { color: isPaid ? '#22C55E' : '#F59E0B' }]}>{isPaid ? 'Paid' : 'Pending'}</Text>
                      </View>
                      <Text style={styles.totalAmount}>
                        ₱{(parseFloat(item.total_amount || item.total) || 0).toFixed(2)}
                      </Text>
                    </View>
                  </View>
                </TouchableOpacity>
              );
            }}
            contentContainerStyle={styles.ordersList}
            refreshControl={
              <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} tintColor={colors.primary} />
            }
          />
        )}
      </View>

      <BottomNav navigation={navigation} activeRoute="Orders" />
    </SafeAreaView>
  );
};

const getStyles = (theme) => StyleSheet.create({
  container:     { flex: 1, backgroundColor: theme.background },
  content:       { flex: 1 },
  centerContent: { flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 20 },
  statsRow:      { flexDirection: 'row', paddingHorizontal: 14, paddingVertical: 12, gap: 8 },
  statCard:      { flex: 1, backgroundColor: theme.cardBackground, borderRadius: 12, paddingVertical: 10, alignItems: 'center', borderTopWidth: 3, elevation: 2, gap: 2, shadowColor: '#000', shadowOffset: {width:0,height:1}, shadowOpacity: 0.06, shadowRadius: 4 },
  statCount:     { fontSize: 20, fontWeight: '800' },
  statLabel:     { fontSize: 9, color: theme.textMuted, fontWeight: '600', textAlign: 'center' },
  filterScroll:  { maxHeight: 44, flexGrow: 0 },
  filterContent: { paddingHorizontal: 14, gap: 8, alignItems: 'center' },
  filterChip:    { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 14, paddingVertical: 7, borderRadius: 20, backgroundColor: theme.cardBackground, borderWidth: 1.5, borderColor: theme.borderLight },
  filterChipActive: { backgroundColor: '#8B1A1A', borderColor: '#8B1A1A' },
  filterChipText: { fontSize: 12, fontWeight: '600', color: theme.textSecondary },
  filterChipTextActive: { color: '#fff' },
  filterBadge:   { backgroundColor: 'rgba(255,255,255,0.25)', borderRadius: 8, paddingHorizontal: 5, paddingVertical: 1, marginLeft: 4 },
  filterBadgeActive: { backgroundColor: '#fff' },
  filterBadgeText: { fontSize: 10, fontWeight: '700', color: '#fff' },
  title:         { fontSize: 22, fontWeight: '700', color: theme.text, marginBottom: 6, marginTop: 12 },
  subtitle:      { fontSize: 13, color: theme.textMuted, fontWeight: '500', marginBottom: 20, textAlign: 'center' },
  emptyIconWrap: { width: 100, height: 100, borderRadius: 50, backgroundColor: theme.cardBackground, justifyContent: 'center', alignItems: 'center', marginBottom: 20, elevation: 3 },
  emptyContainer:{ flex: 1, justifyContent: 'center', alignItems: 'center', paddingHorizontal: 30 },
  emptyText:     { fontSize: 20, fontWeight: '700', color: theme.text, marginBottom: 10, marginTop: 16, textAlign: 'center' },
  emptySubtext:  { fontSize: 14, color: theme.textSecondary, marginBottom: 28, textAlign: 'center', lineHeight: 20 },
  emptyButton:   { backgroundColor: '#8B1A1A', paddingHorizontal: 28, paddingVertical: 14, borderRadius: 12, flexDirection: 'row', alignItems: 'center', elevation: 3 },
  emptyButtonText: { color: '#fff', fontWeight: '700', fontSize: 15 },
  loginButton:   { backgroundColor: '#8B1A1A', paddingHorizontal: 28, paddingVertical: 12, borderRadius: 10, flexDirection: 'row', alignItems: 'center' },
  loginButtonText: { color: '#fff', fontWeight: '600', fontSize: 15 },
  loadingText:   { marginTop: 12, fontSize: 16, color: theme.text },
  ordersList:    { paddingHorizontal: 14, paddingTop: 14, paddingBottom: 100 },
  orderCard:     { backgroundColor: theme.cardBackground, borderRadius: 16, marginBottom: 14, overflow: 'hidden', elevation: 3, shadowColor: '#000', shadowOffset: {width:0,height:2}, shadowOpacity: 0.07, shadowRadius: 8, borderWidth: 1, borderColor: theme.borderLight },

  // Card header
  cardHeader:    { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', paddingHorizontal: 16, paddingTop: 16, paddingBottom: 12 },
  cardHeaderLeft:{ flex: 1, marginRight: 10 },
  orderRef:      { fontSize: 15, fontWeight: '700', color: theme.text, marginBottom: 3 },
  orderDate:     { fontSize: 12, color: theme.textSecondary },
  badgesRow:     { flexDirection: 'row', gap: 6, flexWrap: 'wrap', justifyContent: 'flex-end' },
  statusPill:    { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 20, borderWidth: 1.5 },
  statusPillText:{ fontSize: 11, fontWeight: '700' },

  // Card body
  cardBody:      { flexDirection: 'row', alignItems: 'flex-start', paddingHorizontal: 16, paddingBottom: 14, borderTopWidth: 1, borderTopColor: theme.borderLight, paddingTop: 14, gap: 14 },
  productThumb:  { width: 72, height: 72, borderRadius: 10, backgroundColor: '#F3F4F6' },
  productThumbPlaceholder: { alignItems: 'center', justifyContent: 'center' },
  itemsList:     { flex: 1 },
  itemsLabel:    { fontSize: 10, fontWeight: '700', color: theme.textMuted, letterSpacing: 0.8, marginBottom: 8 },
  itemChips:     { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
  itemChip:      { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 6, borderWidth: 1, borderColor: '#8B1A1A', backgroundColor: '#FEF2F2', maxWidth: 160 },
  itemChipText:  { fontSize: 12, color: '#8B1A1A', fontWeight: '600' },
  moreItems:     { fontSize: 11, color: theme.textMuted, alignSelf: 'center', marginLeft: 2 },

  // Card footer
  cardFooter:    { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16, paddingVertical: 12, borderTopWidth: 1, borderTopColor: theme.borderLight },
  footerLeft:    {},
  footerRight:   { flexDirection: 'row', alignItems: 'center', gap: 10 },
  totalLabel:    { fontSize: 12, color: theme.textMuted, fontWeight: '600' },
  totalAmount:   { fontSize: 16, fontWeight: '800', color: '#8B1A1A' },
  paymentPill:   { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 20, borderWidth: 1.5 },
  paymentPillText:{ fontSize: 11, fontWeight: '700' },
});

export default OrdersScreen;
