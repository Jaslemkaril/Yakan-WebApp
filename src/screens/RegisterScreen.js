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
    backgroundColor: 'rgba(128, 0, 32, 0.85)',
    justifyContent: 'center',
    paddingHorizontal: 30,
    paddingVertical: 50,
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 40,
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
  registerButton: {
    backgroundColor: colors.primary,
    borderRadius: 10,
    padding: 15,
    alignItems: 'center',
    marginTop: 10,
    marginBottom: 15,
  },
  registerButtonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: 'bold',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  loginContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 10,
  },
  loginText: {
    color: colors.textLight,
    fontSize: 14,
  },
  loginLink: {
    color: colors.primary,
    fontSize: 14,
    fontWeight: 'bold',
  },
});