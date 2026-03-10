import React from 'react';
import { View, Text, Image, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

import { useAppContext } from '../context/AppContext';

const BLUE = '#1877F2';
const GREEN = '#00D563';
const DARK_NAVY = '#1B2559';

const ProductCard = ({ product, onAddToCart, onPress }) => {
    const { user } = useAppContext();
    const isMerchant = user?.role === 'staff' || user?.role === 'admin';

    // Simple IP-based image fix for React Native tests
    const getImageUrl = (url) => {
        if (!url) return 'https://via.placeholder.com/150';
        if (typeof url === 'string' && url.startsWith('http')) return url;
        return `http://192.168.137.1/Mini%20Project/${url}`;
    };

    return (
        <TouchableOpacity style={styles.card} onPress={onPress} activeOpacity={0.8}>
            <Image
                source={{ uri: getImageUrl(product.image_url) }}
                style={styles.image}
            />
            {(product.is_sale == 1 || product.is_sale == '1' || product.is_sale === true) && (
                <View style={styles.badge}>
                    <Text style={styles.badgeText}>SALE</Text>
                </View>
            )}
            <View style={styles.info}>
                <View style={styles.metaRow}>
                    <Text style={styles.unit}>{product.unit}</Text>
                    <View style={styles.ratingRow}>
                        <Ionicons name="star" size={10} color="#f59e0b" />
                        <Text style={styles.ratingText}>4.5</Text>
                    </View>
                </View>
                <Text style={styles.name} numberOfLines={2}>{product.name}</Text>
                <View style={styles.footer}>
                    <Text style={styles.price}>₹{product.price}</Text>
                    {!isMerchant && (
                        <TouchableOpacity style={styles.addButton} onPress={() => onAddToCart(product)}>
                            <Ionicons name="cart" size={16} color="#fff" />
                        </TouchableOpacity>
                    )}
                </View>
            </View>
        </TouchableOpacity>
    );
};

const styles = StyleSheet.create({
    card: {
        backgroundColor: '#fff',
        borderRadius: 16,
        margin: 8,
        flex: 1,
        maxWidth: '46%', // Ensures proper 2-column formatting
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 8,
        elevation: 3,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: '#f1f3f5'
    },
    image: {
        width: '100%',
        height: 140,
        resizeMode: 'cover',
        backgroundColor: '#f6f7f9'
    },
    badge: {
        position: 'absolute',
        top: 10,
        left: 10,
        backgroundColor: GREEN,
        paddingHorizontal: 8,
        paddingVertical: 3,
        borderRadius: 12,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        elevation: 2,
    },
    badgeText: {
        color: '#fff',
        fontSize: 9,
        fontWeight: '900',
        letterSpacing: 0.5,
    },
    info: {
        padding: 12,
        flex: 1,
        justifyContent: 'space-between',
    },
    metaRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 4,
    },
    unit: {
        fontSize: 10,
        color: '#A3AED0',
        fontWeight: '700',
        textTransform: 'uppercase',
    },
    ratingRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 2,
    },
    ratingText: {
        fontSize: 10,
        color: '#707EAE',
        fontWeight: 'bold',
    },
    name: {
        fontSize: 14,
        fontWeight: '800',
        color: DARK_NAVY,
        lineHeight: 18,
        marginBottom: 8,
    },
    footer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    price: {
        fontSize: 16,
        fontWeight: '900',
        color: BLUE,
    },
    addButton: {
        backgroundColor: BLUE,
        width: 30,
        height: 30,
        borderRadius: 10,
        alignItems: 'center',
        justifyContent: 'center',
    },
});

export default ProductCard;
