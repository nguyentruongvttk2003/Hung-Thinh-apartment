import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import eventService, { Event } from '../services/eventService';

interface EventState {
  events: Event[];
  upcomingEvents: Event[];
  selectedEvent: Event | null;
  isLoading: boolean;
  error: string | null;
  page: number;
  hasMore: boolean;
}

const initialState: EventState = {
  events: [],
  upcomingEvents: [],
  selectedEvent: null,
  isLoading: false,
  error: null,
  page: 1,
  hasMore: true,
};

// Async thunks
export const fetchEvents = createAsyncThunk(
  'event/fetch',
  async ({ page = 1, refresh = false }: { page?: number; refresh?: boolean }, { rejectWithValue }) => {
    try {
      const response = await eventService.getEvents(page);
      return { ...response, refresh };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải sự kiện');
    }
  }
);

export const fetchUpcomingEvents = createAsyncThunk(
  'event/fetchUpcoming',
  async (_, { rejectWithValue }) => {
    try {
      const events = await eventService.getUpcomingEvents();
      return events;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải sự kiện sắp tới');
    }
  }
);

export const fetchEventById = createAsyncThunk(
  'event/fetchById',
  async (eventId: number, { rejectWithValue }) => {
    try {
      const event = await eventService.getEventById(eventId);
      return event;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải thông tin sự kiện');
    }
  }
);

const eventSlice = createSlice({
  name: 'event',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    clearSelectedEvent: (state) => {
      state.selectedEvent = null;
    },
    resetEvents: (state) => {
      state.events = [];
      state.page = 1;
      state.hasMore = true;
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch events
      .addCase(fetchEvents.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchEvents.fulfilled, (state, action) => {
        state.isLoading = false;
        const { data, refresh } = action.payload;
        
        if (refresh) {
          state.events = data;
          state.page = 1;
        } else {
          state.events = [...state.events, ...data];
        }
        
        state.hasMore = data.length === 10; // Assuming 10 items per page
        state.page = refresh ? 2 : state.page + 1;
      })
      .addCase(fetchEvents.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch upcoming events
      .addCase(fetchUpcomingEvents.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchUpcomingEvents.fulfilled, (state, action) => {
        state.isLoading = false;
        state.upcomingEvents = action.payload;
      })
      .addCase(fetchUpcomingEvents.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch event by ID
      .addCase(fetchEventById.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchEventById.fulfilled, (state, action) => {
        state.isLoading = false;
        state.selectedEvent = action.payload;
      })
      .addCase(fetchEventById.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });
  },
});

export const { clearError, clearSelectedEvent, resetEvents } = eventSlice.actions;
export default eventSlice.reducer;
