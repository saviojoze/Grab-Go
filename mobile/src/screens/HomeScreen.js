import React from 'react';
import { View, Text, StyleSheet, ScrollView, Image, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { productService } from '../services/api';
import { useAppContext } from '../context/AppContext';

const HomeScreen = ({ navigation }) => {
    const { addToCart } = useAppContext();

    const handlePromoAddToCart = () => {
        addToCart({
            id: 2, // Bananas ID (mocked from featured list)
            name: 'Fresh Organic Bananas',
            price: 9.60,
            image_url: 'images/products/bananas.jpg'
        });
    };
    const [categories, setCategories] = React.useState([]);

    React.useEffect(() => {
        const fetchCategories = async () => {
            try {
                const response = await productService.getCategories();
                // Filter for top-level categories and sort by display_order
                const mainCats = (response.data?.data || [])
                    .filter(cat => cat.parent_id === null)
                    .sort((a, b) => parseInt(a.display_order) - parseInt(b.display_order))
                    .slice(0, 8); // Show top 8
                setCategories(mainCats);
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        };
        fetchCategories();
    }, []);

    return (
        <ScrollView style={styles.container}>
            {/* Hero Section */}
            <View style={styles.hero}>
                <View style={styles.heroContent}>
                    <Text style={styles.heroBadge}>LIMITED OFFER 25% OFF</Text>
                    <Text style={styles.heroTitle}>Skip the Line.{"\n"}Save Your Time.</Text>
                    <Text style={styles.heroSub}>Order online, skip the checkout lines, and pick up when ready.</Text>
                    <TouchableOpacity
                        style={styles.heroBtn}
                        onPress={() => navigation.navigate('Shop')}
                    >
                        <Text style={styles.heroBtnText}>Shop Now</Text>
                    </TouchableOpacity>
                </View>
            </View>

            {/* Categories Section */}
            <View style={styles.section}>
                <View style={styles.sectionHeader}>
                    <Text style={styles.sectionTitle}>Shop by Category</Text>
                    <TouchableOpacity onPress={() => navigation.navigate('Shop')}>
                        <Text style={styles.seeAll}>See All</Text>
                    </TouchableOpacity>
                </View>

                <View style={styles.categoriesGrid}>
                    {categories.map((cat) => (
                        <TouchableOpacity
                            key={cat.id}
                            style={styles.categoryItem}
                            onPress={() => navigation.navigate('Shop', { category_id: parseInt(cat.id) })}
                        >
                            <View style={[styles.platform, { backgroundColor: '#E1F5FE' }]}>
                                <Text style={styles.catEmoji}>{cat.icon || '🛍️'}</Text>
                            </View>
                            <Text style={styles.categoryLabel} numberOfLines={1}>{cat.name}</Text>
                        </TouchableOpacity>
                    ))}
                </View>
            </View>

            {/* Featured Promo */}
            <View style={styles.promoCard}>
                <View style={styles.promoInfo}>
                    <Text style={styles.promoTitle}>Fresh Organic Bananas</Text>
                    <Text style={styles.promoPrice}>Only ₹9.60 / kg</Text>
                    <TouchableOpacity style={styles.promoBtn} onPress={handlePromoAddToCart}>
                        <Text style={styles.promoBtnText}>Add to Cart</Text>
                    </TouchableOpacity>
                </View>
                <Image
                    source={{ uri: 'http://192.168.37.21/Mini%20Project/images/products/bananas.jpg' }}
                    style={styles.promoImg}
                />
            </View>
        </ScrollView>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#fff',
    },
    hero: {
        backgroundColor: '#1877F2',
        padding: 24,
        paddingTop: 40,
        borderBottomLeftRadius: 30,
        borderBottomRightRadius: 30,
    },
    heroBadge: {
        backgroundColor: '#00D563',
        color: '#fff',
        alignSelf: 'flex-start',
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 20,
        fontSize: 10,
        fontWeight: 'bold',
        marginBottom: 12,
    },
    heroTitle: {
        fontSize: 28,
        fontWeight: 'bold',
        color: '#fff',
        lineHeight: 34,
    },
    heroSub: {
        color: 'rgba(255,255,255,0.8)',
        marginTop: 8,
        fontSize: 14,
        lineHeight: 20,
    },
    heroBtn: {
        backgroundColor: '#fff',
        paddingHorizontal: 20,
        paddingVertical: 10,
        borderRadius: 8,
        marginTop: 20,
        alignSelf: 'flex-start',
    },
    heroBtnText: {
        color: '#1877F2',
        fontWeight: 'bold',
    },
    section: {
        padding: 20,
    },
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 15,
    },
    sectionTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: '#333',
    },
    seeAll: {
        color: '#1877F2',
        fontWeight: '600',
    },
    categoriesGrid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        justifyContent: 'space-between',
        marginTop: 5,
    },
    categoryItem: {
        width: '24%',
        alignItems: 'center',
        marginBottom: 20,
    },
    platform: {
        width: 65,
        height: 65,
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 8,
        shadowColor: '#1877F2',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 6,
        elevation: 4,
        borderBottomWidth: 4,
        borderBottomColor: 'rgba(24, 119, 242, 0.2)',
    },
    catEmoji: {
        fontSize: 32,
    },
    categoryLabel: {
        fontSize: 12,
        fontWeight: '600',
        color: '#444',
        textAlign: 'center',
    },
    promoCard: {
        margin: 20,
        backgroundColor: '#F0F2F5',
        borderRadius: 20,
        flexDirection: 'row',
        padding: 20,
        alignItems: 'center',
        overflow: 'hidden',
    },
    promoInfo: {
        flex: 1,
    },
    promoTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#333',
    },
    promoPrice: {
        fontSize: 16,
        color: '#1877F2',
        fontWeight: '700',
        marginVertical: 8,
    },
    promoBtn: {
        backgroundColor: '#1877F2',
        paddingHorizontal: 15,
        paddingVertical: 8,
        borderRadius: 8,
        alignSelf: 'flex-start',
    },
    promoBtnText: {
        color: '#fff',
        fontWeight: 'bold',
        fontSize: 12,
    },
    promoImg: {
        width: 120,
        height: 120,
        borderRadius: 60,
        marginLeft: 10,
    },
});

export default HomeScreen;
