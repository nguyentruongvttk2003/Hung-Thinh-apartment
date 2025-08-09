import React, { useEffect, useCallback } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  FlatList, 
  TouchableOpacity, 
  RefreshControl,
  SafeAreaView,
  ActivityIndicator,
  Alert
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useDispatch, useSelector } from 'react-redux';
import { RootState, AppDispatch } from '../../store';
import { fetchInvoices, clearError } from '../../store/invoiceSlice';

export const InvoicesScreen = () => {
  const dispatch = useDispatch<AppDispatch>();
  const { invoices, isLoading, error, hasMore } = useSelector((state: RootState) => state.invoices);

  useEffect(() => {
    dispatch(fetchInvoices({ page: 1, refresh: true }));
  }, [dispatch]);

  useEffect(() => {
    if (error) {
      Alert.alert('Lỗi', error, [
        { text: 'OK', onPress: () => dispatch(clearError()) }
      ]);
    }
  }, [error, dispatch]);

  const onRefresh = useCallback(async () => {
    dispatch(fetchInvoices({ page: 1, refresh: true }));
  }, [dispatch]);

  const onLoadMore = useCallback(() => {
    if (!isLoading && hasMore) {
      dispatch(fetchInvoices({ page: invoices.length > 0 ? Math.floor(invoices.length / 10) + 1 : 1 }));
    }
  }, [dispatch, isLoading, hasMore, invoices.length]);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'paid': return '#34C759';
      case 'pending': return '#FF9500'; 
      case 'partial': return '#5AC8FA';
      case 'overdue': return '#FF3B30';
      case 'cancelled': return '#8E8E93';
      default: return '#8E8E93';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'paid': return 'Đã thanh toán';
      case 'pending': return 'Chờ thanh toán';
      case 'partial': return 'Thanh toán một phần';
      case 'overdue': return 'Quá hạn';
      case 'cancelled': return 'Đã hủy';
      default: return 'Không xác định';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('vi-VN');
  };

  const renderInvoiceItem = ({ item }: { item: any }) => (
    <TouchableOpacity style={styles.invoiceItem}>
      <View style={styles.invoiceHeader}>
        <View style={styles.invoiceInfo}>
          <Text style={styles.invoiceTitle}>
            {item.title || item.invoice_number || `Hóa đơn #${item.id}`}
          </Text>
          <Text style={styles.invoiceDate}>
            Hạn thanh toán: {formatDate(item.due_date)}
          </Text>
        </View>
        <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '20' }]}>
          <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
            {getStatusText(item.status)}
          </Text>
        </View>
      </View>
      
      <View style={styles.invoiceDetails}>
        <Text style={styles.amountLabel}>Số tiền</Text>
        <Text style={styles.amountValue}>
          {formatCurrency(item.total_amount || item.amount || 0)}
        </Text>
      </View>
      
      <View style={styles.invoiceFooter}>
        <Text style={styles.invoiceDescription} numberOfLines={2}>
          {item.notes || item.description || 'Không có mô tả'}
        </Text>
        <Ionicons name="chevron-forward" size={16} color="#C7C7CC" />
      </View>
    </TouchableOpacity>
  );

  if (isLoading && invoices.length === 0) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#007AFF" />
          <Text style={styles.loadingText}>Đang tải hóa đơn...</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Hóa đơn</Text>
        <Text style={styles.headerSubtitle}>
          {invoices.length} hóa đơn
        </Text>
      </View>
      
      <FlatList
        data={invoices}
        renderItem={renderInvoiceItem}
        keyExtractor={(item) => String(item.id || Math.random())}
        refreshControl={
          <RefreshControl refreshing={isLoading} onRefresh={onRefresh} />
        }
        contentContainerStyle={styles.listContainer}
        onEndReached={onLoadMore}
        onEndReachedThreshold={0.5}
        ListFooterComponent={
          isLoading && invoices.length > 0 ? (
            <View style={styles.footerLoader}>
              <ActivityIndicator size="small" color="#007AFF" />
            </View>
          ) : null
        }
        ListEmptyComponent={
          !isLoading ? (
            <View style={styles.emptyContainer}>
              <Ionicons name="receipt-outline" size={64} color="#C7C7CC" />
              <Text style={styles.emptyText}>Không có hóa đơn nào</Text>
              <Text style={styles.emptySubtext}>Hóa đơn của bạn sẽ hiển thị ở đây</Text>
            </View>
          ) : null
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
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E5EA',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#1C1C1E',
    marginBottom: 4,
  },
  headerSubtitle: {
    fontSize: 16,
    color: '#8E8E93',
  },
  listContainer: {
    paddingVertical: 10,
  },
  invoiceItem: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 15,
    marginVertical: 5,
    borderRadius: 12,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 2,
  },
  invoiceHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  invoiceInfo: {
    flex: 1,
    marginRight: 12,
  },
  invoiceTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1C1C1E',
    marginBottom: 4,
  },
  invoiceDate: {
    fontSize: 14,
    color: '#8E8E93',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
  },
  invoiceDetails: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
    paddingVertical: 8,
    backgroundColor: '#F8F9FA',
    borderRadius: 8,
    paddingHorizontal: 12,
  },
  amountLabel: {
    fontSize: 14,
    color: '#8E8E93',
  },
  amountValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#1C1C1E',
  },
  invoiceFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  invoiceDescription: {
    flex: 1,
    fontSize: 14,
    color: '#3C3C43',
    marginRight: 8,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#8E8E93',
  },
  footerLoader: {
    padding: 20,
    alignItems: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 50,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1C1C1E',
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 14,
    color: '#8E8E93',
    textAlign: 'center',
    lineHeight: 20,
  },
});
