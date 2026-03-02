import React, { createContext, useState, useContext, useEffect } from 'react';
import { cartService } from '../services/api';

const AppContext = createContext();

export const AppProvider = ({ children }) => {
    const [user, setUser] = useState({ id: 1, full_name: 'Savio Joe', email: 'savio@example.com' }); // Mock user for demo
    const [cart, setCart] = useState([]);
    const [loading, setLoading] = useState(false);

    const fetchCart = async () => {
        if (!user) return;
        try {
            const response = await cartService.getCart(user.id);
            setCart(response.data?.data || []);
        } catch (error) {
            console.error('Error fetching cart:', error);
        }
    };

    const addToCart = async (product, quantity = 1) => {
        if (!user) {
            alert('Please login to add items to cart');
            return;
        }
        try {
            await cartService.addToCart(user.id, product.id, quantity);
            fetchCart();
            alert(`Added ${product.name} to cart!`);
        } catch (error) {
            console.error('Error adding to cart:', error);
            alert('Failed to add item to cart. Please check your connection.');
        }
    };

    const updateQuantity = async (productId, quantity) => {
        try {
            await cartService.updateQuantity(user.id, productId, quantity);
            fetchCart();
        } catch (error) {
            console.error('Error updating quantity:', error);
        }
    };

    const removeFromCart = async (productId) => {
        try {
            await cartService.removeFromCart(user.id, productId);
            fetchCart();
        } catch (error) {
            console.error('Error removing from cart:', error);
        }
    };

    useEffect(() => {
        fetchCart();
    }, [user]);

    return (
        <AppContext.Provider value={{
            user, setUser,
            cart, fetchCart, addToCart, updateQuantity, removeFromCart,
            loading, setLoading
        }}>
            {children}
        </AppContext.Provider>
    );
};

export const useAppContext = () => useContext(AppContext);
