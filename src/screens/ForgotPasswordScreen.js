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

export default function ForgotPasswordScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [step, setStep] = useState('email'); // 'email', 'code', 'newPassword'
  const [resetCode, setResetCode] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSendResetCode = async () => {
    if (!email) {
      Alert.alert('Error', 'Please enter your email address');
      return;
    }

    setIsLoading(true);
    
    // Simulate sending reset code
    setTimeout(() => {
      Alert.alert('Success', 'Reset code sent to your email');
      setStep('code');
      setIsLoading(false);
    }, 2000);
  };

  const handleVerifyCode = () => {
    if (!resetCode) {
      Alert.alert('Error', 'Please enter the reset code');
      return;
    }

    // Simulate code verification
    if (resetCode.length < 4) {
      Alert.alert('Error', 'Invalid reset code');
      return;
    }

    setStep('newPassword');
  };

  const handleResetPassword = async () => {
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

    // Simulate password reset
    setTimeout(() => {
      Alert.alert('Success', 'Password reset successfully');
      setIsLoading(false);
      navigation.navigate('Login');
    }, 2000);
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
                    Enter your email address and we'll send you a code to reset your password
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
                    onPress={handleSendResetCode}
                    disabled={isLoading}
                  >
                    {isLoading ? (
                      <ActivityIndicator color={colors.white} />
                    ) : (
                      <Text style={styles.submitButtonText}>Send Reset Code</Text>
                    )}
                  </TouchableOpacity>
                </>
              )}

              {step === 'code' && (
                <>
                  <Text style={styles.title}>Enter Reset Code</Text>
                  <Text style={styles.subtitle}>
                    We've sent a code to {email}
                  </Text>

                  <TextInput
                    style={styles.input}
                    placeholder="Enter Code (4 digits)"
                    placeholderTextColor={colors.placeholder}
                    value={resetCode}
                    onChangeText={setResetCode}
                    keyboardType="numeric"
                    maxLength={6}
                    editable={!isLoading}
                  />

                  <TouchableOpacity
                    style={[styles.submitButton, isLoading && styles.buttonDisabled]}
                    onPress={handleVerifyCode}
                    disabled={isLoading}
                  >
                    <Text style={styles.submitButtonText}>Verify Code</Text>
                  </TouchableOpacity>

                  <TouchableOpacity onPress={() => setStep('email')}>
                    <Text style={styles.changeEmailText}>Use Different Email</Text>
                  </TouchableOpacity>
                </>
              )}

              {step === 'newPassword' && (
                <>
                  <Text style={styles.title}>Create New Password</Text>
                  <Text style={styles.subtitle}>
                    Enter your new password below
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
  },
});
