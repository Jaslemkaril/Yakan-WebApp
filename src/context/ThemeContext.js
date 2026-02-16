import React, { createContext, useContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';

const lightColors = {
  primary: '#8B1A1A',
  secondary: '#A62929',
  background: '#FFFFFF',
  cardBackground: '#FFFFFF',
  text: '#333333',
  textSecondary: '#666666',
  textMuted: '#999999',
  textLight: '#666666',
  dark: '#1a1a1a',
  border: '#DDDDDD',
  borderLight: '#F0F2F5',
  divider: '#e0e0e0',
  placeholder: '#999999',
  white: '#FFFFFF',
  black: '#000000',
  lightGray: '#F5F5F5',
  headerBg: '#8B1A1A',
  headerText: '#FFFFFF',
  inputBg: '#FFFFFF',
  inputBorder: '#E8EBED',
  surfaceBg: '#F8F9FA',
  iconMuted: '#ccc',
  // Semantic colors
  successBg: '#e8f5e9',
  successText: '#2e7d32',
  warningBg: '#fff3e0',
  warningText: '#e65100',
  infoBg: '#e3f2fd',
  infoText: '#1976d2',
  dangerBg: '#FFF5F5',
  dangerText: '#E74C3C',
  dangerBorder: '#FFE5E5',
};

const darkColors = {
  primary: '#C0392B',
  secondary: '#E74C3C',
  background: '#121212',
  cardBackground: '#1E1E1E',
  text: '#E0E0E0',
  textSecondary: '#B0B0B0',
  textMuted: '#808080',
  textLight: '#A0A0A0',
  dark: '#FFFFFF',
  border: '#333333',
  borderLight: '#2A2A2A',
  divider: '#333333',
  placeholder: '#777777',
  white: '#1E1E1E',
  black: '#FFFFFF',
  lightGray: '#2A2A2A',
  headerBg: '#1E1E1E',
  headerText: '#E0E0E0',
  inputBg: '#2A2A2A',
  inputBorder: '#444444',
  surfaceBg: '#181818',
  iconMuted: '#666666',
  // Semantic colors
  successBg: '#1B3A26',
  successText: '#66BB6A',
  warningBg: '#3E2A10',
  warningText: '#FFB74D',
  infoBg: '#1A2A3E',
  infoText: '#64B5F6',
  dangerBg: '#3E1A1A',
  dangerText: '#EF5350',
  dangerBorder: '#5C2A2A',
};

const ThemeContext = createContext();

export const ThemeProvider = ({ children }) => {
  const [isDarkMode, setIsDarkMode] = useState(false);

  useEffect(() => {
    loadTheme();
  }, []);

  const loadTheme = async () => {
    try {
      const saved = await AsyncStorage.getItem('darkMode');
      if (saved !== null) {
        setIsDarkMode(JSON.parse(saved));
      }
    } catch (e) {
      console.log('Failed to load theme preference');
    }
  };

  const toggleDarkMode = async (value) => {
    setIsDarkMode(value);
    try {
      await AsyncStorage.setItem('darkMode', JSON.stringify(value));
    } catch (e) {
      console.log('Failed to save theme preference');
    }
  };

  const theme = isDarkMode ? darkColors : lightColors;

  return (
    <ThemeContext.Provider value={{ isDarkMode, toggleDarkMode, theme }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};
