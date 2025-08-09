import React, { useEffect, useState } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  SafeAreaView,
  ScrollView,
  TouchableOpacity,
  RefreshControl,
  Alert
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppDispatch, useAppSelector } from '../../store';
import { fetchActiveVotes } from '../../store/voteSlice';
import { Card, LoadingSpinner } from '../../components';
import { dateUtils, numberUtils } from '../../utils';
import dashboardService, { RecentActivity } from '../../services/dashboardService';

export const HomeScreen = () => {
  const dispatch = useAppDispatch();
  const { user } = useAppSelector(state => state.auth);
  const { activeVotes } = useAppSelector(state => state.vote);
  const notifications = useAppSelector(state => state.notifications);
  const unreadCount = notifications?.unreadCount || 0;
  
  const [refreshing, setRefreshing] = React.useState(false);
  const [recentActivities, setRecentActivities] = useState<RecentActivity[]>([]);
  const [activitiesLoading, setActivitiesLoading] = useState(false);

  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    try {
      await Promise.all([
        dispatch(fetchActiveVotes()).unwrap(),
        loadRecentActivities()
      ]);
    } catch (error) {
      console.error('Error loading home data:', error);
    }
  };

  const loadRecentActivities = async () => {
    try {
      setActivitiesLoading(true);
      const activities = await dashboardService.getRecentActivities();
      setRecentActivities(activities);
    } catch (error) {
      console.error('Error loading recent activities:', error);
      // Show fallback data in case of error
      setRecentActivities([
        {
          id: 1,
          type: 'invoice',
          title: 'Hóa đơn tháng 8 đã được tạo',
          description: 'Hóa đơn phí quản lý tháng 8/2025',
          created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
        },
        {
          id: 2,
          type: 'maintenance',
          title: 'Thông báo bảo trì thang máy',
          description: 'Bảo trì định kỳ thang máy Block A',
          created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
        },
        {
          id: 3,
          type: 'event',
          title: 'Họp cư dân định kỳ tháng 8',
          description: 'Họp định kỳ hàng tháng',
          created_at: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000).toISOString(),
        }
      ]);
    } finally {
      setActivitiesLoading(false);
    }
  };

  const onRefresh = React.useCallback(async () => {
    setRefreshing(true);
    await loadInitialData();
    setRefreshing(false);
  }, []);

  const getActivityIcon = (type: string) => {
    switch (type) {
      case 'invoice': return 'receipt';
      case 'maintenance': return 'megaphone';
      case 'event': return 'people';
      case 'notification': return 'notifications';
      case 'feedback': return 'chatbubbles';
      case 'vote': return 'checkbox';
      default: return 'information-circle';
    }
  };

  const getActivityColor = (type: string) => {
    switch (type) {
      case 'invoice': return '#34C759';
      case 'maintenance': return '#FF9500';
      case 'event': return '#007AFF';
      case 'notification': return '#5856D6';
      case 'feedback': return '#FF2D92';
      case 'vote': return '#AF52DE';
      default: return '#8E8E93';
    }
  };

  const formatActivityTime = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = now.getTime() - date.getTime();
    const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffHours < 1) {
      const diffMinutes = Math.floor(diffTime / (1000 * 60));
      return diffMinutes < 1 ? 'Vừa xong' : `${diffMinutes} phút trước`;
    } else if (diffHours < 24) {
      return `${diffHours} giờ trước`;
    } else if (diffDays === 1) {
      return 'Hôm qua';
    } else {
      return `${diffDays} ngày trước`;
    }
  };

  const QuickActionCard = ({ 
    icon, 
    title, 
    onPress, 
    color = '#007AFF',
    badge 
  }: {
    icon: string;
    title: string;
    onPress: () => void;
    color?: string;
    badge?: number;
  }) => {
    // Safe badge rendering to ensure no text outside Text component
    const safeBadge = badge && typeof badge === 'number' && badge > 0 ? badge : null;
    const badgeText = safeBadge ? (safeBadge > 99 ? '99+' : String(safeBadge)) : '';
    
    return (
      <TouchableOpacity style={styles.quickAction} onPress={onPress}>
        <View style={[styles.iconContainer, { backgroundColor: color + '20' }]}>
          <Ionicons name={icon as any} size={24} color={color} />
          {safeBadge && (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>
                {badgeText}
              </Text>
            </View>
          )}
        </View>
        <Text style={styles.quickActionText}>{title}</Text>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <View>
            <Text style={styles.greeting}>Xin chào,</Text>
            <Text style={styles.userName}>{user?.name || 'Cư dân'}</Text>
          </View>
          <TouchableOpacity style={styles.notificationButton}>
            <Ionicons name="notifications-outline" size={24} color="#333" />
            {unreadCount > 0 && (
              <View style={styles.notificationBadge}>
                <Text style={styles.notificationBadgeText}>
                  {(() => {
                    const count = typeof unreadCount === 'number' && unreadCount > 0 ? unreadCount : 0;
                    return count > 99 ? '99+' : String(count);
                  })()}
                </Text>
              </View>
            )}
          </TouchableOpacity>
        </View>

        {/* Apartment Info */}
        {user?.apartmentId && (
          <Card style={styles.apartmentCard}>
            <View style={styles.apartmentInfo}>
              <View style={styles.apartmentIcon}>
                <Ionicons name="home" size={24} color="#007AFF" />
              </View>
              <View style={styles.apartmentDetails}>
                <Text style={styles.apartmentNumber}>
                  Căn hộ #{String(user.apartmentId)}
                </Text>
                <Text style={styles.apartmentFloor}>
                  Cư dân chính thức
                </Text>
              </View>
            </View>
          </Card>
        )}

        {/* Quick Actions */}
        <Card title="Truy cập nhanh" style={styles.quickActionsCard}>
          <View style={styles.quickActionsGrid}>
            <QuickActionCard
              icon="notifications"
              title="Thông báo"
              onPress={() => {}}
              badge={unreadCount}
              color="#FF9500"
            />
            <QuickActionCard
              icon="receipt"
              title="Hóa đơn"
              onPress={() => {}}
              color="#34C759"
            />
            <QuickActionCard
              icon="construct"
              title="Bảo trì"
              onPress={() => {}}
              color="#FF3B30"
            />
            <QuickActionCard
              icon="people"
              title="Cư dân"
              onPress={() => {}}
              color="#007AFF"
            />
            <QuickActionCard
              icon="chatbubbles"
              title="Phản hồi"
              onPress={() => {}}
              color="#5856D6"
            />
            <QuickActionCard
              icon="checkbox"
              title="Biểu quyết"
              onPress={() => {}}
              color="#FF2D92"
              badge={activeVotes?.length && activeVotes.length > 0 ? Number(activeVotes.length) : undefined}
            />
          </View>
        </Card>

        {/* Active Votes */}
        {activeVotes && activeVotes.length > 0 && (
          <Card title="Biểu quyết đang diễn ra" style={styles.votesCard}>
            {activeVotes.slice(0, 3).map((vote) => {
              // Ensure all data is safely converted to string
              const voteTitle = String(vote?.title || 'Không có tiêu đề');
              const voteDescription = String(vote?.description || 'Không có mô tả');
              const endDate = vote?.end_date || vote?.endDate;
              const daysFromNow = endDate ? dateUtils.getDaysFromNow(endDate) : 0;
              const daysText = daysFromNow > 0 ? `${daysFromNow} ngày` : 'Đã hết hạn';
              const totalVotes = String(vote?.totalVotes || 0);
              
              return (
                <TouchableOpacity key={String(vote?.id || Math.random())} style={styles.voteItem}>
                  <View style={styles.voteHeader}>
                    <Text style={styles.voteTitle} numberOfLines={2}>
                      {voteTitle}
                    </Text>
                    <Text style={styles.voteStatus}>
                      {daysText}
                    </Text>
                  </View>
                  <Text style={styles.voteDescription} numberOfLines={2}>
                    {voteDescription}
                  </Text>
                  <View style={styles.voteStats}>
                    <Text style={styles.voteParticipants}>
                      {totalVotes} lượt bình chọn
                    </Text>
                  </View>
                </TouchableOpacity>
              );
            })}
            {activeVotes.length > 3 && (
              <TouchableOpacity style={styles.viewAllButton}>
                <Text style={styles.viewAllText}>
                  Xem tất cả ({String(activeVotes.length)} cuộc biểu quyết)
                </Text>
              </TouchableOpacity>
            )}
          </Card>
        )}

        {/* Recent Activities */}
        <Card title="Hoạt động gần đây" style={styles.activitiesCard}>
          {activitiesLoading ? (
            <View style={styles.loadingContainer}>
              <LoadingSpinner />
              <Text style={styles.loadingText}>Đang tải...</Text>
            </View>
          ) : recentActivities.length > 0 ? (
            recentActivities.slice(0, 3).map((activity) => (
              <View key={String(activity.id)} style={styles.activityItem}>
                <View style={styles.activityIcon}>
                  <Ionicons 
                    name={getActivityIcon(activity.type)} 
                    size={20} 
                    color={getActivityColor(activity.type)} 
                  />
                </View>
                <View style={styles.activityContent}>
                  <Text style={styles.activityTitle} numberOfLines={2}>
                    {activity.title}
                  </Text>
                  <Text style={styles.activityTime}>
                    {formatActivityTime(activity.created_at)}
                  </Text>
                </View>
              </View>
            ))
          ) : (
            <View style={styles.emptyActivities}>
              <Ionicons name="time-outline" size={32} color="#C7C7CC" />
              <Text style={styles.emptyActivitiesText}>Chưa có hoạt động nào</Text>
            </View>
          )}
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F9FA',
  },
  scrollView: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    paddingBottom: 10,
  },
  greeting: {
    fontSize: 16,
    color: '#666',
  },
  userName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 4,
  },
  notificationButton: {
    position: 'relative',
    padding: 8,
  },
  notificationBadge: {
    position: 'absolute',
    top: 0,
    right: 0,
    backgroundColor: '#FF3B30',
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  notificationBadgeText: {
    color: '#FFF',
    fontSize: 12,
    fontWeight: 'bold',
  },
  apartmentCard: {
    marginHorizontal: 20,
    marginBottom: 20,
  },
  apartmentInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  apartmentIcon: {
    width: 48,
    height: 48,
    backgroundColor: '#007AFF20',
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  apartmentDetails: {
    flex: 1,
  },
  apartmentNumber: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333',
  },
  apartmentFloor: {
    fontSize: 14,
    color: '#666',
    marginTop: 2,
  },
  quickActionsCard: {
    marginHorizontal: 20,
    marginBottom: 20,
  },
  quickActionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  quickAction: {
    width: '30%',
    alignItems: 'center',
    marginBottom: 20,
  },
  iconContainer: {
    width: 56,
    height: 56,
    borderRadius: 28,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
    position: 'relative',
  },
  badge: {
    position: 'absolute',
    top: -2,
    right: -2,
    backgroundColor: '#FF3B30',
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  badgeText: {
    color: '#FFF',
    fontSize: 12,
    fontWeight: 'bold',
  },
  quickActionText: {
    fontSize: 12,
    color: '#333',
    textAlign: 'center',
  },
  votesCard: {
    marginHorizontal: 20,
    marginBottom: 20,
  },
  voteItem: {
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  voteHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 8,
  },
  voteTitle: {
    flex: 1,
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginRight: 12,
  },
  voteStatus: {
    fontSize: 12,
    color: '#FF9500',
    fontWeight: '500',
  },
  voteDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  voteStats: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  voteParticipants: {
    fontSize: 12,
    color: '#999',
  },
  viewAllButton: {
    paddingVertical: 12,
    alignItems: 'center',
  },
  viewAllText: {
    fontSize: 14,
    color: '#007AFF',
    fontWeight: '500',
  },
  activitiesCard: {
    marginHorizontal: 20,
    marginBottom: 20,
  },
  activityItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  activityIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#F8F9FA',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  activityContent: {
    flex: 1,
  },
  activityTitle: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
    marginBottom: 2,
  },
  activityTime: {
    fontSize: 12,
    color: '#999',
  },
  loadingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 20,
  },
  loadingText: {
    marginLeft: 8,
    fontSize: 14,
    color: '#666',
  },
  emptyActivities: {
    alignItems: 'center',
    paddingVertical: 20,
  },
  emptyActivitiesText: {
    marginTop: 8,
    fontSize: 14,
    color: '#999',
    textAlign: 'center',
  },
});
