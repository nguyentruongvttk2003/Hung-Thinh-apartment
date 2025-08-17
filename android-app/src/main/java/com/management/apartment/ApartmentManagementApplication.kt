package com.management.apartment

import android.app.Application

class ApartmentManagementApplication : Application() {
    
    override fun onCreate() {
        super.onCreate()
        
        // Application initialized successfully
        android.util.Log.d("ApartmentApp", "Application started successfully")

        // Init ApiService with auth interceptor
        com.management.apartment.network.ApiService.init(this)

        // Global crash logger to help diagnose immediate crash
        Thread.setDefaultUncaughtExceptionHandler { t, e ->
            android.util.Log.e("AppCrash", "Uncaught exception in thread ${t.name}", e)
        }
    }
}
