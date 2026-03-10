import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    StyleSheet,
    FlatList,
    TouchableOpacity,
    ActivityIndicator,
    Alert,
    Modal,
    RefreshControl,
    TextInput,
    ScrollView
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppContext } from '../context/AppContext';
import { userService } from '../services/api';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';
const SECONDARY_BLUE = '#F4F7FE';

const UserManagementScreen = ({ route }) => {
    const { user: adminUser } = useAppContext();
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [updating, setUpdating] = useState(false);
    const [editName, setEditName] = useState('');
    const [editPhone, setEditPhone] = useState('');
    const [selectedUser, setSelectedUser] = useState(null);
    const [modalVisible, setModalVisible] = useState(false);

    const openManageModal = (userItem) => {
        setSelectedUser(userItem);
        setEditName(userItem.full_name || '');
        setEditPhone(userItem.phone || '');
        setModalVisible(true);
    };

    const fetchUsers = async () => {
        try {
            const response = await userService.getUsers(adminUser.id);
            if (response.data?.success) {
                setUsers(response.data.data);
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            Alert.alert('Error', 'Failed to fetch users');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        fetchUsers();
    }, []);

    const onRefresh = () => {
        setRefreshing(true);
        fetchUsers();
    };

    const handleUpdateUser = async (role, isBlocked) => {
        setUpdating(true);
        try {
            const response = await userService.updateUser(adminUser.id, {
                user_id: selectedUser.id,
                role: role,
                is_blocked: isBlocked,
                full_name: editName,
                phone: editPhone
            });

            if (response.data?.success) {
                Alert.alert('Success', 'User updated successfully');
                setModalVisible(false);
                fetchUsers();
            } else {
                Alert.alert('Error', response.data?.message || 'Update failed');
            }
        } catch (error) {
            console.error('Update error:', error);
            Alert.alert('Error', 'Connection error');
        } finally {
            setUpdating(false);
        }
    };

    const renderUser = ({ item }) => (
        <TouchableOpacity
            style={styles.userCard}
            onPress={() => openManageModal(item)}
        >
            <View style={styles.userHeader}>
                <View style={styles.avatar}>
                    <Text style={styles.avatarText}>{item.full_name[0]}</Text>
                </View>
                <View style={styles.userInfo}>
                    <Text style={styles.userName}>{item.full_name}</Text>
                    <Text style={styles.userEmail}>{item.email}</Text>
                </View>
                <View style={[styles.roleBadge, { backgroundColor: getRoleColor(item.role) + '15' }]}>
                    <Text style={[styles.roleText, { color: getRoleColor(item.role) }]}>
                        {item.role.toUpperCase()}
                    </Text>
                </View>
            </View>
            {item.is_blocked === 1 && (
                <View style={styles.blockedBadge}>
                    <Ionicons name="lock-closed" size={12} color="#EE5D50" />
                    <Text style={styles.blockedText}>BLOCKED</Text>
                </View>
            )}
        </TouchableOpacity>
    );

    const getRoleColor = (role) => {
        switch (role) {
            case 'admin': return '#F57F17';
            case 'staff': return BLUE;
            default: return '#707EAE';
        }
    };

    if (loading) {
        return (
            <View style={styles.center}>
                <ActivityIndicator size="large" color={BLUE} />
            </View>
        );
    }

    const displayedUsers = users.filter(u => {
        if (route?.name === 'Customers') return u.role === 'customer';
        if (route?.name === 'Staff List') return u.role === 'staff' || u.role === 'admin';
        return true;
    });

    return (
        <View style={styles.container}>
            <FlatList
                data={displayedUsers}
                keyExtractor={(item) => item.id.toString()}
                renderItem={renderUser}
                contentContainerStyle={styles.list}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[BLUE]} />}
            />

            <Modal
                animationType="slide"
                transparent={true}
                visible={modalVisible}
                onRequestClose={() => setModalVisible(false)}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Manage User</Text>
                            <TouchableOpacity onPress={() => setModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#A3AED0" />
                            </TouchableOpacity>
                        </View>

                        {selectedUser && (
                            <ScrollView showsVerticalScrollIndicator={false}>
                                <View style={styles.selectedUserInfo}>
                                    <View style={styles.inputGroup}>
                                        <Text style={styles.inputLabel}>Full Name</Text>
                                        <TextInput
                                            style={styles.textInput}
                                            value={editName}
                                            onChangeText={setEditName}
                                        />
                                    </View>
                                    <View style={styles.inputGroup}>
                                        <Text style={styles.inputLabel}>Phone</Text>
                                        <TextInput
                                            style={styles.textInput}
                                            value={editPhone}
                                            onChangeText={setEditPhone}
                                            keyboardType="phone-pad"
                                        />
                                    </View>
                                    <Text style={styles.detailEmail}>{selectedUser.email}</Text>
                                </View>

                                <Text style={styles.subTitle}>Change Role</Text>
                                <View style={styles.buttonRow}>
                                    <TouchableOpacity
                                        style={[styles.roleBtn, selectedUser.role === 'customer' && styles.activeBtn]}
                                        onPress={() => handleUpdateUser('customer', selectedUser.is_blocked)}
                                    >
                                        <Text style={[styles.roleBtnText, selectedUser.role === 'customer' && styles.activeBtnText]}>Customer</Text>
                                    </TouchableOpacity>
                                    <TouchableOpacity
                                        style={[styles.roleBtn, selectedUser.role === 'staff' && styles.activeBtn]}
                                        onPress={() => handleUpdateUser('staff', selectedUser.is_blocked)}
                                    >
                                        <Text style={[styles.roleBtnText, selectedUser.role === 'staff' && styles.activeBtnText]}>Staff</Text>
                                    </TouchableOpacity>
                                    <TouchableOpacity
                                        style={[styles.roleBtn, selectedUser.role === 'admin' && styles.activeBtn]}
                                        onPress={() => handleUpdateUser('admin', selectedUser.is_blocked)}
                                    >
                                        <Text style={[styles.roleBtnText, selectedUser.role === 'admin' && styles.activeBtnText]}>Admin</Text>
                                    </TouchableOpacity>
                                </View>

                                <Text style={styles.subTitle}>Account Status</Text>
                                <TouchableOpacity
                                    style={[styles.statusBtn, selectedUser.is_blocked ? styles.unblockBtn : styles.blockBtn]}
                                    onPress={() => handleUpdateUser(selectedUser.role, selectedUser.is_blocked ? 0 : 1)}
                                >
                                    <Ionicons name={selectedUser.is_blocked ? "unlock-outline" : "lock-closed-outline"} size={20} color="#fff" />
                                    <Text style={styles.statusBtnText}>{selectedUser.is_blocked ? "Unblock Account" : "Block Account"}</Text>
                                </TouchableOpacity>
                            </ScrollView>
                        )}
                        {updating && <ActivityIndicator style={{ marginTop: 20 }} color={BLUE} />}
                    </View>
                </View>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F8F9FE',
    },
    center: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    list: {
        padding: 16,
    },
    userCard: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 16,
        marginBottom: 12,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    userHeader: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    avatar: {
        width: 45,
        height: 45,
        borderRadius: 22.5,
        backgroundColor: SECONDARY_BLUE,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 12,
    },
    avatarText: {
        fontSize: 18,
        fontWeight: 'bold',
        color: BLUE,
    },
    userInfo: {
        flex: 1,
    },
    userName: {
        fontSize: 16,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    userEmail: {
        fontSize: 12,
        color: '#707EAE',
    },
    roleBadge: {
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 8,
    },
    roleText: {
        fontSize: 10,
        fontWeight: '800',
    },
    blockedBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 10,
        backgroundColor: '#FFEEED',
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 6,
        alignSelf: 'flex-start',
    },
    blockedText: {
        fontSize: 10,
        fontWeight: 'bold',
        color: '#EE5D50',
        marginLeft: 4,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(27, 37, 89, 0.5)',
        justifyContent: 'flex-end',
    },
    modalContent: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 30,
        borderTopRightRadius: 30,
        padding: 24,
        paddingBottom: 40,
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 20,
    },
    modalTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    selectedUserInfo: {
        marginBottom: 24,
        borderBottomWidth: 1,
        borderBottomColor: '#F4F7FE',
        paddingBottom: 16,
    },
    detailName: {
        fontSize: 18,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    detailEmail: {
        fontSize: 14,
        color: '#707EAE',
        marginTop: 4,
    },
    inputGroup: {
        marginBottom: 16,
    },
    inputLabel: {
        fontSize: 12,
        fontWeight: 'bold',
        color: '#707EAE',
        marginBottom: 8,
        textTransform: 'uppercase',
    },
    textInput: {
        backgroundColor: '#F4F7FE',
        borderRadius: 12,
        padding: 12,
        fontSize: 16,
        color: DARK_NAVY,
        borderWidth: 1,
        borderColor: '#E9EDF7',
    },
    subTitle: {
        fontSize: 14,
        fontWeight: 'bold',
        color: '#707EAE',
        marginBottom: 12,
        textTransform: 'uppercase',
        letterSpacing: 1,
    },
    buttonRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 24,
    },
    roleBtn: {
        flex: 1,
        paddingVertical: 12,
        marginHorizontal: 4,
        backgroundColor: '#F4F7FE',
        borderRadius: 12,
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#E9EDF7',
    },
    activeBtn: {
        backgroundColor: BLUE,
        borderColor: BLUE,
    },
    roleBtnText: {
        fontSize: 12,
        fontWeight: 'bold',
        color: '#707EAE',
    },
    activeBtnText: {
        color: '#fff',
    },
    statusBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 16,
        borderRadius: 16,
    },
    blockBtn: {
        backgroundColor: '#EE5D50',
    },
    unblockBtn: {
        backgroundColor: '#00D563',
    },
    statusBtnText: {
        color: '#fff',
        fontWeight: 'bold',
        marginLeft: 8,
    },
});

export default UserManagementScreen;
