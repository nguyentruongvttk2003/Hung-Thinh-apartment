import { configureStore } from '@reduxjs/toolkit';
import { useDispatch, useSelector, TypedUseSelectorHook } from 'react-redux';
import authReducer from './authSlice';
import notificationReducer from './notificationSlice';
import feedbackReducer from './feedbackSlice';
import invoiceReducer from './invoiceSlice';
import eventReducer from './eventSlice';
import apartmentReducer from './apartmentSlice';
import maintenanceReducer from './maintenanceSlice';
import voteReducer from './voteSlice';

export const store = configureStore({
  reducer: {
    auth: authReducer,
    notifications: notificationReducer,
    feedback: feedbackReducer,
    invoices: invoiceReducer,
    events: eventReducer,
    apartments: apartmentReducer,
    maintenance: maintenanceReducer,
    vote: voteReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: ['persist/PERSIST'],
      },
    }),
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export const useAppDispatch = () => useDispatch<AppDispatch>();
export const useAppSelector: TypedUseSelectorHook<RootState> = useSelector;
