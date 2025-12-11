import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  Dimensions,
} from 'react-native';

const { width } = Dimensions.get('window');

const CulturalHeritageScreen = ({ navigation }) => {
  const [activeTab, setActiveTab] = useState('patterns');

  const patterns = [
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
  ];

  const artisans = [
    {
      id: 1,
      name: 'Evelinda Otong-Hamja',
      image: require('../assets/images/Weaving.jpg'),
      story: 'The fourth-generation Yakan weaver Evelinda Otong-Hamja also the founder, whose shop stands in Zamboanga City, carries with her the legacy of Yakan weaving. Like many others, she moved from Basilan and, despite initially pursuing a different career path, was already recognized at a young age when she was featured in a national magazine celebrating indigenous groups.\n\nShe later worked abroad as a medical technician but eventually returned home to pursue her long-time dream of becoming an entrepreneur. In 2018, she established Tuwas Yakan Weavers, a collective of thirty to forty Yakan weavers, most of whom are her relatives.\n\nWith the group based both in Basilan and Zamboanga, Evelinda sought to build stronger connections between Yakan artisans and designers. She launched a Facebook page to introduce the collective, emphasizing that authentic Yakan weavers remain active and accessible for direct collaboration. Her efforts mirror the same vision that drives Yakan Culture Clothing today: promoting the livelihood of weavers, preserving traditional craftsmanship, and ensuring that Yakan culture thrives in modern times.',
    },
  ];

  const renderPatternsTab = () => (
    <ScrollView style={styles.tabContent}>
      <Text style={styles.sectionTitle}>Tuwas Yakan Patterns & Fabrics</Text>
      {patterns.map((pattern, index) => (
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
    </ScrollView>
  );

  const renderArtisansTab = () => (
    <ScrollView style={styles.tabContent}>
      {artisans.map((artisan) => (
        <View key={artisan.id} style={styles.artisanCard}>
          <Image source={artisan.image} style={styles.artisanImage} />
          <Text style={styles.artisanStory}>{artisan.story}</Text>
        </View>
      ))}
    </ScrollView>
  );

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
          <Text style={styles.backButtonText}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Cultural Heritage</Text>
        <View style={styles.placeholder} />
      </View>

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
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#8B1A1A',
    paddingTop: 50,
    paddingBottom: 15,
    paddingHorizontal: 20,
  },
  backButton: {
    padding: 5,
  },
  backButtonText: {
    color: '#fff',
    fontSize: 28,
    fontWeight: '300',
  },
  headerTitle: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  placeholder: {
    width: 30,
  },
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  tab: {
    flex: 1,
    paddingVertical: 15,
    alignItems: 'center',
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  activeTab: {
    borderBottomColor: '#8B1A1A',
  },
  tabText: {
    fontSize: 16,
    color: '#666',
    fontWeight: '500',
  },
  activeTabText: {
    color: '#8B1A1A',
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
    color: '#333',
  },
  patternCard: {
    backgroundColor: '#fff',
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
    color: '#333',
    paddingHorizontal: 20,
    textAlign: 'center',
  },
  artisanCard: {
    backgroundColor: '#fff',
    padding: 20,
  },
  artisanImage: {
    width: '100%',
    height: 300,
    borderRadius: 10,
    marginBottom: 20,
    resizeMode: 'cover',
  },
  artisanStory: {
    fontSize: 16,
    lineHeight: 26,
    color: '#333',
    textAlign: 'justify',
  },
});

export default CulturalHeritageScreen;