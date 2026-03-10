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
} from 'react-native';
import { MaterialCommunityIcons, Ionicons } from '@expo/vector-icons';
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
            renderItem={({ item, index }) => {
              const pi = getMiniProgress(item.status);
              const sc = getStatusColor(item.status);
              return (
                <TouchableOpacity
                  style={[styles.orderCard, { borderLeftColor: sc }]}
                  onPress={() => navigation.navigate('OrderDetails', { orderData: item })}
                  activeOpacity={0.7}
                >
                  {/* Card Header */}
                  <View style={styles.orderCardHeader}>
                    <View style={styles.orderHeaderLeft}>
                      <View style={[styles.orderNumberContainer, { backgroundColor: sc + '20' }]}>
                        <MaterialCommunityIcons name={getStatusIcon(item.status)} size={22} color={sc} />
                      </View>
                      <View style={styles.orderInfo}>
                        <Text style={styles.orderRef}>Order #{item.id}</Text>
                        <Text style={styles.orderDate}>
                          {new Date(item.created_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </Text>
                      </View>
                    </View>
                    <View style={[styles.statusBadge, { backgroundColor: sc }]}>
                      <MaterialCommunityIcons name={getStatusIcon(item.status)} size={12} color="#fff" style={{ marginRight: 4 }} />
                      <Text style={styles.statusText}>{getStatusLabel(item.status)}</Text>
                    </View>
                  </View>

                  {/* Mini progress dots */}
                  {pi >= 0 && (
                    <View style={styles.miniProgress}>
                      {MINI_STAGES.map((_, idx) => (
                        <React.Fragment key={idx}>
                          <View style={[
                            styles.miniDot,
                            idx <= pi ? { backgroundColor: sc } : { backgroundColor: theme.borderLight },
                          ]} />
                          {idx < MINI_STAGES.length - 1 && (
                            <View style={[styles.miniLine, { backgroundColor: idx < pi ? sc : theme.borderLight }]} />
                          )}
                        </React.Fragment>
                      ))}
                    </View>
                  )}

                  {/* Order details row */}
                  <View style={styles.orderCardBody}>
                    <View style={styles.orderDetail}>
                      <MaterialCommunityIcons name="shopping-outline" size={16} color="#8B1A1A" style={{ marginBottom: 4 }} />
                      <Text style={styles.detailLabel}>Items</Text>
                      <Text style={styles.detailValue}>{item.orderItems?.length || item.items?.length || 0}</Text>
                    </View>
                    <View style={[styles.detailDivider]} />
                    <View style={styles.orderDetail}>
                      <MaterialCommunityIcons name="currency-php" size={16} color="#22C55E" style={{ marginBottom: 4 }} />
                      <Text style={styles.detailLabel}>Total</Text>
                      <Text style={[styles.detailValue, { color: '#22C55E' }]}>
                        ₱{(parseFloat(item.total_amount || item.total) || 0).toFixed(2)}
                      </Text>
                    </View>
                    <View style={[styles.detailDivider]} />
                    <View style={styles.orderDetail}>
                      <MaterialCommunityIcons name={item.payment_status === 'paid' ? 'check-circle' : 'clock-outline'} size={16} color={item.payment_status === 'paid' ? '#22C55E' : '#F59E0B'} style={{ marginBottom: 4 }} />
                      <Text style={styles.detailLabel}>Payment</Text>
                      <Text style={[styles.detailValue, { color: item.payment_status === 'paid' ? '#22C55E' : '#F59E0B' }]}>
                        {item.payment_status === 'paid' ? 'Paid' : 'Pending'}
                      </Text>
                    </View>
                  </View>

                  {/* Footer */}
                  <View style={styles.orderCardFooter}>
                    <Text style={styles.viewDetails}>View Details</Text>
                    <MaterialCommunityIcons name="chevron-right" size={18} color="#8B1A1A" />
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
  orderCard:     { backgroundColor: theme.cardBackground, borderRadius: 16, marginBottom: 14, overflow: 'hidden', elevation: 4, shadowColor: '#000', shadowOffset: {width:0,height:2}, shadowOpacity: 0.09, shadowRadius: 8, borderLeftWidth: 4 },
  orderCardHeader:{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 14 },
  orderHeaderLeft:{ flexDirection: 'row', alignItems: 'center', flex: 1, marginRight: 10 },
  orderNumberContainer: { width: 46, height: 46, borderRadius: 13, justifyContent: 'center', alignItems: 'center', marginRight: 12 },
  orderInfo:     { flex: 1 },
  orderRef:      { fontSize: 15, fontWeight: '700', color: theme.text, marginBottom: 3 },
  orderDate:     { fontSize: 12, color: theme.textSecondary, fontWeight: '500' },
  statusBadge:   { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 12, flexDirection: 'row', alignItems: 'center' },
  statusText:    { color: '#fff', fontSize: 11, fontWeight: '700' },
  miniProgress:  { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingBottom: 12 },
  miniDot:       { width: 8, height: 8, borderRadius: 4 },
  miniLine:      { flex: 1, height: 2, marginHorizontal: 2 },
  orderCardBody: { flexDirection: 'row', paddingHorizontal: 16, paddingVertical: 14, borderTopWidth: 1, borderTopColor: theme.borderLight, justifyContent: 'space-around' },
  orderDetail:   { alignItems: 'center', flex: 1 },
  detailDivider: { width: 1, height: '100%', backgroundColor: theme.borderLight },
  detailLabel:   { fontSize: 11, color: theme.textMuted, marginBottom: 3, fontWeight: '600' },
  detailValue:   { fontSize: 14, fontWeight: '700', color: theme.text },
  orderCardFooter:{ paddingHorizontal: 16, paddingVertical: 12, borderTopWidth: 1, borderTopColor: theme.borderLight, flexDirection: 'row', alignItems: 'center', justifyContent: 'flex-end' },
  viewDetails:   { fontSize: 13, fontWeight: '700', color: '#8B1A1A', marginRight: 4 },
});

export default OrdersScreen;
