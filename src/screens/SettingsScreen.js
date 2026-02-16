// src/screens/SettingsScreen.js
import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Switch,
  Alert,
  Linking,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';

export default function SettingsScreen({ navigation }) {
  const [notifications, setNotifications] = useState(true);
  const [emailUpdates, setEmailUpdates] = useState(false);
  const { isDarkMode, toggleDarkMode, theme } = useTheme();

  const handleClearCache = () => {
    Alert.alert(
      'Clear Cache',
      'Are you sure you want to clear app cache?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Clear',
          style: 'destructive',
          onPress: () => Alert.alert('✓ Success', 'Cache cleared successfully'),
        },
      ]
    );
  };

  const settingsSections = [
    {
      title: 'Notifications',
      icon: 'bell-outline',
      items: [
        {
          label: 'Push Notifications',
          description: 'Receive order & promo updates',
          icon: 'bell-ring-outline',
          iconColor: '#3B82F6',
          iconBg: '#EFF6FF',
          value: notifications,
          onValueChange: setNotifications,
        },
        {
          label: 'Email Updates',
          description: 'Weekly newsletter & offers',
          icon: 'email-outline',
          iconColor: '#10B981',
          iconBg: '#ECFDF5',
          value: emailUpdates,
          onValueChange: setEmailUpdates,
        },
      ],
    },
    {
      title: 'Appearance',
      icon: 'palette-outline',
      items: [
        {
          label: 'Dark Mode',
          description: isDarkMode ? 'Dark theme active' : 'Light theme active',
          icon: isDarkMode ? 'moon-waning-crescent' : 'white-balance-sunny',
          iconColor: isDarkMode ? '#8B5CF6' : '#F59E0B',
          iconBg: isDarkMode ? '#F5F3FF' : '#FFFBEB',
          value: isDarkMode,
          onValueChange: toggleDarkMode,
        },
      ],
    },
  ];

  const actionItems = [
    {
      icon: 'cached',
      iconColor: '#EF4444',
      iconBg: '#FEF2F2',
      label: 'Clear Cache',
      description: 'Free up storage space',
      onPress: handleClearCache,
    },
    {
      icon: 'shield-check-outline',
      iconColor: '#6366F1',
      iconBg: '#EEF2FF',
      label: 'Privacy Policy',
      description: 'How we handle your data',
      onPress: () => Alert.alert('Privacy Policy', 'Your privacy is important to us. We collect minimal data necessary to provide our services and never share personal information with third parties without consent.'),
    },
    {
      icon: 'file-document-outline',
      iconColor: '#0EA5E9',
      iconBg: '#F0F9FF',
      label: 'Terms of Service',
      description: 'Usage terms & conditions',
      onPress: () => Alert.alert('Terms of Service', 'By using TUWAS YAKAN, you agree to our terms of service. Please use the app responsibly and respect our artisans\' intellectual property.'),
    },
    {
      icon: 'help-circle-outline',
      iconColor: '#14B8A6',
      iconBg: '#F0FDFA',
      label: 'Help & Support',
      description: 'FAQs & contact us',
      onPress: () => navigation.navigate('Chat'),
    },
    {
      icon: 'star-outline',
      iconColor: '#F59E0B',
      iconBg: '#FFFBEB',
      label: 'Rate the App',
      description: 'Share your experience',
      onPress: () => Alert.alert('Rate Us', 'Thank you for using TUWAS YAKAN! Rating feature coming soon.'),
    },
    {
      icon: 'information-outline',
      iconColor: '#8B5CF6',
      iconBg: '#F5F3FF',
      label: 'About',
      description: 'Version & app info',
      onPress: () => {},
      showAbout: true,
    },
  ];

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader navigation={navigation} title="Settings" showBack={false} backgroundColor={theme.headerBg} />

      <ScrollView 
        style={styles.content}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Profile Quick Card */}
        <View style={[styles.profileCard, { backgroundColor: theme.cardBackground, borderColor: theme.border }]}>
          <View style={[styles.profileAvatar, { backgroundColor: theme.primary }]}>
            <MaterialCommunityIcons name="cog" size={28} color="#fff" />
          </View>
          <View style={styles.profileInfo}>
            <Text style={[styles.profileTitle, { color: theme.text }]}>App Preferences</Text>
            <Text style={[styles.profileSubtitle, { color: theme.textLight }]}>
              Customize your experience
            </Text>
          </View>
          <MaterialCommunityIcons name="chevron-right" size={24} color={theme.textLight} />
        </View>

        {/* Toggle Settings */}
        {settingsSections.map((section, sectionIndex) => (
          <View key={sectionIndex} style={styles.section}>
            <View style={styles.sectionHeader}>
              <MaterialCommunityIcons name={section.icon} size={18} color={theme.primary} />
              <Text style={[styles.sectionTitle, { color: theme.textLight }]}>{section.title}</Text>
            </View>
            <View style={[styles.settingsGroup, { backgroundColor: theme.cardBackground, borderColor: theme.border }]}>
              {section.items.map((item, itemIndex) => (
                <View
                  key={itemIndex}
                  style={[
                    styles.settingItem,
                    { borderBottomColor: theme.border },
                    itemIndex === section.items.length - 1 && styles.settingItemLast,
                  ]}
                >
                  <View style={[styles.settingIconContainer, { backgroundColor: isDarkMode ? item.iconColor + '20' : item.iconBg }]}>
                    <MaterialCommunityIcons name={item.icon} size={22} color={item.iconColor} />
                  </View>
                  <View style={styles.settingTextContainer}>
                    <Text style={[styles.settingLabel, { color: theme.text }]}>{item.label}</Text>
                    <Text style={[styles.settingDescription, { color: theme.textLight }]}>{item.description}</Text>
                  </View>
                  <Switch
                    value={item.value}
                    onValueChange={item.onValueChange}
                    trackColor={{ false: isDarkMode ? '#444' : '#D1D5DB', true: theme.primary + 'AA' }}
                    thumbColor={item.value ? theme.primary : '#fff'}
                    ios_backgroundColor={isDarkMode ? '#444' : '#D1D5DB'}
                  />
                </View>
              ))}
            </View>
          </View>
        ))}

        {/* Action Items */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <MaterialCommunityIcons name="dots-horizontal-circle-outline" size={18} color={theme.primary} />
            <Text style={[styles.sectionTitle, { color: theme.textLight }]}>More</Text>
          </View>
          <View style={[styles.settingsGroup, { backgroundColor: theme.cardBackground, borderColor: theme.border }]}>
            {actionItems.map((item, index) => (
              <TouchableOpacity
                key={index}
                style={[
                  styles.actionItem,
                  { borderBottomColor: theme.border },
                  index === actionItems.length - 1 && styles.actionItemLast,
                ]}
                onPress={item.showAbout ? undefined : item.onPress}
                activeOpacity={item.showAbout ? 1 : 0.6}
              >
                <View style={[styles.actionIconContainer, { backgroundColor: isDarkMode ? item.iconColor + '20' : item.iconBg }]}>
                  <MaterialCommunityIcons name={item.icon} size={22} color={item.iconColor} />
                </View>
                <View style={styles.actionTextContainer}>
                  <Text style={[styles.actionLabel, { color: theme.text }]}>{item.label}</Text>
                  <Text style={[styles.actionDescription, { color: theme.textLight }]}>{item.description}</Text>
                </View>
                {!item.showAbout && (
                  <MaterialCommunityIcons name="chevron-right" size={22} color={theme.textLight} />
                )}
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* App Info Card */}
        <View style={[styles.infoCard, { backgroundColor: isDarkMode ? '#1A1520' : '#FDF2F8', borderColor: isDarkMode ? '#333' : '#FCE7F3' }]}>
          <View style={[styles.infoLogoContainer, { backgroundColor: theme.primary }]}>
            <MaterialCommunityIcons name="handshake" size={32} color="#fff" />
          </View>
          <Text style={[styles.infoAppName, { color: theme.text }]}>TUWAS YAKAN</Text>
          <Text style={[styles.infoTagline, { color: theme.textLight }]}>Weaving through generations</Text>
          <View style={[styles.versionBadge, { backgroundColor: isDarkMode ? '#333' : '#F3F4F6' }]}>
            <Text style={[styles.versionText, { color: theme.textLight }]}>v1.0.0</Text>
          </View>
          <Text style={[styles.copyrightText, { color: theme.textLight }]}>
            © 2026 Tuwas Yakan. All rights reserved.
          </Text>
        </View>

        <View style={{ height: 30 }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  content: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 30,
  },

  // Profile Card
  profileCard: {
    flexDirection: 'row',
    alignItems: 'center',
    marginHorizontal: 20,
    marginTop: 20,
    padding: 16,
    borderRadius: 16,
    borderWidth: 1,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
  },
  profileAvatar: {
    width: 48,
    height: 48,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  profileInfo: {
    flex: 1,
    marginLeft: 14,
  },
  profileTitle: {
    fontSize: 17,
    fontWeight: '700',
    letterSpacing: 0.2,
  },
  profileSubtitle: {
    fontSize: 13,
    marginTop: 2,
  },

  // Sections
  section: {
    marginTop: 24,
    paddingHorizontal: 20,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
    gap: 6,
  },
  sectionTitle: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 1,
  },
  settingsGroup: {
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06,
    shadowRadius: 4,
  },

  // Toggle Items
  settingItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
  },
  settingItemLast: {
    borderBottomWidth: 0,
  },
  settingIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
  },
  settingTextContainer: {
    flex: 1,
  },
  settingLabel: {
    fontSize: 15,
    fontWeight: '600',
    letterSpacing: 0.1,
  },
  settingDescription: {
    fontSize: 12,
    marginTop: 2,
  },

  // Action Items
  actionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
  },
  actionItemLast: {
    borderBottomWidth: 0,
  },
  actionIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
  },
  actionTextContainer: {
    flex: 1,
  },
  actionLabel: {
    fontSize: 15,
    fontWeight: '600',
    letterSpacing: 0.1,
  },
  actionDescription: {
    fontSize: 12,
    marginTop: 2,
  },

  // Info Card
  infoCard: {
    marginHorizontal: 20,
    marginTop: 28,
    padding: 24,
    borderRadius: 20,
    alignItems: 'center',
    borderWidth: 1,
  },
  infoLogoContainer: {
    width: 60,
    height: 60,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
  },
  infoAppName: {
    fontSize: 20,
    fontWeight: '800',
    letterSpacing: 1.5,
  },
  infoTagline: {
    fontSize: 13,
    fontStyle: 'italic',
    marginTop: 4,
  },
  versionBadge: {
    paddingHorizontal: 14,
    paddingVertical: 5,
    borderRadius: 20,
    marginTop: 12,
  },
  versionText: {
    fontSize: 12,
    fontWeight: '600',
  },
  copyrightText: {
    fontSize: 11,
    marginTop: 14,
  },
});