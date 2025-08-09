import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import feedbackService, { Feedback } from '../services/feedbackService';

interface FeedbackState {
  feedbacks: Feedback[];
  selectedFeedback: Feedback | null;
  isLoading: boolean;
  error: string | null;
  page: number;
  hasMore: boolean;
}

const initialState: FeedbackState = {
  feedbacks: [],
  selectedFeedback: null,
  isLoading: false,
  error: null,
  page: 1,
  hasMore: true,
};

// Async thunks
export const fetchFeedbacks = createAsyncThunk(
  'feedback/fetch',
  async ({ page = 1, refresh = false }: { page?: number; refresh?: boolean }, { rejectWithValue }) => {
    try {
      const response = await feedbackService.getFeedbacks(page);
      return { ...response, refresh };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải phản hồi');
    }
  }
);

export const fetchFeedbackById = createAsyncThunk(
  'feedback/fetchById',
  async (feedbackId: number, { rejectWithValue }) => {
    try {
      const feedback = await feedbackService.getFeedbackById(feedbackId);
      return feedback;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải thông tin phản hồi');
    }
  }
);

export const createFeedback = createAsyncThunk(
  'feedback/create',
  async (feedbackData: any, { rejectWithValue }) => {
    try {
      const feedback = await feedbackService.createFeedback(feedbackData);
      return feedback;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tạo phản hồi');
    }
  }
);

const feedbackSlice = createSlice({
  name: 'feedback',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    clearSelectedFeedback: (state) => {
      state.selectedFeedback = null;
    },
    resetFeedbacks: (state) => {
      state.feedbacks = [];
      state.page = 1;
      state.hasMore = true;
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch feedbacks
      .addCase(fetchFeedbacks.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchFeedbacks.fulfilled, (state, action) => {
        state.isLoading = false;
        const { data, refresh } = action.payload;
        
        if (refresh) {
          state.feedbacks = data;
          state.page = 1;
        } else {
          state.feedbacks = [...state.feedbacks, ...data];
        }
        
        state.hasMore = data.length === 10; // Assuming 10 items per page
        state.page = refresh ? 2 : state.page + 1;
      })
      .addCase(fetchFeedbacks.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch feedback by ID
      .addCase(fetchFeedbackById.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchFeedbackById.fulfilled, (state, action) => {
        state.isLoading = false;
        state.selectedFeedback = action.payload;
      })
      .addCase(fetchFeedbackById.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Create feedback
      .addCase(createFeedback.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(createFeedback.fulfilled, (state, action) => {
        state.isLoading = false;
        state.feedbacks = [action.payload, ...state.feedbacks];
      })
      .addCase(createFeedback.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });
  },
});

export const { clearError, clearSelectedFeedback, resetFeedbacks } = feedbackSlice.actions;
export default feedbackSlice.reducer;
