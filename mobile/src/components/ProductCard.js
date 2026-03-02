import React from 'react';
import { View, Text, Image, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const ProductCard = ({ product, onAddToCart, onPress }) => {
    return (
        <TouchableOpacity style={styles.card} onPress={onPress}>
            <Image
                source={{ uri: product.image_url.startsWith('http') ? product.image_url : `http://192.168.37.21/Mini%20Project/${product.image_url}` }}
                style={styles.image}
            />
            {!!product.is_sale && (
                <View style={styles.badge}>
                    <Text style={styles.badgeText}>SALE</Text>
                </View>
            )}
            <View style={styles.info}>
                <Text style={styles.name} numberOfLines={1}>{product.name}</Text>
                <Text style={styles.unit}>{product.unit}</Text>
                <View style={styles.footer}>
                    <Text style={styles.price}>₹{product.price}</Text>
                    <TouchableOpacity style={styles.addButton} onPress={() => onAddToCart(product)}>
                        <Ionicons name="add" size={24} color="#fff" />
                    </TouchableOpacity>
                </View>
            </View>
        </TouchableOpacity>
    );
};

const styles = StyleSheet.create({
    card: {
        backgroundColor: '#fff',
        borderRadius: 12,
        margin: 8,
        width: '45%',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        elevation: 3,
        overflow: 'hidden',
    },
    image: {
        width: '100%',
        height: 120,
        resizeMode: 'cover',
    },
    badge: {
        position: 'absolute',
        top: 8,
        left: 8,
        backgroundColor: '#00D563',
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 4,
    },
    badgeText: {
        color: '#fff',
        fontSize: 10,
        fontWeight: 'bold',
    },
    info: {
        padding: 10,
    },
    name: {
        fontSize: 16,
        fontWeight: '600',
        color: '#333',
    },
    unit: {
        fontSize: 12,
        color: 'gray',
        marginVertical: 4,
    },
    footer: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginTop: 4,
    },
    price: {
        fontSize: 18,
        fontWeight: '700',
        color: '#1877F2',
    },
    addButton: {
        backgroundColor: '#00D563',
        width: 32,
        height: 32,
        borderRadius: 16,
        alignItems: 'center',
        justifyContent: 'center',
    },
});

export default ProductCard;
