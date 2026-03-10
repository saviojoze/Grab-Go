import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ScrollView, ActivityIndicator, Image, Modal, Alert, TextInput } from 'react-native';
import { useAppContext } from '../context/AppContext';
import { orderService, authService } from '../services/api';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import QRScannerModal from '../components/QRScannerModal';

const BLUE = '#1877F2';
const GREEN = '#00D563';
const ORANGE = '#ea580c';
const DARK_NAVY = '#1B2559';
const SECONDARY_BLUE = '#F4F7FE';

const ProfileScreen = ({ navigation }) => {
    const { user, logout, setUser } = useAppContext();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [qrModalVisible, setQrModalVisible] = useState(false);
    const [selectedQrUrl, setSelectedQrUrl] = useState('');
    const [editModalVisible, setEditModalVisible] = useState(false);
    const [editName, setEditName] = useState('');
    const [editPhone, setEditPhone] = useState('');
    const [updating, setUpdating] = useState(false);
    // OTP state for ProfileScreen
    const [profileOtpVisible, setProfileOtpVisible] = useState(false);
    const [profileOtpValue, setProfileOtpValue] = useState('');
    const [profilePendingOrderId, setProfilePendingOrderId] = useState(null);
    const [profileVerifying, setProfileVerifying] = useState(false);
    const [profileQrVisible, setProfileQrVisible] = useState(false);

    useEffect(() => {
        if (user) {
            setEditName(user.full_name || '');
            setEditPhone(user.phone || '');
        }
    }, [user]);

    const handleUpdateProfile = async () => {
        if (!editName.trim()) {
            Alert.alert('Error', 'Full name is required');
            return;
        }

        setUpdating(true);
        try {
            const response = await authService.updateProfile({
                user_id: user.id,
                full_name: editName,
                phone: editPhone
            });

            if (response.data?.success) {
                setUser(response.data.data);
                setEditModalVisible(false);
                Alert.alert('Success', 'Profile updated successfully!');
            } else {
                Alert.alert('Error', response.data?.message || 'Update failed');
            }
        } catch (err) {
            console.error('Update error:', err);
            Alert.alert('Error', 'Failed to update profile. Check connection.');
        } finally {
            setUpdating(false);
        }
    };

    useFocusEffect(
        React.useCallback(() => {
            if (user?.id) {
                fetchOrders();
            }
        }, [user?.id])
    );

    const fetchOrders = async (isRefreshing = false) => {
        if (!isRefreshing) setLoading(true);
        else setRefreshing(true);

        try {
            let response;
            if (user.role === 'staff' || user.role === 'admin') {
                // If staff, fetch all recent orders
                response = await orderService.getAllOrders(user.id);
            } else {
                response = await orderService.getOrders(user.id);
            }
            setOrders(response.data?.data || []);
        } catch (error) {
            console.error('Error fetching orders:', error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const handleRefresh = () => {
        fetchOrders(true);
    };

    const handleCompleteOrder = (orderId) => {
        setProfilePendingOrderId(orderId);
        setProfileOtpValue('');
        setProfileOtpVisible(true);
    };

    const submitProfileOtp = async () => {
        if (!profileOtpValue || profileOtpValue.length !== 6) {
            Alert.alert('Error', 'Please enter the 6-digit OTP');
            return;
        }
        setProfileVerifying(true);
        try {
            const result = await orderService.updateStatus(user.id, profilePendingOrderId, 'completed', profileOtpValue);
            if (result.data?.success) {
                setProfileOtpVisible(false);
                Alert.alert('✅ Success', 'Order completed successfully!');
                fetchOrders();
            } else {
                Alert.alert('❌ Invalid OTP', result.data?.message || 'Invalid OTP. Please try again.');
            }
        } catch (err) {
            Alert.alert('Error', 'Failed to update order');
        } finally {
            setProfileVerifying(false);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed': return GREEN;
            case 'ready': return BLUE;
            case 'pending': return ORANGE;
            case 'cancelled': return '#EE5D50';
            default: return '#A3AED0';
        }
    };

    const renderOrder = ({ item }) => {
        const isStaff = user.role === 'staff' || user.role === 'admin';

        if (isStaff) {
            return (
                <View style={styles.orderCard}>
                    <View style={styles.orderHeader}>
                        <View>
                            <Text style={styles.orderNumber}>#{item.order_number}</Text>
                            <Text style={styles.customerName}>{item.customer_name || 'Customer'}</Text>
                        </View>
                        <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '15' }]}>
                            <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
                                {(item.status || 'pending').toUpperCase()}
                            </Text>
                        </View>
                    </View>

                    <View style={styles.orderDetail}>
                        <Ionicons name="time-outline" size={16} color="#A3AED0" />
                        <Text style={styles.detailText}>Pickup: {item.pickup_time}</Text>
                        <Ionicons name="calendar-outline" size={16} color="#A3AED0" style={{ marginLeft: 15 }} />
                        <Text style={styles.detailText}>{item.pickup_date}</Text>
                    </View>

                    {item.status === 'pending' ? (
                        <View style={[styles.pendingInfo]}>
                            <Ionicons name="hourglass-outline" size={16} color="#F57F17" />
                            <Text style={styles.pendingText}>New Order - Go to Management to process</Text>
                        </View>
                    ) : item.status === 'ready' ? (
                        <TouchableOpacity
                            style={styles.completeBtn}
                            onPress={() => handleCompleteOrder(item.id)}
                        >
                            <Ionicons name="shield-checkmark" size={20} color="#fff" />
                            <Text style={styles.completeBtnText}>Complete Collection</Text>
                        </TouchableOpacity>
                    ) : (
                        <View style={[styles.completedSmall]}>
                            <Ionicons name="checkmark-circle" size={16} color={GREEN} />
                            <Text style={styles.completedTextSmall}>Collected</Text>
                        </View>
                    )}
                </View>
            );
        }

        const qrData = JSON.stringify({
            order_id: item.order_number,
            otp: item.delivery_otp,
            user: user.full_name
        });
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrData)}`;

        return (
            <View style={styles.orderCard}>
                <View style={styles.orderHeader}>
                    <Text style={styles.orderNumber}>{item.order_number}</Text>
                    <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '15' }]}>
                        <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
                            {(item.status || 'pending').toUpperCase()}
                        </Text>
                    </View>
                </View>

                <View style={styles.orderDetail}>
                    <Ionicons name="calendar-outline" size={16} color="#A3AED0" />
                    <Text style={styles.detailText}>Pickup: {item.pickup_date} at {item.pickup_time}</Text>
                </View>

                {item.delivery_otp && (
                    <View style={styles.otpSection}>
                        <View style={styles.otpBox}>
                            <Text style={styles.otpLabel}>Delivery OTP</Text>
                            <Text style={styles.otpValue}>{item.delivery_otp}</Text>
                        </View>
                        <TouchableOpacity
                            style={styles.qrIconBtn}
                            onPress={() => {
                                setSelectedQrUrl(qrUrl);
                                setQrModalVisible(true);
                            }}
                        >
                            <Ionicons name="qr-code-outline" size={24} color={BLUE} />
                        </TouchableOpacity>
                    </View>
                )}

                <View style={styles.orderFooter}>
                    <Text style={styles.itemCount}>{(item.items || []).length} items</Text>
                    <Text style={styles.totalPrice}>₹{item.total}</Text>
                </View>
            </View>
        );
    };

    if (!user) {
        return (
            <View style={styles.emptyContainer}>
                {/* Branding Hero */}
                <View style={styles.profileAuthHero}>
                    <View style={styles.logoIconBox}>
                        <Ionicons name="cart" size={32} color={GREEN} />
                    </View>
                    <Text style={styles.brandingTextSmall}>GRAB & GO</Text>
                </View>

                <View style={styles.emptyContent}>
                    <Ionicons name="person-circle-outline" size={80} color="#A3AED0" />
                    <Text style={styles.emptyTitle}>Your Account</Text>
                    <Text style={styles.emptyText}>Sign in to view your order history, manage your profile, and more.</Text>
                    <TouchableOpacity
                        style={styles.loginScreenBtn}
                        onPress={() => navigation.navigate('Login')}
                    >
                        <Text style={styles.loginScreenBtnText}>Log In to Your Account</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    }

    return (
        <View style={styles.container}>
            {/* User Info */}
            <View style={styles.header}>
                <View style={styles.avatar}>
                    <Text style={styles.avatarText}>{user.full_name ? user.full_name[0] : 'U'}</Text>
                </View>
                <View style={{ alignItems: 'center' }}>
                    <Text style={styles.userName}>{user.full_name}</Text>
                    <View style={[styles.roleBadge, user.role === 'staff' && { backgroundColor: SECONDARY_BLUE }, user.role === 'admin' && { backgroundColor: '#FFF9C4' }]}>
                        <Text style={[styles.roleText, user.role === 'staff' && { color: BLUE }, user.role === 'admin' && { color: '#F57F17' }]}>
                            {user.role ? user.role.toUpperCase() : 'CUSTOMER'}
                        </Text>
                    </View>
                </View>
                <Text style={styles.userEmail}>{user.email}</Text>
                {user.phone && <Text style={styles.userPhone}>{user.phone}</Text>}

                <TouchableOpacity
                    style={styles.editProfileBtn}
                    onPress={() => setEditModalVisible(true)}
                >
                    <Ionicons name="create-outline" size={16} color={BLUE} />
                    <Text style={styles.editProfileBtnText}>Edit Profile</Text>
                </TouchableOpacity>
            </View>

            {/* Orders Section */}
            <View style={styles.section}>
                <Text style={styles.sectionTitle}>
                    {(user.role === 'staff' || user.role === 'admin') ? 'Incoming Pickups' : 'Order History'}
                </Text>
                {loading ? (
                    <ActivityIndicator size="small" color={BLUE} style={{ marginTop: 20 }} />
                ) : orders.filter(o => (user.role === 'staff' || user.role === 'admin') ? (o.status === 'pending' || o.status === 'ready') : true).length === 0 ? (
                    <Text style={styles.emptyText}>
                        {(user.role === 'staff' || user.role === 'admin') ? 'No pending orders' : 'No orders yet'}
                    </Text>
                ) : (
                    <FlatList
                        data={orders.filter(o => (user.role === 'staff' || user.role === 'admin') ? (o.status === 'pending' || o.status === 'ready') : true)}
                        keyExtractor={(item) => item.id.toString()}
                        renderItem={renderOrder}
                        contentContainerStyle={styles.orderList}
                        showsVerticalScrollIndicator={false}
                        refreshing={refreshing}
                        onRefresh={handleRefresh}
                    />
                )}
            </View>


            <TouchableOpacity style={styles.logoutBtn} onPress={logout}>
                <Ionicons name="log-out-outline" size={20} color="#EE5D50" />
                <Text style={styles.logoutText}>Log Out</Text>
            </TouchableOpacity>

            {/* QR Modal */}
            <Modal
                animationType="fade"
                transparent={true}
                visible={qrModalVisible}
                onRequestClose={() => setQrModalVisible(false)}
            >
                <View style={styles.qrModalOverlay}>
                    <View style={styles.qrModalContent}>
                        <View style={styles.qrModalHeader}>
                            <Text style={styles.qrModalTitle}>Order QR Code</Text>
                            <TouchableOpacity onPress={() => setQrModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#A3AED0" />
                            </TouchableOpacity>
                        </View>
                        <View style={styles.qrContainer}>
                            <Image
                                source={{ uri: selectedQrUrl }}
                                style={styles.qrImage}
                                resizeMode="contain"
                            />
                        </View>
                        <Text style={styles.qrInstruction}>Show this code to the supermarket staff at pickup.</Text>
                        <TouchableOpacity
                            style={styles.closeBtn}
                            onPress={() => setQrModalVisible(false)}
                        >
                            <Text style={styles.closeBtnText}>Done</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>

            {/* Edit Profile Modal */}
            <Modal
                animationType="slide"
                transparent={true}
                visible={editModalVisible}
                onRequestClose={() => setEditModalVisible(false)}
            >
                <View style={styles.editModalOverlay}>
                    <View style={styles.editModalContent}>
                        <View style={styles.editModalHeader}>
                            <Text style={styles.editModalTitle}>Edit Profile</Text>
                            <TouchableOpacity onPress={() => setEditModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#A3AED0" />
                            </TouchableOpacity>
                        </View>

                        <View style={styles.inputGroup}>
                            <Text style={styles.inputLabel}>Full Name</Text>
                            <TextInput
                                style={styles.textInput}
                                value={editName}
                                onChangeText={setEditName}
                                placeholder="Enter your full name"
                            />
                        </View>

                        <View style={styles.inputGroup}>
                            <Text style={styles.inputLabel}>Phone Number</Text>
                            <TextInput
                                style={styles.textInput}
                                value={editPhone}
                                onChangeText={setEditPhone}
                                placeholder="Enter your phone number"
                                keyboardType="phone-pad"
                            />
                        </View>

                        <TouchableOpacity
                            style={[styles.saveBtn, updating && { opacity: 0.7 }]}
                            onPress={handleUpdateProfile}
                            disabled={updating}
                        >
                            {updating ? (
                                <ActivityIndicator color="#fff" size="small" />
                            ) : (
                                <Text style={styles.saveBtnText}>Save Changes</Text>
                            )}
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>

            {/* OTP Verification Modal for Profile */}
            <Modal
                visible={profileOtpVisible}
                transparent
                animationType="slide"
                onRequestClose={() => setProfileOtpVisible(false)}
            >
                <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'flex-end' }}>
                    <View style={{ backgroundColor: '#fff', borderTopLeftRadius: 30, borderTopRightRadius: 30, padding: 30, paddingBottom: 40 }}>
                        <View style={{ alignItems: 'center', marginBottom: 24 }}>
                            <View style={{ width: 64, height: 64, borderRadius: 20, backgroundColor: '#F0FDF4', justifyContent: 'center', alignItems: 'center', marginBottom: 16 }}>
                                <Ionicons name="shield-checkmark" size={28} color={GREEN} />
                            </View>
                            <Text style={{ fontSize: 22, fontWeight: 'bold', color: DARK_NAVY, marginBottom: 8 }}>Verify Collection</Text>
                            <Text style={{ fontSize: 14, color: '#707EAE', textAlign: 'center', lineHeight: 20 }}>Scan QR code or enter the 6-digit OTP from customer's app</Text>
                        </View>

                        {/* QR Scan Button */}
                        <TouchableOpacity
                            style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: '#EEF2FF', borderRadius: 14, padding: 16, marginBottom: 16, borderWidth: 1, borderColor: '#C7D2FE' }}
                            onPress={() => {
                                setProfileOtpVisible(false);
                                setProfileQrVisible(true);
                            }}
                        >
                            <Ionicons name="qr-code-outline" size={22} color={BLUE} />
                            <Text style={{ flex: 1, marginLeft: 10, fontWeight: '700', color: BLUE, fontSize: 15 }}>Scan QR Code Instead</Text>
                            <Ionicons name="chevron-forward" size={18} color={BLUE} />
                        </TouchableOpacity>

                        <TextInput
                            style={{ borderWidth: 2, borderColor: '#E2E8F0', borderRadius: 16, padding: 18, fontSize: 28, fontWeight: 'bold', textAlign: 'center', letterSpacing: 10, color: DARK_NAVY, backgroundColor: '#F4F7FE', marginBottom: 24 }}
                            value={profileOtpValue}
                            onChangeText={(t) => setProfileOtpValue(t.replace(/[^0-9]/g, '').slice(0, 6))}
                            keyboardType="number-pad"
                            maxLength={6}
                            placeholder="000000"
                            placeholderTextColor="#CBD5E0"
                            autoFocus
                        />

                        <View style={{ flexDirection: 'row', gap: 12 }}>
                            <TouchableOpacity
                                style={{ flex: 1, padding: 16, borderRadius: 14, backgroundColor: '#F4F7FE', alignItems: 'center' }}
                                onPress={() => setProfileOtpVisible(false)}
                            >
                                <Text style={{ fontWeight: '700', color: '#707EAE', fontSize: 16 }}>Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[{ flex: 2, padding: 16, borderRadius: 14, backgroundColor: GREEN, alignItems: 'center', elevation: 4 }, profileVerifying && { opacity: 0.7 }]}
                                onPress={submitProfileOtp}
                                disabled={profileVerifying}
                            >
                                {profileVerifying ? (
                                    <ActivityIndicator color="#fff" size="small" />
                                ) : (
                                    <Text style={{ fontWeight: 'bold', color: '#fff', fontSize: 16 }}>Verify & Complete</Text>
                                )}
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            {/* QR Scanner for Profile */}
            <QRScannerModal
                visible={profileQrVisible}
                onClose={() => {
                    setProfileQrVisible(false);
                    setProfileOtpVisible(true);
                }}
                onScanned={async (otp) => {
                    setProfileQrVisible(false);
                    setProfileVerifying(true);
                    try {
                        const result = await orderService.updateStatus(user.id, profilePendingOrderId, 'completed', otp);
                        if (result.data?.success) {
                            Alert.alert('✅ Verified!', 'Order completed successfully!');
                            fetchOrders();
                        } else {
                            Alert.alert('❌ Invalid QR', result.data?.message || 'OTP mismatch. Try scanning again.');
                            setProfileOtpVisible(true);
                        }
                    } catch (e) {
                        Alert.alert('Error', 'Verification failed.');
                        setProfileOtpVisible(true);
                    } finally {
                        setProfileVerifying(false);
                    }
                }}
            />
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F8F9FE',
    },
    header: {
        backgroundColor: '#fff',
        padding: 30,
        alignItems: 'center',
        borderBottomWidth: 1,
        borderBottomColor: '#F4F7FE',
    },
    avatar: {
        width: 80,
        height: 80,
        borderRadius: 40,
        backgroundColor: BLUE,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 15,
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 10,
        elevation: 6,
    },
    avatarText: {
        color: '#fff',
        fontSize: 32,
        fontWeight: 'bold',
    },
    userName: {
        fontSize: 22,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    userEmail: {
        color: '#A3AED0',
        marginTop: 4,
        fontWeight: '500',
    },
    section: {
        flex: 1,
        padding: 20,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: DARK_NAVY,
        marginBottom: 15,
    },
    orderList: {
        paddingBottom: 20,
    },
    orderCard: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 16,
        marginBottom: 12,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 8,
        elevation: 3,
    },
    orderHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 12,
    },
    orderNumber: {
        fontWeight: 'bold',
        color: DARK_NAVY,
        fontSize: 16,
    },
    statusBadge: {
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 8,
    },
    statusText: {
        fontSize: 10,
        fontWeight: '800',
        letterSpacing: 0.5,
    },
    orderDetail: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 12,
    },
    detailText: {
        marginLeft: 6,
        color: '#707EAE',
        fontSize: 14,
        fontWeight: '500',
    },
    orderFooter: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        borderTopWidth: 1,
        borderTopColor: '#F4F7FE',
        paddingTop: 12,
    },
    itemCount: {
        color: '#A3AED0',
        fontSize: 14,
        fontWeight: '500',
    },
    totalPrice: {
        fontWeight: '800',
        color: BLUE,
        fontSize: 16,
    },
    otpSection: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        backgroundColor: SECONDARY_BLUE,
        padding: 12,
        borderRadius: 12,
        marginBottom: 12,
        borderWidth: 1,
        borderColor: '#E9EDF7',
    },
    otpBox: {
        flex: 1,
    },
    otpLabel: {
        fontSize: 10,
        color: '#707EAE',
        textTransform: 'uppercase',
        fontWeight: 'bold',
        marginBottom: 2,
    },
    otpValue: {
        fontSize: 20,
        fontWeight: '900',
        color: BLUE,
        letterSpacing: 3,
    },
    qrIconBtn: {
        padding: 8,
        backgroundColor: '#fff',
        borderRadius: 8,
    },
    emptyText: {
        textAlign: 'center',
        color: '#A3AED0',
        marginTop: 40,
        fontWeight: '500',
    },
    logoutBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 20,
        backgroundColor: '#fff',
        borderTopWidth: 1,
        borderTopColor: '#F4F7FE',
    },
    logoutText: {
        color: '#EE5D50',
        fontWeight: 'bold',
        marginLeft: 8,
        fontSize: 16,
    },
    qrModalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(27, 37, 89, 0.8)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 24,
    },
    qrModalContent: {
        backgroundColor: '#fff',
        borderRadius: 24,
        padding: 24,
        width: '100%',
        maxWidth: 400,
        alignItems: 'center',
    },
    qrModalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        width: '100%',
        marginBottom: 24,
    },
    qrModalTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    qrContainer: {
        backgroundColor: '#fff',
        padding: 16,
        borderRadius: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.1,
        shadowRadius: 20,
        elevation: 8,
        marginBottom: 24,
        borderWidth: 1,
        borderColor: '#F4F7FE',
    },
    qrImage: {
        width: 250,
        height: 250,
    },
    qrInstruction: {
        textAlign: 'center',
        color: '#707EAE',
        fontSize: 14,
        marginBottom: 30,
        lineHeight: 22,
        fontWeight: '500',
    },
    closeBtn: {
        backgroundColor: BLUE,
        paddingHorizontal: 40,
        paddingVertical: 16,
        borderRadius: 16,
        width: '100%',
        alignItems: 'center',
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 12,
        elevation: 5,
    },
    closeBtnText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16,
    },
    emptyContainer: {
        flex: 1,
        backgroundColor: '#F8F9FE',
    },
    profileAuthHero: {
        backgroundColor: BLUE,
        paddingTop: 60,
        paddingBottom: 40,
        alignItems: 'center',
        borderBottomLeftRadius: 32,
        borderBottomRightRadius: 32,
    },
    logoIconBox: {
        width: 64,
        height: 64,
        borderRadius: 18,
        backgroundColor: '#fff',
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 8,
        elevation: 4,
        marginBottom: 16,
    },
    brandingTextSmall: {
        color: '#fff',
        fontSize: 18,
        fontWeight: '900',
        letterSpacing: 2,
    },
    emptyContent: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        padding: 30,
    },
    emptyTitle: {
        fontSize: 22,
        fontWeight: 'bold',
        color: DARK_NAVY,
        marginTop: 15,
    },
    emptyText: {
        textAlign: 'center',
        color: '#A3AED0',
        marginTop: 12,
        marginBottom: 30,
        lineHeight: 22,
        fontSize: 14,
    },
    loginScreenBtn: {
        backgroundColor: BLUE,
        paddingHorizontal: 30,
        paddingVertical: 16,
        borderRadius: 16,
        width: '100%',
        alignItems: 'center',
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
        elevation: 4,
    },
    loginScreenBtnText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16,
    },
    shopNowBtn: {
        backgroundColor: BLUE,
        paddingHorizontal: 40,
        paddingVertical: 14,
        borderRadius: 14,
        marginTop: 20,
    },
    shopNowText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16,
    },
    roleBadge: {
        paddingHorizontal: 12,
        paddingVertical: 4,
        borderRadius: 20,
        marginTop: 6,
    },
    roleText: {
        fontSize: 10,
        fontWeight: '800',
        letterSpacing: 0.5,
    },
    customerName: {
        fontSize: 14,
        color: '#707EAE',
        marginTop: 2,
        fontWeight: '500',
    },
    completeBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: GREEN,
        padding: 14,
        borderRadius: 12,
        marginTop: 10,
    },
    saveBtnText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16,
    },
    pendingInfo: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 12,
        backgroundColor: '#FFF9C4',
        borderRadius: 12,
        marginTop: 10,
        gap: 8,
    },
    pendingText: {
        color: '#F57F17',
        fontSize: 12,
        fontWeight: '600',
    },
    completedSmall: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 12,
        backgroundColor: '#F0FDF4',
        borderRadius: 12,
        marginTop: 10,
        gap: 8,
    },
    completedTextSmall: {
        color: GREEN,
        fontSize: 12,
        fontWeight: '600',
    },
    userPhone: {
        color: '#707EAE',
        marginTop: 2,
        fontWeight: '500',
    },
    editProfileBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 15,
        paddingHorizontal: 16,
        paddingVertical: 8,
        backgroundColor: SECONDARY_BLUE,
        borderRadius: 12,
    },
    editProfileBtnText: {
        color: BLUE,
        fontWeight: '700',
        fontSize: 14,
        marginLeft: 6,
    },
    editModalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(27, 37, 89, 0.5)',
        justifyContent: 'flex-end',
    },
    editModalContent: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 30,
        borderTopRightRadius: 30,
        padding: 24,
        paddingBottom: 40,
    },
    editModalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 24,
    },
    editModalTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    inputGroup: {
        marginBottom: 20,
    },
    inputLabel: {
        fontSize: 14,
        fontWeight: 'bold',
        color: DARK_NAVY,
        marginBottom: 8,
        marginLeft: 4,
    },
    textInput: {
        backgroundColor: '#F4F7FE',
        borderRadius: 12,
        padding: 16,
        fontSize: 16,
        color: DARK_NAVY,
        borderWidth: 1,
        borderColor: '#E9EDF7',
    },
    saveBtn: {
        backgroundColor: BLUE,
        borderRadius: 16,
        padding: 18,
        alignItems: 'center',
        marginTop: 10,
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
        elevation: 4,
    },
});

export default ProfileScreen;
