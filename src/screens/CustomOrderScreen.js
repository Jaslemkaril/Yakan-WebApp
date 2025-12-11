import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  TextInput,
  Dimensions,
  Alert,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import AsyncStorage from '@react-native-async-storage/async-storage';

const { width } = Dimensions.get('window');

// Patterns with actual images
const PATTERNS = [
  {
    id: 1,
    name: 'Sinaluan',
    image: require('../assets/images/Sinaluan.jpg'),
    basePrice: 500,
  },
  {
    id: 2,
    name: 'Seputangan',
    image: require('../assets/images/Saputangan.jpg'),
    basePrice: 600,
  },
  {
    id: 3,
    name: 'Birey-birey',
    image: require('../assets/images/birey4.jpg'),
    basePrice: 550,
  },
  {
    id: 4,
    name: 'Pinantupan',
    image: require('../assets/images/pinantupan.jpg'),
    basePrice: 580,
  },
];

const COLORS = [
  { id: 1, name: 'Red', hex: '#DC143C' },
  { id: 2, name: 'Blue', hex: '#1E90FF' },
  { id: 3, name: 'Green', hex: '#228B22' },
  { id: 4, name: 'Yellow', hex: '#FFD700' },
  { id: 5, name: 'Orange', hex: '#FF8C00' },
  { id: 6, name: 'Purple', hex: '#9370DB' },
  { id: 7, name: 'Black', hex: '#000000' },
  { id: 8, name: 'White', hex: '#FFFFFF' },
];

const SIZES = [
  { id: 1, label: '1 meter', meters: 1, multiplier: 1 },
  { id: 2, label: '2 meters', meters: 2, multiplier: 1.8 },
  { id: 3, label: '3 meters', meters: 3, multiplier: 2.5 },
  { id: 4, label: '5 meters', meters: 5, multiplier: 4 },
];

const CustomOrderScreen = ({ navigation }) => {
  const [selectedPattern, setSelectedPattern] = useState(null);
  const [selectedColor, setSelectedColor] = useState(null);
  const [selectedSize, setSelectedSize] = useState(null);
  const [uploadedImage, setUploadedImage] = useState(null);
  const [description, setDescription] = useState('');
  const [customSize, setCustomSize] = useState('');

  const calculateEstimatedPrice = () => {
    if (!selectedPattern || !selectedSize) return 0;
    return Math.round(selectedPattern.basePrice * selectedSize.multiplier);
  };

  const saveCustomOrder = async (orderData) => {
    try {
      const orderRef = 'LOCAL-' + Date.now().toString().slice(-7);
      const finalOrder = {
        orderRef,
        date: new Date().toISOString(),
        items: [{
          name: `Custom Order: ${orderData.pattern}`,
          quantity: 1,
          price: orderData.estimatedPrice,
          details: `Color: ${orderData.color}, Size: ${orderData.size}`,
        }],
        total: orderData.estimatedPrice,
        subtotal: orderData.estimatedPrice,
        shippingFee: 0,
        status: 'pending_confirmation',
        isCustom: true,
        customDetails: orderData,
      };
      const existingOrders = await AsyncStorage.getItem('pendingOrders');
      const orders = existingOrders ? JSON.parse(existingOrders) : [];
      orders.push(finalOrder);
      await AsyncStorage.setItem('pendingOrders', JSON.stringify(orders));
    } catch (error) {
      console.error('Failed to save custom order locally:', error);
      // Re-throw the error to be caught by the calling function
      throw error;
    }
  };

  const pickImage = async () => {
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 1,
    });

    if (!result.canceled) {
      setUploadedImage(result.assets[0].uri);
      setSelectedPattern(null);
    }
  };

  const handleSubmitOrder = async () => {
    if (!selectedPattern && !uploadedImage) {
      Alert.alert('Missing Information', 'Please select a pattern or upload your design');
      return;
    }
    if (!selectedColor) {
      Alert.alert('Missing Information', 'Please select a color');
      return;
    }
    if (!selectedSize && !customSize) {
      Alert.alert('Missing Information', 'Please select a size or enter custom size');
      return;
    }

    const orderData = {
      pattern: selectedPattern?.name || 'Custom Design',
      color: selectedColor?.name,
      size: selectedSize?.label || `${customSize} meters`,
      description,
      uploadedImage,
      estimatedPrice: calculateEstimatedPrice(),
    };
    
    try {
      await saveCustomOrder(orderData);
      Alert.alert(
        'Order Submitted!',
        'Thank you! Your custom order request has been received. You can view its status in "Track Orders".',
        [{ text: 'View My Orders', onPress: () => navigation.navigate('TrackOrders') },
         { text: 'OK', onPress: () => navigation.goBack(), style: 'cancel' }]
      );
    } catch (error) {
      Alert.alert('Error', 'There was a problem submitting your custom order. Please try again.');
    }
  };

  return (
    <ScrollView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()}>
          <Text style={styles.backButton}>← Back</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Create Custom Order</Text>
      </View>

      {/* Live Preview */}
      <View style={styles.previewSection}>
        <Text style={styles.sectionTitle}>Live Preview</Text>
        <View style={styles.previewContainer}>
          {(selectedPattern || uploadedImage) ? (
            <View>
              <View style={styles.previewBox}>
                <Image
                  source={uploadedImage ? { uri: uploadedImage } : selectedPattern?.image}
                  style={styles.previewImage}
                  resizeMode="contain"
                />
                {selectedColor && (
                  <View style={[styles.colorOverlay, { backgroundColor: selectedColor.hex + '40' }]} />
                )}
              </View>
              <View style={styles.previewDetailsContainer}>
                {selectedPattern && (
                  <View style={styles.previewInfo}>
                    <Text style={styles.previewInfoText}>📋 Pattern: {selectedPattern.name}</Text>
                  </View>
                )}
                {uploadedImage && (
                  <View style={styles.previewInfo}>
                    <Text style={styles.previewInfoText}>✓ Custom Design Uploaded</Text>
                  </View>
                )}
                {selectedColor && (
                  <View style={styles.previewInfo}>
                    <View style={styles.previewColorIndicator}>
                      <View style={[styles.colorDot, { backgroundColor: selectedColor.hex }]} />
                      <Text style={styles.previewInfoText}>Color: {selectedColor.name}</Text>
                    </View>
                  </View>
                )}
                {selectedSize && (
                  <View style={styles.previewInfo}>
                    <Text style={styles.previewInfoText}>📏 Size: {selectedSize.label}</Text>
                  </View>
                )}
                {customSize && (
                  <View style={styles.previewInfo}>
                    <Text style={styles.previewInfoText}>📏 Size: {customSize} meters</Text>
                  </View>
                )}
              </View>
            </View>
          ) : (
            <View style={styles.previewPlaceholder}>
              <Text style={styles.placeholderText}>👇 Select a pattern or upload design</Text>
            </View>
          )}
        </View>
      </View>

      {/* Pattern Selection */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Choose Pattern</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false}>
          {PATTERNS.map((pattern) => (
            <TouchableOpacity
              key={pattern.id}
              style={[
                styles.patternCard,
                selectedPattern?.id === pattern.id && styles.selectedCard,
              ]}
              onPress={() => {
                setSelectedPattern(pattern);
                setUploadedImage(null);
              }}
            >
              <Image source={pattern.image} style={styles.patternImage} />
              <Text style={styles.patternName}>{pattern.name}</Text>
              <Text style={styles.patternPrice}>₱{pattern.basePrice}/meter</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {/* Upload Custom Design */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Or Upload Your Design</Text>
        <TouchableOpacity style={styles.uploadButton} onPress={pickImage}>
          <Text style={styles.uploadButtonText}>
            {uploadedImage ? '✓ Design Uploaded' : '📤 Upload Image'}
          </Text>
        </TouchableOpacity>
        {uploadedImage && (
          <Text style={styles.uploadNote}>Custom design selected</Text>
        )}
      </View>

      {/* Color Selection */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Select Color</Text>
        <View style={styles.colorGrid}>
          {COLORS.map((color) => (
            <TouchableOpacity
              key={color.id}
              style={[
                styles.colorBox,
                { backgroundColor: color.hex },
                selectedColor?.id === color.id && styles.selectedColor,
                color.hex === '#FFFFFF' && styles.whiteColorBorder,
              ]}
              onPress={() => setSelectedColor(color)}
            >
              {selectedColor?.id === color.id && (
                <Text style={[styles.checkmark, color.hex === '#000000' && { color: '#fff' }]}>✓</Text>
              )}
            </TouchableOpacity>
          ))}
        </View>
        {selectedColor && (
          <Text style={styles.selectedText}>Selected: {selectedColor.name}</Text>
        )}
      </View>

      {/* Size Selection */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Select Size</Text>
        <View style={styles.sizeGrid}>
          {SIZES.map((size) => (
            <TouchableOpacity
              key={size.id}
              style={[
                styles.sizeButton,
                selectedSize?.id === size.id && styles.selectedSize,
              ]}
              onPress={() => {
                setSelectedSize(size);
                setCustomSize('');
              }}
            >
              <Text style={[
                styles.sizeText,
                selectedSize?.id === size.id && styles.selectedSizeText,
              ]}>
                {size.label}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        <Text style={styles.orText}>Or enter custom size:</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter size in meters (e.g., 2.5)"
          keyboardType="decimal-pad"
          value={customSize}
          onChangeText={(text) => {
            setCustomSize(text);
            setSelectedSize(null);
          }}
        />
      </View>

      {/* Description */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Description (Optional)</Text>
        <TextInput
          style={[styles.input, styles.textArea]}
          placeholder="Describe any specific requirements or modifications you want..."
          multiline
          numberOfLines={4}
          value={description}
          onChangeText={setDescription}
        />
      </View>

      {/* Price Estimate */}
      <View style={styles.priceSection}>
        <View style={styles.priceBox}>
          <Text style={styles.priceLabel}>Estimated Price:</Text>
          <Text style={styles.priceAmount}>
            {calculateEstimatedPrice() > 0 ? `₱${calculateEstimatedPrice()}` : 'Select options'}
          </Text>
        </View>
        <Text style={styles.priceNote}>
          * Quotation required. Final price will be confirmed after design consultation.
        </Text>
      </View>

      {/* Submit Button */}
      <TouchableOpacity style={styles.submitButton} onPress={handleSubmitOrder}>
        <Text style={styles.submitButtonText}>Submit Custom Order</Text>
      </TouchableOpacity>

      <View style={styles.bottomSpace} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    backgroundColor: '#8B1A1A',
    padding: 20,
    paddingTop: 50,
  },
  backButton: {
    color: '#fff',
    fontSize: 16,
    marginBottom: 10,
  },
  headerTitle: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  previewSection: {
    backgroundColor: '#fff',
    padding: 20,
    marginBottom: 10,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  previewContainer: {
    alignItems: 'center',
    width: '100%',
  },
  previewBox: {
    width: '100%',
    height: 300,
    borderRadius: 12,
    overflow: 'hidden',
    position: 'relative',
    backgroundColor: '#f5f5f5',
    borderWidth: 1,
    borderColor: '#ddd',
    marginBottom: 15,
  },
  previewImage: {
    width: '100%',
    height: '100%',
  },
  colorOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  previewPlaceholder: {
    width: '100%',
    height: 300,
    borderRadius: 12,
    backgroundColor: '#f0f0f0',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#ddd',
    borderStyle: 'dashed',
    marginBottom: 15,
  },
  placeholderText: {
    color: '#999',
    fontSize: 16,
    textAlign: 'center',
  },
  previewDetailsContainer: {
    width: '100%',
    gap: 10,
  },
  previewInfo: {
    backgroundColor: '#f9f9f9',
    paddingHorizontal: 15,
    paddingVertical: 12,
    borderRadius: 8,
    borderLeftWidth: 4,
    borderLeftColor: '#8B1A1A',
  },
  previewColorIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  colorDot: {
    width: 18,
    height: 18,
    borderRadius: 9,
    borderWidth: 2,
    borderColor: '#ddd',
  },
  previewInfoText: {
    fontSize: 14,
    color: '#333',
    fontWeight: '600',
  },
  section: {
    backgroundColor: '#fff',
    padding: 20,
    marginBottom: 10,
  },
  patternCard: {
    width: 140,
    marginRight: 15,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: 'transparent',
    padding: 10,
    backgroundColor: '#f9f9f9',
  },
  selectedCard: {
    borderColor: '#8B1A1A',
    backgroundColor: '#ffe6e6',
  },
  patternImage: {
    width: 120,
    height: 120,
    borderRadius: 8,
    marginBottom: 8,
  },
  patternName: {
    fontSize: 13,
    fontWeight: '600',
    color: '#333',
    textAlign: 'center',
  },
  patternPrice: {
    fontSize: 12,
    color: '#8B1A1A',
    textAlign: 'center',
    marginTop: 4,
    fontWeight: '500',
  },
  uploadButton: {
    backgroundColor: '#4CAF50',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  uploadButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  uploadNote: {
    color: '#4CAF50',
    fontSize: 12,
    marginTop: 8,
    textAlign: 'center',
  },
  colorGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  colorBox: {
    width: 60,
    height: 60,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  selectedColor: {
    borderColor: '#8B1A1A',
    borderWidth: 3,
  },
  whiteColorBorder: {
    borderWidth: 1,
    borderColor: '#ddd',
  },
  checkmark: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#000',
  },
  selectedText: {
    marginTop: 10,
    fontSize: 14,
    color: '#8B1A1A',
    fontWeight: '600',
  },
  sizeGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  sizeButton: {
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#ddd',
    backgroundColor: '#fff',
  },
  selectedSize: {
    borderColor: '#8B1A1A',
    backgroundColor: '#ffe6e6',
  },
  sizeText: {
    fontSize: 14,
    color: '#666',
  },
  selectedSizeText: {
    color: '#8B1A1A',
    fontWeight: '600',
  },
  orText: {
    marginTop: 15,
    marginBottom: 10,
    fontSize: 14,
    color: '#666',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    backgroundColor: '#fff',
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  priceSection: {
    backgroundColor: '#fff',
    padding: 20,
    marginBottom: 10,
  },
  priceBox: {
    backgroundColor: '#fff5f5',
    padding: 20,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#8B1A1A',
    alignItems: 'center',
  },
  priceLabel: {
    fontSize: 16,
    color: '#666',
    marginBottom: 5,
  },
  priceAmount: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#8B1A1A',
  },
  priceNote: {
    fontSize: 12,
    color: '#666',
    marginTop: 10,
    fontStyle: 'italic',
    textAlign: 'center',
  },
  submitButton: {
    backgroundColor: '#8B1A1A',
    marginHorizontal: 20,
    padding: 18,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 20,
  },
  submitButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  bottomSpace: {
    height: 30,
  },
});

export default CustomOrderScreen;