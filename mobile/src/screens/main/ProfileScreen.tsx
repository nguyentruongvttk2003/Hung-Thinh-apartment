import React from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  SafeAreaView, 
  TouchableOpacity, 
  ScrollView 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppSelector } from '../../store';

export const ProfileScreen = () => {
  const { user } = useAppSelector(state => state.auth);

  const ProfileItem = ({ 
    icon, 
    title, 
    value, 
    onPress 
  }: {
    icon: string;
    title: string;
    value?: string;
    onPress?: () => void;
  }) => (
    <TouchableOpacity style={styles.profileItem} onPress={onPress}>
      <View style={styles.profileItemLeft}>
        <View style={styles.iconContainer}>
          <Ionicons name={icon as any} size={20} color="#007AFF" />
        </View>
        <Text style={styles.profileItemTitle}>{title}</Text>
      </View>
      <View style={styles.profileItemRight}>
        {value && <Text style={styles.profileItemValue}>{value}</Text>}
        <Ionicons name="chevron-forward" size={16} color="#C7C7CC" />
      </View>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView style={styles.scrollView}>
        {/* User Info Header */}
        <View style={styles.userHeader}>
          <View style={styles.avatarContainer}>
            <Ionicons name="person-circle" size={80} color="#007AFF" />
          </View>
          <Text style={styles.userName}>{user?.name || 'Cư dân'}</Text>
          <Text style={styles.userEmail}>{user?.email || 'email@example.com'}</Text>
          <Text style={styles.apartmentInfo}>
            {user?.apartmentId ? `Căn hộ ${user.apartmentId}` : 'Chưa có thông tin căn hộ'}
          </Text>
        </View>

        {/* Profile Sections */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Thông tin cá nhân</Text>
          <View style={styles.sectionContent}>
            <ProfileItem
              icon="person-outline"
              title="Tên đầy đủ"
              value={user?.name || 'Chưa cập nhật'}
            />
            <ProfileItem
              icon="mail-outline"
              title="Email"
              value={user?.email || 'Chưa cập nhật'}
            />
            <ProfileItem
              icon="call-outline"
              title="Số điện thoại"
              value={user?.phone || 'Chưa cập nhật'}
            />
            <ProfileItem
              icon="home-outline"
              title="Căn hộ"
              value={user?.apartmentId ? `Căn hộ ${user.apartmentId}` : 'Chưa cập nhật'}
            />
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Cài đặt</Text>
          <View style={styles.sectionContent}>
            <ProfileItem
              icon="notifications-outline"
              title="Thông báo"
              value="Bật"
            />
            <ProfileItem
              icon="language-outline"
              title="Ngôn ngữ"
              value="Tiếng Việt"
            />
            <ProfileItem
              icon="shield-checkmark-outline"
              title="Bảo mật"
            />
            <ProfileItem
              icon="help-circle-outline"
              title="Trợ giúp"
            />
          </View>
        </View>

        <View style={styles.section}>
          <View style={styles.sectionContent}>
            <TouchableOpacity style={styles.logoutButton}>
              <Ionicons name="log-out-outline" size={20} color="#FF3B30" />
              <Text style={styles.logoutText}>Đăng xuất</Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.footer}>
          <Text style={styles.footerText}>Ứng dụng quản lý chung cư v1.0</Text>
        </View>
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
  userHeader: {
    backgroundColor: '#FFFFFF',
    alignItems: 'center',
    paddingVertical: 30,
    paddingHorizontal: 20,
    marginBottom: 20,
  },
  avatarContainer: {
    marginBottom: 16,
  },
  userName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#1C1C1E',
    marginBottom: 4,
  },
  userEmail: {
    fontSize: 16,
    color: '#8E8E93',
    marginBottom: 8,
  },
  apartmentInfo: {
    fontSize: 16,
    color: '#007AFF',
    fontWeight: '600',
  },
  section: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1C1C1E',
    marginHorizontal: 20,
    marginBottom: 8,
  },
  sectionContent: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 15,
    borderRadius: 12,
    overflow: 'hidden',
  },
  profileItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 16,
    paddingHorizontal: 16,
    borderBottomWidth: 0.5,
    borderBottomColor: '#E5E5EA',
  },
  profileItemLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  iconContainer: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#F2F2F7',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  profileItemTitle: {
    fontSize: 16,
    color: '#1C1C1E',
  },
  profileItemRight: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  profileItemValue: {
    fontSize: 16,
    color: '#8E8E93',
    marginRight: 8,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
  },
  logoutText: {
    fontSize: 16,
    color: '#FF3B30',
    fontWeight: '600',
    marginLeft: 8,
  },
  footer: {
    alignItems: 'center',
    paddingVertical: 20,
  },
  footerText: {
    fontSize: 14,
    color: '#8E8E93',
  },
});
