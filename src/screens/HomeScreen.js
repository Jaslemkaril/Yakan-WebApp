// src/screens/HomeScreen.js
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Alert,
  Dimensions,
  Image,
  ImageBackground,
  ActivityIndicator,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';
import BottomNav from '../components/BottomNav';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import colors from '../constants/colors';
import ApiService from '../services/api';
import API_CONFIG from '../config/config';

const { width } = Dimensions.get('window');

export default function HomeScreen({ navigation }) {
  const [searchQuery, setSearchQuery] = useState('');
  const [isSearchFocused, setIsSearchFocused] = useState(false);
  const [products, setProducts] = useState([]);
  const [featuredProducts, setFeaturedProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [menuOpen, setMenuOpen] = useState(false);
  const { getCartCount, isLoggedIn, addToWishlist, removeFromWishlist, isInWishlist } = useCart();
  const { theme } = useTheme();
  const styles = getStyles(theme);

  // Fetch products from API on component mount
  useEffect(() => {
    fetchProducts();
    fetchFeaturedProducts();
  }, []);

  const fetchFeaturedProducts = async () => {
    try {
      console.log('🏠 HomeScreen: Fetching featured products...');
      const response = await ApiService.request('GET', '/products?featured=true&limit=6');
      
      if (!response.success) {
        console.warn('🏠 Featured products not available, using first 3 products');
        return;
      }
      
      const productsData = response.data?.data || response.data || [];
      console.log('🏠 HomeScreen: Fetched', productsData.length, 'featured products');
      
      const transformedProducts = transformProducts(productsData);
      setFeaturedProducts(transformedProducts);
    } catch (error) {
      console.error('🏠 HomeScreen: Error fetching featured products:', error);
    }
  };

  const transformProducts = (productsData) => {
    return productsData.map(product => ({
      id: product.id,
      name: product.name,
      description: product.description,
      price: parseFloat(product.price),
      category: product.category?.name || 'Uncategorized',
      image: product.image 
        ? { uri: product.image.startsWith('http') 
            ? product.image 
            : product.image.startsWith('/uploads') || product.image.startsWith('/storage')
              ? `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}${product.image}`
              : `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}/storage/products/${product.image}` 
          }
        : require('../assets/images/Saputangan.jpg'),
      stock: product.stock || 0,
    }));
  };

  const fetchProducts = async () => {
    try {
      setLoading(true);
      console.log('🏠 HomeScreen: Starting fetchProducts...');
      console.log('🏠 HomeScreen: API Base URL:', API_CONFIG.API_BASE_URL);
      
      const response = await ApiService.getProducts();
      console.log('🏠 HomeScreen: API response received:', response?.success);
      
      if (!response.success) {
        console.warn('🏠 HomeScreen: API returned error:', response.error);
        console.warn('🏠 HomeScreen: Using fallback mock data');
        // Don't throw - use fallback data instead
        setProducts(getMockProducts());
        return;
      }
      
      const productsData = response.data?.data || response.data || [];
      console.log('🏠 HomeScreen: Fetched', productsData.length, 'products');
      
      const transformedProducts = transformProducts(productsData);
      setProducts(transformedProducts);
      
      // If no featured products yet, use first 3 from all products
      if (featuredProducts.length === 0 && transformedProducts.length > 0) {
        setFeaturedProducts(transformedProducts.slice(0, 3));
      }
      console.log('🏠 HomeScreen: Products loaded successfully');
    } catch (error) {
      console.error('🏠 HomeScreen: Error fetching products:', error.message);
      console.log('🏠 HomeScreen: Using fallback mock data due to error');
      // Fallback to mock data
      setProducts(getMockProducts());
    } finally {
      setLoading(false);
    }
  };

  const getMockProducts = () => {
    return [
      {
        id: 1,
        name: 'Yakan Traditional Dress',
        description: 'Beautiful handwoven traditional Yakan dress with intricate patterns',
        price: 2500.00,
        image: require('../assets/images/Saputangan.jpg'),
      },
      {
        id: 2,
        name: 'Yakan Headwrap',
        description: 'Traditional Yakan headwrap with authentic patterns',
        price: 450.00,
        image: require('../assets/images/pinantupan.jpg'),
      },
      {
        id: 3,
        name: 'Yakan Wall Hanging',
        description: 'Decorative wall hanging featuring traditional Yakan weaving',
        price: 1200.00,
        image: require('../assets/images/Patterns.jpg'),
      },
    ];
  };
  
  const handleMenuPress = () => {
    setMenuOpen(true);
  };

  const handleMenuClose = () => {
    setMenuOpen(false);
  };

  const handleMenuNavigation = (screen) => {
    setMenuOpen(false);
    navigation.navigate(screen);
  };

  const handleLogout = () => {
    setMenuOpen(false);
    Alert.alert('Logout', 'Are you sure you want to logout?', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Logout', style: 'destructive', onPress: () => {
        // Clear user data and navigate to login
        navigation.navigate('Login');
      }},
    ]);
  };
  
  // Show loading state while fetching
  if (loading) {
    return (
      <View style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={theme.primary} />
          <Text style={styles.loadingText}>Loading products...</Text>
        </View>
        <BottomNav navigation={navigation} activeRoute="Home" />
      </View>
    );
  }

  const toggleFavorite = (product) => {
    if (!isLoggedIn) {
      Alert.alert('Login Required', 'Please login to add items to wishlist', [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Login', onPress: () => navigation.navigate('Login') },
      ]);
      return;
    }

    if (isInWishlist(product.id)) {
      removeFromWishlist(product.id);
    } else {
      addToWishlist(product);
    }
  };

  const handleAddToCart = (product) => {
    if (!isLoggedIn) {
      Alert.alert(
        'Login Required',
        'Please login to add items to your cart',
        [
          { text: 'Cancel', style: 'cancel' },
          { text: 'Login', onPress: () => navigation.navigate('Login') },
        ]
      );
      return;
    }
    navigation.navigate('ProductDetail', { product });
  };

  const renderFeaturedCard = (product) => (
    <TouchableOpacity
      key={product.id}
      style={styles.featuredCard}
      onPress={() => navigation.navigate('ProductDetail', { product })}
      activeOpacity={0.7}
    >
      <View style={styles.featuredImageContainer}>
        <Image 
          source={product.image}
          style={styles.featuredImage}
          resizeMode="cover"
        />
        <TouchableOpacity
          style={styles.featuredFavoriteButton}
          onPress={() => toggleFavorite(product)}
        >
          <Ionicons 
            name={isInWishlist(product.id) ? "heart" : "heart-outline"} 
            size={24} 
            color={isInWishlist(product.id) ? "#FF6B6B" : "#999"} 
          />
        </TouchableOpacity>
      </View>
      <View style={styles.featuredInfo}>
        <Text style={styles.featuredName}>{product.name}</Text>
        <Text style={styles.featuredPrice}>₱{product.price.toFixed(2)}</Text>
      </View>
    </TouchableOpacity>
  );

  const renderProductCard = (product) => (
    <TouchableOpacity
      key={product.id}
      style={styles.productCard}
      onPress={() => navigation.navigate('ProductDetail', { product })}
      activeOpacity={0.7}
    >
      <View style={styles.productImageContainer}>
        <Image 
          source={product.image}
          style={styles.productImage}
          resizeMode="cover"
        />
        <TouchableOpacity
          style={styles.favoriteButton}
          onPress={() => toggleFavorite(product)}
        >
          <Ionicons 
            name={isInWishlist(product.id) ? "heart" : "heart-outline"} 
            size={24} 
            color={isInWishlist(product.id) ? "#FF6B6B" : "#999"} 
          />
        </TouchableOpacity>
      </View>
      <View style={styles.productInfo}>
        <Text style={styles.productName}>{product.name}</Text>
        <Text style={styles.productDescription} numberOfLines={2}>
          {product.description}
        </Text>
        <View style={styles.productFooter}>
          <Text style={styles.productPrice}>₱{product.price.toFixed(2)}</Text>
          <TouchableOpacity
            style={styles.cartButton}
            onPress={() => handleAddToCart(product)}
          >
            <Ionicons name="cart" size={20} color="#fff" />
          </TouchableOpacity>
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="TUWAS YAKAN" 
        navigation={navigation} 
        showBack={false}
        rightIcon={<Ionicons name="cart" size={24} color="#fff" />}
        onRightIconPress={() => navigation.navigate('Cart')}
        showCartCount={true}
        cartCount={getCartCount()}
      />
      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* HERO SECTION */}
        <View style={styles.heroSection}>
          <ImageBackground
            source={require('../assets/images/TUWASYAKAN.jpg')}
            style={styles.heroBackground}
            resizeMode="cover"
          >
            <View style={styles.heroOverlay}>
              <View style={[styles.heroSearchContainer, isSearchFocused && styles.heroSearchContainerFocused]}>
                <Ionicons name="search" size={20} color={isSearchFocused ? "#fff" : "rgba(255,255,255,0.8)"} style={styles.heroSearchIcon} />
                <TextInput
                  style={styles.heroSearchInput}
                  placeholder="Search products..."
                  placeholderTextColor="rgba(255,255,255,0.7)"
                  value={searchQuery}
                  onChangeText={setSearchQuery}
                  onFocus={() => setIsSearchFocused(true)}
                  onBlur={() => setIsSearchFocused(false)}
                />
                {searchQuery.length > 0 && (
                  <TouchableOpacity onPress={() => setSearchQuery('')} style={styles.clearButtonHero}>
                    <Ionicons name="close-circle" size={20} color="rgba(255,255,255,0.8)" />
                  </TouchableOpacity>
                )}
              </View>

              <View style={styles.logoContainer}>
                <Text style={styles.logoMain}>TUWAS</Text>
                <Text style={styles.logoSub}>#YAKAN</Text>
                <Text style={styles.tagline}>weaving through generations</Text>
              </View>
            </View>
          </ImageBackground>
        </View>

        {/* FEATURED FABRICS SECTION */}
        <View style={styles.featuredSection}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Featured Fabrics</Text>
            <TouchableOpacity onPress={() => navigation.navigate('Products')}>
              <Text style={styles.seeAllText}>See All →</Text>
            </TouchableOpacity>
          </View>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.featuredScroll}
          >
            {featuredProducts.length > 0 ? (
              featuredProducts.map(product => renderFeaturedCard(product))
            ) : (
              <Text style={styles.noProductsText}>No featured products available</Text>
            )}
          </ScrollView>
        </View>

        {/* SHOP ALL PRODUCTS SECTION */}
        {products.length > 0 && (
          <View style={styles.productsSection}>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Shop All Products</Text>
              <TouchableOpacity onPress={() => navigation.navigate('Products')}>
                <Text style={styles.seeAllText}>View All →</Text>
              </TouchableOpacity>
            </View>
            <View style={styles.productsGrid}>
              {products.slice(0, 6).map(product => renderProductCard(product))}
            </View>
            <TouchableOpacity 
              style={styles.viewAllButton}
              onPress={() => navigation.navigate('Products')}
            >
              <Text style={styles.viewAllButtonText}>Browse All Products</Text>
              <Ionicons name="arrow-forward" size={20} color={'#fff'} />
            </TouchableOpacity>
          </View>
        )}

        {/* CULTURAL HERITAGE SECTION */}
        <View style={styles.culturalSection}>
          <Text style={styles.sectionTitle}>Cultural Heritage</Text>
          
          <View style={styles.culturalImageGrid}>
            {/* Main Image */}
            <View style={styles.culturalMainImage}>
              <Image 
                source={require('../assets/images/Weaving.jpg')}
                style={styles.culturalImage}
                resizeMode="cover"
              />
            </View>
            
            {/* Two smaller images */}
            <View style={styles.culturalSmallImages}>
              <View style={styles.culturalSmallImage}>
                <Image 
                  source={require('../assets/images/philippines.jpg')}
                  style={styles.culturalImage}
                  resizeMode="cover"
                />
              </View>
              <View style={styles.culturalSmallImage}>
                <Image 
                  source={require('../assets/images/Cultural.jpg')}
                  style={styles.culturalImage}
                  resizeMode="cover"
                />
              </View>
            </View>
          </View>

          <View style={styles.culturalContent}>
            <Text style={styles.culturalText}>
              The Yakan people of Basilan have preserved their weaving traditions for centuries. Each pattern and color combination carries deep cultural significance, representing stories, beliefs, and the identity woven around them.
            </Text>
            
            <Text style={styles.culturalText}>
              Our artisans continue this legacy, creating contemporary pieces while honoring traditional techniques passed down through generations.
            </Text>
            
            <Text style={styles.culturalSubtitle}>Traditional Patterns</Text>
            <Text style={styles.culturalText}>
              Geometric designs representing nature, ancestry, and spiritual beliefs
            </Text>
            
            <TouchableOpacity 
              style={styles.learnMoreButton}
              onPress={() => navigation.navigate('CulturalHeritage')}
            >
              <Text style={styles.learnMoreText}>Learn More</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>

      {/* BOTTOM NAVIGATION */}
      <BottomNav navigation={navigation} activeRoute="Home" />
    </View>
  );
}

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: theme.background,
  },
  loadingText: {
    marginTop: 15,
    fontSize: 16,
    color: theme.text,
  },
  scrollView: {
    flex: 1,
  },
  // HERO SECTION
  heroSection: {
    width: '100%',
    height: 400,
    marginBottom: 20,
  },
  heroBackground: {
    width: '100%',
    height: '100%',
  },
  heroOverlay: {
    flex: 1,
    backgroundColor: 'rgba(139, 26, 26, 0.7)',
    paddingTop: 50,
    paddingHorizontal: 20,
  },
  topBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  menuButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 22,
  },
  heroCartButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 22,
    position: 'relative',
  },
  heroBadge: {
    position: 'absolute',
    top: -5,
    right: -5,
    backgroundColor: 'red',
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  heroBadgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: 'bold',
  },
  heroSearchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 30,
    paddingHorizontal: 20,
    paddingVertical: 14,
    marginBottom: 40,
    borderWidth: 2,
    borderColor: 'rgba(255,255,255,0.35)',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  heroSearchContainerFocused: {
    backgroundColor: 'rgba(255,255,255,0.3)',
    borderColor: 'rgba(255,255,255,0.6)',
    shadowOpacity: 0.3,
    shadowRadius: 12,
    elevation: 6,
  },
  heroSearchIcon: {
    marginRight: 12,
  },
  heroSearchInput: {
    flex: 1,
    fontSize: 16,
    color: '#fff',
    paddingVertical: 0,
  },
  clearButtonHero: {
    padding: 4,
    marginLeft: 8,
  },
  logoContainer: {
    alignItems: 'center',
    marginTop: 20,
  },
  logoMain: {
    fontSize: 52,
    fontWeight: 'bold',
    color: '#fff',
    letterSpacing: 4,
  },
  logoSub: {
    fontSize: 40,
    fontWeight: 'bold',
    color: '#fff',
    letterSpacing: 3,
    marginTop: -5,
  },
  tagline: {
    fontSize: 16,
    color: '#fff',
    marginTop: 8,
    fontStyle: 'italic',
    letterSpacing: 1,
  },
  // FEATURED SECTION
  featuredSection: {
    marginBottom: 25,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginHorizontal: 20,
    marginBottom: 15,
  },
  sectionTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: theme.text,
  },
  seeAllText: {
    fontSize: 14,
    color: theme.primary,
    fontWeight: '600',
  },
  noProductsText: {
    fontSize: 14,
    color: theme.textSecondary,
    marginLeft: 20,
    fontStyle: 'italic',
  },
  featuredScroll: {
    paddingHorizontal: 15,
  },
  featuredCard: {
    width: 220,
    backgroundColor: theme.cardBackground,
    borderRadius: 15,
    marginHorizontal: 5,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.15,
    shadowRadius: 5,
    elevation: 4,
  },
  featuredImageContainer: {
    position: 'relative',
    width: '100%',
    height: 200,
  },
  featuredImage: {
    width: '100%',
    height: '100%',
  },
  featuredFavoriteButton: {
    position: 'absolute',
    top: 10,
    right: 10,
    backgroundColor: theme.cardBackground,
    width: 38,
    height: 38,
    borderRadius: 19,
    justifyContent: 'center',
    alignItems: 'center',
  },
  featuredInfo: {
    padding: 15,
  },
  featuredName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: theme.text,
    marginBottom: 8,
  },
  featuredPrice: {
    fontSize: 20,
    fontWeight: 'bold',
    color: theme.primary,
  },
  // SHOP ALL PRODUCTS SECTION
  productsSection: {
    marginBottom: 25,
    paddingHorizontal: 20,
  },
  productsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  viewAllButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: theme.primary,
    paddingVertical: 14,
    paddingHorizontal: 24,
    borderRadius: 10,
    marginTop: 15,
    gap: 8,
  },
  viewAllButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  // CULTURAL HERITAGE SECTION
  culturalSection: {
    marginBottom: 25,
    paddingHorizontal: 20,
  },
  culturalImageGrid: {
    flexDirection: 'row',
    marginBottom: 20,
    height: 220,
  },
  culturalMainImage: {
    flex: 2,
    marginRight: 8,
    borderRadius: 15,
    overflow: 'hidden',
  },
  culturalSmallImages: {
    flex: 1,
    justifyContent: 'space-between',
  },
  culturalSmallImage: {
    height: '48%',
    borderRadius: 15,
    overflow: 'hidden',
  },
  culturalImage: {
    width: '100%',
    height: '100%',
  },
  culturalContent: {
    backgroundColor: theme.cardBackground,
    padding: 20,
    borderRadius: 15,
  },
  culturalText: {
    fontSize: 14,
    color: theme.text,
    lineHeight: 22,
    marginBottom: 15,
  },
  culturalSubtitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: theme.text,
    marginTop: 10,
    marginBottom: 10,
  },
  learnMoreButton: {
    marginTop: 10,
    borderWidth: 2,
    borderColor: theme.primary,
    borderRadius: 8,
    paddingVertical: 10,
    paddingHorizontal: 20,
    alignSelf: 'flex-start',
  },
  learnMoreText: {
    color: theme.primary,
    fontSize: 15,
    fontWeight: '600',
  },
  // PRODUCT CARD
  productCard: {
    width: '48%',
    backgroundColor: theme.cardBackground,
    borderRadius: 15,
    marginBottom: 15,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  productImageContainer: {
    position: 'relative',
    width: '100%',
    height: 150,
  },
  productImage: {
    width: '100%',
    height: '100%',
  },
  favoriteButton: {
    position: 'absolute',
    top: 10,
    right: 10,
    backgroundColor: theme.cardBackground,
    width: 35,
    height: 35,
    borderRadius: 17.5,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.2,
    shadowRadius: 2,
    elevation: 2,
  },
  productInfo: {
    padding: 12,
  },
  productName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: theme.text,
    marginBottom: 5,
  },
  productDescription: {
    fontSize: 12,
    color: theme.textSecondary,
    marginBottom: 10,
    lineHeight: 16,
  },
  productFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 5,
  },
  productPrice: {
    fontSize: 18,
    fontWeight: 'bold',
    color: theme.text,
  },
  cartButton: {
    backgroundColor: theme.text,
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  // MENU MODAL STYLES
  menuOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.4)',
    justifyContent: 'flex-start',
  },
  menuContainer: {
    width: '72%',
    height: '100%',
    backgroundColor: theme.surfaceBg,
    paddingTop: 50,
    shadowColor: '#000',
    shadowOffset: { width: 3, height: 0 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 12,
  },
  menuHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingBottom: 20,
    borderBottomWidth: 1,
    borderBottomColor: theme.borderLight,
  },
  menuTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: theme.text,
    letterSpacing: 0.5,
  },
  menuCloseIcon: {
    fontSize: 24,
    color: theme.textMuted,
    fontWeight: '300',
  },
  menuContent: {
    flex: 1,
    paddingVertical: 8,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    marginVertical: 2,
  },
  menuIconBox: {
    width: 36,
    height: 36,
    borderRadius: 8,
    backgroundColor: theme.surfaceBg,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 14,
  },
  menuItemIcon: {
    fontSize: 18,
    color: theme.textSecondary,
    fontWeight: '300',
  },
  menuItemText: {
    fontSize: 15,
    color: theme.text,
    fontWeight: '400',
    letterSpacing: 0.3,
  },
  menuItemLogin: {
    marginHorizontal: 12,
    marginVertical: 12,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: theme.primary,
  },
  menuIconBoxLogin: {
    backgroundColor: 'rgba(255, 255, 255, 0.3)',
  },
  menuItemIconLogin: {
    color: '#fff',
  },
  menuItemTextLogin: {
    color: '#fff',
    fontWeight: '600',
  },
  menuItemLogout: {
    marginHorizontal: 12,
    marginVertical: 12,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: theme.dangerBg,
  },
  menuIconBoxLogout: {
    backgroundColor: 'rgba(211, 47, 47, 0.15)',
  },
  menuItemIconLogout: {
    color: theme.dangerText,
  },
  menuItemTextLogout: {
    color: theme.dangerText,
    fontWeight: '600',
  },
  menuDivider: {
    height: 1,
    backgroundColor: theme.borderLight,
    marginVertical: 8,
    marginHorizontal: 16,
  },
});