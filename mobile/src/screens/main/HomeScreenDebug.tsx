import React, { useEffect } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  SafeAreaView,
  ScrollView,
  TouchableOpacity,
  RefreshControl
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppDispatch, useAppSelector } from '../../store';
import { fetchActiveVotes } from '../../store/voteSlice';
import { Card, LoadingSpinner } from '../../components';
import { dateUtils, numberUtils } from '../../utils';

export const HomeScreenDebug = () => {
  const dispatch = useAppDispatch();
  const { user } = useAppSelector(state => state.auth);
  const { activeVotes } = useAppSelector(state => state.vote);
  const notifications = useAppSelector(state => state.notifications);
  const unreadCount = notifications?.unreadCount || 0;
  
  const [refreshing, setRefreshing] = React.useState(false);

  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    try {
      await dispatch(fetchActiveVotes()).unwrap();
    } catch (error) {
      console.error('Error loading home data:', error);
    }
  };

  const onRefresh = React.useCallback(async () => {
    setRefreshing(true);
    await loadInitialData();
    setRefreshing(false);
  }, []);

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView
        style={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {/* Simple Header */}
        <View style={styles.header}>
          <Text style={styles.greeting}>Xin chào</Text>
          <Text style={styles.userName}>{user?.name || 'Cư dân'}</Text>
        </View>

        {/* Simple Card with Text only */}
        <Card title="Test Card" style={styles.testCard}>
          <Text style={styles.testText}>This is a test</Text>
        </Card>

        {/* Simple TouchableOpacity with Text */}
        <TouchableOpacity style={styles.testButton}>
          <Text style={styles.testButtonText}>Test Button</Text>
        </TouchableOpacity>

        {/* Vote data display */}
        <Card title="Vote Debug Info" style={styles.debugCard}>
          <Text style={styles.debugText}>
            Active Votes Count: {String(activeVotes?.length || 0)}
          </Text>
          <Text style={styles.debugText}>
            Active Votes Type: {typeof activeVotes}
          </Text>
          <Text style={styles.debugText}>
            Active Votes: {JSON.stringify(activeVotes || [], null, 2)}
          </Text>
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
    padding: 20,
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
  testCard: {
    marginHorizontal: 20,
    marginBottom: 20,
  },
  testText: {
    fontSize: 16,
    color: '#333',
  },
  testButton: {
    backgroundColor: '#007AFF',
    marginHorizontal: 20,
    marginBottom: 20,
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
  },
  testButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  debugCard: {
    marginHorizontal: 20,
    marginBottom: 20,
  },
  debugText: {
    fontSize: 12,
    color: '#333',
    marginBottom: 4,
    fontFamily: 'monospace',
  },
});
