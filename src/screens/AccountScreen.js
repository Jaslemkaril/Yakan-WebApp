import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, TextInput, Modal, Alert, Animated } from 'react-native';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';
import ApiService from '../services/api';

const AccountScreen = ({ navigation }) => {
  const { isLoggedIn, userInfo, logout, updateUserInfo, setUserInfo } = useCart();
  const { theme } = useTheme();
  const styles = getStyles(theme);
  
  // Debug logging
  console.log('[AccountScreen] Render - isLoggedIn:', isLoggedIn);
  console.log('[AccountScreen] Render - userInfo:', JSON.stringify(userInfo, null, 2));
  
  const [isEditModalVisible, setIsEditModalVisible] = useState(false);
  const [editedName, setEditedName] = useState(userInfo?.name || '');
  const [editedEmail, setEditedEmail] = useState(userInfo?.email || '');
  const [isLoading, setIsLoading] = useState(false);

  // Fetch user data when screen loads or when screen comes into focus
  useEffect(() => {
    const fetchUserData = async () => {
      if (isLoggedIn) {
        try {
          setIsLoading(true);
          console.log('[AccountScreen] Fetching user data...');
          const response = await ApiService.getCurrentUser();
          console.log('[AccountScreen] API Response:', JSON.stringify(response, null, 2));
          
          if (response.success) {
            const userData = response.data?.user || response.data;
            console.log('[AccountScreen] Extracted user data:', JSON.stringify(userData, null, 2));
            setUserInfo(userData);
            // Force update the local state
            setEditedName(userData?.name || '');
            setEditedEmail(userData?.email || '');
          } else {
            console.error('[AccountScreen] Failed to fetch user:', response.error);
          }
        } catch (error) {
          console.error('[AccountScreen] Error fetching user:', error);
        } finally {
          setIsLoading(false);
        }
      }
    };
    
    fetchUserData();
    
    // Also fetch when screen comes into focus
    const unsubscribe = navigation.addListener('focus', () => {
      console.log('[AccountScreen] Screen focused, refreshing user data');
      fetchUserData();
    });
    
    return unsubscribe;
  }, [isLoggedIn, navigation]);

  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: () => {
            logout();
            navigation.reset({ index: 0, routes: [{ name: 'Auth' }] });
          },
        },
      ]
    );
  };

  const handleSaveProfile = () => {
    if (!editedName.trim() || !editedEmail.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    updateUserInfo({ name: editedName, email: editedEmail });
    setIsEditModalVisible(false);
    Alert.alert('Success', 'Profile updated successfully!');
  };

  const getInitials = (name) => {
    if (!name) return 'U';
    const names = name.split(' ');
    if (names.length >= 2) {
      return (names[0][0] + names[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
  };

  // Show login prompt if not logged in
  if (!isLoggedIn) {
    return (
      <View style={[styles.container, { backgroundColor: theme.background }]}>
        <ScreenHeader 
          title="Account" 
          navigation={navigation}
          showBack={false}
          showHamburger={true}
        />

        <View style={styles.notLoggedInContainer}>
          <View style={styles.notLoggedInContent}>
            <Text style={styles.lockIcon}></Text>
            <Text style={styles.notLoggedInTitle}>You're not logged in</Text>
            <Text style={styles.notLoggedInText}>
              Please login to access your account and view your profile
            </Text>
            
            <TouchableOpacity
              style={styles.loginButton}
              onPress={() => navigation.navigate('Login')}
            >
              <Text style={styles.loginButtonText}>Login</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.registerButton}
              onPress={() => navigation.navigate('Register')}
            >
              <Text style={styles.registerButtonText}>Create Account</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    );
  }

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Account" 
        navigation={navigation}
        showBack={false}
        showHamburger={true}
      />
      
      <ScrollView style={styles.content}>
        <View style={styles.profileSection}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {getInitials((userInfo?.data?.name || userInfo?.name) || 
                          ((userInfo?.data?.first_name || userInfo?.first_name) && (userInfo?.data?.last_name || userInfo?.last_name) 
                            ? `${userInfo?.data?.first_name || userInfo?.first_name} ${userInfo?.data?.last_name || userInfo?.last_name}` 
                            : 'User'))}
            </Text>
          </View>
          <Text style={styles.userName}>
            {(userInfo?.data?.name || userInfo?.name) || 
             ((userInfo?.data?.first_name || userInfo?.first_name) && (userInfo?.data?.last_name || userInfo?.last_name) 
               ? `${userInfo?.data?.first_name || userInfo?.first_name} ${userInfo?.data?.last_name || userInfo?.last_name}` 
               : (userInfo?.data?.first_name || userInfo?.first_name) || (userInfo?.data?.last_name || userInfo?.last_name) || 'User')}
          </Text>
          <Text style={styles.userEmail}>{userInfo?.data?.email || userInfo?.email || 'email@example.com'}</Text>
          
          {isLoading && <Text style={styles.loadingText}>Loading...</Text>}
          
          <TouchableOpacity
            style={styles.editProfileButton}
            onPress={() => {
              const name = (userInfo?.data?.name || userInfo?.name) || 
                          ((userInfo?.data?.first_name || userInfo?.first_name) && (userInfo?.data?.last_name || userInfo?.last_name) 
                            ? `${userInfo?.data?.first_name || userInfo?.first_name} ${userInfo?.data?.last_name || userInfo?.last_name}` 
                            : '');
              const email = userInfo?.data?.email || userInfo?.email || '';
              setEditedName(name);
              setEditedEmail(email);
              setIsEditModalVisible(true);
            }}
            activeOpacity={0.7}
          >
            <MaterialCommunityIcons name="pencil-outline" size={16} color="#8B1A1A" style={{ marginRight: 6 }} />
            <Text style={styles.editProfileText}>Edit Profile</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.menuSection}>
          <TouchableOpacity 
            style={styles.menuItem}
            onPress={() => navigation.navigate('Orders')}
            activeOpacity={0.6}
          >
            <View style={styles.menuItemLeft}>
              <MaterialCommunityIcons name="package-box-multiple" size={24} color="#8B1A1A" style={styles.menuIcon} />
              <Text style={styles.menuText}>My Orders</Text>
            </View>
            <Ionicons name="chevron-forward" size={20} color={theme.iconMuted} />
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.menuItem}
            onPress={() => navigation.navigate('Wishlist')}
            activeOpacity={0.6}
          >
            <View style={styles.menuItemLeft}>
              <MaterialCommunityIcons name="heart-outline" size={24} color="#E74C3C" style={styles.menuIcon} />
              <Text style={styles.menuText}>My Wishlists</Text>
            </View>
            <Ionicons name="chevron-forward" size={20} color={theme.iconMuted} />
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.menuItem}
            onPress={() => navigation.navigate('SavedAddresses')}
            activeOpacity={0.6}
          >
            <View style={styles.menuItemLeft}>
              <MaterialCommunityIcons name="map-marker-multiple" size={24} color="#3498DB" style={styles.menuIcon} />
              <Text style={styles.menuText}>Saved Addresses</Text>
            </View>
            <Ionicons name="chevron-forward" size={20} color={theme.iconMuted} />
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={styles.menuItem}
            onPress={() => navigation.navigate('PaymentMethods')}
            activeOpacity={0.6}
          >
            <View style={styles.menuItemLeft}>
              <MaterialCommunityIcons name="credit-card-multiple" size={24} color="#27AE60" style={styles.menuIcon} />
              <Text style={styles.menuText}>Payment Methods</Text>
            </View>
            <Ionicons name="chevron-forward" size={20} color={theme.iconMuted} />
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={[styles.menuItem, styles.lastMenuItem]}
            onPress={() => navigation.navigate('Settings')}
            activeOpacity={0.6}
          >
            <View style={styles.menuItemLeft}>
              <MaterialCommunityIcons name="cog-outline" size={24} color="#95A5A6" style={styles.menuIcon} />
              <Text style={styles.menuText}>Settings</Text>
            </View>
            <Ionicons name="chevron-forward" size={20} color={theme.iconMuted} />
          </TouchableOpacity>
        </View>

        <TouchableOpacity 
          style={styles.logoutButton}
          onPress={handleLogout}
          activeOpacity={0.7}
        >
          <MaterialCommunityIcons name="logout" size={20} color="#FF6B6B" style={{ marginRight: 8 }} />
          <Text style={styles.logoutText}>Log Out</Text>
        </TouchableOpacity>
      </ScrollView>

      {/* Edit Profile Modal */}
      <Modal
        visible={isEditModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setIsEditModalVisible(false)}
      >
        <TouchableOpacity 
          style={styles.modalOverlay}
          activeOpacity={1}
          onPress={() => setIsEditModalVisible(false)}
        >
          <TouchableOpacity 
            activeOpacity={1}
            style={styles.modalContent}
            onPress={() => {}} // Prevent closing when tapping inside modal
          >
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Edit Profile</Text>
              <TouchableOpacity onPress={() => setIsEditModalVisible(false)}>
                <MaterialCommunityIcons name="close" size={26} color="#BDC3C7" />
              </TouchableOpacity>
            </View>

            <View style={styles.inputContainer}>
              <Text style={styles.inputLabel}>Full Name</Text>
              <TextInput
                style={styles.input}
                value={editedName}
                onChangeText={setEditedName}
                placeholder="Enter your name"
                placeholderTextColor="#BDC3C7"
              />
            </View>

            <View style={styles.inputContainer}>
              <Text style={styles.inputLabel}>Email</Text>
              <TextInput
                style={styles.input}
                value={editedEmail}
                onChangeText={setEditedEmail}
                placeholder="Enter your email"
                placeholderTextColor="#BDC3C7"
                keyboardType="email-address"
                autoCapitalize="none"
              />
            </View>

            <View style={styles.modalButtons}>
              <TouchableOpacity
                style={styles.cancelButton}
                onPress={() => setIsEditModalVisible(false)}
                activeOpacity={0.7}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.saveButton}
                onPress={handleSaveProfile}
                activeOpacity={0.8}
              >
                <Text style={styles.saveButtonText}>Save Changes</Text>
              </TouchableOpacity>
            </View>
          </TouchableOpacity>
        </TouchableOpacity>
      </Modal>
    </View>
  );
};

const getStyles = (theme) => StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: theme.background,
  },
  content: {
    flex: 1,
    paddingVertical: 10,
  },
  // Not Logged In Styles
  notLoggedInContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
    backgroundColor: theme.background,
  },
  notLoggedInContent: {
    backgroundColor: theme.cardBackground,
    borderRadius: 20,
    padding: 40,
    alignItems: 'center',
    width: '100%',
    maxWidth: 400,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 8,
  },
  lockIcon: {
    fontSize: 80,
    marginBottom: 25,
  },
  notLoggedInTitle: {
    fontSize: 26,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 12,
    textAlign: 'center',
  },
  notLoggedInText: {
    fontSize: 16,
    color: theme.textSecondary,
    textAlign: 'center',
    marginBottom: 35,
    lineHeight: 24,
    fontWeight: '500',
  },
  loginButton: {
    backgroundColor: theme.primary,
    paddingVertical: 16,
    paddingHorizontal: 50,
    borderRadius: 12,
    width: '100%',
    marginBottom: 15,
    elevation: 3,
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
  },
  loginButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '700',
    textAlign: 'center',
    letterSpacing: 0.3,
  },
  registerButton: {
    backgroundColor: theme.cardBackground,
    paddingVertical: 16,
    paddingHorizontal: 50,
    borderRadius: 12,
    borderWidth: 2.5,
    borderColor: theme.primary,
    width: '100%',
  },
  registerButtonText: {
    color: theme.primary,
    fontSize: 18,
    fontWeight: '700',
    textAlign: 'center',
    letterSpacing: 0.3,
  },
  // Profile Styles
  profileSection: {
    backgroundColor: theme.cardBackground,
    marginHorizontal: 15,
    marginVertical: 15,
    padding: 30,
    alignItems: 'center',
    borderRadius: 16,
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: theme.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
    elevation: 5,
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
    borderWidth: 2,
    borderColor: theme.cardBackground,
  },
  avatarText: {
    color: '#fff',
    fontSize: 42,
    fontWeight: '700',
    letterSpacing: 1,
  },
  userName: {
    fontSize: 24,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 6,
    textAlign: 'center',
  },
  userEmail: {
    fontSize: 14,
    color: theme.textMuted,
    marginBottom: 22,
    textAlign: 'center',
    fontWeight: '500',
  },
  loadingText: {
    fontSize: 14,
    color: theme.primary,
    fontWeight: '600',
    marginBottom: 15,
  },
  editProfileButton: {
    marginTop: 15,
    paddingVertical: 11,
    paddingHorizontal: 28,
    backgroundColor: theme.surfaceBg,
    borderRadius: 22,
    borderWidth: 1.5,
    borderColor: theme.primary,
    flexDirection: 'row',
    alignItems: 'center',
  },
  editProfileText: {
    color: theme.primary,
    fontSize: 15,
    fontWeight: '700',
    marginLeft: 6,
    letterSpacing: 0.3,
  },
  menuSection: {
    backgroundColor: theme.cardBackground,
    marginHorizontal: 15,
    marginVertical: 10,
    borderRadius: 16,
    overflow: 'hidden',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
  },
  menuItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 18,
    borderBottomWidth: 1,
    borderBottomColor: theme.borderLight,
    backgroundColor: theme.cardBackground,
  },
  lastMenuItem: {
    borderBottomWidth: 0,
  },
  menuItemLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  menuIcon: {
    marginRight: 16,
    width: 28,
  },
  menuText: {
    fontSize: 16,
    color: theme.text,
    fontWeight: '600',
    flex: 1,
  },
  logoutButton: {
    backgroundColor: theme.dangerBg,
    padding: 16,
    alignItems: 'center',
    marginHorizontal: 15,
    marginVertical: 20,
    marginBottom: 30,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: theme.dangerBorder,
    flexDirection: 'row',
    justifyContent: 'center',
    elevation: 2,
    shadowColor: '#FF6B6B',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
  },
  logoutText: {
    color: theme.dangerText,
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.3,
  },
  // Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(44, 62, 80, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: theme.cardBackground,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 28,
    maxHeight: '80%',
    paddingBottom: 35,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 25,
  },
  modalTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: theme.text,
  },
  modalClose: {
    fontSize: 28,
    color: theme.iconMuted,
    fontWeight: '300',
  },
  inputContainer: {
    marginBottom: 22,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '700',
    color: theme.text,
    marginBottom: 10,
    letterSpacing: 0.3,
  },
  input: {
    borderWidth: 1.5,
    borderColor: theme.inputBorder,
    borderRadius: 12,
    padding: 15,
    fontSize: 16,
    color: theme.text,
    backgroundColor: theme.inputBg,
    fontWeight: '500',
  },
  modalButtons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 28,
    gap: 12,
  },
  cancelButton: {
    flex: 1,
    padding: 15,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: theme.inputBorder,
    backgroundColor: theme.inputBg,
  },
  cancelButtonText: {
    color: theme.text,
    fontSize: 16,
    fontWeight: '700',
    textAlign: 'center',
    letterSpacing: 0.3,
  },
  saveButton: {
    flex: 1,
    padding: 15,
    borderRadius: 12,
    backgroundColor: theme.primary,
    elevation: 3,
    shadowColor: theme.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
  },
  saveButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
    textAlign: 'center',
    letterSpacing: 0.3,
  },
});

export default AccountScreen;