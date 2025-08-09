import apiService from './apiService';
import { 
  Vote, 
  VoteOption, 
  VoteResponse, 
  PaginatedResponse, 
  ApiResponse 
} from '../types';

class VoteService {
  // Get all votes (active and past)
  async getVotes(page: number = 1, status?: string): Promise<PaginatedResponse<Vote>> {
    try {
      const params = new URLSearchParams({ page: page.toString(), limit: '10' });
      if (status) params.append('status', status);
      
      const response = await apiService.get<ApiResponse<PaginatedResponse<Vote>>>(
        `/votes?${params.toString()}`
      );
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách biểu quyết');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get active votes (currently ongoing)
  async getActiveVotes(): Promise<Vote[]> {
    try {
      const response = await apiService.get<ApiResponse<Vote[]>>('/votes/active');
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy danh sách biểu quyết đang diễn ra');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get vote details with options and current results
  async getVoteDetail(voteId: number): Promise<Vote> {
    try {
      const response = await apiService.get<ApiResponse<Vote>>(`/votes/${voteId}`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thông tin biểu quyết');
      }
    } catch (error) {
      throw error;
    }
  }

  // Submit vote response
  async submitVote(voteId: number, optionId: number): Promise<VoteResponse> {
    try {
      const response = await apiService.post<ApiResponse<VoteResponse>>(`/votes/${voteId}/respond`, {
        optionId
      });
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Gửi phiếu biểu quyết thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Check if user has voted for a specific vote
  async hasUserVoted(voteId: number): Promise<boolean> {
    try {
      const response = await apiService.get<ApiResponse<{ hasVoted: boolean }>>(`/votes/${voteId}/check-voted`);
      
      if (response.success && response.data) {
        return response.data.hasVoted;
      } else {
        return false;
      }
    } catch (error) {
      return false;
    }
  }

  // Get user's vote for a specific vote
  async getUserVote(voteId: number): Promise<VoteResponse | null> {
    try {
      const response = await apiService.get<ApiResponse<VoteResponse>>(`/votes/${voteId}/my-vote`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        return null;
      }
    } catch (error) {
      return null;
    }
  }

  // Get vote results (detailed breakdown)
  async getVoteResults(voteId: number): Promise<{
    vote: Vote;
    options: VoteOption[];
    totalVotes: number;
    quorumMet: boolean;
    participationRate: number;
  }> {
    try {
      const response = await apiService.get<ApiResponse<any>>(`/votes/${voteId}/results`);
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy kết quả biểu quyết');
      }
    } catch (error) {
      throw error;
    }
  }

  // Update vote (change your vote if allowed)
  async updateVote(voteId: number, optionId: number): Promise<VoteResponse> {
    try {
      const response = await apiService.put<ApiResponse<VoteResponse>>(`/votes/${voteId}/respond`, {
        optionId
      });
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Cập nhật phiếu biểu quyết thất bại');
      }
    } catch (error) {
      throw error;
    }
  }

  // Get vote statistics for dashboard
  async getVoteStatistics(): Promise<{
    activeVotes: number;
    completedVotes: number;
    participationRate: number;
    pendingVotes: Vote[];
  }> {
    try {
      const response = await apiService.get<ApiResponse<any>>('/votes/statistics');
      
      if (response.success && response.data) {
        return response.data;
      } else {
        throw new Error(response.message || 'Không thể lấy thống kê biểu quyết');
      }
    } catch (error) {
      throw error;
    }
  }
}

// Create singleton instance
const voteService = new VoteService();

export default voteService;
