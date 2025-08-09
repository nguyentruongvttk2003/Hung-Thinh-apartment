import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { Vote, VoteResponse, PaginatedResponse, ApiResponse } from '../types';
import voteService from '../services/voteService';

interface VoteState {
  votes: Vote[];
  activeVotes: Vote[];
  selectedVote: Vote | null;
  userVotes: { [voteId: number]: VoteResponse };
  voteStatistics: {
    activeVotes: number;
    completedVotes: number;
    participationRate: number;
  } | null;
  isLoading: boolean;
  error: string | null;
  page: number;
  hasMore: boolean;
}

const initialState: VoteState = {
  votes: [],
  activeVotes: [],
  selectedVote: null,
  userVotes: {},
  voteStatistics: null,
  isLoading: false,
  error: null,
  page: 1,
  hasMore: true,
};

// Async thunks
export const fetchVotes = createAsyncThunk(
  'vote/fetch',
  async ({ page = 1, refresh = false, status }: { page?: number; refresh?: boolean; status?: string }, { rejectWithValue }) => {
    try {
      const response = await voteService.getVotes(page, status);
      return { ...response, refresh };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải danh sách biểu quyết');
    }
  }
);

export const fetchActiveVotes = createAsyncThunk(
  'vote/fetchActive',
  async (_, { rejectWithValue }) => {
    try {
      const votes = await voteService.getActiveVotes();
      return votes;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải danh sách biểu quyết đang diễn ra');
    }
  }
);

export const fetchVoteDetail = createAsyncThunk(
  'vote/fetchDetail',
  async (voteId: number, { rejectWithValue }) => {
    try {
      const vote = await voteService.getVoteDetail(voteId);
      return vote;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải thông tin biểu quyết');
    }
  }
);

export const submitVote = createAsyncThunk(
  'vote/submit',
  async ({ voteId, optionId }: { voteId: number; optionId: number }, { rejectWithValue }) => {
    try {
      const voteResponse = await voteService.submitVote(voteId, optionId);
      return { voteId, voteResponse };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Gửi phiếu biểu quyết thất bại');
    }
  }
);

export const updateVote = createAsyncThunk(
  'vote/update',
  async ({ voteId, optionId }: { voteId: number; optionId: number }, { rejectWithValue }) => {
    try {
      const voteResponse = await voteService.updateVote(voteId, optionId);
      return { voteId, voteResponse };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Cập nhật phiếu biểu quyết thất bại');
    }
  }
);

export const fetchUserVote = createAsyncThunk(
  'vote/fetchUserVote',
  async (voteId: number, { rejectWithValue }) => {
    try {
      const userVote = await voteService.getUserVote(voteId);
      return { voteId, userVote };
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải phiếu biểu quyết của bạn');
    }
  }
);

export const fetchVoteResults = createAsyncThunk(
  'vote/fetchResults',
  async (voteId: number, { rejectWithValue }) => {
    try {
      const results = await voteService.getVoteResults(voteId);
      return results;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải kết quả biểu quyết');
    }
  }
);

export const fetchVoteStatistics = createAsyncThunk(
  'vote/fetchStatistics',
  async (_, { rejectWithValue }) => {
    try {
      const statistics = await voteService.getVoteStatistics();
      return statistics;
    } catch (error) {
      return rejectWithValue(error instanceof Error ? error.message : 'Lỗi khi tải thống kê biểu quyết');
    }
  }
);

const voteSlice = createSlice({
  name: 'vote',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    resetVotes: (state) => {
      state.votes = [];
      state.page = 1;
      state.hasMore = true;
    },
    setSelectedVote: (state, action: PayloadAction<Vote | null>) => {
      state.selectedVote = action.payload;
    },
    clearUserVotes: (state) => {
      state.userVotes = {};
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch votes cases
      .addCase(fetchVotes.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchVotes.fulfilled, (state, action) => {
        state.isLoading = false;
        const { data, last_page, current_page, refresh } = action.payload;
        
        if (refresh || current_page === 1) {
          state.votes = data;
        } else {
          state.votes.push(...data);
        }
        
        state.page = current_page;
        state.hasMore = current_page < last_page;
        state.error = null;
      })
      .addCase(fetchVotes.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Fetch active votes cases
      .addCase(fetchActiveVotes.fulfilled, (state, action) => {
        state.activeVotes = action.payload;
      })
      
      // Fetch vote detail cases
      .addCase(fetchVoteDetail.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(fetchVoteDetail.fulfilled, (state, action) => {
        state.isLoading = false;
        state.selectedVote = action.payload;
        state.error = null;
      })
      .addCase(fetchVoteDetail.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Submit vote cases
      .addCase(submitVote.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(submitVote.fulfilled, (state, action) => {
        state.isLoading = false;
        const { voteId, voteResponse } = action.payload;
        state.userVotes[voteId] = voteResponse;
        
        // Update vote count in selected vote if it matches
        if (state.selectedVote?.id === voteId) {
          if (state.selectedVote.totalVotes) {
            state.selectedVote.totalVotes += 1;
          } else {
            state.selectedVote.totalVotes = 1;
          }
        }
        
        // Update vote count in votes list
        const voteIndex = state.votes.findIndex(v => v.id === voteId);
        if (voteIndex !== -1) {
          if (state.votes[voteIndex].totalVotes) {
            state.votes[voteIndex].totalVotes += 1;
          } else {
            state.votes[voteIndex].totalVotes = 1;
          }
        }
        
        state.error = null;
      })
      .addCase(submitVote.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      })
      
      // Update vote cases
      .addCase(updateVote.fulfilled, (state, action) => {
        const { voteId, voteResponse } = action.payload;
        state.userVotes[voteId] = voteResponse;
      })
      
      // Fetch user vote cases
      .addCase(fetchUserVote.fulfilled, (state, action) => {
        const { voteId, userVote } = action.payload;
        if (userVote) {
          state.userVotes[voteId] = userVote;
        }
      })
      
      // Fetch vote results cases
      .addCase(fetchVoteResults.fulfilled, (state, action) => {
        const { vote } = action.payload;
        if (state.selectedVote?.id === vote.id) {
          state.selectedVote = { ...state.selectedVote, ...vote };
        }
      })
      
      // Fetch vote statistics cases
      .addCase(fetchVoteStatistics.fulfilled, (state, action) => {
        const { activeVotes, completedVotes, participationRate } = action.payload;
        state.voteStatistics = {
          activeVotes,
          completedVotes,
          participationRate,
        };
      });
  },
});

export const { clearError, resetVotes, setSelectedVote, clearUserVotes } = voteSlice.actions;
export default voteSlice.reducer;
