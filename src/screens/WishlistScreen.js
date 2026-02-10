import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  Dimensions,
  Alert,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { useCart } from '../context/CartContext';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import colors from '../constants/colors';
import BottomNav from '../components/BottomNav';
import ApiService from '../services/api';
import API_CONFIG from '../config/config';

const { width } = Dimensions.get('window');

export default function WishlistScreen({ navigation }) {
  const { theme } = useTheme();
  const styles = getStyles(theme);
  const { addToCart, isLoggedIn, wishlistItems, removeFromWishlist, fetchWishlist } = useCart();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    setLoading(false);
  }, []);

  // Refresh wishlist when screen comes into focus
  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      console.log('[WishlistScreen] Screen focused, refreshing wishlist');
      if (isLoggedIn) {
        fetchWishlist();
      }
    });
    
    return unsubscribe;
  }, [navigation, isLoggedIn]);

  const handleRefresh = async () => {
    setRefreshing(true);
    if (isLoggedIn) {
      await fetchWishlist();
    }
    setRefreshing(false);
  };

  const handleRemoveFromWishlist = async (productId) => {
    try {
      await removeFromWishlist(productId);
      Alert.alert('Success', 'Item removed from wishlist');
    } catch (error) {
      console.error('[Wishlist] Remove error:', error);
      Alert.alert('Error', 'Failed to remove item');
    }
  };

  const handleAddToCart = (product) => {
    if (!isLoggedIn) {
      Alert.alert('Login Required', 'Please login to add items to cart', [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Login', onPress: () => navigation.navigate('Login') },
      ]);
      return;
    }

    addToCart(product, 1);
    Alert.alert('Success', `${product.name} added to cart!`, [
      { text: 'Continue Shopping', onPress: () => {} },
      { text: 'View Cart', onPress: () => navigation.navigate('Cart') },
    ]);
  };

  if (loading) {
    return (
      <View style={styles.container}>
        <ScreenHeader 
          title="Wishlist" 
          navigation={navigation}
          showBack={false}
          showHamburger={true}
        />

        <View style={styles.emptyContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.emptySubtext}>Loading wishlist...</Text>
        </View>

        <BottomNav navigation={navigation} />
      </View>
    );
  }

  if (wishlistItems.length === 0) {
    return (
      <View style={styles.container}>
        <ScreenHeader 
          title="Wishlist" 
          navigation={navigation}
          showBack={false}
          showHamburger={true}
        />

        <View style={styles.emptyContainer}>
          <Text style={styles.emptyIcon}>♡</Text>
          <Text style={styles.emptyText}>Your wishlist is empty</Text>
          <Text style={styles.emptySubtext}>Start adding your favorite items</Text>
          <TouchableOpacity
            style={styles.shopButton}
            onPress={() => navigation.navigate('Products')}
          >
            <Text style={styles.shopButtonText}>Start Shopping</Text>
          </TouchableOpacity>
        </View>

        <BottomNav navigation={navigation} />
      </View>
    );
  }

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title={`Wishlist (${wishlistItems.length})`} 
        navigation={navigation}
        showBack={false}
        showHamburger={true}
      />

      <ScrollView 
        style={styles.content} 
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            colors={[colors.primary]}
          />
        }
      >
        {wishlistItems.map((product) => {
          // Handle image URL - use /uploads/products/ path
          const imgStr = typeof product.image === 'string' ? product.image : '';
          const imageUri = imgStr.startsWith('http') 
            ? imgStr 
            : imgStr.startsWith('/uploads') || imgStr.startsWith('/storage')
              ? `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}${imgStr}`
              : imgStr
                ? `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}/uploads/products/${imgStr}`
                : null;

          return (
            <View key={product.id} style={styles.wishlistItem}>
              <Image
                source={{ uri: imageUri }}
                style={styles.productImage}
                resizeMode="cover"
              />

              <View style={styles.productInfo}>
                <Text style={styles.productName} numberOfLines={2}>
                  {product.name || 'Unknown Product'}
                </Text>
                <Text style={styles.productDescription} numberOfLines={2}>
                  {product.description || ''}
                </Text>
                <Text style={styles.productPrice}>
                  ₱{(product.price || 0).toFixed(2)}
                </Text>

                <View style={styles.actionButtons}>
                  <TouchableOpacity
                    style={styles.addToCartBtn}
                    onPress={() => handleAddToCart(product)}
                  >
                    <Text style={styles.addToCartText}>Add to Cart</Text>
                  </TouchableOpacity>

                  <TouchableOpacity
                    style={styles.removeBtn}
                    onPress={() => handleRemoveFromWishlist(product.id)}
                  >
                    <Text style={styles.removeText}>Remove</Text>
                  </TouchableOpacity>
                </View>
              </View>
            </View>
          );
        })}
      </ScrollView>

      <BottomNav navigation={navigation} />
    </View>
  );
}

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
    marginTop: 8,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: theme.text,
  },
  backButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },
  backIcon: {
    fontSize: 24,
    color: theme.text,
  },
  content: {
    flex: 1,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingBottom: 80,
  },
  emptyIcon: {
    fontSize: 80,
    marginBottom: 16,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '600',
    color: theme.text,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 14,
    color: theme.textMuted,
    marginBottom: 24,
  },
  shopButton: {
    backgroundColor: theme.primary,
    paddingHorizontal: 32,
    paddingVertical: 12,
    borderRadius: 8,
  },
  shopButtonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 16,
  },
  wishlistItem: {
    flexDirection: 'row',
    backgroundColor: theme.cardBackground,
    borderRadius: 8,
    marginBottom: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: theme.border,
  },
  productImage: {
    width: 100,
    height: 100,
    borderRadius: 8,
    marginRight: 12,
  },
  productInfo: {
    flex: 1,
    justifyContent: 'space-between',
  },
  productName: {
    fontSize: 14,
    fontWeight: '600',
    color: theme.text,
    marginBottom: 4,
  },
  productDescription: {
    fontSize: 12,
    color: theme.textSecondary,
    marginBottom: 8,
  },
  productPrice: {
    fontSize: 16,
    fontWeight: '700',
    color: theme.primary,
    marginBottom: 8,
  },
  actionButtons: {
    flexDirection: 'row',
    gap: 8,
  },
  addToCartBtn: {
    flex: 1,
    backgroundColor: theme.primary,
    paddingVertical: 8,
    borderRadius: 6,
    alignItems: 'center',
  },
  addToCartText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 12,
  },
  removeBtn: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: theme.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  removeText: {
    color: theme.textSecondary,
    fontWeight: '600',
    fontSize: 12,
  },
});
