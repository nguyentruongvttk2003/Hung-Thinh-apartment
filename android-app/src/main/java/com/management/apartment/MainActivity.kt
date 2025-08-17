package com.management.apartment

import android.os.Bundle
import androidx.activity.addCallback
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.NavController
import androidx.navigation.fragment.NavHostFragment
import androidx.navigation.ui.setupWithNavController
import com.google.android.material.appbar.MaterialToolbar
import com.google.android.material.bottomnavigation.BottomNavigationView
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.net.HttpURLConnection
import java.net.URL

class MainActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        val existing = supportFragmentManager.findFragmentById(R.id.nav_host_fragment)
        val navHostFragment = if (existing is NavHostFragment) {
            existing
        } else {
            val created = NavHostFragment.create(R.navigation.nav_graph)
            supportFragmentManager
                .beginTransaction()
                .replace(R.id.nav_host_fragment, created)
                .setPrimaryNavigationFragment(created)
                .commitNow()
            created
        }
        val navController = navHostFragment.navController
        val toolbar = findViewById<MaterialToolbar>(R.id.topAppBar)
        setSupportActionBar(toolbar)
        findViewById<BottomNavigationView>(R.id.bottomNavigationView).setupWithNavController(navController)

        val rootDestinations = setOf(
            R.id.navigation_dashboard,
            R.id.navigation_apartments,
            R.id.navigation_invoices,
            R.id.navigation_community,
            R.id.navigation_notifications,
            R.id.navigation_events,
            R.id.navigation_amenities,
            R.id.navigation_votes,
            R.id.navigation_account
        )

        navController.addOnDestinationChangedListener { _, destination, _ ->
            supportActionBar?.title = destination.label
            val showUp = destination.id !in rootDestinations
            supportActionBar?.setDisplayHomeAsUpEnabled(showUp)
            toolbar.navigationIcon = if (showUp) toolbar.navigationIcon else null
        }

        toolbar.setNavigationOnClickListener { handleBack(navController) }
        onBackPressedDispatcher.addCallback(this) { handleBack(navController) }
    }

    private fun handleBack(navController: NavController) {
        if (!navController.popBackStack()) {
            finish()
        }
    }
}
