import React, { useState, useEffect } from 'react';
import { View, Text, FlatList, StyleSheet, ActivityIndicator, ScrollView, TouchableOpacity, Alert, TextInput, Modal } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { productService } from '../services/api';
import { useAppContext } from '../context/AppContext';
import ProductCard from '../components/ProductCard';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';
const SECONDARY_BLUE = '#F4F7FE';

const ShopScreen = ({ navigation, route }) => {
    const [products, setProducts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState(route.params?.category_id || null);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(true);

    // Filter & Sort States
    const [isFilterModalVisible, setFilterModalVisible] = useState(false);
    const [sortOrder, setSortOrder] = useState('none'); // 'none', 'price-asc', 'price-desc'
    const [inStockOnly, setInStockOnly] = useState(false);
    const [onSaleOnly, setOnSaleOnly] = useState(false);

    const { addToCart, user } = useAppContext();

    useEffect(() => {
        if (route.params?.category_id !== undefined) {
            setSelectedCategory(route.params.category_id);
        }
    }, [route.params?.category_id]);

    useEffect(() => {
        fetchCategories();
        fetchProducts();
    }, [selectedCategory]);

    const fetchCategories = async () => {
        try {
            const response = await productService.getCategories();
            const mainCats = response.data?.data || [];
            setCategories([{ id: null, name: 'All Products', icon: '🛒' }, ...mainCats]);
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
        if (user?.role === 'staff' || user?.role === 'admin') {
            Alert.alert('Stock Status', `${product.name}\n\nCurrent Stock: ${product.stock} ${product.unit}`);
            return;
        }
        addToCart(product);
    };

    // Apply client-side filtering and sorting
    const getFilteredProducts = () => {
        let result = products.filter(p =>
            p.name.toLowerCase().includes(searchQuery.toLowerCase())
        );

        if (inStockOnly) {
            result = result.filter(p => p.stock > 0);
        }

        if (onSaleOnly) {
            result = result.filter(p => p.is_sale);
        }

        if (sortOrder === 'price-asc') {
            result.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
        } else if (sortOrder === 'price-desc') {
            result.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
        }

        return result;
    };

    const filteredProducts = getFilteredProducts();

    const activeFilterCount = (sortOrder !== 'none' ? 1 : 0) + (inStockOnly ? 1 : 0) + (onSaleOnly ? 1 : 0);

    return (
        <View style={styles.container}>
            {/* Search Top Bar */}
            <View style={styles.searchHeader}>
                <View style={styles.searchBox}>
                    <Ionicons name="search" size={20} color="#A3AED0" />
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Search products..."
                        placeholderTextColor="#A3AED0"
                        value={searchQuery}
                        onChangeText={setSearchQuery}
                    />
                    {searchQuery.length > 0 && (
                        <TouchableOpacity onPress={() => setSearchQuery('')}>
                            <Ionicons name="close-circle" size={20} color="#A3AED0" />
                        </TouchableOpacity>
                    )}
                </View>
                <TouchableOpacity
                    style={[styles.filterBtn, activeFilterCount > 0 && { backgroundColor: DARK_NAVY }]}
                    onPress={() => setFilterModalVisible(true)}
                >
                    <Ionicons name="options" size={24} color="#FFF" />
                    {activeFilterCount > 0 && (
                        <View style={styles.activeFilterBadge}>
                            <Text style={styles.activeFilterText}>{activeFilterCount}</Text>
                        </View>
                    )}
                </TouchableOpacity>
            </View>

            {/* Category List */}
            <View style={styles.categoryContainer}>
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.catScrollPadding}>
                    {categories.map((cat) => (
                        <TouchableOpacity
                            key={cat.id || 'all'}
                            style={styles.shopCategoryItem}
                            onPress={() => setSelectedCategory(cat.id)}
                            activeOpacity={0.7}
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
                    <ActivityIndicator size="large" color={BLUE} />
                </View>
            ) : (
                <>
                    <View style={styles.toolbar}>
                        <Text style={styles.showingText}>
                            Showing <Text style={{ fontWeight: '900' }}>{filteredProducts.length}</Text> result{filteredProducts.length !== 1 ? 's' : ''}
                        </Text>
                    </View>
                    <FlatList
                        data={filteredProducts}
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
                        ListEmptyComponent={
                            <View style={styles.emptyWrap}>
                                <Text style={styles.emptyEmoji}>🛒</Text>
                                <Text style={styles.emptyTitle}>No products found</Text>
                                <Text style={styles.emptySub}>Try adjusting your search or filters.</Text>
                            </View>
                        }
                    />
                </>
            )}

            {/* Filter Modal */}
            <Modal
                animationType="slide"
                transparent={true}
                visible={isFilterModalVisible}
                onRequestClose={() => setFilterModalVisible(false)}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Filters & Sorting</Text>
                            <TouchableOpacity onPress={() => setFilterModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#A3AED0" />
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.filterSectionTitle}>Sort By</Text>
                        <View style={styles.filterOptionsContainer}>
                            <TouchableOpacity
                                style={[styles.filterOptionBtn, sortOrder === 'none' && styles.filterOptionBtnActive]}
                                onPress={() => setSortOrder('none')}
                            >
                                <Text style={[styles.filterOptionText, sortOrder === 'none' && styles.filterOptionTextActive]}>Featured</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[styles.filterOptionBtn, sortOrder === 'price-asc' && styles.filterOptionBtnActive]}
                                onPress={() => setSortOrder('price-asc')}
                            >
                                <Text style={[styles.filterOptionText, sortOrder === 'price-asc' && styles.filterOptionTextActive]}>Price: Low to High</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[styles.filterOptionBtn, sortOrder === 'price-desc' && styles.filterOptionBtnActive]}
                                onPress={() => setSortOrder('price-desc')}
                            >
                                <Text style={[styles.filterOptionText, sortOrder === 'price-desc' && styles.filterOptionTextActive]}>Price: High to Low</Text>
                            </TouchableOpacity>
                        </View>

                        <Text style={styles.filterSectionTitle}>Availability & Promotions</Text>
                        <View style={styles.toggleRow}>
                            <Text style={styles.toggleLabel}>In Stock Only</Text>
                            <TouchableOpacity
                                style={[styles.toggleBtn, inStockOnly && styles.toggleBtnActive]}
                                onPress={() => setInStockOnly(!inStockOnly)}
                            >
                                <View style={[styles.toggleCircle, inStockOnly && styles.toggleCircleActive]} />
                            </TouchableOpacity>
                        </View>

                        <View style={styles.toggleRow}>
                            <Text style={styles.toggleLabel}>On Sale Only</Text>
                            <TouchableOpacity
                                style={[styles.toggleBtn, onSaleOnly && styles.toggleBtnActive]}
                                onPress={() => setOnSaleOnly(!onSaleOnly)}
                            >
                                <View style={[styles.toggleCircle, onSaleOnly && styles.toggleCircleActive]} />
                            </TouchableOpacity>
                        </View>

                        <View style={styles.modalFooterActions}>
                            <TouchableOpacity
                                style={styles.clearFiltersBtn}
                                onPress={() => {
                                    setSortOrder('none');
                                    setInStockOnly(false);
                                    setOnSaleOnly(false);
                                }}
                            >
                                <Text style={styles.clearFiltersText}>Clear All</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={styles.applyFiltersBtn}
                                onPress={() => setFilterModalVisible(false)}
                            >
                                <Text style={styles.applyFiltersText}>Apply Filters</Text>
                            </TouchableOpacity>
                        </View>
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
    /* Search Header */
    searchHeader: {
        flexDirection: 'row',
        padding: 16,
        backgroundColor: '#fff',
        gap: 12,
        alignItems: 'center',
    },
    searchBox: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#F4F7FE',
        height: 48,
        borderRadius: 14,
        paddingHorizontal: 14,
        gap: 8,
    },
    searchInput: {
        flex: 1,
        fontSize: 15,
        color: DARK_NAVY,
        fontWeight: '600',
    },
    filterBtn: {
        width: 48,
        height: 48,
        backgroundColor: BLUE,
        borderRadius: 14,
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 6,
        elevation: 4,
    },
    activeFilterBadge: {
        position: 'absolute',
        top: -6,
        right: -6,
        backgroundColor: '#00D563',
        width: 20,
        height: 20,
        borderRadius: 10,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
        borderColor: '#fff',
    },
    activeFilterText: {
        color: '#fff',
        fontSize: 10,
        fontWeight: 'bold',
    },

    /* Category List */
    categoryContainer: {
        paddingVertical: 12,
        backgroundColor: '#fff',
        borderBottomWidth: 1,
        borderBottomColor: '#F4F7FE',
    },
    catScrollPadding: {
        paddingHorizontal: 16,
    },
    shopCategoryItem: {
        alignItems: 'center',
        marginRight: 18,
        width: 64,
    },
    shopPlatform: {
        width: 54,
        height: 54,
        borderRadius: 18,
        backgroundColor: '#F8F9FE',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 8,
        borderWidth: 1.5,
        borderColor: 'transparent',
    },
    selectedPlatform: {
        backgroundColor: '#fff',
        borderColor: BLUE,
        borderBottomWidth: 4,
        borderBottomColor: BLUE,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 4,
        elevation: 2,
    },
    shopCatIcon: {
        fontSize: 24,
    },
    shopCatText: {
        fontSize: 10,
        color: '#A3AED0',
        fontWeight: '700',
        textAlign: 'center',
    },
    selectedCatText: {
        color: BLUE,
        fontWeight: '900',
    },

    toolbar: {
        paddingHorizontal: 16,
        paddingTop: 16,
        paddingBottom: 4,
    },
    showingText: {
        fontSize: 13,
        color: '#707EAE',
        fontWeight: '600',
    },

    productList: {
        padding: 8,
        paddingBottom: 40,
    },
    center: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },

    /* Empty State */
    emptyWrap: {
        paddingTop: 60,
        alignItems: 'center',
    },
    emptyEmoji: {
        fontSize: 48,
        marginBottom: 16,
    },
    emptyTitle: {
        fontSize: 18,
        fontWeight: '900',
        color: DARK_NAVY,
        marginBottom: 8,
    },
    emptySub: {
        fontSize: 14,
        color: '#A3AED0',
    },

    /* Modal Styles */
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(27, 37, 89, 0.7)',
        justifyContent: 'flex-end',
    },
    modalContent: {
        backgroundColor: '#fff',
        borderTopLeftRadius: 30,
        borderTopRightRadius: 30,
        padding: 24,
        minHeight: 400,
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 24,
    },
    modalTitle: {
        fontSize: 20,
        fontWeight: '900',
        color: DARK_NAVY,
    },
    filterSectionTitle: {
        fontSize: 15,
        fontWeight: '800',
        color: DARK_NAVY,
        marginBottom: 12,
        marginTop: 10,
    },
    filterOptionsContainer: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: 10,
        marginBottom: 20,
    },
    filterOptionBtn: {
        paddingHorizontal: 16,
        paddingVertical: 10,
        borderRadius: 12,
        backgroundColor: SECONDARY_BLUE,
        borderWidth: 1,
        borderColor: 'transparent',
    },
    filterOptionBtnActive: {
        backgroundColor: '#fff',
        borderColor: BLUE,
    },
    filterOptionText: {
        fontSize: 14,
        fontWeight: '700',
        color: '#707EAE',
    },
    filterOptionTextActive: {
        color: BLUE,
    },
    toggleRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 12,
        borderBottomWidth: 1,
        borderBottomColor: SECONDARY_BLUE,
    },
    toggleLabel: {
        fontSize: 15,
        fontWeight: '600',
        color: DARK_NAVY,
    },
    toggleBtn: {
        width: 50,
        height: 28,
        borderRadius: 14,
        backgroundColor: '#E2E8F0',
        padding: 2,
    },
    toggleBtnActive: {
        backgroundColor: '#00D563',
    },
    toggleCircle: {
        width: 24,
        height: 24,
        borderRadius: 12,
        backgroundColor: '#fff',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 2,
        elevation: 2,
    },
    toggleCircleActive: {
        transform: [{ translateX: 22 }],
    },
    modalFooterActions: {
        flexDirection: 'row',
        marginTop: 30,
        gap: 16,
    },
    clearFiltersBtn: {
        flex: 1,
        paddingVertical: 16,
        borderRadius: 16,
        backgroundColor: SECONDARY_BLUE,
        alignItems: 'center',
    },
    clearFiltersText: {
        fontSize: 15,
        fontWeight: 'bold',
        color: '#707EAE',
    },
    applyFiltersBtn: {
        flex: 2,
        paddingVertical: 16,
        borderRadius: 16,
        backgroundColor: BLUE,
        alignItems: 'center',
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
        elevation: 4,
    },
    applyFiltersText: {
        fontSize: 15,
        fontWeight: 'bold',
        color: '#fff',
    }
});

export default ShopScreen;
