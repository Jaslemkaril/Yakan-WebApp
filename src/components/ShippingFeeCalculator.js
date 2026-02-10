import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  Alert,
} from 'react-native';
import colors from '../constants/colors';
import { useShippingFee } from '../hooks/useShippingFee';

const ShippingFeeCalculator = ({ onFeeCalculated, initialDistance = 0 }) => {
  const [distance, setDistance] = useState(initialDistance.toString());
  const [calculatedFee, setCalculatedFee] = useState(null);
  const { calculateFee, loading, error, shippingRate, fetchShippingRate } = useShippingFee();

  useEffect(() => {
    // Fetch shipping rate on mount
    fetchShippingRate();
  }, []);

  const handleCalculate = async () => {
    const distanceNum = parseFloat(distance);
    
    if (!distance || isNaN(distanceNum) || distanceNum < 0) {
      Alert.alert('Invalid Input', 'Please enter a valid distance in kilometers');
      return;
    }

    const result = await calculateFee(distanceNum);
    
    if (result) {
      setCalculatedFee(result);
      onFeeCalculated?.(result);
    } else {
      Alert.alert('Error', error || 'Failed to calculate shipping fee');
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Shipping Fee Calculator</Text>
      
      {/* Shipping Rate Info */}
      {shippingRate && (
        <View style={styles.rateInfo}>
          <Text style={styles.rateLabel}>Current Rate: {shippingRate.name}</Text>
          <View style={styles.rateDetails}>
            <Text style={styles.rateDetail}>
              Base: ₱{parseFloat(shippingRate.base_fee).toFixed(2)} for {shippingRate.base_km}km
            </Text>
            <Text style={styles.rateDetail}>
              Then: ₱{parseFloat(shippingRate.per_km_fee).toFixed(2)}/km
            </Text>
          </View>
        </View>
      )}

      {/* Distance Input */}
      <View style={styles.inputSection}>
        <Text style={styles.label}>Distance (km)</Text>
        <View style={styles.inputWrapper}>
          <TextInput
            style={styles.input}
            placeholder="Enter distance in kilometers"
            placeholderTextColor="#999"
            keyboardType="decimal-pad"
            value={distance}
            onChangeText={setDistance}
            editable={!loading}
          />
          <Text style={styles.unit}>km</Text>
        </View>
      </View>

      {/* Calculate Button */}
      <TouchableOpacity
        style={[styles.calculateButton, loading && styles.calculateButtonDisabled]}
        onPress={handleCalculate}
        disabled={loading}
      >
        {loading ? (
          <ActivityIndicator color={colors.white} size="small" />
        ) : (
          <Text style={styles.calculateButtonText}>Calculate Fee</Text>
        )}
      </TouchableOpacity>

      {/* Calculated Fee Result */}
      {calculatedFee && (
        <View style={styles.resultCard}>
          <View style={styles.resultRow}>
            <Text style={styles.resultLabel}>Distance</Text>
            <Text style={styles.resultValue}>{calculatedFee.distance_km} km</Text>
          </View>
          <View style={styles.resultDivider} />
          <View style={styles.resultRow}>
            <Text style={styles.resultLabel}>Shipping Fee</Text>
            <Text style={styles.resultFee}>₱{parseFloat(calculatedFee.shipping_fee).toFixed(2)}</Text>
          </View>
          <View style={styles.resultDivider} />
          <View style={styles.resultRow}>
            <Text style={styles.resultLabel}>Rate Applied</Text>
            <Text style={styles.resultValue}>{calculatedFee.rate_name}</Text>
          </View>
        </View>
      )}

      {/* Error Message */}
      {error && (
        <View style={styles.errorCard}>
          <Text style={styles.errorText}>⚠️ {error}</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: colors.border,
  },
  title: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 12,
  },
  rateInfo: {
    backgroundColor: '#F0F9FF',
    borderRadius: 8,
    padding: 12,
    marginBottom: 16,
    borderLeftWidth: 4,
    borderLeftColor: colors.primary,
  },
  rateLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: colors.primary,
    marginBottom: 8,
  },
  rateDetails: {
    gap: 4,
  },
  rateDetail: {
    fontSize: 12,
    color: colors.text,
  },
  inputSection: {
    marginBottom: 16,
  },
  label: {
    fontSize: 13,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 8,
  },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    paddingRight: 12,
  },
  input: {
    flex: 1,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: colors.text,
  },
  unit: {
    fontSize: 13,
    fontWeight: '600',
    color: colors.textLight,
  },
  calculateButton: {
    backgroundColor: colors.primary,
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
    marginBottom: 16,
  },
  calculateButtonDisabled: {
    opacity: 0.6,
  },
  calculateButtonText: {
    color: colors.white,
    fontSize: 14,
    fontWeight: '600',
  },
  resultCard: {
    backgroundColor: '#F0FDF4',
    borderRadius: 8,
    padding: 12,
    borderWidth: 1,
    borderColor: '#22c55e',
  },
  resultRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  resultLabel: {
    fontSize: 13,
    color: colors.text,
    fontWeight: '500',
  },
  resultValue: {
    fontSize: 13,
    color: colors.text,
    fontWeight: '600',
  },
  resultFee: {
    fontSize: 16,
    color: '#22c55e',
    fontWeight: 'bold',
  },
  resultDivider: {
    height: 1,
    backgroundColor: '#E5E7EB',
  },
  errorCard: {
    backgroundColor: '#FEF2F2',
    borderRadius: 8,
    padding: 12,
    borderWidth: 1,
    borderColor: '#ef4444',
  },
  errorText: {
    fontSize: 13,
    color: '#dc2626',
    fontWeight: '500',
  },
});

export default ShippingFeeCalculator;
