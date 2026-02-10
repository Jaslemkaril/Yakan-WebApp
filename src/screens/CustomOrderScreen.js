import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Linking,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import BottomNav from '../components/BottomNav';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import colors from '../constants/colors';

const { width } = require('react-native').Dimensions.get('window');

const CustomOrderScreen = ({ navigation }) => {
  const { theme } = useTheme();
  const webURL = 'https://yakan-webapp-production.up.railway.app/custom-orders';

  const handleOpenWebsite = () => {
    Alert.alert(
      'Open in Browser',
      'This will open the custom order page in your browser.',
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'Open', 
          onPress: () => {
            Linking.openURL(webURL).catch(err =>
              console.error('Failed to open URL:', err)
            );
          }
        },
      ]
    );
  };

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Custom Order" 
        navigation={navigation} 
        showBack={false}
      />
      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>

        {/* Main Content */}
        <View style={styles.contentContainer}>
          <View style={styles.iconContainer}>
            <Ionicons name="color-palette" size={80} color={colors.primary} />
          </View>

          <Text style={styles.mainTitle}>Create Your Custom Yakan Piece</Text>
          <Text style={styles.subtitle}>
            Design your unique traditional weave with our custom order service
          </Text>

          {/* Features */}
          <View style={styles.featuresContainer}>
            <Text style={styles.featuresTitle}>What you can customize:</Text>
            
            <View style={styles.featureItem}>
              <Ionicons name="checkmark-circle" size={24} color={colors.primary} />
              <Text style={styles.featureText}>Choose traditional Yakan patterns</Text>
            </View>
            
            <View style={styles.featureItem}>
              <Ionicons name="checkmark-circle" size={24} color={colors.primary} />
              <Text style={styles.featureText}>Select your preferred colors</Text>
            </View>
            
            <View style={styles.featureItem}>
              <Ionicons name="checkmark-circle" size={24} color={colors.primary} />
              <Text style={styles.featureText}>Specify custom sizes</Text>
            </View>
            
            <View style={styles.featureItem}>
              <Ionicons name="checkmark-circle" size={24} color={colors.primary} />
              <Text style={styles.featureText}>Upload design inspiration</Text>
            </View>

            <View style={styles.featureItem}>
              <Ionicons name="checkmark-circle" size={24} color={colors.primary} />
              <Text style={styles.featureText}>Chat with our artisans</Text>
            </View>
          </View>

          {/* CTA Button */}
          <TouchableOpacity style={styles.ctaButton} onPress={handleOpenWebsite}>
            <Ionicons name="globe-outline" size={24} color="#fff" style={styles.buttonIcon} />
            <Text style={styles.ctaButtonText}>Open Custom Order Page</Text>
            <Ionicons name="arrow-forward" size={20} color="#fff" />
          </TouchableOpacity>

          {/* Info Box */}
          <View style={styles.infoBox}>
            <Ionicons name="information-circle" size={24} color={colors.primary} />
            <Text style={styles.infoText}>
              Custom orders are processed through our website for the best design experience
            </Text>
          </View>

          {/* Website Link */}
          <View style={styles.websiteBox}>
            <Text style={styles.websiteLabel}>Direct Link:</Text>
            <TouchableOpacity onPress={handleOpenWebsite}>
              <Text style={styles.websiteLink}>{webURL}</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
      
      <BottomNav navigation={navigation} activeRoute="CustomOrder" />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  scrollView: {
    flex: 1,
  },
  header: {
    backgroundColor: colors.primary,
    paddingTop: 50,
    paddingBottom: 20,
    paddingHorizontal: 20,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  contentContainer: {
    padding: 20,
    paddingBottom: 100,
  },
  iconContainer: {
    alignItems: 'center',
    marginVertical: 30,
  },
  mainTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: colors.text,
    textAlign: 'center',
    marginBottom: 12,
  },
  subtitle: {
    fontSize: 16,
    color: colors.textLight,
    textAlign: 'center',
    marginBottom: 30,
    lineHeight: 24,
  },
  featuresContainer: {
    backgroundColor: colors.white,
    borderRadius: 15,
    padding: 20,
    marginBottom: 25,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  featuresTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 15,
  },
  featureItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
    gap: 12,
  },
  featureText: {
    fontSize: 15,
    color: colors.text,
    flex: 1,
  },
  ctaButton: {
    backgroundColor: colors.primary,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    paddingHorizontal: 24,
    borderRadius: 12,
    marginBottom: 20,
    gap: 10,
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 5,
  },
  buttonIcon: {
    marginRight: 4,
  },
  ctaButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
    flex: 1,
    textAlign: 'center',
  },
  infoBox: {
    backgroundColor: '#fff3e0',
    borderRadius: 10,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
    gap: 12,
  },
  infoText: {
    fontSize: 14,
    color: '#e65100',
    flex: 1,
    lineHeight: 20,
  },
  websiteBox: {
    backgroundColor: colors.white,
    borderRadius: 10,
    padding: 16,
    borderWidth: 1,
    borderColor: '#e0e0e0',
  },
  websiteLabel: {
    fontSize: 13,
    color: colors.textLight,
    marginBottom: 8,
  },
  websiteLink: {
    fontSize: 14,
    color: colors.primary,
    textDecorationLine: 'underline',
  },
});

export default CustomOrderScreen;