package com.hungthinh.apartment

import android.app.Application
import com.jakewharton.threetenabp.AndroidThreeTen
import dagger.hilt.android.HiltAndroidApp

@HiltAndroidApp
class HungThinhApartmentApp : Application() {
    
    override fun onCreate() {
        super.onCreate()
        
        // Initialize ThreeTenABP for date/time handling
        AndroidThreeTen.init(this)
    }
} 