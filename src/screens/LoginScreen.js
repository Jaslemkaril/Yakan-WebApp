// src/screens/LoginScreen.js
import React, { useState } from 'react';
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
  StatusBar,
  ImageBackground,
  Image,
} from 'react-native';
import * as WebBrowser from 'expo-web-browser';
import { Ionicons, MaterialIcons } from '@expo/vector-icons';
import { useCart } from '../context/CartContext';
import ApiService from '../services/api';
import API_CONFIG from '../config/config';

// Base URL of the deployed Laravel backend (resolved from shared config)
const BACKEND_URL = API_CONFIG.API_BASE_URL.replace(/\/api\/v1$/, '');

// Parse query-string from a URL without relying on the web URL API
const parseQueryParams = (url) => {
  const idx = url.indexOf('?');
  if (idx === -1) return {};
  return url
    .slice(idx + 1)
    .split('&')
    .reduce((acc, pair) => {
      const eqIdx = pair.indexOf('=');
      if (eqIdx === -1) return acc;
      const key = decodeURIComponent(pair.slice(0, eqIdx));
      const val = decodeURIComponent(pair.slice(eqIdx + 1).replace(/\+/g, ' '));
      acc[key] = val;
      return acc;
    }, {});
};

export default function LoginScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [googleLoading, setGoogleLoading] = useState(false);
  const [emailFocused, setEmailFocused] = useState(false);
  const [passwordFocused, setPasswordFocused] = useState(false);
  const { loginWithBackend, login } = useCart();

  // ── Google Sign-In via the same Laravel Socialite flow used on the web ──────
  // Opens the system browser → user authenticates with Google → backend redirects
  // back to yakanapp://auth-callback?token=...&name=...  → app receives the token.
  const handleGoogleLogin = async () => {
    if (googleLoading || isLoading) return;
    setGoogleLoading(true);
    try {
      const result = await WebBrowser.openAuthSessionAsync(
        `${BACKEND_URL}/auth/google/mobile`,
        'yakanapp://auth-callback'
      );

      if (result.type === 'success' && result.url) {
        const params = parseQueryParams(result.url);

        if (params.error) {
          Alert.alert('Google Sign-In Failed', params.error);
          return;
        }

        if (!params.token) {
          Alert.alert('Google Sign-In Failed', 'No authentication token received. Please try again.');
          return;
        }

        // Save the Sanctum token exactly as the regular login does
        await ApiService.saveToken(params.token);

        // Build user object from the URL parameters
        const userData = {
          id: params.id ? Number(params.id) : undefined,
          name: params.name || '',
          email: params.email || '',
          role: params.role || 'user',
          avatar: params.avatar || null,
        };

        login(userData);
      } else if (result.type === 'cancel' || result.type === 'dismiss') {
        // User closed the browser — silent, no error
      } else {
        Alert.alert('Google Sign-In', 'Sign-in was cancelled or could not be completed.');
      }
    } catch (error) {
      console.error('[Google Login] Error:', error);
      Alert.alert('Error', 'Could not open Google Sign-In. Please try again.');
    } finally {
      setGoogleLoading(false);
    }
  };
  // ────────────────────────────────────────────────────────────────────────────

  const handleLogin = async () => {
    const trimmedEmail = email.trim();

    if (!trimmedEmail || !password) {
      Alert.alert('Missing Fields', 'Please enter your email and password.');
      return;
    }

    // Basic email format check
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(trimmedEmail)) {
      Alert.alert('Invalid Email', 'Please enter a valid email address.');
      return;
    }

    if (password.length < 6) {
      Alert.alert('Invalid Password', 'Password must be at least 6 characters.');
      return;
    }

    setIsLoading(true);
    try {
      const result = await loginWithBackend(trimmedEmail, password);
      if (!result.success) {
        Alert.alert('Login Failed', result.message || 'Invalid email or password.');
      }
    } catch (error) {
      Alert.alert('Error', error.message || 'An unexpected error occurred. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#800020" />
      <KeyboardAvoidingView
        style={styles.flex}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          {/* Header Background — Yakan textile pattern, same as web hero */}
          <ImageBackground
            source={require('../assets/images/jus.jpg')}
            style={styles.headerBg}
            resizeMode="cover"
          >
            <View style={styles.headerOverlay}>
              {/* Yakan Logo */}
              <View style={styles.logoIconContainer}>
                <Image source={require('../../assets/icon.png')} style={{ width: 56, height: 56, borderRadius: 12 }} resizeMode="contain" />
              </View>
              <Text style={styles.brandName}>Yakan</Text>

              {/* Heritage badge */}
              <View style={styles.loginHeritageBadge}>
                <View style={styles.loginHeritageDot} />
                <Text style={styles.loginHeritageBadgeText}>PHILIPPINE HERITAGE CRAFT</Text>
              </View>

              <Text style={styles.heroTitle}>TUWAS YAKAN</Text>
              <Text style={styles.tagline}>Weaving Through Generations</Text>
            </View>
          </ImageBackground>

          {/* Card */}
          <View style={styles.card}>
            {/* Gradient accent bar at top */}
            <View style={styles.accentBar} />

            <View style={styles.cardContent}>
              <Text style={styles.title}>Welcome Back</Text>
              <Text style={styles.subtitle}>Sign in to your account to continue</Text>

              {/* Google Sign In — opens system browser, same Socialite flow as web */}
              <TouchableOpacity
                style={[styles.googleButton, (isLoading || googleLoading) && styles.buttonDisabled]}
                onPress={handleGoogleLogin}
                disabled={isLoading || googleLoading}
              >
                {googleLoading ? (
                  <ActivityIndicator size="small" color="#4285F4" style={styles.socialIcon} />
                ) : (
                  <Ionicons name="logo-google" size={20} color="#4285F4" style={styles.socialIcon} />
                )}
                <Text style={styles.googleButtonText}>
                  {googleLoading ? 'Opening Google...' : 'Continue with Google'}
                </Text>
              </TouchableOpacity>

              {/* Divider */}
              <View style={styles.dividerContainer}>
                <View style={styles.divider} />
                <Text style={styles.dividerText}>OR</Text>
                <View style={styles.divider} />
              </View>

              {/* Email Input */}
              <View style={[styles.inputGroup, emailFocused && styles.inputGroupFocused]}>
                <MaterialIcons
                  name="email"
                  size={20}
                  color={emailFocused ? '#dc2626' : '#9ca3af'}
                  style={styles.inputIcon}
                />
                <TextInput
                  style={styles.input}
                  placeholder="Email address"
                  placeholderTextColor="#9ca3af"
                  value={email}
                  onChangeText={setEmail}
                  keyboardType="email-address"
                  autoCapitalize="none"
                  editable={!isLoading}
                  onFocus={() => setEmailFocused(true)}
                  onBlur={() => setEmailFocused(false)}
                />
              </View>

              {/* Password Input */}
              <View style={[styles.inputGroup, passwordFocused && styles.inputGroupFocused]}>
                <MaterialIcons
                  name="lock"
                  size={20}
                  color={passwordFocused ? '#dc2626' : '#9ca3af'}
                  style={styles.inputIcon}
                />
                <TextInput
                  style={styles.input}
                  placeholder="Password"
                  placeholderTextColor="#9ca3af"
                  value={password}
                  onChangeText={setPassword}
                  secureTextEntry={!showPassword}
                  editable={!isLoading}
                  onFocus={() => setPasswordFocused(true)}
                  onBlur={() => setPasswordFocused(false)}
                />
                <TouchableOpacity
                  style={styles.eyeButton}
                  onPress={() => setShowPassword(!showPassword)}
                >
                  <Ionicons
                    name={showPassword ? 'eye-off-outline' : 'eye-outline'}
                    size={20}
                    color="#9ca3af"
                  />
                </TouchableOpacity>
              </View>

              {/* Forgot Password */}
              <TouchableOpacity
                style={styles.forgotPassword}
                disabled={isLoading}
                onPress={() => navigation.navigate('ForgotPassword')}
              >
                <Text style={styles.forgotPasswordText}>Forgot Password?</Text>
              </TouchableOpacity>

              {/* Login Button */}
              <TouchableOpacity
                style={[styles.loginButton, isLoading && styles.buttonDisabled]}
                onPress={handleLogin}
                disabled={isLoading}
              >
                {isLoading ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.loginButtonText}>Sign In</Text>
                )}
              </TouchableOpacity>

              {/* Register Link */}
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
        </ScrollView>
      </KeyboardAvoidingView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#5A0808',
  },
  flex: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingBottom: 40,
  },
  headerBg: {
    width: '100%',
  },
  headerOverlay: {
    alignItems: 'center',
    paddingTop: 64,
    paddingBottom: 36,
    paddingHorizontal: 24,
    backgroundColor: 'rgba(90, 8, 8, 0.58)',
  },
  logoIconContainer: {
    width: 62,
    height: 62,
    borderRadius: 18,
    backgroundColor: 'rgba(220,38,38,0.9)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
    borderWidth: 2,
    borderColor: 'rgba(255,255,255,0.3)',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.4,
    shadowRadius: 10,
    elevation: 10,
  },
  logoIconText: {
    color: '#fff',
    fontSize: 30,
    fontWeight: '900',
  },
  brandName: {
    fontSize: 28,
    fontWeight: '700',
    color: 'rgba(255,255,255,0.85)',
    letterSpacing: 2,
    marginBottom: 12,
  },
  loginHeritageBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.35)',
    borderRadius: 20,
    paddingVertical: 5,
    paddingHorizontal: 14,
    marginBottom: 14,
  },
  loginHeritageDot: {
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: '#f87171',
    marginRight: 7,
  },
  loginHeritageBadgeText: {
    color: 'rgba(255,255,255,0.92)',
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 1.5,
  },
  heroTitle: {
    fontSize: 36,
    fontWeight: '900',
    color: '#fff',
    letterSpacing: 3,
    textAlign: 'center',
    textShadowColor: 'rgba(0,0,0,0.4)',
    textShadowOffset: { width: 0, height: 2 },
    textShadowRadius: 6,
    marginBottom: 6,
  },
  tagline: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    fontWeight: '500',
    letterSpacing: 0.3,
    textAlign: 'center',
  },
  card: {
    marginHorizontal: 20,
    backgroundColor: '#fff',
    borderRadius: 24,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.2,
    shadowRadius: 20,
    elevation: 12,
  },
  accentBar: {
    height: 5,
    backgroundColor: '#dc2626',
  },
  cardContent: {
    padding: 28,
  },
  title: {
    fontSize: 26,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 6,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 14,
    color: '#6b7280',
    textAlign: 'center',
    marginBottom: 24,
  },
  googleButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: '#e5e7eb',
    borderRadius: 12,
    paddingVertical: 14,
    paddingHorizontal: 20,
    backgroundColor: '#fff',
    marginBottom: 4,
  },
  socialIcon: {
    marginRight: 10,
  },
  googleButtonText: {
    color: '#374151',
    fontSize: 15,
    fontWeight: '600',
  },
  dividerContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 20,
  },
  divider: {
    flex: 1,
    height: 1,
    backgroundColor: '#e5e7eb',
  },
  dividerText: {
    color: '#9ca3af',
    paddingHorizontal: 12,
    fontSize: 13,
    fontWeight: '600',
  },
  inputGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#e5e7eb',
    borderRadius: 12,
    marginBottom: 16,
    backgroundColor: '#fff',
  },
  inputGroupFocused: {
    borderColor: '#dc2626',
  },
  inputIcon: {
    paddingLeft: 14,
    paddingRight: 4,
  },
  input: {
    flex: 1,
    paddingVertical: 14,
    paddingRight: 14,
    fontSize: 15,
    color: '#111827',
  },
  eyeButton: {
    paddingHorizontal: 14,
    paddingVertical: 14,
  },
  forgotPassword: {
    alignSelf: 'flex-end',
    marginBottom: 20,
    marginTop: -4,
  },
  forgotPasswordText: {
    color: '#dc2626',
    fontSize: 14,
    fontWeight: '500',
  },
  loginButton: {
    backgroundColor: '#dc2626',
    borderRadius: 12,
    paddingVertical: 15,
    alignItems: 'center',
    marginBottom: 20,
    shadowColor: '#dc2626',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.35,
    shadowRadius: 8,
    elevation: 6,
  },
  loginButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  registerContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
  },
  registerText: {
    color: '#6b7280',
    fontSize: 14,
  },
  registerLink: {
    color: '#dc2626',
    fontSize: 14,
    fontWeight: '700',
  },
});