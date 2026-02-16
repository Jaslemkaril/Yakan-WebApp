// src/screens/ReviewsScreen.js
import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Modal,
  Alert,
  FlatList,
} from 'react-native';
import colors from '../constants/colors';
import ScreenHeader from '../components/ScreenHeader';
import { useTheme } from '../context/ThemeContext';

export default function ReviewsScreen({ navigation, route }) {
  const { theme } = useTheme();
  const { productId, productName } = route.params || {};
  const [reviews, setReviews] = useState([
    {
      id: 1,
      author: 'Maria Santos',
      rating: 5,
      date: '2024-12-05',
      title: 'Excellent Quality!',
      comment: 'The craftsmanship is outstanding. Very satisfied with my purchase.',
      helpful: 24,
      verified: true,
    },
    {
      id: 2,
      author: 'Juan Dela Cruz',
      rating: 4,
      date: '2024-12-02',
      title: 'Good Product',
      comment: 'Good quality but delivery took a bit longer than expected.',
      helpful: 12,
      verified: true,
    },
    {
      id: 3,
      author: 'Ana Rodriguez',
      rating: 5,
      date: '2024-11-28',
      title: 'Perfect for Gift',
      comment: 'Beautiful weaving patterns. Perfect gift for my mother.',
      helpful: 35,
      verified: true,
    },
  ]);

  const [isReviewModalVisible, setIsReviewModalVisible] = useState(false);
  const [newReview, setNewReview] = useState({
    rating: 5,
    title: '',
    comment: '',
  });

  const averageRating = (reviews.reduce((sum, r) => sum + r.rating, 0) / reviews.length).toFixed(1);
  const ratingCounts = {
    5: reviews.filter(r => r.rating === 5).length,
    4: reviews.filter(r => r.rating === 4).length,
    3: reviews.filter(r => r.rating === 3).length,
    2: reviews.filter(r => r.rating === 2).length,
    1: reviews.filter(r => r.rating === 1).length,
  };

  const handleSubmitReview = () => {
    if (!newReview.title.trim() || !newReview.comment.trim()) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    const review = {
      id: reviews.length + 1,
      author: 'You',
      rating: newReview.rating,
      date: new Date().toISOString().split('T')[0],
      title: newReview.title,
      comment: newReview.comment,
      helpful: 0,
      verified: true,
    };

    setReviews([review, ...reviews]);
    setIsReviewModalVisible(false);
    setNewReview({ rating: 5, title: '', comment: '' });
    Alert.alert('Success', 'Your review has been posted!');
  };

  const renderRatingBar = (stars) => {
    const count = ratingCounts[stars];
    const percentage = (count / reviews.length) * 100;

    return (
      <View key={stars} style={styles.ratingBarRow}>
        <Text style={styles.ratingBarLabel}>{stars} ⭐</Text>
        <View style={styles.ratingBarBackground}>
          <View style={[styles.ratingBarFill, { width: `${percentage}%` }]} />
        </View>
        <Text style={styles.ratingBarCount}>{count}</Text>
      </View>
    );
  };

  const renderReviewItem = ({ item }) => (
    <View style={styles.reviewCard}>
      <View style={styles.reviewHeader}>
        <View>
          <Text style={styles.reviewAuthor}>{item.author}</Text>
          <View style={styles.reviewMeta}>
            <Text style={styles.reviewRating}>{'⭐'.repeat(item.rating)}</Text>
            <Text style={styles.reviewDate}>{item.date}</Text>
            {item.verified && <Text style={styles.verifiedBadge}>✓ Verified Purchase</Text>}
          </View>
        </View>
      </View>

      <Text style={styles.reviewTitle}>{item.title}</Text>
      <Text style={styles.reviewComment}>{item.comment}</Text>

      <View style={styles.reviewFooter}>
        <TouchableOpacity style={styles.helpfulButton}>
          <Text style={styles.helpfulIcon}>👍</Text>
          <Text style={styles.helpfulText}>Helpful ({item.helpful})</Text>
        </TouchableOpacity>
      </View>
    </View>
  );

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <ScreenHeader 
        title="Reviews & Ratings" 
        navigation={navigation} 
        showBack={true}
      />

      <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
        {/* Rating Summary */}
        <View style={styles.ratingSummary}>
          <View style={styles.averageRating}>
            <Text style={styles.averageNumber}>{averageRating}</Text>
            <Text style={styles.averageStars}>⭐</Text>
            <Text style={styles.totalReviews}>({reviews.length} reviews)</Text>
          </View>

          <View style={styles.ratingBars}>
            {[5, 4, 3, 2, 1].map(stars => renderRatingBar(stars))}
          </View>
        </View>

        {/* Write Review Button */}
        <TouchableOpacity
          style={styles.writeReviewButton}
          onPress={() => setIsReviewModalVisible(true)}
        >
          <Text style={styles.writeReviewIcon}>✏️</Text>
          <Text style={styles.writeReviewText}>Write a Review</Text>
          <Text style={styles.writeReviewArrow}>→</Text>
        </TouchableOpacity>

        {/* Filter/Sort Options */}
        <View style={styles.filterRow}>
          <TouchableOpacity style={styles.filterButton}>
            <Text style={styles.filterText}>Most Helpful</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.filterButton}>
            <Text style={styles.filterText}>Latest</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.filterButton}>
            <Text style={styles.filterText}>Highest Rating</Text>
          </TouchableOpacity>
        </View>

        {/* Reviews List */}
        <View style={styles.reviewsSection}>
          {reviews.length > 0 ? (
            <FlatList
              data={reviews}
              renderItem={renderReviewItem}
              keyExtractor={item => item.id.toString()}
              scrollEnabled={false}
            />
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No reviews yet</Text>
              <Text style={styles.emptySubtext}>Be the first to review this product</Text>
            </View>
          )}
        </View>
      </ScrollView>

      {/* Write Review Modal */}
      <Modal
        visible={isReviewModalVisible}
        transparent
        animationType="slide"
        onRequestClose={() => setIsReviewModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Write a Review</Text>
              <TouchableOpacity onPress={() => setIsReviewModalVisible(false)}>
                <Text style={styles.closeButton}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody}>
              {/* Rating Selector */}
              <View style={styles.ratingSelector}>
                <Text style={styles.sectionLabel}>Rating</Text>
                <View style={styles.ratingButtons}>
                  {[1, 2, 3, 4, 5].map(stars => (
                    <TouchableOpacity
                      key={stars}
                      style={[
                        styles.ratingButton,
                        newReview.rating === stars && styles.ratingButtonSelected,
                      ]}
                      onPress={() => setNewReview({ ...newReview, rating: stars })}
                    >
                      <Text style={styles.ratingButtonText}>{'⭐'.repeat(stars)}</Text>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>

              {/* Title */}
              <View style={styles.inputGroup}>
                <Text style={styles.sectionLabel}>Review Title</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Summarize your review"
                  value={newReview.title}
                  onChangeText={(text) => setNewReview({ ...newReview, title: text })}
                  maxLength={100}
                />
                <Text style={styles.charCount}>{newReview.title.length}/100</Text>
              </View>

              {/* Comment */}
              <View style={styles.inputGroup}>
                <Text style={styles.sectionLabel}>Your Review</Text>
                <TextInput
                  style={[styles.input, styles.textArea]}
                  placeholder="Tell us about your experience with this product"
                  value={newReview.comment}
                  onChangeText={(text) => setNewReview({ ...newReview, comment: text })}
                  multiline
                  numberOfLines={5}
                  maxLength={500}
                  textAlignVertical="top"
                />
                <Text style={styles.charCount}>{newReview.comment.length}/500</Text>
              </View>

              {/* Tips */}
              <View style={styles.tipsSection}>
                <Text style={styles.tipsTitle}>Tips for helpful reviews:</Text>
                <Text style={styles.tip}>• Be honest and unbiased</Text>
                <Text style={styles.tip}>• Share your personal experience</Text>
                <Text style={styles.tip}>• Be respectful and constructive</Text>
              </View>
            </ScrollView>

            {/* Action Buttons */}
            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={styles.cancelButton}
                onPress={() => setIsReviewModalVisible(false)}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.submitButton}
                onPress={handleSubmitReview}
              >
                <Text style={styles.submitButtonText}>Post Review</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.background,
  },
  header: {
    backgroundColor: colors.primary,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 15,
    paddingTop: 40,
  },
  backButton: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '600',
  },
  title: {
    color: colors.white,
    fontSize: 18,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    padding: 15,
  },
  ratingSummary: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 20,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: colors.border,
  },
  averageRating: {
    alignItems: 'center',
    marginBottom: 20,
    paddingBottom: 20,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  averageNumber: {
    fontSize: 48,
    fontWeight: 'bold',
    color: colors.primary,
  },
  averageStars: {
    fontSize: 24,
    marginVertical: 4,
  },
  totalReviews: {
    fontSize: 14,
    color: colors.textLight,
  },
  ratingBars: {
    gap: 10,
  },
  ratingBarRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  ratingBarLabel: {
    width: 50,
    fontSize: 12,
    fontWeight: '600',
    color: colors.text,
  },
  ratingBarBackground: {
    flex: 1,
    height: 8,
    backgroundColor: colors.lightGray,
    borderRadius: 4,
    overflow: 'hidden',
  },
  ratingBarFill: {
    height: '100%',
    backgroundColor: colors.primary,
  },
  ratingBarCount: {
    width: 30,
    textAlign: 'right',
    fontSize: 12,
    fontWeight: '600',
    color: colors.text,
  },
  writeReviewButton: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 15,
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 15,
    borderWidth: 2,
    borderColor: colors.primary,
  },
  writeReviewIcon: {
    fontSize: 24,
    marginRight: 12,
  },
  writeReviewText: {
    flex: 1,
    fontSize: 14,
    fontWeight: '600',
    color: colors.primary,
  },
  writeReviewArrow: {
    fontSize: 16,
    color: colors.primary,
  },
  filterRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 20,
    justifyContent: 'space-between',
  },
  filterButton: {
    flex: 1,
    backgroundColor: colors.white,
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
  },
  filterText: {
    fontSize: 12,
    fontWeight: '600',
    color: colors.text,
  },
  reviewsSection: {
    gap: 12,
  },
  reviewCard: {
    backgroundColor: colors.white,
    borderRadius: 12,
    padding: 15,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: colors.border,
  },
  reviewHeader: {
    marginBottom: 12,
  },
  reviewAuthor: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 4,
  },
  reviewMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  reviewRating: {
    fontSize: 12,
  },
  reviewDate: {
    fontSize: 12,
    color: colors.textLight,
  },
  verifiedBadge: {
    fontSize: 11,
    color: colors.primary,
    fontWeight: '600',
  },
  reviewTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 8,
  },
  reviewComment: {
    fontSize: 13,
    color: colors.text,
    lineHeight: 20,
    marginBottom: 12,
  },
  reviewFooter: {
    borderTopWidth: 1,
    borderTopColor: colors.border,
    paddingTopColor: 12,
  },
  helpfulButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingTop: 12,
  },
  helpfulIcon: {
    fontSize: 16,
  },
  helpfulText: {
    fontSize: 12,
    color: colors.textLight,
  },
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 40,
  },
  emptyText: {
    fontSize: 16,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 4,
  },
  emptySubtext: {
    fontSize: 12,
    color: colors.textLight,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: colors.white,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '90%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: colors.text,
  },
  closeButton: {
    fontSize: 24,
    color: colors.textLight,
  },
  modalBody: {
    padding: 15,
    maxHeight: '70%',
  },
  ratingSelector: {
    marginBottom: 20,
  },
  sectionLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 10,
  },
  ratingButtons: {
    flexDirection: 'row',
    gap: 10,
  },
  ratingButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: colors.border,
    alignItems: 'center',
  },
  ratingButtonSelected: {
    borderColor: colors.primary,
    backgroundColor: 'rgba(139, 26, 26, 0.1)',
  },
  ratingButtonText: {
    fontSize: 12,
  },
  inputGroup: {
    marginBottom: 20,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: colors.text,
    marginBottom: 4,
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  charCount: {
    fontSize: 12,
    color: colors.textLight,
    textAlign: 'right',
  },
  tipsSection: {
    backgroundColor: colors.lightGray,
    borderRadius: 8,
    padding: 12,
    marginBottom: 20,
  },
  tipsTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: colors.text,
    marginBottom: 8,
  },
  tip: {
    fontSize: 12,
    color: colors.text,
    marginBottom: 4,
    lineHeight: 18,
  },
  modalFooter: {
    flexDirection: 'row',
    gap: 10,
    paddingHorizontal: 15,
    paddingVertical: 15,
    borderTopWidth: 1,
    borderTopColor: colors.border,
  },
  cancelButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
  },
  cancelButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.text,
  },
  submitButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: colors.primary,
    alignItems: 'center',
  },
  submitButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: colors.white,
  },
});
