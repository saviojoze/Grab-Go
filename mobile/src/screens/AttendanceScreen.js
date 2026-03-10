import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, ActivityIndicator, SafeAreaView, TouchableOpacity, Alert, TextInput } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { merchantService } from '../services/api';
import { useAppContext } from '../context/AppContext';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';

const AttendanceScreen = () => {
    const { user } = useAppContext();
    const [boardData, setBoardData] = useState([]);
    const [currentDate, setCurrentDate] = useState(new Date().toISOString().split('T')[0]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    const fetchBoard = async (date) => {
        setLoading(true);
        try {
            const response = await merchantService.getAttendanceBoard(user.id, date);
            if (response.data?.success) {
                setBoardData(response.data.data.attendance || []);
            }
        } catch (error) {
            console.error('Fetch attendance board error:', error);
            Alert.alert('Error', 'Failed to load attendance board');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchBoard(currentDate);
    }, [currentDate]);

    const handleDateChange = (days) => {
        const d = new Date(currentDate);
        d.setDate(d.getDate() + days);
        setCurrentDate(d.toISOString().split('T')[0]);
    };

    const updateStaffRecord = (employeeId, field, value) => {
        setBoardData(prev => prev.map(staff => {
            if (staff.employee_id === employeeId) {
                const updated = { ...staff, [field]: value };
                if (field === 'status') {
                    if (value === 'Absent') {
                        updated.check_in_time = '';
                        updated.check_out_time = '';
                    } else if (!updated.check_in_time) {
                        updated.check_in_time = '09:00';
                    }
                }
                return updated;
            }
            return staff;
        }));
    };

    const markAllPresent = () => {
        setBoardData(prev => prev.map(staff => ({
            ...staff,
            status: 'Present',
            check_in_time: staff.check_in_time || '09:00'
        })));
    };

    const saveChanges = async () => {
        setSaving(true);
        try {
            const payload = {
                date: currentDate,
                attendance: boardData
            };
            const response = await merchantService.saveAttendanceBoard(user.id, payload);
            if (response.data?.success) {
                Alert.alert('Success', 'Attendance saved successfully', [{ text: 'OK' }]);
            } else {
                Alert.alert('Error', response.data?.message || 'Failed to save');
            }
        } catch (error) {
            console.error('Save error:', error);
            Alert.alert('Error', 'Network error while saving attendance');
        } finally {
            setSaving(false);
        }
    };

    const renderItem = ({ item }) => {
        return (
            <View style={styles.card}>
                <View style={styles.staffHeader}>
                    <View style={styles.avatar}>
                        <Text style={styles.avatarText}>{item.staff_name.charAt(0).toUpperCase()}</Text>
                    </View>
                    <View style={styles.staffInfo}>
                        <Text style={styles.name}>{item.staff_name}</Text>
                        <Text style={styles.position}>{item.position}</Text>
                    </View>
                </View>

                <View style={styles.statusRow}>
                    {['Present', 'Late', 'Absent'].map(status => {
                        const isSelected = item.status === status;
                        let activeColor = '#00D563';
                        if (status === 'Late') activeColor = '#FFB547';
                        if (status === 'Absent') activeColor = '#EE5D50';

                        return (
                            <TouchableOpacity
                                key={status}
                                style={[styles.statusBtn, isSelected && { backgroundColor: activeColor, borderColor: activeColor }]}
                                onPress={() => updateStaffRecord(item.employee_id, 'status', status)}
                            >
                                <Text style={[styles.statusText, isSelected && styles.statusTextActive]}>{status}</Text>
                            </TouchableOpacity>
                        );
                    })}
                </View>

                {(item.status === 'Present' || item.status === 'Late') && (
                    <View style={styles.timeInputs}>
                        <View style={styles.timeGroup}>
                            <Text style={styles.timeLabel}>Check In</Text>
                            <TextInput
                                style={styles.timeInput}
                                value={item.check_in_time}
                                onChangeText={(val) => updateStaffRecord(item.employee_id, 'check_in_time', val)}
                                placeholder="09:00"
                                maxLength={5}
                            />
                        </View>
                        <View style={styles.timeGroup}>
                            <Text style={styles.timeLabel}>Check Out</Text>
                            <TextInput
                                style={styles.timeInput}
                                value={item.check_out_time}
                                onChangeText={(val) => updateStaffRecord(item.employee_id, 'check_out_time', val)}
                                placeholder="17:00"
                                maxLength={5}
                            />
                        </View>
                    </View>
                )}
            </View>
        );
    };

    if (loading && boardData.length === 0) {
        return (
            <View style={styles.center}>
                <ActivityIndicator size="large" color={BLUE} />
            </View>
        );
    }

    return (
        <SafeAreaView style={styles.container}>
            <View style={styles.header}>
                <Text style={styles.headerTitle}>Attendance Board</Text>
                <Text style={styles.headerSubtitle}>Track daily staff attendance</Text>

                <View style={styles.dateNav}>
                    <TouchableOpacity onPress={() => handleDateChange(-1)} style={styles.navBtn}>
                        <Ionicons name="chevron-back" size={20} color={BLUE} />
                    </TouchableOpacity>
                    <View style={styles.dateDisplay}>
                        <Ionicons name="calendar-outline" size={16} color={DARK_NAVY} style={{ marginRight: 6 }} />
                        <Text style={styles.dateText}>{currentDate}</Text>
                    </View>
                    <TouchableOpacity onPress={() => handleDateChange(1)} style={styles.navBtn}>
                        <Ionicons name="chevron-forward" size={20} color={BLUE} />
                    </TouchableOpacity>
                </View>

                <View style={styles.actionRow}>
                    <TouchableOpacity style={styles.markAllBtn} onPress={markAllPresent}>
                        <Ionicons name="checkmark-done" size={16} color="#A3AED0" />
                        <Text style={styles.markAllText}>Mark All Present</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={styles.saveBtn} onPress={saveChanges} disabled={saving}>
                        {saving ? <ActivityIndicator size="small" color="#FFF" /> : (
                            <>
                                <Ionicons name="save-outline" size={16} color="#FFF" />
                                <Text style={styles.saveText}>Save</Text>
                            </>
                        )}
                    </TouchableOpacity>
                </View>
            </View>

            <FlatList
                data={boardData}
                keyExtractor={(item) => item.employee_id.toString()}
                renderItem={renderItem}
                contentContainerStyle={styles.list}
                ListEmptyComponent={<Text style={styles.emptyText}>No active staff members found.</Text>}
            />
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8F9FE' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { padding: 20, paddingBottom: 10, backgroundColor: '#FFF', borderBottomWidth: 1, borderBottomColor: '#F4F7FE' },
    headerTitle: { fontSize: 24, fontWeight: 'bold', color: DARK_NAVY },
    headerSubtitle: { fontSize: 13, color: '#A3AED0', marginTop: 4, marginBottom: 16 },
    dateNav: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#F4F7FE', borderRadius: 12, padding: 4, marginBottom: 16 },
    navBtn: { padding: 8, backgroundColor: '#FFF', borderRadius: 8 },
    dateDisplay: { flexDirection: 'row', alignItems: 'center' },
    dateText: { fontSize: 16, fontWeight: 'bold', color: DARK_NAVY },
    actionRow: { flexDirection: 'row', justifyContent: 'space-between' },
    markAllBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F4F7FE', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8 },
    markAllText: { marginLeft: 6, fontSize: 12, fontWeight: 'bold', color: '#A3AED0' },
    saveBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: BLUE, paddingHorizontal: 16, paddingVertical: 8, borderRadius: 8 },
    saveText: { marginLeft: 6, fontSize: 14, fontWeight: 'bold', color: '#FFF' },
    list: { padding: 16, paddingBottom: 80 },
    card: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 },
    staffHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
    avatar: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#F4F7FE', justifyContent: 'center', alignItems: 'center', marginRight: 12 },
    avatarText: { fontSize: 16, fontWeight: 'bold', color: BLUE },
    staffInfo: { flex: 1 },
    name: { fontSize: 16, fontWeight: 'bold', color: DARK_NAVY },
    position: { fontSize: 12, color: '#A3AED0', marginTop: 2 },
    statusRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12, backgroundColor: '#F4F7FE', padding: 4, borderRadius: 10 },
    statusBtn: { flex: 1, paddingVertical: 8, alignItems: 'center', borderRadius: 8, borderWidth: 1, borderColor: 'transparent' },
    statusText: { fontSize: 12, fontWeight: 'bold', color: '#A3AED0' },
    statusTextActive: { color: '#FFF' },
    timeInputs: { flexDirection: 'row', justifyContent: 'space-between', borderTopWidth: 1, borderTopColor: '#F4F7FE', paddingTop: 12 },
    timeGroup: { flex: 1, marginHorizontal: 4 },
    timeLabel: { fontSize: 10, fontWeight: 'bold', color: '#A3AED0', marginBottom: 4, textTransform: 'uppercase' },
    timeInput: { backgroundColor: '#F4F7FE', borderRadius: 8, padding: 8, fontSize: 14, fontWeight: '600', color: DARK_NAVY, textAlign: 'center' },
    emptyText: { textAlign: 'center', color: '#A3AED0', marginTop: 32 }
});

export default AttendanceScreen;
