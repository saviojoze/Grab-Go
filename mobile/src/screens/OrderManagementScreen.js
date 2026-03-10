import React, { useState, useEffect, useRef } from 'react';
import {
    View,
    Text,
    StyleSheet,
    FlatList,
    TouchableOpacity,
    ActivityIndicator,
    Alert,
    RefreshControl,
    Modal,
    TextInput,
    Platform,
    KeyboardAvoidingView
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppContext } from '../context/AppContext';
import { orderService } from '../services/api';
import { useFocusEffect } from '@react-navigation/native';
import QRScannerModal from '../components/QRScannerModal';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';

const OrderManagementScreen = ({ navigation, route }) => {
    const { user } = useAppContext();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [activeFilter, setActiveFilter] = useState('all');

    // OTP Modal state
    const [otpModalVisible, setOtpModalVisible] = useState(false);
    const [otpValue, setOtpValue] = useState('');
    const [pendingOrderId, setPendingOrderId] = useState(null);
    const [verifying, setVerifying] = useState(false);
    const otpInputRef = useRef(null);
    const [qrScannerVisible, setQrScannerVisible] = useState(false);

    const fetchOrders = React.useCallback(async (isRefreshing = false, status = activeFilter) => {
        if (!isRefreshing && !refreshing) setLoading(true);
        else if (isRefreshing) setRefreshing(true);

        try {
            const response = await orderService.getAllOrders(user.id, status === 'all' ? null : status);
            setOrders(response.data?.data || []);
        } catch (error) {
            console.error('Error fetching orders:', error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [user.id, activeFilter, refreshing]);

    useEffect(() => {
        if (route.params?.status) {
            setActiveFilter(route.params.status);
            navigation.setParams({ status: undefined });
        }
    }, [route.params?.status]);

    useFocusEffect(
        React.useCallback(() => {
            fetchOrders();
        }, [fetchOrders])
    );

    const handleUpdateStatus = async (orderId, status) => {
        try {
            const result = await orderService.updateStatus(user.id, orderId, status);
            if (result.data?.success) {
                Alert.alert('Success', `Order marked as ${status}!`);
                fetchOrders();
            }
        } catch (err) {
            Alert.alert('Error', 'Failed to update status');
        }
    };

    const handleCompleteOrder = (orderId) => {
        setPendingOrderId(orderId);
        setOtpValue('');
        setOtpModalVisible(true);
        setTimeout(() => otpInputRef.current?.focus(), 300);
    };

    const submitOtp = async () => {
        if (!otpValue || otpValue.length !== 6) {
            Alert.alert('Error', 'Please enter the 6-digit OTP');
            return;
        }
        setVerifying(true);
        try {
            const result = await orderService.updateStatus(user.id, pendingOrderId, 'completed', otpValue);
            if (result.data?.success) {
                setOtpModalVisible(false);
                Alert.alert('✅ Success', 'Collection Verified & Order Completed!');
                fetchOrders();
            } else {
                Alert.alert('❌ Invalid OTP', result.data?.message || 'The OTP you entered is incorrect. Please try again.');
            }
        } catch (err) {
            Alert.alert('Error', 'Verification failed. Please check your connection.');
        } finally {
            setVerifying(false);
        }
    };

    const renderOrder = ({ item }) => (
        <View style={styles.orderCard}>
            <View style={styles.cardHeader}>
                <View>
                    <Text style={styles.orderNum}>#{item.order_number}</Text>
                    <Text style={styles.custName}>{item.customer_name || 'Customer'}</Text>
                </View>
                <View style={[styles.statusBadge, { backgroundColor: BLUE + '15' }]}>
                    <Text style={[styles.statusText, { color: BLUE }]}>{item.status.toUpperCase()}</Text>
                </View>
            </View>

            <View style={styles.orderInfo}>
                <View style={styles.infoRow}>
                    <Ionicons name="time-outline" size={14} color="#A3AED0" />
                    <Text style={styles.infoText}>{item.pickup_time}</Text>
                </View>
                <View style={styles.infoRow}>
                    <Ionicons name="calendar-outline" size={14} color="#A3AED0" />
                    <Text style={styles.infoText}>{item.pickup_date}</Text>
                </View>
            </View>

            {/* Order Items List */}
            <View style={styles.itemsList}>
                {(item.items || []).map((prod, idx) => (
                    <View key={idx} style={styles.itemRow}>
                        <Text style={styles.itemQty}>{prod.quantity}x</Text>
                        <Text style={styles.itemName} numberOfLines={1}>{prod.product_name}</Text>
                    </View>
                ))}
            </View>

            {item.status === 'pending' && (
                <TouchableOpacity
                    style={[styles.actionBtn, styles.readyBtn]}
                    onPress={() => handleUpdateStatus(item.id, 'ready')}
                >
                    <Ionicons name="restaurant-outline" size={18} color="#fff" />
                    <Text style={styles.actionBtnText}>Mark as Ready</Text>
                </TouchableOpacity>
            )}

            {item.status === 'ready' && (
                <TouchableOpacity
                    style={[styles.actionBtn, styles.verifyBtn]}
                    onPress={() => handleCompleteOrder(item.id)}
                >
                    <Ionicons name="shield-checkmark" size={18} color="#fff" />
                    <Text style={styles.actionBtnText}>Verify OTP & Complete</Text>
                </TouchableOpacity>
            )}

            {item.status === 'completed' && (
                <View style={[styles.completedBadge]}>
                    <Ionicons name="checkmark-circle" size={16} color="#00D563" />
                    <Text style={styles.completedText}>Completed</Text>
                </View>
            )}
        </View>
    );

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <View style={styles.brandingRow}>
                    <View style={styles.logoIconBox}>
                        <Ionicons name="cart" size={12} color="#FFF" />
                    </View>
                    <Text style={styles.brandingText}>GRAB & GO</Text>
                </View>
                <Text style={styles.title}>Order Collection</Text>
                <Text style={styles.sub}>Process and manage customer pickups</Text>

                <View style={styles.filterRow}>
                    {['all', 'pending', 'ready', 'completed'].map((f) => (
                        <TouchableOpacity
                            key={f}
                            style={[styles.filterTab, activeFilter === f && styles.filterTabActive]}
                            onPress={() => setActiveFilter(f)}
                        >
                            <Text style={[styles.filterTabText, activeFilter === f && styles.filterTabTextActive]}>
                                {f.charAt(0).toUpperCase() + f.slice(1)}
                            </Text>
                        </TouchableOpacity>
                    ))}
                </View>
            </View>

            {loading && !refreshing ? (
                <View style={styles.center}>
                    <ActivityIndicator size="large" color={BLUE} />
                </View>
            ) : (
                <FlatList
                    data={orders}
                    renderItem={renderOrder}
                    keyExtractor={(item) => item.id.toString()}
                    contentContainerStyle={styles.list}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => fetchOrders(true)} colors={[BLUE]} />}
                    ListEmptyComponent={
                        <View style={styles.empty}>
                            <Ionicons name="cart-outline" size={60} color="#A3AED0" />
                            <Text style={styles.emptyText}>
                                {activeFilter === 'all' ? 'No orders found.' : `No ${activeFilter} orders.`}
                            </Text>
                        </View>
                    }
                />
            )}

            {/* OTP Verification Modal */}
            <Modal
                visible={otpModalVisible}
                transparent
                animationType="slide"
                onRequestClose={() => setOtpModalVisible(false)}
            >
                <KeyboardAvoidingView
                    style={styles.otpOverlay}
                    behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                >
                    <View style={styles.otpModal}>
                        <View style={styles.otpHeader}>
                            <View style={styles.otpIconBox}>
                                <Ionicons name="shield-checkmark" size={28} color="#00D563" />
                            </View>
                            <Text style={styles.otpTitle}>Verify Collection</Text>
                            <Text style={styles.otpSubtitle}>Scan QR code or enter the 6-digit OTP from customer's app</Text>
                        </View>

                        {/* QR Scan Button */}
                        <TouchableOpacity
                            style={styles.qrScanBtn}
                            onPress={() => {
                                setOtpModalVisible(false);
                                setQrScannerVisible(true);
                            }}
                        >
                            <Ionicons name="qr-code-outline" size={22} color={BLUE} />
                            <Text style={styles.qrScanBtnText}>Scan QR Code Instead</Text>
                            <Ionicons name="chevron-forward" size={18} color={BLUE} />
                        </TouchableOpacity>

                        <TextInput
                            ref={otpInputRef}
                            style={styles.otpInput}
                            value={otpValue}
                            onChangeText={(t) => setOtpValue(t.replace(/[^0-9]/g, '').slice(0, 6))}
                            keyboardType="number-pad"
                            maxLength={6}
                            placeholder="000000"
                            placeholderTextColor="#CBD5E0"
                        />

                        <View style={styles.otpBtnRow}>
                            <TouchableOpacity
                                style={styles.otpCancelBtn}
                                onPress={() => setOtpModalVisible(false)}
                            >
                                <Text style={styles.otpCancelText}>Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[styles.otpConfirmBtn, verifying && { opacity: 0.7 }]}
                                onPress={submitOtp}
                                disabled={verifying}
                            >
                                {verifying ? (
                                    <ActivityIndicator color="#fff" size="small" />
                                ) : (
                                    <Text style={styles.otpConfirmText}>Verify & Complete</Text>
                                )}
                            </TouchableOpacity>
                        </View>
                    </View>
                </KeyboardAvoidingView>
            </Modal>

            {/* QR Scanner */}
            <QRScannerModal
                visible={qrScannerVisible}
                onClose={() => {
                    setQrScannerVisible(false);
                    setOtpModalVisible(true);
                }}
                onScanned={async (otp) => {
                    setQrScannerVisible(false);
                    setVerifying(true);
                    try {
                        const result = await orderService.updateStatus(user.id, pendingOrderId, 'completed', otp);
                        if (result.data?.success) {
                            Alert.alert('✅ Verified!', 'Order completed successfully!');
                            fetchOrders();
                        } else {
                            Alert.alert('❌ Invalid QR', result.data?.message || 'OTP mismatch. Try scanning again.');
                            setOtpModalVisible(true);
                        }
                    } catch (e) {
                        Alert.alert('Error', 'Verification failed.');
                        setOtpModalVisible(true);
                    } finally {
                        setVerifying(false);
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
        padding: 24,
        paddingTop: 40,
        backgroundColor: '#fff',
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
        marginBottom: 10,
    },
    brandingRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 8,
        gap: 8,
    },
    logoIconBox: {
        width: 20,
        height: 20,
        borderRadius: 6,
        backgroundColor: BLUE,
        justifyContent: 'center',
        alignItems: 'center',
    },
    brandingText: {
        color: BLUE,
        fontSize: 12,
        fontWeight: '900',
        letterSpacing: 1,
    },
    title: {
        fontSize: 22,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    sub: {
        fontSize: 14,
        color: '#707EAE',
        marginTop: 4,
    },
    filterRow: {
        flexDirection: 'row',
        marginTop: 20,
        gap: 10,
    },
    filterTab: {
        paddingHorizontal: 15,
        paddingVertical: 8,
        borderRadius: 20,
        backgroundColor: '#F4F7FE',
        borderWidth: 1,
        borderColor: '#E9EDF7',
    },
    filterTabActive: {
        backgroundColor: BLUE,
        borderColor: BLUE,
    },
    filterTabText: {
        fontSize: 12,
        fontWeight: '700',
        color: '#707EAE',
    },
    filterTabTextActive: {
        color: '#fff',
    },
    list: {
        paddingHorizontal: 20,
    },
    orderCard: {
        backgroundColor: '#fff',
        borderRadius: 20,
        padding: 20,
        marginBottom: 16,
        shadowColor: '#1B2559',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: 15,
    },
    orderNum: {
        fontSize: 16,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    custName: {
        fontSize: 13,
        color: '#707EAE',
        marginTop: 2,
    },
    statusBadge: {
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 8,
    },
    statusText: {
        fontSize: 10,
        fontWeight: '800',
    },
    orderInfo: {
        flexDirection: 'row',
        marginBottom: 20,
        gap: 15,
    },
    infoRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
    },
    infoText: {
        fontSize: 13,
        color: '#475467',
    },
    actionBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 14,
        borderRadius: 14,
        gap: 8,
    },
    readyBtn: {
        backgroundColor: BLUE,
    },
    verifyBtn: {
        backgroundColor: '#00D563',
    },
    actionBtnText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 15,
    },
    completedBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 12,
        backgroundColor: '#F0FDF4',
        borderRadius: 12,
        gap: 6,
    },
    completedText: {
        color: '#00D563',
        fontWeight: 'bold',
        fontSize: 14,
    },
    center: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    empty: {
        alignItems: 'center',
        marginTop: 100,
    },
    emptyText: {
        marginTop: 15,
        color: '#A3AED0',
        fontSize: 16,
    },
    itemsList: {
        padding: 12,
        backgroundColor: '#F4F7FE',
        borderRadius: 12,
        marginBottom: 15,
    },
    itemRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 4,
    },
    itemQty: {
        fontSize: 13,
        fontWeight: '700',
        color: BLUE,
        width: 30,
    },
    itemName: {
        fontSize: 13,
        color: DARK_NAVY,
        flex: 1,
    },
    // OTP Modal Styles
    otpOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.6)',
        justifyContent: 'flex-end',
    },
    otpModal: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 30,
        borderTopRightRadius: 30,
        padding: 30,
        paddingBottom: 40,
    },
    otpHeader: {
        alignItems: 'center',
        marginBottom: 24,
    },
    otpIconBox: {
        width: 64,
        height: 64,
        borderRadius: 20,
        backgroundColor: '#F0FDF4',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 16,
    },
    otpTitle: {
        fontSize: 22,
        fontWeight: 'bold',
        color: DARK_NAVY,
        marginBottom: 8,
    },
    otpSubtitle: {
        fontSize: 14,
        color: '#707EAE',
        textAlign: 'center',
        lineHeight: 20,
    },
    otpInput: {
        borderWidth: 2,
        borderColor: '#E2E8F0',
        borderRadius: 16,
        padding: 18,
        fontSize: 28,
        fontWeight: 'bold',
        textAlign: 'center',
        letterSpacing: 10,
        color: DARK_NAVY,
        backgroundColor: '#F4F7FE',
        marginBottom: 24,
    },
    otpBtnRow: {
        flexDirection: 'row',
        gap: 12,
    },
    otpCancelBtn: {
        flex: 1,
        padding: 16,
        borderRadius: 14,
        backgroundColor: '#F4F7FE',
        alignItems: 'center',
    },
    otpCancelText: {
        fontWeight: '700',
        color: '#707EAE',
        fontSize: 16,
    },
    otpConfirmBtn: {
        flex: 2,
        padding: 16,
        borderRadius: 14,
        backgroundColor: '#00D563',
        alignItems: 'center',
        shadowColor: '#00D563',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 4,
    },
    otpConfirmText: {
        fontWeight: 'bold',
        color: '#fff',
        fontSize: 16,
    },
    qrScanBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        backgroundColor: '#EEF2FF',
        borderRadius: 14,
        padding: 16,
        marginBottom: 16,
        borderWidth: 1,
        borderColor: '#C7D2FE',
    },
    qrScanBtnText: {
        flex: 1,
        marginLeft: 10,
        fontWeight: '700',
        color: BLUE,
        fontSize: 15,
    },
});

export default OrderManagementScreen;
