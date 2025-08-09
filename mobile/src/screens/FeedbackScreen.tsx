import React, { useEffect, useState } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  FlatList, 
  TouchableOpacity, 
  RefreshControl,
  SafeAreaView,
  Alert
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppDispatch, useAppSelector } from '../store';
import { fetchFeedbacks, clearError } from '../store/feedbackSlice';

export const FeedbackScreen = () => {
  const dispatch = useAppDispatch();
  const { feedbacks, isLoading, error, hasMore } = useAppSelector(state => state.feedback);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    dispatch(fetchFeedbacks({ page: 1, refresh: true }));
  }, [dispatch]);

  useEffect(() => {
    if (error) {
      Alert.alert('Lỗi', error, [
        { text: 'OK', onPress: () => dispatch(clearError()) }
      ]);
    }
  }, [error, dispatch]);

  const onRefresh = async () => {
    setRefreshing(true);
    try {
      await dispatch(fetchFeedbacks({ page: 1, refresh: true })).unwrap();
    } catch (error) {
      console.error('Error refreshing feedbacks:', error);
    } finally {
      setRefreshing(false);
    }
  };

  const onLoadMore = () => {
    if (!isLoading && hasMore) {
      const nextPage = Math.floor(feedbacks.length / 10) + 1;
      dispatch(fetchFeedbacks({ page: nextPage }));
    }
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'maintenance': return 'construct-outline';
      case 'complaint': return 'alert-circle-outline';
      case 'suggestion': return 'bulb-outline';
      case 'security': return 'shield-outline';
      default: return 'chatbubble-outline';
    }
  };

  const getCategoryColor = (category: string) => {
    switch (category) {
      case 'maintenance': return '#FF9500';
      case 'complaint': return '#FF3B30';
      case 'suggestion': return '#34C759';
      case 'security': return '#007AFF';
      default: return '#8E8E93';
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'urgent': return '#FF2D55';
      case 'high': return '#FF3B30';
      case 'normal': return '#FF9500';
      case 'low': return '#34C759';
      default: return '#8E8E93';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'submitted': return '#FF9500';
      case 'reviewing': return '#5AC8FA';
      case 'assigned': return '#007AFF';
      case 'in_progress': return '#5856D6';
      case 'resolved': return '#34C759';
      case 'closed': return '#8E8E93';
      default: return '#8E8E93';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'submitted': return 'Đã gửi';
      case 'reviewing': return 'Đang xem xét';
      case 'assigned': return 'Đã phân công';
      case 'in_progress': return 'Đang xử lý';
      case 'resolved': return 'Đã giải quyết';
      case 'closed': return 'Đã đóng';
      default: return 'Không xác định';
    }
  };

  const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = now.getTime() - date.getTime();
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
      const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
      if (diffHours === 0) {
        const diffMinutes = Math.floor(diffTime / (1000 * 60));
        return diffMinutes < 1 ? 'Vừa xong' : `${diffMinutes} phút trước`;
      }
      return `${diffHours} giờ trước`;
    } else if (diffDays === 1) {
      return 'Hôm qua';
    } else if (diffDays < 7) {
      return `${diffDays} ngày trước`;
    } else {
      return date.toLocaleDateString('vi-VN');
    }
  };

  const renderFeedbackItem = ({ item }: { item: any }) => (
    <TouchableOpacity style={styles.feedbackItem}>
      <View style={styles.feedbackHeader}>
        <View style={[styles.categoryIconContainer, { backgroundColor: getCategoryColor(item.category) + '20' }]}>
          <Ionicons 
            name={getCategoryIcon(item.category)} 
            size={24} 
            color={getCategoryColor(item.category)} 
          />
        </View>
        <View style={styles.feedbackContent}>
          <Text style={styles.feedbackTitle} numberOfLines={2}>
            {item.title || 'Phản hồi'}
          </Text>
          <Text style={styles.feedbackDescription} numberOfLines={2}>
            {item.description || 'Không có mô tả'}
          </Text>
          <View style={styles.feedbackMetaInfo}>
            <Text style={styles.feedbackDate}>
              {formatDate(item.created_at)}
            </Text>
            {item.apartment && (
              <Text style={styles.feedbackApartment}>
                🏠 {item.apartment.apartment_number}
              </Text>
            )}
          </View>
        </View>
        <View style={styles.badgeContainer}>
          <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '20' }]}>
            <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
              {getStatusText(item.status)}
            </Text>
          </View>
          <View style={[styles.priorityBadge, { backgroundColor: getPriorityColor(item.priority) + '20' }]}>
            <Text style={[styles.priorityText, { color: getPriorityColor(item.priority) }]}>
              {item.priority}
            </Text>
          </View>
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Phản hồi</Text>
        <Text style={styles.headerSubtitle}>
          {feedbacks.length} phản hồi
        </Text>
      </View>
      
      <FlatList
        data={feedbacks}
        renderItem={renderFeedbackItem}
        keyExtractor={(item) => String(item.id || Math.random())}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        onEndReached={onLoadMore}
        onEndReachedThreshold={0.1}
        contentContainerStyle={feedbacks.length === 0 ? styles.emptyContainer : styles.listContainer}
        ListEmptyComponent={
          <View style={styles.emptyState}>
            <Ionicons name="chatbubbles-outline" size={64} color="#C7C7CC" />
            <Text style={styles.emptyTitle}>Chưa có phản hồi</Text>
            <Text style={styles.emptySubtitle}>
              Các phản hồi của bạn sẽ xuất hiện tại đây
            </Text>
          </View>
        }
      />
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F9FA',
  },
  header: {
    padding: 20,
    paddingBottom: 10,
    backgroundColor: '#FFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E5EA',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#000',
  },
  headerSubtitle: {
    fontSize: 16,
    color: '#666',
    marginTop: 4,
  },
  listContainer: {
    padding: 20,
    paddingTop: 10,
  },
  feedbackItem: {
    backgroundColor: '#FFF',
    borderRadius: 12,
    marginBottom: 12,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 5,
  },
  feedbackHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  categoryIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  feedbackContent: {
    flex: 1,
    marginRight: 12,
  },
  feedbackTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#000',
    marginBottom: 4,
  },
  feedbackDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  feedbackMetaInfo: {
    gap: 4,
  },
  feedbackDate: {
    fontSize: 12,
    color: '#007AFF',
    fontWeight: '500',
  },
  feedbackApartment: {
    fontSize: 12,
    color: '#666',
  },
  badgeContainer: {
    gap: 6,
    alignItems: 'flex-end',
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '500',
  },
  priorityBadge: {
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 8,
  },
  priorityText: {
    fontSize: 10,
    fontWeight: '600',
    textTransform: 'uppercase',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 40,
  },
  emptyState: {
    alignItems: 'center',
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: '#8E8E93',
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtitle: {
    fontSize: 16,
    color: '#C7C7CC',
    textAlign: 'center',
    lineHeight: 22,
  },
});