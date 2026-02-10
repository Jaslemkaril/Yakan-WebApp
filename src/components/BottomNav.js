// src/components/BottomNav.js
import React from 'react';
import { View, TouchableOpacity, Text, StyleSheet } from 'react-native';
import { Home, ShoppingBag, User, MessageCircle, Scissors } from 'lucide-react-native';
import { useTheme } from '../context/ThemeContext';

const BottomNav = ({ navigation, activeRoute }) => {
  const { theme, isDarkMode } = useTheme();
  const navItems = [
    { route: 'Home', icon: Home, label: 'Home' },
    { route: 'Products', icon: ShoppingBag, label: 'Products' },
    { route: 'CustomOrder', icon: Scissors, label: 'Custom' },
    { route: 'Chat', icon: MessageCircle, label: 'Chat' },
    { route: 'Account', icon: User, label: 'Profile' },
  ];

  return (
    <View style={[styles.container, { 
      backgroundColor: isDarkMode ? '#1E1E1E' : '#FFFFFF',
      borderTopColor: isDarkMode ? '#333' : '#E5E7EB',
    }]}>
      {navItems.map((item) => {
        const isActive = activeRoute === item.route;
        const IconComponent = item.icon;
        
        return (
          <TouchableOpacity
            key={item.route}
            style={styles.tab}
            onPress={() => navigation.navigate(item.route)}
            activeOpacity={0.7}
          >
            <View style={[styles.iconContainer, isActive && { backgroundColor: isDarkMode ? '#3D1515' : '#FEE2E2' }]}>
              <IconComponent
                size={24}
                color={isActive ? theme.primary : (isDarkMode ? '#777' : '#9CA3AF')}
                strokeWidth={isActive ? 2.5 : 2}
              />
            </View>
            <Text style={[
              styles.label, 
              { color: isDarkMode ? '#777' : '#9CA3AF' },
              isActive && { color: theme.primary, fontWeight: '700' }
            ]}>
              {item.label}
            </Text>
          </TouchableOpacity>
        );
      })}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    paddingBottom: 8,
    paddingTop: 12,
    elevation: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -3 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 4,
  },
  iconContainer: {
    width: 44,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 14,
    marginBottom: 4,
    backgroundColor: 'transparent',
  },
  activeIconContainer: {
    backgroundColor: '#FEE2E2',
  },
  label: {
    fontSize: 11,
    color: '#9CA3AF',
    fontWeight: '500',
    marginTop: 2,
  },
  activeLabel: {
    color: '#8B1A1A',
    fontWeight: '700',
  },
});

export default BottomNav;