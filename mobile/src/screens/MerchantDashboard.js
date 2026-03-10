import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    StyleSheet,
    ScrollView,
    TouchableOpacity,
    ActivityIndicator,
    RefreshControl,
    FlatList,
    Alert
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppContext } from '../context/AppContext';
import { merchantService } from '../services/api';
import { useFocusEffect } from '@react-navigation/native';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';
const SECONDARY_BLUE = '#F4F7FE';

const MerchantDashboard = ({ navigation }) => {
    const { user } = useAppContext();
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchStats = async () => {
        try {
            const response = await merchantService.getStats(user.id);
            if (response.data?.success) {
                setStats(response.data.data);
            }
        } catch (error) {
            console.error('Error fetching merchant stats:', error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useFocusEffect(
        React.useCallback(() => {
            fetchStats();
        }, [])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchStats();
    };

    if (loading && !refreshing) {
        return (
            <View style={styles.center}>
                <ActivityIndicator size="large" color={BLUE} />
            </View>
        );
    }

    const StatCard = ({ label, value, icon, color, subtext }) => (
        <View style={[styles.statCard, { borderLeftWidth: 6, borderLeftColor: color }]}>
            <View style={styles.statTop}>
                <View style={[styles.statIconBox, { backgroundColor: color + '15' }]}>
                    <Ionicons name={icon} size={20} color={color} />
                </View>
                <Text style={styles.statValue}>{value}</Text>
            </View>
            <Text style={styles.statLabel}>{label}</Text>
            {subtext && <Text style={styles.statSubtext}>{subtext}</Text>}
        </View>
    );

    const ActionTile = ({ title, icon, color, onPress }) => (
        <TouchableOpacity style={styles.actionTile} onPress={onPress}>
            <View style={[styles.actionIconBox, { backgroundColor: color + '10' }]}>
                <Ionicons name={icon} size={24} color={color} />
            </View>
            <Text style={styles.actionTitle}>{title}</Text>
        </TouchableOpacity>
    );

    return (
        <ScrollView
            style={styles.container}
            refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[BLUE]} />}
        >
            {/* Header / Welcome */}
            <View style={styles.header}>
                <View>
                    <View style={styles.brandingRow}>
                        <View style={styles.logoIconBox}>
                            <Ionicons name="cart" size={14} color="#FFF" />
                        </View>
                        <Text style={styles.brandingText}>GRAB & GO</Text>
                    </View>
                    <Text style={styles.userName}>{user.full_name} 👋</Text>
                </View>
                <TouchableOpacity style={styles.refreshBtn} onPress={onRefresh}>
                    <Ionicons name="refresh" size={20} color={BLUE} />
                </TouchableOpacity>
            </View>

            {/* Metrics Grid */}
            <View style={styles.statsGrid}>
                <StatCard
                    label="Total Orders"
                    value={stats?.total_orders || 0}
                    icon="cart-outline"
                    color="#1877F2"
                    subtext={`${stats?.today_orders || 0} today`}
                />
                <StatCard
                    label="Pending"
                    value={stats?.pending_orders || 0}
                    icon="time-outline"
                    color="#ea580c"
                    subtext="Needs action"
                />
                <StatCard
                    label="Ready"
                    value={stats?.ready_orders || 0}
                    icon="checkmark-circle-outline"
                    color="#00D563"
                    subtext="Awaiting pickup"
                />
                <StatCard
                    label="Revenue"
                    value={`₹${stats?.today_revenue || 0}`}
                    icon="stats-chart-outline"
                    color="#00D563"
                    subtext="Today's total"
                />
            </View>



            {/* Recent Orders */}
            <View style={styles.section}>
                <View style={styles.sectionHeader}>
                    <Text style={styles.sectionTitle}>Recent Orders</Text>
                    <TouchableOpacity onPress={() => navigation.navigate('Orders')}>
                        <Text style={styles.seeAll}>See All</Text>
                    </TouchableOpacity>
                </View>

                {stats?.recent_orders?.map((order) => (
                    <TouchableOpacity
                        key={order.id}
                        style={styles.orderRow}
                        onPress={() => navigation.navigate('Orders', { status: order.status })}
                    >
                        <View style={styles.orderLeft}>
                            <View style={styles.avatar}>
                                <Text style={styles.avatarText}>
                                    {order.customer_name ? order.customer_name[0] : '#'}
                                </Text>
                            </View>
                            <View>
                                <Text style={styles.orderNum}>#{order.order_number}</Text>
                                <Text style={styles.custName}>{order.customer_name}</Text>
                            </View>
                        </View>
                        <View style={styles.orderRight}>
                            <Text style={styles.orderAmt}>₹{order.total}</Text>
                            <View style={[styles.statusBadge, { backgroundColor: getStatusColor(order.status) + '15' }]}>
                                <Text style={[styles.statusText, { color: getStatusColor(order.status) }]}>{order.status}</Text>
                            </View>
                        </View>
                    </TouchableOpacity>
                ))}
            </View>


        </ScrollView >
    );
};

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return '#00D563';
        case 'ready': return BLUE;
        case 'pending': return '#ea580c';
        case 'cancelled': return '#EE5D50';
        default: return '#A3AED0';
    }
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
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
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
        width: 24,
        height: 24,
        borderRadius: 6,
        backgroundColor: BLUE,
        justifyContent: 'center',
        alignItems: 'center',
    },
    brandingText: {
        color: BLUE,
        fontSize: 14,
        fontWeight: '900',
        letterSpacing: 1,
    },
    userName: {
        fontSize: 24,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    refreshBtn: {
        padding: 10,
        backgroundColor: '#fff',
        borderRadius: 12,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    statsGrid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        paddingHorizontal: 16,
    },
    statCard: {
        width: '45%',
        backgroundColor: '#fff',
        borderRadius: 20,
        padding: 16,
        margin: '2.5%',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    statTop: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 12,
    },
    statIconBox: {
        width: 36,
        height: 36,
        borderRadius: 10,
        justifyContent: 'center',
        alignItems: 'center',
    },
    statValue: {
        fontSize: 18,
        fontWeight: '800',
        color: DARK_NAVY,
    },
    statLabel: {
        fontSize: 12,
        fontWeight: '700',
        color: '#A3AED0',
        textTransform: 'uppercase',
        letterSpacing: 0.5,
    },
    statSubtext: {
        fontSize: 10,
        color: '#00D563',
        fontWeight: 'bold',
        marginTop: 4,
    },
    section: {
        padding: 24,
    },
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 16,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: DARK_NAVY,
        marginBottom: 16,
    },
    seeAll: {
        color: BLUE,
        fontWeight: '700',
    },
    actionsRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
    },
    actionTile: {
        width: '23%',
        alignItems: 'center',
    },
    actionIconBox: {
        width: 50,
        height: 50,
        borderRadius: 15,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 8,
    },
    actionTitle: {
        fontSize: 11,
        fontWeight: '700',
        color: '#707EAE',
    },
    orderRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        backgroundColor: '#fff',
        padding: 16,
        borderRadius: 16,
        marginBottom: 12,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    orderLeft: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    avatar: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: SECONDARY_BLUE,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 12,
    },
    avatarText: {
        color: BLUE,
        fontWeight: 'bold',
        fontSize: 16,
    },
    orderNum: {
        fontSize: 14,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    custName: {
        fontSize: 12,
        color: '#707EAE',
    },
    orderRight: {
        alignItems: 'flex-end',
    },
    orderAmt: {
        fontSize: 14,
        fontWeight: 'bold',
        color: DARK_NAVY,
        marginBottom: 4,
    },
    statusBadge: {
        paddingHorizontal: 8,
        paddingVertical: 2,
        borderRadius: 6,
    },
    statusText: {
        fontSize: 9,
        fontWeight: '800',
        textTransform: 'uppercase',
    },
});

export default MerchantDashboard;
