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
  const [checkoutItems, setCheckoutItems] = useState([]); // Items selected for checkout
  const [wishlistItems, setWishlistItems] = useState([]);
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userInfo, setUserInfo] = useState(null);
  const [isLoadingAuth, setIsLoadingAuth] = useState(true);

  // Initialize auth on app startup
  useEffect(() => {
    initializeAuth();
  }, []);

  // Fetch cart and wishlist when user logs in
  useEffect(() => {
    if (isLoggedIn && !isLoadingAuth) {
      fetchCart();
      fetchWishlist();
    }
  }, [isLoggedIn, isLoadingAuth]);

  const initializeAuth = async () => {
    try {
      const token = await AsyncStorage.getItem('authToken');
      console.log('[Auth] Initialize - token exists:', !!token);
      if (token) {
        try {
          // Token exists, try to get user info from backend
          const response = await ApiService.getCurrentUser();
          console.log('[Auth] getCurrentUser response:', JSON.stringify(response, null, 2));
          if (response.success) {
            // Extract user data - check if it's nested in response.data.data or response.data
            let userData = response.data?.data || response.data?.user || response.data;
            console.log('[Auth] Extracted user data:', JSON.stringify(userData, null, 2));
            
            // Ensure we have the name field
            if (!userData.name && userData.first_name && userData.last_name) {
              userData.name = `${userData.first_name} ${userData.last_name}`;
              console.log('[Auth] Created name from first_name + last_name:', userData.name);
            }
            
            console.log('[Auth] Final user data to set:', JSON.stringify(userData, null, 2));
            setUserInfo(userData);
            setIsLoggedIn(true);
          } else {
            // Token invalid, clear it
            console.log('[Auth] Token invalid, clearing');
            await AsyncStorage.removeItem('authToken');
            setIsLoggedIn(false);
          }
        } catch (apiError) {
          // If API call fails (network error, server down, etc.), 
          // don't crash - just log and continue as logged out
          console.warn('[Auth] API call failed, continuing as logged out:', apiError.message);
          setIsLoggedIn(false);
        }
      } else {
        console.log('[Auth] No token found');
      }
    } catch (error) {
      console.error('[Auth] Initialization error:', error);
      // Don't throw - let app continue
    } finally {
      setIsLoadingAuth(false);
    }
  };

  const fetchCart = async () => {
    try {
      console.log('[Cart] Fetching cart from API...');
      const response = await ApiService.getCart();
      
      if (response.success) {
        const dataArray = response.data?.data || response.data;
        const items = Array.isArray(dataArray) ? dataArray : [];
        
        // Transform API format to local format
        const formattedItems = items.map(item => ({
          id: item.product.id,
          cart_id: item.id,
          name: item.product.name,
          price: parseFloat(item.product.price),
          image: item.product.image,
          stock: item.product.stock,
          quantity: item.quantity,
        }));
        
        console.log('[Cart] Fetched items:', formattedItems.length);
        setCartItems(formattedItems);
      }
    } catch (error) {
      console.error('[Cart] Fetch error:', error);
    }
  };

  const fetchWishlist = async () => {
    try {
      console.log('[Wishlist] Fetching wishlist from API...');
      const response = await ApiService.getWishlist();
      
      if (response.success) {
        const dataArray = response.data?.data || response.data;
        const items = Array.isArray(dataArray) ? dataArray : [];
        
        // Transform API format to local format
        const formattedItems = items.map(item => ({
          id: item.product?.id || item.id,
          name: item.product?.name || item.name,
          price: parseFloat(item.product?.price || item.price || 0),
          image: item.product?.image || item.image,
          description: item.product?.description || item.description,
          stock: item.product?.stock || item.stock || 0,
        }));
        
        console.log('[Wishlist] Fetched items:', formattedItems.length);
        setWishlistItems(formattedItems);
      }
    } catch (error) {
      console.error('[Wishlist] Fetch error:', error);
    }
  };

  const addToCart = async (product, quantity = 1) => {
    // Save snapshot for rollback
    let snapshot;
    setCartItems(prev => {
      snapshot = prev;
      const existingItem = prev.find(item => item.id === product.id);
      if (existingItem) {
        return prev.map(item =>
          item.id === product.id
            ? { ...item, quantity: item.quantity + quantity }
            : item
        );
      } else {
        return [...prev, { ...product, quantity }];
      }
    });

    // Sync with backend if logged in
    if (isLoggedIn) {
      try {
        await ApiService.addToCart(product.id, quantity);
        // Refresh cart to get accurate data (including cart_id)
        await fetchCart();
      } catch (error) {
        console.error('[Cart] Add to cart error:', error);
        // Revert optimistic update on error
        if (snapshot) {
          setCartItems(snapshot);
        }
        throw error;
      }
    }
  };

  const removeFromCart = async (productId) => {
    let snapshot;
    let removedItem;
    setCartItems(prev => {
      snapshot = prev;
      removedItem = prev.find(i => i.id === productId);
      return prev.filter(item => item.id !== productId);
    });

    // Sync with backend if logged in
    if (isLoggedIn && removedItem?.cart_id) {
      try {
        await ApiService.removeFromCart(removedItem.cart_id);
        // Refresh cart to ensure consistency
        await fetchCart();
      } catch (error) {
        console.error('[Cart] Remove error:', error);
        // Revert on error
        if (snapshot) {
          setCartItems(snapshot);
        }
      }
    }
  };

  const updateQuantity = async (productId, quantity) => {
    if (quantity <= 0) {
      removeFromCart(productId);
      return;
    }

    let snapshot;
    let targetItem;
    setCartItems(prev => {
      snapshot = prev;
      targetItem = prev.find(i => i.id === productId);
      return prev.map(item =>
        item.id === productId ? { ...item, quantity } : item
      );
    });

    // Sync with backend if logged in
    if (isLoggedIn && targetItem?.cart_id) {
      try {
        await ApiService.updateCartItem(targetItem.cart_id, quantity);
        // Refresh cart to ensure consistency
        await fetchCart();
      } catch (error) {
        console.error('[Cart] Update error:', error);
        // Revert on error
        if (snapshot) {
          setCartItems(snapshot);
        }
      }
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

  const clearCart = async (skipApi = false) => {
    // Optimistic update
    setCartItems([]);

    // Sync with backend if logged in (and not skipping API call)
    if (isLoggedIn && !skipApi) {
      try {
        await ApiService.clearCart();
      } catch (error) {
        console.error('[Cart] Clear error:', error);
      }
    }
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
      console.log('[CartContext] Login response:', JSON.stringify(response, null, 2));
      
      if (response.success) {
        console.log('[CartContext] Login successful, setting user info');
        // Handle nested response structures: response.data.data or response.data.user or response.data
        let userData = response.data?.data || response.data?.user || response.data;
        console.log('[CartContext] Extracted user data:', JSON.stringify(userData, null, 2));
        
        // Ensure we have the name field
        if (!userData.name && userData.first_name && userData.last_name) {
          userData.name = `${userData.first_name} ${userData.last_name}`;
        }
        
        console.log('[CartContext] Final user data to set:', JSON.stringify(userData, null, 2));
        setUserInfo(userData);
        setIsLoggedIn(true);
        
        // Immediately fetch fresh user data from the server
        try {
          const userResponse = await ApiService.getCurrentUser();
          console.log('[CartContext] Fresh user data response:', JSON.stringify(userResponse, null, 2));
          if (userResponse.success) {
            const freshUserData = userResponse.data?.data || userResponse.data?.user || userResponse.data;
            console.log('[CartContext] Fresh user data:', JSON.stringify(freshUserData, null, 2));
            setUserInfo(freshUserData);
          }
        } catch (error) {
          console.error('[CartContext] Error fetching fresh user data:', error);
        }
        
        // Token is already saved by ApiService.login()
        return { success: true, message: 'Login successful' };
      } else {
        console.log('[CartContext] Login failed:', response.error);
        return { success: false, message: response.error };
      }
    } catch (error) {
      console.error('[CartContext] Login error:', error);
      return { success: false, message: error.message };
    } finally {
      setIsLoadingAuth(false);
    }
  };

  const logout = async () => {
    try {
      // Clear cart and wishlist locally (skip API calls since token will be deleted)
      setCartItems([]);
      setWishlistItems([]);
      
      // Then call logout API (which will delete the token)
      await ApiService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    }
    
    // Finally clear local state
    setIsLoggedIn(false);
    setUserInfo(null);
  };

  const registerWithBackend = async (firstName, lastName, email, password, confirmPassword) => {
    const response = await ApiService.register(firstName, lastName, email, password, confirmPassword);
    console.log('[CartContext] Register response:', response);
    
    if (response.success) {
      console.log('[CartContext] Registration successful, setting user info');
      // Handle nested response structures
      let userData = response.data?.data || response.data?.user || response.data;
      console.log('[CartContext] User data:', userData);
      
      // Ensure we have the name field
      if (!userData.name && userData.first_name && userData.last_name) {
        userData.name = `${userData.first_name} ${userData.last_name}`;
      }
      
      setUserInfo(userData);
      setIsLoggedIn(true);
      // Token is already saved by ApiService.register()
      return { success: true, message: 'Registration successful' };
    } else {
      console.log('[CartContext] Registration failed:', response.error);
      return { success: false, message: response.error };
    }
  };

  const updateUserInfo = (updatedData) => {
    setUserInfo({ ...userInfo, ...updatedData });
  };

  const addToWishlist = async (product) => {
    if (!product || !product.id) {
      console.warn('[Wishlist] Invalid product passed to addToWishlist');
      return;
    }
    
    let alreadyExists = false;
    setWishlistItems(prev => {
      if (prev.find(item => item.id === product.id)) {
        alreadyExists = true;
        return prev;
      }
      return [...prev, product];
    });
    
    if (alreadyExists) {
      console.log('[Wishlist] Product already in wishlist');
      return;
    }
    
    // Sync with backend if logged in
    if (isLoggedIn) {
      try {
        const response = await ApiService.addToWishlist(product.id);
        console.log('[Wishlist] Backend response:', response);
        if (!response.success) {
          console.warn('[Wishlist] Failed to add to backend:', response.error);
          // Rollback on error
          setWishlistItems(prev => prev.filter(item => item.id !== product.id));
        }
      } catch (error) {
        console.error('[Wishlist] Failed to add to backend:', error);
        // Rollback on error
        setWishlistItems(prev => prev.filter(item => item.id !== product.id));
      }
    }
  };

  const removeFromWishlist = async (productId) => {
    if (!productId) {
      console.warn('[Wishlist] Invalid productId passed to removeFromWishlist');
      return;
    }
    
    // Optimistic update - remove from local state immediately
    let snapshot;
    setWishlistItems(prev => {
      snapshot = prev;
      return prev.filter(item => item.id !== productId);
    });
    
    // Sync with backend if logged in
    if (isLoggedIn) {
      try {
        const response = await ApiService.removeFromWishlist(productId);
        
        // Success or "item not in wishlist" are both acceptable outcomes
        if (response.success) {
          console.log('[Wishlist] Successfully removed from backend');
        } else if (response.error === 'Item not in wishlist.') {
          console.log('[Wishlist] Item was not in backend wishlist (already removed or never added)');
        } else {
          // Real error - rollback
          console.error('[Wishlist] Failed to remove from backend:', response.error);
          if (snapshot) setWishlistItems(snapshot);
        }
      } catch (error) {
        console.error('[Wishlist] Failed to remove from backend:', error);
        // Rollback on network/connection error
        if (snapshot) setWishlistItems(snapshot);
      }
    }
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
        checkoutItems,
        setCheckoutItems,
        wishlistItems,
        isLoggedIn,
        userInfo,
        isLoadingAuth,
        fetchCart,
        addToCart,
        removeFromCart,
        updateQuantity,
        increaseQuantity,
        decreaseQuantity,
        clearCart,
        getCartTotal,
        getCartCount,
        fetchWishlist,
        addToWishlist,
        removeFromWishlist,
        isInWishlist,
        clearWishlist,
        login,
        loginWithBackend,
        registerWithBackend,
        logout,
        updateUserInfo,
        setUserInfo,
      }}
    >
      {children}
    </CartContext.Provider>
  );
};