import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Linking, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const BLUE = '#1877F2';
const GREEN = '#00D563';
const DARK_NAVY = '#1B2559';

const LocationsScreen = () => {
    const storeLocation = {
        latitude: 9.557270,
        longitude: 76.789436,
        address: 'Town Center, Kanjirappally, 686507',
        name: 'Grab & Go Supermarket'
    };

    const openMap = () => {
        const url = Platform.select({
            ios: `maps:0,0?q=${storeLocation.name}@${storeLocation.latitude},${storeLocation.longitude}`,
            android: `geo:0,0?q=${storeLocation.latitude},${storeLocation.longitude}(${storeLocation.name})`
        });

        Linking.canOpenURL(url).then(supported => {
            if (supported) {
                Linking.openURL(url);
            } else {
                const browserUrl = `https://www.google.com/maps/search/?api=1&query=${storeLocation.latitude},${storeLocation.longitude}`;
                Linking.openURL(browserUrl);
            }
        });
    };

    const makeCall = () => {
        Linking.openURL('tel:+919876543210');
    };

    return (
        <ScrollView style={styles.container}>
            <View style={styles.header}>
                <View style={styles.iconBox}>
                    <Ionicons name="location" size={40} color={GREEN} />
                </View>
                <Text style={styles.title}>Find Our Store</Text>
                <Text style={styles.subtitle}>Visit us for the best shopping experience</Text>
            </View>

            <View style={styles.card}>
                <Text style={styles.cardTitle}>{storeLocation.name}</Text>

                <View style={styles.infoRow}>
                    <View style={styles.bullet}>
                        <Ionicons name="map" size={20} color={GREEN} />
                    </View>
                    <View style={styles.infoContent}>
                        <Text style={styles.infoLabel}>Address</Text>
                        <Text style={styles.infoValue}>{storeLocation.address}</Text>
                    </View>
                </View>

                <View style={styles.infoRow}>
                    <View style={styles.bullet}>
                        <Ionicons name="call" size={20} color={GREEN} />
                    </View>
                    <View style={styles.infoContent}>
                        <Text style={styles.infoLabel}>Phone</Text>
                        <Text style={styles.infoValue}>+91 98765 43210</Text>
                    </View>
                </View>

                <View style={styles.infoRow}>
                    <View style={styles.bullet}>
                        <Ionicons name="time" size={20} color={GREEN} />
                    </View>
                    <View style={styles.infoContent}>
                        <Text style={styles.infoLabel}>Opening Hours</Text>
                        <Text style={styles.infoValue}>Open Daily: 8:00 AM - 10:00 PM</Text>
                    </View>
                </View>

                <View style={styles.actions}>
                    <TouchableOpacity style={styles.primaryBtn} onPress={openMap}>
                        <Ionicons name="navigate" size={20} color="#fff" />
                        <Text style={styles.primaryBtnText}>Get Directions</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.secondaryBtn} onPress={makeCall}>
                        <Ionicons name="call-outline" size={20} color={GREEN} />
                        <Text style={styles.secondaryBtnText}>Call Us</Text>
                    </TouchableOpacity>
                </View>
            </View>

            <View style={styles.noteBox}>
                <Ionicons name="information-circle-outline" size={20} color="#707EAE" />
                <Text style={styles.noteText}>
                    Order online and pick up your items at the counter within 30 minutes.
                </Text>
            </View>
        </ScrollView>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F8F9FE',
    },
    header: {
        alignItems: 'center',
        paddingVertical: 40,
        paddingHorizontal: 20,
    },
    iconBox: {
        width: 80,
        height: 80,
        borderRadius: 40,
        backgroundColor: '#fff',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 10,
        elevation: 5,
    },
    title: {
        fontSize: 24,
        fontWeight: '900',
        color: DARK_NAVY,
        marginBottom: 8,
    },
    subtitle: {
        fontSize: 14,
        color: '#707EAE',
        textAlign: 'center',
    },
    card: {
        backgroundColor: '#fff',
        marginHorizontal: 20,
        borderRadius: 24,
        padding: 24,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.05,
        shadowRadius: 20,
        elevation: 5,
        borderWidth: 1,
        borderColor: '#F4F7FE',
    },
    cardTitle: {
        fontSize: 18,
        fontWeight: '800',
        color: DARK_NAVY,
        marginBottom: 20,
    },
    infoRow: {
        flexDirection: 'row',
        marginBottom: 20,
        alignItems: 'flex-start',
    },
    bullet: {
        width: 40,
        height: 40,
        borderRadius: 12,
        backgroundColor: '#f6fdf9',
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 16,
    },
    infoContent: {
        flex: 1,
    },
    infoLabel: {
        fontSize: 12,
        color: '#A3AED0',
        fontWeight: '700',
        textTransform: 'uppercase',
        marginBottom: 2,
    },
    infoValue: {
        fontSize: 14,
        color: DARK_NAVY,
        fontWeight: '600',
        lineHeight: 20,
    },
    actions: {
        marginTop: 10,
        gap: 12,
    },
    primaryBtn: {
        backgroundColor: GREEN,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 16,
        borderRadius: 16,
        gap: 8,
    },
    primaryBtnText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
    },
    secondaryBtn: {
        borderWidth: 2,
        borderColor: GREEN,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 14,
        borderRadius: 16,
        gap: 8,
    },
    secondaryBtnText: {
        color: GREEN,
        fontSize: 16,
        fontWeight: 'bold',
    },
    noteBox: {
        flexDirection: 'row',
        margin: 20,
        padding: 16,
        backgroundColor: '#F4F7FE',
        borderRadius: 12,
        alignItems: 'center',
        gap: 12,
    },
    noteText: {
        flex: 1,
        fontSize: 13,
        color: '#707EAE',
        lineHeight: 18,
    },
});

export default LocationsScreen;
