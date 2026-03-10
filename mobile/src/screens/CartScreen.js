import React, { useState } from 'react';
import { View, Text, StyleSheet, FlatList, Image, TouchableOpacity, ScrollView, Modal, TextInput, Alert, ActivityIndicator, Linking } from 'react-native';
import { useAppContext } from '../context/AppContext';
import { Ionicons } from '@expo/vector-icons';
import { orderService } from '../services/api';

const BLUE = '#1877F2';
const GREEN = '#00D563';
const ORANGE = '#ea580c';
const DARK_NAVY = '#1B2559';
const SECONDARY_BLUE = '#F4F7FE';

const CartScreen = ({ navigation }) => {
    const { cart, user, updateQuantity, removeFromCart, fetchCart } = useAppContext();
    const [isModalVisible, setModalVisible] = useState(false);
    const [pickupDate, setPickupDate] = useState(new Date().toISOString().split('T')[0]);
    const [pickupTime, setPickupTime] = useState('14:00');
    const [paymentMethod, setPaymentMethod] = useState('cash');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    const handlePlaceOrder = async () => {
        if (!pickupDate || !pickupTime) {
            Alert.alert('Error', 'Please enter pickup date and time');
            return;
        }

        setIsSubmitting(true);
        try {
            const orderData = {
                pickup_date: pickupDate,
                pickup_time: pickupTime,
                contact_name: user.full_name,
                contact_email: user.email,
                contact_phone: user.phone || '9999999999',
                payment_method: paymentMethod
            };

            const response = await orderService.placeOrder(user.id, orderData);
            const { order_number } = response.data.data;

            if (paymentMethod === 'online') {
                const paymentUrl = `http://192.168.137.1/Mini%20Project/checkout/pay-online.php?order=${order_number}&user_id=${user.id}`;

                setModalVisible(false);
                Alert.alert(
                    'Follow Up Payment',
                    `Order ${order_number} created. We are opening your browser to complete the Razorpay payment securely.`,
                    [{
                        text: 'Go to Payment',
                        onPress: () => {
                            Linking.openURL(paymentUrl).catch(err => {
                                console.error('Error opening URL:', err);
                                Alert.alert('Error', 'Could not open the payment page. Please use the website.');
                            });
                            fetchCart();
                        }
                    }]
                );
            } else {
                const { delivery_otp } = response.data.data;
                setModalVisible(false);
                Alert.alert(
                    'Order Placed',
                    `Your order ${order_number} has been placed successfully!\n\nDelivery OTP: ${delivery_otp}\n\nYou can find your QR code in the Profile tab.`,
                    [{ text: 'OK', onPress: () => fetchCart() }]
                );
            }
        } catch (error) {
            console.error('Checkout error:', error);
            Alert.alert('Error', error.response?.data?.message || 'Failed to place order. Please try again.');
        } finally {
            setIsSubmitting(false);
        }
    };

    const renderItem = ({ item }) => (
        <View style={styles.cartItem}>
            <Image
                source={{
                    uri: (item.image_url && typeof item.image_url === 'string' && item.image_url.startsWith('http'))
                        ? item.image_url
                        : (item.image_url ? `http://192.168.137.1/Mini%20Project/${item.image_url}` : 'https://via.placeholder.com/150')
                }}
                style={styles.itemImage}
            />
            <View style={styles.itemInfo}>
                <Text style={styles.itemName}>{item.name}</Text>
                <Text style={styles.itemPrice}>₹{item.price}</Text>
                <View style={styles.quantityContainer}>
                    <TouchableOpacity
                        style={styles.qtyBtn}
                        onPress={() => updateQuantity(item.product_id, item.quantity - 1)}
                    >
                        <Ionicons name="remove" size={18} color={BLUE} />
                    </TouchableOpacity>
                    <Text style={styles.qtyText}>{item.quantity}</Text>
                    <TouchableOpacity
                        style={styles.qtyBtn}
                        onPress={() => updateQuantity(item.product_id, item.quantity + 1)}
                    >
                        <Ionicons name="add" size={18} color={BLUE} />
                    </TouchableOpacity>
                </View>
            </View>
            <TouchableOpacity
                style={styles.removeBtn}
                onPress={() => removeFromCart(item.product_id)}
            >
                <Ionicons name="trash-outline" size={24} color="#F44336" />
            </TouchableOpacity>
        </View>
    );

    if (!user) {
        return (
            <View style={styles.emptyContainer}>
                <Ionicons name="person-circle-outline" size={80} color="#A3AED0" />
                <Text style={styles.emptyText}>Please log in to see your cart</Text>
                <TouchableOpacity
                    style={styles.shopNowBtn}
                    onPress={() => navigation.navigate('Profile')}
                >
                    <Text style={styles.shopNowText}>Go to Profile</Text>
                </TouchableOpacity>
            </View>
        );
    }

    return (
        <View style={styles.container}>
            {cart.length === 0 ? (
                <View style={[styles.emptyContainer, { paddingTop: 60, flex: undefined, flexGrow: 1, backgroundColor: '#F8F9FE', justifyContent: 'center' }]}>
                    <View style={{ width: 100, height: 100, borderRadius: 50, backgroundColor: 'rgba(24,119,242,0.1)', alignItems: 'center', justifyContent: 'center', marginBottom: 24 }}>
                        <Text style={{ fontSize: 50 }}>🛒</Text>
                    </View>
                    <Text style={{ fontSize: 22, fontWeight: '900', color: DARK_NAVY, marginBottom: 8 }}>Your cart is empty</Text>
                    <Text style={{ fontSize: 14, color: '#707EAE', marginBottom: 30 }}>Looks like you haven't added anything yet.</Text>
                    <TouchableOpacity
                        style={[styles.shopNowBtn, { width: '80%', paddingVertical: 18 }]}
                        onPress={() => navigation.navigate('Shop')}
                    >
                        <Text style={[styles.shopNowText, { fontSize: 16 }]}>Start Shopping</Text>
                    </TouchableOpacity>
                </View>
            ) : (
                <>
                    <FlatList
                        data={cart}
                        keyExtractor={(item) => item.product_id.toString()}
                        renderItem={renderItem}
                        contentContainerStyle={styles.list}
                    />
                    <View style={styles.summaryCard}>
                        <View style={styles.summaryRow}>
                            <Text style={styles.summaryLabel}>Subtotal</Text>
                            <Text style={styles.summaryValue}>₹{total.toFixed(2)}</Text>
                        </View>
                        <View style={styles.summaryRow}>
                            <Text style={styles.summaryLabel}>Delivery</Text>
                            <Text style={styles.summaryValue}>FREE</Text>
                        </View>
                        <View style={[styles.summaryRow, styles.totalRow]}>
                            <Text style={styles.totalLabel}>Total</Text>
                            <Text style={styles.totalValue}>₹{total.toFixed(2)}</Text>
                        </View>
                        <TouchableOpacity
                            style={styles.checkoutBtn}
                            onPress={() => setModalVisible(true)}
                        >
                            <Text style={styles.checkoutText}>Proceed to Checkout</Text>
                        </TouchableOpacity>
                    </View>
                </>
            )}

            {/* Checkout Modal */}
            <Modal
                animationType="slide"
                transparent={true}
                visible={isModalVisible}
                onRequestClose={() => setModalVisible(false)}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Pickup Details</Text>
                            <TouchableOpacity onPress={() => setModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#A3AED0" />
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.inputLabel}>Pickup Date (YYYY-MM-DD)</Text>
                        <TextInput
                            style={styles.input}
                            value={pickupDate}
                            onChangeText={setPickupDate}
                            placeholder="e.g. 2026-03-05"
                        />

                        <Text style={styles.inputLabel}>Pickup Time (HH:MM)</Text>
                        <TextInput
                            style={styles.input}
                            value={pickupTime}
                            onChangeText={setPickupTime}
                            placeholder="e.g. 14:30"
                        />

                        <Text style={styles.inputLabel}>Payment Method</Text>
                        <View style={styles.paymentSelector}>
                            <TouchableOpacity
                                style={[styles.payOption, paymentMethod === 'cash' && styles.payOptionActive]}
                                onPress={() => setPaymentMethod('cash')}
                            >
                                <Ionicons name="cash-outline" size={20} color={paymentMethod === 'cash' ? BLUE : '#A3AED0'} />
                                <Text style={[styles.payOptionText, paymentMethod === 'cash' && styles.payOptionTextActive]}>Cash</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[styles.payOption, paymentMethod === 'online' && styles.payOptionActive]}
                                onPress={() => setPaymentMethod('online')}
                            >
                                <Ionicons name="card-outline" size={20} color={paymentMethod === 'online' ? BLUE : '#A3AED0'} />
                                <Text style={[styles.payOptionText, paymentMethod === 'online' && styles.payOptionTextActive]}>Online</Text>
                            </TouchableOpacity>
                        </View>

                        <View style={styles.infoBox}>
                            <Ionicons name="information-circle-outline" size={20} color={BLUE} />
                            <Text style={styles.infoText}>
                                {paymentMethod === 'cash'
                                    ? "You can pay when picking up your items at the supermarket."
                                    : "You'll be directed to our secure payment gateway (Razorpay)."}
                            </Text>
                        </View>

                        <TouchableOpacity
                            style={[styles.confirmBtn, isSubmitting && styles.disabledBtn]}
                            onPress={handlePlaceOrder}
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? (
                                <ActivityIndicator color="#fff" />
                            ) : (
                                <Text style={styles.confirmBtnText}>Confirm Order</Text>
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
    list: {
        padding: 16,
    },
    cartItem: {
        flexDirection: 'row',
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 12,
        marginBottom: 12,
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 8,
        elevation: 3,
    },
    itemImage: {
        width: 70,
        height: 70,
        borderRadius: 12,
    },
    itemInfo: {
        flex: 1,
        marginLeft: 12,
    },
    itemName: {
        fontSize: 16,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    itemPrice: {
        fontSize: 14,
        color: BLUE,
        fontWeight: '800',
        marginTop: 4,
    },
    quantityContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 8,
    },
    qtyBtn: {
        width: 28,
        height: 28,
        borderRadius: 8,
        backgroundColor: SECONDARY_BLUE,
        alignItems: 'center',
        justifyContent: 'center',
    },
    qtyText: {
        marginHorizontal: 15,
        fontSize: 14,
        fontWeight: '800',
        color: DARK_NAVY,
    },
    removeBtn: {
        padding: 8,
    },
    summaryCard: {
        backgroundColor: '#fff',
        padding: 24,
        borderTopLeftRadius: 30,
        borderTopRightRadius: 30,
        shadowColor: '#1B2559',
        shadowOffset: { width: 0, height: -10 },
        shadowOpacity: 0.05,
        shadowRadius: 20,
        elevation: 20,
    },
    summaryRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 12,
    },
    summaryLabel: {
        color: '#A3AED0',
        fontSize: 14,
        fontWeight: '600',
    },
    summaryValue: {
        fontWeight: '700',
        color: DARK_NAVY,
    },
    totalRow: {
        marginTop: 8,
        borderTopWidth: 1,
        borderTopColor: '#F4F7FE',
        paddingTop: 15,
        marginBottom: 20,
    },
    totalLabel: {
        fontSize: 18,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    totalValue: {
        fontSize: 18,
        fontWeight: '900',
        color: BLUE,
    },
    checkoutBtn: {
        backgroundColor: BLUE,
        borderRadius: 16,
        paddingVertical: 18,
        alignItems: 'center',
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 12,
        elevation: 6,
    },
    checkoutText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
        letterSpacing: 0.5,
    },
    emptyContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    emptyText: {
        fontSize: 16,
        color: '#A3AED0',
        marginTop: 10,
        marginBottom: 24,
        fontWeight: '500',
    },
    shopNowBtn: {
        backgroundColor: BLUE,
        paddingHorizontal: 40,
        paddingVertical: 14,
        borderRadius: 14,
    },
    shopNowText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 16,
    },
    // Modal Styles
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(27, 37, 89, 0.8)',
        justifyContent: 'flex-end',
    },
    modalContent: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 30,
        borderTopRightRadius: 30,
        padding: 24,
        minHeight: 450,
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 24,
    },
    modalTitle: {
        fontSize: 22,
        fontWeight: 'bold',
        color: DARK_NAVY,
    },
    inputLabel: {
        fontSize: 13,
        color: '#707EAE',
        marginBottom: 8,
        fontWeight: '700',
        textTransform: 'uppercase',
    },
    input: {
        backgroundColor: SECONDARY_BLUE,
        borderRadius: 12,
        padding: 14,
        fontSize: 16,
        marginBottom: 20,
        color: DARK_NAVY,
        fontWeight: '600',
    },
    infoBox: {
        flexDirection: 'row',
        backgroundColor: SECONDARY_BLUE,
        padding: 16,
        borderRadius: 12,
        alignItems: 'center',
        marginBottom: 30,
        borderWidth: 1,
        borderColor: '#E9EDF7',
    },
    infoText: {
        marginLeft: 12,
        color: '#707EAE',
        fontSize: 13,
        flex: 1,
        lineHeight: 18,
        fontWeight: '500',
    },
    confirmBtn: {
        backgroundColor: GREEN,
        borderRadius: 16,
        paddingVertical: 18,
        alignItems: 'center',
        shadowColor: GREEN,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 10,
        elevation: 5,
    },
    confirmBtnText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
        letterSpacing: 0.5,
    },
    disabledBtn: {
        backgroundColor: '#A3AED0',
    },
    paymentSelector: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 20,
    },
    payOption: {
        flexDirection: 'row',
        width: '48%',
        backgroundColor: SECONDARY_BLUE,
        padding: 14,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
        borderColor: 'transparent',
    },
    payOptionActive: {
        backgroundColor: '#fff',
        borderColor: BLUE,
    },
    payOptionText: {
        marginLeft: 10,
        fontWeight: '700',
        color: '#A3AED0',
    },
    payOptionTextActive: {
        color: BLUE,
    },
});

export default CartScreen;
