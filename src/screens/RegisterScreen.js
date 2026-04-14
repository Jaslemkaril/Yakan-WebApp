// src/screens/RegisterScreen.js
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
  Image,
  useWindowDimensions,
} from 'react-native';
import { Ionicons, MaterialIcons } from '@expo/vector-icons';
import * as WebBrowser from 'expo-web-browser';
import { useCart } from '../context/CartContext';
import ApiService from '../services/api';
import API_CONFIG from '../config/config';

const BACKEND_URL = API_CONFIG.API_BASE_URL.replace(/\/api\/v1$/, '');

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

export default function RegisterScreen({ navigation }) {
  const { width } = useWindowDimensions();
  const isCompactScreen = width < 380;

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [middleName, setMiddleName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [googleLoading, setGoogleLoading] = useState(false);

  const [firstFocused, setFirstFocused] = useState(false);
  const [lastFocused, setLastFocused] = useState(false);
  const [middleFocused, setMiddleFocused] = useState(false);
  const [emailFocused, setEmailFocused] = useState(false);
  const [passFocused, setPassFocused] = useState(false);
  const [confirmFocused, setConfirmFocused] = useState(false);

  const { registerWithBackend, login } = useCart();

  const handleGoogleSignup = async () => {
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
          Alert.alert('Google Sign-Up Failed', params.error);
          return;
        }

        if (!params.token) {
          Alert.alert('Google Sign-Up Failed', 'No authentication token received. Please try again.');
          return;
        }

        await ApiService.saveToken(params.token);

        const userData = {
          id: params.id ? Number(params.id) : undefined,
          name: params.name || '',
          email: params.email || '',
          role: params.role || 'user',
          avatar: params.avatar || null,
        };

        login(userData);
      } else if (!(result.type === 'cancel' || result.type === 'dismiss')) {
        Alert.alert('Google Sign-Up', 'Sign-up was cancelled or could not be completed.');
      }
    } catch (error) {
      console.error('[Google Register] Error:', error);
      Alert.alert('Error', 'Could not open Google Sign-Up. Please try again.');
    } finally {
      setGoogleLoading(false);
    }
  };

  const getPasswordStrength = () => {
    if (password.length === 0) return null;
    if (password.length < 6) return 'weak';
    if (password.length < 10 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) return 'medium';
    return 'strong';
  };

  const strength = getPasswordStrength();

  const handleRegister = async () => {
    if (!firstName || !lastName || !email || !password || !confirmPassword) {
      Alert.alert('Error', 'Please fill in all required fields');
      return;
    }
    if (password !== confirmPassword) {
      Alert.alert('Error', 'Passwords do not match');
      return;
    }
    setIsLoading(true);
    try {
      const result = await registerWithBackend(firstName, lastName, middleName, email, password, confirmPassword);
      if (result.success && result.requiresOtp) {
        navigation.navigate('OtpVerification', {
          email: (result.email || email).trim(),
        });
        return;
      }

      if (!result.success) {
        Alert.alert('Registration Failed', result.message || 'An error occurred during registration');
      }
    } catch (error) {
      Alert.alert('Error', error.message || 'An error occurred during registration');
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
          {/* Header */}
          <View style={styles.header}>
            <View style={styles.logoIconContainer}>
              <Image source={require('../../assets/icon.png')} style={{ width: 56, height: 56, borderRadius: 12 }} resizeMode="contain" />
            </View>
            <Text style={styles.brandName}>Yakan</Text>
            <Text style={styles.tagline}>weaving through generations</Text>
          </View>

          {/* Card */}
          <View style={styles.card}>
            <View style={styles.accentBar} />
            <View style={styles.cardContent}>
              <Text style={styles.title}>Create Account</Text>
              <Text style={styles.subtitle}>Join us and start shopping today</Text>

              {/* Google Sign Up */}
              <TouchableOpacity
                style={[styles.googleButton, (isLoading || googleLoading) && styles.buttonDisabled]}
                onPress={handleGoogleSignup}
                disabled={isLoading || googleLoading}
              >
                {googleLoading ? (
                  <ActivityIndicator size="small" color="#4285F4" style={styles.socialIcon} />
                ) : (
                  <Ionicons name="logo-google" size={20} color="#4285F4" style={styles.socialIcon} />
                )}
                <Text style={styles.googleButtonText}>
                  {googleLoading ? 'Opening Google...' : 'Sign up with Google'}
                </Text>
              </TouchableOpacity>

              {/* Divider */}
              <View style={styles.dividerContainer}>
                <View style={styles.divider} />
                <Text style={styles.dividerText}>OR</Text>
                <View style={styles.divider} />
              </View>

              {/* Name Row */}
              <View style={[styles.nameRow, isCompactScreen && styles.nameRowStacked]}>
                <View style={[styles.inputGroup, styles.halfInput, isCompactScreen && styles.fullInput, firstFocused && styles.inputGroupFocused]}>
                  <MaterialIcons name="person" size={18} color={firstFocused ? '#dc2626' : '#9ca3af'} style={styles.inputIcon} />
                  <TextInput
                    style={styles.input}
                    placeholder="First Name *"
                    placeholderTextColor="#9ca3af"
                    value={firstName}
                    onChangeText={setFirstName}
                    editable={!isLoading}
                    onFocus={() => setFirstFocused(true)}
                    onBlur={() => setFirstFocused(false)}
                  />
                </View>
                <View style={[styles.inputGroup, styles.halfInput, isCompactScreen && styles.fullInput, lastFocused && styles.inputGroupFocused]}>
                  <MaterialIcons name="person" size={18} color={lastFocused ? '#dc2626' : '#9ca3af'} style={styles.inputIcon} />
                  <TextInput
                    style={styles.input}
                    placeholder="Last Name *"
                    placeholderTextColor="#9ca3af"
                    value={lastName}
                    onChangeText={setLastName}
                    editable={!isLoading}
                    onFocus={() => setLastFocused(true)}
                    onBlur={() => setLastFocused(false)}
                  />
                </View>
              </View>

              {/* Middle Name */}
              <View style={[styles.inputGroup, middleFocused && styles.inputGroupFocused]}>
                <MaterialIcons name="person-outline" size={20} color={middleFocused ? '#dc2626' : '#9ca3af'} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Middle Name (optional)"
                  placeholderTextColor="#9ca3af"
                  value={middleName}
                  onChangeText={setMiddleName}
                  editable={!isLoading}
                  onFocus={() => setMiddleFocused(true)}
                  onBlur={() => setMiddleFocused(false)}
                />
              </View>

              {/* Email */}
              <View style={[styles.inputGroup, emailFocused && styles.inputGroupFocused]}>
                <MaterialIcons name="email" size={20} color={emailFocused ? '#dc2626' : '#9ca3af'} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Email address *"
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

              {/* Password */}
              <View style={[styles.inputGroup, passFocused && styles.inputGroupFocused]}>
                <MaterialIcons name="lock" size={20} color={passFocused ? '#dc2626' : '#9ca3af'} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Password *"
                  placeholderTextColor="#9ca3af"
                  value={password}
                  onChangeText={setPassword}
                  secureTextEntry={!showPassword}
                  editable={!isLoading}
                  onFocus={() => setPassFocused(true)}
                  onBlur={() => setPassFocused(false)}
                />
                <TouchableOpacity style={styles.eyeButton} onPress={() => setShowPassword(!showPassword)}>
                  <Ionicons name={showPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color="#9ca3af" />
                </TouchableOpacity>
              </View>

              {/* Password Strength Bar */}
              {strength && (
                <View style={styles.strengthContainer}>
                  <View style={styles.strengthTrack}>
                    <View style={[
                      styles.strengthBar,
                      strength === 'weak' && styles.strengthWeak,
                      strength === 'medium' && styles.strengthMedium,
                      strength === 'strong' && styles.strengthStrong,
                    ]} />
                  </View>
                  <Text style={[
                    styles.strengthLabel,
                    strength === 'weak' && { color: '#ef4444' },
                    strength === 'medium' && { color: '#f59e0b' },
                    strength === 'strong' && { color: '#10b981' },
                  ]}>
                    {strength === 'weak' ? 'Weak' : strength === 'medium' ? 'Medium' : 'Strong'}
                  </Text>
                </View>
              )}

              {/* Confirm Password */}
              <View style={[styles.inputGroup, confirmFocused && styles.inputGroupFocused]}>
                <MaterialIcons name="lock-outline" size={20} color={confirmFocused ? '#dc2626' : '#9ca3af'} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Confirm Password *"
                  placeholderTextColor="#9ca3af"
                  value={confirmPassword}
                  onChangeText={setConfirmPassword}
                  secureTextEntry={!showConfirmPassword}
                  editable={!isLoading}
                  onFocus={() => setConfirmFocused(true)}
                  onBlur={() => setConfirmFocused(false)}
                />
                <TouchableOpacity style={styles.eyeButton} onPress={() => setShowConfirmPassword(!showConfirmPassword)}>
                  <Ionicons name={showConfirmPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color="#9ca3af" />
                </TouchableOpacity>
              </View>

              {/* Register Button */}
              <TouchableOpacity
                style={[styles.registerButton, isLoading && styles.buttonDisabled]}
                onPress={handleRegister}
                disabled={isLoading}
              >
                {isLoading ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.registerButtonText}>Create Account</Text>
                )}
              </TouchableOpacity>

              {/* Login Link */}
              <View style={styles.loginContainer}>
                <Text style={styles.loginText}>Already have an account? </Text>
                <TouchableOpacity onPress={() => navigation.navigate('Login')} disabled={isLoading}>
                  <Text style={styles.loginLink}>Sign In</Text>
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
    backgroundColor: '#800020',
  },
  flex: { flex: 1 },
  scrollContent: {
    flexGrow: 1,
    paddingBottom: 40,
  },
  header: {
    alignItems: 'center',
    paddingTop: 50,
    paddingBottom: 32,
    paddingHorizontal: 24,
  },
  logoIconContainer: {
    width: 56,
    height: 56,
    borderRadius: 16,
    backgroundColor: '#dc2626',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  logoIconText: {
    color: '#fff',
    fontSize: 28,
    fontWeight: 'bold',
  },
  brandName: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#fff',
    letterSpacing: 1,
    marginBottom: 4,
  },
  tagline: {
    fontSize: 13,
    color: 'rgba(255,255,255,0.75)',
    fontStyle: 'italic',
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
    height: 4,
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
  nameRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 0,
  },
  nameRowStacked: {
    flexDirection: 'column',
    gap: 0,
  },
  halfInput: {
    flex: 1,
  },
  fullInput: {
    flex: 0,
    width: '100%',
  },
  inputGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#e5e7eb',
    borderRadius: 12,
    marginBottom: 14,
    backgroundColor: '#fff',
  },
  inputGroupFocused: {
    borderColor: '#dc2626',
  },
  inputIcon: {
    paddingLeft: 12,
    paddingRight: 4,
  },
  input: {
    flex: 1,
    paddingVertical: 13,
    paddingRight: 12,
    fontSize: 14,
    color: '#111827',
  },
  eyeButton: {
    paddingHorizontal: 12,
    paddingVertical: 13,
  },
  strengthContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: -8,
    marginBottom: 14,
    gap: 10,
  },
  strengthTrack: {
    flex: 1,
    height: 4,
    backgroundColor: '#e5e7eb',
    borderRadius: 2,
    overflow: 'hidden',
  },
  strengthBar: {
    height: '100%',
    borderRadius: 2,
  },
  strengthWeak: { width: '33%', backgroundColor: '#ef4444' },
  strengthMedium: { width: '66%', backgroundColor: '#f59e0b' },
  strengthStrong: { width: '100%', backgroundColor: '#10b981' },
  strengthLabel: {
    fontSize: 12,
    fontWeight: '600',
    minWidth: 45,
  },
  registerButton: {
    backgroundColor: '#dc2626',
    borderRadius: 12,
    paddingVertical: 15,
    alignItems: 'center',
    marginTop: 4,
    marginBottom: 20,
    shadowColor: '#dc2626',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.35,
    shadowRadius: 8,
    elevation: 6,
  },
  registerButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  loginContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
  },
  loginText: {
    color: '#6b7280',
    fontSize: 14,
  },
  loginLink: {
    color: '#dc2626',
    fontSize: 14,
    fontWeight: '700',
  },
});