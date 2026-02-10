import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Modal, ScrollView } from 'react-native';
import { MaterialCommunityIcons, Ionicons } from '@expo/vector-icons';
import { useTheme } from '../context/ThemeContext';

const ScreenHeader = ({ 
  title, 
  navigation, 
  showBack = true, 
  showHamburger = true,
  rightIcon = null,
  onRightIconPress = null,
  backgroundColor,
  paddingTop = 50,
  showCartCount = false,
  cartCount = 0,
}) => {
  const [menuVisible, setMenuVisible] = useState(false);
  const { theme, isDarkMode } = useTheme();
  const headerBg = backgroundColor || theme.headerBg;

  const handleHamburgerPress = () => {
    setMenuVisible(true);
  };

  const handleMenuItemPress = (screenName) => {
    setMenuVisible(false);
    if (navigation && navigation.navigate) {
      navigation.navigate(screenName);
    }
  };

  const handleBackPress = () => {
    if (navigation && navigation.goBack) {
      navigation.goBack();
    }
  };

  const menuItems = [
    { label: 'Home', icon: 'home', screen: 'Home' },
    { label: 'Products', icon: 'shopping', screen: 'Products' },
    { label: 'My Orders', icon: 'package-multiple', screen: 'Orders' },
    { label: 'Wishlist', icon: 'heart', screen: 'Wishlist' },
    { label: 'Cart', icon: 'cart', screen: 'Cart' },
    { label: 'Account', icon: 'account', screen: 'Account' },
    { label: 'Custom Order', icon: 'pencil-box', screen: 'CustomOrder' },
    { label: 'Cultural Heritage', icon: 'palette', screen: 'CulturalHeritage' },
    { label: 'Settings', icon: 'cog', screen: 'Settings' },
  ];

  return (
    <>
      <View style={[styles.header, { backgroundColor: headerBg, paddingTop }]}>
        <View style={styles.headerContent}>
          {/* Left Section: Hamburger Menu */}
          {showHamburger && (
            <TouchableOpacity 
              style={styles.iconButton}
              onPress={handleHamburgerPress}
              activeOpacity={0.7}
            >
              <MaterialCommunityIcons name="menu" size={28} color="#fff" />
            </TouchableOpacity>
          )}

          {/* Center Section: Back Button and Title */}
          <View style={styles.centerContent}>
            {showBack && (
              <TouchableOpacity 
                style={styles.backButton}
                onPress={handleBackPress}
                activeOpacity={0.7}
              >
                <Ionicons name="arrow-back" size={24} color="#fff" />
              </TouchableOpacity>
            )}
            <Text style={[styles.title, !showBack && { marginLeft: 0 }]}>
              {title}
            </Text>
          </View>

          {/* Right Section: Cart or Custom Icon */}
          {rightIcon ? (
            <TouchableOpacity 
              style={styles.iconButton}
              onPress={onRightIconPress}
              activeOpacity={0.7}
            >
              {rightIcon}
              {showCartCount && cartCount > 0 && (
                <View style={styles.cartBadge}>
                  <Text style={styles.cartBadgeText}>
                    {cartCount > 9 ? '9+' : cartCount}
                  </Text>
                </View>
              )}
            </TouchableOpacity>
          ) : (
            <View style={styles.iconButton} />
          )}
        </View>
      </View>

      {/* Menu Modal */}
      <Modal
        visible={menuVisible}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setMenuVisible(false)}
      >
        <View style={[styles.menuOverlay, { backgroundColor: isDarkMode ? 'rgba(0,0,0,0.7)' : 'rgba(0,0,0,0.5)' }]}>
          <View style={[styles.menuContainer, { backgroundColor: theme.cardBackground }]}>
            <View style={[styles.menuHeader, { borderBottomColor: theme.border }]}>
              <Text style={[styles.menuTitle, { color: theme.text }]}>Navigation</Text>
              <TouchableOpacity onPress={() => setMenuVisible(false)}>
                <MaterialCommunityIcons name="close" size={28} color={theme.primary} />
              </TouchableOpacity>
            </View>
            
            <ScrollView style={styles.menuList}>
              {menuItems.map((item, index) => (
                <TouchableOpacity
                  key={index}
                  style={[styles.menuItem, { borderBottomColor: isDarkMode ? '#2A2A2A' : '#f5f5f5' }]}
                  onPress={() => handleMenuItemPress(item.screen)}
                >
                  <MaterialCommunityIcons 
                    name={item.icon} 
                    size={24} 
                    color={theme.primary} 
                    style={styles.menuItemIcon}
                  />
                  <Text style={[styles.menuItemText, { color: theme.text }]}>{item.label}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        </View>
      </Modal>
    </>
  );
};

const styles = StyleSheet.create({
  header: {
    backgroundColor: '#8B1A1A',
    paddingTop: 50,
    paddingBottom: 15,
    paddingHorizontal: 16,
    elevation: 8,
    shadowColor: '#8B1A1A',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
  },
  headerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  centerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    justifyContent: 'flex-start',
  },
  iconButton: {
    padding: 8,
    borderRadius: 8,
  },
  backButton: {
    padding: 8,
    marginRight: 12,
  },
  title: {
    color: '#fff',
    fontSize: 22,
    fontWeight: '700',
    letterSpacing: 0.5,
    marginLeft: 8,
  },
  cartBadge: {
    position: 'absolute',
    top: 0,
    right: 0,
    backgroundColor: '#FF6B6B',
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#fff',
  },
  cartBadgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: '700',
  },
  menuOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
  },
  menuContainer: {
    flex: 1,
    backgroundColor: '#fff',
    marginTop: 90,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    paddingTop: 0,
    elevation: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
  },
  menuHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  menuTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1a1a1a',
  },
  menuList: {
    flex: 1,
    paddingVertical: 12,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#f5f5f5',
  },
  menuItemIcon: {
    marginRight: 16,
  },
  menuItemText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1a1a1a',
  },
});

export default ScreenHeader;
