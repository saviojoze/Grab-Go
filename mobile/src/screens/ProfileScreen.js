import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ScrollView, ActivityIndicator } from 'react-native';
import { useAppContext } from '../context/AppContext';
import { orderService } from '../services/api';
import { Ionicons } from '@expo/vector-icons';

const ProfileScreen = () => {
    const { user } = useAppContext();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchOrders();
    }, []);

    const fetchOrders = async () => {
        try {
            const response = await orderService.getOrders(user.id);
            setOrders(response.data.data);
        } catch (error) {
            console.error('Error fetching orders:', error);
        } finally {
            setLoading(false);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'completed': return '#00D563';
            case 'ready': return '#1877F2';
            case 'pending': return '#FFB300';
            case 'cancelled': return '#FF5252';
            default: return '#999';
        }
    };

    const renderOrder = ({ item }) => (
        <View style={styles.orderCard}>
            <View style={styles.orderHeader}>
                <Text style={styles.orderNumber}>{item.order_number}</Text>
                <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '15' }]}>
                    <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
                        {item.status.toUpperCase()}
                    </Text>
                </View>
            </View>

            <View style={styles.orderDetail}>
                <Ionicons name="calendar-outline" size={16} color="#666" />
                <Text style={styles.detailText}>Pickup: {item.pickup_date} at {item.pickup_time}</Text>
            </View>

            <View style={styles.orderFooter}>
                <Text style={styles.itemCount}>{(item.items || []).length} items</Text>
                <Text style={styles.totalPrice}>₹{item.total}</Text>
            </View>
        </View>
    );

    return (
        <View style={styles.container}>
            {/* User Info */}
            <View style={styles.header}>
                <View style={styles.avatar}>
                    <Text style={styles.avatarText}>{user.full_name[0]}</Text>
                </View>
                <Text style={styles.userName}>{user.full_name}</Text>
                <Text style={styles.userEmail}>{user.email}</Text>
            </View>

            {/* Orders Section */}
            <View style={styles.section}>
                <Text style={styles.sectionTitle}>Order History</Text>
                {loading ? (
                    <ActivityIndicator size="small" color="#1877F2" style={{ marginTop: 20 }} />
                ) : orders.length === 0 ? (
                    <Text style={styles.emptyText}>No orders yet</Text>
                ) : (
                    <FlatList
                        data={orders}
                        keyExtractor={(item) => item.id.toString()}
                        renderItem={renderOrder}
                        contentContainerStyle={styles.orderList}
                        showsVerticalScrollIndicator={false}
                    />
                )}
            </View>

            <TouchableOpacity style={styles.logoutBtn}>
                <Ionicons name="log-out-outline" size={20} color="#FF5252" />
                <Text style={styles.logoutText}>Log Out</Text>
            </TouchableOpacity>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F8F9FA',
    },
    header: {
        backgroundColor: '#fff',
        padding: 30,
        alignItems: 'center',
        borderBottomWidth: 1,
        borderBottomColor: '#eee',
    },
    avatar: {
        width: 80,
        height: 80,
        borderRadius: 40,
        backgroundColor: '#1877F2',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 15,
    },
    avatarText: {
        color: '#fff',
        fontSize: 32,
        fontWeight: 'bold',
    },
    userName: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#333',
    },
    userEmail: {
        color: '#666',
        marginTop: 4,
    },
    section: {
        flex: 1,
        padding: 20,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#333',
        marginBottom: 15,
    },
    orderList: {
        paddingBottom: 20,
    },
    orderCard: {
        backgroundColor: '#fff',
        borderRadius: 12,
        padding: 16,
        marginBottom: 12,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.05,
        shadowRadius: 2,
        elevation: 2,
    },
    orderHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 12,
    },
    orderNumber: {
        fontWeight: 'bold',
        color: '#333',
        fontSize: 16,
    },
    statusBadge: {
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 6,
    },
    statusText: {
        fontSize: 10,
        fontWeight: 'bold',
    },
    orderDetail: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 12,
    },
    detailText: {
        marginLeft: 6,
        color: '#666',
        fontSize: 14,
    },
    orderFooter: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        borderTopWidth: 1,
        borderTopColor: '#f1f3f5',
        paddingTop: 12,
    },
    itemCount: {
        color: '#999',
        fontSize: 14,
    },
    totalPrice: {
        fontWeight: 'bold',
        color: '#1877F2',
        fontSize: 16,
    },
    emptyText: {
        textAlign: 'center',
        color: '#999',
        marginTop: 40,
    },
    logoutBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 20,
        backgroundColor: '#fff',
        borderTopWidth: 1,
        borderTopColor: '#eee',
    },
    logoutText: {
        color: '#FF5252',
        fontWeight: 'bold',
        marginLeft: 8,
        fontSize: 16,
    },
});

export default ProfileScreen;
