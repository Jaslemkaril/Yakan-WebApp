import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Animated,
  TouchableOpacity,
  Dimensions,
} from 'react-native';
import { useNotification } from '../context/NotificationContext';

const { width } = Dimensions.get('window');

const NotificationBar = () => {
  const { notifications, removeNotification } = useNotification();
  const [animatedValues] = useState({});

  // Create animated value for each notification
  const getAnimatedValue = (id) => {
    if (!animatedValues[id]) {
      animatedValues[id] = new Animated.Value(0);
    }
    return animatedValues[id];
  };

  // Animate notification in + auto-dismiss after its duration
  useEffect(() => {
    if (notifications.length > 0) {
      const latestNotif = notifications[0];
      const animValue = getAnimatedValue(latestNotif.id);

      Animated.timing(animValue, {
        toValue: 1,
        duration: 300,
        useNativeDriver: true,
      }).start();

      // Auto-dismiss the toast after its duration (default 4 s)
      const dismissMs = latestNotif.duration || 4000;
      const timer = setTimeout(() => {
        removeNotification(latestNotif.id);
      }, dismissMs);

      return () => clearTimeout(timer);
    }
  }, [notifications]);

  const getNotificationColor = (type) => {
    switch (type) {
      case 'success':
        return '#4CAF50';
      case 'error':
        return '#f44336';
      case 'warning':
        return '#ff9800';
      case 'info':
      default:
        return '#2196F3';
    }
  };

  const getNotificationIcon = (type) => {
    switch (type) {
      case 'success':
        return '✓';
      case 'error':
        return '✕';
      case 'warning':
        return '⚠';
      case 'info':
      default:
        return 'ℹ';
    }
  };

  if (notifications.length === 0) {
    return null;
  }

  const notification = notifications[0];
  const animValue = getAnimatedValue(notification.id);
  const backgroundColor = getNotificationColor(notification.type);

  const translateY = animValue.interpolate({
    inputRange: [0, 1],
    outputRange: [-100, 0],
  });

  const opacity = animValue.interpolate({
    inputRange: [0, 0.5, 1],
    outputRange: [0, 0.8, 1],
  });

  return (
    <Animated.View
      style={[
        styles.container,
        {
          transform: [{ translateY }],
          opacity,
        },
      ]}
    >
      <View style={[styles.notificationBar, { backgroundColor }]}>
        <View style={styles.content}>
          <Text style={styles.icon}>{getNotificationIcon(notification.type)}</Text>
          <Text style={styles.message} numberOfLines={2}>
            {notification.message}
          </Text>
        </View>
        <TouchableOpacity
          style={styles.closeButton}
          onPress={() => removeNotification(notification.id)}
          activeOpacity={0.7}
        >
          <Text style={styles.closeIcon}>✕</Text>
        </TouchableOpacity>
      </View>

      {/* Progress bar */}
      <View style={[styles.progressBar, { backgroundColor }]}>
        <Animated.View
          style={[
            styles.progressFill,
            {
              transform: [
                {
                  scaleX: animValue.interpolate({
                    inputRange: [0, 1],
                    outputRange: [1, 0],
                  }),
                },
              ],
              transformOrigin: 'left',
            },
          ]}
        />
      </View>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    zIndex: 9999,
  },
  notificationBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 14,
    marginHorizontal: 12,
    marginTop: 50,
    borderRadius: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
    elevation: 5,
  },
  content: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 12,
  },
  icon: {
    fontSize: 20,
    color: '#fff',
    marginRight: 12,
    fontWeight: 'bold',
  },
  message: {
    flex: 1,
    fontSize: 14,
    color: '#fff',
    fontWeight: '500',
    lineHeight: 20,
  },
  closeButton: {
    padding: 8,
    marginRight: -8,
  },
  closeIcon: {
    fontSize: 18,
    color: '#fff',
    fontWeight: 'bold',
  },
  progressBar: {
    height: 3,
    marginTop: 0,
    marginHorizontal: 12,
    borderBottomLeftRadius: 8,
    borderBottomRightRadius: 8,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    width: '100%',
    backgroundColor: 'rgba(255, 255, 255, 0.3)',
  },
});

export default NotificationBar;
