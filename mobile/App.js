import React, { useState } from 'react';
import { StyleSheet, View, Text, ScrollView, TouchableOpacity } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';

// Context
import { AppProvider } from './src/context/AppContext';

// Screens
import HomeScreen from './src/screens/HomeScreen';
import ShopScreen from './src/screens/ShopScreen';
import CartScreen from './src/screens/CartScreen';
import ProfileScreen from './src/screens/ProfileScreen';
import LoginScreen from './src/screens/LoginScreen';
import RegisterScreen from './src/screens/RegisterScreen';
import MerchantDashboard from './src/screens/MerchantDashboard';
import MyLeavesScreen from './src/screens/MyLeavesScreen';
import OrderManagementScreen from './src/screens/OrderManagementScreen';
import UserManagementScreen from './src/screens/UserManagementScreen';
import InventoryScreen from './src/screens/InventoryScreen';
import CategoriesScreen from './src/screens/CategoriesScreen';
import AttendanceScreen from './src/screens/AttendanceScreen';
import LogsScreen from './src/screens/LogsScreen';
import ReportsScreen from './src/screens/ReportsScreen';
import AdminHubScreen from './src/screens/AdminHubScreen';
import LocationsScreen from './src/screens/LocationsScreen';
import { useAppContext } from './src/context/AppContext';

const PlaceholderScreen = () => (
  <View style={styles.container}>
    <Text style={styles.title}>Coming Soon</Text>
  </View>
);

const CustomTabBar = ({ state, descriptors, navigation }) => {
  return (
    <View style={styles.tabBarContainer}>
      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.tabBarScroll}>
        {state.routes.map((route, index) => {
          const { options } = descriptors[route.key];
          const label =
            options.tabBarLabel !== undefined
              ? options.tabBarLabel
              : options.title !== undefined
                ? options.title
                : route.name;

          const isFocused = state.index === index;

          const onPress = () => {
            const event = navigation.emit({
              type: 'tabPress',
              target: route.key,
              canPreventDefault: true,
            });

            if (!isFocused && !event.defaultPrevented) {
              navigation.navigate({ name: route.name, merge: true });
            }
          };

          const onLongPress = () => {
            navigation.emit({
              type: 'tabLongPress',
              target: route.key,
            });
          };

          return (
            <TouchableOpacity
              key={index}
              accessibilityRole="button"
              accessibilityState={isFocused ? { selected: true } : {}}
              accessibilityLabel={options.tabBarAccessibilityLabel}
              testID={options.tabBarTestID}
              onPress={onPress}
              onLongPress={onLongPress}
              style={styles.tabItem}
            >
              {options.tabBarIcon && options.tabBarIcon({ focused: isFocused, color: isFocused ? '#1877F2' : 'gray', size: 22 })}
              <Text style={{ color: isFocused ? '#1877F2' : 'gray', marginTop: 2, fontSize: 10, fontWeight: isFocused ? 'bold' : 'normal', textAlign: 'center' }}>
                {label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </ScrollView>
    </View>
  );
};

const Tab = createBottomTabNavigator();

const AppNavigator = () => {
  const { user } = useAppContext();
  const [showRegister, setShowRegister] = useState(false);

  if (!user) {
    return (
      <NavigationContainer>
        {showRegister ? (
          <RegisterScreen onBack={() => setShowRegister(false)} />
        ) : (
          <LoginScreen onRegister={() => setShowRegister(true)} />
        )}
      </NavigationContainer>
    );
  }

  const role = user.role?.toLowerCase();
  const isMerchant = role === 'staff' || role === 'admin';

  return (
    <NavigationContainer>
      <Tab.Navigator
        tabBar={props => isMerchant ? <CustomTabBar {...props} /> : null}
        screenOptions={({ route }) => ({
          tabBarIcon: ({ focused, color, size }) => {
            let iconName;
            switch (route.name) {
              case 'Home': iconName = focused ? 'home' : 'home-outline'; break;
              case 'Dashboard': iconName = focused ? 'grid' : 'grid-outline'; break;
              case 'Shop': iconName = focused ? 'basket' : 'basket-outline'; break;
              case 'Cart': iconName = focused ? 'cart' : 'cart-outline'; break;
              case 'Profile': iconName = focused ? 'person' : 'person-outline'; break;
              case 'Products': iconName = focused ? 'cube' : 'cube-outline'; break;
              case 'Categories': iconName = focused ? 'list' : 'list-outline'; break;
              case 'Orders': iconName = focused ? 'cart' : 'cart-outline'; break;
              case 'Customers': iconName = focused ? 'people' : 'people-outline'; break;
              case 'Staff List': iconName = focused ? 'id-card' : 'id-card-outline'; break;
              case 'Manage Leaves': iconName = focused ? 'calendar' : 'calendar-outline'; break;
              case 'Attendance': iconName = focused ? 'time' : 'time-outline'; break;
              case 'Logs': iconName = focused ? 'document-text' : 'document-text-outline'; break;
              case 'Reports': iconName = focused ? 'bar-chart' : 'bar-chart-outline'; break;
              case 'Locations': iconName = focused ? 'location' : 'location-outline'; break;
              default: iconName = 'help-outline';
            }
            return <Ionicons name={iconName} size={size} color={color} />;
          },
          tabBarActiveTintColor: '#1877F2',
          tabBarInactiveTintColor: 'gray',
          headerStyle: {
            backgroundColor: '#fff',
            elevation: 0,
            shadowOpacity: 0,
            borderBottomWidth: 1,
            borderBottomColor: '#F4F7FE',
          },
          headerTitleStyle: {
            fontWeight: '900',
            color: '#1B2559',
          },
        })}
      >
        {!isMerchant ? (
          <>
            <Tab.Screen
              name="Home"
              component={HomeScreen}
              options={({ navigation }) => ({
                title: 'Grab & Go',
                headerTitle: () => (
                  <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                    <Text style={{ fontSize: 20, fontWeight: '900', color: '#1877F2' }}>Grab</Text>
                    <Text style={{ fontSize: 20, fontWeight: '900', color: '#00D563' }}>&Go</Text>
                  </View>
                ),
                headerRight: () => (
                  <View style={{ flexDirection: 'row', marginRight: 16, gap: 15 }}>
                    <TouchableOpacity onPress={() => navigation.navigate('Cart')}>
                      <Ionicons name="cart-outline" size={24} color="#1B2559" />
                    </TouchableOpacity>
                    <TouchableOpacity onPress={() => navigation.navigate('Profile')}>
                      <Ionicons name="person-outline" size={24} color="#1B2559" />
                    </TouchableOpacity>
                  </View>
                )
              })}
            />
            <Tab.Screen name="Shop" component={ShopScreen} options={{ title: 'Our Products', tabBarButton: () => null }} />
            <Tab.Screen name="Cart" component={CartScreen} options={{ title: 'My Cart' }} />
            <Tab.Screen name="Profile" component={ProfileScreen} options={{ title: 'My Profile' }} />
            <Tab.Screen name="Locations" component={LocationsScreen} options={{ title: 'Our Store' }} />
          </>
        ) : (
          <>
            <Tab.Screen name="Dashboard" component={MerchantDashboard} options={{ title: 'Dashboard' }} />
            <Tab.Screen name="Products" component={InventoryScreen} options={{ title: 'Products' }} />
            {role === 'admin' && <Tab.Screen name="Categories" component={CategoriesScreen} options={{ title: 'Categories' }} />}
            <Tab.Screen name="Orders" component={OrderManagementScreen} options={{ title: 'Orders' }} />
            {role === 'admin' && (
              <>
                <Tab.Screen name="Customers" component={UserManagementScreen} options={{ title: 'Customers' }} />
                <Tab.Screen name="Staff List" component={UserManagementScreen} options={{ title: 'Staff List' }} />
              </>
            )}
            <Tab.Screen name="Manage Leaves" component={MyLeavesScreen} options={{ title: 'Manage Leaves' }} />
            {role === 'admin' && (
              <>
                <Tab.Screen name="Attendance" component={AttendanceScreen} options={{ title: 'Attendance' }} />
                <Tab.Screen name="Logs" component={LogsScreen} options={{ title: 'Logs' }} />
                <Tab.Screen name="Reports" component={ReportsScreen} options={{ title: 'Reports' }} />
              </>
            )}
            <Tab.Screen name="Profile" component={ProfileScreen} options={{ title: 'My Profile' }} />
          </>
        )}
      </Tab.Navigator>
    </NavigationContainer>
  );
}

export default function App() {
  return (
    <AppProvider>
      <AppNavigator />
    </AppProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 20,
    color: '#1877F2',
  },
  tabBarContainer: {
    height: 65,
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#e0e0e0',
    elevation: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  tabBarScroll: {
    paddingHorizontal: 0,
  },
  tabItem: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    paddingHorizontal: 12,
    minWidth: 80,
  },
});
