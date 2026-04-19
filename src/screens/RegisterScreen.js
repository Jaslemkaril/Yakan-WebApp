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
  const [birthDate, setBirthDate] = useState(''); // mm/dd/yyyy
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [agreedToTerms, setAgreedToTerms] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [googleLoading, setGoogleLoading] = useState(false);

  const [firstFocused, setFirstFocused] = useState(false);
  const [lastFocused, setLastFocused] = useState(false);
  const [middleFocused, setMiddleFocused] = useState(false);
  const [dobFocused, setDobFocused] = useState(false);
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

  const passwordChecks = {
    length: password.length >= 8,
    lower: /[a-z]/.test(password),
    upper: /[A-Z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[@$!%*#?&]/.test(password),
  };
  const passwordIsValid = Object.values(passwordChecks).every(Boolean);

  const getPasswordStrength = () => {
    if (password.length === 0) return null;
    const passed = Object.values(passwordChecks).filter(Boolean).length;
    if (passed <= 2) return 'weak';
    if (passed <= 4) return 'medium';
    return 'strong';
  };

  const strength = getPasswordStrength();

  // Auto-format mm/dd/yyyy as the user types.
  const handleBirthDateChange = (text) => {
    const digits = String(text || '').replace(/\D/g, '').slice(0, 8);
    let formatted = digits;
    if (digits.length >= 5) {
      formatted = `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
    } else if (digits.length >= 3) {
      formatted = `${digits.slice(0, 2)}/${digits.slice(2)}`;
    }
    setBirthDate(formatted);
  };

  // Parse mm/dd/yyyy into a Date, or null if invalid.
  const parseBirthDate = (value) => {
    const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(value || ''));
    if (!match) return null;
    const month = parseInt(match[1], 10);
    const day = parseInt(match[2], 10);
    const year = parseInt(match[3], 10);
    if (month < 1 || month > 12 || day < 1 || day > 31 || year < 1900) return null;
    const dt = new Date(year, month - 1, day);
    if (
      dt.getFullYear() !== year
      || dt.getMonth() !== month - 1
      || dt.getDate() !== day
    ) return null;
    return dt;
  };

  const birthDateObj = parseBirthDate(birthDate);
  const eighteenYearsAgo = (() => {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    d.setFullYear(d.getFullYear() - 18);
    return d;
  })();
  const birthDateIsValid = birthDateObj && birthDateObj <= eighteenYearsAgo;

  const formatBirthDateForApi = (dt) => {
    if (!dt) return '';
    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, '0');
    const d = String(dt.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  };

  const openLegalLink = async (path) => {
    try {
      await WebBrowser.openBrowserAsync(`${BACKEND_URL}${path}`);
    } catch (err) {
      Alert.alert('Unable to open link', 'Please check your internet connection.');
    }
  };

  const canSubmit = Boolean(
    firstName.trim()
    && lastName.trim()
    && birthDateIsValid
    && email.trim()
    && passwordIsValid
    && password === confirmPassword
    && agreedToTerms
    && !isLoading
  );

  const handleRegister = async () => {
    if (!firstName || !lastName || !email || !password || !confirmPassword) {
      Alert.alert('Error', 'Please fill in all required fields');
      return;
    }

    if (!birthDateIsValid) {
      Alert.alert(
        'Invalid Birth Date',
        'Please enter a valid date of birth (mm/dd/yyyy). You must be at least 18 years old to create an account.'
      );
      return;
    }

    const normalizedEmail = (email || '').trim().toLowerCase();
    if (!/^\S+@\S+\.\S+$/.test(normalizedEmail)) {
      Alert.alert('Invalid Email', 'Please enter a valid email address.');
      return;
    }

    if (password !== confirmPassword) {
      Alert.alert('Error', 'Passwords do not match');
      return;
    }

    if (!passwordIsValid) {
      Alert.alert(
        'Weak Password',
        'Password must be at least 8 characters and include one lowercase letter, one uppercase letter, one number, and one special character (@$!%*#?&).'
      );
      return;
    }

    if (!agreedToTerms) {
      Alert.alert(
        'Terms Required',
        'Please agree to the Terms of Service and Privacy Policy to continue.'
      );
      return;
    }

    setIsLoading(true);
    try {
      const result = await registerWithBackend(
        firstName.trim(),
        lastName.trim(),
        middleName.trim(),
        normalizedEmail,
        password,
        confirmPassword,
        formatBirthDateForApi(birthDateObj)
      );

      if (result.success && result.requiresOtp) {
        Alert.alert('Verify Your Email', result.message || 'We sent an OTP code to your email.');
        navigation.navigate('OtpVerification', {
          email: (result.email || normalizedEmail).trim(),
        });
        return;
      }

      if (!result.success) {
        if (result?.emailTaken) {
          Alert.alert('Email Already Registered', result.message || 'This email is already in use. Please sign in.');
          return;
        }

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

              {/* Date of Birth */}
              <View style={[styles.inputGroup, dobFocused && styles.inputGroupFocused]}>
                <MaterialIcons name="event" size={20} color={dobFocused ? '#dc2626' : '#9ca3af'} style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Date of Birth (mm/dd/yyyy) *"
                  placeholderTextColor="#9ca3af"
                  value={birthDate}
                  onChangeText={handleBirthDateChange}
                  keyboardType="number-pad"
                  maxLength={10}
                  editable={!isLoading}
                  onFocus={() => setDobFocused(true)}
                  onBlur={() => setDobFocused(false)}
                />
              </View>
              {birthDate.length > 0 && !birthDateIsValid ? (
                <Text style={styles.fieldError}>
                  You must be at least 18 years old to create an account.
                </Text>
              ) : null}

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

              <Text style={styles.passwordHint}>
                Must contain: 8+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special (@$!%*#?&)
              </Text>

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

              {/* Terms of Service & Privacy Policy */}
              <TouchableOpacity
                style={styles.termsRow}
                onPress={() => setAgreedToTerms((prev) => !prev)}
                activeOpacity={0.8}
                disabled={isLoading}
              >
                <Ionicons
                  name={agreedToTerms ? 'checkbox' : 'square-outline'}
                  size={22}
                  color={agreedToTerms ? '#dc2626' : '#9ca3af'}
                  style={styles.termsCheckbox}
                />
                <Text style={styles.termsText}>
                  I agree to the{' '}
                  <Text style={styles.termsLink} onPress={() => openLegalLink('/terms-of-service')}>
                    Terms of Service
                  </Text>{' '}
                  and{' '}
                  <Text style={styles.termsLink} onPress={() => openLegalLink('/privacy-policy')}>
                    Privacy Policy
                  </Text>
                </Text>
              </TouchableOpacity>

              {/* Register Button */}
              <TouchableOpacity
                style={[styles.registerButton, (!canSubmit || isLoading) && styles.buttonDisabled]}
                onPress={handleRegister}
                disabled={!canSubmit || isLoading}
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
  passwordHint: {
    fontSize: 11,
    color: '#6b7280',
    marginTop: -4,
    marginBottom: 14,
    lineHeight: 16,
  },
  fieldError: {
    fontSize: 12,
    color: '#dc2626',
    fontWeight: '600',
    marginTop: -8,
    marginBottom: 12,
  },
  termsRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 18,
    paddingRight: 4,
  },
  termsCheckbox: {
    marginRight: 10,
    marginTop: 1,
  },
  termsText: {
    flex: 1,
    fontSize: 13,
    color: '#374151',
    lineHeight: 18,
  },
  termsLink: {
    color: '#dc2626',
    fontWeight: '700',
    textDecorationLine: 'underline',
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