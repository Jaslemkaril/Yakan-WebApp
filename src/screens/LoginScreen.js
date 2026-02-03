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
  ImageBackground,
} from 'react-native';
import { useCart } from '../context/CartContext';
import colors from '../constants/colors';

export default function LoginScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { loginWithBackend } = useCart();

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