import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';

const OrderDetailsScreen = ({ navigation, route }) => {
  const { order } = route.params;

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending_payment':
        return '#FF9800';
      case 'payment_verified':
        return '#2196F3';
      case 'processing':
        return '#9C27B0';
      case 'shipped':
        return '#00BCD4';
      case 'delivered':
        return '#4CAF50';
      case 'cancelled':
        return '#F44336';
      default:
        return '#757575';
    }
  };

  const getStatusText = (status) => {
    switch (status) {
      case 'pending_payment':
        return 'Pending Payment';
      case 'payment_verified':
        return 'Payment Verified';
      case 'processing':
        return 'Processing';
      case 'shipped':
        return 'Shipped';
      case 'delivered':
        return 'Delivered';
      case 'cancelled':
        return 'Cancelled';
      default:
        return 'Unknown';
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getTrackingSteps = () => {
    const steps = [
      { 
        key: 'pending_payment', 
        label: 'Order Placed', 
        description: 'Waiting for payment confirmation' 
      },
      { 
        key: 'payment_verified', 
        label: 'Payment Verified', 
        description: 'Payment has been confirmed' 
      },
      { 
        key: 'processing', 
        label: 'Processing', 
        description: 'Order is being prepared' 
      },
      { 
        key: 'shipped', 
        label: 'Shipped', 
        description: 'Order is on the way' 
      },
      { 
        key: 'delivered', 
        label: 'Delivered', 
        description: 'Order has been delivered' 
      },
    ];

    const statusOrder = ['pending_payment', 'payment_verified', 'processing', 'shipped', 'delivered'];
    const currentStatusIndex = statusOrder.indexOf(order.status);

    return steps.map((step, index) => ({
      ...step,
      isCompleted: index <= currentStatusIndex,
      isCurrent: index === currentStatusIndex,
    }));
  };

  const trackingSteps = getTrackingSteps();
  const statusColor = getStatusColor(order.status);
  const statusText = getStatusText(order.status);

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Text style={styles.backButton}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Order Details</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.statusCard}>
          <Text style={styles.orderRef}>{order.orderRef}</Text>
          <View style={[styles.statusBadge, { backgroundColor: statusColor }]}>
            <Text style={styles.statusText}>{statusText}</Text>
          </View>
          <Text style={styles.orderDate}>Placed on {formatDate(order.date)}</Text>
        </View>

        {order.status === 'pending_payment' && (
          <View style={styles.paymentAlert}>
            <Text style={styles.alertTitle}>Payment Required</Text>
            <Text style={styles.alertText}>
              Please send your payment to complete this order.
            </Text>
            <View style={styles.paymentDetailsBox}>
              {order.paymentMethod === 'gcash' ? (
                <>
                  <Text style={styles.paymentLabel}>GCash Number:</Text>
                  <Text style={styles.paymentValue}>0917-123-4567</Text>
                  <Text style={styles.paymentLabel}>Name:</Text>
                  <Text style={styles.paymentValue}>TUWAS Yakan Weaving</Text>
                </>
              ) : (
                <>
                  <Text style={styles.paymentLabel}>Bank: BDO</Text>
                  <Text style={styles.paymentValue}>1234-5678-9012</Text>
                  <Text style={styles.paymentLabel}>Name:</Text>
                  <Text style={styles.paymentValue}>TUWAS Yakan Weaving</Text>
                </>
              )}
            </View>
            <Text style={styles.contactInfo}>
              Send proof to:{'\n'}
              • 0917-123-4567 (Viber/Messenger){'\n'}
              • tuwasweavingyakan@gmail.com
            </Text>
          </View>
        )}

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Order Timeline</Text>
          <View style={styles.timeline}>
            {trackingSteps.map((step, index) => (
              <View key={step.key} style={styles.timelineItem}>
                <View style={styles.timelineLeft}>
                  <View
                    style={[
                      styles.timelineDot,
                      step.isCompleted && styles.timelineDotCompleted,
                      step.isCurrent && styles.timelineDotCurrent,
                    ]}
                  />
                  {index < trackingSteps.length - 1 && (
                    <View
                      style={[
                        styles.timelineLine,
                        step.isCompleted && styles.timelineLineCompleted,
                      ]}
                    />
                  )}
                </View>
                <View style={styles.timelineRight}>
                  <Text
                    style={[
                      styles.timelineLabel,
                      step.isCompleted && styles.timelineLabelCompleted,
                    ]}
                  >
                    {step.label}
                  </Text>
                  <Text style={styles.timelineDescription}>
                    {step.description}
                  </Text>
                </View>
              </View>
            ))}
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Order Items</Text>
          {order.items.map((item, index) => (
            <View key={index} style={styles.itemRow}>
              <View style={styles.itemInfo}>
                <Text style={styles.itemName}>{item.name}</Text>
                <Text style={styles.itemQuantity}>Qty: {item.quantity}</Text>
              </View>
              <Text style={styles.itemPrice}>
                ₱{(item.price * item.quantity).toFixed(2)}
              </Text>
            </View>
          ))}
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Shipping Address</Text>
          <Text style={styles.addressText}>
            {order.shippingAddress.street}
          </Text>
          <Text style={styles.addressText}>
            {order.shippingAddress.city}, {order.shippingAddress.province}
          </Text>
          <Text style={styles.addressText}>
            {order.shippingAddress.zipCode}
          </Text>
          <Text style={styles.addressText}>
            Phone: {order.shippingAddress.phoneNumber}
          </Text>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Payment Information</Text>
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Payment Method:</Text>
            <Text style={styles.infoValue}>
              {order.paymentMethod === 'gcash' ? 'GCash' : 'Bank Transfer'}
            </Text>
          </View>
        </View>

        <View style={styles.summaryCard}>
          <Text style={styles.sectionTitle}>Order Summary</Text>
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>₱{order.subtotal.toFixed(2)}</Text>
          </View>
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Shipping Fee</Text>
            <Text style={styles.summaryValue}>₱{order.shippingFee.toFixed(2)}</Text>
          </View>
          
          <View style={styles.divider} />
          
          <View style={styles.summaryRow}>
            <Text style={styles.totalLabel}>Total</Text>
            <Text style={styles.totalValue}>₱{order.total.toFixed(2)}</Text>
          </View>
        </View>

        <View style={styles.helpSection}>
          <Text style={styles.helpTitle}>Need Help?</Text>
          <Text style={styles.helpText}>
            Contact us for any questions about your order:
          </Text>
          <Text style={styles.helpContact}>
            Phone: 0917-123-4567{'\n'}
            Email: tuwasweavingyakan@gmail.com
          </Text>
        </View>

        <View style={{ height: 30 }} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
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
  statusCard: {
    backgroundColor: '#fff',
    padding: 20,
    marginBottom: 15,
    alignItems: 'center',
  },
  orderRef: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  statusBadge: {
    paddingHorizontal: 20,
    paddingVertical: 8,
    borderRadius: 20,
    marginBottom: 10,
  },
  statusText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  orderDate: {
    fontSize: 13,
    color: '#666',
  },
  paymentAlert: {
    backgroundColor: '#FFF9F0',
    borderLeftWidth: 4,
    borderLeftColor: '#FF9800',
    padding: 16,
    marginBottom: 15,
    marginHorizontal: 15,
    borderRadius: 8,
  },
  alertTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#D84315',
    marginBottom: 8,
  },
  alertText: {
    fontSize: 14,
    color: '#333',
    marginBottom: 12,
    lineHeight: 20,
  },
  paymentDetailsBox: {
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 6,
    marginBottom: 12,
  },
  paymentLabel: {
    fontSize: 13,
    color: '#666',
    marginBottom: 4,
  },
  paymentValue: {
    fontSize: 15,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  contactInfo: {
    fontSize: 12,
    color: '#666',
    lineHeight: 18,
  },
  section: {
    backgroundColor: '#fff',
    padding: 20,
    marginBottom: 15,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  timeline: {
    paddingLeft: 0,
  },
  timelineItem: {
    flexDirection: 'row',
    paddingBottom: 20,
  },
  timelineLeft: {
    alignItems: 'center',
    marginRight: 15,
  },
  timelineDot: {
    width: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: '#e0e0e0',
    borderWidth: 3,
    borderColor: '#fff',
  },
  timelineDotCompleted: {
    backgroundColor: '#4CAF50',
  },
  timelineDotCurrent: {
    backgroundColor: '#8B1A1A',
    width: 20,
    height: 20,
    borderRadius: 10,
  },
  timelineLine: {
    width: 2,
    flex: 1,
    backgroundColor: '#e0e0e0',
    marginTop: 4,
  },
  timelineLineCompleted: {
    backgroundColor: '#4CAF50',
  },
  timelineRight: {
    flex: 1,
    paddingTop: 0,
  },
  timelineLabel: {
    fontSize: 15,
    fontWeight: '600',
    color: '#999',
    marginBottom: 4,
  },
  timelineLabelCompleted: {
    color: '#333',
  },
  timelineDescription: {
    fontSize: 13,
    color: '#999',
    lineHeight: 18,
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  itemInfo: {
    flex: 1,
  },
  itemName: {
    fontSize: 15,
    color: '#333',
    marginBottom: 4,
  },
  itemQuantity: {
    fontSize: 13,
    color: '#666',
  },
  itemPrice: {
    fontSize: 15,
    fontWeight: '600',
    color: '#333',
  },
  addressText: {
    fontSize: 14,
    color: '#333',
    marginBottom: 6,
    lineHeight: 20,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
  },
  infoValue: {
    fontSize: 14,
    color: '#333',
    fontWeight: '500',
  },
  summaryCard: {
    backgroundColor: '#fff',
    padding: 20,
    marginBottom: 15,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  summaryLabel: {
    fontSize: 14,
    color: '#666',
  },
  summaryValue: {
    fontSize: 14,
    color: '#333',
  },
  divider: {
    height: 1,
    backgroundColor: '#e0e0e0',
    marginVertical: 10,
  },
  totalLabel: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  totalValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#8B1A1A',
  },
  helpSection: {
    backgroundColor: '#F5F5F5',
    padding: 20,
    marginHorizontal: 15,
    borderRadius: 8,
    marginBottom: 15,
  },
  helpTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  helpText: {
    fontSize: 14,
    color: '#666',
    marginBottom: 10,
  },
  helpContact: {
    fontSize: 14,
    color: '#8B1A1A',
    lineHeight: 20,
  },
});

export default OrderDetailsScreen;