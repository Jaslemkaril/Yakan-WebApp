import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Alert,
  Image,
  ActivityIndicator,
} from 'react-native';
import { useCart } from '../context/CartContext';
import ApiService from '../services/api';
import API_CONFIG from '../config/config';
import BottomNav from '../components/BottomNav';

const ProductsScreen = ({ navigation }) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [categories, setCategories] = useState(['All']);
  const { isLoggedIn, addToWishlist, removeFromWishlist, isInWishlist } = useCart();

  // Fetch products from API
  useEffect(() => {
    fetchProducts();
  }, []);

  const fetchProducts = async () => {
    try {
      setLoading(true);
      
      console.log('🔵 Fetching products from API...');
      
      // Fetch from Laravel API via ngrok
      const response = await ApiService.getProducts();
      
      console.log('🔵 API Response:', JSON.stringify(response, null, 2));
      
      // Handle triple-nested response: response.data.data.data
      // ApiService wraps in {success, data}, Laravel wraps in {data: {data: []}}
      const apiData = response.data?.data || response.data || {};
      console.log('🔵 API Data:', JSON.stringify(apiData, null, 2));
      
      const productsData = Array.isArray(apiData.data) ? apiData.data :  // Laravel pagination
                          Array.isArray(apiData) ? apiData : [];
      
      console.log('🔵 Products Array Length:', productsData.length);
      
      // Transform API data to match app structure
      const transformedProducts = productsData.map(product => ({
        id: product.id,
        name: product.name,
        description: product.description,
        price: parseFloat(product.price),
        category: product.category?.name || 'Uncategorized',
        image: product.image 
          ? { uri: `${API_CONFIG.STORAGE_BASE_URL}/products/${product.image}` }
          : require('../assets/images/Saputangan.jpg'), // fallback image
        stock: product.stock || 0,
      }));
      
      setProducts(transformedProducts);
      const uniqueCategories = ['All', ...new Set(transformedProducts.map(p => p.category))];
      setCategories(uniqueCategories);
    } catch (error) {
      console.error('Error fetching products:', error);
      Alert.alert('Error', 'Failed to fetch products. Using offline data.');
      
      // Fallback to mock data if API fails
      const mockProducts = [
        { id: 1, name: 'Saputangan', description: 'The Saputangan is a square piece of woven cloth usually measuring no less than standard size', price: 50, category: 'Saputangan', image: require('../assets/images/Saputangan.jpg') },
        { id: 2, name: 'Pinantupan', description: 'Pinantupan uses simple patterns like flowers and diamonds for special occasions', price: 50, category: 'Pinantupan', image: require('../assets/images/pinantupan.jpg') },
        { id: 3, name: 'Birey-Birey', description: 'Traditional handwoven textile pattern that resembles rice fields', price: 50, category: 'Birey-Birey', image: require('../assets/images/birey4.jpg') },
        { id: 4, name: 'Saputangan Classic', description: 'Classic design with traditional Yakan patterns and vibrant colors', price: 60, category: 'Saputangan', image: require('../assets/images/SaputanganClassic.jpg') },
        { id: 5, name: 'Sinaluan', description: 'Intricate geometric patterns representing Yakan heritage', price: 75, category: 'Sinaluan', image: require('../assets/images/Sinaluan.jpg') },
        { id: 6, name: 'Pinantupan Premium', description: 'Premium quality with detailed floral patterns', price: 85, category: 'Pinantupan', image: require('../assets/images/pinantupanpremium.jpg') },
        { id: 7, name: 'Birey-Birey Deluxe', description: 'Deluxe version with enhanced colors and intricate detailing', price: 70, category: 'Birey-Birey', image: require('../assets/images/birey4.jpg') },
        { id: 8, name: 'Sinaluan Premium', description: 'Premium Sinaluan with extra fine weaving', price: 95, category: 'Sinaluan', image: require('../assets/images/Sinaluan.jpg') },
      ];
      
      setProducts(mockProducts);
      const uniqueCategories = ['All', ...new Set(mockProducts.map(p => p.category))];
      setCategories(uniqueCategories);
    } finally {
      setLoading(false);
    }
  };

  const filteredProducts = products.filter(product => {
    const matchesCategory = selectedCategory === 'All' || product.category === selectedCategory;
    const matchesSearch = product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         product.description.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCategory && matchesSearch;
  });

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

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Products</Text>
        <TouchableOpacity onPress={() => navigation.navigate('Cart')}>
          <Text style={styles.cartIcon}>🛒</Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#FF6B6B" />
          <Text style={styles.loadingText}>Loading products...</Text>
        </View>
      ) : (
        <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {/* Search Bar */}
        <View style={styles.searchContainer}>
          <Text style={styles.searchIcon}>🔍</Text>
          <TextInput
            style={styles.searchInput}
            placeholder="Search products..."
            placeholderTextColor="#999"
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
        </View>

        {/* Category Filter */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.categoryContainer}
        >
          {categories.map((category) => (
            <TouchableOpacity
              key={category}
              style={[
                styles.categoryButton,
                selectedCategory === category && styles.categoryButtonActive,
              ]}
              onPress={() => setSelectedCategory(category)}
            >
              <Text
                style={[
                  styles.categoryText,
                  selectedCategory === category && styles.categoryTextActive,
                ]}
              >
                {category}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        {/* Products Grid */}
        <View style={styles.productsGrid}>
          {filteredProducts.length > 0 ? (
            filteredProducts.map((product) => (
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
                    <Text style={styles.favoriteIcon}>
                      {isInWishlist(product.id) ? '❤️' : '🤍'}
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
                      <Text style={styles.cartButtonIcon}>🛒</Text>
                    </TouchableOpacity>
                  </View>
                </View>
              </TouchableOpacity>
            ))
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No products found</Text>
            </View>
          )}
        </View>

        <View style={{ height: 100 }} />
      </ScrollView>
      )}

      {/* Bottom Navigation */}
      <BottomNav navigation={navigation} activeRoute="Products" />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
  },
  loadingText: {
    marginTop: 15,
    fontSize: 16,
    color: '#666',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 15,
    backgroundColor: '#8B1A1A',
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  cartIcon: {
    fontSize: 24,
  },
  content: {
    flex: 1,
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    margin: 15,
    paddingHorizontal: 15,
    paddingVertical: 12,
    borderRadius: 25,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  searchIcon: {
    fontSize: 18,
    marginRight: 10,
  },
  searchInput: {
    flex: 1,
    fontSize: 16,
    color: '#333',
  },
  categoryContainer: {
    paddingHorizontal: 15,
    marginBottom: 15,
  },
  categoryButton: {
    paddingHorizontal: 20,
    paddingVertical: 10,
    backgroundColor: '#fff',
    borderRadius: 20,
    marginRight: 10,
    borderWidth: 1,
    borderColor: '#e0e0e0',
  },
  categoryButtonActive: {
    backgroundColor: '#8B1A1A',
    borderColor: '#8B1A1A',
  },
  categoryText: {
    fontSize: 14,
    color: '#666',
  },
  categoryTextActive: {
    color: '#fff',
    fontWeight: '600',
  },
  productsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    paddingHorizontal: 10,
    justifyContent: 'space-between',
  },
  productCard: {
    width: '48%',
    backgroundColor: '#fff',
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
    backgroundColor: '#fff',
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
    color: '#333',
    marginBottom: 5,
  },
  productDescription: {
    fontSize: 12,
    color: '#666',
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
    color: '#333',
  },
  cartButton: {
    backgroundColor: '#000',
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  cartButtonIcon: {
    fontSize: 18,
  },
  emptyContainer: {
    width: '100%',
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#999',
  },
});

export default ProductsScreen;