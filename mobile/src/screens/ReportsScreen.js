import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, SafeAreaView, TouchableOpacity, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { merchantService } from '../services/api';
import { useAppContext } from '../context/AppContext';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';
const SUCCESS = '#00D563';
const WARNING = '#FFB547';
const ERROR = '#EE5D50';

const ReportsScreen = () => {
    const { user } = useAppContext();
    const [reportData, setReportData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    // Default to last 30 days
    const [timeRange, setTimeRange] = useState(30);

    const fetchReports = async () => {
        try {
            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - timeRange);

            const startStr = start.toISOString().split('T')[0];
            const endStr = end.toISOString().split('T')[0];

            const response = await merchantService.getReports(user.id, { start_date: startStr, end_date: endStr });
            if (response.data?.success) {
                setReportData(response.data.data);
            }
        } catch (error) {
            console.error('Fetch reports error:', error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        setLoading(true);
        fetchReports();
    }, [timeRange]);

    const onRefresh = () => {
        setRefreshing(true);
        fetchReports();
    };

    const formatCurr = (val) => {
        return '₹' + parseFloat(val || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 });
    };

    if (loading && !reportData) {
        return (
            <View style={styles.center}>
                <ActivityIndicator size="large" color={BLUE} />
            </View>
        );
    }

    const { overview, top_products, category_revenue, low_stock } = reportData || {};

    return (
        <SafeAreaView style={styles.container}>
            <View style={styles.header}>
                <Text style={styles.headerTitle}>Reports & Analytics</Text>
                <Text style={styles.headerSubtitle}>Performance tracking and insights</Text>
            </View>

            <View style={styles.filterBar}>
                {[7, 30, 90].map(days => (
                    <TouchableOpacity
                        key={days}
                        style={[styles.filterBtn, timeRange === days && styles.filterBtnActive]}
                        onPress={() => setTimeRange(days)}
                    >
                        <Text style={[styles.filterBtnText, timeRange === days && styles.filterBtnTextActive]}>Last {days} Days</Text>
                    </TouchableOpacity>
                ))}
            </View>

            <ScrollView
                contentContainerStyle={styles.scrollContent}
                showsVerticalScrollIndicator={false}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={BLUE} />}
            >
                {/* Overview Cards */}
                <View style={styles.statsGrid}>
                    <View style={styles.statCard}>
                        <View style={[styles.iconBox, { backgroundColor: SUCCESS + '20' }]}>
                            <Ionicons name="cash-outline" size={24} color={SUCCESS} />
                        </View>
                        <Text style={styles.statValue}>{formatCurr(overview?.total_revenue)}</Text>
                        <Text style={styles.statLabel}>Total Revenue</Text>
                    </View>
                    <View style={styles.statCard}>
                        <View style={[styles.iconBox, { backgroundColor: BLUE + '20' }]}>
                            <Ionicons name="cart-outline" size={24} color={BLUE} />
                        </View>
                        <Text style={styles.statValue}>{overview?.total_orders}</Text>
                        <Text style={styles.statLabel}>Total Orders</Text>
                    </View>
                    <View style={styles.statCard}>
                        <View style={[styles.iconBox, { backgroundColor: WARNING + '20' }]}>
                            <Ionicons name="stats-chart-outline" size={24} color={WARNING} />
                        </View>
                        <Text style={styles.statValue}>{formatCurr(overview?.avg_order_value)}</Text>
                        <Text style={styles.statLabel}>Avg Order Value</Text>
                    </View>
                    <View style={styles.statCard}>
                        <View style={[styles.iconBox, { backgroundColor: '#8E24AA' + '20' }]}>
                            <Ionicons name="checkmark-circle-outline" size={24} color="#8E24AA" />
                        </View>
                        <Text style={styles.statValue}>{formatCurr(overview?.completed_revenue)}</Text>
                        <Text style={styles.statLabel}>Completed Revenue</Text>
                    </View>
                </View>

                {/* Top Products */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Text style={styles.sectionTitle}>Top Selling Products</Text>
                    </View>
                    <View style={styles.card}>
                        {top_products?.length > 0 ? top_products.map((item, idx) => (
                            <View key={item.id} style={[styles.rowItem, idx !== top_products.length - 1 && styles.borderBottom]}>
                                <View style={styles.rowInfo}>
                                    <Text style={styles.rowName}>{item.name}</Text>
                                    <Text style={styles.rowSub}>{item.total_sold} units sold</Text>
                                </View>
                                <Text style={styles.rowValue}>{formatCurr(item.revenue)}</Text>
                            </View>
                        )) : (
                            <Text style={styles.emptyText}>No sales data available</Text>
                        )}
                    </View>
                </View>

                {/* Categories */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Text style={styles.sectionTitle}>Revenue by Category</Text>
                    </View>
                    <View style={styles.card}>
                        {category_revenue?.length > 0 ? category_revenue.map((item, idx) => (
                            <View key={item.id} style={[styles.categoryItem, idx !== category_revenue.length - 1 && styles.borderBottom]}>
                                <View style={styles.catHeader}>
                                    <Text style={styles.catName}>{item.icon} {item.name}</Text>
                                    <Text style={styles.catRevenue}>{formatCurr(item.revenue)}</Text>
                                </View>
                                <View style={styles.progressBarBg}>
                                    <View style={[styles.progressBarFill, { width: `${item.percentage}%` }]} />
                                </View>
                                <View style={styles.catFooter}>
                                    <Text style={styles.catSub}>{item.percentage ? parseFloat(item.percentage).toFixed(1) : 0}% of total</Text>
                                    <Text style={styles.catSub}>{item.order_count} orders</Text>
                                </View>
                            </View>
                        )) : (
                            <Text style={styles.emptyText}>No category data available</Text>
                        )}
                    </View>
                </View>

                {/* Low Stock */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Text style={styles.sectionTitle}>Low Stock Alert</Text>
                        <View style={styles.badgeWarning}>
                            <Text style={styles.badgeWarningText}>{low_stock?.length || 0} items</Text>
                        </View>
                    </View>
                    <View style={styles.card}>
                        {low_stock?.length > 0 ? low_stock.map((item, idx) => (
                            <View key={item.id} style={[styles.rowItem, idx !== low_stock.length - 1 && styles.borderBottom]}>
                                <View style={styles.rowInfo}>
                                    <Text style={styles.rowName}>{item.name}</Text>
                                    <Text style={styles.rowSub}>{formatCurr(item.price)}</Text>
                                </View>
                                <View style={[styles.stockBadge, item.stock < 5 ? styles.stockCritical : styles.stockLow]}>
                                    <Text style={[styles.stockBadgeText, item.stock < 5 ? styles.stockCriticalText : styles.stockLowText]}>{item.stock} left</Text>
                                </View>
                            </View>
                        )) : (
                            <Text style={styles.emptyText}>All products are well stocked! 🎉</Text>
                        )}
                    </View>
                </View>
            </ScrollView>
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8F9FE' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { padding: 20, paddingBottom: 10, backgroundColor: '#FFF' },
    headerTitle: { fontSize: 24, fontWeight: 'bold', color: DARK_NAVY },
    headerSubtitle: { fontSize: 13, color: '#A3AED0', marginTop: 4 },
    filterBar: { flexDirection: 'row', padding: 16, backgroundColor: '#FFF', borderBottomWidth: 1, borderBottomColor: '#F4F7FE' },
    filterBtn: { flex: 1, alignItems: 'center', paddingVertical: 8, marginHorizontal: 4, borderRadius: 8, backgroundColor: '#F4F7FE' },
    filterBtnActive: { backgroundColor: BLUE },
    filterBtnText: { fontSize: 13, fontWeight: '600', color: '#A3AED0' },
    filterBtnTextActive: { color: '#FFF' },
    scrollContent: { padding: 16, paddingBottom: 80 },
    statsGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', marginBottom: 20 },
    statCard: { width: '48%', backgroundColor: '#FFF', padding: 16, borderRadius: 16, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 4 },
    iconBox: { width: 44, height: 44, borderRadius: 12, justifyContent: 'center', alignItems: 'center', marginBottom: 12 },
    statValue: { fontSize: 18, fontWeight: 'bold', color: DARK_NAVY, marginBottom: 4 },
    statLabel: { fontSize: 12, color: '#A3AED0', fontWeight: '500' },
    section: { marginBottom: 24 },
    sectionHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 },
    sectionTitle: { fontSize: 18, fontWeight: 'bold', color: DARK_NAVY },
    badgeWarning: { backgroundColor: WARNING + '20', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    badgeWarningText: { color: WARNING, fontSize: 12, fontWeight: 'bold' },
    card: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 4 },
    rowItem: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 12 },
    borderBottom: { borderBottomWidth: 1, borderBottomColor: '#F4F7FE' },
    rowInfo: { flex: 1 },
    rowName: { fontSize: 15, fontWeight: 'bold', color: DARK_NAVY, marginBottom: 4 },
    rowSub: { fontSize: 13, color: '#A3AED0' },
    rowValue: { fontSize: 16, fontWeight: 'bold', color: SUCCESS },
    categoryItem: { paddingVertical: 12 },
    catHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    catName: { fontSize: 15, fontWeight: 'bold', color: DARK_NAVY },
    catRevenue: { fontSize: 15, fontWeight: 'bold', color: BLUE },
    progressBarBg: { height: 8, backgroundColor: '#F4F7FE', borderRadius: 4, overflow: 'hidden', marginBottom: 8 },
    progressBarFill: { height: '100%', backgroundColor: BLUE, borderRadius: 4 },
    catFooter: { flexDirection: 'row', justifyContent: 'space-between' },
    catSub: { fontSize: 12, color: '#A3AED0', fontWeight: '500' },
    stockBadge: { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 12 },
    stockLow: { backgroundColor: WARNING + '20' },
    stockLowText: { color: WARNING, fontSize: 12, fontWeight: 'bold' },
    stockCritical: { backgroundColor: ERROR + '20' },
    stockCriticalText: { color: ERROR, fontSize: 12, fontWeight: 'bold' },
    emptyText: { textAlign: 'center', color: '#A3AED0', paddingVertical: 10 }
});

export default ReportsScreen;
