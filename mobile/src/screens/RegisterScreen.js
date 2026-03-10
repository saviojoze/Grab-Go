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
import { authService } from '../services/api';

const BLUE = '#1877F2';

const RegisterScreen = ({ onBack }) => {
    const [fullName, setFullName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [phone, setPhone] = useState('');
    const [showPass, setShowPass] = useState(false);
    const [regLoading, setRegLoading] = useState(false);
    const { setUser } = useAppContext();

    const handleRegister = async () => {
        if (!fullName.trim() || !email.trim() || !password.trim()) {
            Alert.alert('Missing Info', 'Please fill in your name, email, and password.');
            return;
        }

        if (password.length < 6) {
            Alert.alert('Security', 'Password must be at least 6 characters long.');
            return;
        }

        setRegLoading(true);
        try {
            console.log('Sending registration request...');
            const response = await authService.register({
                full_name: fullName.trim(),
                email: email.trim().toLowerCase(),
                password: password,
                phone: phone.trim()
            });

            console.log('Reg response:', response.data);

            if (response.data?.success) {
                Alert.alert('Welcome!', 'Your account has been created successfully.', [
                    { text: 'Happy Shopping!', onPress: () => setUser(response.data.data) }
                ]);
            } else {
                Alert.alert('Registration Stopped', response.data?.message || 'Could not create account. Please check your data.');
            }
        } catch (error) {
            console.error('Registration error:', error);
            const errMsg = error.response?.data?.message || 'Registration failed. Check your network or server status.';
            Alert.alert('Network Error', errMsg);
        } finally {
            setRegLoading(false);
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
                <View style={styles.hero}>
                    <TouchableOpacity style={styles.backBtn} onPress={onBack}>
                        <Ionicons name="arrow-back" size={24} color="#FFF" />
                    </TouchableOpacity>
                    <Text style={styles.heroHeadline}>Create{'\n'}Account</Text>
                    <Text style={styles.heroSub}>Join GRAB & GO for the best shopping experience.</Text>
                </View>

                <View style={styles.form}>
                    {/* Full Name */}
                    <Text style={styles.label}>Full Name</Text>
                    <View style={styles.inputWrap}>
                        <TextInput
                            style={styles.input}
                            placeholder="John Doe"
                            placeholderTextColor="#A3AED0"
                            value={fullName}
                            onChangeText={setFullName}
                        />
                    </View>

                    {/* Email */}
                    <Text style={styles.label}>Email Address</Text>
                    <View style={styles.inputWrap}>
                        <TextInput
                            style={styles.input}
                            placeholder="you@example.com"
                            placeholderTextColor="#A3AED0"
                            value={email}
                            onChangeText={setEmail}
                            autoCapitalize="none"
                            keyboardType="email-address"
                        />
                    </View>

                    {/* Phone */}
                    <Text style={styles.label}>Phone Number (Optional)</Text>
                    <View style={styles.inputWrap}>
                        <TextInput
                            style={styles.input}
                            placeholder="+1 234 567 890"
                            placeholderTextColor="#A3AED0"
                            value={phone}
                            onChangeText={setPhone}
                            keyboardType="phone-pad"
                        />
                    </View>

                    {/* Password */}
                    <Text style={styles.label}>Password</Text>
                    <View style={styles.inputWrap}>
                        <TextInput
                            style={[styles.input, { flex: 1 }]}
                            placeholder="••••••••"
                            placeholderTextColor="#A3AED0"
                            value={password}
                            onChangeText={setPassword}
                            secureTextEntry={!showPass}
                        />
                        <TouchableOpacity onPress={() => setShowPass(!showPass)}>
                            <Ionicons
                                name={showPass ? 'eye-outline' : 'eye-off-outline'}
                                size={20}
                                color="#aaa"
                            />
                        </TouchableOpacity>
                    </View>

                    <TouchableOpacity
                        style={[styles.regBtn, regLoading && styles.disabledBtn]}
                        onPress={handleRegister}
                        disabled={regLoading}
                    >
                        {regLoading ? (
                            <ActivityIndicator color="#fff" />
                        ) : (
                            <Text style={styles.regBtnText}>Create Account</Text>
                        )}
                    </TouchableOpacity>

                    <View style={styles.footer}>
                        <Text style={styles.footerText}>Already have an account? </Text>
                        <TouchableOpacity onPress={onBack}>
                            <Text style={styles.footerLink}>Login</Text>
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
    hero: {
        paddingTop: 60,
        paddingBottom: 40,
        paddingHorizontal: 28,
    },
    backBtn: {
        marginBottom: 20,
    },
    heroHeadline: {
        color: '#fff',
        fontSize: 32,
        fontWeight: '900',
        lineHeight: 40,
    },
    heroSub: {
        color: 'rgba(255,255,255,0.7)',
        fontSize: 14,
        marginTop: 10,
    },
    form: {
        flex: 1,
        backgroundColor: '#fff',
        borderTopLeftRadius: 30,
        borderTopRightRadius: 30,
        padding: 28,
    },
    label: {
        fontSize: 13,
        fontWeight: '700',
        color: '#555',
        marginBottom: 8,
    },
    inputWrap: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f4f7fe',
        borderRadius: 12,
        paddingHorizontal: 15,
        height: 52,
        marginBottom: 20,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    input: {
        flex: 1,
        fontSize: 15,
        color: '#111',
    },
    regBtn: {
        backgroundColor: BLUE,
        borderRadius: 14,
        height: 54,
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: 10,
        shadowColor: BLUE,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 8,
        elevation: 4,
    },
    disabledBtn: {
        opacity: 0.6,
    },
    regBtnText: {
        color: '#fff',
        fontSize: 16,
        fontWeight: 'bold',
    },
    footer: {
        flexDirection: 'row',
        justifyContent: 'center',
        marginTop: 24,
    },
    footerText: {
        color: '#707EAE',
    },
    footerLink: {
        color: BLUE,
        fontWeight: '900',
    },
});

export default RegisterScreen;
