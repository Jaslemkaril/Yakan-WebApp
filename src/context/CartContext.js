// src/context/CartContext.js
import React, { createContext, useState, useContext, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from '../services/api';

const CartContext = createContext();

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within CartProvider');
  }
  return context;
};

export const CartProvider = ({ children }) => {
  const [cartItems, setCartItems] = useState([]);
  const [wishlistItems, setWishlistItems] = useState([]);
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userInfo, setUserInfo] = useState(null);
  const [isLoadingAuth, setIsLoadingAuth] = useState(true);

  // Initialize auth on app startup
  useEffect(() => {
    initializeAuth();
  }, []);

  const initializeAuth = async () => {
    try {
      const token = await AsyncStorage.getItem('authToken');
      if (token) {
        // Token exists, try to get user info from backend
        const response = await ApiService.getCurrentUser();
        if (response.success) {
          setUserInfo(response.data.user);
          setIsLoggedIn(true);
        } else {
          // Token invalid, clear it
          await AsyncStorage.removeItem('authToken');
          setIsLoggedIn(false);
        }
      }
    } catch (error) {
      console.error('Auth initialization error:', error);
    } finally {
      setIsLoadingAuth(false);
    }
  };

  const addToCart = (product, quantity = 1) => {
    const existingItem = cartItems.find(item => item.id === product.id);
    
    if (existingItem) {
      setCartItems(cartItems.map(item =>
        item.id === product.id
          ? { ...item, quantity: item.quantity + quantity }
          : item
      ));
    } else {
      setCartItems([...cartItems, { ...product, quantity }]);
    }
  };

  const removeFromCart = (productId) => {
    setCartItems(cartItems.filter(item => item.id !== productId));
  };

  const updateQuantity = (productId, quantity) => {
    if (quantity <= 0) {
      removeFromCart(productId);
    } else {
      setCartItems(cartItems.map(item =>
        item.id === productId ? { ...item, quantity } : item
      ));
    }
  };

  const increaseQuantity = (productId) => {
    const item = cartItems.find(item => item.id === productId);
    if (item) {
      updateQuantity(productId, item.quantity + 1);
    }
  };

  const decreaseQuantity = (productId) => {
    const item = cartItems.find(item => item.id === productId);
    if (item) {
      updateQuantity(productId, item.quantity - 1);
    }
  };

  const clearCart = () => {
    setCartItems([]);
  };

  const getCartTotal = () => {
    return cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
  };

  const getCartCount = () => {
    // Return number of unique products in cart, not total quantity
    return cartItems.length;
  };

  const login = (userData) => {
    setIsLoggedIn(true);
    setUserInfo(userData);
  };

  const loginWithBackend = async (email, password) => {
    try {
      setIsLoadingAuth(true);
      const response = await ApiService.login(email, password);
      if (response.success) {
        setUserInfo(response.data.user);
        setIsLoggedIn(true);
        // Save token if returned
        if (response.data.token) {
          await AsyncStorage.setItem('authToken', response.data.token);
        }
        return { success: true, message: 'Login successful' };
      } else {
        return { success: false, message: response.error };
      }
    } catch (error) {
      return { success: false, message: error.message };
    } finally {
      setIsLoadingAuth(false);
    }
  };

  const logout = async () => {
    try {
      await ApiService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    }
    setIsLoggedIn(false);
    setUserInfo(null);
    clearCart();
    clearWishlist();
  };

  const registerWithBackend = async (firstName, lastName, email, password, confirmPassword) => {
    const response = await ApiService.register(firstName, lastName, email, password, confirmPassword);
    if (response.success) {
      setUserInfo(response.data.user);
      setIsLoggedIn(true);
      // Save token if returned
      if (response.data.token) {
        await AsyncStorage.setItem('authToken', response.data.token);
      }
      return { success: true, message: 'Registration successful' };
    } else {
      return { success: false, message: response.error };
    }
  };

  const updateUserInfo = (updatedData) => {
    setUserInfo({ ...userInfo, ...updatedData });
  };

  const addToWishlist = (product) => {
    if (!product || !product.id) {
      console.warn('Invalid product passed to addToWishlist');
      return;
    }
    const exists = wishlistItems.find(item => item.id === product.id);
    if (!exists) {
      setWishlistItems([...wishlistItems, product]);
    }
  };

  const removeFromWishlist = (productId) => {
    if (!productId) {
      console.warn('Invalid productId passed to removeFromWishlist');
      return;
    }
    setWishlistItems(wishlistItems.filter(item => item.id !== productId));
  };

  const clearWishlist = () => {
    setWishlistItems([]);
  };

  const isInWishlist = (productId) => {
    if (!productId) return false;
    return wishlistItems.some(item => item.id === productId);
  };

  return (
    <CartContext.Provider
      value={{
        cartItems,
        wishlistItems,
        isLoggedIn,
        userInfo,
        isLoadingAuth,
        addToCart,
        removeFromCart,
        updateQuantity,
        increaseQuantity,
        decreaseQuantity,
        clearCart,
        getCartTotal,
        getCartCount,
        addToWishlist,
        removeFromWishlist,
        isInWishlist,
        clearWishlist,
        login,
        loginWithBackend,
        registerWithBackend,
        logout,
        updateUserInfo,
      }}
    >
      {children}
    </CartContext.Provider>
  );
};