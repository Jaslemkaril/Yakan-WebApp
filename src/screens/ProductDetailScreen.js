// src/screens/ProductDetailScreen.js
import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  Dimensions,
  Alert,
} from 'react-native';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import colors from '../constants/colors';
import API_CONFIG from '../config/config';

const { width } = Dimensions.get('window');

export default function ProductDetailScreen({ route, navigation }) {
  const { theme } = useTheme();
  const styles = getStyles(theme);
  const { product } = route.params || {};
  
  // All hooks MUST be called before any conditional return (React Rules of Hooks)
  const [quantity, setQuantity] = useState(1);
  const { addToCart, isLoggedIn, addToWishlist, removeFromWishlist, isInWishlist } = useCart();
  const [isFavorite, setIsFavorite] = useState(false);

  // Update isFavorite when product changes
  React.useEffect(() => {
    if (product?.id) {
      setIsFavorite(isInWishlist(product.id));
    }
  }, [product?.id, isInWishlist]);
  
  if (!product) {
    return (
      <View style={styles.container}>
        <Text style={styles.errorText}>Product not found</Text>
      </View>
    );
  }

  const increaseQuantity = () => {
    setQuantity(quantity + 1);
  };

  const decreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity(quantity - 1);
    }
  };

  const handleFavoriteToggle = async () => {
    if (!isLoggedIn) {
      Alert.alert('Login Required', 'Please login to manage your wishlist', [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Login', onPress: () => navigation.navigate('Auth', { screen: 'Login' }) },
      ]);
      return;
    }
    try {
      if (isFavorite) {
        await removeFromWishlist(product.id);
      } else {
        await addToWishlist(product);
      }
      setIsFavorite(!isFavorite);
    } catch (error) {
      Alert.alert('Error', 'Failed to update wishlist. Please try again.');
    }
  };

  const handleAddToCart = async () => {
    if (!isLoggedIn) {
      Alert.alert('Login Required', 'Please login to add items to cart', [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Login', onPress: () => navigation.navigate('Auth', { screen: 'Login' }) },
      ]);
      return;
    }
    
    try {
      await addToCart(product, quantity);
      Alert.alert('Success', `${product.name} added to cart!`, [
        { text: 'Continue Shopping', onPress: () => navigation.goBack() },
        { text: 'View Cart', onPress: () => navigation.navigate('Cart') },
      ]);
    } catch (error) {
      Alert.alert('Error', 'Failed to add item to cart. Please try again.');
    }
  };

  const handleBuyNow = async () => {
    if (!isLoggedIn) {
      Alert.alert('Login Required', 'Please login to proceed', [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Login', onPress: () => navigation.navigate('Auth', { screen: 'Login' }) },
      ]);
      return;
    }
    
    try {
      await addToCart(product, quantity);
      navigation.navigate('Cart');
    } catch (error) {
      Alert.alert('Error', 'Failed to add item to cart. Please try again.');
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      {/* Header */}
      <ScreenHeader 
        title="Product Details" 
        navigation={navigation}
        showBack={true}
        showHamburger={true}
        rightIcon={
          <TouchableOpacity 
            style={styles.favoriteButton}
            onPress={handleFavoriteToggle}
          >
            <MaterialCommunityIcons 
              name={isFavorite ? "heart" : "heart-outline"} 
              size={28} 
              color={isFavorite ? "#FF6B6B" : "#ccc"} 
            />
          </TouchableOpacity>
        }
        onRightIconPress={handleFavoriteToggle}
      />

      <ScrollView style={styles.content}>
        {/* Product Image */}
        <View style={styles.imageContainer}>
          <Image 
            source={
              product.image 
                ? (typeof product.image === 'object' && product.image.uri)
                  ? product.image  // Already transformed with {uri: ...}
                  : { uri: product.image.startsWith('http')
                      ? product.image
                      : product.image.startsWith('/uploads') || product.image.startsWith('/storage')
                        ? `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}${product.image}`
                        : `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}/uploads/products/${product.image}`
                    }
                : require('../assets/images/Saputangan.jpg')
            }
            style={styles.productImage}
            resizeMode="cover"
            onError={(error) => {
              console.log('[ProductDetail] Image load error:', error);
              console.log('[ProductDetail] Product image:', product.image);
            }}
          />
        </View>

        {/* Product Info */}
        <View style={styles.infoContainer}>
          <Text style={styles.productName}>{product.name}</Text>
          <Text style={styles.productPrice}>₱{parseFloat(product.price || 0).toFixed(2)}</Text>
          
          {/* Rating */}
          <View style={styles.ratingContainer}>
            <MaterialCommunityIcons name="star" size={20} color="#FFB800" />
            <MaterialCommunityIcons name="star" size={20} color="#FFB800" style={{ marginLeft: 4 }} />
            <MaterialCommunityIcons name="star" size={20} color="#FFB800" style={{ marginLeft: 4 }} />
            <MaterialCommunityIcons name="star" size={20} color="#FFB800" style={{ marginLeft: 4 }} />
            <MaterialCommunityIcons name="star" size={20} color="#FFB800" style={{ marginLeft: 4 }} />
            <Text style={styles.ratingText}>(4.8) 120 reviews</Text>
          </View>

          {/* Description */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Description</Text>
            <Text style={styles.description}>{product.description}</Text>
            <Text style={styles.descriptionExtra}>
              This traditional Yakan weaving represents centuries of cultural heritage. 
              Each piece is handwoven by skilled artisans using traditional techniques 
              passed down through generations. The intricate patterns and vibrant colors 
              make each fabric unique and special.
            </Text>
          </View>

          {/* Details */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>
              <MaterialCommunityIcons name="information-outline" size={20} color="#8B1A1A" style={{ marginRight: 8 }} />
              Details
            </Text>
            <View style={styles.detailRow}>
              <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                <MaterialCommunityIcons name="palette" size={18} color="#8B1A1A" style={{ marginRight: 8 }} />
                <Text style={styles.detailLabel}>Material:</Text>
              </View>
              <Text style={styles.detailValue}>100% Cotton</Text>
            </View>
            <View style={styles.detailRow}>
              <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                <MaterialCommunityIcons name="ruler" size={18} color="#3498DB" style={{ marginRight: 8 }} />
                <Text style={styles.detailLabel}>Size:</Text>
              </View>
              <Text style={styles.detailValue}>42" x 42"</Text>
            </View>
            <View style={styles.detailRow}>
              <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                <MaterialCommunityIcons name="map-marker" size={18} color="#27AE60" style={{ marginRight: 8 }} />
                <Text style={styles.detailLabel}>Origin:</Text>
              </View>
              <Text style={styles.detailValue}>Basilan, Philippines</Text>
            </View>
            <View style={[styles.detailRow, { borderBottomWidth: 0 }]}>
              <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                <MaterialCommunityIcons name="water" size={18} color="#E74C3C" style={{ marginRight: 8 }} />
                <Text style={styles.detailLabel}>Care:</Text>
              </View>
              <Text style={styles.detailValue}>Hand wash only</Text>
            </View>
          </View>

          {/* Quantity Selector */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>
              <MaterialCommunityIcons name="counter" size={20} color="#8B1A1A" style={{ marginRight: 8 }} />
              Quantity
            </Text>
            <View style={styles.quantityContainer}>
              <TouchableOpacity 
                style={styles.quantityButton}
                onPress={decreaseQuantity}
              >
                <Text style={styles.quantityButtonText}>-</Text>
              </TouchableOpacity>
              
              <Text style={styles.quantityText}>{quantity}</Text>
              
              <TouchableOpacity 
                style={styles.quantityButton}
                onPress={increaseQuantity}
              >
                <Text style={styles.quantityButtonText}>+</Text>
              </TouchableOpacity>
            </View>
          </View>

          {/* Total Price */}
          <View style={styles.totalContainer}>
            <Text style={styles.totalLabel}>Total Price:</Text>
            <Text style={styles.totalPrice}>
              ₱{(parseFloat(product.price || 0) * quantity).toFixed(2)}
            </Text>
          </View>
        </View>
      </ScrollView>

      {/* Bottom Action Buttons */}
      <View style={styles.bottomActions}>
        <TouchableOpacity 
          style={styles.addToCartButton}
          onPress={handleAddToCart}
        >
          <MaterialCommunityIcons name="shopping" size={20} color="#8B1A1A" style={{ marginRight: 8 }} />
          <Text style={styles.addToCartText}>Add to Cart</Text>
        </TouchableOpacity>
        
        <TouchableOpacity 
          style={styles.buyNowButton}
          onPress={handleBuyNow}
        >
          <MaterialCommunityIcons name="flash" size={20} color="#fff" style={{ marginRight: 8 }} />
          <Text style={styles.buyNowText}>Buy Now</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
  },
  errorText: {
    fontSize: 18,
    color: theme.text,
    textAlign: 'center',
    marginTop: 100,
  },
  favoriteButton: {
    marginRight: 8,
    padding: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    flex: 1,
  },
  imageContainer: {
    width: width,
    height: width,
    backgroundColor: theme.cardBackground,
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  infoContainer: {
    padding: 20,
    backgroundColor: theme.cardBackground,
  },
  productName: {
    fontSize: 28,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 12,
    lineHeight: 36,
  },
  productPrice: {
    fontSize: 32,
    fontWeight: '700',
    color: theme.primary,
    marginBottom: 15,
  },
  ratingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  stars: {
    fontSize: 16,
    marginRight: 10,
  },
  ratingText: {
    fontSize: 14,
    color: theme.textMuted,
    marginLeft: 12,
  },
  section: {
    marginBottom: 25,
    paddingHorizontal: 20,
    paddingVertical: 15,
    backgroundColor: theme.cardBackground,
    marginHorizontal: 12,
    borderRadius: 12,
    marginTop: 8,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 12,
    flexDirection: 'row',
    alignItems: 'center',
  },
  description: {
    fontSize: 15,
    color: theme.textSecondary,
    lineHeight: 24,
    marginBottom: 10,
  },
  descriptionExtra: {
    fontSize: 14,
    color: theme.textMuted,
    lineHeight: 21,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: theme.borderLight,
  },
  detailLabel: {
    fontSize: 15,
    color: theme.textMuted,
    flex: 0.5,
    flexDirection: 'row',
    alignItems: 'center',
  },
  detailValue: {
    fontSize: 15,
    color: theme.text,
    fontWeight: '600',
    flex: 0.5,
    textAlign: 'right',
  },
  quantityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: theme.surfaceBg,
    borderRadius: 12,
    padding: 12,
    borderWidth: 2,
    borderColor: theme.primary,
    marginVertical: 15,
  },
  quantityButton: {
    width: 40,
    height: 40,
    backgroundColor: theme.primary,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  quantityButtonText: {
    fontSize: 24,
    color: '#fff',
    fontWeight: 'bold',
  },
  quantityText: {
    fontSize: 24,
    fontWeight: 'bold',
    color: theme.text,
    marginHorizontal: 30,
    minWidth: 40,
    textAlign: 'center',
  },
  totalContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: theme.dangerBg,
    padding: 16,
    borderRadius: 12,
    marginTop: 12,
    borderLeftWidth: 4,
    borderLeftColor: theme.primary,
  },
  totalLabel: {
    fontSize: 16,
    color: theme.textSecondary,
    fontWeight: '600',
  },
  totalPrice: {
    fontSize: 28,
    fontWeight: '700',
    color: theme.primary,
  },
  bottomActions: {
    flexDirection: 'row',
    padding: 16,
    paddingBottom: 28,
    backgroundColor: theme.cardBackground,
    borderTopWidth: 1,
    borderTopColor: theme.borderLight,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -3 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 5,
  },
  addToCartButton: {
    flex: 1,
    flexDirection: 'row',
    backgroundColor: theme.cardBackground,
    borderWidth: 2,
    borderColor: theme.primary,
    borderRadius: 12,
    paddingVertical: 14,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  addToCartIcon: {
    fontSize: 20,
    marginRight: 8,
  },
  addToCartText: {
    fontSize: 16,
    fontWeight: '700',
    color: theme.primary,
  },
  buyNowButton: {
    flex: 1,
    flexDirection: 'row',
    backgroundColor: theme.primary,
    borderRadius: 12,
    paddingVertical: 14,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 5,
  },
  buyNowText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#fff',
  },
});