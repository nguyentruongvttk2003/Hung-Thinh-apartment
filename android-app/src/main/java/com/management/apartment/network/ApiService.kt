package com.management.apartment.network

import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

object ApiService {
    private const val BASE_URL = "http://10.0.2.2:8000/api/"

    private val logging = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }

    private lateinit var httpClient: OkHttpClient

    fun init(context: android.content.Context) {
        httpClient = OkHttpClient.Builder()
            .addInterceptor(logging)
            .addInterceptor(AuthInterceptor(context))
            .build()
    }

    val retrofit: Retrofit by lazy {
        if (!this::httpClient.isInitialized) {
            // Fallback without auth
            httpClient = OkHttpClient.Builder().addInterceptor(logging).build()
        }
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(httpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
    }
}


