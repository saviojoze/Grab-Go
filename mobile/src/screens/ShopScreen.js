import React, { useState, useEffect } from 'react';
import { View, Text, FlatList, StyleSheet, ActivityIndicator, ScrollView, TouchableOpacity } from 'react-native';
import { productService } from '../services/api';
import { useAppContext } from '../context/AppContext';
import ProductCard from '../components/ProductCard';

const ShopScreen = ({ navigation }) => {
    const [products, setProducts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState(null);
    const [loading, setLoading] = useState(true);
    const { addToCart } = useAppContext();

    useEffect(() => {
        fetchCategories();
        fetchProducts();
    }, [selectedCategory]);

    const fetchCategories = async () => {
        try {
            const response = await productService.getCategories();
            const allCats = response.data?.data || [];
            // Filter for top-level categories only
            const mainCats = allCats.filter(cat => cat.parent_id === null);
            setCategories([{ id: null, name: 'All', icon: '🛍️' }, ...mainCats]);
        } catch (error) {
            console.error('Error fetching categories:', error);
        }
    };

    const fetchProducts = async () => {
        setLoading(true);
        try {
            const response = await productService.getProducts({
                category_id: selectedCategory
            });
            setProducts(response.data?.data || []);
        } catch (error) {
            console.error('Error fetching products:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleAddToCart = (product) => {
        addToCart(product);
    };

    return (
        <View style={styles.container}>
            {/* Category List */}
            <View style={styles.categoryContainer}>
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.catScrollPadding}>
                    {categories.map((cat) => (
                        <TouchableOpacity
                            key={cat.id || 'all'}
                            style={styles.shopCategoryItem}
                            onPress={() => setSelectedCategory(cat.id)}
                        >
                            <View style={[
                                styles.shopPlatform,
                                selectedCategory === cat.id && styles.selectedPlatform
                            ]}>
                                <Text style={styles.shopCatIcon}>{cat.icon || '🛍️'}</Text>
                            </View>
                            <Text style={[
                                styles.shopCatText,
                                selectedCategory === cat.id && styles.selectedCatText
                            ]} numberOfLines={1}>{cat.name}</Text>
                        </TouchableOpacity>
                    ))}
                </ScrollView>
            </View>

            {loading ? (
                <View style={styles.center}>
                    <ActivityIndicator size="large" color="#1877F2" />
                </View>
            ) : (
                <FlatList
                    data={products}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={({ item }) => (
                        <ProductCard
                            product={item}
                            onAddToCart={handleAddToCart}
                            onPress={() => { }} // Navigate to detail
                        />
                    )}
                    numColumns={2}
                    contentContainerStyle={styles.productList}
                />
            )}
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#f8f9fa',
    },
    categoryContainer: {
        paddingVertical: 15,
        backgroundColor: '#fff',
        borderBottomWidth: 1,
        borderBottomColor: '#f1f3f5',
    },
    catScrollPadding: {
        paddingHorizontal: 10,
    },
    shopCategoryItem: {
        alignItems: 'center',
        marginHorizontal: 10,
        width: 65,
    },
    shopPlatform: {
        width: 50,
        height: 50,
        borderRadius: 15,
        backgroundColor: '#f8f9fa',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 6,
        borderWidth: 1,
        borderColor: '#e9ecef',
    },
    selectedPlatform: {
        backgroundColor: '#E1F5FE',
        borderColor: '#1877F2',
        borderBottomWidth: 3,
        borderBottomColor: '#1877F2',
    },
    shopCatIcon: {
        fontSize: 24,
    },
    shopCatText: {
        fontSize: 11,
        color: '#666',
        fontWeight: '500',
        textAlign: 'center',
    },
    selectedCatText: {
        color: '#1877F2',
        fontWeight: 'bold',
    },
    productList: {
        padding: 8,
    },
    center: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
});

export default ShopScreen;
