import axios from 'axios';

// Use the computer's Mobile Hotspot IP - this is PERMANENT and never changes.
// Connect your phone to the computer's mobile hotspot for this to work.
// Hotspot IP: 192.168.137.1
const BASE_URL = 'http://192.168.137.1/Mini%20Project/api/';

const api = axios.create({
    baseURL: BASE_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
    },
});

export const authService = {
    login: (firebaseData) => api.post('auth.php', firebaseData),
    manualLogin: (email, password) => api.post('mobile_login.php', { email, password }),
    register: (regData) => api.post('register.php', regData),
    updateProfile: (profileData) => api.post('update_profile.php', profileData),
};

export const productService = {
    getProducts: (params) => api.get('products.php', { params }),
    getCategories: () => api.get('categories.php'),
    createCategory: (adminId, data) => api.post(`manage_categories.php?admin_id=${adminId}`, data),
    updateCategory: (adminId, data) => api.put(`manage_categories.php?admin_id=${adminId}`, data),
    deleteCategory: (adminId, categoryId) => api.delete(`manage_categories.php?admin_id=${adminId}&category_id=${categoryId}`),
    getAllProducts: (params) => api.get('products.php', { params: { limit: 100, ...params } }),
    createProduct: (adminId, data) => api.post(`manage_product.php?admin_id=${adminId}`, data),
    updateProduct: (adminId, data) => api.put(`manage_product.php?admin_id=${adminId}`, data),
    deleteProduct: (adminId, productId) => api.delete(`manage_product.php?admin_id=${adminId}&product_id=${productId}`),
    uploadImage: (adminId, imageUri) => {
        const formData = new FormData();
        const filename = imageUri.split('/').pop();
        const ext = filename.split('.').pop().toLowerCase();
        const mimeType = ext === 'png' ? 'image/png' : ext === 'gif' ? 'image/gif' : 'image/jpeg';
        formData.append('admin_id', String(adminId));
        formData.append('image', { uri: imageUri, name: filename, type: mimeType });
        return api.post('upload_image.php', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            timeout: 30000,
        });
    },
};

export const cartService = {
    getCart: (userId) => api.get('cart.php', { params: { user_id: userId } }),
    addToCart: (userId, productId, quantity = 1) =>
        api.post(`cart.php?user_id=${userId}`, { product_id: productId, quantity }),
    updateQuantity: (userId, productId, quantity) =>
        api.put(`cart.php?user_id=${userId}`, { product_id: productId, quantity }),
    removeFromCart: (userId, productId) =>
        api.delete(`cart.php?user_id=${userId}&product_id=${productId}`),
};

export const orderService = {
    getOrders: (userId) => api.get('orders.php', { params: { user_id: userId } }),
    getAllOrders: (userId, status) => api.get('orders.php', { params: { user_id: userId, status } }),
    placeOrder: (userId, orderData) =>
        api.post(`orders.php?user_id=${userId}`, orderData),
    updateStatus: (userId, orderId, status, verificationPin = null) =>
        api.put(`orders.php?user_id=${userId}`, { order_id: orderId, status, verification_pin: verificationPin }),
    verifyPayment: (paymentData) =>
        api.post('verify_payment.php', paymentData),
};

export const merchantService = {
    getStats: (userId) => api.get('merchant_stats.php', { params: { user_id: userId } }),
    getAttendanceBoard: (adminId, date) => api.get('admin_attendance_board.php', { params: { admin_id: adminId, date } }),
    saveAttendanceBoard: (adminId, data) => api.post(`admin_attendance_board.php?admin_id=${adminId}`, data),
    getLogs: (adminId) => api.get('admin_attendance.php', { params: { admin_id: adminId } }),
    getReports: (adminId, params) => api.get('admin_reports.php', { params: { admin_id: adminId, ...params } }),
};

export const userService = {
    getUsers: (adminId) => api.get('users.php', { params: { admin_id: adminId } }),
    updateUser: (adminId, userData) => api.put(`users.php?admin_id=${adminId}`, userData),
};

export const leaveService = {
    getLeaves: (userId) => api.get('leaves.php', { params: { user_id: userId } }),
    applyLeave: (leaveData) => api.post('leaves.php', leaveData),
};

export default api;
