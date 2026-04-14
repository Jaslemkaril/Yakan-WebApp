import React from 'react';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { CartProvider, useCart } from './src/context/CartContext';
import { NotificationProvider } from './src/context/NotificationContext';
import { ThemeProvider } from './src/context/ThemeContext';
import NotificationBar from './src/components/NotificationBar';
import GlobalOrderPoller from './src/components/GlobalOrderPoller';
import AutoUpdater from './src/components/AutoUpdater';
import ErrorBoundary from './src/components/ErrorBoundary';
import LoginScreen from './src/screens/LoginScreen';
import RegisterScreen from './src/screens/RegisterScreen';
import ForgotPasswordScreen from './src/screens/ForgotPasswordScreen';
import OtpVerificationScreen from './src/screens/OtpVerificationScreen';
import HomeScreen from './src/screens/HomeScreen';
import ProductsScreen from './src/screens/ProductsScreen';
import CustomOrderScreen from './src/screens/CustomOrderScreen';
import CulturalHeritageScreen from './src/screens/CulturalHeritageScreen';
import AccountScreen from './src/screens/AccountScreen';
import SettingsScreen from './src/screens/SettingsScreen';
import ProductDetailScreen from './src/screens/ProductDetailScreen';
import CartScreen from './src/screens/CartScreen';
import WishlistScreen from './src/screens/WishlistScreen';
import CheckoutScreen from './src/screens/CheckoutScreen';
import TrackOrderScreen from './src/screens/TrackOrderScreen';
import OrdersScreen from './src/screens/OrdersScreen';
import OrderDetailsScreen from './src/screens/OrderDetailsScreen';
import SavedAddressesScreen from './src/screens/SavedAddressesScreen';
import PaymentMethodsScreen from './src/screens/PaymentMethodsScreen';
import PaymentScreen from './src/screens/PaymentScreen';
import ReviewsScreen from './src/screens/ReviewsScreen';
import NotificationsScreen from './src/screens/NotificationsScreen';
import ChatScreen from './src/screens/ChatScreen';

const Stack = createNativeStackNavigator();

const AppStack = () => (
  <Stack.Navigator
    initialRouteName="Home"
    screenOptions={{
      headerShown: false,
      animation: 'fade',
      animationDuration: 300,
    }}
  >
    <Stack.Screen name="Home" component={HomeScreen} />
    <Stack.Screen name="Products" component={ProductsScreen} />
    <Stack.Screen name="CustomOrder" component={CustomOrderScreen} />
    <Stack.Screen name="CulturalHeritage" component={CulturalHeritageScreen} />
    <Stack.Screen name="Account" component={AccountScreen} />
    <Stack.Screen name="Notifications" component={NotificationsScreen} />
    <Stack.Screen name="Chat" component={ChatScreen} />
    <Stack.Screen name="Settings" component={SettingsScreen} />
    <Stack.Screen name="ProductDetail" component={ProductDetailScreen} />
    <Stack.Screen name="Cart" component={CartScreen} />
    <Stack.Screen name="Wishlist" component={WishlistScreen} />
    <Stack.Screen name="Checkout" component={CheckoutScreen} />
    <Stack.Screen name="TrackOrders" component={TrackOrderScreen} />
    <Stack.Screen name="Orders" component={OrdersScreen} />
    <Stack.Screen name="OrderDetails" component={OrderDetailsScreen} />
    <Stack.Screen name="Payment" component={PaymentScreen} />
    <Stack.Screen name="SavedAddresses" component={SavedAddressesScreen} />
    <Stack.Screen name="PaymentMethods" component={PaymentMethodsScreen} />
    <Stack.Screen name="Reviews" component={ReviewsScreen} />
  </Stack.Navigator>
);

const AuthStackNavigator = () => (
  <Stack.Navigator
    screenOptions={{
      headerShown: false,
      animation: 'fade',
      animationDuration: 300,
    }}
  >
    <Stack.Screen name="Login" component={LoginScreen} />
    <Stack.Screen name="Register" component={RegisterScreen} />
    <Stack.Screen name="OtpVerification" component={OtpVerificationScreen} />
    <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
  </Stack.Navigator>
);

const AppNavigator = ({ isLoggedIn, isLoadingAuth }) => {
  if (isLoadingAuth) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#800020" />
      </View>
    );
  }

  return (
    <Stack.Navigator
      screenOptions={{
        headerShown: false,
        animation: 'fade',
        animationDuration: 300,
      }}
    >
      {isLoggedIn ? (
        <Stack.Screen name="App" component={AppStack} />
      ) : (
        <Stack.Screen name="Auth" component={AuthStackNavigator} />
      )}
    </Stack.Navigator>
  );
};

const styles = StyleSheet.create({
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#800020',
  },
});

// Deep-link / URL scheme configuration for React Navigation.
// Listing the yakanapp:// prefix prevents NavigationContainer from
// crashing when the OAuth callback deep-link fires (yakanapp://auth-callback).
// The auth-callback path is intentionally NOT listed so React Navigation
// ignores it — it is handled directly by WebBrowser.openAuthSessionAsync.
const linking = {
  prefixes: ['yakanapp://'],
  config: {
    screens: {
      App: {
        screens: {
          Home: 'home',
          Products: 'products',
          Cart: 'cart',
          Orders: 'orders',
          Account: 'account',
        },
      },
      Auth: {
        screens: {
          Login: 'login',
          Register: 'register',
        },
      },
    },
  },
};

function RootNavigator() {
  const { isLoggedIn, isLoadingAuth } = useCart();
  
  return <AppNavigator isLoggedIn={isLoggedIn} isLoadingAuth={isLoadingAuth} />;
}

export default function App() {
  console.log('🚀 App: Starting...');
  
  return (
    <ErrorBoundary>
      <ThemeProvider>
        <NotificationProvider>
          <CartProvider>
            <AutoUpdater />
            <NavigationContainer linking={linking}>
              <NotificationBar />
              <GlobalOrderPoller />
              <RootNavigator />
            </NavigationContainer>
          </CartProvider>
        </NotificationProvider>
      </ThemeProvider>
    </ErrorBoundary>
  );
}
