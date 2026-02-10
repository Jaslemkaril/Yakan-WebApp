import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

console.log('📱 Importing contexts...');
import { CartProvider } from './src/context/CartContext';
import { NotificationProvider } from './src/context/NotificationContext';

console.log('📱 Importing components...');
import NotificationBar from './src/components/NotificationBar';
import ErrorBoundary from './src/components/ErrorBoundary';

console.log('📱 Importing screens...');
import HomeScreen from './src/screens/HomeScreen';

const Stack = createNativeStackNavigator();

const AppNavigator = () => (
  <Stack.Navigator
    initialRouteName="Home"
    screenOptions={{
      headerShown: false,
      animation: 'fade',
      animationDuration: 300,
    }}
  >
    <Stack.Screen name="Home" component={HomeScreen} />
  </Stack.Navigator>
);

export default function App() {
  console.log('🚀 App: Starting...');
  
  return (
    <ErrorBoundary>
      <NotificationProvider>
        <CartProvider>
          <NavigationContainer>
            <NotificationBar />
            <AppNavigator />
          </NavigationContainer>
        </CartProvider>
      </NotificationProvider>
    </ErrorBoundary>
  );
}
