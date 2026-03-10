import React, { useState, useEffect, useRef } from 'react';
import {
    View, Text, StyleSheet, Modal, TouchableOpacity,
    ActivityIndicator, Alert, Platform
} from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { Ionicons } from '@expo/vector-icons';

const BLUE = '#4318FF';
const DARK_NAVY = '#1B2559';

/**
 * QRScannerModal
 * Props:
 *   visible       - boolean
 *   onClose       - () => void
 *   onScanned     - (otp: string) => void   called when a valid OTP is extracted
 */
const QRScannerModal = ({ visible, onClose, onScanned }) => {
    const [permission, requestPermission] = useCameraPermissions();
    const [scanned, setScanned] = useState(false);
    const [torch, setTorch] = useState(false);

    // Reset scanned state every time modal opens
    useEffect(() => {
        if (visible) {
            setScanned(false);
            setTorch(false);
        }
    }, [visible]);

    const handleBarCodeScanned = ({ data }) => {
        if (scanned) return;
        setScanned(true);

        try {
            // Try to parse as JSON (our QR format: { order_id, otp, user })
            const parsed = JSON.parse(data);
            if (parsed.otp && /^\d{6}$/.test(parsed.otp)) {
                onScanned(parsed.otp);
                return;
            }
        } catch (_) { /* not JSON */ }

        // Fallback: if the raw data is a plain 6-digit number
        const stripped = data.trim();
        if (/^\d{6}$/.test(stripped)) {
            onScanned(stripped);
            return;
        }

        // Unknown QR — give user a chance to retry
        Alert.alert(
            '❌ Invalid QR Code',
            'This QR code does not contain a valid Delivery OTP. Ask the customer to show the correct QR.',
            [{ text: 'Scan Again', onPress: () => setScanned(false) }]
        );
    };

    if (!visible) return null;

    if (!permission) {
        return (
            <Modal visible transparent animationType="fade">
                <View style={styles.center}>
                    <ActivityIndicator color={BLUE} size="large" />
                </View>
            </Modal>
        );
    }

    if (!permission.granted) {
        return (
            <Modal visible transparent animationType="slide" onRequestClose={onClose}>
                <View style={styles.permissionOverlay}>
                    <View style={styles.permissionBox}>
                        <Ionicons name="camera-outline" size={60} color={BLUE} />
                        <Text style={styles.permTitle}>Camera Permission Needed</Text>
                        <Text style={styles.permSub}>
                            To scan the customer's QR code, please allow camera access.
                        </Text>
                        <TouchableOpacity style={styles.permBtn} onPress={requestPermission}>
                            <Text style={styles.permBtnText}>Allow Camera</Text>
                        </TouchableOpacity>
                        <TouchableOpacity style={styles.cancelLink} onPress={onClose}>
                            <Text style={styles.cancelLinkText}>Enter OTP Manually Instead</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </Modal>
        );
    }

    return (
        <Modal visible={visible} animationType="slide" onRequestClose={onClose}>
            <View style={styles.scannerContainer}>
                {/* Header */}
                <View style={styles.scannerHeader}>
                    <TouchableOpacity style={styles.closeBtn} onPress={onClose}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.scannerTitle}>Scan Customer QR</Text>
                    <TouchableOpacity style={styles.torchBtn} onPress={() => setTorch(t => !t)}>
                        <Ionicons name={torch ? 'flashlight' : 'flashlight-outline'} size={22} color={torch ? '#FFB300' : '#fff'} />
                    </TouchableOpacity>
                </View>

                {/* Camera */}
                <CameraView
                    style={StyleSheet.absoluteFill}
                    facing="back"
                    enableTorch={torch}
                    barcodeScannerSettings={{ barcodeTypes: ['qr'] }}
                    onBarcodeScanned={scanned ? undefined : handleBarCodeScanned}
                />

                {/* Scan Frame Overlay */}
                <View style={styles.overlay}>
                    <View style={styles.overlayTop} />
                    <View style={styles.overlayMiddle}>
                        <View style={styles.overlaySide} />
                        <View style={styles.scanFrame}>
                            {/* Corner markers */}
                            <View style={[styles.corner, styles.cornerTL]} />
                            <View style={[styles.corner, styles.cornerTR]} />
                            <View style={[styles.corner, styles.cornerBL]} />
                            <View style={[styles.corner, styles.cornerBR]} />
                            {scanned && (
                                <View style={styles.scannedOverlay}>
                                    <ActivityIndicator color="#fff" size="large" />
                                </View>
                            )}
                        </View>
                        <View style={styles.overlaySide} />
                    </View>
                    <View style={styles.overlayBottom}>
                        <View style={styles.instructionBox}>
                            <Ionicons name="qr-code-outline" size={20} color="#fff" />
                            <Text style={styles.instructionText}>
                                Point camera at the customer's QR code
                            </Text>
                        </View>
                        {scanned && (
                            <TouchableOpacity style={styles.rescanBtn} onPress={() => setScanned(false)}>
                                <Ionicons name="refresh" size={18} color={BLUE} />
                                <Text style={styles.rescanText}>Scan Again</Text>
                            </TouchableOpacity>
                        )}
                    </View>
                </View>
            </View>
        </Modal>
    );
};

const FRAME_SIZE = 240;
const CORNER = 24;
const CORNER_THICKNESS = 4;

const styles = StyleSheet.create({
    center: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: 'rgba(0,0,0,0.5)' },

    // Permission
    permissionOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'center', padding: 24 },
    permissionBox: {
        backgroundColor: '#fff', borderRadius: 24, padding: 32,
        alignItems: 'center', gap: 12,
    },
    permTitle: { fontSize: 20, fontWeight: 'bold', color: DARK_NAVY, textAlign: 'center' },
    permSub: { fontSize: 14, color: '#707EAE', textAlign: 'center', lineHeight: 20 },
    permBtn: {
        backgroundColor: BLUE, paddingHorizontal: 32, paddingVertical: 14,
        borderRadius: 14, marginTop: 8,
    },
    permBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
    cancelLink: { marginTop: 4 },
    cancelLinkText: { color: '#A3AED0', fontSize: 14, textDecorationLine: 'underline' },

    // Scanner
    scannerContainer: { flex: 1, backgroundColor: '#000' },
    scannerHeader: {
        position: 'absolute', top: 0, left: 0, right: 0, zIndex: 10,
        flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
        paddingTop: Platform.OS === 'ios' ? 55 : 40,
        paddingHorizontal: 20, paddingBottom: 16,
        backgroundColor: 'rgba(0,0,0,0.4)',
    },
    closeBtn: { width: 40, height: 40, justifyContent: 'center', alignItems: 'center' },
    torchBtn: { width: 40, height: 40, justifyContent: 'center', alignItems: 'center' },
    scannerTitle: { fontSize: 18, fontWeight: 'bold', color: '#fff' },

    // Overlay
    overlay: { ...StyleSheet.absoluteFillObject, flexDirection: 'column' },
    overlayTop: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)' },
    overlayMiddle: { height: FRAME_SIZE, flexDirection: 'row' },
    overlaySide: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)' },
    overlayBottom: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', alignItems: 'center', paddingTop: 24, gap: 12 },

    // Scan frame
    scanFrame: {
        width: FRAME_SIZE,
        height: FRAME_SIZE,
        position: 'relative',
    },
    corner: {
        position: 'absolute',
        width: CORNER,
        height: CORNER,
        borderColor: BLUE,
    },
    cornerTL: { top: 0, left: 0, borderTopWidth: CORNER_THICKNESS, borderLeftWidth: CORNER_THICKNESS, borderTopLeftRadius: 4 },
    cornerTR: { top: 0, right: 0, borderTopWidth: CORNER_THICKNESS, borderRightWidth: CORNER_THICKNESS, borderTopRightRadius: 4 },
    cornerBL: { bottom: 0, left: 0, borderBottomWidth: CORNER_THICKNESS, borderLeftWidth: CORNER_THICKNESS, borderBottomLeftRadius: 4 },
    cornerBR: { bottom: 0, right: 0, borderBottomWidth: CORNER_THICKNESS, borderRightWidth: CORNER_THICKNESS, borderBottomRightRadius: 4 },
    scannedOverlay: {
        ...StyleSheet.absoluteFillObject,
        backgroundColor: 'rgba(0,0,0,0.4)',
        justifyContent: 'center', alignItems: 'center',
        borderRadius: 4,
    },

    // Instructions
    instructionBox: {
        flexDirection: 'row', alignItems: 'center', gap: 10,
        backgroundColor: 'rgba(255,255,255,0.15)',
        paddingHorizontal: 20, paddingVertical: 12, borderRadius: 14,
    },
    instructionText: { color: '#fff', fontSize: 13, fontWeight: '600', flex: 1 },
    rescanBtn: {
        flexDirection: 'row', alignItems: 'center', gap: 8,
        backgroundColor: '#fff', paddingHorizontal: 24, paddingVertical: 12,
        borderRadius: 12,
    },
    rescanText: { color: BLUE, fontWeight: 'bold', fontSize: 14 },
});

export default QRScannerModal;
