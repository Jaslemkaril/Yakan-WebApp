import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  Dimensions,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import ApiService from '../services/api';
import API_CONFIG from '../config/config';
import colors from '../constants/colors';
import BottomNav from '../components/BottomNav';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';

const { width } = Dimensions.get('window');

const CulturalHeritageScreen = ({ navigation }) => {
  const [activeTab, setActiveTab] = useState('patterns');
  const [patterns, setPatterns] = useState([]);
  const [artisans, setArtisans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const { theme } = useTheme();
  const styles = getStyles(theme);

  useEffect(() => {
    fetchCulturalHeritage();
  }, []);

  const fetchCulturalHeritage = async () => {
    try {
      setLoading(true);
      console.log('📚 CulturalHeritage: Fetching content from API...');
      
      const response = await ApiService.getCulturalHeritage();
      
      if (!response.success) {
        throw new Error(response.error || 'Failed to fetch cultural heritage');
      }
      
      // Handle both paginated and non-paginated responses
      let heritageData = [];
      if (response.data?.data && Array.isArray(response.data.data)) {
        // Paginated response
        heritageData = response.data.data;
      } else if (Array.isArray(response.data)) {
        // Direct array response
        heritageData = response.data;
      }
      
      console.log('📚 CulturalHeritage: Fetched', heritageData.length, 'items');
      
      // If no data from API, load fallback
      if (heritageData.length === 0) {
        console.log('📚 CulturalHeritage: No data from API, loading fallback');
        loadFallbackData();
        return;
      }
      
      // Separate patterns and artisan stories
      const patternsData = heritageData.filter(item => 
        item.category === 'pattern' || item.category === 'fabric'
      );
      const artisansData = heritageData.filter(item => 
        item.category === 'artisan' || item.category === 'story'
      );
      
      // Transform data
      const transformedPatterns = patternsData.map(item => ({
        id: item.id,
        name: item.title,
        description: item.content || item.summary,
        subtitle: item.author || 'From Tuwas Yakan',
        image: item.image 
          ? { uri: item.image.startsWith('http') 
              ? item.image 
              : `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}/storage/cultural-heritage/${item.image}` 
            }
          : require('../assets/images/Patterns.jpg'),
      }));
      
      const transformedArtisans = artisansData.map(item => ({
        id: item.id,
        name: item.title,
        story: item.content || item.summary,
        image: item.image 
          ? { uri: item.image.startsWith('http') 
              ? item.image 
              : `${API_CONFIG.API_BASE_URL.replace('/api/v1', '')}/storage/cultural-heritage/${item.image}` 
            }
          : require('../assets/images/Weaving.jpg'),
      }));
      
      setPatterns(transformedPatterns);
      setArtisans(transformedArtisans);
      
    } catch (error) {
      console.error('📚 CulturalHeritage: Error fetching content:', error);
      // Fallback to static data
      loadFallbackData();
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const loadFallbackData = () => {
    setPatterns([
      {
        id: 1,
        name: 'Birey-birey',
        image: require('../assets/images/birey4.jpg'),
        description: 'Birey-birey is a traditional handwoven textile pattern that resembles the sections of a bamboo stalk. It is a type of tennun (woven cloth), specifically a kind of sinaluan, which is a striped design that draws inspiration from nature.',
        subtitle: 'From Tuwas Yakan',
      },
      {
        id: 2,
        name: 'Pinantupan',
        image: require('../assets/images/pinantupan.jpg'),
        description: 'Pinantupan uses simple patterns like flowers and diamonds that are also used for traditional Yakan skirt. For 50 inches of pinantupan it takes 7-10days of weaving.',
        subtitle: 'From Tuwas Yakan',
      },
      {
        id: 3,
        name: 'Saputangan',
        image: require('../assets/images/Saputangan.jpg'),
        description: 'The Saputangan is a square piece of woven cloth usually measuring no less than 74 centimeters on its sides. While most commonly used as a headscarf, the saputangan also function as a such or waistband to secure their sawal (trousers worn by both men and women) in place. How the yakan headscarf is worn-twirled, folded, or casually placed on the head.',
        subtitle: 'From Tuwas Yakan',
      },
    ]);

    setArtisans([
      {
        id: 1,
        name: 'Evelinda Otong-Hamja',
        image: require('../assets/images/Weaving.jpg'),
        story: 'The fourth-generation Yakan weaver Evelinda Otong-Hamja also the founder, whose shop stands in Zamboanga City, carries with her the legacy of Yakan weaving. Like many others, she moved from Basilan and, despite initially pursuing a different career path, was already recognized at a young age when she was featured in a national magazine celebrating indigenous groups.\n\nShe later worked abroad as a medical technician but eventually returned home to pursue her long-time dream of becoming an entrepreneur. In 2018, she established Tuwas Yakan Weavers, a collective of thirty to forty Yakan weavers, most of whom are her relatives.\n\nWith the group based both in Basilan and Zamboanga, Evelinda sought to build stronger connections between Yakan artisans and designers. She launched a Facebook page to introduce the collective, emphasizing that authentic Yakan weavers remain active and accessible for direct collaboration. Her efforts mirror the same vision that drives Yakan Culture Clothing today: promoting the livelihood of weavers, preserving traditional craftsmanship, and ensuring that Yakan culture thrives in modern times.',
      },
    ]);
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchCulturalHeritage();
  };

  const renderPatternsTab = () => (
    <ScrollView 
      style={styles.tabContent}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
    >
      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading patterns...</Text>
        </View>
      ) : patterns.length > 0 ? (
        <>
          <Text style={styles.sectionTitle}>Tuwas Yakan Patterns & Fabrics</Text>
          {patterns.map((pattern) => (
            <View key={pattern.id} style={styles.patternCard}>
              <View style={styles.imageContainer}>
                <Image source={pattern.image} style={styles.patternImage} />
                <View style={styles.imageOverlay}>
                  <Text style={styles.patternName}>{pattern.name}</Text>
                  <Text style={styles.patternSubtitle}>{pattern.subtitle}</Text>
                </View>
              </View>
              <Text style={styles.patternDescription}>{pattern.description}</Text>
            </View>
          ))}
        </>
      ) : (
        <View style={styles.emptyContainer}>
          <Ionicons name="images-outline" size={64} color={colors.textLight} />
          <Text style={styles.emptyText}>No patterns available</Text>
          <TouchableOpacity style={styles.retryButton} onPress={fetchCulturalHeritage}>
            <Text style={styles.retryButtonText}>Retry</Text>
          </TouchableOpacity>
        </View>
      )}
    </ScrollView>
  );

  const renderArtisansTab = () => (
    <ScrollView 
      style={styles.tabContent}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
    >
      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading artisan stories...</Text>
        </View>
      ) : artisans.length > 0 ? (
        artisans.map((artisan) => (
          <View key={artisan.id} style={styles.artisanCard}>
            <Image source={artisan.image} style={styles.artisanImage} />
            <Text style={styles.artisanName}>{artisan.name}</Text>
            <Text style={styles.artisanStory}>{artisan.story}</Text>
          </View>
        ))
      ) : (
        <View style={styles.emptyContainer}>
          <Ionicons name="people-outline" size={64} color={colors.textLight} />
          <Text style={styles.emptyText}>No artisan stories available</Text>
          <TouchableOpacity style={styles.retryButton} onPress={fetchCulturalHeritage}>
            <Text style={styles.retryButtonText}>Retry</Text>
          </TouchableOpacity>
        </View>
      )}
    </ScrollView>
  );

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Cultural Heritage" 
        navigation={navigation} 
        showBack={true}
      />

      {/* Tabs */}
      <View style={styles.tabContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'patterns' && styles.activeTab]}
          onPress={() => setActiveTab('patterns')}
        >
          <Text style={[styles.tabText, activeTab === 'patterns' && styles.activeTabText]}>
            Patterns & Fabrics
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'artisans' && styles.activeTab]}
          onPress={() => setActiveTab('artisans')}
        >
          <Text style={[styles.tabText, activeTab === 'artisans' && styles.activeTabText]}>
            Artisan Stories
          </Text>
        </TouchableOpacity>
      </View>

      {/* Tab Content */}
      {activeTab === 'patterns' ? renderPatternsTab() : renderArtisansTab()}
      
      <BottomNav navigation={navigation} activeRoute="CulturalHeritage" />
    </View>
  );
};

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: theme.headerBg,
    paddingTop: 50,
    paddingBottom: 15,
    paddingHorizontal: 20,
  },
  backButton: {
    padding: 5,
  },
  headerTitle: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  placeholder: {
    width: 30,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 60,
  },
  loadingText: {
    marginTop: 15,
    fontSize: 16,
    color: theme.textSecondary,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 60,
  },
  emptyText: {
    fontSize: 16,
    color: theme.textSecondary,
    marginTop: 15,
    marginBottom: 20,
  },
  retryButton: {
    backgroundColor: theme.primary,
    paddingVertical: 10,
    paddingHorizontal: 24,
    borderRadius: 8,
  },
  retryButtonText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: '600',
  },
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: theme.cardBackground,
    borderBottomWidth: 1,
    borderBottomColor: theme.border,
  },
  tab: {
    flex: 1,
    paddingVertical: 15,
    alignItems: 'center',
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  activeTab: {
    borderBottomColor: theme.primary,
  },
  tabText: {
    fontSize: 16,
    color: theme.textSecondary,
    fontWeight: '500',
  },
  activeTabText: {
    color: theme.primary,
    fontWeight: 'bold',
  },
  tabContent: {
    flex: 1,
  },
  sectionTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    textAlign: 'center',
    marginVertical: 20,
    paddingHorizontal: 20,
    color: theme.text,
  },
  patternCard: {
    backgroundColor: theme.cardBackground,
    marginBottom: 20,
    paddingBottom: 20,
  },
  imageContainer: {
    position: 'relative',
    width: '100%',
    height: 250,
    marginBottom: 15,
  },
  patternImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  imageOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    padding: 15,
  },
  patternName: {
    color: '#fff',
    fontSize: 22,
    fontWeight: 'bold',
    fontStyle: 'italic',
  },
  patternSubtitle: {
    color: '#fff',
    fontSize: 14,
    fontStyle: 'italic',
    marginTop: 2,
  },
  patternDescription: {
    fontSize: 16,
    lineHeight: 24,
    color: theme.text,
    paddingHorizontal: 20,
    textAlign: 'center',
  },
  artisanCard: {
    backgroundColor: theme.cardBackground,
    padding: 20,
    marginBottom: 20,
    borderRadius: 10,
  },
  artisanImage: {
    width: '100%',
    height: 300,
    borderRadius: 10,
    marginBottom: 20,
    resizeMode: 'cover',
  },
  artisanName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: theme.text,
    marginBottom: 12,
  },
  artisanStory: {
    fontSize: 16,
    lineHeight: 26,
    color: theme.text,
    textAlign: 'justify',
  },
});

export default CulturalHeritageScreen;