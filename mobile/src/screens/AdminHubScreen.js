import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';

const AdminHubScreen = ({ navigation }) => {
    const ActionTile = ({ title, icon, color, onPress }) => (
        <TouchableOpacity style={styles.actionTile} onPress={onPress}>
            <View style={[styles.actionIconBox, { backgroundColor: color + '10' }]}>
                <Ionicons name={icon} size={28} color={color} />
            </View>
            <Text style={styles.actionTitle}>{title}</Text>
        </TouchableOpacity>
    );

    return (
        <ScrollView style={styles.container}>
            <View style={styles.grid}>
                <ActionTile title="Products" icon="cube-outline" color="#2196F3" onPress={() => navigation.navigate('Inventory')} />
                <ActionTile title="Categories" icon="list-outline" color="#9C27B0" onPress={() => Alert.alert('Coming Soon', 'Categories management is under development.')} />
                <ActionTile title="Orders" icon="cart-outline" color="#FF9800" onPress={() => navigation.navigate('Orders')} />
                <ActionTile title="Customers" icon="people-outline" color="#4CAF50" onPress={() => navigation.navigate('Users')} />

                <ActionTile title="Staff List" icon="id-card-outline" color="#3F51B5" onPress={() => navigation.navigate('Users')} />
                <ActionTile title="Leaves" icon="calendar-outline" color="#E91E63" onPress={() => navigation.navigate('Leaves')} />
                <ActionTile title="Attendance" icon="time-outline" color="#00BCD4" onPress={() => Alert.alert('Coming Soon', 'Attendance tracking is under development.')} />
                <ActionTile title="Logs" icon="document-text-outline" color="#607D8B" onPress={() => Alert.alert('Coming Soon', 'System logs are under development.')} />
            </View>
        </ScrollView>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F8F9FE',
        padding: 16,
    },
    grid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        justifyContent: 'space-between',
        marginTop: 10,
    },
    actionTile: {
        width: '23%',
        alignItems: 'center',
        marginBottom: 24,
    },
    actionIconBox: {
        width: 60,
        height: 60,
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 10,
        backgroundColor: '#fff',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 5,
        elevation: 2,
    },
    actionTitle: {
        fontSize: 12,
        fontWeight: '700',
        color: '#707EAE',
        textAlign: 'center',
    },
});

export default AdminHubScreen;
