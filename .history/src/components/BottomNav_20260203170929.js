// src/components/BottomNav.js
import React from 'react';
import { View, TouchableOpacity, Text, StyleSheet } from 'react-native';
import { Home, ShoppingBag, User, MessageCircle, Scissors } from 'lucide-react-native';
import colors from '../constants/colors';

const BottomNav = ({ navigation, activeRoute }) => {
  const navItems = [
    { route: 'Home', icon: Home, label: 'Home' },
    { route: 'Products', icon: ShoppingBag, label: 'Products' },
    { route: 'CustomOrder', icon: Scissors, label: 'Custom' },
    { route: 'Chat', icon: MessageCircle, label: 'Chat' },
    { route: 'Account', icon: User, label: 'Profile' },
  ];

  return (
    <View style={styles.container}>
      {navItems.map((item) => {
        const isActive = activeRoute === item.route;
        const IconComponent = item.icon;
        
        return (
          <TouchableOpacity
            key={item.route}
            style={styles.tab}
            onPress={() => navigation.navigate(item.route)}
            activeOpacity={0.65}
          >
            <View style={[styles.iconContainer, isActive && styles.activeIconContainer]}>
              <IconComponent
                size={26}
                color={isActive ? colors.primary : colors.textLight}
                strokeWidth={isActive ? 2.8 : 2.2}
              />
            </View>
            <Text style={[styles.label, isActive && styles.activeLabel]}>
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
    backgroundColor: colors.white,
    borderTopWidth: 1.2,
    borderTopColor: colors.borderLight,
    paddingBottom: 10,
    paddingTop: 14,
    elevation: 12,
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: -4 },
    shadowOpacity: 0.12,
    shadowRadius: 8,
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 6,
  },
  iconContainer: {
    width: 48,
    height: 48,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 16,
    marginBottom: 6,
    backgroundColor: 'transparent',
    transition: 'all 0.2s ease',
  },
  activeIconContainer: {
    backgroundColor: '#FEF2F2',
  },
  label: {
    fontSize: 11,
    color: colors.textLight,
    fontWeight: '600',
    marginTop: 2,
    letterSpacing: -0.2,
  },
  activeLabel: {
    color: colors.primary,
    fontWeight: '700',
  },
});

export default BottomNav;