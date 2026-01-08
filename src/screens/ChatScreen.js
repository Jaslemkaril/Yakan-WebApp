import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TextInput,
  TouchableOpacity,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
  Alert,
  ScrollView,
  Animated,
  RefreshControl,
  Image,
} from 'react-native';
import { useCart } from '../context/CartContext';
import ApiService from '../services/api';
import colors from '../constants/colors';
import BottomNav from '../components/BottomNav';

export default function ChatScreen({ navigation }) {
  const [chats, setChats] = useState([]);
  const [selectedChat, setSelectedChat] = useState(null);
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [showNewChatForm, setShowNewChatForm] = useState(false);
  const [newChatSubject, setNewChatSubject] = useState('');
  const [newChatMessage, setNewChatMessage] = useState('');
  const { isLoggedIn } = useCart();
  const flatListRef = useRef(null);
  const scaleAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    if (isLoggedIn) {
      fetchChats();
      const interval = setInterval(fetchChats, 3000); // Poll every 3 seconds
      return () => clearInterval(interval);
    }
  }, [isLoggedIn]);

  useEffect(() => {
    if (selectedChat) {
      // Immediately fetch messages when chat is selected
      fetchChatMessages(selectedChat.id);
      
      // When viewing a chat, poll for new messages more frequently
      const messageInterval = setInterval(() => {
        fetchChatMessages(selectedChat.id);
      }, 1500); // Poll every 1.5 seconds for new messages
      
      return () => {
        console.log('[ChatScreen] Clearing message polling interval');
        clearInterval(messageInterval);
      };
    }
  }, [selectedChat]);

  useEffect(() => {
    Animated.spring(scaleAnim, {
      toValue: 1,
      useNativeDriver: true,
    }).start();
  }, []);

  const fetchChats = async () => {
    try {
      console.log('[ChatScreen] Starting fetchChats...');
      const response = await ApiService.getChats();
      console.log('[ChatScreen] Response received:', response);
      
      if (response && response.success) {
        // Handle nested data structure: response.data.data contains the actual chats
        const chatsData = response.data?.data || response.data || [];
        const chatCount = Array.isArray(chatsData) ? chatsData.length : 0;
        console.log(`[ChatScreen] Fetched ${chatCount} chats`);
        setChats(chatsData);
        setLoading(false);
      } else {
        console.error('[ChatScreen] Failed to fetch chats:', response?.error || 'Unknown error');
        setChats([]);
        setLoading(false);
      }
    } catch (error) {
      console.error('[ChatScreen] Error fetching chats:', error);
      setChats([]);
      setLoading(false);
    }
  };

  const fetchChatMessages = async (chatId) => {
    try {
      console.log('[ChatScreen] Fetching messages for chat:', chatId);
      const response = await ApiService.getChat(chatId);
      console.log('[ChatScreen] Messages response:', response);
      
      if (response && response.success) {
        // Handle nested data structure
        const chatData = response.data?.data || response.data;
        const newMessages = chatData?.messages || [];
        const messageCount = Array.isArray(newMessages) ? newMessages.length : 0;
        console.log(`[ChatScreen] Fetched ${messageCount} messages for chat ${chatId}`);
        setMessages(newMessages);
        setSelectedChat(chatData);
        
        // Auto-scroll to latest message
        setTimeout(() => {
          flatListRef.current?.scrollToEnd({ animated: true });
        }, 100);
      } else {
        console.error('[ChatScreen] Failed to fetch messages:', response?.error || 'Unknown error');
      }
    } catch (error) {
      console.error('[ChatScreen] Error fetching chat messages:', error);
    }
  };

  const handleSelectChat = (chat) => {
    setSelectedChat(chat);
    fetchChatMessages(chat.id);
  };

  const handleRefreshMessages = async () => {
    setRefreshing(true);
    try {
      await fetchChatMessages(selectedChat.id);
    } finally {
      setRefreshing(false);
    }
  };

  const handleSendMessage = async () => {
    if (!newMessage.trim()) {
      Alert.alert('Error', 'Please enter a message');
      return;
    }

    setSending(true);
    try {
      const response = await ApiService.sendChatMessage(selectedChat.id, newMessage);
      if (response.success) {
        setNewMessage('');
        // Immediately fetch updated messages
        await fetchChatMessages(selectedChat.id);
      } else {
        Alert.alert('Error', response.error || 'Failed to send message');
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to send message');
    } finally {
      setSending(false);
    }
  };

  const handleCreateChat = async () => {
    if (!newChatSubject.trim() || !newChatMessage.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    setSending(true);
    try {
      const response = await ApiService.createChat(newChatSubject, newChatMessage);
      if (response.success) {
        setNewChatSubject('');
        setNewChatMessage('');
        setShowNewChatForm(false);
        fetchChats();
        Alert.alert('Success', 'Chat created successfully!');
      } else {
        Alert.alert('Error', response.error || 'Failed to create chat');
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to create chat');
    } finally {
      setSending(false);
    }
  };

  const handleRespondToQuote = async (chatId, response, quoteMessageId) => {
    Alert.alert(
      response === 'accepted' ? 'Accept Quote?' : 'Decline Quote?',
      `Are you sure you want to ${response === 'accepted' ? 'accept' : 'decline'} this price quote?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Confirm',
          onPress: async () => {
            setSending(true);
            try {
              const result = await ApiService.respondToQuote(chatId, response, quoteMessageId);
              if (result.success) {
                Alert.alert('Success', `Quote ${response} successfully!`);
                await fetchChatMessages(chatId);
              } else {
                Alert.alert('Error', result.error || 'Failed to respond to quote');
              }
            } catch (error) {
              Alert.alert('Error', 'Failed to respond to quote');
            } finally {
              setSending(false);
            }
          }
        }
      ]
    );
  };

  const handleCloseChat = async () => {
    try {
      const response = await ApiService.updateChatStatus(selectedChat.id, 'closed');
      if (response.success) {
        setSelectedChat(null);
        fetchChats();
        Alert.alert('Success', 'Chat closed successfully');
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to close chat');
    }
  };

  if (!isLoggedIn) {
    return (
      <View style={styles.container}>
        <View style={styles.centerContent}>
          <Text style={styles.emptyIcon}>💬</Text>
          <Text style={styles.title}>Login Required</Text>
          <Text style={styles.subtitle}>Please login to access chat</Text>
          <TouchableOpacity
            style={styles.loginButton}
            onPress={() => navigation.navigate('Login')}
          >
            <Text style={styles.loginButtonText}>Go to Login</Text>
          </TouchableOpacity>
        </View>
        <BottomNav navigation={navigation} activeRoute="Chat" />
      </View>
    );
  }

  if (loading) {
    return (
      <View style={styles.container}>
        <View style={styles.centerContent}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading chats...</Text>
        </View>
        <BottomNav navigation={navigation} activeRoute="Chat" />
      </View>
    );
  }

  // Show chat detail view
  if (selectedChat) {
    return (
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
        <View style={styles.chatHeader}>
          <TouchableOpacity 
            style={styles.backButtonContainer}
            onPress={() => {
              console.log('[ChatScreen] Back button pressed, clearing selectedChat');
              setSelectedChat(null);
              setMessages([]);
            }}
          >
            <Text style={styles.backButton}>← Back</Text>
          </TouchableOpacity>
          <View style={styles.chatHeaderInfo}>
            <Text style={styles.chatSubject} numberOfLines={1}>{selectedChat.subject}</Text>
            <View style={styles.statusBadge}>
              <View style={[
                styles.statusDot,
                selectedChat.status === 'open' ? styles.statusDotOpen : styles.statusDotClosed
              ]} />
              <Text style={styles.chatStatus}>{selectedChat.status}</Text>
            </View>
          </View>
          {selectedChat.status === 'open' && (
            <TouchableOpacity 
              style={styles.closeButtonContainer}
              onPress={handleCloseChat}
            >
              <Text style={styles.closeButton}>✕</Text>
            </TouchableOpacity>
          )}
        </View>

        <FlatList
          ref={flatListRef}
          data={messages}
          keyExtractor={(item) => item.id.toString()}
          renderItem={({ item }) => {
            const isPriceQuote = item.sender_type === 'admin' && item.message.includes('PRICE QUOTE');
            const hasResponded = messages.some(msg => 
              msg.sender_type === 'user' && 
              msg.created_at > item.created_at &&
              (msg.message.includes('accepted the price quote') || msg.message.includes('declined the price quote'))
            );

            return (
              <View>
                <View
                  style={[
                    styles.messageBubble,
                    item.sender_type === 'user'
                      ? styles.userMessage
                      : styles.adminMessage,
                  ]}
                >
                  {item.image_url && (
                    <Image 
                      source={{ uri: item.image_url }}
                      style={styles.messageImage}
                      resizeMode="cover"
                    />
                  )}
                  <Text style={[
                    styles.messageText,
                    item.sender_type === 'user' ? styles.userMessageText : styles.adminMessageText
                  ]}>
                    {item.message}
                  </Text>
                  <Text style={[
                    styles.messageTime,
                    item.sender_type === 'user' ? styles.userMessageTime : styles.adminMessageTime
                  ]}>
                    {new Date(item.created_at).toLocaleTimeString([], { 
                      hour: '2-digit', 
                      minute: '2-digit' 
                    })}
                  </Text>
                </View>
                
                {isPriceQuote && !hasResponded && (
                  <View style={styles.quoteButtonsContainer}>
                    <TouchableOpacity
                      style={[styles.quoteButton, styles.acceptButton]}
                      onPress={() => handleRespondToQuote(selectedChat.id, 'accepted', item.id)}
                    >
                      <Text style={styles.quoteButtonText}>✓ Accept Price</Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                      style={[styles.quoteButton, styles.declineButton]}
                      onPress={() => handleRespondToQuote(selectedChat.id, 'declined', item.id)}
                    >
                      <Text style={styles.quoteButtonText}>✗ Decline</Text>
                    </TouchableOpacity>
                  </View>
                )}
              </View>
            );
          }}
          onContentSizeChange={() =>
            flatListRef.current?.scrollToEnd({ animated: true })
          }
          contentContainerStyle={styles.messagesList}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefreshMessages}
              tintColor={colors.primary}
            />
          }
          ListEmptyComponent={
            <View style={styles.emptyMessages}>
              <Text style={styles.emptyMessagesText}>No messages yet</Text>
            </View>
          }
        />

        {selectedChat.status === 'open' && (
          <View style={styles.inputContainer}>
            <TextInput
              style={styles.input}
              placeholder="Type your message..."
              placeholderTextColor="#999"
              value={newMessage}
              onChangeText={setNewMessage}
              multiline
              maxLength={500}
              editable={!sending}
            />
            <TouchableOpacity
              style={[styles.sendButton, sending && styles.sendButtonDisabled]}
              onPress={handleSendMessage}
              disabled={sending}
            >
              {sending ? (
                <ActivityIndicator color={colors.white} size="small" />
              ) : (
                <Text style={styles.sendButtonIcon}>➤</Text>
              )}
            </TouchableOpacity>
          </View>
        )}

        <BottomNav navigation={navigation} activeRoute="Chat" />
      </KeyboardAvoidingView>
    );
  }

  // Show new chat form
  if (showNewChatForm) {
    return (
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
        <View style={styles.formHeader}>
          <TouchableOpacity 
            style={styles.backButtonContainer}
            onPress={() => setShowNewChatForm(false)}
          >
            <Text style={styles.backButton}>← Back</Text>
          </TouchableOpacity>
          <Text style={styles.formTitle}>New Support Chat</Text>
          <View style={{ width: 40 }} />
        </View>

        <ScrollView style={styles.formContainer} showsVerticalScrollIndicator={false}>
          <View style={styles.formSection}>
            <Text style={styles.label}>Subject *</Text>
            <TextInput
              style={styles.input}
              placeholder="What is this about?"
              placeholderTextColor="#999"
              value={newChatSubject}
              onChangeText={setNewChatSubject}
              editable={!sending}
              maxLength={100}
            />
            <Text style={styles.charCount}>{newChatSubject.length}/100</Text>
          </View>

          <View style={styles.formSection}>
            <Text style={styles.label}>Message *</Text>
            <TextInput
              style={[styles.input, styles.messageInput]}
              placeholder="Describe your issue..."
              placeholderTextColor="#999"
              value={newChatMessage}
              onChangeText={setNewChatMessage}
              multiline
              editable={!sending}
              maxLength={1000}
            />
            <Text style={styles.charCount}>{newChatMessage.length}/1000</Text>
          </View>

          <TouchableOpacity
            style={[styles.createButton, sending && styles.buttonDisabled]}
            onPress={handleCreateChat}
            disabled={sending}
          >
            {sending ? (
              <ActivityIndicator color={colors.white} size="small" />
            ) : (
              <>
                <Text style={styles.createButtonIcon}>✎</Text>
                <Text style={styles.createButtonText}>Create Chat</Text>
              </>
            )}
          </TouchableOpacity>
        </ScrollView>

        <BottomNav navigation={navigation} activeRoute="Chat" />
      </KeyboardAvoidingView>
    );
  }

  // Show chats list
  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.title}>Messages</Text>
          <Text style={styles.subtitle}>Support & Inquiries</Text>
        </View>
        <TouchableOpacity
          style={styles.newChatButton}
          onPress={() => setShowNewChatForm(true)}
        >
          <Text style={styles.newChatButtonIcon}>+</Text>
        </TouchableOpacity>
      </View>

      {chats.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Text style={styles.emptyIcon}>💬</Text>
          <Text style={styles.emptyText}>No chats yet</Text>
          <Text style={styles.emptySubtext}>Start a new chat to get support</Text>
          <TouchableOpacity
            style={styles.emptyButton}
            onPress={() => setShowNewChatForm(true)}
          >
            <Text style={styles.emptyButtonText}>Start Chat</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <FlatList
          data={chats}
          keyExtractor={(item) => item.id.toString()}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={styles.chatItem}
              onPress={() => handleSelectChat(item)}
              activeOpacity={0.7}
            >
              <View style={styles.chatItemLeft}>
                <View style={[
                  styles.chatAvatar,
                  item.status === 'open' ? styles.avatarOpen : styles.avatarClosed
                ]}>
                  <Text style={styles.chatAvatarText}>
                    {item.subject.charAt(0).toUpperCase()}
                  </Text>
                </View>
                <View style={styles.chatItemContent}>
                  <Text style={styles.chatItemSubject} numberOfLines={1}>
                    {item.subject}
                  </Text>
                  <Text style={styles.chatItemPreview} numberOfLines={1}>
                    {item.latestMessage?.message || 'No messages yet'}
                  </Text>
                </View>
              </View>
              <View style={styles.chatItemRight}>
                <View style={[
                  styles.chatItemStatus,
                  item.status === 'open'
                    ? styles.statusOpen
                    : styles.statusClosed,
                ]}>
                  <Text style={styles.statusText}>
                    {item.status === 'open' ? '●' : '✓'}
                  </Text>
                </View>
                <Text style={styles.chatItemDate}>
                  {new Date(item.updated_at).toLocaleDateString()}
                </Text>
              </View>
            </TouchableOpacity>
          )}
          contentContainerStyle={styles.chatsList}
          scrollEnabled={chats.length > 5}
        />
      )}

      <BottomNav navigation={navigation} activeRoute="Chat" />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  centerContent: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 16,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  title: {
    fontSize: 26,
    fontWeight: '700',
    color: colors.text,
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 13,
    color: '#999',
    fontWeight: '500',
  },
  emptyIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 16,
    color: colors.text,
  },
  newChatButton: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  newChatButtonIcon: {
    fontSize: 28,
    color: colors.white,
    fontWeight: 'bold',
  },
  chatsList: {
    paddingHorizontal: 12,
    paddingVertical: 12,
  },
  chatItem: {
    backgroundColor: colors.white,
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    shadowColor: colors.black,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
  },
  chatItemLeft: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
  },
  chatAvatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
    backgroundColor: '#f0f0f0',
  },
  avatarOpen: {
    backgroundColor: '#e8f5e9',
  },
  avatarClosed: {
    backgroundColor: '#ffebee',
  },
  chatAvatarText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: colors.primary,
  },
  chatItemContent: {
    flex: 1,
  },
  chatItemSubject: {
    fontSize: 15,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 4,
  },
  chatItemPreview: {
    fontSize: 13,
    color: '#999',
    lineHeight: 18,
  },
  chatItemRight: {
    alignItems: 'flex-end',
    marginLeft: 10,
  },
  chatItemStatus: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
    marginBottom: 6,
  },
  statusOpen: {
    backgroundColor: '#e8f5e9',
  },
  statusClosed: {
    backgroundColor: '#ffebee',
  },
  statusText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: colors.primary,
  },
  chatItemDate: {
    fontSize: 12,
    color: '#bbb',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 14,
    color: '#999',
    marginBottom: 24,
    textAlign: 'center',
  },
  emptyButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: 32,
    paddingVertical: 12,
    borderRadius: 10,
  },
  emptyButtonText: {
    color: colors.white,
    fontWeight: '600',
    fontSize: 15,
  },
  loginButton: {
    backgroundColor: colors.primary,
    paddingHorizontal: 30,
    paddingVertical: 12,
    borderRadius: 10,
    marginTop: 20,
  },
  loginButtonText: {
    color: colors.white,
    fontWeight: '600',
    fontSize: 16,
  },
  chatHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 12,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  backButtonContainer: {
    padding: 8,
  },
  backButton: {
    fontSize: 16,
    color: colors.primary,
    fontWeight: '600',
  },
  chatHeaderInfo: {
    flex: 1,
    marginHorizontal: 12,
  },
  chatSubject: {
    fontSize: 16,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 4,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginRight: 6,
  },
  statusDotOpen: {
    backgroundColor: '#4caf50',
  },
  statusDotClosed: {
    backgroundColor: '#f44336',
  },
  chatStatus: {
    fontSize: 12,
    color: '#666',
    fontWeight: '500',
  },
  closeButtonContainer: {
    padding: 8,
  },
  closeButton: {
    fontSize: 20,
    color: '#f44336',
    fontWeight: 'bold',
  },
  messagesList: {
    paddingHorizontal: 12,
    paddingVertical: 12,
  },
  messageBubble: {
    marginVertical: 6,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 16,
    maxWidth: '85%',
  },
  messageImage: {
    width: 200,
    height: 200,
    borderRadius: 12,
    marginBottom: 8,
  },
  userMessage: {
    alignSelf: 'flex-end',
    backgroundColor: colors.primary,
    borderBottomRightRadius: 4,
  },
  adminMessage: {
    alignSelf: 'flex-start',
    backgroundColor: '#e8e8e8',
    borderBottomLeftRadius: 4,
  },
  messageText: {
    fontSize: 15,
    lineHeight: 20,
  },
  userMessageText: {
    color: colors.white,
  },
  adminMessageText: {
    color: colors.text,
  },
  messageTime: {
    fontSize: 11,
    marginTop: 4,
  },
  userMessageTime: {
    color: 'rgba(255,255,255,0.7)',
  },
  adminMessageTime: {
    color: '#999',
  },
  emptyMessages: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 40,
  },
  emptyMessagesText: {
    fontSize: 14,
    color: '#999',
  },
  inputContainer: {
    flexDirection: 'row',
    paddingHorizontal: 12,
    paddingVertical: 10,
    backgroundColor: colors.white,
    borderTopWidth: 1,
    borderTopColor: '#e0e0e0',
  },
  input: {
    flex: 1,
    borderWidth: 1,
    borderColor: '#e0e0e0',
    borderRadius: 24,
    paddingHorizontal: 16,
    paddingVertical: 10,
    fontSize: 15,
    color: colors.text,
    marginRight: 10,
    maxHeight: 100,
    backgroundColor: '#f5f5f5',
  },
  messageInput: {
    minHeight: 80,
    textAlignVertical: 'top',
    paddingVertical: 12,
  },
  sendButton: {
    backgroundColor: colors.primary,
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  sendButtonDisabled: {
    opacity: 0.6,
  },
  sendButtonIcon: {
    color: colors.white,
    fontSize: 18,
    fontWeight: 'bold',
  },
  quoteButtonsContainer: {
    flexDirection: 'row',
    justifyContent: 'flex-start',
    gap: 10,
    paddingHorizontal: 16,
    paddingBottom: 16,
  },
  quoteButton: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 8,
    minWidth: 120,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  acceptButton: {
    backgroundColor: '#16a34a',
  },
  declineButton: {
    backgroundColor: '#dc2626',
  },
  quoteButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  formHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 14,
    backgroundColor: colors.white,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  formTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: colors.text,
  },
  formContainer: {
    flex: 1,
    paddingHorizontal: 16,
    paddingVertical: 16,
  },
  formSection: {
    marginBottom: 20,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 8,
  },
  charCount: {
    fontSize: 11,
    color: '#999',
    marginTop: 4,
    textAlign: 'right',
  },
  createButton: {
    backgroundColor: colors.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 8,
    marginBottom: 30,
    flexDirection: 'row',
    justifyContent: 'center',
    shadowColor: colors.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  createButtonIcon: {
    color: colors.white,
    fontSize: 18,
    marginRight: 8,
    fontWeight: 'bold',
  },
  createButtonText: {
    color: colors.white,
    fontWeight: '600',
    fontSize: 16,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
});
