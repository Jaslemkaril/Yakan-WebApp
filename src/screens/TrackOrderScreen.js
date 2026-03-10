import React, { useState, useEffect } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity,
  ActivityIndicator, RefreshControl, Alert, Image,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { useOrders } from '../../useOrders';
import ScreenHeader from '../components/ScreenHeader';
import BottomNav from '../components/BottomNav';
import { useTheme } from '../context/ThemeContext';

const ORDER_STATUSES = {
  pending:          { label: 'Pending',          icon: 'clock-outline',                color: '#FF9800', bg: '#FFF3E0' },
  pending_payment:  { label: 'Awaiting Payment',  icon: 'cash-clock',                   color: '#E91E63', bg: '#FCE4EC' },
  confirmed:        { label: 'Confirmed',          icon: 'check-circle-outline',         color: '#2196F3', bg: '#E3F2FD' },
  processing:       { label: 'Processing',         icon: 'cog-outline',                  color: '#9C27B0', bg: '#F3E5F5' },
  shipped:          { label: 'Shipped',            icon: 'truck-delivery-outline',       color: '#00BCD4', bg: '#E0F7FA' },
  out_for_delivery: { label: 'Out for Delivery',   icon: 'moped-outline',                color: '#FF5722', bg: '#FBE9E7' },
  delivered:        { label: 'Delivered',          icon: 'package-variant-closed-check', color: '#4CAF50', bg: '#E8F5E9' },
  cancelled:        { label: 'Cancelled',          icon: 'close-circle-outline',         color: '#F44336', bg: '#FFEBEE' },
  refunded:         { label: 'Refunded',           icon: 'cash-refund',                  color: '#795548', bg: '#EFEBE9' },
};

const PROGRESS_STAGES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

const getStatusInfo = (s) =>
  ORDER_STATUSES[s] || { label: s || 'Unknown', icon: 'help-circle-outline', color: '#757575', bg: '#F5F5F5' };

const getProgressIndex = (s) => {
  if (s === 'cancelled' || s === 'refunded') return -1;
  const i = PROGRESS_STAGES.indexOf(s);
  return i === -1 ? 0 : i;
};

// ─── Static Delivery Route Map (sample visual) ──────────────────────────────
const DeliveryMapCard = ({ status, orderRef }) => {
  const isMoving    = ['shipped', 'out_for_delivery'].includes(status);
  const isDelivered = status === 'delivered';
  const accentColor = isDelivered ? '#4CAF50' : isMoving ? '#FF5722' : '#800020';
  return (
    <View style={mSt.wrapper}>
      <View style={mSt.mapBg}>
        {[55, 120, 180].map(y => <View key={'h'+y} style={[mSt.roadH, { top: y }]} />)}
        {[58, 130, 202].map(x => <View key={'v'+x} style={[mSt.roadV, { left: x }]} />)}
        {[[8,8,40,37],[72,8,48,37],[144,8,52,37],[8,68,40,40],[72,68,48,40],[144,68,52,40],[8,130,40,38],[72,130,48,38],[144,130,52,38],[8,183,40,26],[72,183,48,26],[144,183,52,26]]
          .map(([l,t,w,h],i) => <View key={i} style={[mSt.block,{left:l,top:t,width:w,height:h}]} />)}
        {[0,1,2,3,4,5,6,7,8].map(i => (
          <View key={'d'+i} style={[mSt.dash,{top:132-i*12,left:20+i*20},isMoving&&mSt.dashActive,isDelivered&&mSt.dashDone]} />
        ))}
        <View style={[mSt.pinWrap, { bottom: 28, left: 8 }]}>
          <View style={[mSt.pin, { backgroundColor: '#800020' }]}>
            <MaterialCommunityIcons name="store" size={13} color="#fff" />
          </View>
          <View style={[mSt.pinTail, { borderTopColor: '#800020' }]} />
          <Text style={mSt.pinLbl}>Store</Text>
        </View>
        <View style={[mSt.pinWrap, { top: 8, right: 16 }]}>
          <View style={[mSt.pin, { backgroundColor: accentColor }]}>
            <MaterialCommunityIcons name={isDelivered ? 'home-check' : 'home-map-marker'} size={13} color="#fff" />
          </View>
          <View style={[mSt.pinTail, { borderTopColor: accentColor }]} />
          <Text style={mSt.pinLbl}>You</Text>
        </View>
        {isMoving && (
          <View style={[mSt.riderDot, { top: 62, left: 108 }]}>
            <MaterialCommunityIcons name="moped" size={13} color="#fff" />
          </View>
        )}
        <View style={mSt.badge}>
          <MaterialCommunityIcons name="map-marker-path" size={11} color="#800020" style={{marginRight:3}} />
          <Text style={mSt.badgeTxt}>Sample Route Map</Text>
        </View>
      </View>
      <View style={[mSt.strip, { backgroundColor: accentColor }]}>
        <MaterialCommunityIcons
          name={isDelivered ? 'package-variant-closed-check' : isMoving ? 'moped-outline' : 'map-marker-path'}
          size={14} color="#fff" style={{ marginRight: 6 }}
        />
        <Text style={mSt.stripTxt}>
          {isDelivered ? 'Order Delivered Successfully!' : isMoving ? `Your order is on the way \u2022 ${orderRef}` : `Tracking \u2022 ${orderRef}`}
        </Text>
      </View>
    </View>
  );
};
const mSt = StyleSheet.create({
  wrapper:  { borderRadius: 14, overflow: 'hidden', marginBottom: 14, elevation: 3, shadowColor: '#000', shadowOffset: {width:0,height:2}, shadowOpacity: 0.1, shadowRadius: 6 },
  mapBg:    { height: 215, backgroundColor: '#E9EEF4', position: 'relative', overflow: 'hidden' },
  roadH:    { position: 'absolute', left: 0, right: 0, height: 10, backgroundColor: '#CDD5DF' },
  roadV:    { position: 'absolute', top: 0, bottom: 0, width: 10, backgroundColor: '#CDD5DF' },
  block:    { position: 'absolute', backgroundColor: '#BEC8D4', borderRadius: 4 },
  dash:     { position: 'absolute', width: 14, height: 4, borderRadius: 2, backgroundColor: '#AAAAAA', opacity: 0.6 },
  dashActive: { backgroundColor: '#FF5722', opacity: 1 },
  dashDone:   { backgroundColor: '#4CAF50', opacity: 1 },
  pinWrap:  { position: 'absolute', alignItems: 'center' },
  pin:      { width: 28, height: 28, borderRadius: 14, justifyContent: 'center', alignItems: 'center', elevation: 4 },
  pinTail:  { width: 0, height: 0, borderLeftWidth: 5, borderRightWidth: 5, borderTopWidth: 7, borderLeftColor: 'transparent', borderRightColor: 'transparent' },
  pinLbl:   { fontSize: 9, fontWeight: '700', color: '#444', marginTop: 2 },
  riderDot: { position: 'absolute', width: 26, height: 26, borderRadius: 13, backgroundColor: '#FF5722', justifyContent: 'center', alignItems: 'center', elevation: 5, shadowColor: '#FF5722', shadowOffset: {width:0,height:2}, shadowOpacity: 0.5, shadowRadius: 4 },
  badge:    { position: 'absolute', top: 8, left: 8, flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.88)', borderRadius: 8, paddingHorizontal: 8, paddingVertical: 4 },
  badgeTxt: { fontSize: 10, color: '#800020', fontWeight: '700' },
  strip:    { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 14, paddingVertical: 10 },
  stripTxt: { color: '#fff', fontSize: 12, fontWeight: '700', flex: 1 },
});
// ────────────────────────────────────────────────────────────────────────────

const TrackOrderScreen = ({ navigation }) => {
  const { theme } = useTheme();
  const st = getStyles(theme);
  const { orders, loading, refreshing, loadOrders, onRefresh, savePaymentProof } = useOrders();

  useEffect(() => { const u = navigation.addListener('focus', loadOrders); return u; }, [navigation]);
  useEffect(() => { loadOrders(); }, []);

  const handleUploadPaymentProof = (order) => {
    Alert.alert('Upload Payment Proof', 'Choose how to upload', [
      { text: 'Take Photo', onPress: () => takePhoto(order) },
      { text: 'Choose from Gallery', onPress: () => pickImage(order) },
      { text: 'Cancel', style: 'cancel' },
    ]);
  };

  const takePhoto = async (order) => {
    const { status } = await ImagePicker.requestCameraPermissionsAsync();
    if (status !== 'granted') { Alert.alert('Permission needed', 'Camera permission is required'); return; }
    const r = await ImagePicker.launchCameraAsync({ mediaTypes: ImagePicker.MediaTypeOptions.Images, allowsEditing: true, aspect: [4,3], quality: 0.8 });
    if (!r.canceled) savePaymentProof(order, r.assets[0].uri);
  };

  const pickImage = async (order) => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') { Alert.alert('Permission needed', 'Gallery permission is required'); return; }
    const r = await ImagePicker.launchImageLibraryAsync({ mediaTypes: ImagePicker.MediaTypeOptions.Images, allowsEditing: true, aspect: [4,3], quality: 0.8 });
    if (!r.canceled) savePaymentProof(order, r.assets[0].uri);
  };

  const formatDate = (d) => {
    if (!d) return '\u2014';
    return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  };

  const renderProgressBar = (status) => {
    const pi = getProgressIndex(status);
    if (pi < 0) return null;
    return (
      <View style={st.progressContainer}>
        {PROGRESS_STAGES.map((stage, idx) => {
          const info  = getStatusInfo(stage);
          const done   = idx < pi;
          const active = idx === pi;
          return (
            <React.Fragment key={stage}>
              <View style={st.progressStep}>
                <View style={[st.progressDot,
                  done   && st.progressDotDone,
                  active && { backgroundColor: info.color, borderColor: info.color },
                  !done && !active && st.progressDotUpcoming,
                ]}>
                  {done   && <MaterialCommunityIcons name='check'     size={9} color='#fff' />}
                  {active && <MaterialCommunityIcons name={info.icon} size={9} color='#fff' />}
                </View>
                <Text style={[st.progressLabel,
                  done   && { color: '#4CAF50' },
                  active && { color: info.color, fontWeight: '700' },
                  !done && !active && { color: theme.textMuted },
                ]} numberOfLines={1}>{info.label.split(' ')[0]}</Text>
              </View>
              {idx < PROGRESS_STAGES.length - 1 && (
                <View style={[st.progressLine,
                  done   && st.progressLineDone,
                  !done && !active && { backgroundColor: theme.borderLight },
                ]} />
              )}
            </React.Fragment>
          );
        })}
      </View>
    );
  };

  const renderOrder = (order, index) => {
    const si = getStatusInfo(order.status);
    return (
      <TouchableOpacity key={order.orderRef || index} style={[st.orderCard, { borderLeftColor: si.color }]}
        onPress={() => navigation.navigate('OrderDetails', { orderData: order })} activeOpacity={0.75}>
        <View style={st.orderHeader}>
          <View style={st.orderRefRow}>
            <View style={[st.statusIconBg, { backgroundColor: si.bg }]}>
              <MaterialCommunityIcons name={si.icon} size={20} color={si.color} />
            </View>
            <View style={{ flex: 1 }}>
              <Text style={st.orderRefLabel}>Order #{order.orderRef}</Text>
              <Text style={st.orderDate}>{formatDate(order.date)}</Text>
            </View>
          </View>
          <View style={[st.statusBadge, { backgroundColor: si.color }]}>
            <MaterialCommunityIcons name={si.icon} size={11} color='#fff' style={{ marginRight: 3 }} />
            <Text style={st.statusText}>{si.label}</Text>
          </View>
        </View>

        {renderProgressBar(order.status)}

        {/* Delivery map for shipped/out_for_delivery/delivered orders */}
        {['shipped','out_for_delivery','delivered'].includes(order.status) && (
          <DeliveryMapCard status={order.status} orderRef={order.orderRef} />
        )}

        <View style={st.divider} />

        <View style={st.itemsContainer}>
          {order.isCustom ? (
            <View style={st.itemRow}>
              <View style={[st.itemIconBg, { backgroundColor: '#F3E5F5' }]}>
                <MaterialCommunityIcons name='palette' size={16} color='#9C27B0' />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={st.itemText} numberOfLines={1}>{order.items[0]?.name}</Text>
                <Text style={st.itemDetail} numberOfLines={1}>{order.items[0]?.details}</Text>
              </View>
            </View>
          ) : (
            <>
              {order.items.slice(0, 2).map((item, idx) => (
                <View key={idx} style={st.itemRow}>
                  <View style={st.itemIconBg}>
                    <MaterialCommunityIcons name='shopping' size={14} color={theme.primary} />
                  </View>
                  <Text style={st.itemText} numberOfLines={1}>{item.name} <Text style={st.itemQty}>x{item.quantity}</Text></Text>
                </View>
              ))}
              {order.items.length > 2 && <Text style={st.moreItems}>+{order.items.length - 2} more item{order.items.length - 2 > 1 ? 's' : ''}</Text>}
            </>
          )}
        </View>

        <View style={st.orderFooter}>
          <View style={st.footerLeft}>
            <MaterialCommunityIcons name='currency-php' size={14} color={theme.textSecondary} />
            <Text style={st.totalLabel}>Total Amount</Text>
          </View>
          <Text style={st.totalAmount}>\u20B1{(order.total || 0).toFixed(2)}</Text>
        </View>

        {order.status === 'pending_payment' && !order.isCustom && (
          <TouchableOpacity style={st.paymentReminder} onPress={() => handleUploadPaymentProof(order)} activeOpacity={0.7}>
            <MaterialCommunityIcons name='camera-upload' size={22} color='#E91E63' style={{ marginRight: 10 }} />
            <View style={{ flex: 1 }}>
              <Text style={st.reminderTitle}>Action Required</Text>
              <Text style={st.reminderText}>Upload your payment proof to complete this order</Text>
            </View>
            <View style={st.uploadBtn}>
              <MaterialCommunityIcons name='upload' size={14} color='#fff' />
              <Text style={st.uploadBtnText}>Upload</Text>
            </View>
          </TouchableOpacity>
        )}

        {order.paymentProof && (
          <View style={st.proofUploaded}>
            <MaterialCommunityIcons name='check-circle' size={18} color='#4CAF50' style={{ marginRight: 8 }} />
            <Text style={st.proofUploadedText}>Payment proof uploaded</Text>
            <Image source={{ uri: order.paymentProof }} style={st.proofThumbnail} />
          </View>
        )}

        <View style={st.viewDetailsContainer}>
          <Text style={st.viewDetailsText}>View Details</Text>
          <MaterialCommunityIcons name='chevron-right' size={18} color={theme.primary} />
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) return (
    <View style={[st.container, { backgroundColor: theme.background }]}>
      <ScreenHeader title='Track Orders' navigation={navigation} showBack={true} />
      <View style={st.loadingContainer}>
        <ActivityIndicator size='large' color={theme.primary} />
        <Text style={st.loadingText}>Loading orders...</Text>
      </View>
      <BottomNav navigation={navigation} activeRoute='TrackOrders' />
    </View>
  );

  return (
    <View style={[st.container, { backgroundColor: theme.background }]}>
      <ScreenHeader title='Track Orders' navigation={navigation} showBack={true} />
      {orders.length === 0 ? (
        <View style={st.emptyContainer}>
          <View style={st.emptyIconCircle}>
            <MaterialCommunityIcons name='package-variant-closed' size={64} color={theme.primary} />
          </View>
          <Text style={st.emptyTitle}>No Orders Yet</Text>
          <Text style={st.emptyText}>You have not placed any orders yet. Start shopping to see your orders here.</Text>
          <TouchableOpacity style={st.shopButton} onPress={() => navigation.navigate('Home')} activeOpacity={0.8}>
            <MaterialCommunityIcons name='shopping-outline' size={20} color='#fff' style={{ marginRight: 8 }} />
            <Text style={st.shopButtonText}>Start Shopping</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <ScrollView style={st.scrollView} contentContainerStyle={st.scrollContent} showsVerticalScrollIndicator={false}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[theme.primary]} tintColor={theme.primary} />}>
          <View style={st.summaryRow}>
            {[
              { icon: 'package-variant',              color: theme.primary, count: orders.length,                                                                                                         label: 'Total' },
              { icon: 'truck-delivery-outline',        color: '#FF5722',     count: orders.filter(o => ['shipped','out_for_delivery'].includes(o.status)).length,                                         label: 'In Transit' },
              { icon: 'package-variant-closed-check', color: '#4CAF50',     count: orders.filter(o => o.status === 'delivered').length,                                                                   label: 'Delivered' },
              { icon: 'clock-outline',                 color: '#FF9800',     count: orders.filter(o => ['pending','pending_payment','confirmed','processing'].includes(o.status)).length,                  label: 'Pending' },
            ].map((s, i) => (
              <View key={i} style={[st.summaryCard, { borderTopColor: s.color, borderTopWidth: 3 }]}>
                <MaterialCommunityIcons name={s.icon} size={20} color={s.color} />
                <Text style={[st.summaryCount, { color: s.color }]}>{s.count}</Text>
                <Text style={st.summaryLabel}>{s.label}</Text>
              </View>
            ))}
          </View>
          {orders.map((o, i) => renderOrder(o, i))}
          <View style={{ height: 30 }} />
        </ScrollView>
      )}

      <BottomNav navigation={navigation} activeRoute='TrackOrders' />
    </View>
  );
};

const getStyles = (theme) => StyleSheet.create({
  container:       { flex: 1, backgroundColor: theme.background },
  loadingContainer:{ flex: 1, justifyContent: 'center', alignItems: 'center', gap: 12 },
  loadingText:     { fontSize: 16, color: theme.textSecondary, fontWeight: '500' },
  emptyContainer:  { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 40 },
  emptyIconCircle: { width: 120, height: 120, borderRadius: 60, backgroundColor: theme.cardBackground, justifyContent: 'center', alignItems: 'center', marginBottom: 24, elevation: 4 },
  emptyTitle:      { fontSize: 24, fontWeight: '700', color: theme.text, marginBottom: 12 },
  emptyText:       { fontSize: 15, color: theme.textSecondary, textAlign: 'center', lineHeight: 22, marginBottom: 32 },
  shopButton:      { backgroundColor: theme.primary, paddingHorizontal: 30, paddingVertical: 14, borderRadius: 12, flexDirection: 'row', alignItems: 'center' },
  shopButtonText:  { color: '#fff', fontSize: 16, fontWeight: '700' },
  scrollView:      { flex: 1 },
  scrollContent:   { padding: 15 },
  summaryRow:      { flexDirection: 'row', gap: 10, marginBottom: 16 },
  summaryCard:     { flex: 1, backgroundColor: theme.cardBackground, borderRadius: 12, padding: 12, alignItems: 'center', elevation: 2, gap: 4 },
  summaryCount:    { fontSize: 22, fontWeight: '800', color: theme.primary },
  summaryLabel:    { fontSize: 11, color: theme.textMuted, fontWeight: '600', textAlign: 'center' },
  orderCard:       { backgroundColor: theme.cardBackground, borderRadius: 16, padding: 16, marginBottom: 14, elevation: 3, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.08, shadowRadius: 6, borderLeftWidth: 4 },
  orderHeader:     { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 12 },
  orderRefRow:     { flexDirection: 'row', alignItems: 'center', flex: 1, marginRight: 8, gap: 10 },
  statusIconBg:    { width: 42, height: 42, borderRadius: 21, justifyContent: 'center', alignItems: 'center' },
  orderRefLabel:   { fontSize: 15, fontWeight: '700', color: theme.text },
  orderDate:       { fontSize: 12, color: theme.textMuted, marginTop: 2 },
  statusBadge:     { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 20 },
  statusText:      { color: '#fff', fontSize: 11, fontWeight: '700' },
  progressContainer: { flexDirection: 'row', alignItems: 'flex-start', marginBottom: 12, paddingHorizontal: 2 },
  progressStep:    { alignItems: 'center', width: 44 },
  progressDot:     { width: 20, height: 20, borderRadius: 10, borderWidth: 2, borderColor: '#4CAF50', backgroundColor: '#4CAF50', justifyContent: 'center', alignItems: 'center', marginBottom: 4 },
  progressDotDone: { backgroundColor: '#4CAF50', borderColor: '#4CAF50' },
  progressDotUpcoming: { backgroundColor: 'transparent', borderColor: theme.borderLight },
  progressLabel:   { fontSize: 9, fontWeight: '600', textAlign: 'center' },
  progressLine:    { flex: 1, height: 2, backgroundColor: '#4CAF50', marginTop: 9 },
  progressLineDone:{ backgroundColor: '#4CAF50' },
  divider:         { height: 1, backgroundColor: theme.borderLight, marginVertical: 12 },
  itemsContainer:  { marginBottom: 10 },
  itemRow:         { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 6 },
  itemIconBg:      { width: 28, height: 28, borderRadius: 8, backgroundColor: theme.surfaceBg || '#F8F9FA', justifyContent: 'center', alignItems: 'center' },
  itemText:        { fontSize: 14, color: theme.text, fontWeight: '500', flex: 1 },
  itemQty:         { color: theme.textMuted, fontWeight: '400' },
  itemDetail:      { fontSize: 12, color: theme.textMuted, fontStyle: 'italic' },
  moreItems:       { fontSize: 12, color: theme.primary, fontStyle: 'italic', marginLeft: 38 },
  orderFooter:     { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 12, borderTopWidth: 1, borderTopColor: theme.borderLight },
  footerLeft:      { flexDirection: 'row', alignItems: 'center', gap: 4 },
  totalLabel:      { fontSize: 14, color: theme.textSecondary },
  totalAmount:     { fontSize: 20, fontWeight: '800', color: theme.primary },
  paymentReminder: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FCE4EC', borderRadius: 12, padding: 12, marginTop: 12, borderWidth: 1, borderColor: '#F48FB1' },
  reminderTitle:   { fontSize: 13, fontWeight: '700', color: '#C2185B', marginBottom: 2 },
  reminderText:    { fontSize: 11, color: '#880E4F', lineHeight: 15 },
  uploadBtn:       { flexDirection: 'row', alignItems: 'center', backgroundColor: '#E91E63', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, gap: 4, marginLeft: 8 },
  uploadBtnText:   { color: '#fff', fontSize: 12, fontWeight: '700' },
  proofUploaded:   { flexDirection: 'row', alignItems: 'center', backgroundColor: '#E8F5E9', padding: 10, marginTop: 12, borderRadius: 10, borderWidth: 1, borderColor: '#A5D6A7' },
  proofUploadedText: { fontSize: 12, color: '#2E7D32', fontWeight: '700', flex: 1 },
  proofThumbnail:  { width: 40, height: 40, borderRadius: 8 },
  viewDetailsContainer: { flexDirection: 'row', alignItems: 'center', justifyContent: 'flex-end', marginTop: 10 },
  viewDetailsText: { fontSize: 13, color: theme.primary, fontWeight: '700', marginRight: 2 },
});

export default TrackOrderScreen;