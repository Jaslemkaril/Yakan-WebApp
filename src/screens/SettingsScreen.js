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
} from 'react-native';
import Header from '../components/Header';
import colors from '../constants/colors';

export default function SettingsScreen({ navigation }) {
  const [notifications, setNotifications] = useState(true);
  const [emailUpdates, setEmailUpdates] = useState(false);
  const [darkMode, setDarkMode] = useState(false);

  const handleClearCache = () => {
    Alert.alert(
      'Clear Cache',
      'Are you sure you want to clear app cache?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Clear',
          onPress: () => Alert.alert('Success', 'Cache cleared successfully'),
        },
      ]
    );
  };

  const settingsSections = [
    {
      title: 'Notifications',
      items: [
        {
          label: 'Push Notifications',
          value: notifications,
          onValueChange: setNotifications,
        },
        {
          label: 'Email Updates',
          value: emailUpdates,
          onValueChange: setEmailUpdates,
        },
      ],
    },
    {
      title: 'Appearance',
      items: [
        {
          label: 'Dark Mode',
          value: darkMode,
          onValueChange: setDarkMode,
        },
      ],
    },
  ];

  const actionItems = [
    {
      icon: '',
      label: 'Clear Cache',
      onPress: handleClearCache,
    },
    {
      icon: '',
      label: 'Privacy Policy',
      onPress: () => Alert.alert('Privacy Policy', 'Privacy policy content here'),
    },
    {
      icon: '📋',
      label: 'Terms of Service',
      onPress: () => Alert.alert('Terms of Service', 'Terms of service content here'),
    },
    {
      icon: 'ℹ',
      label: 'About',
      onPress: () => Alert.alert('About', 'TUWAS #YAKAN\nVersion 1.0.0\n\nPreserving Yakan weaving traditions'),
    },
  ];

  return (
    <View style={styles.container}>
      <Header navigation={navigation} title="Settings" showBack={true} />

      <ScrollView 
        style={styles.content}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Toggle Settings */}
        {settingsSections.map((section, sectionIndex) => (
          <View key={sectionIndex} style={styles.section}>
            <Text style={styles.sectionTitle}>{section.title}</Text>
            <View style={styles.settingsGroup}>
              {section.items.map((item, itemIndex) => (
                <View
                  key={itemIndex}
                  style={[
                    styles.settingItem,
                    itemIndex === section.items.length - 1 && styles.settingItemLast,
                  ]}
                >
                  <Text style={styles.settingLabel}>{item.label}</Text>
                  <Switch
                    value={item.value}
                    onValueChange={item.onValueChange}
                    trackColor={{ false: '#D1D5DB', true: colors.primary }}
                    thumbColor={colors.white}
                  />
                </View>
              ))}
            </View>
          </View>
        ))}

        {/* Action Items */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>More</Text>
          <View style={styles.settingsGroup}>
            {actionItems.map((item, index) => (
              <TouchableOpacity
                key={index}
                style={[
                  styles.actionItem,
                  index === actionItems.length - 1 && styles.actionItemLast,
                ]}
                onPress={item.onPress}
              >
                <Text style={styles.actionIcon}>{item.icon}</Text>
                <Text style={styles.actionLabel}>{item.label}</Text>
                <Text style={styles.actionArrow}>›</Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* App Info */}
        <View style={styles.infoSection}>
          <Text style={styles.infoText}>TUWAS #YAKAN</Text>
          <Text style={styles.infoSubtext}>Weaving through generations</Text>
          <Text style={styles.versionText}>Version 1.0.0</Text>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  content: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 30,
  },
  section: {
    marginTop: 25,
    paddingHorizontal: 20,
  },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: colors.textLight,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 10,
  },
  settingsGroup: {
    backgroundColor: colors.white,
    borderRadius: 10,
    overflow: 'hidden',
  },
  settingItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 15,
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  settingItemLast: {
    borderBottomWidth: 0,
  },
  settingLabel: {
    fontSize: 16,
    color: colors.text,
  },
  actionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  actionItemLast: {
    borderBottomWidth: 0,
  },
  actionIcon: {
    fontSize: 24,
    marginRight: 15,
  },
  actionLabel: {
    flex: 1,
    fontSize: 16,
    color: colors.text,
  },
  actionArrow: {
    fontSize: 24,
    color: colors.textLight,
  },
  infoSection: {
    alignItems: 'center',
    marginTop: 40,
    paddingHorizontal: 20,
  },
  infoText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: colors.text,
    marginBottom: 5,
  },
  infoSubtext: {
    fontSize: 14,
    color: colors.textLight,
    fontStyle: 'italic',
    marginBottom: 10,
  },
  versionText: {
    fontSize: 12,
    color: colors.textLight,
  },
});