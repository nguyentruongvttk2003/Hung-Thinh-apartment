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
import { fetchEvents, clearError } from '../store/eventSlice';

export const EventsScreen = () => {
  const dispatch = useAppDispatch();
  const { events, isLoading, error, hasMore } = useAppSelector(state => state.events);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    dispatch(fetchEvents({ page: 1, refresh: true }));
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
      await dispatch(fetchEvents({ page: 1, refresh: true })).unwrap();
    } catch (error) {
      console.error('Error refreshing events:', error);
    } finally {
      setRefreshing(false);
    }
  };

  const onLoadMore = () => {
    if (!isLoading && hasMore) {
      const nextPage = Math.floor(events.length / 10) + 1;
      dispatch(fetchEvents({ page: nextPage }));
    }
  };

  const getEventIcon = (type: string) => {
    switch (type) {
      case 'meeting': return 'people-outline';
      case 'maintenance': return 'construct-outline';
      case 'power_outage': return 'flash-outline';
      case 'water_outage': return 'water-outline';
      case 'social_event': return 'heart-outline';
      case 'emergency': return 'warning-outline';
      default: return 'calendar-outline';
    }
  };

  const getEventColor = (type: string) => {
    switch (type) {
      case 'meeting': return '#007AFF';
      case 'maintenance': return '#FF9500';
      case 'power_outage': return '#FF3B30';
      case 'water_outage': return '#5AC8FA';
      case 'social_event': return '#34C759';
      case 'emergency': return '#FF2D55';
      default: return '#8E8E93';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'scheduled': return '#FF9500';
      case 'in_progress': return '#5AC8FA';
      case 'completed': return '#34C759';
      case 'cancelled': return '#8E8E93';
      default: return '#8E8E93';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'scheduled': return 'Đã lên lịch';
      case 'in_progress': return 'Đang diễn ra';
      case 'completed': return 'Đã hoàn thành';
      case 'cancelled': return 'Đã hủy';
      default: return 'Không xác định';
    }
  };

  const formatDateTime = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const renderEventItem = ({ item }: { item: any }) => (
    <TouchableOpacity style={styles.eventItem}>
      <View style={styles.eventHeader}>
        <View style={[styles.eventIconContainer, { backgroundColor: getEventColor(item.type) + '20' }]}>
          <Ionicons 
            name={getEventIcon(item.type)} 
            size={24} 
            color={getEventColor(item.type)} 
          />
        </View>
        <View style={styles.eventContent}>
          <Text style={styles.eventTitle} numberOfLines={2}>
            {item.title || 'Sự kiện'}
          </Text>
          <Text style={styles.eventDescription} numberOfLines={2}>
            {item.description || 'Không có mô tả'}
          </Text>
          <View style={styles.eventMetaInfo}>
            <Text style={styles.eventDateTime}>
              {formatDateTime(item.start_time)}
            </Text>
            {item.location && (
              <Text style={styles.eventLocation}>
                📍 {item.location}
              </Text>
            )}
          </View>
        </View>
        <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '20' }]}>
          <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
            {getStatusText(item.status)}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Sự kiện</Text>
        <Text style={styles.headerSubtitle}>
          {events.length} sự kiện
        </Text>
      </View>
      
      <FlatList
        data={events}
        renderItem={renderEventItem}
        keyExtractor={(item) => String(item.id || Math.random())}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        onEndReached={onLoadMore}
        onEndReachedThreshold={0.1}
        contentContainerStyle={events.length === 0 ? styles.emptyContainer : styles.listContainer}
        ListEmptyComponent={
          <View style={styles.emptyState}>
            <Ionicons name="calendar-outline" size={64} color="#C7C7CC" />
            <Text style={styles.emptyTitle}>Chưa có sự kiện</Text>
            <Text style={styles.emptySubtitle}>
              Các sự kiện mới sẽ xuất hiện tại đây
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
  eventItem: {
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
  eventHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  eventIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  eventContent: {
    flex: 1,
    marginRight: 12,
  },
  eventTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#000',
    marginBottom: 4,
  },
  eventDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  eventMetaInfo: {
    gap: 4,
  },
  eventDateTime: {
    fontSize: 12,
    color: '#007AFF',
    fontWeight: '500',
  },
  eventLocation: {
    fontSize: 12,
    color: '#666',
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    alignSelf: 'flex-start',
  },
  statusText: {
    fontSize: 12,
    fontWeight: '500',
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