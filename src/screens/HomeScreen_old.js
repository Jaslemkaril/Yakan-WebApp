// src/screens/HomeScreen.js
import React, { useState } from 'react';
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
} from 'react-native';
import { useCart } from '../context/CartContext';
import BottomNav from '../components/BottomNav';
import colors from '../constants/colors';

const { width } = Dimensions.get('window');

export default function HomeScreen({ navigation }) {
  const [searchQuery, setSearchQuery] = useState('');
  const [favorites, setFavorites] = useState([]);
  const { getCartCount, isLoggedIn } = useCart();

  const products = [
    {
      id: 1,
      name: 'Saputangan',
      description: 'The Saputangan is a square piece of woven cloth usually measuring no less than...',
      price: 50.00,
      featured: true,
      image: require('../assets/images/Saputangan.jpg'),
    },
    {
      id: 2,
      name: 'Pinantupan',
      description: 'Pinantupan uses simple patterns like flowers and diamonds and are also used for...',
      price: 50.00,
      featured: true,
      image: require('../assets/images/pattern1.jpg'),
    },
    {
      id: 3,
      name: 'Birey - Birey',
      description: 'Birey-birey is a traditional handwoven textile pattern that resembles the sections of...',
      price: 50.00,
      featured: false,
      image: require('../assets/images/Patterns.jpg'),
    },
    {
      id: 4,
      name: 'Saputangan',
      description: 'The Saputangan is a square piece of woven cloth usually measuring no less than...',
      price: 50.00,
      featured: false,
      image: require('../assets/images/Saputangan.jpg'),
    },
  ];

  const featuredProducts = products.filter(p => p.featured);

  const toggleFavorite = (productId) => {
    if (favorites.includes(productId)) {
      setFavorites(favorites.filter(id => id !== productId));
    } else {
      setFavorites([...favorites, productId]);
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
          onPress={() => toggleFavorite(product.id)}
        >
          <Text style={styles.favoriteIcon}>
            {favorites.includes(product.id) ? '❤️' : '🤍'}
          </Text>
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
          onPress={() => toggleFavorite(product.id)}
        >
          <Text style={styles.favoriteIcon}>
            {favorites.includes(product.id) ? '❤️' : '🤍'}
          </Text>
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
            <Text style={styles.cartIcon}>🛒</Text>
          </TouchableOpacity>
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* HERO SECTION */}
        <View style={styles.heroSection}>
          <ImageBackground
            source={require('../assets/images/TUWASYAKAN.jpg')}
            style={styles.heroBackground}
            resizeMode="cover"
          >
            <View style={styles.heroOverlay}>
              <View style={styles.topBar}>
                <TouchableOpacity style={styles.menuButton}>
                  <Text style={styles.menuIcon}>☰</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={styles.heroCartButton}
                  onPress={() => navigation.navigate('Cart')}
                >
                  <Text style={styles.heroCartIcon}>🛒</Text>
                  {getCartCount() > 0 && (
                    <View style={styles.heroBadge}>
                      <Text style={styles.heroBadgeText}>{getCartCount()}</Text>
                    </View>
                  )}
                </TouchableOpacity>
              </View>

              <View style={styles.heroSearchContainer}>
                <Text style={styles.heroSearchIcon}></Text>
                <TextInput
                  style={styles.heroSearchInput}
                  placeholder="Search products..."
                  placeholderTextColor="rgba(255,255,255,0.7)"
                  value={searchQuery}
                  onChangeText={setSearchQuery}
                />
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
          <Text style={styles.sectionTitle}>Featured Fabrics</Text>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.featuredScroll}
          >
            {featuredProducts.map(product => renderFeaturedCard(product))}
          </ScrollView>
        </View>

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

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
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
  menuIcon: {
    fontSize: 24,
    color: colors.white,
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
  heroCartIcon: {
    fontSize: 22,
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
    color: colors.white,
    fontSize: 11,
    fontWeight: 'bold',
  },
  heroSearchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.25)',
    borderRadius: 25,
    paddingHorizontal: 20,
    paddingVertical: 12,
    marginBottom: 40,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.3)',
  },
  heroSearchIcon: {
    fontSize: 18,
    marginRight: 10,
  },
  heroSearchInput: {
    flex: 1,
    fontSize: 16,
    color: colors.white,
  },
  logoContainer: {
    alignItems: 'center',
    marginTop: 20,
  },
  logoMain: {
    fontSize: 52,
    fontWeight: 'bold',
    color: colors.white,
    letterSpacing: 4,
  },
  logoSub: {
    fontSize: 40,
    fontWeight: 'bold',
    color: colors.white,
    letterSpacing: 3,
    marginTop: -5,
  },
  tagline: {
    fontSize: 16,
    color: colors.white,
    marginTop: 8,
    fontStyle: 'italic',
    letterSpacing: 1,
  },
  // FEATURED SECTION
  featuredSection: {
    marginBottom: 25,
  },
  sectionTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: colors.text,
    marginHorizontal: 20,
    marginBottom: 15,
  },
  featuredScroll: {
    paddingHorizontal: 15,
  },
  featuredCard: {
    width: 220,
    backgroundColor: colors.white,
    borderRadius: 15,
    marginHorizontal: 5,
    overflow: 'hidden',
    shadowColor: colors.black,
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
    backgroundColor: colors.white,
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
    color: colors.text,
    marginBottom: 8,
  },
  featuredPrice: {
    fontSize: 20,
    fontWeight: 'bold',
    color: colors.primary,
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
    backgroundColor: colors.white,
    padding: 20,
    borderRadius: 15,
  },
  culturalText: {
    fontSize: 14,
    color: colors.text,
    lineHeight: 22,
    marginBottom: 15,
  },
  culturalSubtitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: colors.text,
    marginTop: 10,
    marginBottom: 10,
  },
  learnMoreButton: {
    marginTop: 10,
    borderWidth: 2,
    borderColor: colors.primary,
    borderRadius: 8,
    paddingVertical: 10,
    paddingHorizontal: 20,
    alignSelf: 'flex-start',
  },
  learnMoreText: {
    color: colors.primary,
    fontSize: 15,
    fontWeight: '600',
  },
  // PRODUCT CARD
  productCard: {
    width: '48%',
    backgroundColor: colors.white,
    borderRadius: 15,
    marginBottom: 15,
    overflow: 'hidden',
    shadowColor: colors.black,
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
    backgroundColor: colors.white,
    width: 35,
    height: 35,
    borderRadius: 17.5,
    justifyContent: 'center',
    alignItems: 'center',
  },
  favoriteIcon: {
    fontSize: 18,
  },
  productInfo: {
    padding: 12,
  },
  productName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 5,
  },
  productDescription: {
    fontSize: 12,
    color: colors.textLight,
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
    color: colors.text,
  },
  cartButton: {
    backgroundColor: colors.black,
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  cartIcon: {
    fontSize: 18,
  },
});