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
import { Ionicons, MaterialIcons } from '@expo/vector-icons';
import colors from '../constants/colors';
import ApiService from '../services/api';

export default function ForgotPasswordScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [step, setStep] = useState('email'); // 'email', 'link', 'newPassword'
  const [resetLink, setResetLink] = useState('');
  const [token, setToken] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
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

  const getCurrentStep = () => {
    if (step === 'email') return 1;
    if (step === 'link') return 2;
    return 3;
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
            <View style={styles.headerContainer}>
              <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButtonWrap}>
                <Ionicons name="arrow-back" size={20} color={colors.white} />
                <Text style={styles.backButton}>Back</Text>
              </TouchableOpacity>
              <Text style={styles.headerTitle}>Account Recovery</Text>
            </View>

            <View style={styles.formContainer}>
              <View style={styles.stepperContainer}>
                <View style={[styles.stepDot, getCurrentStep() >= 1 && styles.stepDotActive]}>
                  <Text style={[styles.stepDotText, getCurrentStep() >= 1 && styles.stepDotTextActive]}>1</Text>
                </View>
                <View style={[styles.stepLine, getCurrentStep() >= 2 && styles.stepLineActive]} />
                <View style={[styles.stepDot, getCurrentStep() >= 2 && styles.stepDotActive]}>
                  <Text style={[styles.stepDotText, getCurrentStep() >= 2 && styles.stepDotTextActive]}>2</Text>
                </View>
                <View style={[styles.stepLine, getCurrentStep() >= 3 && styles.stepLineActive]} />
                <View style={[styles.stepDot, getCurrentStep() >= 3 && styles.stepDotActive]}>
                  <Text style={[styles.stepDotText, getCurrentStep() >= 3 && styles.stepDotTextActive]}>3</Text>
                </View>
              </View>

              {step === 'email' && (
                <>
                  <Text style={styles.title}>Forgot Password</Text>
                  <Text style={styles.subtitle}>
                    Enter your account email to receive a secure password reset link
                  </Text>

                  <View style={styles.inputGroup}>
                    <MaterialIcons name="email" size={20} color={colors.placeholder} style={styles.inputIcon} />
                    <TextInput
                      style={styles.input}
                      placeholder="Email address"
                      placeholderTextColor={colors.placeholder}
                      value={email}
                      onChangeText={setEmail}
                      keyboardType="email-address"
                      autoCapitalize="none"
                      editable={!isLoading}
                    />
                  </View>

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

                  <Text style={styles.helperText}>
                    We will send the same reset link used by the website flow.
                  </Text>
                </>
              )}

              {step === 'link' && (
                <>
                  <Text style={styles.title}>Paste Reset Link</Text>
                  <Text style={styles.subtitle}>
                    Copy the reset link from your email and paste it below
                  </Text>

                  <View style={[styles.inputGroup, styles.inputGroupTop]}>
                    <MaterialIcons name="link" size={20} color={colors.placeholder} style={[styles.inputIcon, styles.inputIconTop]} />
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
                  </View>

                  <TouchableOpacity
                    style={[styles.submitButton, isLoading && styles.buttonDisabled]}
                    onPress={handleVerifyLink}
                    disabled={isLoading}
                  >
                    <Text style={styles.submitButtonText}>Continue</Text>
                  </TouchableOpacity>

                  <View style={styles.linkActions}>
                    <TouchableOpacity onPress={handleSendResetLink} disabled={isLoading}>
                      <Text style={styles.changeEmailText}>Send New Reset Link</Text>
                    </TouchableOpacity>

                    <TouchableOpacity onPress={() => setStep('email')}>
                      <Text style={styles.changeEmailText}>Change Email</Text>
                    </TouchableOpacity>
                  </View>
                </>
              )}

              {step === 'newPassword' && (
                <>
                  <Text style={styles.title}>Create New Password</Text>
                  <Text style={styles.subtitle}>
                    Enter your new password for {email}
                  </Text>

                  <View style={styles.inputGroup}>
                    <MaterialIcons name="lock" size={20} color={colors.placeholder} style={styles.inputIcon} />
                    <TextInput
                      style={styles.input}
                      placeholder="New password"
                      placeholderTextColor={colors.placeholder}
                      value={newPassword}
                      onChangeText={setNewPassword}
                      secureTextEntry={!showNewPassword}
                      editable={!isLoading}
                    />
                    <TouchableOpacity style={styles.eyeButton} onPress={() => setShowNewPassword((prev) => !prev)}>
                      <Ionicons name={showNewPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color={colors.placeholder} />
                    </TouchableOpacity>
                  </View>

                  <View style={styles.inputGroup}>
                    <MaterialIcons name="lock-outline" size={20} color={colors.placeholder} style={styles.inputIcon} />
                    <TextInput
                      style={styles.input}
                      placeholder="Confirm password"
                      placeholderTextColor={colors.placeholder}
                      value={confirmPassword}
                      onChangeText={setConfirmPassword}
                      secureTextEntry={!showConfirmPassword}
                      editable={!isLoading}
                    />
                    <TouchableOpacity style={styles.eyeButton} onPress={() => setShowConfirmPassword((prev) => !prev)}>
                      <Ionicons name={showConfirmPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color={colors.placeholder} />
                    </TouchableOpacity>
                  </View>

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

                  <Text style={styles.helperText}>Use at least 8 characters for better security.</Text>
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
    backgroundColor: 'rgba(0, 0, 0, 0.42)',
    paddingHorizontal: 18,
    paddingTop: 12,
    paddingBottom: 24,
  },
  headerContainer: {
    marginTop: 6,
    marginBottom: 14,
    flexDirection: 'row',
    alignItems: 'center',
  },
  backButtonWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(0,0,0,0.28)',
    borderRadius: 999,
    paddingVertical: 8,
    paddingHorizontal: 10,
    marginRight: 12,
  },
  backButton: {
    fontSize: 16,
    color: colors.white,
    fontWeight: '600',
    marginLeft: 4,
  },
  headerTitle: {
    fontSize: 18,
    color: colors.white,
    fontWeight: '700',
  },
  formContainer: {
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    borderRadius: 22,
    padding: 22,
    marginTop: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  stepperContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
  },
  stepDot: {
    width: 26,
    height: 26,
    borderRadius: 13,
    borderWidth: 1,
    borderColor: '#d1d5db',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fff',
  },
  stepDotActive: {
    borderColor: colors.primary,
    backgroundColor: colors.primary,
  },
  stepDotText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#9ca3af',
  },
  stepDotTextActive: {
    color: '#fff',
  },
  stepLine: {
    width: 36,
    height: 2,
    backgroundColor: '#e5e7eb',
  },
  stepLineActive: {
    backgroundColor: colors.primary,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: colors.dark,
    marginBottom: 8,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 15,
    color: colors.text,
    marginBottom: 20,
    textAlign: 'center',
    lineHeight: 21,
  },
  inputGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    backgroundColor: colors.white,
    marginBottom: 14,
  },
  inputGroupTop: {
    alignItems: 'flex-start',
  },
  inputIcon: {
    paddingHorizontal: 12,
    paddingVertical: 12,
  },
  inputIconTop: {
    paddingTop: 12,
  },
  input: {
    flex: 1,
    paddingRight: 12,
    paddingVertical: 12,
    fontSize: 15,
    color: colors.dark,
  },
  eyeButton: {
    paddingHorizontal: 12,
    paddingVertical: 12,
  },
  submitButton: {
    backgroundColor: colors.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 4,
    marginBottom: 10,
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
  linkActions: {
    marginTop: 2,
  },
  linkInput: {
    minHeight: 84,
    textAlignVertical: 'top',
  },
  helperText: {
    textAlign: 'center',
    color: colors.textLight,
    fontSize: 12,
    lineHeight: 18,
    marginTop: 2,
  },
});
