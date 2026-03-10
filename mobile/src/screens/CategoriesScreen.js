import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, Alert, Modal, TextInput, ActivityIndicator, SafeAreaView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { productService } from '../services/api';
import { useAppContext } from '../context/AppContext';

const BLUE = '#1877F2';
const DARK_NAVY = '#1B2559';

const CategoriesScreen = () => {
    const { user } = useAppContext();
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [modalVisible, setModalVisible] = useState(false);
    const [editingCategory, setEditingCategory] = useState(null);
    const [form, setForm] = useState({ name: '', display_order: '0', icon: '' });

    const fetchCategories = async () => {
        try {
            const response = await productService.getCategories();
            if (response.data?.success) {
                setCategories(response.data.data);
            }
        } catch (error) {
            console.error('Fetch categories error:', error);
            Alert.alert('Error', 'Failed to load categories');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    const onRefresh = () => {
        setRefreshing(true);
        fetchCategories();
    };

    const handleSave = async () => {
        if (!form.name.trim()) {
            Alert.alert('Error', 'Category name is required');
            return;
        }

        try {
            const data = {
                id: editingCategory?.id,
                name: form.name.trim(),
                display_order: parseInt(form.display_order) || 0,
                icon: form.icon.trim()
            };

            let response;
            if (editingCategory) {
                response = await productService.updateCategory(user.id, data);
            } else {
                response = await productService.createCategory(user.id, data);
            }

            if (response.data?.success) {
                Alert.alert('Success', `Category ${editingCategory ? 'updated' : 'created'} successfully`);
                setModalVisible(false);
                fetchCategories();
            } else {
                Alert.alert('Error', response.data?.message || 'Failed to save category');
            }
        } catch (error) {
            console.error('Save category error:', error);
            Alert.alert('Error', 'Failed to save category');
        }
    };

    const handleDelete = (categoryId) => {
        if (user.role?.toLowerCase() !== 'admin') {
            Alert.alert('Unauthorized', 'Only admins can delete categories');
            return;
        }

        Alert.alert(
            'Confirm Delete',
            'Are you sure you want to delete this category?',
            [
                { text: 'Cancel', style: 'cancel' },
                {
                    text: 'Delete',
                    style: 'destructive',
                    onPress: async () => {
                        try {
                            const response = await productService.deleteCategory(user.id, categoryId);
                            if (response.data?.success) {
                                fetchCategories();
                            } else {
                                Alert.alert('Error', response.data?.message || 'Failed to delete category');
                            }
                        } catch (error) {
                            Alert.alert('Error', 'Failed to delete category');
                        }
                    }
                }
            ]
        );
    };

    const openModal = (category = null) => {
        setEditingCategory(category);
        if (category) {
            setForm({
                name: category.name || '',
                display_order: (category.display_order || 0).toString(),
                icon: category.icon || ''
            });
        } else {
            setForm({ name: '', display_order: '0', icon: '' });
        }
        setModalVisible(true);
    };

    const renderItem = ({ item }) => (
        <View style={styles.card}>
            <View style={styles.cardContent}>
                <View style={styles.iconBox}>
                    <Text style={styles.iconText}>{item.icon || '🛍️'}</Text>
                </View>
                <View style={styles.info}>
                    <Text style={styles.name}>{item.name}</Text>
                    <Text style={styles.orderText}>Order: {item.display_order || 0}</Text>
                </View>
                {(user.role?.toLowerCase() === 'admin' || user.role?.toLowerCase() === 'staff') && (
                    <View style={styles.actions}>
                        <TouchableOpacity style={styles.iconBtn} onPress={() => openModal(item)}>
                            <Ionicons name="pencil" size={20} color={BLUE} />
                        </TouchableOpacity>
                        {user.role?.toLowerCase() === 'admin' && (
                            <TouchableOpacity style={styles.iconBtn} onPress={() => handleDelete(item.id)}>
                                <Ionicons name="trash" size={20} color="#FF3B30" />
                            </TouchableOpacity>
                        )}
                    </View>
                )}
            </View>
        </View>
    );

    if (loading) {
        return (
            <View style={styles.center}>
                <ActivityIndicator size="large" color={BLUE} />
            </View>
        );
    }

    return (
        <SafeAreaView style={styles.container}>
            <FlatList
                data={categories}
                keyExtractor={(item) => item.id.toString()}
                renderItem={renderItem}
                contentContainerStyle={styles.list}
                refreshing={refreshing}
                onRefresh={onRefresh}
                ListEmptyComponent={<Text style={styles.emptyText}>No categories found</Text>}
            />

            {(user.role?.toLowerCase() === 'admin' || user.role?.toLowerCase() === 'staff') && (
                <TouchableOpacity style={styles.fab} onPress={() => openModal()}>
                    <Ionicons name="add" size={24} color="#FFF" />
                </TouchableOpacity>
            )}

            <Modal visible={modalVisible} transparent={true} animationType="slide">
                <View style={styles.modalBg}>
                    <View style={styles.modalCard}>
                        <Text style={styles.modalTitle}>{editingCategory ? 'Edit Category' : 'New Category'}</Text>

                        <Text style={styles.label}>Name</Text>
                        <TextInput
                            style={styles.input}
                            value={form.name}
                            onChangeText={(val) => setForm({ ...form, name: val })}
                            placeholder="Current Category Name"
                        />

                        <Text style={styles.label}>Display Order</Text>
                        <TextInput
                            style={styles.input}
                            value={form.display_order}
                            onChangeText={(val) => setForm({ ...form, display_order: val })}
                            keyboardType="numeric"
                            placeholder="0"
                        />

                        <Text style={styles.label}>Icon (Emoji)</Text>
                        <TextInput
                            style={styles.input}
                            value={form.icon}
                            onChangeText={(val) => setForm({ ...form, icon: val })}
                            placeholder="🍎"
                        />

                        <View style={styles.modalActions}>
                            <TouchableOpacity style={[styles.btn, styles.btnCancel]} onPress={() => setModalVisible(false)}>
                                <Text style={styles.btnCancelText}>Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={[styles.btn, styles.btnSave]} onPress={handleSave}>
                                <Text style={styles.btnSaveText}>Save</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>
        </SafeAreaView>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8F9FE' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    list: { padding: 16, paddingBottom: 80 },
    card: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 },
    cardContent: { flexDirection: 'row', alignItems: 'center' },
    iconBox: { width: 44, height: 44, backgroundColor: '#F0F3FF', borderRadius: 12, justifyContent: 'center', alignItems: 'center', marginRight: 12 },
    iconText: { fontSize: 24 },
    info: { flex: 1 },
    name: { fontSize: 16, fontWeight: 'bold', color: DARK_NAVY, marginBottom: 4 },
    orderText: { fontSize: 12, color: '#A3AED0' },
    actions: { flexDirection: 'row' },
    iconBtn: { padding: 8, marginLeft: 8 },
    emptyText: { textAlign: 'center', color: '#A3AED0', marginTop: 32 },
    fab: { position: 'absolute', bottom: 20, right: 20, width: 56, height: 56, borderRadius: 28, backgroundColor: BLUE, justifyContent: 'center', alignItems: 'center', elevation: 4, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.2, shadowRadius: 4 },
    modalBg: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20 },
    modalCard: { backgroundColor: '#FFF', padding: 24, borderRadius: 20 },
    modalTitle: { fontSize: 20, fontWeight: 'bold', color: DARK_NAVY, marginBottom: 20 },
    label: { fontSize: 14, fontWeight: '600', color: DARK_NAVY, marginBottom: 8 },
    input: { backgroundColor: '#F4F7FE', borderRadius: 12, padding: 12, marginBottom: 16, fontSize: 16, color: DARK_NAVY },
    modalActions: { flexDirection: 'row', justifyContent: 'flex-end', marginTop: 10 },
    btn: { paddingVertical: 12, paddingHorizontal: 20, borderRadius: 12, marginLeft: 12 },
    btnCancel: { backgroundColor: '#F4F7FE' },
    btnCancelText: { color: DARK_NAVY, fontWeight: '600' },
    btnSave: { backgroundColor: BLUE },
    btnSaveText: { color: '#FFF', fontWeight: '600' }
});

export default CategoriesScreen;
