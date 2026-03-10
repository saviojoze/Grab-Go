import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Dimensions, FlatList, ActivityIndicator, TextInput, Modal, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { productService } from '../services/api';
import { useAppContext } from '../context/AppContext';
import ProductCard from '../components/ProductCard';

const { width } = Dimensions.get('window');

const BLUE = '#1877F2';
const GREEN = '#00D563';
const ORANGE = '#ea580c';
const DARK_NAVY = '#1B2559';
const SECONDARY_BLUE = '#F4F7FE';

const BANNER_DATA = [
    {
        id: '1',
        tag: '🛒 Smart Shopping',
        title: 'Browse Online,\nPick Up In Store.',
        subtitle: 'Shop from our full range online, then collect your order at the counter — no waiting.',
        bg: ['#1a4d1e', '#2d7a32'],
        solidBg: '#2d7a32',
        cta: 'Start Shopping',
        badge: 'QUICK\nPICKUP',
        badgeColor: '#1a4d1e',
        emoji: '🏪'
    },
    {
        id: '2',
        tag: '⚡ Skip the Line',
        title: 'Order Ahead,\nSkip the Queue.',
        subtitle: 'Place your order in advance, walk straight to the counter, and collect your items.',
        bg: ['#1e3a5c', '#1877f2'],
        solidBg: '#1877F2',
        cta: 'Order Now',
        badge: 'NO\nQUEUES',
        badgeColor: '#ea580c',
        emoji: '⏱️'
    },
    {
        id: '3',
        tag: '🎉 Weekend Deals',
        title: 'Fill Your Cart\n& Save Big.',
        subtitle: 'Grab more, pay less! Enjoy up to 25% off on your in-store pickup orders this weekend.',
        bg: ['#7c2d12', '#ea580c'],
        solidBg: '#ea580c',
        cta: 'View Offers',
        badge: 'UP TO\n25% OFF',
        badgeColor: '#92400e',
        emoji: '🧃'
    }
];


const HomeScreen = ({ navigation }) => {
    const { addToCart, user } = useAppContext();
    const [activeBanner, setActiveBanner] = useState(0);

    // Products & Categories States
    const [products, setProducts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(true);

    // Filter & Sort States
    const [isFilterModalVisible, setFilterModalVisible] = useState(false);
    const [sortOrder, setSortOrder] = useState('none');
    const [inStockOnly, setInStockOnly] = useState(false);
    const [onSaleOnly, setOnSaleOnly] = useState(false);

    useEffect(() => {
        fetchCategories();
    }, []);

    useEffect(() => {
        fetchProducts();
    }, [selectedCategory]);

    const fetchCategories = async () => {
        try {
            const response = await productService.getCategories();
            const mainCats = (response.data?.data || []).sort((a, b) => parseInt(a.display_order) - parseInt(b.display_order));
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

    const handleScroll = (event) => {
        const slideSize = event.nativeEvent.layoutMeasurement.width;
        const offset = event.nativeEvent.contentOffset.x;
        setActiveBanner(Math.round(offset / slideSize));
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
            result = result.filter(p => (Number(p.stock) || 0) > 0);
        }

        if (onSaleOnly) {
            result = result.filter(p => (p.is_sale == 1 || p.is_sale == '1' || p.is_sale === true));
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

    const renderHeader = () => (
        <View style={{ paddingBottom: 10, marginHorizontal: -8, marginTop: -8 }}>
            {/* ── TOP SECONDARY TABS ── */}
            <View style={styles.topTabsBar}>
                <TouchableOpacity style={styles.topTabActive}>
                    <Text style={styles.topTabTextActive}>Shop</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.topTab} onPress={() => navigation.navigate('Locations')}>
                    <Text style={styles.topTabText}>Locations</Text>
                </TouchableOpacity>
            </View>
            <View style={{ height: 4, backgroundColor: BLUE }} />

            {/* ── BANNERS ── */}
            <View style={styles.bannerContainer}>
                <ScrollView
                    horizontal
                    pagingEnabled
                    showsHorizontalScrollIndicator={false}
                    onMomentumScrollEnd={handleScroll}
                >
                    {BANNER_DATA.map((banner, index) => (
                        <View key={banner.id} style={[styles.bannerSlide, { backgroundColor: banner.solidBg, paddingTop: 60 }]}>
                            <View style={styles.decCircleTop} />
                            <View style={styles.decCircleBottom} />
                            <View style={styles.bannerContent}>
                                <View style={styles.bannerTextCol}>
                                    <View style={styles.tagWrap}>
                                        <Text style={styles.tagText}>{banner.tag}</Text>
                                    </View>
                                    <Text style={styles.bannerHeadline}>{banner.title}</Text>
                                    <Text style={styles.bannerSub}>{banner.subtitle}</Text>
                                    <TouchableOpacity style={styles.ctaBtn} activeOpacity={0.8}>
                                        <Text style={[styles.ctaText, { color: banner.solidBg }]}>{banner.cta} →</Text>
                                    </TouchableOpacity>
                                </View>
                                <View style={styles.bannerVisualCol}>
                                    <View style={styles.emojiCircle}>
                                        <Text style={styles.mainEmoji}>{banner.emoji}</Text>
                                    </View>
                                    <View style={styles.badgeFloat}>
                                        <Text style={[styles.badgeFloatText, { color: banner.badgeColor }]}>{banner.badge}</Text>
                                    </View>
                                </View>
                            </View>
                        </View>
                    ))}
                </ScrollView>
                <View style={styles.dotsRow}>
                    {BANNER_DATA.map((_, i) => (
                        <View key={i} style={[styles.dot, activeBanner === i && styles.dotActive]} />
                    ))}
                </View>
            </View>

            {/* ── SEARCH & FILTER ROW ── */}
            <View style={styles.searchHeader}>
                <View style={styles.searchBox}>
                    <Ionicons name="search" size={20} color="#A3AED0" />
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Search our store..."
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

            {/* ── CATEGORIES ── */}
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

            <View style={styles.toolbar}>
                <Text style={styles.sectionTitle}>
                    {selectedCategory === null ? 'All Products' : categories.find(c => c.id === selectedCategory)?.name || 'Products'}
                </Text>
                <Text style={styles.showingText}>
                    Showing <Text style={{ fontWeight: '900' }}>{filteredProducts.length}</Text> result{filteredProducts.length !== 1 ? 's' : ''}
                </Text>
            </View>
        </View>
    );

    return (
        <View style={styles.container}>
            {loading ? (
                <>
                    {renderHeader()}
                    <View style={styles.center}>
                        <ActivityIndicator size="large" color={BLUE} />
                    </View>
                </>
            ) : (
                <FlatList
                    data={filteredProducts}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={({ item }) => (
                        <ProductCard
                            product={item}
                            onAddToCart={handleAddToCart}
                            onPress={() => { }}
                        />
                    )}
                    numColumns={2}
                    ListHeaderComponent={renderHeader()}
                    contentContainerStyle={styles.productList}
                    showsVerticalScrollIndicator={false}
                    ListEmptyComponent={
                        <View style={styles.emptyWrap}>
                            <Text style={styles.emptyEmoji}>🛒</Text>
                            <Text style={styles.emptyTitle}>No products found</Text>
                            <Text style={styles.emptySub}>Try adjusting your search or filters.</Text>
                        </View>
                    }
                />
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
    /* ── Top Secondary Tabs ── */
    topTabsBar: {
        flexDirection: 'row',
        backgroundColor: '#fff',
        paddingVertical: 12,
        paddingHorizontal: 16,
        gap: 20,
        alignItems: 'center',
        borderBottomWidth: 1,
        borderBottomColor: '#F4F7FE',
        zIndex: 10,
    },
    topTab: {
        paddingVertical: 6,
        paddingHorizontal: 10,
    },
    topTabActive: {
        paddingVertical: 8,
        paddingHorizontal: 14,
        backgroundColor: '#f6fdf9',
        borderRadius: 10,
    },
    topTabText: {
        fontSize: 15,
        fontWeight: '700',
        color: '#707EAE',
    },
    topTabTextActive: {
        fontSize: 15,
        fontWeight: '800',
        color: '#00D563',
    },
    /* ── Banners ── */
    bannerContainer: {
        position: 'relative',
        backgroundColor: '#fff',
        paddingBottom: 24,
    },
    bannerSlide: {
        width: width,
        paddingTop: 30,
        paddingBottom: 40,
        paddingHorizontal: 24,
        overflow: 'hidden',
        position: 'relative',
        borderBottomLeftRadius: 24,
        borderBottomRightRadius: 24,
    },
    decCircleTop: {
        position: 'absolute',
        width: 200, height: 200,
        borderRadius: 100,
        backgroundColor: 'rgba(255,255,255,0.06)',
        top: -60, right: -40,
    },
    decCircleBottom: {
        position: 'absolute',
        width: 150, height: 150,
        borderRadius: 75,
        backgroundColor: 'rgba(255,255,255,0.04)',
        bottom: -30, left: -20,
    },
    bannerContent: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        zIndex: 2,
    },
    bannerTextCol: {
        flex: 1,
        paddingRight: 15,
    },
    tagWrap: {
        backgroundColor: 'rgba(255,255,255,0.2)',
        paddingHorizontal: 10,
        paddingVertical: 4,
        borderRadius: 12,
        alignSelf: 'flex-start',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.3)',
        marginBottom: 12,
    },
    tagText: {
        color: '#fff',
        fontSize: 10,
        fontWeight: '800',
        textTransform: 'uppercase',
    },
    bannerHeadline: {
        color: '#fff',
        fontSize: 26,
        fontWeight: '900',
        lineHeight: 32,
        marginBottom: 8,
    },
    bannerSub: {
        color: 'rgba(255,255,255,0.85)',
        fontSize: 12.5,
        lineHeight: 18,
        marginBottom: 16,
    },
    ctaBtn: {
        backgroundColor: '#fff',
        paddingHorizontal: 16,
        paddingVertical: 10,
        borderRadius: 20,
        alignSelf: 'flex-start',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.15,
        shadowRadius: 8,
        elevation: 4,
    },
    ctaText: {
        fontWeight: '800',
        fontSize: 13,
    },
    bannerVisualCol: {
        width: 90,
        height: 90,
        position: 'relative',
        alignItems: 'center',
        justifyContent: 'center',
    },
    emojiCircle: {
        width: 70, height: 70,
        borderRadius: 35,
        backgroundColor: 'rgba(255,255,255,0.15)',
        alignItems: 'center',
        justifyContent: 'center',
    },
    mainEmoji: {
        fontSize: 36,
    },
    badgeFloat: {
        position: 'absolute',
        top: -10, right: -15,
        backgroundColor: '#fff',
        width: 50, height: 50,
        borderRadius: 25,
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 5,
        elevation: 5,
    },
    badgeFloatText: {
        fontSize: 8,
        fontWeight: '900',
        textAlign: 'center',
        lineHeight: 10,
    },
    dotsRow: {
        position: 'absolute',
        bottom: 12,
        width: '100%',
        flexDirection: 'row',
        justifyContent: 'center',
        gap: 6,
    },
    dot: {
        width: 6, height: 6,
        borderRadius: 3,
        backgroundColor: 'rgba(0,0,0,0.1)',
    },
    dotActive: {
        backgroundColor: BLUE,
        width: 14,
    },

    /* Search Header */
    searchHeader: {
        flexDirection: 'row',
        paddingHorizontal: 16,
        paddingTop: 16,
        paddingBottom: 8,
        backgroundColor: '#F8F9FE',
        gap: 12,
        alignItems: 'center',
    },
    searchBox: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fff',
        height: 48,
        borderRadius: 14,
        paddingHorizontal: 14,
        gap: 8,
        borderWidth: 1,
        borderColor: '#E9EDF7',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.02,
        shadowRadius: 4,
        elevation: 1,
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
        backgroundColor: GREEN,
        width: 20,
        height: 20,
        borderRadius: 10,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 2,
        borderColor: '#F8F9FE',
    },
    activeFilterText: {
        color: '#fff',
        fontSize: 10,
        fontWeight: 'bold',
    },

    /* Category List */
    categoryContainer: {
        paddingVertical: 12,
        backgroundColor: '#F8F9FE',
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
        backgroundColor: '#fff',
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
        paddingBottom: 4,
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-end',
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: '900',
        color: DARK_NAVY,
    },
    showingText: {
        fontSize: 13,
        color: '#707EAE',
        fontWeight: '600',
        marginBottom: 2,
    },

    productList: {
        padding: 8,
        paddingBottom: 100, // To avoid bottom nav covering
    },
    center: {
        paddingVertical: 40,
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
        backgroundColor: GREEN,
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

export default HomeScreen;
