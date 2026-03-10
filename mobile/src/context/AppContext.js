import React, { createContext, useState, useContext, useEffect } from 'react';
import { cartService, authService } from '../services/api';
import * as AuthSession from 'expo-auth-session';
import * as WebBrowser from 'expo-web-browser';

// Required for Expo Go OAuth redirect handling
WebBrowser.maybeCompleteAuthSession();

const AppContext = createContext();

// ── Google OAuth config ─────────────────────
const GOOGLE_CLIENT_ID = '281115445168-avr382ednm9gojdb7gkm3gc56rf5nr6b.apps.googleusercontent.com';

const discovery = {
    authorizationEndpoint: 'https://accounts.google.com/o/oauth2/v2/auth',
    tokenEndpoint: 'https://oauth2.googleapis.com/token',
    revocationEndpoint: 'https://oauth2.googleapis.com/revoke',
};

export const AppProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [cart, setCart] = useState([]);
    const [loading, setLoading] = useState(false);

    // ── Auth Session for Google ────────────────
    const redirectUri = AuthSession.makeRedirectUri({ useProxy: true });

    const [request, response, promptAsync] = AuthSession.useAuthRequest(
        {
            clientId: GOOGLE_CLIENT_ID,
            redirectUri,
            scopes: ['openid', 'profile', 'email'],
            responseType: AuthSession.ResponseType.Token,
            usePKCE: false,
        },
        discovery
    );

    // When Google redirects back with a token, use it
    useEffect(() => {
        if (response?.type === 'success') {
            const { access_token } = response.params;
            handleGoogleToken(access_token);
        }
    }, [response]);

    const handleGoogleToken = async (accessToken) => {
        setLoading(true);
        try {
            // Fetch Google user profile
            const profileRes = await fetch(
                `https://www.googleapis.com/oauth2/v3/userinfo?access_token=${accessToken}`
            );
            const profile = await profileRes.json();

            // Send to backend auth.php
            const backendRes = await authService.login({
                uid: profile.sub,
                email: profile.email,
                displayName: profile.name,
                photoURL: profile.picture,
                access_token: accessToken,
            });

            if (backendRes.data?.success) {
                setUser(backendRes.data.data);
                return { success: true };
            } else {
                return { success: false, message: backendRes.data?.message || 'Google login failed.' };
            }
        } catch (err) {
            console.error('Google token error:', err);
            return { success: false, message: 'Could not verify Google account. Check your connection.' };
        } finally {
            setLoading(false);
        }
    };

    // Exposed function called from LoginScreen
    const googleLogin = async () => {
        if (!request) {
            return { success: false, message: 'Google auth not ready. Try again.' };
        }
        const result = await promptAsync({ useProxy: true });
        if (result?.type === 'cancel' || result?.type === 'dismiss') {
            return { success: false, message: 'Google sign-in was cancelled.' };
        }
        // The useEffect above handles the token on success
        return { success: true };
    };

    // ── Cart helpers ───────────────────────────
    const fetchCart = async () => {
        if (!user) return;
        try {
            const res = await cartService.getCart(user.id);
            setCart(res.data?.data || []);
        } catch (e) {
            console.error('fetchCart error:', e);
        }
    };

    const addToCart = async (product, quantity = 1) => {
        if (!user) { alert('Please login to add items to cart'); return; }
        try {
            await cartService.addToCart(user.id, product.id, quantity);
            fetchCart();
            alert(`Added ${product.name} to cart!`);
        } catch (e) {
            console.error('addToCart error:', e);
            alert('Failed to add item. Check your connection.');
        }
    };

    const updateQuantity = async (productId, quantity) => {
        try {
            await cartService.updateQuantity(user.id, productId, quantity);
            fetchCart();
        } catch (e) { console.error(e); }
    };

    const removeFromCart = async (productId) => {
        try {
            await cartService.removeFromCart(user.id, productId);
            fetchCart();
        } catch (e) { console.error(e); }
    };

    // ── Manual login ─────────────────────────
    const login = async (email, password) => {
        setLoading(true);
        try {
            const res = await authService.manualLogin(email, password);
            if (res.data?.success) {
                setUser(res.data.data);
                return { success: true };
            }
            return { success: false, message: res.data?.message || 'Invalid credentials' };
        } catch (err) {
            const status = err.response?.status;
            const message = err.response?.data?.message;
            if (status === 404) return { success: false, message: `API endpoint not found (404) at ${authService.manualLogin.toString().includes('BASE_URL') ? 'configured URL' : 'server'}.` };
            if (status === 500) return { success: false, message: 'Server error (500). Contact support.' };
            return { success: false, message: (message || 'Connection error.') + `\nAttempted: ${authService.manualLogin.toString()}` };
        } finally {
            setLoading(false);
        }
    };

    const logout = () => { setUser(null); setCart([]); };

    useEffect(() => {
        if (user) fetchCart();
        else setCart([]);
    }, [user]);

    return (
        <AppContext.Provider value={{
            user, setUser, logout,
            login, googleLogin,
            cart, fetchCart, addToCart, updateQuantity, removeFromCart,
            loading, setLoading,
        }}>
            {children}
        </AppContext.Provider>
    );
};

export const useAppContext = () => useContext(AppContext);
