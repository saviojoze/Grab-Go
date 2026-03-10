import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    StyleSheet,
    FlatList,
    TouchableOpacity,
    Modal,
    TextInput,
    ActivityIndicator,
    Alert,
    RefreshControl
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppContext } from '../context/AppContext';
import { leaveService } from '../services/api';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';
const SECONDARY_BLUE = '#F4F7FE';

const MyLeavesScreen = () => {
    const { user } = useAppContext();
    const [leaves, setLeaves] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [modalVisible, setModalVisible] = useState(false);

    // Form State
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [reason, setReason] = useState('');
    const [leaveType, setLeaveType] = useState('Sick Leave');
    const [submitting, setSubmitting] = useState(false);

    const fetchLeaves = async () => {
        try {
            const response = await leaveService.getLeaves(user.id);
            if (response.data?.success) {
                setLeaves(response.data.data || []);
            }
        } catch (error) {
            console.error('Error fetching leaves:', error);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        fetchLeaves();
    }, []);

    const onRefresh = () => {
        setRefreshing(true);
        fetchLeaves();
    };

    const handleApply = async () => {
        if (!startDate || !endDate || !reason) {
            Alert.alert('Error', 'Please fill in all fields');
            return;
        }

        setSubmitting(true);
        try {
            const res = await leaveService.applyLeave({
                user_id: user.id,
                start_date: startDate,
                end_date: endDate,
                leave_type: leaveType,
                reason: reason
            });

            if (res.data?.success) {
                Alert.alert('Success', 'Leave application submitted successfully');
                setModalVisible(false);
                setStartDate('');
                setEndDate('');
                setReason('');
                fetchLeaves();
            } else {
                Alert.alert('Error', res.data?.message || 'Failed to submit application');
            }
        } catch (error) {
            Alert.alert('Error', 'An error occurred while submitting');
        } finally {
            setSubmitting(false);
        }
    };

    const getStatusColor = (status) => {
        switch (status.toLowerCase()) {
            case 'approved': return '#00D563';
            case 'pending': return '#ea580c';
            case 'rejected': return '#EE5D50';
            default: return '#A3AED0';
        }
    };

    const renderLeave = ({ item }) => (
        <View style={styles.leaveCard}>
            <View style={styles.cardHeader}>
                <View>
                    <Text style={styles.leaveType}>{item.leave_type}</Text>
                    <Text style={styles.leaveDates}>
                        {item.start_date} → {item.end_date}
                    </Text>
                </View>
                <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) + '15' }]}>
                    <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
                        {item.status.toUpperCase()}
                    </Text>
                </View>
            </View>
            <Text style={styles.reasonText} numberOfLines={2}>{item.reason}</Text>
            {item.admin_remarks && (
                <View style={styles.remarksBox}>
                    <Text style={styles.remarksLabel}>Admin Remarks:</Text>
                    <Text style={styles.remarksText}>{item.admin_remarks}</Text>
                </View>
            )}
        </View>
    );

    return (
        <View style={styles.container}>
            <FlatList
                data={leaves}
                renderItem={renderLeave}
                keyExtractor={(item) => item.id.toString()}
                contentContainerStyle={styles.listContainer}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[BLUE]} />}
                ListEmptyComponent={
                    !loading && (
                        <View style={styles.emptyContainer}>
                            <Ionicons name="calendar-outline" size={60} color="#A3AED0" />
                            <Text style={styles.emptyText}>No leave requests yet.</Text>
                        </View>
                    )
                }
            />

            <TouchableOpacity style={styles.fab} onPress={() => setModalVisible(true)}>
                <Ionicons name="add" size={30} color="#fff" />
            </TouchableOpacity>

            <Modal
                animationType="slide"
                transparent={true}
                visible={modalVisible}
                onRequestClose={() => setModalVisible(false)}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Apply for Leave</Text>
                            <TouchableOpacity onPress={() => setModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#A3AED0" />
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.label}>Start Date (YYYY-MM-DD)</Text>
                        <TextInput
                            style={styles.input}
                            value={startDate}
                            onChangeText={setStartDate}
                            placeholder="e.g. 2026-04-01"
                        />

                        <Text style={styles.label}>End Date (YYYY-MM-DD)</Text>
                        <TextInput
                            style={styles.input}
                            value={endDate}
                            onChangeText={setEndDate}
                            placeholder="e.g. 2026-04-05"
                        />

                        <Text style={styles.label}>Reason</Text>
                        <TextInput
                            style={[styles.input, styles.textArea]}
                            value={reason}
                            onChangeText={setReason}
                            placeholder="Reason for leave..."
                            multiline
                        />

                        <TouchableOpacity
                            style={[styles.applyBtn, submitting && styles.btnDisabled]}
                            onPress={handleApply}
                            disabled={submitting}
                        >
                            {submitting ? (
                                <ActivityIndicator color="#fff" />
                            ) : (
                                <Text style={styles.applyBtnText}>Submit Application</Text>
                            )}
                        </TouchableOpacity>
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
    listContainer: {
        padding: 20,
    },
    leaveCard: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 20,
        marginBottom: 16,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 2,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: 12,
    },
    leaveType: {
        fontSize: 16,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    leaveDates: {
        fontSize: 12,
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
        fontWeight: 'bold',
    },
    reasonText: {
        fontSize: 14,
        color: '#475467',
        lineHeight: 20,
    },
    remarksBox: {
        marginTop: 15,
        paddingTop: 15,
        borderTopWidth: 1,
        borderTopColor: '#F4F7FE',
    },
    remarksLabel: {
        fontSize: 11,
        fontWeight: 'bold',
        color: '#A3AED0',
        marginBottom: 4,
    },
    remarksText: {
        fontSize: 13,
        color: '#EE5D50',
    },
    emptyContainer: {
        alignItems: 'center',
        marginTop: 100,
    },
    emptyText: {
        marginTop: 15,
        color: '#A3AED0',
        fontSize: 16,
    },
    fab: {
        position: 'absolute',
        right: 24,
        bottom: 24,
        backgroundColor: BLUE,
        width: 56,
        height: 56,
        borderRadius: 28,
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 10,
        elevation: 10,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.5)',
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
        marginBottom: 24,
    },
    modalTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    label: {
        fontSize: 14,
        fontWeight: '600',
        color: '#475467',
        marginBottom: 8,
    },
    input: {
        backgroundColor: '#F4F7FE',
        borderRadius: 12,
        padding: 14,
        marginBottom: 20,
        fontSize: 15,
        color: DARK_NAVY,
    },
    textArea: {
        height: 100,
        textAlignVertical: 'top',
    },
    applyBtn: {
        backgroundColor: BLUE,
        padding: 16,
        borderRadius: 16,
        alignItems: 'center',
        marginTop: 10,
    },
    btnDisabled: {
        opacity: 0.6,
    },
    applyBtnText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16,
    },
});

export default MyLeavesScreen;
