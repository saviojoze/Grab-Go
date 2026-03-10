import React, { useState, useCallback } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity,
    ActivityIndicator, TextInput, Modal, Alert, Image,
    KeyboardAvoidingView, Platform, ScrollView, RefreshControl
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { useFocusEffect } from '@react-navigation/native';
import { useAppContext } from '../context/AppContext';
import { productService } from '../services/api';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';
const BASE_URL = 'http://192.168.137.1/Mini%20Project/';

const InventoryScreen = () => {
    const { user } = useAppContext();
    const [products, setProducts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch] = useState('');
    const [catFilter, setCatFilter] = useState(null);
    const [stockFilter, setStockFilter] = useState('all');
    const [saving, setSaving] = useState(false);
    const [uploadingImage, setUploadingImage] = useState(false);

    // Modal state
    const [modalVisible, setModalVisible] = useState(false);
    const [editingProduct, setEditingProduct] = useState(null);

    // Form state
    const [form, setForm] = useState({
        name: '', description: '', price: '', stock: '',
        unit: 'units', category_id: '', image_url: '', localImageUri: null
    });

    useFocusEffect(
        useCallback(() => { loadData(); }, [])
    );

    const loadData = async (isRefreshing = false) => {
        if (isRefreshing) setRefreshing(true);
        else setLoading(true);
        try {
            const [productsRes, catsRes] = await Promise.all([
                productService.getAllProducts(),
                productService.getCategories(),
            ]);
            setProducts(productsRes.data?.data || []);
            setCategories(catsRes.data?.data || []);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const getImageUrl = (imgUrl) => {
        if (!imgUrl) return null;
        if (imgUrl.startsWith('http')) return imgUrl;
        return `${BASE_URL}${imgUrl}`;
    };

    const getStockColor = (stock) => {
        if (stock === 0) return '#EE5D50';
        if (stock < 10) return '#ea580c';
        return '#00D563';
    };

    const getStockLabel = (stock) => {
        if (stock === 0) return 'Out of Stock';
        if (stock < 10) return 'Low Stock';
        return 'In Stock';
    };

    // ─── Image Picker ────────────────────────────────────────────────────────────
    const pickImage = async (useCamera = false) => {
        try {
            if (useCamera) {
                const { status } = await ImagePicker.requestCameraPermissionsAsync();
                if (status !== 'granted') {
                    Alert.alert('Permission Needed', 'Camera permission is required to take a photo.');
                    return;
                }
            } else {
                const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
                if (status !== 'granted') {
                    Alert.alert('Permission Needed', 'Photo library permission is required.');
                    return;
                }
            }

            const result = useCamera
                ? await ImagePicker.launchCameraAsync({
                    mediaTypes: ImagePicker.MediaTypeOptions.Images,
                    quality: 0.8,
                    allowsEditing: true,
                    aspect: [1, 1],
                })
                : await ImagePicker.launchImageLibraryAsync({
                    mediaTypes: ImagePicker.MediaTypeOptions.Images,
                    quality: 0.8,
                    allowsEditing: true,
                    aspect: [1, 1],
                });

            if (!result.canceled && result.assets?.length > 0) {
                const uri = result.assets[0].uri;
                setForm(f => ({ ...f, localImageUri: uri }));
            }
        } catch (err) {
            Alert.alert('Error', 'Failed to pick image: ' + err.message);
        }
    };

    const showImageOptions = () => {
        Alert.alert(
            'Product Image',
            'Choose how to add an image',
            [
                { text: 'Take Photo', onPress: () => pickImage(true) },
                { text: 'Choose from Gallery', onPress: () => pickImage(false) },
                form.localImageUri || form.image_url
                    ? { text: 'Remove Image', style: 'destructive', onPress: () => setForm(f => ({ ...f, localImageUri: null, image_url: '' })) }
                    : null,
                { text: 'Cancel', style: 'cancel' },
            ].filter(Boolean)
        );
    };

    // ─── Modal Open ──────────────────────────────────────────────────────────────
    const openAddModal = () => {
        setEditingProduct(null);
        setForm({ name: '', description: '', price: '', stock: '', unit: 'units', category_id: categories[0]?.id?.toString() || '', image_url: '', localImageUri: null });
        setModalVisible(true);
    };

    const openEditModal = (product) => {
        setEditingProduct(product);
        setForm({
            name: product.name || '',
            description: product.description || '',
            price: product.price?.toString() || '',
            stock: product.stock?.toString() || '',
            unit: product.unit || 'units',
            category_id: product.category_id?.toString() || '',
            image_url: product.image_url || '',
            localImageUri: null,
        });
        setModalVisible(true);
    };

    // ─── Save ────────────────────────────────────────────────────────────────────
    const handleSave = async () => {
        if (!form.name.trim() || !form.price) {
            Alert.alert('Error', 'Name and price are required');
            return;
        }
        setSaving(true);
        try {
            let finalImageUrl = form.image_url;

            // Upload new image if selected
            if (form.localImageUri) {
                setUploadingImage(true);
                try {
                    const uploadRes = await productService.uploadImage(user.id, form.localImageUri);
                    finalImageUrl = uploadRes.data?.data?.image_url || finalImageUrl;
                } catch (uploadErr) {
                    Alert.alert('Image Upload Failed', 'Could not upload image. The product will be saved without it.');
                } finally {
                    setUploadingImage(false);
                }
            }

            const payload = {
                name: form.name.trim(),
                description: form.description.trim(),
                price: parseFloat(form.price),
                stock: parseInt(form.stock) || 0,
                unit: form.unit.trim() || 'units',
                category_id: parseInt(form.category_id) || 0,
                image_url: finalImageUrl,
            };

            if (editingProduct) {
                await productService.updateProduct(user.id, { id: editingProduct.id, ...payload });
                Alert.alert('✅ Updated', 'Product updated successfully!');
            } else {
                await productService.createProduct(user.id, payload);
                Alert.alert('✅ Added', 'Product added successfully!');
            }
            setModalVisible(false);
            loadData();
        } catch (e) {
            Alert.alert('Error', e.response?.data?.message || 'Failed to save product');
        } finally {
            setSaving(false);
        }
    };

    // ─── Delete ──────────────────────────────────────────────────────────────────
    const handleDelete = (product) => {
        if (user.role !== 'admin') {
            Alert.alert('Restricted', 'Only admins can delete products.');
            return;
        }
        Alert.alert(
            'Delete Product',
            `Delete "${product.name}"?\n\nThis cannot be undone.`,
            [
                { text: 'Cancel', style: 'cancel' },
                {
                    text: 'Delete', style: 'destructive',
                    onPress: async () => {
                        try {
                            await productService.deleteProduct(user.id, product.id);
                            Alert.alert('Deleted', `"${product.name}" was removed.`);
                            loadData();
                        } catch (e) {
                            Alert.alert('Error', 'Failed to delete product');
                        }
                    }
                }
            ]
        );
    };

    // ─── Filter ──────────────────────────────────────────────────────────────────
    const filtered = products.filter(p => {
        const matchSearch = p.name.toLowerCase().includes(search.toLowerCase()) ||
            (p.description || '').toLowerCase().includes(search.toLowerCase());
        const matchCat = !catFilter || p.category_id === catFilter;
        const matchStock =
            stockFilter === 'all' ||
            (stockFilter === 'out' && p.stock === 0) ||
            (stockFilter === 'low' && p.stock > 0 && p.stock < 10);
        return matchSearch && matchCat && matchStock;
    });

    // ─── Render Product Row ──────────────────────────────────────────────────────
    const renderProduct = ({ item }) => (
        <View style={styles.productRow}>
            {item.image_url ? (
                <Image source={{ uri: getImageUrl(item.image_url) }} style={styles.productImg} />
            ) : (
                <View style={[styles.productImg, styles.productImgPlaceholder]}>
                    <Ionicons name="cube-outline" size={22} color="#A3AED0" />
                </View>
            )}

            <View style={styles.productInfo}>
                <Text style={styles.productName} numberOfLines={1}>{item.name}</Text>
                <Text style={styles.productDesc} numberOfLines={1}>{item.description || '—'}</Text>
                <View style={styles.catBadge}>
                    <Text style={styles.catBadgeText}>{item.category_name || 'Uncategorized'}</Text>
                </View>
            </View>

            <View style={styles.productRight}>
                <Text style={styles.productPrice}>₹{parseFloat(item.price).toFixed(0)}</Text>
                <View style={[styles.stockBadge, { backgroundColor: getStockColor(item.stock) + '15' }]}>
                    <Text style={[styles.stockText, { color: getStockColor(item.stock) }]}>
                        {item.stock} {item.unit || 'units'}
                    </Text>
                </View>
                <Text style={[styles.stockLabel, { color: getStockColor(item.stock) }]}>
                    {getStockLabel(item.stock)}
                </Text>
            </View>

            <View style={styles.productActions}>
                <TouchableOpacity style={styles.editBtn} onPress={() => openEditModal(item)}>
                    <Ionicons name="create-outline" size={18} color={BLUE} />
                </TouchableOpacity>
                {user.role === 'admin' && (
                    <TouchableOpacity style={styles.deleteBtn} onPress={() => handleDelete(item)}>
                        <Ionicons name="trash-outline" size={18} color="#EE5D50" />
                    </TouchableOpacity>
                )}
            </View>
        </View>
    );

    if (loading) {
        return <View style={styles.center}><ActivityIndicator size="large" color={BLUE} /></View>;
    }

    // ─── Current image to show in form ───────────────────────────────────────────
    const currentImageUri = form.localImageUri || (form.image_url ? getImageUrl(form.image_url) : null);

    return (
        <View style={styles.container}>
            {/* Header */}
            <View style={styles.header}>
                <View>
                    <Text style={styles.title}>Product Inventory</Text>
                    <Text style={styles.sub}>{filtered.length} of {products.length} products</Text>
                </View>
                <TouchableOpacity style={styles.addBtn} onPress={openAddModal}>
                    <Ionicons name="add" size={20} color="#fff" />
                    <Text style={styles.addBtnText}>Add</Text>
                </TouchableOpacity>
            </View>

            {/* Search Bar */}
            <View style={styles.searchBar}>
                <Ionicons name="search-outline" size={18} color="#A3AED0" />
                <TextInput
                    style={styles.searchInput}
                    placeholder="Search products..."
                    placeholderTextColor="#A3AED0"
                    value={search}
                    onChangeText={setSearch}
                />
                {search.length > 0 && (
                    <TouchableOpacity onPress={() => setSearch('')}>
                        <Ionicons name="close-circle" size={18} color="#A3AED0" />
                    </TouchableOpacity>
                )}
            </View>

            {/* Stock Filter Chips */}
            <View style={styles.filtersRow}>
                {[
                    { key: 'all', label: 'All Stock' },
                    { key: 'low', label: '⚠️ Low Stock' },
                    { key: 'out', label: '🔴 Out of Stock' },
                ].map(f => (
                    <TouchableOpacity
                        key={f.key}
                        style={[styles.chip, stockFilter === f.key && styles.chipActive]}
                        onPress={() => setStockFilter(f.key)}
                    >
                        <Text style={[styles.chipText, stockFilter === f.key && styles.chipTextActive]}>
                            {f.label}
                        </Text>
                    </TouchableOpacity>
                ))}
            </View>

            {/* Category Filter */}
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.catRow}>
                <TouchableOpacity
                    style={[styles.catChip, !catFilter && styles.catChipActive]}
                    onPress={() => setCatFilter(null)}
                >
                    <Text style={[styles.catChipText, !catFilter && styles.catChipTextActive]}>All</Text>
                </TouchableOpacity>
                {categories.map(cat => (
                    <TouchableOpacity
                        key={cat.id}
                        style={[styles.catChip, catFilter === cat.id && styles.catChipActive]}
                        onPress={() => setCatFilter(catFilter === cat.id ? null : cat.id)}
                    >
                        <Text style={[styles.catChipText, catFilter === cat.id && styles.catChipTextActive]}>
                            {cat.icon || ''} {cat.name}
                        </Text>
                    </TouchableOpacity>
                ))}
            </ScrollView>

            {/* Product List */}
            <FlatList
                data={filtered}
                keyExtractor={(item) => item.id.toString()}
                renderItem={renderProduct}
                contentContainerStyle={styles.list}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => loadData(true)} colors={[BLUE]} />}
                ListEmptyComponent={
                    <View style={styles.empty}>
                        <Ionicons name="cube-outline" size={60} color="#A3AED0" />
                        <Text style={styles.emptyText}>No products found</Text>
                    </View>
                }
            />

            {/* ─── Add / Edit Modal ─── */}
            <Modal visible={modalVisible} animationType="slide" transparent onRequestClose={() => setModalVisible(false)}>
                <KeyboardAvoidingView
                    style={styles.modalOverlay}
                    behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                >
                    <View style={styles.modalBox}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>{editingProduct ? 'Edit Product' : 'Add New Product'}</Text>
                            <TouchableOpacity onPress={() => setModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#A3AED0" />
                            </TouchableOpacity>
                        </View>

                        <ScrollView showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">

                            {/* ── Image Picker Section ── */}
                            <Text style={styles.fieldLabel}>Product Image</Text>
                            <TouchableOpacity style={styles.imagePicker} onPress={showImageOptions}>
                                {currentImageUri ? (
                                    <View style={styles.imagePreviewWrapper}>
                                        <Image source={{ uri: currentImageUri }} style={styles.imagePreview} />
                                        <View style={styles.imageOverlay}>
                                            <Ionicons name="camera" size={22} color="#fff" />
                                            <Text style={styles.imageOverlayText}>Tap to change</Text>
                                        </View>
                                    </View>
                                ) : (
                                    <View style={styles.imagePlaceholder}>
                                        <Ionicons name="camera-outline" size={36} color="#A3AED0" />
                                        <Text style={styles.imagePlaceholderText}>Tap to add image</Text>
                                        <Text style={styles.imagePlaceholderSub}>Camera or Gallery</Text>
                                    </View>
                                )}
                            </TouchableOpacity>

                            <Text style={styles.fieldLabel}>Product Name *</Text>
                            <TextInput
                                style={styles.field}
                                value={form.name}
                                onChangeText={v => setForm(f => ({ ...f, name: v }))}
                                placeholder="e.g. Fresh Milk 1L"
                            />

                            <Text style={styles.fieldLabel}>Description</Text>
                            <TextInput
                                style={[styles.field, { height: 80, textAlignVertical: 'top' }]}
                                value={form.description}
                                onChangeText={v => setForm(f => ({ ...f, description: v }))}
                                placeholder="Product description..."
                                multiline
                            />

                            <View style={styles.row2}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.fieldLabel}>Price (₹) *</Text>
                                    <TextInput
                                        style={styles.field}
                                        value={form.price}
                                        onChangeText={v => setForm(f => ({ ...f, price: v }))}
                                        keyboardType="numeric"
                                        placeholder="0.00"
                                    />
                                </View>
                                <View style={{ flex: 1, marginLeft: 12 }}>
                                    <Text style={styles.fieldLabel}>Stock Qty</Text>
                                    <TextInput
                                        style={styles.field}
                                        value={form.stock}
                                        onChangeText={v => setForm(f => ({ ...f, stock: v }))}
                                        keyboardType="numeric"
                                        placeholder="0"
                                    />
                                </View>
                            </View>

                            <Text style={styles.fieldLabel}>Unit (e.g. kg, L, pcs)</Text>
                            <TextInput
                                style={styles.field}
                                value={form.unit}
                                onChangeText={v => setForm(f => ({ ...f, unit: v }))}
                                placeholder="units / kg / g / L"
                            />

                            <Text style={styles.fieldLabel}>Category</Text>
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 8 }}>
                                {categories.map(cat => (
                                    <TouchableOpacity
                                        key={cat.id}
                                        style={[styles.catChip, form.category_id === cat.id?.toString() && styles.catChipActive, { marginRight: 8 }]}
                                        onPress={() => setForm(f => ({ ...f, category_id: cat.id.toString() }))}
                                    >
                                        <Text style={[styles.catChipText, form.category_id === cat.id?.toString() && styles.catChipTextActive]}>
                                            {cat.icon || ''} {cat.name}
                                        </Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>

                        </ScrollView>

                        <TouchableOpacity
                            style={[styles.saveBtn, (saving || uploadingImage) && { opacity: 0.7 }]}
                            onPress={handleSave}
                            disabled={saving || uploadingImage}
                        >
                            {saving || uploadingImage ? (
                                <>
                                    <ActivityIndicator color="#fff" size="small" />
                                    <Text style={styles.saveBtnText}>
                                        {uploadingImage ? 'Uploading Image...' : 'Saving...'}
                                    </Text>
                                </>
                            ) : (
                                <>
                                    <Ionicons name={editingProduct ? 'checkmark' : 'add'} size={20} color="#fff" />
                                    <Text style={styles.saveBtnText}>{editingProduct ? 'Save Changes' : 'Add Product'}</Text>
                                </>
                            )}
                        </TouchableOpacity>
                    </View>
                </KeyboardAvoidingView>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8F9FE' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },

    header: {
        flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
        backgroundColor: '#fff', padding: 20,
        borderBottomWidth: 1, borderBottomColor: '#F4F7FE',
    },
    title: { fontSize: 22, fontWeight: 'bold', color: DARK_NAVY },
    sub: { fontSize: 13, color: '#A3AED0', marginTop: 2 },
    addBtn: {
        flexDirection: 'row', alignItems: 'center', gap: 6,
        backgroundColor: BLUE, paddingHorizontal: 16, paddingVertical: 10,
        borderRadius: 12, elevation: 3,
    },
    addBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 14 },

    searchBar: {
        flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff',
        marginHorizontal: 16, marginTop: 14, marginBottom: 8,
        borderRadius: 14, paddingHorizontal: 14, paddingVertical: 10,
        borderWidth: 1, borderColor: '#E9EDF7', gap: 10,
    },
    searchInput: { flex: 1, fontSize: 14, color: DARK_NAVY },

    filtersRow: { flexDirection: 'row', paddingHorizontal: 16, gap: 8, marginBottom: 8 },
    chip: {
        paddingHorizontal: 12, paddingVertical: 7, borderRadius: 20,
        backgroundColor: '#F4F7FE', borderWidth: 1, borderColor: '#E9EDF7',
    },
    chipActive: { backgroundColor: BLUE, borderColor: BLUE },
    chipText: { fontSize: 11, fontWeight: '700', color: '#707EAE' },
    chipTextActive: { color: '#fff' },

    catRow: { paddingHorizontal: 16, paddingBottom: 10, gap: 8 },
    catChip: {
        paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20,
        backgroundColor: '#F4F7FE', borderWidth: 1, borderColor: '#E9EDF7',
    },
    catChipActive: { backgroundColor: '#EEF2FF', borderColor: BLUE },
    catChipText: { fontSize: 12, fontWeight: '600', color: '#A3AED0' },
    catChipTextActive: { color: BLUE },

    list: { paddingHorizontal: 16, paddingBottom: 20 },

    productRow: {
        flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff',
        borderRadius: 16, padding: 12, marginBottom: 10,
        shadowColor: '#1B2559', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.04, shadowRadius: 8, elevation: 2, gap: 10,
    },
    productImg: { width: 52, height: 52, borderRadius: 12 },
    productImgPlaceholder: {
        backgroundColor: '#F4F7FE', justifyContent: 'center', alignItems: 'center',
    },
    productInfo: { flex: 1 },
    productName: { fontSize: 14, fontWeight: 'bold', color: DARK_NAVY },
    productDesc: { fontSize: 12, color: '#A3AED0', marginTop: 2 },
    catBadge: {
        backgroundColor: '#EEF2FF', borderRadius: 6, paddingHorizontal: 8,
        paddingVertical: 2, marginTop: 4, alignSelf: 'flex-start',
    },
    catBadgeText: { fontSize: 10, color: BLUE, fontWeight: '700' },

    productRight: { alignItems: 'flex-end', gap: 3 },
    productPrice: { fontSize: 14, fontWeight: 'bold', color: DARK_NAVY },
    stockBadge: { borderRadius: 8, paddingHorizontal: 8, paddingVertical: 3 },
    stockText: { fontSize: 11, fontWeight: '800' },
    stockLabel: { fontSize: 10, fontWeight: '700' },

    productActions: { gap: 8 },
    editBtn: {
        width: 34, height: 34, borderRadius: 10, backgroundColor: '#EEF2FF',
        justifyContent: 'center', alignItems: 'center',
    },
    deleteBtn: {
        width: 34, height: 34, borderRadius: 10, backgroundColor: '#FFF1F0',
        justifyContent: 'center', alignItems: 'center',
    },

    empty: { alignItems: 'center', marginTop: 80, gap: 12 },
    emptyText: { fontSize: 16, color: '#A3AED0', fontWeight: '600' },

    // Modal
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.55)', justifyContent: 'flex-end' },
    modalBox: {
        backgroundColor: '#fff', borderTopLeftRadius: 28, borderTopRightRadius: 28,
        padding: 24, paddingBottom: 36, maxHeight: '92%',
    },
    modalHeader: {
        flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16,
    },
    modalTitle: { fontSize: 20, fontWeight: 'bold', color: DARK_NAVY },

    // Image Picker
    imagePicker: { marginBottom: 8 },
    imagePreviewWrapper: { position: 'relative', borderRadius: 16, overflow: 'hidden' },
    imagePreview: { width: '100%', height: 180, borderRadius: 16 },
    imageOverlay: {
        position: 'absolute', bottom: 0, left: 0, right: 0,
        backgroundColor: 'rgba(0,0,0,0.4)', alignItems: 'center',
        justifyContent: 'center', paddingVertical: 10, gap: 4,
    },
    imageOverlayText: { color: '#fff', fontSize: 12, fontWeight: '700' },
    imagePlaceholder: {
        height: 150, borderRadius: 16, backgroundColor: '#F4F7FE',
        borderWidth: 2, borderColor: '#E2E8F0', borderStyle: 'dashed',
        justifyContent: 'center', alignItems: 'center', gap: 8,
    },
    imagePlaceholderText: { fontSize: 15, fontWeight: '700', color: '#A3AED0' },
    imagePlaceholderSub: { fontSize: 12, color: '#C4CDD5' },

    fieldLabel: { fontSize: 11, fontWeight: '800', color: '#707EAE', textTransform: 'uppercase', marginBottom: 6, marginTop: 14 },
    field: {
        backgroundColor: '#F4F7FE', borderRadius: 12, padding: 14,
        fontSize: 15, color: DARK_NAVY, borderWidth: 1, borderColor: '#E9EDF7',
    },
    row2: { flexDirection: 'row' },
    saveBtn: {
        flexDirection: 'row', justifyContent: 'center', alignItems: 'center',
        backgroundColor: BLUE, borderRadius: 16, paddingVertical: 16,
        gap: 8, marginTop: 20, elevation: 4,
    },
    saveBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
});

export default InventoryScreen;
