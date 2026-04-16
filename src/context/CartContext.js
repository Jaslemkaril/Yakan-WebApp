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

  const normalizeUserData = (rawResponseData) => {
    // Supports shapes like:
    // { id, ... }, { user: { ... }, token }, { data: { ... } }, { data: { user: { ... } } }
    const container = rawResponseData?.data || rawResponseData;
    const candidate = container?.user || rawResponseData?.user || container;

    if (!candidate || typeof candidate !== 'object') {
      return null;
    }

    const normalized = { ...candidate };
    if (!normalized.name && normalized.first_name && normalized.last_name) {
      normalized.name = `${normalized.first_name} ${normalized.last_name}`.trim();
    }

    return normalized;
  };

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
            const userData = normalizeUserData(response.data);
            console.log('[Auth] Extracted user data:', JSON.stringify(userData, null, 2));

            if (userData && (userData.id || userData.email || userData.name || userData.first_name)) {
              console.log('[Auth] Final user data to set:', JSON.stringify(userData, null, 2));
              setUserInfo(userData);
              setIsLoggedIn(true);
            } else {
              console.warn('[Auth] Invalid user payload shape, clearing token');
              await AsyncStorage.removeItem('authToken');
              setUserInfo(null);
              setIsLoggedIn(false);
            }
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
          id: item.id,
          product_id: item.product_id,
          variant_id: item.variant_id || null,
          variant_size: item.variant?.size || null,
          variant_color: item.variant?.color || null,
          name: item.product.name,
          price: parseFloat(item.product.price),
          original_price: parseFloat(item.product.original_price ?? item.product.price ?? 0),
          has_product_discount: !!item.product.has_product_discount,
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
          original_price: parseFloat(item.product?.original_price || item.original_price || item.product?.price || item.price || 0),
          has_product_discount: !!(item.product?.has_product_discount || item.has_product_discount),
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

  const addToCart = async (product, quantity = 1, selectedVariant = null) => {
    // Save snapshot for rollback
    let snapshot;
    const selectedVariantId = selectedVariant?.id || null;
    const selectedVariantPrice = selectedVariant?.price != null
      ? parseFloat(selectedVariant.price)
      : parseFloat(product.price || 0);
    const selectedVariantOriginalPrice = selectedVariant?.original_price != null
      ? parseFloat(selectedVariant.original_price)
      : parseFloat(product.originalPrice ?? product.original_price ?? product.price || 0);
    const itemKey = `${product.id}:${selectedVariantId || 'base'}`;

    setCartItems(prev => {
      snapshot = prev;
      const existingItem = prev.find(item => `${item.product_id || item.id}:${item.variant_id || 'base'}` === itemKey);
      if (existingItem) {
        return prev.map(item =>
          item.id === existingItem.id
            ? { ...item, quantity: item.quantity + quantity }
            : item
        );
      } else {
        return [...prev, {
          id: `temp-${itemKey}`,
          product_id: product.id,
          variant_id: selectedVariantId,
          variant_size: selectedVariant?.size || null,
          variant_color: selectedVariant?.color || null,
          name: product.name,
          price: selectedVariantPrice,
          original_price: selectedVariantOriginalPrice,
          has_product_discount: selectedVariantOriginalPrice > selectedVariantPrice,
          image: product.image,
          stock: selectedVariant?.stock ?? product.stock,
          quantity,
        }];
      }
    });

    // Sync with backend if logged in
    if (isLoggedIn) {
      try {
        await ApiService.addToCart(product.id, quantity, selectedVariantId);
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

  const removeFromCart = async (cartItemId) => {
    let snapshot;
    let removedItem;
    setCartItems(prev => {
      snapshot = prev;
      removedItem = prev.find(i => i.id === cartItemId);
      return prev.filter(item => item.id !== cartItemId);
    });

    // Sync with backend if logged in
    if (isLoggedIn && removedItem?.id && !String(removedItem.id).startsWith('temp-')) {
      try {
        await ApiService.removeFromCart(removedItem.id);
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

  const updateQuantity = async (cartItemId, quantity) => {
    if (quantity <= 0) {
      removeFromCart(cartItemId);
      return;
    }

    let snapshot;
    let targetItem;
    setCartItems(prev => {
      snapshot = prev;
      targetItem = prev.find(i => i.id === cartItemId);
      return prev.map(item =>
        item.id === cartItemId ? { ...item, quantity } : item
      );
    });

    // Sync with backend if logged in
    if (isLoggedIn && targetItem?.id && !String(targetItem.id).startsWith('temp-')) {
      try {
        await ApiService.updateCartItem(targetItem.id, quantity);
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
        const userData = normalizeUserData(response.data);
        console.log('[CartContext] Extracted user data:', JSON.stringify(userData, null, 2));

        if (!userData || (!userData.id && !userData.email)) {
          return { success: false, message: 'Login succeeded but user data is invalid. Please try again.' };
        }

        console.log('[CartContext] Final user data to set:', JSON.stringify(userData, null, 2));
        setUserInfo(userData);
        setIsLoggedIn(true);
        
        // Immediately fetch fresh user data from the server
        try {
          const userResponse = await ApiService.getCurrentUser();
          console.log('[CartContext] Fresh user data response:', JSON.stringify(userResponse, null, 2));
          if (userResponse.success) {
            const freshUserData = normalizeUserData(userResponse.data);
            console.log('[CartContext] Fresh user data:', JSON.stringify(freshUserData, null, 2));
            if (freshUserData) {
              setUserInfo(freshUserData);
            }
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
      
      // Clear any cached order data so it doesn't leak to the next logged-in user
      await AsyncStorage.multiRemove(['pendingOrders', 'cachedOrders', 'orders']);

      // Then call logout API (which will delete the token)
      await ApiService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    }
    
    // Finally clear local state
    setIsLoggedIn(false);
    setUserInfo(null);
  };

  const registerWithBackend = async (firstName, lastName, middleName, email, password, confirmPassword) => {
    const response = await ApiService.register(firstName, lastName, middleName, email, password, confirmPassword);
    console.log('[CartContext] Register response:', response);
    
    if (response.success) {
      // Handle nested response structures
      const payload = response.data?.data || response.data;
      const otpRequired = !!(payload?.otp_required || payload?.requires_otp_verification || payload?.otp_sent);
      const registeredEmail = payload?.user?.email || payload?.email || email;

      // Registration must always end in OTP verification.
      await ApiService.clearToken();
      setUserInfo(null);
      setIsLoggedIn(false);

      return {
        success: true,
        requiresOtp: true,
        email: registeredEmail,
        otpSent: otpRequired,
        message: payload?.message || 'Verification code sent to your email. Please verify your account.',
      };
    } else {
      console.log('[CartContext] Registration failed:', response.error);
      const normalizedEmail = (email || '').trim().toLowerCase();
      const emailErrors = response?.errors?.email || [];
      const emailTaken = emailErrors.some((msg) =>
        String(msg).toLowerCase().includes('already been taken')
      );

      if (emailTaken && normalizedEmail) {
        // If the account exists but is not verified yet, resend OTP and continue OTP flow.
        const resend = await ApiService.resendOtp(normalizedEmail);

        if (resend.success) {
            await ApiService.clearToken();
            setUserInfo(null);
            setIsLoggedIn(false);

          return {
            success: true,
            requiresOtp: true,
            email: normalizedEmail,
            message: 'Email already exists but is not verified. We sent a new OTP code.',
          };
        }

        const resendMessage = resend?.error || '';
        if (resendMessage.toLowerCase().includes('already verified')) {
          return {
            success: false,
            message: 'This email is already registered. Please sign in instead.',
            emailTaken: true,
          };
        }

        return {
          success: false,
          message: resendMessage || 'This email is already taken. Please use another email.',
          emailTaken: true,
        };
      }

      return {
        success: false,
        message: response.error || 'Registration failed. Please try again.',
        errors: response.errors || null,
      };
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