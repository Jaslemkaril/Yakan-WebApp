// src/screens/LoginScreen.js
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Alert,
  ActivityIndicator,
  ImageBackground,
} from 'react-native';
import * as Google from 'expo-auth-session/providers/google';
import * as WebBrowser from 'expo-web-browser';
import { Ionicons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';
import ApiService from '../services/api';
import colors from '../constants/colors';

WebBrowser.maybeCompleteAuthSession();

export default function LoginScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { loginWithBackend, setUserInfo } = useCart();

  // Google OAuth Configuration
  const [request, response, promptAsync] = Google.useAuthRequest({
    expoClientId: '406025644598-hsd1i2a5lnrvjpdi3lrdlfblhd1bmf2s.apps.googleusercontent.com',
    iosClientId: '406025644598-hsd1i2a5lnrvjpdi3lrdlfblhd1bmf2s.apps.googleusercontent.com',
    androidClientId: '406025644598-hsd1i2a5lnrvjpdi3lrdlfblhd1bmf2s.apps.googleusercontent.com',
    webClientId: '406025644598-hsd1i2a5lnrvjpdi3lrdlfblhd1bmf2s.apps.googleusercontent.com',
  });

  useEffect(() => {
    if (response?.type === 'success') {
      const { authentication } = response;
      getUserInfo(authentication.accessToken);
    }
  }, [response]);

  const getUserInfo = async (token) => {
    try {
      setIsLoading(true);
      const response = await fetch('https://www.googleapis.com/userinfo/v2/me', {
        headers: { Authorization: `Bearer ${token}` },
      });
      const user = await response.json();
      
      console.log('[Google Login] User info:', user);
      
      // Send to backend
      const loginResponse = await ApiService.loginWithGoogle({
        idToken: token,
        id: user.id,
        email: user.email,
        name: user.name,
        photo: user.picture,
      });

      if (loginResponse.success) {
        const userData = loginResponse.data?.user || loginResponse.data?.data?.user;
        if (userData) {
          await setUserInfo(userData);
        }
        
        Alert.alert('Success', 'Logged in with Google!', [
          {
            text: 'OK',
            onPress: () => navigation.navigate('Home')
          }
        ]);
      } else {
        Alert.alert('Error', loginResponse.message || 'Google login failed');
      }
    } catch (error) {
      console.error('[Google Login] Error:', error);
      Alert.alert('Error', 'Failed to login with Google');
    } finally {
      setIsLoading(false);
    }
  };

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }
    
    console.log('[LoginScreen] Starting login with email:', email);
    setIsLoading(true);
    
    try {
      console.log('[LoginScreen] Calling loginWithBackend');
      const result = await loginWithBackend(email, password);
      console.log('[LoginScreen] Login result:', result);
      
      if (result.success) {
        console.log('[LoginScreen] Login successful, navigating to Home');
        Alert.alert('Success', 'Login successful!', [
          {
            text: 'OK',
            onPress: () => navigation.navigate('Home')
          }
        ]);
      } else {
        console.log('[LoginScreen] Login failed:', result.message);
        Alert.alert('Login Failed', result.message || 'Invalid email or password');
      }
    } catch (error) {
      console.error('[LoginScreen] Login error:', error);
      Alert.alert('Error', error.message || 'An error occurred during login');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <ImageBackground
          source={require('../assets/images/RL.jpg')}
          style={styles.backgroundContainer}
          resizeMode="cover"
        >
          <View style={styles.overlay}>
            {/* Logo Section */}
            <View style={styles.logoContainer}>
              <Text style={styles.logoText}>TUWAS</Text>
              <Text style={styles.logoSubtext}>#YAKAN</Text>
              <Text style={styles.tagline}>weaving through generations</Text>
            </View>

            {/* Login Form */}
            <View style={styles.formContainer}>
              <Text style={styles.title}>Login</Text>

              <TextInput
                style={styles.input}
                placeholder="Email"
                placeholderTextColor={colors.placeholder}
                value={email}
                onChangeText={setEmail}
                keyboardType="email-address"
                autoCapitalize="none"
                editable={!isLoading}
              />

              <TextInput
                style={styles.input}
                placeholder="Password"
                placeholderTextColor={colors.placeholder}
                value={password}
                onChangeText={setPassword}
                secureTextEntry
                editable={!isLoading}
              />

              <TouchableOpacity 
                style={styles.forgotPassword} 
                disabled={isLoading}
                onPress={() => navigation.navigate('ForgotPassword')}
              >
                <Text style={styles.forgotPasswordText}>Forgot Password?</Text>
              </TouchableOpacity>

              <TouchableOpacity 
                style={[styles.loginButton, isLoading && styles.buttonDisabled]} 
                onPress={handleLogin}
                disabled={isLoading}
              >
                {isLoading ? (
                  <ActivityIndicator color={colors.white} />
                ) : (
                  <Text style={styles.loginButtonText}>LOGIN</Text>
                )}
              </TouchableOpacity>

              {/* Divider */}
              <View style={styles.dividerContainer}>
                <View style={styles.divider} />
                <Text style={styles.dividerText}>OR</Text>
                <View style={styles.divider} />
              </View>

              {/* Google Sign In */}
              <TouchableOpacity 
                style={styles.googleButton}
                onPress={() => promptAsync()}
                disabled={isLoading || !request}
              >
                <Ionicons name="logo-google" size={20} color="#DB4437" style={styles.googleIcon} />
                <Text style={styles.googleButtonText}>Sign in with Google</Text>
              </TouchableOpacity>

              <View style={styles.registerContainer}>
                <Text style={styles.registerText}>Don't have an account? </Text>
                <TouchableOpacity 
                  onPress={() => navigation.navigate('Register')}
                  disabled={isLoading}
                >
                  <Text style={styles.registerLink}>Register</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </ImageBackground>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
  },
  backgroundContainer: {
    flex: 1,
    width: '100%',
    minHeight: '100%',
  },
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(128, 0, 32, 0.85)',
    justifyContent: 'center',
    paddingHorizontal: 30,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 50,
  },
  logoText: {
    fontSize: 48,
    fontWeight: 'bold',
    color: colors.white,
    letterSpacing: 2,
  },
  logoSubtext: {
    fontSize: 36,
    fontWeight: 'bold',
    color: colors.white,
    letterSpacing: 2,
  },
  tagline: {
    fontSize: 14,
    color: colors.white,
    marginTop: 5,
    fontStyle: 'italic',
  },
  formContainer: {
    backgroundColor: colors.white,
    borderRadius: 20,
    padding: 25,
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: colors.primary,
    marginBottom: 20,
    textAlign: 'center',
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
    fontSize: 16,
    color: colors.text,
  },
  forgotPassword: {
    alignSelf: 'flex-end',
    marginBottom: 20,
  },
  forgotPasswordText: {
    color: colors.primary,
    fontSize: 14,
  },
  loginButton: {
    backgroundColor: colors.primary,
    borderRadius: 10,
    padding: 15,
    alignItems: 'center',
    marginBottom: 15,
  },
  loginButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  dividerContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 20,
  },
  divider: {
    flex: 1,
    height: 1,
    backgroundColor: colors.border,
  },
  dividerText: {
    color: colors.textLight,
    paddingHorizontal: 15,
    fontSize: 14,
    fontWeight: '600',
  },
  googleButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.white,
    borderWidth: 1.5,
    borderColor: '#DB4437',
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
  },
  googleIcon: {
    marginRight: 10,
  },
  googleButtonText: {
    color: '#DB4437',
    fontSize: 16,
    fontWeight: '600',
  },
  registerContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 10,
  },
  registerText: {
    color: colors.textLight,
    fontSize: 14,
  },
  registerLink: {
    color: colors.primary,
    fontSize: 14,
    fontWeight: 'bold',
  },
});