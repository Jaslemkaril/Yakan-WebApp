import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  SafeAreaView,
} from 'react-native';
import { MaterialCommunityIcons, Ionicons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';

const CustomDrawerContent = ({ navigation, state }) => {
  const { isLoggedIn, userInfo, logout } = useCart();

  const menuItems = [
    { icon: 'home-outline', label: 'Home', screen: 'Home' },
    { icon: 'shopping-outline', label: 'Products', screen: 'Products' },
    { icon: 'heart-outline', label: 'Wishlist', screen: 'Wishlist' },
    { icon: 'android-box', label: 'Custom Order', screen: 'CustomOrder' },
    { icon: 'palette-outline', label: 'Cultural Items', screen: 'CulturalHeritage' },
    { icon: 'package-variant-closed', label: 'My Orders', screen: 'Orders' },
    { icon: 'account-outline', label: 'Account', screen: 'Account' },
    { icon: 'bell-outline', label: 'Notifications', screen: 'Notifications' },
    { icon: 'chat-outline', label: 'Chat', screen: 'Chat' },
    { icon: 'cog-outline', label: 'Settings', screen: 'Settings' },
  ];

  const handleNavigation = (screenName) => {
    // Navigate to the HomeStack first, then to the specific screen
    navigation.navigate('HomeStack', { screen: screenName });
    // Close the drawer after navigation
    setTimeout(() => {
      navigation.closeDrawer?.();
    }, 300);
  };

  const handleLogout = () => {
    logout();
    navigation.navigate('Home');
  };

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.drawerHeader}>
        <TouchableOpacity onPress={() => navigation.closeDrawer()}>
          <Ionicons name="close" size={28} color="#8B1A1A" />
        </TouchableOpacity>
      </View>

      {/* User Info */}
      {isLoggedIn && userInfo && (
        <View style={styles.userInfo}>
          <View style={styles.userAvatar}>
            <Text style={styles.userAvatarText}>
              {userInfo?.name?.[0]?.toUpperCase() || 'U'}
            </Text>
          </View>
          <View style={styles.userDetails}>
            <Text style={styles.userName}>
              {userInfo?.name || userInfo?.first_name || 'User'}
            </Text>
            <Text style={styles.userEmail} numberOfLines={1}>
              {userInfo?.email || 'user@example.com'}
            </Text>
          </View>
        </View>
      )}

      {/* Menu Items */}
      <ScrollView style={styles.menuContainer}>
        {menuItems.map((item, index) => {
          // Get the current active screen name (handle nested navigation)
          const currentRoute = state.routes[state.index];
          const isActive = currentRoute?.params?.screen === item.screen || 
                          currentRoute?.name === item.screen ||
                          (currentRoute?.state?.routes[currentRoute.state.index]?.name === item.screen);
          
          return (
            <TouchableOpacity
              key={index}
              style={[
                styles.menuItem,
                isActive && styles.menuItemActive,
              ]}
              onPress={() => handleNavigation(item.screen)}
              activeOpacity={0.7}
            >
              <MaterialCommunityIcons
                name={item.icon}
                size={22}
                color={isActive ? '#8B1A1A' : '#555'}
                style={styles.menuIcon}
              />
              <Text
                style={[
                  styles.menuLabel,
                  isActive && styles.menuLabelActive,
                ]}
              >
                {item.label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </ScrollView>

      {/* Logout Button */}
      {isLoggedIn && (
        <View style={styles.logoutContainer}>
          <TouchableOpacity
            style={styles.logoutButton}
            onPress={handleLogout}
            activeOpacity={0.7}
          >
            <MaterialCommunityIcons name="logout" size={22} color="#FF6B6B" />
            <Text style={styles.logoutText}>Log Out</Text>
          </TouchableOpacity>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F9FA',
  },
  drawerHeader: {
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E8EBED',
  },
  userInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 20,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#E8EBED',
  },
  userAvatar: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#8B1A1A',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  userAvatarText: {
    color: '#fff',
    fontSize: 20,
    fontWeight: '700',
  },
  userDetails: {
    flex: 1,
  },
  userName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#2C3E50',
    marginBottom: 4,
  },
  userEmail: {
    fontSize: 13,
    color: '#7F8C8D',
    fontWeight: '500',
  },
  menuContainer: {
    flex: 1,
    paddingTop: 12,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 20,
    marginHorizontal: 8,
    marginVertical: 4,
    borderRadius: 12,
  },
  menuItemActive: {
    backgroundColor: '#FFF5F5',
  },
  menuIcon: {
    marginRight: 14,
    width: 24,
  },
  menuLabel: {
    fontSize: 15,
    color: '#555',
    fontWeight: '600',
    flex: 1,
  },
  menuLabelActive: {
    color: '#8B1A1A',
    fontWeight: '700',
  },
  logoutContainer: {
    paddingHorizontal: 16,
    paddingBottom: 20,
    borderTopWidth: 1,
    borderTopColor: '#E8EBED',
    paddingTop: 12,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    backgroundColor: '#FFF5F5',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#FFE5E5',
  },
  logoutText: {
    fontSize: 15,
    color: '#FF6B6B',
    fontWeight: '700',
    marginLeft: 12,
  },
});

export default CustomDrawerContent;
