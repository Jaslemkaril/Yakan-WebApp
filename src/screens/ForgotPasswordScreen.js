// src/screens/ForgotPasswordScreen.js
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
  ImageBackground,
} from 'react-native';
import colors from '../constants/colors';
import ApiService from '../services/api';

export default function ForgotPasswordScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [step, setStep] = useState('email'); // 'email', 'link', 'newPassword'
  const [resetLink, setResetLink] = useState('');
  const [token, setToken] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const parseResetLink = (value) => {
    const input = (value || '').trim();
    if (!input) return { token: '', email: '' };

    // Accept full reset URL from email, or a raw token.
    const tokenFromPathMatch = input.match(/\/reset-password\/([^/?#]+)/i);
    const emailFromQueryMatch = input.match(/[?&]email=([^&#]+)/i);

    if (tokenFromPathMatch?.[1]) {
      return {
        token: decodeURIComponent(tokenFromPathMatch[1]),
        email: emailFromQueryMatch?.[1]
          ? decodeURIComponent(emailFromQueryMatch[1]).trim().toLowerCase()
          : '',
      };
    }

    return { token: input, email: '' };
  };

  const handleSendResetLink = async () => {
    const normalizedEmail = email.trim().toLowerCase();

    if (!normalizedEmail) {
      Alert.alert('Error', 'Please enter your email address');
      return;
    }

    setIsLoading(true);

    try {
      const response = await ApiService.forgotPassword(normalizedEmail);

      if (!response.success) {
        Alert.alert('Request Failed', response.error || 'Unable to send reset link.');
        return;
      }

      Alert.alert('Reset Link Sent', 'Check your email and copy the reset link from the message.');
      setEmail(normalizedEmail);
      setStep('link');
    } catch (error) {
      Alert.alert('Error', error.message || 'Unable to send reset link right now.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleVerifyLink = () => {
    const parsed = parseResetLink(resetLink);

    if (!parsed.token) {
      Alert.alert('Error', 'Please paste the full reset link from your email, or paste the token.');
      return;
    }

    if (parsed.email && parsed.email !== email.trim().toLowerCase()) {
      setEmail(parsed.email);
    }

    setToken(parsed.token);
    setStep('newPassword');
  };

  const handleResetPassword = async () => {
    const normalizedEmail = email.trim().toLowerCase();

    if (!normalizedEmail) {
      Alert.alert('Error', 'Missing account email. Please go back and enter your email.');
      return;
    }

    if (!token.trim()) {
      Alert.alert('Error', 'Missing reset token. Please paste the reset link again.');
      return;
    }

    if (!newPassword || !confirmPassword) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    if (newPassword !== confirmPassword) {
      Alert.alert('Error', 'Passwords do not match');
      return;
    }

    if (newPassword.length < 6) {
      Alert.alert('Error', 'Password must be at least 6 characters');
      return;
    }

    setIsLoading(true);

    try {
      const response = await ApiService.resetPassword(
        normalizedEmail,
        token.trim(),
        newPassword,
        confirmPassword
      );

      if (!response.success) {
        Alert.alert('Reset Failed', response.error || 'Unable to reset password.');
        return;
      }

      Alert.alert('Success', 'Password reset successfully. Please sign in with your new password.');
      setIsLoading(false);
      navigation.navigate('Login');
    } catch (error) {
      setIsLoading(false);
      Alert.alert('Error', error.message || 'Unable to reset password right now.');
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
            {/* Header */}
            <View style={styles.headerContainer}>
              <TouchableOpacity onPress={() => navigation.goBack()}>
                <Text style={styles.backButton}>← Back</Text>
              </TouchableOpacity>
            </View>

            {/* Content */}
            <View style={styles.formContainer}>
              {step === 'email' && (
                <>
                  <Text style={styles.title}>Forgot Password</Text>
                  <Text style={styles.subtitle}>
                    Enter your email and we will send you the same reset link flow used on the website
                  </Text>

                  <TextInput
                    style={styles.input}
                    placeholder="Email Address"
                    placeholderTextColor={colors.placeholder}
                    value={email}
                    onChangeText={setEmail}
                    keyboardType="email-address"
                    autoCapitalize="none"
                    editable={!isLoading}
                  />

                  <TouchableOpacity
                    style={[styles.submitButton, isLoading && styles.buttonDisabled]}
                    onPress={handleSendResetLink}
                    disabled={isLoading}
                  >
                    {isLoading ? (
                      <ActivityIndicator color={colors.white} />
                    ) : (
                      <Text style={styles.submitButtonText}>Send Reset Link</Text>
                    )}
                  </TouchableOpacity>
                </>
              )}

              {step === 'link' && (
                <>
                  <Text style={styles.title}>Paste Reset Link</Text>
                  <Text style={styles.subtitle}>
                    Copy the reset link from your email and paste it below
                  </Text>

                  <TextInput
                    style={[styles.input, styles.linkInput]}
                    placeholder="https://.../reset-password/{token}?email=..."
                    placeholderTextColor={colors.placeholder}
                    value={resetLink}
                    onChangeText={setResetLink}
                    autoCapitalize="none"
                    multiline
                    editable={!isLoading}
                  />

                  <TouchableOpacity
                    style={[styles.submitButton, isLoading && styles.buttonDisabled]}
                    onPress={handleVerifyLink}
                    disabled={isLoading}
                  >
                    <Text style={styles.submitButtonText}>Continue</Text>
                  </TouchableOpacity>

                  <TouchableOpacity onPress={handleSendResetLink} disabled={isLoading}>
                    <Text style={styles.changeEmailText}>Send New Reset Link</Text>
                  </TouchableOpacity>

                  <TouchableOpacity onPress={() => setStep('email')}>
                    <Text style={styles.changeEmailText}>Change Email</Text>
                  </TouchableOpacity>
                </>
              )}

              {step === 'newPassword' && (
                <>
                  <Text style={styles.title}>Create New Password</Text>
                  <Text style={styles.subtitle}>
                    Enter your new password for {email}
                  </Text>

                  <TextInput
                    style={styles.input}
                    placeholder="New Password"
                    placeholderTextColor={colors.placeholder}
                    value={newPassword}
                    onChangeText={setNewPassword}
                    secureTextEntry
                    editable={!isLoading}
                  />

                  <TextInput
                    style={styles.input}
                    placeholder="Confirm Password"
                    placeholderTextColor={colors.placeholder}
                    value={confirmPassword}
                    onChangeText={setConfirmPassword}
                    secureTextEntry
                    editable={!isLoading}
                  />

                  <TouchableOpacity
                    style={[styles.submitButton, isLoading && styles.buttonDisabled]}
                    onPress={handleResetPassword}
                    disabled={isLoading}
                  >
                    {isLoading ? (
                      <ActivityIndicator color={colors.white} />
                    ) : (
                      <Text style={styles.submitButtonText}>Reset Password</Text>
                    )}
                  </TouchableOpacity>
                </>
              )}
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
  },
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    padding: 20,
  },
  headerContainer: {
    marginTop: 10,
    marginBottom: 30,
  },
  backButton: {
    fontSize: 16,
    color: colors.white,
    fontWeight: '600',
  },
  formContainer: {
    flex: 1,
    justifyContent: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    borderRadius: 20,
    padding: 25,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: colors.dark,
    marginBottom: 10,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 14,
    color: colors.text,
    marginBottom: 25,
    textAlign: 'center',
    lineHeight: 20,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    paddingHorizontal: 15,
    paddingVertical: 12,
    marginBottom: 15,
    fontSize: 16,
    color: colors.dark,
    backgroundColor: colors.white,
  },
  submitButton: {
    backgroundColor: colors.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 10,
    marginBottom: 15,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  submitButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
  changeEmailText: {
    color: colors.primary,
    textAlign: 'center',
    fontSize: 14,
    fontWeight: '600',
    marginTop: 8,
  },
  linkInput: {
    minHeight: 90,
    textAlignVertical: 'top',
  },
});
