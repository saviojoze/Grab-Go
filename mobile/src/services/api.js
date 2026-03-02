import axios from 'axios';

// Replace with your local IP address for physical device testing
// Run 'ipconfig' (Windows) or 'ifconfig' (Mac/Linux) to find it
const BASE_URL = 'http://192.168.37.21/Mini%20Project/api';

const api = axios.create({
    baseURL: BASE_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
    },
});

export const authService = {
    login: (firebaseData) => api.post('/auth.php', firebaseData),
};

export const productService = {
    getProducts: (params) => api.get('/products.php', { params }),
    getCategories: () => api.get('/categories.php'),
};

export const cartService = {
    getCart: (userId) => api.get('/cart.php', { params: { user_id: userId } }),
    addToCart: (userId, productId, quantity = 1) =>
        api.post(`/cart.php?user_id=${userId}`, { product_id: productId, quantity }),
    updateQuantity: (userId, productId, quantity) =>
        api.put(`/cart.php?user_id=${userId}`, { product_id: productId, quantity }),
    removeFromCart: (userId, productId) =>
        api.delete(`/cart.php?user_id=${userId}&product_id=${productId}`),
};

export const orderService = {
    getOrders: (userId) => api.get('/orders.php', { params: { user_id: userId } }),
    placeOrder: (userId, orderData) =>
        api.post(`/orders.php?user_id=${userId}`, orderData),
};

export default api;
