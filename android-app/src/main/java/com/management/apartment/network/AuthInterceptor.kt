package com.management.apartment.network

import android.content.Context
import com.management.apartment.data.SessionManager
import okhttp3.Interceptor
import okhttp3.Response

class AuthInterceptor(context: Context) : Interceptor {
    private val session = SessionManager(context)

    override fun intercept(chain: Interceptor.Chain): Response {
        val token = session.getToken()
        val original = chain.request()
        val builder = original.newBuilder()
            .addHeader("Accept", "application/json")
        if (!token.isNullOrEmpty()) {
            builder.addHeader("Authorization", "Bearer $token")
        }
        return chain.proceed(builder.build())
    }
}


