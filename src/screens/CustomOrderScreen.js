import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Linking,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import BottomNav from '../components/BottomNav';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import colors from '../constants/colors';

const WEBSITE_URL = 'https://yakan-webapp-production.up.railway.app/custom-order';

const CustomOrderScreen = ({ navigation }) => {
  const { theme } = useTheme();
  const styles = getStyles(theme);

  const openWebsite = async () => {
    try {
      await Linking.openURL(WEBSITE_URL);
    } catch (error) {
      console.error('Failed to open URL:', error);
    }
  };

  return (
    <View style={styles.container}>
      <ScreenHeader
        title="Custom Order"
        navigation={navigation}
        showBack={false}
      />

      <View style={styles.content}>
        {/* Icon */}
        <View style={styles.iconWrapper}>
          <Ionicons name="color-wand" size={72} color="#8B1A1A" />
        </View>

        {/* Title */}
        <Text style={styles.title}>Order a Custom Yakan Weave</Text>
        <Text style={styles.subtitle}>
          Design your own handwoven Yakan textile — choose your fabric type,
          colors, dimensions, and more through our website.
        </Text>

        {/* Feature bullets */}
        <View style={styles.featureList}>
          {[
            { icon: 'brush', text: 'Choose fabric type & colors' },
            { icon: 'resize', text: 'Set custom dimensions' },
            { icon: 'car', text: 'Pick delivery option' },
            { icon: 'notifications', text: 'Track production status' },
          ].map(({ icon, text }) => (
            <View key={text} style={styles.featureRow}>
              <Ionicons name={icon} size={20} color="#8B1A1A" style={styles.featureIcon} />
              <Text style={styles.featureText}>{text}</Text>
            </View>
          ))}
        </View>

        {/* CTA Button */}
        <TouchableOpacity style={styles.button} onPress={openWebsite} activeOpacity={0.85}>
          <Ionicons name="globe-outline" size={22} color="#fff" style={{ marginRight: 8 }} />
          <Text style={styles.buttonText}>Place Custom Order on Website</Text>
        </TouchableOpacity>

        <Text style={styles.note}>
          Opens in your browser at {WEBSITE_URL}
        </Text>
      </View>

      <BottomNav navigation={navigation} activeRoute="CustomOrder" />
    </View>
  );
};

const getStyles = (theme) =>
  StyleSheet.create({
    container: {
      flex: 1,
      backgroundColor: theme.background,
    },
    content: {
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
      paddingHorizontal: 28,
      paddingBottom: 100,
    },
    iconWrapper: {
      width: 120,
      height: 120,
      borderRadius: 60,
      backgroundColor: '#FFF0F0',
      alignItems: 'center',
      justifyContent: 'center',
      marginBottom: 24,
      shadowColor: '#8B1A1A',
      shadowOffset: { width: 0, height: 4 },
      shadowOpacity: 0.15,
      shadowRadius: 12,
      elevation: 5,
    },
    title: {
      fontSize: 22,
      fontWeight: '700',
      color: '#8B1A1A',
      textAlign: 'center',
      marginBottom: 12,
    },
    subtitle: {
      fontSize: 14,
      color: theme.text || '#555',
      textAlign: 'center',
      lineHeight: 22,
      marginBottom: 28,
    },
    featureList: {
      alignSelf: 'stretch',
      backgroundColor: theme.card || '#fff',
      borderRadius: 14,
      padding: 18,
      marginBottom: 28,
      shadowColor: '#000',
      shadowOffset: { width: 0, height: 2 },
      shadowOpacity: 0.06,
      shadowRadius: 8,
      elevation: 3,
    },
    featureRow: {
      flexDirection: 'row',
      alignItems: 'center',
      paddingVertical: 8,
    },
    featureIcon: {
      marginRight: 12,
    },
    featureText: {
      fontSize: 14,
      color: theme.text || '#333',
      fontWeight: '500',
    },
    button: {
      flexDirection: 'row',
      alignItems: 'center',
      backgroundColor: '#8B1A1A',
      paddingVertical: 16,
      paddingHorizontal: 28,
      borderRadius: 14,
      alignSelf: 'stretch',
      justifyContent: 'center',
      shadowColor: '#8B1A1A',
      shadowOffset: { width: 0, height: 4 },
      shadowOpacity: 0.3,
      shadowRadius: 8,
      elevation: 6,
      marginBottom: 14,
    },
    buttonText: {
      color: '#fff',
      fontSize: 16,
      fontWeight: '700',
    },
    note: {
      fontSize: 11,
      color: '#aaa',
      textAlign: 'center',
    },
  });

export default CustomOrderScreen;
