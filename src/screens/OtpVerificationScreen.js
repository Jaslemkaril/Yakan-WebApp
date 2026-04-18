import React, { useEffect, useMemo, useState } from 'react';
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
} from 'react-native';
import { Ionicons, MaterialIcons } from '@expo/vector-icons';
import ApiService from '../services/api';
import { useCart } from '../context/CartContext';

export default function OtpVerificationScreen({ route, navigation }) {
  const initialEmail = route?.params?.email || '';
  const [email, setEmail] = useState(initialEmail);
  const [otp, setOtp] = useState('');
  const [isVerifying, setIsVerifying] = useState(false);
  const [isResending, setIsResending] = useState(false);
  const [cooldown, setCooldown] = useState(0);
  const { login } = useCart();

  useEffect(() => {
    if (!cooldown) return;

    const timer = setInterval(() => {
      setCooldown((prev) => (prev > 0 ? prev - 1 : 0));
    }, 1000);

    return () => clearInterval(timer);
  }, [cooldown]);

  const maskedEmail = useMemo(() => {
    const value = (email || '').trim();
    const at = value.indexOf('@');
    if (at <= 1) return value;

    const local = value.slice(0, at);
    const domain = value.slice(at);
    if (local.length <= 2) return `${local[0]}*${domain}`;
    return `${local[0]}${'*'.repeat(Math.min(local.length - 2, 6))}${local[local.length - 1]}${domain}`;
  }, [email]);

  const handleVerifyOtp = async () => {
    const normalizedEmail = (email || '').trim().toLowerCase();
    const normalizedOtp = (otp || '').trim();

    if (!normalizedEmail) {
      Alert.alert('Missing Email', 'Please enter your registration email.');
      return;
    }

    if (!/^\d{6}$/.test(normalizedOtp)) {
      Alert.alert('Invalid Code', 'Please enter the 6-digit verification code.');
      return;
    }

    setIsVerifying(true);
    try {
      const response = await ApiService.verifyOtp(normalizedEmail, normalizedOtp);

      if (!response.success) {
        const msg = response.error
          || response.data?.message
          || 'Invalid or expired code.';
        Alert.alert('Verification Failed', msg);
        return;
      }

      const payload = response.data?.data || response.data;
      const user = payload?.user;

      if (user) {
        login(user);
      }

      Alert.alert('Success', 'Your email has been verified. Welcome to Yakan!');
    } catch (error) {
      Alert.alert('Error', error.message || 'Unable to verify OTP at this time.');
    } finally {
      setIsVerifying(false);
    }
  };

  const handleResendOtp = async () => {
    const normalizedEmail = (email || '').trim().toLowerCase();

    if (!normalizedEmail) {
      Alert.alert('Missing Email', 'Please enter your registration email first.');
      return;
    }

    setIsResending(true);
    try {
      const response = await ApiService.resendOtp(normalizedEmail);

      if (!response.success) {
        Alert.alert('Resend Failed', response.error || 'Unable to resend verification code.');
        return;
      }

      setCooldown(30);
      Alert.alert('Code Sent', 'A new verification code has been sent to your email.');
    } catch (error) {
      Alert.alert('Error', error.message || 'Unable to resend OTP at this time.');
    } finally {
      setIsResending(false);
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
          <View style={styles.header}>
            <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
              <Ionicons name="arrow-back" size={22} color="#ffffff" />
            </TouchableOpacity>
            <Text style={styles.brandName}>Yakan</Text>
            <Text style={styles.tagline}>Account Verification</Text>
          </View>

          <View style={styles.card}>
            <View style={styles.accentBar} />
            <View style={styles.cardContent}>
              <Text style={styles.title}>Verify Your Email</Text>
              <Text style={styles.subtitle}>
                Enter the 6-digit code sent to {maskedEmail || 'your email'}
              </Text>

              <View style={styles.inputGroup}>
                <MaterialIcons name="email" size={20} color="#9ca3af" style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="Email address"
                  placeholderTextColor="#9ca3af"
                  value={email}
                  onChangeText={setEmail}
                  keyboardType="email-address"
                  autoCapitalize="none"
                  editable={!isVerifying && !isResending}
                />
              </View>

              <View style={styles.inputGroup}>
                <MaterialIcons name="lock-outline" size={20} color="#9ca3af" style={styles.inputIcon} />
                <TextInput
                  style={styles.input}
                  placeholder="6-digit OTP"
                  placeholderTextColor="#9ca3af"
                  value={otp}
                  onChangeText={(value) => setOtp(value.replace(/[^0-9]/g, '').slice(0, 6))}
                  keyboardType="number-pad"
                  maxLength={6}
                  editable={!isVerifying}
                />
              </View>

              <TouchableOpacity
                style={[styles.verifyButton, isVerifying && styles.buttonDisabled]}
                onPress={handleVerifyOtp}
                disabled={isVerifying}
              >
                {isVerifying ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.verifyButtonText}>Verify OTP</Text>
                )}
              </TouchableOpacity>

              <TouchableOpacity
                style={[
                  styles.resendButton,
                  (isResending || cooldown > 0) && styles.buttonDisabled,
                ]}
                onPress={handleResendOtp}
                disabled={isResending || cooldown > 0}
              >
                {isResending ? (
                  <ActivityIndicator color="#dc2626" />
                ) : (
                  <Text style={styles.resendButtonText}>
                    {cooldown > 0 ? `Resend Code in ${cooldown}s` : 'Send New Code'}
                  </Text>
                )}
              </TouchableOpacity>
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
  flex: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingBottom: 40,
  },
  header: {
    alignItems: 'center',
    paddingTop: 64,
    paddingBottom: 30,
    paddingHorizontal: 24,
    position: 'relative',
  },
  backButton: {
    position: 'absolute',
    left: 20,
    top: 60,
    padding: 8,
  },
  brandName: {
    fontSize: 30,
    fontWeight: '700',
    color: '#ffffff',
    letterSpacing: 2,
  },
  tagline: {
    marginTop: 8,
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
  },
  card: {
    marginHorizontal: 20,
    backgroundColor: '#ffffff',
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
    fontSize: 24,
    fontWeight: '700',
    color: '#111827',
    textAlign: 'center',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 14,
    color: '#6b7280',
    textAlign: 'center',
    marginBottom: 22,
    lineHeight: 21,
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
  verifyButton: {
    backgroundColor: '#dc2626',
    borderRadius: 12,
    paddingVertical: 15,
    alignItems: 'center',
    marginBottom: 14,
  },
  verifyButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '700',
  },
  resendButton: {
    borderWidth: 1,
    borderColor: '#fca5a5',
    borderRadius: 12,
    paddingVertical: 13,
    alignItems: 'center',
    backgroundColor: '#fff5f5',
  },
  resendButtonText: {
    color: '#dc2626',
    fontSize: 15,
    fontWeight: '600',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
});
