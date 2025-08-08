package com.hungthinh.apartment.data.local

import android.content.Context
import android.content.SharedPreferences
import com.google.gson.Gson
import com.hungthinh.apartment.data.model.User
import dagger.hilt.android.qualifiers.ApplicationContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PreferenceManager @Inject constructor(
    @ApplicationContext private val context: Context
) {
    companion object {
        private const val PREF_NAME = "apartment_prefs"
        private const val KEY_ACCESS_TOKEN = "access_token"
        private const val KEY_TOKEN_TYPE = "token_type"
        private const val KEY_EXPIRES_IN = "expires_in"
        private const val KEY_USER = "user"
        private const val KEY_IS_LOGGED_IN = "is_logged_in"
        private const val KEY_FIRST_TIME = "first_time"
        private const val KEY_LANGUAGE = "language"
        private const val KEY_THEME = "theme"
        private const val KEY_NOTIFICATIONS_ENABLED = "notifications_enabled"
    }
    
    private val sharedPreferences: SharedPreferences = 
        context.getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE)
    
    private val gson = Gson()
    
    // Token management
    fun saveAccessToken(token: String) {
        sharedPreferences.edit()
            .putString(KEY_ACCESS_TOKEN, token)
            .putBoolean(KEY_IS_LOGGED_IN, true)
            .apply()
    }
    
    fun getAccessToken(): String? {
        return sharedPreferences.getString(KEY_ACCESS_TOKEN, null)
    }
    
    fun saveTokenType(tokenType: String) {
        sharedPreferences.edit()
            .putString(KEY_TOKEN_TYPE, tokenType)
            .apply()
    }
    
    fun getTokenType(): String? {
        return sharedPreferences.getString(KEY_TOKEN_TYPE, null)
    }
    
    fun saveExpiresIn(expiresIn: Int) {
        sharedPreferences.edit()
            .putInt(KEY_EXPIRES_IN, expiresIn)
            .apply()
    }
    
    fun getExpiresIn(): Int {
        return sharedPreferences.getInt(KEY_EXPIRES_IN, 0)
    }
    
    // User management
    fun saveUser(user: User) {
        val userJson = gson.toJson(user)
        sharedPreferences.edit()
            .putString(KEY_USER, userJson)
            .apply()
    }
    
    fun getCurrentUser(): User? {
        val userJson = sharedPreferences.getString(KEY_USER, null)
        return if (userJson != null) {
            try {
                gson.fromJson(userJson, User::class.java)
            } catch (e: Exception) {
                null
            }
        } else {
            null
        }
    }
    
    // Login state
    fun isLoggedIn(): Boolean {
        return sharedPreferences.getBoolean(KEY_IS_LOGGED_IN, false) && 
               getAccessToken() != null
    }
    
    fun setLoggedIn(isLoggedIn: Boolean) {
        sharedPreferences.edit()
            .putBoolean(KEY_IS_LOGGED_IN, isLoggedIn)
            .apply()
    }
    
    // App settings
    fun isFirstTime(): Boolean {
        return sharedPreferences.getBoolean(KEY_FIRST_TIME, true)
    }
    
    fun setFirstTime(isFirstTime: Boolean) {
        sharedPreferences.edit()
            .putBoolean(KEY_FIRST_TIME, isFirstTime)
            .apply()
    }
    
    fun saveLanguage(language: String) {
        sharedPreferences.edit()
            .putString(KEY_LANGUAGE, language)
            .apply()
    }
    
    fun getLanguage(): String {
        return sharedPreferences.getString(KEY_LANGUAGE, "vi") ?: "vi"
    }
    
    fun saveTheme(theme: String) {
        sharedPreferences.edit()
            .putString(KEY_THEME, theme)
            .apply()
    }
    
    fun getTheme(): String {
        return sharedPreferences.getString(KEY_THEME, "light") ?: "light"
    }
    
    fun setNotificationsEnabled(enabled: Boolean) {
        sharedPreferences.edit()
            .putBoolean(KEY_NOTIFICATIONS_ENABLED, enabled)
            .apply()
    }
    
    fun areNotificationsEnabled(): Boolean {
        return sharedPreferences.getBoolean(KEY_NOTIFICATIONS_ENABLED, true)
    }
    
    // Clear all data
    fun clearAll() {
        sharedPreferences.edit().clear().apply()
    }
    
    // Clear only auth data
    fun clearAuthData() {
        sharedPreferences.edit()
            .remove(KEY_ACCESS_TOKEN)
            .remove(KEY_TOKEN_TYPE)
            .remove(KEY_EXPIRES_IN)
            .remove(KEY_USER)
            .remove(KEY_IS_LOGGED_IN)
            .apply()
    }
}
