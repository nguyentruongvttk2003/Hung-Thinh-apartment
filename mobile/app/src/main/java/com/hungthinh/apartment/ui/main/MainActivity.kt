package com.hungthinh.apartment.ui.main

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import com.google.android.material.bottomnavigation.BottomNavigationView
import com.hungthinh.apartment.R
import com.hungthinh.apartment.data.local.PreferenceManager
import com.hungthinh.apartment.ui.auth.LoginActivity
import com.hungthinh.apartment.ui.home.HomeFragment
import com.hungthinh.apartment.ui.notifications.NotificationsFragment
import com.hungthinh.apartment.ui.invoices.InvoicesFragment
import com.hungthinh.apartment.ui.feedback.FeedbackFragment
import com.hungthinh.apartment.ui.profile.ProfileFragment
import dagger.hilt.android.AndroidEntryPoint
import javax.inject.Inject

@AndroidEntryPoint
class MainActivity : AppCompatActivity() {
    
    @Inject
    lateinit var preferenceManager: PreferenceManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Check authentication status
        if (!preferenceManager.isLoggedIn()) {
            navigateToLogin()
            return
        }
        
        setContentView(R.layout.activity_main)

        setupBottomNavigation()
        
        // Load default fragment
        loadFragment(HomeFragment())
        
        // Show welcome message with user name
        val user = preferenceManager.getCurrentUser()
        val welcomeMessage = if (user != null) {
            "Chào mừng ${user.name}!"
        } else {
            "Chào mừng đến với HungThinh Apartment!"
        }
        Toast.makeText(this, welcomeMessage, Toast.LENGTH_SHORT).show()
        
        // Set action bar title
        supportActionBar?.title = "HungThinh Apartment"
    }
    
    private fun navigateToLogin() {
        val intent = Intent(this, LoginActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        finish()
    }

    private fun setupBottomNavigation() {
        val bottomNavigation = findViewById<BottomNavigationView>(R.id.bottom_navigation)
        bottomNavigation.menu.clear()
        bottomNavigation.menu.add(0, R.id.nav_home, 0, "Home").setIcon(android.R.drawable.ic_menu_info_details)
        bottomNavigation.menu.add(0, R.id.nav_notifications, 1, "Notifications").setIcon(android.R.drawable.ic_popup_reminder)
        bottomNavigation.menu.add(0, R.id.nav_invoices, 2, "Invoices").setIcon(android.R.drawable.ic_menu_agenda)
        bottomNavigation.menu.add(0, R.id.nav_feedback, 3, "Feedback").setIcon(android.R.drawable.ic_menu_edit)
        bottomNavigation.menu.add(0, R.id.nav_profile, 4, "Profile").setIcon(android.R.drawable.ic_menu_myplaces)
        
        bottomNavigation.selectedItemId = R.id.nav_home
        
        bottomNavigation.setOnItemSelectedListener { item ->
            when (item.itemId) {
                R.id.nav_home -> loadFragment(HomeFragment())
                R.id.nav_notifications -> loadFragment(NotificationsFragment())
                R.id.nav_invoices -> loadFragment(InvoicesFragment())
                R.id.nav_feedback -> loadFragment(FeedbackFragment())
                R.id.nav_profile -> loadFragment(ProfileFragment())
            }
            true
        }
    }
    
    private fun loadFragment(fragment: Fragment) {
        supportFragmentManager.beginTransaction()
            .replace(R.id.nav_host_fragment, fragment)
            .commit()
    }
}
