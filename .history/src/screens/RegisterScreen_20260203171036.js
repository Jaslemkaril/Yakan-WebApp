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
  ImageBackground,
} from 'react-native';
import { useCart } from '../context/CartContext';
import colors from '../constants/colors';

export default function RegisterScreen({ navigation }) {
  const [firstName, setFirstName] = useState('');
  const [middleName, setMiddleName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { registerWithBackend } = useCart();

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
      const result = await registerWithBackend(firstName, lastName, email, password, confirmPassword);
      
      if (result.success) {
        Alert.alert('Success', 'Registration successful! You are now logged in.', [
          {
            text: 'OK',
            onPress: () => navigation.navigate('Home')
          }
        ]);
      } else {
        Alert.alert('Registration Failed', result.message || 'An error occurred during registration');
      }
    } catch (error) {
      Alert.alert('Error', error.message || 'An error occurred during registration');
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

            {/* Register Form */}
            <View style={styles.formContainer}>
              <Text style={styles.title}>Create Account</Text>

              <TextInput
                style={styles.input}
                placeholder="First Name *"
                placeholderTextColor={colors.placeholder}
                value={firstName}
                onChangeText={setFirstName}
                editable={!isLoading}
              />

              <TextInput
                style={styles.input}
                placeholder="Middle Name (Optional)"
                placeholderTextColor={colors.placeholder}
                value={middleName}
                onChangeText={setMiddleName}
                editable={!isLoading}
              />

              <TextInput
                style={styles.input}
                placeholder="Last Name *"
                placeholderTextColor={colors.placeholder}
                value={lastName}
                onChangeText={setLastName}
                editable={!isLoading}
              />

              <TextInput
                style={styles.input}
                placeholder="Email *"
                placeholderTextColor={colors.placeholder}
                value={email}
                onChangeText={setEmail}
                keyboardType="email-address"
                autoCapitalize="none"
                editable={!isLoading}
              />

              <TextInput
                style={styles.input}
                placeholder="Password *"
                placeholderTextColor={colors.placeholder}
                value={password}
                onChangeText={setPassword}
                secureTextEntry
                editable={!isLoading}
              />

              <TextInput
                style={styles.input}
                placeholder="Confirm Password *"
                placeholderTextColor={colors.placeholder}
                value={confirmPassword}
                onChangeText={setConfirmPassword}
                secureTextEntry
                editable={!isLoading}
              />

              <TouchableOpacity 
                style={[styles.registerButton, isLoading && styles.buttonDisabled]} 
                onPress={handleRegister}
                disabled={isLoading}
              >
                {isLoading ? (
                  <ActivityIndicator color={colors.white} />
                ) : (
                  <Text style={styles.registerButtonText}>REGISTER</Text>
                )}
              </TouchableOpacity>

              <View style={styles.loginContainer}>
                <Text style={styles.loginText}>Already have an account? </Text>
                <TouchableOpacity 
                  onPress={() => navigation.navigate('Login')}
                  disabled={isLoading}
                >
                  <Text style={styles.loginLink}>Login</Text>
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
    backgroundColor: 'rgba(123, 45, 45, 0.80)',
    justifyContent: 'center',
    paddingHorizontal: 32,
    paddingVertical: 50,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 40,
  },
  logoText: {
    fontSize: 52,
    fontWeight: '800',
    color: colors.white,
    letterSpacing: 3,
    textShadowColor: 'rgba(0,0,0,0.2)',
    textShadowOffset: { width: 1, height: 1 },
    textShadowRadius: 2,
  },
  logoSubtext: {
    fontSize: 40,
    fontWeight: '700',
    color: colors.accent,
    letterSpacing: 2,
    marginTop: -6,
  },
  tagline: {
    fontSize: 13,
    color: 'rgba(255,255,255,0.9)',
    marginTop: 8,
    fontStyle: 'italic',
    fontWeight: '300',
  },
  formContainer: {
    backgroundColor: colors.white,
    borderRadius: 22,
    padding: 28,
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 8,
  },
  title: {
    fontSize: 32,
    fontWeight: '800',
    color: colors.primary,
    marginBottom: 24,
    textAlign: 'center',
    letterSpacing: -0.5,
  },
  input: {
    borderWidth: 1.2,
    borderColor: colors.borderLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    fontSize: 15,
    color: colors.text,
    backgroundColor: colors.backgroundAlt,
    fontWeight: '400',
  },
  registerButton: {
    backgroundColor: colors.primary,
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
    marginTop: 12,
    marginBottom: 18,
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 4,
  },
  registerButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.3,
  },
  buttonDisabled: {
    opacity: 0.65,
  },
  loginContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 12,
  },
  loginText: {
    color: colors.textLight,
    fontSize: 14,
    fontWeight: '400',
  },
  loginLink: {
    color: colors.primary,
    fontSize: 14,
    fontWeight: '700',
    marginLeft: 4,
  },
});