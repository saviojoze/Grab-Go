import React, { useState } from 'react';
import {
    View,
    Text,
    StyleSheet,
    TextInput,
    TouchableOpacity,
    ActivityIndicator,
    KeyboardAvoidingView,
    Platform,
    Alert,
    ScrollView,
    StatusBar,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppContext } from '../context/AppContext';

const BLUE = '#1877F2';
const BLUE_DARK = '#145CBF';
const GREEN = '#00D563';

const LoginScreen = ({ onRegister }) => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPass, setShowPass] = useState(false);
    const [googleLoading, setGoogleLoading] = useState(false);
    const { login, googleLogin, loading } = useAppContext();

    const handleLogin = async () => {
        if (!email.trim() || !password.trim()) {
            Alert.alert('Missing fields', 'Please enter your email and password.');
            return;
        }
        const result = await login(email.trim(), password);
        if (!result.success) {
            Alert.alert('Login Failed', result.message || 'Invalid credentials.');
        }
    };

    const handleGoogle = async () => {
        setGoogleLoading(true);
        try {
            const result = await googleLogin();
            if (result && !result.success && result.message !== 'Google sign-in was cancelled.') {
                Alert.alert('Google Sign-In Failed', result.message || 'Please try again.');
            }
        } finally {
            setGoogleLoading(false);
        }
    };

    return (
        <KeyboardAvoidingView
            behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
            style={styles.root}
        >
            <StatusBar barStyle="light-content" backgroundColor={BLUE} />
            <ScrollView
                contentContainerStyle={styles.scroll}
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={false}
            >
                {/* ── HERO PANEL ─────────────────────── */}
                <View style={styles.hero}>
                    {/* Decorative circles */}
                    <View style={styles.decCircleTL} />
                    <View style={styles.decCircleBR} />

                    {/* Logo Area */}
                    <View style={styles.brandingRow}>
                        <View style={styles.logoIconBox}>
                            <Ionicons name="cart" size={24} color={GREEN} />
                        </View>
                        <Text style={styles.brandingText}>GRAB & GO</Text>
                    </View>

                    {/* Offer badge */}
                    <View style={styles.offerBadge}>
                        <Text style={styles.offerBadgeText}>LIMITED OFFER 25% OFF</Text>
                    </View>

                    {/* Headline */}
                    <Text style={styles.heroHeadline}>
                        Skip the Line.{'\n'}Save Your Time.
                    </Text>
                    <Text style={styles.heroSub}>
                        Order online, skip the checkout lines, and manage
                        your purchases seamlessly.
                    </Text>
                </View>

                {/* ── FORM PANEL ─────────────────────── */}
                <View style={styles.form}>
                    <Text style={styles.welcomeTitle}>Welcome Back!</Text>
                    <Text style={styles.welcomeSub}>Please enter your credentials to continue.</Text>

                    {/* Email */}
                    <Text style={styles.label}>
                        <Ionicons name="mail-outline" size={13} color="#888" />
                        {'  '}Email Address
                    </Text>
                    <View style={styles.inputWrap}>
                        <TextInput
                            style={styles.input}
                            placeholder="you@grabandgo.com"
                            placeholderTextColor="#bbb"
                            value={email}
                            onChangeText={setEmail}
                            autoCapitalize="none"
                            keyboardType="email-address"
                        />
                    </View>

                    {/* Password */}
                    <Text style={styles.label}>
                        <Ionicons name="lock-closed-outline" size={13} color="#888" />
                        {'  '}Password
                    </Text>
                    <View style={styles.inputWrap}>
                        <TextInput
                            style={[styles.input, { flex: 1 }]}
                            placeholder="••••••••"
                            placeholderTextColor="#bbb"
                            value={password}
                            onChangeText={setPassword}
                            secureTextEntry={!showPass}
                        />
                        <TouchableOpacity onPress={() => setShowPass(!showPass)} style={styles.eyeBtn}>
                            <Ionicons
                                name={showPass ? 'eye-outline' : 'eye-off-outline'}
                                size={20}
                                color="#aaa"
                            />
                        </TouchableOpacity>
                    </View>

                    {/* Forgot */}
                    <TouchableOpacity style={styles.forgotRow}>
                        <Text style={styles.forgotText}>Forgot Password?</Text>
                    </TouchableOpacity>

                    {/* Sign In */}
                    <TouchableOpacity
                        style={[styles.signInBtn, loading && styles.signInBtnDisabled]}
                        onPress={handleLogin}
                        disabled={loading}
                        activeOpacity={0.85}
                    >
                        {loading ? (
                            <ActivityIndicator color="#fff" />
                        ) : (
                            <Text style={styles.signInBtnText}>Sign In</Text>
                        )}
                    </TouchableOpacity>

                    {/* OR Divider */}
                    <View style={styles.orRow}>
                        <View style={styles.orLine} />
                        <Text style={styles.orText}>OR</Text>
                        <View style={styles.orLine} />
                    </View>

                    {/* Continue with Google */}
                    <TouchableOpacity
                        style={[styles.googleBtn, googleLoading && { opacity: 0.7 }]}
                        onPress={handleGoogle}
                        disabled={googleLoading || loading}
                        activeOpacity={0.85}
                    >
                        {googleLoading ? (
                            <ActivityIndicator color="#4285F4" size="small" />
                        ) : (
                            <>
                                <View style={styles.googleIconWrap}>
                                    <View style={styles.googleGContainer}>
                                        <Text style={styles.googleG}>G</Text>
                                    </View>
                                </View>
                                <Text style={styles.googleBtnText}>Continue with Google</Text>
                            </>
                        )}
                    </TouchableOpacity>

                    {/* Footer */}
                    <View style={styles.footer}>
                        <Text style={styles.footerText}>New here?{'  '}</Text>
                        <TouchableOpacity
                            onPress={onRegister}
                            hitSlop={{ top: 20, bottom: 20, left: 20, right: 20 }}
                        >
                            <Text style={styles.footerLink}>Create Account</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </ScrollView>
        </KeyboardAvoidingView>
    );
};

const styles = StyleSheet.create({
    root: {
        flex: 1,
        backgroundColor: BLUE,
    },
    scroll: {
        flexGrow: 1,
    },

    /* ── Branding ── */
    brandingRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 24,
        gap: 12,
    },
    logoIconBox: {
        width: 44,
        height: 44,
        borderRadius: 12,
        backgroundColor: '#fff',
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        elevation: 3,
    },
    brandingText: {
        color: '#fff',
        fontSize: 20,
        fontWeight: '900',
        letterSpacing: 2,
    },

    /* ── Hero ─────────────────────────── */
    hero: {
        backgroundColor: BLUE,
        paddingTop: 60,
        paddingBottom: 48,
        paddingHorizontal: 28,
        position: 'relative',
        overflow: 'hidden',
    },
    decCircleTL: {
        position: 'absolute',
        top: -50,
        left: -50,
        width: 160,
        height: 160,
        borderRadius: 80,
        backgroundColor: 'rgba(255,255,255,0.07)',
    },
    decCircleBR: {
        position: 'absolute',
        bottom: -40,
        right: -40,
        width: 130,
        height: 130,
        borderRadius: 65,
        backgroundColor: 'rgba(255,255,255,0.06)',
    },
    offerBadge: {
        alignSelf: 'flex-start',
        backgroundColor: GREEN,
        borderRadius: 20,
        paddingHorizontal: 12,
        paddingVertical: 5,
        marginBottom: 20,
    },
    offerBadgeText: {
        color: '#fff',
        fontSize: 10,
        fontWeight: '800',
        letterSpacing: 0.8,
        textTransform: 'uppercase',
    },
    heroIconWrap: {
        width: 60,
        height: 60,
        borderRadius: 16,
        backgroundColor: '#fff',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 18,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.15,
        shadowRadius: 8,
        elevation: 6,
    },
    heroHeadline: {
        color: '#fff',
        fontSize: 30,
        fontWeight: '900',
        lineHeight: 38,
        letterSpacing: -0.5,
        marginBottom: 14,
    },
    heroSub: {
        color: 'rgba(255,255,255,0.75)',
        fontSize: 14,
        lineHeight: 22,
        maxWidth: 300,
    },

    /* ── Form Panel ───────────────────── */
    form: {
        flex: 1,
        backgroundColor: '#fff',
        borderTopLeftRadius: 28,
        borderTopRightRadius: 28,
        paddingTop: 32,
        paddingHorizontal: 28,
        paddingBottom: 40,
        // shadow upward
        shadowColor: '#000',
        shadowOffset: { width: 0, height: -4 },
        shadowOpacity: 0.07,
        shadowRadius: 12,
        elevation: 10,
    },
    welcomeTitle: {
        fontSize: 24,
        fontWeight: '900',
        color: '#111',
        marginBottom: 6,
        letterSpacing: -0.3,
    },
    welcomeSub: {
        fontSize: 13.5,
        color: '#888',
        marginBottom: 28,
        lineHeight: 20,
    },
    label: {
        fontSize: 12.5,
        fontWeight: '700',
        color: '#555',
        marginBottom: 7,
        letterSpacing: 0.1,
    },
    inputWrap: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f3f4f6',
        borderRadius: 12,
        borderWidth: 1.5,
        borderColor: '#e5e7eb',
        paddingHorizontal: 14,
        height: 52,
        marginBottom: 16,
    },
    input: {
        flex: 1,
        fontSize: 15,
        color: '#111',
    },
    eyeBtn: {
        padding: 4,
    },
    forgotRow: {
        alignItems: 'flex-end',
        marginBottom: 22,
        marginTop: -6,
    },
    forgotText: {
        fontSize: 13,
        color: BLUE,
        fontWeight: '600',
    },

    /* Sign In */
    signInBtn: {
        backgroundColor: BLUE,
        borderRadius: 14,
        height: 54,
        justifyContent: 'center',
        alignItems: 'center',
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.28,
        shadowRadius: 10,
        elevation: 6,
    },
    signInBtnDisabled: {
        backgroundColor: '#a5c7f9',
        shadowOpacity: 0,
        elevation: 0,
    },
    signInBtnText: {
        color: '#fff',
        fontSize: 17,
        fontWeight: '800',
        letterSpacing: 0.2,
    },

    /* OR */
    orRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginVertical: 22,
        gap: 10,
    },
    orLine: {
        flex: 1,
        height: 1,
        backgroundColor: '#e5e7eb',
    },
    orText: {
        fontSize: 12,
        color: '#aaa',
        fontWeight: '700',
        letterSpacing: 1,
    },

    /* Google */
    googleBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        borderRadius: 14,
        height: 54,
        borderWidth: 1.5,
        borderColor: '#e5e7eb',
        backgroundColor: '#fff',
        gap: 10,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.06,
        shadowRadius: 6,
        elevation: 2,
    },
    googleIconWrap: {
        width: 28,
        height: 28,
        justifyContent: 'center',
        alignItems: 'center',
    },
    googleGContainer: {
        width: 24,
        height: 24,
        borderRadius: 12,
        backgroundColor: '#fff',
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#eee',
    },
    googleG: {
        fontSize: 16,
        fontWeight: '900',
        color: '#4285F4',
    },
    googleBtnText: {
        fontSize: 15.5,
        fontWeight: '700',
        color: '#222',
    },

    /* Footer */
    footer: {
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: 28,
        flexWrap: 'wrap',
    },
    footerText: {
        fontSize: 13.5,
        color: '#888',
    },
    footerLink: {
        fontSize: 13.5,
        fontWeight: '800',
        color: BLUE,
    },
});

export default LoginScreen;
