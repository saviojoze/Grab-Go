import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, ActivityIndicator, SafeAreaView, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { merchantService } from '../services/api';
import { useAppContext } from '../context/AppContext';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';

const LogsScreen = () => {
    const { user } = useAppContext();
    const [attendance, setAttendance] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchAttendance = async () => {
        try {
            const response = await merchantService.getLogs(user.id);
            if (response.data?.success) {
                setAttendance(response.data.data);
            }
        } catch (error) {
            console.error('Fetch attendance error:', error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        fetchAttendance();
    }, []);

    const onRefresh = () => {
        setRefreshing(true);
        fetchAttendance();
    };

    const renderItem = ({ item }) => {
        const isLate = item.status === 'Late';
        const isHalfDay = item.status === 'Half Day';
        const isAbsent = item.status === 'Absent';

        let statusColor = '#00D563';
        if (isLate) statusColor = '#FF9800';
        if (isHalfDay) statusColor = '#00BCD4';
        if (isAbsent) statusColor = '#EE5D50';

        return (
            <View style={styles.card}>
                <View style={styles.cardTop}>
                    <Text style={styles.name}>{item.staff_name || `Staff #${item.employee_id || item.user_id}`}</Text>
                    <View style={[styles.statusBadge, { backgroundColor: statusColor + '20' }]}>
                        <Text style={[styles.statusText, { color: statusColor }]}>{item.status}</Text>
                    </View>
                </View>

                <View style={styles.cardContent}>
                    <View style={styles.timeBlock}>
                        <Ionicons name="enter-outline" size={16} color="#A3AED0" />
                        <Text style={styles.timeText}>In: {item.check_in_time ? item.check_in_time.substring(0, 5) : '--:--'}</Text>
                    </View>
                    <View style={styles.timeBlock}>
                        <Ionicons name="exit-outline" size={16} color="#A3AED0" />
                        <Text style={styles.timeText}>Out: {item.check_out_time ? item.check_out_time.substring(0, 5) : '--:--'}</Text>
                    </View>
                    <View style={styles.timeBlock}>
                        <Ionicons name="calendar-outline" size={16} color="#A3AED0" />
                        <Text style={styles.timeText}>{item.date}</Text>
                    </View>
                </View>
            </View>
        );
    };

    if (loading) {
        return (
            <View style={styles.center}>
                <ActivityIndicator size="large" color={BLUE} />
            </View>
        );
    }

    return (
        <SafeAreaView style={styles.container}>
            <View style={styles.header}>
                <Text style={styles.headerTitle}>Attendance Logs</Text>
                <Text style={styles.headerSubtitle}>View historical check-in and check-out records</Text>
            </View>
            <FlatList
                data={attendance}
                keyExtractor={(item, index) => item.id?.toString() || index.toString()}
                renderItem={renderItem}
                contentContainerStyle={styles.list}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={BLUE} />}
                ListEmptyComponent={<Text style={styles.emptyText}>No attendance records found</Text>}
            />
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8F9FE' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { padding: 20, paddingBottom: 10 },
    headerTitle: { fontSize: 24, fontWeight: 'bold', color: DARK_NAVY },
    headerSubtitle: { fontSize: 13, color: '#A3AED0', marginTop: 4 },
    list: { padding: 16, paddingBottom: 80 },
    card: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 },
    cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderBottomWidth: 1, borderBottomColor: '#F4F7FE', paddingBottom: 12, marginBottom: 12 },
    name: { fontSize: 16, fontWeight: 'bold', color: DARK_NAVY },
    statusBadge: { paddingHorizontal: 12, paddingVertical: 4, borderRadius: 12 },
    statusText: { fontSize: 12, fontWeight: 'bold' },
    cardContent: { flexDirection: 'row', justifyContent: 'space-between' },
    timeBlock: { flexDirection: 'row', alignItems: 'center' },
    timeText: { fontSize: 13, color: '#A3AED0', marginLeft: 6, fontWeight: '500' },
    emptyText: { textAlign: 'center', color: '#A3AED0', marginTop: 32 }
});

export default LogsScreen;
